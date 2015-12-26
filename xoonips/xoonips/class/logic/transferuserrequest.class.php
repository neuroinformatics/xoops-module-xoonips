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

include_once dirname( dirname( __FILE__ ) ) . '/base/logic.class.php';
include_once dirname( __FILE__ ) . '/transfer.class.php';

class XooNIpsLogicTransferUserRequest extends XooNIpsLogicTransfer
{
    function XooNIpsLogicTransferUserRequest(){
        parent::XooNIpsLogic();
    }
    
    /**
     * transfer request
     *
     * @param[in]  $vars[0] array of item_id 
     * @param[in]  $vars[1] uid of old item owner
     * @param[in]  $vars[2] uid of new item owner
     * @param[out] XooNIpsError error
     * @return bool true if succeeded
     */
    function execute_without_transaction(&$vars, &$error){
        $item_ids = $vars[0];
        $from_uid = $vars[1];
        $to_uid = $vars[2];
        
        if ( false == xoonips_transfer_is_transferrable(
            $from_uid, $to_uid, $item_ids ) ){
            $error->add(XNPERR_SERVER_ERROR, "not transferrable");
            return false;
        }
        
        foreach ( $item_ids as $item_id ){
            $transfer_request_handler =& xoonips_getormhandler(
                'xoonips', 'transfer_request' );
            $transfer_request = $transfer_request_handler->create();
            $transfer_request->set( 'item_id', $item_id );
            $transfer_request->set( 'to_uid', $to_uid );
            if ( false == $transfer_request_handler->insert(
                $transfer_request ) ){
                $error->add(XNPERR_SERVER_ERROR,
                            "cannot insert tranfer_request");
                return false;
            }
            
            $item_lock_handler =& xoonips_getormhandler(
                'xoonips', 'item_lock' );
            if ( false == $item_lock_handler->lock( $item_id ) ){
                $error->add(XNPERR_SERVER_ERROR, "cannot lock item");
                return false;
            }
            
            $eventlog_handler =& xoonips_getormhandler( 'xoonips', 'event_log' );
            if ( false == $eventlog_handler->recordRequestTransferItemEvent( $item_id, $to_uid ) ){
                $error->add(XNPERR_SERVER_ERROR, "cannot insert evnet log");
                return false;
            }
        }
        
        return true;
    }
}
?>
