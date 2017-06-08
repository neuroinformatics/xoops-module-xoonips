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
$ticket_area = 'xoonips_admin_policy_ranking';
if (!$xoopsGTicket->check(true, $ticket_area, false)) {
    redirect_header($xoonips_admin['mypage_url'], 3, $xoopsGTicket->getErrors());
    exit();
}

// get requests
$post_keys = array(
    'ranking_num_rows' => array('i', false, true),
    'ranking_new_num_rows' => array('i', false, true),
    'ranking_days' => array('i', false, true),
    'ranking_days_enabled' => array('s', false, false),
    // checkbox
    'ranking_visible' => array('i', true, false),
    // checkbox
    'ranking_new_visible' => array('i', true, false),
    // checkbox,
);
$post_vals = xoonips_admin_get_requests('post', $post_keys);

// set config keys
$config_keys = array();
foreach ($post_keys as $key => $attributes) {
    list($data_type, $is_array, $required) = $attributes;
    $config_keys[$key] = $data_type;
}

// >> ranking_days_enabled 'on' or null
if (is_null($post_vals['ranking_days_enabled'])) {
    $post_vals['ranking_days_enabled'] = '';
}
// >> visible
$post_array_keys = array(
    'ranking_visible' => 5,
    'ranking_new_visible' => 2,
);
foreach ($post_array_keys as $key => $max_num) {
    $val = $post_vals[$key];
    for ($i = 0; $i < $max_num; ++$i) {
        if (!isset($val[$i])) {
            $val[$i] = 0;
        }
    }
    ksort($val);
    $post_vals[$key] = implode(',', $val);
    $config_keys[$key] = 's';
}

// update db values
foreach ($config_keys as $key => $type) {
    xoonips_admin_set_config($key, $post_vals[$key], $type);
}

// recalc ranking data
$admin_ranking_handler = &xoonips_gethandler('xoonips', 'admin_ranking');
$admin_ranking_handler->rebuild();

redirect_header($xoonips_admin['mypage_url'], 1, _AM_XOONIPS_MSG_DBUPDATED);
