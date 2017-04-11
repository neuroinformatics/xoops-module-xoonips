<?php

// $Revision: 1.1.2.4 $
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
$title = _AM_XOONIPS_POLICY_ITEM_IMEXPORT_TITLE;
$description = _AM_XOONIPS_POLICY_ITEM_IMEXPORT_DESC;

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
$ticket_area = 'xoonips_admin_policy_item_imexport';
$token_ticket = $xoopsGTicket->getTicketHtml(__LINE__, 1800, $ticket_area);

// get configs
$config_keys = array(
    'export_enabled' => 's',
    'export_attachment' => 's',
    'private_import_enabled' => 's',
);
$config_values = xoonips_admin_get_configs($config_keys, 'e');

// >> export enabled
$export_enabled = array();
$ee['yes'] = _AM_XOONIPS_LABEL_YES;
$ee['no'] = _AM_XOONIPS_LABEL_NO;
$ee['checked'] = $config_values['export_enabled'];
$export_enabled[] = $ee;
// >> export attachment
$export_attachment = array();
$ea['yes'] = _AM_XOONIPS_LABEL_YES;
$ea['no'] = _AM_XOONIPS_LABEL_NO;
$ea['checked'] = $config_values['export_attachment'];
$export_attachment[] = $ea;
// >> private import enabled
$import_enabled = array();
$ie['yes'] = _AM_XOONIPS_LABEL_YES;
$ie['no'] = _AM_XOONIPS_LABEL_NO;
$ie['checked'] = $config_values['private_import_enabled'];
$import_enabled[] = $ie;

// templates
require_once '../class/base/pattemplate.class.php';
$tmpl = new PatTemplate();
$tmpl->setBaseDir('templates');
$tmpl->readTemplatesFromFile('policy_item_imexport.tmpl.html');

// assign template variables
$tmpl->addVar('header', 'TITLE', $title);
$tmpl->setAttribute('description', 'visibility', 'visible');
$tmpl->addVar('description', 'DESCRIPTION', $description);
$tmpl->setAttribute('breadcrumbs', 'visibility', 'visible');
$tmpl->addRows('breadcrumbs_items', $breadcrumbs);
$tmpl->addVar('main', 'TOKEN_TICKET', $token_ticket);
$tmpl->addVar('main', 'SUBMIT', _AM_XOONIPS_LABEL_UPDATE);
// export
$tmpl->addVar('main', 'EXPORT_TITLE', _AM_XOONIPS_POLICY_ITEM_IMEXPORT_EXPORT_TITLE);
// >> export enabled
$tmpl->addVar('main', 'EXPORT_ENABLED_TITLE', _AM_XOONIPS_POLICY_ITEM_IMEXPORT_EXPORT_ENABLED_TITLE);
$tmpl->addVar('main', 'EXPORT_ENABLED_DESC', _AM_XOONIPS_POLICY_ITEM_IMEXPORT_EXPORT_ENABLED_DESC);
$tmpl->addRows('export_enabled', $export_enabled);
// >> export attachment
$tmpl->addVar('main', 'EXPORT_ATTACHMENT_TITLE', _AM_XOONIPS_POLICY_ITEM_IMEXPORT_EXPORT_ATTACHMENT_TITLE);
$tmpl->addVar('main', 'EXPORT_ATTACHMENT_DESC', _AM_XOONIPS_POLICY_ITEM_IMEXPORT_EXPORT_ATTACHMENT_DESC);
$tmpl->addRows('export_attachment', $export_attachment);
// import
$tmpl->addVar('main', 'IMPORT_TITLE', _AM_XOONIPS_POLICY_ITEM_IMEXPORT_IMPORT_TITLE);
// >> private import enabled
$tmpl->addVar('main', 'IMPORT_ENABLED_TITLE', _AM_XOONIPS_POLICY_ITEM_IMEXPORT_IMPORT_ENABLED_TITLE);
$tmpl->addVar('main', 'IMPORT_ENABLED_DESC', _AM_XOONIPS_POLICY_ITEM_IMEXPORT_IMPORT_ENABLED_DESC);
$tmpl->addRows('import_enabled', $import_enabled);

// display
xoops_cp_header();
$tmpl->displayParsedTemplate('main');
xoops_cp_footer();
