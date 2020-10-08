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
 * subclass of XooNIpsLogic(getRootIndex).
 */
class XooNIpsLogicGetRootIndex extends XooNIpsLogic
{
    /**
     * execute getRootIndex.
     *
     * @param[in]  $vars[0] session ID
     * @param[in]  $vars[1] name of root index('Public'|'Private'|group name)
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
        }
        if (count($vars) < 2) {
            $error->add(XNPERR_MISSING_PARAM);
        }

        if (isset($vars[0]) && strlen($vars[0]) > 32) {
            $error->add(XNPERR_INVALID_PARAM, 'too long parameter 1');
        }
        if (isset($vars[1]) && strlen($vars[1]) > 255) {
            $error->add(XNPERR_INVALID_PARAM, 'too long parameter 2');
        }

        if ($error->get(0)) {
            // return if parameter error
            $response->setResult(false);

            return;
        } else {
            $sessionid = $vars[0];
            $name = $vars[1];
        }
        list($result, $uid, $session) = $this->restoreSession($sessionid);
        if (!$result) {
            $response->setResult(false);
            $error->add(XNPERR_INVALID_SESSION);

            return false;
        }
        // get index_id from name
        if ('Public' == $name) { // todo: define constant string
            $index_id = IID_PUBLIC;
        } elseif ('Private' == $name) { // todo: define constant string
            if (UID_GUEST == $uid) {
                $response->setResult(false);
                $error->add(XNPERR_ACCESS_FORBIDDEN, 'guest doesn\'t have private index');

                return false;
            }
            $users_handler = &xoonips_getormhandler('xoonips', 'users');
            $user = $users_handler->get($uid);
            $index_id = $user->get('private_index_id');
        } else {
            $groups_handler = &xoonips_getormhandler('xoonips', 'groups');
            $groups = &$groups_handler->getObjects(new Criteria('gname', addslashes($name)));
            if (!$groups || 1 != count($groups)) {
                $response->setResult(false);
                $error->add(XNPERR_NOT_FOUND, 'group not found');

                return false;
            }
            $index_id = $groups[0]->get('group_index_id');
        }
        // check permission
        $index_handler = &xoonips_getormhandler('xoonips', 'index');
        if (!$index_handler->getPerm($index_id, $uid, 'read')) {
            $response->setResult(false);
            $error->add(XNPERR_ACCESS_FORBIDDEN, 'no permission');

            return false;
        }
        // get index from index_id
        $index_compo_handler = &xoonips_getormcompohandler('xoonips', 'index');
        $index = $index_compo_handler->get($index_id);
        if (false == $index) {
            $response->setResult(false);
            $error->add(XNPERR_NOT_FOUND, 'cannot get index');

            return false;
        }
        $response->setSuccess($index);
        $response->setResult(true);

        return true;
    }
}
