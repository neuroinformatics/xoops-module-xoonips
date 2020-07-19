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

//  page for confirm to edit items

$xoopsOption['pagetype'] = 'user';
require 'include/common.inc.php';

require_once 'include/item_limit_check.php';
require_once 'include/lib.php';
require_once 'include/AL.php';

$xnpsid = $_SESSION['XNPSID'];
$system_message = '';

$formdata = &xoonips_getutility('formdata');
$add_to_index_id = $formdata->getValue('post', 'add_to_index_id', 'i', false);
$xoonipsCheckedXID = $formdata->getValue('post', 'xoonipsCheckedXID', 's', false);

if (isset($add_to_index_id)) {
    // Processing when add button is pushed to public on detail page.
    if (!in_array($add_to_index_id, explode(',', $xoonipsCheckedXID))) {
        $xoonipsCheckedXID .= ','.$add_to_index_id;
    }
}
$formdata->set('post', 'xoonipsCheckedXID', $xoonipsCheckedXID);

foreach (array('item_id' => array('i', 0), 'op' => array('s', '')) as $k => $meta) {
    list($type, $default) = $meta;
    $$k = $formdata->getValue('both', $k, $type, false, $default);
}

xoonips_deny_guest_access();

$uid = $_SESSION['xoopsUserId'];

$textutil = &xoonips_getutility('text');

//error if item is locked
$item_lock_handler = &xoonips_getormhandler('xoonips', 'item_lock');
if ($item_lock_handler->isLocked($item_id)) {
    redirect_header(XOOPS_URL.'/modules/xoonips/detail.php?item_id='.$item_id, 5, sprintf(_MD_XOONIPS_ERROR_CANNOT_EDIT_LOCKED_ITEM, xoonips_get_lock_type_string($item_lock_handler->getLockType($item_id))));
    exit();
}

//retrieve item detail and set item type id to $item_type_id;
$item = array();
if (RES_OK != xnp_get_item($xnpsid, $item_id, $item)) {
    xoonips_error_exit(400);
}
$item_type_id = $item['item_type_id'];

//retrive module name to $itemtype
$itemtypes = array();
if (RES_OK != xnp_get_item_types($itemtypes)) {
    xoonips_error_exit(500);
} else {
    foreach ($itemtypes as $i) {
        if ($i['item_type_id'] == $item_type_id) {
            $itemtype = $i;
            $modname = $itemtype['name'];
            break;
        }
    }
}

// include view.php
require_once XOOPS_ROOT_PATH.'/modules/'.$itemtype['viewphp'];

$title = $formdata->getValue('post', 'title', 's', false);
if (!isset($title)) {
    //title is not filled
    $op = '';
    $system_message = '<span style="color: red;">'._MD_XOONIPS_ITEM_TITLE_REQUIRED.'</span>';
}

// check private_item_storage_limit
if (!check_private_item_storage_limit()) {
    $op = '';
    $system_message .= '<span style="color: red;">'._MD_XOONIPS_ITEM_WARNING_ITEM_STORAGE_LIMIT.'</span><br />';
}

// check group_item_number_limit
// check group_item_storage_limit
if ('' != $xoonipsCheckedXID) {
    $checked_xids = explode(',', $xoonipsCheckedXID);
} else {
    $checked_xids = array();
}

$gids = array();
foreach ($checked_xids as $xid) {
    $index = array();
    $result = xnp_get_index($xnpsid, $xid, $index);
    if (RES_OK != $result) {
        continue;
    }
    if (OL_GROUP_ONLY == $index['open_level']) {
        $gids[] = $index['owner_gid'];
    }
}
$xgroup_handler = &xoonips_gethandler('xoonips', 'group');
foreach (array_unique($gids) as $gid) {
    $xgroup_obj = &$xgroup_handler->getGroupObject($gid);
    if (is_object($xgroup_obj)) {
        $gname = $xgroup_obj->getVar('gname', 's');
    }
    if (0 == available_space_of_group_item($gid)) {
        // warinig, if not enough space to store items
        $op = '';
        if (is_object($xgroup_obj)) {
            $system_message .= '<span style="color: red;">'._MD_XOONIPS_ITEM_WARNING_ITEM_NUMBER_LIMIT.'(group='.$gname.')</span><br />';
        } else {
            $system_message .= '<span style="color: red;">'._MD_XOONIPS_ITEM_WARNING_ITEM_NUMBER_LIMIT.'(gid='.$gid.')</span><br />';
        }
    }
    if (!check_group_item_storage_limit($gid)) {
        $op = '';
        $group = array();
        if (is_object($xgroup_obj)) {
            $system_message .= '<span style="color: red;">'._MD_XOONIPS_ITEM_WARNING_ITEM_STORAGE_LIMIT.'(group='.$gname.')</span><br />';
        } else {
            $system_message .= '<span style="color: red;">'._MD_XOONIPS_ITEM_WARNING_ITEM_STORAGE_LIMIT.'(gid='.$gid.')</span><br />';
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
    $result = xnp_get_index($xnpsid, $xid, $index);
    if (RES_OK != $result) {
        continue;
    }
    if (OL_PRIVATE == $index['open_level']) {
        $private_index_flag = true;
    } elseif (OL_GROUP_ONLY == $index['open_level']) {
        $group_index_flag = true;
    } elseif (OL_PUBLIC == $index['open_level']) {
        $public_index_flag = true;
    }
}

$index_item_link_handler = &xoonips_getormhandler('xoonips', 'index_item_link');
if (ITID_INDEX != $item_type_id && !$private_index_flag && $index_item_link_handler->privateIndexReadable($item_id, $xoopsUser->getVar('uid'))) {
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
            $system_message .= "\n".'<br /><span style="color: red;">'.sprintf(_MD_XOONIPS_ITEM_DOI_INVALID_ID, XNP_CONFIG_DOI_FIELD_PARAM_MAXLEN).'</span><br />';
        }
        //check doi duplication when doi is changed.
        $org_doi = '';
        if (RES_OK == xnpGetDoiByItemId($item_id, $org_doi)) {
            if ($org_doi != $doi && xnpIsDoiExists($doi)) {
                $op = '';
                $system_message .= "\n".'<br /><span style="color: red;">'._MD_XOONIPS_ITEM_DOI_DUPLICATE_ID.'</span><br />';
            }
        }
    }
}

//check required field(detail information)
$msg = '';
eval('$param_check_result = '.$modname.'CheckEditParameters( $msg );');
if (!$param_check_result) {
    $op = '';
}

if ('update' == $op) {
    //update item

    $f = $itemtype['name'].'GetModifiedFields';
    $modified = xnpGetModifiedFields($item_id)
        + (function_exists($f) ? $f($item_id) : array());
    if (0 == count($modified)) {
        //no modified fields.don't update.
        redirect_header(xnpGetItemDetailURL($item_id, $doi), 3, 'Succeed');
    }

    //update item
    eval('$result = '.$modname.'UpdateItem( $item_id );');
    if (!$result) {
        redirect_header(XOOPS_URL.'/', 3, 'ERROR in '.$modname.'UpdateItem( )');
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
                if (OL_PUBLIC == $index->get('open_level')
                    || OL_GROUP_ONLY == $index->get('open_level')
                ) {
                    $item_basic_handler->lockItemAndIndexes($item_id, $index_id);
                    $certify_required = true;
                }
            }
        }

        $detail_url = xnpGetItemDetailURL($item_id, $doi);
        if ($certify_required) {
            redirect_header($detail_url, 5, "Succeed\n<br />"._MD_XOONIPS_ITEM_NEED_TO_BE_CERTIFIED);
        } else {
            redirect_header($detail_url, 3, 'Succeed');
        }
    }
    exit();
} else {
    if (!$param_check_result) {
        if (function_exists($modname.'CorrectEditParameters')) {
            eval($modname.'CorrectEditParameters();');
        }
        $msg = '';
        eval('$param_check_result = '.$modname.'CheckEditParameters( $msg );');
        $system_message = $system_message.$msg;
    }

    //confirm
    $check_xids = array_unique(
        array_merge(
            array($formdata->getValue('post', 'add_to_index_id', 'i', false)),
            explode(',', $formdata->getValue('post', 'xoonipsCheckedXID', 's', false))
        )
    );

    // $_POST['related_to_check'] is an array(from edit.php)
    // or string(from detail.php,confirm_edit.php).
    $related_to_check = $formdata->getValueArray('post', 'related_to_check', 'i', false);

    //prepare template
    $xoopsOption['template_main'] = 'xoonips_confirm_edit.html';
    require XOOPS_ROOT_PATH.'/header.php';
    $xoopsTpl->assign('item_id', $item_id);

    // select /Private and notice that /Private is selected automatically
    // if any private indexes are not selected.
    $item_basic_handler = &xoonips_getormhandler('xoonips', 'item_basic');
    $basic = &$item_basic_handler->get($item_id);
    if ($basic) {
        $xoonips_user_handler = &xoonips_getormhandler('xoonips', 'users');
        $xuser = $xoonips_user_handler->get($basic->get('uid'));
        if (!$private_index_flag && $index_item_link_handler->privateIndexReadable($item_id, $xoopsUser->getVar('uid'))) {
            $check_xids[] = $xuser->get('private_index_id');
            $xoopsTpl->assign('select_private_index_auto', '1');
            $formdata->set('post', 'xoonipsCheckedXID', implode(',', $check_xids));
        }
    }

    //Add group_owner_permission
    $xoopsTpl->assign('auto_select_private_index_message', $index_item_link_handler->privateIndexReadable($item_id, $xoopsUser->getVar('uid')));

    $checkedIndexIds = explode(',', $formdata->getvalue('post', 'xoonipsCheckedXID', 's', false));
    $index_handler = &xoonips_getormhandler('xoonips', 'index');
    $index_item_link_handler = &xoonips_getormhandler('xoonips', 'index_item_link');
    $index_item_links = &$index_item_link_handler->getObjects(new Criteria('item_id', $formdata->getValue('post', 'item_id', 'i', false)));
    foreach ($index_item_links as $link) {
        if (!$index_handler->getPerm($link->get('index_id'), $xoopsUser ? $xoopsUser->getVar('uid') : UID_GUEST, 'read')) {
            $checkedIndexIds[] = $link->get('index_id');
        }
    }
    $formdata->set('post', 'xoonipsCheckedXID', implode(',', $checkedIndexIds));

    $change_log = $formdata->getValue('post', 'change_log', 's', false);
    if (!isset($change_log) || '' == $change_log) {
        $item_id = $formdata->getValue('post', 'item_id', 'i', false);
        $fields = xnpGetModifiedFields($item_id);

        $fname = $modname.'GetModifiedFields';
        if (function_exists($fname)) {
            $fields = array_merge($fields, $fname($item_id));
        }
        if (count($fields) > 0) {
            $formdata->set('post', 'change_log', sprintf(_MD_XOONIPS_ITEM_CHANGE_LOG_AUTOFILL_TEXT, implode(_MD_XOONIPS_ITEM_CHANGE_LOG_AUTOFILL_DELIMITER, $fields)));
        }
    }

    eval('$body = '.$modname.'GetConfirmBlock( '.$item_id.' );');
    $xoopsTpl->assign('body', $body);
    // send basic information using hidden to next(before)page.
    // - basic inforamtion form data
    $itemlib_handler = &XooNIpsItemLibraryHandler::getInstance();
    $itemlib_obj = &$itemlib_handler->get($item_id);
    $itemlib_handler->fetchRequest($itemlib_obj, false);
    $hidden = $itemlib_obj->getHiddenHtml();
    $xoopsTpl->assign('hidden', $hidden);
    // - index form data
    $http_vars = array();
    $k = 'xoonipsCheckedXID';
    $tmp = $formdata->getValue('post', $k, 's', false);
    if (isset($tmp)) {
        $http_vars[$k] = $textutil->html_special_chars($tmp);
    } else {
        $http_vars[$k] = '';
    }
    $xoopsTpl->assign('http_vars', $http_vars);
    $xoopsTpl->assign('system_message', $system_message);
    if ($param_check_result) {
        $xoopsTpl->assign('op', 'update');
        $xoopsTpl->assign('update_button_visible', true);
    } else {
        $xoopsTpl->assign('op', '');
        $xoopsTpl->assign('update_button_visible', false);
    }

    require XOOPS_ROOT_PATH.'/footer.php';
}
/**
 * find whether that user have permission to read private index of the item.
 *
 * @param int $item_id
 * @param int $uid
 *
 * @return
 */
function private_index_readable($item_id, $uid)
{
    $join = new XooNIpsJoinCriteria('xoonips_index', 'index_id', 'index_id', 'INNER');
    $index_handler = &xoonips_getormhandler('xoonips', 'index');
    $index_item_link_handler = &xoonips_getormhandler('xoonips', 'index_item_link');
    $criteria = new CriteriaCompo(new Criteria('item_id', intval($item_id)));
    $criteria->add(new Criteria('open_level', OL_PRIVATE));
    $index_item_links = &$index_item_link_handler->getObjects($criteria, false, '', false, $join);
    foreach ($index_item_links as $link) {
        if (!$index_handler->getPerm($link->get('index_id'), $uid, 'read')) {
            return false;
        }
    }

    return true;
}
