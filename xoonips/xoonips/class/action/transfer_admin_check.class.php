<?php
// $Revision: 1.1.2.11 $
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

class XooNIpsActionTransferAdminCheck extends XooNIpsActionTransfer{
    
    function XooNIpsActionTransferAdminCheck(){
        parent::XooNIpsAction();
    }
    
    function _get_logic_name(){
        return null;
    }
    
    function _get_view_name(){
        return "transfer_admin_item_list";
    }
    
    function preAction(){
        xoonips_allow_post_method();
    }
    
    function doAction(){
        global $xoopsUser;
        
        
        $this -> _view_params['from_uid']
            = $this->_formdata->getValue( 'post', 'from_uid', 'i', true );

        $this -> _view_params['from_index_id']
            = $this->_formdata->getValue( 'post', 'from_index_id', 'i', true );

        $this -> _view_params['to_uid']
            = $this->_formdata->getValue( 'post', 'to_uid', 'i', true );

        $this -> _view_params['to_index_id']
            = $this->_formdata->getValue( 'post', 'to_index_id', 'i', true );

        $error_message = false;
        if ( ! $this -> is_valid_uid($this -> _view_params['from_uid']) ){
            $error_message = 
                _AM_XOONIPS_MAINTENANCE_TRANSFER_ITEM_ERROR_NO_FROM_UID_SELECTED;
        }
        else if ( ! $this -> is_valid_uid($this -> _view_params['to_uid']) ){
            $error_message = 
                _AM_XOONIPS_MAINTENANCE_TRANSFER_ITEM_ERROR_NO_TO_UID_SELECTED;
        }
        else if ( ! $this -> is_private_index_id_of(
            $this -> _view_params['from_index_id'], 
            $this -> _view_params['from_uid']) ){
            $error_message = 
                _AM_XOONIPS_MAINTENANCE_TRANSFER_ITEM_ERROR_NO_FROM_INDEX_ID_SELECTED;
        }
        else if ( ! $this -> is_private_index_id_of(
            $this -> _view_params['to_index_id'], 
            $this -> _view_params['to_uid']) ){
            $error_message = 
                _AM_XOONIPS_MAINTENANCE_TRANSFER_ITEM_ERROR_NO_TO_INDEX_ID_SELECTED;
        }
        if ( $error_message ){
            redirect_header(
                XOOPS_URL
                . '/modules/xoonips/admin/maintenance.php?page=item',
                3,
                $error_message
                );
        }
        
        $this -> _view_params['item_ids_to_transfer']
            = $this -> sort_item_ids_by_title(
                $this->_formdata->getValueArray( 'post', 'checked_item_ids', 'i', false ));
        
        $this -> _view_params['child_items']=$this->get_child_items(
            $this->_formdata->getValue( 'post', 'from_uid', 'i', true ),
            $this->_formdata->getValueArray( 'post', 'checked_item_ids', 'i', false ));
        
        $this -> _view_params['limit_check_result']
            = $this->get_limit_check_result(
                $this->_formdata->getValue( 'post', 'to_uid', 'i', true ),
                $this->_formdata->getValueArray( 'post', 'checked_item_ids', 'i', false ) );
        
        $this -> _view_params['group_ids_to_subscribe']
            = xoonips_transfer_get_group_ids_of_items(
                $this->_formdata->getValueArray( 'post', 'checked_item_ids', 'i', false ) );
        
        $this -> _view_params['item_ids_transfer_disabled']
            = $this->get_transfer_disabled_item_ids(
                $this->_formdata->getValue( 'post', 'from_uid', 'i', true ),
                $this->_formdata->getValueArray( 'post', 'checked_item_ids', 'i', false ) );
        
        $this -> _view_params['can_not_transfer_items'] = 
            $this -> get_untransferrable_reasons_and_items(
                $this->_formdata->getValue( 'post', 'from_uid', 'i', true ),
                $this->_formdata->getValueArray( 'post', 'checked_item_ids', 'i', false ) );
    }
    
    function get_child_items($from_uid, $item_ids){
        $result = array();
        foreach( xoonips_transfer_get_transferrable_item_information(
            $from_uid, 
            $item_ids )
                 as $info ){
            $result[$info['item_id']] = array();
            foreach( $info['child_items'] as $child_item ){
                $result[$info['item_id']][] = $child_item['item_id'];
            }
        }
        return $result;
    }
    
    function get_private_index_id($uid){
        $user_hanlder =& xoonips_getormhandler( 'xoonips', 'users' );
        $user =& $user_hanlder->get($uid);
        if( !$user ) return false;
        return $user -> get( 'private_index_id' );
    }
    
    function get_transfer_disabled_item_ids( $from_uid, $item_ids ){
        $result = array();
        foreach( xoonips_transfer_get_transferrable_item_information( $from_uid,
                                                                      $item_ids)
                 as $info ){
            if( !$info['transfer_enable'] ){
                $result[] = $info['item_id'];
            }
        }
        return $result;
    }
    function get_cause_of_transfer_disable( $from_uid, $item_ids ){
        $result = array();
        foreach( xoonips_transfer_get_transferrable_item_information( $from_uid,
                                                                      $item_ids)
                 as $info ){
            if( !$info['transfer_enable'] ){
                
                $result[] = $info['item_id'];
            }
        }
        return $result;
    }
    
    function is_valid_uid( $uid )
    {
        $user_hanlder =& xoonips_getormhandler( 'xoonips', 'users' );
        $user =& $user_hanlder->get($uid);
        if( !$user ) return false;
        return true;
    }
    function is_private_index_id_of( $index_id, $uid )
    {
        $index_handler =& xoonips_getormhandler( 'xoonips', 'index' );
        $index = $index_handler->get( $index_id );
        if ( $index == false ||
            $index->get( 'open_level' ) != OL_PRIVATE ||
            $index->get( 'uid' ) != $uid ){
            return false;
        }
        return true;
    }
}

?>
