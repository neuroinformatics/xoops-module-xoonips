<?php

// $Revision: 1.1.2.4 $
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

require_once XOOPS_ROOT_PATH.'/modules/xoonips/class/base/relatedobject.class.php';
class XooNIpsUserCompoHandler extends XooNIpsRelatedObjectHandler
{
    public function XooNIpsUserCompoHandler(&$db)
    {
        $u_handler = &xoonips_getormhandler('xoonips', 'xoops_users');
        $xu_handler = &xoonips_getormhandler('xoonips', 'users');
        parent::XooNIpsRelatedObjectHandler($db);
        parent::__init_handler('xoops_user', $u_handler, 'uid');
        $this->addHandler('xoonips_user', $xu_handler, 'uid');
    }

    public function &create()
    {
        $user = new XooNIpsUserCompo();

        return $user;
    }

    /**
     * @param int $uid uid of transferee
     *
     * @return true if uid is activated and certified user
     */
    public function isCertifiedUser($uid)
    {
        $c = new CriteriaCompo();
        $c->add(new Criteria('uid', intval($uid)));
        $c->add(new Criteria('level', 1, '>='));
        $rows = &$this->getObjects($c);
        if ($rows && count($rows) == 1) {
            $user = $rows[0]->getVar('xoonips_user');

            return $user->get('activate') == 1;
        }

        return false;
    }

    /**
     * delete user account and related data
     * - delete user account
     * - delete user's items
     * - delete user's private indexes
     * - remove user from groups
     * - remove user from xoonips groups
     * - remove user from notifications.
     *
     * @param int $uid uid to be deleted
     */
    public function deleteAccount($uid)
    {
        $criteria = new Criteria('uid', intval($uid));

        //delete user's item
        $item_type_handler = &xoonips_getormhandler('xoonips', 'item_type');
        foreach ($item_type_handler->getObjects() as $itemtype) {
            if ($itemtype->get('item_type_id') == ITID_INDEX) {
                continue;
            }
            $item_handler = &xoonips_getormcompohandler($itemtype->get('name'), 'item');
            if (!$item_handler) {
                continue;
            }
            foreach ($item_handler->getObjects($criteria) as $item) {
                $item_handler->delete($item);
            }
        }

        //remove user from groups
        $member_handler = &xoops_gethandler('member');
        if ($member_handler->getUser($uid)) {
            $member_handler->deleteUser($member_handler->getUser($uid));
        }

        //remove user from xoonips groups
        $xgroups_users_link_handler = &xoonips_getormhandler('xoonips', 'groups_users_link');
        $xgroups_users_link_handler->deleteAll($criteria);

        //delete index
        $index_compo_handler = &xoonips_getormcompohandler('xoonips', 'index');
        foreach ($index_compo_handler->getObjects($criteria) as $index) {
            $index_compo_handler->delete($index);
        }

        //remove user from notifications
        $notification_handler = &xoops_gethandler('notification');
        $notification_handler->deleteAll(new Criteria('not_uid', intval($uid)));

        //delete xoonips user
        $xu_handler = &xoonips_getormhandler('xoonips', 'users');
        $xu_handler->deleteAll($criteria);

        return true;
    }
}
class XooNIpsUserCompo extends XooNIpsRelatedObject
{
    public function XooNIpsUserCompo()
    {
        parent::XooNIpsRelatedObject();
        $u_handler = &xoonips_getormhandler('xoonips', 'xoops_users');
        $u_obj = &$u_handler->create();
        $xu_handler = &xoonips_getormhandler('xoonips', 'users');
        $xu_obj = &$xu_handler->create();
        $this->initVar('xoops_user', $u_obj, 'uid', true);
        $this->initVar('xoonips_user', $xu_obj, 'uid', true);
    }
}
