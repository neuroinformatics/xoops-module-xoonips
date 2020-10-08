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
 * @param string $ini
 */
function inival2num($ini)
{
    $len = strlen($ini);
    if (0 == $len) {
        return 0.0;
    } elseif (1 == $len) {
        return floatval($ini);
    }
    $num = substr($ini, 0, $len - 1);
    $str = substr($ini, -1, 1);
    if (!is_numeric($str)) {
        $ratio = 1;
        switch (strtoupper($str)) {
        case 'G':
            $ratio *= 1024.0;
            // no break
        case 'M':
            $ratio *= 1024.0;
            // no break
        case 'K':
            $ratio *= 1024.0;
            break;
        }
        $num = floatval($num) * $ratio;
    } else {
        $num = floatval($ini);
    }

    return $num;
}

/**
 * @param string $ini
 */
function inival2bool($ini)
{
    if (is_bool($ini)) {
        return $ini;
    }
    if (is_numeric($ini)) {
        return  intval($ini) > 0;
    }
    if ('on' == strtolower($ini)) {
        return true;
    }

    return false;
}

function xoonips_admin_system_check_phpini(&$category)
{
    // general settings
    // -- default_mimetype
    $name = 'default_mimetype';
    $res = new XooNIpsAdminSystemCheckResult($name);
    $key = $name;
    $ini = ini_get($key);
    if ('text/html' == $ini) {
        $res->setResult(_XASC_STATUS_OK, $ini, _AM_XOONIPS_SYSTEM_CHECK_LABEL_OK);
    } else {
        $res->setResult(_XASC_STATUS_FAIL, $ini, _AM_XOONIPS_SYSTEM_CHECK_LABEL_FAIL);
        $res->setMessage('This variable should set this variable to \'text/html\'');
        $category->setError(_XASC_ERRORTYPE_PHP, _XASC_STATUS_FAIL);
    }
    $category->registerResult($res);
    unset($res);

    // -- default_charset
    $name = 'default_charset';
    $res = new XooNIpsAdminSystemCheckResult($name);
    $key = $name;
    $ini = ini_get($key);
    if (empty($ini)) {
        $res->setResult(_XASC_STATUS_OK, '(no value)', _AM_XOONIPS_SYSTEM_CHECK_LABEL_OK);
    } elseif (_CHARSET == $ini) {
        $res->setResult(_XASC_STATUS_OK, $ini, _AM_XOONIPS_SYSTEM_CHECK_LABEL_OK);
    } else {
        $res->setResult(_XASC_STATUS_NOTICE, $ini, _AM_XOONIPS_SYSTEM_CHECK_LABEL_NOTICE);
        $res->setMessage('This variable is recommended that you comment out the \'default_charset = '.$ini.'\' line.');
        $category->setError(_XASC_ERRORTYPE_PHP, _XASC_STATUS_NOTICE);
    }
    $category->registerResult($res);
    unset($res);

    // -- register_globals
    $name = 'register_globals';
    $res = new XooNIpsAdminSystemCheckResult($name);
    $key = $name;
    $ini = ini_get($key);
    $cond = inival2bool($ini);
    if (!$cond) {
        $res->setResult(_XASC_STATUS_OK, 'Off', _AM_XOONIPS_SYSTEM_CHECK_LABEL_OK);
    } else {
        $res->setResult(_XASC_STATUS_FAIL, 'On', _AM_XOONIPS_SYSTEM_CHECK_LABEL_FAIL);
        $res->setMessage('This variable should turn off');
        $category->setError(_XASC_ERRORTYPE_PHP, _XASC_STATUS_FAIL);
    }
    $category->registerResult($res);
    unset($res);

    // -- magic_quotes_gpc
    $name = 'magic_quotes_gpc';
    $res = new XooNIpsAdminSystemCheckResult($name);
    $key = $name;
    $ini = ini_get($key);
    $cond = inival2bool($ini);
    if (!$cond) {
        $res->setResult(_XASC_STATUS_OK, 'Off', _AM_XOONIPS_SYSTEM_CHECK_LABEL_OK);
    } else {
        $res->setResult(_XASC_STATUS_FAIL, 'On', _AM_XOONIPS_SYSTEM_CHECK_LABEL_FAIL);
        $res->setMessage('This variable should turn off');
        $category->setError(_XASC_ERRORTYPE_PHP, _XASC_STATUS_FAIL);
    }
    $category->registerResult($res);
    unset($res);

    // -- allow_url_fopen
    $name = 'allow_url_fopen';
    $res = new XooNIpsAdminSystemCheckResult($name);
    $key = $name;
    $ini = ini_get($key);
    $cond = inival2bool($ini);
    if (!$cond) {
        $res->setResult(_XASC_STATUS_OK, 'Off', _AM_XOONIPS_SYSTEM_CHECK_LABEL_OK);
    } else {
        $res->setResult(_XASC_STATUS_FAIL, 'On', _AM_XOONIPS_SYSTEM_CHECK_LABEL_FAIL);
        $res->setMessage('This variable should turn off');
        $category->setError(_XASC_ERRORTYPE_PHP, _XASC_STATUS_FAIL);
    }
    $category->registerResult($res);
    unset($res);

    // -- memory limit
    $name = 'memory_limit';
    $res = new XooNIpsAdminSystemCheckResult($name);
    $key = $name;
    $ini = ini_get($key);
    $memory_limit = inival2num($ini);
    $unlimit['memory_limit'] = false;
    if ('' == $ini) {
        // disable to limit memory
        $res->setResult(_XASC_STATUS_OK, '(disable memory limit)', _AM_XOONIPS_SYSTEM_CHECK_LABEL_OK);
        $unlimit['memory_limit'] = true;
    } elseif ($memory_limit <= -1) {
        // disable to limit memory
        $res->setResult(_XASC_STATUS_OK, '(unlimit)', _AM_XOONIPS_SYSTEM_CHECK_LABEL_OK);
        $unlimit['memory_limit'] = true;
    } else {
        if ($memory_limit >= inival2num('128M')) {
            $res->setResult(_XASC_STATUS_OK, $ini, _AM_XOONIPS_SYSTEM_CHECK_LABEL_OK);
        } else {
            $res->setResult(_XASC_STATUS_FAIL, $ini, _AM_XOONIPS_SYSTEM_CHECK_LABEL_FAIL);
            $res->setMessage('This variable should be more than \'128M\'');
            $category->setError(_XASC_ERRORTYPE_PHP, _XASC_STATUS_FAIL);
        }
    }
    $category->registerResult($res);
    unset($res);

    // -- post_max_size
    $name = 'post_max_size';
    $res = new XooNIpsAdminSystemCheckResult($name);
    $key = $name;
    $ini = ini_get($key);
    $post_max_size = inival2num($ini);
    $unlimit['post_max_size'] = false;
    if ($unlimit['memory_limit'] || $memory_limit >= $post_max_size) {
        if ('' == $ini) {
            // disable to limit memory
            $res->setResult(_XASC_STATUS_OK, '(disable memory limit)', _AM_XOONIPS_SYSTEM_CHECK_LABEL_OK);
            $unlimit['post_max_size'] = true;
        } elseif ($post_max_size <= -1) {
            // unlimit memory
            $res->setResult(_XASC_STATUS_OK, '(unlimit)', _AM_XOONIPS_SYSTEM_CHECK_LABEL_OK);
            $unlimit['post_max_size'] = true;
        } elseif ($post_max_size >= inival2num('128M')) {
            $res->setResult(_XASC_STATUS_OK, $ini, _AM_XOONIPS_SYSTEM_CHECK_LABEL_OK);
        } else {
            $res->setResult(_XASC_STATUS_NOTICE, $ini, _AM_XOONIPS_SYSTEM_CHECK_LABEL_NOTICE);
            $res->setMessage('This variable is \'128M\' or more is recommended');
            $category->setError(_XASC_ERRORTYPE_PHP, _XASC_STATUS_NOTICE);
        }
    } else {
        $res->setResult(_XASC_STATUS_FAIL, $ini, _AM_XOONIPS_SYSTEM_CHECK_LABEL_FAIL);
        $res->setMessage('This variable should be less than \'memory_limit\'');
        $category->setError(_XASC_ERRORTYPE_PHP, _XASC_STATUS_FAIL);
    }
    $category->registerResult($res);
    unset($res);

    // file uploads
    // -- file uploads
    $name = 'file_uploads';
    $res = new XooNIpsAdminSystemCheckResult($name);
    $key = $name;
    $ini = ini_get($key);
    $file_uploads = inival2bool($ini);
    if ($file_uploads) {
        $res->setResult(_XASC_STATUS_OK, 'On', _AM_XOONIPS_SYSTEM_CHECK_LABEL_OK);
    } else {
        $res->setResult(_XASC_STATUS_FAIL, 'Off', _AM_XOONIPS_SYSTEM_CHECK_LABEL_FAIL);
        $res->setMessage('This variable should turn on');
        $category->setError(_XASC_ERRORTYPE_PHP, _XASC_STATUS_FAIL);
    }
    $category->registerResult($res);
    unset($res);

    // -- upload_max_filesize
    $name = 'upload_max_filesize';
    $res = new XooNIpsAdminSystemCheckResult($name);
    $key = $name;
    $ini = ini_get($key);
    $upload_max_filesize = inival2num($ini);
    $unlimit['upload_max_filesize'] = false;
    if ($unlimit['post_max_size'] || $post_max_size >= $upload_max_filesize) {
        if ('' == $ini || $upload_max_filesize <= -1) {
            // unlimit memory size
            $res->setResult(_XASC_STATUS_OK, '(unlimit)', _AM_XOONIPS_SYSTEM_CHECK_LABEL_OK);
        } elseif ($upload_max_filesize >= inival2num('128M')) {
            $res->setResult(_XASC_STATUS_OK, $ini, _AM_XOONIPS_SYSTEM_CHECK_LABEL_OK);
        } else {
            $res->setResult(_XASC_STATUS_NOTICE, $ini, _AM_XOONIPS_SYSTEM_CHECK_LABEL_NOTICE);
            $res->setMessage('This variable is \'128M\' or more is recommended');
            $category->setError(_XASC_ERRORTYPE_PHP, _XASC_STATUS_NOTICE);
        }
    } else {
        $res->setResult(_XASC_STATUS_FAIL, $ini, _AM_XOONIPS_SYSTEM_CHECK_LABEL_FAIL);
        $res->setMessage('This variable should be less than \'post_max_size\'');
        $category->setError(_XASC_ERRORTYPE_PHP, _XASC_STATUS_FAIL);
    }
    $category->registerResult($res);
    unset($res);

    // session extension
    // -- session.use_trans_sid
    $name = 'session.use_trans_sid';
    $res = new XooNIpsAdminSystemCheckResult($name);
    $key = $name;
    $ini = ini_get($key);
    $cond = inival2bool($ini);
    if (!$cond) {
        $res->setResult(_XASC_STATUS_OK, $ini, _AM_XOONIPS_SYSTEM_CHECK_LABEL_OK);
    } else {
        $res->setResult(_XASC_STATUS_FAIL, $ini, _AM_XOONIPS_SYSTEM_CHECK_LABEL_FAIL);
        $res->setMessage('This variable should set 0 (disable)');
        $category->setError(_XASC_ERRORTYPE_PHP, _XASC_STATUS_FAIL);
    }
    $category->registerResult($res);
    unset($res);

    // -- session.use_cookies
    $name = 'session.use_cookies';
    $res = new XooNIpsAdminSystemCheckResult($name);
    $key = $name;
    $ini = ini_get($key);
    $cond = inival2bool($ini);
    if ($cond) {
        $res->setResult(_XASC_STATUS_OK, $ini, _AM_XOONIPS_SYSTEM_CHECK_LABEL_OK);
    } else {
        $res->setResult(_XASC_STATUS_FAIL, $ini, _AM_XOONIPS_SYSTEM_CHECK_LABEL_FAIL);
        $res->setMessage('This variable should set 1');
        $category->setError(_XASC_ERRORTYPE_PHP, _XASC_STATUS_FAIL);
    }
    $category->registerResult($res);
    unset($res);

    // -- session.use_only_cookies
    $name = 'session.use_only_cookies';
    $res = new XooNIpsAdminSystemCheckResult($name);
    $key = $name;
    $ini = ini_get($key);
    $cond = inival2bool($ini);
    if ($cond) {
        $res->setResult(_XASC_STATUS_OK, $ini, _AM_XOONIPS_SYSTEM_CHECK_LABEL_OK);
    } else {
        $res->setResult(_XASC_STATUS_NOTICE, $ini, _AM_XOONIPS_SYSTEM_CHECK_LABEL_NOTICE);
        $res->setMessage('This variable should set 1');
        $category->setError(_XASC_ERRORTYPE_PHP, _XASC_STATUS_NOTICE);
    }
    $category->registerResult($res);
    unset($res);
}
