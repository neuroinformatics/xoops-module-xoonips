<?php

// $Revision: 1.1.2.7 $
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

require_once __DIR__.'/abstract_ranking.class.php';

/**
 * @brief data object of ranking contributing user
 *
 * @li getVar('item_id') :
 * @li getVar('uid') :
 * @li getVar('timestamp') :
 */
class XooNIpsOrmRankingContributingUser extends XooNIpsTableObject
{
    public function XooNIpsOrmRankingContributingUser()
    {
        parent::XooNIpsTableObject();
        $this->initVar('item_id', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('uid', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('timestamp', XOBJ_DTYPE_OTHER, null, false);
    }
}

/**
 * @brief handler object of ranking contributing user
 */
class XooNIpsOrmRankingContributingUserHandler extends XooNIpsOrmAbstractRankingHandler
{
    public function XooNIpsOrmRankingContributingUserHandler(&$db)
    {
        parent::XooNIpsTableObjectHandler($db);
        $this->__initHandler('XooNIpsOrmRankingContributingUser', 'xoonips_ranking_contributing_user', 'item_id', false);
        $this->_set_columns(array('item_id', 'uid', 'timestamp'));
    }

    /**
     * insert/upldate/replace object.
     *
     * @param object &$obj
     * @param bool   $force force operation
     *
     * @return bool false if failed
     */
    public function insert(&$obj, $force = false)
    {
        $item_id = $obj->get('item_id');
        $uid = $obj->get('uid');
        if ($item_id == 0 || $uid == 0) {
            // ignore if item id or user id is zero
            return true;
        }

        return parent::insert($obj, $force);
    }

    /**
     * replace contributing user raking data for updating/rebuilding rankings.
     *
     * @param int $item_id   item id
     * @param int $uid       user id
     * @param int $timestamp timestamp
     *
     * @return bool FALSE if failed
     */
    public function replace($item_id, $uid, $timestamp)
    {
        $obj = &$this->create();
        $obj->setReplace();
        $obj->set('item_id', $item_id);
        $obj->set('uid', $uid);
        $obj->set('timestamp', date('Y-m-d H:i:s', $timestamp));
        // force insertion
        return $this->insert($obj, true);
    }
}
