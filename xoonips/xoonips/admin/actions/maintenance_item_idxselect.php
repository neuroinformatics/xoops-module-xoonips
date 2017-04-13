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
/**
 * select index page.
 *
 * this file will include from 'maintenance_item_withdraw.php' or
 * 'maintenance_item_delete.php'
 *
 * requrement variables
 *
 * @var string page title
 * @var string $description page description
 * @var string $ticket_area area name of token ticket
 * @var string $index_mode index mode
 *             'private' : private index
 *             'public' : public index
 * @var int    $uid user id. this variable only use when $index_mode is 'private'
 * @var bool   $has_back has back button?
 * @var string $confrim_desc confirmation description
 * @var string $confrim confirmation message
 * @var string $nextaction next action
 * @var string $submit submit button label
 */
if (!defined('XOOPS_ROOT_PATH')) {
    exit();
}

// load libraries
require_once '../include/libitem.php';

$treelist = array();
switch ($index_mode) {
case 'private':
    $treelist = xnpitmgrListIndexTree(XNPITMGR_LISTMODE_PRIVATEONLY, $uid);
    break;
case 'public':
    $treelist = xnpitmgrListIndexTree(XNPITMGR_LISTMODE_PUBLICONLY);
    break;
}
if (empty($treelist)) {
    die('unexpected error');
}

// token ticket
require_once '../class/base/gtickets.php';
$token_ticket = $xoopsGTicket->getTicketHtml(__LINE__, 1800, $ticket_area);

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
        'label' => _AM_XOONIPS_MAINTENANCE_ITEM_TITLE,
        'url' => $xoonips_admin['mypage_url'],
    ),
    array(
        'type' => 'label',
        'label' => $title,
        'url' => '',
    ),
);

// templates
require_once '../class/base/pattemplate.class.php';
$tmpl = new PatTemplate();
$tmpl->setBaseDir('templates');
$tmpl->readTemplatesFromFile('maintenance_item_idxselect.tmpl.html');
$tmpl->addVar('header', 'TITLE', $title);
$tmpl->setAttribute('description', 'visibility', 'visible');
$tmpl->addVar('description', 'DESCRIPTION', $description);
$tmpl->setAttribute('breadcrumbs', 'visibility', 'visible');
$tmpl->addRows('breadcrumbs_items', $breadcrumbs);
$tmpl->addVar('main', 'token_ticket', $token_ticket);
$tmpl->addRows('treelist', $treelist);
if ($has_back) {
    $tmpl->setAttribute('back', 'visibility', 'visible');
    $tmpl->addVar('back', 'back', _AM_XOONIPS_LABEL_BACK);
}
$tmpl->addVar('main', 'submit', $submit);
$tmpl->addVar('submit_javascript', 'confirm_desc', $confirm_desc);
$tmpl->addVar('submit_javascript', 'confirm', $confirm);
$tmpl->addVar('submit_javascript', 'action', $nextaction);
$tmpl->addVar('submit_javascript', 'select_index', _AM_XOONIPS_MAINTENANCE_ITEM_MSG_SELECT_INDEX);

xoops_cp_header();
$tmpl->displayParsedTemplate('main');
xoops_cp_footer();
