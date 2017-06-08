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

defined('XOOPS_ROOT_PATH') || exit('XOOPS root path not defined');

$formdata = &xoonips_getutility('formdata');
$uname = $formdata->getValue('post', 'uname', 's', false, '');
$pass = $formdata->getValue('post', 'pass', 's', false, '');
$xoops_redirect = $formdata->getValue('post', 'xoops_redirect', 's', false, '');
$redirect = !empty($xoops_redirect) ? '?xoops_redirect='.urlencode($xoops_redirect) : '';

$myts = &MyTextsanitizer::getInstance();

if ($uname == '' || $pass == '') {
    // Record events (login failure)
    $event_handler = &xoonips_getormhandler('xoonips', 'event_log');
    $event_handler->recordLoginFailureEvent($myts->stripSlashesGPC($uname));

    redirect_header(XOOPS_URL.'/modules/xoonips/user.php'.$redirect, 1, _US_INCORRECTLOGIN, false);
    exit();
}

$myxoopsConfig = &xoonips_get_xoops_configs(XOOPS_CONF);

$member_handler = &xoops_gethandler('member');
$user = &$member_handler->loginUser(addslashes($myts->stripSlashesGPC($uname)), $myts->stripSlashesGPC($pass));
if (false != $user) {
    if (0 == $user->getVar('level')) {
        redirect_header(XOOPS_URL.'/', 5, _US_NOACTTPADM);
        exit();
    }
    if ($myxoopsConfig['closesite'] == 1) {
        $allowed = false;
        foreach ($user->getGroups() as $group) {
            if (in_array($group, $myxoopsConfig['closesite_okgrp']) || XOOPS_GROUP_ADMIN == $group) {
                $allowed = true;
                break;
            }
        }
        if (!$allowed) {
            redirect_header(XOOPS_URL.'/', 1, _NOPERM);
            exit();
        }
    }
    $user->setVar('last_login', time());
    if (!$member_handler->insertUser($user)) {
    }
    include_once __DIR__.'/session.php';
    xoonips_session_regenerate();
    $_SESSION = array();
    $_SESSION['xoopsUserId'] = $user->getVar('uid');
    $_SESSION['xoopsUserGroups'] = $user->getGroups();
    if ($myxoopsConfig['use_mysession'] && $myxoopsConfig['session_name'] != '') {
        setcookie($myxoopsConfig['session_name'], session_id(), time() + (60 * $myxoopsConfig['session_expire']), '/', '', 0);
    }
    $user_theme = $user->getVar('theme');
    if (in_array($user_theme, $myxoopsConfig['theme_set_allowed'])) {
        $_SESSION['xoopsUserTheme'] = $user_theme;
    }
    if (!empty($xoops_redirect) && !strpos($xoops_redirect, 'registeruser')) {
        $parsed = parse_url(XOOPS_URL);
        $url = isset($parsed['scheme']) ? $parsed['scheme'].'://' : 'http://';
        if (isset($parsed['host'])) {
            $xoops_redirect = $formdata->getValue('post', 'xoops_redirect', 's', false);
            $url .= isset($parsed['port']) ? $parsed['host'].':'.$parsed['port'].trim($xoops_redirect) : $parsed['host'].$xoops_redirect;
        } else {
            $url .= xoops_getenv('HTTP_HOST').$xoops_redirect;
        }
    } else {
        $url = XOOPS_URL.'/';
    }

    // set cookie for autologin
    // if (!empty($_POST['rememberme'])) {
    //   $expire = time() + $myxoopsConfig['session_expire'] * 60;
    //   setcookie('autologin_uname', $uname, $expire, '/', '', 0);
    //   setcookie('autologin_pass', md5($pass), $expire, '/', '', 0);
    // }

    // RMV-NOTIFY
    // Perform some maintenance of notification records
    $notification_handler = &xoops_gethandler('notification');
    $notification_handler->doLoginMaintenance($user->getVar('uid'));

    // init xoonips session
    $uid = $user->getVar('uid', 'n');
    $session_handler = &xoonips_getormhandler('xoonips', 'session');
    $session_handler->initSession($uid);

    // validate xoonips user
    // if user is not xoonips user or not certified user then logout now
    $session_handler->validateUser($uid, true);

    // Record events(login success)
    $event_handler = &xoonips_getormhandler('xoonips', 'event_log');
    $event_handler->recordLoginSuccessEvent($uid);

    // garbage collect expired sessions
    $session_handler->gcSession();

    redirect_header($url, 1, sprintf(_US_LOGGINGU, $user->getVar('uname')));
} else {
    // Record events(login failure)
    $event_handler = &xoonips_getormhandler('xoonips', 'event_log');
    $event_handler->recordLoginFailureEvent($myts->stripSlashesGPC($uname));

    redirect_header(XOOPS_URL.'/modules/xoonips/user.php'.$redirect, 1, _US_INCORRECTLOGIN, false);
}
exit();
