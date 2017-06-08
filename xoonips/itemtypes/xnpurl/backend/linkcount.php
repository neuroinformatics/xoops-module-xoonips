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
if (!defined('XOONIPS_PATH')) {
    exit();
}

if (!isset($_SERVER['HTTP_REFERER']) || preg_match('/\\/modules\\/xoonips\\//', $_SERVER['HTTP_REFERER']) == 0) {
    die('Turn REFERER on');
}

if (!isset($_POST['item_id'])) {
    die('illegal request');
}

$item_id = intval($_POST['item_id']);
$detail_handler = &xoonips_getormhandler('xnpurl', 'item_detail');
$detail_obj = &$detail_handler->get($item_id);
if (!is_object($detail_obj)) {
    die('invalid item id');
}
$url_count = $detail_obj->get('url_count');
$detail_obj->set('url_count', $url_count + 1);
$detail_handler->insert($detail_obj);
