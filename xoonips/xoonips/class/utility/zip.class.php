<?php

/* vim: set expandtab sw=4 ts=4 sts=4: */
/**
 * Zip file creation library class for the XooNIps
 *  - original file can be found in phpMyAdmin 2.11.6 (zip.lib.php).
 *  - modified for runtime memory saving.
 *
 * $Revision:$
 */

/**
 * @version $phpMyAdmin-2.11.6-Id: zip.lib.php 10240 2007-04-01 11:02:46Z cybot_tm $
 */
if (!defined('XOOPS_ROOT_PATH')) {
    exit();
}

/**
 * Zip file creation class.
 * Makes zip files.
 *
 * Based on :
 *
 *  http://www.zend.com/codex.php?id=535&single=1
 *  By Eric Mueller <eric@themepark.com>
 *
 *  http://www.zend.com/codex.php?id=470&single=1
 *  by Denis125 <webmaster@atlant.ru>
 *
 *  a patch from Peter Listiak <mlady@users.sourceforge.net> for last modified
 *  date and time of the compressed file
 *
 * Official ZIP file format: http://www.pkware.com/appnote.txt
 */
// XooNIps: renamed class name
class XooNIpsUtilityZip extends XooNIpsUtility
{
    /**
     * Data buffer size.
     *
     * @var int
     */
    public $bsize = 65536;

    /**
     * File handle to store compressed data.
     *
     * @var resource
     */
    public $datasec_handle = false;

    /**
     * Central directory.
     *
     * @var array
     */
    public $ctrl_dir = array();

    /**
     * End of central directory record.
     *
     * @var string
     */
    public $eof_ctrl_dir = "\x50\x4b\x05\x06\x00\x00\x00\x00";

    /**
     * Last offset position.
     *
     * @var int
     */
    public $old_offset = 0;

    /**
     * Converts an Unix timestamp to a four byte DOS date and time format (date
     * in high two bytes, time in low two bytes allowing magnitude comparison).
     *
     * @param int  the current Unix timestamp
     *
     * @return int the current date in a four byte DOS format
     */
    public function unix2DosTime($unixtime = 0)
    {
        $timearray = ($unixtime == 0) ? getdate() : getdate($unixtime);

        if ($timearray['year'] < 1980) {
            $timearray['year'] = 1980;
            $timearray['mon'] = 1;
            $timearray['mday'] = 1;
            $timearray['hours'] = 0;
            $timearray['minutes'] = 0;
            $timearray['seconds'] = 0;
        } // end if

        return (($timearray['year'] - 1980) << 25) | ($timearray['mon'] << 21) | ($timearray['mday'] << 16) |
                ($timearray['hours'] << 11) | ($timearray['minutes'] << 5) | ($timearray['seconds'] >> 1);
    }

    // end of the 'unix2DosTime()' method

    /**
     * open zip file.
     *
     * @param string $zip_filename creating zip file name
     */
    public function open($zip_filename)
    {
        if (!empty($this->datasec_handle)) {
            return false;
        }
        $fh = @fopen($zip_filename, 'wb');
        if ($fh === false) {
            return false;
        }
        $this->datasec_handle = $fh;

        return true;
    }

    // end of the 'open()' method

    /**
     * Adds "file" to archive.
     *
     * @param string   local file path
     * @param string   name of the file in the archive (may contains the path)
     * @param int  the current timestamp
     *
     * @return bool result status, false if failed
     */
    public function add($path, $name)
    {
        if (empty($this->datasec_handle)) {
            return false;
        }

        // create compressed temporary file
        $tmpfile = tempnam('/tmp', 'XooNIpsZipFile_Add');
        if ($tmpfile === false) {
            return false;
        }
        $h = @fopen($path, 'rb');
        if ($h === false) {
            unlink($tmpfile);

            return false;
        }
        $hgz = gzopen($tmpfile, 'wb');
        if ($hgz === false) {
            unlink($tmpfile);
            fclose($h);

            return false;
        }
        while (!feof($h)) {
            $buf = fread($h, $this->bsize);
            gzwrite($hgz, $buf);
        }
        fclose($h);
        gzclose($hgz);

        $name = str_replace('\\', '/', $name);
        $time = filemtime($path);

        $dtime = dechex($this->unix2DosTime($time));
        $hexdtime = '\x'.$dtime[6].$dtime[7]
                  .'\x'.$dtime[4].$dtime[5]
                  .'\x'.$dtime[2].$dtime[3]
                  .'\x'.$dtime[0].$dtime[1];
        eval('$hexdtime = "'.$hexdtime.'";');

        $fr = "\x50\x4b\x03\x04";
        $fr .= "\x14\x00";            // ver needed to extract
        $fr .= "\x00\x00";            // gen purpose bit flag
        $fr .= "\x08\x00";            // compression method
        $fr .= $hexdtime;             // last mod time and date

        // "local file header" segment
        $htmp = fopen($tmpfile, 'rb');
        fseek($htmp, -8, SEEK_END);
        $ar = unpack('Vcrc', fread($htmp, 4));
        $crc = $ar['crc'];
        fseek($htmp, 10, SEEK_SET);

        $unc_len = filesize($path);
        $c_len = filesize($tmpfile) - 18;    // 10 for header, 4 for crc, 4 for isize
        $fr .= pack('V', $crc);             // crc32
        $fr .= pack('V', $c_len);           // compressed filesize
        $fr .= pack('V', $unc_len);         // uncompressed filesize
        $fr .= pack('v', strlen($name));    // length of filename
        $fr .= pack('v', 0);                // extra field length
        $fr .= $name;
        fwrite($this->datasec_handle, $fr);

        // "file data" segment
        $remain = $c_len;
        while ($remain && !feof($htmp)) {
            $buf = fread($htmp, min($remain, $this->bsize));
            fwrite($this->datasec_handle, $buf);
            $remain -= strlen($buf);
        }
        fclose($htmp);
        unlink($tmpfile);
        $fr_len = strlen($fr) + $c_len;

        // "data descriptor" segment (optional but necessary if archive is not
        // served as file)
        // nijel(2004-10-19): this seems not to be needed at all and causes
        // problems in some cases (bug #1037737)
        //$fr .= pack('V', $crc);                 // crc32
        //$fr .= pack('V', $c_len);               // compressed filesize
        //$fr .= pack('V', $unc_len);             // uncompressed filesize

        // now add to central directory record
        $cdrec = "\x50\x4b\x01\x02";
        $cdrec .= "\x00\x00";                // version made by
        $cdrec .= "\x14\x00";                // version needed to extract
        $cdrec .= "\x00\x00";                // gen purpose bit flag
        $cdrec .= "\x08\x00";                // compression method
        $cdrec .= $hexdtime;                 // last mod time & date
        $cdrec .= pack('V', $crc);           // crc32
        $cdrec .= pack('V', $c_len);         // compressed filesize
        $cdrec .= pack('V', $unc_len);       // uncompressed filesize
        $cdrec .= pack('v', strlen($name)); // length of filename
        $cdrec .= pack('v', 0);             // extra field length
        $cdrec .= pack('v', 0);             // file comment length
        $cdrec .= pack('v', 0);             // disk number start
        $cdrec .= pack('v', 0);             // internal file attributes
        $cdrec .= pack('V', 32);            // external file attributes - 'archive' bit set

        $cdrec .= pack('V', $this->old_offset); // relative offset of local header
        $this->old_offset += $fr_len;

        $cdrec .= $name;

        // optional extra field, file comment goes here
        // save to central directory
        $this->ctrl_dir[] = $cdrec;

        return true;
    }

    // end of the 'add()' method

    /**
     * Close zip file.
     *
     * @return bool status
     */
    public function close()
    {
        if (empty($this->datasec_handle)) {
            return false;
        }

        $datasec_len = ftell($this->datasec_handle);
        $ctrldir = implode('', $this->ctrl_dir);

        fwrite(
            $this->datasec_handle,
            $ctrldir.
            $this->eof_ctrl_dir.
            pack('v', sizeof($this->ctrl_dir)).  // total # of entries "on this disk"
            pack('v', sizeof($this->ctrl_dir)).  // total # of entries overall
            pack('V', strlen($ctrldir)).           // size of central dir
            pack('V', $datasec_len).               // offset to start of central dir
            "\x00\x00"                              // .zip file comment length
        );
        fclose($this->datasec_handle);

        // reset runtime variables
        $this->datasec_handle = false;
        $this->ctrl_dir = array();
        $this->old_offset = 0;

        return true;
    }

    // end of the 'close()' method
} // end of the 'XooNIpsUtilityZip' class
