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
$title = _AM_XOONIPS_SYSTEM_TREE_TITLE;
$description = _AM_XOONIPS_SYSTEM_TREE_DESC;

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
$ticket_area = 'xoonips_admin_system_tree';
$token_ticket = $xoopsGTicket->getTicketHtml(__LINE__, 1800, $ticket_area);

// get configs
$config_keys = array(
    'tree_frame_width' => 's',
    'tree_frame_height' => 's',
);
$config_values = xoonips_admin_get_configs($config_keys, 'e');
// >> tree_frame_width
$tree_frame_width_title = _AM_XOONIPS_SYSTEM_TREE_TREE_FRAME_WIDTH_TITLE;
$tree_frame_width_desc = _AM_XOONIPS_SYSTEM_TREE_TREE_FRAME_WIDTH_DESC;
$tree_frame_width = $config_values['tree_frame_width'];
// >> tree_frame_height
$tree_frame_height_title = _AM_XOONIPS_SYSTEM_TREE_TREE_FRAME_HEIGHT_TITLE;
$tree_frame_height_desc = _AM_XOONIPS_SYSTEM_TREE_TREE_FRAME_HEIGHT_DESC;
$tree_frame_height = $config_values['tree_frame_height'];

// templates
require_once '../class/base/pattemplate.class.php';
$tmpl = new PatTemplate();
$tmpl->setBaseDir('templates');
$tmpl->readTemplatesFromFile('system_tree.tmpl.html');

// assign template variables
$tmpl->addVar('header', 'TITLE', $title);
$tmpl->addVar('main', 'TITLE', $title);
$tmpl->setAttribute('description', 'visibility', 'visible');
$tmpl->addVar('description', 'DESCRIPTION', $description);
$tmpl->setAttribute('breadcrumbs', 'visibility', 'visible');
$tmpl->addRows('breadcrumbs_items', $breadcrumbs);
$tmpl->addVar('main', 'token_ticket', $token_ticket);
$tmpl->addVar('main', 'submit', _AM_XOONIPS_LABEL_UPDATE);

// >> width of index tree frame
$tmpl->addVar('main', 'tree_frame_width_title', $tree_frame_width_title);
$tmpl->addVar('main', 'tree_frame_width_desc', $tree_frame_width_desc);
$tmpl->addVar('main', 'tree_frame_width', $tree_frame_width);
// >> height of index tree frame
$tmpl->addVar('main', 'tree_frame_height_title', $tree_frame_height_title);
$tmpl->addVar('main', 'tree_frame_height_desc', $tree_frame_height_desc);
$tmpl->addVar('main', 'tree_frame_height', $tree_frame_height);

// display
xoops_cp_header();
$tmpl->displayParsedTemplate('main');
xoops_cp_footer();
