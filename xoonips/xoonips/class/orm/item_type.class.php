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

/**
 * @brief handler class of item type
 *
 * make subclass for each itemtype
 *
 * @li getVar('item_type_id') : item type id
 * @li getVar('name') : item type name
 * @li getVar('display_name') : display name of item type
 * @li getVar('viewphp') : file path of view.php
 *
 * detail of fields
 * @li name : field name string
 * @li type : data type string(int|string|date|fileid)
 * @li required : true=required, false=optional
 * @li multiple : true=multiple, false=not multiple
 */
class XooNIpsOrmItemType extends XooNIpsTableObject
{
    public $fields;
    public $description = null;
    public $mainFileName = null;
    public $previewFileName = null;

    public $iteminfo = null;

    public function __construct($module = null)
    {
        parent::__construct();
        if (isset($module) && is_null($this->iteminfo)) {
            include XOOPS_ROOT_PATH.'/modules/'.$module.'/iteminfo.php';
            $this->iteminfo = &$iteminfo;
            $this->description = $iteminfo['description'];
            $this->mainFileName = isset($iteminfo['files']['main']) ? $iteminfo['files']['main'] : null;
            $this->previewFileName = isset($iteminfo['files']['preview']) ? $iteminfo['files']['preview'] : null;
        }

        $this->fields = array();
        if (!is_null($this->iteminfo['ormfield']['detail'])) {
            $this->fields = array_merge($this->fields, $this->iteminfo['ormfield']['detail']);
        }

        $this->initVar('item_type_id', XOBJ_DTYPE_INT, null, true, 10);
        $this->initVar('name', XOBJ_DTYPE_TXTBOX, null, true, 30);
        $this->initVar('mid', XOBJ_DTYPE_INT, null, true);
        $this->initVar('display_name', XOBJ_DTYPE_TXTBOX, null, true, 30);
        $this->initVar('viewphp', XOBJ_DTYPE_TXTBOX, null, true, 255);
    }

    /**
     * get field information by name.
     *
     * @param $ormName field name of orm
     * @param $fieldName field name
     *
     * @return field information if found. false if not found.
     */
    public function getFieldByName($ormName, $fieldName)
    {
        // return false if $ormName is not found in iteminfo
        if (!in_array($ormName, array_keys($this->iteminfo['ormfield']))) {
            return false;
        }
        // find fieldName in iteminfo['ormfield']
        foreach ($this->iteminfo['ormfield'][$ormName] as $field) {
            if ($field['name'] == $fieldName) {
                return $field;
            }
        }

        return false;
    }

    /**
     * @brief get description
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @brief get field name of mainfile
     */
    public function getMainFileName()
    {
        return $this->mainFileName;
    }

    /**
     * @brief get field name of previewfile
     */
    public function getPreviewFileName()
    {
        return $this->previewFileName;
    }

    /**
     * get iteminfo array.
     */
    public function getIteminfo()
    {
        return $this->iteminfo;
    }

    /**
     * return names of all file type used in the item type.
     *
     * @return array array of file type name string
     */
    public function getFileTypeNames()
    {
        $ar = array();
        if (isset($this->iteminfo['files']['main'])) {
            $ar[] = $this->iteminfo['files']['main'];
        }
        if (isset($this->iteminfo['files']['preview'])) {
            $ar[] = $this->iteminfo['files']['preview'];
        }

        return array_merge($ar, isset($this->iteminfo['files']['others']) ? $this->iteminfo['files']['others'] : array());
    }

    /**
     * return field has multiple value or not.
     *
     * @param string field name of orm
     *
     * @return bool true if the field can have multiple value
     */
    public function getMultiple($fieldname)
    {
        foreach ($this->iteminfo['orm'] as $i) {
            if ($i['field'] == $fieldname) {
                return isset($i['multiple']) ? $i['multiple'] : false;
            }
        }

        return false;
    }

    /**
     * return that field is required or not.
     *
     * @param string field name of orm
     *
     * @return bool true if the field is required
     */
    public function getRequired($fieldname)
    {
        foreach ($this->iteminfo['orm'] as $i) {
            if ($i['field'] == $fieldname) {
                return isset($i['required']) ? $i['required'] : false;
            }
        }

        return false;
    }
}

/**
 * @brief data object of item type
 *
 * make subclass for each itemtype
 */
class XooNIpsOrmItemTypeHandler extends XooNIpsTableObjectHandler
{
    public function __construct(&$db)
    {
        parent::__construct($db);
        $this->__initHandler('XooNIpsOrmItemType', 'xoonips_item_type', 'item_type_id', false);
    }

    /**
     * get item type objects sort by weight.
     *
     * @return array objects
     */
    public function &getObjectsSortByWeight()
    {
        // TODO: xoonips_item_type table should have itemtype sort order.
        $table = $this->db->prefix($this->getTableName());
        $criteria = new Criteria('name', addslashes('xoonips_index'), '!=', $table);
        $criteria->setSort('weight');
        $criteria->setOrder('ASC');
        $join = new XooNIpsJoinCriteria('modules', 'mid', 'mid', 'INNER');
        $fields = sprintf('item_type_id, %s.name, %s.mid, display_name, viewphp, weight', $table, $table);
        $objs = &$this->getObjects($criteria, false, $fields, false, $join);
        if (count($objs) != 0) {
            usort($objs, array($this, '_order_weight_cmp'));
        }

        return $objs;
    }

    /**
     * sort function for item type order.
     *
     * @param object &$a
     * @param object &$b
     *
     * @return order
     */
    public function _order_weight_cmp(&$a, &$b)
    {
        if ($a->getExtraVar('weight') == $b->getExtraVar('weight')) {
            // mid must be uniq
            return($a->get('mid') < $b->get('mid')) ? -1 : 1;
        }

        return($a->getExtraVar('weight') < $b->getExtraVar('weight')) ? -1 : 1;
    }
}
