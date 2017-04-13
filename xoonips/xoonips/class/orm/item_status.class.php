<?php

// $Revision: 1.1.4.1.2.11 $
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
 * @brief Data object of XooNIps Item Status
 *
 * @li getVar('item_id') : item ID
 * @li getVar('created_timestamp') : creation timestamp(time_t)
 * @li getVar('modified_timestamp') : last modified timestamp(time_t)
 * @li getVar('deleted_timestamp') : deleted timestamp(time_t)
 * @li getVar('is_deleted') : deleted flag
 */
class XooNIpsOrmItemStatus extends XooNIpsTableObject
{
    public function XooNIpsOrmItemStatus()
    {
        parent::XooNIpsTableObject();
        $this->initVar('item_id', XOBJ_DTYPE_INT, null, false, null);
        $this->initVar('created_timestamp', XOBJ_DTYPE_INT, null, true, null);
        $this->initVar('modified_timestamp', XOBJ_DTYPE_INT, null, true, null);
        $this->initVar('deleted_timestamp', XOBJ_DTYPE_INT, null, true, null);
        $this->initVar('is_deleted', XOBJ_DTYPE_INT, null, true, null);
    }
}

/**
 * @brief Handler object of XooNIps Item Status
 */
class XooNIpsOrmItemStatusHandler extends XooNIpsTableObjectHandler
{
    public function XooNIpsOrmItemStatusHandler(&$db)
    {
        parent::XooNIpsTableObjectHandler($db);
        $this->__initHandler('XooNIpsOrmItemStatus', 'xoonips_item_status', 'item_id', false);
    }

    /**
     * update item status table.
     *
     * @param int $item_id Item ID to update item status
     *
     * @return bool
     */
    public function updateItemStatus($item_id)
    {
        $index_handler = &xoonips_getormhandler('xoonips', 'index');
        $item_status_handler = &xoonips_getormhandler('xoonips', 'item_status');
        $criteria1 = new CriteriaCompo();
        $criteria1->add(new Criteria('certify_state', CERTIFIED));
        $criteria1->add(new Criteria('open_level', OL_PUBLIC));
        $criteria1->add(new Criteria('item_id', intval($item_id), '=', 'txil'));
        $join = new XooNIpsJoinCriteria('xoonips_index_item_link', 'index_id', 'index_id', 'LEFT', 'txil');
        $join->cascade(new XooNIpsJoinCriteria('xoonips_item_status', 'item_id', 'item_id', 'LEFT', 'tis'), 'txil', true);
        $results = &$index_handler->getObjects($criteria1, false, 'txil.item_id, tis.is_deleted', true, $join);
        foreach ($results as $row) {
            if (is_null($row->getExtraVar('is_deleted'))) {
                $item_status = &$item_status_handler->create();
                $item_status->set('created_timestamp', time());
            } else {
                $item_status = &$item_status_handler->get($row->getExtraVar('item_id'));
                $item_status->set('modified_timestamp', time());
            }
            if (!$item_status) {
                // item_status not found
                return false;
            }

            // insert or update item_status
            $item_status->set('item_id', $row->getExtraVar('item_id'));
            $item_status->set('is_deleted', 0);
            if (!$item_status_handler->insert($item_status)) {
                return false;
            }
        }

        $item_status_handler = &xoonips_getormhandler('xoonips', 'item_status');
        $index_item_link_handler = &xoonips_getormhandler('xoonips', 'index_item_link');
        $criteria2 = new CriteriaCompo();
        $criteria2->add(new Criteria('is_deleted', 0));
        $criteria2->add(new Criteria('item_id', intval($item_id)));
        $rows = &$item_status_handler->getObjects($criteria2);
        if (!$rows) {
            return false;
        }
        foreach ($rows as $row) {
            $criteria3 = new CriteriaCompo();
            $criteria3->add(new Criteria('certify_state', CERTIFIED));
            $criteria3->add(new Criteria('open_level', OL_PUBLIC));
            $criteria3->add(new Criteria('item_id', $row->get('item_id')));
            $join = new XooNIpsJoinCriteria('xoonips_index', 'index_id', 'index_id', 'LEFT', 'tx');
            $results = &$index_item_link_handler->getObjects($criteria3, false, 'count(*)', true, $join);
            if (!$results) {
                return false;
            }
            if ($results[0]->getExtraVar('count(*)') == 0) {
                $row->set('is_deleted', 1);
                $row->set('deleted_timestamp', time());
                $item_status_handler->insert($row);
            }
        }

        return true;
    }
}
