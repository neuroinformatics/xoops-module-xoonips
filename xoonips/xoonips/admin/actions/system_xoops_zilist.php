<?php

// $Revision: 1.1.2.6 $
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
$title = _AM_XOONIPS_SYSTEM_XOOPS_ITEM_RESCUE_TITLE;
$description = _AM_XOONIPS_SYSTEM_XOOPS_ITEM_RESCUE_DESC;

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
        'type' => 'link',
        'label' => _AM_XOONIPS_SYSTEM_XOOPS_TITLE,
        'url' => $xoonips_admin['mypage_url'],
    ),
    array(
        'type' => 'label',
        'label' => $title,
        'url' => '',
    ),
);

// logic
$formdata = &xoonips_getutility('formdata');

// get uid
$uid = $formdata->getValue('get', 'uid', 'i', true);

// is uid really zombie user ?
$xusers_handler = &xoonips_getormhandler('xoonips', 'users');
$users_handler = &xoonips_getormhandler('xoonips', 'xoops_users');
$xuser_obj = &$xusers_handler->get($uid);
$user_obj = &$users_handler->get($uid);
if (!is_object($xuser_obj) || is_object($user_obj)) {
    die('illegal request');
}

// get non private item ids
$index_item_link_handler = &xoonips_getormhandler('xoonips', 'index_item_link');
$item_ids = $index_item_link_handler->getNonPrivateItemIds($uid);
// merge group and public item ids
$item_ids = array_unique($item_ids);
if (count($item_ids) == 0) {
    die('illegal request');
}

// get item list
$item_type_handler = &xoonips_getormhandler('xoonips', 'item_type');
$title_handler = &xoonips_getormhandler('xoonips', 'title');
$item_basic_handler = &xoonips_getormhandler('xoonips', 'item_basic');
$items = array();
$evenodd = 'odd';
foreach ($item_ids as $item_id) {
    $item_basic_obj = &$item_basic_handler->get($item_id);
    $criteria = new Criteria('item_id', $item_id);
    $criteria->setSort('title_id');
    $criteria->setOrder('ASC');
    $title_objs = &$title_handler->getObjects($criteria);
    $item_title = '';
    foreach ($title_objs as $title_obj) {
        if ($item_title != '') {
            $item_title .= ' ';
        }
        $item_title .= $title_obj->getVar('title', 's');
    }
    $item_type_id = $item_basic_obj->get('item_type_id');
    $item_type_obj = &$item_type_handler->get($item_type_id);
    $item_type = $item_type_obj->getVar('display_name', 's');
    $item_url = sprintf('%s/transfer_item.php?action=detail_item&item_id=%u', XOONIPS_URL, $item_id);
    $evenodd = ($evenodd == 'even') ? 'odd' : 'even';
    $items[] = array(
        'EVENODD' => $evenodd,
        'ITEM_ID' => $item_id,
        'ITEM_TYPE' => $item_type,
        'ITEM_TITLE' => $item_title,
        'ITEM_URL' => $item_url,
    );
}

// get to user list
$to_uid = $formdata->getValue('get', 'tuid', 'i', false, 0);
$to_users = get_user_list('s');
foreach ($to_users as $key => $to_user) {
    if ($to_uid == 0) {
        $to_uid = $to_user['uid'];
    }
    $to_users[$key]['selected'] = ($to_uid == $to_user['uid']);
}
if ($to_uid == 0 || count($to_users) == 0) {
    // to user not exists
    die('illegal request');
}

// get to index list
$to_urxid = get_user_root_index_id($to_uid);
$to_indexes = get_index_list($to_urxid, 0);
// override root index name
$to_indexes[0]['index_title'] = XNP_PRIVATE_INDEX_TITLE;

// token ticket
require_once '../class/base/gtickets.php';
$ticket_area = 'xoonips_admin_system_xoops_item_rescue';
$token_ticket = $xoopsGTicket->getTicketHtml(__LINE__, 1800, $ticket_area);

// templates
require_once '../class/base/pattemplate.class.php';
$tmpl = new PatTemplate();
$tmpl->setBaseDir('templates');
$tmpl->readTemplatesFromFile('system_xoops_zilist.tmpl.html');

// assign template variables
$tmpl->addVar('header', 'TITLE', $title);
$tmpl->setAttribute('description', 'visibility', 'visible');
$tmpl->addVar('description', 'DESCRIPTION', $description);
$tmpl->setAttribute('breadcrumbs', 'visibility', 'visible');
$tmpl->addRows('breadcrumbs_items', $breadcrumbs);
$tmpl->addVar('javascript', 'MYPAGE_URL', $xoonips_admin['mypage_url']);
$tmpl->addVar('javascript', 'UID', $uid);
$tmpl->addVar('main', 'TOKEN_TICKET', $token_ticket);
$tmpl->addVar('main', 'LABEL_ITEM_ID', _AM_XOONIPS_LABEL_ITEM_ID);
$tmpl->addVar('main', 'LABEL_ITEM_TYPE', _AM_XOONIPS_LABEL_ITEM_TYPE);
$tmpl->addVar('main', 'LABEL_ITEM_TITLE', _AM_XOONIPS_LABEL_ITEM_TITLE);
$tmpl->addVar('main', 'LABEL_UID', _AM_XOONIPS_LABEL_UID);
$tmpl->addVar('main', 'LABEL_EXECUTE', _AM_XOONIPS_LABEL_EXECUTE);
$tmpl->addVar('main', 'LABEL_FROM', _AM_XOONIPS_SYSTEM_XOOPS_ITEM_RESCUE_LABEL_FROM);
$tmpl->addVar('main', 'LABEL_TO', _AM_XOONIPS_SYSTEM_XOOPS_ITEM_RESCUE_LABEL_TO);
$tmpl->addVar('main', 'UNAME', get_uname_by_index_title($uid, 's'));
$tmpl->addVar('main', 'UID', $uid);
$tmpl->addRows('items', $items);
$tmpl->addRows('to_users', $to_users);
$tmpl->addRows('to_indexes', $to_indexes);

// display
xoops_cp_header();
$tmpl->displayParsedTemplate('main');
xoops_cp_footer();
exit();

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

/**
 * get activated and certified user list.
 *
 * @param string $fmt
 *
 * @return array user list
 */
function get_user_list($fmt)
{
    $users_handler = &xoonips_getormhandler('xoonips', 'xoops_users');
    $join = new XooNIpsJoinCriteria('xoonips_users', 'uid', 'uid', 'INNER', 'xu');
    $criteria = new CriteriaCompo(new Criteria('level', 0, '>'));
    $criteria->add(new Criteria('activate', 1, '=', 'xu'));
    $criteria->setSort('uname');
    $criteria->setOrder('ASC');
    $users_objs = &$users_handler->getObjects($criteria, false, '', false, $join);
    $users = array();
    foreach ($users_objs as $users_obj) {
        $users[] = array(
            'uid' => $users_obj->get('uid'),
            'uname' => $users_obj->getVar('uname', $fmt),
        );
    }

    return $users;
}

/**
 * get user root index id.
 *
 * @param int $uid user id
 *
 * @return int root index id
 */
function get_user_root_index_id($uid)
{
    $index_handler = &xoonips_getormhandler('xoonips', 'index');
    $criteria = new CriteriaCompo(new Criteria('parent_index_id', IID_ROOT));
    $criteria->add(new Criteria('uid', $uid));
    $index_objs = &$index_handler->getObjects($criteria);
    if (count($index_objs) != 1) {
        die('unexpected error');
    }
    $index_obj = &$index_objs[0];

    return $index_obj->get('index_id');
}

/**
 * get index list.
 *
 * @param int $xid   index id
 * @param int $depth index depth
 *
 * @return array index list
 */
function get_index_list($xid, $depth)
{
    $index_handler = &xoonips_getormhandler('xoonips', 'index');
    $index_item_link_handler = &xoonips_getormhandler('xoonips', 'index_item_link');
    $index_obj = &$index_handler->get($xid);
    $title = $index_obj->getTitle('s');
    $criteria = new Criteria('index_id', $xid);
    $item_count = $index_item_link_handler->getCount($criteria);
    $ret = array();
    $ret[] = array(
        'index_id' => $xid,
        'index_title' => $title,
        'item_count' => $item_count,
        'indent_html' => str_repeat('&nbsp;&nbsp;', $depth),
    );
    $cindex_objs = &$index_obj->getAllChildren();
    foreach ($cindex_objs as $cindex_obj) {
        $cxid = $cindex_obj->get('index_id');
        $cret = get_index_list($cxid, $depth + 1);
        $ret = array_merge($ret, $cret);
    }

    return $ret;
}
