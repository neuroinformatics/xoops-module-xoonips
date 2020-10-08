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

/**
 * @brief data object of groups users link
 *
 * @li get('groups_users_link_id') :
 * @li get('gid') : group id
 * @li get('uid') : group member uid
 * @li get('is_admin') : 1 if group admin. 0 if not group admin.
 */
class XooNIpsOrmGroupsUsersLink extends XooNIpsTableObject
{
    public function __construct()
    {
        parent::__construct();
        $this->initVar('groups_users_link_id', XOBJ_DTYPE_INT, null, false);
        $this->initVar('gid', XOBJ_DTYPE_INT, null, true);
        $this->initVar('uid', XOBJ_DTYPE_INT, null, true);
        $this->initVar('is_admin', XOBJ_DTYPE_INT, 0, true);
    }
}

/**
 * @brief handler object of groups users link
 */
class XooNIpsOrmGroupsUsersLinkHandler extends XooNIpsTableObjectHandler
{
    public function __construct(&$db)
    {
        parent::__construct($db);
        $this->__initHandler('XooNIpsOrmGroupsUsersLink', 'xoonips_groups_users_link', 'groups_users_link_id');
    }

    /**
     * remove a member from a group. cannot remove if member
     * shares(certified or certify request) items to this group.
     *
     * @param int $gid group id
     * @param int $uid user id
     *
     * @return bool FALSE if failed
     */
    public function remove($gid, $uid)
    {
        // is $uid a member of $gid ?
        $criteria = new CriteriaCompo();
        $criteria->add(new Criteria('gid', $gid));
        $criteria->add(new Criteria('uid', $uid));
        $groups_users_links = &$this->getObjects($criteria);
        if (false === $groups_users_links) {
            // error
            return false;
        }
        if (1 != count($groups_users_links)) {
            // not a member
            return false;
        }

        /* select from item_basic
         left join $join(index_item_link)
         left join $join2(index)
         where item_basic.uid=$uid and index.gid=$gid
        */
        $criteria = new CriteriaCompo();
        $criteria->add(new Criteria('uid', $uid, '=', $this->db->prefix('xoonips_item_basic')));
        $criteria->add(new Criteria('gid', $gid, '=', 'tx'));
        $join = new XooNIpsJoinCriteria('xoonips_index_item_link', 'item_id', 'item_id');
        $join2 = new XooNIpsJoinCriteria('xoonips_index', 'index_id', 'index_id', 'LEFT', 'tx');
        $join->cascade($join2);
        $item_basic_handler = &xoonips_getormhandler('xoonips', 'item_basic');
        $item_basics = &$item_basic_handler->getObjects($criteria, false, '', false, $join);
        if (false === $item_basics) {
            // error
            return false;
        }
        if (0 != count($item_basics)) {
            // cannot remove because user shares items to this group.
            return false;
        }

        return $this->delete($groups_users_links[0]);
    }
}
