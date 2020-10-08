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

/**
 * @brief Class that transform associative array of XML-RPC argument(parsed by XoopsXmlRpcParser) to DataObject
 */
class XooNIpsXmlRpcTransformElement
{
    /**
     * @brief add XooNIpsXmlRpcTransformElement to transform
     *
     * @param string                        $field field name correspond to $child
     * @param XooNIpsXmlRpcTransformElement $child child element
     *
     * @return XooNIpsXmlRpcTransformEelemnt $child object to transform array
     */
    public function add($field, &$child)
    {
        if (isset($field) && !empty($field) && isset($child) && (is_subclass_of($child, 'XooNIpsXmlRpcTransformElement') || 'XooNIpsXmlRpcTransformElement' == get_class($child))) {
            $this->childs[$field] = $child;
        }
    }
}

/**
 * @brief XmlRpcTransform composer class
 *
 * XmlRpcTransformCompo is composed by one or more XmlRpcTransform.
 * It has getObject method.
 */
class XooNIpsXmlRpcTransformCompo extends XooNIpsXmlRpcTransformElement
{
    //    var $module = null;
    //    var $name = null;
    public $handlers = null;
    public $iteminfo = null;

    /**
     * @param string $module
     */
    public function __construct($module = null)
    {
        if (isset($module)) {
            $this->__init($module);
        }
    }

    /**
     * @brief initialize module and name for xoonips_getormcompohandler
     * These parameter is used in getObject to create object to be returned.
     *
     * @param string $module
     */
    public function __init($module)
    {
        //        $this->module = $module;
        //        $this->name = $name;
        if (is_null($this->iteminfo)) {
            require XOOPS_ROOT_PATH.'/modules/'.$module.'/iteminfo.php';
            $this->iteminfo = &$iteminfo;
        }
        //
        // create and add orm accrding to $this -> iteminfo['orm']
        foreach ($this->iteminfo['orm'] as $orm) {
            $this->handlers[$orm['field']] = &xoonips_getormhandler($orm['module'], $orm['name']);
        }
    }

    /**
     * @brief check that required fields are filled.
     * empty string('') is seems 'not filled'. Multiple variable must have at least one non-empty value.
     *
     * @param[in] $in_array associative array of item
     * @param[out] $missing array of string of missing field name
     * @retval ture filled
     * @retval false not filled
     */
    public function isFilledRequired($in_array, &$missing)
    {
        if (!isset($missing) || !is_array($missing)) {
            $missing = array();
        }
        foreach ($this->iteminfo['io']['xmlrpc']['item'] as $input) {
            if (!isset($input['xmlrpc']['required']) || !$input['xmlrpc']['required']) {
                continue;
            } // no need to check required
            $value = null; //target value
            $name = null; //name of variable
            $is_multiple = isset($input['xmlrpc']['multiple']) ? $input['xmlrpc']['multiple'] : false;
            if ('detail_field' == $input['xmlrpc']['field'][0]) { // case of detail_field
                $name = implode('.', $input['xmlrpc']['field']);
                if (!empty($in_array['detail_field'])) {
                    foreach ($in_array['detail_field'] as $field) {
                        if (trim($field['name']) == $input['xmlrpc']['field'][1]) {
                            $value = trim($field['value']);
                            break;
                        }
                    }
                }
            } else { // case of not detail_field
                $name = $input['xmlrpc']['field'][0];
                if (isset($in_array[$name])) {
                    $value = $in_array[$name];
                }
            }
            //if( is_null( $value ) || is_null( $name ) ) continue;
            if ($is_multiple) {
                if (0 == count($value)) {
                    // missing if multiple variable has no values
                    $missing[] = $name;
                } else {
                    // set missing if empty value is found
                    foreach ($value as $val) {
                        if (!empty($val)) {
                            break;
                        }
                        $missing[] = $name;
                        break;
                    }
                }
            } elseif (empty($value)) {
                $missing[] = $name;
            }
        }

        return 0 == count($missing);
    }

    /**
     * @brief check that multiple fields has array and non-multiple field has single value
     * don't check a variable if its value is not specified.
     *
     * @param[in] $in_array associative array of item
     * @param[out] $fields array of string of field name
     * @retval ture filled
     * @retval false not filled
     */
    public function checkMultipleFields($in_array, &$fields)
    {
        if (!isset($fields) || !is_array($fields)) {
            $fields = array();
        }
        foreach ($this->iteminfo['io']['xmlrpc']['item'] as $input) {
            $value = null; //target value
            $name = null; //name of variable
            $is_multiple = isset($input['xmlrpc']['multiple']) ? $input['xmlrpc']['multiple'] : false;
            if ('detail_field' == $input['xmlrpc']['field'][0]) { // case of detail_field
                if ($is_multiple) {
                    $value = array();
                }
                $name = implode('.', $input['xmlrpc']['field']);
                foreach ($in_array['detail_field'] as $field) {
                    if (trim($field['name']) == $input['xmlrpc']['field'][1]) {
                        if (isset($value) && !is_array($value)) {
                            $value = array($value);
                        }
                        if (is_array($value)) {
                            $value[] = $field['value'];
                        } else {
                            $value = $field['value'];
                        }
                    }
                }
            } else { // case of not detail_field
                $name = $input['xmlrpc']['field'][0];
                if (isset($in_array[$name])) {
                    $value = $in_array[$name];
                }
            }
            // skip variable if it is not specified a value
            if (!isset($value)) {
                continue;
            }

            if ($is_multiple) {
                if (!is_array($value)) {
                    // error if multiple variable doesn't have an array
                    $fields[] = $name;
                }
            } elseif (is_array($value)) {
                // error if non-multiple variable has an array
                $fields[] = $name;
            }
        }

        return 0 == count($fields);
    }

    /**
     * @brief check that each field has valid value.
     *
     * @param[in] $in_array associative array of item
     * @param[out] $error XooNIpsError to add error
     * @retval ture valid
     * @retval false some invalid fields
     */
    public function checkFields($in_array, &$error)
    {
        $is_valid = true;
        foreach ($this->iteminfo['io']['xmlrpc']['item'] as $input) {
            if (!isset($input['xmlrpc']['options'])) {
                continue;
            }
            $value = null; //target value
            $name = null; //name of variable
            $is_multiple = isset($input['xmlrpc']['multiple']) ? $input['xmlrpc']['multiple'] : false;
            if ('detail_field' == $input['xmlrpc']['field'][0]) { // case of detail_field
                if ($is_multiple) {
                    $value = array();
                }
                $name = implode('.', $input['xmlrpc']['field']);
                foreach ($in_array['detail_field'] as $field) {
                    if (trim($field['name']) == $input['xmlrpc']['field'][1]) {
                        if (isset($value) && !is_array($value)) {
                            $value = array($value);
                        }
                        if (is_array($value)) {
                            $value[] = $field['value'];
                        } else {
                            $value = $field['value'];
                        }
                    }
                }
            } else { // case of not detail_field
                $name = $input['xmlrpc']['field'][0];
                if (isset($in_array[$name])) {
                    $value = $in_array[$name];
                }
            }

            $valid_option_values = array();
            foreach ($input['xmlrpc']['options'] as $option) {
                $valid_option_values[] = $option['option'];
            }

            if ($is_multiple) {
                // set missing if empty value is found
                foreach ($value as $val) {
                    if (!in_array($val, $valid_option_values)) {
                        $error->add(XNPERR_INVALID_PARAM, $name);
                        $is_valid = false;
                        break;
                    }
                }
            } elseif (!in_array($value, $valid_option_values)) {
                $error->add(XNPERR_INVALID_PARAM, $name);
                $is_valid = false;
            }
        }

        return $is_valid;
    }

    /**
     * get orm composer object( subclass of XooNIpsItemCompo ) from associative array
     * see also {@link $this -> iteminfo}.
     *
     * @param array associative array of XML-RPC argument
     *
     * @return XooNIpsTableObject reference of subclass of XooNIpsTableObject
     */
    public function getObject($in_array)
    {
        $handler = &xoonips_getormcompohandler($this->iteminfo['ormcompo']['module'], $this->iteminfo['ormcompo']['name']);
        $this->iteminfo = $handler->getIteminfo();
        //
        // get primary key
        $primary_id = false;
        foreach ($this->iteminfo['io']['xmlrpc']['item'] as $input) {
            if ($input['orm']['field'][0]['orm'] == $this->iteminfo['ormcompo']['primary_orm'] && $input['orm']['field'][0]['field'] == $this->iteminfo['ormcompo']['primary_key']) {
                $primary_id = isset($in_array[$input['xmlrpc']['field'][0]]) ? $in_array[$input['xmlrpc']['field'][0]] : false;
                break;
            }
        }
        if (false == $primary_id) {
            // create ormcompo
            $obj = $handler->create();
        } else {
            // get object from database
            $obj = $handler->get($primary_id); //var_dump('obj=get(primary_id):', $obj);
            if (!$obj) {
                $obj = $handler->create();
            }
        }
        $primary_orm = $obj->getVar($this->iteminfo['ormcompo']['primary_orm']);
        //
        // apply all input rule in iteminfo
        $unicode = &xoonips_getutility('unicode');
        foreach ($this->iteminfo['io']['xmlrpc']['item'] as $input) {
            $in_field = null;
            $in_var = array();
            $out_var = array();
            //
            // get reference of orm's information corresnponds to $input
            $orminfo = null;
            foreach ($this->iteminfo['orm'] as $orm) {
                if ($orm['field'] == $input['orm']['field'][0]['orm']) {
                    $orminfo = $orm;
                    break;
                }
            }
            //
            // get variable in $in_var according to $this -> iteminfo['input'] rule
            $is_multiple = isset($input['xmlrpc']['multiple']) ? $input['xmlrpc']['multiple'] : false;
            if ('detail_field' == $input['xmlrpc']['field'][0]) {
                if ($is_multiple) {
                    $in_field = array();
                }
                foreach ($in_array['detail_field'] as $field) {
                    if (trim($field['name']) == $input['xmlrpc']['field'][1]) {
                        if (isset($in_field) && !is_array($in_field)) {
                            $in_field = array($in_field);
                        }
                        if (is_array($in_field)) {
                            $in_field[] = $field['value'];
                        } else {
                            $in_field = trim($field['value']);
                        }
                    }
                }
            } else {
                $in_field = $in_array;
                foreach ($input['xmlrpc']['field'] as $field) {
                    if (array_key_exists($field, $in_field)) {
                        $in_field = $in_field[$field];
                    } else {
                        // set empty array if $field is not given in $in_array
                        $in_field = array();
                        break;
                    }
                }
            }
            //
            // set value to orm variable
            if ($orminfo['multiple']) {
                //
                // get handler of orm to set variable
                $handler = &$this->handlers[$input['orm']['field'][0]['orm']];

                // prepare orm array
                $foreign_key = $orminfo['foreign_key'];
                $cri = new CriteriaCompo(new Criteria($foreign_key, $primary_orm->get($this->iteminfo['ormcompo']['primary_key'])));
                $var_objs = &$handler->getObjects($cri);
                array_splice($var_objs, count($in_field)); //delete redundant element
                while (count($var_objs) < count($in_field)) {
                    $var_objs[] = $handler->create();
                }

                $pos = 0;
                $array = array();
                foreach ($in_field as $v) {
                    if (isset($array[$pos])) {
                        $var_obj = $array[$pos];
                    } else {
                        $var_obj = $handler->create();
                    }

                    // convert to numeric reference
                    if (is_string($v)) {
                        $in = $v;
                        $v = $unicode->decode_utf8($v, xoonips_get_server_charset(), 'h');
                    }

                    // evaluate
                    $in_var = array($v);
                    $out_var = array();
                    $context = array('position' => $pos);
                    eval(isset($input['eval']['xmlrpc2orm']) ? $input['eval']['xmlrpc2orm'] : '$out_var[0] = $in_var[0];');

                    for ($i = 0; $i < count($input['orm']['field']); ++$i) {
                        if (isset($out_var[$i])) {
                            $var_objs[$pos]->setVar($input['orm']['field'][$i]['field'], $out_var[$i], true);
                        } else {
                            $var_objs[$pos]->setDefault($input['orm']['field'][$i]['field']);
                        }
                    }
                    $array[$pos] = $var_objs[$pos];
                    ++$pos;
                }
                $obj->setVar($input['orm']['field'][0]['orm'], $array);
            } else {
                // convert to numeric reference
                if (is_string($in_field)) {
                    $in_field = $unicode->decode_utf8($in_field, xoonips_get_server_charset(), 'h');
                }

                //
                // evaluate
                $in_var = array($in_field);
                $out_var = array();
                eval(isset($input['eval']['xmlrpc2orm']) ? $input['eval']['xmlrpc2orm'] : '$out_var[0] = $in_var[0];');
                $i = 0;
                foreach ($input['orm']['field'] as $field) {
                    $orm = $obj->getVar($field['orm']);
                    if (!isset($out_var[$i])) {
                        //this field musn't be transformed XML to ORM
                        continue;
                    } elseif (0 == strlen($out_var[$i])) {
                        $orm->setDefault($field['field']);
                    } else {
                        $orm->setVar($field['field'], $out_var[$i], true);
                    }
                    $obj->setVar($field['orm'], $orm);
                    ++$i;
                }
            }
        }

        return $obj;
    }
}

/**
 * create XooNIpxXmlRpcTransform.
 */
class XooNIpsXmlRpcTransformFactory
{
    public function __construct()
    {
    }

    /**
     * return XooNIpsLogicFactory instance.
     *
     * @return XooNIpsXmlRpcTransformFactory
     */
    public static function &getInstance()
    {
        static $singleton = null;
        if (!isset($singleton)) {
            $singleton = new self();
        }

        return $singleton;
    }

    /**
     * return XooNIpsLogic corresponding to $logic.
     *
     * @param string $module module name
     * @param string $name   name of Transform
     * @retval XooNIpsXmlRpcTransform corresponding to $module and $name
     * @retval false unknown logic
     */
    public function &create($module, $name)
    {
        static $falseVar = false;
        $logic = null;

        $name = trim($name);
        if (false !== strstr($name, '..')) {
            return $falseVar;
        }
        $include_file = XOOPS_ROOT_PATH."/modules/{$module}/class/xmlrpc/xmlrpctransform".strtolower($name).'.class.php';
        if (file_exists($include_file)) {
            require_once $include_file;
        } else {
            return $falseVar;
        }

        if (0 == strncmp('xnp', $module, 3)) {
            $tok = substr($module, 3);
            $class = 'XNP'.ucfirst($tok).'XmlRpcTransform'.str_replace(' ', '', ucwords(str_replace('_', ' ', $name)));
        } else {
            $class = 'XooNIpsXmlRpcTransform'.str_replace(' ', '', ucwords(str_replace('_', ' ', $name)));
        }
        if (class_exists($class)) {
            $logic = new $class();
        }

        if (!isset($logic)) {
            trigger_error('Handler does not exist. Name: '.$name, E_USER_ERROR);
        }
        // return result
        if (isset($logic)) {
            return $logic;
        } else {
            return $falseVar;
        }
    }
}

/**
 * create XooNIpxXmlRpcTransformCompo.
 */
class XooNIpsXmlRpcTransformCompoFactory
{
    public function __construct()
    {
    }

    /**
     * return XooNIpsLogicFactory instance.
     *
     * @return XooNIpsXmlRpcTransformCompoFactory
     */
    public static function &getInstance()
    {
        static $singleton = null;
        if (!isset($singleton)) {
            $singleton = new self();
        }

        return $singleton;
    }

    /**
     * return XooNIpsLogic corresponding to $logic.
     *
     * @param string $module module name
     * @retval XooNIpsXmlRpcTransformCompo corresponding to $module and $name
     * @retval false unknown logic
     */
    public function &create($module)
    {
        static $falseVar = false;
        $compo = null;

        $module = trim($module);
        $include_file = XOOPS_ROOT_PATH."/modules/{$module}/class/xmlrpc/xmlrpctransformcompo.class.php";
        if (file_exists($include_file)) {
            require_once $include_file;
        }

        if (0 == strncmp('xnp', $module, 3)) {
            $tok = substr($module, 3);
            $class = 'XNP'.ucfirst($tok).'XmlRpcTransformCompo';
        } else {
            return $falseVar;
        }
        if (class_exists($class)) {
            $compo = new $class();
        } else {
            //use XooNIpsXmlRpcTransformCompo if item type specific class is not eixsts
            $compo = new XooNIpsXmlRpcTransformCompo($module);
        }

        if (!isset($compo)) {
            trigger_error('Handler does not exist. Name: '.$module, E_USER_ERROR);
        }
        // return result
        if (isset($compo)) {
            return $compo;
        } else {
            return $falseVar;
        }
    }
}
