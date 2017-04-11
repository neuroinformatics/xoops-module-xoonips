<?php

// $Revision: 1.1.2.4 $
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

include_once XOOPS_ROOT_PATH.'/modules/xoonips/class/base/logic.class.php';

/**
 * subclass of XooNIpsLogic(getItemPermission).
 */
class XooNIpsLogicGetItemPermission extends XooNIpsLogic
{
    /**
     * execute getItemPermission.
     *
     * @param[in] $vars[0] sessionid
     * @param[in] $vars[1] id
     * @param[in] $vars[2] id_type
     * @param[out] $response->result true:success, false:failed
     * @param[out] $response->error  error information
     * @param[out] $response->success array item permission structure
     *
     * @return false if fault
     */
    public function execute(&$vars, &$response)
    {
        // parameter check
        $error = &$response->getError();
        if (count($vars) > 3) {
            $error->add(XNPERR_EXTRA_PARAM);
        } elseif (count($vars) < 3) {
            $error->add(XNPERR_MISSING_PARAM);
        } else {
            if (isset($vars[0]) && strlen($vars[0]) > 32) {
                $error->add(XNPERR_INVALID_PARAM, 'too long parameter 1');
            }
            if ($vars[2] != 'item_id' && $vars[2] != 'ext_id') {
                $error->add(XNPERR_INVALID_PARAM, 'invalid parameter 3');
            }
            if ($vars[2] == 'item_id') {
                if (!is_int($vars[1]) && !ctype_digit($vars[1])) {
                    $error->add(XNPERR_INVALID_PARAM,
                                'not integer parameter 2');
                }
                if (strlen($vars[1]) > 10) {
                    $error->add(XNPERR_INVALID_PARAM, 'too long parameter 2');
                }
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
            if ($id_type == 'item_id') {
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

        // ext_id to item_id
        $item_compo_handler = &xoonips_getormcompohandler('xoonips', 'item');
        if ($id_type == 'ext_id') {
            $item_compo = $item_compo_handler->getByExtId($id);
        } elseif ($id_type == 'item_id') {
            $item_compo = $item_compo_handler->get($id);
        } else {
            $error->add(XNPERR_INVALID_PARAM, "invalid id_type({$id_type})");
            $response->setResult(false);

            return false;
        }
        if ($item_compo == false) {
            $error->add(XNPERR_NOT_FOUND);
            $response->setResult(false);

            return false;
        }
        $item_basic = $item_compo->getVar('basic');
        $item_id = $item_basic->get('item_id');

        // get permission
        $result = array(
            'read' => $item_compo_handler->getPerm($item_id, $uid, 'read'),
            'write' => $item_compo_handler->getPerm($item_id, $uid, 'write'),
            'delete' => $item_compo_handler->getPerm($item_id, $uid,
                                                      'delete'),
        );
        $response->setSuccess($result);
        $response->setResult(true);

        return true;
    }
}
