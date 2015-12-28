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

/*
alter table following from xoonips 3.24
ALTER TABLE `x_xoonips_search_cache_item` DROP PRIMARY KEY ;
ALTER TABLE `x_xoonips_search_cache_item` ADD `search_cache_item_id` INT( 10 ) NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST ;
ALTER TABLE `x_xoonips_search_cache_item` ADD UNIQUE (`search_cache_id`,`item_id`);
ALTER TABLE `x_xoonips_search_cache_item` ADD `matchfor_index` TINYINT( 1 ) NOT NULL DEFAULT 0 ;
ALTER TABLE `x_xoonips_search_cache_item` ADD `matchfor_item`  TINYINT( 1 ) NOT NULL DEFAULT 0 ;
ALTER TABLE `x_xoonips_search_cache_item` ADD `matchfor_file`  TINYINT( 1 ) NOT NULL DEFAULT 0 ;
*/
/**
 * class of XooNIps Search Cache Item
 * @li getVar( 'search_cache_item_id' ) : primary key
 * @li getVar( 'search_cache_id' ) :search cache ID
 * @li getVar( 'item_id' ) : item id
 * @li getVar( 'matchfor_index' ) : match for index
 * @li getVar( 'matchfor_item' )  : match for item
 * @li getVar( 'matchfor_file' )  : match for file
 *
 */
class XooNIpsOrmSearchCacheItem extends XooNIpsTableObject {
  function XooNIpsOrmSearchCacheItem() {
    parent::XooNIpsTableObject();
    $this->initVar( 'search_cache_item_id', XOBJ_DTYPE_INT, null, false, null );
    $this->initVar( 'search_cache_id', XOBJ_DTYPE_INT, null, true, null );
    $this->initVar( 'item_id', XOBJ_DTYPE_INT, null, true, null );
    $this->initVar( 'matchfor_index', XOBJ_DTYPE_INT, null, true, null );
    $this->initVar( 'matchfor_item', XOBJ_DTYPE_INT, null, true, null );
    $this->initVar( 'matchfor_file', XOBJ_DTYPE_INT, null, true, null );
  }
}

/**
 *
 * XooNIps search cache item Handler class
 *
 */
class XooNIpsOrmSearchCacheItemHandler extends XooNIpsTableObjectHandler {
  function XooNIpsOrmSearchCacheItemHandler( &$db ) {
    parent::XooNIpsTableObjectHandler( $db );
    $this->__initHandler( 'XooNIpsOrmSearchCacheItem', 'xoonips_search_cache_item', 'search_cache_item_id', true );
  }
}
?>
