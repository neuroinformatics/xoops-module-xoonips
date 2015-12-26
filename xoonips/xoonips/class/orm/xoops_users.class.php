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

/**
 * @brief class of XooNIps User.
 */
class XooNIpsOrmXoopsUsers extends XooNIpsTableObject {
  function XooNIpsOrmXoopsUsers() {
    parent::XooNIpsTableObject();
    // from XooNIps_users table
    $this->initVar( 'uid', XOBJ_DTYPE_INT, 0, false );
    $this->initVar( 'name', XOBJ_DTYPE_TXTBOX, null, false, 60 );
    $this->initVar( 'uname', XOBJ_DTYPE_TXTBOX, null, true, 25 );
    $this->initVar( 'email', XOBJ_DTYPE_TXTBOX, null, true, 60 );
    $this->initVar( 'url', XOBJ_DTYPE_TXTBOX, null, false, 100 );
    $this->initVar( 'user_avatar', XOBJ_DTYPE_TXTBOX, null, false, 30 );
    $this->initVar( 'user_regdate', XOBJ_DTYPE_INT, null, false );
    $this->initVar( 'user_icq', XOBJ_DTYPE_TXTBOX, null, false, 15 );
    $this->initVar( 'user_from', XOBJ_DTYPE_TXTBOX, null, false, 100 );
    $this->initVar( 'user_sig', XOBJ_DTYPE_TXTAREA, null, false, null );
    $this->initVar( 'user_viewemail', XOBJ_DTYPE_INT, 0, false );
    $this->initVar( 'actkey', XOBJ_DTYPE_OTHER, null, false );
    $this->initVar( 'user_aim', XOBJ_DTYPE_TXTBOX, null, false, 18 );
    $this->initVar( 'user_yim', XOBJ_DTYPE_TXTBOX, null, false, 25 );
    $this->initVar( 'user_msnm', XOBJ_DTYPE_TXTBOX, null, false, 100 );
    $this->initVar( 'pass', XOBJ_DTYPE_TXTBOX, null, false, 32 );
    $this->initVar( 'posts', XOBJ_DTYPE_INT, null, false );
    $this->initVar( 'attachsig', XOBJ_DTYPE_INT, 0, false );
    $this->initVar( 'rank', XOBJ_DTYPE_INT, 0, false );
    $this->initVar( 'level', XOBJ_DTYPE_INT, 0, false );
    $this->initVar( 'theme', XOBJ_DTYPE_OTHER, null, false );
    $this->initVar( 'timezone_offset', XOBJ_DTYPE_OTHER, null, false );
    $this->initVar( 'last_login', XOBJ_DTYPE_INT, 0, false );
    $this->initVar( 'umode', XOBJ_DTYPE_OTHER, null, false );
    $this->initVar( 'uorder', XOBJ_DTYPE_INT, 1, false );
    // RMV-NOTIFY
    $this->initVar( 'notify_method', XOBJ_DTYPE_OTHER, 1, false );
    $this->initVar( 'notify_mode', XOBJ_DTYPE_OTHER, 0, false );
    $this->initVar( 'user_occ', XOBJ_DTYPE_TXTBOX, null, false, 100 );
    $this->initVar( 'bio', XOBJ_DTYPE_TXTAREA, null, false, null );
    $this->initVar( 'user_intrest', XOBJ_DTYPE_TXTBOX, null, false, 150 );
    $this->initVar( 'user_mailok', XOBJ_DTYPE_INT, 1, false );
  }
}

/**
 * handler class of XooNIps User.
 */
class XooNIpsOrmXoopsUsersHandler extends XooNIpsTableObjectHandler {
  function XooNIpsOrmXoopsUsersHandler( &$db ) {
    parent::XooNIpsTableObjectHandler( $db );
    $this->__initHandler( 'XooNIpsOrmXoopsUsers', 'users', 'uid', false );
  }

  function &create( $isNew = true ) {
    $obj =& parent::create( $isNew );
    if ( $isNew ) {
      // override default values
      $obj->set( 'name', '' );
      $obj->set( 'url', '' );
      $obj->set( 'user_avatar', 'blank.gif' );
      $obj->set( 'user_icq', '' );
      $obj->set( 'user_from', '' );
      $obj->set( 'user_sig', '' );
      $obj->set( 'actkey', '' );
      $obj->set( 'user_aim', '' );
      $obj->set( 'user_yim', '' );
      $obj->set( 'user_msnm', '' );
      $obj->set( 'pass', '' );
      $obj->set( 'posts', 0 );
      $obj->set( 'level', 1 );
      $obj->set( 'theme', '' );
      $obj->set( 'umode', '' );
      $obj->set( 'uorder', 0 );
      $obj->set( 'user_occ', '' );
      $obj->set( 'bio', '' );
      $obj->set( 'user_mailok', 0 );
    }
    return $obj;
  }

  function insert( &$obj, $force = false ) {
    if ( $obj->isNew() ) {
      $obj->set( 'user_regdate', time() );
    }
    return parent::insert( $obj, $force );
  }
}

?>
