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
$title = _AM_XOONIPS_SYSTEM_BASIC_TITLE;
$description = _AM_XOONIPS_SYSTEM_BASIC_DESC;

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
$ticket_area = 'xoonips_admin_system_basic';
$token_ticket = $xoopsGTicket->getTicketHtml(__LINE__, 1800, $ticket_area);

// get configs
$config_keys = array(
    'moderator_gid' => 'i',
    'upload_dir' => 's',
    'magic_file_path' => 's',
);
$config_values = xoonips_admin_get_configs($config_keys, 'e');
// >> moderator_gid
$moderator_gid_title = _AM_XOONIPS_SYSTEM_BASIC_MODERATOR_GROUP_TITLE;
$moderator_gid_desc = _AM_XOONIPS_SYSTEM_BASIC_MODERATOR_GROUP_DESC;
$xmember_handler = &xoops_gethandler('member');
$grouplist = &$xmember_handler->getGroupList(new Criteria('groupid', XOOPS_GROUP_ANONYMOUS, '!='));
$moderator_gid = array();
foreach ($grouplist as $gid => $name) {
    $selected = ($gid == $config_values['moderator_gid']) ? 'yes' : 'no';
    $moderator_gid[] = array(
        'label' => $name,
        'value' => $gid,
        'selected' => $selected,
    );
}
// >> upload_dir
$upload_dir_title = _AM_XOONIPS_SYSTEM_BASIC_UPLOAD_DIR_TITLE;
$upload_dir_desc = _AM_XOONIPS_SYSTEM_BASIC_UPLOAD_DIR_DESC;
$upload_dir = $config_values['upload_dir'];

// >> magic_file_path
$magic_file_path_title = _AM_XOONIPS_SYSTEM_BASIC_MAGIC_FILE_PATH_TITLE;
$magic_file_path_desc = _AM_XOONIPS_SYSTEM_BASIC_MAGIC_FILE_PATH_DESC;
$magic_file_path = $config_values['magic_file_path'];

// templates
require_once '../class/base/pattemplate.class.php';
$tmpl = new PatTemplate();
$tmpl->setBaseDir('templates');
$tmpl->readTemplatesFromFile('system_basic.tmpl.html');

// assign template variables
$tmpl->addVar('header', 'TITLE', $title);
$tmpl->addVar('main', 'TITLE', $title);
$tmpl->setAttribute('description', 'visibility', 'visible');
$tmpl->addVar('description', 'DESCRIPTION', $description);
$tmpl->setAttribute('breadcrumbs', 'visibility', 'visible');
$tmpl->addRows('breadcrumbs_items', $breadcrumbs);
$tmpl->addVar('main', 'token_ticket', $token_ticket);
$tmpl->addVar('main', 'submit', _AM_XOONIPS_LABEL_UPDATE);

// >> moderator gid
$tmpl->addVar('main', 'moderator_gid_title', $moderator_gid_title);
$tmpl->addVar('main', 'moderator_gid_desc', $moderator_gid_desc);
$tmpl->addRows('moderator_gid', $moderator_gid);
// >> file upload directory
$tmpl->addVar('main', 'upload_dir_title', $upload_dir_title);
$tmpl->addVar('main', 'upload_dir_desc', $upload_dir_desc);
$tmpl->addVar('main', 'upload_dir', $upload_dir);
// >> magic file path for fileinfo functions
$tmpl->addVar('main', 'magic_file_path_title', $magic_file_path_title);
$tmpl->addVar('main', 'magic_file_path_desc', $magic_file_path_desc);
$tmpl->addVar('main', 'magic_file_path', $magic_file_path);

// display
xoops_cp_header();
$tmpl->displayParsedTemplate('main');
xoops_cp_footer();
