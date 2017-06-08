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
$xoopsOption['pagetype'] = 'user';
require 'include/common.inc.php';

require_once 'include/lib.php';
require_once 'include/AL.php';

$xnpsid = $_SESSION['XNPSID'];
if (is_object($xoopsUser)) {
    $uid = $xoopsUser->getVar('uid', 'n');
} else {
    $uid = UID_GUEST;
}

$xoopsOption['template_main'] = 'xoonips_index.html';
require XOOPS_ROOT_PATH.'/header.php';

// exit at here if guest can't access /Public tree
if (!xnp_is_valid_session_id($xnpsid)) {
    require XOOPS_ROOT_PATH.'/footer.php';

    return;
}

// get blocks
$item_type_handler = &xoonips_getormhandler('xoonips', 'item_type');
$item_type_objs = &$item_type_handler->getObjectsSortByWeight();
$blocks = array();
foreach ($item_type_objs as $item_type_obj) {
    $name = $item_type_obj->get('name');
    $file = XOOPS_ROOT_PATH.'/modules/'.$item_type_obj->get('viewphp');
    if (file_exists($file)) {
        require_once $file;
    }
    $fname = $name.'GetTopBlock';
    if (function_exists($fname)) {
        $itemtype = $item_type_obj->getVarArray('s');
        $html = $fname($itemtype);
        if (!empty($html)) {
            $blocks[] = $fname($itemtype);
        }
    }
}

if (count($blocks) != 0) {
    $xoopsTpl->assign('blocks', $blocks);
}

$xoopsTpl->assign('xoonips_editprofile_url', XOOPS_URL.'/modules/xoonips/edituser.php?uid='.$uid);
require XOOPS_ROOT_PATH.'/footer.php';
