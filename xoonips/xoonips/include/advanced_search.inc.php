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

require_once 'AL.php';
require_once 'lib.php';

defined('XOOPS_ROOT_PATH') || exit('XOOPS root path not defined');

$formdata = &xoonips_getutility('formdata');
$checkbox = $formdata->getValue('both', 'checkbox', 's', false, 'off');
$add_to_index_id = $formdata->getValue('both', 'add_to_index_id', 'i', false, 0);
$jumpto_url = $formdata->getValue('both', 'jumpto_url', 's', false, '');

$item_type_handler = &xoonips_getormhandler('xoonips', 'item_type');
$itemtypes = &$item_type_handler->getObjectsSortByWeight();

// making block, search_var
$search_blocks = array();
$search_var = array();
foreach ($itemtypes as $itemtype) {
    $modname = $itemtype->get('name');
    require_once XOOPS_ROOT_PATH.'/modules/'.$itemtype->get('viewphp');
    $fname = $modname.'GetAdvancedSearchBlock';
    if (function_exists($fname)) {
        $search_blocks[] = $fname($search_var);
    }
}

if (!isset($itemselect_url)) {
    $itemselect_url = 'itemselect.php';
}
if (!isset($pankuzu)) {
    $pankuzu = _MI_XOONIPS_ACCOUNT_PANKUZU_PLATFORM_USER._MI_XOONIPS_ACCOUNT_PANKUZU_SEPARATOR._MD_XOONIPS_ITEM_PANKUZU_ADVANCED_SEARCH;
}

$textutil = &xoonips_getutility('text');

$xoopsTpl->assign('itemselect_url', $textutil->html_special_chars($itemselect_url));
$xoopsTpl->assign('pankuzu', $pankuzu);
$escaped_search_var = array();
foreach ($search_var as $val) {
    $escaped_search_var[] = $textutil->html_special_chars($val);
}
$xoopsTpl->assign('search_var', $escaped_search_var);
$xoopsTpl->assign('search_blocks', $search_blocks);
$xoopsTpl->assign('add_to_index_id', intval($add_to_index_id));
$xoopsTpl->assign('accept_charset', '');

$xoopsTpl->assign('jumpto_url', $textutil->html_special_chars($jumpto_url));
