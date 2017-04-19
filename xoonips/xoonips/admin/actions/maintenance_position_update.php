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

// check token ticket
require_once '../class/base/gtickets.php';
$ticket_area = 'xoonips_admin_maintenance_position';
if (!$xoopsGTicket->check(true, $ticket_area, false)) {
    redirect_header($xoonips_admin['mypage_url'], 3, $xoopsGTicket->getErrors());
    exit();
}

// get variables
$post_keys = array(
  'order' => array('i', true, true),
);
$post_vals = xoonips_admin_get_requests('post', $post_keys);

// update db values
$xusers_handler = &xoonips_getormhandler('xoonips', 'users');
foreach ($post_vals['order'] as $uid => $order) {
    $xusers_obj = $xusers_handler->get($uid);
    $old_order = intval($xusers_obj->getVar('user_order', 'n'));
    if ($order != $old_order) {
        $xusers_obj->set('user_order', $order);
        $xusers_handler->insert($xusers_obj);
    }
}

redirect_header($xoonips_admin['mypage_url'], 3, _AM_XOONIPS_MSG_DBUPDATED);
