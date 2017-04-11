<?php

// $Revision: 1.7.4.1.2.17 $
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
include 'include/common.inc.php';
include 'include/group.inc.php';
include 'class/base/gtickets.php';

// privileges check : admin, group admin
$uid = is_object($xoopsUser) ? $xoopsUser->getVar('uid', 'n') : UID_GUEST;
$xmember_handler = &xoonips_gethandler('xoonips', 'member');
$admin_xgroup_handler = &xoonips_gethandler('xoonips', 'admin_group');
if (!$xmember_handler->isAdmin($uid) && !$admin_xgroup_handler->isGroupAdmin($uid)) {
    redirect_header(XOOPS_URL.'/', 3, _MD_XOONIPS_MODERATOR_SHULD_BE_MODERATOR);
    exit();
}

$formdata = &xoonips_getutility('formdata');

$op = $formdata->getValue('both', 'op', 's', false, '');

$breadcrumbs = array(
  array(
   'name' => _MD_XOONIPS_BREADCRUMBS_GROUPADMIN,
  ),
  array(
   'name' => _MD_XOONIPS_TITLE_GROUP_MEMBER_EDIT,
   'url' => XOOPS_URL.'/modules/xoonips/groupadmin.php',
  ),
);

$ticket_area = 'xoonips_group_member_edit';

switch ($op) {
case 'edit':
  $gid = $formdata->getValue('get', 'gid', 'i', true);
  if (!$xmember_handler->isAdmin($uid) && !$admin_xgroup_handler->isGroupAdmin($uid, $gid)) {
      xoonips_group_error('groupadmin.php', 'select');
  }
  $gids = array($gid);
  $xg_obj = &$admin_xgroup_handler->getGroupObject($gid);
  $breadcrumbs[] = array(
   'name' => $xg_obj->getVar('gname', 's'),
   'url' => XOOPS_URL.'/modules/xoonips/groupadmin.php?op=edit&amp;gid='.$gid,
  );
  break;
case 'update':
  $gid = $formdata->getValue('post', 'gid', 'i', true);
  if (!$xmember_handler->isAdmin($uid) && !$admin_xgroup_handler->isGroupAdmin($uid, $gid)) {
      xoonips_group_error('groupadmin.php', 'select');
  }
  $mode = $formdata->getValue('post', 'mode', 's', true);
  $guids = $formdata->getValueArray('post', 'uids', 'i', false);
  if (!in_array($mode, array('add', 'delete'))) {
      xoonips_group_error('groupadmin.php', 'update');
  }
  foreach ($guids as $guid) {
      if ($admin_xgroup_handler->isGroupAdmin($guid, $gid)) {
          // ignore if group administrator
      continue;
      }
      if ($mode == 'add') {
          // subscribe to group
      $admin_xgroup_handler->addUserToXooNIpsGroup($gid, $guid, false);
      } else {
          // unsubscribe from group
      $admin_xgroup_handler->deleteUserFromXooNIpsGroup($gid, $guid);
      }
  }
  $gids = array($gid);
  $xg_obj = &$admin_xgroup_handler->getGroupObject($gid);
  $breadcrumbs[] = array(
   'name' => $xg_obj->getVar('gname', 's'),
   'url' => XOOPS_URL.'/modules/xoonips/groupadmin.php?op=edit&amp;gid='.$gid,
  );
  break;
case '':
  $gid = 0;
  if ($xmember_handler->isAdmin($uid)) {
      $gids = $admin_xgroup_handler->getGroupIds();
  } else {
      $gids = $admin_xgroup_handler->getGroupIds($uid, true);
  }
  break;
}
$groups = xoonips_group_get_groups($uid, $gids);
$admin_members = array();
$locked_members = array();
$members = array();
$non_members = array();
if ($gid != 0) {
    $gadmin_uids = $admin_xgroup_handler->getUserIds($gid, true);
    $member_uids = $admin_xgroup_handler->getUserIds($gid);
    $users = xoonips_group_get_users($gadmin_uids);
    foreach ($users as $user) {
        if (in_array($user['uid'], $member_uids)) {
            $user['item_num'] = count($admin_xgroup_handler->getGroupItemIds($gid, $user['uid']));
            if ($user['isadmin']) {
                $admin_members[] = $user;
            } elseif ($user['item_num'] != 0) {
                $locked_members[] = $user;
            } else {
                $members[] = $user;
            }
        } else {
            $non_members[] = $user;
        }
    }
}
$token_ticket = $xoopsGTicket->getTicketHtml(__LINE__, 1800, $ticket_area);

$xoopsOption['template_main'] = 'xoonips_groupadmin.html';
require XOOPS_ROOT_PATH.'/header.php';
$xoopsTpl->assign('xoops_breadcrumbs', $breadcrumbs);
$xoopsTpl->assign('token_ticket', $token_ticket);
$xoopsTpl->assign('gid', $gid);
$xoopsTpl->assign('groups', $groups);
$xoopsTpl->assign('admin_members', $admin_members);
$xoopsTpl->assign('locked_members', $locked_members);
$xoopsTpl->assign('members', $members);
$xoopsTpl->assign('non_members', $non_members);
require XOOPS_ROOT_PATH.'/footer.php';
