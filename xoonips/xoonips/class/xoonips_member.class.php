<?php
// $Revision: 1.1.2.12 $
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

/**
 * class of XooNIps Member Handler
 */
class XooNIpsMemberHandler
{

    /**
     * holds reference to XooNIps account handler(DAO) class
     */
    var $_aHandler;

    /**
     * constructor
     */
    function XooNIpsMemberHandler(&$db) 
    {
        $this->_aHandler = &xoonips_getormcompohandler('xoonips', 'user');
    }

    /** authenticate and return XooNIpsUser object.
     * @param uname user name
     * @param pass password
     * @return if uname and pass are correct, returns XooNIpsAccount object. 
     * otherwise returns false.
     */
    function &loginUser($uname, $pass) 
    {
        $criteria = new CriteriaCompo(new Criteria('uname', $uname));
        $criteria->add(new Criteria('pass', md5($pass)));
        $user = &$this->_aHandler->getObjects($criteria);
        if (!$user || count($user) != 1) {
            $ret = false;
            return $ret;
        }
        return $user[0];
    }

    /** check if $uid is moderator. todo: should be cached?
     * @access public
     * @param uid user ID
     * @return true if $uid is moderator. false otherwise.
     */
    function isModerator($uid) 
    {
        if ($uid == UID_GUEST) {
            return false;
        }
        // get moderator group id
        $xconfig_handler =& xoonips_getormhandler( 'xoonips', 'config' );
        $moderator_gid = $xconfig_handler->getValue( 'moderator_gid' ); 
        if ( is_null( $moderator_gid ) ) {
            return false;
        }
        // is $uid in that group?
        $xoops_member_handler = &xoops_gethandler('member');
        $groups = $xoops_member_handler->getGroupsByUser($uid);
        return in_array((int)$moderator_gid, $groups);
    }
    
    /** check if $uid is xoonips admin. todo: should be cached?
     * @access public
     * @param uid user ID
     * @return true if $uid is xoonips admin. false otherwise.
     */
    function isAdmin($uid)
    {
        // xoonips admin?
        $xoops_user_handler = &xoops_gethandler( 'user' );
        $user = $xoops_user_handler->get( $uid );
        $module_handler =& xoops_gethandler( 'module' );
        $module =& $module_handler->getByDirname( 'xoonips' );
        if( is_object( $module ) && is_object( $user ) ) {
            $mid = $module->getVar( 'mid', 'n' );
            if ( $user->isAdmin( $mid ) ){
                return true; // xoonips admin
            }
        }
        return false;
    }

    /**
     * XOOPS user pickup
     *
     * @access public
     * @param int uid user id
     * @param bool is_certified initial certification state
     * @return bool false if failure
     */
    function pickupXoopsUser( $uid, $is_certified ) {
      $xu_handler =& xoonips_getormhandler( 'xoonips', 'users' );
      // create user root index
      $index_handler =& xoonips_getormhandler( 'xoonips', 'index' );
      $index_id = $index_handler->createUserRootIndex( $uid );
      if ( $index_id === false ) {
        return false;
      }

      // set xoonips user information
      $activate = ( $is_certified ) ? 1 : 0;
      $xu_obj =& $xu_handler->create();
      $xu_obj->setVar( 'uid', $uid, true ); // not gpc
      $xu_obj->setVar( 'activate', $activate, true ); // not gpc
      $xu_obj->setVar( 'private_index_id', $index_id, true ); // not gpc
      // set dummy variable to required field
      $dummy_field = 'required';
      $xconfig_handler = &xoonips_getormhandler('xoonips', 'config');
      $keys = array(
        // config key name => field name of 'xoonips_users' table
        'account_address_optional' => 'address',
        'account_division_optional' => 'division',
        'account_tel_optional' => 'tel',
        'account_company_name_optional' => 'company_name',
        'account_country_optional' => 'country',
        'account_zipcode_optional' => 'zipcode',
        'account_fax_optional' => 'fax',
      );
      foreach ( $keys as $key => $field ) {
        $val = $xconfig_handler->getValue( $key );
        if ( $val == 'off' ) {
          // 'optional off' means 'required'
          $xu_obj->setVar( $field, $dummy_field, true ); // not gpc
        }
        unset( $xc_obj );
      }
      // register user information
      if ( ! $xu_handler->insert( $xu_obj ) ) {
        // TODO: delete created private index
        return false;
      }

      // record event logs
      $event_handler =& xoonips_getormhandler( 'xoonips', 'event_log' );
      $event_handler->recordRequestInsertAccountEvent( $uid );
      if ( $activate ) {
        $event_handler->recordCertifyAccountEvent( $uid );
      }

      // join default group
      $admin_xgroup_handler =& xoonips_gethandler( 'xoonips', 'admin_group' );
      if ( ! $admin_xgroup_handler->addUserToDefaultXooNIpsGroup( $uid ) ) {
        // TODO: delete created private index
        $xu_handler->delete( $xu_obj );
        return false;
      }
      return true;
    }
}
?>
