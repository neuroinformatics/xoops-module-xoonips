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

$uid = is_object($xoopsUser) ? $xoopsUser->getVar('uid', 'n') : UID_GUEST;

if ($uid != UID_GUEST) {
    // deny to access from registered user
    redirect_header(XOOPS_URL.'/', 3, _NOPERM);
    exit();
}

$formdata = &xoonips_getutility('formdata');
$email = $formdata->getValue('both', 'email', 's', true);

$myxoopsConfig = &xoonips_get_xoops_configs(XOOPS_CONF);

$member_handler = &xoops_gethandler('member');
$getuser = &$member_handler->getUsers(new Criteria('email', addslashes($email)));

if (count($getuser) != 1) {
    redirect_header('user.php', 2, _US_SORRYNOTFOUND);
    exit();
}

$code = $formdata->getValue('get', 'code', 's', false);
$areyou = substr($getuser[0]->getVar('pass', 's'), 0, 5);
if (!is_null($code) && $areyou == $code) {
    $newpass = xoops_makepass();
    $xoopsMailer = &getMailer();
    $xoopsMailer->useMail();
    $xoopsMailer->setTemplate('lostpass2.tpl');
    $xoopsMailer->assign('SITENAME', $myxoopsConfig['sitename']);
    $xoopsMailer->assign('ADMINMAIL', $myxoopsConfig['adminmail']);
    $xoopsMailer->assign('SITEURL', XOOPS_URL.'/');
    $xoopsMailer->assign('IP', $_SERVER['REMOTE_ADDR']);
    $xoopsMailer->assign('NEWPWD', $newpass);
    $xoopsMailer->setToUsers($getuser[0]);
    $xoopsMailer->setFromEmail($myxoopsConfig['adminmail']);
    $xoopsMailer->setFromName($myxoopsConfig['sitename']);
    $xoopsMailer->setSubject(sprintf(_US_NEWPWDREQ, XOOPS_URL));
    if (!$xoopsMailer->send()) {
        echo $xoopsMailer->getErrors();
    }
    // Next step: add the new password to the database
    $sql = sprintf('UPDATE `%s` SET `pass`=%s WHERE `uid`=%u', $xoopsDB->prefix('users'), $xoopsDB->quoteString(md5($newpass)), $getuser[0]->getVar('uid', 's'));
    if (!$xoopsDB->queryF($sql)) {
        require XOOPS_ROOT_PATH.'/header.php';
        echo _US_MAILPWDNG;
        require XOOPS_ROOT_PATH.'/footer.php';
        exit();
    }
    redirect_header('user.php', 3, sprintf(_US_PWDMAILED, $getuser[0]->getVar('uname')), false);
    exit();
} else {
    // If no validation code, send it
    if (!is_null($code)) {
        // if invalid code send, die process
        // die( 'invalid code request' );
        die('Your new password has been send to your email address. Please check your email again.');
    }
    $xoopsMailer = &getMailer();
    $xoopsMailer->useMail();
    $xoopsMailer->setTemplate('lostpass1.tpl');
    $xoopsMailer->assign('SITENAME', $myxoopsConfig['sitename']);
    $xoopsMailer->assign('ADMINMAIL', $myxoopsConfig['adminmail']);
    $xoopsMailer->assign('SITEURL', XOOPS_URL.'/');
    $xoopsMailer->assign('IP', $_SERVER['REMOTE_ADDR']);
    $xoopsMailer->assign('NEWPWD_LINK', XOOPS_URL.'/modules/xoonips/lostpass.php?email='.$email.'&code='.$areyou);
    $xoopsMailer->setToUsers($getuser[0]);
    $xoopsMailer->setFromEmail($myxoopsConfig['adminmail']);
    $xoopsMailer->setFromName($myxoopsConfig['sitename']);
    $xoopsMailer->setSubject(sprintf(_US_NEWPWDREQ, $myxoopsConfig['sitename']));
    require XOOPS_ROOT_PATH.'/header.php';
    if (!$xoopsMailer->send()) {
        echo $xoopsMailer->getErrors();
    }
    echo '<h4>';
    printf(_US_CONFMAIL, $getuser[0]->getVar('uname'));
    echo '</h4>';
    require XOOPS_ROOT_PATH.'/footer.php';
}
