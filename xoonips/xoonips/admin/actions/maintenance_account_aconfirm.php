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

// this file will include from maintenance_account_modify.php
// title
$title = _AM_XOONIPS_MAINTENANCE_ACCOUNT_ACONFIRM_TITLE;

// breadcrumbs
$breadcrumbs = array(
    array(
        'type' => 'top',
        'label' => _AM_XOONIPS_TITLE,
        'url' => $xoonips_admin['admin_url'].'/',
    ),
    array(
        'type' => 'link',
        'label' => _AM_XOONIPS_MAINTENANCE_TITLE,
        'url' => $xoonips_admin['myfile_url'],
    ),
    array(
        'type' => 'link',
        'label' => _AM_XOONIPS_MAINTENANCE_ACCOUNT_TITLE,
        'url' => $xoonips_admin['mypage_url'],
    ),
    array(
        'type' => 'label',
        'label' => $title,
        'url' => '',
    ),
);

// token ticket
require_once '../class/base/gtickets.php';
$ticket_area = 'xoonips_admin_maintenance_account_aconfirm';
$token_ticket = $xoopsGTicket->getTicketHtml(__LINE__, 1800, $ticket_area);

// templates
require_once '../class/base/pattemplate.class.php';
$tmpl = new PatTemplate();
$tmpl->setBaseDir('templates');
$tmpl->readTemplatesFromFile('maintenance_account_confirm.tmpl.html');

// assign template variables
$tmpl->addVar('header', 'TITLE', $title);
$tmpl->setAttribute('breadcrumbs', 'visibility', 'visible');
$tmpl->addRows('breadcrumbs_items', $breadcrumbs);
$tmpl->addVar('main', 'token_ticket', $token_ticket);
$tmpl->addVar('main', 'confirm_msg', _AM_XOONIPS_MSG_ACTIVATE_CONFIRM);
// >> user inforamtion from
$tmpl->addVar('main', 'action', 'activate');
$tmpl->addVar('main', 'uid_title', _AM_XOONIPS_LABEL_UID);
$tmpl->addVar('main', 'uid_value', $user['xoops']['uid']);
$tmpl->addVar('main', 'uname_title', _AM_XOONIPS_LABEL_UNAME);
$tmpl->addVar('main', 'uname_value', $user['xoops']['uname']);
$tmpl->addVar('main', 'name_title', _AM_XOONIPS_LABEL_NAME);
$tmpl->addVar('main', 'name_value', $user['xoops']['name']);
$tmpl->addVar('main', 'email_title', _AM_XOONIPS_LABEL_EMAIL);
$tmpl->addVar('main', 'email_value', $user['xoops']['email']);
$tmpl->addVar('submit', 'yes', _AM_XOONIPS_LABEL_YES);
$tmpl->addVar('main', 'no', _AM_XOONIPS_LABEL_NO);

// display
xoops_cp_header();
$tmpl->displayParsedTemplate('main');
xoops_cp_footer();
