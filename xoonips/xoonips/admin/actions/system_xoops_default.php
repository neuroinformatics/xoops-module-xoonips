<?php

// $Revision: 1.1.4.1.2.7 $
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
$title = _AM_XOONIPS_SYSTEM_XOOPS_TITLE;
$description = _AM_XOONIPS_SYSTEM_XOOPS_DESC;

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
$ticket_area = 'xoonips_admin_system_xoops';
$token_ticket = $xoopsGTicket->getTicketHtml(__LINE__, 1800, $ticket_area);

// templates
require_once '../class/base/pattemplate.class.php';
$tmpl = new PatTemplate();
$tmpl->setBaseDir('templates');
$tmpl->readTemplatesFromFile('system_xoops.tmpl.html');

// logic
function &get_xoonips_unregistered_users()
{
    global $xoopsDB;
    $users_handler = &xoonips_getormhandler('xoonips', 'xoops_users');
    $xusers_handler = &xoonips_getormhandler('xoonips', 'users');
    $criteria = new CriteriaElement();
    // $criteria = new Criteria( 'level', '0', '>' );
    $criteria->setSort('uname');
    $users_objs = &$users_handler->getObjects($criteria);
    $users = array();
    $evenodd = 'odd';
    foreach ($users_objs as $users_obj) {
        $uid = $users_obj->getVar('uid', 's');
        $criteria = new Criteria('uid', $uid);
        if ($xusers_handler->getCount($criteria) == 0) {
            $user['uid'] = $uid;
            $user['uname'] = $users_obj->getVar('uname', 's');
            $user['name'] = $users_obj->getVar('name', 's');
            $user['email'] = $users_obj->getVar('email', 's');
            $user['uname_js'] = str_replace('&#039;', '\\\'', $user['uname']);
            $user['register'] = _AM_XOONIPS_LABEL_REGISTER;
            $user['evenodd'] = $evenodd;
            $users[] = $user;
            $evenodd = ($evenodd == 'even') ? 'odd' : 'even';
        }
    }

    return $users;
}
$users = get_xoonips_unregistered_users();
$has_users = (count($users) == 0) ? false : true;

// assign template variables
$tmpl->addVar('header', 'TITLE', $title);
$tmpl->addVar('main', 'TITLE', $title);
$tmpl->setAttribute('description', 'visibility', 'visible');
$tmpl->addVar('description', 'DESCRIPTION', $description);
$tmpl->setAttribute('breadcrumbs', 'visibility', 'visible');
$tmpl->addRows('breadcrumbs_items', $breadcrumbs);
$tmpl->addVar('main', 'USERADD_TOKEN_TICKET', $token_ticket);
$tmpl->addVar('main', 'USERADD_TITLE', _AM_XOONIPS_SYSTEM_XOOPS_USERADD_TITLE);
$tmpl->addVar('main', 'USERADD_DESC', _AM_XOONIPS_SYSTEM_XOOPS_USERADD_DESC);
$tmpl->addVar('confirm_javascript', 'REGISTER_CONFIRM', _AM_XOONIPS_MSG_REGISTER_CONFIRM);
$tmpl->addVar('confirm_javascript', 'LABEL_UNAME', _AM_XOONIPS_LABEL_UNAME);
$tmpl->addVar('main', 'LABEL_UNAME', _AM_XOONIPS_LABEL_UNAME);
$tmpl->addVar('main', 'LABEL_NAME', _AM_XOONIPS_LABEL_NAME);
$tmpl->addVar('main', 'LABEL_EMAIL', _AM_XOONIPS_LABEL_EMAIL);
$tmpl->addVar('main', 'LABEL_ACTION', _AM_XOONIPS_LABEL_ACTION);
$tmpl->addVar('users_empty', 'USERADD_MSG_EMPTY', _AM_XOONIPS_SYSTEM_XOOPS_USERADD_MSG_EMPTY);
if ($has_users) {
    $tmpl->addRows('users', $users);
} else {
    $tmpl->setAttribute('users', 'visibility', 'hidden');
    $tmpl->setAttribute('users_empty', 'visibility', 'visible');
}

/**
 * render_zombie_list.
 */
function render_zombie_list()
{
    global $tmpl;
    global $xoopsGTicket;

    // set token
    $ticket_area = 'xoonips_admin_system_xoops_zombielist';
    $token_ticket = $xoopsGTicket->getTicketHtml(__LINE__, 1800, $ticket_area);
    $tmpl->addVar('main', 'ZOMBIELIST_TOKEN_TICKET', $token_ticket);

    // assign labels
    $tmpl->addVar('main', 'ZOMBIELIST_TITLE', _AM_XOONIPS_SYSTEM_XOOPS_ZOMBIELIST_TITLE);
    $tmpl->addVar('main', 'ZOMBIELIST_DESC', _AM_XOONIPS_SYSTEM_XOOPS_ZOMBIELIST_DESC);
    $tmpl->addVar('main', 'LABEL_UID', _AM_XOONIPS_LABEL_UID);
    $tmpl->addVar('main', 'ZOMBIELIST_LABEL_ITEMCOUNT', _AM_XOONIPS_SYSTEM_XOOPS_ZOMBIELIST_LABEL_ITEMCOUNT);
    $tmpl->addVar('zombies_empty', 'ZOMBIELIST_MSG_EMPTY', _AM_XOONIPS_SYSTEM_XOOPS_ZOMBIELIST_MSG_EMPTY);

    $zombies = array();
    $evenodd = 'odd';
    foreach (get_zombie_user_ids() as $zombie_id) {
        $zombie = array();
        $zombie['uid'] = $zombie_id;
        $zombie['uname'] = get_uname_by_index_title($zombie_id, 's');
        $zombie['delete'] = _AM_XOONIPS_LABEL_DELETE;
        $zombie['evenodd'] = $evenodd;
        $basic_handler = &xoonips_getormhandler('xoonips', 'item_basic');
        $criteria2 = new CriteriaCompo();
        $criteria2->add(new Criteria('uid', $zombie_id));
        $criteria2->add(new Criteria('item_type_id', ITID_INDEX, '!='));
        $basics = &$basic_handler->getObjects($criteria2);
        if (is_array($basics)) {
            $zombie['itemcount'] = sprintf('%d&nbsp;(%d/%d)', count($basics), get_number_of_item_by_open_level($zombie_id, OL_GROUP_ONLY), get_number_of_item_by_open_level($zombie_id, OL_PUBLIC));
        } else {
            $zombie['itemcount'] = 0;
        }
        $zombies[] = $zombie;
        $evenodd = ($evenodd == 'even') ? 'odd' : 'even';
    }
    $has_zombies = (count($zombies) > 0) ? true : false;

    if ($has_zombies) {
        $tmpl->addRows('zombies', $zombies);
    } else {
        $tmpl->setAttribute('zombies', 'visibility', 'hidden');
        $tmpl->setAttribute('zombies_empty', 'visibility', 'visible');
    }
}

render_zombie_list();

// display
xoops_cp_header();
$tmpl->displayParsedTemplate('main');
xoops_cp_footer();

/**
 * get number of user's items of specified open level(ignore certify state).
 *
 * @param int $uid        user id
 * @param int $open_leven OL_PUBLIC|OL_GROUP_ONLY|OL_PRIVATE
 *
 * @return int
 */
function get_number_of_item_by_open_level($uid, $open_level)
{
    $criteria = new CriteriaCompo();
    $criteria->add(new Criteria('item_type_id', ITID_INDEX, '!='));
    $criteria->add(new Criteria('open_level', intval($open_level)));
    $criteria->add(new Criteria('uid', intval($uid), '=', 'basic'));
    $join = new XooNIpsJoinCriteria('xoonips_index', 'index_id', 'index_id');
    $join->cascade(new XooNIpsJoinCriteria('xoonips_item_basic', 'item_id', 'item_id', 'INNER', 'basic'));
    $index_item_link_handler = &xoonips_getormhandler('xoonips', 'index_item_link');

    return count($index_item_link_handler->getObjects($criteria, false, '', true, $join));
}

/**
 * get array of user id that have no corresponding row in XOOPS users table.
 *
 * @param void
 *
 * @return array zombie_users
 */
function get_zombie_user_ids()
{
    $xoonips_users_handler = &xoonips_getormhandler('xoonips', 'users');
    $users_handler = &xoonips_getormhandler('xoonips', 'xoops_users');

    return array_diff(array_keys($xoonips_users_handler->getObjects(null, true)), array_keys($users_handler->getObjects(null, true)));
}

/**
 * get user name by index title.
 *
 * @param int    $uid
 * @param string $fmt
 *
 * @return string title
 */
function get_uname_by_index_title($uid, $fmt)
{
    $index_handler = &xoonips_getormhandler('xoonips', 'index');
    $title_handler = &xoonips_getormhandler('xoonips', 'title');
    // get root index
    $criteria = new CriteriaCompo(new Criteria('uid', $uid));
    $criteria->add(new Criteria('parent_index_id', IID_ROOT));
    $criteria->add(new Criteria('open_level', OL_PRIVATE));
    $index_objs = &$index_handler->getObjects($criteria);
    if (count($index_objs) != 1) {
        return '';
    }
    $index_obj = &$index_objs[0];
    $index_id = $index_obj->get('index_id');
    // get title
    $criteria = new CriteriaCompo(new Criteria('item_id', $index_id));
    $criteria->add(new Criteria('title_id', DEFAULT_INDEX_TITLE_OFFSET));
    $title_objs = &$title_handler->getObjects($criteria);
    if (count($title_objs) != 1) {
        return '';
    }
    $title_obj = &$title_objs[0];

    return $title_obj->getVar('title', $fmt);
}
