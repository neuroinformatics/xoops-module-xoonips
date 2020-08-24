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

function xoonips_admin_system_check_phpext(&$category)
{
    // mbstring
    $name = 'mbstring';
    $res = new XooNIpsAdminSystemCheckResult($name);
    $ext = $name;
    if (extension_loaded($ext)) {
        $res->setResult(_XASC_STATUS_OK, '', _AM_XOONIPS_SYSTEM_CHECK_LABEL_ENABLE);
        $category->registerResult($res);
        unset($res);
        // -- mbstring_language
        $name = 'mbstring.language';
        $ans[$name] = mb_language();
        $res = new XooNIpsAdminSystemCheckResult(' &raquo; '.$name);
        $res->setResult(_XASC_STATUS_OK, $ans[$name], _AM_XOONIPS_SYSTEM_CHECK_LABEL_OK);
        $category->registerResult($res);
        unset($res);
        // -- mbstring.internal_encoding
        $name = 'mbstring.internal_encoding';
        $ans[$name] = mb_internal_encoding();
        $res = new XooNIpsAdminSystemCheckResult(' &raquo; '.$name);
        if ($ans[$name] == _CHARSET) {
            $res->setResult(_XASC_STATUS_OK, $ans[$name], _AM_XOONIPS_SYSTEM_CHECK_LABEL_OK);
        } else {
            $res->setResult(_XASC_STATUS_FAIL, $ans[$name], _AM_XOONIPS_SYSTEM_CHECK_LABEL_FAIL);
            $res->setMessage('This variable should set \''._CHARSET.'\'');
            $category->setError(_XASC_ERRORTYPE_PHP, _XASC_STATUS_FAIL);
        }
        $category->registerResult($res);
        unset($res);
        // -- mbstring.detect_order
        $name = 'mbstring.detect_order';
        $ans[$name] = mb_detect_order();
        $res = new XooNIpsAdminSystemCheckResult(' &raquo; '.$name);
        $mb_require_order = array('ASCII', _CHARSET);
        if (_CHARSET != 'UTF-8') {
            array_push($mb_require_order, 'UTF-8');
        }
        $mb_req_success = true;
        foreach ($mb_require_order as $mb_req) {
            if (!in_array($mb_req, $ans[$name])) {
                $mb_req_success = false;
            }
        }
        $mes = implode(',', $ans[$name]);
        if ($mb_req_success) {
            $res->setResult(_XASC_STATUS_OK, $mes, _AM_XOONIPS_SYSTEM_CHECK_LABEL_OK);
        } else {
            $res->setResult(_XASC_STATUS_FAIL, $mes, _AM_XOONIPS_SYSTEM_CHECK_LABEL_FAIL);
            $res->setMessage('This results must be included charset '.implode(' and ', $mb_require_order));
            $category->setError(_XASC_ERRORTYPE_PHP, _XASC_STATUS_FAIL);
        }
        $category->registerResult($res);
        unset($res);
        // -- mbstring.func_overload
        $name = 'mbstring.func_overload';
        $ans[$name] = ini_get($name);
        $res = new XooNIpsAdminSystemCheckResult(' &raquo; '.$name);
        if ($ans[$name] == 0) {
            $res->setResult(_XASC_STATUS_OK, $ans[$name], _AM_XOONIPS_SYSTEM_CHECK_LABEL_OK);
        } else {
            $res->setResult(_XASC_STATUS_FAIL, $ans[$name], _AM_XOONIPS_SYSTEM_CHECK_LABEL_FAIL);
            $res->setMessage('This variable should set \'0\'');
            $category->setError(_XASC_ERRORTYPE_PHP, _XASC_STATUS_FAIL);
        }
        $category->registerResult($res);
        unset($res);
        // -- mbstring.http_input
        $name = 'mbstring.http_input';
        $ans[$name] = ini_get($name);
        $res = new XooNIpsAdminSystemCheckResult(' &raquo; '.$name);
        if ($ans[$name] == 'pass' || $ans[$name] == '') {
            $res->setResult(_XASC_STATUS_OK, $ans[$name], _AM_XOONIPS_SYSTEM_CHECK_LABEL_OK);
        } else {
            $res->setResult(_XASC_STATUS_FAIL, $ans[$name], _AM_XOONIPS_SYSTEM_CHECK_LABEL_FAIL);
            $res->setMessage('This variable should set \'pass\'');
            $category->setError(_XASC_ERRORTYPE_PHP, _XASC_STATUS_FAIL);
        }
        $category->registerResult($res);
        unset($res);
        // -- mbstring.http_output
        $name = 'mbstring.http_output';
        $ans[$name] = ini_get($name);
        $res = new XooNIpsAdminSystemCheckResult(' &raquo; '.$name);
        if ($ans[$name] == 'pass' || $ans[$name] == '') {
            $res->setResult(_XASC_STATUS_OK, $ans[$name], _AM_XOONIPS_SYSTEM_CHECK_LABEL_OK);
        } else {
            $res->setResult(_XASC_STATUS_FAIL, $ans[$name], _AM_XOONIPS_SYSTEM_CHECK_LABEL_FAIL);
            $res->setMessage('This variable should set \'pass\'');
            $category->setError(_XASC_ERRORTYPE_PHP, _XASC_STATUS_FAIL);
        }
        $category->registerResult($res);
        unset($res);
        // -- mbstring.encoding_translation
        $name = 'mbstring.encoding_translation';
        $ans[$name] = ini_get($name);
        $res = new XooNIpsAdminSystemCheckResult(' &raquo; '.$name);
        $res->setResult(_XASC_STATUS_OK, $ans[$name], _AM_XOONIPS_SYSTEM_CHECK_LABEL_OK);
        $category->registerResult($res);
        unset($res);
        // -- mbstring.substitute_character
        $name = 'mbstring.substitute_character';
        $ans[$name] = ini_get($name);
        $res = new XooNIpsAdminSystemCheckResult(' &raquo; '.$name);
        if ($ans[$name] == '') {
            $res->setResult(_XASC_STATUS_OK, '(no value)', _AM_XOONIPS_SYSTEM_CHECK_LABEL_OK);
        } elseif ($ans[$name] == 'none') {
            $res->setResult(_XASC_STATUS_OK, 'none', _AM_XOONIPS_SYSTEM_CHECK_LABEL_OK);
        } else {
            $res->setResult(_XASC_STATUS_FAIL, $ans[$name], _AM_XOONIPS_SYSTEM_CHECK_LABEL_FAIL);
            $res->setMessage('This variable should set \'none\'');
            $category->setError(_XASC_ERRORTYPE_PHP, _XASC_STATUS_FAIL);
        }
        $category->registerResult($res);
        unset($res);
    } else {
        $res->setResult(_XASC_STATUS_FAIL, '', _AM_XOONIPS_SYSTEM_CHECK_LABEL_DISABLE);
        $res->setMessage('\''.$ext.'\' extension is required');
        $category->setError(_XASC_ERRORTYPE_PHP, _XASC_STATUS_FAIL);
        $category->registerResult($res);
        unset($res);
    }

    // gd
    $name = 'gd';
    $res = new XooNIpsAdminSystemCheckResult($name);
    $ext = $name;
    if (extension_loaded($ext)) {
        $gd_support_info = gd_info();
        $gd_support = array(
            'FreeType Support' => false,
            'GIF Read Support' => false,
            'GIF Create Support' => false,
            'JPG Support' => false,
            'PNG Support' => false,
            'WBMP Support' => false,
            'XBM Support' => false,
        );
        if (isset($gd_support_info['JPEG Support'])) {
            $gd_support_info['JPG Support'] = $gd_support_info['JPEG Support'];
        }
        foreach (array_keys($gd_support) as $gd_key) {
            if (isset($gd_support_info[$gd_key])) {
                $gd_support[$gd_key] = $gd_support_info[$gd_key];
            }
        }
        $res->setResult(_XASC_STATUS_OK, '', _AM_XOONIPS_SYSTEM_CHECK_LABEL_ENABLE);
        $category->registerResult($res);
        unset($res);
        foreach ($gd_support as $gd_key => $gd_result) {
            $res = new XooNIpsAdminSystemCheckResult(' &raquo; '.$gd_key);
            if ($gd_result) {
                $res->setResult(_XASC_STATUS_OK, 'enable', _AM_XOONIPS_SYSTEM_CHECK_LABEL_OK);
            } else {
                $res->setResult(_XASC_STATUS_NOTICE, 'disable', _AM_XOONIPS_SYSTEM_CHECK_LABEL_NOTICE);
                $category->setError(_XASC_ERRORTYPE_PHP, _XASC_STATUS_NOTICE);
            }
            $category->registerResult($res);
            unset($res);
        }
    } else {
        $res->setResult(_XASC_STATUS_FAIL, '', _AM_XOONIPS_SYSTEM_CHECK_LABEL_DISABLE);
        $res->setMessage('\''.$ext.'\' extension is required');
        $category->setError(_XASC_ERRORTYPE_PHP, _XASC_STATUS_FAIL);
        $category->registerResult($res);
        unset($res);
    }

    // zlib
    $name = 'zlib';
    $res = new XooNIpsAdminSystemCheckResult($name);
    $ext = $name;
    if (extension_loaded($ext)) {
        $res->setResult(_XASC_STATUS_OK, '', _AM_XOONIPS_SYSTEM_CHECK_LABEL_ENABLE);
        $category->registerResult($res);
        unset($res);
    } else {
        $res->setResult(_XASC_STATUS_FAIL, '', _AM_XOONIPS_SYSTEM_CHECK_LABEL_DISABLE);
        $res->setMessage('\''.$ext.'\' extension is required');
        $category->setError(_XASC_ERRORTYPE_PHP, _XASC_STATUS_FAIL);
        $category->registerResult($res);
        unset($res);
    }

    // xml
    $name = 'xml';
    $res = new XooNIpsAdminSystemCheckResult($name);
    $ext = $name;
    if (extension_loaded($ext)) {
        $res->setResult(_XASC_STATUS_OK, '', _AM_XOONIPS_SYSTEM_CHECK_LABEL_ENABLE);
        $category->registerResult($res);
        unset($res);
    } else {
        $res->setResult(_XASC_STATUS_FAIL, '', _AM_XOONIPS_SYSTEM_CHECK_LABEL_DISABLE);
        $res->setMessage('\''.$ext.'\' extension is required');
        $category->setError(_XASC_ERRORTYPE_PHP, _XASC_STATUS_FAIL);
        $category->registerResult($res);
        unset($res);
    }

    // fileinfo
    $name = 'fileinfo';
    $res = new XooNIpsAdminSystemCheckResult($name);
    $ext = $name;
    if (extension_loaded($ext)) {
        $res->setResult(_XASC_STATUS_OK, '', _AM_XOONIPS_SYSTEM_CHECK_LABEL_ENABLE);
        $category->registerResult($res);
        unset($res);
    } else {
        $res->setResult(_XASC_STATUS_FAIL, '', _AM_XOONIPS_SYSTEM_CHECK_LABEL_DISABLE);
        $res->setMessage('\''.$ext.'\' extension is required');
        $category->setError(_XASC_ERRORTYPE_PHP, _XASC_STATUS_FAIL);
        $category->registerResult($res);
        unset($res);
    }
}
