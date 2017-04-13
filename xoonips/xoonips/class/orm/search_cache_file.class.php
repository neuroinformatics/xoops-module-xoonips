<?php

// $Revision: 1.1.4.1.2.4 $
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
 * class of XooNIps Search Cache File.
 *
 * @li getVar( 'search_cache_id' ) :search cache ID
 * @li getVar( 'file_id' ) : file id
 */
class XooNIpsOrmSearchCacheFile extends XooNIpsTableObject
{
    public function __construct()
    {
        parent::__construct();
        $this->initVar('search_cache_id', XOBJ_DTYPE_TXTBOX, null, true, null);
        $this->initVar('file_id', XOBJ_DTYPE_INT, null, true, null);
    }
}

/**
 * XooNIps search cache item Handler class.
 */
class XooNIpsOrmSearchCacheFileHandler extends XooNIpsTableObjectHandler
{
    public function __construct(&$db)
    {
        parent::__construct($db);
        $this->__initHandler('XooNIpsOrmSearchCacheFile', 'xoonips_search_cache_file', 'search_cache_id', false);
    }
}
