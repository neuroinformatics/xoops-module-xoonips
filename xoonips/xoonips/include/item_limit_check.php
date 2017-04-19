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

function filesize_private()
{
    $iids = array();
    if (xnp_get_private_item_id($_SESSION['XNPSID'], $_SESSION['xoopsUserId'], $iids) != RES_OK) {
        return 0;
    }

    return filesize_by_item_id($iids);
}

function filesize_group($gid)
{
    $xgroup_handler = &xoonips_gethandler('xoonips', 'group');
    $iids = $xgroup_handler->getGroupItemIds($gid);

    return filesize_by_item_id($iids);
}

function filesize_by_item_id($iids)
{
    if (count($iids) == 0) {
        return 0;
    }
    $itemtypes = array();
    if (xnp_get_item_types($itemtypes) != RES_OK) {
        return 0;
    }

    $ret = 0.0;
    foreach ($itemtypes as $i) {
        if ($i['item_type_id'] == ITID_INDEX) {
            continue;
        }
        $modname = $i['name'];
        global $xoopsDB;

        $table = $xoopsDB->prefix("${modname}_item_detail");
        $id_name = preg_replace('/^xnp/', '', $modname).'_id';
        $query = "SELECT ${id_name} FROM $table where ${id_name} IN (".implode(', ', $iids).')';
        $result = $xoopsDB->query($query);
        if ($result) {
            $mod_iids = array();
            while (list($id) = $xoopsDB->fetchRow($result)) {
                $mod_iids[] = $id;
            }
            include_once "../$modname/include/view.php";
            $fname = "${modname}GetDetailInformationTotalSize";
            if (function_exists($fname)) {
                $ret += $fname($mod_iids);
            }
        }
    }

    return $ret;
}

/**
 * how many items can be registered more to private index?
 *
 * @return available space for register item( in a number of items )
 */
function available_space_of_private_item()
{
    $xnpsid = $_SESSION['XNPSID'];
    $uid = $_SESSION['xoopsUserId'];
    $account = array();
    if (xnp_get_account($xnpsid, $uid, $account) == RES_OK) {
        $iids = array();
        if (xnp_get_private_item_id($xnpsid, $uid, $iids) == RES_OK) {
            return max(0, $account['item_number_limit'] - count($iids));
        }
    }

    return 0;
}

/**
 * private_item_storage_limit <= file size of all attachment files registered in private area(not public)
 * : return false.
 */
function check_private_item_storage_limit()
{
    $xnpsid = $_SESSION['XNPSID'];
    $uid = $_SESSION['xoopsUserId'];
    $account = array();
    if (xnp_get_account($xnpsid, $uid, $account) == RES_OK) {
        if (filesize_private() >= $account['item_storage_limit']) {
            return false;
        }
    }

    return true;
}

/**
 * how many items can be registered more to group index ?
 *
 * @param gid id of group to be checked
 *
 * @return available space for register item( in a number of items )
 */
function available_space_of_group_item($gid)
{
    $xgroup_handler = &xoonips_gethandler('xoonips', 'group');
    $xgroup_obj = &$xgroup_handler->getGroupObject($gid);
    if (!is_object($xgroup_obj)) {
        return 0;
    }
    $item_number_limit = $xgroup_obj->getVar('group_item_number_limit', 'n');
    $iids = $xgroup_handler->getGroupItemIds($gid);

    return max(0, $item_number_limit - count($iids));
}

/**
 * groupde_item_storage_limit <= file size of all attachment files registered in group area
 * : return false.
 *
 * @param gid id of group to be checked
 *
 * @return true if available space is enough
 */
function check_group_item_storage_limit($gid)
{
    $xgroup_handler = &xoonips_gethandler('xoonips', 'group');
    $xgroup_obj = &$xgroup_handler->getGroupObject($gid);
    if (!is_object($xgroup_obj)) {
        return false;
    }
    $item_storage_limit = $xgroup_obj->getVar('group_item_storage_limit', 'n');
    if (filesize_group($gid) >= $item_storage_limit) {
        return false;
    }

    return true;
}
