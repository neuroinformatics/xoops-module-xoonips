<?php

// $Revision:$
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

$xoopsOption['pagetype'] = 'user';
require 'include/common.inc.php';
require_once 'include/notification.inc.php';
require_once 'class/base/gtickets.php';
require_once 'include/AL.php';
require_once 'include/lib.php'; // for xnpCleanup()
require_once 'class/base/gtickets.php';

$op = 'main';

$formdata = &xoonips_getutility('formdata');
$op = $formdata->getValue('both', 'op', 's', false, 'main');

$myxoopsConfig = &xoonips_get_xoops_configs(XOOPS_CONF);
$myxoopsConfigUser = &xoonips_get_xoops_configs(XOOPS_CONF_USER);

$textutil = &xoonips_getutility('text');

if ($op == 'main') {
    if (!$xoopsUser) {
        $xoopsOption['template_main'] = 'xoonips_userform.html';
        require XOOPS_ROOT_PATH.'/header.php';
        if (isset($_COOKIE[$myxoopsConfig['usercookie']])) {
            $xoopsTpl->assign('usercookie', $_COOKIE[$myxoopsConfig['usercookie']]);
        }
        $xoops_redirect = $formdata->getValue('get', 'xoops_redirect', 's', false);
        if (!is_null($xoops_redirect)) {
            $xoopsTpl->assign('redirect_page', $textutil->html_special_chars($xoops_redirect));
        }
        require XOOPS_ROOT_PATH.'/footer.php';
    } elseif ($xoopsUser) {
        header('Location: '.XOOPS_URL.'/modules/xoonips/showusers.php?uid='.$xoopsUser->getVar('uid'));
    }
    exit();
}

if ($op == 'login') {
    require_once XOOPS_ROOT_PATH.'/modules/xoonips/include/checklogin.php';
    exit();
}

if ($op == 'logout') {
    // logout of Platform
    if (isset($_SESSION['xoonips_old_uid'])) {
        redirect_header(XOOPS_URL.'/', 3, _MD_XOONIPS_SU_CANNOT_LOGOUT);
    }
    if (isset($_SESSION['xoopsUserId'])) {
        // reject if $_SESSION is empty
        // empty $_SESSION will be found if reload logout page
        $eventlog_handler = &xoonips_getormhandler('xoonips', 'event_log');
        $eventlog_handler->recordLogoutEvent($_SESSION['xoopsUserId']);
        // delete xoonips session
        $sess_id = session_id();
        $session_handler = &xoonips_getormhandler('xoonips', 'session');
        $session_obj = &$session_handler->get($sess_id);
        $session_handler->delete($session_obj, true); // force
    }

    // logout
    $message = '';
    $_SESSION = array();
    session_destroy();
    if ($myxoopsConfig['use_mysession'] && $myxoopsConfig['session_name'] != '') {
        setcookie($myxoopsConfig['session_name'], '', time() - 3600, '/', '', 0);
    }
    // clear autologin cookies
    //setcookie('autologin_uname', '', time() - 3600, '/', '', 0);
    //setcookie('autologin_pass', '', time() - 3600, '/', '', 0);
    // clear entry from online users table
    if (is_object($xoopsUser)) {
        $online_handler = &xoops_gethandler('online');
        $online_handler->destroy($xoopsUser->getVar('uid'));
    }

    // remove files which are not associated from any item and any session
    xnpCleanup();

    $message = _US_LOGGEDOUT.'<br />'._US_THANKYOUFORVISIT;
    redirect_header(XOOPS_URL.'/', 1, $message);

    exit();
}

if ($op == 'lostpass') {
    // page for password lost
    $xoopsOption['template_main'] = 'xoonips_userform.html';
    require XOOPS_ROOT_PATH.'/header.php';
    require XOOPS_ROOT_PATH.'/footer.php';
    exit();
}

// activate an account of user(uid=$id)
if ($op == 'actv') {
    if ($_SERVER['REQUEST_METHOD'] == 'GET') {
        $id = $formdata->getValue('get', 'id', 'i', true);
        $actkey = $formdata->getValue('get', 'actkey', 's', true);
        $xoopsOption['template_main'] = 'xoonips_activate_user.html';
        require XOOPS_ROOT_PATH.'/header.php';

        // token ticket
        $token_ticket = $xoopsGTicket->getTicketHtml(__LINE__, 1800, 'xoonips_activate_user');
        $xoopsTpl->assign('token_ticket', $token_ticket);

        $xoopsTpl->assign('op', $op);
        $xoopsTpl->assign('id', $id);
        $xoopsTpl->assign('actkey', $actkey);
        require XOOPS_ROOT_PATH.'/footer.php';
        exit();
    }
    if (!$xoopsGTicket->check(true, 'xoonips_activate_user', false)) {
        redirect_header(XOOPS_URL.'/', 3, $xoopsGTicket->getErrors());
        exit();
    }

    $id = $formdata->getValue('post', 'id', 'i', true);
    $actkey = $formdata->getValue('post', 'actkey', 's', true);
    $member_handler = &xoops_gethandler('member');
    $thisuser = &$member_handler->getUser($id);
    if (!is_object($thisuser)) {
        exit();
    }
    if ($thisuser->getVar('actkey') != $actkey) {
        redirect_header(XOOPS_URL.'/', 5, _US_ACTKEYNOT);
    } else {
        if ($thisuser->getVar('level') > 0) {
            redirect_header('user.php', 5, _US_ACONTACT);
        } else {
            if (false != $member_handler->activateUser($thisuser)) {
                $xconfig_handler = &xoonips_getormhandler('xoonips', 'config');
                $certify_user = $xconfig_handler->getValue('certify_user');
                $moderator_gid = $xconfig_handler->getValue('moderator_gid');
                require XOOPS_ROOT_PATH.'/header.php';
                // To send e-mail to activated user.
                if ($certify_user == 'on') {
                    // To send a e-mail to users who are belong to the group
                    // specified by moderator_gid if certify_user is 'manual'
                    xoonips_notification_account_certify_request($id);
                    if ($myxoopsConfigUser['activation_type'] == 2) {//activate by xoops admin & certify manual
                        echo _MD_XOONIPS_ACTIVATED_BY_ADMIN_AND_CERTIFY_MANUAL;
                    } elseif ($myxoopsConfigUser['activation_type'] == 0) {//activate by xoops by user & certify manual
                        echo _MD_XOONIPS_ACTIVATED_BY_USER_AND_CERTIFY_MANUAL;
                    }
                } else {
                    //certification automatically
                    xoonips_notification_account_certified($id);
                    if ($myxoopsConfigUser['activation_type'] == 2) { //activate xoops account by xoops administrator
                        // send e-mail to the registered address
                        // notify a completion of certification to the certified user by e-mail
                        $langman = &xoonips_getutility('languagemanager');
                        $xoopsMailer = &getMailer();
                        $xoopsMailer->useMail();
                        $xoopsMailer->setTemplateDir($langman->mail_template_dir());
                        $xoopsMailer->setTemplate('xoonips_account_certified.tpl');
                        $xoopsMailer->assign('SITENAME', $myxoopsConfig['sitename']);
                        $xoopsMailer->assign('ADMINMAIL', $myxoopsConfig['adminmail']);
                        $xoopsMailer->assign('SITEURL', XOOPS_URL.'/');
                        $xoopsMailer->setToUsers($thisuser);
                        $xoopsMailer->setFromEmail($myxoopsConfig['adminmail']);
                        $xoopsMailer->setFromName($myxoopsConfig['sitename']);
                        $xoopsMailer->setSubject(_MD_XOONIPS_ACCOUNT_CERTIFIED);
                        if ($xoopsMailer->send()) {
                            echo _MD_XOONIPS_ACTIVATED_BY_ADMIN_CERTIFIED_AUTO;
                        } else {
                            echo _MD_XOONIPS_ACTIVATED_BY_ADMIN_CERTIFIED_AUTO_MAILNG;
                        }
                    } else {
                        redirect_header('user.php', 5, _MD_XOONIPS_ACTIVATED_BY_USER_CERTIFIED_AUTO, false);
                    }
                }
                require XOOPS_ROOT_PATH.'/footer.php';
            } else {
                redirect_header(XOOPS_URL.'/', 3, 'Activation failed!');
            }
        }
    }
}

if ($op == 'delete_confirm') {
    $xnpsid = $_SESSION['XNPSID'];
    $uid = $_SESSION['xoopsUserId'];

    $xmember_handler = &xoonips_gethandler('xoonips', 'member');
    $xgroup_handler = &xoonips_gethandler('xoonips', 'group');
    if ($xmember_handler->isAdmin($uid) || $xmember_handler->isModerator($uid) || $xgroup_handler->isGroupAdmin($uid)) {
        redirect_header(XOOPS_URL."/modules/xoonips/userinfo.php?uid={$uid}", 3, _MD_XOONIPS_ACCOUNT_DELETE_IGNORE_USER);
        exit();
    }

    $index_item_link_handler = &xoonips_getormhandler('xoonips', 'index_item_link');
    if (count($index_item_link_handler->getNonPrivateItemIds($uid)) > 0) {
        redirect_header(XOOPS_URL."/modules/xoonips/userinfo.php?uid={$uid}", 3, _MD_XOONIPS_ACCOUNT_DELETE_ITEM_HANDOVER);
        exit();
    }

    require '../../header.php';
    $xoopsOption['template_main'] = 'xoonips_user_delete.html';

    $token_ticket = $xoopsGTicket->getTicketHtml(__LINE__, 1800, 'xoonips_certify_user_uncertfy');
    $xoopsTpl->assign('token_ticket', $token_ticket);
    $xoopsTpl->assign('delete_uid', $_SESSION['xoopsUserId']);
    require '../../footer.php';
    exit();
}

if ($op == 'delete') {
    $uid = $_SESSION['xoopsUserId'];

    if (!isset($_POST['is_exec'])) {
        redirect_header(XOOPS_URL."/modules/xoonips/userinfo.php?uid={$uid}", 3, _TAKINGBACK);
        exit();
    }

    // check token ticket
    if (!$xoopsGTicket->check(true, 'xoonips_certify_user_uncertfy')) {
        exit();
    }

    $user_compo_handler = &xoonips_getormcompohandler('xoonips', 'user');
    $user_compo_handler->deleteAccount($uid);

    $event_handler = &xoonips_getormhandler('xoonips', 'event_log');
    $event_handler->recordDeleteAccountEvent($uid);

    $config_handler = &xoonips_getormhandler('xoonips', 'config');
    $member_handler = &xoops_gethandler('member');
    $moderators = $member_handler->getUsersByGroup($config_handler->getValue('moderator_gid'), true);

    //
    // notify a delete account to moderator by e-mail
    //
    $langman = &xoonips_getutility('languagemanager');
    $xoopsMailer = &getMailer();
    $xoopsMailer->useMail();
    $xoopsMailer->setTemplateDir($langman->mail_template_dir());
    $xoopsMailer->setTemplate('xoonips_account_deleted.tpl');
    $xoopsMailer->assign('SITENAME', $xoopsConfig['sitename']);
    $xoopsMailer->assign('ADMINMAIL', $xoopsConfig['adminmail']);
    $xoopsMailer->assign('SITEURL', XOOPS_URL.'/');
    $xoopsMailer->setToUsers($moderators);
    $xoopsMailer->setFromEmail($xoopsConfig['adminmail']);
    $xoopsMailer->setFromName($xoopsConfig['sitename']);
    $xoopsMailer->setSubject(_MD_XOONIPS_ACCOUNT_DELETE_SUCCESS);

    $result_mail = $xoopsMailer->send();

    redirect_header(XOOPS_URL.'/modules/xoonips/user.php?op=logout', 3, _MD_XOONIPS_ACCOUNT_DELETE_SUCCESS);
    exit();
}
