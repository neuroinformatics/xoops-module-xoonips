<?php
// $Revision: 1.1.4.1.2.5 $
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

// xoonips user list block
function b_xoonips_userlist_show() {
  global $xoopsDB;

  $uid = is_object( $xoopsUser ) ? $xoopsUser->getVar( 'uid', 'n' ) : UID_GUEST;

  // hide block if user is invalid xoonips user
  $xsession_handler =& xoonips_getormhandler( 'xoonips', 'session' );
  if ( ! $xsession_handler->validateUser( $uid, false ) ) {
    return false;
  }

  $tables['users'] = $xoopsDB->prefix( 'users' );
  $tables['xusers'] = $xoopsDB->prefix( 'xoonips_users' );
  $user_handler =& xoonips_getormhandler( 'xoonips', 'xoops_users' );
  $positions_handler =& xoonips_getormhandler( 'xoonips', 'positions' );

  // get visible positions
  $criteria = new Criteria( 'posi_order', 0, '>' );
  $criteria->setSort( 'posi_order' );
  $criteria->setOrder( 'ASC' );
  $position_objs =& $positions_handler->getObjects( $criteria );
  $positions = array();
  foreach ( $position_objs as $position_obj ) {
    $posi_id = $position_obj->getVar( 'posi_id', 'n' );
    $posi_title = $position_obj->getVar( 'posi_title', 's' );
    // get visible users, who set position to $posi_id
    $join_criteria = new XooNIpsJoinCriteria( 'xoonips_users', 'uid', 'uid' );
    $criteria = new CriteriaCompo();
    $criteria->add( new Criteria( $tables['users'].'.level', '0', '>' ) );
    $criteria->add( new Criteria( $tables['xusers'].'.activate', '1' ) );
    $criteria->add( new Criteria( $tables['xusers'].'.posi', $posi_id ) );
    $sort = array(
      $tables['users'].'.uid',
      $tables['xusers'].'.user_order',
    );
    $criteria->setSort( $sort );
    $criteria->setOrder( 'ASC' );
    $fields = array(
      $tables['users'].'.uid',
      $tables['users'].'.uname',
      $tables['users'].'.name',
    );
    $user_objs =& $user_handler->getObjects( $criteria, false, implode( ',', $fields ), false, $join_criteria );
    if ( ! empty( $user_objs ) ) {
      $position['title'] = $posi_title;
      $position['users'] = array();
      foreach ( $user_objs as $user_obj ) {
        $position['users'][] = array(
          'uid' => $user_obj->getVar( 'uid', 'e' ),
          'name' => $user_obj->getVar( 'name', 's' ),
          'uname' => $user_obj->getVar( 'uname', 's' ),
        );
      }
      $positions[] = $position;
    }
  }
  if ( empty( $positions ) ) {
    return false;
    // visible users not found
  }

  // assign block template variables
  $block = array();
  $block['positions'] = $positions;

  return $block;
}

?>
