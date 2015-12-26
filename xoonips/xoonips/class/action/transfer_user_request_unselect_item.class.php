<?php
// $Revision: 1.1.2.10 $
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
include_once dirname( dirname( dirname( __FILE__ ) ) )
    . '/include/transfer.inc.php';
include_once dirname( dirname( dirname( __FILE__ ) ) )
    . '/include/extra_param.inc.php';

class XooNIpsActionTransferUserRequestUnselectItem 
extends XooNIpsActionTransfer{
    
    function XooNIpsActionTransferUserRequestUnselectItem(){
        parent::XooNIpsAction();
    }
    
    function _get_logic_name(){
        return null;
    }
    
    function _get_view_name(){
        return "transfer_user_item_list";
    }
    
    function preAction(){
        global $xoopsUser;
        xoonips_deny_guest_access();
        xoonips_allow_post_method();
        
        $extra_params = xoonips_extra_param_restore();
        xoonips_validate_request(
            $this->is_valid_transferee_user( @$extra_params['to_uid'] ) );
        
        xoonips_validate_request(
            $this->is_readable_all_items(
                $this->_formdata->getValueArray( 'post', 'selected_original', 'i', false ),
                $xoopsUser -> getVar( 'uid' ) ) );
                
    }

    function doAction(){
        global $xoopsUser;
        
        $this -> _view_params['to_uid']
            = $this->_formdata->getValue( 'post', 'to_uid', 'i', false);
        
        $item_ids_to_transfer = $this -> remove_item_id(
            $this->_formdata->getValue( 'post', 'item_id_to_unselect', 'i', false ),
            $this->_formdata->getValueArray( 'post', 'item_ids_to_transfer', 'i', false ) );
        
        $this -> _view_params['items_to_transfer']
            = xoonips_transfer_get_transferrable_item_information(
                $xoopsUser -> getVar( 'uid' ),
                $item_ids_to_transfer );
        
        $this -> _view_params['to_user_options']
            = xoonips_transfer_get_users_for_dropdown( 
                $xoopsUser -> getVar( 'uid' ) );
        
        $this -> _view_params['transfer_enable']
            = $this -> is_all_transferrable_items( 
                $xoopsUser -> getVar( 'uid' ),
                $item_ids_to_transfer );
    }
    
    /**
     * remove $item_id from $item_ids array.
     * Note: nothing to do if $item_id is not numeric
     *  or $item_ids is not an array.
     * 
     * @access private
     * @param integer $item_id item id to remove
     * @param array $item_ids array of item id 
     * @return array array of item id
     */
    function remove_item_id($item_id, $item_ids){
        if( !is_numeric( $item_id ) ) return $item_ids;
        if( !is_array( $item_ids ) ) return $item_ids;
        return array_diff( $item_ids, array( $item_id ) );
    }
}

?>
