<?php
// $Revision: 1.1.2.13 $
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

include_once 'transfer.class.php';
include_once dirname( dirname( __DIR__ ) )
    . '/include/transfer.inc.php';
include_once dirname( __DIR__ ).'/base/gtickets.php';

class XooNIpsActionTransferAdminTransfer extends XooNIpsActionTransfer{
    
    function XooNIpsActionTransferAdminTransfer(){
        parent::XooNIpsAction();
    }
    
    function _get_logic_name(){
        return 'transferAdminTransfer';
    }
    
    function _get_view_name(){
        return null;
    }
    
    function preAction(){
        xoonips_allow_post_method();

        if( ! $GLOBALS['xoopsGTicket']->check( true , 'xoonips_transfer_admin_list_item', false ) ){
          die( 'ticket error' );
        }

        global $xoopsUser;
        
        $from_uid = $this->_formdata->getValue( 'post', 'from_uid', 'i', true );
        $to_uid = $this->_formdata->getValue( 'post', 'to_uid', 'i', true );
        $to_index_id = $this->_formdata->getValue( 'post', 'to_index_id', 'i', true );
        
        $transfer_item_ids = array_merge( 
            $this -> get_item_ids_to_transfer(),
            $this -> get_child_item_ids_to_transfer() );

        if( !xoonips_transfer_is_transferrable( $from_uid, $to_uid,
                                                $transfer_item_ids ) ){
            redirect_header( XOOPS_URL
                             . '/modules/xoonips/admin/maintenance.php?'
                             . 'page=item&action=transfer_admin_initialize',
                             3, _AM_XOONIPS_MAINTENANCE_TRANSFER_ITEM_ERROR );
        }
        if( xoonips_transfer_is_private_item_number_exceeds_if_transfer(
            $to_uid, $transfer_item_ids ) ){
            redirect_header(
                XOOPS_URL
                . '/modules/xoonips/admin/maintenance.php?'
                . 'page=item&action=transfer_admin_initialize',
                3,
                _AM_XOONIPS_MAINTENANCE_TRANSFER_ITEM_ERROR_ITEM_NUMBER_EXCEEDS
                );
        }
        if( xoonips_transfer_is_private_item_storage_exceeds_if_transfer(
            $to_uid, $transfer_item_ids )){
            redirect_header(
                XOOPS_URL
                . '/modules/xoonips/admin/maintenance.php?'
                . 'page=item&action=transfer_admin_initialize',
                3,
                _AM_XOONIPS_MAINTENANCE_TRANSFER_ITEM_ERROR_ITEM_STORAGE_EXCEEDS
                );
        }
        
        if( !$this->is_equals_group_ids(
            xoonips_transfer_get_group_ids_of_items($transfer_item_ids),
            $this->_formdata->getValueArray( 'post', 'group_ids_to_subscribe', 'i', false ) ) ) {
            redirect_header(
                XOOPS_URL
                . '/modules/xoonips/admin/maintenance.php'
                . '?page=item',
                3,
                _AM_XOONIPS_MAINTENANCE_TRANSFER_ITEM_ERROR_BAD_SUBSCRIBE_GROUP
                );
        }
        
        $this -> _params[] = $transfer_item_ids;
        $this -> _params[] = $from_uid;
        $this -> _params[] = $to_uid;
        $this -> _params[] = $to_index_id;
        $this -> _params[] = xoonips_transfer_get_group_ids_of_items(
            $transfer_item_ids);
    }
    
    function postAction(){
        if( $this -> _response -> getResult() ){
            redirect_header( XOOPS_URL
                             . '/modules/xoonips/admin/maintenance.php'
                             . '?page=item',
                             3,
                             _AM_XOONIPS_MAINTENANCE_TRANSFER_ITEM_COMPLETE );
        }else{
            redirect_header( XOOPS_URL
                             . '/modules/xoonips/admin/maintenance.php'
                             . '?page=item',
                             3, _AM_XOONIPS_MAINTENANCE_TRANSFER_ITEM_ERROR );
        }
    }

    function is_equals_group_ids( $gids1, $gids2 ){
        $a = is_array( $gids1 ) ? $gids1 : array();
        $b = is_array( $gids2 ) ? $gids2 : array();
        return count( array_diff( $a, $b ) ) == 0
            && count( array_diff( $b, $a ) ) == 0;
    }

    function get_item_ids_to_transfer(){
        $result = $this->_formdata->getValueArray( 'post', 'item_ids_to_transfer', 'i', false );
        return is_array( $result ) ? $result : array();
    }
    
    function get_child_item_ids_to_transfer(){
        $result = $this->_formdata->getValueArray( 'post', 'child_item_ids_to_transfer', 'i', false );
        return is_array( $result ) ? $result : array();
    }
}
?>
