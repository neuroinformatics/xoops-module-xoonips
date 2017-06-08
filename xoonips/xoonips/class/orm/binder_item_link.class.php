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
 * @brief data object of binder item link
 *
 * @li getVar('binder_item_link_id') :
 * @li getVar('binder_id') :
 * @li getVar('item_id') :
 */
class XooNIpsOrmBinderItemLink extends XooNIpsTableObject
{
    public function __construct()
    {
        parent::__construct();
        $this->initVar('binder_item_link_id', XOBJ_DTYPE_INT, null, false);
        $this->initVar('binder_id', XOBJ_DTYPE_INT, null, true);
        $this->initVar('item_id', XOBJ_DTYPE_INT, null, false);
    }
}

/**
 * @brief handler object of binder item link
 */
class XooNIpsOrmBinderItemLinkHandler extends XooNIpsTableObjectHandler
{
    public function __construct(&$db)
    {
        parent::__construct($db);
        $this->__initHandler('XooNIpsOrmBinderItemLink', 'xoonips_binder_item_link', 'binder_item_link_id', true);
    }
}
