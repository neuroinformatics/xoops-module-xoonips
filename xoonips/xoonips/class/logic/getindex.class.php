<?php

// $Revision: 1.1.4.1.2.2 $
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
 * subclass of XooNIpsLogic(gettIndex).
 */
class XooNIpsLogicGetIndex extends XooNIpsLogic
{
    /**
     * execute getIndex.
     *
     * @param[in]  $vars[0] session ID
     * @param[in]  $vars[1] index ID
     * @param[out] $response->result true:success, false:failed
     * @param[out] $response->error  error information
     * @param[out] $response->success XooNIpsIndexCompo index information
     */
    public function execute(&$vars, &$response)
    {
        // parameter check
        $error = &$response->getError();
        if (count($vars) > 2) {
            $error->add(XNPERR_EXTRA_PARAM);
        } elseif (count($vars) < 2) {
            $error->add(XNPERR_MISSING_PARAM);
        } else {
            if (isset($vars[0]) && strlen($vars[0]) > 32) {
                $error->add(XNPERR_INVALID_PARAM, 'too long parameter 1');
            }
            if (!is_int($vars[1]) && !ctype_digit($vars[1])) {
                $error->add(XNPERR_INVALID_PARAM, 'not integer parameter 2');
            }
        }
        if ($error->get(0)) {
            // return if parameter error
            $response->setResult(false);

            return;
        } else {
            $sessionid = $vars[0];
            $index_id = $vars[1];
        }
        list($result, $uid, $session) = $this->restoreSession($sessionid);
        if (!$result) {
            $response->setResult(false);
            $error->add(XNPERR_INVALID_SESSION);

            return false;
        }
        // get index from index_id
        $index_compo_handler = &xoonips_getormcompohandler('xoonips', 'index');
        $index = $index_compo_handler->get($index_id);
        if ($index == false) {
            $response->setResult(false);
            $response->error->add(XNPERR_NOT_FOUND, 'cannot get index');

            return false;
        }
        // check permission
        $index_handler = &xoonips_getormhandler('xoonips', 'index');
        if (!$index_handler->getPerm($index_id, $uid, 'read')) {
            $response->setResult(false);
            $response->error->add(XNPERR_ACCESS_FORBIDDEN, 'no permission');

            return false;
        }
        $response->setSuccess($index);
        $response->setResult(true);

        return true;
    }
}
