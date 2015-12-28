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

include_once dirname( __DIR__ ) . '/base/action.class.php';
include_once dirname( __DIR__ ) .'/base/logicfactory.class.php';
require_once dirname( __DIR__ ).'/base/gtickets.php';

class XooNIpsActionImportImport extends XooNIpsAction{
    
    var $_view_name = null;
    var $_collection = null;
    
    function XooNIpsActionImportImport(){
        parent::XooNIpsAction();
    }
    
    function _get_logic_name(){
        return 'importImport';
    }
    
    function _get_view_name(){
        return 'import_finish';
    }
    
    function preAction(){
        xoonips_deny_guest_access();
        xoonips_allow_post_method();

        if ( ! $GLOBALS['xoopsGTicket']->check( true , 'import', false ) ) {
          die( 'ticket error' );
        }

        $itemtype_handler =& xoonips_getormhandler( 'xoonips', 'item_type' );
        foreach( $itemtype_handler -> getObjects() as $itemtype ){
            if( 'xoonips_index' == $itemtype -> get( 'name' ) ) continue;
            $handler =& xoonips_gethandler( $itemtype -> get( 'name' ),
                                            'import_item' );
            $handler -> create();
        }
        $sess_handler =& xoonips_getormhandler( 'xoonips', 'session' );
        $sess =& $sess_handler -> get( session_id() );
        $session = unserialize( $sess -> get( 'sess_data' ) );
        $this -> _collection = unserialize( gzuncompress(
            base64_decode( $session['xoonips_import_items'] ) ) );
        xoonips_validate_request( $this -> _collection );
        
        $this -> _make_clone_of_update_item($this -> _collection);
        
        $this -> _begin_time = time();
        $this -> _params[] =& $this -> _collection -> getItems();
    }
    
    function postAction(){
        global $xoopsUser;
        if( !$this -> _response -> getResult() ){
            foreach( $this -> _collection -> getItems() as $item ){
                foreach( $item -> getErrorCodes() as $code ){
                    if( $code != E_XOONIPS_UPDATE_CERTIFY_REQUEST_LOCKED ){
                        continue;
                    }
                    $titles =& $item -> getVar( 'titles' );
                    $item_lock_handler =& 
                        xoonips_getormhandler( 'xoonips', 'item_lock' );
                    redirect_header(
                        XOOPS_URL.'/modules/xoonips/import.php?action=default',
                        5, sprintf(
                            _MD_XOONIPS_ERROR_CANNOT_OVERWRITE_LOCKED_ITEM, 
                            $titles[0] -> get( 'title' ),
                            xoonips_get_lock_type_string(
                                $item_lock_handler->getLockType(
                                    $item -> getUpdateItemId() ))));
                }
            }
        }
        
        $this -> _finish_time = time();
        $success =& $this -> _response -> getSuccess();
        $this -> _view_params['result'] = $this -> _response -> getResult();
        $this -> _view_params['import_items'] = $success['import_items'];
        $this -> _view_params['begin_time'] = $this -> _begin_time;
        $this -> _view_params['finish_time'] = $this -> _finish_time;
        $this -> _view_params['filename']
            = $this -> _collection -> getImportFileName();
        $this -> _view_params['uname'] = $xoopsUser -> getVar( 'uname' );
        $this -> _view_params['errors'] = array();
        foreach( $success['import_items'] as $item ){
            foreach( array_unique( $item -> getErrorCodes() ) as $code ){
                $this -> _view_params['errors'][]
                    = array( 'code' => $code,
                             'extra' => $item -> getPseudoId() );
            }
        }
    }
    
    function _make_clone_of_update_item(&$collection){
        $items =& $collection->getItems();
        foreach( array_keys( $items ) as $key ){
            if( !$items[ $key ] -> getUpdateFlag() ) continue;
            
            if( count( $items[ $key ] -> getDuplicateUpdatableItemId() ) == 1 ){
                $update_item_ids
                    = $items[ $key ] -> getDuplicateUpdatableItemId();
                $items[ $key ] -> setUpdateItemId($update_item_ids[0] );
            }else{
                $i = 0;
                foreach( $items[ $key ] -> getDuplicateUpdatableItemId()
                         as $update_item_id ){
                    if( $i == 0 ){
                        $items[ $key ] -> setUpdateItemId( $update_item_id );
                        $i=1;
                    }else{
                        $clone_item =& $items[ $key ] -> getClone();
                        $clone_item -> setUpdateItemId( $update_item_id );
                        $collection -> addItem( $clone_item );
                    }
                }
            }
        }
    }
}

?>
