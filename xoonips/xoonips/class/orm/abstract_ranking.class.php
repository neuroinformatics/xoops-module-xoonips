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
 * @brief abstract handler object of ranking tables
 */
class XooNIpsOrmAbstractRankingHandler extends XooNIpsTableObjectHandler
{
    /**
     * object column names.
     *
     * @var array
     */
    public $columns = array();

    /**
     * flag of sum table handler.
     *
     * @var bool
     */
    public $is_sum_table = false;

    /**
     * get object column names.
     *
     */
    public function get_columns()
    {
        return $this->columns;
    }

    /**
     * set object column names.
     *
     * @param string[] $columns
     */
    public function _set_columns($columns)
    {
        $this->columns = $columns;
    }

    /**
     * set sum table flag.
     */
    public function _set_sum_table()
    {
        $this->is_sum_table = true;
    }

    /**
     * copy ranking data from ranking_sum_* table for rebuild rankings.
     *
     * @return null|resource FALSE if failed
     */
    public function copy_from_sum_table()
    {
        if ($this->is_sum_table) {
            die('invalid operation found in '.__FILE__.' at '.__LINE__);
        }
        $columns = implode(', ', $this->columns);
        $name = $this->__table_name;
        $table = $this->db->prefix($name);
        $sum_name = str_replace('ranking_', 'ranking_sum_', $name);
        $sum_table = $this->db->prefix($sum_name);
        $sql = 'INSERT INTO '.$table.' ( '.$columns.' ) SELECT '.$columns.' FROM '.$sum_table;

        return $this->_query($sql, true);
    }
}
