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
defined('XOOPS_ROOT_PATH') || exit('XOOPS root path not defined');

/*
alter table following from xoonips 3.24
ALTER TABLE `x_xoonips_item_basic`  DROP `title`,  DROP `keywords`;
*/
/**
 * @brief data object of ItemBasic
 *
 * @li getVar('item_id') : item ID
 * @li getVar('item_type_id') : item type id
 * @li getVar('uid') : user id who register this item
 * @li getVar('description') : comment of item
 * @li getVar('doi') : Extended item ID(like a DOI)
 * @li getVar('last_update_date') : last udpate datetime since 1970/1/1 00:00:00(UTC)
 * @li getVar('creation_date') : item creation datetime since 1970/1/1 00:00:00(UTC)
 * @li getVar('publication_year') : year that original(not an item) was created
 * @li getVar('publication_month') : month that original(not an item) was created
 * @li getVar('publication_mday') : day of month that original(not an item) was created
 * @li getVar('lang') : language of item
 */
class XooNIpsOrmItemBasic extends XooNIpsTableObject
{
    public function __construct()
    {
        $this->initVar('item_id', XOBJ_DTYPE_INT, 0, false);
        $this->initVar('uid', XOBJ_DTYPE_TXTBOX, null, false, 10);
        $this->initVar('description', XOBJ_DTYPE_TXTAREA, null, false, 65535);
        $this->initVar('creation_date', XOBJ_DTYPE_INT, null, false);
        $this->initVar('last_update_date', XOBJ_DTYPE_INT, null, false);
        $this->initVar('publication_year', XOBJ_DTYPE_INT, null, false);
        $this->initVar('publication_month', XOBJ_DTYPE_INT, null, false);
        $this->initVar('publication_mday', XOBJ_DTYPE_INT, null, false);
        $this->initVar('item_type_id', XOBJ_DTYPE_INT, null, false);
        $this->initVar('lang', XOBJ_DTYPE_TXTBOX, 'eng', false, 3);
        $this->initVar('doi', XOBJ_DTYPE_TXTBOX, null, false, 65535);
        $this->setTextAreaDisplayAttributes(false, false, false, true);
    }
}
class XooNIpsOrmItemBasicHandler extends XooNIpsTableObjectHandler
{
    public function __construct(&$db)
    {
        parent::__construct($db);
        $this->__initHandler('XooNIpsOrmItemBasic', 'xoonips_item_basic', 'item_id', false);
    }

    /**
     * @brief set current time to creation_date and last_update_date if these are not initialized and call parent::insert.
     */
    public function insert(&$obj, $force = false)
    {
        $date = $obj->get('creation_date');
        if ($obj->isNew() && !isset($date)) {
            $obj->set('creation_date', time());
        }
        $date = $obj->get('last_update_date');
        if ($obj->isDirty() && !isset($date)) {
            // update last_update_date
            $obj->set('last_update_date', time());
        }

        return parent::insert($obj, $force);
    }

    /**
     * lock item.
     *
     * @param int $id item_id
     */
    public function lock($id)
    {
        $item_lock_handler = &xoonips_getormhandler('xoonips', 'item_lock');
        $item_lock_handler->lock($id);
    }

    /**
     * unlock item.
     *
     * @param int $id item_id
     */
    public function unlock($id)
    {
        $item_lock_handler = &xoonips_getormhandler('xoonips', 'item_lock');
        $item_lock_handler->unlock($id);
    }

    /**
     * lock item and index.
     *
     * @param int $item_id  item_id
     * @param int $index_id index_id
     */
    public function lockItemAndIndexes($item_id, $index_id)
    {
        $item_lock_handler = &xoonips_getormhandler('xoonips', 'item_lock');
        $item_lock_handler->lock($item_id);
        $item_lock_handler->lockIndexes($index_id);
    }

    /**
     * unlock item and index.
     *
     * @param int $item_id  item_id
     * @param int $index_id index_id
     */
    public function unlockItemAndIndexes($item_id, $index_id)
    {
        $item_lock_handler = &xoonips_getormhandler('xoonips', 'item_lock');
        $item_lock_handler->unlock($item_id);
        $item_lock_handler->unlockIndexes($index_id);
    }
}
