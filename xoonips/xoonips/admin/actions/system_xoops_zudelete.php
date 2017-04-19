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

// get uid from form request
$formdata = &xoonips_getutility('formdata');
$uid = $formdata->getValue('post', 'uid', 'i', true);

// check token ticket
require_once '../class/base/gtickets.php';
$ticket_area = 'xoonips_admin_system_xoops_zombielist';
if (!$xoopsGTicket->check(true, $ticket_area, false)) {
    redirect_header($xoonips_admin['mypage_url'], 3, $xoopsGTicket->getErrors());
    exit();
}

// check exists public items
$index_item_link_handler = &xoonips_getormhandler('xoonips', 'index_item_link');
if (count($index_item_link_handler->getNonPrivateItemIds($uid)) > 0) {
    $url = sprintf('%s&action=zilist&uid=%u', $xoonips_admin['mypage_url'], $uid);
    redirect_header($url, 2, _AM_XOONIPS_SYSTEM_XOOPS_ZOMBIE_DELETE_MSG_REDIRECT);
    exit();
}

// delete account
$user_compo_handler = &xoonips_getormcompohandler('xoonips', 'user');
$user_compo_handler->deleteAccount($uid);

$event_handler = &xoonips_getormhandler('xoonips', 'event_log');
$event_handler->recordDeleteAccountEvent($uid);

redirect_header($xoonips_admin['mypage_url'], 3, _AM_XOONIPS_MAINTENANCE_ACCOUNT_DELETE_MSG_SUCCESS);
exit();
