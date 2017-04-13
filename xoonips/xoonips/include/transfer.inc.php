<?php

// $Revision: 1.1.2.13 $
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

/**
 * return true if $item_id have unkonwn parent item
 * (ex-$known_parent_item_ids) which have the same owner($uid).
 */
function xoonips_transfer_have_another_parent($item_id, $known_parent_item_ids, $uid)
{
    $item_basic_handler = &xoonips_getormhandler('xoonips', 'item_basic');
    $compo_handler = &xoonips_getormcompohandler('xoonips', 'item');
    foreach ($compo_handler->getParentItemIds($item_id) as $parent_item_id) {
        if (!in_array($parent_item_id, $known_parent_item_ids)) {
            $parent_item_basic = $item_basic_handler->get($parent_item_id);
            if ($parent_item_basic->get('uid') == $uid) {
                return true;
            }
        }
    }

    return false;
}

/**
 * check items transferrable.
 *
 * @param int from_uid
 * @param int to_uid
 * @param array item_ids
 *
 * @return bool true if items are transferrable
 */
function xoonips_transfer_is_transferrable($from_uid, $to_uid, $item_ids)
{
    if ($from_uid == $to_uid) {
        return false;
    }

    if (count($item_ids) == 0) {
        return false;
    }

    $user_handler = &xoonips_getormhandler('xoonips', 'users');
    $user = $user_handler->get($to_uid);
    if ($user == false || $user->get('activate') == 0) {
        return false; // invalid user or not certified
    }

    foreach ($item_ids as $item_id) {
        $lock_handler = &xoonips_getormhandler('xoonips', 'item_lock');
        if ($lock_handler->isLocked($item_id)) {
            return false; // locked
        }
        $item_basic_handler = &xoonips_getormhandler('xoonips', 'item_basic');
        $item_basic = $item_basic_handler->get($item_id);
        if ($item_basic == false || $item_basic->get('item_type_id') == ITID_INDEX
        ) {
            return false; // invalid id or index id
        }

        if ($item_basic->get('uid') != $from_uid) {
            return false; // not own this item
        }

        $item_type_handler = &xoonips_getormhandler('xoonips', 'item_type');
        $item_type = $item_type_handler->get($item_basic->get('item_type_id'));
        $compo_handler = &xoonips_getormcompohandler($item_type->get('name'), 'item');

        $compo = $compo_handler->get($item_id);
        foreach ($compo->getChildItemIds() as $child_item_id) {
            if (!in_array($child_item_id, $item_ids)) {
                $child_item_basic = $item_basic_handler->get($child_item_id);
                if ($child_item_basic->get('uid') == $from_uid) {
                    return false; // child item missing from $item_ids
                }
            }
        }

        if (xoonips_transfer_have_another_parent($item_id, $item_ids, $from_uid)) {
            return false; // parent item missing from $item_ids
        }
    }

    return true;
}

function xoonips_transfer_get_private_item_ids($uid)
{
    $index_item_link_handler = &xoonips_getormhandler('xoonips', 'index_item_link');
    $join = new XooNIpsJoinCriteria('xoonips_item_basic', 'item_id', 'item_id');
    $criteria = new Criteria('uid', $uid);
    $index_item_links = &$index_item_link_handler->getObjects($criteria, false, '', null, $join);
    $iids = array();
    $certified_iids = array();
    foreach ($index_item_links as $index_item_link) {
        $item_id = $index_item_link->get('item_id');
        $iids[$item_id] = $item_id;
        if ($index_item_link->get('certify_state') == CERTIFIED) {
            $certified_iids[$item_id] = $item_id;
        }
    }
    $private_iids = array_diff_assoc($iids, $certified_iids);

    return $private_iids;
}

function xoonips_transfer_get_total_size_of_items($item_ids)
{
    if (empty($item_ids)) {
        return 0;
    }
    $total_size = 0;
    $file_handler = &xoonips_getormhandler('xoonips', 'file');
    $files = &$file_handler->getObjects(new Criteria('item_id', '('.implode(',', $item_ids).')', 'in'));
    foreach ($files as $file) {
        if ($file->get('is_deleted') == 0) {
            $total_size += $file->get('file_size');
        }
    }

    return $total_size;
}

function xoonips_transfer_extract_private_item_ids($item_ids)
{
    if (!is_array($item_ids)) {
        return array();
    }

    $private_item_ids = array();
    $index_item_link_handler = &xoonips_getormhandler('xoonips', 'index_item_link');
    foreach ($item_ids as $item_id) {
        $join = new XooNIpsJoinCriteria('xoonips_index', 'index_id', 'index_id', 'LEFT', 'tindex');
        $criteria = new CriteriaCompo();
        $criteria->add(new Criteria('item_id', $item_id));
        $criteria->add(new Criteria('open_level', '('.OL_PUBLIC.','.OL_GROUP_ONLY.')', 'IN'));
        $criteria->add(new Criteria('certify_state', CERTIFIED));
        $objs = &$index_item_link_handler->getObjects($criteria, false, '', false, $join);

        if (count($objs) == 0) {
            $private_item_ids[] = $item_id;
        }
    }

    return $private_item_ids;
}

/**
 * check private item number exceeds limit if
 * $item_ids are transferred to $to_uid.
 *
 * @param int to_uid
 * @param array item_ids
 *
 * @return bool true if private item number exceeds limit
 */
function xoonips_transfer_is_private_item_number_exceeds_if_transfer($to_uid, $item_ids)
{
    $user_handler = &xoonips_getormhandler('xoonips', 'users');
    $user = $user_handler->get($to_uid);
    if ($user == false) {
        return false;
    }
    $index_item_link_handler = &xoonips_getormhandler('xoonips', 'index_item_link');

    if (count($index_item_link_handler->getPrivateItemIdsByUid($to_uid)) + count(xoonips_transfer_extract_private_item_ids($item_ids)) > $user->get('private_item_number_limit')
    ) {
        return true; // exceeds;
    }

    return false;
}

/**
 * check private item storage exceeds limit if
 * $item_ids are transferred to $to_uid.
 *
 * @param int to_uid
 * @param array item_ids
 *
 * @return bool true if private item storage exceeds limit
 */
function xoonips_transfer_is_private_item_storage_exceeds_if_transfer($to_uid, $item_ids)
{
    $user_handler = &xoonips_getormhandler('xoonips', 'users');
    $user = $user_handler->get($to_uid);
    if ($user == false) {
        return false;
    }
    $file_handler = &xoonips_getormhandler('xoonips', 'file');
    $index_item_link_handler = &xoonips_getormhandler('xoonips', 'index_item_link');

    if ($file_handler->getTotalSizeOfItems($index_item_link_handler->getPrivateItemIdsByUid($to_uid)) + $file_handler->getTotalSizeOfItems(xoonips_transfer_extract_private_item_ids($item_ids)) > $user->get('private_item_storage_limit')) {
        return true; // exceeds;
    }

    return false;
}

/**
 * get all group ids which $item_ids are registered to indexes of.
 *
 * @param array item_ids
 *
 * @return array array of group ids
 */
function xoonips_transfer_get_group_ids_of_items($item_ids)
{
    if (!is_array($item_ids) || count($item_ids) == 0) {
        return array();
    }

    $index_item_link_handler = &xoonips_getormhandler('xoonips', 'index_item_link');
    $index_handler = &xoonips_getormhandler('xoonips', 'index');

    $group_ids = array();
    foreach ($item_ids as $item_id) {
        $index_ids = $index_item_link_handler->getIndexIdsByItemId($item_id, array(OL_GROUP_ONLY));
        foreach ($index_ids as $index_id) {
            $index = $index_handler->get($index_id);
            $group_ids[$index->get('gid')] = true;
        }
    }

    return array_keys($group_ids);
}

/**
 * get information of transferrable items.
 *
 * @param int from_uid
 * @param array item_ids
 *
 * @return array information of each $item_ids.
 *
 * structure of return value is:
 *   array(
 *     array(
 *       'item_id' => (int item_id),
 *       'transfer_enable' => bool
 *       'child_items' => array(
 *         array(
 *           'item_id' => (int child_item_id)
 *           'lock_type' => (int lock_type)
 *         ) // repeated
 *       ),
 *       'lock_type' => (int lock_type),
 *       'have_another_parent' => bool,
 *     ) // repated
 *   )
 */
/*
  have_another_parent of item A is:
    true if
      there exists B such that
        B is parent of A && A.uid == B.uid
    or
      there exists C and D such that
        C is child of A && C.uid == A.uid && D
        is parent of C && D.uid == A.uid && D is not in $item_ids
*/
function xoonips_transfer_get_transferrable_item_information($from_uid, $item_ids)
{
    if (!is_array($item_ids)) {
        return array();
    }

    $result = array();
    $item_lock_handler = &xoonips_getormhandler('xoonips', 'item_lock');

    foreach ($item_ids as $item_id) {
        $transfer_enable = true;
        $have_another_parent = false;

        $item_basic_handler = &xoonips_getormhandler('xoonips', 'item_basic');
        $item_basic = $item_basic_handler->get($item_id);
        if ($item_basic == false
            || $item_basic->get('item_type_id') == ITID_INDEX
        ) {
            continue; // no such item.
        }

        if ($item_lock_handler->isLocked($item_id)) {
            $transfer_enable = false; // locked
        }

        $item_type_handler = &xoonips_getormhandler('xoonips', 'item_type');
        $item_type = $item_type_handler->get($item_basic->get('item_type_id'));
        $compo_handler = &xoonips_getormcompohandler($item_type->get('name'), 'item');
        $compo = $compo_handler->get($item_id);

        if (xoonips_transfer_have_another_parent($item_id, array(), $from_uid)) {
            $transfer_enable = false;
            $have_another_parent = true;
        }

        $child_items = array();
        foreach ($compo->getChildItemIds() as $child_item_id) {
            $child_item_basic = $item_basic_handler->get($child_item_id);
            if ($child_item_basic->get('uid') != $from_uid) {
                continue; // not my item
            }
            if ($item_lock_handler->isLocked($child_item_id)) {
                $transfer_enable = false;
            }
            $child_items[] = array(
                'item_id' => $child_item_id,
                'lock_type' => $item_lock_handler->getLockType($child_item_id),
            );
            if (xoonips_transfer_have_another_parent($child_item_id, $item_ids, $from_uid)) {
                $transfer_enable = false;
                $have_another_parent = true;
            }
        }

        $result[] = array(
            'item_id' => $item_id,
            'transfer_enable' => $transfer_enable,
            'child_items' => $child_items,
            'lock_type' => $item_lock_handler->getLockType($item_id),
            'have_another_parent' => $have_another_parent,
        );
    }

    return $result;
}

/**
 * get private indexes for dropdown list of $user_id.
 *
 * @param int user_id
 *
 * @return array array of private indexes.
 *               structure of return value is:
 *               array(
 *               array(
 *               'index_id' => (int index_id),
 *               'title' => (title of index)
 *               'depth' => (depth of index. depth of /Private is 0.)
 *               'item_count' => (number of items in this index)
 *               ) // repeated
 *               )
 */
function xoonips_transfer_get_private_indexes_for_dropdown($user_id)
{
    $user_handler = &xoonips_getormhandler('xoonips', 'users');
    $user = $user_handler->get($user_id);

    $index_handler = &xoonips_getormhandler('xoonips', 'index');
    $result = array();
    xoonips_transfer_get_index_tree_for_dropdown($user->get('private_index_id'), $result, 0);

    return $result;
}

function xoonips_transfer_get_index_tree_for_dropdown($index_id, &$result, $depth)
{
    $index_compo_handler = &xoonips_getormcompohandler('xoonips', 'index');
    $index_compo = $index_compo_handler->get($index_id);
    if ($index_compo === false) {
        return; // bad index_id
    }
    $titles = $index_compo->getVar('titles');

    $index_item_link_handler = &xoonips_getormhandler('xoonips', 'index_item_link');

    $result[] = array(
        'index_id' => $index_id,
        'title' => $titles[DEFAULT_INDEX_TITLE_OFFSET]->get('title'),
        'depth' => $depth,
        'item_count' => $index_item_link_handler->getCount(new Criteria('index_id', $index_id)),
    );
    $index_handler = &xoonips_getormhandler('xoonips', 'index');
    $index = $index_compo->getVar('index');
    $criteria = new Criteria('parent_index_id', $index_id);
    $criteria->setOrder('asc');
    $criteria->setSort('sort_number');
    $indexes = &$index_handler->getObjects($criteria);
    if ($indexes !== false) {
        foreach ($indexes as $index) {
            xoonips_transfer_get_index_tree_for_dropdown($index->get('index_id'), $result, $depth + 1);
        }
    }
}

/**
 * get certified users for dropdown list.
 *
 * @param int user_id remove this user from result. omittable.
 *
 * @return array array of certified users.
 *               structure of return value is:
 *               array(
 *               (user id) => (login name),
 *               )
 */
function xoonips_transfer_get_users_for_dropdown($user_id = null)
{
    $textutil = &xoonips_getutility('text');
    $users_handler = &xoonips_getormhandler('xoonips', 'users');
    $xoops_users_handler = &xoops_gethandler('user');

    $users = &$users_handler->getObjects(new Criteria('activate', 1));

    $result = array();
    foreach ($users as $user) {
        if ($user_id != $user->get('uid')) {
            $xoops_user = $xoops_users_handler->get($user->get('uid'));
            $result[$user->get('uid')] = $textutil->html_special_chars($xoops_user->getVar('uname'));
        }
    }
    asort($result);

    return $result;
}
