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
 * XooNIps Index Handler class.
 */
class XooNIpsIndexHandler
{
    public $_x_handler;
    public $_xil_handler;

    /**
     * constractor.
     */
    public function __construct()
    {
        $this->_x_handler = &xoonips_getormhandler('xoonips', 'index');
        $this->_xil_handler = &xoonips_getormhandler('xoonips', 'index_item_link');
    }

    /**
     * get index title.
     *
     * @param int    $index_id index id
     * @param string $fmt      format
     *
     * @return string title
     */
    public function getIndexTitle($index_id, $fmt)
    {
        $titles = $this->_get_index_titles_by_index_ids(array($index_id), $fmt);

        return $titles[$index_id];
    }

    /**
     * get parent index id.
     *
     * @param int $index_id index id
     *
     * @return int parent index id
     */
    public function getParentIndexId($index_id)
    {
        $obj = &$this->_x_handler->get($index_id);
        if (!is_object($obj)) {
            $this->_fatal_error('index not found', __LINE__);
        }

        return $obj->get('parent_index_id');
    }

    /**
     * get child index ids.
     *
     * @param int $index_id index id
     *
     * @return array index ids
     */
    public function getChildIndexIds($index_id)
    {
        $mcxids = $this->_get_child_index_ids_by_index_ids(array($index_id));

        return $mcxids[$index_id];
    }

    /**
     * get item ids under index.
     *
     * @param int    $index_id index id
     * @param int    $uid      user id
     * @param string $perm     permission: 'read', 'write', 'delete' or 'export'
     *
     * @return array item ids
     */
    public function getItemIds($index_id, $uid, $perm)
    {
        // set empty item permission cache first
        $iid_perm_cache = array();
        // get item ids
        $miids = $this->_get_item_ids_by_index_ids(array($index_id), $uid, $perm, $iid_perm_cache);

        return $miids[$index_id];
    }

    /**
     * get item count.
     *
     * @param int    $index_id index id
     * @param int    $uid      user id
     * @param string $perm     permission: 'read', 'write', 'delete' or 'export'
     *
     * @return int number of items
     */
    public function getCountItems($index_id, $uid, $perm)
    {
        return count($this->getItemIds($index_id, $uid, $perm));
    }

    /**
     * get index path array.
     *
     * @param int    $index_id index id
     * @param string $fmt      format
     *
     * @return array index path array
     *               array(
     *               array(
     *               'index_id' => $root_idx_id,
     *               'title' => $root_idx_title
     *               ),
     *               array(
     *               'index_id' => $child_idx_id1,
     *               'title' => $child_idx_title1
     *               ),...
     *               );
     */
    public function getIndexPathArray($index_id, $fmt)
    {
        if ($index_id == IID_ROOT) {
            return array();
        }
        $pxid = $this->getParentIndexId($index_id);
        $ret = $this->getIndexPathArray($pxid, $fmt);
        $title = $this->getIndexTitle($index_id, $fmt);
        $ret[] = array(
            'index_id' => $index_id,
            'title' => $title,
        );

        return $ret;
    }

    /**
     * get index structure array.
     *
     * @param int    $index_id root index id
     * @param string $fmt      format
     * @param int    $uid      user id
     * @param string $perm     permission: 'read', 'write', 'delete' or 'export'
     *
     * @return array index structure
     *               array(
     *               array(
     *               'index_id' => $index_id,
     *               'title' => $title,
     *               'number_of_indexes' => $cxid_num,
     *               'number_of_items' => $ciid_num,
     *               'depth' => $depth,
     *               ),...
     *               )
     */
    public function getIndexStructure($index_id, $fmt, $uid, $perm)
    {
        $x_obj = &$this->_x_handler->get($index_id);
        if (!is_object($x_obj)) {
            $this->_fatal_error('unknown index found', __LINE__);
        }
        // set empty item permission cache first
        $iid_perm_cache = array();
        // get current index information
        $depth = 0;
        $mcxids = $this->_get_child_index_ids_by_index_ids(array($index_id));
        $mciids = $this->_get_item_ids_by_index_ids(array($index_id), $uid, $perm, $iid_perm_cache);
        $xinfo = array();
        $xinfo[] = array(
            'index_id' => $index_id,
            'title' => $this->getIndexTitle($index_id, $fmt),
            'number_of_indexes' => count($mcxids[$index_id]),
            'number_of_items' => count($mciids[$index_id]),
            'depth' => $depth,
        );
        $xinfo = array_merge($xinfo, $this->_get_descendant_index_structure($mcxids[$index_id], $fmt, $depth, $uid, $perm, $iid_perm_cache));

        return $xinfo;
    }

    /**
     * get index object.
     *
     * @param int $index_id index id
     *
     * @return object instance of XooNIpsOrmIndex
     */
    public function &getIndexObject($index_id)
    {
        return $this->_x_handler->get($index_id);
    }

    /**
     * get index titles by multiple index ids.
     *
     * @param array  $xids index ids
     * @param string $fmt  format
     *
     * @return array multiple index title
     *               array(
     *               xid1 => $title1,
     *               xid2 => $title2,
     *               ...
     *               );
     */
    public function _get_index_titles_by_index_ids($xids, $fmt)
    {
        $t_handler = &xoonips_getormhandler('xoonips', 'title');
        $criteria = new CriteriaCompo(new Criteria('item_id', '('.implode(',', $xids).')', 'IN'));
        $criteria->add(new Criteria('title_id', DEFAULT_INDEX_TITLE_OFFSET));
        // hold returning index order
        $mtitles = array();
        foreach ($xids as $xid) {
            $mtitles[$xid] = '';
        }
        $res = &$t_handler->open($criteria, 'item_id, title');
        while ($t_obj = &$t_handler->getNext($res)) {
            $xid = $t_obj->get('item_id');
            $title = $t_obj->getVar('title', $fmt);
            $mtitles[$xid] = $title;
        }
        $t_handler->close($res);

        return $mtitles;
    }

    /**
     * get item ids by multiple index id.
     *
     * @param array  $xids            index ids
     * @param int    $uid             user id
     * @param string $perm            permission: 'read', 'write', 'delete' or 'export'
     * @param array  &$iid_perm_cache item ids access permission cache
     *
     * @return array multiple count items
     *               array(
     *               $xid1 => array( $iid1_1, $iid1_2,... ),
     *               $xid2 => array( $iid2_1,... ),
     *               ...
     *               );
     */
    public function _get_item_ids_by_index_ids($xids, $uid, $perm, &$iid_perm_cache)
    {
        if (!is_array($xids) || empty($xids)) {
            $this->_fatal_error('invalid index ids', __LINE__);
        }
        $index_item_link_handler = &xoonips_getormhandler('xoonips', 'index_item_link');
        $join = new XooNIpsJoinCriteria('xoonips_index', 'index_id', 'index_id', 'INNER', 'idx');
        // ignore item id has not item basic table
        $join->cascade(new XooNIpsJoinCriteria('xoonips_item_basic', 'item_id', 'item_id', 'INNER', 'ib'));
        $criteria = new Criteria('index_id', '('.implode(',', $xids).')', 'IN', 'idx');
        // hold returning index order
        $miids = array();
        foreach ($xids as $xid) {
            $miids[$xid] = array();
        }
        // get child item ids
        $item_chandler = &xoonips_getormcompohandler('xoonips', 'item');
        $res = &$index_item_link_handler->open($criteria, null, false, $join);
        while ($xil_obj = &$index_item_link_handler->getNext($res)) {
            $xid = $xil_obj->get('index_id');
            $iid = $xil_obj->get('item_id');
            if (!isset($iid_perm_cache[$iid])) {
                // check permission, if item id is not cached
                $iid_perm_cache[$iid] = $item_chandler->getPerm($iid, $uid, $perm);
            }
            if ($iid_perm_cache[$iid]) {
                $miids[$xid][] = $iid;
            }
        }
        $index_item_link_handler->close($res);

        return $miids;
    }

    /**
     * get child index ids by multiple index ids.
     *
     * @param array $xids index ids
     *
     * @return array multiple index ids
     *               array(
     *               $xid1 => array( $cxid1_1, $cxid1_2,... ),
     *               $xid2 => array( $cxid2_1,... ),
     *               ...
     *               );
     */
    public function _get_child_index_ids_by_index_ids($xids)
    {
        if (!is_array($xids) || empty($xids)) {
            $this->_fatal_error('invalid index ids', __LINE__);
        }
        $criteria = new Criteria('parent_index_id', '('.implode(',', $xids).')', 'IN');
        $criteria->setSort('sort_number');
        // hold returning index order
        $mcxids = array();
        foreach ($xids as $xid) {
            $mcxids[$xid] = array();
        }
        // get child index ids
        $res = $this->_x_handler->open($criteria, 'index_id, parent_index_id');
        while ($obj = &$this->_x_handler->getNext($res)) {
            $cpxid = $obj->get('parent_index_id');
            $cxid = $obj->get('index_id');
            $mcxids[$cpxid][] = $cxid;
        }
        $this->_x_handler->close($res);

        return $mcxids;
    }

    /**
     * get descendant index structure.
     *
     * @param array  $xids            child index ids
     * @param string $fmt             format
     * @param int    $depth           current depth
     * @param int    $uid             user id
     * @param string $perm            permission: 'read', 'write', 'delete' or 'export'
     * @param array  &$iid_perm_cache item ids access permission cache
     *
     * @return array index structure
     *               array(
     *               array(
     *               'index_id' => $index_id,
     *               'title' => $title,
     *               'number_of_indexes' => $cxid_num,
     *               'number_of_items' => $ciid_num,
     *               'depth' => $depth,
     *               ),...
     *               )
     */
    public function _get_descendant_index_structure($xids, $fmt, $depth, $uid, $perm, &$iid_perm_cache)
    {
        if (empty($xids)) {
            return array();
        }
        $cdepth = $depth + 1;
        $mcxids = $this->_get_child_index_ids_by_index_ids($xids);
        $mciids = $this->_get_item_ids_by_index_ids($xids, $uid, $perm, $iid_perm_cache);
        $mctitles = $this->_get_index_titles_by_index_ids($xids, $fmt);
        $cxinfo = array();
        foreach ($xids as $xid) {
            $cxinfo[] = array(
                'index_id' => $xid,
                'title' => $mctitles[$xid],
                'number_of_indexes' => count($mcxids[$xid]),
                'number_of_items' => count($mciids[$xid]),
                'depth' => $cdepth,
            );
            $cxinfo = array_merge($cxinfo, $this->_get_descendant_index_structure($mcxids[$xid], $fmt, $cdepth, $uid, $perm, $iid_perm_cache));
        }

        return $cxinfo;
    }

    /**
     * fatal error.
     *
     * @param string $msg  error message
     * @param int    $line line number
     */
    public function _fatal_error($msg, $line)
    {
        if (XOONIPS_DEBUG_MODE) {
            echo '<pre>';
            print_r(debug_backtrace());
            echo '</pre>';
        }
        die('fatal error : '.$msg.' in '.__FILE__.' at '.$line);
    }
}
