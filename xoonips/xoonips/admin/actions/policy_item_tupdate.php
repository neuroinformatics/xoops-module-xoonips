<?php

// $Revision: 1.1.2.6 $
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
$ticket_area = 'xoonips_admin_policy_item_type';
if (!$xoopsGTicket->check(true, $ticket_area, false)) {
    redirect_header($xoonips_admin['mypage_url'], 3, $xoopsGTicket->getErrors());
    exit();
}

// get variables
$post_keys = array(
  'weight' => array(
    'i',
    true,
    false,
  ),
  'display_name' => array(
    's',
    true,
    false,
  ),
);
$post_vals = xoonips_admin_get_requests('post', $post_keys);

// update db values
$module_handler = &xoops_gethandler('module');
foreach ($post_vals['weight'] as $mid => $w) {
    $module = &$module_handler->get($mid);
    $weight_orig = $module->getVar('weight', 'n');
    if ($w != $weight_orig) {
        $module->setVar('weight', $w, true);
        $module_handler->insert($module);
    }
}
$itemtype_handler = &xoonips_getormhandler('xoonips', 'item_type');
foreach ($post_vals['display_name'] as $itid => $display_name) {
    $itemtype = &$itemtype_handler->get($itid);
    $display_name_orig = $itemtype->getVar('display_name', 'n');
    if ($display_name != $display_name_orig) {
        $itemtype->set('display_name', $display_name);
        $itemtype_handler->insert($itemtype);
    }
}

redirect_header($xoonips_admin['mypage_url'].'&amp;action=type', 3, _AM_XOONIPS_MSG_DBUPDATED);
