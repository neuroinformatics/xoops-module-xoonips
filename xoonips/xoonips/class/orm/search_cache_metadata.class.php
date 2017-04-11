<?php

// $Revision: 1.1.2.7 $
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

/*
alter table following from xoonips 3.30
ALTER TABLE `x_xoonips_search_cache_metadata` DROP PRIMARY KEY ;
ALTER TABLE `x_xoonips_search_cache_metadata`
 ADD `search_cache_metadata_id` INT( 10 ) NOT NULL
 AUTO_INCREMENT PRIMARY KEY FIRST ;
ALTER TABLE `x_xoonips_search_cache_metadata`
 ADD UNIQUE (`search_cache_id`,`identifier`);
*/
/**
 * class of XooNIps Search Cache Metadata.
 *
 * @li getVar( 'search_cache_metadata_id' ) : primary key
 * @li getVar( 'search_cache_id' ) :search cache ID
 * @li getVar( 'identifier' ) : metadata identifier
 */
class XooNIpsOrmSearchCacheMetadata extends XooNIpsTableObject
{
    public function XooNIpsOrmSearchCacheMetadata()
    {
        parent::XooNIpsTableObject();
        $this->initVar('search_cache_metadata_id', XOBJ_DTYPE_INT, null, true, null);
        $this->initVar('search_cache_id', XOBJ_DTYPE_INT, null, true, null);
        $this->initVar('identifier', XOBJ_DTYPE_TXTBOX, null, true, null);
    }
}

/**
 * XooNIps search cache item Handler class.
 */
class XooNIpsOrmSearchCacheMetadataHandler extends XooNIpsTableObjectHandler
{
    public function XooNIpsOrmSearchCacheMetadataHandler(&$db)
    {
        parent::XooNIpsTableObjectHandler($db);
        $this->__initHandler('XooNIpsOrmSearchCacheMetadata', 'xoonips_search_cache_metadata', 'search_cache_metadata_id', true);
    }
}
