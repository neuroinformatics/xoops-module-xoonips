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
require 'include/common.inc.php';
require 'include/group.inc.php';
require 'class/base/gtickets.php';

// privileges check : admin, moderator
$uid = is_object($xoopsUser) ? $xoopsUser->getVar('uid', 'n') : UID_GUEST;
$xmember_handler = &xoonips_gethandler('xoonips', 'member');
if (!$xmember_handler->isAdmin($uid) && !$xmember_handler->isModerator($uid)) {
    redirect_header(XOOPS_URL.'/', 3, _MD_XOONIPS_MODERATOR_SHULD_BE_MODERATOR);
    exit();
}

$formdata = &xoonips_getutility('formdata');

$op = $formdata->getValue('both', 'op', 's', false, '');

$xconfig_handler = &xoonips_getormhandler('xoonips', 'config');
$admin_xgroup_handler = &xoonips_gethandler('xoonips', 'admin_group');

$ticket_area = 'xoonips_group_edit';
$groups_show = true;
$gname_forbidden = false;
$gname_exists = false;
$gadmins_empty = false;
$gname_system = array(
    'public',
    'private',
    'root',
);

$breadcrumbs = array(
    array(
        'name' => _MD_XOONIPS_BREADCRUMBS_MODERATOR,
    ),
    array(
        'name' => _MD_XOONIPS_TITLE_GROUP_EDIT,
        'url' => 'editgroups.php',
    ),
);

switch ($op) {
case 'edit':
    $gid = $formdata->getValue('get', 'gid', 'i', true);
    $xg_obj = &$admin_xgroup_handler->getGroupObject($gid);
    if (!is_object($xg_obj)) {
        xoonips_group_error('editgroups.php', 'select');
    }
    if (!xoonips_group_check_perm($gid)) {
        xoonips_group_error('editgroups.php', 'lock_edit');
    }
    $gname = $xg_obj->getVar('gname', 'e');
    $gdesc = $xg_obj->getVar('gdesc', 'e');
    $gilimit = $xg_obj->get('group_item_number_limit');
    $gxlimit = $xg_obj->get('group_index_number_limit');
    $gslimit = $xg_obj->get('group_item_storage_limit') / 1000 / 1000;
    $gadmin_uids = $admin_xgroup_handler->getUserIds($gid, true);
    $breadcrumbs[] = array(
      'name' => $xg_obj->getVar('gname', 's'),
      'url' => 'editgroups.php?op=edit&amp;gid='.$gid,
    );
    $groups_show = false;
    break;
case 'update':
    if (!$xoopsGTicket->check(true, $ticket_area, false)) {
        redirect_header('editgroups.php', 3, $xoopsGTicket->getErrors());
        exit();
    }
    $gid = $formdata->getValue('post', 'gid', 'i', true);
    $gname = $formdata->getValue('post', 'gname', 's', true);
    $gdesc = $formdata->getValue('post', 'gdesc', 's', true);
    $gilimit = $formdata->getValue('post', 'item_number_limit', 'i', true);
    $gxlimit = $formdata->getValue('post', 'index_number_limit', 'i', true);
    $gslimit = $formdata->getValue('post', 'item_storage_limit', 'i', true);
    $gadmin_uids = $formdata->getValueArray('post', 'gadmins', 'i', false);
    $xg_obj = &$admin_xgroup_handler->getGroupObject($gid);
    if (!is_object($xg_obj)) {
        xoonips_group_error('editgroups.php', 'select');
    }
    $is_error = false;
    if (in_array(strtolower($gname), $gname_system)) {
        $gname_forbidden = true;
        $is_error = true;
    }
    if ($admin_xgroup_handler->existsGroup($gname, $gid)) {
        $gname_exists = true;
        $is_error = true;
    }
    if (empty($gadmin_uids)) {
        $gadmins_empty = true;
        $is_error = true;
    }
    if ($is_error) {
        $groups_show = false;
        break;
    }
    if (!$admin_xgroup_handler->updateGroup($gid, $gname, $gdesc, $gadmin_uids, $gilimit, $gxlimit, $gslimit * 1000 * 1000)) {
        xoonips_group_error('editgroups.php', 'update');
    }
    $op = '';
    break;
case 'register':
    $gid = 0;
    if (!$xoopsGTicket->check(true, $ticket_area, false)) {
        redirect_header('editgroups.php', 3, $xoopsGTicket->getErrors());
        exit();
    }
    $gname = $formdata->getValue('post', 'gname', 's', true);
    $gdesc = $formdata->getValue('post', 'gdesc', 's', true);
    $gilimit = $formdata->getValue('post', 'item_number_limit', 'i', true);
    $gxlimit = $formdata->getValue('post', 'index_number_limit', 'i', true);
    $gslimit = $formdata->getValue('post', 'item_storage_limit', 'i', true);
    $gadmin_uids = $formdata->getValueArray('post', 'gadmins', 'i', false);
    $is_error = false;
    if (in_array(strtolower($gname), $gname_system)) {
        $gname_forbidden = true;
        $is_error = true;
    }
    if ($admin_xgroup_handler->existsGroup($gname)) {
        $gname_exists = true;
        $is_error = true;
    }
    if (empty($gadmin_uids)) {
        $gadmins_empty = true;
        $is_error = true;
    }
    if ($is_error) {
        $groups_show = false;
        break;
    }
    if (!$admin_xgroup_handler->createGroup($gname, $gdesc, $gadmin_uids, $gilimit, $gxlimit, $gslimit * 1000 * 1000)) {
        xoonips_group_error('editgroups.php', 'insert');
    }
    $op = '';
    break;
case 'delete':
    if (!$xoopsGTicket->check(true, $ticket_area, false)) {
        redirect_header('editgroups.php', 3, $xoopsGTicket->getErrors());
        exit();
    }
    $gid = $formdata->getValue('post', 'gid', 'i', true);
    $xg_obj = &$admin_xgroup_handler->getGroupObject($gid);
    if (!is_object($xg_obj)) {
        xoonips_group_error('editgroups.php', 'select');
    }
    if (!xoonips_group_check_perm($gid, 'delete')) {
        xoonips_group_error('editgroups.php', 'lock_delete');
    }
    if (!$admin_xgroup_handler->deleteGroup($gid)) {
        xoonips_group_error('editgroups.php', 'delete');
    }
    $op = '';
    break;
}

if ($op == '') {
    $gid = 0;
    $gname = '';
    $gdesc = '';
    $gilimit = $xconfig_handler->getValue('group_item_number_limit');
    $gxlimit = $xconfig_handler->getValue('group_index_number_limit');
    $gslimit = $xconfig_handler->getValue('group_item_storage_limit') / 1000 / 1000;
    $gadmin_uids = array();
}

$groups = ($groups_show) ? xoonips_group_get_groups($uid) : array();
$gadmins = xoonips_group_get_users($gadmin_uids);
$msg_locked = sprintf(_MD_XOONIPS_WARNING_CANNOT_EDIT_LOCKED_GROUP, _MD_XOONIPS_LOCK_TYPE_STRING_CERTIFY_REQUEST);
$token_ticket = $xoopsGTicket->getTicketHtml(__LINE__, 1800, $ticket_area);

$xoopsOption['template_main'] = 'xoonips_editgroups.html';
require XOOPS_ROOT_PATH.'/header.php';
$xoopsTpl->assign('xoops_breadcrumbs', $breadcrumbs);
$xoopsTpl->assign('token_ticket', $token_ticket);
$xoopsTpl->assign('gid', $gid);
$xoopsTpl->assign('gname', $gname);
$xoopsTpl->assign('gdesc', $gdesc);
$xoopsTpl->assign('item_number_limit', $gilimit);
$xoopsTpl->assign('index_number_limit', $gxlimit);
$xoopsTpl->assign('item_storage_limit', $gslimit);
$xoopsTpl->assign('gadmins', $gadmins);
$xoopsTpl->assign('groups', $groups);
$xoopsTpl->assign('gname_forbidden', $gname_forbidden);
$xoopsTpl->assign('gname_exists', $gname_exists);
$xoopsTpl->assign('gadmins_empty', $gadmins_empty);
$xoopsTpl->assign('groups_show', $groups_show);
$xoopsTpl->assign('msg_locked', $msg_locked);
require XOOPS_ROOT_PATH.'/footer.php';
exit();
