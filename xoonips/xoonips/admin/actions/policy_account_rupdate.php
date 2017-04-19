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
$ticket_area = 'xoonips_admin_policy_account';
if (!$xoopsGTicket->check(true, $ticket_area, false)) {
    redirect_header($xoonips_admin['mypage_url'], 3, $xoopsGTicket->getErrors());
    exit();
}

// get variables
$post_keys = array(
    'activate_user' => array('i', false, true),
    'certify_user' => array('s', false, true),
);
$post_vals = xoonips_admin_get_requests('post', $post_keys);

// activate user
$config_handler = &xoops_gethandler('config');
if (defined('XOOPS_CUBE_LEGACY')) {
    // for Cube 2.1
    $module_handler = &xoops_gethandler('module');
    $user_module = &$module_handler->getByDirname('user');
    $user_mid = $user_module->get('mid');
    $criteria = new CriteriaCompo(new Criteria('conf_modid', $user_mid));
} else {
    // for Cube 2.0
    $criteria = new CriteriaCompo(new Criteria('conf_catid', XOOPS_CONF_USER));
}
$criteria->add(new Criteria('conf_name', 'activation_type'));
$xoopsUserConfigs = &$config_handler->getConfigs($criteria);
if (count($xoopsUserConfigs) != 1) {
    redirect_header($xoonips_admin['mypage_url'], 3, _AM_XOONIPS_MSG_UNEXPECTED_ERROR);
    exit();
}

// update db values
// >> activate user
list($activation_type) = $xoopsUserConfigs;
$activation_type->setConfValueForInput($post_vals['activate_user'], true);
if (!$config_handler->insertConfig($activation_type)) {
    redirect_header($xoonips_admin['mypage_url'], 3, _AM_XOONIPS_MSG_UNEXPECTED_ERROR);
    exit();
}
// >> certify user
xoonips_admin_set_config('certify_user', $post_vals['certify_user'], 's');

redirect_header($xoonips_admin['mypage_url'], 3, _AM_XOONIPS_MSG_DBUPDATED);
