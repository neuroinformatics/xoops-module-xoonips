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
$title = _AM_XOONIPS_POLICY_POSITION_TITLE;
$description = _AM_XOONIPS_POLICY_POSITION_DESC;

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
$ticket_area = 'xoonips_admin_policy_position';
$token_ticket = $xoopsGTicket->getTicketHtml(__LINE__, 1800, $ticket_area);

// >> positions
$posi_handler = &xoonips_getormhandler('xoonips', 'positions');
$posi_array = $posi_handler->getPositionList('e');
$positions = array();
$evenodd = 'odd';
foreach ($posi_array as $posi_id => $posi) {
    $positions[] = array(
    'id' => $posi_id,
    'order' => $posi['posi_order'],
    'title_e' => $posi['posi_title'],
    'title_s' => $posi['posi_title'],
    'title_js' => str_replace('&#039;', '\\\'', $posi['posi_title']),
    'evenodd' => $evenodd,
    'delete' => _AM_XOONIPS_LABEL_DELETE,
  );
    $evenodd = ($evenodd == 'even') ? 'odd' : 'even';
}

// templates
require_once '../class/base/pattemplate.class.php';
$tmpl = new PatTemplate();
$tmpl->setBaseDir('templates');
$tmpl->readTemplatesFromFile('policy_position.tmpl.html');

// assign template variables
$tmpl->addVar('header', 'TITLE', $title);
$tmpl->setAttribute('description', 'visibility', 'visible');
$tmpl->addVar('description', 'DESCRIPTION', $description);
$tmpl->setAttribute('breadcrumbs', 'visibility', 'visible');
$tmpl->addRows('breadcrumbs_items', $breadcrumbs);
$tmpl->addVar('main', 'token_ticket', $token_ticket);
$tmpl->addVar('main', 'position', _AM_XOONIPS_LABEL_POSITION);
$tmpl->addVar('main', 'weight', _AM_XOONIPS_LABEL_WEIGHT);
$tmpl->addVar('main', 'action', _AM_XOONIPS_LABEL_ACTION);
$tmpl->addVar('delete_javascript', 'delete_confirm', _AM_XOONIPS_MSG_DELETE_CONFIRM);
$tmpl->addVar('delete_javascript', 'position', _AM_XOONIPS_LABEL_POSITION);
// >> position list
$tmpl->addVar('main', 'list_title', _AM_XOONIPS_POLICY_POSITION_MODIFY_TITLE);
if (empty($positions)) {
    $tmpl->setAttribute('position_list', 'visibility', 'hidden');
    $tmpl->setAttribute('position_list_submit', 'visibility', 'hidden');
    $tmpl->setAttribute('position_list_empty', 'visibility', 'visible');
    $tmpl->addVar('position_list_empty', 'empty', _AM_XOONIPS_MSG_EMPTY);
} else {
    $tmpl->addRows('position_list', $positions);
    $tmpl->addVar('position_list_submit', 'submit', _AM_XOONIPS_LABEL_UPDATE);
}
// >> add position
$tmpl->addVar('main', 'add_title', _AM_XOONIPS_POLICY_POSITION_ADD_TITLE);
$tmpl->addVar('main', 'submit', _AM_XOONIPS_LABEL_ADD);

// display
xoops_cp_header();
$tmpl->displayParsedTemplate('main');
xoops_cp_footer();
