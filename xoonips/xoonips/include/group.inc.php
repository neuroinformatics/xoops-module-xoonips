<?php

// $Revision: 1.1.4.2 $
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
 * show error.
 *
 * @param string $reason 'select', 'insert', 'delete',
 *                       'lock_edit', 'lock_delete'
 */
function xoonips_group_error($url, $reason)
{
    $messages = array(
        'select' => _MD_XOONIPS_ERROR_GROUP_SELECT,
        'insert' => _MD_XOONIPS_ERROR_GROUP_INSERT,
        'update' => _MD_XOONIPS_ERROR_GROUP_UPDATE,
        'delete' => _MD_XOONIPS_ERROR_GROUP_DELETE,
        'lock_edit' => sprintf(_MD_XOONIPS_ERROR_CANNOT_EDIT_LOCKED_GROUP, _MD_XOONIPS_LOCK_TYPE_STRING_CERTIFY_REQUEST),
        'lock_delete' => sprintf(_MD_XOONIPS_ERROR_CANNOT_DELETE_LOCKED_GROUP, _MD_XOONIPS_LOCK_TYPE_STRING_CERTIFY_REQUEST),
    );
    $msg = isset($messages[$reason]) ? $messages[$reason] : 'fatal error';
    redirect_header($url, 3, $msg);
    exit();
}

/**
 * get group list.
 *
 * @return array group list
 */
function xoonips_group_get_groups($uid, $gids = null)
{
    $xgroup_handler = &xoonips_gethandler('xoonips', 'group');
    $xmember_handler = &xoonips_gethandler('xoonips', 'member');
    $u_handler = &xoonips_getormhandler('xoonips', 'xoops_users');
    $is_admin = $xmember_handler->isAdmin($uid);
    $xg_objs = &$xgroup_handler->getGroupObjects($gids);
    $groups = array();
    foreach ($xg_objs as $xg_obj) {
        $gid = $xg_obj->get('gid');
        $gadmin_uids = $xgroup_handler->getUserIds($gid, true);
        $is_gadmin = in_array($uid, $gadmin_uids);
        $gadmins = array();
        foreach ($gadmin_uids as $gadmin_uid) {
            $u_obj = &$u_handler->get($gadmin_uid);
            if (!is_object($u_obj)) {
                continue;
            }
            $gadmins[] = array(
                'uid' => $gadmin_uid,
                'uname' => $u_obj->getVar('uname', 's'),
            );
        }
        $groups[] = array(
            'gid' => $gid,
            'gname' => $xg_obj->get('gname', 's'),
            'gdesc' => $xg_obj->get('gdesc', 's'),
            'locked' => (!xoonips_group_check_perm($gid)),
            'gadmins' => $gadmins,
            'is_admin' => ($is_admin || $is_gadmin),
        );
    }

    return $groups;
}

/**
 * get user list.
 *
 * @param array $gadmin_uids group admin user ids
 *
 * @return array user list
 */
function xoonips_group_get_users($gadmin_uids)
{
    $u_handler = &xoonips_getormhandler('xoonips', 'xoops_users');
    $join = new XooNIpsJoinCriteria('xoonips_users', 'uid', 'uid', 'INNER', 'xu');
    $criteria = new CriteriaCompo(new Criteria('level', 0, '>'));
    $criteria->add(new Criteria('activate', 1, '=', 'xu'));
    $criteria->setSort('uname');
    $criteria->setOrder('ASC');
    $u_objs = &$u_handler->getObjects($criteria, false, '', false, $join);
    $gadmins = array();
    foreach ($u_objs as $u_obj) {
        $uid = $u_obj->get('uid');
        $gadmins[] = array(
            'uid' => $uid,
            'uname' => $u_obj->getVar('uname', 's'),
            'isadmin' => in_array($uid, $gadmin_uids),
        );
    }

    return $gadmins;
}

/**
 * check group index locking status for group root index.
 *
 * @param int $gid group id
 *
 * @return bool false if not permissive
 */
function xoonips_group_check_perm($gid)
{
    $index_handler = &xoonips_getormhandler('xoonips', 'index');
    $criteria = new Criteria('gid', $gid);
    $join = new XooNIpsJoinCriteria('xoonips_item_lock', 'index_id', 'item_id', 'INNER');

    return  $index_handler->getCount($criteria, $join) == 0;
}
