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
require 'include/common.inc.php';

require_once 'class/base/pagenavi.class.php';
require_once 'class/base/gtickets.php';
require_once 'include/AL.php';
require_once 'include/lib.php';

$myuid = is_object($xoopsUser) ? $xoopsUser->getVar('uid', 'n') : UID_GUEST;
if ($myuid == UID_GUEST) {
    redirect_header(XOOPS_URL.'/', 3, _NOPERM);
    exit();
}

$ticket_area = 'xoonips_editshowitem';

$formdata = &xoonips_getutility('formdata');

// administrator can edit everyone's show items
$uid = $formdata->getValue('both', 'uid', 'i', false, $myuid);
$xmember_handler = &xoonips_gethandler('xoonips', 'member');
if (!$xmember_handler->isAdmin($uid) && $uid != $myuid) {
    // no permission
    redirect_header(XOOPS_URL.'/', 3, _NOPERM);
    exit();
}

$breadcrumbs = array(
  array(
    'name' => _MD_XOONIPS_BREADCRUMBS_USER,
  ),
  array(
    'name' => _MD_XOONIPS_SHOW_USER_TITLE,
    'url' => 'showusers.php'.'?uid='.$uid,
  ),
  array(
    'name' => _MD_XOONIPS_ITEM_SHOW_EDIT_TITLE,
  ),
);

$op = $formdata->getValue('post', 'op', 's', false, '');
switch ($op) {
case 'update':
    // check token ticket
    if (!$xoopsGTicket->check(true, $ticket_area, false)) {
        redirect_header('showusers.php', 3, $xoopsGTicket->getErrors());
        exit();
    }
    // TODO: update
    $checked_item_ids = $formdata->getValueArray('post', 'checked_item_ids', 'i', false);
    _xoonips_editshowitem_update_item_ids($uid, $checked_item_ids);
    redirect_header('showusers.php', 1, _MD_XOONIPS_PUBLICATION_ITEM_INSERT);
    exit();
    break;
case 'navi':
    // check token ticket, if error occured accept to repost
    if (!$xoopsGTicket->check(true, $ticket_area, true)) {
        redirect_header('showusers.php', 3, $xoopsGTicket->getErrors());
        exit();
    }
    // get selected item ids
    $checked_item_ids = $formdata->getValueArray('post', 'checked_item_ids', 'i', false);
    break;
default:
    // get current selected item ids
    $checked_item_ids = _xoonips_editshowitem_get_item_ids_by_uid($uid);
    break;
}

// get item types
$item_type_names = _xoonips_editshowitem_get_item_type_names('s');
if (empty($item_type_names)) {
    // no item types found
    redirect_header(XOOPS_URL.'/', 3, _NOPERM);
    exit();
}
$item_type_ids = array_keys($item_type_names);

// get showing item type id
$item_type_id = $formdata->getValue('post', 'item_type_id', 'i', false);
if (is_null($item_type_id)) {
    $item_type_id = $item_type_ids[0];
} elseif (!in_array($item_type_id, $item_type_ids)) {
    // invalid item type id
    redirect_header(XOOPS_URL.'/', 3, _NOPERM);
    exit();
}

// item_show_optional column in xoonips_config table
// -> on : calculate in all public items
// -> off : calculate in items user registered (default)
$xconfig_handler = &xoonips_getormhandler('xoonips', 'config');
$item_show_optional = $xconfig_handler->getValue('item_show_optional');
$is_owner_only = ($item_show_optional != 'on');

// calculate page navigation
$page_navi = array();
$total_item_count = 0;
// - get page number in each item types
$page = $formdata->getValueArray('post', 'page', 'i', false);
foreach ($item_type_ids as $it_id) {
    // - get total number of items in each item types
    $item_count = _xoonips_editshowitem_count_public_items($it_id, $uid, $is_owner_only);
    // - maximum number of items per page
    $item_limit = 20;
    // - current page
    $item_page = isset($page[$it_id]) ? $page[$it_id] : 1;
    $navi = new XooNIpsPageNavi($item_count, $item_limit, $item_page);
    // - sort
    $navi->setSort('title');
    // - order
    $navi->setOrder('ASC');
    $page_navi[$it_id] = $navi;
    // - total item count
    $total_item_count += $item_count;
}

// assign template values
$token_ticket = $xoopsGTicket->getTicketHtml(__LINE__, 1800, $ticket_area);
$xoopsOption['template_main'] = 'xoonips_editshowitem.html';
require XOOPS_ROOT_PATH.'/header.php';
// create item list block after loaded header.php,
// because $GLOBALS['xoopsTpl'] variable is used in item list block generator
$item_types = array();
$hidden_checked_item_ids = $checked_item_ids;
foreach ($item_type_ids as $it_id) {
    $navi = &$page_navi[$it_id];
    $items = array();
    if ($item_type_id == $it_id) {
        // current selected item type
        $item_ids = _xoonips_editshowitem_get_item_ids($it_id, $uid, $is_owner_only, $navi->getSort(), $navi->getOrder(), $navi->getStart(), $navi->getLimit());
        foreach ($item_ids as $item_id) {
            $items[] = array(
                'item_id' => $item_id,
                'checked' => in_array($item_id, $checked_item_ids),
                'html' => _xoonips_editshowitem_get_item_html($item_id),
            );
        }
        $hidden_checked_item_ids = array_diff($hidden_checked_item_ids, $item_ids);
    }
    $item_types[$it_id] = array(
        'item_type_id' => $it_id,
        'name' => $item_type_names[$it_id],
        'navi' => $navi->getTemplateVars(10),
        'items' => $items,
    );
}
$xoopsTpl->assign('xoops_breadcrumbs', $breadcrumbs);
$xoopsTpl->assign('token_ticket', $token_ticket);
$xoopsTpl->assign('uid', $uid);
$xoopsTpl->assign('item_type_id', $item_type_id);
$xoopsTpl->assign('checked_item_ids', $checked_item_ids);
$xoopsTpl->assign('hidden_checked_item_ids', $hidden_checked_item_ids);
$xoopsTpl->assign('total_item_num', $total_item_count);
$xoopsTpl->assign('checked_item_num', count($checked_item_ids));
$xoopsTpl->assign('item_types', $item_types);
require XOOPS_ROOT_PATH.'/footer.php';
exit();

/**
 * get current selected item ids.
 *
 * @param int $uid user id
 *
 * @return array selected item ids
 */
function _xoonips_editshowitem_get_item_ids_by_uid($uid)
{
    $is_handler = &xoonips_getormhandler('xoonips', 'item_show');
    $criteria = new Criteria('uid', $uid);
    $objs = &$is_handler->getObjects($criteria);
    $iids = array();
    foreach ($objs as $obj) {
        $iids[] = $obj->get('item_id');
    }

    return $iids;
}

/**
 * update selected item ids.
 *
 * @param int   $uid      user id
 * @param array $item_ids selected item ids
 *
 * @return bool false if failure
 */
function _xoonips_editshowitem_update_item_ids($uid, $item_ids)
{
    $is_handler = &xoonips_getormhandler('xoonips', 'item_show');
    $criteria = new Criteria('uid', $uid);
    // get current item ids
    $objs = &$is_handler->getObjects($criteria);
    foreach ($objs as $obj) {
        $iid = $obj->get('item_id');
        if (!in_array($iid, $item_ids)) {
            // delete not selected item id
            $is_handler->delete($obj);
        } else {
            // already exists
            $item_ids = array_diff($item_ids, array($iid));
        }
    }
    // insert non existant item ids
    foreach ($item_ids as $iid) {
        $obj = &$is_handler->create();
        $obj->set('uid', $uid);
        $obj->set('item_id', $iid);
        $is_handler->insert($obj);
    }

    return true;
}

/**
 * get item type names.
 *
 * @param string $fmt format
 *
 * @return array item type ids
 */
function _xoonips_editshowitem_get_item_type_names($fmt)
{
    $it_handler = &xoonips_getormhandler('xoonips', 'item_type');
    $objs = &$it_handler->getObjectsSortByWeight();
    $res = array();
    foreach ($objs as $obj) {
        $item_type_id = $obj->get('item_type_id');
        $res[$item_type_id] = $obj->getVar('display_name', $fmt);
    }

    return $res;
}

/**
 * count certified public items by item type.
 *
 * @param int  $item_type_id  item type id
 * @param int  $uid           user id
 * @param bool $is_owner_only true if count own items
 *
 * @return int number of items
 */
function _xoonips_editshowitem_count_public_items($item_type_id, $uid, $is_owner_only)
{
    $objs = &_xoonips_editshowitem_get_item_objects($item_type_id, $uid, $is_owner_only, null, null, null, null);

    return count($objs);
}

/**
 * get item html.
 *
 * @param int $item_id item id
 *
 * @return string html
 */
function _xoonips_editshowitem_get_item_html($item_id)
{
    $htmls = itemid2ListBlock($item_id);

    return $htmls[$item_id];
}

/**
 * get item ids.
 *
 * @param int    $item_type_id  item type id
 * @param int    $uid           user id
 * @param bool   $is_owner_only true if count own items
 * @param string $sort          sort of criteria
 *                              'title', 'item_id', 'ext_id', 'last_update' or 'creation_date'
 * @param string $order         order of criteria
 *                              'ASC' or 'DESC'
 * @param int    $start         start of criteria
 * @param int    $limit         limit of criteria
 *
 * @return array item ids
 */
function _xoonips_editshowitem_get_item_ids($item_type_id, $uid, $is_owner_only, $sort, $order, $start, $limit)
{
    $objs = &_xoonips_editshowitem_get_item_objects($item_type_id, $uid, $is_owner_only, $sort, $order, $start, $limit);
    $item_ids = array();
    foreach ($objs as $obj) {
        $item_ids[] = $obj->get('item_id');
    }

    return $item_ids;
}

/**
 * get item objects.
 *
 * @param int    $item_type_id  item type id
 * @param int    $uid           user id
 * @param bool   $is_owner_only true if count own items
 * @param string $sort          sort of criteria
 *                              'title', 'item_id', 'ext_id', 'last_update' or 'creation_date'
 * @param string $order         order of criteria
 *                              'ASC' or 'DESC'
 * @param int    $start         start of criteria
 * @param int    $limit         limit of criteria
 *
 * @return array item list htmls
 */
function &_xoonips_editshowitem_get_item_objects($item_type_id, $uid, $is_owner_only, $sort = null, $order = null, $start = null, $limit = null)
{
    $xil_handler = &xoonips_getormhandler('xoonips', 'index_item_link');
    $join = new XooNIpsJoinCriteria('xoonips_index', 'index_id', 'index_id', 'INNER', 'idx');
    $join->cascade(new XooNIpsJoinCriteria('xoonips_item_basic', 'item_id', 'item_id', 'INNER', 'ib'));
    $join->cascade(new XooNIpsJoinCriteria('xoonips_item_title', 'item_id', 'item_id', 'INNER', 'it'));
    $criteria = new CriteriaCompo(new Criteria('certify_state', CERTIFIED));
    $criteria->add(new Criteria('open_level', OL_PUBLIC, '=', 'idx'));
    if ($is_owner_only) {
        $criteria->add(new Criteria('uid', $uid, '=', 'ib'));
    }
    $criteria->add(new Criteria('item_type_id', $item_type_id, '=', 'ib'));
    $criteria->add(new Criteria('title_id', 0, '=', 'it'));
    if (!is_null($start)) {
        $def_sort = array(
            'title' => 'it.title',
            'item_id' => 'ib.item_id',
            'ext_id' => 'ib.doi',
            'last_update' => 'last_updated_date',
            'creation_date' => 'creation_date',
        );
        $def_order = array(
            'ASC' => 'ASC',
            'DESC' => 'DESC',
        );
        $sort = isset($def_sort[$sort]) ? $def_sort[$sort] : 'it.title';
        $order = isset($def_order[$order]) ? $def_order[$order] : 'ASC';
        $criteria->setSort($sort);
        $criteria->setOrder($order);
        $criteria->setStart($start);
        $criteria->setLimit($limit);
    }

    return $xil_handler->getObjects($criteria, false, 'ib.item_id', true, $join);
}
