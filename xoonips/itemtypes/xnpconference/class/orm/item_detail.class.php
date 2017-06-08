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
 * @brief Data object of Conference detail information
 *
 * @li getVar('') :
 */
class XNPConferenceOrmItemDetail extends XooNIpsTableObject
{
    // for column length check
    public $lengths = array(
        'conference_id' => 10,
        'presentation_type' => 30,
        'conference_title' => 255,
        'place' => 255,
        'abstract' => 65535,
        'conference_from_year' => 10,
        'conference_from_month' => 10,
        'conference_from_mday' => 10,
        'conference_to_year' => 10,
        'conference_to_month' => 10,
        'conference_to_mday' => 10,
        'attachment_dl_limit' => 1,
        'attachment_dl_notify' => 1,
    );

    public function __construct()
    {
        parent::__construct();
        $this->initVar('conference_id', XOBJ_DTYPE_INT, 0, false);
        $this->initVar('presentation_type', XOBJ_DTYPE_TXTBOX, '', true, $this->lengths['presentation_type']);
        $this->initVar('conference_title', XOBJ_DTYPE_TXTBOX, '', true, $this->lengths['conference_title']);
        $this->initVar('place', XOBJ_DTYPE_TXTBOX, '', true, $this->lengths['place']);
        $this->initVar('abstract', XOBJ_DTYPE_TXTBOX, null, false, $this->lengths['abstract']);
        $this->initVar('conference_from_year', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('conference_from_month', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('conference_from_mday', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('conference_to_year', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('conference_to_month', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('conference_to_mday', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('attachment_dl_limit', XOBJ_DTYPE_INT, 0, false);
        $this->initVar('attachment_dl_notify', XOBJ_DTYPE_INT, 0, false);
    }

    /**
     * get author objects of this item.
     *
     * @return XNPConferenceOrmAuthor[]
     */
    public function getAuthors()
    {
        $handler = &xoonips_getormhandler('xnpconference', 'author');
        $criteria = new Criteria('conference_id', $this->get('conference_id'));
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
class XNPConferenceOrmItemDetailHandler extends XooNIpsTableObjectHandler
{
    public function __construct(&$db)
    {
        parent::__construct($db);
        $this->__initHandler('XNPConferenceOrmItemDetail', 'xnpconference_item_detail', 'conference_id', false);
    }
}
