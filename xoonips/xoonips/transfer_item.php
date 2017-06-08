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

require 'include/common.inc.php';

require_once 'class/base/actionfactory.class.php';

$formdata = &xoonips_getutility('formdata');
$op = $formdata->getValue('get', 'action', 'n', false);
if (empty($op)) {
    header('Location: '.XOOPS_URL.'/modules/xoonips/transfer_item.php?action=request_initialize');
    exit(0);
}

xoonips_validate_request(is_valid_action($op));

// disable to link index tree
$xoonipsURL = '';

$factory = &XooNIpsActionFactory::getInstance();
$action = &$factory->create('transfer_user_'.$op);
if (!$action) {
    header('Location: '.XOOPS_URL.'/');
    exit(0);
}
$action->action();
exit(0);

function is_valid_action($action)
{
    return in_array($action, array('accept', 'detail_item', 'list_item', 'reject', 'request', 'request_check', 'request_initialize', 'request_select_item', 'request_unselect_item'));
}
