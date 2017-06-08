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

/**
 * @brief Data object of Paper detail information
 *
 * @li getVar('') :
 */
class XNPPaperOrmItemDetail extends XooNIpsTableObject
{
    // for column length check
    public $lengths = array(
        'paper_id' => 10,
        'journal' => 255,
        'page' => 30,
        'abstract' => 65535,
        'pubmed_id' => 30,
    );

    public function __construct()
    {
        parent::__construct();
        $this->initVar('paper_id', XOBJ_DTYPE_INT, 0, false);
        $this->initVar('journal', XOBJ_DTYPE_TXTBOX, '', true, $this->lengths['journal']);
        $this->initVar('volume', XOBJ_DTYPE_INT, null, false);
        $this->initVar('number', XOBJ_DTYPE_INT, null, false);
        $this->initVar('page', XOBJ_DTYPE_TXTBOX, null, false, $this->lengths['page']);
        $this->initVar('abstract', XOBJ_DTYPE_TXTAREA, null, false, $this->lengths['abstract']);
        $this->initVar('pubmed_id', XOBJ_DTYPE_TXTBOX, null, false, $this->lengths['pubmed_id']);
        $this->setTextAreaDisplayAttributes(false, false, false, true);
    }

    /**
     * get author objects of this item.
     *
     * @return XNPPaperOrmAuthor[]
     */
    public function getAuthors()
    {
        $handler = &xoonips_getormhandler('xnppaper', 'author');
        $criteria = new Criteria('paper_id', $this->get('paper_id'));
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
class XNPPaperOrmItemDetailHandler extends XooNIpsTableObjectHandler
{
    public function __construct(&$db)
    {
        parent::__construct($db);
        $this->__initHandler('XNPPaperOrmItemDetail', 'xnppaper_item_detail', 'paper_id', false);
    }
}
