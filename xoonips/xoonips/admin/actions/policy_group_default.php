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

// title
$title = _AM_XOONIPS_POLICY_GROUP_TITLE;
$description = _AM_XOONIPS_POLICY_GROUP_DESC;

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
$ticket_area = 'xoonips_admin_policy_group';
$token_ticket = $xoopsGTicket->getTicketHtml(__LINE__, 1800, $ticket_area);

// get configs
$config_keys = array();
$initvals_config_keys = array(
    'group_item_number_limit' => array(
        'title' => _AM_XOONIPS_LABEL_ITEM_NUMBER_LIMIT,
        'desc' => _AM_XOONIPS_POLICY_GROUP_INITIAL_MAX_ITEM_DESC,
        'type' => 'i',
    ),
    'group_index_number_limit' => array(
        'title' => _AM_XOONIPS_LABEL_INDEX_NUMBER_LIMIT,
        'desc' => _AM_XOONIPS_POLICY_GROUP_INITIAL_MAX_INDEX_DESC,
        'type' => 'i',
    ),
    'group_item_storage_limit' => array(
        'title' => _AM_XOONIPS_LABEL_ITEM_STORAGE_LIMIT,
        'desc' => _AM_XOONIPS_POLICY_GROUP_INITIAL_MAX_DISK_DESC,
        'type' => 'f',
    ),
);
foreach ($initvals_config_keys as $key => $value) {
    $config_keys[$key] = $value['type'];
}
$config_values = xoonips_admin_get_configs($config_keys, 'e');

// initial values
$initil_values = array();
foreach ($initvals_config_keys as $name => $value) {
    $iv = array();
    $iv['title'] = $value['title'];
    $iv['desc'] = $value['desc'];
    $iv['name'] = $name;
    if ('f' == $value['type']) {
        $iv['value'] = $config_values[$name] / 1000000.0;
    } else {
        $iv['value'] = $config_values[$name];
    }
    $initial_values[] = $iv;
}

// templates
require_once '../class/base/pattemplate.class.php';
$tmpl = new PatTemplate();
$tmpl->setBaseDir('templates');
$tmpl->readTemplatesFromFile('policy_group.tmpl.html');

// assign template variables
$tmpl->addVar('header', 'TITLE', $title);
$tmpl->setAttribute('description', 'visibility', 'visible');
$tmpl->addVar('description', 'DESCRIPTION', $description);
$tmpl->setAttribute('breadcrumbs', 'visibility', 'visible');
$tmpl->addRows('breadcrumbs_items', $breadcrumbs);
$tmpl->addVar('main', 'token_ticket', $token_ticket);
$tmpl->addVar('main', 'submit', _AM_XOONIPS_LABEL_UPDATE);

// initial values
$tmpl->addVar('main', 'initial_values_title', _AM_XOONIPS_POLICY_GROUP_INITIAL_VALUES_TITLE);
$tmpl->addRows('initial_values', $initial_values);

// display
xoops_cp_header();
$tmpl->displayParsedTemplate('main');
xoops_cp_footer();
