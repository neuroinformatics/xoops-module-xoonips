<?php

// $Revision: 1.1.4.3 $
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
 * abstract class for file search plugin 2.0.
 */
class XooNIpsFileSearchPlugin
{
    /**
   * file handle.
   *
   * @var resource
   */
  public $handle = false;

  /**
   * is xml data.
   *
   * @var bool
   */
  public $is_xml = false;

  /**
   * is UTF-8 data.
   *
   * @var bool
   */
  public $is_utf8 = true;

  /**
   * log for last operation.
   *
   * @var resource
   */
  public $lastlog = '';

  /**
   * constractor.
   */
  public function XooNIpsFileSearchPlugin()
  {
  }

  /**
   * open file.
   *
   * @param string $filename file name
   *
   * @return bool false if failure
   */
  public function open($filename)
  {
      $this->lastlog = '';
      if ($this->handle !== false) {
          $this->lastlog = 'FILE ALREADY OPENED';

          return false;
      }
      $this->_open_file($filename);
      if ($this->handle === false) {
          $this->lastlog = 'FAILED TO OPEN FILE';

          return false;
      }

      return true;
  }

  /**
   * close file.
   *
   * @return bool false if failure
   */
  public function close()
  {
      $this->lastlog = '';
      if ($this->handle == false) {
          $this->lastlog = 'FILE NOT OPENED';

          return false;
      }
      $this->_close_file();

      return true;
  }

  /**
   * fetch 'UTF-8' text from file.
   *
   * @return string fetched data if false returned an error occured
   */
  public function fetch()
  {
      $this->lastlog = '';
      if ($this->handle == false) {
          $this->lastlog = 'FILE NOT OPENED';

          return false;
      }
      $text = '';
      while (!$this->_is_eof()) {
          $tmp = $this->_fetch_data();
          if ($tmp != false) {
              $text .= $tmp;
          }
      }
      if (!$this->is_utf8) {
          // convert encoding to utf8
      $unicode = &xoonips_getutility('unicode');
          $text = $unicode->encode_utf8($text);
      }
      if ($this->is_xml) {
          // convert html or xml entities to utf8 character
      $textutil = &xoonips_getutility('text');
          $text = $textutil->html_numeric_entities($text);
          $text = preg_replace('/&#x([0-9a-f]+);/ie', 'chr(hexdec("\\1"))', $text);
          $text = preg_replace('/&#([0-9]+);/e', 'chr("\\1")', $text);
      }
    // chop non printable characters
    $text = preg_replace('/[\\x00-\\x1f\\x7f]/', ' ', $text);
    // join white space separated multibyte characters
    $text = preg_replace('/([^\\x20-\\x7e]) ([^\\x20-\\x7e])/', '\\1\\2', $text);
    // TODO: i want to use \s+ pattern.
    //       but it's very slow to extract search text, why??
    // $text = preg_replace( '/([^\x20-\x7e])\s+([^\x20-\x7e])/', '\\1\\2', $text );
    return $text;
  }

  /**
   * get last log.
   *
   * @return string last log
   */
  public function getLastLog()
  {
      return $this->lastlog;
  }

  /**
   * abstract function to open file resource.
   *
   * @acccess protected
   *
   * @param string $file_path file path
   */
  public function _open_file($file_path)
  {
      // if not text file, override this function in file search plugins
    $this->handle = fopen($file_path, 'rb');
  }

  /**
   * abstract function to close file resource.
   *
   * @acccess protected
   */
  public function _close_file()
  {
      // if not text file, override this function in file search plugins
    fclose($this->handle);
  }

  /**
   * abstract function to check end of file resource.
   *
   * @acccess protected
   *
   * @return bool true if end of file
   */
  public function _is_eof()
  {
      // if not text file, override this function in file search plugins
    return feof($this->handle);
  }

  /**
   * abstract function to fetch data in file.
   *
   * @acccess protected
   *
   * @return string fetched data if false returned it was terminated
   */
  public function _fetch_data()
  {
      // override this function in file search plugins if needed
    return $this->is_xml ? $this->_fetch_from_xml() : $this->_fetch_from_text();
  }

  /**
   * fetch from text.
   *
   * @return string fetched data if false returned it was terminated
   */
  public function _fetch_from_text()
  {
      return fgets($this->handle, 8192);
  }

  /**
   * fetch from xml.
   *
   * @return string fetched data if false returned it was terminated
   */
  public function _fetch_from_xml()
  {
      return fgetss($this->handle, 8192);
  }
}
