<?php

// $Revision: 1.1.4.1.2.14 $
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

/**
 * @brief get user id of moderators
 *
 * @return array of user id of moderators
 */
function xoonips_notification_get_moderator_uids()
{
    $xoonips_config_handler = &xoonips_getormhandler('xoonips', 'config');
    $moderator_gid = $xoonips_config_handler->getValue('moderator_gid');
    if (is_null($moderator_gid)) {
        return array(); // no moderator
    }

    $xoops_member_handler = &xoops_gethandler('member');

    return $xoops_member_handler->getUsersByGroup($moderator_gid);
}

/**
 * @brief get notification tags for item
 *
 * @param[in] $item_id item id
 *
 * @return tags for notification
 */
function xoonips_notification_get_item_tags($item_id)
{
    $item_basic_handler = &xoonips_getormhandler('xoonips', 'item_basic');
    $item_basic = $item_basic_handler->get($item_id);
    if (!$item_basic) {
        return false;
    }

    $item_type_handler = &xoonips_getormhandler('xoonips', 'item_type');
    $item_type = $item_type_handler->get($item_basic->get('item_type_id'));
    if (!$item_type) {
        return false;
    }

    $compo_handler = &xoonips_getormcompohandler($item_type->get('name'), 'item');
    $compo = $compo_handler->get($item_id);
    if (!$compo) {
        return false;
    }

    $titles = $compo->getVar('titles');
    $keywords = $compo->getVar('keywords');
    $keyword_strings = array();
    for ($i = 0; $i < count($keywords); ++$i) {
        $keyword_strings[] = $keywords[$i]->get('keyword');
    }

    $xoops_user_handler = &xoops_gethandler('user');
    $xoops_user = $xoops_user_handler->get($item_basic->get('uid'));

    $tags = array(
        'ITEM_DOI' => strval($item_basic->get('doi')),
        'ITEM_ITEMTYPE' => strval($item_type->get('display_name')),
        'ITEM_TITLE' => (empty($titles) ? '' : strval($titles[0]->get('title'))),
        'ITEM_UNAME' => strval($xoops_user->getVar('uname', 'n')),
        'ITEM_NAME' => strval($xoops_user->getVar('name', 'n')),
        'ITEM_KEYWORDS' => implode(',', $keyword_strings),
        'ITEM_DESCRIPTION' => strval($item_basic->get('description')),
    );
    if ($item_basic->get('doi') == '') {
        $tags['ITEM_DETAIL_URL'] = XOOPS_URL.
            '/modules/xoonips/detail.php?item_id='.$item_id;
    } else {
        $tags['ITEM_DETAIL_URL'] = XOOPS_URL.
            '/modules/xoonips/detail.php?doi='.$item_basic->get('doi');
    }

    return $tags;
}

/**
 * @brief get notification tags for user
 *
 * @param[in] $user_id user id
 *
 * @return tags for notification
 */
function _xoonips_notification_get_user_tags($user_id)
{
    $user_compo_handler = &xoonips_getormcompohandler('xoonips', 'user');
    $user_compo = $user_compo_handler->get($user_id);

    $xoops_user = $user_compo->getVar('xoops_user');
    $xoonips_user = $user_compo->getVar('xoonips_user');

    $tags = array(
        'USER_UNAME' => $xoops_user->get('uname'),
        'USER_NAME' => $xoops_user->get('name'),
        'USER_EMAIL' => $xoops_user->get('email'),
        'USER_DIVISION' => $xoonips_user->get('division'),
        'USER_COMPANY_NAME' => $xoonips_user->get('company_name'),
        'USER_ADDRESS' => $xoonips_user->get('address'),
        'USER_COUNTRY' => $xoonips_user->get('country'),
        'USER_CERTIFY_URL' => XOOPS_URL.'/modules/xoonips/certifyuser.php?uid='.$user_id,
        'USER_DETAIL_URL' => XOOPS_URL.'/modules/xoonips/userinfo.php?uid='.$user_id,
    );

    return $tags;
}

/**
 * @brief notify that items are transferred.
 *
 * @param[in] $from_uid user id of transferer
 * @param[in] $to_uid user id of transferee
 * @param[in] $item_ids item id of transferred items
 * @param[in] $uid_to_notify array of user id to send notification.
 */
function xoonips_notification_item_transfer($from_uid, $to_uid, $item_ids, $uid_to_notify = array())
{
    $xoops_user_handler = &xoops_gethandler('user');
    $to_user = $xoops_user_handler->get($to_uid);
    $from_user = $xoops_user_handler->get($from_uid);

    $item_titles = _xoonips_notification_get_title_of_items($item_ids);
    $item_urls = _xoonips_notification_get_detail_urls($item_ids);

    $item_list = array();
    while (false !== current($item_titles)
           && false !== current($item_urls)) {
        $item_list[] =
            _MD_XOONIPS_TRANSFER_NOTIFICATION_ITEM_TITLE
            .current($item_titles)
            ."\n"
            ._MD_XOONIPS_TRANSFER_NOTIFICATION_ITEM_DETAIL
            .current($item_urls);
        next($item_titles);
        next($item_urls);
    }

    $tags = array(
        'FROM_UNAME' => $from_user->getVar('uname'),
        'TO_UNAME' => $to_user->getVar('uname'),
        'ITEM_LIST' => implode("\n\n", $item_list),
        );

    $nhandler = &xoonips_gethandler('xoonips', 'notification');
    $nhandler->triggerEvent2(
        'administrator', 0, 'item_transfer',
        _MD_XOONIPS_ITEM_TRANSFER_NOTIFYSBJ,
        $nhandler->getTemplateDirByMid(),
        'administrator_item_transfer_notify',
        $tags,
        $uid_to_notify
    );
}

/**
 * @brief notify that account waits for certification.
 *
 * @param[in] $user_id user id
 */
function xoonips_notification_account_certify_request($user_id)
{
    $nhandler = &xoonips_gethandler('xoonips', 'notification');
    $nhandler->triggerEvent2(
        'administrator', 0, 'account_certify',
        _MD_XOONIPS_ACCOUNT_CERTIFY_REQUEST_NOTIFYSBJ,
        $nhandler->getTemplateDirByMid(),
        'administrator_account_certify_request_notify',
        _xoonips_notification_get_user_tags($user_id),
        xoonips_notification_get_moderator_uids()
    );
}

/**
 * @brief notify that account was certified.
 *
 * @param[in] $user_id user id
 */
function xoonips_notification_account_certified($user_id)
{
    $nhandler = &xoonips_gethandler('xoonips', 'notification');
    $nhandler->triggerEvent2(
        'administrator', 0, 'account_certify',
        _MD_XOONIPS_ACCOUNT_CERTIFIED_NOTIFYSBJ,
        $nhandler->getTemplateDirByMid(),
        'administrator_account_certified_notify',
        _xoonips_notification_get_user_tags($user_id),
        xoonips_notification_get_moderator_uids()
    );
}

/**
 * @brief notify that account was rejected.
 *
 * @param[in] $user_id user id
 * @param[in] $comments reviewers comments
 */
function xoonips_notification_account_rejected($user_id, $comments)
{
    $tags = _xoonips_notification_get_user_tags($user_id);
    $tags['REVIEWERS_COMMENTS'] = $comments;
    $nhandler = &xoonips_gethandler('xoonips', 'notification');
    $nhandler->triggerEvent2(
        'administrator', 0, 'account_certify',
        _MD_XOONIPS_ACCOUNT_REJECTED_NOTIFYSBJ,
        $nhandler->getTemplateDirByMid(),
        'administrator_account_rejected_notify',
        $tags,
        xoonips_notification_get_moderator_uids()
    );
}

function xoonips_notification_get_index_path_string($index_id)
{
    //    return xnpGetIndexPathServerString( session_id(), $index_id );
    $compo_handler = &xoonips_getormcompohandler('xoonips', 'index');
    $index_names = array();
    for ($xid = $index_id; $xid != IID_ROOT;) {
        $compo = $compo_handler->get($xid);
        if (!$compo) {
            break;
        }
        $titles = $compo->getVar('titles');
        $index_names[] = $titles[DEFAULT_INDEX_TITLE_OFFSET]->get('title');
        $index = $compo->getVar('index');
        $xid = $index->get('parent_index_id');
    }

    return '/ '.implode(' / ', array_reverse($index_names));
}

/**
 * @brief notify that item waits for certification, was manually certified,
 * was manually rejected or was automatically certified.
 *
 * @param[in] $item_id  item id
 * @param[in] $index_id index id
 * @param[in] $subject  subject of notification
 * @param[in] $template_name  template file name of notification
 */
function xoonips_notification_item_certify($item_id, $index_id, $subject, $template_name)
{
    $index_handler = &xoonips_getormhandler('xoonips', 'index');
    $index = $index_handler->get($index_id);
    if ($index->get('open_level') == OL_PUBLIC) {
        $uids = xoonips_notification_get_moderator_uids();
    } elseif ($index->get('open_level') == OL_GROUP_ONLY) {
        // get uids of group admin
        $xg_handler = &xoonips_gethandler('xoonips', 'group');
        $uids = $xg_handler->getUserIds($index->get('gid'), true);
    } else { // private index
        return;
    }

    $tags = xoonips_notification_get_item_tags($item_id);
    $tags['INDEX_PATH'] = xoonips_notification_get_index_path_string($index_id);
    $tags['ITEM_CERTIFY_URL'] = XOOPS_URL.'/modules/xoonips/certify.php';

    $nhandler = &xoonips_gethandler('xoonips', 'notification');
    $nhandler->triggerEvent2(
        'administrator', 0, 'item_certify',
        $subject, $nhandler->getTemplateDirByMid(),
        $template_name, $tags, $uids
    );
}

/**
 * @brief notify that item waits for certification
 *
 * @param[in] $item_id item id
 * @param[in] $index_id index id
 */
function xoonips_notification_item_certify_request($item_id, $index_id)
{
    xoonips_notification_item_certify($item_id, $index_id, _MD_XOONIPS_ITEM_CERTIFY_REQUEST_NOTIFYSBJ, 'administrator_item_certify_request_notify');
}

/**
 * @brief notify that item waits for certification
 *
 * @param[in] $item_id item id
 * @param[in] $index_id index id
 */
function xoonips_notification_item_certified_auto($item_id, $index_id)
{
    xoonips_notification_item_certify($item_id, $index_id, _MD_XOONIPS_ITEM_CERTIFIED_AUTO_NOTIFYSBJ, 'administrator_item_certified_auto_notify');
}

function xoonips_notification_item_certified($item_id, $index_id)
{
    xoonips_notification_item_certify($item_id, $index_id, _MD_XOONIPS_ITEM_CERTIFIED_NOTIFYSBJ, 'administrator_item_certified_notify');
}

function xoonips_notification_item_rejected($item_id, $index_id)
{
    xoonips_notification_item_certify($item_id, $index_id, _MD_XOONIPS_ITEM_REJECTED_NOTIFYSBJ, 'administrator_item_rejected_notify');
}

function xoonips_notification_user_item_transfer_request($to_uid)
{
    $tags = array(
        'TRANSFER_REQUEST_CONFIRM_URL' => XOOPS_URL.'/modules/xoonips/transfer_item.php?action=list_item',
    );

    $nhandler = &xoonips_gethandler('xoonips', 'notification');
    $nhandler->triggerEvent2(
        'user', 0, 'item_transfer',
        _MD_XOONIPS_USER_ITEM_TRANSFER_REQUEST_NOTIFYSBJ,
        $nhandler->getTemplateDirByMid(),
        'user_item_transfer_request_notify',
        $tags, array($to_uid)
    );
}

function xoonips_notification_user_item_transfer_accepted($from_uid, $to_uid, $item_ids)
{
    $xoops_user_handler = &xoops_gethandler('user');
    $to_user = $xoops_user_handler->get($to_uid);

    $tags = array(
        'TO_UNAME' => $to_user->getVar('uname'),
        'ITEM_LIST' => _xoonips_notification_get_item_list($from_uid, $item_ids),
    );

    $nhandler = &xoonips_gethandler('xoonips', 'notification');
    $nhandler->triggerEvent2(
        'user', 0, 'item_transfer',
        _MD_XOONIPS_USER_ITEM_TRANSFER_ACCEPTED_NOTIFYSBJ,
        $nhandler->getTemplateDirByMid(),
        'user_item_transfer_accepted_notify',
        $tags, array($from_uid)
    );
}

function xoonips_notification_user_item_transfer_rejected($from_uid, $to_uid, $item_ids)
{
    $xoops_user_handler = &xoops_gethandler('user');
    $to_user = $xoops_user_handler->get($to_uid);
    $item_titles = _xoonips_notification_get_title_of_items($item_ids);

    $tags = array(
        'TO_UNAME' => $to_user->getVar('uname'),
        'ITEM_LIST' => _xoonips_notification_get_item_list($from_uid, $item_ids),
    );

    $nhandler = &xoonips_gethandler('xoonips', 'notification');
    $nhandler->triggerEvent2(
        'user', 0, 'item_transfer',
        _MD_XOONIPS_USER_ITEM_TRANSFER_REJECTED_NOTIFYSBJ,
        $nhandler->getTemplateDirByMid(),
        'user_item_transfer_rejected_notify',
        $tags, array($from_uid)
    );
}

function _xoonips_notification_get_descendant_index_ids($index_id)
{
    $result = array($index_id);
    $index_handler = &xoonips_getormhandler('xoonips', 'index');
    $indexes = &$index_handler->getObjects(new Criteria('parent_index_id', $index_id));
    if (!empty($indexes)) {
        foreach ($indexes as $index) {
            $result = array_merge($result, _xoonips_notification_get_descendant_index_ids($index->get('index_id')));
        }
    }

    return $result;
}

function _xoonips_notification_get_affected_items($start_index_id)
{
    // get all descendant index id
    $index_ids = _xoonips_notification_get_descendant_index_ids($start_index_id);

    // get all affected item_id
    $result = array();
    $item_basic_handler = &xoonips_getormhandler('xoonips', 'item_basic');
    $index_item_link_handler = &xoonips_getormhandler('xoonips', 'index_item_link');
    foreach ($index_ids as $index_id) {
        $links = &$index_item_link_handler->getObjects(new Criteria('index_id', $index_id));
        foreach ($links as $link) {
            $item_basic = $item_basic_handler->get($link->get('item_id'));
            if (!isset($result[$item_basic->get('uid')])) {
                $result[$item_basic->get('uid')] = array();
            }
            $result[$item_basic->get('uid')][] =
                $item_basic->get('item_id');
        }
    }
    foreach (array_keys($result) as $uid) {
        $result[$uid] = array_unique($result[$uid]);
    }

    return $result;
}

function _xoonips_notification_get_title_of_items($item_ids)
{
    $item_titles = array();
    $title_handler = &xoonips_getormhandler('xoonips', 'title');
    foreach ($item_ids as $item_id) {
        $titles = &$title_handler->getObjects(new Criteria('item_id', $item_id));
        if (count($titles)) {
            $item_titles[] = $titles[DEFAULT_INDEX_TITLE_OFFSET]->get('title');
        }
    }

    return $item_titles;
}

function xoonips_notification_send_user_index_notification($context, $subject, $template_name)
{
    $new_index_path = xoonips_notification_get_index_path_string($context['index_id']);

    foreach ($context['affected_items'] as $uid => $item_ids) {
        $item_list = array();
        foreach ($item_ids as $item_id) {
            $tags = xoonips_notification_get_item_tags($item_id);
            $item_list[] =
                _MD_XOONIPS_TRANSFER_NOTIFICATION_ITEM_TITLE
                .$tags['ITEM_TITLE']
                ."\n"
                ._MD_XOONIPS_TRANSFER_NOTIFICATION_ITEM_DETAIL
                .$tags['ITEM_DETAIL_URL'];
        }

        $tags = array(
            'OLD_INDEX_PATH' => $context['old_index_path'],
            'NEW_INDEX_PATH' => $new_index_path,
            'LISTITEM_URL' => XOOPS_URL.
                '/modules/xoonips/listitem.php?index_id='.
                $context['listitem_index_id'],
            'ITEM_LIST' => implode("\n\n", $item_list),
        );

        $nhandler = &xoonips_gethandler('xoonips', 'notification');
        $nhandler->triggerEvent2(
            'user', 0, 'item_updated',
            $subject, $nhandler->getTemplateDirByMid(),
            $template_name, $tags, array($uid)
        );
    }
}

function xoonips_notification_before_user_index_renamed($index_id)
{
    return array(
        'index_id' => $index_id,
        'listitem_index_id' => $index_id,
        'affected_items' => _xoonips_notification_get_affected_items($index_id),
        'old_index_path' => xoonips_notification_get_index_path_string($index_id),
    );
}

function xoonips_notification_after_user_index_renamed($context)
{
    xoonips_notification_send_user_index_notification($context, _MD_XOONIPS_USER_INDEX_RENAMED_NOTIFYSBJ, 'user_index_renamed_notify');
}

function xoonips_notification_before_user_index_moved($index_id)
{
    return array(
        'index_id' => $index_id,
        'listitem_index_id' => $index_id,
        'affected_items' => _xoonips_notification_get_affected_items($index_id),
        'old_index_path' => xoonips_notification_get_index_path_string($index_id),
    );
}

function xoonips_notification_after_user_index_moved($context)
{
    xoonips_notification_send_user_index_notification($context, _MD_XOONIPS_USER_INDEX_MOVED_NOTIFYSBJ, 'user_index_moved_notify');
}

function xoonips_notification_before_user_index_deleted($index_id)
{
    $index_handler = &xoonips_getormhandler('xoonips', 'index');
    $index = $index_handler->get($index_id);

    return array(
        'index_id' => $index_id,
        'listitem_index_id' => $index->get('parent_index_id'),
        'affected_items' => _xoonips_notification_get_affected_items($index_id),
        'old_index_path' => xoonips_notification_get_index_path_string($index_id),
    );
}

function xoonips_notification_after_user_index_deleted($context)
{
    xoonips_notification_send_user_index_notification($context, _MD_XOONIPS_USER_INDEX_DELETED_NOTIFYSBJ, 'user_index_deleted_notify');
}

function xoonips_notification_user_item_certified($item_id, $index_ids)
{
    $tags = xoonips_notification_get_item_tags($item_id);

    $paths = array();
    foreach ($index_ids as $index_id) {
        $paths[] = xoonips_notification_get_index_path_string($index_id);
    }
    $tags['INDEX_PATHS'] = implode("\n", $paths);

    $item_basic_handler = &xoonips_getormhandler('xoonips', 'item_basic');
    $item_basic = $item_basic_handler->get($item_id);

    $nhandler = &xoonips_gethandler('xoonips', 'notification');
    $nhandler->triggerEvent2(
        'user', 0, 'item_certified',
        _MD_XOONIPS_USER_ITEM_CERTIFIED_NOTIFYSBJ,
        $nhandler->getTemplateDirByMid(),
        'user_item_certified_notify',
        $tags, array($item_basic->get('uid'))
    );
}

function xoonips_notification_user_item_rejected($item_id, $index_ids)
{
    $tags = xoonips_notification_get_item_tags($item_id);

    $paths = array();
    foreach ($index_ids as $index_id) {
        $paths[] = xoonips_notification_get_index_path_string($index_id);
    }
    $tags['INDEX_PATHS'] = implode("\n", $paths);

    $item_basic_handler = &xoonips_getormhandler('xoonips', 'item_basic');
    $item_basic = $item_basic_handler->get($item_id);

    $nhandler = &xoonips_gethandler('xoonips', 'notification');
    $nhandler->triggerEvent2(
        'user', 0, 'item_rejected',
        _MD_XOONIPS_USER_ITEM_REJECTED_NOTIFYSBJ,
        $nhandler->getTemplateDirByMid(),
        'user_item_rejected_notify',
        $tags, array($item_basic->get('uid'))
    );
}

function xoonips_notification_user_file_downloaded($file_id, $downloader_uid)
{
    $file_handler = &xoonips_getormhandler('xoonips', 'file');
    $file = $file_handler->get($file_id);
    $user_handler = &xoops_gethandler('user');
    $user = $user_handler->get($downloader_uid);
    $item_basic_handler = &xoonips_getormhandler('xoonips', 'item_basic');
    $item_basic = $item_basic_handler->get($file->get('item_id'));

    $tags = xoonips_notification_get_item_tags($file->get('item_id'));
    $tags['DOWNLOAD_TIMESTAMP'] = date('Y/m/d H:i:s');
    $tags['ORIGINAL_FILE_NAME'] = $file->get('original_file_name');
    $tags['UNAME'] = $user->getVar('uname');

    $nhandler = &xoonips_gethandler('xoonips', 'notification');
    $nhandler->triggerEvent2(
        'user', 0, 'file_downloaded',
        _MD_XOONIPS_USER_FILE_DOWNLOADED_NOTIFYSBJ,
        $nhandler->getTemplateDirByMid(),
        'user_file_downloaded_notify',
        $tags, array($item_basic->get('uid'))
    );
}

function _xoonips_notification_get_detail_urls($item_ids)
{
    $result = array();
    $handler = &xoonips_getormcompohandler('xoonips', 'item');
    foreach ($item_ids as $id) {
        $result[] = $handler->getItemDetailUrl($id);
    }

    return $result;
}

function _xoonips_notification_get_item_list($transfer_uid, $item_ids)
{
    $item_list = array();
    $handler = &xoonips_getormcompohandler('xoonips', 'item');
    foreach ($item_ids as $item_id) {
        $item_titles = _xoonips_notification_get_title_of_items(array($item_id));
        $item_urls = _xoonips_notification_get_detail_urls(array($item_id));
        $item_list[] =
            _MD_XOONIPS_TRANSFER_NOTIFICATION_ITEM_TITLE
            .$item_titles[0]
            ."\n"
            ._MD_XOONIPS_TRANSFER_NOTIFICATION_ITEM_DETAIL
            .($handler->getPerm($item_id, $transfer_uid, 'read')
                ? $item_urls[0]
                : _MD_XOONIPS_TRANSFER_NOTIFICATION_ITEM_DETAIL_FORBIDDEN);
    }

    return implode("\n\n", $item_list);
}
