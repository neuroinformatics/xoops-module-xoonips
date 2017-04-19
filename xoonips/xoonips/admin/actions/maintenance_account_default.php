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

// get requsts
$get_keys = array(
    'navi' => array('i', false, false),
);
$get_vals = xoonips_admin_get_requests('get', $get_keys);

// class files
require '../class/base/pagenavi.class.php';

// title
$title = _AM_XOONIPS_MAINTENANCE_ACCOUNT_TITLE;
$description = _AM_XOONIPS_MAINTENANCE_ACCOUNT_DESC;

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

// TODO: set sort, start and limit
// TODO: implement this into object handler
function &account_get_userlist($limit, $start, $sort)
{
    global $xoopsDB;
    $users_handler = &xoonips_getormhandler('xoonips', 'xoops_users');
    $tables['users'] = $xoopsDB->prefix('users');
    $join_criteria = new XooNIpsJoinCriteria('xoonips_users', 'uid', 'uid', 'INNER');
    $criteria = new CriteriaElement();
    $sort_arr = array();
    foreach ($sort as $so) {
        $sort_arr[] = $tables['users'].'.'.$so;
    }
    if (!empty($sort_arr)) {
        $criteria->setSort($sort_arr);
    }
    $criteria->setLimit($limit);
    $criteria->setStart($start);
    $fields = array();
    $fields[] = $tables['users'].'.uid';
    $fields[] = $tables['users'].'.name';
    $fields[] = $tables['users'].'.uname';
    $fields[] = $tables['users'].'.email';
    $users_objs = &$users_handler->getObjects($criteria, false, implode(',', $fields), false, $join_criteria);

    return $users_objs;
}

function count_users()
{
    $users_handler = &xoonips_getormhandler('xoonips', 'xoops_users');
    $join_criteria = new XooNIpsJoinCriteria('xoonips_users', 'uid', 'uid');

    return $users_handler->getCount(null, $join_criteria);
}

// page navigation
$page = (is_null($get_vals['navi'])) ? 1 : $get_vals['navi'];
$limit = 20;
$pagenavi = new XooNIpsPageNavi(count_users(), $limit, $page);
$pagenavi->setSort(array('uname'));
$navi = &$pagenavi->getTemplateVars(10);
$navi_title = sprintf(_AM_XOONIPS_MAINTENANCE_ACCOUNT_PAGENAVI_FORMAT, $navi['start'], $navi['end'], $navi['total']);
$navi_body = array();
foreach ($navi['navi'] as $body) {
    $navi_body[] = array(
        'has_link' => ($navi['page'] == $body) ? 'no' : 'yes',
        'link' => $xoonips_admin['mypage_url'],
        'page' => $body,
    );
}

$users_objs = &account_get_userlist($limit, $pagenavi->getStart(), $pagenavi->getSort());
$users = array();
$evenodd = 'odd';
foreach ($users_objs as $users_obj) {
    $uid = $users_obj->getVar('uid', 'e');
    $name = $users_obj->getVar('name', 's');
    $uname = $users_obj->getVar('uname', 's');
    $email = $users_obj->getVar('email', 's');
    $users[] = array(
        'uid' => $uid,
        'name' => $name,
        'uname' => $uname,
        'email' => $email,
        'evenodd' => $evenodd,
        'modify' => _AM_XOONIPS_LABEL_MODIFY,
        'delete' => _AM_XOONIPS_LABEL_DELETE,
    );
    $evenodd = ($evenodd == 'even') ? 'odd' : 'even';
}

// templates
require_once '../class/base/pattemplate.class.php';
$tmpl = new PatTemplate();
$tmpl->setBaseDir('templates');
$tmpl->readTemplatesFromFile('maintenance_account.tmpl.html');

// assign template variables
$tmpl->addVar('header', 'TITLE', $title);
$tmpl->setAttribute('description', 'visibility', 'visible');
$tmpl->addVar('description', 'DESCRIPTION', $description);
$tmpl->setAttribute('breadcrumbs', 'visibility', 'visible');
$tmpl->addRows('breadcrumbs_items', $breadcrumbs);
$tmpl->addVar('main', 'add_title', _AM_XOONIPS_MAINTENANCE_ACCOUNT_ADD_TITLE);
$tmpl->addVar('main', 'uid', _AM_XOONIPS_LABEL_UID);
$tmpl->addVar('main', 'uname', _AM_XOONIPS_LABEL_UNAME);
$tmpl->addVar('main', 'name', _AM_XOONIPS_LABEL_NAME);
$tmpl->addVar('main', 'email', _AM_XOONIPS_LABEL_EMAIL);
$tmpl->addVar('main', 'action', _AM_XOONIPS_LABEL_ACTION);
if (count($users) == 0) {
    $tmpl->setAttribute('users', 'visibility', 'hidden');
    $tmpl->setAttribute('users_empty', 'visibility', 'visible');
    $tmpl->addVar('users_empty', 'empty', _AM_XOONIPS_MAINTENANCE_ACCOUNT_EMPTY);
    $tmpl->setAttribute('page_navi_title', 'visibility', 'hidden');
    $tmpl->setAttribute('page_navi', 'visibility', 'hidden');
} else {
    $tmpl->addRows('users', $users);
    $tmpl->addVar('page_navi_title', 'navi_title', $navi_title);
    if ($navi['maxpage'] == 1) {
        $tmpl->setAttribute('page_navi', 'visibility', 'hidden');
    } else {
        $tmpl->addVar('page_navi_prev', 'prev', $navi['prev']);
        $tmpl->addVar('page_navi_prev', 'link', $xoonips_admin['mypage_url']);
        $tmpl->addRows('page_navi_body', $navi_body);
        $tmpl->addVar('page_navi_next', 'next', $navi['next']);
        $tmpl->addVar('page_navi_next', 'link', $xoonips_admin['mypage_url']);
    }
}

// display
xoops_cp_header();
$tmpl->displayParsedTemplate('main');
xoops_cp_footer();
