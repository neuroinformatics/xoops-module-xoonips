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

//  page to edit items
session_cache_limiter('private');
session_cache_expire(5);
$xoopsOption['pagetype'] = 'user';
require 'include/common.inc.php';

require_once 'include/item_limit_check.php';
require_once 'include/lib.php';
require_once 'include/AL.php';
require_once 'include/extra_param.inc.php';

$xnpsid = $_SESSION['XNPSID'];

// if there is post_id, it restores $_POST.
$formdata = &xoonips_getutility('formdata');
$post_id = $formdata->getValue('get', 'post_id', 's', false);
if (isset($post_id) && 'GET' == $_SERVER['REQUEST_METHOD']) {
    if (isset($_SESSION['post_id']) && isset($_SESSION['post_id'][$post_id])) {
        $_POST = unserialize($_SESSION['post_id'][$post_id]);
    }
}

foreach (array('item_id' => 0, 'scrollX' => 0, 'scrollY' => 0) as $k => $v) {
    $$k = $formdata->getValue('both', $k, 'i', false);
}

// extra_item['item_id'] has priority over $_POST['item_id']
$extra_param = xoonips_extra_param_restore();
if ($extra_param) {
    $item_id = array_key_exists('item_id', $extra_param) ? $extra_param['item_id'] : $item_id;
}

xoonips_deny_guest_access();

$uid = $_SESSION['xoopsUserId'];
//Uncertified user can't access(except XOOPS administrator).
if (!$xoopsUser->isAdmin($xoopsModule->getVar('mid'))
    && !xnp_is_activated($xnpsid, $uid)
) {
    redirect_header(XOOPS_URL.'/modules/xoonips/index.php', 3, _MD_XOONIPS_MODERATOR_NOT_ACTIVATED);
    exit();
}

//error if item is locked
$item_lock_handler = &xoonips_getormhandler('xoonips', 'item_lock');
if ($item_lock_handler->isLocked($item_id)) {
    redirect_header(XOOPS_URL.'/modules/xoonips/detail.php?item_id='.$item_id, 5, sprintf(_MD_XOONIPS_ERROR_CANNOT_EDIT_LOCKED_ITEM, xoonips_get_lock_type_string($item_lock_handler->getLockType($item_id))));
    exit();
}

$item_compo_handler = &xoonips_getormcompohandler('xoonips', 'item');
if (!$item_compo_handler->getPerm($item_id, $xoopsUser->getVar('uid'), 'write')) {
    redirect_header(XOOPS_URL.'/modules/xoonips/index.php', 3, _MD_XOONIPS_ITEM_FORBIDDEN);
    exit();
}

$item = array();
if (RES_OK != xnp_get_item($xnpsid, $item_id, $item)) {
    redirect_header(XOOPS_URL.'/modules/xoonips/index.php', 3, _MD_XOONIPS_ITEM_CANNOT_ACCESS_ITEM);
    exit();
}
$item_type_id = $item['item_type_id'];

$xoonipsTreeCheckBox = true;
$xoonipsURL = '';
$xoonipsCheckPrivateHandlerId = 'PrivateIndexCheckedHandler'; //see also xoonips_edit.html

//show item creator's tree if moderator modifys this item.
if ($xoopsUser->getVar('uid') != $item['uid']) {
    $xoonipsTreePrivateUid = $item['uid'];
}

$xoopsOption['template_main'] = 'xoonips_edit.html';
require XOOPS_ROOT_PATH.'/header.php';

//Add group_owner_permission
$index_item_link_handler = &xoonips_getormhandler('xoonips', 'index_item_link');
$xoopsTpl->assign('require_private_index_message', $index_item_link_handler->privateIndexReadable($item_id, $xoopsUser->getVar('uid')));

$xoopsTpl->assign('next_url', 'confirm_edit.php');
//retrieve index ids
$xoonipsCheckedXID = $formdata->getValue('post', 'xoonipsCheckedXID', 's', false);
if (null !== $xoonipsCheckedXID) {
    $xoopsTpl->assign('xoonipsCheckedXID', $xoonipsCheckedXID);
} else {
    $index_ids = array();
    xnp_get_index_id_by_item_id($xnpsid, $item_id, $index_ids);
    $xoopsTpl->assign('xoonipsCheckedXID', implode(',', $index_ids));
}
$xoopsTpl->assign('item_id', $item_id);

$item_type_handler = &xoonips_getormhandler('xoonips', 'item_type');
$item_type = &$item_type_handler->get($item_type_id);
if (!$item_type) {
    die('item type is not found');
}

require_once XOOPS_ROOT_PATH.'/modules/'.$item_type->get('viewphp');
$func = $item_type->get('name').'GetEditBlock';
$body = $func($item_id);

$xoopsTpl->assign('body', $body);
$xoopsTpl->assign('scrollX', isset($scrollX) ? (int) $scrollX : 0);
$xoopsTpl->assign('scrollY', isset($scrollY) ? (int) $scrollY : 0);

$xoopsTpl->assign('invalid_doi_message', sprintf(_MD_XOONIPS_ITEM_DOI_INVALID_ID, XNP_CONFIG_DOI_FIELD_PARAM_MAXLEN));

$account = array();
if (RES_OK == xnp_get_account($xnpsid, $uid, $account)) {
    $iids = array();
    if (RES_OK == xnp_get_private_item_id($xnpsid, $uid, $iids)) {
        $xoopsTpl->assign('num_of_items_current', count($iids));
    }
    $xoopsTpl->assign('num_of_items_max', $account['item_number_limit']);
    $xoopsTpl->assign('storage_of_items_max', sprintf('%.02lf', $account['item_storage_limit'] / 1000 / 1000));
    $xoopsTpl->assign('storage_of_items_current', sprintf('%.02lf', filesize_private() / 1000 / 1000));
    $xoopsTpl->assign('accept_charset', '');
}

// If the page is made by POST, $_POST is made to save somewhere and page redirects.
if ('POST' == $_SERVER['REQUEST_METHOD']) {
    $post_id = uniqid('postid');
    $_SESSION['post_id'] = array($post_id => serialize($_POST));
    header('HTTP/1.0 303 See Other');
    header('Location: '.XOOPS_URL."/modules/xoonips/edit.php?post_id=$post_id");
    echo sprintf(_IFNOTRELOAD, XOOPS_URL."/modules/xoonips/edit.php?post_id=$post_id");
    //redirect_header("edit.php?post_id=$post_id", 5, "redirecting...");
    exit;
}
// The output( header("Cache-control: no-cache") etc ) is prevented by footer.php.
header('Content-Type:text/html; charset='._CHARSET);
//echo "\r\n"; flush();

require XOOPS_ROOT_PATH.'/footer.php';

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
