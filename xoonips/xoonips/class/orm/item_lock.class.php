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

define('XOONIPS_LOCK_TYPE_NOT_LOCKED', 0);
define('XOONIPS_LOCK_TYPE_CERTIFY_REQUEST', 1);
define('XOONIPS_LOCK_TYPE_TRANSFER_REQUEST', 2);
define('XOONIPS_LOCK_TYPE_PUBLICATION_GROUP_INDEX', 3);

/**
 * @brief Data object of XooNIps Item Lock
 *
 * @li get('item_id') : item ID
 * @li get('lock_count') : lock count
 */
class XooNIpsOrmItemLock extends XooNIpsTableObject
{
    public function __construct()
    {
        parent::__construct();
        $this->initVar('item_id', XOBJ_DTYPE_INT, null, true, null);
        $this->initVar('lock_count', XOBJ_DTYPE_INT, null, true, null);
    }
}

/**
 * @brief Handler object of XooNIps Item Lock
 */
class XooNIpsOrmItemLockHandler extends XooNIpsTableObjectHandler
{
    public function __construct(&$db)
    {
        parent::__construct($db);
        $this->__initHandler('XooNIpsOrmItemLock', 'xoonips_item_lock', 'item_id', false);
    }

    /**
     * lock content(item or index).
     *
     * @param int $id item_id or index_id
     *
     * @return bool true if succeeded
     */
    public function lock($id)
    {
        $lock = &$this->get($id);
        if (!is_object($lock)) {
            $lock = &$this->create();
            $lock->set('item_id', $id);
            $lock->set('lock_count', 1);
        } else {
            $lock->set('lock_count', $lock->get('lock_count') + 1);
        }

        return $this->insert($lock);
    }

    /**
     * unlock content(item or index).
     *
     * @param int $id item_id or index_id
     *
     * @return bool true if succeeded
     */
    public function unlock($id)
    {
        $lock = &$this->get($id);
        if (!is_object($lock)) {
            return true;
        }
        if ($lock->get('lock_count') == 1) {
            return $this->delete($lock);
        } else {
            $lock->set('lock_count', $lock->get('lock_count') - 1);

            return $this->insert($lock);
        }
    }

    /**
     * get lock state of content.
     *
     * @param int $id item_id or index_id
     *
     * @return bool true if content is locked. otherwise, false.
     */
    public function isLocked($id)
    {
        $lock = &$this->get($id);
        if (!is_object($lock)) {
            return false;
        }

        return true;
    }

    /**
     * get lock reason of content.
     *
     * @param int $id item_id or index_id
     *
     * @return int XOONIPS_LOCK_TYPE_NOT_LOCKED : not locked
     * @return int xOONIPS_LOCK_TYPE_CERTIFY_REQUEST
     *             : locked because of certify request
     * @return int xOONIPS_LOCK_TYPE_TRANSFER_REQUEST
     *             : locked because of transfer request
     * @return int XOONIPS_LOCK_TYPE_PUBLICATION_GROUP_INDEX
     *             : locked because of publication group index
     */
    public function getLockType($id)
    {
        $lock = &$this->get($id);
        if (!is_object($lock)) {
            // not locked
            return XOONIPS_LOCK_TYPE_NOT_LOCKED;
        }

        $index_group_index_link_handler = &xoonips_getormhandler('xoonips', 'index_group_index_link');
        $criteria2 = new CriteriaCompo();
        $criteria2->add(new Criteria('group_index_id', $id));

        if ($index_group_index_link_handler->getObjects($criteria2) || $index_group_index_link_handler->getObjectsByGroupIndexId($id) || $index_group_index_link_handler->getObjectsByItemId($id)) {
            return XOONIPS_LOCK_TYPE_PUBLICATION_GROUP_INDEX;
        }

        $item_basic_handler = &xoonips_getormhandler('xoonips', 'item_basic');
        $item_basic = &$item_basic_handler->get($id);
        if ($item_basic === false) {
            return XOONIPS_LOCK_TYPE_NOT_LOCKED;
            // no such content
        }

        $item_type_id = $item_basic->get('item_type_id');
        if ($item_type_id == ITID_INDEX) {
            return XOONIPS_LOCK_TYPE_CERTIFY_REQUEST;
        } else {
            // is item in certify_request state ?
            $criteria = new CriteriaCompo();
            $criteria->add(new Criteria('item_id', $id));
            $criteria->add(new Criteria('certify_state', CERTIFY_REQUIRED));
            $criteria->add(new Criteria('open_level', '('.OL_GROUP_ONLY.','.OL_PUBLIC.')', 'in'));

            $index_item_link_handler = &xoonips_getormhandler('xoonips', 'index_item_link');
            $join = new XooNIpsJoinCriteria('xoonips_index', 'index_id', 'index_id');
            $index_item_links = &$index_item_link_handler->getObjects($criteria, false, '', false, $join);

            if ($index_item_links !== false && count($index_item_links) != 0) {
                return XOONIPS_LOCK_TYPE_CERTIFY_REQUEST;
            }

            // is item in transfer_request table?
            $transfer_request_handler = &xoonips_getormhandler('xoonips', 'transfer_request');
            if ($transfer_request_handler->getCount(new Criteria('item_id', $id)) != 0) {
                return XOONIPS_LOCK_TYPE_TRANSFER_REQUEST;
            }
        }

        return XOONIPS_LOCK_TYPE_NOT_LOCKED;
    }

    /**
     * lock an index and its ancestors.
     *
     * @param int $id index_id
     */
    public function lockIndexes($id)
    {
        $index_handler = &xoonips_getormhandler('xoonips', 'index');
        while ($id != IID_ROOT) {
            $index = &$index_handler->get($id);
            if (!is_object($index)) {
                // no such index
                return;
            }
            $this->lock($id);
            $id = $index->get('parent_index_id');
        }
    }

    /**
     * unlock an index and its ancestors.
     *
     * @param int $id index_id
     */
    public function unlockIndexes($id)
    {
        $index_handler = &xoonips_getormhandler('xoonips', 'index');
        while ($id != IID_ROOT) {
            $index = &$index_handler->get($id);
            if (!is_object($index)) {
                // no such index
                return;
            }
            $this->unlock($id);
            $id = $index->get('parent_index_id');
        }
    }
}
