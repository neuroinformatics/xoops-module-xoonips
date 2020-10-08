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

// class file
require_once XOONIPS_PATH.'/class/base/JSON.php';

// change internal encoding to UTF-8
if (extension_loaded('mbstring')) {
    mb_language('uni');
    mb_internal_encoding('UTF-8');
    mb_http_output('pass');
}

if (!isset($_SERVER['HTTP_REFERER']) || 0 == preg_match('/\\/modules\\/xoonips\\//', $_SERVER['HTTP_REFERER'])) {
    die('Turn REFERER on');
}

$formdata = &xoonips_getutility('formdata');
$mode = $formdata->getValue('post', 'mode', 's', true);
$num = $formdata->getValue('post', 'num', 'i', true);

$admin_file_handler = &xoonips_gethandler('xoonips', 'admin_file');

$total = $admin_file_handler->getCountFiles();
if ($num < 0 || $num > $total) {
    die('fatal error : invalid \'num\' parameter');
}
$file_id = $admin_file_handler->getFileIdByCount($num);
if (false === $file_id) {
    die('fatal error : file id not found');
}

if ('info' == $mode) {
    $admin_file_handler->updateFileInfo($file_id);
} elseif ('index' == $mode) {
    $admin_file_handler->updateFileSearchText($file_id, false);
} else {
    die('fatal error : invalid \'mode\' parameter');
}

$data = array(
  'mode' => $mode,
  'num' => $num,
);

// json
$encode = json_encode($data);

// output
header('Content-Type: text/javascript+json; charset=utf-8');
echo $encode;
exit();
