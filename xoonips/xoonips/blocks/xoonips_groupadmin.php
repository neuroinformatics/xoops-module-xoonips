<?php
// $Revision: 1.1.4.1.2.11 $
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

// xoonips group admin menu block
function b_xoonips_group_show() {
  global $xoopsUser;

  // hide block if user is guest
  if ( ! is_object( $xoopsUser ) ) {
    return false;
  }

  $uid = $xoopsUser->getVar( 'uid', 'n' );

  // hide block if user is invalid xoonips user
  $xsession_handler =& xoonips_getormhandler( 'xoonips', 'session' );
  if ( ! $xsession_handler->validateUser( $uid, false ) ) {
    return false;
  }

  // get administrable group ids
  $xgroup_handler =& xoonips_gethandler( 'xoonips', 'group' );
  $admin_gids = $xgroup_handler->getGroupIds( $uid, true );
  if ( empty( $admin_gids ) ) {
    // user is not group admin
    return false;
  }

  // get index id of primary group
  $gid = $admin_gids[0];
  // primary gid
  $group_index_id = $xgroup_handler->getGroupRootIndexId( $gid );

  // count certification requested items
  $xil_handler =& xoonips_getormhandler( 'xoonips', 'index_item_link' );
  $join = new XooNIpsJoinCriteria( 'xoonips_index', 'index_id', 'index_id', 'INNER', 'x' );
  $criteria = new CriteriaCompo( new Criteria( 'certify_state', CERTIFY_REQUIRED ) );
  $criteria->add( new Criteria( 'open_level', OL_GROUP_ONLY, '=', 'x' ) );
  $criteria->add( new Criteria( 'gid', '('.implode(',', $admin_gids).')', 'IN', 'x' ) );
  $ci_count = $xil_handler->getCount( $criteria, $join );

  // assign block template variables
  $block = array();
  $block['lang_edit_group'] = _MB_XOONIPS_GROUP_EDIT_GROUP_MEMBERS;
  $block['lang_certify_group_items'] = _MB_XOONIPS_GROUP_CERTIFY_GROUP_ITEMS;
  $block['lang_certify_group_items_count'] = $ci_count;
  $block['lang_edit_group_index'] = _MB_XOONIPS_GROUP_EDIT_GROUP_INDEX;
  $block['xid'] = $group_index_id;

  return $block;
}

?>
