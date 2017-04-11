<?php

// $Revision: 1.1.4.1.2.9 $
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

//- - - - - - - - - - - - - - - - - - - - - - - - - - - -
//
// Handlers
//
//- - - - - - - - - - - - - - - - - - - - - - - - - - - -

/**
 * @brief handler class of an object that composite of data objects.
 *
 * @li get/insert/delete primary table and dependent table
 * @li primary key and foreign key must be a single integer column
 * @li handler name corresponds to a field name of a data object.
 * @li set handler of primary table and primary key using __init_handler
 * @li set handler of dependent table,freign key and multiple attribute(optional) using addHandler
 */
class XooNIpsRelatedObjectHandler
{
    /**
     * associative array.
     */
    public $handlers = array();

    /**
     * handler's name of primary table.
     */
    public $primary_handler_name = null;

    public function XooNIpsRelatedObjectHandler()
    {
    }

    /**
     * @protected
     *
     * @brief initialize handler of primary table
     *
     * @param string $primary_handler_name handler name of primary table data object(corresponding to variable name of XooNIpsTableObject)
     * @param string $primary_handler_name handler name of data object of primary table
     * @param string $primary_handler      reference of handler
     * @param string $primary_key_name     primary key
     */
    public function __init_handler($primary_handler_name, &$primary_handler, $primary_key_name)
    {
        $this->addHandler($primary_handler_name, $primary_handler, $primary_key_name);
        $this->primary_handler_name = $primary_handler_name;
    }

    /**
     * get data object.
     */
    public function &get($id)
    {
        $obj = &$this->create();
        $falseVar = false;
        foreach (array_keys($this->handlers) as $key) {
            $foreign_key = $this->handlers[$key]['foreign_key'];
            if ($this->handlers[$key]['criteria'] && (is_subclass_of($criteria, 'CriteriaElement') || strtolower(get_class($criteria)) == 'CriteriaElement')) {
                $criteria = new CriteriaCompo();
                $criteria->add(new Criteria($foreign_key, $id));
                $criteria->add($this->handlers[$key]['criteria']);
                $criteria->setSort($this->handlers[$key]['criteria']->getSort());
                $criteria->setOrder($this->handlers[$key]['criteria']->getOrder());
                $criteria->setLimit($this->handlers[$key]['criteria']->getLimit());
                $criteria->setStart($this->handlers[$key]['criteria']->getStart());
            } else {
                $criteria = new Criteria($foreign_key, $id);
            }

            $objs = &$this->handlers[$key]['handler']->getObjects($criteria);
            if (false == $objs && $key == $this->primary_handler_name) {
                // return false if primary object is not found
                return $falseVar;
            }
            foreach ($objs as $k => $v) {
                $objs[$k]->unsetNew();
            }
            if ($this->handlers[$key]['multiple'] /*is_array($obj->getVar($key))*/) {
                if (false !== $objs) {
                    $obj->setVar($key, $objs);
                }
            } else {
                if (false !== $objs && count($objs) == 1) {
                    $obj->setVar($key, $objs[0]);
                }
            }
        }

        return $obj;
    }

    /**
     * @brief insert specified object
     *
     * @param XooNIpsRelatedObject object to insert
     *
     * @return bool false if failure
     */
    public function insert(&$obj)
    {
        if (isset($this->primary_handler_name)) {
            // insert data object using primary handler
            $primary_obj = &$obj->getVar($this->primary_handler_name);
            if (!$this->handlers[$this->primary_handler_name]['handler']->insert($primary_obj)) {
                trigger_error('failure in insert primary table');

                return false;
            }
            $primary_id = $primary_obj->get($this->handlers[$this->primary_handler_name]['foreign_key']);
        } else {
            trigger_error('no primary handler');

            return false;
        }
        foreach (array_keys($obj->getVars()) as $key) {
            if ($key == $this->primary_handler_name) {
                continue;
            } //no need to insert(already inserted)
            //
            if (!isset($this->handlers[$key])) {
                trigger_error("unknown handlers index $key");

                return false;
            }
            $foreign_key = $this->handlers[$key]['foreign_key'];
            if ($this->handlers[$key]['multiple'] && is_array($obj->getVar($key))) {
                $objs = &$obj->getVar($key); //orm objects to insert
                //
                // delete orm that is not in $objs and is in database.
                $is_in_ids = array();
                foreach ($objs as $o) {
                    if (!$o->isNew()) {
                        $is_in_ids[] = $o->get($this->handlers[$key]['handler']->getKeyName());
                    }
                }

                if (count($is_in_ids) > 0) {
                    $cri = new CriteriaCompo(new Criteria($foreign_key, $primary_id));
                    $cri->add(new Criteria($this->handlers[$key]['handler']->getKeyName(), '('.implode(',', $is_in_ids).')', 'NOT IN'));
                } else { //all of orm is deleted
                    $cri = new CriteriaCompo(new Criteria($foreign_key, $primary_id));
                }
                if (isset($this->handlers[$key]['criteria'])) {
                    // add handler's criteria
                    $cri->add($this->handlers[$key]['criteria']);
                }

                $del_objs = &$this->handlers[$key]['handler']->getObjects($cri);
                foreach ($del_objs as $o) {
                    $this->handlers[$key]['handler']->delete($o);
                }
                //
                // insert each orms in array
                $insert_ids = array(); //array of ids that is inserted below
                foreach ($objs as $k => $v) {
                    if (!$objs[$k]->isDirty()) {
                        continue;
                    } // no need to insert
                    $objs[$k]->set($foreign_key, $primary_id);
                    if (!$this->handlers[$key]['handler']->insert($objs[$k])) {
                        trigger_error("failure in insert: $key");

                        return false;
                    }
                    // add value of primary_key to insert_ids
                    $insert_ids[] = intval($objs[$k]->get($this->handlers[$key]['handler']->getKeyName()));
                }
            } else {
                // insert orm
                $var = &$obj->getVar($key);
                if (empty($var)) {
                    continue;
                } // no orm object
                if (!$var->isDirty()) {
                    continue;
                } // no need to insert
                $var->set($foreign_key, $primary_id);
                if (!$this->handlers[$key]['handler']->insert($var)) {
                    trigger_error("failure in insert: $key");

                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @brief delete object of specified key
     *
     * @param mixed primary key of object to delete
     *
     * @return bool false if failure
     */
    public function deleteByKey($key)
    {
        return $this->delete($this->get($key));
    }

    /**
     * @brief delete specified object
     *
     * @param XooNIpsRelatedObject object to delete
     *
     * @return bool false if failure
     */
    public function delete(&$obj)
    {
        foreach (array_keys($obj->getVars()) as $key) {
            if (!isset($this->handlers[$key])) {
                trigger_error("unknown handlers index $key");

                return false;
            }
            if ($this->handlers[$key]['multiple'] && is_array($obj->getVar($key))) {
                $objs = &$obj->getVar($key);
                for ($i = 0; $i < count($objs); ++$i) {
                    if (!$this->handlers[$key]['handler']->delete($objs[$i])) {
                        trigger_error("failure in delete $key");

                        return false;
                    }
                }
            } elseif ($obj->getVar($key)) {
                if (!$this->handlers[$key]['handler']->delete($obj->getVar($key))) {
                    trigger_error("failure in delete $key");

                    return false;
                }
            }
        }

        return true;
    }

    /**
     * gets objects.
     *
     * @param object $criteria
     * @param bool   $id_as_key
     * @param string $fieldlist fieldlist for distinct select
     * @param bool   $distinct
     *
     * @return array objects
     */
    public function &getObjects($criteria = null, $id_as_key = false, $fieldlist = '', $distinct = false, $joindef = null)
    {
        $ret_objs = array();
        static $falseVar = false;
        if (isset($this->primary_handler_name)) {
            $primary_objs = &$this->handlers[$this->primary_handler_name]['handler']->getObjects($criteria, false, $this->handlers[$this->primary_handler_name]['foreign_key'], $distinct, $joindef);
            if (false === $primary_objs) {
                return $falseVar;
            }

            foreach ($primary_objs as $k => $v) {
                $primary_objs[$k]->unsetNew();
            }
            // insert data object using primary handler
            foreach ($primary_objs as $o) {
                $id = $o->get($this->handlers[$this->primary_handler_name]['foreign_key']);
                if (!$id) {
                    continue;
                }
                $obj = &$this->create();
                foreach ($obj->getVars() as $key => $value) {
                    if (!isset($this->handlers[$key])) {
                        trigger_error("unknown handlers index $key");

                        return $falseVar;
                    }
                    $objs = &$this->handlers[$key]['handler']->getObjects(new Criteria($this->handlers[$key]['foreign_key'], $id));
                    if ($value['required'] && empty($objs)) {
                        continue 2;
                    }
                    foreach ($objs as $k => $v) {
                        $objs[$k]->unsetNew();
                    }
                    if ($this->handlers[$key]['multiple']) {
                        $obj->setVar($key, $objs);
                    } else {
                        // skip this object if incomplete(related row is not found)
                        if (count($objs) != 1) {
                            continue;
                        }
                        $obj->setVar($key, $objs[0]);
                    }
                }
                if ($id_as_key) {
                    $ret_objs[$id] = $obj;
                } else {
                    $ret_objs[] = $obj;
                }
            }

            return $ret_objs;
        }

        return $falseVar;
    }

    /**
     * add handler.
     *
     * @param string handler name(must be same of item field name)
     * @param XooNIpsRelatedObjectHandler handler
     * @param string key name to be joined to xoonips_item_basic.item_id
     * @param bool     $multiple true if this field has more than one data objects
     * @param Criteria $criteria
     */
    public function addHandler($key, &$handler, $foreign_key, $multiple = false, $criteria = null)
    {
        $this->handlers[$key] = array(
            'name' => $key,
            'handler' => &$handler,
            'foreign_key' => $foreign_key,
            'multiple' => $multiple,
            'criteria' => &$criteria,
        );
    }

    /**
     * @get attachment download limitation.
     * if no attachment, return false.
     *
     * @param XooNIpsItem $item
     * @retval true login user only(limited)
     * @retval false all user(not limited)
     */
    public function getDownloadLimitation($item)
    {
        return false;
    }

    public function setNew(&$obj)
    {
        $this->callForeachVars($obj, 'setNew');
    }

    public function unsetNew(&$obj)
    {
        $this->callForeachVars($obj, 'unsetNew');
    }

    public function setDirty(&$obj)
    {
        $this->callForeachVars($obj, 'setDirty');
    }

    public function unsetDirty(&$obj)
    {
        $this->callForeachVars($obj, 'unsetDirty');
    }

    public function callForeachVars(&$obj, $methodName)
    {
        foreach (array_keys($obj->getVars()) as $key) {
            if (!isset($this->handlers[$key])) {
                trigger_error("unknown handlers index $key");

                return;
            }
            if ($this->handlers[$key]['multiple'] && is_array($obj->getVar($key))) {
                $objs = &$obj->getVar($key);
                for ($i = 0; $i < count($objs); ++$i) {
                    if (is_subclass_of($objs[$i], 'XoopsObject')) {
                        $objs[$i]->$methodName();
                    }
                }
            } else {
                $o = &$obj->getVar($key);
                if (is_subclass_of($o, 'XoopsObject')) {
                    $o->$methodName();
                }
            }
        }
    }
}
//- - - - - - - - - - - - - - - - - - - - - - - - - - - -
//
// Data object
//
//- - - - - - - - - - - - - - - - - - - - - - - - - - - -

/**
 * @brief data object of xoonips item(basic fields)
 */
class XooNIpsRelatedObject
{
    public $vars = array();

    public function XooNIpsRelatedObject()
    {
    }

    /**
     * @brief initialize variable.
     *
     * @param[in] string $key
     * @param[in] mixed $val
     * @param[in] boolean $required true if required in insert(default is false)
     */
    public function initVar($key, &$val, $required = false)
    {
        $this->vars[$key] = array(
            'key' => $key,
            'value' => &$val,
            'required' => $required,
        );
    }

    /**
     * assign a value to a variable.
     *
     * @param string $key   name of the variable to assign
     * @param mixed  $value value to assign
     */
    public function setVar($key, $val)
    {
        if (!empty($key) && isset($val) && isset($this->vars[$key])) {
            $this->vars[$key]['value'] = &$val;
        }
    }

    /**
     * returns all variables for the object.
     *
     * @return array array of associative array( 'key' => $key, 'value' => &$val, 'required' => $required );
     */
    public function &getVars()
    {
        return $this->vars;
    }

    /**
     * returns a specific variable for the object.
     *
     * @param string $key key of the object's variable to be returned
     *
     * @return mixed a value of the variable
     */
    public function &getVar($key)
    {
        return $this->vars[$key]['value'];
    }

    /**
     * @brief check that required fields are filled.
     *
     * @param[in] $file file object to be checked
     * @param[out] $missing array of string of missing field name
     * @retval ture filled
     * @retval false not filled
     */
    public function isFilledRequired(&$missing)
    {
        if (!isset($missing) || !is_array($missing)) {
            $missing = array();
        }
        foreach ($this->vars as $field => $value) {
            if (!$value['required']) {
                continue;
            } //skip not required field
            //
            $var = &$this->getVar($field);
            if (is_array($var)) {
                if (0 == count($var)) {
                    $missing[] = $field;
                } else {
                    for ($i = 0; $i < count($var); ++$i) {
                        $var[$i]->isFilledRequired($miss);
                        $missing += $miss;
                    }
                }
            } else {
                $var->isFilledRequired($miss);
                $missing += $miss;
            }
        }

        return 0 == count($missing);
    }

    public function &xoopsClone()
    {
        $class = get_class($this);
        $clone = new $class();
        foreach (array_keys($this->getVars()) as $key) {
            $ret = &$this->getVar($key);
            if (!isset($ret)) {
                continue;
            }
            if (is_array($this->getVar($key))) {
                $objs = &$this->getVar($key);
                $clone_objs = array();
                for ($i = 0; $i < count($objs); ++$i) {
                    if (is_subclass_of($objs[$i], 'XoopsObject')) {
                        $clone_objs[$i] = &$objs[$i]->xoopsClone();
                    }
                }
                $clone->setVar($key, $clone_objs);
            } else {
                $o = &$this->getVar($key);
                if (is_subclass_of($o, 'XoopsObject')) {
                    $clone->setVar($key, $o->xoopsClone());
                }
            }
        }

        return $clone;
    }
}
