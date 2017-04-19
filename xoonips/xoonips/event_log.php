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

/* browse/download event logs (for moderators).  */

// avoid IE's bug. see: http://jp2.php.net/header  Harry 10-Dec-2004 03:26
session_cache_limiter('none');

require 'include/common.inc.php';
require 'include/eventlog.inc.php';

$textutil = &xoonips_getutility('text');

if (!$xoopsUser) {
    // redirect login page and back here after login
    redirect_header('user.php', 3, _NOPERM);
    exit();
}
$uid = $xoopsUser->getVar('uid');
$mhandler = &xoonips_gethandler('xoonips', 'member');
if (!$mhandler->isModerator($uid)) {
    redirect_header(XOOPS_URL.'/', 3, _MD_XOONIPS_MODERATOR_SHULD_BE_MODERATOR);
    exit();
}

// request variables
$formdata = &xoonips_getutility('formdata');
$is_post = $_SERVER['REQUEST_METHOD'] == 'POST' ? true : false;
$method = $is_post ? 'post' : 'get';
$log_type_id = $formdata->getValue($method, 'log_type_id', 'i', false, 0);
$mode = $formdata->getValue($method, 'mode', 's', false, '');
$page = $formdata->getValue($method, 'page', 'i', false, 1);
$limit = $formdata->getValue($method, 'limit', 'i', false, 20);

$breadcrumbs = array(
    array('name' => _MD_XOONIPS_BREADCRUMBS_MODERATOR),
    array('name' => _MD_XOONIPS_BREADCRUMBS_EVENTLOG, 'url' => 'event_log.php'),
);

switch ($mode) {
case 'download':
    if ($log_type_id >= 0 && $log_type_id <= 19) {
        // download event logs
        xoonips_eventlog_download($is_post, $log_type_id);
    } elseif ($log_type_id == 20 || $log_type_id == 21) {
        // download registered data
        xoonips_eventlog_download_registered_list($is_post, $log_type_id);
    } else {
        die('invalid log type id');
    }
    break;
case 'graphview':
    // show graph view page
    $time_range = xoonips_eventlog_get_request_date($is_post, false);
    $start_time = $time_range['StartDate']['value'];
    $end_time = $time_range['EndDate']['value'];
    if ($start_time > $end_time) {
        die('invalid time range');
    }
    $breadcrumbs[] = array('name' => _MD_XOONIPS_BREADCRUMBS_EVENTLOG_GRAPH);
    $xoopsOption['template_main'] = 'xoonips_event_graph.html';
    include XOOPS_ROOT_PATH.'/header.php';
    $xoopsTpl->assign('xoops_breadcrumbs', $breadcrumbs);
    $xoopsTpl->assign('log_type_id', $log_type_id);
    $xoopsTpl->assign('start_time', $start_time);
    $xoopsTpl->assign('end_time', $end_time);
    include XOOPS_ROOT_PATH.'/footer.php';
    break;
case 'graph':
    // show graph
    xoonips_eventlog_graph($log_type_id);
    break;
case 'list':
    if ($limit < 1 || $page < 1 || ($log_type_id != 20 && $log_type_id != 21)) {
        die('Illegal request');
    }
    $is_users = ($log_type_id == 20) ? true : false;
    $users = array();
    $items = array();
    if ($is_users) {
        $total = xoonips_eventlog_count_users();
    } else {
        $total = xoonips_eventlog_count_items();
    }
    include 'class/base/pagenavi.class.php';
    $pagenavi = new XooNIpsPageNavi($total, $limit, $page);
    $start = $pagenavi->getStart();
    $limit = $pagenavi->getLimit();
    if ($is_users) {
        $objs = &xoonips_eventlog_get_users($start, $limit);
        foreach ($objs as $obj) {
            $user = array();
            $uname = $obj->getExtraVar('uname');
            $email = $obj->getExtraVar('email');
            $user['uname'] = $textutil->html_special_chars($uname);
            $user['company_name'] = $textutil->html_special_chars($obj->getVar('company_name', 'n'));
            $user['division'] = $textutil->html_special_chars($obj->getVar('division', 'n'));
            $user['email'] = $textutil->html_special_chars($email);
            $users[] = $user;
        }
    } else {
        $objs = &xoonips_eventlog_get_items($start, $limit);
        foreach ($objs as $obj) {
            $item = array();
            $item_id = $obj->getVar('item_id', 's');
            $title = xoonips_eventlog_get_item_title($item_id);
            $display_name = $obj->getExtraVar('display_name');
            $uname = $obj->getExtraVar('uname');
            $item['item_id'] = $item_id;
            $item['title'] = $textutil->html_special_chars($title);
            $item['display_name'] = $textutil->html_special_chars($display_name);
            $item['uname'] = $textutil->html_special_chars($uname);
            $items[] = $item;
        }
    }
    $navi = $pagenavi->getTemplateVars(10);
    $breadcrumbs[] = array('name' => _MD_XOONIPS_BREADCRUMBS_EVENTLOG_LIST);
    $xoopsOption['template_main'] = 'xoonips_event_view.html';
    include XOOPS_ROOT_PATH.'/header.php';
    $xoopsTpl->assign('xoops_breadcrumbs', $breadcrumbs);
    $xoopsTpl->assign('navi', $navi);
    $xoopsTpl->assign('navi_limits', array(20, 50, 100));
    $xoopsTpl->assign('is_users', $is_users);
    $xoopsTpl->assign('users', $users);
    $xoopsTpl->assign('items', $items);
    include XOOPS_ROOT_PATH.'/footer.php';
    break;
default:
    // main page
    $usercnt = xoonips_eventlog_count_users();
    $itemcnt = xoonips_eventlog_count_items();
    $xoopsOption['template_main'] = 'xoonips_event_log.html';
    include XOOPS_ROOT_PATH.'/header.php';
    $xoopsTpl->assign('xoops_breadcrumbs', $breadcrumbs);
    $xoopsTpl->assign('time', time());
    $xoopsTpl->assign('start_year', 2005);
    $xoopsTpl->assign('end_year', date('Y'));
    $xoopsTpl->assign('num_of_user', $usercnt);
    $xoopsTpl->assign('num_of_item', $itemcnt);
    include XOOPS_ROOT_PATH.'/footer.php';
}
