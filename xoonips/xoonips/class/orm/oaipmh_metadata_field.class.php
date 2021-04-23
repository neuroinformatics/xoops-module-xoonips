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
 * @brief data object of OAI-PMH metadata
 *
 * @li metadata_field_id
 * @li metadata_id
 * @li format
 * @li name
 * @li order
 * @li category_name
 * @li value
 */
class XooNIpsOrmOaipmhMetadataField extends XooNIpsTableObject
{
    public function __construct()
    {
        $this->initVar('metadata_field_id', XOBJ_DTYPE_INT, 0, false, 10);
        $this->initVar('metadata_id', XOBJ_DTYPE_INT, 0, true, 10);
        $this->initVar('format', XOBJ_DTYPE_TXTBOX, '', true, 100);
        $this->initVar('name', XOBJ_DTYPE_TXTBOX, '', true, 255);
        $this->initVar('ordernum', XOBJ_DTYPE_INT, 0, true, 10);
        $this->initVar('category_name', XOBJ_DTYPE_TXTBOX, '', false, 255);
        $this->initVar('value', XOBJ_DTYPE_TXTBOX, '', true);
        $this->initVar('namespace', XOBJ_DTYPE_TXTBOX, '', false, 255);
        $this->initVar('namespace_uri', XOBJ_DTYPE_TXTBOX, '', false);
    }
}

/**
 * @brief handler object of OAI-PMH metadata
 */
class XooNIpsOrmOaipmhMetadataFieldHandler extends XooNIpsTableObjectHandler
{
    public function __construct(&$db)
    {
        parent::__construct($db);
        $this->__initHandler('XooNIpsOrmOaipmhMetadataField', 'xoonips_oaipmh_metadata_field', 'metadata_field_id', true);
    }

    /**
     * get metadata field objects array.
     *
     * @param string $identifier identifier string of OAI-PMH
     *
     * @return array of XooNIpsOrmOaipmhMetadataField object or false
     */
    public function getByIdentifier($identifier)
    {
        $criteria = new Criteria('identifier', addslashes($identifier));
        $result = &$this->getObjects($criteria);
        if (!$result) {
            return false;
        }

        return $result[0];
    }
}
