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

//  select items

// This page can't be cached. Results of search(cached before login) don't display after login.
session_cache_limiter('none');
$xoopsOption['pagetype'] = 'user';
require 'include/common.inc.php';

$xnpsid = $_SESSION['XNPSID'];

require_once 'include/lib.php';
require_once 'include/AL.php';

// If not a user, redirect
if (!is_object($xoopsUser)) {
    if (!xnp_is_valid_session_id($xnpsid)) {
        // User is guest group, and guest isn't admitted to access the page.
        // -> display login block.
        redirect_header(XOOPS_URL.'/modules/xoonips/user.php', 3, _MD_XOONIPS_ITEM_FORBIDDEN);
        exit();
    }
}

require_once __DIR__.'/include/extra_param.inc.php';

$requested_vars = array(
    'op' => array('s', ''),
    'print' => array('b', false),
);
$formdata = &xoonips_getutility('formdata');
foreach ($requested_vars as $key => $meta) {
    list($type, $default) = $meta;
    $$key = $formdata->getValue('both', $key, $type, false, $default);
}

$myxoopsConfig = &xoonips_get_xoops_configs(XOOPS_CONF);
$myxoopsConfigMetaFooter = &xoonips_get_xoops_configs(XOOPS_CONF_METAFOOTER);

// disable to link index list in index tree block
$noidx_ops = array(
    'add_to_index',
    'select_item_advancedsearch',
    'select_item_advancedsearch_pagenavi',
    'select_item_useritem',
    'select_item_useritem_pagenavi',
);
if (in_array($op, $noidx_ops)) {
    $xoonipsURL = '';
}
$onclickidx_ops = array(
    'select_item_index',
    'select_item_index_pagenavi',
);
if (in_array($op, $onclickidx_ops)) {
    $xoonipsTree['onclick_title'] = 'xoonips_itemselect_index';
}

if ($print) {
    require_once XOOPS_ROOT_PATH.'/class/template.php';
    $xoopsTpl = new XoopsTpl();
    xoops_header(false);
    echo "</head><body onload='window.print();'>\n";
} else {
    require XOOPS_ROOT_PATH.'/header.php';
}

require 'include/itemselect.inc.php';

if (isset($search_itemtype)) {
    $xoopsTpl->assign('search_itemtype', $search_itemtype);
}

if ($print) {
    $xoopsTpl->assign('meta_copyright', $myxoopsConfigMetaFooter['meta_copyright']);
    $xoopsTpl->assign('meta_author', $myxoopsConfigMetaFooter['meta_author']);
    $xoopsTpl->assign('sitename', $myxoopsConfig['sitename']);

    if ('quicksearch' == $op) {
        $search_itemtypes = array(
            'all' => _MD_XOONIPS_SEARCH_ALL,
            'basic' => _MD_XOONIPS_SEARCH_TITLE_AND_KEYWORD,
            'metadata' => _MD_XOONIPS_SEARCH_METADATA,
        );

        $itemtypes = array();
        if (RES_OK == xnp_get_item_types($itemtypes)) {
            foreach ($itemtypes as $itemtype) {
                if ($itemtype['item_type_id'] > 2) {
                    $search_itemtypes[$itemtype['name']] = $itemtype['display_name'];
                }
            }
        }

        $xoopsTpl->assign('quick_search_itemtype', $textutil->html_special_chars($search_itemtypes[$search_itemtype]));
    }

    $xconfig_handler = &xoonips_getormhandler('xoonips', 'config');
    $xoopsTpl->assign('printer_friendly_header', $xconfig_handler->getValue('printer_friendly_header'));
    $textutil = &xoonips_getutility('text');
    $xoopsTpl->assign('title', _MD_XOONIPS_ITEM_SEARCH_RESULT);
    $xoopsTpl->assign('date', $textutil->html_special_chars(date(DATETIME_FORMAT, xoops_getUserTimestamp(time()))));
    $xoopsTpl->assign(
        'order_by_select', array(
        'title' => _MD_XOONIPS_ITEM_TITLE_LABEL,
        'doi' => _MD_XOONIPS_ITEM_DOI_LABEL,
        'last_update_date' => _MD_XOONIPS_ITEM_LAST_UPDATE_DATE_LABEL,
        'creation_date' => _MD_XOONIPS_ITEM_CREATION_DATE_LABEL,
        'publication_date' => _MD_XOONIPS_ITEM_PUBLICATION_DATE_LABEL,
        )
    );
    $xoopsTpl->assign('order_by', $textutil->html_special_chars($order_by));

    $xoopsTpl->display('db:xoonips_itemselect_print.html');
    xoops_footer();
    exit();
} else {
    require XOOPS_ROOT_PATH.'/footer.php';
}
