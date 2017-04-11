<?php

// $Revision: 1.1.4.1.2.15 $
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

include '../condefs.php';
include '../include/functions.php';

// initialize xoonips session
$xsession_handler = &xoonips_getormhandler('xoonips', 'session');
$uid = is_object($xoopsUser) ? $xoopsUser->getVar('uid', 'n') : UID_GUEST;
$xsession_handler->initSession($uid);
$xsession_handler->validateUser($uid, true);
unset($uid);
unset($xsession_handler);

// create class instance of language manager
$langman = &xoonips_getutility('languagemanager');

// load modinfo.php resouce
$langman->read('modinfo.php');

function xoonips_admin_initialize($myfile, $preference, $pages)
{
    global $xoonips_admin;
    $xoonips_admin = array();
    $xoonips_admin['mydirname'] = basename(dirname(dirname($myfile)));
    $xoonips_admin['mod_url'] = XOOPS_URL.'/modules/'.$xoonips_admin['mydirname'];
    $xoonips_admin['mod_path'] = XOOPS_ROOT_PATH.'/modules/'.$xoonips_admin['mydirname'];
    $xoonips_admin['admin_url'] = $xoonips_admin['mod_url'].'/admin';
    $xoonips_admin['admin_path'] = $xoonips_admin['mod_path'].'/admin';
    $xoonips_admin['myfile_url'] = $xoonips_admin['admin_url'].'/'.basename($myfile);
  // select page and action
  $formdata = &xoonips_getutility('formdata');
    $page = $formdata->getValue('get', 'page', 's', false, 'main');
    if (!preg_match('/^[a-z]+$/', $page)) {
        die('illegal request');
    }
    $actions = &$pages[$page];
    $action = $formdata->getValue('both', 'action', 's', false, 'default');
    $method = strtolower($formdata->getRequestMethod());
    if (!preg_match('/^[a-z]+$/', $action)) {
        die('illegal request');
    }
    if ($action != 'default' && (!isset($actions[$method]) || !in_array($action, $actions[$method]))) {
        die('illegal request');
    }
    $xoonips_admin['myaction_path'] = 'actions/'.$preference.'_'.$page.'_'.$action.'.php';
    if (!file_exists($xoonips_admin['myaction_path'])) {
        die('illegal request');
    }
    $xoonips_admin['mypage'] = $page;
    $xoonips_admin['myaction'] = $action;
    $xoonips_admin['mypage_url'] = $xoonips_admin['myfile_url'].'?page='.$xoonips_admin['mypage'];
}

function xoonips_admin_get_requests($method, $keys)
{
    global $xoonips_admin;
    $formdata = &xoonips_getutility('formdata');
    $ret = array();
    foreach ($keys as $key => $attributes) {
        list($type, $is_array, $required) = $attributes;
        if ($method == 'files') {
            $value = $formdata->getFile($key, $required);
        } else {
            if ($is_array) {
                $value = $formdata->getValueArray($method, $key, $type, $required);
            } else {
                $value = $formdata->getValue($method, $key, $type, $required);
            }
        }
        $ret[$key] = $value;
    }

    return $ret;
}

function xoonips_admin_get_configs($keys, $fmt)
{
    $textutil = &xoonips_getutility('text');
    $xconfig_handler = &xoonips_getormhandler('xoonips', 'config');
    $ret = array();
    foreach ($keys as $key => $key_fmt) {
        $val = $xconfig_handler->getValue($key);
        if (is_null($val)) {
            $ret[$key] = $val;
        } else {
            switch ($key_fmt) {
      case 's':
        // string
        switch ($fmt) {
        case 's':
        case 'show':
        case 'e':
        case 'edit':
          $ret[$key] = $textutil->html_special_chars($val);
          break;
        case 'n':
        case 'none':
          $ret[$key] = $val;
        }
        break;
      case 'i':
        // int
        $ret[$key] = intval($val);
        break;
      case 'f':
        // float
        $ret[$key] = floatval($val);
        break;
      default:
        die('unknown key type');
      }
        }
    }

    return $ret;
}

function xoonips_admin_set_config($key, &$val, $type)
{
    $myts = &MyTextSanitizer::getInstance();
    $xconfig_handler = &xoonips_getormhandler('xoonips', 'config');
    $cleanv = null;
    switch ($type) {
  case 's':
    // string
    $cleanv = $myts->censorString($val);
    break;
  case 'i':
    // int
    $cleanv = intval($val);
    break;
  case 'f':
    // float
    $cleanv = floatval($val);
    break;
  }
    if (is_null($cleanv)) {
        return false;
    }

    return $xconfig_handler->setValue($key, $cleanv);
}
