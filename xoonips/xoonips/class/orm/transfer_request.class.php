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
if (!defined('XOOPS_ROOT_PATH')) {
    exit();
}

/**
 * @brief Data object of XooNIps item transfer request
 *
 * @li get('item_id') : item id
 * @li get('to_uid') : user id of new item owner
 */
class XooNIpsOrmTransferRequest extends XooNIpsTableObject
{
    public function __construct()
    {
        parent::__construct();
        $this->initVar('item_id', XOBJ_DTYPE_INT, null, true, null);
        $this->initVar('to_uid', XOBJ_DTYPE_INT, null, true, null);
    }
}

/**
 * @brief Handler object of XooNIps Item Lock
 */
class XooNIpsOrmTransferRequestHandler extends XooNIpsTableObjectHandler
{
    public function __construct(&$db)
    {
        parent::__construct($db);
        $this->__initHandler('XooNIpsOrmTransferRequest', 'xoonips_transfer_request', 'item_id', false);
    }
}
