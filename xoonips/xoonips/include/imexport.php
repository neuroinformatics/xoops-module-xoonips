<?php
// $Revision: 1.33.2.1.2.10 $
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
include_once 'lib.php';

/**
 * Return xml with Basic information.
 *
 * To write xml of item to specified folder.
 *
 * In the case of writing attachment file,  at first create a files/ folder
 * in the specified folder.
 * next it write attachment file to the folder.
 *
 * path of xml file is "${export_path}/${item_id}.xml"
 * path of attchment file is "${export_path}/files/${file_id}"
 *
 * If unknown item_id or error in database exists, return false.
 *
 * @param export_path folder that export file is written to.
 * @param export_xml name of export file(xml file)
 * @param item_id item id that is exported
 * @param attachment true if attachment files are exported, else false.
 * @return returns array(
 *     'path' => folder that export files are written to.
 *     'xml' => file path of xml(relative path of 'path')
 *     'attachments' => arary( file path of attachment 1, file path of
 *                      attachment2, .... ) ) (relative path of 'path')
 *     return false if failed.
 */
function xnpExportItem( $export_path, $item_id, $attachment = false, $is_absolute, $base_index_id = false ) {
  $filename = "${export_path}/${item_id}.xml";
  $tmpfile = tempnam( '/tmp', 'XooNIps' );
  
  $fhdl = fopen( $tmpfile, 'w' );
  if ( ! $fhdl ) {
    xoonips_error( "can't open file '${tmpfile}' for write." );
    return false;
  }
  
  $xnpsid = $_SESSION['XNPSID'];
  $item = array();
  $itemtypes = array();
  
  $res = xnp_get_item( $xnpsid, $item_id, $item );
  if ( $res != RES_OK ) {
    return false;
  }
  
  $res = xnp_get_item_types( $itemtypes );
  if ( $res != RES_OK ) {
    return false;
  } else {
    foreach ( $itemtypes as $i ) {
      if ( $i['item_type_id'] == $item['item_type_id'] ) {
        $itemtype = $i;
        break;
      }
    }
  }
  if ( ! isset( $itemtype ) ) {
    return false;
  }
  
  include_once XOOPS_ROOT_PATH.'/modules/'.$itemtype['viewphp'];
  
  if ( ! fwrite( $fhdl, "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<item version=\"1.00\">\n" ) ) {
    return false;
  }
  
  // item type module doesn't support export
  $func = "${itemtype['name']}ExportItem";
  if ( ! function_exists( $func ) ) {
    return false;
  }
  
  if ( ! xnpExportBasic( $fhdl, $item_id, $is_absolute, $base_index_id ) ) {
    return false;
  }
  
  $attachment_files = $func( $export_path, $fhdl, $item_id, $attachment );
  if ( ! $attachment_files ) {
    return false;
  }
  
  if ( ! fwrite( $fhdl, "</item>\n" ) ) {
    return false;
  }
  
  fclose( $fhdl );
  
  // convert encoding for client environment
  $fp_r = fopen( $tmpfile, 'r' );
  $fp_w = fopen( $filename, 'w' );
  if ( ! $fp_r || ! $fp_w ) {
    if ( ! $fp_r ) {
      xoonips_error( "can't open file '${tmpfile}' for read." );
    }
    if ( ! $fp_w ) {
      xoonips_error( "can't open file '${filename}' for write." );
    }
    unlink( $tmpfile );
    unlink( $filename );
    if ( $fp_r ) {
      fclose( $fp_r );
    }
    if ( $fp_w ) {
      fclose( $fp_w );
    }
    return false;
  }
  $unicode =& xoonips_getutility( 'unicode' );
  while ( ! feof( $fp_r ) ) {
    fputs( $fp_w, $unicode->encode_utf8( fgets( $fp_r, 131072 ), xoonips_get_server_charset() ) );
  }
  fclose( $fp_r );
  fclose( $fp_w );
  unlink( $tmpfile );
  

  return array( 'path' => $export_path, 'xml' => "${item_id}.xml", 'attachments' => $attachment_files['attachments'] );
}

/**
 * export index
 *
 * @param fhdl file handle that indexes are exported to.
 * @param index_id id of index to display
 * @param recurse true:export recursively index that hangs under index_id.
 * @return true:success, false:failure
 */
function xnpExportIndex( $fhdl, $index_id, $recurse ) {
  if ( ! $fhdl ) {
    return false;
  }
  $myts =& MyTextSanitizer::getInstance();
  
  $xnpsid = $_SESSION['XNPSID'];
  $index = array();
  $child = array();
  
  $res = xnp_get_index( $xnpsid, $index_id, $index );
  if ( $res != RES_OK ) {
    return false;
  }
  
  $res = xnp_get_indexes( $xnpsid, $index_id, array(), $child );
  if ( $res != RES_OK ) {
    return false;
  }
  
  if ( ! fwrite( $fhdl, "<index parent_id=\"${index['parent_index_id']}\" id=\"${index_id}\">\n".'<title>'.$myts->htmlSpecialChars( $index['titles'][DEFAULT_INDEX_TITLE_OFFSET] )."</title>\n"."</index>\n" ) ) {
    return false;
  }
  
  $xml = array();
  if ( $recurse ) {
    $res = xnp_get_indexes( $xnpsid, $index_id, array( 'orders' => array(
      array(
        'sort_number',
        0,
      ),
    ) ), $child );
    if ( $res == RES_OK ) {
      foreach ( $child as $i ) {
        if ( ! xnpExportIndex( $fhdl, $i['item_id'], $recurse ) ) {
          return false;
        }
      }
    }
  }
  return true;
}

/**
 *
 * exporting attachment files related to item.
 *
 * @param string $export_path storing directory name
 * @param resource $fhdl file handle that items are exported to.
 * @param int $item_id id of item with attachment files to export.
 * @return array( 'path' => $export_path,
 *                   'attachments' => array( file path of attachment1, file path of attachment2, ... ) )
 *            (relative path of $export_filepath)
 *         returns false if it failed
 */
function xnpExportFile( $export_path, $fhdl, $item_id ) {
  $file = xnpGetFileInfo( 't_file.file_id, t_file_type.name, t_file.original_file_name, t_file.file_size, t_file.mime_type, t_file.thumbnail_file, t_file.caption', "item_id = ${item_id} and is_deleted=0", $item_id );
  
  if ( ! $fhdl ) {
    return false;
  }
  
  // create files directory under $export_path.
  $dir = $export_path.'/files';
  if ( ! file_exists( $dir ) ) {
    if ( ! mkdir( $dir ) ) {
      xoonips_error( "can't make directory '${dir}'" );
      return false;
    }
  }
  
  // for absolete path of attachment file
  $files = array();
  foreach ( $file as $f ) {
    $file = array();
    list( $file['file_id'], $file['file_type_name'], $file['original_file_name'], $file['file_size'], $file['mime_type'], $file['thumbnail_file'], $file['caption'] ) = $f;
    
    // copy atatchment file $file['file_id'] to $dir and renamed to original file name
    // output <file> to file handle $fhdl
    $hdl = fopen( xnpGetUploadFilePath( $file['file_id'] ), 'rb' );
    if ( file_exists( xnpGetUploadFilePath( $file['file_id'] ) ) ) {
      if ( ! copy( xnpGetUploadFilePath( $file['file_id'] ), $dir.'/'.$file['file_id'] ) ) {
        xoonips_error( 'can\'t write a file \''.$dir.'/'.$file['file_id']."' of the item(ID=${item_id})" );
        return false;
      }
      if ( ! fwrite( $fhdl, '<file'." item_id=\"${item_id}\""." file_type_name=\"${file['file_type_name']}\""." original_file_name=\"${file['original_file_name']}\""." file_name=\"files/${file['file_id']}\""." file_size=\"${file['file_size']}\""." mime_type=\"${file['mime_type']}\"".">\n".( isset( $file['thumbnail_file'] ) ? '<thumbnail>'.base64_encode( $file['thumbnail_file'] )."</thumbnail>\n" : '' ).'<caption>'.$file['caption']."</caption>\n"."</file>\n" ) ) {
        fclose( $hdl );
        xoonips_error( "can't export <file> of the item(ID=${item_id})" );
        return false;
      }
      $files[] = "files/${file['file_id']}";
    }
  }
  return true;
}

/**
 *
 * export Basic information of item
 *
 * @param fhdl file handle that items are exported to.
 * @param item_id id of the item to change into XML.
 * @return true:success, false:failure
 */
function xnpExportBasic( $fhdl, $item_id, $is_absolute, $base_index_id = false ) {
  if ( ! $fhdl ) {
    return false;
  }
  
  $xnpsid = $_SESSION['XNPSID'];
  $item = array();
  $account = array();
  
  $res = xnp_get_item( $xnpsid, $item_id, $item );
  if ( $res != RES_OK ) {
    return false;
  }
  return xnpBasicInformation2XML( $fhdl, $item, $is_absolute, $base_index_id );
}

/**
 *
 * export ChangeLog of item.
 *
 * @param fhdl file handle that changelogs are exported to.
 * @param item id of the item to export.
 * @return true:success, false:failure
 */
function xnpExportChangeLog( $fhdl, $item_id ) {
  if ( ! $fhdl ) {
    return false;
  }
  $myts =& MyTextSanitizer::getInstance();
  
  $xnpsid = $_SESSION['XNPSID'];
  $xml = array();
  $logs = array();
  $res = xnp_get_change_logs( $xnpsid, $item_id, $logs );
  if ( $res != RES_OK ) {
    return false;
  }
  if ( ! fwrite( $fhdl, "<changelogs>\n" ) ) {
    return false;
  }
  foreach ( $logs as $l ) {
    $log_date = gmdate( 'Y-m-d\\TH:i:s\\Z', $l['log_date'] );
    if ( ! fwrite( $fhdl, "<changelog date='${log_date}'>".$myts->htmlSpecialChars( $l['log'] )."</changelog>\n" ) ) {
      return false;
    }
  }
  if ( ! fwrite( $fhdl, "</changelogs>\n" ) ) {
    return false;
  }
  
  return true;
}

/**
 *
 * export 'Related to' information of an item.
 *
 * @param parent_id id of the item to export.
 * @return generated XML or NULL
 */
function xnpExportRelatedTo( $parent_id ) {
  $xnpsid = $_SESSION['XNPSID'];
  $xml = array();
  $item_id = array();
  // Export item: only accesible item
  // ->export link information getted xnp_get_related_to.
  $res = xnp_get_related_to( $xnpsid, $parent_id, $item_id );
  if ( $res != RES_OK ) {
    return NULL;
  }
  $xml = '';
  foreach ( $item_id as $i ) {
    $xml = $xml."<related_to item_id='${i}'/>\n";
  }
  return $xml;
}

/******************************************************************/
// Import
$parser_hash = array();

/**
 * @param str XML characters (UTF-8)
 * @param parent_id index_id of import place
 * @param array( 'pseudo ID' => 'actual ID', ... ) effected index ids
 * @param errmsg reference recieve error message
 * @return bool false if falure. refer $errmsg.
 */
function xnpImportIndex( $str, $parent_index_id, &$id_table, &$errmsg ) {
  global $parser_hash;
  $textutil =& xoonips_getutility( 'text' );
  
  $uid = $_SESSION['xoopsUserId'];
  $xnpsid = $_SESSION['XNPSID'];
  $item = array();
  
  $str = mb_decode_numericentity( $str, xoonips_get_conversion_map(), 'UTF-8' );
  $parser = xml_parser_create( 'UTF-8' );
  if ( ! $parser ) {
    $errmsg .= "can't create parser\n";
    return false;
  }
  $parser_hash[$parser] = array(
    'tagstack' => array(),
    'id_table' => $id_table,
    'errmsg' => $errmsg,
    'handler' => array(),
    'handlerstack' => array(),
    'indexes' => array(),
    'parent_index_id' => $parent_index_id,
  );
  
  $parser_hash[$parser]['handler']['/INDEXES'] = array(
    '_xoonips_import_indexStartElement',
    '_xoonips_import_indexEndElement',
    '_xoonips_import_indexCharacterData',
  );
  
  xml_set_element_handler( $parser, '_xoonips_import_startElement', '_xoonips_import_endElement' );
  xml_set_character_data_handler( $parser, '_xoonips_import_CharacterData' );
  
  if ( ! xml_parse( $parser, $str, true ) ) {
    $lines = preg_split( "/[\r\n]+/", $str );
    die( xml_error_string( xml_get_error_code( $parser ) ).' at column '.xml_get_current_column_number( $parser ).' of line '.$textutil->html_special_chars( $lines[xml_get_current_line_number( $parser ) - 1] ) );
  }
  
  xml_parser_free( $parser );
  
  $id_table = $parser_hash[$parser]['id_table'];
  $parser_hash[$parser] = NULL;
  return true;
}

function xnpImportIndexCheck( $str, &$indexes ) {
  global $parser_hash;
  $textutil =& xoonips_getutility( 'text' );
  
  $xnpsid = $_SESSION['XNPSID'];
  $item = array();
  
  $str = mb_decode_numericentity( $str, xoonips_get_conversion_map(), 'UTF-8' );
  $parser = xml_parser_create( 'UTF-8' );
  if ( ! $parser ) {
    return NULL;
  }
  $parser_hash[$parser] = array(
    'tagstack' => array(),
    'indexes' => array(),
    'handler' => array(),
    'handlerstack' => array(),
  );
  // XooNIps processes following tags.
  $parser_hash[$parser]['handler']['/INDEXES'] = array(
    '_xoonips_import_indexcheckStartElement',
    '_xoonips_import_indexcheckEndElement',
    '_xoonips_import_indexcheckCharacterData',
  );
  
  xml_set_element_handler( $parser, '_xoonips_import_startElement', '_xoonips_import_endElement' );
  xml_set_character_data_handler( $parser, '_xoonips_import_CharacterData' );
  
  if ( ! xml_parse( $parser, $str, true ) ) {
    $lines = preg_split( "/[\r\n]+/", $str );
    die( xml_error_string( xml_get_error_code( $parser ) ).' at column '.xml_get_current_column_number( $parser ).' of line '.$textutil->html_special_chars( $lines[xml_get_current_line_number( $parser ) - 1] ) );
  }
  
  xml_parser_free( $parser );
  
  $indexes = $parser_hash[$parser]['indexes'];
  $parser_hash[$parser] = NULL;
  return true;
}

/**
 * private functions
 * these functions will be called from this file
 */

// parser handlers for index
function _xoonips_import_startElement( $parser, $name, $attribs ) {
  global $currentTag, $currentAttribs;
  global $parser_hash;
  $currentTag = $name;
  
  // return if item_id is not in accept_id
  if ( array_key_exists( 'basic', $parser_hash[$parser] ) && array_key_exists( 'ID', $parser_hash[$parser]['basic'] ) && ! in_array( $parser_hash[$parser]['basic']['ID'], $parser_hash[$parser]['accept_id'] ) ) {
    return;
  }
  
  $currentAttribs = $attribs;
  
  array_push( $parser_hash[$parser]['tagstack'], $name );
  
  $tags = '/'.implode( '/', $parser_hash[$parser]['tagstack'] );
  if ( array_key_exists( $tags, $parser_hash[$parser]['handler'] ) ) {
    array_push( $parser_hash[$parser]['handlerstack'], $parser_hash[$parser]['handler'][$tags] );
  }
  
  if ( count( $parser_hash[$parser]['handlerstack'] ) > 0 ) {
    $handler = end( $parser_hash[$parser]['handlerstack'] );
    if ( function_exists( $handler[0] ) ) {
      $handler[0]( $parser, $name, $attribs, $parser_hash[$parser] );
    }
    return;
  }
}

function _xoonips_import_endElement( $parser, $name ) {
  global $currentTag;
  global $parser_hash;
  
  // return if item_id is not in accept_id
  if ( array_key_exists( 'basic', $parser_hash[$parser] ) && array_key_exists( 'ID', $parser_hash[$parser]['basic'] ) && ! in_array( $parser_hash[$parser]['basic']['ID'], $parser_hash[$parser]['accept_id'] ) ) {
    return;
  }
  
  if ( count( $parser_hash[$parser]['handlerstack'] ) > 0 ) {
    $handler = end( $parser_hash[$parser]['handlerstack'] );
    if ( function_exists( $handler[1] ) ) {
      $handler[1]( $parser, $name, $parser_hash[$parser] );
    }
    // TODO: compare with first value in 'handler' key.
    if ( array_key_exists( '/'.implode( '/', $parser_hash[$parser]['tagstack'] ), $parser_hash[$parser]['handler'] ) ) {
      array_pop( $parser_hash[$parser]['handlerstack'] );
    }
    array_pop( $parser_hash[$parser]['tagstack'] );
    return;
  }
  
  $currentTag = '';
  $currentAttribs = '';
  
  array_pop( $parser_hash[$parser]['tagstack'] );
}

function _xoonips_import_CharacterData( $parser, $data ) {
  global $currentTag, $currentAttribs;
  global $parser_hash;
  
  // return if item_id is not in accept_id
  if ( array_key_exists( 'basic', $parser_hash[$parser] ) && array_key_exists( 'ID', $parser_hash[$parser]['basic'] ) && ! in_array( $parser_hash[$parser]['basic']['ID'], $parser_hash[$parser]['accept_id'] ) ) {
    return;
  }
  
  $tags = '/'.implode( '/', $parser_hash[$parser]['tagstack'] );
  
  if ( count( $parser_hash[$parser]['handlerstack'] ) > 0 ) {
    $handler = end( $parser_hash[$parser]['handlerstack'] );
    if ( function_exists( $handler[2] ) ) {
      $handler[2]( $parser, $data, $parser_hash[$parser] );
    }
    return;
  }
}

// parser handlers for index
function _xoonips_import_indexStartElement( $parser, $name, $attribs, &$parser_hash ) {
  $xnpsid = $_SESSION['XNPSID'];
  
  $tags = '/'.implode( '/', $parser_hash['tagstack'] );
  
  switch ( $tags ) {
  case '/INDEXES/INDEX/TITLE':
    // prepare a new buffer of index title to read
    $parser_hash['indexes'][count( $parser_hash['indexes'] ) - 1]['titles'][] = '';
    break;
  case '/INDEXES/INDEX':
    $ar = array(
      'parent_id' => $attribs['PARENT_ID'],
      'index_id' => $attribs['ID'],
      'titles' => array(),
    );
    array_push( $parser_hash['indexes'], $ar );
    break;
  case '/INDEXES':
    break;
  }
}

function _xoonips_import_indexEndElement( $parser, $name, &$parser_hash ) {
  $xnpsid = $_SESSION['XNPSID'];
  
  switch ( $name ) {
  case 'INDEXES':
    $indexes = $parser_hash['indexes'];
    
    // To construct tree structure from given indexes by $indexes
    // associative array (child ID -> parent ID)
    $c2p = array();
    // associative array (parent ID -> array of child ID)
    $p2c = array();
    // $index_by_id[ index_id ] => index array;
    $index_by_id = array();
    foreach ( $indexes as $i ) {
      $c2p[$i['index_id']] = $i['parent_id'];
      if ( ! isset( $p2c[$i['parent_id']] ) ) {
        $p2c[$i['parent_id']] = array();
      }
      $p2c[$i['parent_id']][] = $i['index_id'];
      $index_by_id[$i['index_id']] = $i;
    }
    
    // Index id of root of each index trees
    $root_ids = array();
    while ( list( $child, $parent ) = each( $c2p ) ) {
      while ( array_key_exists( $parent, $c2p ) ) {
        // track back to root
        unset( $c2p[$child] );
        $child = $parent;
        $parent = $c2p[$parent];
      }
      
      $parent = $child;
      
      $root_ids[] = $parent;
      unset( $c2p[$parent] );
      
      // remove all childs of the root($parent)
      if ( isset( $p2c[$parent] ) ) {
        $stack = $p2c[$parent];
        while ( count( $stack ) > 0 ) {
          $id = array_pop( $stack );
          unset( $c2p[$id] );
          if ( isset( $p2c[$id] ) ) {
            $stack = array_merge( $stack, $p2c[$id] );
          }
        }
      }
      reset( $c2p );
    }
    // structured index tree
    $tree = array();
    foreach ( $root_ids as $root_id ) {
      $tree[] = _xoonips_import_constructIndexTree( $p2c, $index_by_id, $root_id );
    }
    
    _xoonips_import_index( $parser_hash['parent_index_id'], $tree, $parser_hash['id_table'] );
    break;
  }
}

function _xoonips_import_indexCharacterData( $parser, $data, &$parser_hash ) {
  $tags = '/'.implode( '/', $parser_hash['tagstack'] );
  
  switch ( $tags ) {
  case '/INDEXES/INDEX/TITLE':
    $index = array_pop( $parser_hash['indexes'] );
    $index['titles'][count( $index['titles'] ) - 1] .= $data;
    array_push( $parser_hash['indexes'], $index );
    break;
  }
}

// parser handlers for index check
function _xoonips_import_indexcheckStartElement( $parser, $name, $attribs, &$parser_hash ) {
  $tags = '/'.implode( '/', $parser_hash['tagstack'] );
  
  switch ( $tags ) {
  case '/INDEXES':
    $parser_hash['indexlinks'] = array();
    break;
  case '/INDEXES/INDEX':
    $ar = array(
      'parent_id' => $attribs['PARENT_ID'],
      'index_id' => $attribs['ID'],
      'titles' => array(),
    );
    array_push( $parser_hash['indexes'], $ar );
    break;
  case '/INDEXES/INDEX/TITLE':
    $parser_hash['indexes'][count( $parser_hash['indexes'] ) - 1]['titles'][] = '';
    break;
  }
}

function _xoonips_import_indexcheckEndElement( $parser, $name, &$parser_hash ) {
  $tags = '/'.implode( '/', $parser_hash['tagstack'] );
  
  switch ( $tags ) {
  case '/INDEXES/INDEX/TITLE':
    $title =& $parser_hash['indexes'][count( $parser_hash['indexes'] ) - 1]['titles'][count( $parser_hash['indexes'][count( $parser_hash['indexes'] ) - 1]['titles'] ) - 1];
    $unicode =& xoonips_getutility( 'unicode' );
    $title = $unicode->decode_utf8( $title, xoonips_get_server_charset(), 'h' );
    break;
  }
}

function _xoonips_import_indexcheckCharacterData( $parser, $data, &$parser_hash ) {
  $tags = '/'.implode( '/', $parser_hash['tagstack'] );
  
  switch ( $tags ) {
  case '/INDEXES/INDEX/TITLE':
    $parser_hash['indexes'][count( $parser_hash['indexes'] ) - 1]['titles'][count( $parser_hash['indexes'][count( $parser_hash['indexes'] ) - 1]['titles'] ) - 1] .= $data;
    break;
  }
}

/**
 * Importing indexes to a index that is specified by $parent_index_id.
 * Associations of pseudo ID and Real index ID are sotred to $id_table.
 *
 * @param parent_index_id index_id that indexes is imported to.
 * @param $indexes array of index information to be imported.
 * $indexes = array(
 *                   array( 'titles' => array( TITLE1, TITLE2, ... )
 *                          'parent_id' => pseudo id of parent index
 *                          'item_id' => pseudo id of own index
 *                          'child' => array( [0] => array( 'titles' => ..., 'parent_id' => ..., 'child' => ....)
 *                                            [1] => array( same above ),
 *                                            ....
 *                           )
 *                         ),
 *                   array( 'titles' => array( TITLE1, TITLE2, ... )
 *                          same above ... ),
 *                   ...
 *                   );
 * @param id_table reference of associative array for output( [pseudo id] => [real index id] )
 * @return no return value.
 */
function _xoonips_import_index( $parent_index_id, &$indexes, &$id_table ) {
  $xnpsid = $_SESSION['XNPSID'];
  $lengths = xnpGetColumnLengths( 'xoonips_item_title' );
  $unicode =& xoonips_getutility( 'unicode' );
  foreach ( $indexes as $index ) {
    foreach ( $index['titles'] as $k => $title ) {
      list( $index['titles'][$k], $dummy ) = xnpTrimString( $unicode->decode_utf8( $title, xoonips_get_server_charset(), 'h' ), $lengths['title'], 'UTF-8' );
    }
    $child = array();
    // numbers of same index name
    $cnt = 0;
    $index_id = 0;
    if ( xnp_get_indexes( $xnpsid, $parent_index_id, array(), $child ) == RES_OK ) {
      foreach ( $child as $i ) {
        $diff = array_diff( $i['titles'], $index['titles'] );
        if ( empty( $diff ) ) {
          // true if $index have only same names of $i ( $i['titles'] == $index['titles'] )
          $cnt++;
          $index_id = $i['item_id'];
        }
      }
    }
    if ( $cnt == 1 ) {
      $id_table[$index['index_id']] = $index_id;
    } else {
      $insert_index = array();
      $insert_index['titles'] = $index['titles'];
      $insert_index['parent_index_id'] = $parent_index_id;
      $result = xnp_insert_index( $xnpsid, $insert_index, $index_id );
      if ( $result != RES_OK ) {
        break;
      }
      $id_table[$index['index_id']] = $index_id;
      
      // record event log
      $mydirname = basename( dirname( __DIR__ ) );
      $event_handler =& xoonips_getormhandler( 'xoonips', 'event_log' );
      $event_handler->recordInsertIndexEvent( $index_id );
    }
    if ( array_key_exists( 'child', $index ) ) {
      _xoonips_import_index( $index_id, $index['child'], $id_table );
    }
  }
}

/**
 * To construct tree structure of the index.
 * The root of the tree structue is specified by $root_id.
 * Return value of this function has information of this index and the childs of this index.
 * This function is called recursive by own.
 *
 * @param p2c $p2c[ Parent ID of the index ] = array( ID of child index of the index[0], ...[1], ...[2], .... )
 * @param index_by_id assosiative array of index( index information that is associated by own index ID )
 *                    $index_by_id[ ID of the index 'A' ] = array( information of the index 'A' )
 * @param root_id Index ID of the root of the index tree structure that you want to get
 * @return assosiative array of index tree
 */
function _xoonips_import_constructIndexTree( $p2c, $index_by_id, $root_id ) {
  $ret = $index_by_id[$root_id];
  
  if ( isset( $p2c[$root_id] ) ) {
    // call _xoonips_import_constructIndexTree if index(id=$root_id) has
    // child indexes
    foreach ( $p2c[$root_id] as $child_id ) {
      $ret['child'][] = _xoonips_import_constructIndexTree( $p2c, $index_by_id, $child_id );
    }
  }
  
  return $ret;
}

?>
