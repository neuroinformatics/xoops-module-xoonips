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
 * @brief Data object of Tool detail information
 *
 * @li getVar('') :
 */
class XNPToolOrmItemDetail extends XooNIpsTableObject
{
    // for column length check
    public $lengths = array(
        'tool_id' => 10,
        'tool_type' => 30,
        'developer' => 255,
        'readme' => 65535,
        'rights' => 65535,
        'use_cc' => 3,
        'cc_commercial_use' => 3,
        'cc_modification' => 3,
        'attachment_dl_limit' => 1,
        'attachment_dl_notify' => 1,
    );

    public function __construct()
    {
        parent::__construct();
        $this->initVar('tool_id', XOBJ_DTYPE_INT, 0, false);
        $this->initVar('tool_type', XOBJ_DTYPE_TXTBOX, null, false, $this->lengths['tool_type']);
        $this->initVar('rights', XOBJ_DTYPE_TXTBOX, null, false, $this->lengths['rights']);
        $this->initVar('readme', XOBJ_DTYPE_TXTBOX, null, false, $this->lengths['readme']);
        $this->initVar('use_cc', XOBJ_DTYPE_INT, 0, false);
        $this->initVar('cc_commercial_use', XOBJ_DTYPE_INT, null, false);
        $this->initVar('cc_modification', XOBJ_DTYPE_INT, null, false);
        $this->initVar('attachment_dl_limit', XOBJ_DTYPE_INT, 0, false);
        $this->initVar('attachment_dl_notify', XOBJ_DTYPE_INT, 0, false);
    }

    /**
     * get developer objects of this item.
     *
     * @return XNPToolOrmDeveloper[]
     */
    public function getDevelopers()
    {
        $handler = &xoonips_getormhandler('xnptool', 'developer');
        $criteria = new Criteria('tool_id', $this->get('tool_id'));
        $criteria->setSort('developer_order');
        $result = &$handler->getObjects($criteria);
        if ($result) {
            return $result;
        }

        return array();
    }
}

/**
 * @brief Handler class that create, insert, update, get and delete detail information
 */
class XNPToolOrmItemDetailHandler extends XooNIpsTableObjectHandler
{
    public function __construct(&$db)
    {
        parent::__construct($db);
        $this->__initHandler('XNPToolOrmItemDetail', 'xnptool_item_detail', 'tool_id', false);
    }

    public function insert(&$obj, $force = false)
    {
        if (strtolower(get_class($obj)) != strtolower($this->__class_name)) {
            return false;
        }
        if (!$obj->isDirty()) {
            return true;
        }

        $cc = $this->get_cc($obj);
        if ($cc) {
            $obj->set('rights', $cc);
        }

        return parent::insert($obj, $force);
    }

    public function get_cc($detail)
    {
        if ('1' == $detail->get('use_cc')) {
            return xoonips_get_cc_license($detail->get('cc_commercial_use'), $detail->get('cc_modification'), 2.5, 'GENERIC');
        } else {
            return false;
        }
    }
}
