<?php

// $Revision: 1.1.4.1.2.6 $
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

// resources
$langman->read_pagetype('user.php');
$langman->read_pagetype('notification.php');
include XOOPS_ROOT_PATH.'/include/notification_constants.php';

// class files
require_once XOOPS_ROOT_PATH.'/class/xoopslists.php';

// functions
function get_user_info($uid)
{
    $textutil = &xoonips_getutility('text');
    $u_handler = &xoonips_getormhandler('xoonips', 'xoops_users');
    $xu_handler = &xoonips_getormhandler('xoonips', 'users');
    $m_handler = &xoops_gethandler('member');
    $p_handler = &xoonips_getormhandler('xoonips', 'positions');
    if ($uid == 0) {
        $u_obj = &$u_handler->create();
        $xu_obj = &$xu_handler->create();
    } else {
        $u_obj = &$u_handler->get($uid);
        $xu_obj = &$xu_handler->get($uid);
    }
    if ((!is_object($u_obj)) || (!is_object($xu_obj))) {
        return false;
    }
    $user['xoops'] = $u_obj->getVarArray('e');
    $user['xoonips'] = $xu_obj->getVarArray('e');
    // groups
    $grouplist = $m_handler->getGroupList(new Criteria('groupid', XOOPS_GROUP_ANONYMOUS, '!='));
    if ($uid == 0) {
        $groups = array(XOOPS_GROUP_USERS);
    } else {
        $groups = $m_handler->getGroupsByUser($user['xoops']['uid']);
    }
    $user['groups'] = array();
    foreach ($grouplist as $gid => $name) {
        $user['groups'][] = array(
            'gid' => $gid,
            'name' => $textutil->html_special_chars($name),
            'selected' => in_array($gid, $groups) ? 'selected="selected"' : '',
        );
    }
    // position
    $positionlist = &$p_handler->getPositionList('s');
    $user['position'] = array();
    $user['position'][] = array(
        'posi_id' => 0,
        'title' => '--------------',
        'selected' => (empty($user['xoonips']['posi'])) ? 'selected="selected"' : '',
    );
    foreach ($positionlist as $posi_id => $vars) {
        $user['position'][] = array(
            'posi_id' => $posi_id,
            'title' => $vars['posi_title'],
            'selected' => ($user['xoonips']['posi'] == $posi_id) ? 'selected="selected"' : '',
        );
    }
    // timezone
    $timezones = XoopsLists::getTimeZoneList();
    $user['timezone'] = array();
    foreach ($timezones as $offset => $name) {
        $user['timezone'][] = array(
            'offset' => $offset,
            'name' => $textutil->html_special_chars($name),
            'selected' => ($offset == $user['xoops']['timezone_offset']) ? 'selected="selected"' : '',
       );
    }
    // umode - comment display mode
    $user['umode'] = array();
    $umodes = array(
        'nest' => _NESTED,
        'flat' => _FLAT,
        'thread' => _THREADED,
    );
    foreach ($umodes as $key => $name) {
        $user['umode'][] = array(
            'umode' => $key,
            'name' => $textutil->html_special_chars($name),
            'selected' => ($key == $user['xoops']['umode']) ? 'selected="selected"' : '',
        );
    }
    // uorder - comment sort order
    $user['uorder'] = array();
    $uorders = array(
        '0' => _OLDESTFIRST,
        '1' => _NEWESTFIRST,
    );
    foreach ($uorders as $key => $name) {
        $user['uorder'][] = array(
            'uorder' => $key,
            'name' => $textutil->html_special_chars($name),
            'selected' => ($key == $user['xoops']['uorder']) ? 'selected="selected"' : '',
        );
    }
    // rank
    $ranklist = XoopsLists::getUserRankList();
    if (count($ranklist) > 0) {
        $user['rank'][] = array(
            'rank' => 0,
            'name' => '--------------',
            'selected' => ($user['xoops']['rank'] == 0) ? 'selected="selected"' : '',
        );
        foreach ($ranklist as $rank => $name) {
            $user['rank'][] = array(
              'rank' => $rank,
              'name' => $textutil->html_special_chars($name),
              'selected' => ($user['xoops']['rank'] == $rank) ? 'selected="selected"' : '',
            );
        }
    } else {
        $user['rank'][] = array(
            'rank' => 0,
            'name' => $textutil->html_special_chars(_AM_NSRID),
            'selected' => 'selected="selected"',
        );
    }
    // notify method
    $user['notify_method'] = array();
    $notify_methods = array(
        XOOPS_NOTIFICATION_METHOD_DISABLE => _NOT_METHOD_DISABLE,
        XOOPS_NOTIFICATION_METHOD_PM => _NOT_METHOD_PM,
        XOOPS_NOTIFICATION_METHOD_EMAIL => _NOT_METHOD_EMAIL,
    );
    foreach ($notify_methods as $key => $name) {
        $user['notify_method'][] = array(
            'notify_method' => $key,
            'name' => $textutil->html_special_chars($name),
            'selected' => ($key == $user['xoops']['notify_method']) ? 'selected="selected"' : '',
        );
    }
    // notify mode
    $user['notify_mode'] = array();
    $notify_modes = array(
        XOOPS_NOTIFICATION_MODE_SENDALWAYS => _NOT_MODE_SENDALWAYS,
        XOOPS_NOTIFICATION_MODE_SENDONCETHENDELETE => _NOT_MODE_SENDONCE,
        XOOPS_NOTIFICATION_MODE_SENDONCETHENWAIT => _NOT_MODE_SENDONCEPERLOGIN,
    );
    foreach ($notify_modes as $key => $name) {
        $user['notify_mode'][] = array(
            'notify_mode' => $key,
            'name' => $textutil->html_special_chars($name),
            'selected' => ($key == $user['xoops']['notify_mode']) ? 'selected="selected"' : '',
        );
    }
    // mailok
    $user['user_mailok'] = array();
    $user_mailoks = array(
        1 => _AM_XOONIPS_LABEL_YES,
        0 => _AM_XOONIPS_LABEL_NO,
    );
    foreach ($user_mailoks as $key => $name) {
        $user['user_mailok'][] = array(
          'user_mailok' => $key,
          'title' => $textutil->html_special_chars($name),
          'checked' => ($key == $user['xoops']['user_mailok']) ? 'checked="checked"' : '',
        );
    }

    return $user;
}

function get_requirements($uid)
{
    $requirements = array(
        'uname',
        'email',
        'umode',
        'uorder',
        'rank',
        'notify_method',
        'notify_mode',
        'user_mailok',
        'private_item_number_limit',
        'private_index_number_limit',
        'private_item_storage_limit',
    );
    if ($uid == 0) {
        array_push($requirements, 'pass');
        array_push($requirements, 'pass2');
    }
    $keys = array(
        // config key => array name
        'account_realname_optional' => 'name',
        'account_address_optional' => 'address',
        'account_division_optional' => 'division',
        'account_tel_optional' => 'tel',
        'account_company_name_optional' => 'company_name',
        'account_country_optional' => 'country',
        'account_zipcode_optional' => 'zipcode',
        'account_fax_optional' => 'fax',
    );
    $config_keys = array();
    foreach (array_keys($keys) as $key) {
        $config_keys[$key] = 's';
    }
    $config_vals = xoonips_admin_get_configs($config_keys, 's');
    foreach ($keys as $key => $name) {
        if ($config_vals[$key] == 'off') {
            $requirements[] = $name;
        }
    }

    return $requirements;
}

function get_title($key)
{
    global $userinfo_labels;
    global $userinfo_requirements;

    return $userinfo_labels[$key].(in_array($key, $userinfo_requirements) ? _AM_XOONIPS_LABEL_REQUIRED_MARK : '');
}

// get user information
$user = get_user_info($uid);
if ($user === false) {
    redirect_header($xoonips_admin['mypage_url'], 3, _AM_XOONIPS_MSG_ILLACCESS);
    exit();
}
if ($uid != 0 && $user['xoops']['level'] == 0) {
    // needs activate
    include 'actions/maintenance_account_aconfirm.php';
    exit();
}

// labels
$userinfo_labels = array(
    'uname' => _US_NICKNAME,
    'name' => _US_REALNAME,
    'email' => _US_EMAIL,
    'user_viewmail' => _US_ALLOWVIEWEMAIL,
    'url' => _US_WEBSITE,
    'groups' => _US_GROUPS,
    'position' => _AM_XOONIPS_LABEL_POSITION,
    'division' => _AM_XOONIPS_LABEL_DIVISION,
    'company_name' => _AM_XOONIPS_LABEL_COMPANY_NAME,
    'tel' => _AM_XOONIPS_LABEL_TEL,
    'fax' => _AM_XOONIPS_LABEL_FAX,
    'address' => _AM_XOONIPS_LABEL_ADDRESS,
    'country' => _AM_XOONIPS_LABEL_COUNTRY,
    'zipcode' => _AM_XOONIPS_LABEL_ZIPCODE,
    'timezone' => _US_TIMEZONE,
    'user_intrest' => _US_INTEREST,
    'appeal' => _AM_XOONIPS_LABEL_APPEAL,
    'user_sig' => _US_SIGNATURE,
    'attachsig' => _US_SHOWSIG,
    'pass' => _US_PASSWORD,
    'pass2' => _US_VERIFYPASS,
    'notice_mail' => _AM_XOONIPS_LABEL_NOTICE_MAIL,
    'private_item_number_limit' => _AM_XOONIPS_LABEL_PRIVATE_ITEM_NUMBER_LIMIT,
    'private_index_number_limit' => _AM_XOONIPS_LABEL_PRIVATE_INDEX_NUMBER_LIMIT,
    'private_item_storage_limit' => _AM_XOONIPS_LABEL_PRIVATE_ITEM_STORAGE_LIMIT,
    'umode' => _US_CDISPLAYMODE,
    'uorder' => _US_CSORTORDER,
    'rank' => _US_RANK,
    'notify_method' => _NOT_NOTIFYMETHOD,
    'notify_mode' => _NOT_NOTIFYMODE,
    'user_mailok' => _US_MAILOK,
    'submit' => _AM_XOONIPS_LABEL_UPDATE,
);

// requirements
$userinfo_requirements = get_requirements($uid);

// validate
$validate = array();
foreach ($userinfo_requirements as $key) {
    $validate[] = array(
        'name' => $key,
        'message' => sprintf(_FORM_ENTER, $userinfo_labels[$key]),
    );
}

// breadcrumbs
$breadcrumbs = array(
    array(
        'type' => 'top',
        'label' => _AM_XOONIPS_TITLE,
        'url' => $xoonips_admin['admin_url'].'/',
    ),
    array(
        'type' => 'link',
        'label' => _AM_XOONIPS_MAINTENANCE_TITLE,
        'url' => $xoonips_admin['myfile_url'],
    ),
    array(
        'type' => 'link',
        'label' => _AM_XOONIPS_MAINTENANCE_ACCOUNT_TITLE,
        'url' => $xoonips_admin['mypage_url'],
    ),
    array(
        'type' => 'label',
        'label' => $title,
        'url' => '',
    ),
);

// token ticket
require_once '../class/base/gtickets.php';
$ticket_area = 'xoonips_admin_maintenance_account_edit';
$token_ticket = $xoopsGTicket->getTicketHtml(__LINE__, 1800, $ticket_area);

// templates
require_once '../class/base/pattemplate.class.php';
$tmpl = new PatTemplate();
$tmpl->setBaseDir('templates');
$tmpl->readTemplatesFromFile('maintenance_account_edit.tmpl.html');

// assign template variables
$tmpl->addVar('header', 'TITLE', $title);
$tmpl->setAttribute('breadcrumbs', 'visibility', 'visible');
$tmpl->addRows('breadcrumbs_items', $breadcrumbs);
$tmpl->addVar('main', 'token_ticket', $token_ticket);
// >> user inforamtion from
$tmpl->addVar('main', 'form_title', $title);
$tmpl->addVar('main', 'action', 'update');
$tmpl->addVar('main', 'uid', $uid);
$tmpl->addVar('main', 'uname_title', get_title('uname'));
$tmpl->addVar('main', 'uname_value', $user['xoops']['uname']);
$tmpl->addVar('main', 'name_title', get_title('name'));
$tmpl->addVar('main', 'name_value', $user['xoops']['name']);
$tmpl->addVar('main', 'email_title', get_title('email'));
$tmpl->addVar('main', 'email_value', $user['xoops']['email']);
$tmpl->addVar('main', 'user_viewemail_title', get_title('user_viewmail'));
$tmpl->addVar('main', 'user_viewemail_checked', ($user['xoops']['user_viewemail'] == 1) ? 'checked="checked"' : '');
$tmpl->addVar('main', 'url_title', get_title('url'));
$tmpl->addVar('main', 'url_value', $user['xoops']['url']);
$tmpl->addVar('main', 'groups_title', get_title('groups'));
$tmpl->addRows('groups', $user['groups']);
$tmpl->addVar('main', 'position_title', get_title('position'));
$tmpl->addRows('position', $user['position']);
$tmpl->addVar('main', 'division_title', get_title('division'));
$tmpl->addVar('main', 'division_value', $user['xoonips']['division']);
$tmpl->addVar('main', 'company_name_title', get_title('company_name'));
$tmpl->addVar('main', 'company_name_value', $user['xoonips']['company_name']);
$tmpl->addVar('main', 'tel_title', get_title('tel'));
$tmpl->addVar('main', 'tel_value', $user['xoonips']['tel']);
$tmpl->addVar('main', 'fax_title', get_title('fax'));
$tmpl->addVar('main', 'fax_value', $user['xoonips']['fax']);
$tmpl->addVar('main', 'address_title', get_title('address'));
$tmpl->addVar('main', 'address_value', $user['xoonips']['address']);
$tmpl->addVar('main', 'country_title', get_title('country'));
$tmpl->addVar('main', 'country_value', $user['xoonips']['country']);
$tmpl->addVar('main', 'zipcode_title', get_title('zipcode'));
$tmpl->addVar('main', 'zipcode_value', $user['xoonips']['zipcode']);
$tmpl->addVar('main', 'timezone_title', get_title('timezone'));
$tmpl->addRows('timezone', $user['timezone']);
$tmpl->addVar('main', 'user_intrest_title', get_title('user_intrest'));
$tmpl->addVar('main', 'user_intrest_value', $user['xoops']['user_intrest']);
$tmpl->addVar('main', 'appeal_title', get_title('appeal'));
$tmpl->addVar('main', 'appeal_value', $user['xoonips']['appeal']);
$tmpl->addVar('main', 'user_sig_title', get_title('user_sig'));
$tmpl->addVar('main', 'user_sig_value', $user['xoops']['user_sig']);
$tmpl->addVar('main', 'attachsig_title', get_title('attachsig'));
$tmpl->addVar('main', 'attachsig_checked', ($user['xoops']['attachsig'] == 1 ? 'checked="checked"' : ''));
$tmpl->addVar('main', 'pass_title', get_title('pass'));
$tmpl->addVar('main', 'pass2_title', get_title('pass2'));
$tmpl->addVar('main', 'notice_mail_title', get_title('notice_mail'));
$tmpl->addVar('main', 'notice_mail_value', $user['xoonips']['notice_mail']);
$tmpl->addVar('main', 'private_item_number_limit_title', get_title('private_item_number_limit'));
$tmpl->addVar('main', 'private_item_number_limit_value', $user['xoonips']['private_item_number_limit']);
$tmpl->addVar('main', 'private_index_number_limit_title', get_title('private_index_number_limit'));
$tmpl->addVar('main', 'private_index_number_limit_value', $user['xoonips']['private_index_number_limit']);
$tmpl->addVar('main', 'private_item_storage_limit_title', get_title('private_item_storage_limit'));
$tmpl->addVar('main', 'private_item_storage_limit_value', $user['xoonips']['private_item_storage_limit'] / 1000000.0);
$tmpl->addVar('main', 'umode_title', get_title('umode'));
$tmpl->addRows('umode', $user['umode']);
$tmpl->addVar('main', 'uorder_title', get_title('uorder'));
$tmpl->addRows('uorder', $user['uorder']);
$tmpl->addVar('main', 'rank_title', get_title('rank'));
$tmpl->addRows('rank', $user['rank']);
$tmpl->addVar('main', 'notify_method_title', get_title('notify_method'));
$tmpl->addRows('notify_method', $user['notify_method']);
$tmpl->addVar('main', 'notify_mode_title', get_title('notify_mode'));
$tmpl->addRows('notify_mode', $user['notify_mode']);
$tmpl->addVar('main', 'user_mailok_title', get_title('user_mailok'));
$tmpl->addRows('user_mailok', $user['user_mailok']);
$tmpl->addVar('main', 'submit', get_title('submit'));
if (count($validate) == 0) {
    $tmpl->setAttribute('validate', 'visibility', 'hidden');
} else {
    $tmpl->addRows('validate', $validate);
}

// display
xoops_cp_header();
$tmpl->displayParsedTemplate('main');
xoops_cp_footer();
