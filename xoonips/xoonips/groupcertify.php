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
require 'include/common.inc.php';
require_once 'include/lib.php';
require 'class/base/gtickets.php';

$xnpsid = $_SESSION['XNPSID'];
$formdata = &xoonips_getutility('formdata');
$textutil = &xoonips_getutility('text');

// If not a user, redirect
if (!$xoopsUser) {
    redirect_header('user.php', 3, _MD_XOONIPS_ITEM_FORBIDDEN);
    exit();
}

$uid = $_SESSION['xoopsUserId'];

// Only Moderator can access this page.
$member_handler = &xoonips_gethandler('xoonips', 'member');
if (!$member_handler->isModerator($uid)) {
    redirect_header(XOOPS_URL.'/', 3, _MD_XOONIPS_ITEM_FORBIDDEN);
    exit();
}

// get requests
$op = $formdata->getValue('post', 'op', 's', false, '');

$index_ids = $formdata->getValueArray('post', 'index_ids', 'i', false);
$group_index_id = $formdata->getValue('post', 'group_index_id', 'i', false);
// check request variables
if ($op == 'certify' || $op == 'uncertify') {
    if ($group_index_id == 0) {
        die('illegal request');
    }
} elseif ($op != '') {
    die('illegal request');
}

// pankuzu for administrator
$pankuzu = _MI_XOONIPS_ACCOUNT_PANKUZU_MODERATOR._MI_XOONIPS_ACCOUNT_PANKUZU_SEPARATOR._MI_XOONIPS_ITEM_PANKUZU_CERTIFY_GROUP_PUBLIC_ITEMS;

if ($op == 'certify') {
    if (!$xoopsGTicket->check(true, 'xoonips_group_certify_index')) {
        exit();
    }

    certify($index_ids, $group_index_id);
    exit();
} elseif ($op == 'uncertify') {
    if (!$xoopsGTicket->check(true, 'xoonips_group_certify_index')) {
        exit();
    }

    uncertify($index_ids, $group_index_id);
    exit();
}

$group_indexes = array();
$index_compo_handler = &xoonips_getormcompohandler('xoonips', 'index');
$index_group_index_link_handler = &xoonips_getormhandler('xoonips', 'index_group_index_link');
foreach ($index_group_index_link_handler->getObjects() as $link) {
    if (!array_key_exists($link->get('group_index_id'), $group_indexes)) {
        $group_index_path = $index_compo_handler->getIndexPathNames($link->get('group_index_id'));
        if (!$group_index_path) {
            continue;
        }
        $group_indexes[$link->get('group_index_id')] = array(
        'group_index_id' => $link->get('group_index_id'),
        'indexes' => array(),
        'group_index_path' => $textutil->html_special_chars('/'.join('/', $group_index_path)),
        );
    }
    array_push($group_indexes[$link->get('group_index_id')]['indexes'], array('id' => $link->get('index_id'), 'path' => $textutil->html_special_chars('/'.join('/', $index_compo_handler->getIndexPathNames($link->get('index_id'))))));
}

$xoopsOption['template_main'] = 'xoonips_groupcertify.html';
require XOOPS_ROOT_PATH.'/header.php';

$xoopsTpl->assign('pankuzu', $pankuzu);
$xoopsTpl->assign('certify_button_label', _MD_XOONIPS_ITEM_CERTIFY_BUTTON_LABEL);
$xoopsTpl->assign('uncertify_button_label', _MD_XOONIPS_ITEM_UNCERTIFY_BUTTON_LABEL);
$xoopsTpl->assign('group_index_label', _MD_XOONIPS_ITEM_GROUP_INDEX_LABEL);
$xoopsTpl->assign('index_label', _MD_XOONIPS_ITEM_INDEX_LABEL);
if (count($group_indexes) > 0) {
    $xoopsTpl->assign('group_indexes', $group_indexes);
}
$xoopsTpl->assign('xoonips_editprofile_url', XOOPS_URL.'/modules/xoonips/edituser.php?uid='.$uid);
// token ticket
$token_ticket = $xoopsGTicket->getTicketHtml(__LINE__, 1800, 'xoonips_group_certify_index');
$xoopsTpl->assign('token_ticket', $token_ticket);

require XOOPS_ROOT_PATH.'/footer.php';

function certify($to_index_ids, $group_index_id)
{
    // transaction
    require_once __DIR__.'/class/base/transaction.class.php';
    $transaction = &XooNIpsTransaction::getInstance();
    $transaction->start();

    $index_group_index_link_handler = &xoonips_getormhandler('xoonips', 'index_group_index_link');
    foreach ($to_index_ids as $to_index_id) {
        if (!$index_group_index_link_handler->makePublic($to_index_id, array($group_index_id))) {
            $transaction->rollback();
            redirect_header(XOOPS_URL.'/', 3, _MD_XOONIPS_GROUP_TREE_TO_PUBLIC_INDEX_TREE_FAILED);
            exit();
        }
    }
    $eventlog_handler = &xoonips_getormhandler('xoonips', 'event_log');
    $eventlog_handler->recordCertifyGroupIndexEvent($group_index_id);

    $transaction->commit();
    $index_group_index_link_handler->notifyMakePublicGroupIndex($to_index_ids, array($group_index_id), 'group_item_certified');
    redirect_header(XOOPS_URL.'/modules/xoonips/groupcertify.php', 3, 'Succeed');
}

function uncertify($to_index_ids, $group_index_id)
{
    // transaction
    require_once __DIR__.'/class/base/transaction.class.php';
    $transaction = &XooNIpsTransaction::getInstance();
    $transaction->start();

    $index_group_index_link_handler = &xoonips_getormhandler('xoonips', 'index_group_index_link');
    foreach ($to_index_ids as $to_index_id) {
        if (!$index_group_index_link_handler->rejectMakePublic($to_index_id, array($group_index_id))) {
            $transaction->rollback();
            redirect_header(XOOPS_URL.'/', 3, _MD_XOONIPS_GROUP_TREE_TO_PUBLIC_INDEX_TREE_FAILED);
            exit();
        }
    }
    $index_group_index_link_handler->notifyMakePublicGroupIndex($to_index_ids, array($group_index_id), 'group_item_rejected');

    $eventlog_handler = &xoonips_getormhandler('xoonips', 'event_log');
    $eventlog_handler->recordRejectGroupIndexEvent($group_index_id);

    $transaction->commit();
    redirect_header(XOOPS_URL.'/modules/xoonips/groupcertify.php', 3, 'Succeed');
}
