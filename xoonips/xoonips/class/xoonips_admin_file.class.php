<?php
// $Revision: 1.1.4.6 $
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
if ( ! defined( 'XOOPS_ROOT_PATH' ) ) {
  exit();
}

require_once __DIR__.'/xoonips_file.class.php';

/**
 * XooNIps File Admin Handler Class
 */
class XooNIpsAdminFileHandler extends XooNIpsFileHandler {

  /**
   * array of file search plugins
   * @access private
   * @var array
   */
  var $fsearch_plugins;

  /**
   * file type id of preview
   * @access private
   * @var int
   */
  var $preview_ftid;

  function XooNIpsAdminFileHandler() {
    parent::XooNIpsFileHandler();
    $xc_handler =& xoonips_getormhandler( 'xoonips', 'config' );
    $this->_load_file_search_plugins();
    $xft_handler =& xoonips_getormhandler( 'xoonips', 'file_type' );
    // get preview file type id
    $criteria = new Criteria( 'name', 'preview' );
    $xft_objs =& $xft_handler->getObjects( $criteria );
    if ( count( $xft_objs ) != 1 ) {
      die( 'Fatal Error : Preview File Type not found' );
    }
    $this->preview_ftid = $xft_objs[0]->get( 'file_type_id' );
  }

  /**
   * get number of files
   *
   * @access public
   * @return int number of files
   */
  function getCountFiles() {
    $criteria = new CriteriaCompo( new Criteria( 'is_deleted', 0 ) );
    $criteria->add( new Criteria( 'ISNULL( item_id )', 0 ) );
    return $this->xf_handler->getCount( $criteria );
  }

  /**
   * get file id by count number
   *
   * @access public
   * @return int file id
   */
  function getFileIdByCount( $num ) {
    if ( $num < 1 ) {
      return false;
    }
    $criteria = new CriteriaCompo( new Criteria( 'is_deleted', 0 ) );
    $criteria->add( new Criteria( 'ISNULL( item_id )', 0 ) );
    $criteria->setSort( 'file_id' );
    $criteria->setOrder( 'ASC' );
    $criteria->setLimit( 1 );
    $criteria->setStart( $num - 1 );
    $xf_objs =& $this->xf_handler->getObjects( $criteria );
    if ( count( $xf_objs ) != 1 ) {
      return false;
    }
    return $xf_objs[0]->get( 'file_id' );
  }

  /**
   * get file search plugins
   *
   * @access public
   * @return array array of file search plugins
   */
  function getFileSearchPlugins() {
    return $this->fsearch_plugins;
  }

  /**
   * update file search text
   *
   * @access public
   * @param int $file_id file id
   * @param bool $force force update
   * @return bool false if failure
   */
  function updateFileSearchText( $file_id, $force ) {
    $xf_obj =& $this->xf_handler->get( $file_id );
    if ( ! $xf_obj ) {
      return false;
    }
    $is_deleted = $xf_obj->get( 'is_deleted' );
    $file_name = $xf_obj->get( 'original_file_name' );
    $file_mimetype = $xf_obj->get( 'mime_type' );
    $file_path = $this->getFilePath( $file_id );
    $fs_name = $this->_detect_file_search_plugin( $file_name, $file_mimetype );
    $fs_version = is_null( $fs_name ) ? null : $this->fsearch_plugins[$fs_name]['version'];
    if ( ! $force ) {
      // plugin version check
      $old_fs_name = $xf_obj->get( 'search_module_name' );
      $old_fs_version = $xf_obj->get( 'search_module_version' );
      if ( $fs_name == $old_fs_name ) {
        if ( is_null( $fs_name ) ) {
          // file search is not supported
          return true;
        }
        if ( floatVal( $fs_version ) <= floatVal( $old_fs_version ) ) {
          // no need to update search text
          return true;
        }
      }
    }

    // delete search text at once
    $xst_obj =& $this->xst_handler->get( $file_id );
    if ( is_object( $xst_obj ) ) {
      $this->xst_handler->delete( $xst_obj );
    }

    if ( $is_deleted || ! is_readable( $file_path ) || is_null( $fs_name ) ) {
      // clear search plugin informations
      $xf_obj->setDefault( 'search_module_name' );
      $xf_obj->setDefault( 'search_module_version' );
      return $this->xf_handler->insert( $xf_obj );
    }

    // fetch plain text string using file search plugins
    $classname = $this->fsearch_plugins[$fs_name]['class_name'];
    $indexer = new $classname();
    $indexer->open( $file_path );
    $text = $indexer->fetch();
    $indexer->close();

    // get windowed strings
    $searchutil =& xoonips_getutility( 'search' );
    $text = $searchutil->getFulltextData( $text );

    // open temporary file
    $dirutil =& xoonips_getutility( 'directory' );
    $tmpfile = $dirutil->get_template( 'XooNIpsSearch' );
    $fp = $dirutil->mkstemp( $tmpfile );
    if ( $fp === false ) {
      return false;
    }
    // register callback function to remove temporary file
    register_shutdown_function( array( $this, '_unlink_file_onshutdown' ), $tmpfile );

    // write first field 'file_id'
    fwrite( $fp, $file_id."\t" );
    // dump hashed search text to temporary file
    fwrite( $fp, $text );
    fclose( $fp );

    // insert search text
    global $xoopsDB;
    $esc_tmpfile = addslashes( $tmpfile );
    $xst_table = $xoopsDB->prefix( 'xoonips_search_text' );
    // - try to load data from directory of mysql server
    $sql = sprintf( 'LOAD DATA INFILE \'%s\' INTO TABLE %s ( file_id, search_text )', $esc_tmpfile, $xst_table );
    $result = $xoopsDB->queryF( $sql );
    if ( $result === false ) {
      // - try to load data from direcotry of mysql client
      $sql = sprintf( 'LOAD DATA LOCAL INFILE \'%s\' INTO TABLE %s ( file_id, search_text )', $esc_tmpfile, $xst_table );
      $result = $xoopsDB->queryF( $sql );
    }

    // update file search plugin information
    $xf_obj->set( 'search_module_name', $fs_name );
    $xf_obj->set( 'search_module_version', $fs_version );
    return $this->xf_handler->insert( $xf_obj );
  }

  /**
   * update extra information for maintainance
   *
   * @access public
   * @param int $file_id file id
   * @return bool false if failure
   */
  function updateFileInfo( $file_id ) {
    $fileutil =& xoonips_getutility( 'file' );
    $file_path = $this->getFilePath( $file_id );
    $xf_obj =& $this->xf_handler->get( $file_id );
    if ( ! file_exists( $file_path ) || ! is_object( $xf_obj ) ) {
      // file or object not found
      return false;
    }
    $file_name = $xf_obj->get( 'original_file_name' );
    $file_ftid = $xf_obj->get( 'file_type_id' );
    $mimetype = $fileutil->get_mimetype( $file_path, $file_name );
    $thumbnail = $file_ftid == $this->preview_ftid ? $fileutil->get_thumbnail( $file_path, $mimetype ) : null;
    $xf_obj->set( 'mime_type', $mimetype );
    if ( is_null( $thumbnail ) ) {
      $xf_obj->setDefault( 'thumbnail_file' );
    } else {
      $xf_obj->set( 'thumbnail_file', $thumbnail );
    }
    return $this->xf_handler->insert( $xf_obj );
  }

  /**
   * detect file search plugin
   * @access private
   * @return string file search plugin name
   */
  function _detect_file_search_plugin( $file_name, $file_mimetype ) {
    $file_pathinfo = pathinfo( $file_name );
    $file_ext = isset( $file_pathinfo['extension'] ) ? $file_pathinfo['extension'] : '';
    $fs_name = null;
    foreach ( $this->fsearch_plugins as $module ) {
      if ( in_array( $file_ext, $module['extensions'] ) && in_array( $file_mimetype, $module['mime_type'] ) ) {
        $fs_name = $module['name'];
        break;
      }
    }
    return $fs_name;
  }

  /**
   * load file search plugins
   * @access private
   * @return bool false if failure
   */
  function _load_file_search_plugins() {
    $this->fsearch_plugins = array();
    require_once __DIR__.'/base/filesearchplugin.class.php';
    $fs_path = dirname( __DIR__ ).'/filesearch';
    $plugins = array();
    if ( $dir = opendir( $fs_path ) ) {
      while ( $file = readdir( $dir ) ) {
        if ( ! preg_match( '/^def_.+\\.php$/', $file ) ) {
          continue;
        }
        // load module definition
        $module = array();
        include( $fs_path.'/'.$file );
        $fs_name = $module['name'];
        $plugins[$fs_name] = $module;
        // load indexer class
        require_once $fs_path.'/'.$module['php_file_name'];
      }
      closedir( $dir );
    }
    uasort( $plugins, array( &$this, '_sort_file_search_plugins' ) );
    $this->fsearch_plugins = $plugins;
    return true;
  }

  /**
   * call back function for file search plugin sorting
   * @access private
   * @param array $a
   * @param array $b
   * @return int
   */
  function _sort_file_search_plugins( $a, $b ) {
    return strcmp( $a['display_name'], $b['display_name'] );
  }

  /**
   * callback function on shutdown for temporary file removing
   * @access private
   * @param string $file_path file path
   */
  function _unlink_file_onshutdown( $file_path ) {
    @unlink( $file_path );
  }
}

?>
