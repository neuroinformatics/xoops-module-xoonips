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
 * The XooNIps page navigation class.
 *
 * @copyright copyright &copy; 2005-2011 RIKEN, Japan
 * @author    Yoshihiro OKUMURA <orrisroot@users.sourceforge.jp>
 */
class XooNIpsPageNavi
{
    public $_count;
    public $_limit;
    public $_page;
    public $_maxpage;
    public $_start;
    public $_order = null;
    public $_sort = null;

    public function __construct($count, $limit, $page)
    {
        if ($count <= 0) {
            $count = 0;
        }
        if ($limit <= 0) {
            $limit = 1;
        }
        if ($page <= 0) {
            $page = 1;
        }
        $this->_count = $count;
        $this->_limit = $limit;
        $this->_maxpage = intval(ceil($count / $limit));
        if ($this->_maxpage == 0) {
            $page = 1;
        } elseif ($this->_maxpage < $page) {
            $page = $this->_maxpage;
        }
        $this->_page = $page;
        $this->_start = ($page - 1) * $limit;
    }

    public function getCount()
    {
        return $this->_count;
    }

    public function getLimit()
    {
        return $this->_limit;
    }

    public function getPage()
    {
        return $this->_page;
    }

    public function getMaxPage()
    {
        return $this->_maxpage;
    }

    public function getStart()
    {
        return $this->_start;
    }

    public function getOrder()
    {
        return $this->_order;
    }

    public function getSort()
    {
        return $this->_sort;
    }

    public function setOrder($order)
    {
        if ($order != 'DESC') {
            $order = 'ASC';
        }
        $this->_order = $order;
    }

    public function setSort($sort)
    {
        $this->_sort = $sort;
    }

    public function &getCriteria()
    {
        $criteria = new CriteriaElement();
        if ($this->_order) {
            $criteria->SetOrder($this->_order);
        }
        if ($this->_sort) {
            $criteria->SetSort($this->_sort);
        }
        $criteria->SetLimit($this->_limit);
        $criteria->SetStart($this->_start);

        return $criteria;
    }

    public function &getTemplateVars($show_cols)
    {
        $vars = array();
        $vars['sort'] = $this->_sort;
        $vars['order'] = $this->_order;
        $vars['limit'] = $this->_limit;
        $vars['page'] = $this->_page;
        $vars['next'] = ($this->_page < $this->_maxpage) ? $this->_page + 1 : null;
        $vars['prev'] = ($this->_page > 1) ? $this->_page - 1 : null;
        $vars['maxpage'] = $this->_maxpage;
        // counter
        $vars['total'] = $this->_count;
        $vars['start'] = $this->_start + 1;
        $vars['end'] = $this->_start + $this->_limit;
        if ($vars['end'] > $vars['total']) {
            $vars['end'] = $vars['total'];
        }
        // navigation pages
        $diff_max = floor($show_cols / 2);
        $diff_min = $show_cols - $diff_max - 1;
        $show_minpage = $this->_page - $diff_min;
        $show_maxpage = $this->_page + $diff_max;
        if ($show_maxpage > $this->_maxpage) {
            $show_minpage -= $show_maxpage - $this->_maxpage;
            if ($show_minpage < 1) {
                $show_minpage = 1;
            }
            $show_maxpage = $this->_maxpage;
        } elseif ($show_minpage < 1) {
            $show_maxpage += 1 - $show_minpage;
            if ($show_maxpage > $this->_maxpage) {
                $show_maxpage = $this->_maxpage;
            }
            $show_minpage = 1;
        }
        $navi = array();
        for ($ii = $show_minpage; $ii <= $show_maxpage; ++$ii) {
            $navi[] = $ii;
        }
        $vars['navi'] = &$navi;

        return $vars;
    }
}
