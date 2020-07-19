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

//  page for confirm to register items

$xoopsOption['pagetype'] = 'user';
require 'include/common.inc.php';

require_once 'include/item_limit_check.php';
require_once 'include/lib.php';
require_once 'include/AL.php';
require_once 'class/base/gtickets.php';

$xgroup_handler = &xoonips_gethandler('xoonips', 'group');
$xnpsid = $_SESSION['XNPSID'];
$system_message = '';

$textutil = &xoonips_getutility('text');
$formdata = &xoonips_getutility('formdata');

$xoonipsCheckedXID = $formdata->getValue('post', 'xoonipsCheckedXID', 's', true);
$checked_xids = explode(',', $xoonipsCheckedXID);

$xoonipsURL = ''; // disable to link for index tree

foreach (array('item_type_id' => array('i', true, 0), 'op' => array('s', false, '')) as $k => $meta) {
    list($type, $is_required, $default) = $meta;
    $$k = $formdata->getValue('both', $k, $type, $is_required, $default);
}

xoonips_deny_guest_access();

$uid = $_SESSION['xoopsUserId'];

//retrive module name to $modname
$itemtypes = array();
if (RES_OK != xnp_get_item_types($itemtypes)) {
    xoonips_error_exit(500);
} else {
    foreach ($itemtypes as $i) {
        if ($i['item_type_id'] == $item_type_id) {
            $modname = $i['name'];
            $itemtype = $i;
            break;
        }
    }
}
if (!isset($itemtype)) {
    redirect_header(XOOPS_URL.'/', 3, 'ERROR item type not detected');
    exit();
}

//include view.php
require_once XOOPS_ROOT_PATH.'/modules/'.$itemtype['viewphp'];

//check required field
$title = $formdata->getValue('post', 'title', 's', false);
if ('' == $title) {
    //title is not filled
    $op = '';
    $system_message = '<span style="color: red;">'._MD_XOONIPS_ITEM_TITLE_REQUIRED.'</span>';
}

//check private_item_number_limit
if (0 == available_space_of_private_item()) {
    // warning, if not enough to store items
    $op = '';
    $system_message .= '<span style="color: red;">'._MD_XOONIPS_ITEM_WARNING_ITEM_NUMBER_LIMIT.'</span><br />';
}

//check private_item_storage_limit
if (!check_private_item_storage_limit()) {
    $op = '';
    $system_message .= '<span style="color: red;">'._MD_XOONIPS_ITEM_WARNING_ITEM_STORAGE_LIMIT.'</span><br />';
}

//check group_item_number_limit
//check group_item_storage_limit
$gids = array();
foreach ($checked_xids as $xid) {
    $index = array();
    $result = xnp_get_index($xnpsid, (int) $xid, $index);
    if (RES_OK == $result && OL_GROUP_ONLY == $index['open_level']) {
        $gids[] = $index['owner_gid'];
    }
}
foreach (array_unique($gids) as $gid) {
    if (0 == available_space_of_group_item($gid)) {
        // warning, if not enough to store items
        $op = '';
        $xg_obj = &$xgroup_handler->getGroupObject($gid);
        if (is_object($xg_obj)) {
            $system_message .= '<span style="color: red;">'._MD_XOONIPS_ITEM_WARNING_ITEM_NUMBER_LIMIT.'(group='.$xg_obj->getVar('gname', 's').')</span><br />';
        } else {
            $system_message .= '<span style="color: red;">'._MD_XOONIPS_ITEM_WARNING_ITEM_NUMBER_LIMIT."(gid=${gid})</span><br />";
        }
    }
    if (!check_group_item_storage_limit($gid)) {
        $op = '';
        $xg_obj = &$xgroup_handler->getGroupObject($gid);
        if (is_object($xg_obj)) {
            $system_message .= '<span style="color: red;">'._MD_XOONIPS_ITEM_WARNING_ITEM_STORAGE_LIMIT.'(group='.$xg_obj->getVar('gname', 's').')</span><br />';
        } else {
            $system_message .= '<span style="color: red;">'._MD_XOONIPS_ITEM_WARNING_ITEM_STORAGE_LIMIT."(gid=${gid})</span><br />";
        }
    }
}

//check registration to Private Index
//if there is no registration, registration of items are forbidden.
$user = array();
$private_index_flag = false; // true if private index is selected
$public_index_flag = false; // true if public index is selected
$group_index_flag = false; // true if group index is selected
foreach ($checked_xids as $xid) {
    $index = array();
    $result = xnp_get_index($xnpsid, (int) $xid, $index);
    if (RES_OK == $result) {
        if (OL_PRIVATE == $index['open_level']) {
            $private_index_flag = true;
        } elseif (OL_GROUP_ONLY == $index['open_level']) {
            $group_index_flag = true;
        } elseif (OL_PUBLIC == $index['open_level']) {
            $public_index_flag = true;
        }
    }
}
if (ITID_INDEX != $item_type_id && !$private_index_flag) {
    $op = '';
}

if (XNP_CONFIG_DOI_FIELD_PARAM_NAME != '') {
    //check doi field format and length(basic information)
    $doi = $formdata->getValue('post', 'doi', 's', false);
    if ('' != $doi) {
        $matches = array();
        $res = preg_match('/'.XNP_CONFIG_DOI_FIELD_PARAM_PATTERN.'/', $doi, $matches);
        if (strlen($doi) > XNP_CONFIG_DOI_FIELD_PARAM_MAXLEN
            || 0 == $res || $matches[0] != $doi
        ) {
            $op = '';
            $system_message .= "\n".'<br /><span style="color: red;">'
                .sprintf(_MD_XOONIPS_ITEM_DOI_INVALID_ID, XNP_CONFIG_DOI_FIELD_PARAM_MAXLEN).'</span><br />';
        }
        //check doi duplication
        if (xnpIsDoiExists($doi)) {
            $op = '';
            $system_message .= "\n".'<br /><span style="color: red;">'
                ._MD_XOONIPS_ITEM_DOI_DUPLICATE_ID.'</span><br />';
        }
    }
}
//check required field(detail information)
$msg = '';
eval('$param_check_result = '.$modname.'CheckRegisterParameters( $msg );');
if (!$param_check_result) {
    $op = '';
}

if (isset($op) && 'register' == $op) {
    if (!$xoopsGTicket->check(true, 'register', false)) {
        die('ticket error');
    }

    $item_type_handler = &xoonips_getormhandler('xoonips', 'item_type');
    $item_type = $item_type_handler->get($_POST['item_type_id']);
    if (!$item_type) {
        trigger_error('invalid item_type_id:'.$_POST['item_type_id']);
        exit();
    }
    $modname = $item_type->get('name');

    eval('$result = '.$modname.'CheckRegisterParameters( $msg );');
    if (!$result) {
        trigger_error('incomplete parameters');
        exit();
    }

    //register item
    $item_id = 0;
    eval('$result = '.$modname.'InsertItem( $item_id );');
    if (!$result) {
        redirect_header(XOOPS_URL.'/', 3, 'ERROR in '.$modname.'InsertItem( )');
    } else {
        // lock item and indexes if certify required
        $item_basic_handler = &xoonips_getormhandler('xoonips', 'item_basic');
        $index_handler = &xoonips_getormhandler('xoonips', 'index');
        $index_item_link_handler = &xoonips_getormhandler('xoonips', 'index_item_link');
        $index_item_links = &$index_item_link_handler->getObjects(new Criteria('item_id', $item_id));
        $certify_required = false;
        foreach ($index_item_links as $index_item_link) {
            if (CERTIFY_REQUIRED == $index_item_link->get('certify_state')) {
                $index_id = $index_item_link->get('index_id');
                $index = $index_handler->get($index_id);
                if (OL_PUBLIC == $index->get('open_level') || OL_GROUP_ONLY == $index->get('open_level')) {
                    $item_basic_handler->lockItemAndIndexes($item_id, $index_id);
                    $certify_required = true;
                }
            }
        }
        if ($certify_required) {
            redirect_header('register.php', 5, "Succeed\n<br />"._MD_XOONIPS_ITEM_NEED_TO_BE_CERTIFIED);
        } else {
            redirect_header('register.php', 3, 'Succeed');
        }
    }
    exit();
} else {
    if (!$param_check_result) {
        if (function_exists($modname.'CorrectRegisterParameters')) {
            eval($modname.'CorrectRegisterParameters();');
        }

        $msg = '';
        eval('$param_check_result = '.$modname.'CheckRegisterParameters( $msg );');
        $system_message = $system_message.$msg;
    }

    //confirm
    $check_xids = empty($xoonipsCheckedXID) ? array() : explode(',', $xoonipsCheckedXID);

    //prepare template
    $xoopsOption['template_main'] = 'xoonips_confirm_register.html';

    require XOOPS_ROOT_PATH.'/header.php';

    if ($param_check_result) {
        // select /Private and notice
        // that /Private is selected automatically
        // if any private indexes are not selected.
        $account = array();
        if (RES_OK != xnp_get_account($xnpsid, $uid, $account)) {
            xoonips_error_exit(500);
        }
        if (!$private_index_flag) {
            //select /Private
            $check_xids[] = $account['private_index_id'];
            //notice message
            $xoopsTpl->assign('select_private_index_auto', '1');
            //overwrite xoonipsCheckedXID with an array of index Ids
            // that contains /Private
            $_POST['xoonipsCheckedXID'] = implode(',', $check_xids);
        }
    }
    $token_ticket = $xoopsGTicket->getTicketHtml(__LINE__, 600, 'register');
    $xoopsTpl->assign('token_hidden', $token_ticket);

    $select_item_type = array();
    $itemtypes = array();
    if (RES_OK != xnp_get_item_types($itemtypes)) {
        redirect_header(XOOPS_URL.'/', 3, 'ERROR xnp_get_item_types');
        exit();
    } else {
        foreach ($itemtypes as $i) {
            if ($i['item_type_id'] > 2) {
                if (!isset($item_type_id)) {
                    // set default item type id
                    $item_type_id = $i['item_type_id'];
                }
                if ($i['item_type_id'] == $item_type_id) {
                    $itemtype = $i;
                    $modname = $i['name'];
                }
                $select_item_type[$i['display_name']] = $i['item_type_id'];
            }
        }
    }

    require_once XOOPS_ROOT_PATH.'/modules/'.$itemtype['viewphp'];
    eval('$body = '.$modname.'GetConfirmBlock(false);');
    $xoopsTpl->assign('body', $body);
    ////send basic information using hidden to next(before)page.
    $http_vars = array();
    foreach (array('item_type_id',
                    'title',
                    'keywords',
                    'description',
                    'doi',
                    'publicationDateYear',
                    'publicationDateMonth',
                    'publicationDateDay',
                    'change_log',
                    'xoonipsCheckedXID',
                    'lang',
                    'related_to', ) as $k) {
        $tmp = $formdata->getValue('post', $k, 'n', false);
        if (isset($tmp)) {
            $http_vars[$k] = $textutil->html_special_chars($tmp);
        } else {
            $http_vars[$k] = '';
        }
    }
    $xoopsTpl->assign('http_vars', $http_vars);
    $xoopsTpl->assign('system_message', $system_message);
    if ($param_check_result) {
        $xoopsTpl->assign('op', 'register');
        $xoopsTpl->assign('register_button_visible', true);
    } else {
        $xoopsTpl->assign('op', '');
        $xoopsTpl->assign('register_button_visible', false);
    }

    require XOOPS_ROOT_PATH.'/footer.php';
}
