<?php

// $Revision: 1.1.1.6 $
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

require_once dirname(dirname(__DIR__)).'/xoonips/class/xoonips_import_item.class.php';

class XNPBinderImportItem extends XooNIpsImportItem
{
    public function __construct()
    {
        $handler = &xoonips_getormcompohandler('xnpbinder', 'item');
        $this->_item = &$handler->create();
    }
}

class XNPBinderImportItemHandler extends XooNIpsImportItemHandler
{
    public function __construct()
    {
        parent::__construct();
    }

    public function create()
    {
        return new XNPBinderImportItem();
    }

    /**
     * @param
     */
    public function xmlEndElementHandler($parser, $name)
    {
        global $xoopsDB;
        $binder_item_links = &$this->_import_item->getVar('binder_item_links');
        $unicode = &xoonips_getutility('unicode');

        switch (implode('/', $this->_tag_stack)) {
        case 'ITEM/DETAIL':
            if (count($binder_item_links) == 0) {
                $this->_import_item->setErrors(E_XOONIPS_TAG_NOT_FOUND, ' no binder_item_link'.$this->_get_parser_error_at());
            }
            break;
        case 'ITEM/DETAIL/BINDER_ITEM_LINK':
            $handler = &xoonips_getormhandler('xnpbinder', 'binder_item_link');
            $link = &$handler->create();
            $link->set('item_id', intval($unicode->decode_utf8($this->_cdata, xoonips_get_server_charset(), 'h')));
            $binder_item_links[] = &$link;
            break;
        }

        parent::xmlEndElementHandler($parser, $name);
    }

    public function onReadFileFinished(&$item, &$import_items)
    {
        if ('xnpbinderimportitem' != strtolower(get_class($item))) {
            return;
        }

        if (count($item->getImportIndexId()) == 0) {
            return;
        }
        //if( count( $item -> getVar( 'indexes' ) ) == 0 ) return;

        $index_ids = $item->getImportIndexId();

        $chid_item_ids = array();
        foreach ($item->getVar('binder_item_links') as $binder_item_link) {
            $child_item_ids[] = $binder_item_link->get('item_id');
        }

        $child_items = array();
        foreach (array_keys($import_items) as $key) {
            if (in_array($import_items[$key]->getPseudoId(), $child_item_ids)) {
                $child_items[] = &$import_items[$key];
            }
        }

        $binder_handler = &xoonips_gethandler('xnpbinder', 'import_item');
        if ($binder_handler->publicBinderHasNotPublicItems($child_items, $index_ids)) {
            $item->setErrors(E_XOONIPS_INVALID_VALUE, 'public binder cannot have private and group items');
        }
        if ($binder_handler->groupBinderHasPrivateItems($child_items, $index_ids)) {
            $item->setErrors(E_XOONIPS_INVALID_VALUE, 'group binder cannot have private items');
        }

        parent::onReadFileFinished($item, $import_items);
    }

    /**
     * Update item_id of xnpbinder_binder_item_link.
     *
     * @param $item xooNIpsImportItem that is imported
     * @param $import_items array of all of XooNIpsImportItems
     */
    public function onImportFinished(&$item, &$import_items)
    {
        if ('xnpbinderimportitem' != strtolower(get_class($item))) {
            return;
        }

        // nothing to do for not updated, not imported binder.
        if (!$item->getUpdateFlag() && !$item->getImportAsNewFlag()) {
            return;
        }

        $pseudo_id2id = array();
        foreach ($import_items as $i) {
            $basic = &$i->getVar('basic');
            if (!$i->getUpdateFlag() && !$i->getImportAsNewFlag()) {
                continue;
            }
            if (array_key_exists($i->getPseudoId(), $pseudo_id2id)) {
                $pseudo_id2id[$i->getPseudoId()][] = $basic->get('item_id');
            } else {
                $pseudo_id2id[$i->getPseudoId()] = array($basic->get('item_id'));
            }
        }

        // update xnpbinder_binder_item_link.item_id from pseudo
        //  item id to item id
        $handler = &xoonips_getormhandler('xnpbinder', 'binder_item_link');
        $links = &$item->getVar('binder_item_links');
        $new_links = array();
        foreach (array_keys($links) as $key) {
            if (!array_key_exists($links[$key]->get('item_id'), $pseudo_id2id)) {
                continue;
            }
            foreach ($pseudo_id2id[$links[$key]->get('item_id')] as $item_id) {
                $l = &$handler->create();
                $l->set('binder_id', $links[$key]->get('binder_id'));
                $l->set('item_id', $item_id);
                $handler->insert($l);
                $new_links[] = &$l;
            }
            $handler->delete($links[$key]);
        }
        $item->setVar('binder_item_links', $new_links);

        parent::onImportFinished($item, $import_items);
    }

    public function insert(&$item)
    {
        $handler = &xoonips_getormcompohandler('xnpbinder', 'item');

        return $handler->insert($item);
    }

    public function setNew(&$item)
    {
        $handler = &xoonips_getormcompohandler('xnpbinder', 'item');

        return $handler->setNew($item);
    }

    public function unsetNew(&$item)
    {
        $handler = &xoonips_getormcompohandler('xnpbinder', 'item');

        return $handler->unsetNew($item);
    }

    public function setDirty(&$item)
    {
        $handler = &xoonips_getormcompohandler('xnpbinder', 'item');

        return $handler->setDirty($item);
    }

    public function unsetDirty(&$item)
    {
        $handler = &xoonips_getormcompohandler('xnpbinder', 'item');

        return $handler->unsetDirty($item);
    }

    /**
     * reeturn import log text of import item.
     *
     * @param $import_item reference of XooNIpsImportItem object
     *
     * @return string import log text
     */
    public function getImportLog($import_item)
    {
        $text = parent::getImportLog($import_item);
        foreach ($import_item->getVar('binder_item_links') as $link) {
            $text .= "\ndetail.binder_item_link ".$link->get('item_id');
        }

        return $text;
    }

    public function import(&$item)
    {
        if ($item->getUpdateFlag()) {
            $detail = &$item->getVar('detail');
            $detail->unsetNew();
            $detail->setDirty();
            $links = &$item->getVar('binder_item_links');
            foreach (array_keys($links) as $key) {
                $links[$key]->setDirty();
            }
        }
        parent::import($item);
    }

    /**
     * @param array $child_items array of XooNIpsImportItem of child item
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
                foreach ($item->getImportIndexId() as $child_index_id) {
                    $child_index = &$index_handler->get($child_index_id);
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
     * @param array $child_items array of XooNIpsImportItem of child item
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
                foreach ($item->getImportIndexId() as $child_index_id) {
                    $child_index = &$index_handler->get($child_index_id);
                    if (!$child_index) {
                        continue;
                    }
                    if (OL_PUBLIC == $child_index->get('open_level') || OL_GROUP_ONLY == $child_index->get('open_level')
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
}
