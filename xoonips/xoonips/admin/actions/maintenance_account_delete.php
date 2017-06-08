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

// get requests
$formdata = &xoonips_getutility('formdata');
$uid = $formdata->getValue('post', 'uid', 'i', true);

// is uid really xoonips user ?
$u_handler = &xoonips_getormhandler('xoonips', 'xoops_users');
$xu_handler = &xoonips_getormhandler('xoonips', 'users');
$u_obj = &$u_handler->get($uid);
$xu_obj = &$xu_handler->get($uid);
if (!is_object($u_obj) || !is_object($xu_obj)) {
    redirect_header($xoonips_admin['mypage_url'], 3, _AM_XOONIPS_MSG_ILLACCESS);
    exit();
}

// is uid really not system admin or moderator or group admin user ?
$xmember_handler = &xoonips_gethandler('xoonips', 'member');
$xgroup_handler = &xoonips_gethandler('xoonips', 'group');
if ($xmember_handler->isAdmin($uid) || $xmember_handler->isModerator($uid) || $xgroup_handler->isGroupAdmin($uid)) {
    redirect_header($xoonips_admin['mypage_url'], 3, _AM_XOONIPS_MAINTENANCE_ACCOUNT_DCONFIRM_MSG_IGNORE_USER);
    exit();
}

// is uid really has not public/group items ?
$index_item_link_handler = &xoonips_getormhandler('xoonips', 'index_item_link');
if (count($index_item_link_handler->getNonPrivateItemIds($uid)) > 0) {
    redirect_header($xoonips_admin['mypage_url'], 3, _AM_XOONIPS_MAINTENANCE_ACCOUNT_DCONFIRM_MSG_ITEM_HANDOVER);
    exit();
}

// check token ticket
require_once '../class/base/gtickets.php';
$ticket_area = 'xoonips_admin_maintenance_account_delete';
if (!$xoopsGTicket->check(true, $ticket_area)) {
    exit();
}

$user_compo_handler = &xoonips_getormcompohandler('xoonips', 'user');
$user_compo_handler->deleteAccount($uid);

$event_handler = &xoonips_getormhandler('xoonips', 'event_log');
$event_handler->recordDeleteAccountEvent($uid);

redirect_header($xoonips_admin['mypage_url'], 3, _AM_XOONIPS_MAINTENANCE_ACCOUNT_DELETE_MSG_SUCCESS);
exit();
