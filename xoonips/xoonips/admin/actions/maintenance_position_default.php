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
$title = _AM_XOONIPS_MAINTENANCE_POSITION_TITLE;
$description = _AM_XOONIPS_MAINTENANCE_POSITION_DESC;

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
        'type' => 'label',
        'label' => $title,
        'url' => '',
    ),
);

// token ticket
require_once '../class/base/gtickets.php';
$ticket_area = 'xoonips_admin_maintenance_position';
$token_ticket = $xoopsGTicket->getTicketHtml(__LINE__, 1800, $ticket_area);

// logic
// TODO: implement this into object handler
function &positions_get_userlist()
{
    global $xoopsDB;
    $xusers_handler = &xoonips_getormhandler('xoonips', 'users');
    $tables['users'] = $xoopsDB->prefix('users');
    $tables['xusers'] = $xoopsDB->prefix('xoonips_users');
    $tables['positions'] = $xoopsDB->prefix('xoonips_positions');
    $join_criteria = new XooNIpsJoinCriteria('users', 'uid', 'uid');
    $join_criteria->cascade(new XooNIpsJoinCriteria('xoonips_positions', 'posi', 'posi_id'), 'xoonips_users');
    $criteria = new Criteria($tables['xusers'].'.posi', '0', '>');
    $criteria->setSort(array($tables['positions'].'.posi_order', $tables['xusers'].'.user_order'));
    $fields = array();
    $fields[] = $tables['xusers'].'.uid';
    $fields[] = $tables['xusers'].'.user_order';
    $fields[] = $tables['users'].'.name';
    $fields[] = $tables['users'].'.uname';
    $fields[] = $tables['positions'].'.posi_title';
    $xusers_objs = &$xusers_handler->getObjects($criteria, false, implode(',', $fields), false, $join_criteria);

    return $xusers_objs;
}

$textutil = &xoonips_getutility('text');
$xusers_objs = &positions_get_userlist();
$positions = array();
$evenodd = 'odd';
foreach ($xusers_objs as $xusers_obj) {
    $uid = $xusers_obj->getVar('uid', 'e');
    $order = $xusers_obj->getVar('user_order', 'e');
    $posi = $textutil->html_special_chars($xusers_obj->getExtraVar('posi_title'));
    $name = $textutil->html_special_chars($xusers_obj->getExtraVar('name'));
    $uname = $textutil->html_special_chars($xusers_obj->getExtraVar('uname'));
    $positions[] = array(
        'uid' => $uid,
        'position' => $posi,
        'name' => ($name == '') ? $uname : $name,
        'order' => $order,
        'evenodd' => $evenodd,
    );
    $evenodd = ($evenodd == 'even') ? 'odd' : 'even';
}
$is_user_empty = (count($positions) == 0);

// templates
require_once '../class/base/pattemplate.class.php';
$tmpl = new PatTemplate();
$tmpl->setBaseDir('templates');
$tmpl->readTemplatesFromFile('maintenance_position.tmpl.html');

// assign template variables
$tmpl->addVar('header', 'TITLE', $title);
$tmpl->setAttribute('description', 'visibility', 'visible');
$tmpl->addVar('description', 'DESCRIPTION', $description);
$tmpl->setAttribute('breadcrumbs', 'visibility', 'visible');
$tmpl->addRows('breadcrumbs_items', $breadcrumbs);
$tmpl->addVar('main', 'token_ticket', $token_ticket);
$tmpl->addVar('main', 'weight', _AM_XOONIPS_LABEL_WEIGHT);
$tmpl->addVar('main', 'user_title', _AM_XOONIPS_MAINTENANCE_POSITION_USER_TITLE);
$tmpl->addVar('positions_submit', 'update', _AM_XOONIPS_LABEL_UPDATE);
$tmpl->addVar('positions_empty', 'empty', _AM_XOONIPS_MAINTENANCE_POSITION_EMPTY);
if ($is_user_empty) {
    $tmpl->setAttribute('positions', 'visibility', 'hidden');
    $tmpl->setAttribute('positions_submit', 'visibility', 'hidden');
    $tmpl->setAttribute('positions_empty', 'visibility', 'visible');
} else {
    $tmpl->addRows('positions', $positions);
}

// display
xoops_cp_header();
$tmpl->displayParsedTemplate('main');
xoops_cp_footer();
