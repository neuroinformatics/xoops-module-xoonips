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
defined('XOOPS_ROOT_PATH') || exit('XOOPS root path not defined');

// check token ticket
require_once '../class/base/gtickets.php';
$ticket_area = 'xoonips_admin_system_xoops_item_rescue';
if (!$xoopsGTicket->check(true, $ticket_area, false)) {
    redirect_header($xoonips_admin['mypage_url'], 3, $xoopsGTicket->getErrors());
    exit();
}

// get form request
$formdata = &xoonips_getutility('formdata');
$uid = $formdata->getValue('post', 'uid', 'i', true);
$to_uid = $formdata->getValue('post', 'tuid', 'i', true);
$to_xid = $formdata->getValue('post', 'txid', 'i', true);

// is uid really zombie user ?
$xusers_handler = &xoonips_getormhandler('xoonips', 'users');
$users_handler = &xoonips_getormhandler('xoonips', 'xoops_users');
$xusers_obj = &$xusers_handler->get($uid);
$users_obj = &$users_handler->get($uid);
if (!is_object($xusers_obj) || is_object($users_obj)) {
    die('illegal request');
}

// is to_uid really activated and certified user ?
$to_xusers_obj = &$xusers_handler->get($to_uid);
$to_users_obj = &$users_handler->get($to_uid);
if (!is_object($to_xusers_obj) || !is_object($to_users_obj)) {
    die('illegal request');
}
if ($to_xusers_obj->get('activate') != 1 || $to_users_obj->get('level') == 0) {
    die('illegal request');
}

// is to uid really owner of to xid ?
$index_handler = &xoonips_getormhandler('xoonips', 'index');
$to_index_obj = &$index_handler->get($to_xid);
if (!is_object($to_index_obj)) {
    die('illegal request');
}
if ($to_index_obj->get('uid') != $to_uid || $to_index_obj->get('open_level') != OL_PRIVATE) {
    die('illegal request');
}

// get rescue and delete item ids
$index_item_link_handler = &xoonips_getormhandler('xoonips', 'index_item_link');
$item_ids = $index_item_link_handler->getNonPrivateItemIds($uid);
// merge group and public item ids
$item_ids = array_unique($item_ids);
if (count($item_ids) == 0) {
    die('illegal request');
}

// handover of items
$item_basic_handler = &xoonips_getormhandler('xoonips', 'item_basic');
$admin_xgroup_handler = &xoonips_gethandler('xoonips', 'admin_group');
$event_handler = &xoonips_getormhandler('xoonips', 'event_log');
foreach ($item_ids as $item_id) {
    $item_basic_obj = &$item_basic_handler->get($item_id);
    $index_item_link_objs = &$index_item_link_handler->getByItemId($item_id);
    foreach ($index_item_link_objs as $index_item_link_obj) {
        $xid = $index_item_link_obj->get('index_id');
        $index_obj = &$index_handler->get($xid);
        $open_level = $index_obj->get('open_level');
        switch ($open_level) {
        case OL_PRIVATE:
            // delete index item link
            $index_item_link_handler->delete($index_item_link_obj);
            break;
        case OL_GROUP_ONLY:
            // join old user joined group
            $gid = $index_obj->get('gid');
            if (!$admin_xgroup_handler->isGroupMember($to_uid, $gid)) {
                $admin_xgroup_handler->addUserToXooNIpsGroup($gid, $to_uid, false);
            }
            break;
        case OL_PUBLIC:
            // nothing to do
            break;
        }
    }
    // added new index item link to $to_xid
    $index_item_link_handler->add($to_xid, $item_id, NOT_CERTIFIED);
    // change item owner to $to_uid
    $item_basic_obj->set('uid', $to_uid);
    $item_basic_handler->insert($item_basic_obj);
    // TODO: append change logs
    $event_handler->recordRequestTransferItemEvent($item_id, $to_uid);
    $event_handler->recordTransferItemEvent($item_id, $to_xid, $to_uid);
}

// success
redirect_header($xoonips_admin['mypage_url'], 3, _AM_XOONIPS_MSG_DBUPDATED);
exit();
