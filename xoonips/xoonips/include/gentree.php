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
    attributes add to index:
        bool is_last; // not null
        int depth; // not null
        int child_count; // not null
 */

/**
 * A child is added recursively. (Caution) not add parentXID.
 *
 * @param integer $parentXID parentXID
 * @param $in  XID->index Conversion table
 * @param $childFinder  XID->array(childXID) Conversion table
 * @param $out Output place. Priority is given to the depth when searching.
 *             Brothers output in result sorted by sort_number.  $out[] = index
 *             ROOT's depth = 0 ($out don't contain ROOT.)
 * @param integer $depth
 *
 * @return nothing
 */
function genIndexTreeAdd($parentXID, &$indexFinder, &$childFinder, &$out, $depth)
{
    ++$depth;
    $len = count($childFinder[$parentXID]);
    for ($i = 0; $i < $len; ++$i) {
        $xid = $childFinder[$parentXID][$i];

        $indexFinder[$xid]['is_last'] = ($i == $len - 1);
        $indexFinder[$xid]['depth'] = $depth;

        if (isset($childFinder[$xid])) {
            $indexFinder[$xid]['child_count'] = count($childFinder[$xid]);
            $indexFinder[$xid]['child'] = $childFinder[$xid];
        } else {
            $indexFinder[$xid]['child_count'] = 0;
        }

        // first, add children.
        $out[] = $indexFinder[$xid];

        // next, children's posterity is added recursively.
        if (isset($childFinder[$xid])) {
            genIndexTreeAdd($xid, $indexFinder, $childFinder, $out, $depth);
        }
    }
}

/* todo: to collect genIndexTree, genPublicIndexTree, genMyIndexTree, genSameAreaIndexTree, genPublicPrivateIndexTree,
 *       exractWritableIndexTree, and extractEditableIndexTree.
 * genIndexTree( xnpsid, uid, public_flag, gids, private_flag );
 */

function genIndexTree0($xnpsid)
{
    $criteria = array(
        'orders' => array(
            array('name' => 'parent_index_id', 'order' => 'ASC'),
            array('name' => 'sort_number', 'order' => 'ASC'),
        ),
    );
    $indexes = array();
    xnp_get_all_indexes($xnpsid, $criteria, $indexes);
    if (empty($indexes)) {
        return array();
    }

    return $indexes;
}

function genIndexTree1(&$indexes)
{
    // divide into every parents
    $indexFinder = array(); // index_id -> index
    $childFinder = array(); // index_id -> child_index_id
    foreach ($indexes as $index) {
        if ($index === false) {
            continue;
        }
        $parent_xid = $index['parent_index_id'];
        if (!isset($childFinder[$parent_xid])) {
            $childFinder[$parent_xid] = array();
        }
        $childFinder[$parent_xid][] = $index['item_id'];

        $indexFinder[$index['item_id']] = $index;
    }

    // add recursively from ROOT
    $out = array();
    genIndexTreeAdd(IID_ROOT, $indexFinder, $childFinder, $out, 0);

    return $out;
}

/**
 * get Index tree.
 *
 * @param $xnpsid XNPSID
 *
 * @return tree empty array in error
 */
function genIndexTree($xnpsid)
{
    $indexes = genIndexTree0($xnpsid);

    return genIndexTree1($indexes);
}

/**
 * get Public index tree.
 *
 * @param $xnpsid XNPSID
 *
 * @return tree empty array in error
 */
function genPublicIndexTree($xnpsid)
{
    $indexes = genIndexTree0($xnpsid);
    filterPublicIndex($indexes);

    return genIndexTree1($indexes);
}

function filterPublicIndex(&$indexes)
{
    $len = count($indexes);
    for ($i = 0; $i < $len; ++$i) {
        if ($indexes[$i] === false) {
            continue;
        }
        if ($indexes[$i]['open_level'] != OL_PUBLIC) {
            $indexes[$i] = false;
        }
    }
}

/**
 * get Index tree for moderator
 *  not contain other's private index, and not contain group-index of group which he does not belong.
 *
 * @param $xnpsid XNPSID
 *
 * @return tree empty array in error
 */
function genMyIndexTree($xnpsid, $uid)
{
    $indexes = genIndexTree0($xnpsid);
    filterMyIndex($indexes, $xnpsid, $uid);

    return genIndexTree1($indexes);
}

function filterMyIndex(&$indexes, $xnpsid, $uid)
{
    $xgroup_handler = &xoonips_gethandler('xoonips', 'group');
    $gids = $xgroup_handler->getGroupIds($uid, false);
    $len = count($indexes);
    for ($i = 0; $i < $len; ++$i) {
        $index = &$indexes[$i];
        if ($index === false) {
            continue;
        }
        if ($index['open_level'] == OL_GROUP_ONLY) {
            if (!in_array($index['owner_gid'], $gids)) {
                // group which he does not belong.
                $indexes[$i] = false;
            }
        } elseif ($index['open_level'] == OL_PRIVATE) {
            if ($index['owner_uid'] != $uid) {
                // index is not this user's one.
                $indexes[$i] = false;
            }
        }
    }
}

function filterPrivateIndex(&$indexes, $uid)
{
    $len = count($indexes);
    for ($i = 0; $i < $len; ++$i) {
        $index = &$indexes[$i];
        if ($index === false) {
            continue;
        }
        if ($index['open_level'] != OL_PRIVATE) {
            $indexes[$i] = false;
        }
    }
}

/**
 * get Index tree.
 *  get same index as $refIndex ($refIndex have same open_level and same owner_gid, and  same owner_uid).
 *
 * @param $xnpsid XNPSID
 *
 * @return tree empty array in error
 */
function genSameAreaIndexTree($xnpsid, $uid, $refIndex)
{
    $indexes = genIndexTree0($xnpsid);
    filterSameAreaIndex($indexes, $refIndex);

    return genIndexTree1($indexes);
}
function filterSameAreaIndex(&$indexes, &$refIndex)
{
    $len = count($indexes);
    for ($i = 0; $i < $len; ++$i) {
        $index = &$indexes[$i];
        if ($index === false) {
            continue;
        }
        if (!($index['open_level'] == OL_PUBLIC && $refIndex['open_level'] == OL_PUBLIC
            || $index['open_level'] == OL_GROUP_ONLY && $refIndex['open_level'] == OL_GROUP_ONLY && $index['owner_gid'] == $refIndex['owner_gid']
            || $index['open_level'] == OL_PRIVATE && $refIndex['open_level'] == OL_PRIVATE && $index['owner_uid'] == $refIndex['owner_uid'])
        ) {
            $indexes[$i] = false;
        }
    }
}

/**
 * get Index tree contains Public and Private Index.
 *
 * @param $xnpsid XNPSID
 *
 * @return tree empty array in error
 */
function genPublicPrivateIndexTree($xnpsid, $uid)
{
    $indexes = genIndexTree0($xnpsid);
    filterPublicPrivateIndex($indexes, $uid);

    return genIndexTree1($indexes);
}

function filterPublicPrivateIndex(&$indexes, $uid)
{
    $len = count($indexes);
    for ($i = 0; $i < $len; ++$i) {
        $index = &$indexes[$i];
        if ($index === false) {
            continue;
        }
        if ($index['open_level'] != OL_PUBLIC && !($index['open_level'] == OL_PRIVATE && $index['owner_uid'] == $uid)) {
            $indexes[$i] = false;
        }
    }
}

function filterWritableIndex(&$indexes, $xnpsid, $uid)
{
    $xmember_handler = &xoonips_gethandler('xoonips', 'member');
    $xgroup_handler = &xoonips_gethandler('xoonips', 'group');
    if ($xmember_handler->isModerator($uid)) {
        return;
    }
    $admin_gids = $xgroup_handler->getGroupIds($uid, true);
    $len = count($indexes);
    for ($i = 0; $i < $len; ++$i) {
        $index = &$indexes[$i];
        if ($index === false) {
            continue;
        }
        if (!($index['open_level'] == OL_PRIVATE && $index['owner_uid'] == $uid
            || $index['open_level'] == OL_GROUP_ONLY && in_array($index['owner_gid'], $admin_gids))
        ) {
            $indexes[$i] = false;
        }
    }
}

// get editable Index in editindex.php.
function filterEditableIndex(&$indexes, $xnpsid, $uid, $puid, $isPublicEditable)
{
    $xmember_handler = &xoonips_gethandler('xoonips', 'member');
    $xgroup_handler = &xoonips_gethandler('xoonips', 'group');
    $isPublicEditable |= $xmember_handler->isModerator($uid);
    $admin_gids = $xgroup_handler->getGroupIds($puid, true);
    $len = count($indexes);
    for ($i = 0; $i < $len; ++$i) {
        $index = &$indexes[$i];
        if ($index === false) {
            continue;
        }
        if (!($index['open_level'] == OL_PRIVATE && $index['owner_uid'] == $puid
            || $index['open_level'] == OL_GROUP_ONLY && in_array($index['owner_gid'], $admin_gids)
            || $index['open_level'] == OL_PUBLIC && $isPublicEditable)
        ) {
            $indexes[$i] = false;
        }
    }
}
