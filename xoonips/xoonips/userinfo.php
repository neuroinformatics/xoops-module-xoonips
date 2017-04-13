<?php

// $Revision: 1.10.4.1.2.16 $
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

$myuid = is_object($xoopsUser) ? $xoopsUser->getVar('uid', 'n') : UID_GUEST;

if ($myuid == UID_GUEST) {
    // deny to access from guest user
    redirect_header('user.php', 3, _NOPERM);
    exit();
}

$formdata = &xoonips_getutility('formdata');
$uid = $formdata->getValue('get', 'uid', 'i', false, $myuid);

// validate selected user
$xmember_handler = &xoonips_gethandler('xoonips', 'member');
$is_admin = $xmember_handler->isAdmin($myuid);
$is_moderator = $xmember_handler->isModerator($myuid);
$member_handler = &xoops_gethandler('member');
$thisUser = &$member_handler->getUser($uid);
if (!is_object($thisUser)) {
    // selected user not found
    redirect_header(XOOPS_URL.'/', 3, _US_SELECTNG);
    exit();
} elseif (!$thisUser->isActive()) {
    // not activated user
    if ($is_admin) {
        // try activate using admin privilege
        header('Location: admin/maintenance.php?page=account&action=modify&uid='.$uid);
        exit();
    } else {
        // deny access to selected user information
        redirect_header(XOOPS_URL.'/', 3, _US_NOACTTPADM);
        exit();
    }
}

$xoopsOption['template_main'] = 'xoonips_userinfo.html';
require XOOPS_ROOT_PATH.'/header.php';
if ($uid == $myuid || $is_admin) {
    $xoopsTpl->assign('user_ownpage', true);
    $xoopsTpl->assign('lang_editprofile', _US_EDITPROFILE);
    $xoopsTpl->assign('lang_deleteaccount', _US_DELACCOUNT);
    $xoopsTpl->assign('lang_avatar', _US_AVATAR);
    $xoopsTpl->assign('lang_inbox', _US_INBOX);
    $xoopsTpl->assign('lang_logout', _US_LOGOUT);
    $myxoopsConfigUser = &xoonips_get_xoops_configs(XOOPS_CONF_USER);
    if (($myxoopsConfigUser['self_delete'] == 1) && ($uid == $_SESSION['xoopsUserId'])) {
        $xoopsTpl->assign('user_candelete', true);
    } else {
        $xoopsTpl->assign('user_candelete', false);
    }
} else {
    $xoopsTpl->assign('user_ownpage', false);
    $xoopsTpl->assign('user_candelete', false);
}

(method_exists(MyTextSanitizer, sGetInstance) and $myts = &MyTextSanitizer::sGetInstance()) || $myts = &MyTextSanitizer::getInstance();
$textutil = &xoonips_getutility('text');

// assign basic user information
$xoopsTpl->assign('user_uid', $thisUser->getVar('uid', 's'));
$xoopsTpl->assign('lang_basicInfo', _US_BASICINFO);
$xoopsTpl->assign('lang_allaboutuser', sprintf(_US_ALLABOUT, $thisUser->getVar('uname', 's')));
$xoopsTpl->assign('lang_avatar', _US_AVATAR);
$xoopsTpl->assign('user_avatarurl', XOOPS_URL.'/uploads/'.$thisUser->getVar('user_avatar', 's'));
$xoopsTpl->assign('lang_realname', _US_REALNAME);
$xoopsTpl->assign('user_realname', $thisUser->getVar('name', 's'));
$xoopsTpl->assign('lang_website', _US_WEBSITE);
$xoopsTpl->assign('user_websiteurl', $textutil->convert_link($thisUser->getVar('url', 's')));
$xoopsTpl->assign('lang_email', _US_EMAIL);
if ($thisUser->getVar('user_viewemail') == 1 || $uid == $myuid || $is_admin || $is_moderator) {
    $xoopsTpl->assign('user_email', $textutil->convert_link($thisUser->getVar('email', 's')));
} else {
    $xoopsTpl->assign('user_email', '&nbsp;');
}
$xoopsTpl->assign('lang_privmsg', _US_PM);
$xoopsTpl->assign('lang_icq', _US_ICQ);
$xoopsTpl->assign('user_icq', $thisUser->getVar('user_icq', 's'));
$xoopsTpl->assign('lang_aim', _US_AIM);
$xoopsTpl->assign('user_aim', $thisUser->getVar('user_aim', 's'));
$xoopsTpl->assign('lang_yim', _US_YIM);
$xoopsTpl->assign('user_yim', $thisUser->getVar('user_yim', 's'));
$xoopsTpl->assign('lang_msnm', _US_MSNM);
$xoopsTpl->assign('user_msnm', $thisUser->getVar('user_msnm', 's'));
$xoopsTpl->assign('lang_location', _US_LOCATION);
$xoopsTpl->assign('user_location', $thisUser->getVar('user_from', 's'));
$xoopsTpl->assign('lang_occupation', _US_OCCUPATION);
$xoopsTpl->assign('user_occupation', $thisUser->getVar('user_occ', 's'));
$xoopsTpl->assign('lang_interest', _US_INTEREST);
$xoopsTpl->assign('user_interest', $thisUser->getVar('user_intrest', 's'));
$xoopsTpl->assign('lang_extrainfo', _US_EXTRAINFO);
$xoopsTpl->assign('user_extrainfo', $myts->makeTareaData4Show($thisUser->getVar('bio', 'n'), 0, 1, 1));
if ($myuid != UID_GUEST) {
    $xoopsTpl->assign('user_pmlink', '<a href="javascript:openWithSelfMain(\''.XOOPS_URL.'/pmlite.php?send2=1&amp;to_userid='.$thisUser->getVar('uid').'\', \'pmlite\', 450, 380);"><img src="'.XOOPS_URL.'/images/icons/pm.gif" alt="'.sprintf(_SENDPMTO, $thisUser->getVar('uname', 'e')).'"/></a>');
} else {
    $xoopsTpl->assign('user_pmlink', '');
}

// statistics information
$xoopsTpl->assign('lang_statistics', _US_STATISTICS);
$xoopsTpl->assign('lang_membersince', _US_MEMBERSINCE);
$xoopsTpl->assign('user_joindate', formatTimestamp($thisUser->getVar('user_regdate', 'n'), 's'));
$xoopsTpl->assign('lang_rank', _US_RANK);
$userrank = &$thisUser->rank();
if (isset($userrank['image']) && $userrank['image']) {
    $xoopsTpl->assign('user_rankimage', '<img src="'.XOOPS_UPLOAD_URL.'/'.$userrank['image'].'" alt=""/>');
}
$xoopsTpl->assign('user_ranktitle', $userrank['title']);
$xoopsTpl->assign('lang_posts', _US_POSTS);
$xoopsTpl->assign('user_posts', $thisUser->getVar('posts', 's'));
$xoopsTpl->assign('lang_lastlogin', _US_LASTLOGIN);
$date = $thisUser->getVar('last_login', 'n');
if (!empty($date)) {
    $xoopsTpl->assign('user_lastlogin', formatTimestamp($date, 'm'));
}

// signature
$xoopsTpl->assign('lang_signature', _US_SIGNATURE);
$xoopsTpl->assign('user_signature', $myts->makeTareaData4Show($thisUser->getVar('user_sig', 'n'), 0, 1, 1));

// others
$xoopsTpl->assign('lang_notregistered', _US_NOTREGISTERED);
$xoopsTpl->assign('lang_more', _US_MOREABOUT);
$xoopsTpl->assign('lang_myinfo', _US_MYINFO);

// xoonips user information
$xu_handler = &xoonips_getormhandler('xoonips', 'users');
$xu_obj = &$xu_handler->get($uid);
if (is_object($xu_obj)) {
    $xoopsTpl->assign('xnp_user_ownpage', true);
    $xu_vars = $xu_obj->getVarArray('s');
    $posi_handler = &xoonips_getormhandler('xoonips', 'positions');
    foreach ($xu_vars as $key => $val) {
        if ($key == 'posi') {
            if ($val == 0) {
                $posi_obj = &$posi_handler->get($val);
                if (is_object($posi_obj)) {
                    $posi_title = $posi_obj->getVar('posi_title', 's');
                    $xoopsTpl->assign('xnp_user_posi_t', $posi_title);
                }
            }
        } else {
            $xoopsTpl->assign('xnp_user_'.$key, $val);
        }
    }
} else {
    // not xoonips user
    $xoopsTpl->assign('xnp_user_ownpage', false);
}

// curriculum vitae
$cv_handler = &xoonips_getormhandler('xoonips', 'cvitaes');
$cv_objs = &$cv_handler->getCVs($uid);
foreach ($cv_objs as $cv_obj) {
    $cv = array();
    foreach (array('from', 'to') as $key_pre) {
        foreach (array('month', 'year') as $key_post) {
            $key = $key_pre.'_'.$key_post;
            $val = $cv_obj->get($key);
            if ($val == 0) {
                $str = '';
            } elseif ($key_post == 'month') {
                $str = date('M.', mktime(0, 0, 0, $val, 1, 0));
            } else {
                $str = date('Y', mktime(0, 0, 0, 1, 1, $val));
            }
            $cv['cvitae_'.$key] = $str;
        }
    }
    $from_year = $cv_obj->get('from_year');
    $to_year = $cv_obj->get('to_year');
    $cv['cvitae_title'] = $cv_obj->get('cvitae_title', 's');
    $xoopsTpl->append('cv_array', $cv);
}

// posted message list
$gperm_handler = &xoops_gethandler('groupperm');
$groups = ($uid != UID_GUEST) ? $xoopsUser->getGroups() : XOOPS_GROUP_ANONYMOUS;
$module_handler = &xoops_gethandler('module');
$criteria = new CriteriaCompo(new Criteria('hassearch', 1));
$criteria->add(new Criteria('isactive', 1));
$mids = &array_keys($module_handler->getList($criteria));
foreach ($mids as $mid) {
    // Hack by marcan : only return results of modules for which user has access permission
    if ($gperm_handler->checkRight('module_read', $mid, $groups)) {
        $module = &$module_handler->get($mid);
        $results = &$module->search('', '', 5, 0, $uid);
        $count = count($results);
        if (is_array($results) && $count > 0) {
            $dirname = $module->getVar('dirname', 's');
            $modname = $module->getVar('name', 's');
            for ($i = 0; $i < $count; ++$i) {
                if (isset($results[$i]['image']) && $results[$i]['image'] != '') {
                    $results[$i]['image'] = '../'.$dirname.'/'.$results[$i]['image'];
                } else {
                    $results[$i]['image'] = '../../images/icons/posticon2.gif';
                }
                $results[$i]['link'] = '../'.$dirname.'/'.$results[$i]['link'];
                $results[$i]['title'] = $myts->makeTboxData4Show($results[$i]['title']);
                $results[$i]['time'] = $results[$i]['time'] ? formatTimestamp($results[$i]['time']) : '';
            }
            if ($count == 5) {
                $showall_link = '<a href="../../search.php?action=showallbyuser&amp;mid='.$mid.'&amp;uid='.$thisUser->getVar('uid').'">'._US_SHOWALL.'</a>';
            } else {
                $showall_link = '';
            }
            $xoopsTpl->append('modules', array('name' => $modname, 'results' => $results, 'showall_link' => $showall_link));
        }
        unset($module);
    }
}
require XOOPS_ROOT_PATH.'/footer.php';
