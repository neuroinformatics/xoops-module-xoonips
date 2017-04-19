<?php

// $Revision:$
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
 * file download handling class.
 *
 * @copyright copyright &copy; 2008 RIKEN Japan
 */
class XooNIpsUtilityDownload extends XooNIpsUtility
{
    /**
     * HTTP_USER_AGENT.
     *
     * @var string HTTP_USER_AGENT environment variable
     */
    public $ua;

    /**
     * HTTP_ACCEPT_LANGUAGE.
     *
     * @var string HTTP_ACCEPT_LANGUAGE environment variable
     */
    public $al;

    /**
     * PATH_INFO.
     *
     * @var string PATH_INFO environment variable
     */
    public $pi;

    /**
     * client browser encoding.
     *
     * @var string client browser encoding
     */
    public $browser_encoding;

    /**
     * constractor.
     */
    public function __construct()
    {
        $this->ua = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        $this->al = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : '';
        $this->pi = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';
        if (!preg_match('/^\\/.*$/', $this->pi)) {
            $this->pi = '';
            // invalid path info
        }
        $this->browser_encoding = $this->_detect_browser_encoding();
    }

    /**
     * check PATH_INFO for file downloading.
     *
     * @param string $file_name downloading file name on server environment
     *
     * @return bool false if it will fail to get file name on client browser
     */
    public function check_pathinfo($file_name)
    {
        if (strstr($this->ua, 'KHTML')) {
            // KHTML based browser require PATH_INFO for file downloading
            $file_name = $this->_encode_utf8($file_name);
            if (urldecode($this->pi) != '/'.$file_name) {
                // does not match file name.
                return false;
            }

            return true;
        }
        // other browsers
        return true;
    }

    /**
     * append PATH_INFO to url string.
     *
     * @param string $url       url string
     * @param string $file_name file name on server environment
     *
     * @return string appended url
     */
    public function append_pathinfo($url, $file_name)
    {
        $pathinfo = '/'.urlencode($this->_encode_utf8($file_name));
        if (preg_match('/^([^\\?]+)(\\?.*)$/', $url, $matches)) {
            $result = $matches[1].$pathinfo.$matches[2];
        } else {
            $result = $url.$pathinfo;
        }

        return $result;
    }

    /**
     * convert encoding to client environment.
     *
     * @param string $text     source text
     * @param string $fallback unmapped character encoding method
     *                         'h' : encode to HTML numeric entities
     *                         'u' : encode to UTF-8 based url string
     *
     * @return string appended url
     */
    public function convert_to_client($text, $fallback)
    {
        $result = $this->_convert_encoding($text, $this->browser_encoding, $fallback);

        return $result;
    }

    /**
     * download file.
     *
     * @param string $file_path downloading local file path
     * @param string $file_name file name on server environment
     * @param string $mime_type mime type of downloading file
     */
    public function download_file($file_path, $file_name, $mime_type)
    {
        // check file exists
        if (!file_exists($file_path) || !is_readable($file_path)) {
            die('Fatal Error : file not found');
        }

        // get file inforamation
        $file_size = filesize($file_path);

        // output header
        $this->output_header($file_name, $mime_type, $file_size);

        // output content body
        // readfile( $file_path );
        $chunksize = 1024 * 1024;
        $fh = fopen($file_path, 'rb');
        while (!feof($fh)) {
            echo fread($fh, $chunksize);
            flush();
        }
        fclose($fh);
    }

    /**
     * download data.
     *
     * @param string $str       downloading data
     * @param string $file_name file name on server environment
     * @param string $mime_type mime type
     */
    public function download_data($str, $file_name, $mime_type)
    {
        // get file inforamation
        $file_size = strlen($str);
        $this->output_header($file_name, $mime_type, $file_size);
        // output content body
        echo $str;
    }

    /**
     * output header for file download.
     *
     * @param string $file_name file name on server environment
     * @param string $mime_type mime type
     * @param int    $file_size file size
     */
    public function output_header($file_name, $mime_type, $file_size = '')
    {
        $content_disposition = $this->_content_disposition_filename($file_name);
        // remove ob fileters
        $handlers = ob_list_handlers();
        while (!empty($handlers)) {
            ob_end_clean();
            $handlers = ob_list_handlers();
        }

        // unlimit time out
        set_time_limit(0);

        // output header
        header('Expires: Sat, 01 Jan 2000 00:00:00 GMT');
        header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
        // Cache-Control: avoid IE bug - see http://support.microsoft/kb/436605/ja
        header('Cache-Control: none');
        if ($content_disposition) {
            header('Content-Disposition: attachment; filename="'.$content_disposition.'"');
        }
        if (!empty($file_size)) {
            header('Content-Length: '.$file_size);
        }
        header('Content-Type: '.$mime_type);
    }

    /**
     * detect browser encoding.
     *
     * @return string detected encoding
     */
    public function _detect_browser_encoding()
    {
        static $windows1252_map = array(
        // English
        'en',
        );
        $encoding = 'ASCII';
        if (strstr($this->ua, 'Mac OS X')) {
            $encoding = 'UTF-8';
        } elseif (strstr($this->ua, 'Windows')) {
            if (strstr($this->ua, 'MSIE') || strstr($this->ua, 'Gecko')) {
                if (strstr($this->al, 'ja')) {
                    // for japanese
                    $encoding = 'SJIS-win';
                } else {
                    // for Western Europian : Windows-1252
                    foreach ($windows1252_map as $lang) {
                        if (strstr($this->al, $lang)) {
                            $encoding = 'Windows-1252';
                            break;
                        }
                    }
                }
            }
        }

        return $encoding;
    }

    /**
     * generate file name for 'Content-Disposition:' header.
     *
     * @param string $filename file name on server environment
     *
     * @return string generated file name
     */
    public function _content_disposition_filename($file_name)
    {
        if (strstr($this->ua, 'MSIE') || strstr($this->ua, 'Trident')) {
            // Microsoft Internet Explorer
            // - utf8 + x-www-form-url
            $client_filename = $this->_convert_encoding($file_name, $this->browser_encoding, 'u');
            $utf8_client_filename = $this->_encode_utf8($client_filename, $this->browser_encoding);

            return urlencode($utf8_client_filename);
        } elseif (strstr($this->ua, 'KHTML')) {
            // KHTML based browser (e.g. Safari)
            // - return empty string. this browser have to use PATH_INFO.
            return '';
        } elseif (strstr($this->ua, 'Gecko') || strstr($this->ua, 'Mac OS X')) {
            // Gecko based browser (e.g. Mozilla FireFox)
            // - rfc2047 : mime header encode
            $client_file_name = $this->convert_to_client($file_name, 'u');
            // set mime encoding
            $mime_encoding = $this->browser_encoding;
            // save current internal encoding
            $internal_encoding_orig = mb_internal_encoding();
            // change internal encoding for mb_encode_mimeheader()
            if (!@mb_internal_encoding($this->browser_encoding)) {
                if ($this->browser_encoding == 'SJIS-win') {
                    // use fallback encoding 'Shift_JIS'
                    mb_internal_encoding('Shift_JIS');
                } else {
                    // use fallback encoding 'ASCII'
                    $client_file_name = $this->_convert_encoding($file_name, 'ASCII', 'u');
                    mb_internal_encoding('ASCII');
                    $mime_encoding = 'ASCII';
                }
            }
            // encode mime header
            $mimeheader = mb_encode_mimeheader($client_file_name, $mime_encoding, 'B');
            // restore internal encoding
            mb_internal_encoding($internal_encoding_orig);
            // done
            return $mimeheader;
        }
        // unknown browsers
        return $this->_convert_encoding($file_name, 'ASCII', 'u');
    }

    /**
     * convert encoding to UTF-8.
     *
     * @param string $text     input text
     * @param string $encoding text encoding
     *
     * @return string 'UTF-8' encoded text string
     */
    public function _encode_utf8($text, $encoding = '')
    {
        $textutil = &xoonips_getutility('text');
        if (empty($encoding)) {
            $encoding = mb_detect_encoding($text);
        }
        $text = mb_convert_encoding($text, 'UTF-8', $encoding);
        $text = $textutil->html_numeric_entities($text);
        $text = mb_decode_numericentity($text, array(0, 0x10ffff, 0, 0x1fffff), 'UTF-8');

        return $text;
    }

    /**
     * convert encoding.
     *
     * @param string $text     input text
     * @param string $encoding output text encoding
     * @param string $fallback unmapped character encoding method
     *                         'h' : encode to HTML numeric entities
     *                         'u' : encode to UTF-8 based url string
     *
     * @return string encoding converted text string
     */
    public function _convert_encoding($text, $encoding, $fallback)
    {
        $unicode = &xoonips_getutility('unicode');
        $text = $this->_encode_utf8($text);
        $text = $unicode->decode_utf8($text, $encoding, $fallback);

        return $text;
    }
}

/*

// example1 : file download
$download = new XooNIpsDownload();
$filename = 'hogehoge.txt'; // downloding file name on server environment
if ( ! $download->check_pathinfo( $filename ) ) {
  $url = '....'; // create url with PATH_INFO
  $url = $download->append_pathinfo( $url, $filename );
  header( 'Location: '.$url );
  exit();
}
// uncomment below line if use client filename
// $client_filename = $download->convert_to_client( $filename, 'u' );
// ... do something
$local_filepath = '/var/tmp/hogehoget.txt'; // generated local file
// ... do something
$download->download_file( $local_filepath, $filename, 'text/plain' );
unlink( $local_filepath );
exit();

// example2 : data download
$download = new XooNIpsDownload();
$filename = 'hogehoge.txt'; // downloding file name on server environment
if ( ! $download->check_pathinfo( $filename ) ) {
  $url = '....'; // create url with PATH_INFO
  $url = $download->append_pathinfo( $url, $filename );
  header( 'Location: '.$url );
  exit();
}
// uncomment below line if use client filename
// $client_filename = $download->convert_to_client( $filename, 'u' );
// ... do something
$data = readfile( '/var/tmp/ddddd.txt' ); // generated local file
$mimetype = 'text/plain; charset="ASCII"';
// ... do something
$download->download_data( $data, $filename, $mimetype );

*/
