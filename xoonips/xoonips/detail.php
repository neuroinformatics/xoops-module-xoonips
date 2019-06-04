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

//  page to display item's detail

$xoopsOption['pagetype'] = 'user';
require 'include/common.inc.php';
require_once 'include/lib.php';
require_once 'include/AL.php';
require_once 'include/notification.inc.php';
require_once 'class/xoonipsresponse.class.php';
require_once 'class/xoonipserror.class.php';
require_once 'class/base/logicfactory.class.php';

$xnpsid = $_SESSION['XNPSID'];
$textutil = &xoonips_getutility('text');
$formdata = &xoonips_getutility('formdata');

// If not a user, redirect
if (!$xoopsUser) {
    if (!xnp_is_valid_session_id($xnpsid)) {
        // User is guest group, and guest isn't admitted to access the page.
        redirect_header('user.php', 3, _MD_XOONIPS_ITEM_FORBIDDEN);
        exit();
    }
    $uid = UID_GUEST;
} elseif ($xoopsUser && !$xoopsUser->isAdmin($xoopsModule->getVar('mid'))
    && !xnp_is_activated($xnpsid, $xoopsUser->getVar('uid'))
) {
    // disable to access by not certified user without xoonips admin
    redirect_header(XOOPS_URL.'/', 3, _MD_XOONIPS_MODERATOR_NOT_ACTIVATED);
    exit();
} else {
    $uid = $_SESSION['xoopsUserId'];
}

$myxoopsConfig = &xoonips_get_xoops_configs(XOOPS_CONF);
$myxoopsConfigMetaFooter = &xoonips_get_xoops_configs(XOOPS_CONF_METAFOOTER);

$doi_column_name = XNP_CONFIG_DOI_FIELD_PARAM_NAME;
$item_id = $formdata->getValue('both', 'item_id', 'i', false, 0);
$op = $formdata->getValue('both', 'op', 's', false, '');
$doi = '';
if ($doi_column_name != '') {
    $doi = $formdata->getValue('both', $doi_column_name, 's', false, '');
}

// update $item_id by the ID specified by given doi if exists $$doi_column_name param.
if ($doi != '') {
    $new_item_ids = array();
    $result = xnpGetItemIdByDoi($doi, $new_item_ids);
    // error check. $new_item_ids must be one.
    if (count($new_item_ids) == 0) {
        redirect_header(XOOPS_URL.'/', 3, _MD_XOONIPS_ITEM_DOI_NOT_FOUND);
        exit();
    } elseif (count($new_item_ids) > 1) {
        redirect_header(XOOPS_URL.'/', 3, _MD_XOONIPS_ITEM_DOI_DUPLICATE_ID);
        exit();
    }
    $item_id = $new_item_ids[0];
    // for comment function
    $comformdata = &xoonips_getutility('formdata');
    $comformdata->set('get', 'item_id', $item_id);
    // end of comment function
}

// set download file id if op == 'download'
$download_file_id = false;
if ($op == 'download') {
    $download_file_id = $formdata->getValue('both', 'download_file_id', 'i', false, false);
}

// retrieve item detail and set item type id to $item_type_id;
$item_compo_handler = &xoonips_getormcompohandler('xoonips', 'item');
if (!$item_compo_handler->getPerm($item_id, $uid, 'read')) {
    redirect_header('user.php', 3, _MD_XOONIPS_ITEM_FORBIDDEN);
}

$item = &$item_compo_handler->get($item_id);
if (!$item) {
    redirect_header('user.php', 3, _MD_XOONIPS_ITEM_FORBIDDEN);
}
$basic = &$item->getVar('basic');
$item_type_id = $basic->get('item_type_id');

//retrieve module name to $modname
$tmp = array();
$itemtypes = array();
if (xnp_get_item_types($tmp) != RES_OK) {
    redirect_header(XOOPS_URL.'/', 3, 'ERROR xnp_get_item_types');
    exit();
} else {
    foreach ($tmp as $i) {
        $itemtypes[$i['item_type_id']] = $i;
    }

    $itemtype = $itemtypes[$item_type_id];
    $modname = $itemtype['name'];
}

if ($op == 'reject_certify' || $op == 'accept_certify' || $op == 'withdraw') {
    $item_lock_handler = &xoonips_getormhandler('xoonips', 'item_lock');
    $succeeded_index_ids = array();
    foreach ($formdata->getValueArray('post', 'index_ids', 'i', true) as $index_id) {
        if ($op == 'withdraw' && $item_lock_handler->isLocked($item_id)) {
            redirect_header(XOOPS_URL.'/modules/xoonips/detail.php?item_id='.$item_id, 5, sprintf(_MD_XOONIPS_ERROR_CANNOT_WITHDRAW_LOCKED_ITEM, xoonips_get_lock_type_string($item_lock_handler->getLockType($item_id))));
            exit();
        }
        if ($op == 'reject_certify') {
            if (xoonips_reject_item($uid, $item_id, $index_id)) {
                $succeeded_index_ids[] = $index_id;
            }
        } elseif ($op == 'withdraw') {
            if (xoonips_withdraw_item($uid, $item_id, $index_id)) {
                $succeeded_index_ids[] = $index_id;
            }
        } elseif ($op == 'accept_certify') {
            if (xoonips_certify_item($uid, $item_id, $index_id)) {
                $succeeded_index_ids[] = $index_id;
            }
        }
    }
    if (!empty($succeeded_index_ids)) {
        if ($op == 'reject_certify' || $op == 'withdraw') {
            xoonips_notification_item_rejected($item_id, $succeeded_index_ids);
            xoonips_notification_user_item_rejected($item_id, $succeeded_index_ids);
        } elseif ($op == 'accept_certify') {
            xoonips_notification_item_certified($item_id, $succeeded_index_ids);
            xoonips_notification_user_item_certified($item_id, $succeeded_index_ids);
        }
    }
    $op = '';
}

$item_compo_handler = &xoonips_getormcompohandler('xoonips', 'item');

if ($op == 'delete') {
    //show error if locked
    $item_lock_handler = &xoonips_getormhandler('xoonips', 'item_lock');
    if ($item_lock_handler->isLocked($item_id)) {
        redirect_header(XOOPS_URL.'/modules/xoonips/detail.php?item_id='.$item_id, 5, sprintf(_MD_XOONIPS_ERROR_CANNOT_DELETE_LOCKED_ITEM, xoonips_get_lock_type_string($item_lock_handler->getLockType($item_id))));
        exit();
    }
    //show error if no permission
    if (!$item_compo_handler->getPerm($item_id, $uid, 'delete')) {
        redirect_header(XOOPS_URL.'/modules/xoonips/detail.php?item_id='.$item_id, 3, _MD_XOONIPS_ITEM_FORBIDDEN);
    }

    xoonips_delete_item($item_id);
}
if ($op == 'print') {
    require_once XOOPS_ROOT_PATH.'/class/template.php';
    $xoopsTpl = new XoopsTpl();
    xoops_header(false);

    $xoopsTpl->assign('meta_copyright', $myxoopsConfigMetaFooter['meta_copyright']);
    $xoopsTpl->assign('meta_author', $myxoopsConfigMetaFooter['meta_author']);
    $xoopsTpl->assign('sitename', $myxoopsConfig['sitename']);

    require_once XOOPS_ROOT_PATH.'/modules/'.$itemtype['viewphp'];
    eval('$body = '.$modname.'GetPrinterFriendlyDetailBlock( $item_id );');
    echo "</head><body onload='window.print();'>\n";
    $val = '';
    xnp_get_config_value('printer_friendly_header', $val);
    $xoopsTpl->assign('printer_friendly_header', $val);
    $xoopsTpl->assign('item_url', xnpGetItemDetailURL($item_id, $doi));
    $xoopsTpl->assign('body', $body);
    $xoopsTpl->display('db:xoonips_detail_print.html');
    xoops_footer();
    exit();
}

$xoopsOption['template_main'] = 'xoonips_detail.html';
require XOOPS_ROOT_PATH.'/header.php';

$item_lock_handler = &xoonips_getormhandler('xoonips', 'item_lock');
if ($item_lock_handler->isLocked($item_id)) {
    $xoopsTpl->assign('locked_message', sprintf(_MD_XOONIPS_WARNING_CANNOT_EDIT_LOCKED_ITEM, xoonips_get_lock_type_string($item_lock_handler->getLockType($item_id))));
} else {
    $xoopsTpl->assign('locked_message', false);
}

if ($item_compo_handler->getPerm($item_id, $uid, 'delete') && $op != 'print') {
    $xoopsTpl->assign('delete_button_visible', '1');
} else {
    $xoopsTpl->assign('delete_button_visible', '0');
}

// makes Modify button visible if following case
// user have write permission and this page is not for printer friendly.
if ($item_compo_handler->getPerm($item_id, $uid, 'write') && $op != 'print') {
    $xoopsTpl->assign('modify_button_visible', '1');
} else {
    $xoopsTpl->assign('modify_button_visible', '0');
}

if ($op != 'print') {
    $xoopsTpl->assign('print_button_visible', '1');
} else {
    $xoopsTpl->assign('print_button_visible', '0');
}

$xoopsTpl->assign('item_id', $item_id);
if ($doi != '') {
    $xoopsTpl->assign('doi', $textutil->html_special_chars($doi));
    $xoopsTpl->assign('doi_column_name', $textutil->html_special_chars($doi_column_name));
}

if (xoonips_is_user_export_enabled()) {
    $handler = &xoonips_getormcompohandler('xoonips', 'item');
    $xoopsTpl->assign('export_enabled', $handler->getPerm($item_id, $xoopsUser ? $xoopsUser->getVar('uid') : UID_GUEST, 'export'));
}

function genSelectLabels(&$index)
{
    $textutil = &xoonips_getutility('text');
    $title = $index['titles'][DEFAULT_INDEX_TITLE_OFFSET];
    $indent_html = str_repeat('&nbsp;&nbsp;', (int) ($index['depth']));
    if (isset($index['child_count']) && $index['child_count'] != 0) {
        $select_label = sprintf(' %s ( %u )', $title, $index['child_count']);
    } else {
        $select_label = sprintf(' %s ', $title);
    }
    $index['indent_html'] = $indent_html;
    $index['select_label'] = $textutil->html_special_chars($select_label);
}

// display of 'add to public'
if ($op == '' || $op == 'download') {
    // Display only 'Binder -> Binders'. Display 'Not Binder -> Public not Binders'.
    require_once 'include/gentree.php';
    $index = array('open_level' => OL_PUBLIC);
    $indexTree = genSameAreaIndexTree($xnpsid, $uid, $index);
    array_walk($indexTree, 'genSelectLabels');
    $xoopsTpl->assign('index_tree', $indexTree);
}

require_once XOOPS_ROOT_PATH.'/modules/'.$itemtype['viewphp'];
eval('$body = '.$modname.'GetDetailBlock( $item_id );');
$xoopsTpl->assign('body', $body);

$func = $modname.'GetDownloadConfirmationBlock';
if (function_exists($func)) {
    $xoopsTpl->assign('download_confirmation', $func($item_id, $download_file_id));
} else {
    $xoopsTpl->assign('download_file_id', $download_file_id);
}

$xoonips_module_header = '<link rel="stylesheet" type="text/css" href="style.css" />';
$func = $modname.'GetHeadMeta';
if (function_exists($func)) {
    eval('$xoonips_module_header .= "\n".'.$func.'($item_id);');
}
$xoonips_module_header .= "\n".$xoopsTpl->get_template_vars('xoops_module_header');
$xoopsTpl->assign('xoops_module_header', $xoonips_module_header);
// Record events(view item)
$eventlog_handler = &xoonips_getormhandler('xoonips', 'event_log');
$eventlog_handler->recordViewItemEvent($item_id);

$basic = xnpGetBasicInformationArray($item_id);
$xoopsTpl->assign('xoops_pagetitle', $textutil->html_special_chars($basic['titles'][0]));

// get item viewed count
$ranking_handler = &xoonips_gethandler('xoonips', 'ranking');
$ranking_handler->update();
$viewed_count = $ranking_handler->get_count_viewed_item($item_id);
$xoopsTpl->assign('viewed_count', $viewed_count);

//start of item comment function
$comconfig_handler = &xoonips_getormhandler('xoonips', 'config');
$com_dir_name = $comconfig_handler->getValue('item_comment_dirname');
$com_forum_id = $comconfig_handler->getValue('item_comment_forum_id');
$xoopsTpl->assign('dir_name', $com_dir_name);
$xoopsTpl->assign('forum_id', $com_forum_id);
//end of item comment function

require XOOPS_ROOT_PATH.'/footer.php';

function xoonips_delete_item($item_id)
{
    $params = array(session_id(), $item_id, 'item_id');
    $response = new XooNIpsResponse();
    $factory = &XooNIpsLogicFactory::getInstance();
    $logic = &$factory->create('removeItem');
    $logic->execute($params, $response);

    if ($response->getResult()) {
        redirect_header(XOOPS_URL.'/', 3, 'Succeed');
    } else {
        redirect_header(XOOPS_URL.'/', 3, 'ERROR');
    }
}
