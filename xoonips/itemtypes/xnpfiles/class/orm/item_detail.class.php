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
 * @brief Data object of Files detail information
 *
 * @li getVar('') :
 */
class XNPFilesOrmItemDetail extends XooNIpsTableObject
{
    // for column length check
    public $lengths = array(
        'files_id' => 10,
        'data_file_name' => 255,
        'data_file_mimetype' => 255,
        'data_file_filetype' => 255,
    );

    public function __construct()
    {
        parent::__construct();
        $this->initVar('files_id', XOBJ_DTYPE_INT, 0, false);
        $this->initVar('data_file_name', XOBJ_DTYPE_TXTBOX, null, true, $this->lengths['data_file_name']);
        $this->initVar('data_file_mimetype', XOBJ_DTYPE_TXTBOX, null, false, $this->lengths['data_file_mimetype']);
        $this->initVar('data_file_filetype', XOBJ_DTYPE_TXTBOX, null, false, $this->lengths['data_file_filetype']);
    }
}

/**
 * @brief Handler class that create, insert, update, get and delete detail information
 */
class XNPFilesOrmItemDetailHandler extends XooNIpsTableObjectHandler
{
    public function __construct(&$db)
    {
        parent::__construct($db);
        $this->__initHandler('XNPFilesOrmItemDetail', 'xnpfiles_item_detail', 'files_id', false);
    }
}
