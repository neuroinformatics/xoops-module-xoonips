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

require_once XOOPS_ROOT_PATH.'/modules/xoonips/class/xoonips_compo_item.class.php';

//- - - - - - - - - - - - - - - - - - - - - - - - - - - -
//
// Handlers
//
//- - - - - - - - - - - - - - - - - - - - - - - - - - - -
class XooNIpsIndexCompoHandler extends XooNIpsItemCompoHandler
{
    public function __construct(&$db)
    {
        parent::__construct($db);
        $this->addHandler('index', xoonips_getormhandler('xoonips', 'index'), 'index_id');
    }

    public function &create()
    {
        $index = new XooNIpsIndexCompo();

        return $index;
    }

    /**
     * return string array of index name from /(root) to specified index($index_id)
     * private index name is replaced by 'Private' if
     * $private_index_id is given.
     *
     * @param int $index_id         value of index id
     * @param int $private_index_id integer value of private index(optional)
     * @param int $fmt              (optional)string format(see XoopsObject::getVar)
     *
     * @return string array or false if failure
     */
    public function getIndexPathNames($index_id, $private_index_id = false, $fmt = 'n')
    {
        $ret = array();
        $index_handler = &xoonips_getormhandler('xoonips', 'index');
        for ($index_obj = &$index_handler->get($index_id); is_object($index_obj); $index_obj = &$index_handler->get($index_id)) {
            if ($index_id === $private_index_id) {
                $ret[] = XNP_PRIVATE_INDEX_TITLE;
            } else {
                $ret[] = $index_obj->getTitle($fmt);
            }
            $index_id = $index_obj->get('parent_index_id');
            if (IID_ROOT == $index_id) {
                break;
            }
        }

        return array_reverse($ret);
    }

    /**
     * return array of indexes from index(id=$index_id) to root index.
     *
     * @param $index_id
     *
     * @return array of XooNIpsIndex or false
     */
    public function getPathIndexes($index_id)
    {
        $indexes = array();
        do {
            $xoonips_index = $this->get($index_id);
            if (!$xoonips_index) {
                return false;
            }
            $indexes[] = $xoonips_index;
            $index = $xoonips_index->getVar('index');
            $index_id = $index->get('parent_index_id');
        } while (IID_ROOT != $index_id);

        return array_reverse($indexes);
    }

    /**
     * duplicateIndexStructure
     * - duplicate index recursively
     * - register items in group index to duplicated index.
     *
     * @param int $target_index_id index that duplicated index is inserted to
     * @param int $source_index_id index that to be duplicated
     *
     * @return int index id of copy of $source_index_id or false if failure
     */
    public function duplicateIndexStructure($target_index_id, $source_index_id)
    {
        $index_handler = &xoonips_getormhandler('xoonips', 'index');

        $target_index = &$index_handler->get($target_index_id);
        $source_index = &$index_handler->get($source_index_id);
        if (!$target_index) {
            trigger_error('target index is not found');

            return false;
        }
        if (!$source_index) {
            trigger_error('source index is not found');

            return false;
        }
        if (OL_PUBLIC != $target_index->get('open_level')) {
            trigger_error('target index must be public index');

            return false;
        }
        if (OL_GROUP_ONLY != $source_index->get('open_level')) {
            trigger_error('source index must be group index');

            return false;
        }

        $source_index_compo = &$this->get($source_index_id);
        $new_index_compo = &$source_index_compo->xoopsClone();
        $new_index = &$new_index_compo->getVar('index');
        $new_index->setDefault('index_id');
        $new_index->setDefault('uid');
        $new_index->setDefault('gid');
        $new_index->setDefault('sort_number');
        $new_index->set('open_level', OL_PUBLIC);
        $new_index->set('parent_index_id', $target_index_id);
        $new_basic = &$new_index_compo->getVar('basic');
        $new_basic->set('item_id', 0);
        $new_titles = &$new_index_compo->getVar('titles');
        $new_titles[0]->setDefault('item_id');
        if (!$this->insert($new_index_compo)) {
            trigger_error('cannot insert new index');

            return false;
        }

        $index_item_link_handler = &xoonips_getormhandler('xoonips', 'index_item_link');
        foreach ($index_item_link_handler->getObjects(new Criteria('index_id', $source_index->get('index_id'))) as $link) {
            if (!$index_item_link_handler->add($new_index->get('index_id'), $link->get('item_id'), CERTIFIED)) {
                trigger_error('cannot register item to new index');

                return false;
            }
        }

        foreach ($source_index->getAllChildren() as $child) {
            if (!$this->duplicateIndexStructure($new_index->get('index_id'), $child->get('index_id'))) {
                trigger_error('cannot duplicate sub index');

                return false;
            }
        }

        return $new_index->get('index_id');
    }

    /**
     * delete all of descendents.
     *
     * @param int $index_id index id
     *
     * @return bool
     */
    public function deleteAllDescendents($index_id)
    {
        $index_compo_handler = &xoonips_getormcompohandler('xoonips', 'index');
        $index_handler = &xoonips_getormhandler('xoonips', 'index');
        foreach ($index_handler->getAllDescendents($index_id) as $descendent) {
            if (!$index_compo_handler->deleteByKey($descendent->get('index_id'))) {
                return false;
            }
        }

        return true;
    }
}
//- - - - - - - - - - - - - - - - - - - - - - - - - - - -
//
// Data object
//
//- - - - - - - - - - - - - - - - - - - - - - - - - - - -

/**
 * @brief data object of index
 */
class XooNIpsIndexCompo extends XooNIpsItemCompo
{
    public function __construct()
    {
        parent::__construct();
        $index_handler = &xoonips_getormhandler('xoonips', 'index');
        $this->initVar('index', $index_handler->create(), true);
    }
}
