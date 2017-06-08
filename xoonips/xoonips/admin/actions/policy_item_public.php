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
$title = _AM_XOONIPS_POLICY_ITEM_PUBLIC_TITLE;
$description = _AM_XOONIPS_POLICY_ITEM_PUBLIC_DESC;

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
        'type' => 'link',
        'label' => _AM_XOONIPS_POLICY_ITEM_TITLE,
        'url' => $xoonips_admin['myfile_url'].'?page=item',
    ),
    array(
        'type' => 'label',
        'label' => $title,
        'url' => '',
    ),
);

// token ticket
require_once '../class/base/gtickets.php';
$ticket_area = 'xoonips_admin_policy_item_public';
$token_ticket = $xoopsGTicket->getTicketHtml(__LINE__, 1800, $ticket_area);

// get configs
$config_keys = array(
    'certify_item' => 's',
    'public_item_target_user' => 's',
    'download_file_compression' => 's',
    'item_show_optional' => 's',
);
$config_values = xoonips_admin_get_configs($config_keys, 'e');

// >> certify item
$certify_item = array();
$ci['value'] = 'on';
$ci['label'] = _AM_XOONIPS_POLICY_ITEM_PUBLIC_CERTIFY_ITEM_MANUAL;
$ci['selected'] = ($config_values['certify_item'] == 'on') ? 'yes' : 'no';
$certify_item[] = $ci;
$ci['value'] = 'auto';
$ci['label'] = _AM_XOONIPS_POLICY_ITEM_PUBLIC_CERTIFY_ITEM_AUTO;
$ci['selected'] = ($config_values['certify_item'] == 'auto') ? 'yes' : 'no';
$certify_item[] = $ci;
// >> public item target user
$target_user = array();
$tu['value'] = 'platform';
$tu['label'] = _AM_XOONIPS_POLICY_ITEM_PUBLIC_TARGET_USER_PLATFORM;
$tu['selected'] = ($config_values['public_item_target_user'] == 'platform') ? 'yes' : 'no';
$target_user[] = $tu;
$tu['value'] = 'all';
$tu['label'] = _AM_XOONIPS_POLICY_ITEM_PUBLIC_TARGET_USER_ALL;
$tu['selected'] = ($config_values['public_item_target_user'] == 'all') ? 'yes' : 'no';
$target_user[] = $tu;
// >> download file compression
$download_file = array();
$df['value'] = 'on';
$df['label'] = _AM_XOONIPS_POLICY_ITEM_PUBLIC_DOWNLOAD_FILE_ZIP;
$df['selected'] = ($config_values['download_file_compression'] == 'on') ? 'yes' : 'no';
$download_file[] = $df;
$df['value'] = 'off';
$df['label'] = _AM_XOONIPS_POLICY_ITEM_PUBLIC_DOWNLOAD_FILE_PLAIN;
$df['selected'] = ($config_values['download_file_compression'] == 'off') ? 'yes' : 'no';
$download_file[] = $df;
// >>  item show optional
$item_show = array();
$is['yes'] = _AM_XOONIPS_LABEL_YES;
$is['no'] = _AM_XOONIPS_LABEL_NO;
$is['checked'] = $config_values['item_show_optional'];
$item_show[] = $is;

// templates
require_once '../class/base/pattemplate.class.php';
$tmpl = new PatTemplate();
$tmpl->setBaseDir('templates');
$tmpl->readTemplatesFromFile('policy_item_public.tmpl.html');

// assign template variables
$tmpl->addVar('header', 'TITLE', $title);
$tmpl->setAttribute('description', 'visibility', 'visible');
$tmpl->addVar('description', 'DESCRIPTION', $description);
$tmpl->setAttribute('breadcrumbs', 'visibility', 'visible');
$tmpl->addRows('breadcrumbs_items', $breadcrumbs);
$tmpl->addVar('main', 'TOKEN_TICKET', $token_ticket);
$tmpl->addVar('main', 'SUBMIT', _AM_XOONIPS_LABEL_UPDATE);
// publication
$tmpl->addVar('main', 'PUBLIC_TITLE', _AM_XOONIPS_POLICY_ITEM_PUBLIC_MAIN_TITLE);
// >> certify user
$tmpl->addVar('main', 'CERTIFY_ITEM_TITLE', _AM_XOONIPS_POLICY_ITEM_PUBLIC_CERTIFY_ITEM_TITLE);
$tmpl->addVar('main', 'CERTIFY_ITEM_DESC', _AM_XOONIPS_POLICY_ITEM_PUBLIC_CERTIFY_ITEM_DESC);
$tmpl->addRows('certify_item', $certify_item);
// >> public item target user
$tmpl->addVar('main', 'TARGET_USER_TITLE', _AM_XOONIPS_POLICY_ITEM_PUBLIC_TARGET_USER_TITLE);
$tmpl->addVar('main', 'TARGET_USER_DESC', _AM_XOONIPS_POLICY_ITEM_PUBLIC_TARGET_USER_DESC);
$tmpl->addRows('target_user', $target_user);
// other things
$tmpl->addVar('main', 'OTHER_TITLE', _AM_XOONIPS_POLICY_ITEM_PUBLIC_OTHER_TITLE);
// >> download file compression
$tmpl->addVar('main', 'DOWNLOAD_FILE_TITLE', _AM_XOONIPS_POLICY_ITEM_PUBLIC_DOWNLOAD_FILE_TITLE);
$tmpl->addVar('main', 'DOWNLOAD_FILE_DESC', _AM_XOONIPS_POLICY_ITEM_PUBLIC_DOWNLOAD_FILE_DESC);
$tmpl->addRows('download_file', $download_file);
// >>  item show optional
$tmpl->addVar('main', 'ITEM_SHOW_TITLE', _AM_XOONIPS_POLICY_ITEM_PUBLIC_ITEM_SHOW_TITLE);
$tmpl->addVar('main', 'ITEM_SHOW_DESC', _AM_XOONIPS_POLICY_ITEM_PUBLIC_ITEM_SHOW_DESC);
$tmpl->addRows('item_show', $item_show);

// display
xoops_cp_header();
$tmpl->displayParsedTemplate('main');
xoops_cp_footer();
