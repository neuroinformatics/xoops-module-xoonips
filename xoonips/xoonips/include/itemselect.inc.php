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

require_once 'include/extra_param.inc.php';
/*
 *
 * $_POST['op'] :
 * advancedsearch: advanced search
 * quicksearch: xoonips search
 * itemtypesearch: item type search
 * itemsubtypesearch: advanced search by item subtype
 * related_to_from_index::list items of an index to select related_to item.
 * add_selected_item: list items of advanced search result
 * add_to_index: add selected items to an index.
 * select_item_advancedsearch: list and select items from result of advanced search
 * select_item_index: list and select items of an index
 * select_item_useritem: list and select items of user
 *
 */

// TODO: check uncommented NOT USED variables
$sess_orderby = isset($_SESSION['xoonips_order_by']) ? $_SESSION['xoonips_order_by'] : 'title';
$sess_orderdir = isset($_SESSION['xoonips_order_dir']) ? $_SESSION['xoonips_order_dir'] : ASC;
$request_vars = array(
    'op' => array('s', ''),
    'checkbox' => array('s', 'off'), // 'on' or 'off'
    'add_to_index_id' => array('i', false),
    'submit_url' => array('s', ''),
    'page' => array('i', 1),
    'item_per_page' => array('i', 20),
    'order_by' => array('s', $sess_orderby),
    'order_dir' => array('i', $sess_orderdir),
    'index_id' => array('i', false),
    'keyword' => array('n', ''),
    'search_itemtype' => array('s', ''),
    'print' => array('b', false),
    'search_cache_id' => array('i', null),
    'search_tab' => array('s', 'item'),
);
$formdata = &xoonips_getutility('formdata');
foreach ($request_vars as $key => $meta) {
    list($type, $default) = $meta;
    $$key = $formdata->getValue('both', $key, $type, false, $default);
}
$search_var = $formdata->getValueArray('both', 'search_var', 's', false, array());
$selected = $formdata->getValueArray('both', 'selected', 'i', false, array());
$selected_hidden = $formdata->getValueArray('both', 'selected_hidden', 's', false, array());
$selected_original = $formdata->getValueArray('both', 'selected_original', 's', false, array());
$initially_selected = $formdata->getValueArray('both', 'initially_selected', 's', false, array());

// check form params
if ('' != $op && !preg_match('/\A[a-z_]+\z/i', $op)) {
    xoonips_error_exit(400);
}
if (!in_array($checkbox, array('on', 'off'))) {
    xoonips_error_exit(400);
}
if (!in_array($submit_url, array('', 'itemselect.php')) && !preg_match('/\A'.preg_quote(XOONIPS_URL.'/', '/').'[0-9a-z_]+\.php\z/', $submit_url)) {
    xoonips_error_exit(400);
}
if (!in_array($order_by, array('title', 'doi', 'last_update_date', 'creation_date', 'publication_date'))) {
    unset($_SESSION['xoonips_order_by']);
    unset($_SESSION['xoonips_order_dir']);
    xoonips_error_exit(400);
}
if (!in_array($search_itemtype, array('', 'all', 'basic', 'metadata')) && !preg_match('/\Axnp[a-z0-9_]+\z/i', $search_itemtype)) {
    xoonips_error_exit(400);
}
if (!in_array($search_tab, array('item', 'file', 'metadata'))) {
    xoonips_error_exit(400);
}

$_SESSION['xoonips_order_by'] = $order_by;
$_SESSION['xoonips_order_dir'] = $order_dir;

$xnpsid = $_SESSION['XNPSID'];

$textutil = &xoonips_getutility('text');

// Variables need to be passed by 'GET' in coming from xoonips's top("Redirect from login" is placed among).
// But variables need to be passed by 'POST' in advanced search. So 'GET' is changed to 'POST'.
$formdata->copy('get', 'post');

$initially_selected = array();

$itemtypes = array();
$itemtype_names = array();
$tmp = array();
if (RES_OK != xnp_get_item_types($tmp)) {
    xoonips_error_exit(500);
} else {
    foreach ($tmp as $i) {
        $itemtypes[$i['item_type_id']] = $i;
        $itemtype_names[$i['name']] = $i;
    }
}

$xoopsTpl->assign('checkbox', $checkbox);
$xoopsTpl->assign('add_to_index_id', $add_to_index_id);

$iids = array();
$items = array();
$cri = array();
$errorMessage = null;
switch ($op) {
case 'advancedsearch':
    $errorMessage = '';
    $iids = xoonips_advanced_search($keyword, $search_itemtype, false, $errorMessage, $search_cache_id);

    if (!$add_to_index_id) {
        $xoopsTpl->assign('print_enabled', true);
    }
    if (!$add_to_index_id) {
        $xoopsTpl->assign('export_enabled', xoonips_is_user_export_enabled());
    }
    $xoopsTpl->assign('op', 'advancedsearch');
    $xoopsTpl->assign('error_message', $textutil->html_special_chars($errorMessage));

    $xoopsOption['template_main'] = 'xoonips_itemselect_listitem.html';
    break;
case 'quicksearch':
    $errorMessage = '';
    $iids = xoonips_quick_search($keyword, $search_itemtype, false, $errorMessage, $search_cache_id, $search_tab);
    $xoopsTpl->assign('export_enabled', xoonips_is_user_export_enabled());
    $xoopsTpl->assign('print_enabled', 1);
    $xoopsTpl->assign('error_message', $textutil->html_special_chars($errorMessage));
    $xoopsTpl->assign('quick_search_keyword', $textutil->html_special_chars($keyword));
    $xoopsTpl->assign('keyword', $textutil->html_special_chars($keyword));

    $page = $page;
    $url_orderby = urlencode($order_by);
    $url_orderdir = urlencode($order_dir);

    $url_keyword = urlencode($keyword);
    $url_search_itemtype = urlencode($search_itemtype);
    $xoopsTpl->assign('url_for_get', XOOPS_URL."/modules/xoonips/itemselect.php?op=$op&amp;search_itemtype=$url_search_itemtype&amp;keyword=$url_keyword"."&amp;page=$page&amp;item_per_page=$item_per_page&amp;orderby=$url_orderby&amp;orderdir=$url_orderdir");

    $metadata_count = xoonips_get_metadata_count_from_search_cache($search_cache_id);
    $item_count = xoonips_get_item_count_from_search_cache($search_cache_id);
    $file_count = xoonips_get_file_count_from_search_cache($search_cache_id);

    $item_type_handler = &xoonips_getormhandler('xoonips', 'item_type');
    $criteria = new Criteria('name', $search_itemtype);
    $item_types = &$item_type_handler->getObjects($criteria);
    if (count($item_types) > 0) {
        $xoopsTpl->assign(
            'search_tabs', array(
            array('search_tab' => 'item', 'label' => _MD_XOONIPS_ITEM_SEARCH_TAB_ITEM, 'count' => $item_count),
            array('search_tab' => 'file', 'label' => _MD_XOONIPS_ITEM_SEARCH_TAB_FILE, 'count' => $file_count),
            )
        );
    } elseif ('all' == $search_itemtype) {
        $xoopsTpl->assign(
            'search_tabs', array(
            array('search_tab' => 'item', 'label' => _MD_XOONIPS_ITEM_SEARCH_TAB_ITEM, 'count' => $item_count),
            array('search_tab' => 'metadata', 'label' => _MD_XOONIPS_ITEM_SEARCH_TAB_METADATA, 'count' => $metadata_count),
            array('search_tab' => 'file', 'label' => _MD_XOONIPS_ITEM_SEARCH_TAB_FILE, 'count' => $file_count),
            )
        );
    }

    if ('metadata' == $search_tab || 'metadata' == $search_itemtype) {
        $start = ($page - 1) * $item_per_page;

        // retrieving metadata and constructing associative array of the items
        // that has same structure of a xoonips item.
        if ('title' == $order_by) {
            $order_sql = 'order by tmetadata.title '.(ASC == $order_dir ? 'ASC' : 'DESC');
        } else {
            $order_sql = 'order by tmetadata.datestamp '.(ASC == $order_dir ? 'ASC' : 'DESC');
        }

        $table_metadata = $xoopsDB->prefix('xoonips_oaipmh_metadata');
        $table_repo = $xoopsDB->prefix('xoonips_oaipmh_repositories');
        $table_cache = $xoopsDB->prefix('xoonips_search_cache');
        $table_cache_metadata = $xoopsDB->prefix('xoonips_search_cache_metadata');
        $sql = 'SELECT tmetadata.identifier, tmetadata.repository_id, title'
            ." FROM $table_cache AS tcache, ${table_metadata} AS tmetadata, ${table_repo} AS trepo, ${table_cache_metadata} AS tcachemeta"
            ." WHERE tcache.search_cache_id = $search_cache_id"
            .' AND tcachemeta.search_cache_id = tcache.search_cache_id'
            .' AND tmetadata.identifier = tcachemeta.identifier'
            .' AND tmetadata.repository_id = trepo.repository_id'
            .' AND trepo.enabled=1'
            .' AND trepo.deleted!=1'
            ." $order_sql LIMIT $item_per_page OFFSET $start";
        $result = $xoopsDB->query($sql);
        // get number of rows
        $sql = "select count(*) from $table_cache_metadata where search_cache_id = $search_cache_id";
        $result2 = $xoopsDB->query($sql);
        list($total_count) = $xoopsDB->fetchRow($result2);

        if ($page * $item_per_page >= $total_count) {
            $page = 1; //reset page number
        }

        // query to database
        $items = array();
        $metadata = array();
        while ($row = $xoopsDB->fetchRow($result)) {
            $ar = array();
            list($ar['identifier'], $ar['repository_id'], $title) = $row;
            $ar['title'] = $textutil->html_special_chars($title);
            $ar['params'] = urlencode(
                implode(
                    '&', array(
                    'op'.'='.urlencode($op),
                    'keyword'.'='.urlencode($keyword),
                    'search_itemtype'.'='.urlencode($search_itemtype),
                    'search_cache_id'.'='.urlencode($search_cache_id),
                    'order_by'.'='.urlencode($order_by),
                    'order_dir'.'='.urlencode($order_dir),
                    'item_per_page'.'='.urlencode($item_per_page),
                    'page'.'='.urlencode($page),
                    'search_tab'.'='.urlencode($search_tab),
                    )
                )
            );
            $items[] = $ar;
            $metadata[] = $ar;
        }
        $xoopsTpl->assign('metadata', $metadata);
        $xoopsTpl->assign('maxpage', ceil($total_count / $item_per_page));
        $xoopsTpl->assign('page_no_label', xoonips_get_page_number_label($metadata_count, $start + 1, count($metadata)));
        $xoopsTpl->assign('pages', xoonips_get_selectable_page_number($page, ceil($total_count / $item_per_page)));
        $xoopsTpl->assign('op', $op);
    }
    if ('metadata' == $search_itemtype) {
        $xoopsOption['template_main'] = 'xoonips_itemselect_metadata.html';
    } else {
        $xoopsOption['template_main'] = 'xoonips_itemselect_tabbed.html';
    }

    $xoopsTpl->assign('op', 'quicksearch');
    $submit_url = 'itemselect.php';

    break;
case 'itemtypesearch':
    $iids = xoonips_itemtype_search($keyword, $search_itemtype, false, $errorMessage, $search_cache_id, 'item');
    $xoopsTpl->assign('print_enabled', true);
    $xoopsTpl->assign('op', 'itemtypesearch');
    $xoopsOption['template_main'] = 'xoonips_itemselect_listitem.html';
    $submit_url = 'itemselect.php';
    break;
case 'itemsubtypesearch':
    $iids = xoonips_itemsubtype_search($keyword, $search_itemtype, false, $errorMessage, $search_cache_id, 'item');
    $xoopsTpl->assign('print_enabled', true);
    $xoopsTpl->assign('op', 'itemsubtypesearch');
    $xoopsOption['template_main'] = 'xoonips_itemselect_listitem.html';
    $submit_url = 'itemselect.php';
    break;
case 'add_selected_item':
    xoonips_deny_guest_access();

    $iids = xoonips_advanced_search($keyword, $search_itemtype, false, $errorMessage, $search_cache_id);
    $iids = _xoonips_filter_add_items($add_to_index_id, $iids);

    // get registered items of index(of $add_to_index_id) to $initially_selected
    $registered_iids = array();
    $index_item_link_handler = &xoonips_getormhandler('xoonips', 'index_item_link');
    $criteria = new Criteria('index_id', (int) $add_to_index_id);
    $index_item_links = &$index_item_link_handler->getObjects($criteria);
    $initially_selected = array();
    foreach ($index_item_links as $i) {
        if (in_array($i->get('item_id'), $iids)) {
            $initially_selected[] = $i->get('item_id');
        }
    }

    // selected = selected OR ( registered_iids AND iids )
    $selected = array_unique(array_merge($selected, $initially_selected));

    $xoopsTpl->assign('selected', $selected);
    $xoopsTpl->assign('initially_selected', $initially_selected);

    xoonips_add_selected_item($add_to_index_id, $xoopsUser->getVar('uid'), $initially_selected, $selected);
    header('Location: '.XOOPS_URL."/modules/xoonips/listitem.php?index_id=$add_to_index_id");
    exit();
case 'add_to_index':
    $errorMessage = '';
    $iids = xoonips_advanced_search($keyword, $search_itemtype, false, $errorMessage, $search_cache_id);
    $iids = _xoonips_filter_add_items($add_to_index_id, $iids);
    $xoopsTpl->assign('error_message', $textutil->html_special_chars($errorMessage));

    $xoopsTpl->assign('add_to_index_id', $add_to_index_id);
    $xoopsTpl->assign('selected', $selected);
    $xoopsTpl->assign('op', 'add_to_index');
    $xoopsOption['template_main'] = 'xoonips_itemselect_add_to_index.html';
    break;
case 'select_item_advancedsearch':
    $selected = $selected_original; //initialize $selected
    $selected_hidden = array();
    // no break
case 'select_item_advancedsearch_pagenavi':
    $errorMessage = '';
    $iids = xoonips_advanced_search($keyword, $search_itemtype, false, $errorMessage, $search_cache_id);
    $xoopsTpl->assign('error_message', $textutil->html_special_chars($errorMessage));
    $xoopsTpl->assign('op', 'select_item_advancedsearch');
    $xoopsOption['template_main'] = 'xoonips_itemselect_select_item.html';
    break;
case 'select_item_index':
    // select item from indexed item
    // - initialize $selected
    $selected = $selected_original;
    $selected_hidden = array();
    // - reset page number
    $page = 1;
    // no break
case 'select_item_index_pagenavi':
    // change page no of select item from indexed item
    if (IID_ROOT == $index_id) {
        // access denied to IID_ROOT
        header('Location: '.XOOPS_URL.'/modules/xoonips/itemselect.php');
        exit();
    }
    $myuid = $xoopsUser->getVar('uid');
    // get private index id
    $xu_ohandler = &xoonips_getormhandler('xoonips', 'users');
    $xu_obj = &$xu_ohandler->get($myuid);
    $private_index_id = $xu_obj->get('private_index_id');

    $index_handler = &xoonips_gethandler('xoonips', 'index');
    if (isset($itemselect_private_only) && $itemselect_private_only) {
        // only if select items from private indexes.
        // this case is used for transferring items in user menu
        $index_obj = &$index_handler->getIndexObject($index_id);
        if ($index_obj->get('uid') != $myuid || OL_PRIVATE != $index_obj->get('open_level')) {
            // if target index is not user's own index, override index id to
            // user's private root index
            $index_id = $private_index_id;
        }
    }
    // get item ids into target index
    $iids = $index_handler->getItemIds($index_id, $myuid, 'read');
    $indexes_vars = $index_handler->getIndexPathArray($index_id, 's');
    if ($indexes_vars[0]['index_id'] == $private_index_id) {
        $indexes_vars[0]['title'] = XNP_PRIVATE_INDEX_TITLE;
    }
    // assign template variables
    $xoopsTpl->assign('indexes', $indexes_vars);
    $xoopsTpl->assign('op', 'select_item_index');
    $xoopsOption['template_main'] = 'xoonips_itemselect_select_item.html';
    break;
case 'select_item_useritem'://select item from user's items
    $selected = $selected_original; //initialize $selected
    $selected_hidden = array();
    // no break
case 'select_item_useritem_pagenavi':
    $iids = array();
    if (RES_OK != xnp_dump_item_id($xnpsid, $cri, $iids)) {
        xoonips_error_exit(500);
    }

    $xoopsTpl->assign('op', 'select_item_useritem');
    $xoopsOption['template_main'] = 'xoonips_itemselect_select_item.html';
    break;
case 'related_to_from_index':
     $items = xoonips_get_indexed_items($index_id, empty($xoopsUser) ? UID_GUEST : $xoopsUser->getVar('uid'));
     $iids = array();
    foreach ($items as $item) {
        $basic = $item->getVar('basic');
        $iids[] = $basic->get('item_id');
    }
    break;
default:
    xoonips_deny_guest_access();

    $xoopsTpl->assign('export_enabled', xoonips_is_user_export_enabled());
    $xoopsTpl->assign('print_enabled', true);
    $uid = $xoopsUser->getVar('uid');
    $iids = array();
    xnp_get_own_public_item_id($xnpsid, $uid, $iids);
    $xoopsOption['template_main'] = 'xoonips_itemselect_listitem.html';
    break;
}

if (isset($itemselect_private_only) && $itemselect_private_only
    && count($iids) > 0
) {
    $iids = get_only_own_items($iids);
}

$selected = array_merge($selected, $selected_hidden);

if ('metadata' == $search_tab || 'metadata' == $search_itemtype) {
} else {
    $start = ($page - 1) * $item_per_page;
    if ($start >= count($iids)) {
        $page = 1;
        $start = 0;
    }

    if ('publication_date' == $order_by) {
        $cri = array('orders' => array(array('name' => 'publication_year', 'order' => $order_dir), array('name' => 'publication_month', 'order' => $order_dir), array('name' => 'publication_mday', 'order' => $order_dir)));
    } else {
        $cri = array('orders' => array(array('name' => $order_by, 'order' => $order_dir)));
    }
    if (RES_OK != xnp_get_items($xnpsid, $iids, $cri, $items)) {
        xoonips_error_exit(500);
    }
    $xoopsTpl->assign('ids', $iids);

    //slice items array
    $xoopsTpl->assign('items', $items);

    $item_htmls = array();
    $display_item_ids = array();
    $c = 0;
    $item_template_vars = array();
    foreach ($items as $i) {
        if ($start <= $c && $c < $start + $item_per_page) {
            if (array_key_exists($i['item_type_id'], $itemtypes)) {
                $itemtype = $itemtypes[$i['item_type_id']];
                $modname = $itemtype['name'];
                require_once XOOPS_ROOT_PATH.'/modules/'.$itemtype['viewphp'];
                if ($print && function_exists($modname.'GetPrinterFriendlyListBlock')) {
                    eval('$html = '.$modname.'GetPrinterFriendlyListBlock( $i );');
                } elseif (function_exists($modname.'GetListBlock')) {
                    eval('$html = '.$modname.'GetListBlock( $i );');
                }
                if (in_array($i['item_id'], $selected)) {
                    $sel = 1;
                } else {
                    $sel = 0;
                }
                $item_htmls[] = array('selected' => $sel,
                                       'item_id' => $i['item_id'],
                                       'html' => $html, );
                $display_item_ids[] = $i['item_id'];
            }
        }
        ++$c;
    }

    $selected_hidden = array();
    foreach ($selected as $sel) {
        if (!in_array($sel, $display_item_ids)) {
            $selected_hidden[] = $sel;
        }
    }
    $xoopsTpl->assign('item_template_vars', $item_template_vars);
    $xoopsTpl->assign('item_htmls', $item_htmls);
    $xoopsTpl->assign('maxpage', ceil(count($items) / $item_per_page));
    $xoopsTpl->assign('page_no_label', xoonips_get_page_number_label(count($items), $start + 1, count($item_htmls)));
    $xoopsTpl->assign('pages', xoonips_get_selectable_page_number($page, ceil(count($items) / $item_per_page)));
    $xoopsTpl->assign('selected_hidden', $selected_hidden);
    $xoopsTpl->assign('selected_original', $selected_original);
}

$xoopsTpl->assign('page', intval($page));
$xoopsTpl->assign('order_by', $order_by);
$xoopsTpl->assign('order_dir', intval($order_dir));
$xoopsTpl->assign('item_per_page', intval($item_per_page));
$xoopsTpl->assign('search_cache_id', intval($search_cache_id));
$xoopsTpl->assign('search_itemtype', $textutil->html_special_chars($search_itemtype));
$xoopsTpl->assign('search_tab', $search_tab);
$xoopsTpl->assign('submit_url', $textutil->html_special_chars($submit_url));
$escaped_search_var = array();
foreach ($search_var as $val) {
    $escaped_search_var[] = $textutil->html_special_chars($val);
}
$xoopsTpl->assign('search_var', $escaped_search_var);
$xoopsTpl->assign('isKHTML', (bool) (stristr($_SERVER['HTTP_USER_AGENT'], 'khtml')));
$xoopsTpl->assign('extra_param', $textutil->html_special_chars(json_encode(xoonips_extra_param_restore())));

if (isset($pankuzu)) {
    $xoopsTpl->assign('pankuzu', $pankuzu);
}
if (isset($index_id)) {
    $xoopsTpl->assign('index_id', $index_id);
}

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
//
// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
/**
 * @param $keyword search keyword
 * @param $search_itemtype 'all'/'basic'/each item type name(ex:xnppaper)
 * @param $search_only_private boolean true if search only private items
 * @param $errorMessage reference of string to write errorMessage
 * @param $search_content_type 'item'/'metadata'/'file'
 *
 * @return array id of content of search result
 */
function xoonips_advanced_search($keyword, $search_itemtype, $search_only_private, &$errorMessage, &$search_cache_id)
{
    global $xoopsUser;

    $iids = array();
    $search_var = array(); //not used here
    $res = xnpSearchExec('advancedsearch', $keyword, $search_itemtype, $search_only_private, $errorMessage, $iids, $search_var, $search_cache_id, 'item');

    // record events(advanced search)
    $item_type_handler = &xoonips_getormhandler('xoonips', 'item_type');
    $item_types = &$item_type_handler->getObjects();
    $search_target = array();
    foreach ($item_types as $item_type) {
        if (isset($_POST[$item_type->get('name')])) {
            $search_target[] = $item_type->get('name');
        }
    }

    $search_keywords = array(); // characeter strings that brings search condition together
    $formdata = &xoonips_getutility('formdata');
    $mysearch_vars = $formdata->getValueArray('post', 'search_var', 's', false);
    if (!empty($mysearch_vars)) {
        foreach ($mysearch_vars as $var_name) {
            $ar = explode('_', $var_name);
            $var = $formdata->getValue('post', $var_name, 's', false);
            if (in_array($ar[0], $search_target) && !is_null($var)) {
                if (!is_array($var)) {
                    if (0 != strlen($var)) {
                        $search_keywords[] = urlencode($var_name).'='.urlencode($var);
                    }
                } else {
                    foreach ($var as $tmp_words) {
                        $search_keywords[] = urlencode($var_name).'='.urlencode($tmp_words);
                    }
                }
            }
        }
        $eventlog_handler = &xoonips_getormhandler('xoonips', 'event_log');
        $eventlog_handler->recordAdvancedSearchEvent($search_keywords);
    }

    return $iids;
}

function xoonips_quick_search($keyword, $search_itemtype, $search_only_private, &$errorMessage, &$search_cache_id, $search_content_type, $file_or_item_metadata = 'all')
{
    $iids = array();
    $search_var = array(); //not used here
    $res = xnpSearchExec('quicksearch', $keyword, $search_itemtype, $search_only_private, $errorMessage, $iids, $search_var, $search_cache_id, $search_content_type, $file_or_item_metadata);

    // record events(quick search)
    // don't escape character strings.
    $eventlog_handler = &xoonips_getormhandler('xoonips', 'event_log');
    $eventlog_handler->recordQuickSearchEvent($search_itemtype, $keyword);

    return $iids;
}

function xoonips_itemtype_search($keyword, $search_itemtype, $search_only_private, &$errorMessage, &$search_cache_id, $search_content_type, $file_or_item_metadata = 'all')
{
    $iids = array();
    $search_var = array(); //not used here
    $res = xnpSearchExec('itemtypesearch', $keyword, $search_itemtype, $search_only_private, $errorMessage, $iids, $search_var, $search_cache_id, $search_content_type, $file_or_item_metadata);

    // record events(quick search)
    // don't escape character strings.
    $eventlog_handler = &xoonips_getormhandler('xoonips', 'event_log');
    $eventlog_handler->recordQuickSearchEvent($search_itemtype, $keyword);

    return $iids;
}

function xoonips_itemsubtype_search($keyword, $search_itemtype, $search_only_private, &$errorMessage, &$search_cache_id)
{
    global $xoopsUser;

    $iids = array();
    $search_var = array(); //not used here
    $res = xnpSearchExec('itemsubtypesearch', $keyword, $search_itemtype, $search_only_private, $errorMessage, $iids, $search_var, $search_cache_id, 'item');

    // record events(advanced search)
    $item_type_handler = &xoonips_getormhandler('xoonips', 'item_type');
    $item_types = &$item_type_handler->getObjects();
    $search_target = array();
    foreach ($item_types as $item_type) {
        if (isset($_POST[$item_type->get('name')])) {
            $search_target[] = $item_type->get('name');
        }
    }

    $search_keywords = array(); // characeter strings that brings search condition together
    $formdata = &xoonips_getutility('formdata');
    $mysearch_vars = $formdata->getValueArray('post', 'search_var', 's', false);
    if (!empty($mysearch_vars)) {
        foreach ($mysearch_vars as $var_name) {
            $ar = explode('_', $var_name);
            $var = $formdata->getValue('post', $var_name, 's', false);
            if (in_array($ar[0], $search_target) && !is_null($var)) {
                if (!is_array($var)) {
                    if (0 != strlen($var)) {
                        $search_keywords[] = urlencode($var_name).'='.urlencode($var);
                    }
                } else {
                    foreach ($var as $tmp_words) {
                        $search_keywords[] = urlencode($var_name).'='.urlencode($tmp_words);
                    }
                }
            }
        }
        $eventlog_handler = &xoonips_getormhandler('xoonips', 'event_log');
        $eventlog_handler->recordAdvancedSearchEvent($search_keywords);
    }

    return $iids;
}

/**
 * return array of XooNIpsItemCompo that is registerd to the index.
 *
 * @param $index_id integer index id
 * @param $uid integer uid (for permission check)
 *
 * @return array XooNIpsItemCompo(s)
 */
function xoonips_get_indexed_items($index_id, $uid)
{
    global $xoopsDB;

    $index_item_link_handler = &xoonips_getormhandler('xoonips', 'index_item_link');
    $join = new XooNIpsJoinCriteria('xoonips_index', 'index_id', 'index_id', 'LEFT', 'tiil');
    $criteria = new Criteria('tiil.index_id', $index_id);
    $index_item_links = &$index_item_link_handler->getObjects($criteria, false, '', false, $join);

    $items = array();
    foreach ($index_item_links as $i) {
        $xoonips_item_handler = &xoonips_getormcompohandler('xoonips', 'item');
        if (!$xoonips_item_handler->getPerm($i->get('item_id'), $uid, 'read')) {
            continue;
        }
        $items[] = $xoonips_item_handler->get($i->get('item_id'));
    }

    return $items;
}

/**
 * add item to index, set certify_state and lock item and indexes.
 *
 * @param $index_id integer id of index item is registerd to
 * @param $uid integer user id of executor
 * @param old_selected_item_ids array id of all of already registerd items to the index( $index_id)
 * @param new_selected_item_ids array id of all of items to be registered to the index(  $index_id)
 */
function xoonips_add_selected_item($index_id, $uid, $old_selected_item_ids, $new_selected_item_ids)
{
    global $xoopsDB;

    $uid_list = array(); // uid(s) who receive notification.
    $add_item_ids = array_diff($new_selected_item_ids, $old_selected_item_ids);
    $del_item_ids = array_diff($old_selected_item_ids, $new_selected_item_ids);

    $index_handler = &xoonips_getormhandler('xoonips', 'index');
    $index = $index_handler->get($index_id);

    switch ($index->get('open_level')) {
    case OL_GROUP_ONLY:
        // get uid list of group admins
        $xgroup_handler = &xoonips_gethandler('xoonips', 'group');
        $uid_list = $xgroup_handler->getUserIds($index->get('gid'), true);
        break;
    case OL_PUBLIC:
        // get uid list of moderators
        $xoonips_users_handler = &xoonips_getormhandler('xoonips', 'users');
        $xoonips_config_handler = &xoonips_getormhandler('xoonips', 'config');
        $moderator_gid = $xoonips_config_handler->getValue('moderator_gid');
        $member_handler = &xoops_gethandler('member');
        $users = $member_handler->getUsersByGroup($moderator_gid, true);
        foreach ($users as $u) {
            if ($xoonips_users_handler->get($u->getVar('uid'))) {
                $uid_list[] = $u->getVar('uid');
            }
        }
        break;
    }

    $xoonips_config_handler = &xoonips_getormhandler('xoonips', 'config');

    $index_item_link_handler = &xoonips_getormhandler('xoonips', 'index_item_link');

    // add selected item into index
    foreach ($add_item_ids as $item_id) {
        // can't add items when the item is certified already or is in pending state.
        $criteria = new CriteriaCompo(new Criteria('index_id', $index_id));
        $criteria = $criteria->add(new Criteria('item_id', $item_id));
        $index_item_link = &$index_item_link_handler->getObjects($criteria);
        if (isset($index_item_link[0]) && CERTIFY_REQUIRED == $index_item_link[0]->get('certify_state')
            || isset($index_item_link[0]) && CERTIFIED == $index_item_link[0]->get('certify_state')
        ) {
            continue;
        }

        // add
        $certify_item = $xoonips_config_handler->getValue(XNP_CONFIG_CERTIFY_ITEM_KEY);
        $index_item_link = $index_item_link_handler->create();
        $index_item_link->set('index_id', $index_id);
        $index_item_link->set('item_id', $item_id);
        $index_item_link->set('certify_state', 'auto' == $certify_item ? CERTIFIED : CERTIFY_REQUIRED);
        if (!$index_item_link_handler->insert($index_item_link)) {
            xoonips_error_exit(500);
        }

        $changelogs_handler = &xoonips_getormhandler('xoonips', 'changelog');
        $change_log = $changelogs_handler->create();
        $change_log->set('uid', $uid);
        $change_log->set('item_id', $item_id);
        $change_log->set('log_date', time());
        $change_log->set('log', sprintf(_MD_XOONIPS_ITEM_CHANGE_LOG_AUTOFILL_TEXT, implode(_MD_XOONIPS_ITEM_CHANGE_LOG_AUTOFILL_DELIMITER, array(_MD_XOONIPS_ITEM_INDEX_LABEL))));

        $item_handler = &xoonips_getormcompohandler('xoonips', 'item');
        $item = $item_handler->get($item_id);
        $item_basic = $item->getVar('basic');
        $change_logs = $item->getVar('changelogs');

        $item_basic->set('last_update_date', time());
        $change_logs[] = $change_log;

        $item->setVar('changelogs', $change_logs);
        $item->setVar('basic', $item_basic);

        if (!$item_handler->insert($item)) {
            xoonips_error_exit(500);
        }
        trigger_error('TODO lock item and indexes');

        // lock if imported items are registered to public/group index and certify required
        $item_basic_handler = &xoonips_getormhandler('xoonips', 'item_basic');
        $index_item_links = &$item->getVar('indexes');
        $index_handler = &xoonips_getormhandler('xoonips', 'index');
        foreach ($index_item_links as $index_item_link) {
            if (CERTIFY_REQUIRED == $index_item_link->get('certify_state')) {
                $index = $index_handler->get($index_item_link->get('index_id'));
                if (OL_PUBLIC == $index->get('open_level')
                    || OL_GROUP_ONLY == $index->get('open_level')
                ) {
                    $basic = &$item->getVar('basic');
                    $item_basic_handler->lockItemAndIndexes($basic->get('item_id'), $index_item_link->get('index_id'));
                }
            }
        }

        // record events(update item)
        $eventlog_handler = &xoonips_getormhandler('xoonips', 'event_log');
        $eventlog_handler->recordUpdateItemEvent($item_id);

        if (OL_PUBLIC == $index->get('open_level')
            || OL_GROUP_ONLY == $index->get('open_level')
        ) {
            $certify_item = $xoonips_config_handler->getValue(XNP_CONFIG_CERTIFY_ITEM_KEY);
            switch ($certify_item) {
            case 'auto':
                // record events(certify item)
                $eventlog_handler->recordCertifyItemEvent($item_id, $index_id);
                break;
            case 'on':
                global $xoopsModule;
                $notification_handler = &xoops_gethandler('notification');
                //define tags here for notification message
                $tags = array();
                $tags['ITEM_URL'] = XOOPS_URL.'/modules/'.$xoopsModule->dirname()."/detail.php?item_id=${item_id}";
                $tags['CERTIFY_URL'] = XOOPS_URL.'/modules/'.$xoopsModule->dirname().'/certify.php';
                $tags['INDEX'] = xnpGetIndexPathString($_SESSION['XNPSID'], $index_id);
                $notification_handler = &xoops_gethandler('notification');
                $result = $notification_handler->triggerEvent('administrator', 0, 'item_certify_request', $tags, $uid_list);
                break;
            }
        }
    }
}

function xoonips_get_page_number_label($total, $offset, $limit)
{
    if ((int) $total > 0) {
        return (int) $offset.' - '.((int) $offset + (int) $limit - 1).' of '.(int) $total.' Items';
    } else {
        return '0 - 0 of 0 Items';
    }
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

/**
 * return number of items of the file search cache.
 *
 * @param $search_cache_id integer cache id
 *
 * @return int number of items
 */
function xoonips_get_file_count_from_search_cache($search_cache_id)
{
    global $xoopsDB, $xoopsUser;

    $search_cache_file_handler = &xoonips_getormhandler('xoonips', 'search_cache_file');
    $join = new XooNIpsJoinCriteria('xoonips_file', 'file_id', 'file_id', 'INNER', 'tf');
    $join->cascade(new XooNIpsJoinCriteria('xoonips_item_basic', 'item_id', 'item_id', 'INNER', 'tb'), 'tf', true);

    $search_cache_file = &$search_cache_file_handler->getObjects(new Criteria('search_cache_id', $search_cache_id), false, 'tb.item_id', false, $join);
    if (0 == count($search_cache_file)) {
        return 0;
    }

    $c = 0;
    $item_handler = &xoonips_getormcompohandler('xoonips', 'item');
    foreach ($search_cache_file as $item) {
        if ($item_handler->getPerm($item->getExtraVar('item_id'), $xoopsUser ? $xoopsUser->getVar('uid') : UID_GUEST, 'read')) {
            ++$c;
        }
    }

    return $c;
}

/**
 * return number of items of the item search cache.
 *
 * @param $search_cache_id integer cache id
 *
 * @return int number of items
 */
function xoonips_get_item_count_from_search_cache($search_cache_id)
{
    global $xoopsDB, $xoopsUser;

    $search_cache_item_handler = &xoonips_getormhandler('xoonips', 'search_cache_item');
    $join = new XooNIpsJoinCriteria('xoonips_item_basic', 'item_id', 'item_id', 'INNER', 'tb');

    $search_cache_item = &$search_cache_item_handler->getObjects(new Criteria('search_cache_id', $search_cache_id), false, '', false, $join);
    if (0 == count($search_cache_item)) {
        return 0;
    }

    $c = 0;
    $item_handler = &xoonips_getormcompohandler('xoonips', 'item');
    foreach ($search_cache_item as $item) {
        if ($item_handler->getPerm($item->get('item_id'), $xoopsUser ? $xoopsUser->getVar('uid') : UID_GUEST, 'read')) {
            ++$c;
        }
    }

    return $c;

    /*
    $search_cache_item =& $search_cache_item_handler->getObjects( new Criteria( 'search_cache_id', $search_cache_id ), false, 'count(tb.item_id)', false, $join );
    if( count( $search_cache_item ) == 0 ) return 0;
    var_dump( $search_cache_item[0] -> getExtraVar( 'count(tb.item_id)' ) );
    return $search_cache_item[0] -> getExtraVar( 'count(tb.item_id)' );
    */
}

/**
 * return number of items of the metadata search cache.
 *
 * @param $search_cache_id integer cache id
 *
 * @return int number of metadata
 */
function xoonips_get_metadata_count_from_search_cache($search_cache_id)
{
    global $xoopsDB;

    $search_cache_metadata_handler = &xoonips_getormhandler('xoonips', 'search_cache_metadata');
    $search_cache_metadata = &$search_cache_metadata_handler->getObjects(new Criteria('search_cache_id', $search_cache_id));

    return count($search_cache_metadata);
}

/**
 * get only own item ids(item's uid == $xoopsUser->getVar('uid')) from given item id array.
 *
 * @param array $item_ids array of item id
 *
 * @return array item id of own items
 */
function get_only_own_items($item_ids)
{
    global $xoopsUser;

    if (!is_array($item_ids) || 0 == count($item_ids)) {
        return array();
    }

    $basic_handler = &xoonips_getormhandler('xoonips', 'item_basic');
    $criteria = new CriteriaCompo(new Criteria('item_id', '('.implode(',', $item_ids).')', 'IN'));
    $criteria->add(new Criteria('uid', $xoopsUser->getVar('uid')));
    $result = array();
    foreach ($basic_handler->getObjects($criteria) as $basic) {
        $result[] = $basic->get('item_id');
    }

    return $result;
}

/**
 * filter for add items to index feature.
 *
 * @param int        $index_id target index id
 * @param array(int) $iids     item ids
 *
 * @return array(int) filtered item ids
 */
function _xoonips_filter_add_items($index_id, $iids)
{
    $lock_handler = &xoonips_getormhandler('xoonips', 'item_lock');
    $idx_handler = &xoonips_getormhandler('xoonips', 'index');
    $ib_handler = &xoonips_getormhandler('xoonips', 'item_basic');
    // filter currently locked items
    if (is_array($iids) && !empty($iids)) {
        $criteria = new CriteriaCompo(new Criteria('lock_count', 0, '!='));
        $criteria->add(new Criteria('item_id', '('.implode(',', $iids).')', 'IN'));
        $lock_objs = $lock_handler->getObjects($criteria, 'item_id');
        $lock_iids = array();
        foreach ($lock_objs as $lock_obj) {
            $lock_iids[] = $lock_obj->get('item_id');
        }
        $iids = array_diff($iids, $lock_iids);
    }
    // filter other contributer's item if selected index id is private
    if (is_array($iids) && !empty($iids)) {
        $idx_obj = &$idx_handler->get($index_id);
        if (false !== $idx_obj && OL_PRIVATE == $idx_obj->get('open_level')) {
            $uid = intval($idx_obj->get('uid'));
            $criteria = new CriteriaCompo(new Criteria('uid', $uid));
            $criteria->add(new Criteria('item_id', '('.implode(',', $iids).')', 'IN'));
            $ib_objs = $ib_handler->getObjects($criteria, 'item_id');
            $iids = array();
            foreach ($ib_objs as $ib_obj) {
                $iids[] = $ib_obj->get('item_id');
            }
        }
    }

    return $iids;
}
