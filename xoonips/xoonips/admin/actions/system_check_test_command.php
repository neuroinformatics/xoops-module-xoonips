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

function system_check_find_path($command)
{
    $is_windows = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');
    if (!defined('PATH_SEPARATOR')) {
        $path_sep = $is_windows ? ':' : ';';
    } else {
        $path_sep = PATH_SEPARATOR;
    }
    $path = getenv('PATH');
    $path_array = explode($path_sep, $path);
    $pathext = $is_windows ? strtolower($_SERVER['PATHEXT']) : '';
    $ext_array = explode(';', $pathext);
    $file_sep = $is_windows ? '\\' : '/';
    $found = false;
    clearstatcache();
    foreach ($path_array as $p) {
        if (substr($p, -1) === $file_sep) {
            $p = substr($p, 0, strlen($p) - 1);
        }
        foreach ($ext_array as $e) {
            $full_path = $p.$file_sep.$command.$e;
            if (file_exists($full_path)) {
                if (is_file($full_path) && ($is_windows || is_executable($full_path))) {
                    $found = $full_path;
                    break;
                }
            }
        }
    }

    return $found;
}

function xoonips_admin_system_check_command(&$category)
{
    $commands = array(
        'pdftotext' => 'PDF',
        'wvText' => 'MS-Word',
        'xlhtml' => 'MS-Excel',
        'ppthtml' => 'MS-PowerPoint',
    );
    foreach ($commands as $command => $filetype) {
        $res = new XooNIpsAdminSystemCheckResult($command);
        $path = system_check_find_path($command);
        if ($path) {
            $res->setResult(_XASC_STATUS_OK, $path, _AM_XOONIPS_SYSTEM_CHECK_LABEL_OK);
        } else {
            $res->setResult(_XASC_STATUS_FAIL, 'not found', _AM_XOONIPS_SYSTEM_CHECK_LABEL_FAIL);
            $res->setMessage('External program \''.$command.'\' not found. It is required for \''.$filetype.'\' file search index creation.');
            $category->setError(_XASC_ERRORTYPE_COMMAND, _XASC_STATUS_FAIL);
        }
        $category->registerResult($res);
        unset($res);
    }
}
