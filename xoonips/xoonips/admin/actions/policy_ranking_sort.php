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
$ticket_area = 'xoonips_admin_policy_ranking';
if (!$xoopsGTicket->check(true, $ticket_area, false)) {
    redirect_header($xoonips_admin['mypage_url'], 3, $xoopsGTicket->getErrors());
    exit();
}

// get requests
$post_keys = array(
    'sort_ranking' => array('s', false, true),
    // block, 'general' or 'recent'
    'sort_id' => array('i', false, true),
    // target id
    'sort_updown' => array('s', false, true),
    // sort order, 'up' or 'down',
);
$post_vals = xoonips_admin_get_requests('post', $post_keys);
$sort_ranking = $post_vals['sort_ranking'];
$sort_id = $post_vals['sort_id'];
$sort_updown = $post_vals['sort_updown'];

if (!in_array($sort_ranking, array('general', 'recent')) || !in_array($sort_updown, array('up', 'down'))) {
    redirect_header($xoonips_admin['mypage_url'], 3, _AM_XOONIPS_MSG_ILLACCESS);
    exit();
}

switch ($sort_ranking) {
case 'general':
    $max_num = 5;
    $step_key = 'general_step';
    $order_key = 'ranking_order';
    break;
case 'recent':
    $max_num = 2;
    $step_key = 'recent_step';
    $order_key = 'ranking_new_order';
    break;
}
$step_requests = array(
    $step_key => array('i', true, true),
);
$step_vals = xoonips_admin_get_requests('post', $step_requests);
if ($sort_id >= $max_num || $sort_id < 0 || !isset($step_vals[$step_key][$sort_id])) {
    redirect_header($xoonips_admin['mypage_url'], 3, _AM_XOONIPS_MSG_ILLACCESS);
    exit();
}
$sort_step = $step_vals[$step_key][$sort_id];
$config_keys = array(
  $order_key => 's',
);
$config_values = xoonips_admin_get_configs($config_keys, 'n');
$current_orders = array_map('intval', explode(',', $config_values[$order_key]));
// adjust sort_step
if ($sort_updown == 'up') {
    if (($current_orders[$sort_id] - $sort_step) < 0) {
        $sort_step = $current_orders[$sort_id];
    }
    $sort_diff = -$sort_step;
} else {
    if (($current_orders[$sort_id] + $sort_step) >= $max_num) {
        $sort_step = $max_num - $current_orders[$sort_id] - 1;
    }
    $sort_diff = $sort_step;
}
$new_orders = array();
if ($sort_updown == 'up') {
    $area_min = $current_orders[$sort_id] + $sort_diff;
    $area_max = $current_orders[$sort_id];
} else {
    $area_min = $current_orders[$sort_id];
    $area_max = $current_orders[$sort_id] + $sort_diff;
}
for ($i = 0; $i < $max_num; ++$i) {
    if ($i == $sort_id) {
        $new_orders[$sort_id] = $current_orders[$sort_id] + $sort_diff;
    } else {
        if ($current_orders[$i] >= $area_min && $current_orders[$i] <= $area_max) {
            if ($sort_updown == 'up') {
                $new_orders[$i] = $current_orders[$i] + 1;
            } else {
                $new_orders[$i] = $current_orders[$i] - 1;
            }
        } else {
            $new_orders[$i] = $current_orders[$i];
        }
    }
}
$current_order = implode(',', $current_orders);
$new_order = implode(',', $new_orders);

// update db values
if ($current_order != $new_order) {
    xoonips_admin_set_config($order_key, $new_order, 's');
}

redirect_header($xoonips_admin['mypage_url'], 1, _AM_XOONIPS_MSG_DBUPDATED);
