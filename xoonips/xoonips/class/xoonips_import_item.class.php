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

class XooNIpsImportItemCollection
{
    /**
     * option of importing as new item.
     */
    public $_import_as_new_option = false;

    /**
     * option of logging.
     */
    public $_logging_option = false;

    /**
     * associative array(pseudo item id => XooNIpsImportItem).
     */
    public $_items = array();

    /**
     * original file name of imoprt file.
     */
    public $_original_filename = null;

    /**
     * error message array.
     */
    public $_error_messages = array();

    public function __construct()
    {
    }

    /**
     * set import file name.
     */
    public function setImportFileName($filename)
    {
        $this->_original_filename = $filename;
    }

    /**
     * get import file name.
     */
    public function getImportFileName()
    {
        return $this->_original_filename;
    }

    /**
     * set import file name.
     */
    public function setImportAsNewOption($option)
    {
        $this->_import_as_new_option = $option;
    }

    /**
     * get import file name.
     */
    public function getImportAsNewOption()
    {
        return $this->_import_as_new_option;
    }

    /**
     * set logging option.
     *
     * @param bool $option
     */
    public function setLoggingOption($option)
    {
        $this->_logging_option = $option;
    }

    /**
     * get logging option.
     */
    public function getLoggingOption()
    {
        return $this->_logging_option;
    }

    /**
     * add import item.
     *
     * @param $item integer pseudo item id of duplicate item to add
     */
    public function addItem(&$item)
    {
        $this->_items[] = $item;
    }

    /**
     * remove import item.
     *
     * @param $item integer pseudo item id of duplicate item to remove
     */
    public function removeItem(&$item)
    {
        foreach ($this->_items as $key => $val) {
            if ($item->getPseudoId() == $val->getPseudoId()) {
                unset($this->_items[$key]);
            }
        }
        $this->_items = array_values($this->_items);
    }

    /**
     * remove import item by indexed value.
     *
     * @param $index integer that index item to remove
     */
    public function removeItemAt($index)
    {
        if (array_key_exists($key, $this->_items)) {
            unset($this->_items[$key]);
        }
        $this->_items = array_values($this->_items);
    }

    /**
     * get an array of import item.
     *
     * @return array XooNIpsImpotItem(s)
     */
    public function &getItems()
    {
        return $this->_items;
    }

    /**
     * * add error message.
     *
     * @param string $msg string error message
     */
    public function addError($msg)
    {
        $this->_error_messages[] = $msg;
    }

    /**
     * get an array of error message.
     *
     * @return array string error message
     */
    public function getErrors()
    {
        return $this->_error_messages;
    }
}

class XooNIpsImportItem extends XoopsObject
{
    /**
     * XooNIpsItemCompo object.
     */
    public $_item = null;

    /**
     * xml filename string.
     */
    public $_xml_filename = null;

    /**
     * pseudo item id.
     */
    public $_pseudo_id = 0;

    /**
     * array of index id that item is imported.
     */
    public $_import_index_ids = array();

    /**
     * flag of update import.
     */
    public $_update_flag = false;

    /**
     * array of duplicate pseudo item id.
     */
    public $_duplicate_pseudo_ids = array();

    /**
     * array of duplicate updatable item id.
     */
    public $_duplicate_updatable_item_ids = array();

    /**
     * array of duplicate un-updatable item id.
     */
    public $_duplicate_un_updatable_item_ids = array();

    /**
     * array of duplicate locked item id.
     */
    public $_duplicate_locked_item_ids = array();

    /**
     * flag of importing as new item.
     */
    public $_import_as_new_flag = false;

    /**
     * flag of certifying automatically.
     */
    public $_certify_auto_flag = false;

    /**
     * set of imported item id(s).
     */
    public $_imported_item_ids = array();

    /**
     * an array of error codes.
     */
    public $_error_codes = array();

    /**
     * update item id(false means don't update).
     */
    public $_update_item_id = false;

    /**
     * flag of doi conflict.
     */
    public $_doi_conflict_flag = false;

    public function __construct()
    {
        $handler = &xoonips_getormcompohandler('xoonips', 'item');
        $this->_item = &$handler->create();
    }

    /**
     * add an error and error code.
     *
     * @param string $err_code error code to add
     * @param string $err_str  error to add
     */
    public function setImportErrors($err_code, $err_str)
    {
        $this->_error_codes[] = $err_code;
        parent::setErrors($err_str);
    }

    /**
     * return the error codes for this object as an array.
     *
     * @return array an array of error codes
     */
    public function getErrorCodes()
    {
        return $this->_error_codes;
    }

    /**
     * @param int    $line
     * @param string $file
     * @param string $func
     */
    public function getErrorAt($line, $file, $func)
    {
        return '';
    }

    /**
     * add duplicate pseudo id to set.
     *
     * @param $item_id integer pseudo item id of duplicate item to add
     */
    public function addDuplicatePseudoId($item_id)
    {
        $this->_duplicate_pseudo_ids[] = $item_id;
        $this->_duplicate_pseudo_ids = array_values(array_unique($this->_duplicate_pseudo_ids));
    }

    /**
     * get a set of duplicate pseudo item ids.
     *
     * @return array a set of duplicate pseudo item ids
     */
    public function getDuplicatePseudoId()
    {
        return $this->_duplicate_pseudo_ids;
    }

    /**
     * add duplicate updatable item id to set.
     *
     * @param $item_id integer pseudo item id of duplicate item to add
     */
    public function addDuplicateUpdatableItemId($item_id)
    {
        $this->_duplicate_updatable_item_ids[] = $item_id;
        $this->_duplicate_updatable_item_ids = array_values(array_unique($this->_duplicate_updatable_item_ids));
    }

    /**
     * get a set of duplicate pseudo item ids.
     *
     * @return array a set of duplicate pseudo item ids
     */
    public function getDuplicateUpdatableItemId()
    {
        return $this->_duplicate_updatable_item_ids;
    }

    /**
     * add duplicate item id to set.
     *
     * @param $item_id integer pseudo item id of duplicate item to add
     */
    public function addDuplicateUnupdatableItemId($item_id)
    {
        $this->_duplicate_un_updatable_item_ids[] = $item_id;
        $this->_duplicate_un_updatable_item_ids = array_values(array_unique($this->_duplicate_un_updatable_item_ids));
    }

    /**
     * get a set of duplicate pseudo item ids.
     *
     * @return array a set of duplicate pseudo item ids
     */
    public function getDuplicateUnupdatableItemId()
    {
        return $this->_duplicate_un_updatable_item_ids;
    }

    /**
     * add duplicate item id to set.
     *
     * @param $item_id integer pseudo item id of duplicate item to add
     */
    public function addDuplicateLockedItemId($item_id)
    {
        $this->_duplicate_locked_item_ids[] = $item_id;
        $this->_duplicate_locked_item_ids = array_values(array_unique($this->_duplicate_locked_item_ids));
    }

    /**
     * get a set of duplicate pseudo item ids.
     *
     * @return array a set of duplicate pseudo item ids
     */
    public function getDuplicateLockedItemId()
    {
        return $this->_duplicate_locked_item_ids;
    }

    /**
     * add index id item to be imported to.
     *
     * @param $index_id integer
     */
    public function addImportIndexId($index_id)
    {
        $this->_import_index_ids[] = $index_id;
        $this->_import_index_ids = array_values(array_unique($this->_import_index_ids));
    }

    /**
     * get a set of index id(s) item to be imported to.
     *
     * @return array a set of index id(s) item to be imported to
     */
    public function getImportIndexId()
    {
        return $this->_import_index_ids;
    }

    /**
     * set import as new flag.
     *
     * @param bool flag
     */
    public function setImportAsNewFlag($flag)
    {
        $this->_import_as_new_flag = (bool) $flag;
    }

    /**
     * get import as new flag.
     *
     * @return bool import as new flag
     */
    public function getImportAsNewFlag()
    {
        return $this->_import_as_new_flag;
    }

    /**
     * set doi conflict flag.
     *
     * @param bool flag
     */
    public function setDoiConflictFlag($flag)
    {
        $this->_doi_conflict_flag = (bool) $flag;
    }

    /**
     * get doi conflict flag.
     *
     * @return bool doi conflict flag
     */
    public function getDoiConflictFlag()
    {
        return $this->_doi_conflict_flag;
    }

    /**
     * set update import flag.
     *
     * @param bool flag
     */
    public function setUpdateFlag($flag)
    {
        $this->_update_flag = (bool) $flag;
    }

    /**
     * get update import flag.
     *
     * @return bool update import flag
     */
    public function getUpdateFlag()
    {
        return $this->_update_flag;
    }

    /**
     * set item id of update import.
     *
     * @param $flag integer item id to update
     */
    public function setUpdateItemId($item_id)
    {
        $this->_update_item_id = intval($item_id);
    }

    /**
     * get item id of update import.
     *
     * @return bool item id to update
     */
    public function getUpdateItemId()
    {
        return $this->_update_item_id;
    }

    /**
     * set certify automatically flag.
     *
     * @param bool flag
     */
    public function setCertifyAutoFlag($flag)
    {
        $this->_certify_auto_flag = (bool) $flag;
    }

    /**
     * get certify automatically flag.
     *
     * @return bool certify automatically flag
     */
    public function getCertifyAutoFlag()
    {
        return $this->_certify_auto_flag;
    }

    /**
     * set pseudo item id.
     *
     * @param int $id integer
     */
    public function setPseudoId($id)
    {
        $this->_pseudo_id = intval($id);
    }

    /**
     * get pseudo item id.
     *
     * @return int pseudo item id
     */
    public function getPseudoId()
    {
        return $this->_pseudo_id;
    }

    /**
     * set XML filename.
     *
     * @param $filename string
     */
    public function setFilename($filename)
    {
        $this->_xml_filename = $filename;
    }

    /**
     * get XML filename.
     *
     * @return string XML filename
     */
    public function getFilename()
    {
        return $this->_xml_filename;
    }

    /**
     * get total file size(bytes) of this item.
     *
     * @return int file size in bytes
     */
    public function getTotalFileSize()
    {
        return 0;
    }

    /**
     * Is this importable as new item ?
     *
     * @return bool true(importable) or false(not)
     */
    public function isImportableAsNew()
    {
        if (count($this->getDuplicateUpdatableItemId()) >= 0) {
            return false;
        }
        $lock_handler = &xoonips_getormhandler('xoonips', 'item_lock');
        foreach ($this->getDuplicateUnupdatableItemId() as $id) {
            if ($lock_handler->isLocked($id)) {
                return false;
            }
        }

        return true;
    }

    /*
     * delegate these methods
     * - initVar
     * - setVar
     * - getVars
     * - getVar
     * - isFilledRequired
     */
    public function initVar($key, $data_type, $value = null, $required = false, $maxlength = null, $options = '')
    {
        $this->_item->initVar($key, $value, $required);
    }

    /**
     * @param string $key
     */
    public function setVar($key, $val, $not_gpc = false)
    {
        $this->_item->setVar($key, $val);
    }

    public function &getVars()
    {
        return $this->_item->getVars();
    }

    /**
     * @param string $key
     */
    public function &getVar($key, $format = 's')
    {
        return $this->_item->getVar($key);
    }

    public function isFilledRequired(&$missing)
    {
        return $this->_item->isFilledRequired($missing);
    }

    public function &getClone()
    {
        $class = get_class($this);
        $clone = new $class();

        $clone->_item = &$this->_item->xoopsClone();
        $clone->_xml_filename = null;
        $clone->_pseudo_id = $this->_pseudo_id;
        $clone->_import_index_ids = $this->_import_index_ids;
        $clone->_update_flag = $this->_update_flag;
        $clone->_duplicate_pseudo_ids = $this->_duplicate_pseudo_ids;
        $clone->_duplicate_updatable_item_ids = $this->_duplicate_updatable_item_ids;
        $clone->_duplicate_un_updatable_item_ids = $this->_duplicate_un_updatable_item_ids;
        $clone->_duplicate_locked_item_ids = $this->_duplicate_locked_item_ids;
        $clone->_import_as_new_flag = $this->_import_as_new_flag;
        $clone->_doi_conflict_flag = $this->_doi_conflict_flag;
        $clone->_certify_auto_flag = $this->_certify_auto_flag;
        $clone->_imported_item_ids = $this->_imported_item_ids;
        $clone->_error_codes = $this->_error_codes;

        return $clone;
    }

    public function getItemAbstractText()
    {
        $basic = &$this->getVar('basic');
        $titles = &$this->getVar('titles');
        $indexes = &$this->getVar('indexes');
        $handler = &xoonips_getormhandler('xoonips', 'item_type');
        $itemtype = &$handler->get($basic->get('item_type_id'));

        $ret = array();
        foreach ($titles as $title) {
            $ret[] = $title->get('title');
        }
        if ($itemtype) {
            $ret[] = $itemtype->get('display_name');
        }

        return implode("\n", $ret);
    }
}

class XooNIpsImportItemHandler
{
    // directory path which attachment file is in.
    public $_attachment_dir = '';

    // current import item object for parseXml
    public $_import_item = null;

    // xml parser object
    public $_parser = null;

    // current cdata
    public $_cdata = null;

    // xml tag stack
    public $_tag_stack = array();

    public $_import_index_ids = array();

    // internal server encoding
    public $_encoding = '';

    public function __construct()
    {
        $this->_encoding = xoonips_get_server_charset();
        mb_regex_encoding($this->_encoding);
    }

    public function create()
    {
        return new XooNIpsImportItem();
    }

    /**
     * create XooNIpsImportItem object from XML.
     *
     * @param $xml string xml text to parse
     *
     * @return XooNIpsImportItem
     */
    public function &parseXml($xml)
    {
        $this->_import_item = $this->create();

        $this->_parser = xml_parser_create();
        xml_set_character_data_handler($this->_parser, 'xmlCdataHandler');
        xml_set_element_handler($this->_parser, 'xmlStartElementHandler', 'xmlEndElementHandler');
        xml_set_object($this->_parser, $this);

        xml_parse($this->_parser, $xml);

        return $this->_import_item;
    }

    /**
     * add index id item to be imported to.
     *
     * @param $index_id integer
     */
    public function addImportIndexId($index_id)
    {
        $this->_import_index_ids[] = $index_id;
        $this->_import_index_ids = array_values(array_unique($this->_import_index_ids));
    }

    /**
     * remove index id item to be imported to.
     *
     * @param $index_id integer
     */
    public function removeImportIndexId($index_id)
    {
        $this->_import_index_ids = array_values(array_diff($this->_import_index_ids, array($index_id)));
    }

    /**
     * get a set of duplicate pseudo item ids.
     *
     * @return array a set of index id(s) item to be imported to
     */
    public function getImportIndexId()
    {
        return $this->_import_index_ids;
    }

    /**
     * set directory path which attachment file is in.
     *
     * @param bool flag
     */
    public function setAttachmentDirectoryPath($path)
    {
        $this->_attachment_dir = $path;
    }

    /**
     * get directory path which attachment file is in.
     *
     * @return string directory path which attachment file is in
     */
    public function getAttachmentDirectoryPath()
    {
        return $this->_attachment_dir;
    }

    /**
     * set certify auto opution to all XooNIpsImportItems.
     *
     * @param $all_import_items array of reference
     * of all XooNIpsImportItem objects
     * @param $option boolean certify auto option
     * (true means auto, false means maually)
     */
    public function setCertifyAutoOption(&$all_import_items, $option)
    {
        foreach (array_keys($all_import_items) as $key) {
            $all_import_items[$key]->setCertifyAutoFlag($option);
        }
    }

    /**
     * find all duplicate items.
     *
     * @param $all_import_items array of reference
     * of all XooNIpsImportItem objects
     */
    public function findDuplicateItems($all_import_items)
    {
        $title_item_map = &$this->_create_title_item_map($all_import_items);
        foreach (array_keys($all_import_items) as $key) {
            $basic = &$all_import_items[$key]->getVar('basic');
            $handler = &$this->_get_import_item_handler_by_item_type_id($basic->get('item_type_id'));
            foreach ($handler->_findDuplicateImportItemIds($all_import_items[$key], $all_import_items, $title_item_map) as $pseudo_id) {
                $all_import_items[$key]->addDuplicatePseudoId($pseudo_id);
            }
            foreach ($handler->_findDuplicateUpdatableItemIds($all_import_items[$key]) as $item_id) {
                $all_import_items[$key]->addDuplicateUpdatableItemId($item_id);
            }
            foreach ($handler->_findDuplicateUnupdatableItemIds($all_import_items[$key]) as $item_id) {
                $all_import_items[$key]->addDuplicateUnupdatableItemId($item_id);
            }
            foreach ($handler->_findDuplicateLockedItemIds($all_import_items[$key]) as $item_id) {
                $all_import_items[$key]->addDuplicateLockedItemId($item_id);
            }
        }
        $this->setAllDoiConflictFlag($all_import_items);
    }

    public function _findDuplicateImportItemIds($import_item, $all_import_items, $title_item_map)
    {
        $a = false;
        $a_pseudo_id_item_map = false;
        foreach ($import_item->getVar('titles') as $title) {
            if (!is_array($a)) {
                $a = array_keys($title_item_map[$title->get('title')]);
                $a_pseudo_id_item_map = &$title_item_map[$title->get('title')];
                continue;
            }
            $b = array_keys($title_item_map[$title->get('title')]);
            $a = array_intersect($a, $b);
        }

        $duplicate_possible = array();
        foreach ($a as $pseudo_id) {
            $duplicate_possible[] = &$a_pseudo_id_item_map[$pseudo_id];
        }

        $item_ids = array();
        $titles = array();
        foreach ($import_item->getVar('titles') as $title) {
            $titles[] = $title->get('title');
        }
        sort($titles);

        foreach ($duplicate_possible as $i) {
            // skip same import item(same pseudo id)
            if ($i->getPseudoId() == $import_item->getPseudoId()) {
                continue;
            }

            // skip item if number of titles differ
            if (count($i->getVar('titles')) != count($import_item->getVar('titles'))
            ) {
                continue;
            }

            // compare each title
            $titles2 = array();
            foreach ($i->getVar('titles') as $title) {
                $titles2[] = $title->get('title');
            }
            sort($titles2);

            if (!$this->_equals_array($titles, $titles2)) {
                continue;
            }

            $item_ids[] = $i->getPseudoId();
        }

        return $item_ids;
    }

    /**
     * @param $import_item reference of XooNIpsImportItem object
     *
     * @return bool integer of item ids
     */
    public function _equals_array($a1, $a2)
    {
        if (count($a1) != count($a2)) {
            return false;
        }
        for ($i = 0; $i < count($a1); ++$i) {
            if ($a1[$i] != $a2[$i]) {
                return false;
            }
        }

        return true;
    }

    public function _findDuplicateItemIds($item)
    {
        //Item A:title=A,B,C,C
        //Item B:title=A,B,B,C
        //Item C:title=A,B,C
        //Item D:title=A,B,C,C
        //
        //Item A != Item B
        //Item A != Item C
        //Item A == Item D
        //Item B != Item C
        //Item B != Item D
        //Item C != Item D

        global $xoopsDB, $xoopsUser;

        $basic = &$item->getVar('basic');
        $title_handler = &xoonips_getormhandler('xoonips', 'title');
        $titles = array();
        foreach ($item->getVar('titles') as $title) {
            if (array_key_exists($title->get('title'), $titles)) {
                ++$titles[$title->get('title')];
            } else {
                $titles[$title->get('title')] = 1;
            }
        }

        // title matching
        // select item that has same number of each titles
        //
        $item_ids = array();
        foreach ($titles as $title => $count) {
            $criteria = new CriteriaCompo(new Criteria('title', $title));
            $criteria->add(new Criteria('item_type_id', $basic->get('item_type_id')));
            $criteria->add(new Criteria('uid', $xoopsUser->getVar('uid')));
            if (count($item_ids) > 0) {
                $criteria->add(new Criteria('tb.item_id', '('.implode(',', $item_ids).')', 'IN'));
            }
            $criteria->setGroupby('tb.item_id,title having count(*)='.$count);
            $join = new XooNIpsJoinCriteria('xoonips_item_basic', 'item_id', 'item_id', 'LEFT', 'tb');
            $results = &$title_handler->getObjects($criteria, false, '*, count(*)', null, $join);
            $item_ids = array();
            foreach ($results as $result) {
                $item_ids[] = $result->get('item_id');
            }
        }
        if (0 == count($item_ids)) {
            // return only doi conflict item id
            // if no conflict items of title matching
            return $this->_findDoiConflictItemIds($item);
        }

        //
        // select item that has same number of title
        //
        $criteria = new Criteria('item_id', '('.implode(',', $item_ids).')', 'IN');
        $criteria->setGroupby('item_id having count(*)='.count($item->getVar('titles')));
        $titles = &$title_handler->getObjects($criteria);
        if (0 == count($titles)) {
            return array();
        } // no conflict item
        $item_ids = array();
        foreach ($titles as $title) {
            $item_ids[] = $title->get('item_id');
        }
        $ids = array_unique(array_merge($item_ids, $this->_findDoiConflictItemIds($item)));

        return $ids;
    }

    /**
     * @param $import_item reference of XooNIpsImportItem object
     *
     * @return array integer of item ids
     */
    public function _findDuplicateUpdatableItemIds($import_item)
    {
        global $xoopsUser;

        $item_ids = $this->_findDuplicateItemIds($import_item);
        //no duplicate items mean no conflict items
        if (0 == count($item_ids)) {
            return array();
        }

        $item_ids = $this->_get_items_in_indexes($item_ids, $import_item->getImportIndexId());

        $writable_item_ids = array();
        $item_handler = &xoonips_getormcompohandler('xoonips', 'item');
        $index_item_link_handler = &xoonips_getormhandler('xoonips', 'index_item_link');
        foreach ($item_ids as $id) {
            if (!$item_handler->getPerm($id, $xoopsUser->getVar('uid'), 'write')) {
                continue;
            }
            $writable_item_ids[] = $id;
        }

        return $writable_item_ids;
    }

    /**
     * @param $import_item reference of XooNIpsImportItem object
     *
     * @return array integer of item ids
     */
    public function _findDuplicateUnupdatableItemIds($import_item)
    {
        global $xoopsUser;

        $item_ids = $this->_findDuplicateItemIds($import_item);
        //no duplicate items mean no conflict items
        if (0 == count($item_ids)) {
            return array();
        }

        //remove updatable conlict item ids
        $item_ids = array_diff($item_ids, $this->_findDuplicateUpdatableItemIds($import_item));
        //no duplicate items mean no conflict items
        if (0 == count($item_ids)) {
            return array();
        }

        //remove locked conflict item ids
        $item_ids = array_diff($item_ids, $this->_findDuplicateLockedItemIds($import_item));

        return $item_ids;
    }

    public function _get_items_in_indexes($item_ids, $index_ids)
    {
        $index_item_link_handler = &xoonips_getormhandler('xoonips', 'index_item_link');
        $criteria = new CriteriaCompo(new Criteria('item_id', '('.implode(',', $item_ids).')', 'IN'));
        $criteria->add(new Criteria('index_id', '('.implode(',', $index_ids).')', 'IN'));
        $index_item_links = &$index_item_link_handler->getObjects($criteria);
        // no item in indexes
        if (0 == count($index_item_links)) {
            return array();
        }
        $item_ids = array();
        $item_handler = &xoonips_getormcompohandler('xoonips', 'item');
        foreach ($index_item_links as $i) {
            $item_ids[] = $i->get('item_id');
        }

        return $item_ids;
    }

    /**
     * @param $import_item reference of XooNIpsImportItem object
     *
     * @return array integer of item ids
     */
    public function _findDuplicateLockedItemIds($import_item)
    {
        $item_ids = $this->_findDuplicateItemIds($import_item);
        //no duplicate items mean no conflict items
        if (0 == count($item_ids)) {
            return array();
        }

        $item_ids = $this->_get_items_in_indexes($item_ids, $import_item->getImportIndexId());

        $result = array();
        $lock_handler = &xoonips_getormhandler('xoonips', 'item_lock');
        foreach ($item_ids as $id) {
            if ($lock_handler->isLocked($id)) {
                $result[] = $id;
            }
        }

        return $result;
    }

    /**
     * get item ids of doi conflict with import item and exits item.
     *
     * @param $import_item import_item to check a doi conflict
     *
     * @return array of integer item id of doi conflict existing item
     */
    public function _findDoiConflictItemIds($import_item)
    {
        $basic = &$import_item->getVar('basic');
        $doi = $basic->get('doi');
        if (empty($doi)) {
            return array();
        } // no conflict if doi is empty

        $handler = &xoonips_getormhandler('xoonips', 'item_basic');
        $criteria = new Criteria('doi', $doi);
        $basics = &$handler->getObjects($criteria);
        if (!$basics) {
            return array();
        }

        $result = array();
        foreach ($basics as $basic) {
            $result[] = $basic->get('item_id');
        }

        return $result;
    }

    /**
     * @param $item reference of XooNIpsImportItem object
     */
    public function import(&$item)
    {
        global $xoopsDB, $xoopsUser;

        if (!$xoopsUser) {
            return;
        }

        $indexes = &$this->_create_index_item_links_from_index_ids($item->getImportIndexId(), $item->getCertifyAutoFlag(), $xoopsUser);
        $item->setVar('indexes', $indexes);

        if ($item->getUpdateFlag()) {
            // single field(basic) _isNew=false
            $basic = &$item->getVar('basic');
            $basic->set('item_id', $item->getUpdateItemId());
            $basic->unsetNew();
            $basic->setDirty();

            // multiple field(title, keyword, ...) _isNew=true
            foreach (array('titles', 'keywords', 'related_tos', 'indexes') as $key) {
                $var = &$item->getVar($key);
                assert(is_array($var));
                foreach (array_keys($var) as $k) {
                    $var[$k]->setNew();
                    $var[$k]->setDirty();
                }
            }

            // get changelog from DB to keep old changelog
            $changelog_handler = &xoonips_getormhandler('xoonips', 'changelog');
            $criteria = new Criteria('item_id', $item->getUpdateItemId());
            $changelogs = &$changelog_handler->getObjects($criteria);
            $item->setVar('changelogs', $changelogs);

            if ($this->insert($item)) {
                $event_handler = &xoonips_getormhandler('xoonips', 'event_log');
                $event_handler->recordUpdateItemEvent($item->getUpdateItemId());
            } else {
                $item->setImportErrors(E_XOONIPS_DB_QUERY, 'DB query error in updating');
            }
        } else {
            if ($this->insert($item)) {
                $basic = &$item->getVar('basic');
                $event_handler = &xoonips_getormhandler('xoonips', 'event_log');
                $event_handler->recordInsertItemEvent($basic->get('item_id'));
            } else {
                $item->setImportErrors(E_XOONIPS_DB_QUERY, 'DB query error in updating');
            }
        }

        if (0 == count($item->getErrors())) {
            $event_handler = &xoonips_getormhandler('xoonips', 'event_log');
            foreach ($item->getVar('indexes') as $links) {
                $index_handler = &xoonips_getormhandler('xoonips', 'index');
                $index = &$index_handler->get($links->get('index_id'));
                if (!$index || OL_PUBLIC != $index->get('open_level')) {
                    continue;
                }
                $basic = &$item->getVar('basic');
                $event_handler->recordRequestCertifyItemEvent($basic->get('item_id'), $index->get('index_id'));
                if ($item->getCertifyAutoFlag()) {
                    $event_handler->recordCertifyItemEvent($basic->get('item_id'), $index->get('index_id'));
                }
            }
        }

        $basic = &$item->getVar('basic');
        insertMetadataEventAuto($basic->get('item_id'), $basic->isNew());
    }

    /**
     * return import log text of import item.
     *
     * @param $import_item reference of XooNIpsImportItem object
     *
     * @return string import log text
     */
    public function getImportLog($import_item)
    {
        $text = '';
        $basic = &$import_item->getVar('basic');
        $item_id = $basic->get('item_id');
        if (!empty($item_id)) {
            $text .= 'basic.id '.$basic->get('item_id');
        }
        foreach ($import_item->getVar('titles') as $title) {
            $text .= "\nbasic.title ".$title->get('title');
        }
        $text .= "\nbasic.contributor ".$basic->get('uid');
        $text .= "\nbasic.itemtype ".$basic->get('item_type_id');

        foreach ($import_item->getVar('keywords') as $keyword) {
            $text .= "\nbasic.keyword ".$keyword->get('keyword');
        }

        $comment = $basic->get('description', 'none');
        $text .= "\nbasic.description ".mb_ereg_replace('\n', '\n', mb_ereg_replace('\\\\', '\\\\', $comment));
        $text .= "\nbasic.doi ".$basic->get('doi');
        $text .= "\nbasic.last_update_date ".$basic->get('last_update_date');
        $text .= "\nbasic.creation_date ".$basic->get('creation_date');
        $text .= "\nbasic.publication_year ".$basic->get('publication_year');
        $text .= "\nbasic.publication_month ".$basic->get('publication_month');
        $text .= "\nbasic.publication_mday ".$basic->get('publication_mday');
        $text .= "\nbasic.lang ".$basic->get('lang');
        $text .= "\nfilename ".(is_null($import_item->getFilename()) ? '' : $import_item->getFilename());
        $item_id = $basic->get('item_id');
        if (!empty($item_id)) {
            $text .= "\n".'basic.url '.XOOPS_URL.'/modules/xoonips/detail.php?item_id='.$basic->get('item_id');
        }
        foreach ($import_item->getVar('related_tos') as $related_to) {
            $text .= "\n".'basic.related_to '.$related_to->get('item_id');
        }
        foreach ($import_item->getVar('indexes') as $index_item_link) {
            $text .= "\n".'basic.index '.$this->_index_id2index_str($index_item_link->get('index_id'));
        }

        return trim($text);
    }

    /**
     * @param
     */
    public function xmlStartElementHandler($parser, $name, $attribs)
    {
        array_push($this->_tag_stack, $name);
        $this->_cdata = '';
        switch (implode('/', $this->_tag_stack)) {
        case 'ITEM':
            if (!isset($attribs['VERSION'])) {
                $this->_import_item->setImportErrors(E_XOONIPS_ATTR_NOT_FOUND, 'VERSION is not declared'.$this->_get_parser_error_at());
            } else {
                $unicode = xoonips_getutility('unicode');
                $version = $unicode->decode_utf8($attribs['VERSION'], xoonips_get_server_charset(), 'h');
                if ('1.00' != $version) {
                    $this->_import_item->setImportErrors(E_XOONIPS_INVALID_VERSION, 'unsupported version('.$version.')'.$this->_get_parser_error_at());
                }
            }
            break;
        case 'ITEM/BASIC':
            if (!isset($attribs['ID'])) {
                $this->_import_item->setImportErrors(E_XOONIPS_ATTR_NOT_FOUND, 'ID is not declared'.$this->_get_parser_error_at());
            }

            if (empty($attribs['ID'])) {
                $this->_import_item->setImportErrors(E_XOONIPS_ATTR_INVALID_VALUE, 'ID is empty'.$this->_get_parser_error_at());
            } elseif (!ctype_digit($attribs['ID'])) {
                $this->_import_item->setImportErrors(E_XOONIPS_ATTR_INVALID_VALUE, "ID is not integer(${attribs['ID']})".$this->_get_parser_error_at());
            } else {
                $this->_import_item->setPseudoId(intval($attribs['ID']));
            }
            break;
        case 'ITEM/BASIC/RELATED_TO':
            if (empty($attribs['ITEM_ID'])) {
                $this->_import_item->setImportErrors(E_XOONIPS_ATTR_NOT_FOUND, 'ITEM_ID is not declared'.$this->_get_parser_error_at());
            } elseif (!ctype_digit($attribs['ITEM_ID'])) {
                $this->_import_item->setImportErrors(E_ATTR_INVALID_VALUE, "ITEM_ID is not integer(${attribs['ITEM_ID']})".$this->_get_parser_error_at());
            } else {
                $related_tos = &$this->_import_item->getVar('related_tos');
                $related_to_handler = &xoonips_getormhandler('xoonips', 'related_to');
                $related_to = &$related_to_handler->create();
                $related_to->set('item_id', $attribs['ITEM_ID']);
                $related_tos[] = $related_to;
            }
            break;
        case 'ITEM/BASIC/INDEX':
            if (!isset($attribs['OPEN_LEVEL'])) {
                $this->open_level = 'private'; //null;
            } else {
                switch ($attribs['OPEN_LEVEL']) {
                case 'private':
                case 'group':
                case 'public':
                    $this->open_level = $attribs['OPEN_LEVEL'];
                    break;
                default:
                    $this->open_level = null;
                    $this->_import_item->setImportErrors(E_XOONIPS_ATTR_INVALID_VALUE, 'illegal open_level('.$attribs['OPEN_LEVEL'].')'.$this->_get_parser_error_at());
                    break;
                }
            }
        }
    }

    /**
     * @param
     */
    public function xmlCdataHandler($parser, $data)
    {
        $this->_cdata .= $data;
    }

    /**
     * @param
     */
    public function xmlEndElementHandler($parser, $name)
    {
        $unicode = xoonips_getutility('unicode');
        $cdata = $unicode->decode_utf8($this->_cdata, $this->_encoding, 'h');
        switch (implode('/', $this->_tag_stack)) {
        case 'ITEM/BASIC':
            $index_ids = $this->_import_item->getImportIndexId();
            $index_handler = &xoonips_getormhandler('xoonips', 'index');
            foreach ($index_ids as $id) {
                $index = &$index_handler->get($id);
                if (OL_PRIVATE == $index->get('open_level')) {
                    break 2;
                }
            }
            $this->_import_item->setImportErrors(E_XOONIPS_NO_PRIVATE_INDEX, 'item is not registered any private indexes');
            break;
        case 'ITEM/BASIC/TITLES/TITLE':
            if ('' == trim($cdata)) {
                break;
            }
            $titles_handler = &xoonips_getormhandler('xoonips', 'title');
            $title = &$titles_handler->create();
            $title->set('title_id', count($this->_import_item->getVar('titles')));
            $title->set('title', $cdata);
            $titles = &$this->_import_item->getVar('titles');
            $titles[] = &$title;
            if (mb_strlen($cdata, $this->_encoding) > $this->_get_max_len($this->_import_item, 'titles', 'title')) {
                $this->_import_item->setImportErrors(E_XOONIPS_DATA_TOO_LONG, 'title is too long:'.$cdata.$this->_get_parser_error_at());
            }
            break;
        case 'ITEM/BASIC/KEYWORDS/KEYWORD':
            if ('' == trim($cdata)) {
                break;
            }
            $keywords_handler = &xoonips_getormhandler('xoonips', 'keyword');
            $keyword = &$keywords_handler->create();
            $keyword->set('keyword_id', count($this->_import_item->getVar('keywords')));
            $keyword->set('keyword', $cdata);
            $keywords = &$this->_import_item->getVar('keywords');
            $keywords[] = $keyword;
            if (mb_strlen($cdata, $this->_encoding) > $this->_get_max_len($this->_import_item, 'keywords', 'keyword')) {
                $this->_import_item->setImportErrors(E_XOONIPS_DATA_TOO_LONG, 'keyword is too long:'.$cdata.$this->_get_parser_error_at());
            }
            break;
        case 'ITEM/BASIC/ITEMTYPE':
            $itemtype_handler = &xoonips_getormhandler('xoonips', 'item_type');
            $itemtype = &$itemtype_handler->getObjects(new Criteria('name', addslashes($cdata)));
            if (0 == count($itemtype)) {
                $this->_import_item->setImportErrors(E_XOONIPS_INVALID_VALUE, 'unknown itemtype('.$cdata.')'.$this->_get_parser_error_at());
                break;
            }
            $basic = &$this->_import_item->getVar('basic');
            $basic->set('item_type_id', $itemtype[0]->get('item_type_id'));
            break;
        case 'ITEM/BASIC/CONTRIBUTOR':
            global $xoopsUser;
            if ($xoopsUser) {
                $basic = &$this->_import_item->getVar('basic');
                $basic->set('uid', $xoopsUser->getVar('uid'));
            }
            break;
        case 'ITEM/BASIC/DESCRIPTION':
            $basic = &$this->_import_item->getVar('basic');
            $basic->set('description', $cdata);
            if (mb_strlen($cdata, $this->_encoding) > $this->_get_max_len($this->_import_item, 'basic', 'description')
            ) {
                $this->_import_item->setImportErrors(E_XOONIPS_DATA_TOO_LONG, 'description is too long:'.$cdata.$this->_get_parser_error_at());
            }
            break;
        case 'ITEM/BASIC/DOI':
            $basic = &$this->_import_item->getVar('basic');
            $basic->set('doi', $cdata);
            if (mb_strlen($cdata, $this->_encoding) > $this->_get_max_len($this->_import_item, 'basic', 'doi')) {
                $this->_import_item->setImportErrors(E_XOONIPS_DATA_TOO_LONG, 'doi is too long:'.$cdata.$this->_get_parser_error_at());
            }
            break;
        case 'ITEM/BASIC/LAST_UPDATE_DATE':
            $basic = &$this->_import_item->getVar('basic');
            if (false === $this->_iso8601_to_utc($cdata)) {
                $this->_import_item->setImportErrors(E_XOONIPS_INVALID_VALUE, 'illegal date format('.$cdata.')'.$this->_get_parser_error_at());
            } else {
                $basic->set('last_update_date', $this->_iso8601_to_utc($cdata));
            }

            break;
        case 'ITEM/BASIC/CREATION_DATE':
            $basic = &$this->_import_item->getVar('basic');
            if (false === $this->_iso8601_to_utc($cdata)) {
                $this->_import_item->setImportErrors(E_XOONIPS_INVALID_VALUE, 'illegal date format('.$cdata.')'.$this->_get_parser_error_at());
            } else {
                $basic->set('creation_date', $this->_iso8601_to_utc($cdata));
            }
            break;
        case 'ITEM/BASIC/PUBLICATION_YEAR':
            $basic = &$this->_import_item->getVar('basic');
            $basic->set('publication_year', intval($cdata));
            break;
        case 'ITEM/BASIC/PUBLICATION_MONTH':
            $basic = &$this->_import_item->getVar('basic');
            $basic->set('publication_month', intval($cdata));
            break;
        case 'ITEM/BASIC/PUBLICATION_MDAY':
            $basic = &$this->_import_item->getVar('basic');
            $basic->set('publication_mday', intval($cdata));
            break;
        case 'ITEM/BASIC/URL':
            // url don't any effect to item
            break;
        case 'ITEM/BASIC/LANG':
            $basic = &$this->_import_item->getVar('basic');
            $basic->set('lang', $cdata);
            break;
        case 'ITEM/BASIC/RELATED_TO':
            break;
        case 'ITEM/BASIC/INDEX':
            global $xoopsUser;
            //
            // error if unescaped yen is found
            //
            $regexp = array('.*[^\\\\]\\\\[^\\/\\\\].*', '.*[^\\\\]\\\$', '^\\\\[^\\/\\\\].*');
            if (mb_ereg_match($regexp[0], $cdata) || mb_ereg_match($regexp[1], $cdata) || mb_ereg_match($regexp[2], $cdata)) {
                $this->_import_item->setImportErrors(E_XOONIPS_INVALID_VALUE, "invalid value in index. illegal use of '\\'(".$cdata.')'.$this->getErrorAt(__LINE__, __FILE__, __FUNCTION__));
                break;
            }
            //
            // get index ID for each given base_index_id
            // if CDATA of <index> is relative path
            //

            //true if relative path
            if (0 != strncmp($cdata, '/', 1)) {
                foreach ($this->_import_index_ids as $base_index_id) {
                    $index_id = $this->_get_index_id($base_index_id, $cdata);
                    if (!$index_id) {
                        $this->_import_item->setImportErrors(E_XOONIPS_INDEX_NOT_FOUND, 'index is not found('.$this->_index_id2index_str($base_index_id).'/'.$cdata.')');
                        continue;
                    }
                    $this->_import_item->addImportIndexId($index_id);

                    $index_handler = &xoonips_getormhandler('xoonips', 'index');
                    if (!$index_handler->getPerm($index_id, $xoopsUser->getVar('uid'), 'read')) {
                        $this->_import_item->setImportErrors(E_XOONIPS_NOT_PERMITTED_ACCESS, 'not permitted access to index('.$this->index_id2index_str($index_id).'/'.$cdata.') by user(uid='.$this->_import_item->getVar('uid').')');
                    }
                }
            } else {
                // absolute index path
                $id = $this->index_str2index_id($cdata, $xoopsUser, '/', $this->open_level);
                if (!$id) {
                    $this->_import_item->setImportErrors(E_XOONIPS_INDEX_NOT_FOUND, 'index '.$cdata.' is not found.'.$this->_import_item->getErrorAt(__LINE__, __FILE__, __FUNCTION__));
                    break;
                }

                // user have access to index($id) ?
                $index_handler = &xoonips_getormhandler('xoonips', 'index');
                if (!$index_handler->getPerm($id, $xoopsUser->getVar('uid'), 'register_item')) {
                    $this->_import_item->setImportErrors(E_XOONIPS_NOT_PERMITTED_ACCESS, 'not permitted access to index('.$cdata.') by user(uid='.$this->_import_item->getVar('uid').')'.$this->_import_item->getErrorAt(__LINE__, __FILE__, __FUNCTION__));
                    break;
                }

                if ($index_handler->get($id)) {
                    // add import index id to import item
                    $this->_import_item->addImportIndexId($id);
                } else {
                    $this->_import_item->setImportErrors(E_XOONIPS_INDEX_NOT_FOUND, 'index '.$cdata.' is not found.'.$this->_import_item->getErrorAt(__LINE__, __FILE__, __FUNCTION__));
                }
            }
            break;
        }

        array_pop($this->_tag_stack);
    }

    /**
     * @param
     */
    public function cleanup()
    {
    }

    /**
     * change expression of ISO8601 to UTC. return false when we can't change.
     * Usage: _iso8601_to_utc( "2005-08-01T12:00:00Z" );
     * Usage: _iso8601_to_utc( "2005-08-01" );.
     *
     * @param string $str
     */
    public function _iso8601_to_utc($str)
    {
        // $match[?]
        // $match[0]  : input string ($str)
        // $match[1]  : year
        // $match[2]  :
        // $match[3]  : month
        // $match[4]  :
        // $match[5]  : mday
        // $match[6]  :
        // $match[7]  : hour
        // $match[8]  : minute
        // $match[9]  :
        // $match[10] : secound
        // $match[11] : 'Z' or time difference (Rexp Z|[-+][0-9]{2}:[0-9]{2})
        // $match[12] : direction for time difference (+|-)
        // $match[13] : hour of time difference
        // $match[14] : minute of time difference

        if (0 == preg_match('/^([0-9]{4})(-([0-9]{2})(-([0-9]{2})(T([0-9]{2}):([0-9]{2})(:([0-9]{2}))?(Z|([-+])([0-9]{2}):([0-9]{2}))?)?)?)?$/', $str, $match)) {
            return false;
        }

        if (!isset($match[3])) {
            $match[3] = '01';
        }
        if (!isset($match[5])) {
            $match[5] = '01';
        }
        if (!isset($match[7])) {
            $match[7] = '00';
        }
        if (!isset($match[8])) {
            $match[8] = '00';
        }
        if (!isset($match[10]) || '' == $match[10]) {
            $match[10] = '00';
        }
        $tm = gmmktime($match[7], $match[8], $match[10], $match[3], $match[5], $match[1]);
        if (false === $tm || -1 == $tm && version_compare(phpversion(), '5.1.0', '<')) {
            return false;
        } // gmmktime failed.

        // hh:mm:ss must be in 00:00:00 - 24:00:00
        if ($match[10] >= 60) {
            return false;
        }
        if ($match[8] >= 60) {
            return false;
        }
        if ($match[7] > 24 || 24 == $match[7] && (0 != $match[8] || 0 != $match[10])) {
            return false;
        }

        // mm and dd must not overflow
        if ($match[1].$match[3].$match[5] != gmdate('Ymd', gmmktime(0, 0, 0, $match[3], $match[5], $match[1]))) {
            return false;
        }

        //correct a time difference to GMT
        if (isset($match[11]) && isset($match[12]) && isset($match[13]) && isset($match[14])) {
            if ('Z' != $match[11] && '-' == $match[12]) {
                $tm = $tm + ($match[13] * 3600 + $match[14] * 60);
            } elseif (isset($match[12]) && 'Z' != $match[11] && '+' == $match[12]) {
                $tm = $tm - ($match[13] * 3600 + $match[14] * 60);
            }
        }

        return $tm;
    }

    public function _get_parser_error_at()
    {
        return ' at line '.xml_get_current_line_number($this->_parser).', column '.xml_get_current_column_number($this->_parser);
    }

    public function &_get_import_item_handler_by_item_type_id($item_type_id)
    {
        static $itemTypeIdToImportItemHandler = array();
        if (!array_key_exists($item_type_id, $itemTypeIdToImportItemHandler)) {
            $handler = &xoonips_getormhandler('xoonips', 'item_type');
            $itemtype = &$handler->get($item_type_id);
            if (!$itemtype) {
                return false;
            }
            $itemTypeIdToImportItemHandler[$item_type_id] = &xoonips_gethandler($itemtype->get('name'), 'import_item');
        }

        return $itemTypeIdToImportItemHandler[$item_type_id];
    }

    /**
     * return index path to the index specified by $index_id
     * like a '/userA/books', '/Public/Paper/Science'.
     *
     * @param usernameToPrivate use '/Private' instead of '/username'
     */
    public function _index_id2index_str($index_id, $usernameToPrivate = false)
    {
        global $xoopsDB;
        $path = array();
        do {
            $sql = 'SELECT t.title AS title, parent_index_id, open_level FROM '
                .$xoopsDB->prefix('xoonips_index').', '
                .$xoopsDB->prefix('xoonips_item_basic').' AS b, '
                .$xoopsDB->prefix('xoonips_item_title').' AS t '
                .'WHERE index_id=b.item_id AND b.item_id=t.item_id '
                .' AND t.title_id=0 AND index_id='.(int) $index_id;
            if (!$result = $xoopsDB->query($sql)) {
                $this->_import_item->setImportErrors(E_XOONIPS_DB_QUERY, $xoopsDB->error());

                return null;
            }
            if (1 == $xoopsDB->getRowsNum($result)) {
                $row = $xoopsDB->fetchArray($result);
                if ($usernameToPrivate && IID_ROOT == $row['parent_index_id'] && OL_PRIVATE == $row['open_level']
                ) {
                    $path[] = XNP_PRIVATE_INDEX_TITLE;
                } else {
                    $path[] = mb_ereg_replace('/', '\\/', mb_ereg_replace('\\\\', '\\\\', $row['title']));
                }
                $index_id = $row['parent_index_id'];
            }
        } while (0 != $row['parent_index_id']);
        array_pop($path); //remove '/Root'

        return '/'.implode('/', array_reverse($path));
    }

    /**
     * get number of import items that conflict
     *  to import file and already exists items.
     *
     * @param $ipmort_items array of XooNIpsImportItem
     *
     * @return int number of import items
     */
    public function numberOfConflictItem($import_items)
    {
        $count = 0;
        foreach ($import_items as $i) {
            if (count($i->getDuplicatePseudoId()) > 0 || count($i->getDuplicateUpdatableItemId()) > 0 || count($i->getDuplicateUnupdatableItemId()) > 0 || count($i->getDuplicateLockedItemId()) > 0) {
                ++$count;
            }
        }

        return $count;
    }

    public function getItemAbstractTextFromItem($import_item)
    {
        $basic = &$import_item->getVar('basic');
        $titles = &$import_item->getVar('titles');
        $indexes = &$import_item->getVar('indexes');
        $handler = &xoonips_getormhandler('xoonips', 'item_type');
        $itemtype = &$handler->get($basic->get('item_type_id'));

        $ret = array();
        foreach ($titles as $title) {
            $ret[] = $title->get('title');
        }
        if ($itemtype) {
            $ret[] = $itemtype->get('display_name');
        }

        return implode("\n", $ret);
    }

    public function insert(&$item)
    {
        $handler = &xoonips_getormcompohandler('xoonips', 'item');

        return $handler->insert($item);
    }

    public function setNew(&$item)
    {
        $handler = &xoonips_getormcompohandler('xoonips', 'item');

        return $handler->setNew($item);
    }

    public function unsetNew(&$item)
    {
        $handler = &xoonips_getormcompohandler('xoonips', 'item');

        return $handler->unsetNew($item);
    }

    public function setDirty(&$item)
    {
        $handler = &xoonips_getormcompohandler('xoonips', 'item');

        return $handler->setDirty($item);
    }

    public function unsetDirty(&$item)
    {
        $handler = &xoonips_getormcompohandler('xoonips', 'item');

        return $handler->unsetDirty($item);
    }

    public function onReadFileFinished(&$item, &$import_items)
    {
    }

    public function onImportFinished(&$item, &$import_items)
    {
        $pseudo_id2id = array();
        foreach ($import_items as $i) {
            $basic = &$i->getVar('basic');
            if (array_key_exists($i->getPseudoId(), $pseudo_id2id)) {
                $pseudo_id2id[$i->getPseudoId()][] = $basic->get('item_id');
            } else {
                $pseudo_id2id[$i->getPseudoId()] = array($basic->get('item_id'));
            }
        }

        // update xoonips_related_to.item_id from pseudo item id to item id
        $handler = &xoonips_getormhandler('xoonips', 'related_to');
        $related_tos = &$item->getVar('related_tos');

        $new_related_tos = array();
        foreach (array_keys($related_tos) as $key) {
            if (!array_key_exists($related_tos[$key]->get('item_id'), $pseudo_id2id)) {
                continue;
            }
            foreach ($pseudo_id2id[$related_tos[$key]->get('item_id')] as $item_id) {
                $l = &$handler->create();
                $l->set('parent_id', $related_tos[$key]->get('parent_id'));
                $l->set('item_id', $item_id);
                $handler->insert($l);
                $new_related_tos[] = &$l;
            }
            $handler->delete($related_tos[$key]);
        }
        $item->setVar('related_tos', $new_related_tos);

        // lock if imported items are registered to public/group
        // index and certify required
        $item_basic_handler = &xoonips_getormhandler('xoonips', 'item_basic');
        $index_item_links = &$item->getVar('indexes');
        $index_handler = &xoonips_getormhandler('xoonips', 'index');
        foreach ($index_item_links as $index_item_link) {
            if (CERTIFY_REQUIRED == $index_item_link->get('certify_state')) {
                $index = $index_handler->get($index_item_link->get('index_id'));
                if (OL_PUBLIC == $index->get('open_level') || OL_GROUP_ONLY == $index->get('open_level')) {
                    $basic = &$item->getVar('basic');
                    $item_basic_handler->lockItemAndIndexes($basic->get('item_id'), $index_item_link->get('index_id'));
                }
            }
        }
    }

    /**
     * index path strnig to array of each indexes and strip escape char 'yen'
     * (ex:/ABC/XYZ/012 -> array( 'ABC', 'XYZ', '012' ) )
     * (ex:/ABC/XYZ/012/ -> array( 'ABC', 'XYZ', '012', '' ) ).
     *
     * @param $str index path like '/Public/Parent/Child'
     *
     * @return string[] of indexes;
     */
    public function _decomposite_index_path($str)
    {
        return array_map(function ($x) { return str_replace('&#47;', '/', $x); }, explode('/', str_replace('\\/', '&#47;', substr($str, 1))));
    }

    /**
     * get index id conrresponds to indexstr
     *  (that is placed relatively from $base_index_id).
     *
     * @param $base_index_id integer index_id of base index
     * @param string $indexstr string index path string like 'foo/bar/xxx'
     *
     * @return int index id or false
     */
    public function _get_index_id($base_index_id, $indexstr)
    {
        global $xoopsDB;

        if ('' == trim($indexstr)) {
            return $base_index_id;
        }

        $names = $this->_decomposite_index_path($indexstr);
        $index_id = $base_index_id;
        foreach ($names as $name) {
            if ('' == $name) {
                // root index like '/Public'
                break;
            }
            $index_handler = &xoonips_getormhandler('xoonips', 'index');
            $base_index = &$index_handler->get($index_id);
            if (!is_object($base_index)) {
                return false;
            }

            $criteria = new CriteriaCompo(new Criteria('parent_index_id', $index_id));
            $criteria->add(new Criteria('tt.title', $name));
            $join = new XooNIpsJoinCriteria('xoonips_item_title', 'index_id', 'item_id', 'LEFT', 'tt');
            $indexes = &$index_handler->getObjects($criteria, false, '', false, $join);
            if (0 == count($indexes)) {
                return false;
            }

            $index_id = $indexes[0];
        }

        return $index_id;
    }

    /**
     * get an id of index specified by given arguments.
     * if $str is relative path, $base_index_path must be given.
     * regards as private if $open_level is null.
     *
     * @param string $str index path like
     *                    '/Public/Parent/Child'(absolute)
     * @param $user XoopsUser of import user(need to convert
     * /Private to user's private index)
     * @param $base_index_path base index path like
     * '/Public/XXX/YYY'(absolute only). default is '/'.
     * @param $open_level open_level of index of
     * base_index_path('private'|'group'|'public'|null). null if not specified.
     *
     * @return index id or false if failed
     */
    public function index_str2index_id($str, $user, $base_index_path = '/', $open_level = 'private')
    {
        global $xoopsDB;

        if ('xoopsuser' != strtolower(get_class($user))) {
            return false;
        }
        if (!is_object($user)) {
            return false;
        }

        if (!empty($str) && '/' == $str[0]) {
            $index_path = $str;
        } else {
            //
            // don't append any string if $str is empty('').
            //  $index_path equals to $base_index_path
            // to specify index that items is imported to by relatively.
            //
            $index_path = $base_index_path.(empty($str) ? '' : '/'.$str);
        }

        $indexes = $this->_decomposite_index_path($index_path);

        //
        // - find last(depth is max) indexes
        // - for each indeses, we traverse ancestor index to root index
        // - returns index path that reach to root index first.
        //

        // replace 'Private' to own uname
        // set 'private' to $open_level
        if ('Private' == $indexes[0]) {
            $indexes[0] = $user->getVar('uname');
            $open_level = 'private';
        }

        // return index id
        $result = $this->index_array2index_id(IID_ROOT, $indexes, $open_level);

        return $result;
    }

    /**
     * get index_id of given an array of index hierarchy
     * (ex: index_array2index_id <id of root index>, array( 'foo', 'bar' ),
     *  'group' ); you can get group index id of /foo/bar ).
     *
     * @param index_id integer index id
     * @param indexes array of index hierarchy
     * @param open_level open_level of index of target index
     *  (public|group|private)
     *
     * @return index id or false if index is not found
     */
    public function index_array2index_id($index_id, $indexes, $open_level = 'private')
    {
        global $xoopsDB;
        switch ($open_level) {
        case 'public':
            $open_level_val = OL_PUBLIC;
            break;
        case 'group':
            $open_level_val = OL_GROUP_ONLY;
            break;
        case 'private':
        default:
            $open_level_val = OL_PRIVATE;
            break;
        }
        $title = array_shift($indexes);
        $sql = 'SELECT i.item_id FROM '
            .$xoopsDB->prefix('xoonips_item_basic').' AS i, '
            .$xoopsDB->prefix('xoonips_item_title').' AS t, '
            .$xoopsDB->prefix('xoonips_index').' AS x '
            .'WHERE title_id=0 AND x.parent_index_id='.(int) $index_id
            .' AND x.index_id=t.item_id AND t.item_id=i.item_id'
            .' AND t.title='.$xoopsDB->quoteString($title)
            .' AND open_level='.(int) $open_level_val;
        $result = $xoopsDB->query($sql);
        if (!$result) {
            return false;
        } elseif (0 == $xoopsDB->getRowsNum($result)) {
            return false;
        }
        list($next_index_id) = $xoopsDB->fetchRow($result);
        if (count($indexes) >= 1) {
            return $this->index_array2index_id($next_index_id, $indexes, $open_level);
        } else {
            return $next_index_id;
        }
    }

    public function &_create_index_item_links_from_index_ids($index_ids, $certify_auto_flag, $user)
    {
        $indexes = array();

        foreach ($index_ids as $index_id) {
            $index_handler = &xoonips_getormhandler('xoonips', 'index');
            $index = &$index_handler->get($index_id);
            $index_item_link_handler = &xoonips_getormhandler('xoonips', 'index_item_link');
            $index_item_link = &$index_item_link_handler->create();
            $index_item_link->set('index_id', $index_id);
            if ((OL_PUBLIC == $index->get('open_level') || OL_GROUP_ONLY == $index->get('open_level'))) {
                if ($user->isAdmin() || isset($_SESSION['xoonips_old_uid'])) {
                    $index_item_link->set('certify_state', $certify_auto_flag ? CERTIFIED : CERTIFY_REQUIRED);
                } else {
                    $xconfig_handler = &xoonips_getormhandler('xoonips', 'config');
                    $certify_item = $xconfig_handler->getValue(XNP_CONFIG_CERTIFY_ITEM_KEY);
                    $index_item_link->set('certify_state', XNP_CONFIG_CERTIFY_ITEM_AUTO == $certify_item ? CERTIFIED : CERTIFY_REQUIRED);
                }
            }
            $indexes[] = &$index_item_link;
        }

        return $indexes;
    }

    /**
     * create text search index.
     * update xoonips_search_text.
     *
     * @param $file XooNIpsFile
     */
    public function _create_text_search_index($file)
    {
        //create full text search index
        $admin_file_handler = &xoonips_gethandler('xoonips', 'admin_file');
        $admin_file_handler->updateFileSearchText($file->get('file_id'), true);
    }

    /**
     * delete all files of specified item.
     *
     * @param $item reference of XooNIpsImportItem
     */
    public function _set_file_delete_flag(&$item)
    {
        // set true to delete flag of old url_banner_file
        $basic = &$item->getVar('basic');
        $handler = &xoonips_getormhandler('xoonips', 'file');
        $criteria = new CriteriaCompo(new Criteria('item_id', $basic->get('item_id')));
        $old_url = &$handler->getObjects($criteria);
        if ($old_url) {
            foreach ($old_url as $file) {
                $file->set('is_deleted', 1);
                $handler->insert($file);
            }
        }
    }

    /**
     * Update item_id and sess_id of xoonips_file.
     * item_id is given by $item.
     *
     * @param $item reference of XooNIpsImportItem
     * @param $file reference of XooNIpsFile
     */
    public function _fix_item_id_of_file(&$item, &$file)
    {
        if ($file->get('file_id') > 0) {
            // fix file record
            $handler = &xoonips_getormhandler('xoonips', 'file');
            $newfile = &$handler->create();
            $newfile->unsetNew();
            $newfile->setDirty();

            $vars = $file->getVarArray('n');
            $vars['sess_id'] = null;
            $newfile->setVars($vars, true);

            $basic = &$item->getVar('basic');
            $newfile->set('item_id', $basic->get('item_id'));
            $handler->insert($newfile);
        }
    }

    /**
     * Get max length of the field.
     * It returns max length in bytes defined in XooNIpsObject.
     *
     * @param XooNIpsImportItem $item
     * @param string            $ormname    string orm name
     * @param string            $field_name string field name
     *
     * @return int max length of the field in bytes
     */
    public function _get_max_len($item, $ormname, $field_name)
    {
        $orm = &$item->getVar($ormname);
        if (!$orm) {
            return 0;
        }
        if (is_array($orm)) {
            if (count($orm) > 0) {
                $vars = $orm[0]->getVars();
            } else {
                return 0;
            }
        } else {
            $vars = $orm->getVars();
        }
        if (!array_key_exists($field_name, $vars)) {
            return 0;
        }

        return $vars[$field_name]['maxlength'];
    }

    public function setAllDoiConflictFlag(&$import_items)
    {
        $handler = &xoonips_gethandler('xoonips', 'import_item');
        foreach (array_keys($import_items) as $key) {
            $doi_conflict_id = $handler->_findDoiConflictItemIds($import_items[$key]);
            $import_items[$key]->setDoiConflictFlag(count($doi_conflict_id) > 0);
        }
    }

    /**
     * create map(associate array title and XooNIpsImportItem)
     *  from XooNIpsImportItem array.
     *
     * @param $import_item reference of XooNIpsImportItem object
     * @param $all_import_items array of reference
     *  of all XooNIpsImportItem objects
     *
     * @return array associate array
     *               (title string => array( pseudo_id => reference of XooNIpsItem, ... ))
     */
    public function &_create_title_item_map(&$all_import_items)
    {
        $title_item_map = array();
        foreach ($all_import_items as $key => $i) {
            foreach ($i->getVar('titles') as $title) {
                if (!array_key_exists($title->get('title'), $title_item_map)) {
                    $title_item_map[$title->get('title')] = array();
                }
                $title_item_map[$title->get('title')][$i->getPseudoId()] = &$all_import_items[$key];
            }
        }

        return $title_item_map;
    }
}
