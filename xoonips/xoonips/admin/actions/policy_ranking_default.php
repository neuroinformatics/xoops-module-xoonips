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

// block resources
$langman->read('blocks.php');

// class files
require_once XOOPS_ROOT_PATH.'/class/xoopsblock.php';

// title
$title = _AM_XOONIPS_POLICY_RANKING_TITLE;
$description = _AM_XOONIPS_POLICY_RANKING_DESC;

// breadcrumbs
$breadcrumbs = array(
    array(
        'type' => 'top',
        'label' => _AM_XOONIPS_TITLE,
        'url' => $xoonips_admin['admin_url'].'/',
    ),
    array(
        'type' => 'link',
        'label' => _AM_XOONIPS_POLICY_TITLE,
        'url' => $xoonips_admin['myfile_url'],
    ),
    array(
        'type' => 'label',
        'label' => $title,
        'url' => '',
    ),
);

// token ticket
require_once '../class/base/gtickets.php';
$ticket_area = 'xoonips_admin_policy_ranking';
$token_ticket = $xoopsGTicket->getTicketHtml(__LINE__, 1800, $ticket_area);

// get configs
$config_keys = array(
    'ranking_num_rows' => 'i',
    'ranking_order' => 's',
    'ranking_visible' => 's',
    'ranking_new_num_rows' => 'i',
    'ranking_new_order' => 's',
    'ranking_new_visible' => 's',
    'ranking_days' => 'i',
    'ranking_days_enabled' => 's',
);
$config_values = xoonips_admin_get_configs($config_keys, 'e');

// functions
function xoonips_get_module_id()
{
    $module_handler = &xoops_gethandler('module');
    $module = &$module_handler->getByDirname('xoonips');
    if (!is_object($module)) {
        return false;
    }
    $mid = $module->getVar('mid');

    return $mid;
}

function get_block_title($mid, $fname, $sfunc)
{
    $block_objs = &XoopsBlock::getByModule($mid);
    $block_title = '';
    foreach ($block_objs as $block_obj) {
        $func_file = $block_obj->getVar('func_file', 'n');
        $show_func = $block_obj->getVar('show_func', 'n');
        if ($func_file == $fname && $show_func == $sfunc) {
            // found
            $block_title = $block_obj->getVar('title', 's');
            break;
        }
    }

    return $block_title;
}

function ranking_create_array($names, $order_conf, $visible_conf)
{
    $orders = array_map('intval', explode(',', $order_conf));
    $visibles = array_map('intval', explode(',', $visible_conf));
    $cnt = count($names);
    $ranking = array();
    for ($i = 0; $i < $cnt; ++$i) {
        $ranking[$orders[$i]] = array(
            'id' => $i,
            'name' => $names[$i], 'order' => $orders[$i], 'checked' => ($visibles[$i] == 1) ? 'checked="checked"' : '',
            'up' => _AM_XOONIPS_LABEL_UP, 'down' => _AM_XOONIPS_LABEL_DOWN,
        );
    }
    ksort($ranking);
    $evenodd = 'odd';
    for ($i = 0; $i < $cnt; ++$i) {
        $ranking[$i]['evenodd'] = $evenodd;
        $evenodd = ($evenodd == 'even') ? 'odd' : 'even';
    }

    return $ranking;
}

// get module id
$xoonips_mid = xoonips_get_module_id();
if ($xoonips_mid === false) {
    redirect_header($xoonips_admin['mypage_url'], 3, _AM_XOONIPS_MSG_UNEXPECTED_ERROR);
    exit();
}

// >> general ranking block
$general_ranking_title = _AM_XOONIPS_POLICY_RANKING_BLOCK_TITLE.'&nbsp;:&nbsp;'.get_block_title($xoonips_mid, 'xoonips_blocks.php', 'b_xoonips_ranking_show');
$general_ranking_names = array(
    _MB_XOONIPS_RANKING_VIEWED_ITEM,
    _MB_XOONIPS_RANKING_DOWNLOADED_ITEM,
    _MB_XOONIPS_RANKING_CONTRIBUTING_USER,
    _MB_XOONIPS_RANKING_SEARCHED_KEYWORD,
    _MB_XOONIPS_RANKING_CONTRIBUTED_GROUP,
);
$general_ranking = ranking_create_array($general_ranking_names, $config_values['ranking_order'], $config_values['ranking_visible']);
$general_ranking_numrows = $config_values['ranking_num_rows'];

// >> recent ranking block
$recent_ranking_title = _AM_XOONIPS_POLICY_RANKING_BLOCK_TITLE.'&nbsp;&nbsp;:&nbsp;&nbsp;'.get_block_title($xoonips_mid, 'xoonips_blocks.php', 'b_xoonips_ranking_new_show');
$recent_ranking_names = array(
    _MB_XOONIPS_RANKING_NEW_ITEM,
    _MB_XOONIPS_RANKING_NEW_GROUP,
);
$recent_ranking = ranking_create_array($recent_ranking_names, $config_values['ranking_new_order'], $config_values['ranking_new_visible']);
$recent_ranking_numrows = $config_values['ranking_new_num_rows'];

// >> calculation days
$ranking_days = $config_values['ranking_days'];
$ranking_days_checked = ($config_values['ranking_days_enabled'] == 'on') ? 'checked="checked"' : '';

// templates
require_once '../class/base/pattemplate.class.php';
$tmpl = new PatTemplate();
$tmpl->setBaseDir('templates');
$tmpl->readTemplatesFromFile('policy_ranking.tmpl.html');

// assign template variables
$tmpl->addVar('header', 'TITLE', $title);
$tmpl->setAttribute('description', 'visibility', 'visible');
$tmpl->addVar('description', 'DESCRIPTION', $description);
$tmpl->setAttribute('breadcrumbs', 'visibility', 'visible');
$tmpl->addRows('breadcrumbs_items', $breadcrumbs);
$tmpl->addVar('main', 'token_ticket', $token_ticket);
$tmpl->addVar('main', 'submit', _AM_XOONIPS_LABEL_UPDATE);

// >> general ranking
$tmpl->addVar('main', 'general_ranking_title', $general_ranking_title);
$tmpl->addVar('main', 'visible', _AM_XOONIPS_LABEL_VISIBLE);
$tmpl->addVar('main', 'ranking_name', _AM_XOONIPS_POLICY_RANKING_NAME_TITLE);
$tmpl->addVar('main', 'sort', _AM_XOONIPS_LABEL_SORT);
$tmpl->addVar('main', 'general_range_desc1', _AM_XOONIPS_POLICY_RANKING_RANGE_DESC1);
$tmpl->addVar('main', 'general_range_desc2', _AM_XOONIPS_POLICY_RANKING_RANGE_DESC2);
$tmpl->addVar('main', 'general_numrows', $general_ranking_numrows);
$tmpl->addRows('general_ranking', $general_ranking);
// >> recent ranking
$tmpl->addVar('main', 'recent_ranking_title', $recent_ranking_title);
$tmpl->addVar('main', 'recent_range_desc1', _AM_XOONIPS_POLICY_RANKING_RANGE_DESC1);
$tmpl->addVar('main', 'recent_range_desc2', _AM_XOONIPS_POLICY_RANKING_RANGE_DESC2);
$tmpl->addVar('main', 'recent_numrows', $recent_ranking_numrows);
$tmpl->addRows('recent_ranking', $recent_ranking);
// >> calcuration days
$tmpl->addVar('main', 'days_title', _AM_XOONIPS_POLICY_RANKING_DAYS_TITLE);
$tmpl->addVar('main', 'days_desc1', _AM_XOONIPS_POLICY_RANKING_DAYS_DESC1);
$tmpl->addVar('main', 'days_desc2', _AM_XOONIPS_POLICY_RANKING_DAYS_DESC2);
$tmpl->addVar('main', 'days_desc3', _AM_XOONIPS_POLICY_RANKING_DAYS_DESC3);
$tmpl->addVar('main', 'days_checked', $ranking_days_checked);
$tmpl->addVar('main', 'days', $ranking_days);

// display
xoops_cp_header();
$tmpl->displayParsedTemplate('main');
xoops_cp_footer();
