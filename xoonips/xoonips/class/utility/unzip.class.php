<?php

// $Revision: 1.1.2.11 $
// ------------------------------------------------------------------------- //
//  XooNIps - Neuroinformatics Base Platform System                          //
//  Copyright (C) 2005-2011 RIKEN, Japan All rights reserved.                //
//  http://xoonips.sourceforge.jp/                                           //
// ------------------------------------------------------------------------- //
//  This program is free software; you can redistribute it and/or modify     //
//  it under the terms of the GNU General Public License as published by     //
//  the Free Software Foundation; either version 2 of the License, or        //
//  (at your option) any later version.                                      //
//                                                                           //
//  You may not change or alter any portion of this comment or credits       //
//  of supporting developers from this source code or any supporting         //
//  source code which is considered copyrighted (c) material of the          //
//  original comment or credit authors.                                      //
//                                                                           //
//  This program is distributed in the hope that it will be useful,          //
//  but WITHOUT ANY WARRANTY; without even the implied warranty of           //
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            //
//  GNU General Public License for more details.                             //
//                                                                           //
//  You should have received a copy of the GNU General Public License        //
//  along with this program; if not, write to the Free Software              //
//  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA //
// ------------------------------------------------------------------------- //
if (!defined('XOOPS_ROOT_PATH')) {
    exit();
}

/**
 * Zip file extraction library.
 *
 * @copyright copyright &copy; 2005-2008 RIKEN Japan
 */
class XooNIpsUtilityUnzip extends XooNIpsUtility
{
    /**
   * zip file name.
   *
   * @var string
   */
  public $_zfname = '';

  /**
   * zip file handle.
   *
   * @var resource
   */
  public $_zfhandle = false;

  /**
   * end of central directory.
   *
   * @var array
   */
  public $_ecdirecty = array();

  /**
   * central directories.
   *
   * @var array
   */
  public $_cdirecties = array();

  /**
   * zip file entries.
   *
   * @var array
   */
  public $_entries = array();

  /**
   * open zip file.
   *
   * @param string $zip_filename extracting zip file name
   *
   * @return bool false if failure
   */
  public function open($zip_filename)
  {
      if ($this->_zfhandle) {
          // close already opened file before new file open
      $this->close();
      }
      $fh = @fopen($zip_filename, 'rb');
      if ($fh === false) {
          return false;
      }
      $this->_zfname = $zip_filename;
      $this->_zfhandle = $fh;
      if (!$this->_read_all_entries()) {
          // no entries found
      $this->close();

          return false;
      }

      return true;
  }

  /**
   * close zip file.
   *
   * @return bool false if failure
   */
  public function close()
  {
      if ($this->_zfhandle === false) {
          // zip file not opened
      return false;
      }
      fclose($this->_zfhandle);
    // initialize local resouces
    $this->_zfname = '';
      $this->_zfhandle = false;
      $this->_ecdirectory = array();
      $this->_cdirectories = array();
      $this->_entries = array();

      return true;
  }

  /**
   * get zip information.
   *
   * @param string $key
   *
   * @return mixed information
   */
  public function get_zip_information($key)
  {
      if (!isset($this->_ecdirectory[$key])) {
          return false;
      }

      return $this->_ecdirectory[$key];
  }

  /**
   * get extra information of content file.
   *
   * @param string $fname file name
   * @param string $key
   *
   * @return mixed information
   */
  public function get_extra_information($fname, $key)
  {
      if ((!isset($this->_cdirectories[$fname]) || (!isset($this->_cdirectories[$fname][$key])))) {
          return false;
      }

      return $this->_cdirectories[$fname][$key];
  }

  /**
   * get file information.
   *
   * @param string $fname file name
   * @param string $key
   *
   * @return mixed information
   */
  public function get_file_information($fname, $key)
  {
      if ((!isset($this->_entries[$fname]) || (!isset($this->_entries[$fname][$key])))) {
          return false;
      }

      return $this->_entries[$fname][$key];
  }

  /**
   * get content file name list.
   *
   * @return array file name array
   */
  public function get_file_list()
  {
      return array_keys($this->_entries);
  }

  /**
   * get file data.
   *
   * @param string $filename
   *
   * @return string file data
   */
  public function get_data($fname)
  {
      $data = false;
      if (!isset($this->_entries[$fname])) {
          return $data;
      }
      if (isset($this->_cdirectories[$fname])) {
          $entry = &$this->_cdirectories[$fname];
      } else {
          $entry = &$this->_entries[$fname];
      }
      $data_offset = $this->_entries[$fname]['data_offset'];
      if (substr($entry['filename'], -1) == '/') {
          // this is directory
      return $data;
      }
      if ($entry['bitflag'] & 0x01) {
          // file is encrypted
      return $data;
      }
    // seek to file data offset
    fseek($this->_zfhandle, $data_offset, SEEK_SET);
      switch ($entry['compmethod']) {
    case 0:
      // not compressed
      $data = fread($this->_zfhandle, $entry['compsize']);
      break;
    case 8:
      // deflate
      $data = gzinflate(fread($this->_zfhandle, $entry['compsize']));
      break;
    case 12:
      // bzip2
      if (function_exists('bzdecompress')) {
          $data = bzdecompress(fread($this->_zfhandle, $entry['compsize']));
      }
      break;
    default:
      // unsupported compression method
      break;
    }

      return $data;
  }

  /**
   * extract file.
   *
   * @param string $fname   file name
   * @param string $basedir base directory
   *
   * @return bool false if failure
   */
  public function extract_file($fname, $basedir)
  {
      if (!isset($this->_entries[$fname])) {
          return false;
      }
      if (isset($this->_cdirectories[$fname])) {
          $entry = &$this->_cdirectories[$fname];
      } else {
          $entry = &$this->_entries[$fname];
      }
      $data_offset = $this->_entries[$fname]['data_offset'];
    // use unix path separator
    $basedir = str_replace('\\', '/', $basedir);
    // remove absolute path separator, this is dangerous.
    if (substr($fname, 0, 1) == '/') {
        $fname = substr($fname, 1);
    }
      $filepath = $basedir.'/'.$fname;
      if (substr($entry['filename'], -1) == '/') {
          // this is directory
      return $this->_create_directory($filepath);
      }
      if ($entry['bitflag'] & 0x01) {
          // file is encrypted
      return false;
      }
    // create sub directory
    if (!$this->_create_directory($filepath)) {
        return false;
    }

    // extract target file of zip archive to temporary file
    // data buffer
    $unit = 16384;
    // file data size in zip
    $size = $entry['compsize'];

    // seek to file data offset
    fseek($this->_zfhandle, $data_offset, SEEK_SET);

    // open out put file
    $ofh = @fopen($filepath, 'wb');
      if ($ofh === false) {
          return false;
      }
      switch ($entry['compmethod']) {
    case 0:
      // not compressed
      while (!feof($this->_zfhandle) && $size > 0) {
          $len = $unit < $size ? $unit : $size;
          $buf = fread($this->_zfhandle, $len);
          if ($buf === false) {
              fclose($ofh);
              unlink($filepath);

              return false;
          }
          if (false === fwrite($ofh, $buf)) {
              fclose($ofh);
              unlink($filepath);

              return false;
          }
          $size -= $len;
      }
      break;
    case 8:
      // deflate
      // create temporary file
      $tfn = tempnam('/tmp', 'XooNIpsUnzip');
      $tfh = fopen($tfn, 'wb');
      if ($tfh === false) {
          fclose($ofh);
          unlink($filepath);

          return false;
      }
      // ID1
      fwrite($tfh, "\x1f");
      // ID2
      fwrite($tfh, "\x8b");
      // CM=8
      fwrite($tfh, "\x08");
      // FLAGS(all zero)
      fwrite($tfh, "\x00");
      // MTIME(1970/1/1)
      fwrite($tfh, "\x00\x00\x00\x00");
      // XFL=4
      fwrite($tfh, "\x00");
      // OS(unknown)
      fwrite($tfh, "\xff");
      while (!feof($this->_zfhandle) && $size > 0) {
          $len = $unit < $size ? $unit : $size;
          $buf = fread($this->_zfhandle, $len);
          if ($buf === false) {
              fclose($tfh);
              unlink($tfn);
              fclose($ofh);
              unlink($filepath);

              return false;
          }
          if (false === fwrite($tfh, $buf)) {
              fclose($tfh);
              unlink($tfn);
              fclose($ofh);
              unlink($filepath);

              return false;
          }
          $size -= $len;
      }
      // CRC32
      fwrite($tfh, pack('V', $entry['crc32']));
      // ISIZE
      fwrite($tfh, pack('V', $entry['uncompsize']));
      fclose($tfh);
      // read temporary file and write to $filepath
      $size = $entry['uncompsize'];
      $result = true;
      $tfh = gzopen($tfn, 'rb');
      if ($tfh === false) {
          unlink($tfn);
          fclose($ofh);
          unlink($filepath);

          return false;
      }
      while (!gzeof($tfh) && $size > 0) {
          $len = $unit < $size ? $unit : $size;
          $buf = gzread($tfh, $len);
          if ($buf == '' || false === fwrite($ofh, $buf)) {
              // maybe corrupt zip file
          fclose($tfh);
              unlink($tfn);
              fclose($ofh);
              unlink($filepath);

              return false;
          }
          $size -= $len;
      }
      fclose($ofh);
      fclose($tfh);
      unlink($tfn);
      break;
    case 12:
      // bzip2
    default:
      // unsupported compression method
      fclose($ofh);
      unlink($filepath);

      return false;
    }

      return true;
  }

  /**
   * read all entries.
   *
   * @return bool false if failure
   */
  public function _read_all_entries()
  {
      // try to search 'end of central directory'
    if ($this->_search_end_of_central_directory()) {
        // 'end of central directory' found
      // seek first entry point of central directories
      fseek($this->_zfhandle, $this->_ecdirectory['offset'], SEEK_SET);
        while ($this->_read_central_directory());
      // read file entries
      foreach ($this->_cdirectories as $cdir) {
          fseek($this->_zfhandle, $cdir['offset'], SEEK_SET);
          $this->_read_local_file_header();
      }
    } else {
        // 'end of central directory' not found
      // read file entries from top of file pointer
      fseek($this->_zfhandle, 0, SEEK_SET);
        while ($this->_read_local_file_header());
    }

      return !empty($this->_entries);
  }

  /**
   * Search contents of 'end of central directory'.
   *
   * @return bool false if 'end of central directory' not found
   */
  public function _search_end_of_central_directory()
  {
      static $signature = "\x50\x4b\x05\x06";
      fseek($this->_zfhandle, -(1024 + 22), SEEK_END);
      $sig = fread($this->_zfhandle, 4);
      while ($sig != $signature) {
          if (feof($this->_zfhandle)) {
              return false;
          }
          $sig = substr($sig, 1).fread($this->_zfhandle, 1);
      }
      $entry = array();
    // number of this disk
    $entry['numofdisk'] = $this->_fread_unpack('us');
    // number of the disk with the start of the central directory
    $entry['numofdiskwithcentraldir'] = $this->_fread_unpack('us');
    // total number of entries in the central directory on this disk
    $entry['entriescountondisk'] = $this->_fread_unpack('us');
    // total number of entries in the central directory
    $entry['entriescount'] = $this->_fread_unpack('us');
    // size of the central directory
    $entry['centraldirsize'] = $this->_fread_unpack('ul');
    // offset of start of central directory with respect to the starting
    // disk number
    $entry['offset'] = $this->_fread_unpack('ul');
    // .zip file comment length
    $entry['commentlen'] = $this->_fread_unpack('us');
    // .zip file comment
    $entry['comment'] = ($entry['commentlen'] > 0) ? fread($this->_zfhandle, $entry['commentlen']) : '';
      $this->_ecdirectory = $entry;

      return true;
  }

  /**
   * read 'central directory' information.
   *
   * @return bool false if failure
   */
  public function _read_central_directory()
  {
      static $signature = "\x50\x4b\x01\x02";
      $sig = fread($this->_zfhandle, 4);
      if ($sig != $signature) {
          return false;
      }
      $cdir = array();
    // version made by
    $cdir['versionmadeby'] = $this->_fread_unpack('us');
    // version needed to extract
    $cdir['version'] = $this->_fread_unpack('us');
    // general purpose bit flag
    $cdir['bitflag'] = $this->_fread_unpack('us');
    // compression method
    $cdir['compmethod'] = $this->_fread_unpack('us');
    // last mod file time
    $cdir['mod_time'] = $this->_fread_unpack('us');
    // last mod file date
    $cdir['mod_date'] = $this->_fread_unpack('us');
    // crc-32
    $cdir['crc32'] = $this->_fread_unpack('ul');
    // compressed size
    $cdir['compsize'] = $this->_fread_unpack('ul');
    // uncompressed size
    $cdir['uncompsize'] = $this->_fread_unpack('ul');
    // file name length
    $cdir['filenamelen'] = $this->_fread_unpack('us');
    // extra field length
    $cdir['extralen'] = $this->_fread_unpack('us');
    // file comment length
    $cdir['commentlen'] = $this->_fread_unpack('us');
    // disk number start
    $cdir['disknum'] = $this->_fread_unpack('us');
    // internal file attributes
    $cdir['infileattr'] = $this->_fread_unpack('us');
    // external file attributes
    $cdir['exfileattr'] = $this->_fread_unpack('ul');
    // relative offset of local header
    $cdir['offset'] = $this->_fread_unpack('ul');
    // file name (variable size)
    $cdir['filename'] = ($cdir['filenamelen'] > 0) ? fread($this->_zfhandle, $cdir['filenamelen']) : '';
    // extra field (variable size)
    $cdir['extra'] = ($cdir['extralen'] > 0) ? fread($this->_zfhandle, $cdir['extralen']) : '';
    // file comment (variable size)
    $cdir['comment'] = ($cdir['commentlen'] > 0) ? fread($this->_zfhandle, $cdir['commentlen']) : '';

      if ($cdir['filename'] != '') {
          $this->_cdirectories[$cdir['filename']] = $cdir;
      }

      return true;
  }

  /**
   * read local file header.
   *
   * @return array file entry header information
   */
  public function _read_local_file_header()
  {
      static $signature = "\x50\x4b\x03\x04";
      $entry = array();
      $entry['offset'] = ftell($this->_zfhandle);
      $sig = fread($this->_zfhandle, 4);
      if ($sig != $signature) {
          fseek($this->_zfhandle, 0, SEEK_END);
      // move to the end of file
      return false;
      }
    // version needed to extract
    $entry['version'] = $this->_fread_unpack('us');
    // general purpose bit flag
    $entry['bitflag'] = $this->_fread_unpack('us');
    // compression method
    $entry['compmethod'] = $this->_fread_unpack('us');
    // last mod file time
    $entry['mod_time'] = $this->_fread_unpack('us');
    // last mod file date
    $entry['mod_date'] = $this->_fread_unpack('us');
    // crc-32
    $entry['crc32'] = $this->_fread_unpack('ul');
    // compressed size
    $entry['compsize'] = $this->_fread_unpack('ul');
    // uncompressed size
    $entry['uncompsize'] = $this->_fread_unpack('ul');
    // file name length
    $entry['filenamelen'] = $this->_fread_unpack('us');
    // extra field length
    $entry['extralen'] = $this->_fread_unpack('us');
    // file name
    $entry['filename'] = ($entry['filenamelen'] > 0) ? fread($this->_zfhandle, $entry['filenamelen']) : '';
    // extra field
    $entry['extra'] = ($entry['extralen'] > 0) ? fread($this->_zfhandle, $entry['extralen']) : '';

    // get file data offset
    $entry['data_offset'] = ftell($this->_zfhandle);

    // skip file data
    fseek($this->_zfhandle, $entry['compsize'], SEEK_CUR);

    // data descriptor
    if ($entry['bitflag'] & 0x04) {
        // crc-32
      $entry['crc32'] = $this->_fread_unpack('ul');
      // compressed size
      $entry['compsize'] = $this->_fread_unpack('ul');
      // uncompressed size
      $entry['uncompsize'] = $this->_fread_unpack('ul');
    }

      if ($entry['filename'] != '') {
          $this->_entries[$entry['filename']] = $entry;
      }

      return true;
  }

  /**
   * read unpacked data from file.
   *
   * @return mixed data
   */
  public function _fread_unpack($type)
  {
      static $types = array(
      // unsigned short integer of little endian
      'us' => array(
        'format' => 'v',
        'length' => 2,
      ),
      // unsigned long integer of little endian
      'ul' => array(
        'format' => 'V',
        'length' => 4,
      ),
    );
      if (!isset($types[$type])) {
          return false;
      }
      $data = fread($this->_zfhandle, $types[$type]['length']);
      $arr = unpack($types[$type]['format'], $data);

      return $arr[1];
  }

  /**
   * create directory.
   *
   * @param string $filepath
   *
   * @return bool false if failure
   */
  public function _create_directory($filepath)
  {
      $pos = strrpos($filepath, '/');
      if ($pos === false) {
          // $filepath doesn't contain directory path
      return true;
      }
      $dirpath = substr($filepath, 0, $pos);
      $dirnames = explode('/', $dirpath);
      $path = '';
      foreach ($dirnames as $dirname) {
          if ($dirname == '') {
              if ($path == '') {
                  $path = '/';
              } else {
                  // ignore double /
              }
          } else {
              if ($path == '') {
                  $path = $dirname;
              } else {
                  $path .= '/'.$dirname;
              }
          }
          if (!is_dir($path)) {
              if (!@mkdir($path, 0755)) {
                  // failed to create directory
          return false;
              }
          }
      }

      return true;
  }
}

// $unzip = new XooNIpsUtilityUnzip();
// if ( $unzip->open( $argv[1] ) ) {
//   $files = $unzip->get_file_list();
//   var_dump( $files );
//   foreach( $files as $file ) {
//     if ( ! $unzip->extract_file( $file, '.' ) ) {
//       echo 'extract ERROR: '.$file. "\n";
//     }
//   }
//   $unzip->close();
// } else {
//   echo 'failed to open zip file : '.$argv[1]."\n";
// }
