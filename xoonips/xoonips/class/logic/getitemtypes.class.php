<?php
// $Revision: 1.1.4.1.2.3 $
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
 * subclass of XooNIpsLogic(getItemtypes)
 *
 */
class XooNIpsLogicGetItemtypes extends XooNIpsLogic
{

    /**
     * execute getItemtypes
     *
     * @param[in]  $vars[0] session ID
     * @param[out] $response->result true:success, false:failed
     * @param[out] $response->error  error information
     * @param[out] $response->success array of item type structure
     */
    function execute(&$vars, &$response) 
    {
        // parameter check
        $error = &$response->getError();
        if (count($vars) > 1) $error->add(XNPERR_EXTRA_PARAM);
        if (count($vars) < 1) $error->add(XNPERR_MISSING_PARAM);
        //
        if (isset($vars[0]) && strlen($vars[0]) > 32) $error->add(XNPERR_INVALID_PARAM, 'too long parameter 1');
        //
        if ($error->get(0)) {
            // return if parameter error
            $response->setResult(false);
            return;
        } else {
            $response->setResult(false);
            $sessionid = $vars[0];
        }
        list($result, $uid, $session) = $this->restoreSession($sessionid);
        if (!$result) {
            $response->setResult(false);
            $error->add(XNPERR_INVALID_SESSION);
            return false;
        }
        //
        $item_type_handler = &xoonips_getormhandler('xoonips', 'item_type');
        $item_types =& $item_type_handler->getObjects();
        if (!$item_types) {
            $response->setResult(false);
            $error->add(XNPERR_SERVER_ERROR, "cannot get itemtypes");
            return false;
        }
        $result = array();
        foreach($item_types as $item_type) {
            $item_type_name = $item_type->get('name');
            $detail_item_type_handler = &xoonips_getormhandler($item_type_name, 'item_type');
            if (!$detail_item_type_handler) {
                continue;
            }
            $detail_item_type = $detail_item_type_handler->get($item_type->get('item_type_id'));
            if (!$detail_item_type) {
                continue;
            }
            $result[] = $detail_item_type;
        }
        $response->setSuccess($result);
        $response->setResult(true);
        return true;
    }
}
?>
