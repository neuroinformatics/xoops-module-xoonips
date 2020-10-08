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
 * @brief data object of Positions
 *
 * @li getVar('posi_id') :
 * @li getVar('posi_title') :
 * @li getVar('posi_order') :
 */
class XooNIpsOrmPositions extends XooNIpsTableObject
{
    public function __construct()
    {
        $this->initVar('posi_id', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('posi_title', XOBJ_DTYPE_TXTBOX, '', true, 50);
        $this->initVar('posi_order', XOBJ_DTYPE_INT, 0, true);
    }
}

/**
 * @brief handler object of Positions
 */
class XooNIpsOrmPositionsHandler extends XooNIpsTableObjectHandler
{
    public function __construct(&$db)
    {
        parent::__construct($db);
        $this->__initHandler('XooNIpsOrmPositions', 'xoonips_positions', 'posi_id', true);
    }

    public function getPositionList($fmt)
    {
        $criteria = new CriteriaElement();
        $criteria->setSort('posi_order');
        $objs = &$this->getObjects($criteria);
        $positionlist = array();
        foreach ($objs as $obj) {
            $posi_id = $obj->getVar('posi_id', 'n');
            $positionlist[$posi_id] = $obj->getVarArray($fmt);
        }

        return $positionlist;
    }

    public function deleteById($id)
    {
        // check existing id
        $posi_criteria = new Criteria('posi_id', $id);
        if (0 == $this->getCount($posi_criteria)) {
            return false;
        }

        // if deleting position has used in existing users,
        // change position to neutral.
        $xusers_handler = &xoonips_getormhandler('xoonips', 'users');
        $xusers_criteria = new Criteria('posi', $id);
        $xusers_objs = &$xusers_handler->getObjects($xusers_criteria);
        foreach ($xusers_objs as $xusers_obj) {
            $xusers_obj->set('posi', 0);
            $xusers_handler->insert($xusers_obj);
        }

        // delete
        return $this->deleteAll($posi_criteria);
    }
}
