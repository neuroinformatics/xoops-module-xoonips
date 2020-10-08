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
/*
 * select index page.
 *
 * this file will include from 'maintenance_item_delete.php'
 *
 * requrement variables
 *
 * @var string page title
 * @var int    $upage page for user selection
 * @var string $nextaction next action
 */
defined('XOOPS_ROOT_PATH') || exit('XOOPS root path not defined');

// functions
function item_get_userlist($upage)
{
    global $xoopsDB;
    global $xoopsUser;
    // myuid
    $myuid = $xoopsUser->getVar('uid', 'n');
    $xusers_handler = &xoonips_getormhandler('xoonips', 'users');
    $tables['users'] = $xoopsDB->prefix('users');
    $tables['xusers'] = $xoopsDB->prefix('xoonips_users');
    $join_criteria = new XooNIpsJoinCriteria('users', 'uid', 'uid');
    $criteria = new Criteria($tables['users'].'.level', 0, '>');
    $criteria->setSort($tables['users'].'.uname');
    $fields = array();
    $fields[] = $tables['xusers'].'.uid';
    $fields[] = $tables['users'].'.uname';
    $xusers_objs = &$xusers_handler->getObjects($criteria, false, implode(',', $fields), false, $join_criteria);

    $textutil = &xoonips_getutility('text');
    $users = array();
    $users[] = array(
        'uid' => 0,
        'uname' => $textutil->html_special_chars(_AM_XOONIPS_MAINTENANCE_ITEM_LABEL_ALLUSERS),
        'selected' => 'selected="selected"',
    );
    foreach ($xusers_objs as $xusers_obj) {
        $uid = $xusers_obj->getVar('uid', 'e');
        $uname = $textutil->html_special_chars($xusers_obj->getExtraVar('uname'));
        $users[] = array(
          'uid' => $uid,
          'uname' => $uname,
          'selected' => '',
        );
    }

    return $users;
}

$userlist = item_get_userlist($upage);

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
$tmpl->readTemplatesFromFile('maintenance_item_uselect.tmpl.html');
$tmpl->addVar('header', 'TITLE', $title);
$tmpl->setAttribute('description', 'visibility', 'visible');
$tmpl->addVar('description', 'DESCRIPTION', _AM_XOONIPS_MAINTENANCE_ITEM_MSG_SELECT_USER);
$tmpl->setAttribute('breadcrumbs', 'visibility', 'visible');
$tmpl->addRows('breadcrumbs_items', $breadcrumbs);
$tmpl->addRows('userlist', $userlist);
$tmpl->addVar('main', 'nextaction', $nextaction);
$tmpl->addVar('main', 'submit', _AM_XOONIPS_LABEL_NEXT);

xoops_cp_header();
$tmpl->displayParsedTemplate('main');
xoops_cp_footer();
