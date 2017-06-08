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
$ticket_area = 'xoonips_admin_system_xoops';
if (!$xoopsGTicket->check(true, $ticket_area, false)) {
    redirect_header($xoonips_admin['mypage_url'], 3, $xoopsGTicket->getErrors());
    exit();
}

// get requests
$post_keys = array(
    'uid' => array('i', false, true),
);
$post_vals = xoonips_admin_get_requests('post', $post_keys);
$uid = $post_vals['uid'];

// get user certification mode
$config_keys = array(
    'certify_user' => 's',
);
$config_values = xoonips_admin_get_configs($config_keys, 'n');
$is_certified = ($config_values['certify_user'] == 'on') ? false : true;

// XOOPS user pickup
$xm_handler = &xoonips_gethandler('xoonips', 'member');
if (!$xm_handler->pickupXoopsUser($uid, $is_certified)) {
    redirect_header($xoonips_admin['mypage_url'], 3, _AM_XOONIPS_MSG_UNEXPECTED_ERROR);
    exit();
}

redirect_header($xoonips_admin['mypage_url'], 3, _AM_XOONIPS_MSG_DBUPDATED);
