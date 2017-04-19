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
if (!defined('XOOPS_ROOT_PATH')) {
    exit();
}

require_once XOOPS_ROOT_PATH.'/modules/xoonips/include/lib.php';
require_once XOOPS_ROOT_PATH.'/modules/xoonips/include/AL.php';

/**
 * @brief data object of searchtext
 *
 * @li getVar('file_id') :
 * @li getVar('search_text') :
 */
class XooNIpsOrmSearchText extends XooNIpsTableObject
{
    public function __construct()
    {
        parent::__construct();
        $this->initVar('file_id', XOBJ_DTYPE_INT, 0, false);
        $this->initVar('search_text', XOBJ_DTYPE_TXTBOX, null, false);
    }
}

/**
 * @brief data object of searchtext
 */
class XooNIpsOrmSearchTextHandler extends XooNIpsTableObjectHandler
{
    public function __construct(&$db)
    {
        parent::__construct($db);
        $this->__initHandler('XooNIpsOrmSearchText', 'xoonips_search_text', 'file_id', false);
    }

    /**
     * @brief search file
     *
     * @param query query
     * @param limit the maximum number of rows to return
     * @param offset the offset of the first row to return
     * @param uid user ID
     *
     * @return array of item id
     */
    public function search($query, $limit, $offset, $uid)
    {
        $msg = false;
        $iids = false;
        $dummy = false;
        $search_cache_id = false;
        $_SESSION['XNPSID'] = session_id();
        $member_handler = &xoops_gethandler('member');
        if (empty($GLOBALS['xoopsUser'])) {
            $GLOBALS['xoopsUser'] = $member_handler->getUser($uid);
        }
        if (xnpSearchExec('quicksearch', $query, 'all', false, $dummy, $dummy, $dummy, $search_cache_id, false, 'file')) {
            // search_cache_id -> file_ids
            $criteria = new Criteria('search_cache_id', $search_cache_id);
            $criteria->setSort('item_id');
            $criteria->setStart($offset);
            if ($limit) {
                $criteria->setLimit($limit);
            }
            $join = new XooNIpsJoinCriteria('xoonips_search_cache_file', 'file_id', 'file_id', 'LEFT');
            $file_handler = &xoonips_getormhandler('xoonips', 'file');
            $files = &$file_handler->getObjects($criteria, false, 'item_id', true, $join);
            if (false === $files) {
                return false;
            }
            $item_ids = array();
            foreach ($files as $file) {
                $item_ids[] = $file->get('item_id');
            }

            return $item_ids;
        } else {
            return false;
        }
    }
}
