<?php

// $Revision: 1.1.2.7 $
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
$title = _AM_XOONIPS_POLICY_ITEM_TYPE_TITLE;
$description = _AM_XOONIPS_POLICY_ITEM_TYPE_DESC;

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
$ticket_area = 'xoonips_admin_policy_item_type';
$token_ticket = $xoopsGTicket->getTicketHtml(__LINE__, 1800, $ticket_area);

// >> item type order
$module_handler = &xoops_gethandler('module');
$it_handler = &xoonips_getormhandler('xoonips', 'item_type');
$it_objs = &$it_handler->getObjectsSortByWeight();
$itemtype_order = array();
$editicon = '<img src="../images/icon_modify.png" alt="'._AM_XOONIPS_LABEL_PREFERENCES.'" title="'._AM_XOONIPS_LABEL_PREFERENCES.'"/>';
foreach ($it_objs as $it_obj) {
    // get module id
  $mid = $it_obj->get('mid');
  // get display name
  $display_name_s = $it_obj->getVar('display_name', 's');
    $display_name_e = $it_obj->getVar('display_name', 'e');
    $item_type_id = $it_obj->getVar('item_type_id', 'e');
  // get module information
  $xoonips_module = &$module_handler->getByDirname('xoonips');
    $xoonips_mid = $xoonips_module->getVar('mid');
    $module = &$module_handler->get($mid);
    $modname = $module->getVar('name', 's');
  // get admin page link
  $hasadmin = $module->getVar('hasadmin', 'n');
    if ($hasadmin) {
        $adminindex = $module->getInfo('adminindex');
        $dirname = $module->getVar('dirname', 'e');
        $adminlink = '<a href="'.XOOPS_URL.'/modules/'.$dirname.'/'.$adminindex.'">'.$editicon.'</a>';
    } else {
        $adminlink = '&nbsp;';
    }
  // get module order
  $weight = $module->getVar('weight', 'n');
    $itemtype_order[] = array(
    'mid' => $mid,
    'item_type_id' => $item_type_id,
    'display_name_s' => $display_name_s,
    'display_name_e' => $display_name_e,
    'modname' => $modname,
    'weight' => $weight,
    'admin_link' => $adminlink,
  );
    unset($module);
}
$num = 0;
foreach ($itemtype_order as $key => $itemtype) {
    $itemtype_order[$key]['evenodd'] = ($num % 2) ? 'even' : 'odd';
    ++$num;
}

// templates
require_once '../class/base/pattemplate.class.php';
$tmpl = new PatTemplate();
$tmpl->setBaseDir('templates');
$tmpl->readTemplatesFromFile('policy_item_type.tmpl.html');

// assign template variables
$tmpl->addVar('header', 'TITLE', $title);
$tmpl->setAttribute('description', 'visibility', 'visible');
$tmpl->addVar('description', 'DESCRIPTION', $description);
$tmpl->setAttribute('breadcrumbs', 'visibility', 'visible');
$tmpl->addRows('breadcrumbs_items', $breadcrumbs);
$tmpl->addVar('main', 'TOKEN_TICKET', $token_ticket);

// >> itemtype order
$tmpl->addVar('main', 'ITEMTYPE', _AM_XOONIPS_LABEL_ITEM_TYPE);
$tmpl->addVar('main', 'MODULENAME', _AM_XOONIPS_LABEL_MODULENAME);
$tmpl->addVar('main', 'WEIGHT', _AM_XOONIPS_LABEL_WEIGHT);
$tmpl->addVar('main', 'ACTION', _AM_XOONIPS_LABEL_ACTION);
$tmpl->addVar('main', 'ITEMTYPE_VIEWCONFIG_TITLE', _AM_XOONIPS_POLICY_ITEM_TYPE_VIEWCONFIG_TITLE);
$tmpl->addVar('main', 'ITEMTYPE_VIEWCONFIG_DESC', _AM_XOONIPS_POLICY_ITEM_TYPE_VIEWCONFIG_DESC);
$tmpl->addVar('itemtypes', 'NOITEMTYPES', empty($itemtype_order));
$tmpl->addVar('itemtypes', 'NOITEMTYPES_LABEL', _AM_XOONIPS_POLICY_ITEM_TYPE_EMPTY);
if (!empty($itemtype_order)) {
    $tmpl->addRows('itemtype_order', $itemtype_order);
}
$tmpl->addVar('itemtypes', 'SUBMIT', _AM_XOONIPS_LABEL_UPDATE);

// display
xoops_cp_header();
$tmpl->displayParsedTemplate('main');
xoops_cp_footer();
