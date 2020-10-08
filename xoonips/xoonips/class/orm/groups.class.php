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
 * @brief class that has group information
 *
 * @li getVar('gid') : group ID
 * @li getVar('gname') : group name
 * @li getVar('gdesc') : group description
 * @li getVar('group_index_id') : group index id
 * @li getVar('group_item_number_limit') : group item number limit
 * @li getVar('group_index_number_limit') : group index number limit
 * @li getVar('group_item_storage_limit') : group item storage limit(bytes)
 */
class XooNIpsOrmGroups extends XooNIpsTableObject
{
    public function __construct()
    {
        parent::__construct();
        // from XooNIps_users table
        $this->initVar('gid', XOBJ_DTYPE_INT, 0, false);
        $this->initVar('gname', XOBJ_DTYPE_TXTBOX, '', true, 255);
        $this->initVar('gdesc', XOBJ_DTYPE_TXTBOX, '', false, 255);
        $this->initVar('group_index_id', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('group_item_number_limit', XOBJ_DTYPE_INT, null, true);
        $this->initVar('group_index_number_limit', XOBJ_DTYPE_INT, null, true);
        // data type = double
        $this->initVar('group_item_storage_limit', XOBJ_DTYPE_OTHER, null, true);
    }

    public function cleanVars()
    {
        $retval = true;
        // is group_item_storage_limit double?
        if (!is_numeric($this->getVar('group_item_storage_limit', 'n'))) {
            // todo: define constant string
            $this->setErrors('group_item_storage_limit must be numeric.');
            $retval = false;
        }

        return $retval && parent::cleanVars();
    }
}

/**
 * @brief handler object that create, insert, update, get, delete group information
 */
class XooNIpsOrmGroupsHandler extends XooNIpsTableObjectHandler
{
    public function __construct(&$db)
    {
        parent::__construct($db);
        $this->__initHandler('XooNIpsOrmGroups', 'xoonips_groups', 'gid');
    }

    /**
     * create object.
     *
     * @param bool $isNew true if create new object
     *
     * @return object
     */
    public function &create($isNew = true)
    {
        $obj = &parent::create($isNew);
        if ($isNew) {
            $xconfig_handler = &xoonips_getormhandler('xoonips', 'config');
            $obj->set('group_item_number_limit', $xconfig_handler->getValue('group_item_number_limit'));
            $obj->set('group_index_number_limit', $xconfig_handler->getValue('group_index_number_limit'));
            $obj->set('group_item_storage_limit', $xconfig_handler->getValue('group_item_storage_limit'));
        }

        return $obj;
    }
}
