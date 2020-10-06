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
 * requested form data handling class.
 *
 * @copyright copyright &copy; 2008 RIKEN Japan
 */
class XooNIpsUtilityFormdata extends XooNIpsUtility
{
    /**
     * constructor.
     */
    public function __construct()
    {
        $this->setSingleton();
    }

    /**
     * get value from requested form data.
     *
     * @param string $method        'get', 'post', 'both'
     * @param string $name          data name
     * @param string $type          data type
     *                              s:string, i:integer, f:float, n:none, b:boolean
     * @param bool   $is_required   true if form must be requested data
     * @param mixed  $default_value set default value if request is null
     *
     * @return mixed requested form data
     */
    public function getValue($method, $name, $type, $is_required, $default_value = null)
    {
        $val = $this->_get_request_data($method, $name, $is_required);
        if (is_null($val)) {
            if (is_null($default_value)) {
                return null;
            } else {
                return $default_value;
            }
        }
        if (is_array($val)) {
            $this->_form_error(__LINE__);
        }

        return $this->_sanitize($val, $type);
    }

    /**
     * get values array from requested form data.
     *
     * @param string $method      'get', 'post', 'both'
     * @param string $name        data name
     * @param string $type        data type
     *                            s:string, i:integer, f:float, n:none, b:boolean
     * @param bool   $is_required true if form must be requested data
     *
     * @return array requested form data
     */
    public function getValueArray($method, $name, $type, $is_required)
    {
        $ret = array();
        $vals = $this->_get_request_data($method, $name, $is_required);
        if (is_null($vals)) {
            return $ret;
        }
        if (!is_array($vals)) {
            $this->_form_error(__LINE__);
        }
        foreach ($vals as $num => $val) {
            $ret[$num] = $this->_sanitize($val, $type);
        }

        return $ret;
    }

    /**
     * get file from requested form data.
     *
     * @param string $name        data name
     * @param bool   $is_required true if form must be requested data
     *
     * @return array requested form data
     */
    public function getFile($name, $is_required)
    {
        $val = isset($_FILES[$name]) ? $_FILES[$name] : null;
        if (is_null($val)) {
            if ($is_required) {
                $this->_form_error(__LINE__);
            }

            return null;
        }
        if (version_compare(phpversion(), '5.4.0', '<') && get_magic_quotes_gpc()) {
            $val = array_map('stripslashes', $val);
        }
        if (isset($val['error']) && $val['error'] != 0) {
            // error occured
            return null;
        }
        if (!is_uploaded_file($val['tmp_name'])) {
            return null;
        }
        $val['name'] = $this->_convert_to_numeric_entities($val['name']);
        $fileutil = &xoonips_getutility('file');
        $val['type'] = $fileutil->get_mimetype($val['tmp_name'], $val['name']);
        if ($val['type'] === false) {
            return null;
        }

        return $val;
    }

    /**
     * get object from requested form data.
     *
     * @param string $method      'get', 'post', 'both'
     * @param string $name        data name
     * @param object &$handler    orm object handler
     * @param bool   $is_required true if form must be requested data
     *
     * @return object object of requested form data
     */
    public function &getObject($method, $name, &$handler, $is_required)
    {
        $ret = false;
        $val = $this->_get_request_data($method, $name, $is_required);
        if (is_null($val)) {
            return $ret;
        }
        if (is_array($val)) {
            $this->_form_error(__LINE__);
        }
        $ret = &$this->_createObject($val, $handler);

        return $ret;
    }

    /**
     * get object array from requested form data.
     *
     * @param string $method      'get', 'post', 'both'
     * @param string $name        data name
     * @param object &$handler    orm object handler
     * @param bool   $is_required true if form must be requested data
     *
     * @return array object array of requested form data
     */
    public function &getObjectArray($method, $name, &$handler, $is_required)
    {
        $ret = array();
        $vals = $this->_get_request_data($method, $name, $is_required);
        if (is_null($vals)) {
            return $ret;
        }
        if (!is_array($vals)) {
            $this->_form_error(__LINE__);
        }
        foreach ($vals as $num => $val) {
            $ret[$num] = &$this->_createObject($val, $handler);
        }

        return $ret;
    }

    /**
     * set requested form data.
     *
     * @param string $method 'get', 'post', 'both'
     * @param string $name   data name
     * @param string $val    value
     */
    public function set($method, $name, $val)
    {
        if (is_null($val)) {
            if ($method == 'get') {
                unset($_GET[$name]);
            } elseif ($method == 'post') {
                unset($_POST[$name]);
            } elseif ($method == 'both') {
                unset($_GET[$name]);
                unset($_POST[$name]);
            } else {
                $this->_form_error(__LINE__);
            }
        } else {
            if (version_compare(phpversion(), '5.4.0', '<') && get_magic_quotes_gpc()) {
                $val = is_array($val) ? array_map('addslashes', $val) : addslashes($val);
            }
            if ($method == 'get') {
                $_GET[$name] = $val;
            } elseif ($method == 'post') {
                $_POST[$name] = $val;
            } elseif ($method == 'both') {
                $_GET[$name] = $val;
                $_POST[$name] = $val;
            } else {
                $this->_form_error(__LINE__);
            }
        }
    }

    /**
     * copy requested form data.
     *
     * @param string $src_method 'get', 'post'
     * @param string $dst_method 'get', 'post'
     */
    public function copy($src_method, $dst_method)
    {
        $accept = array(
        'get',
        'post',
        );
        if (!in_array($src_method, $accept) || !in_array($dst_method, $accept) || $src_method == $dst_method) {
            $this->_form_error(__LINE__);
        }
        if ($src_method == 'get') {
            // copy variables $_GET to $_POST
            foreach ($_GET as $key => $val) {
                $_POST[$key] = $val;
            }
        } else {
            // copy variables $_POST to $_GET
            foreach ($_POST as $key => $val) {
                $_GET[$key] = $val;
            }
        }
    }

    /**
     * get request method.
     *
     * @return string request method 'POST' or 'GET'
     */
    public function getRequestMethod()
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    /**
     * get requested data.
     *
     * @param string $method      'get', 'post', 'both'
     * @param string $name        data name
     * @param bool   $is_required true if form must be requested data
     *
     * @return array requested form data
     */
    public function _get_request_data($method, $name, $is_required)
    {
        $val = null;
        switch ($method) {
        case 'get':
            $val = isset($_GET[$name]) ? $_GET[$name] : null;
            break;
        case 'post':
            $val = isset($_POST[$name]) ? $_POST[$name] : null;
        case 'both':
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $val = isset($_POST[$name]) ? $_POST[$name] : (isset($_GET[$name]) ? $_GET[$name] : null);
            } elseif ($_SERVER['REQUEST_METHOD'] == 'GET') {
                $val = isset($_GET[$name]) ? $_GET[$name] : (isset($_POST[$name]) ? $_POST[$name] : null);
            } else {
                $this->_form_error(__LINE__);
            }
            break;
        default:
            $this->_form_error(__LINE__);
        }
        if ($is_required && is_null($val)) {
            $this->_form_error(__LINE__);
        }

        return $val;
    }

    /**
     * sanitize value.
     *
     * @param mixed  $val  value
     * @param string $type data type
     *                     s:string, i:integer, f:float, n:none, b:boolean
     *
     * @return mixed sanitized value
     */
    public function _sanitize($val, $type)
    {
        if (is_null($val)) {
            return null;
        }
        if (version_compare(phpversion(), '5.4.0', '<') && get_magic_quotes_gpc()) {
            $val = stripslashes($val);
        }
        switch ($type) {
        case 's':
            // string
            $val = $this->_convert_to_numeric_entities(trim($val));
            break;
        case 'b':
            // boolean
            $val = (intval($val) != 0);
            break;
        case 'i':
            // integer
            if (XOONIPS_DEBUG_MODE) {
                if ($val != '' && !preg_match('/^-?[0-9]+$/', $val)) {
                    $this->_form_error(__LINE__);
                }
            }
            $val = intval($val);
            break;
        case 'f':
            // float
            if (XOONIPS_DEBUG_MODE) {
                if ($val != '' && !is_numeric($val)) {
                    $this->_form_error(__LINE__);
                }
            }
            $val = floatval($val);
            break;
        case 'n':
            // none
            break;
        default:
            $this->_form_error(__LINE__);
        }

        return $val;
    }

    /**
     * create object from values.
     *
     * @param array  $val      values
     * @param object &$handler orm object handler
     *
     * @return object created object
     */
    public function &_createObject($val, &$handler)
    {
        $pkey = $handler->getKeyName();
        $is_str_pkey = $handler->isStringPrimaryKey();
        if (isset($val[$pkey])) {
            $pkey_val = $this->_sanitize($val[$pkey], ($is_str_pkey ? 's' : 'i'));
        } else {
            $pkey_val = $is_str_pkey ? '' : 0;
        }
        if ($is_str_pkey && $pkey_val == '' || (!$is_str_pkey) && $pkey_val == 0) {
            // new object
            $obj = &$handler->create();
        } else {
            // get existing object
            $obj = &$handler->get($pkey_val);
        }
        if (!is_object($obj)) {
            $this->_form_error(__LINE__);
        }
        foreach ($obj->getKeysArray() as $key) {
            if ($key == $pkey) {
                continue;
            }
            if (isset($val[$key])) {
                switch ($obj->getDataType($key)) {
                case XOBJ_DTYPE_TXTBOX:
                case XOBJ_DTYPE_TXTAREA:
                    $type = 's';
                    break;
                case XOBJ_DTYPE_INT:
                      $type = 'i';
                    break;
                case XOBJ_DTYPE_ARRAY:
                case XOBJ_DTYPE_OTHER:
                      $this->_form_error(__LINE__);
                case XOBJ_DTYPE_BINARY:
                      $type = 'n';
                    break;
                default:
                      $this->_form_error(__LINE__);
                }
                $val[$key] = $this->_sanitize($val[$key], $type);
            } else {
                $val[$key] = null;
            }
            $obj->setVar($key, $val[$key], true);
            // not gpc
        }

        return $obj;
    }

    /**
     * convert string to numeric entities
     *  - html entities => numeric entities
     *  - 3 byte EUC => numeric entities
     *  - strip unknown character.
     *
     * @param string $val
     *
     * @return string converted string
     */
    public function _convert_to_numeric_entities($val)
    {
        $textutil = &xoonips_getutility('text');
        // convert html character entity references to numeric character references
        $val = $textutil->html_numeric_entities($val);

        // convert JIS X 0212 to numeric character reference
        if (_CHARSET == 'EUC-JP') {
            $len = strlen($val);
            $chars = array();
            $convmap = array(
            0x0,
            0xffff,
            0,
            0xffff,
            );
            for ($i = 0; $i < $len; ++$i) {
                if (ord($val[$i]) <= 127) {
                    $chars[] = $val[$i];
                } elseif (ord($val[$i]) != 0x8f) {
                    $chars[] = substr($val, $i, 2);
                    ++$i;
                } else {
                    $chars[] = mb_encode_numericentity(substr($val, $i, 3), $convmap, 'EUC-JP');
                    $i += 2;
                }
            }
            $val = implode('', $chars);
        }

        if (_CHARSET != 'UTF-8') {
            // remove bad character
            $substitute_char = mb_substitute_character();
            mb_substitute_character('none');
            $val = mb_convert_encoding(mb_convert_encoding($val, 'UTF-8', _CHARSET), _CHARSET, 'UTF-8');
            mb_substitute_character($substitute_char);
        }

        return $val;
    }

    /**
     * output error message and die script.
     *
     * @param int $line line
     */
    public function _form_error($line)
    {
        if (XOONIPS_DEBUG_MODE) {
            echo '<pre>';
            echo 'FILE: '.__FILE__.', LINE: '.$line.'<br />';
            print_r(debug_backtrace());
            echo '</pre>';
            die('illegal request');
        }
        redirect_header(XOOPS_URL.'/', 3, 'illegal request');
        exit();
    }
}
