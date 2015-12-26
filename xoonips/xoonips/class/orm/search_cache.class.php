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
if ( ! defined( 'XOOPS_ROOT_PATH' ) ) {
  exit();
}

/**
 * class of XooNIps Search Cache
 * @li get( 'search_cache_id' ) :search cache ID
 * @li get( 'sess_id' ) : session id
 * @li get( 'timestamp' ) : timestamp(time_t)
 *
 */
class XooNIpsOrmSearchCache extends XooNIpsTableObject {
  function XooNIpsOrmSearchCache() {
    parent::XooNIpsTableObject();
    $this->initVar( 'search_cache_id', XOBJ_DTYPE_INT, null, true, null );
    $this->initVar( 'sess_id', XOBJ_DTYPE_TXTBOX, null, true, 32 );
    $this->initVar( 'timestamp', XOBJ_DTYPE_TXTBOX, null, false, 14 );
  }
}

/**
 *
 * XooNIps search cache Handler class
 *
 */
class XooNIpsOrmSearchCacheHandler extends XooNIpsTableObjectHandler {
  function XooNIpsOrmSearchCacheHandler( &$db ) {
    parent::XooNIpsTableObjectHandler( $db );
    $this->__initHandler( 'XooNIpsOrmSearchCache', 'xoonips_search_cache', 'search_cache_id', true );
  }
}
?>
