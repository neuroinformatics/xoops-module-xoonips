<?php

// $Revision: 1.1.4.1.2.6 $
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
$ticket_area = 'xoonips_admin_maintenance_account_aconfirm';
if (!$xoopsGTicket->check(true, $ticket_area, false)) {
    redirect_header($xoonips_admin['mypage_url'], 3, $xoopsGTicket->getErrors());
    exit();
}

// get requests
$keys = array(
  'uid' => array(
    'i',
    false,
    true,
  ),
);
$vals = xoonips_admin_get_requests('post', $keys);

function user_reactivate($uid)
{
    global $xoonips_admin;
  // get user information
  $u_handler = &xoonips_getormhandler('xoonips', 'xoops_users');
    $xu_handler = &xoonips_getormhandler('xoonips', 'users');
    $u_obj = &$u_handler->get($uid);
    $xu_obj = &$xu_handler->get($uid);
    if (!is_object($u_obj) || !is_object($xu_obj)) {
        redirect_header($xoonips_admin['mypage_url'], 3, _AM_XOONIPS_MSG_ILLACCESS);
        exit();
    }

  // update db values
  $u_obj->set('level', 1);
    if (!$u_handler->insert($u_obj)) {
        redirect_header($xoonips_admin['mypage_url'], 1, _AM_XOONIPS_MSG_UNEXPECTED_ERROR);
        exit();
    }
}

user_reactivate($vals['uid']);

// load modify panel.
$_GET['uid'] = $_POST['uid'];
include 'actions/maintenance_account_modify.php';
