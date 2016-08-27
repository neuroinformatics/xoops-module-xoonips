<?php
// $Revision: 1.1.4.1.2.37 $
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

if ( ! defined( 'XOOPS_ROOT_PATH' ) ) exit();

include_once dirname( __DIR__ ).'/class/base/tableobject.class.php';
include_once dirname( __DIR__ ).'/class/base/criteria.class.php';

/**
 * get xoonips version
 *
 * @access public
 * @return int version
 */
function xoonips_get_version() {
  $mydirname = basename( dirname( __DIR__ ) );
  $module_handler =& xoops_gethandler( 'module' );
  $module_obj =& $module_handler->getByDirname( $mydirname );
  if ( ! is_object( $module_obj ) ) {
    return 0;
  }
  $version = intval( $module_obj->getVar( 'version', 'n' ) );
  return $version;
}

/**
 *
 * @brief get reference of handler of xoonips
 *
 * @param[in] $module string module name
 * @param[in] $name string handler name
 * @return reference of handler or false
 */
function &xoonips_gethandler($module,$name) 
{
    static $falseVar = false;
    static $handlers;

    if (!isset($handlers["${module}_${name}"])) {
        $include_file = XOOPS_ROOT_PATH . "/modules/${module}/class/${module}_{$name}.class.php";
        if (file_exists($include_file)) {
            include_once $include_file;
        } else {
            trigger_error('file not found: ' . $include_file, E_USER_ERROR);
            return $falseVar;
        }
        if( $module == 'xoonips' ){
            $class = 'XooNIps' . str_replace(' ', '', ucwords(str_replace('_', ' ', $name))) . 'Handler';
        }else{
            $class = 'XNP' . str_replace(' ', '', ucwords(str_replace('_', ' ', substr($module,3)."_".$name))) . 'Handler';
        }
        if (class_exists($class)) {
            $handlers[$name] = new $class($GLOBALS['xoopsDB']);
        }
    }
    if (!isset($handlers[$name])) {
        trigger_error('Handler does not exist. Name: ' . $name, E_USER_ERROR);
    }
    // return result
    $falseVar = false;
    if (isset($handlers[$name])) return $handlers[$name];
    else return $falseVar;
}

/**
 *
 * @brief get handler of xoonips
 *
 * @param[in] $name handler name
 * @return XoopsTableObjectHandler
 * @retval false
 */
function &xoonips_getormhandler($module, $name) 
{
    static $falseVar = false;
    static $handlers;
    //
    if (!isset($handlers[$module . $name])) {
        $include_file = XOOPS_ROOT_PATH . "/modules/${module}/class/orm/${name}.class.php";
        if (file_exists($include_file)) {
            include_once $include_file;
        } else {
            return $falseVar;
        }
        if (strncmp('xnp', $module, 3) == 0) {
            $tok = substr($module, 3);
            $class = 'XNP'.ucfirst($tok).'Orm'.str_replace(' ', '', ucwords(str_replace('_', ' ', $name))).'Handler';
        } else {
            $class = 'XooNIpsOrm' . str_replace(' ', '', ucwords(str_replace('_', ' ', $name))) . 'Handler';
        }
        if (class_exists($class)) {
            $handlers[$module . $name] = new $class($GLOBALS['xoopsDB']);
        }
    }
    if (!isset($handlers[$module . $name])) {
        trigger_error('Handler does not exist. Class: ' . $class, E_USER_ERROR);
    }
    // return result
    if (isset($handlers[$module . $name])) return $handlers[$module . $name];
    else return $falseVar;
}

/**
 *
 * get XooNIpsItemHandler of specified itemtype
 *
 * @param[in] $module module name
 * @param[in] $name handler name
 * @return XooNIpsItemCompoHandler
 * @retval false
 */
function &xoonips_getormcompohandler($module, $name) 
{
    static $falseVar = false;
    static $handlers;
    //
    if (!isset($handlers[$module . $name])) {
        $include_file = XOOPS_ROOT_PATH . "/modules/${module}/class/${module}_compo_${name}.class.php";
        if (file_exists($include_file)) {
            include_once $include_file;
        } else {
            return $falseVar;
        }
        if (strncmp('xnp', $module, 3) == 0) {
            $tok = substr($module, 3);
            $class = 'XNP' . ucfirst($tok) . 'CompoHandler';
        } else {
            $class = 'XooNIps' . ucfirst($name) . 'CompoHandler';
        }
        if (class_exists($class)) {
            $handlers[$module . $name] = new $class($GLOBALS['xoopsDB']);
        }
    }
    if (!isset($handlers[$module . $name])) {
        trigger_error('Handler does not exist. Name: ' . $module . ' ' . $name, E_USER_ERROR);
    }
    // return result
    if (isset($handlers[$module . $name])) return $handlers[$module . $name];
    else return $falseVar;
}

/**
 * get utility instance
 *
 * @access public
 * @param string $name
 * @return object class instance
 */
function &xoonips_getutility( $name ) {
  static $instances = array();
  if ( isset( $instances[$name] ) ) {
    return $instances[$name];
  }
  // load class file
  $cname = 'XooNIpsUtility'.ucfirst( $name );
  if ( ! class_exists( $cname ) ) {
    $cpath = dirname( __DIR__ ).'/class';
    if ( ! class_exists( 'XooNIpsUtility' ) ) {
      require_once $cpath.'/base/utility.class.php';
    }
    $path = $cpath.'/utility/'.$name.'.class.php';
    require_once $path;
  }
  $instance = new $cname();
  if ( $instance->isSingleton() ) {
    $instances[$name] =& $instance;
  }
  return $instance;
}

/**
 * get xoops configs for compatibility with XOOPS Cube Legacy 2.1
 * @access public
 * @return array xoops configs
 */
function &xoonips_get_xoops_configs( $category ) {
  static $cache_configs = array();
  if ( isset( $cache_configs[$category] ) ) {
    return $cache_configs[$category];
  }
  $config_handler =& xoops_gethandler( 'config' );
  $configs = $config_handler->getConfigsByCat( $category ); // copy
  if ( defined( 'XOOPS_CUBE_LEGACY' ) ) {
    // for XOOPS Cube Legacy 2.1
    switch ( $category ) {
    case XOOPS_CONF:
      // -----------------------------------------------------------------
      // missing configs:
      //   banners, root_path, usercookie, xoops_url
      // duplicated configs in 'user' module:
      //   avatar_minposts, maxuname, sslloginlink, sslpost_name, use_ssl
      // -----------------------------------------------------------------
      // 'xoops_url' and 'root_path' are DEPRECATED since 2.0.
      $configs['xoops_url'] = XOOPS_URL;
      $configs['root_path'] = XOOPS_ROOT_PATH.'/';
      // 'banners' found in 'legacyRender' module
      $tmp =& $config_handler->getConfigsByDirname( 'legacyRender' );
      $configs['banners'] = $tmp['banners'];
      // 'usercookie' found in 'user' module
      $tmp =& $config_handler->getConfigsByDirname( 'user' );
      $configs['usercookie'] = $tmp['usercookie'];
      // override duplicated configs in 'user' module
      $keys = array( 'avatar_minposts', 'maxuname', 'sslloginlink', 'sslpost_name', 'use_ssl' );
      foreach ( $keys as $key ) {
        $configs[$key] = $tmp[$key];
      }
      break;
    case XOOPS_CONF_USER:
      // all 2.0 compatible configs available in 'user' module
      // added configs from 2.1 'user' module are:
      //   self_delete_confirm, sslloginlink, sslpost_name, use_ssl, usercookie
      $configs = $config_handler->getConfigsByDirname( 'user' ); // copy
      break;
    case XOOPS_CONF_METAFOOTER:
      // all 2.0 compatible configs available in 'legacyRender' module
      // added configs from 2.1 'legacyRender' module are:
      //   banners 
      $configs = $config_handler->getConfigsByDirname( 'legacyRender' ); // copy
      break;
    case XOOPS_CONF_CENSOR:
      // same config keys
      break;
    case XOOPS_CONF_SEARCH:
      // same config keys
      break;
    case XOOPS_CONF_MAILER:
      // same config keys
      break;
    }
  }
  $cache_configs[$category] =& $configs;
  return $cache_configs[$category];
}

function ISO8601toUnixTimestamp($str) 
{
    if (preg_match('/^([0-9]{4})(-?([0-9]{2})(-?([0-9]{2})(T([0-9]{2}):([0-9]{2})(:([0-9]{2}))?(Z|([-+])([0-9]{2})([0-9]{2}))?)?)?)?$/', $str, $match) == 1) {
        // $match[?]
        // $match[0]  : input($str)
        // $match[1]  : year
        // $match[2]  :
        // $match[3]  : month
        // $match[4]  :
        // $match[5]  : day of month
        // $match[6]  :
        // $match[7]  : hour
        // $match[8]  : minute
        // $match[9]  :
        // $match[10] : second
        // time difference below(regexp Z|[-+][0-9]{2}:[0-9]{2})
        // $match[11] : 'Z' or ''
        // $match[12] : +|-
        // $match[13] : hour of time difference
        // $match[14] : minute of time difference
        if (!isset($match[3])) $match[3] = '01';
        if (!isset($match[5])) $match[5] = '01';
        if (!isset($match[7])) $match[7] = '00';
        if (!isset($match[8])) $match[8] = '00';
        if (!isset($match[10]) || $match[10] == "") $match[10] = '00';
        $tm = gmmktime($match[7], $match[8], $match[10], $match[3], $match[5], $match[1]);
        if (false === $tm || -1 == $tm && version_compare(phpversion() , "5.1.0", "<")) return false; // gmmktime failed.
        // hh:mm:ss must be in 00:00:00 - 24:00:00
        if ($match[10] >= 60) return false;
        if ($match[8] >= 60) return false;
        if ($match[7] > 24 || $match[7] == 24 && ($match[8] != 0 || $match[10] != 0)) return false;
        // mm and dd must not overflow
        if (gmdate('Ymd', gmmktime(0, 0, 0, $match[3], $match[5], $match[1])) != $match[1] . $match[3] . $match[5]) return false;
        //correct a time difference to GMT
        if (isset($match[11]) && isset($match[12]) && isset($match[13]) && isset($match[14])) {
            if ($match[11] != 'Z' && $match[12] == '-') {
                $tm = $tm+($match[13]*3600+$match[14]*60);
            } else if (isset($match[12]) && $match[11] != 'Z' && $match[12] == '+') {
                $tm = $tm-($match[13]*3600+$match[14]*60);
            }
        }
    } else if (preg_match('/^([0-9]{4})(-W([0-5][0-9]))(-([1-7]))$/', $str, $match) == 1) {
        // Week dates format
        $y = $match[1];
        $w = $match[3];
        $d = $match[5];
        $tm = gmmktime(0, 0, 0, 1, 1, $match[1])+(($w-1) *7+$d-getDayOfWeek($y,1,1)) *86400;
    } else if (preg_match('/^([0-9]{4})(-?([0-3][0-9]{2}))$/', $str, $match) == 1) {
        // Ordinal dates format
        $tm = gmmktime(0, 0, 0, 1, 1, $match[1]) +($match[3]-1) *86400;
    } else return false;
    return $tm;
}
function getDayOfWeek($year, $month, $day) 
{
    return gmdate("w", gmmktime(0,0,0,$month,$day,$year));
}

/**
 * get server character set.
 * XOOPS character set 'ISO-8859-1' is treated as Windows-1252.
 * @return character set name
 */
function xoonips_get_server_charset()
{
  if ( _CHARSET == 'ISO-8859-1' )
    return 'Windows-1252';
  else
    return _CHARSET;
}

/**
 * get unicode character conversion map.
 * @return conversion map for mb_decode_numericentity
 */
function xoonips_get_conversion_map()
{
  return array(0, 0x10ffff, 0, 0x1fffff);
}

/**
 * get unicode character conversion map to ascii.
 * useful to convert UTF-8 to ASCII + numeric character entity.
 * @return conversion map
 */
function xoonips_get_conversion_map_to_ascii()
{
  return array(0x80, 0x10ffff, 0, 0x1fffff);
}

/**
 * 
 * deny guest access and redirect
 * 
 * @param $url string redurect URL(default is modules/xoonips/user.php)
 * @param $msg string message of redirect(default is _MD_XOONIPS_ITEM_FORBIDDEN)
 */
function xoonips_deny_guest_access($url=null, $msg=_MD_XOONIPS_ITEM_FORBIDDEN)
{
    global $xoopsUser;
    if( !$xoopsUser ) redirect_header( is_null($url) ? XOOPS_URL.'/modules/xoonips/user.php' : $url, 3, $msg );
}

/**
 *
 * @brief convert lock type to string
 *
 * @param[in] $lock_type return value of XooNIpsItemLock::getLockType()
 * @return string represents lock type
 */
function xoonips_get_lock_type_string( $lock_type )
{
    switch ( $lock_type ){
    case XOONIPS_LOCK_TYPE_NOT_LOCKED:
        return _MD_XOONIPS_LOCK_TYPE_STRING_NOT_LOCKED;
    case XOONIPS_LOCK_TYPE_CERTIFY_REQUEST:
        return _MD_XOONIPS_LOCK_TYPE_STRING_CERTIFY_REQUEST;
    case XOONIPS_LOCK_TYPE_TRANSFER_REQUEST:
        return _MD_XOONIPS_LOCK_TYPE_STRING_TRANSFER_REQUEST;
    case XOONIPS_LOCK_TYPE_PUBLICATION_GROUP_INDEX:
        return _MD_XOONIPS_LOCK_TYPE_STRING_PUBLICATION_GROUP_INDEX;
    }
    return "(internal error: unsupported lock type. lock_type=$lock_type)";
}


function xoonips_certify_item( $uid, $item_id, $index_id )
{
    $index_item_link_handler =& 
        xoonips_getormhandler('xoonips', 'index_item_link');
    if ( !$index_item_link_handler->getPerm($index_id, $item_id, $uid, 'accept') ){
        return false;
    }
    $index_item_link = 
        $index_item_link_handler->getByIndexIdAndItemId( $index_id, $item_id );
    $index_item_link->set('certify_state', CERTIFIED );
    if ( false == $index_item_link_handler->insert($index_item_link) ){
        return false;
    }
    $event_log_handler =& xoonips_getormhandler('xoonips', 'event_log');
    $event_log_handler->recordCertifyItemEvent( $item_id, $index_id );
    $item_basic_handler =& xoonips_getormhandler('xoonips', 'item_basic');
    $item_basic_handler->unlockItemAndIndexes( $item_id, $index_id );
    
    $item_status_handler =& xoonips_getormhandler( 'xoonips','item_status' );
    $item_status_handler->updateItemStatus($item_id);
    
    return true;
}

function xoonips_reject_item( $uid, $item_id, $index_id )
{
    $index_item_link_handler =& 
        xoonips_getormhandler('xoonips', 'index_item_link');
    if ( !$index_item_link_handler->getPerm($index_id, $item_id, $uid, 'reject') ){
        return false;
    }
    $index_item_link = 
        $index_item_link_handler->getByIndexIdAndItemId( $index_id, $item_id );
    if ( false == $index_item_link_handler->delete($index_item_link) ){
        return false;
    }
    $event_log_handler =& xoonips_getormhandler('xoonips', 'event_log');
    $event_log_handler->recordRejectItemEvent( $item_id, $index_id );
    $item_basic_handler =& xoonips_getormhandler('xoonips', 'item_basic');
    $item_basic_handler->unlockItemAndIndexes( $item_id, $index_id );
    
    $item_status_handler =& xoonips_getormhandler( 'xoonips','item_status' );
    $item_status_handler->updateItemStatus($item_id);
    
    return true;
}

function xoonips_withdraw_item( $uid, $item_id, $index_id )
{
    $index_item_link_handler =& 
        xoonips_getormhandler('xoonips', 'index_item_link');
    if ( !$index_item_link_handler->getPerm($index_id, $item_id, $uid, 'withdraw') ){
        return false;
    }
    $index_item_link =
        $index_item_link_handler->getByIndexIdAndItemId( $index_id, $item_id );
    if ( false == $index_item_link_handler->delete($index_item_link) ){
        return false;
    }
    $item_show_handler =& xoonips_getormhandler('xoonips', 'item_show');
    $item_show_handler->deleteAll( new Criteria('item_id', $item_id) );
    $item_status_handler =& xoonips_getormhandler('xoonips', 'item_status');
    
    $item_show_handler->deleteAll( new Criteria('item_id', $item_id) );
    
    $item_status_handler =& xoonips_getormhandler( 'xoonips','item_status' );
    $item_status_handler->updateItemStatus($item_id);
    
    return true;
}

function xoonips_get_transfer_request_item_detail_url( $item_id )
{
    return XOOPS_URL . '/modules/xoonips/transfer_item.php?'
        . 'action=detail_item&item_id='
        . intval( $item_id );
}

/**
 * 
 * allow only post method access
 * 
 */
function xoonips_allow_post_method()
{
    xoonips_validate_request( $_SERVER['REQUEST_METHOD'] == 'POST' );
}

/**
 * 
 * allow only get method access
 * 
 */
function xoonips_allow_get_method()
{
    xoonips_validate_request( $_SERVER['REQUEST_METHOD'] == 'GET' );
}

/**
 * 
 * allow only post and get method access
 * 
 */
function xoonips_allow_both_method()
{
    xoonips_validate_request( $_SERVER['REQUEST_METHOD'] == 'GET'
                              || $_SERVER['REQUEST_METHOD'] == 'POST');
}

/**
 * 
 * die if given false
 * 
 */
function xoonips_validate_request( $bool ){
    if( !$bool ){
        die( 'illegal request' );
    }
}

/**
 * Finds whether a USER can export. 
 * It regards $xoopsUser as USER.
 * @return bool true if export is permitted for USER.
 * 
 */
function xoonips_is_user_export_enabled(){
    global $xoopsUser;
    
    if( !$xoopsUser ){
        return false;//guest can not export
    }
    
    if( $xoopsUser -> isAdmin() ){
        return true;//admin can always export
    }
    
    $xmember_handler =& xoonips_gethandler( 'xoonips', 'member' );
    if( $xmember_handler->isModerator( $xoopsUser -> getVar('uid') ) ){
        return true; //moderator can always export
    }
    
    $xoonips_config_handler = &xoonips_getormhandler('xoonips', 'config');
    $export_enabled = $xoonips_config_handler->getValue( 'export_enabled' );
    if( is_null( $export_enabled ) ) {
        return false;
    }
    
    //see xoonips_config setting for other users
    return $export_enabled == 'on';
}

/**
 * get multiple field array from post data
 * - table name must be "{$module}_{$name}"
 * - table must have columns "{$name}" and "{$name}_order"
 * - e.g. when 'xnpmodel_creator' table has columns 'creator' and 'creator_order', 
 * $module is 'xnpmodel', $name is 'creator'
 * 
 * @access public
 * @param string $module module name of field
 * @param string $name handler name 
 * @return array
 * 
 */
function xoonips_get_multi_field_array_from_post($module, $name)
{
    $formdata =& xoonips_getutility( 'formdata' );
    $result=array();

    $field_handler =& xoonips_getormhandler( $module, $name );
    $objs =& $formdata->getObjectArray( 'post', $field_handler->getTableName(), $field_handler, false );

    foreach($objs as $field){
        $result[]=$field->getVarArray('s');
    }
    
    return $result;
}

/**
 * find that whether the length of field value of ormObjects is longer than DB column length.
 * - return true when at least one field value is too long
 * - regards $name as field name of ormObjects.
 * @param XooNIpsTableObject[] orm to get template vars
 * @param string $module module name for xoonips_getormhandler
 * @param string $name name for xoonips_getormhandler
 * @see xoonips_getormhandler
 */
function xoonips_is_multiple_field_too_long($ormObjects, $module, $name)
{
    $field_handler =& xoonips_getormhandler( $module, $name );
    $lengths = xnpGetColumnLengths( $field_handler->getTableName() );
    foreach($ormObjects as $orm){
        list( $within, $without )=xnpTrimString( $orm->get($name), $lengths[$name], _CHARSET );
        if( $without ){
            return true;
        }
    }
    return false;
}

/**
 * return template variables for 'xoonips_multiple_filed_confirm' template
 * @param XooNIpsTableObject[] orm to get template vars
 * @param string $field_name orm field name that is used as value to show.
 */
function xoonips_get_multiple_field_template_vars($ormObjects, $module, $name)
{
    $field_handler =& xoonips_getormhandler( $module, $name );
    $lengths = xnpGetColumnLengths( $field_handler->getTableName() );
    
    $vars = array(
      'table_name' => $field_handler->getTableName(),
      'name' => array(
        'primary_key' => $field_handler->getKeyName(),
        'text' => $name,
        'order' => $name.'_order',
      ),
      'objects'=>array()
    );
    foreach($ormObjects as $orm){
        list( $within, $without )=xnpTrimString( $orm->getVar($name, 's'), $lengths[$name], _CHARSET );
        $vars['objects'][]=array(
            'primary_key'=>array(
                'name'=>$field_handler->getKeyName(),
                'value'=>$orm->getVar($field_handler->getKeyName(), 's')),
            'text'=>array(
                'name'=>$name,
                'within'=> empty($within) ? '' : $within,
                'without'=> empty($without) ? '' : $without,
                'value'=>$orm->getVar($name, 's')),
            'order'=>array(
                'name'=>"{$name}_order",
                'value'=>$orm->get("{$name}_order")));
    }
    $vars['num'] = count( $vars['objects'] );
    return $vars;
}

function xoonips_get_orm_from_post($module, $name)
{
    $formdata =& xoonips_getutility( 'formdata' );
    $result=array();
    $field_handler =& xoonips_getormhandler( $module, $name );
    $objs =& $formdata->getObjectArray( 'post', $field_handler->getTableName(), $field_handler, false );

    return $objs; 
}

/**
 * compare objects
 *
 * @param array &$objs1
 * @param array &$objs2
 * @return bool true if $objs1 and $objs2 are all same objects
 */
function xoonips_is_same_objects( &$objs1, &$objs2 ) {
  if ( count( $objs1 ) != count( $objs2 ) ) {
    return false;
  }
  $sorted_objs1 = array();
  $sorted_objs2 = array();
  $matches = array();
  foreach ( $objs1 as $num1 => $obj1 ) {
    $found = false;
    foreach ( $objs2 as $num2 => $obj2 ) {
      if ( in_array( $num2, $matches ) ) {
        continue;
      }
      if ( $obj1->equals( $obj2 ) ) {
        $matches[] = $num2;
        $found = true;
        break;
      }
    }
    if ( ! $found ) {
      return false;
    }
  }
  return true;
}

/**
 * get creative commons license
 *
 * @access public
 * @param int $cc_commercial_use
 * @param int $cc_modification
 * @param float $version
 * @param string $region
 * @return string rendlerd creative commons licnese
 */
function xoonips_get_cc_license( $cc_commercial_use, $cc_modification, $version, $region ) {
  static $cc_condition_map = array( 
    '00' => 'BY-NC-ND',
    '01' => 'BY-NC-SA',
    '02' => 'BY-NC',
    '10' => 'BY-ND',
    '11' => 'BY-SA',
    '12' => 'BY',
  );
  static $cc_region_map = array(
    // php-indent: disable
    'INTERNATIONAL' => array( '40' ),
    // php-indent: enable
  );
  static $cc_cache = array();
  $condtion = sprintf( '%u%u', $cc_commercial_use, $cc_modification );
  $region = strtoupper( $region );
  $version = sprintf( '%u', $version * 10 );
  if ( ! isset( $cc_condition_map[$condtion] ) ) {
    // unknown condtion
    return false;
  }
  $condtion = $cc_condition_map[$condtion];
  if ( ! isset( $cc_region_map[$region] ) ) {
    // unknown region
    return false;
  }
  if ( ! in_array( $version, $cc_region_map[$region] ) ) {
    // unkown version
    return false;
  }
  if ( isset( $cc_cache[$region][$version][$condtion] ) ) {
    return $cc_cache[$region][$version][$condtion];
  }
  $fname = sprintf( 'CC-%s-%s-%s.html', $condtion, $version, $region );
  $fpath = __DIR__.'/creativecommons/'.$fname;
  if ( ! file_exists( $fpath ) ) {
    // file not found
    return false;
  }
  $cc_html = @file_get_contents( $fpath );
  if ( $cc_html === false ) {
    // failed to read file
    return false;
  }
  $cc_cache[$region][$version][$condtion] = $cc_html;
  return $cc_html;
}

?>
