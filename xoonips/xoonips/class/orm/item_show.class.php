<?php
// $Revision: 1.1.2.7 $
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

/**
 * @brief Data object of XooNIps Item Show
 *
 * @li getVar('item_show_id') :
 * @li getVar('item_id') : item ID
 * @li getVar('uid') : user ID
 */
class XooNIpsOrmItemShow extends XooNIpsTableObject {
  function XooNIpsOrmItemShow() {
    parent::XooNIpsTableObject();
    $this->initVar( 'item_show_id', XOBJ_DTYPE_INT, null, false, null );
    $this->initVar( 'item_id', XOBJ_DTYPE_INT, null, true, null );
    $this->initVar( 'uid', XOBJ_DTYPE_INT, null, true, null );
  }
}

/**
 * @brief Handler object of XooNIps Item Status
 *
 */
class XooNIpsOrmItemShowHandler extends XooNIpsTableObjectHandler {
  function XooNIpsOrmItemShowHandler( &$db ) {
    parent::XooNIpsTableObjectHandler( $db );
    $this->__initHandler( 'XooNIpsOrmItemShow', 'xoonips_item_show', 'item_show_id', true );
  }

  /**
   * count user defined publications
   *
   * @access public
   * @param int $uid user id
   * @return array publication count by item type id
   */
  function getCountPublications( $uid ) {
    $join = new XooNIpsJoinCriteria( 'xoonips_item_basic', 'item_id', 'item_id', 'INNER', 'ib' );
    $criteria = new Criteria( 'uid', $uid, '=', $this->db->prefix( $this->__table_name ) );
    $criteria->setGroupby( 'ib.item_type_id' );
    $res = $this->open( $criteria, 'item_type_id, COUNT(DISTINCT ib.item_id)', false, $join );
    $nums = array();
    while ( $obj =& $this->getNext( $res ) ) {
      $item_type_id = $obj->getExtraVar( 'item_type_id' );
      $count = $obj->getExtraVar( 'COUNT(DISTINCT ib.item_id)' );
      $nums[$item_type_id] = $count;
    }
    $this->close( $res );
    return $nums;
  }
}
?>
