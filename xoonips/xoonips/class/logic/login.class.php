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

require_once XOOPS_ROOT_PATH.'/modules/xoonips/class/base/transaction.class.php';
require_once XOOPS_ROOT_PATH.'/modules/xoonips/class/base/logic.class.php';
require_once XOOPS_ROOT_PATH.'/modules/xoonips/class/base/transaction.class.php';

/**
 * subclass of XooNIpsLogic(login).
 */
class XooNIpsLogicLogin extends XooNIpsLogic
{
    /**
     * execute login.
     *
     * @param[in]  $vars[0] id (use '' if guest login)
     * @param[in]  $vars[1] pass (use '' if guest login)
     * @param[out] $response->result true:success, false:failed
     * @param[out] $response->error error information
     * @param[out] $response->success session id
     *
     * @return null|boolean if error
     */
    public function execute(&$vars, &$response)
    {
        $myxoopsConfig = &xoonips_get_xoops_configs(XOOPS_CONF);
        // parameter check
        $error = &$response->getError();
        if (count($vars) > 2) {
            $error->add(XNPERR_EXTRA_PARAM);
        }
        if (count($vars) < 2) {
            $error->add(XNPERR_MISSING_PARAM);
        }

        if (isset($vars[0]) && strlen($vars[0]) > 25) {
            $error->add(XNPERR_INVALID_PARAM, 'too long parameter 1');
        }

        if ($error->get(0)) {
            // return if parameter error
            $response->setResult(false);

            return;
        } else {
            $id = $vars[0];
            $pass = $vars[1];
        }
        $member_handler = &xoonips_gethandler('xoonips', 'member');
        $user_handler = &xoonips_getormhandler('xoonips', 'users');
        $eventlog_handler = &xoonips_getormhandler('xoonips', 'event_log');
        $xconfig_handler = &xoonips_getormhandler('xoonips', 'config');
        $transaction = XooNIpsTransaction::getInstance();
        $transaction->start();
        if ($id == '') {
            $target_user = $xconfig_handler->getValue(XNP_CONFIG_PUBLIC_ITEM_TARGET_USER_KEY);
            if ($pass != '' || $target_user != XNP_CONFIG_PUBLIC_ITEM_TARGET_USER_ALL) {
                $transaction->rollback();
                $response->error->add(XNPERR_AUTH_FAILURE);
                $response->setResult(false);

                return false;
            }
            $user = false;
            $uid = UID_GUEST;
            $groups = array();
        } else {
            $user = &$member_handler->loginUser($id, $pass);
            if (!$user) {
                $transaction->rollback();
                // insert login failure event
                if (!$eventlog_handler->recordLoginFailureEvent($id)) {
                    $response->error->add(XNPERR_SERVER_ERROR, 'cannot insert event');
                }
                // return error
                $response->error->add(XNPERR_AUTH_FAILURE);
                $response->setResult(false);

                return false;
            }
            $xoonips_user = $user->getVar('xoonips_user');
            $uid = $xoonips_user->get('uid');
            $xoops_user_handler = &xoops_gethandler('user');
            $xoops_user = $xoops_user_handler->get($uid);
            if (0 == $xoops_user->getVar('level', 'n') || !$xoonips_user->get('activate')) { // not activated, not certified
                // return error
                $transaction->rollback();
                $response->error->add(XNPERR_AUTH_FAILURE);
                $response->setResult(false);

                return false;
            }
            $groups = $xoops_user->getGroups();
        }
        if ($myxoopsConfig['closesite'] == 1) {
            $allowed = false;
            if ($user) {
                foreach ($groups as $group) {
                    if (in_array($group, $myxoopsConfig['closesite_okgrp']) || XOOPS_GROUP_ADMIN == $group) {
                        $allowed = true;
                        break;
                    }
                }
            }
            if (!$allowed) {
                // site closed
                $transaction->rollback();
                $response->error->add(XNPERR_AUTH_FAILURE);
                $response->setResult(false);

                return false;
            }
        }
        // remove expired xoonips sessions
        $session_handler = &xoonips_getormhandler('xoonips', 'session');
        if (!$session_handler->gcSession()) {
            $transaction->rollback();
            $response->error->add(XNPERR_SERVER_ERROR, 'failed to gc session');
            $response->setResult(false);

            return false;
        }
        // record $uid
        $_SESSION = array();
        $_SESSION['xoopsUserId'] = $uid;
        $_SESSION['xoopsUserGroups'] = $groups;

        // set XNPSID(for old routines)
        $_SESSION['XNPSID'] = ($uid == UID_GUEST) ? SID_GUEST : session_id();

        if ($user) {
            // update last_login
            $xoops_user->setVar('last_login', time());
            if (!$xoops_user_handler->insert($xoops_user)) {
            }
            // init xoonips_session
            $session_handler->initSession($uid);
            // insert login event
            $eventlog_handler->recordLoginSuccessEvent($uid);
        }
        $transaction->commit();
        $response->setSuccess(session_id());
        $response->setResult(true);

        return true;
    }
}
