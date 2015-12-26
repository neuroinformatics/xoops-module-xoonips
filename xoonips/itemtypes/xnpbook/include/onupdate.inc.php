<?php
// $Revision: 1.1.4.1.2.13 $
// ------------------------------------------------------------------------- //
//  XooNIps - Neuroinformatics Base Platform System                          //
//  Copyright (C) 2005-2013 RIKEN, Japan All rights reserved.                //
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

function xoops_module_update_xnpbook( $xoopsMod, $oldversion ) {

  global $xoopsDB;
  switch ( $oldversion ) {
    // remember that version is multiplied with 100 to get an integer
  case 100:
    // perform actions to upgrade from version 1.00
    //    global $xoopsDB;
    $sql = sprintf( 'ALTER TABLE %s ADD attachment_dl_limit int(1) unsigned default 0', $xoopsDB->prefix( 'xnpbook_item_detail' ) );
    $result = $xoopsDB->query( $sql );
    if ( ! $result ) {
      echo 'ERROR: '.$xoopsDB->error();
      return false;
    }
    return true;
  case 101:
  case 102:
    // correction different encoding data in DB caused by no-converting ISBN information from 'UTF-8' to specified encoding at registration.
    if ( _CHARSET != 'UTF-8' ) {
      $table1 = $xoopsDB->prefix( 'xnpbook_item_detail' );
      $table2 = $xoopsDB->prefix( 'xoonips_item_basic' );
      $items = array();
      $sql1 = "SELECT book_id, author, publisher FROM $table1";
      $result1 = $xoopsDB->query( $sql1 );
      if ( ! $result1 ) {
        echo 'ERROR: '.$xoopsDB->error();
        return false;
      }
      $i = 0;
      while ( list( $book_id, $author, $publisher ) = $xoopsDB->fetchRow( $result1 ) ) {
        $unicode =& xoonips_getutility( 'unicode' );
        if ( _CHARSET == 'EUC-JP' ) {
          $author = $unicode->encode_utf8( $author, xoonips_get_server_charset() );
          $publisher = $unicode->encode_utf8( $publisher, xoonips_get_server_charset() );
        }
        $author = $unicode->decode_utf8( $author, xoonips_get_server_charset(), 'h' );
        $author = addslashes( $author );
        $publisher = $unicode->decode_utf8( $publisher, xoonips_get_server_charset(), 'h' );
        $publisher = addslashes( $publisher );
        $sql2 = "SELECT title FROM $table2 where item_id=$book_id";
        $result2 = $xoopsDB->query( $sql2 );
        if ( ! $result2 ) {
          echo 'ERROR: '.$xoopsDB->error();
          return false;
        }
        list( $title ) = $xoopsDB->fetchRow( $result2 );
        if ( _CHARSET == 'EUC-JP' ) {
          $title = $unicode->encode_utf8( $title, xoonips_get_server_charset() );
        }
        $title = $unicode->decode_utf8( $title, xoonips_get_server_charset(), 'h' );
        $title = addslashes( $title );
        $sql3 = "UPDATE $table2 SET title='$title' where item_id=$book_id";
        $result3 = $xoopsDB->query( $sql3 );
        if ( ! $result3 ) {
          echo 'ERROR: '.$xoopsDB->error();
          return false;
        }
        $sql4 = "UPDATE $table1 SET author='$author', publisher='$publisher' where book_id=$book_id";
        $result4 = $xoopsDB->query( $sql4 );
        if ( ! $result4 ) {
          echo 'ERROR: '.$xoopsDB->error();
          return false;
        }
      }
    }
  case 110:
    // 110->111: author varchar -> text
    $result = $xoopsDB->query( 'ALTER TABLE '.$xoopsDB->prefix( 'xnpbook_item_detail' ).' CHANGE COLUMN author author TEXT NOT NULL' );
    if ( ! $result ) {
      echo 'ERROR: '.$xoopsDB->error();
      return false;
    }
  case 111:
  case 200:
  case 300:
  case 310:
    $sql = 'ALTER TABLE '.$xoopsDB->prefix( 'xnpbook_item_detail' ).' TYPE = innodb';
    $result = $xoopsDB->query( $sql );
    if ( ! $result ) {
      echo 'ERROR: line='.__LINE__." sql=$sql ".$xoopsDB->error();
    }
  case 311:
    $sql = 'ALTER TABLE '.$xoopsDB->prefix( 'xnpbook_item_detail' ).' ADD COLUMN attachment_dl_notify int(1) unsigned default 0 ';
    $result = $xoopsDB->query( $sql );
    if ( ! $result ) {
      echo 'ERROR: line='.__LINE__." sql=$sql ".$xoopsDB->error();
    }
  case 330:
  case 331:
  case 332:
  case 333:
  case 334:
  case 335:
  case 336:
  case 337:
  case 338:
  case 339:
    // support for ISBN13
    $sql = sprintf( 'ALTER TABLE `%s` MODIFY `isbn` char(13) default NULL', $xoopsDB->prefix( 'xnpbook_item_detail' ) );
    $result = $xoopsDB->query( $sql );
    if ( ! $result ) {
      echo 'ERROR: line='.__LINE__." sql=$sql ".$xoopsDB->error();
    }

    // support authors
    $key_name = 'book_id';
    $table_detail = 'xnpbook_item_detail';
    $table_author = 'xnpbook_author';

    $sql = 'CREATE TABLE '.$xoopsDB->prefix( $table_author ).' (';
    $sql .= '`book_author_id` int(10) unsigned NOT NULL auto_increment,';
    $sql .= '`book_id` int(10) unsigned NOT NULL,';
    $sql .= '`author` varchar(255) NOT NULL,';
    $sql .= '`author_order` int(10) unsigned NOT NULL default \'0\',';
    $sql .= '  PRIMARY KEY  (`book_author_id`)';
    $sql .= ') TYPE=InnoDB';
    $result = $xoopsDB->query( $sql );
    if ( ! $result ) {
      echo '&nbsp;&nbsp;'.$xoopsDB->error().'<br />';
      return false;
    }

    $result = $xoopsDB->query( 'select '.$key_name.',author from '.$xoopsDB->prefix( $table_detail ).' where author!=\'\'' );
    while ( list( $id, $author ) = $xoopsDB->fetchRow( $result ) ) {
      $author_array = array_map( 'trim', explode( ',', $author ) );
      $i = 0;
      foreach ( $author_array as $author ) {
        if ( empty( $author ) ) {
          continue;
        }
        $sql = 'insert into '.$xoopsDB->prefix( $table_author );
        $sql .= '('.$key_name.',author,author_order) values (';
        $sql .= $id.','.$xoopsDB->quoteString( $author ).','.$i.')';
        if ( $xoopsDB->queryF( $sql ) == false ) {
          echo '&nbsp;&nbsp;'.$xoopsDB->error().'<br />';
          return false;
        }
        $i++;
      }
    }
    $sql = 'ALTER TABLE '.$xoopsDB->prefix( $table_detail ).' DROP COLUMN `author`';
    $result = $xoopsDB->query( $sql );
    if ( ! $result ) {
      echo '&nbsp;&nbsp;'.$xoopsDB->error().'<br />';
      return false;
    }
  case 340:
  case 341:
  case 342:
  case 343:
  case 344:
  case 345:
  case 346:
    // drop year field from datail table
    $sql = 'ALTER TABLE `'.$xoopsDB->prefix('xnpbook_item_detail').'` DROP COLUMN `year`';
    $result = $xoopsDB->query($sql);
    if (!$result) {
      echo '&nbsp;&nbsp;'.$xoopsDB->error().'<br />';
      return false;
    }
  case 347:
  default:
  }
  return true;
}
?>
