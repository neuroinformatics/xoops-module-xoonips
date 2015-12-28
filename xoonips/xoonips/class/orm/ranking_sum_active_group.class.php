<?php
// $Revision: 1.1.2.5 $
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

require_once __DIR__.'/abstract_ranking.class.php';
require_once __DIR__.'/ranking_active_group.class.php';

/**
 * @brief handler object of ranking sum active group
 *
 */
class XooNIpsOrmRankingSumActiveGroupHandler extends XooNIpsOrmRankingActiveGroupHandler {
  function XooNIpsOrmRankingSumActiveGroupHandler( &$db ) {
    parent::XooNIpsTableObjectHandler( $db );
    $this->__initHandler( 'XooNIpsOrmRankingActiveGroup', 'xoonips_ranking_sum_active_group', 'gid', false );
    $this->_set_columns( array( 'gid', 'count' ) );
    $this->_set_sum_table();
  }
}

?>
