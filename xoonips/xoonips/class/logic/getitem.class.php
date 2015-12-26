<?php
// $Revision: 1.1.4.1.2.10 $
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

include_once XOOPS_ROOT_PATH . '/modules/xoonips/class/base/logic.class.php';

/**
 *
 * subclass of XooNIpsLogic(getItem)
 *
 */
class XooNIpsLogicGetItem extends XooNIpsLogic
{

    /**
     * execute getItem
     *
     * @param[in] $vars[0] sessionid
     * @param[in] $vars[1] id
     * @param[in] $vars[2] id_type
     * @param[out] $response->result true:success, false:failed
     * @param[out] $response->error  error information
     * @param[out] $response->success array of child indexes
     * @return XooNIpsItem retrieved item object
     * @return false if fault
     */
    function execute(&$vars, &$response) 
    {
        // parameter check
        $error = &$response->getError();
        if (count($vars) > 3) $error->add(XNPERR_EXTRA_PARAM);
        else if (count($vars) < 3) $error->add(XNPERR_MISSING_PARAM);
        else {
            if (isset($vars[0]) && strlen($vars[0]) > 32) $error->add(XNPERR_INVALID_PARAM, 'too long parameter 1');
            if ($vars[2] != 'item_id' && $vars[2] != 'ext_id') $error->add(XNPERR_INVALID_PARAM, 'invalid parameter 3');
            if ($vars[2] == 'item_id') {
                if (!is_int($vars[1]) && !ctype_digit($vars[1])) $error->add(XNPERR_INVALID_PARAM, 'not integer parameter 2');
                if (strlen($vars[1]) > 10) $error->add(XNPERR_INVALID_PARAM, 'too long parameter 2');
            }
        }
        if ($error->get(0)) {
            // return if parameter error
            $response->setResult(false);
            return false;
        } else {
            $sessionid = $vars[0];
            $id = $vars[1];
            $id_type = $vars[2];
            if ( $id_type == 'item_id' ){
                $id = intval($id);
            }
        }
        // validate session
        list($result, $uid, $session) = $this->restoreSession($sessionid);
        if (!$result) {
            // error invalid session
            $error->add(XNPERR_INVALID_SESSION);
            $response->setResult(false);
            return false;
        }
        // retrieve item
        //
        $itemtype_handler = &xoonips_getormhandler('xoonips', 'item_type');
        if ($id_type == 'item_id') {
            $itemtype =& $itemtype_handler->getObjects( new Criteria('item_id', $id) , false, '', false, new XooNIpsJoinCriteria( 'xoonips_item_basic', 'item_type_id', 'item_type_id' ) );
        } else if ($id_type == 'ext_id') {
            //$itemtype =& $itemtype_handler->getByExtId($id);
            $itemtype =& $itemtype_handler->getObjects( new Criteria('doi', addslashes($id)) , false, '', false, new XooNIpsJoinCriteria( 'xoonips_item_basic', 'item_type_id', 'item_type_id' ) );
        } else {
            $error->add(XNPERR_INVALID_PARAM, "invalid id_type({$id_type})");
            $response->setResult(false);
            return false;
        }
        //
        if ( $itemtype === false ){
            $error->add(XNPERR_SERVER_ERROR, "cannot get itemtype");
            $response->setResult(false);
            return false;
        }
        else if ( empty($itemtype) ){
            $error->add(XNPERR_NOT_FOUND, "({$id})");
            $response->setResult(false);
            return false;
        }
        
        // not found error if item is not suported itemtype.
        $item_handler = &xoonips_getormcompohandler($itemtype[0]->get('name') , 'item');
        if( !is_object($item_handler) ){
            $error->add(XNPERR_NOT_FOUND, "({$id})");
            $response->setResult(false);
            return false;
        }

        $item_basic_handler = &xoonips_getormhandler('xoonips', 'item_basic');
        if ($id_type == 'item_id') {
            $item = &$item_handler->get($id);
        } else if ($id_type == 'ext_id') {
            if ( strlen($id) == 0 ){
                $response->setResult(false);
                $error->add(XNPERR_INVALID_PARAM, "ext_id is empty");
                return false;
            }
            else {
                $basics =& $item_basic_handler->getObjects(new Criteria('doi', addslashes($id)));
                if ( false === $basics ){
                    $response->setResult(false);
                    $error->add(XNPERR_SERVER_ERROR, "cannot get basic information");
                    return false;
                }
                else if ( count($basics) >= 2 ){
                    $response->setResult(false);
                    $error->add(XNPERR_SERVER_ERROR, "ext_id is duplicated");
                    return false;
                }
                else if ( count($basics) == 1 ){
                    $item = $item_handler->get($basics[0]->get('item_id'));
                }
                else {
                    $item = false;
                }
            }
        } else {
            $error->add(XNPERR_INVALID_PARAM, "invalid id_type({$id_type})");
            $response->setResult(false);
            return false;
        }
        if (!$item) {
            $error->add(XNPERR_NOT_FOUND, "({$id})");
            $response->setResult(false);
            return false;
        }

        $basic = $item->getVar('basic');
        
        // check itemtype( return error if index or binder )
        if( $basic->get('item_type_id')==ITID_INDEX ){
            $error->add(XNPERR_NOT_FOUND, "({$id})");
            $response->setResult(false);
            return false;
        }

        // check access permission
        $perm = $item_handler->getPerm($basic->get('item_id') , $uid , 'read');
        if (!$perm) {
            $error->add(XNPERR_ACCESS_FORBIDDEN);
            $response->setResult(false);
            return false;
        }
        // insert view_item event
        $eventlog_handler =& xoonips_getormhandler('xoonips', 'event_log');
        $eventlog_handler->recordViewItemEvent( $basic->get( 'item_id' ) );
        
        $this->adjustPublicationDate( $item );
        $item->setVar( 'related_tos', 
            $this->getReadableRelatedTos( $item, $uid ) );
        
        $response->setSuccess($item);
        $response->setResult(true);
        return true;
    }
    
    
    /**
     * adjust publication year, month, day
     * @param XooNIpsItemInfoCompo item
     */
    function adjustPublicationDate( &$item )
    {
        $basic = $item->getVar( 'basic' );
        $y = $basic->get( 'publication_year' );
        $m = $basic->get( 'publication_month' );
        $d = $basic->get( 'publication_mday' );
        
        if ( empty( $y ) ){
            $y = 0;
        }
        if ( empty( $m ) ){
            $m = 0;
        }
        if ( empty( $d ) ){
            $d = 0;
        }
        
        if ( $y <= 0 || 10000 <= $y ){
            $y = 0;
            $m = 0;
            $d = 0;
        }
        else if ( $m <= 0 || 13 <= $m ){
            $m = 0;
            $d = 0;
        }
        else if ( $d <= 0 || 32 <= $d ){
            $d = 0;
        }
        else {
            if ( !checkdate( $m, $d, $y ) ){
                if      ( !checkdate( $m, 29, $y ) ) $d -= 28;
                else if ( !checkdate( $m, 30, $y ) ) $d -= 29;
                else                                 $d -= 30;
                $m++;
            }
        }
        
        $basic->set( 'publication_year', $y );
        $basic->set( 'publication_month', $m );
        $basic->set( 'publication_mday', $d );
        $item->setVar( 'basic', $basic );
    }
    
    /**
     * get readable related_to
     * @param XooNIpsItemInfoCompo item
     * @param int uid
     * @return array array of readable related_to objects
     */
    function getReadableRelatedTos( $item, $uid )
    {
        $related_tos = $item->getVar('related_tos');
        $item_compo_handler =& xoonips_getormcompohandler('xoonips', 'item');
        $new_related_tos = array();
        foreach ( $related_tos as $related_to ){
            $item_id = $related_to->get( 'item_id' );
            if ( $item_compo_handler->getPerm( $item_id, $uid, 'read' ) ){
                $new_related_tos[] = $related_to;
            }
        }
        return $new_related_tos;
    }
    
}
?>
