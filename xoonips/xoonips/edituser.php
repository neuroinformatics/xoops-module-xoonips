<?php

// $Revision: 1.20.4.1.2.17 $
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
require 'class/base/gtickets.php';
require_once XOOPS_ROOT_PATH.'/class/xoopsformloader.php';

require_once 'include/lib.php';
require_once 'include/AL.php';

$xnpsid = $_SESSION['XNPSID'];

xoonips_deny_guest_access();

$myuid = is_object($xoopsUser) ? $xoopsUser->getVar('uid', 'n') : UID_GUEST;

$formdata = &xoonips_getutility('formdata');
$uid = $formdata->getValue('both', 'uid', 'i', false, $myuid);
if ($myuid == UID_GUEST) {
    // user id not selected
    redirect_header(XOOPS_URL.'/', 3, _US_SELECTNG);
    exit();
}

//Uncertified user can't access
if (!xnp_is_activated($xnpsid, $uid)) {
    redirect_header(XOOPS_URL.'/', 3, _MD_XOONIPS_ACCOUNT_NOT_ACTIVATED);
    exit();
}

// private function
function getUserPosiList()
{
    $posi_handler = &xoonips_getormhandler('xoonips', 'positions');
    $criteria = new CriteriaElement();
    $criteria->setSort('posi_order');
    $criteria->setOrder(ASC);
    $posi_objs = &$posi_handler->getObjects($criteria, false, 'posi_id, posi_title');
    $ret = array();
    foreach ($posi_objs as $posi_obj) {
        $posi_id = $posi_obj->getVar('posi_id', 's');
        $posi_title = $posi_obj->getVar('posi_title', 'e');
        $ret[$posi_id] = $posi_title;
    }

    return $ret;
}

// initialize variable
$op = $formdata->getValue('both', 'op', 's', false, 'editprofile');

$xmember_handler = &xoonips_gethandler('xoonips', 'member');
$is_admin = $xmember_handler->isAdmin($myuid);
$is_moderator = $xmember_handler->isModerator($myuid);

if (!$is_admin && !$is_moderator && $myuid != $uid) {
    redirect_header(XOOPS_URL.'/', 3, _US_NOEDITRIGHT);
    exit();
}

$myxoopsConfig = &xoonips_get_xoops_configs(XOOPS_CONF);
$myxoopsConfigUser = &xoonips_get_xoops_configs(XOOPS_CONF_USER);

$xconfig_handler = &xoonips_getormhandler('xoonips', 'config');
$xconfig_keys = array(
    'realname' => _US_REALNAME,
    'address' => _MD_XOONIPS_ACCOUNT_ADDRESS,
    'company_name' => _MD_XOONIPS_ACCOUNT_COMPANY_NAME,
    'division' => _MD_XOONIPS_ACCOUNT_DIVISION,
    'tel' => _MD_XOONIPS_ACCOUNT_TEL,
    'country' => _MD_XOONIPS_ACCOUNT_COUNTRY,
    'zipcode' => _MD_XOONIPS_ACCOUNT_ZIPCODE,
    'fax' => _MD_XOONIPS_ACCOUNT_FAX,
);
$xconfig_vars = array();
foreach ($xconfig_keys as $key => $label) {
    $xconfig_vars[$key] = $xconfig_handler->getValue('account_'.$key.'_optional');
}

$u_handler = &xoonips_getormhandler('xoonips', 'xoops_users');
$xu_handler = &xoonips_getormhandler('xoonips', 'users');
$u_obj = &$u_handler->get($uid);
$xu_obj = &$xu_handler->get($uid);
if (!is_object($u_obj) || !is_object($xu_obj)) {
    // user not found
    redirect_header(XOOPS_URL.'/', 3, _US_SELECTNG);
    exit();
}

$errors = array();

if ($op == 'saveuser') {
    if (!$xoopsGTicket->check(true, 'saveuser', false)) {
        redirect_header(XOOPS_URL.'/', 3, $xoopsGTiket->getErrors());
        exit();
    }

    $request_vars = array(
        // xoops user information
        'realname' => array('s', true),
        'email' => array('s', false),
        'url' => array('s', true),
        'user_sig' => array('s', true),
        'user_viewemail' => array('i', false),
        'password' => array('s', true),
        'vpass' => array('s', true),
        'attachsig' => array('i', false),
        'timezone_offset' => array('f', true),
        'umode' => array('s', true),
        'uorder' => array('i', true),
        'notify_method' => array('i', true),
        'notify_mode' => array('i', true),
        'user_intrest' => array('s', true),
        'user_mailok' => array('i', true),
        // xoonips user information
        'address' => array('s', true),
        'company_name' => array('s', true),
        'division' => array('s', true),
        'tel' => array('s', true),
        'country' => array('s', true),
        'zipcode' => array('s', true),
        'fax' => array('s', true),
        'notice_mail' => array('i', true),
        'posi' => array('i', true),
        'appeal' => array('s', true),
        // others
        'usecookie' => array('i', false),
    );
    foreach ($request_vars as $key => $meta) {
        list($type, $is_required) = $meta;
        $$key = $formdata->getValue('post', $key, $type, $is_required);
    }

    if ($myxoopsConfigUser['allow_chgmail'] == 1) {
        if (is_null($email) || $email == '' || !checkEmail($email)) {
            $errors[] = _US_INVALIDMAIL;
        }
    }
    if ($vpass != '' && $password != $vpass) {
        $errors[] = _US_PASSNOTSAME;
    }
    if ($password != '' && strlen($password) < $myxoopsConfigUser['minpass']) {
        $errors[] = sprintf(_US_PWDTOOSHORT, $myxoopsConfigUser['minpass']);
    }
    if ($notice_mail < 0) {
        $errors[] = _MD_XOONIPS_ACCOUNT_NOTICE_MAIL_TOO_LITTLE;
    }

    // acquire required flags of XooNIps user information
    $val = '';
    $required = array();
    foreach ($xconfig_keys as $key => $label) {
        if ($xconfig_vars[$key] != 'on' && ${$key} == '') {
            $errors[] = sprintf(_MD_XOONIPS_ACCOUNT_MUST_BE_FILLED_IN, $label);
        }
    }

    if (count($errors) > 0) {
        $op = 'editprofile'; // TODO: check here
    } else {
        // set new values
        // - xoops user information
        $u_obj->setVar('name', $realname, true); // not gpc
        if ($myxoopsConfigUser['allow_chgmail'] == 1) {
            $u_obj->setVar('email', $email, true); // not gpc
        }
        $u_obj->setVar('url', formatURL($url), true); // not gpc
        $u_obj->setVar('user_sig', xoops_substr($user_sig, 0, 255), true); // not gpc
        $user_viewemail = empty($user_viewemail) ? 0 : 1;
        $u_obj->setVar('user_viewemail', $user_viewemail, true); // not gpc
        if ($vpass != '') {
            $u_obj->setVar('pass', md5($password), true); // not gpc
        }
        $attachsig = empty($attachsig) ? 0 : 1;
        $u_obj->setVar('attachsig', $attachsig, true); // not gpc
        $u_obj->setVar('timezone_offset', $timezone_offset, true); // not gpc
        $u_obj->setVar('uorder', $uorder, true); // not gpc
        $u_obj->setVar('umode', $umode, true); // not gpc
        $u_obj->setVar('notify_method', $notify_method, true); // not gpc
        $u_obj->setVar('notify_mode', $notify_mode, true); // not gpc
        $u_obj->setVar('user_intrest', $user_intrest, true); // not gpc
        $u_obj->setVar('user_mailok', $user_mailok, true); // not gpc
        if ($myuid == $uid) {
            // set cookie if editing user information is mine
            if (!empty($usecookie)) {
                $uname = $u_obj->getVar('uname', 's');
                setcookie($myxoopsConfig['usercookie'], $uname, time() + 31536000, '/');
            } else {
                setcookie($myxoopsConfig['usercookie'], '', 0, '/');
            }
        }
        // - xoonips user information
        $xu_obj->set('address', $address);
        $xu_obj->set('company_name', $company_name);
        $xu_obj->set('division', $division);
        $xu_obj->set('tel', $tel);
        $xu_obj->set('country', $country);
        $xu_obj->set('zipcode', $zipcode);
        $xu_obj->set('fax', $fax);
        $xu_obj->set('notice_mail', $notice_mail);
        $xu_obj->set('notice_mail_since', time());
        $xu_obj->set('posi', $posi);
        $xu_obj->set('appeal', $appeal);

        $error = '';
        if (!$u_handler->insert($u_obj)) {
            $error = $u_obj->getHtmlErrors();
        }
        if (!$xu_handler->insert($xu_obj)) {
            $error .= $xu_obj->getHtmlErrors();
        }
        if (empty($error)) {
            redirect_header('showusers.php?uid='.$uid, 0, _US_PROFUPDATED);
        } else {
            include XOOPS_ROOT_PATH.'/header.php';
            echo $error;
            include XOOPS_ROOT_PATH.'/footer.php';
        }
        exit();
    }
}

if ($op == 'editprofile') {
    include_once XOOPS_ROOT_PATH.'/header.php';
    include_once XOOPS_ROOT_PATH.'/include/xoopscodes.php';
    include_once XOOPS_ROOT_PATH.'/include/comment_constants.php';
    // RMV-NOTIFY
    $langman = &xoonips_getutility('languagemanager');
    $langman->read_pagetype('notification.php');
    include_once XOOPS_ROOT_PATH.'/include/notification_constants.php';

    // required mark, and required flag
    $required = array();
    foreach ($xconfig_keys as $key => $label) {
        if ($xconfig_vars[$key] != 'on') {
            $required[$key]['mark'] = _MD_XOONIPS_ACCOUNT_REQUIRED_MARK;
            $required[$key]['flag'] = true;
        } else {
            $required[$key]['mark'] = '';
            $required[$key]['flag'] = false;
        }
    }

    // TODO: check pankuzu
    echo '<p>';
    echo '<a href="showusers.php?uid='.$uid.'">'._MD_XOONIPS_SHOW_USER_TITLE.'</a>';
    echo _MI_XOONIPS_ACCOUNT_PANKUZU_SEPARATOR._US_EDITPROFILE;
    echo '</p><br />';

    // show error message if error occured in $op = 'saveuser'
    if (count($errors) > 0) {
        echo '<div style="margin: 10px;">';
        foreach ($errors as $err) {
            echo '<div style="color: #ff0000; font-weight: bold;">'.$err.'</div>';
        }
        echo '</div>';
    }

    $form = new XoopsThemeForm(_US_EDITPROFILE, 'userinfo', 'edituser.php');
    // uname
    $uname_label = new XoopsFormLabel(_US_NICKNAME._MD_XOONIPS_ACCOUNT_REQUIRED_MARK, $u_obj->getVar('uname', 's'));
    $form->addElement($uname_label);
    // name
    $name_text = new XoopsFormText(_US_REALNAME.$required['realname']['mark'], 'realname', 30, 60, $u_obj->getVar('name', 'e'));
    $form->addElement($name_text, $required['realname']['flag']);

    // email
    $email_tray = new XoopsFormElementTray(_US_EMAIL._MD_XOONIPS_ACCOUNT_REQUIRED_MARK, '<br />');
    if ($myxoopsConfigUser['allow_chgmail'] == 1) {
        $email_text = new XoopsFormText('', 'email', 30, 60, $u_obj->getVar('email', 's'));
    } else {
        $email_text = new XoopsFormLabel('', $u_obj->getVar('email', 's'));
    }
    $email_tray->addElement($email_text);
    $email_cbox_value = $u_obj->getVar('user_viewemail', 's') ? 1 : 0;
    $email_cbox = new XoopsFormCheckBox('', 'user_viewemail', $email_cbox_value);
    $email_cbox->addOption(1, _US_ALLOWVIEWEMAIL);
    $email_tray->addElement($email_cbox);
    $form->addElement($email_tray);
    if ($myxoopsConfigUser['allow_chgmail'] == 1) {
        $form->setRequired($email_text);
    }

    // url
    $url_text = new XoopsFormText(_US_WEBSITE, 'url', 30, 100, $u_obj->getVar('url', 'e'));
    $form->addElement($url_text);

    // posi
    $posi_select = new XoopsFormSelect(_MD_XOONIPS_ACCOUNT_POSITION, 'posi', $xu_obj->getVar('posi', 'e'));
    $posi_list = getUserPosiList();
    $posi_select->addOption(0, '--------------');
    if (count($posi_list) > 0) {
        $posi_select->addOptionArray($posi_list);
    }
    $form->addElement($posi_select);

    // company_name
    $company_text = new XoopsFormText(_MD_XOONIPS_ACCOUNT_COMPANY_NAME.$required['company_name']['mark'], 'company_name', 50, 255, $xu_obj->getVar('company_name', 'e'));
    $form->addElement($company_text, $required['company_name']['flag']);

    // division
    $division_text = new XoopsFormText(_MD_XOONIPS_ACCOUNT_DIVISION.$required['division']['mark'], 'division', 50, 255, $xu_obj->getVar('division', 'e'));
    $form->addElement($division_text, $required['division']['flag']);

    // tel
    $tel_text = new XoopsFormText(_MD_XOONIPS_ACCOUNT_TEL.$required['tel']['mark'], 'tel', 25, 32, $xu_obj->getVar('tel', 'e'));
    $form->addElement($tel_text, $required['tel']['flag']);

    // fax
    $fax_text = new XoopsFormText(_MD_XOONIPS_ACCOUNT_FAX.$required['fax']['mark'], 'fax', 25, 32, $xu_obj->getVar('fax', 'e'));
    $form->addElement($fax_text, $required['fax']['flag']);

    // address
    $address_text = new XoopsFormText(_MD_XOONIPS_ACCOUNT_ADDRESS.$required['address']['mark'], 'address', 50, 255, $xu_obj->getVar('address', 'e'));
    $form->addElement($address_text, $required['address']['flag']);

    // country
    $country_text = new XoopsFormText(_MD_XOONIPS_ACCOUNT_COUNTRY.$required['country']['mark'], 'country', 25, 255, $xu_obj->getVar('country', 'e'));
    $form->addElement($country_text, $required['country']['flag']);

    // zipcode
    $zipcode_text = new XoopsFormText(_MD_XOONIPS_ACCOUNT_ZIPCODE.$required['zipcode']['mark'], 'zipcode', 20, 32, $xu_obj->getVar('zipcode', 'e'));
    $form->addElement($zipcode_text, $required['zipcode']['flag']);

    // timezone_offset
    $timezone_select = new XoopsFormSelectTimezone(_US_TIMEZONE, 'timezone_offset', $u_obj->getVar('timezone_offset', 'e'));
    $form->addElement($timezone_select);

    // user_intrest
    $interest_text = new XoopsFormText(_US_INTEREST, 'user_intrest', 30, 150, $u_obj->getVar('user_intrest', 'e'));
    $form->addElement($interest_text);

    // appeal
    $app_tray = new XoopsFormElementTray(_MD_XOONIPS_ACCOUNT_APPEAL, '<br />');
    $app_tarea = new XoopsFormTextArea('', 'appeal', $xu_obj->getVar('appeal', 'e'), 5, 50, 'u_appeal');
    $app_tray->addElement($app_tarea);
    $form->addElement($app_tray);

    // user_sig
    $sig_tray = new XoopsFormElementTray(_US_SIGNATURE, '<br />');
    $sig_tarea = new XoopsFormTextArea('', 'user_sig', $u_obj->getVar('user_sig', 'e'));
    $sig_tray->addElement($sig_tarea);
    $sig_cbox_value = $u_obj->getVar('attachsig', 's') ? 1 : 0;
    $sig_cbox = new XoopsFormCheckBox('', 'attachsig', $sig_cbox_value);
    $sig_cbox->addOption(1, _US_SHOWSIG);
    $sig_tray->addElement($sig_cbox);
    $form->addElement($sig_tray);

    // password & vpass
    $pwd_text = new XoopsFormPassword('', 'password', 10, 32);
    $pwd_text2 = new XoopsFormPassword('', 'vpass', 10, 32);
    $pwd_tray = new XoopsFormElementTray(_US_PASSWORD.'<br />'._US_TYPEPASSTWICE);
    $pwd_tray->addElement($pwd_text);
    $pwd_tray->addElement($pwd_text2);
    $form->addElement($pwd_tray);

    // notice mail
    $notice_mail_text = new XoopsFormText(_MD_XOONIPS_ACCOUNT_NOTICE_MAIL, 'notice_mail', 5, 10, $xu_obj->getVar('notice_mail', 'e'));
    $form->addElement($notice_mail_text);

    // usercookie
    if ($uid == $myuid) {
        $cookie_radio_value = empty($_COOKIE[$myxoopsConfig['usercookie']]) ? 0 : 1;
        $cookie_radio = new XoopsFormRadioYN(_US_USECOOKIE, 'usecookie', $cookie_radio_value, _YES, _NO);
        $form->addElement($cookie_radio);
    }

    // user_mailok
    $mailok_radio = new XoopsFormRadioYN(_US_MAILOK, 'user_mailok', $u_obj->getVar('user_mailok', 'e'));
    $form->addElement($mailok_radio);

    // umode
    $umode_select = new XoopsFormSelect(_US_CDISPLAYMODE, 'umode', $u_obj->getVar('umode', 'e'));
    $umode_select->addOptionArray(array('nest' => _NESTED, 'flat' => _FLAT, 'thread' => _THREADED));
    $form->addElement($umode_select);

    // uorder
    $uorder_select = new XoopsFormSelect(_US_CSORTORDER, 'uorder', $u_obj->getVar('uorder', 'e'));
    $uorder_select->addOptionArray(array(XOOPS_COMMENT_OLD1ST => _OLDESTFIRST, XOOPS_COMMENT_NEW1ST => _NEWESTFIRST));
    $form->addElement($uorder_select);

    // notify method
    $notify_method_select = new XoopsFormSelect(_NOT_NOTIFYMETHOD, 'notify_method', $u_obj->getVar('notify_method', 'e'));
    $notify_method_select->addOptionArray(array(XOOPS_NOTIFICATION_METHOD_DISABLE => _NOT_METHOD_DISABLE, XOOPS_NOTIFICATION_METHOD_PM => _NOT_METHOD_PM, XOOPS_NOTIFICATION_METHOD_EMAIL => _NOT_METHOD_EMAIL));
    $form->addElement($notify_method_select);

    // notify mode
    $notify_mode_select = new XoopsFormSelect(_NOT_NOTIFYMODE, 'notify_mode', $u_obj->getVar('notify_mode', 'e'));
    $notify_mode_select->addOptionArray(array(XOOPS_NOTIFICATION_MODE_SENDALWAYS => _NOT_MODE_SENDALWAYS, XOOPS_NOTIFICATION_MODE_SENDONCETHENDELETE => _NOT_MODE_SENDONCE, XOOPS_NOTIFICATION_MODE_SENDONCETHENWAIT => _NOT_MODE_SENDONCEPERLOGIN));
    $form->addElement($notify_mode_select);

    // uid
    $uid_hidden = new XoopsFormHidden('uid', $uid);
    $form->addElement($uid_hidden);

    // op
    $op_hidden = new XoopsFormHidden('op', 'saveuser');
    $form->addElement($op_hidden);

    // token ticket
    $xoopsGTicket->addTicketXoopsFormElement($form, __LINE__, 1800, 'saveuser');

    // submit button
    $submit_button = new XoopsFormButton('', 'submit', _US_SAVECHANGES, 'submit');
    $form->addElement($submit_button);

    //set accept-charset attribute if Safari on Mac OS
    $form->setExtra(xnpGetMacSafariAcceptCharset());

    // show form
    $form->display();

    include XOOPS_ROOT_PATH.'/footer.php';
    exit();
}

if ($op == 'avatarform') {
    include XOOPS_ROOT_PATH.'/header.php';
    echo '<a href="showusers.php?uid='.$uid.'">'._MD_XOONIPS_SHOW_USER_TITLE.'</a>&nbsp;<span style="font-weight:bold;">&raquo;&raquo;</span>&nbsp;'._US_UPLOADMYAVATAR.'<br /><br />';
    $oldavatar = $u_obj->getVar('user_avatar', 's');
    if (!empty($oldavatar) && $oldavatar != 'blank.gif') {
        echo '<div style="text-align:center;"><h4 style="color:#ff0000; font-weight:bold;">'._US_OLDDELETED.'</h4>';
        echo '<img src="'.XOOPS_UPLOAD_URL.'/'.$oldavatar.'" alt="oldavatar" /></div>';
    }
    if ($myxoopsConfigUser['avatar_allow_upload'] == 1 && $u_obj->getVar('posts', 's') >= $myxoopsConfigUser['avatar_minposts']) {
        $form = new XoopsThemeForm(_US_UPLOADMYAVATAR, 'uploadavatar', 'edituser.php');
        $form->setExtra('enctype="multipart/form-data"');
        $form->addElement(new XoopsFormLabel(_US_MAXPIXEL, $myxoopsConfigUser['avatar_width'].' x '.$myxoopsConfigUser['avatar_height']));
        $form->addElement(new XoopsFormLabel(_US_MAXIMGSZ, $myxoopsConfigUser['avatar_maxsize']));
        $form->addElement(new XoopsFormFile(_US_SELFILE, 'avatarfile', $myxoopsConfigUser['avatar_maxsize']), true);
        $form->addElement(new XoopsFormHidden('op', 'avatarupload'));
        $xoopsGTicket->addTicketXoopsFormElement($form, __LINE__, 1800, 'avatarupload');
        $form->addElement(new XoopsFormHidden('uid', $uid));
        $form->addElement(new XoopsFormButton('', 'submit', _SUBMIT, 'submit'));
        $form->display();
    }

    $avatar_handler = &xoops_gethandler('avatar');
    $form2 = new XoopsThemeForm(_US_CHOOSEAVT, 'uploadavatar', 'edituser.php');
    $avatar_select = new XoopsFormSelect('', 'user_avatar', $xoopsUser->getVar('user_avatar'));
    $avatar_select->addOptionArray($avatar_handler->getList('S'));
    $avatar_select->setExtra('onchange="showImgSelected( \'avatar\', \'user_avatar\', \'uploads\', \'\', \''.XOOPS_URL.'\')"');
    $avatar_tray = new XoopsFormElementTray(_US_AVATAR, '&nbsp;');
    $avatar_tray->addElement($avatar_select);
    $avatar_tray->addElement(new XoopsFormLabel('', '<img src="'.XOOPS_UPLOAD_URL.'/'.$u_obj->getVar('user_avatar', 'e').'" name="avatar" id="avatar" alt="avatar"/><a href="javascript:openWithSelfMain(\''.XOOPS_URL.'/misc.php?action=showpopups&amp;type=avatars\',\'avatars\',600,400);">'._LIST.'</a>'));
    $form2->addElement($avatar_tray);
    $form2->addElement(new XoopsFormHidden('uid', $uid));
    $form2->addElement(new XoopsFormHidden('op', 'avatarchoose'));
    $xoopsGTicket->addTicketXoopsFormElement($form2, __LINE__, 1800, 'avatarchoose');
    $form2->addElement(new XoopsFormButton('', 'submit2', _SUBMIT, 'submit'));
    $form2->display();
    include XOOPS_ROOT_PATH.'/footer.php';
    exit();
}

if ($op == 'avatarupload') {
    if (!$xoopsGTicket->check(true, 'avatarupload', false)) {
        redirect_header(XOOPS_URL.'/', 3, $xoopsGTiket->getErrors());
        exit();
    }
    if ($myxoopsConfigUser['avatar_allow_upload'] == 1 && $u_obj->getVar('posts', 's') >= $myxoopsConfigUser['avatar_minposts']) {
        include_once XOOPS_ROOT_PATH.'/class/uploader.php';
        $uploader = new XoopsMediaUploader(XOOPS_UPLOAD_PATH, array('image/gif', 'image/jpeg', 'image/pjpeg', 'image/x-png', 'image/png'), $myxoopsConfigUser['avatar_maxsize'], $myxoopsConfigUser['avatar_width'], $myxoopsConfigUser['avatar_height']);
        $uploader->setAllowedExtensions(array('gif', 'jpeg', 'jpg', 'png'));
        $xoops_upload_file = $formdata->getValueArray('post', 'xoops_upload_file', 's', true);
        if ($uploader->fetchMedia($xoops_upload_file[0])) {
            $uploader->setPrefix('cavt');
            if ($uploader->upload()) {
                $avt_handler = &xoops_gethandler('avatar');
                $avatar = &$avt_handler->create();
                $avatar->setVar('avatar_file', $uploader->getSavedFileName());
                $avatar->setVar('avatar_name', $u_obj->getVar('uname', 'n'), true); // not gpc
                $avatar->setVar('avatar_mimetype', $uploader->getMediaType());
                $avatar->setVar('avatar_display', 1);
                $avatar->setVar('avatar_type', 'C');
                if (!$avt_handler->insert($avatar)) {
                    @unlink($uploader->getSavedDestination());
                } else {
                    $oldavatar = $u_obj->getVar('user_avatar', 's');
                    if (!empty($oldavatar) && $oldavatar != 'blank.gif' && !preg_match('/^savt/', strtolower($oldavatar))) {
                        $avatars = &$avt_handler->getObjects(new Criteria('avatar_file', $oldavatar));
                        $avt_handler->delete($avatars[0]);
                        $oldavatar_path = str_replace('\\', '/', realpath(XOOPS_UPLOAD_PATH.'/'.$oldavatar));
                        if (0 === strpos($oldavatar_path, XOOPS_UPLOAD_PATH) && is_file($oldavatar_path)) {
                            unlink($oldavatar_path);
                        }
                    }
                    $u_obj->setVar('user_avatar', $uploader->getSavedFileName(), true); // not gpc
                    $u_handler->insert($u_obj);
                    $avt_handler->addUser($avatar->getVar('avatar_id'), $uid);
                    redirect_header('showusers.php', 0, _US_PROFUPDATED);
                    exit();
                }
            }
        }
        include XOOPS_ROOT_PATH.'/header.php';
        echo $uploader->getErrors();
        include XOOPS_ROOT_PATH.'/footer.php';
        exit();
    }
}

if ($op == 'avatarchoose') {
    if (!$xoopsGTicket->check(true, 'avatarchoose', false)) {
        redirect_header(XOOPS_URL.'/', 3, $xoopsGTicket->getErrors());
        exit();
    }
    $user_avatar = $formdata->getValue('post', 'user_avatar', 's', true);
    $user_avatarpath = str_replace('\\', '/', realpath(XOOPS_UPLOAD_PATH.'/'.$user_avatar));
    if (0 === strpos($user_avatarpath, XOOPS_UPLOAD_PATH) && is_file($user_avatarpath)) {
        $oldavatar = $u_obj->getVar('user_avatar', 's');
        $u_obj->setVar('user_avatar', $user_avatar, true);
        if (!$u_handler->insert($u_obj)) {
            include XOOPS_ROOT_PATH.'/header.php';
            echo $u_obj->getHtmlErrors();
            include XOOPS_ROOT_PATH.'/footer.php';
            exit();
        }
        $avt_handler = &xoops_gethandler('avatar');
        if ($oldavatar && $oldavatar != 'blank.gif' && !preg_match('/^savt/', strtolower($oldavatar))) {
            $avatars = &$avt_handler->getObjects(new Criteria('avatar_file', $oldavatar));
            if (is_object($avatars[0])) {
                $avt_handler->delete($avatars[0]);
            }
            $oldavatar_path = str_replace('\\', '/', realpath(XOOPS_UPLOAD_PATH.'/'.$oldavatar));
            if (0 === strpos($oldavatar_path, XOOPS_UPLOAD_PATH) && is_file($oldavatar_path)) {
                unlink($oldavatar_path);
            }
        }
        if ($user_avatar != 'blank.gif') {
            $avatars = &$avt_handler->getObjects(new Criteria('avatar_file', $user_avatar));
            if (is_object($avatars[0])) {
                $avt_handler->addUser($avatars[0]->getVar('avatar_id'), $uid);
            }
        }
    }
    redirect_header('showusers.php', 0, _US_PROFUPDATED);
    exit();
}
