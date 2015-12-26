<?php
// $Revision: 1.1.2.9 $
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

require_once dirname( __FILE__ ).'/abstract_ranking.class.php';

/**
 * @brief data object of ranking searched keyword
 *
 * @li getVar('keyword') :
 * @li getVar('count') :
 */
class XooNIpsOrmRankingSearchedKeyword extends XooNIpsTableObject {
  function XooNIpsOrmRankingSearchedKeyword() {
    parent::XooNIpsTableObject();
    $this->initVar( 'keyword', XOBJ_DTYPE_BINARY, '', false, 255 );
    $this->initVar( 'count', XOBJ_DTYPE_INT, 0, true );
  }
}

/**
 * @brief handler object of ranking searched keyword
 *
 */
class XooNIpsOrmRankingSearchedKeywordHandler extends XooNIpsOrmAbstractRankingHandler {
  function XooNIpsOrmRankingSearchedKeywordHandler( &$db ) {
    parent::XooNIpsTableObjectHandler( $db );
    $this->__initHandler( 'XooNIpsOrmRankingSearchedKeyword', 'xoonips_ranking_searched_keyword', 'keyword', false, true );
    $this->_set_columns( array( 'keyword', 'count' ) );
  }

  /**
   * insert/upldate/replace object
   *
   * @access public
   * @param object &$obj
   * @param bool $force force operation
   * @return bool false if failed
   */
  function insert( &$obj, $force = false ) {
    $keyword = $obj->get( 'keyword' );
    // trim keyword to 255 maximum chars
    if ( strlen( $keyword ) > 255 ) {
      $obj->set( 'keyword', substr( 0, 255, $keyword ) );
    }
    return parent::insert( $obj, $force );
  }

  /**
   * increment searched keyword counter for updating/rebuilding rankings
   *
   * @param string $keyword searched keyword
   * @param int $delta counter delta
   * @return bool FALSE if failed
   */
  function increment( $keyword, $delta ) {
    // chop illegal characters
    if ( _CHARSET != 'UTF-8' ) {
      $keyword = mb_convert_encoding( $keyword, 'UTF-8', _CHARSET );
    }
    $keyword = preg_replace( '/[\x00-\x1f]/', '', $keyword );
    if ( ! preg_match( '/^([\x00-\x7f]|[\xc0-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xf7][\x80-\xbf]{3})+$/', $keyword ) ) {
      return true;
    }
    if ( _CHARSET != 'UTF-8' ) {
      $keyword = mb_convert_encoding( $keyword, _CHARSET, 'UTF-8' );
    }
    if ( empty( $keyword ) ) {
      return true;
    }
    // trim keyword to 255 maximum chars
    if ( mb_strlen( $keyword, _CHARSET ) > 255 ) {
      $keyword = mb_substr( 0, 255, $keyword, _CHARSET );
    }
    $obj =& $this->get( $keyword );
    if ( is_object( $obj ) ) {
      $delta += $obj->get( 'count' );
    } else {
      $obj =& $this->create();
      $obj->set( 'keyword', $keyword );
    }
    $obj->set( 'count', $delta );
    // force insertion
    return $this->insert( $obj, true );
  }
}

?>
