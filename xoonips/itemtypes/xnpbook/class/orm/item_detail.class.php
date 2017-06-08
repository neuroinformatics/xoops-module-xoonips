<?php

// $Revision:$
// ------------------------------------------------------------------------- //
//  XooNIps - Neuroinformatics Base Platform System                          //
//  Copyright (C) 2005-2013 RIKEN, Japan All rights reserved.                //
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
 * @brief Data object of Book detail information
 *
 * @li getVar('book_id') :
 * @li getVar('editor') :
 * @li getVar('publisher') :
 * @li getVar('isbn') :
 * @li getVar('url') :
 * @li getVar('attachment_dl_limit') :
 * @li getVar('attachment_dl_notify') :
 */
class XNPBookOrmItemDetail extends XooNIpsTableObject
{
    // for column length check
    public $lengths = array(
        'book_id' => 10,
        'classification' => 30,
        'editor' => 255,
        'publisher' => 255,
        'isbn' => 13,
        'url' => 65535,
        'attachment_dl_limit' => 1,
        'attachment_dl_notify' => 1,
    );

    public function __construct()
    {
        parent::__construct();
        $this->initVar('book_id', XOBJ_DTYPE_INT, 0, false);
        $this->initVar('editor', XOBJ_DTYPE_TXTBOX, null, false, $this->lengths['editor']);
        $this->initVar('publisher', XOBJ_DTYPE_TXTBOX, null, true, $this->lengths['publisher']);
        $this->initVar('isbn', XOBJ_DTYPE_TXTBOX, null, false, $this->lengths['isbn']);
        $this->initVar('url', XOBJ_DTYPE_TXTBOX, null, false, $this->lengths['url']);
        $this->initVar('attachment_dl_limit', XOBJ_DTYPE_INT, 0, false);
        $this->initVar('attachment_dl_notify', XOBJ_DTYPE_INT, 0, false);
    }

    /**
     * get author objects of this item.
     *
     * @return XNPBookOrmAuthor[]
     */
    public function getAuthors()
    {
        $handler = &xoonips_getormhandler('xnpbook', 'author');
        $criteria = new Criteria('book_id', $this->get('book_id'));
        $criteria->setSort('author_order');
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
class XNPBookOrmItemDetailHandler extends XooNIpsTableObjectHandler
{
    public function __construct(&$db)
    {
        parent::__construct($db);
        $this->__initHandler('XNPBookOrmItemDetail', 'xnpbook_item_detail', 'book_id', false);
    }
}
