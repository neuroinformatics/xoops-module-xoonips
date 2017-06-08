<?php

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

require_once XOOPS_ROOT_PATH.'/modules/xoonips/class/base/logic.class.php';

/**
 * subclass of XooNIpsLogic(getPreference).
 */
class XooNIpsLogicGetPreference extends XooNIpsLogic
{
    /**
     * execute getPreference.
     *
     * @param[in]  $vars[0] session ID
     * @param[out] $response->result true:success, false:failed
     * @param[out] $response->error  error information
     * @param[out] $response->success associative array of system configure
     */
    public function execute(&$vars, &$response)
    {
        global $xoopsModule;

        // parameter check
        $error = &$response->getError();
        if (count($vars) > 1) {
            $error->add(XNPERR_EXTRA_PARAM);
        }
        if (count($vars) < 1) {
            $error->add(XNPERR_MISSING_PARAM);
        }

        if (isset($vars[0]) && strlen($vars[0]) > 32) {
            $error->add(XNPERR_INVALID_PARAM, 'too long parameter 1');
        }

        if ($error->get(0)) {
            // return if parameter error
            $response->setResult(false);

            return;
        } else {
            $sessionid = $vars[0];
        }
        list($result, $uid, $session) = $this->restoreSession($sessionid);
        if (!$result) {
            $response->setResult(false);
            $error->add(XNPERR_INVALID_SESSION);

            return false;
        }
        if ($uid == UID_GUEST) {
            $preference['private_index_number'] = 0;
            $preference['private_item_number'] = 0;
            $preference['private_item_storage'] = 0;
            $preference['private_index_number_limit'] = 0;
            $preference['private_item_number_limit'] = 0;
            $preference['private_item_storage_limit'] = 0;
        } else {
            $users_handler = &xoonips_getormhandler('xoonips', 'users');
            $user = $users_handler->get($uid);
            $preference = array();
            // count private_index_number
            $index_handler = &xoonips_getormhandler('xoonips', 'index');
            $preference['private_index_number'] = $index_handler->getCount(new Criteria('uid', $uid));
            // get private item ids. (private items = my all items - my certified items)
            $index_item_link_handler = &xoonips_getormhandler('xoonips', 'index_item_link');
            $private_iids = $index_item_link_handler->getPrivateItemIdsByUid($uid);
            // count private_item_number.
            $preference['private_item_number'] = count($private_iids);
            // count private_item_storage.
            $total_size = 0;
            $file_handler = &xoonips_getormhandler('xoonips', 'file');
            if (count($private_iids)) {
                $criteria = new CriteriaCompo(new Criteria('item_id', '('.implode(',', $private_iids).')', 'in'));
                $criteria->add(new Criteria('is_deleted', 0));
                $files = &$file_handler->getObjects($criteria);
                foreach ($files as $file) {
                    $total_size += $file->get('file_size');
                }
            }
            $preference['private_item_storage'] = $total_size / 1000.0;
            // user setting
            $preference['private_index_number_limit'] = $user->get('private_index_number_limit');
            $preference['private_item_number_limit'] = $user->get('private_item_number_limit');
            $preference['private_item_storage_limit'] = $user->get('private_item_storage_limit') / 1000.0; // in kilo bytes
        }
        $preference['maximum_filesize'] = $this->returnBytes(ini_get('upload_max_filesize')) / 1000; // in kilo bytes
        $preference['version'] = $xoopsModule->getVar('version');

        $response->setSuccess($preference);
        $response->setResult(true);

        return true;
    }
}
