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
 * @brief data object of OAI-PMH metadata
 *
 * @li metadata_id
 * @li identifier
 * @li repository_id
 * @li format
 * @li title
 * @li search_text
 * @li datestamp
 * @li last_update_date
 * @li creation_date
 * @li date
 * @li creator
 * @li link
 * @li last_update_date_for_sort
 * @li creation_date_for_sort
 * @li date_for_sort
 */
class XooNIpsOrmOaipmhMetadata extends XooNIpsTableObject
{
    public function __construct()
    {
        $this->initVar('metadata_id', XOBJ_DTYPE_INT, 0, true, 10);
        $this->initVar('identifier', XOBJ_DTYPE_TXTBOX, '', false, 255);
        $this->initVar('repository_id', XOBJ_DTYPE_INT, 0, true, 11);
        $this->initVar('format', XOBJ_DTYPE_TXTBOX, '', true, 255);
        $this->initVar('title', XOBJ_DTYPE_TXTBOX, '', false);
        $this->initVar('search_text', XOBJ_DTYPE_TXTBOX, '', false);
        $this->initVar('datestamp', XOBJ_DTYPE_TXTBOX, '', false);
        $this->initVar('last_update_date', XOBJ_DTYPE_TXTBOX, '', false);
        $this->initVar('creation_date', XOBJ_DTYPE_TXTBOX, '', false);
        $this->initVar('date', XOBJ_DTYPE_TXTBOX, '', false);
        $this->initVar('creator', XOBJ_DTYPE_TXTBOX, '', false, 255);
        $this->initVar('link', XOBJ_DTYPE_TXTBOX, '', false);
        $this->initVar('last_update_date_for_sort', XOBJ_DTYPE_TXTBOX, '1970-01-01 00:00:00', false);
        $this->initVar('creation_date_for_sort', XOBJ_DTYPE_TXTBOX, '1970-01-01 00:00:00', false);
        $this->initVar('date_for_sort', XOBJ_DTYPE_TXTBOX, '1970-01-01 00:00:00', false);
    }
}

/**
 * @brief handler object of OAI-PMH metadata
 */
class XooNIpsOrmOaipmhMetadataHandler extends XooNIpsTableObjectHandler
{
    public function __construct(&$db)
    {
        parent::__construct($db);
        $this->__initHandler('XooNIpsOrmOaipmhMetadata', 'xoonips_oaipmh_metadata', 'metadata_id', true);
    }

    /**
     * @param string $identifier identifier string of OAI-PMH
     *
     * @return XooNIpsOrmOaipmhMetadata object or false
     */
    public function getByIdentifier($identifier)
    {
        $criteria = new Criteria('identifier', $identifier);
        $result = &$this->getObjects($criteria);
        if (!$result) {
            return false;
        }

        return $result[0];
    }
}
