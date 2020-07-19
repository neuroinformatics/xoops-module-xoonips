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
require_once '../../class/xoopstree.php';

$xnpsid = $_SESSION['XNPSID'];

// If not a user, redirect
$uid = is_object($xoopsUser) ? $xoopsUser->getVar('uid', 'n') : UID_GUEST;
if (!xnp_is_valid_session_id($xnpsid)) {
    // User is guest group, and guest isn't admitted to access the page.
    redirect_header('user.php', 3, _MD_XOONIPS_ITEM_FORBIDDEN);
    exit();
}

$myxoopsConfig = &xoonips_get_xoops_configs(XOOPS_CONF);
$myxoopsConfigMetaFooter = &xoonips_get_xoops_configs(XOOPS_CONF_METAFOOTER);

$textutil = &xoonips_getutility('text');

$sess_orderby = isset($_SESSION['xoonips_order_by']) ? $_SESSION['xoonips_order_by'] : 'title';
$sess_orderdir = isset($_SESSION['xoonips_order_dir']) ? $_SESSION['xoonips_order_dir'] : ASC;
$request_vars = array(
    'page' => array('i', 1),
    'orderby' => array('s', $sess_orderby),
    'order_dir' => array('i', $sess_orderdir),
    'itemcount' => array('i', 20),
    'selected' => array('i', array()),
    'initially_selected' => array('i', array()),
    'print' => array('b', false),
    'add_to_index' => array('b', false),
    'num_of_items' => array('i', null),
    'index_id' => array('i', null),
);

$formdata = &xoonips_getutility('formdata');
foreach ($request_vars as $key => $meta) {
    list($type, $default) = $meta;
    $$key = $formdata->getValue('both', $key, $type, false, $default);
}

// check 'orderby' value
if (!in_array($orderby, array('title', 'doi', 'last_update_date', 'creation_date', 'publication_date'))) {
    unset($_SESSION['xoonips_order_by']);
    unset($_SESSION['xoonips_order_dir']);
    xoonips_error_exit(400);
}

$_SESSION['xoonips_order_by'] = $orderby;
$_SESSION['xoonips_order_dir'] = $order_dir;

$itemtypes = array();
$tmp = array();
if (xnp_get_item_types($tmp) != RES_OK) {
    xoonips_error_exit(500);
} else {
    foreach ($tmp as $i) {
        $itemtypes[$i['item_type_id']] = $i;
    }
}

$xoopsOption['template_main'] = 'xoonips_itemlist.html';
if ($print) {
    require_once XOOPS_ROOT_PATH.'/class/template.php';
    $xoopsTpl = new XoopsTpl();
    xoops_header(false);
    echo "</head><body onload='window.print();'>\n";
} else {
    require XOOPS_ROOT_PATH.'/header.php';
}

$index_handler = &xoonips_getormhandler('xoonips', 'index');
if (isset($index_id)) {
    // check permission
    $idx_obj = &$index_handler->get($index_id);
    if ($idx_obj === false) {
        // index not found
        redirect_header(XOOPS_URL.'/', 3, _NOPERM);
        exit();
    }
    if (!$index_handler->getPerm($index_id, $uid, 'read')) {
        if ($uid == UID_GUEST) {
            // try login
            redirect_header(XOONIPS_URL.'/user.php', 3, _NOPERM);
            exit();
        }
        // no permission
        redirect_header(XOOPS_URL.'/', 3, _NOPERM);
        exit();
    }
}

$xoopsTpl->assign('add_button_visible', $index_handler->getPerm($index_id, @$_SESSION['xoopsUserId'], 'register_item'));
$xoopsTpl->assign('title_page', _MD_XOONIPS_ITEM_LISTING_ITEM);
$xoopsTpl->assign('order_by_label', _MD_XOONIPS_ITEM_ORDER_BY);
$xoopsTpl->assign('item_count_label', _MD_XOONIPS_ITEM_NUM_OF_ITEM_PER_PAGE);
//order_by_select: array( "variable name" => "name for view", ... )
$xoopsTpl->assign(
    'order_by_select',
    array(
        'title' => _MD_XOONIPS_ITEM_TITLE_LABEL,
        'doi' => _MD_XOONIPS_ITEM_DOI_LABEL,
        'last_update_date' => _MD_XOONIPS_ITEM_LAST_UPDATE_DATE_LABEL,
        'creation_date' => _MD_XOONIPS_ITEM_CREATION_DATE_LABEL,
        'publication_date' => _MD_XOONIPS_ITEM_PUBLICATION_DATE_LABEL,
    )
);
$xoopsTpl->assign('order_by', $textutil->html_special_chars($orderby));

$xoopsTpl->assign('item_count_select', array('20', '50', '100'));

$iids = array();
$items = array();
$cri = array();
if ($orderby == 'publication_date') {
    $cri = array(
        'start' => ($page - 1) * $itemcount,
        'rows' => $itemcount,
        'orders' => array(
            array('name' => 'publication_year', 'order' => $order_dir),
            array('name' => 'publication_month', 'order' => $order_dir),
            array('name' => 'publication_mday', 'order' => $order_dir),
        ),
    );
} else {
    $cri = array(
        'start' => ($page - 1) * $itemcount,
        'rows' => $itemcount,
        'orders' => array(array('name' => $orderby, 'order' => $order_dir)),
    );
}
if (isset($index_id)) {
    $index_item_link_handler = &xoonips_getormhandler('xoonips', 'index_item_link');
    $index_item_links = $index_item_link_handler->getByIndexId($index_id, $uid);
    $num_of_items = count($index_item_links);
    foreach ($index_item_links as $link) {
        $iids[] = $link->get('item_id');
    }
} else {
    $ret = xnp_dump_item_id($xnpsid, array(), $iids);
    $num_of_items = count($iids);
    if ($ret != RES_OK) {
        xoonips_error_exit(500);
    }
}

function my_xoonips_get_child_index(&$db, $index_id)
{
    $sql = 'SELECT index_id FROM '.$db->prefix('xoonips_index').' WHERE parent_index_id='.$index_id.' ORDER BY sort_number';
    $results = &$db->query($sql);
    $cids = array();
    while ($row = $db->fetchArray($results)) {
        $cids[] = $row['index_id'];
    }

    return $cids;
}

if (isset($index_id)) {
    // add index list
    $my_indexes = array();
    $cids = my_xoonips_get_child_index($xoopsDB, $index_id);
    if (count($cids) > 0) {
        $item_counts = array();
        xnp_get_item_count_group_by_index($xnpsid, $item_counts);
        foreach ($cids as $cid) {
            $info = array();
            $cicnt = count(my_xoonips_get_child_index($xoopsDB, $cid));
            if (xnp_get_index($xnpsid, $cid, $info) == RES_OK) {
                $cnt = isset($item_counts[$cid]) ? $item_counts[$cid] : 0;
                $my_index = array(
                     'index_id' => $cid,
                     'title' => $info['html_title'],
                     'child_index_num' => $cicnt,
                     'child_item_num' => $cnt,
                );
                $index_tpl = new XoopsTpl();
                $index_tpl->assign('index', $my_index);
                $my_indexes[] = $index_tpl->fetch('db:xoonips_index_list_block.html');
            }
        }
    }

    // making character strings in display current place (Root/Private/Tools&Techniques etc)
    // -> index_path
    $dirArray = array();
    for ($p_xid = $index_id; $p_xid != IID_ROOT; $p_xid = (int) ($index['parent_index_id'])) {
        // get $index
        $index = array();
        $result = xnp_get_index($xnpsid, $p_xid, $index);
        if ($result != RES_OK) {
            xoonips_error_exit(500);
        }
        $dirArray[] = $index;
    }
    $indexes = array_reverse($dirArray);
    $xoopsTpl->assign('index_path', $indexes);
    $xoopsTpl->assign('my_indexes', $my_indexes);
    $index_titles = array();
    foreach ($indexes as $i) {
        $index_titles[] = $i['titles'][DEFAULT_INDEX_TITLE_OFFSET];
    }
    $xoopsTpl->assign('xoops_pagetitle', $textutil->html_special_chars('/'.implode('/', $index_titles)));

    // check that index is editable
    $handler = &xoonips_getormhandler('xoonips', 'index');
    $xoopsTpl->assign('edit_index', $handler->getPerm($index_id, $xoopsUser ? $xoopsUser->getVar('uid') : UID_GUEST, 'write'));
}

//centering current page number(5th of $pages)
$xoopsTpl->assign('pages', xoonips_get_selectable_page_number($page, ceil($num_of_items / $itemcount)));

if ($num_of_items == 0) {
    $page_no_label = _MD_XOONIPS_ITEM_NO_ITEM_LISTED;
} else {
    $_pMin = min(($page - 1) * $itemcount + 1, $num_of_items);
    $_pMax = min($page * $itemcount, $num_of_items);
    if ($_pMin == 1 && $_pMax == $num_of_items && $num_of_items == 1) {
        $page_no_label = '';
    } else {
        $page_no_label = $_pMin.' - '.$_pMax.' of '.$num_of_items.' Items';
    }
}

$xoopsTpl->assign('maxpage', ceil($num_of_items / $itemcount));
$xoopsTpl->assign('orderby', $textutil->html_special_chars($orderby));
$xoopsTpl->assign('order_dir', $order_dir);
$xoopsTpl->assign('page', $page);
$xoopsTpl->assign('itemcount', intval($itemcount));
$xoopsTpl->assign('num_of_items', $textutil->html_special_chars($num_of_items));
$xoopsTpl->assign('page_no_label', $textutil->html_special_chars($page_no_label));

// retrieve items
// ignore 'start' and 'rows' of criteria because already truncated by dump_item_id
//if( xnp_get_items( $xnpsid, $iids, array( 'orders' => $cri['orders'] ), $items ) != RES_OK ){
if (xnp_get_items($xnpsid, $iids, $cri, $items) != RES_OK) {
    xoonips_error_exit(500);
}

$item_htmls = array();
foreach ($items as $i) {
    if (array_key_exists($i['item_type_id'], $itemtypes)) {
        $itemtype = $itemtypes[$i['item_type_id']];
        $modname = $itemtype['name'];
        require_once XOOPS_ROOT_PATH.'/modules/'.$itemtype['viewphp'];
        if ($print && function_exists($modname.'GetPrinterFriendlyListBlock')) {
            eval('$html = '.$modname.'GetPrinterFriendlyListBlock( $i );');
        } elseif (function_exists($modname.'GetListBlock')) {
            eval('$html = '.$modname.'GetListBlock( $i );');
        } else {
            $html = '';
        }
        $item_htmls[] = array('html' => $html);
    }
}

$xoopsTpl->assign('item_htmls', $item_htmls);

if (isset($index_id)) {
    $xoopsTpl->assign('index_id', $index_id);
}

// assign export_enable variable if permitted
if (xoonips_is_user_export_enabled()) {
    $xoopsTpl->assign('export_enabled', 1);
}

if ($print) {
    //$xoopsTpl->assign('footer', $myxoopsConfigMetaFooter['footer'] );
    $xoopsTpl->assign('meta_copyright', $myxoopsConfigMetaFooter['meta_copyright']);
    $xoopsTpl->assign('meta_author', $myxoopsConfigMetaFooter['meta_author']);
    $xoopsTpl->assign('sitename', $myxoopsConfig['sitename']);

    $xconfig_handler = &xoonips_getormhandler('xoonips', 'config');
    $xoopsTpl->assign('printer_friendly_header', $xconfig_handler->getValue('printer_friendly_header'));
    $textutil = &xoonips_getutility('text');
    $xoopsTpl->assign('title', _MD_XOONIPS_ITEM_LISTING_ITEM);
    $xoopsTpl->assign('date', $textutil->html_special_chars(date(DATETIME_FORMAT, xoops_getUserTimestamp(time()))));
    $xoopsTpl->display('db:xoonips_itemselect_print.html');
    xoops_footer();
    exit();
} else {
    require XOOPS_ROOT_PATH.'/footer.php';
}

/**
 * @param $page integer current page number
 * @param $maxpage integer max page number
 *
 * @return array of integer page numbers
 */
function xoonips_get_selectable_page_number($page, $maxpage)
{
    //centering current page number(5th of $pages)
    $pages = array(min(max(1, $page - 4), max(1, $maxpage - 9)));
    for ($i = 1; $i < 10 && $pages[$i - 1] < $maxpage; ++$i) {
        $pages[$i] = $pages[$i - 1] + 1;
    }

    return $pages;
}
