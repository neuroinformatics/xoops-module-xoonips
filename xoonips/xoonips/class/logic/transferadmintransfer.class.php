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

include_once dirname( dirname( __FILE__ ) ) . '/base/logic.class.php';
include_once dirname( __FILE__ ) . '/transfer.class.php';

class XooNIpsLogicTransferAdminTransfer extends XooNIpsLogicTransfer
{
    function XooNIpsLogicTransferAdminTransfer(){
        parent::XooNIpsLogic();
    }
    
    /**
     * transfer items for admin
     *
     * @param[in]  $vars[0] array of item_id 
     * @param[in]  $vars[1] uid of current item owner
     * @param[in]  $vars[2] uid of new item owner
     * @param[in]  $vars[3] index_id where items are registered to
     * @param[in]  $vars[4] array of group id 
     * which new item owner are registered to
     * @param[out] XooNIpsError error
     * @return bool true if succeeded
     */
    function execute_without_transaction(&$vars, &$error){
        $item_ids = $vars[0];
        $from_uid = $vars[1];
        $to_uid = $vars[2];
        $index_id = $vars[3];
        $gids = $vars[4];
        
        if ( false == $this->add_user_to_groups( $error, $to_uid, $gids ) ){
            return false;
        }
        
        if ( false == xoonips_transfer_is_transferrable( $from_uid,
                                                         $to_uid, $item_ids ) ){
            $error->add(XNPERR_SERVER_ERROR, "not transferrable");
            return false;
        }
        
        if ( false == $this->is_private_index_id_of( $index_id, $to_uid ) ){
            $error->add(XNPERR_SERVER_ERROR, "bad index id");
            return false;
        }
        
        foreach ( $item_ids as $item_id ){
            if ( false == $this->move_item_to_other_private_index(
                $error, $item_id, $index_id ) ){
                return false;
            }
            
            if ( false == $this->remove_item_from_achievements_if_needed(
                $error, $item_id ) ){
                return false;
            }
            
            $item_basic_handler =& xoonips_getormhandler( 'xoonips',
                                                          'item_basic' );
            $item_basic = $item_basic_handler->get( $item_id );
            $item_basic->set( 'uid', $to_uid );
            if ( false == $item_basic_handler->insert( $item_basic ) ){
                $error->add(XNPERR_SERVER_ERROR, "cannot change owner of item");
                return false;
            }
            
            $eventlog_handler =& xoonips_getormhandler( 'xoonips', 'event_log' );
            if ( ! $eventlog_handler->recordTransferItemEvent( $item_id, $index_id, $to_uid ) ){
                $error->add(XNPERR_SERVER_ERROR, "cannot insert event");
                return false;
            }
            
            if ( false == $this->update_item_status_if_public_certified(
                $error, $item_id ) ){
                return false;
            }
        }
        foreach ( $item_ids as $item_id ){
            if ( false == $this->remove_related_to_if_no_read_permission(
                $item_id, $from_uid, $to_uid ) ){
                return false;
            }
        }
        
        return true;
    }
    
    function add_user_to_groups( &$error, $uid, $gids )
    {
        foreach ( $gids as $gid ){
            // add to group
            $groups_users_link_handler =& xoonips_getormhandler(
                'xoonips', 'groups_users_link' );
            $groups_users_link = $groups_users_link_handler->create();
            $groups_users_link->set( 'gid', $gid );
            $groups_users_link->set( 'uid', $uid );
            $groups_users_link->set( 'is_admin', 0 );
            if ( false == $groups_users_link_handler->insert(
                $groups_users_link ) ){
                $error->add(XNPERR_SERVER_ERROR, "cannot add to group");
                return false;
            }
            
            // add event
            $eventlog_handler =& xoonips_getormhandler( 'xoonips', 'event_log' );
            if ( ! $eventlog_handler->recordInsertGroupMemberEvent( $uid, $gid ) ){
                $error->add(XNPERR_SERVER_ERROR, "cannot insert event");
                return false;
            }
        }
        return true;
    }
}
?>
