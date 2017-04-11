<?php

// $Revision: 1.1.4.1.2.3 $
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
$title = _AM_XOONIPS_SYSTEM_PROXY_TITLE;
$description = _AM_XOONIPS_SYSTEM_PROXY_DESC;

// breadcrumbs
$breadcrumbs = array(
    array(
        'type' => 'top',
        'label' => _AM_XOONIPS_TITLE,
        'url' => $xoonips_admin['admin_url'].'/',
    ),
    array(
        'type' => 'link',
        'label' => _AM_XOONIPS_SYSTEM_TITLE,
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
$ticket_area = 'xoonips_admin_system_proxy';
$token_ticket = $xoopsGTicket->getTicketHtml(__LINE__, 1800, $ticket_area);

// get configs
$config_keys = array(
    'proxy_host' => 's',
    'proxy_port' => 'i',
    'proxy_user' => 's',
    'proxy_pass' => 's',
);
$config_values = xoonips_admin_get_configs($config_keys, 'e');
// >> proxy_host
$proxy_host_title = _AM_XOONIPS_SYSTEM_PROXY_PROXY_HOST_TITLE;
$proxy_host_desc = _AM_XOONIPS_SYSTEM_PROXY_PROXY_HOST_DESC;
$proxy_host = $config_values['proxy_host'];
// >> proxy_port
$proxy_port_title = _AM_XOONIPS_SYSTEM_PROXY_PROXY_PORT_TITLE;
$proxy_port_desc = _AM_XOONIPS_SYSTEM_PROXY_PROXY_PORT_DESC;
$proxy_port = $config_values['proxy_port'];
// >> proxy_user
$proxy_user_title = _AM_XOONIPS_SYSTEM_PROXY_PROXY_USER_TITLE;
$proxy_user_desc = _AM_XOONIPS_SYSTEM_PROXY_PROXY_USER_DESC;
$proxy_user = $config_values['proxy_user'];
// >> proxy_pass
$proxy_pass_title = _AM_XOONIPS_SYSTEM_PROXY_PROXY_PASS_TITLE;
$proxy_pass_desc = _AM_XOONIPS_SYSTEM_PROXY_PROXY_PASS_DESC;
$proxy_pass = $config_values['proxy_pass'];

// templates
require_once '../class/base/pattemplate.class.php';
$tmpl = new PatTemplate();
$tmpl->setBaseDir('templates');
$tmpl->readTemplatesFromFile('system_proxy.tmpl.html');

// assign template variables
$tmpl->addVar('header', 'TITLE', $title);
$tmpl->addVar('main', 'TITLE', $title);
$tmpl->setAttribute('description', 'visibility', 'visible');
$tmpl->addVar('description', 'DESCRIPTION', $description);
$tmpl->setAttribute('breadcrumbs', 'visibility', 'visible');
$tmpl->addRows('breadcrumbs_items', $breadcrumbs);
$tmpl->addVar('main', 'token_ticket', $token_ticket);
$tmpl->addVar('main', 'submit', _AM_XOONIPS_LABEL_UPDATE);

// >> proxy host
$tmpl->addVar('main', 'proxy_host_title', $proxy_host_title);
$tmpl->addVar('main', 'proxy_host_desc', $proxy_host_desc);
$tmpl->addVar('main', 'proxy_host', $proxy_host);
// >> proxy port
$tmpl->addVar('main', 'proxy_port_title', $proxy_port_title);
$tmpl->addVar('main', 'proxy_port_desc', $proxy_port_desc);
$tmpl->addVar('main', 'proxy_port', $proxy_port);
// >> proxy user
$tmpl->addVar('main', 'proxy_user_title', $proxy_user_title);
$tmpl->addVar('main', 'proxy_user_desc', $proxy_user_desc);
$tmpl->addVar('main', 'proxy_user', $proxy_user);
// >> proxy password
$tmpl->addVar('main', 'proxy_pass_title', $proxy_pass_title);
$tmpl->addVar('main', 'proxy_pass_desc', $proxy_pass_desc);
$tmpl->addVar('main', 'proxy_pass', $proxy_pass);

// display
xoops_cp_header();
$tmpl->displayParsedTemplate('main');
xoops_cp_footer();
