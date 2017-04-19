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
if (!defined('XOOPS_ROOT_PATH')) {
    exit();
}

// xoonips index tree block
function b_xoonips_tree_show()
{
    global $xoopsUser;
    global $xoonipsTreeCheckBox, $xoonipsSelectedTab, $xoonipsTreePrivateUid;
    global $xoonipsURL, $xoonipsEditIndex;
    global $xoonipsCheckPrivateHandlerId, $xoonipsEditPublic;
    global $xoonipsTree;

    $uid = is_object($xoopsUser) ? $xoopsUser->getVar('uid', 'n') : UID_GUEST;
    $puid = $uid;

    $textutil = &xoonips_getutility('text');
    $formdata = &xoonips_getutility('formdata');

    if (!class_exists('Xoonips_UserPreload')) {
        // view page hook
        // http://foo.bar/register.php
        //   -> http://foo.bar/modules/xoonips/registeruser.php
        // http://foo.bar/userinfo.php
        //   -> http://foo.bar/modules/xoonips/userinfo.php
        // http://foo.bar/user.php
        //   -> http://foo.bar/modules/xoonips/user.php
        $site_url = XOOPS_URL.'/';
        $site_url_base = '/';
        if (preg_match('/^(\\S+):\\/\\/([^\\/]+)((\\/[^\\/]+)*\\/)$/', $site_url, $matches)) {
            $site_url_base = $matches[3];
        }
        $current_script = (isset($_SERVER['SCRIPT_NAME'])) ? $_SERVER['SCRIPT_NAME'] : '';
        if ($current_script == $site_url_base.'register.php') {
            header('Location: modules/xoonips/registeruser.php');
            exit();
        }
        if ($current_script == $site_url_base.'userinfo.php') {
            $uid = $formdata->getValue('get', 'uid', 'i', false);
            $uid = isset($uid) ? '?uid='.$uid : '';
            header('Location: modules/xoonips/userinfo.php'.$uid);
            exit();
        }
        if ($current_script == $site_url_base.'user.php') {
            $op = $formdata->getValue('both', 'op', 's', false);
            if (is_null($op)) {
                $xoops_redirect = $formdata->getValue('get', 'xoops_redirect', 's', false);
                $redirect = !empty($xoops_redirect) ? '?xoops_redirect='.urlencode($xoops_redirect) : '';
                header('Location: modules/xoonips/user.php'.$redirect);
                exit();
            }
        }
    }

    // hide block if user is invalid xoonips user
    $xsession_handler = &xoonips_getormhandler('xoonips', 'session');
    if (!$xsession_handler->validateUser($uid, false)) {
        return false;
    }

    // record view top page event
    $eventlog_handler = &xoonips_getormhandler('xoonips', 'event_log');
    $eventlog_handler->recordViewTopPageEvent();

    // load handlers
    $xconfig_handler = &xoonips_getormhandler('xoonips', 'config');
    $xuser_handler = &xoonips_getormhandler('xoonips', 'users');
    $title_handler = &xoonips_getormhandler('xoonips', 'title');
    $xmember_handler = &xoonips_gethandler('xoonips', 'member');
    $xgroup_handler = &xoonips_gethandler('xoonips', 'group');

    // get configs
    $tree_frame_width = $xconfig_handler->getValue('tree_frame_width');
    $tree_frame_height = $xconfig_handler->getValue('tree_frame_height');
    $public_item_target_user = $xconfig_handler->getValue('public_item_target_user');

    // set query for tree.php of inline frame
    $query = array();
    if (!empty($xoonipsTreeCheckBox)) {
        $query[] = 'checkbox=1';
    }
    if (!empty($xoonipsEditIndex)) {
        $query[] = 'edit=1';
    }
    if (!empty($xoonipsCheckPrivateHandlerId)) {
        $query[] = 'on_check_private_handler_id='.$xoonipsCheckPrivateHandlerId;
    }
    if (isset($xoonipsURL)) {
        $query[] = 'url='.urlencode($xoonipsURL);
    }
    if (!empty($xoonipsSelectedTab)) {
        $query[] = 'selected_tab='.$xoonipsSelectedTab;
    }
    if (!empty($xoonipsEditPublic)) {
        $query[] = 'edit_public=1';
    }
    if (!empty($xoonipsTreePrivateUid)) {
        $query[] = 'puid='.$xoonipsTreePrivateUid;
        $puid = $xoonipsTreePrivateUid;
    }
    if (!empty($xoonipsTree)) {
        if (isset($xoonipsTree['onclick_title'])) {
            $query[] = 'onclick_title='.$xoonipsTree['onclick_title'];
        }
        if (isset($xoonipsTree['private_only']) && $xoonipsTree['private_only']) {
            $query[] = 'private_only=1';
        }
    }

    // get user informations
    $pxid = 0;
    if ($puid != UID_GUEST) {
        $xuser_obj = &$xuser_handler->get($puid);
        if (is_object($xuser_obj)) {
            $pxid = $xuser_obj->getVar('private_index_id', 'n');
        }
    }
    $is_moderator = $xmember_handler->isModerator($uid);

    // get indexes
    $xids = array();
    if ($uid == UID_GUEST) {
        if ($public_item_target_user == 'all') {
            // guest user can view public index
            $xids[] = IID_PUBLIC;
        } else {
            // disable to show public index
        }
    } else {
        // login users
        if (!empty($xoonipsEditIndex)) {
            // edit index - show editable indexes
            if ($is_moderator || !empty($xoonipsEditPublic) && !empty($_SESSION['xoonips_old_uid'])) {
                $xids[] = IID_PUBLIC;
            }
            $xids = array_merge($xids, $xgroup_handler->getGroupRootIndexIds($puid, false));
            $xids[] = $pxid;
        } elseif (isset($xoonipsTree['private_only']) && $xoonipsTree['private_only']) {
            // only private index only mode
            if ($pxid != 0) {
                $xids[] = $pxid;
            }
        } else {
            $xids[] = IID_PUBLIC;
            $xids = array_merge($xids, $xgroup_handler->getGroupRootIndexIds($puid, false));
            if ($pxid != 0) {
                $xids[] = $pxid;
            }
        }
    }
    $indexes = array();
    foreach ($xids as $xid) {
        if ($xid == $pxid && $puid == $uid) {
            $title = XNP_PRIVATE_INDEX_TITLE;
        } else {
            $criteria = new CriteriaCompo(new Criteria('item_id', $xid));
            $criteria->add(new Criteria('title_id', DEFAULT_INDEX_TITLE_OFFSET));
            $title_objs = &$title_handler->getObjects($criteria);
            $title = $textutil->html_special_chars($title_objs[0]->get('title'));
        }
        $indexes[] = array(
            'item_id' => $xid,
            'title' => $title,
        );
    }

    // assign block template variables
    $block = array();
    $block['tree_frame_width'] = $tree_frame_width;
    $block['tree_frame_height'] = $tree_frame_height;
    $block['query'] = implode('&amp;', $query);
    $block['isKHTML'] = (strstr($_SERVER['HTTP_USER_AGENT'], 'KHTML') !== false);
    $block['checkbox'] = !empty($xoonipsTreeCheckBox);
    $block['indexes'] = $indexes;

    return $block;
}
