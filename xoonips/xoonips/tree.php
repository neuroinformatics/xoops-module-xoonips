<?php

// $Revision: 1.36.2.1.2.21 $
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

/**
 * display tree in iframe. Tree's state(open/close) and state checkbox are memorized in cookie.
 * input:
 *   $_GET['checkbox']
 *           0:no check in checkbox(default), 1:checked in checkbox
 *   $_GET['url']
 *           Page that user moves to when user clicks index.
 *           no $_GET['url']: listeitem.php(default),  "": don't link
 *           not to contain since '?'
 *   $_GET['edit']
 *           0:display only readable index(default), 1:display only editable index
 *   $_GET['on_check_private_handler_id']
 *           Specified: Id of the element that has function 'onCheckPrivate'.
 *           If the Id is given, callback onCheckPrivate of the element
 *           when a number of selected private indexes is zero or not.
 *           Not specified: nothing to do.
 *   $_GET['selected_tab']
 *           No specified:Select tab that selected last time(default), specified:select the specified tab.
 *   $_GET['edit_public']
 *           Specified:display Public if su.
 *   $_GET['puid']
 *           displaying a user's tree specified by puid if xoopsUser is moderator and puid is specified.
 *
 * output:
 *   nothing. Getting state of checkbox in onSubmit isn't work of 'tree.php'.
 */

/*
  the font used in a tree is specified like following in css of theme
    div  .tree { font definition }

  for example
    .tree {
        font-family: Times;
        font-size: 10pt;
        font-weight: bold;
    }

*/

require 'include/common.inc.php';
require 'include/AL.php';

$xnpsid = $_SESSION['XNPSID'];

// get variables
$formdata = &xoonips_getutility('formdata');
$get_keys = array(
    'checkbox' => array('type' => 'b', 'default' => false),
    'url' => array('type' => 's', 'default' => 'listitem.php'),
    'edit' => array('type' => 'b', 'default' => false),
    'on_check_private_handler_id' => array('type' => 's', 'default' => ''),
    'selected_tab' => array('type' => 'i', 'default' => ''),
    'edit_public' => array('type' => 'b', 'default' => false),
    'puid' => array('type' => 'i', 'default' => 0),
    'onclick_title' => array('type' => 's', 'default' => ''),
    'private_only' => array('type' => 'i', 'default' => 0),
);
$get_vals = array();
foreach ($get_keys as $key => $meta) {
    $get_vals[$key] = $formdata->getValue('get', $key, $meta['type'], false, $meta['default']);
}

// get variable check
if (strpos($get_vals['url'], '?') !== false) {
    die('illegal request');
}

$myxoopsConfig = &xoonips_get_xoops_configs(XOOPS_CONF);
$textutil = &xoonips_getutility('text');

$xoonips_target_url = $textutil->html_special_chars($get_vals['url']);
$xoonips_oncheck_private_handler_id = $textutil->html_special_chars($get_vals['on_check_private_handler_id']);

// tree node images
$tree_image_path = XOOPS_THEME_PATH.'/'.$myxoopsConfig['theme_set'].'/'.XOONIPS_TREE_SWAP_IMAGE_DIR;
if (!is_dir($tree_image_path)) {
    $tree_image_path = XOOPS_ROOT_PATH.'/modules/xoonips/images';
}
$tree_image_url = str_replace(XOOPS_ROOT_PATH, XOOPS_URL, $tree_image_path);
// check compat33 node image
if (file_exists($tree_image_path.'/tree_root_normal.gif')) {
    // new node image found
    $tree_image_compat33 = false;
} else {
    $tree_image_compat33 = true;
}

$uid = is_object($xoopsUser) ? $xoopsUser->getVar('uid', 'n') : UID_GUEST;

if ($uid == UID_GUEST && !public_item_target_user_all()) {
    //
    // for guest access, show message that access to index tree is forbidden.
    //
    xoops_header(false);
    echo '</head><body><div style="font-size: 10pt;">';
    echo _MD_XOONIPS_INDEX_FORBIDDEN;
    echo '</div>';
    xoops_footer();
    exit();
}

// get index tree structure
require_once 'include/gentree.php';
$indexes = genIndexTree0($xnpsid);
$is_moderator = xnp_is_moderator($xnpsid, $uid);
if ($is_moderator && $get_vals['puid'] > 0) {
    $puid = $get_vals['puid'];
} else {
    $puid = $uid;
}
if ($is_moderator) {
    filterMyIndex($indexes, $xnpsid, $puid);
}
if ($get_vals['edit']) {
    filterEditableIndex($indexes, $xnpsid, $uid, $puid, $get_vals['edit_public'] && !empty($_SESSION['xoonips_old_uid']));
}
if ($get_vals['private_only']) {
    filterPrivateIndex($indexes, $puid);
}

$indexes = genIndexTree1($indexes);
// get number of items under nodes (by index).
// use special function.
$itemCounts = array();
$result = xnp_get_item_count_group_by_index($xnpsid, $itemCounts);
$ct = count($indexes);
for ($i = 0; $i < $ct; ++$i) { // can't change value in foreach
    $index = &$indexes[$i];
    $index_id = $index['item_id'];
}
unset($index);

$xoonips_tree_nodes_array = array();
$xoonips_tree_roots_array = array();

$length = count($indexes);
foreach ($indexes as $i => $index) {
    $xid = $index['item_id'];
    // tree nodes
    $node = array();
    $node['xid'] = $xid;
    $node['is_last'] = $index['is_last'] ? 1 : 0;
    $node['open_level'] = $index['open_level'];
    $node['title'] = $textutil->javascript_special_chars($index['titles'][DEFAULT_INDEX_TITLE_OFFSET]);
    $itemCount = isset($itemCounts[$xid]) ? $itemCounts[$xid] : null;
    if ($itemCount) {
        $node['title'] .= sprintf('(%d)', $itemCount);
    }
    if ($index['child_count'] == 0) {
        $node['child'] = 'null';
    } else {
        $node['child'] = sprintf('[%s]', implode(',', $index['child']));
    }
    $xoonips_tree_nodes_array[] = $node;

    // tree roots
    if ($index['depth'] == 1) {
        $xoonips_tree_roots_array[] = $xid;
    }
}
unset($index);
unset($i);

// global attributes
$attributes = array(
    'url' => XOOPS_URL.'/modules/xoonips',
    'target_url' => $xoonips_target_url,
    'link_is_checkbox' => intval($get_vals['checkbox']),
    'selected_tab' => $get_vals['selected_tab'],
    'onclick_title' => $get_vals['onclick_title'],
    'image_url' => $tree_image_url,
    'image_compat33' => $tree_image_compat33 ? 1 : 0,
);
$xoonips_tree_attributes_array = array();
foreach ($attributes as $key => $val) {
    $xoonips_tree_attributes_array[] = array('key' => $key, 'value' => $val);
}

// start to output html
require_once XOOPS_ROOT_PATH.'/class/template.php';
$xoopsTpl = new XoopsTpl();
xoops_header(false);
echo "\n";
$xoopsTpl->assign('tree_nodes', $xoonips_tree_nodes_array);
$xoopsTpl->assign('tree_roots', $xoonips_tree_roots_array);
$xoopsTpl->assign('tree_attribs', $xoonips_tree_attributes_array);
$xoopsTpl->assign('oncheck_private_handler_id', $xoonips_oncheck_private_handler_id);
$xoopsTpl->display('db:xoonips_tree.html');
xoops_footer();
