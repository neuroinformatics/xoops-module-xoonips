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

defined('XOOPS_ROOT_PATH') || exit('XOOPS root path not defined');

require_once XOOPS_ROOT_PATH.'/modules/xoonips/class/xoonips_compo_item.class.php';
require_once XOOPS_ROOT_PATH.'/modules/xnpbinder/iteminfo.php';

/**
 * @brief Handler object that create,insert,update,get,delete XNPBinderCompo object.
 */
class XNPBinderCompoHandler extends XooNIpsItemInfoCompoHandler
{
    public function __construct(&$db)
    {
        parent::__construct($db, 'xnpbinder');
    }

    public function &create()
    {
        $binder = new XNPBinderCompo();

        return $binder;
    }

    public function insert(&$obj)
    {
        // set dirty to detail in force.
        // detail never dirty before because detail has only
        // primary key(binder_id).
        $detail = &$obj->getVar('detail');
        if ($detail->isNew()) {
            $detail->setDirty();
        }

        return parent::insert($obj);
    }

    /**
     * @param array $child_items array of XooNIpsItem of child item
     * @param array $index_ids   array of integer of index id of binder
     *
     * @return true(has private and group items) or false(doesn't have)
     */
    public function publicBinderHasNotPublicItems($child_items, $index_ids)
    {
        if (!is_array($child_items)) {
            return false;
        }
        if (!is_array($index_ids)) {
            return false;
        }
        if (count($child_items) == 0) {
            return false;
        }

        $index_handler = &xoonips_getormhandler('xoonips', 'index');
        foreach ($index_ids as $id) {
            $index = &$index_handler->get($id);
            if (!$index) {
                continue;
            }
            if (OL_PUBLIC != $index->get('open_level')) {
                continue;
            }

            foreach ($child_items as $item) {
                foreach ($item->getVar('indexes') as $index_item_link) {
                    $child_index = &$index_handler->get($index_item_link->get('index_id'));
                    if (!$child_index) {
                        continue;
                    }
                    if (OL_PUBLIC == $child_index->get('open_level')) {
                        continue 2;
                    }
                }

                return true; //one child item is not public
            }

            return false;
        }

        return false;
    }

    /**
     * @param array $child_items array of XooNIpsItem of child item
     * @param array $index_ids   array of integer of index id of binder
     *
     * @return true(has private items) or false(doesn't have private items)
     */
    public function groupBinderHasPrivateItems($child_items, $index_ids)
    {
        if (!is_array($child_items)) {
            return false;
        }
        if (!is_array($index_ids)) {
            return false;
        }
        if (count($child_items) == 0) {
            return false;
        }

        $index_handler = &xoonips_getormhandler('xoonips', 'index');
        foreach ($index_ids as $id) {
            $index = &$index_handler->get($id);
            if (!$index) {
                continue;
            }
            if (OL_GROUP_ONLY != $index->get('open_level')) {
                continue;
            }

            foreach ($child_items as $item) {
                foreach ($item->getVar('indexes') as $index_item_link) {
                    $child_index = &$index_handler->get($index_item_link->get('index_id'));
                    if (!$child_index) {
                        continue;
                    }
                    if (OL_PUBLIC == $child_index->get('open_level')
                        || OL_GROUP_ONLY == $child_index->get('open_level')
                    ) {
                        continue 2;
                    }
                }

                return true; //one child item is private public
            }

            return false;
        }

        return false;
    }

    /**
     * return template filename.
     *
     * @param string $type defined symbol
     *                     XOONIPS_TEMPLATE_TYPE_TRANSFER_ITEM_DETAIL
     *                     or XOONIPS_TEMPLATE_TYPE_TRANSFER_ITEM_LISTL
     *
     * @return template filename
     */
    public function getTemplateFileName($type)
    {
        switch ($type) {
        case XOONIPS_TEMPLATE_TYPE_TRANSFER_ITEM_DETAIL:
            return 'xnpbinder_transfer_item_detail.html';
        case XOONIPS_TEMPLATE_TYPE_TRANSFER_ITEM_LIST:
            return 'xnpbinder_transfer_item_list.html';
        default:
            return '';
        }
    }

    /**
     * return template variables of item.
     *
     * @param string $type    defined symbol
     *                        XOONIPS_TEMPLATE_TYPE_TRANSFER_ITEM_DETAIL
     *                        , XOONIPS_TEMPLATE_TYPE_TRANSFER_ITEM_LIST
     *                        or XOONIPS_TEMPLATE_TYPE_ITEM_LIST
     * @param int    $item_id
     * @param int    $uid     user id who get item
     *
     * @return array of template variables
     */
    public function getTemplateVar($type, $item_id, $uid)
    {
        $binder = &$this->get($item_id);
        if (!is_object($binder)) {
            return array();
        }
        $result = $this->getBasicTemplateVar($type, $binder, $uid);

        $links = &$binder->getVar('binder_item_links');
        if (false === $links || count($links) == 0) {
            return $result;
        }

        switch ($type) {
        case XOONIPS_TEMPLATE_TYPE_ITEM_LIST:
            return $result;
        case XOONIPS_TEMPLATE_TYPE_TRANSFER_ITEM_DETAIL:
        case XOONIPS_TEMPLATE_TYPE_TRANSFER_ITEM_LIST:
            $result['detail'] = array('child_items' => array());
            foreach ($links as $link) {
                $handler = &$this->get_item_compo_handler_by_item_id($link->get('item_id'));
                if (false === $handler) {
                    continue;
                }
                if ($handler->getPerm($item_id, $uid, 'read')) {
                    $result['detail']['child_items'][] = array(
                        'filename' => 'db:'.$handler->getTemplateFileName(XOONIPS_TEMPLATE_TYPE_TRANSFER_ITEM_LIST),
                        'var' => $handler->getTemplateVar(XOONIPS_TEMPLATE_TYPE_TRANSFER_ITEM_LIST, $link->get('item_id'), $uid),
                    );
                }
            }

            return $result;
        default:
            return $result;
        }
    }

    public function &get_item_compo_handler_by_item_id($item_id)
    {
        $falsevar = false;

        $basic_handler = &xoonips_getormhandler('xoonips', 'item_basic');
        $basic = &$basic_handler->get($item_id);
        if (false === $basic) {
            return $falsevar;
        }

        $item_type_handler = &xoonips_getormhandler('xoonips', 'item_type');
        $itemtype = &$item_type_handler->get($basic->get('item_type_id'));
        if (false === $itemtype) {
            return $falsevar;
        }

        return xoonips_getormcompohandler($itemtype->get('name'), 'item');
    }

    /**
     * get parent item ids.
     *
     * @param int item_id
     *
     * @return array
     */
    public function getItemTypeSpecificParentItemIds($item_id)
    {
        $binder_item_link_handler = &xoonips_getormhandler('xnpbinder', 'binder_item_link');
        $binder_item_links = &$binder_item_link_handler->getObjects(new Criteria('item_id', $item_id));
        $result = array();
        foreach ($binder_item_links as $binder_item_link) {
            $result[] = $binder_item_link->get('binder_id');
        }

        return $result;
    }
}

/**
 * @brief Data object that have one ore more XooNIpsTableObject for Binder type.
 */
class XNPBinderCompo extends XooNIpsItemInfoCompo
{
    public function __construct()
    {
        parent::__construct('xnpbinder');
    }

    /**
     * get child item ids of this item.
     *
     * @return array array of child item ids
     */
    public function getChildItemIds()
    {
        $basic = $this->getVar('basic');
        $binder_item_link_handler = &xoonips_getormhandler('xnpbinder', 'binder_item_link');
        $binder_item_links = &$binder_item_link_handler->getObjects(new Criteria('binder_id', $basic->get('item_id')));
        $result = array();
        foreach ($binder_item_links as $binder_item_link) {
            $result[] = $binder_item_link->get('item_id');
        }

        return $result;
    }
}
