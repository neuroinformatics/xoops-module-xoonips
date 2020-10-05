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
 * @brief data object of cvitaes
 */
class XooNIpsOrmCvitaes extends XooNIpsTableObject
{
    public function __construct()
    {
        $this->initVar('cvitae_id', XOBJ_DTYPE_INT, 0, false);
        $this->initVar('uid', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('from_month', XOBJ_DTYPE_INT, 0, false);
        $this->initVar('from_year', XOBJ_DTYPE_INT, 0, false);
        $this->initVar('to_month', XOBJ_DTYPE_INT, 0, false);
        $this->initVar('to_year', XOBJ_DTYPE_INT, 0, false);
        $this->initVar('cvitae_title', XOBJ_DTYPE_TXTBOX, '', true, 65535);
        $this->initVar('cvitae_order', XOBJ_DTYPE_INT, 0, true);
    }
}

/**
 * @brief handler class of cvitaes
 */
class XooNIpsOrmCvitaesHandler extends XooNIpsTableObjectHandler
{
    public function __construct(&$db)
    {
        parent::__construct($db);
        $this->__initHandler('XooNIpsOrmCvitaes', 'xoonips_cvitaes', 'cvitae_id');
    }

    /**
     * insert/update/replace object.
     *
     * @param object $obj
     * @param bool   $force force operation
     *
     * @return bool FALSE if failed
     */
    public function insert(&$obj, $force = false)
    {
        if ($obj->isNew() && !$obj->doReplace()) {
            // set cvitae_order
            $uid = $obj->get('uid');
            $criteria = new Criteria('uid', $uid);
            $objs = &$this->getObjects($criteria, false, 'MAX(`cvitae_order`) AS `max`');
            if (isset($objs[0])) {
                $max = $objs[0]->getExtraVar('max');
            } else {
                $max = 0;
            }
            $obj->set('cvitae_order', $max + 1);
        }

        return parent::insert($obj, $force);
    }

    /**
     * get curriculum vitae list.
     *
     * @param int $uid user id
     *
     * @return array object instance array
     */
    public function &getCVs($uid)
    {
        $criteria = new Criteria('uid', $uid);
        $criteria->setSort('cvitae_order');
        $criteria->setOrder('ASC');

        return $this->getObjects($criteria);
    }
}
