<?php

// $Revision: 1.1.4.1.2.15 $
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

// helper function for item order sorting
function _xoonips_helper_quick_search_cmp($a, $b)
{
    if ($a['weight'] == $b['weight']) {
        return($a['mid'] < $b['mid']) ? -1 : 1;
        // mid must be uniq
    }

    return($a['weight'] < $b['weight']) ? -1 : 1;
}

// xoonips quick search block
function b_xoonips_quick_search_show()
{
    global $xoopsUser;

    $textutil = &xoonips_getutility('text');

    // hide block if user is guest and xoonips public index viewing
    // policy is 'platform'.
    if (!is_object($xoopsUser)) {
        $xconfig_handler = &xoonips_getormhandler('xoonips', 'config');
        $target_user = $xconfig_handler->getValue('public_item_target_user');
        if ($target_user != 'all') {
            // 'platform'
            return false;
        }
    }

    $uid = is_object($xoopsUser) ? $xoopsUser->getVar('uid', 'n') : UID_GUEST;

    // hide block if user is invalid xoonips user
    $xsession_handler = &xoonips_getormhandler('xoonips', 'session');
    if (!$xsession_handler->validateUser($uid, false)) {
        return false;
    }

    $search_itemtypes = array(
        'all' => _MB_XOONIPS_SEARCH_ALL,
        'basic' => _MB_XOONIPS_SEARCH_TITLE_AND_KEYWORD,
        'metadata' => _MB_XOONIPS_SEARCH_METADATA,
    );

    // get installed itemtypes
    // TODO: xoonips_item_type table should have itemtype sort order.
    $module_handler = &xoops_gethandler('module');
    $itemtype_handler = &xoonips_getormhandler('xoonips', 'item_type');
    $itemtype_objs = &$itemtype_handler->getObjects();
    $itemtypes = array();
    foreach ($itemtype_objs as $itemtype_obj) {
        $name = $itemtype_obj->getVar('name', 'e');
        if (!in_array($name, array('xoonips_index'))) {
            $mid = $itemtype_obj->getVar('mid', 'n');
            $display_name = $itemtype_obj->getVar('display_name', 'e');
            $module = &$module_handler->get($mid);
            if (!$module) {
                continue;
            }
            $weight = $module->getVar('weight', 'n');
            $itemtypes[] = array(
                'mid' => $mid,
                'name' => $name,
                'display_name' => $display_name,
                'weight' => $weight,
            );
        }
    }
    if (!empty($itemtypes)) {
        // sort itemtypes
        usort($itemtypes, '_xoonips_helper_quick_search_cmp');
        // append each itemtypes to search condtions
        foreach ($itemtypes as $itemtype) {
            $search_itemtypes[$itemtype['name']] = $itemtype['display_name'];
        }
    }

    // fetch previous query conditions
    // - keyword
    $formdata = &xoonips_getutility('formdata');
    $keyword = $formdata->getValue('both', 'keyword', 'n', false, '');
    // - search_itemtype
    $selected = $formdata->getValue('both', 'search_itemtype', 's', false);
    if (!is_null($selected) && !in_array($selected, array_keys($search_itemtypes))) {
        $selected = '';
    }

    // assign block template variables
    $block = array();
    $block['lang_search'] = _MB_XOONIPS_SEARCH_QUICK;
    $block['lang_advanced_search'] = _MB_XOONIPS_SEARCH_ADVANCED;
    $block['search_itemtypes'] = $search_itemtypes;
    $block['keyword'] = $textutil->html_special_chars($keyword);
    $block['search_itemtypes_selected'] = $textutil->html_special_chars($selected);
    $block['op'] = 'quicksearch';
    $block['submit_url'] = XOOPS_URL.'/modules/xoonips/itemselect.php';

    return $block;
}
