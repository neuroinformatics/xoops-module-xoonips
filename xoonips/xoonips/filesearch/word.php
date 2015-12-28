<?php
// $Revision: 1.4.2.1.2.5 $
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
class XooNIpsFileSearchPluginWORD extends XooNIpsFileSearchPlugin {

  /**
   * temporary file path for wv output data
   * @access private
   * @var string temporary file path
   */
  var $tmpfile = '';

  /**
   * flag to use antiword for file reader
   * @access private
   * @var bool true if use antiword
   */
  var $use_antiword = false;

  /**
   * environemnt variable for antword 'ANTIWORDHOME'
   * @access private
   * @var string
   */
  var $antiwordhome = '/usr/local/antiword';

  /**
   * constractor
   */
  function XooNIpsFileSearchPluginWORD() {
    parent::XooNIpsFileSearchPlugin();
    $this->is_xml = false;
    $this->is_utf8 = true;
    // for antiword
    // $this->use_antiword = true;
    // $this->antiwordhome = '/foo/bar';
  }

  /**
   * open file resource
   *
   * @acccess protected
   * @param string $filename file name
   */
  function _open_file( $filename ) {
    if ( $this->use_antiword ) {
      // for antiword
      putenv( 'ANTIWORDHOME='.$this->antiwordhome );
      $cmd = sprintf( 'antiword -t -m UTF-8.txt %s', $filename );
      $this->handle = @popen( $cmd, 'rb' );
    } else {
      // for wv
      $dirutil =& xoonips_getutility( 'directory' );
      $this->tmpfile = $dirutil->tempnam( $dirutil->get_tempdir(), 'XooNIpsFileSearchPluginWord' );
      $cmd = sprintf( 'wvText %s %s', escapeshellarg( $filename ), escapeshellarg( $this->tmpfile ) );
      // set LANG to UTF-8 for wvText(elinks)
      $lang = getenv( 'LANG' );
      putenv( 'LANG=en_US.UTF-8' );
      // execute wvText command
      @system( $cmd );
      // restore original lang
      putenv( 'LANG='.( ( $lang === false ) ? '' : $lang ) );
      $this->handle = @fopen( $this->tmpfile, 'rb' );
    }
  }

  /**
   * close file resource
   *
   * @acccess protected
   */
  function _close_file() {
    if ( $this->use_antiword ) {
      // for antiword
      @pclose( $this->handle );
      putenv( 'ANTWORDHOME=' );
    } else {
      // for wv
      parent::_close_file();
      @unlink( $this->tmpfile );
      $this->tmpfile = '';
    }
  }
}

?>
