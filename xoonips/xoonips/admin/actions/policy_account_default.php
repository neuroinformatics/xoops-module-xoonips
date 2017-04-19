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

// title
$title = _AM_XOONIPS_POLICY_ACCOUNT_TITLE;
$description = _AM_XOONIPS_POLICY_ACCOUNT_DESC;

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
$ticket_area = 'xoonips_admin_policy_account';
$token_ticket = $xoopsGTicket->getTicketHtml(__LINE__, 1800, $ticket_area);

// get configs
$config_keys = array(
    'certify_user' => 's',
);
$userinfo_config_keys = array(
    'account_realname_optional' => _AM_XOONIPS_LABEL_NAME,
    'account_company_name_optional' => _AM_XOONIPS_LABEL_COMPANY_NAME,
    'account_division_optional' => _AM_XOONIPS_LABEL_DIVISION,
    'account_country_optional' => _AM_XOONIPS_LABEL_COUNTRY,
    'account_address_optional' => _AM_XOONIPS_LABEL_ADDRESS,
    'account_zipcode_optional' => _AM_XOONIPS_LABEL_ZIPCODE,
    'account_tel_optional' => _AM_XOONIPS_LABEL_TEL,
    'account_fax_optional' => _AM_XOONIPS_LABEL_FAX,
);
$initvals_config_keys = array(
    'private_item_number_limit' => array(
        'title' => _AM_XOONIPS_LABEL_ITEM_NUMBER_LIMIT,
        'desc' => _AM_XOONIPS_POLICY_ACCOUNT_INITIAL_MAX_ITEM_DESC,
        'type' => 'i',
    ),
    'private_index_number_limit' => array(
        'title' => _AM_XOONIPS_LABEL_INDEX_NUMBER_LIMIT,
        'desc' => _AM_XOONIPS_POLICY_ACCOUNT_INITIAL_MAX_INDEX_DESC,
        'type' => 'i',
    ),
    'private_item_storage_limit' => array(
        'title' => _AM_XOONIPS_LABEL_ITEM_STORAGE_LIMIT,
        'desc' => _AM_XOONIPS_POLICY_ACCOUNT_INITIAL_MAX_DISK_DESC,
        'type' => 'f',
    ),
);
foreach (array_keys($userinfo_config_keys) as $key) {
    $config_keys[$key] = 's';
}
foreach ($initvals_config_keys as $key => $value) {
    $config_keys[$key] = $value['type'];
}
$config_values = xoonips_admin_get_configs($config_keys, 'e');

// >> activate user
$myxoopsConfigUser = &xoonips_get_xoops_configs(XOOPS_CONF_USER);
$au['label'] = _AM_XOONIPS_POLICY_ACCOUNT_ACTIVATE_USER_USER;
$au['value'] = '0';
$au['selected'] = ($myxoopsConfigUser['activation_type'] == 0) ? 'yes' : 'no';
$activate_user[] = $au;
$au['label'] = _AM_XOONIPS_POLICY_ACCOUNT_ACTIVATE_USER_AUTO;
$au['value'] = '1';
$au['selected'] = ($myxoopsConfigUser['activation_type'] == 1) ? 'yes' : 'no';
$activate_user[] = $au;
$au['label'] = _AM_XOONIPS_POLICY_ACCOUNT_ACTIVATE_USER_ADMIN;
$au['value'] = '2';
$au['selected'] = ($myxoopsConfigUser['activation_type'] == 2) ? 'yes' : 'no';
$activate_user[] = $au;
// >> certify user
$cu['label'] = _AM_XOONIPS_POLICY_ACCOUNT_CERTIFY_USER_MANUAL;
$cu['value'] = 'on';
$cu['selected'] = ($config_values['certify_user'] == 'on') ? 'yes' : 'no';
$certify_user[] = $cu;
$cu['label'] = _AM_XOONIPS_POLICY_ACCOUNT_CERTIFY_USER_AUTO;
$cu['value'] = 'auto';
$cu['selected'] = ($config_values['certify_user'] == 'auto') ? 'yes' : 'no';
$certify_user[] = $cu;

// user information requirements
$info_requirement = array();
foreach ($userinfo_config_keys as $name => $label) {
    $ir = array();
    $ir['title'] = $label;
    $ir['name'] = $name;
    $ir['require'] = _AM_XOONIPS_LABEL_REQUIRED;
    $ir['optional'] = _AM_XOONIPS_LABEL_OPTIONAL;
    $ir['checked'] = ($config_values[$name] == 'on') ? 'yes' : 'no';
    $info_requirement[] = $ir;
}

// initial values
$initil_values = array();
foreach ($initvals_config_keys as $name => $value) {
    $iv = array();
    $iv['title'] = $value['title'];
    $iv['desc'] = $value['desc'];
    $iv['name'] = $name;
    if ($value['type'] == 'f') {
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
$tmpl->readTemplatesFromFile('policy_account.tmpl.html');

// assign template variables
$tmpl->addVar('header', 'TITLE', $title);
$tmpl->setAttribute('description', 'visibility', 'visible');
$tmpl->addVar('description', 'DESCRIPTION', $description);
$tmpl->setAttribute('breadcrumbs', 'visibility', 'visible');
$tmpl->addRows('breadcrumbs_items', $breadcrumbs);
$tmpl->addVar('main', 'token_ticket', $token_ticket);
$tmpl->addVar('main', 'submit', _AM_XOONIPS_LABEL_UPDATE);
// register user
$tmpl->addVar('main', 'register_user_title', _AM_XOONIPS_POLICY_ACCOUNT_REGISTER_USER_TITLE);
// >> activate user
$tmpl->addVar('main', 'activate_user_title', _AM_XOONIPS_POLICY_ACCOUNT_ACTIVATE_USER_TITLE);
$tmpl->addVar('main', 'activate_user_desc', _AM_XOONIPS_POLICY_ACCOUNT_ACTIVATE_USER_DESC);
$tmpl->addRows('activate_user', $activate_user);
// >> certify user
$tmpl->addVar('main', 'certify_user_title', _AM_XOONIPS_POLICY_ACCOUNT_CERTIFY_USER_TITLE);
$tmpl->addVar('main', 'certify_user_desc', _AM_XOONIPS_POLICY_ACCOUNT_CERTIFY_USER_DESC);
$tmpl->addRows('certify_user', $certify_user);
// user information requirements
$tmpl->addVar('main', 'info_requirement_title', _AM_XOONIPS_POLICY_ACCOUNT_INFO_REQUIREMENT_TITLE);
$tmpl->addRows('info_requirement', $info_requirement);
// initial values
$tmpl->addVar('main', 'initial_values_title', _AM_XOONIPS_POLICY_ACCOUNT_INITIAL_VALUES_TITLE);
$tmpl->addRows('initial_values', $initial_values);

// display
xoops_cp_header();
$tmpl->displayParsedTemplate('main');
xoops_cp_footer();
