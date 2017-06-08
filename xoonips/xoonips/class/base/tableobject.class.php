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

define('XOBJ_DTYPE_BINARY', 201);

/**
 * The basic object class for the XooNIps.
 */
class XooNIpsTableObject extends XoopsObject
{
    /**
     * extra vars holder for the joining tables.
     *
     * @var array
     */
    public $_extra_vars = array();

    /**
     * default vars holder.
     *
     * @var array
     */
    public $_default_vars = array();

    /**
     * do you want to replace object?
     *
     * @var bool
     */
    public $_do_replace = false;

    /**
     * acceptable data types.
     *
     * @var array
     */
    public $_data_types = array(
    XOBJ_DTYPE_TXTBOX,
    XOBJ_DTYPE_TXTAREA,
    XOBJ_DTYPE_INT,
    XOBJ_DTYPE_ARRAY,
    XOBJ_DTYPE_OTHER,
    XOBJ_DTYPE_BINARY,
    );

    /**
     * constructor.
     *
     * normally, this is called from child classes only
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * set replace mark for replace object.
     */
    public function setReplace()
    {
        $this->_do_replace = true;
    }

    /**
     * unset replace mark for replace object.
     */
    public function unsetReplace()
    {
        $this->_do_replace = false;
    }

    /**
     * check replace mark for replace object.
     *
     * @return bool status
     */
    public function doReplace()
    {
        return $this->_do_replace;
    }

    /**
     * get data type.
     *
     * @param string $key
     *
     * @return int data type: XOBJ_DTYPE_XXXX
     */
    public function getDataType($key)
    {
        return $this->vars[$key]['data_type'];
    }

    /**
     * set text area display attributes.
     *
     * @parem bool $dohtml use raw html
     * @parem bool $doxcode use xcode
     * @parem bool $dosmiley use smiley marks
     * @parem bool $dobr use <br /> new line
     */
    public function setTextAreaDisplayAttributes($dohtml, $doxcode, $dosmiley, $dobr)
    {
        $this->vars['dohtml']['value'] = ($dohtml === true) ? 1 : 0;
        $this->vars['dohtml']['changed'] = false;
        $this->vars['doxcode']['value'] = ($doxcode === true) ? 1 : 0;
        $this->vars['doxcode']['changed'] = false;
        $this->vars['dosmiley']['value'] = ($dosmiley === true) ? 1 : 0;
        $this->vars['dosmiley']['changed'] = false;
        $this->vars['dobr']['value'] = ($dobr === true) ? 1 : 0;
        $this->vars['dobr']['changed'] = false;
    }

    /**
     * initialize variables for the object.
     *
     * @param string $key
     * @param int    $data_type set to one of XOBJ_DTYPE_XXX constants (set to XOBJ_DTYPE_OTHER if no data type ckecking nor text sanitizing is required)
     * @param mixed
     * @param bool   $required  require html form input?
     * @param int    $maxlength for XOBJ_DTYPE_TXTBOX type only
     * @param string $option    does this data have any select options?
     */
    public function initVar($key, $data_type, $value = null, $required = false, $maxlength = null, $options = '')
    {
        if (XOONIPS_DEBUG_MODE) {
            if (!in_array($data_type, $this->_data_types)) {
                die('fatal error : you should not use data_type '.$data_type);
            }
        }
        $this->vars[$key] = array(
        'value' => $value,
        'required' => $required,
        'data_type' => $data_type,
        'maxlength' => $maxlength,
        'changed' => false,
        'options' => $options,
        );
        $this->_default_vars[$key] = $value;
    }

    /**
     * set a value to a variable.
     *
     * @param string $key     name of the variable to assign
     * @param mixed  $value   value to assign
     * @param bool   $not_gpc
     *
     * @return bool false if failed
     */
    public function setVar($key, $value, $not_gpc = false)
    {
        if (XOONIPS_DEBUG_MODE) {
            if ($not_gpc == false) {
                // fatal error if not gpc data set
                echo '<pre>';
                print_r(debug_backtrace());
                echo '</pre>';
                die('fatal error: you should not set $not_gpc = false');
            }
        }
        $value = $not_gpc ? $value : $this->_stripSlashesGPC($value);

        return $this->set($key, $value);
    }

    /**
     * returns a specific variable for the object in a proper format.
     *
     * @param string $key    key of the object's variable to be returned
     * @param string $format format to use for the output
     *
     * @return mixed formatted value of the variable
     */
    public function &getVar($key, $format = null)
    {
        $ret = $this->vars[$key]['value'];
        (method_exists('MyTextSanitizer', 'sGetInstance') and $ts = &MyTextSanitizer::sGetInstance()) || $ts = &MyTextSanitizer::getInstance();
        $textutil = &xoonips_getutility('text');

        if (XOONIPS_DEBUG_MODE) {
            // fatal error if format not set
            if (is_null($format)) {
                echo '<pre>';
                print_r(debug_backtrace());
                echo '</pre>';
                die('fatal error: you should set $format parameter');
            }
            $format = strtolower($format);
            // fatail error if $format given 'p' or 'f' option
            if ($format == 'p' || $format == 'preview' || $format == 'f' || $format == 'formpreview') {
                $cname = get_class($this);
                echo '<pre>';
                print_r(debug_backtrace());
                echo '</pre>';
                die('fatal error: you should not use '.$cname.'->getVar( $k, \''.$format.'\' ) in XooNIps');
            }
        }

        switch ($this->vars[$key]['data_type']) {
        case XOBJ_DTYPE_TXTBOX:
            switch ($format) {
            case 's':
            case 'show':
            case 'e':
            case 'edit':
                $ret = $textutil->html_special_chars($ret);

                return $ret;
              break 1;
            case 'n':
            case 'none':
            default:
                break 1;
            }
            break;
        case XOBJ_DTYPE_TXTAREA:
            switch ($format) {
            case 's':
            case 'show':
                $html = (isset($this->vars['dohtml']['value']) && $this->vars['dohtml']['value'] == 1);
                $xcode = (isset($this->vars['doxcode']['value']) && $this->vars['doxcode']['value'] == 1);
                $smiley = (isset($this->vars['dosmiley']['value']) && $this->vars['dosmiley']['value'] == 1);
                $image = (isset($this->vars['doimage']['value']) && $this->vars['doimage']['value'] == 1);
                $br = (isset($this->vars['dobr']['value']) && $this->vars['dobr']['value'] == 1);
                $ret = $textutil->display_text_area($ret, $html, $smiley, $xcode, $image, $br);

                return $ret;
              break 1;
            case 'e':
            case 'edit':
                $ret = $textutil->html_special_chars($ret);

                return $ret;
              break 1;
            case 'n':
            case 'none':
            default:
                break 1;
            }
            break;
        case XOBJ_DTYPE_ARRAY:
            $ret = unserialize($ret);
            break;
        case XOBJ_DTYPE_SOURCE:
            switch ($format) {
            case 's':
            case 'show':
                break 1;
            case 'e':
            case 'edit':
                $ret = $textutil->html_special_chars($ret);

                return $ret;
              break 1;
            case 'n':
            case 'none':
            default:
                break 1;
            }
            break;
        default:
            if ($this->vars[$key]['options'] != '' && $ret != '') {
                switch ($format) {
                case 's':
                case 'show':
                    $selected = explode('|', $ret);
                    $options = explode('|', $this->vars[$key]['options']);
                    $i = 1;
                    $ret = array();
                    foreach ($options as $op) {
                        if (in_array($i, $selected)) {
                            $ret[] = $op;
                        }
                        ++$i;
                    }
                    $ret = implode(', ', $ret);

                    return $ret;
                case 'e':
                case 'edit':
                    $ret = explode('|', $ret);
                    break 1;
                default:
                    break 1;
                }
            }
            break;
        }

        return $ret;
    }

    /**
     * get all variables as array.
     *
     * @param string $format format to use for the output
     *
     * @return array reference to the variables array
     */
    public function getVarArray($format)
    {
        $vars_array = array();
        $keys_array = &$this->getKeysArray();
        foreach ($keys_array as $k) {
            $vars_array[$k] = $this->getVar($k, $format);
        }

        return $vars_array;
    }

    /**
     * get all keys as array.
     *
     * @return array keys array
     */
    public function &getKeysArray()
    {
        $ignore_keys = array(
        'dohtml',
        'doxcode',
        'dosmiley',
        'dobr',
        );
        $keys_array = array_diff(array_keys($this->vars), $ignore_keys);

        return $keys_array;
    }

    /**
     * strip slashes when magic quota gpc is on.
     *
     * @param mixed $value
     *
     * @return mixed slashes striped value
     */
    public function _stripSlashesGPC($value)
    {
        return get_magic_quotes_gpc() ? stripslashes($value) : $value;
    }

    /**
     * check that required fields are filled.
     * it refers $vars[$key]['required'].
     *
     * @param string &$missing array of string of missing field name
     *
     * @return bool false if not filled
     */
    public function isFilledRequired(&$missing)
    {
        $missing = array();
        foreach (array_keys($this->vars) as $field) {
            if (!$this->vars[$field]['required']) {
                continue;
            }
            $var = &$this->get($field);
            if (is_array($var) && 0 == count($var) || !is_array($var) && empty($var)) {
                $missing[] = $field;
            }
        }

        return 0 == count($missing);
    }

    /**
     * @brief get a value of variable without any sanitizing
     *
     * @param string $key name of the variable to get
     *
     * @return mixed value of variable
     */
    public function get($key)
    {
        return $this->vars[$key]['value'];
    }

    /**
     * @brief set a sanitized value
     *
     * @param string $key   $key name of the variable to assign
     * @param mixed  $value value of variable to assin
     *
     * @return bool false if failed
     */
    public function set($key, $value)
    {
        if (!empty($key) && isset($value) && isset($this->vars[$key])) {
            $this->vars[$key]['value'] = $value;
            // always true
            $this->vars[$key]['not_gpc'] = true;
            $this->vars[$key]['changed'] = true;
            $this->setDirty();

            return true;
        }

        return false;
    }

    /**
     * get all variables(no format conversions).
     *
     * @return array associative array of key->value pairs
     */
    public function getArray()
    {
        return $this->getVarArray('none');
    }

    /**
     * @brief compare to another object
     *
     * @param obj XooNIpsTableObject or empty value
     *
     * @return true if all vars equal to obj vars
     */
    public function equals($obj)
    {
        if (!$obj) {
            return false;
        }
        foreach (array_keys($this->vars) as $field) {
            if ($this->vars[$field]['value'] != $obj->get($field)) {
                return false;
            }
        }

        return true;
    }

    /**
     * clean values of all variables of the object for storage.
     * also add slashes whereever needed.
     *
     * @return bool true if successful
     */
    public function cleanVars()
    {
        (method_exists('MyTextSanitizer', 'sGetInstance') and $ts = &MyTextSanitizer::sGetInstance()) || $ts = &MyTextSanitizer::getInstance();
        foreach ($this->vars as $k => $v) {
            $cleanv = $v['value'];
            if ($v['changed']) {
                $cleanv = is_string($cleanv) ? trim($cleanv) : $cleanv;
                switch ($v['data_type']) {
                case XOBJ_DTYPE_TXTBOX:
                    if ($v['required'] && $cleanv != '0' && $cleanv == '') {
                        $this->setErrors("$k is required.");
                        continue;
                    }
                        $cleanv = $ts->censorString($cleanv);
                    if (isset($v['maxlength']) && mb_strlen($cleanv, _CHARSET) > intval($v['maxlength'])) {
                        $this->setErrors("$k must be shorter than ".intval($v['maxlength']).' characters.');
                        continue;
                    }
                    break;
                case XOBJ_DTYPE_TXTAREA:
                    if ($v['required'] && $cleanv != '0' && $cleanv == '') {
                        $this->setErrors("$k is required.");
                        continue;
                    }
                        $cleanv = $ts->censorString($cleanv);
                    break;
                case XOBJ_DTYPE_SOURCE:
                        $cleanv = $cleanv;
                    break;
                case XOBJ_DTYPE_INT:
                    if (!is_null($cleanv)) {
                        $cleanv = intval($cleanv);
                    }
                    break;
                case XOBJ_DTYPE_EMAIL:
                    if ($v['required'] && $cleanv == '') {
                        $this->setErrors("$k is required.");
                        continue;
                    }
                    if ($cleanv != '' && !preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+([\.][a-z0-9-]+)+$/i", $cleanv)) {
                        $this->setErrors('Invalid Email');
                        continue;
                    }
                    break;
                case XOBJ_DTYPE_URL:
                    if ($v['required'] && $cleanv == '') {
                        $this->setErrors("$k is required.");
                        continue;
                    }
                    if ($cleanv != '' && !preg_match("/^http[s]*:\/\//i", $cleanv)) {
                        $cleanv = 'http://'.$cleanv;
                    }
                    break;
                case XOBJ_DTYPE_ARRAY:
                        $cleanv = serialize($cleanv);
                    break;
                case XOBJ_DTYPE_STIME:
                case XOBJ_DTYPE_MTIME:
                case XOBJ_DTYPE_LTIME:
                        $cleanv = !is_string($cleanv) ? intval($cleanv) : strtotime($cleanv);
                    break;
                case XOBJ_DTYPE_BINARY:
                        $cleanv = $v['value'];
                    if ($v['required'] && (is_null($cleanv) || $cleanv === '')) {
                        $this->setErrors($k.' is required.');
                        continue;
                    }
                    if (isset($v['maxlength']) && strlen($cleanv) > intval($v['maxlength'])) {
                        $this->setErrors("$k must be shorter than ".intval($v['maxlength']).' characters.');
                        continue;
                    }
                    break;
                default:
                    break;
                }
            }
            $this->cleanVars[$k] = &$cleanv;
            unset($cleanv);
        }
        if (count($this->_errors) > 0) {
            return false;
        }
        $this->unsetDirty();

        return true;
    }

    /**
     * assign values to multiple variables in a batch.
     *
     * @param array $var_array ssociative array of values to assign
     *
     * @return bool false if failed
     */
    public function assignVars($var_arr)
    {
        foreach ($var_arr as $key => $value) {
            $this->assignVar($key, $value);
        }

        return true;
    }

    /**
     * assign a value to a variable.
     *
     * @param string $key
     * @param string $value
     *
     * @return bool false if failed
     */
    public function assignVar($key, $value)
    {
        if (!empty($key) && isset($value) && isset($this->vars[$key])) {
            $this->vars[$key]['value'] = &$value;
        } else {
            $this->setExtraVar($key, $value);
        }

        return true;
    }

    /**
     * set extra var.
     *
     * @param string $key
     * @param string $value
     *
     * @return bool false if failed
     */
    public function setExtraVar($key, $value)
    {
        $this->_extra_vars[$key] = &$value;

        return true;
    }

    /**
     * get extra var.
     *
     * @param object $key
     *
     * @return string extra var
     */
    public function &getExtraVar($key)
    {
        return $this->_extra_vars[$key];
    }

    /**
     * set default value to a variable.
     *
     * @param string $key name of the variable
     *
     * @return bool false if faiure
     */
    public function setDefault($key)
    {
        if (!empty($key) && isset($this->vars[$key])) {
            $this->vars[$key]['value'] = $this->_default_vars[$key];
            $this->vars[$key]['not_gpc'] = true;
            $this->vars[$key]['changed'] = true;
            $this->setDirty();

            return true;
        }

        return false;
    }

    /**
     * get maximum field length.
     *
     * @param string $key
     *
     * @return int
     */
    public function getMaxLength($key)
    {
        if (!isset($this->vars[$key])) {
            return false;
        }
        if ($this->vars[$key]['data_type'] == XOBJ_DTYPE_TXTAREA) {
            // must be mysql data type 'text' or 'blob'
            return 65535;
        }
        if (isset($this->vars[$key]['maxlength'])) {
            return $this->vars[$key]['maxlength'];
        }

        return false;
    }
}

/**
 * The basic object handler class for the XooNIps.
 */
class XooNIpsTableObjectHandler extends XoopsObjectHandler
{
    /**
     * class name of handling object.
     *
     * @var string
     */
    public $__class_name;

    /**
     * database table name for object mapping.
     *
     * @var string
     */
    public $__table_name;

    /**
     * primary key name of database table.
     *
     * @var string
     */
    public $__key_name;

    /**
     * flag for primary key is auto increment.
     *
     * @var bool
     */
    public $__is_autoincrement = true;

    /**
     * flag for primary key is string.
     *
     * @var bool
     */
    public $__is_string_primary_key = true;

    /**
     * last sql query string.
     *
     * @var string
     */
    public $__last_sql = '';

    /**
     * constructor.
     *
     * normally, this is called from child classes only
     *
     * @param XoopsDatabase &$db XoopsDatabase instance
     */
    public function __construct(&$db)
    {
        parent::__construct($db);
    }

    /**
     * initilizing function, this is called from child class only.
     *
     * @param string $cname                 class name
     * @param string $tname                 database table name for object mapping
     * @param string $key                   primary key name of database table
     * @param bool   $is_autoincrement      TRUE if primary key is autoincrement field
     * @param bool   $is_string_primary_key TRUE if primary key is string field
     */
    public function __initHandler($cname, $tname, $key, $is_autoincrement = true, $is_string_primary_key = false)
    {
        $this->__class_name = $cname;
        $this->__table_name = $tname;
        $this->__key_name = $key;
        $this->__is_autoincrement = $is_autoincrement;
        $this->__is_string_primary_key = $is_string_primary_key;
    }

    /**
     * return non prefixed table name.
     *
     * @return string table name
     */
    public function getTableName()
    {
        return $this->__table_name;
    }

    /**
     * return primary key name.
     *
     * @return string primary key name
     */
    public function getKeyName()
    {
        return $this->__key_name;
    }

    /**
     * check is string primary key.
     *
     * @return bool true if primary key is string
     */
    public function isStringPrimaryKey()
    {
        return $this->__is_string_primary_key;
    }

    /**
     * get last sql query string.
     *
     * @return string sql
     */
    public function getLastSQL()
    {
        return $this->__last_sql;
    }

    /**
     * create a new object.
     *
     * @param bool isNew mark the new object as 'new'?
     *
     * @return object XooNIpsTableObject reference to the new object
     */
    public function &create($isNew = true)
    {
        $obj = new $this->__class_name();
        if ($isNew) {
            $obj->setNew();
        }

        return $obj;
    }

    /**
     * gets a value object.
     *
     * @param mixed(int/string) $id
     *
     * @return object XooNIpsTableObject reference to the object instance
     */
    public function &get($id)
    {
        $ret = false;
        if (intval($id) > 0 || $this->__is_string_primary_key) {
            if ($this->__is_string_primary_key) {
                $id_str = $this->db->quoteString($id);
            } else {
                $id_str = sprintf('%u', $id);
            }
            $sql = sprintf('SELECT * FROM `%s` WHERE `%s`=%s', $this->db->prefix($this->__table_name), $this->__key_name, $id_str);
            if ($result = &$this->_query($sql)) {
                $numrows = $this->db->getRowsNum($result);
                if ($numrows == 1) {
                    $obj = new $this->__class_name();
                    $obj->assignVars($this->db->fetchArray($result));
                    $ret = &$obj;
                }
                $this->db->freeRecordSet($result);
            }
        }

        return $ret;
    }

    /**
     * insert/update/replace object.
     *
     * @param object &$obj
     * @param bool   $force force operation
     *
     * @return bool false if failed
     */
    public function insert(&$obj, $force = false)
    {
        if (strtolower(get_class($obj)) != strtolower($this->__class_name)) {
            return false;
        }
        if (!$obj->isDirty()) {
            return true;
        }
        if (!$obj->cleanVars()) {
            return false;
        }
        if ($obj->isNew() || $obj->doReplace()) {
            $sql_arr = &$this->_makeVarsArray4SQL($obj, $obj->cleanVars);
            if ($this->__is_autoincrement && !$obj->doReplace()) {
                $myid = $this->db->genId($this->__table_name.'_'.$this->__key_name.'_seq');
            } else {
                $myid = $sql_arr[$this->__key_name];
            }
            $sql_fields = array();
            $sql_values = array();
            foreach (array_keys($sql_arr) as $name) {
                $sql_fields[] = '`'.$name.'`';
                if ($name == $this->__key_name) {
                    $sql_values[] = $myid;
                } else {
                    $sql_values[] = (is_null($sql_arr[$name]) ? 'NULL' : $sql_arr[$name]);
                }
            }
            if ($obj->doReplace()) {
                $sql_cmd = 'REPLACE';
            } else {
                $sql_cmd = 'INSERT';
            }
            $sql = sprintf('%s INTO `%s` ( %s ) VALUES ( %s )', $sql_cmd, $this->db->prefix($this->__table_name), implode(',', $sql_fields), implode(',', $sql_values));
        } else {
            $sql_arr = &$this->_makeVarsArray4SQL($obj, $obj->cleanVars);
            $myid = $sql_arr[$this->__key_name];
            $sql_keyl = array();
            $sql_setl = array();
            foreach (array_keys($sql_arr) as $name) {
                if ($name == $this->__key_name) {
                    $sql_keyl[] = '`'.$name.'`='.$sql_arr[$name];
                } else {
                    $sql_setl[] = '`'.$name.'`='.(is_null($sql_arr[$name]) ? 'NULL' : $sql_arr[$name]);
                }
            }
            $sql = sprintf('UPDATE `%s` SET %s WHERE %s', $this->db->prefix($this->__table_name), implode(', ', $sql_setl), implode(' AND ', $sql_keyl));
        }
        if (!$result = &$this->_query($sql, $force)) {
            return false;
        }
        if (!$this->__is_string_primary_key) {
            if (empty($myid)) {
                // get inserted primary key id when primary key is auto
                // increment field and $db->genId() returned zero
                $myid = $this->db->getInsertId();
            }
            // update primary key id
            $obj->assignVar($this->__key_name, $myid);
        }

        return true;
    }

    /**
     * delete object.
     *
     * @param object &$obj
     * @param bool   $force force operation
     *
     * @return bool false if failed
     */
    public function delete(&$obj, $force = false)
    {
        if (strtolower(get_class($obj)) != strtolower($this->__class_name)) {
            return false;
        }
        if ($this->__is_string_primary_key) {
            $id_str = $this->db->quoteString($obj->get($this->__key_name));
        } else {
            $id_str = sprintf('%u', $obj->get($this->__key_name));
        }
        $sql = sprintf('DELETE FROM `%s` WHERE `%s` = %s', $this->db->prefix($this->__table_name), $this->__key_name, $id_str);
        if (!$result = &$this->_query($sql, $force)) {
            return false;
        }

        return true;
    }

    /**
     * gets objects.
     *
     * @param object            $criteria
     * @param bool              $id_as_key
     * @param string            $fieldlist fieldlist for distinct select
     * @param bool              $distinct
     * @param XoopsJoinCriteria $joindef   join criteria object
     *
     * @return array objects
     */
    public function &getObjects($criteria = null, $id_as_key = false, $fieldlist = '', $distinct = false, $joindef = null)
    {
        $ret = array();
        $result = &$this->open($criteria, $fieldlist, $distinct, $joindef);
        if (!$result) {
            return $ret;
        }
        while ($obj = &$this->getNext($result)) {
            if (!$id_as_key) {
                $ret[] = &$obj;
            } else {
                $key_id = $obj->get($this->__key_name);
                $ret[$key_id] = &$obj;
            }
            unset($obj);
        }
        $this->close($result);

        return $ret;
    }

    /**
     * open select query.
     *
     * @param object            $criteria
     * @param string            $fieldlist fieldlist for distinct select
     * @param bool              $distinct
     * @param XoopsJoinCriteria $joindef   join criteria object
     *
     * @return resource
     */
    public function &open($criteria = null, $fieldlist = '', $distinct = false, $joindef = null)
    {
        $limit = $start = 0;
        if (isset($criteria) && (is_subclass_of($criteria, 'criteriaelement') || strtolower(get_class($criteria)) == 'criteriaelement')) {
            $limit = $criteria->getLimit();
            $start = $criteria->getStart();
        }
        $sql = $this->_makeSQL($criteria, $fieldlist, $distinct, $joindef);

        return $this->_query($sql, false, $limit, $start);
    }

    /**
     * get next object.
     *
     * @param resource &$result mysql result
     *
     * @return object
     */
    public function &getNext(&$result)
    {
        if (!$myrow = $this->db->fetchArray($result)) {
            $ret = false;

            return $ret;
        }
        $obj = new $this->__class_name();
        $obj->assignVars($myrow);

        return $obj;
    }

    /**
     * close select query.
     *
     * @param resource &$result mysql result
     *
     * @return bool false if failed
     */
    public function close(&$result)
    {
        if (!$result) {
            return false;
        }

        return $this->db->freeRecordSet($result);
    }

    /**
     * count how many rows in tables.
     *
     * @param object $criteria
     * @param object $joindef  join criteria
     *
     * @return int number of rows
     */
    public function getCount($criteria = null, $joindef = null)
    {
        $sql = sprintf('SELECT COUNT(*) FROM `%s`', $this->db->prefix($this->__table_name));
        if (is_object($joindef) && strtolower(get_class($joindef)) == 'xoonipsjoincriteria') {
            $sql .= $joindef->render($this->db, $this->__table_name, false);
        }
        if (isset($criteria) && is_subclass_of($criteria, 'criteriaelement')) {
            $sql .= ' '.$criteria->renderWhere();
        }
        if (!$result = &$this->_query($sql)) {
            return 0;
        }
        list($count) = $this->db->fetchRow($result);
        $this->db->freeRecordSet($result);

        return $count;
    }

    /**
     * delete objects using criteria.
     *
     * @param object $criteria
     * @param bool   $force    force operation
     *
     * @return bool false if failed
     */
    public function deleteAll($criteria = null, $force = false)
    {
        $sql = sprintf('DELETE FROM `%s`', $this->db->prefix($this->__table_name));
        if (isset($criteria) && is_subclass_of($criteria, 'criteriaelement')) {
            $sql .= ' '.$criteria->renderWhere();
        }
        if (!$result = &$this->_query($sql, $force)) {
            return false;
        }

        return true;
    }

    /**
     * gets union objects.
     *
     * @param array  $vars
     * @param bool   $id_bas_key
     * @param bool   $unionall      true if use UNION ALL(default is UNION)
     * @param object $unioncriteria
     *
     * @return array objects
     */
    public function &getUnionObjects($vars, $id_as_key = false, $unionall = false, $unioncriteria = null)
    {
        $ret = array();
        foreach ($vars as $var) {
            $criteria = isset($var[0]) ? $var[0] : null;
            $fieldlist = isset($var[1]) ? $var[1] : '';
            $distinct = isset($var[2]) ? $var[2] : false;
            $joindef = isset($var[3]) ? $var[3] : null;
        }
        $result = &$this->openUnion($vars, $unionall, $unioncriteria);
        if (!$result) {
            return $ret;
        }
        while ($obj = &$this->getNext($result)) {
            if (!$id_as_key) {
                $ret[] = &$obj;
            } else {
                $key_id = $obj->get($this->__key_name);
                $ret[$key_id] = &$obj;
            }
            unset($obj);
        }
        $this->close($result);

        return $ret;
    }

    /**
     * open union select query.
     *
     * @param array  $vars
     * @param object $unioncriteria
     * @param bool   $unionall      true if use UNION ALL(default is UNION)
     *
     * @return resource
     */
    public function &openUnion($vars, $unionall = false, $unioncriteria = null)
    {
        $limit = $start = 0;
        $order_by = '';
        $sqls = array();
        foreach ($vars as $var) {
            $criteria = isset($var[0]) ? $var[0] : null;
            $fieldlist = isset($var[1]) ? $var[1] : '';
            $distinct = isset($var[2]) ? $var[2] : false;
            $joindef = isset($var[3]) ? $var[3] : null;
            $sqls[] = $this->_makeSQL($criteria, $fieldlist, $distinct, $joindef);
        }
        if (count($sqls) > 1) {
            // last query must be enclosed last select query by () for order by
            $sqls[count($sqls) - 1] = '('.$sqls[count($sqls) - 1].')';
        }

        $sql = '';
        if (isset($unioncriteria) && (is_subclass_of($unioncriteria, 'criteriaelement') || strtolower(get_class($unioncriteria)) == 'criteriaelement')) {
            if ($unioncriteria->getGroupby() != ' GROUP BY ') {
                $sql .= ' '.$unioncriteria->getGroupby();
            }
            if ((is_array($unioncriteria->getSort()) && count($unioncriteria->getSort()) > 0)) {
                $orderStr = 'ORDER BY ';
                $orderDelim = '';
                foreach ($unioncriteria->getSort() as $sortVar) {
                    $orderStr .= $orderDelim.$sortVar.' '.$unioncriteria->getOrder();
                    $orderDelim = ',';
                }
                $sql .= ' '.$orderStr;
            } elseif ($unioncriteria->getSort() != '') {
                $orderStr = 'ORDER BY '.$unioncriteria->getSort().' '.$unioncriteria->getOrder();
                $sql .= ' '.$orderStr;
            }
            $limit = $unioncriteria->getLimit();
            $start = $unioncriteria->getStart();
        }
        $sql = implode($unionall ? ' UNION ALL ' : ' UNION ', $sqls).$sql;

        return $this->_query($sql, false, $limit, $start);
    }

    /**
     * update foreign key related objects
     * - insert new object
     * - update modified object
     * - delete object not in $objects from DB.
     *
     * @param string               $foreign_key   key name of ORM
     * @param string               $foreign_value value of foreign key
     * @param XooNIpsTableObject[] &$objects
     *
     * @return bool
     */
    public function updateAllObjectsByForeignKey($foreign_key, $foreign_value, &$objects)
    {
        // insert/update creator
        $inserted_primary_ids = array();
        foreach ($objects as $obj) {
            $obj->set($foreign_key, $foreign_value);
            if (!$this->insert($obj)) {
                trigger_error('cannot insert '.get_class($obj).': '.serialize($obj->getArray()));

                return false;
            }
            $inserted_primary_ids[] = $obj->get($this->getKeyName());
        }
        // delete redundant obj from DB
        $criteria = new CriteriaCompo();
        $criteria->add(new Criteria($foreign_key, $foreign_value));
        if (!empty($inserted_primary_ids)) {
            $criteria->add(new Criteria($this->getKeyName(), '('.join(',', $inserted_primary_ids).')', 'NOT IN'));
        }
        if (!$this->deleteAll($criteria)) {
            trigger_error('cannot delete redundant '.get_class($obj));

            return false;
        }

        return true;
    }

    /**
     * helper function for sql string creation.
     *
     * @param object &$obj
     * @param array  &$vars array of variables
     *
     * @return array quoted strings
     */
    public function &_makeVarsArray4SQL(&$obj, &$vars)
    {
        $ret = array();
        $keys_array = &$obj->getKeysArray();
        foreach ($keys_array as $k) {
            switch ($obj->vars[$k]['data_type']) {
            case XOBJ_DTYPE_TXTBOX:
            case XOBJ_DTYPE_TXTAREA:
            case XOBJ_DTYPE_URL:
            case XOBJ_DTYPE_EMAIL:
            case XOBJ_DTYPE_ARRAY:
            case XOBJ_DTYPE_OTHER:
            case XOBJ_DTYPE_SOURCE:
            case XOBJ_DTYPE_BINARY:
                if (is_null($vars[$k])) {
                    $ret[$k] = 'NULL';
                } else {
                    $ret[$k] = $this->db->quoteString($vars[$k]);
                }
                break;
            case XOBJ_DTYPE_INT:
            case XOBJ_DTYPE_STIME:
            case XOBJ_DTYPE_MTIME:
            case XOBJ_DTYPE_LTIME:
            default:
                if (is_null($vars[$k])) {
                    $ret[$k] = 'NULL';
                } else {
                    $ret[$k] = $vars[$k];
                }
            }
        }

        return $ret;
    }

    /**
     * make SQL statement.
     *
     * @param object            $criteria
     * @param string            $fieldlist fieldlist for distinct select
     * @param bool              $distinct
     * @param XoopsJoinCriteria $joindef   join criteria object
     *
     * @return string SQL
     */
    public function _makeSQL($criteria = null, $fieldlist = '', $distinct = false, $joindef = null)
    {
        $distinct = ($distinct) ? 'DISTINCT ' : '';
        $fieldlist = ($fieldlist == '') ? '*' : $fieldlist;
        $sql = sprintf('SELECT %s%s FROM `%s`', $distinct, $fieldlist, $this->db->prefix($this->__table_name));
        if ($joindef) {
            if (strtolower(get_class($joindef)) == 'xoonipsjoincriteria') {
                $sql .= $joindef->render($this->db, $this->__table_name, false);
            }
        }
        if (isset($criteria) && is_subclass_of($criteria, 'criteriaelement')) {
            $sql .= ' '.$criteria->renderWhere();
        }
        if (isset($criteria) && (is_subclass_of($criteria, 'criteriaelement') || strtolower(get_class($criteria)) == 'criteriaelement')) {
            if ($criteria->getGroupby() != ' GROUP BY ') {
                $sql .= ' '.$criteria->getGroupby();
            }
            if ((is_array($criteria->getSort()) && count($criteria->getSort()) > 0)) {
                $orderStr = 'ORDER BY ';
                $orderDelim = '';
                foreach ($criteria->getSort() as $sortVar) {
                    $orderStr .= $orderDelim.$sortVar.' '.$criteria->getOrder();
                    $orderDelim = ',';
                }
                $sql .= ' '.$orderStr;
            } elseif ($criteria->getSort() != '') {
                $orderStr = 'ORDER BY '.$criteria->getSort().' '.$criteria->getOrder();
                $sql .= ' '.$orderStr;
            }
        }

        return $sql;
    }

    /**
     * query sql.
     *
     * @param string $sql   sql query string
     * @param bool   $force force operation
     * @param int    $limit
     * @param int    $start
     *
     * @return resource
     */
    public function &_query($sql, $force = false, $limit = 0, $start = 0)
    {
        if (empty($limit)) {
            $this->__last_sql = $sql;
        } else {
            $this->__last_sql = $sql.' LIMIT '.(int) $start.', '.(int) $limit;
        }
        if ($force) {
            $result = &$this->db->queryF($sql, $limit, $start);
        } else {
            $result = &$this->db->query($sql, $limit, $start);
        }
        if (!$result) {
            if (XOONIPS_DEBUG_MODE) {
                echo '<pre>';
                print_r(debug_backtrace());
                echo '</pre>';
                die('fatal error: on SQL query - '.$this->db->error());
            }
            trigger_error($this->db->error());
        }

        return $result;
    }
}
