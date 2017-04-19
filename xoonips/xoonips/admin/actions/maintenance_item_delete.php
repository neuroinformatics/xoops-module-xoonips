<?php

// $Revision:$
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

// get requests
$get_keys = array(
    'uid' => array('i', false, false),
    'upage' => array('i', false, false),
);
$get_vals = xoonips_admin_get_requests('get', $get_keys);
$uid = $get_vals['uid'];
$upage = $get_vals['upage'];

if (is_null($uid)) {
    // user select
    $title = _AM_XOONIPS_MAINTENANCE_ITEM_DELETE_TITLE;
    $nextaction = 'delete';
    include 'actions/maintenance_item_uselect.php';
    exit();
}

// index select
$title = _AM_XOONIPS_MAINTENANCE_ITEM_DELETE_TITLE;
$description = _AM_XOONIPS_MAINTENANCE_ITEM_DELETE_DESC;
$ticket_area = 'xoonips_admin_maintenance_item_delete';
$index_mode = 'private';
$has_back = true;
$confirm_desc = _AM_XOONIPS_MAINTENANCE_ITEM_DELETE_CONFIRM;
$confirm = _AM_XOONIPS_MSG_DELETE_CONFIRM;
$nextaction = 'dupdate';
$submit = _AM_XOONIPS_LABEL_DELETE;
require 'actions/maintenance_item_idxselect.php';
exit();
