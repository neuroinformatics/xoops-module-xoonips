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
if (!defined('XOOPS_ROOT_PATH')) {
    exit();
}

function xoonips_admin_system_check_xoonips(&$category)
{
    $module_handler = &xoops_gethandler('module');
    $module = &$module_handler->getByDirname('xoonips');

    $is_windows = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');

    // version
    $name = 'XooNIps version';
    $res = new XooNIpsAdminSystemCheckResult($name);
    $version = sprintf('%3.2f', $module->getVar('version', 's') / 100.0);
    $res->setResult(_XASC_STATUS_OK, $version, _AM_XOONIPS_SYSTEM_CHECK_LABEL_OK);
    $category->registerResult($res);
    unset($res);

    // file upload dir
    $keys = array('upload_dir' => 's');
    $vals = xoonips_admin_get_configs($keys, 'n');
    $upload_dir = $vals['upload_dir'];
    $name = 'File upload directory';
    $res = new XooNIpsAdminSystemCheckResult($name);
    $ans['status'] = _XASC_STATUS_OK;
    $ans['label'] = $upload_dir;
    $ans['result'] = _AM_XOONIPS_SYSTEM_CHECK_LABEL_OK;
    $ans['message'] = '';
    $ans['etype'] = _XASC_ERRORTYPE_XOONIPS;
    $ans['error'] = _XASC_STATUS_OK;
    if (trim($upload_dir) == '') {
        $ans['status'] = _XASC_STATUS_FAIL;
        $ans['label'] = '(no value)';
        $ans['result'] = _AM_XOONIPS_SYSTEM_CHECK_LABEL_FAIL;
        $ans['message'] = 'You have to set the file upload directory';
        $ans['error'] = _XASC_STATUS_FAIL;
    }
    // -- check absolute directory
    if ($ans['error'] == _XASC_STATUS_OK) {
        if ($is_windows) {
            // trim drive letter
            $upload_dir = preg_replace('/^[a-zA-Z]:/', '', $upload_dir);
            // use '/' file separator
            $upload_dir = str_replace('\\', '/', $upload_dir);
        }
        if ($upload_dir[0] != '/') {
            $ans['status'] = _XASC_STATUS_FAIL;
            $ans['result'] = _AM_XOONIPS_SYSTEM_CHECK_LABEL_FAIL;
            $ans['message'] = 'File upload directory must be absolute path';
            $ans['error'] = _XASC_STATUS_FAIL;
        }
    }
    // -- check temporary directory
    if ($ans['error'] == _XASC_STATUS_OK) {
        if (preg_match('/^(\\/var\\/tmp|\\/tmp)(\\/.*)?$/', $upload_dir)) {
            $ans['status'] = _XASC_STATUS_FAIL;
            $ans['result'] = _AM_XOONIPS_SYSTEM_CHECK_LABEL_FAIL;
            $ans['message'] = 'File upload directory should not locate under temporary directory';
            $ans['error'] = _XASC_STATUS_FAIL;
        }
    }
    // -- check XOOPS_ROOT_PATH
    if ($ans['error'] == _XASC_STATUS_OK) {
        $pos = strpos($upload_dir, XOOPS_ROOT_PATH);
        if ($pos === 0) {
            $ans['status'] = _XASC_STATUS_FAIL;
            $ans['result'] = _AM_XOONIPS_SYSTEM_CHECK_LABEL_FAIL;
            $ans['message'] = 'File upload directory should not locate under XOOPS_ROOT_PATH';
            $ans['error'] = _XASC_STATUS_FAIL;
        }
    }
    // -- check directory
    if ($ans['error'] == _XASC_STATUS_OK) {
        if (!is_dir($upload_dir)) {
            $ans['status'] = _XASC_STATUS_FAIL;
            $ans['result'] = _AM_XOONIPS_SYSTEM_CHECK_LABEL_FAIL;
            $ans['message'] = 'File upload directory not found';
            $ans['error'] = _XASC_STATUS_FAIL;
        }
    }
    // -- check permission
    if ($ans['error'] == _XASC_STATUS_OK) {
        if (!is_writable($upload_dir) || !is_readable($upload_dir) || (!$is_windows && !is_executable($upload_dir))) {
            $ans['status'] = _XASC_STATUS_FAIL;
            $ans['result'] = _AM_XOONIPS_SYSTEM_CHECK_LABEL_FAIL;
            $ans['message'] = 'File upload directory has invalid permission';
            $ans['error'] = _XASC_STATUS_FAIL;
        }
    }
    $res->setResult($ans['status'], $ans['label'], $ans['result']);
    if (!empty($ans['message'])) {
        $res->setMessage($ans['message']);
    }
    if ($ans['error'] != _XASC_STATUS_OK) {
        $category->setError($ans['etype'], $ans['error']);
    }
    $category->registerResult($res);
    unset($res);

    // magic file path
    $keys = array('magic_file_path' => 's');
    $vals = xoonips_admin_get_configs($keys, 'n');
    $magic_file_path = $vals['magic_file_path'];
    $name = 'Magic file path';
    $res = new XooNIpsAdminSystemCheckResult($name);
    $ans['status'] = _XASC_STATUS_OK;
    $ans['label'] = ($magic_file_path == '') ? '(empty)' : $magic_file_path;
    $ans['result'] = _AM_XOONIPS_SYSTEM_CHECK_LABEL_OK;
    $ans['message'] = '';
    $ans['etype'] = _XASC_ERRORTYPE_XOONIPS;
    $ans['error'] = _XASC_STATUS_OK;
    if (!extension_loaded('fileinfo')) {
        $ans['status'] = _XASC_STATUS_FAIL;
        $ans['result'] = _AM_XOONIPS_SYSTEM_CHECK_LABEL_FAIL;
        $ans['message'] = 'PHP extension PECL Fileinfo not loaded';
        $ans['etype'] = _XASC_ERRORTYPE_PHP;
        $ans['error'] = _XASC_STATUS_FAIL;
    } else {
        if ($magic_file_path == '') {
            $finfo = @finfo_open(FILEINFO_MIME);
        } else {
            $finfo = @finfo_open(FILEINFO_MIME, $magic_file_path);
        }
        if (!$finfo) {
            $ans['status'] = _XASC_STATUS_FAIL;
            $ans['result'] = _AM_XOONIPS_SYSTEM_CHECK_LABEL_FAIL;
            $ans['message'] = 'Invalid magic file path';
            $ans['error'] = _XASC_STATUS_FAIL;
        } else {
            $val = @finfo_file($finfo, __DIR__.'/index.html');
            $val = preg_replace(array('/;.*$/', '/ +.*$/'), array('', ''), $val);
            if (!in_array($val, array('text/html', 'text/plain'))) {
                $ans['status'] = _XASC_STATUS_FAIL;
                $ans['result'] = _AM_XOONIPS_SYSTEM_CHECK_LABEL_FAIL;
                $ans['message'] = 'Broken magic database';
                $ans['error'] = _XASC_STATUS_FAIL;
            }
            finfo_close($finfo);
        }
    }
    $res->setResult($ans['status'], $ans['label'], $ans['result']);
    if (!empty($ans['message'])) {
        $res->setMessage($ans['message']);
    }
    if ($ans['error'] != _XASC_STATUS_OK) {
        $category->setError($ans['etype'], $ans['error']);
    }
    $category->registerResult($res);
    unset($res);

    // TODO: proxy
}
