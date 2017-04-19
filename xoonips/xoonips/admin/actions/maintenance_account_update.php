<?php

// $Revision:$
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

// check token ticket
require_once '../class/base/gtickets.php';
$ticket_area = 'xoonips_admin_maintenance_account_edit';
if (!$xoopsGTicket->check(true, $ticket_area, false)) {
    redirect_header($xoonips_admin['mypage_url'], 3, $xoopsGTicket->getErrors());
    exit();
}

// get variables
$keys['extra'] = array(
    'uid' => array('i', false, true),
    'pass' => array('s', false, true),
    'pass2' => array('s', false, true),
);
$keys['xoops'] = array(
    // key => array ( type, is_array, required ),
    'uname' => array('s', false, true),
    'name' => array('s', false, true),
    'email' => array('s', false, true),
    'user_viewemail' => array('i', false, false),
    'url' => array('s', false, true),
    'timezone_offset' => array('s', false, true),
    'user_intrest' => array('s', false, true),
    'user_sig' => array('s', false, true),
    'attachsig' => array('i', false, false),
    'umode' => array('s', false, true),
    'uorder' => array('s', false, true),
    'rank' => array('i', false, true),
    'notify_method' => array('i', false, true),
    'notify_mode' => array('i', false, true),
    'user_mailok' => array('i', false, true),
);
$keys['xoonips'] = array(
    // key => array ( type, is_array, required ),
    'posi' => array('i', false, true),
    'division' => array('s', false, true),
    'company_name' => array('s', false, true),
    'tel' => array('s', false, true),
    'fax' => array('s', false, true),
    'address' => array('s', false, true),
    'country' => array('s', false, true),
    'zipcode' => array('s', false, true),
    'appeal' => array('s', false, true),
    'private_item_number_limit' => array('i', false, true),
    'private_index_number_limit' => array('i', false, true),
    'private_item_storage_limit' => array('f', false, true),
    'notice_mail' => array('i', false, true),
);
$keys['groups'] = array(
    // key => array ( type, is_array, required ),
    'groups' => array('i', true, false),
);

// get requests
$vals['extra'] = xoonips_admin_get_requests('post', $keys['extra']);
$vals['xoops'] = xoonips_admin_get_requests('post', $keys['xoops']);
$vals['xoonips'] = xoonips_admin_get_requests('post', $keys['xoonips']);
$vals['groups'] = xoonips_admin_get_requests('post', $keys['groups']);

$uid = $vals['extra']['uid'];
$is_newuser = ($uid == 0) ? true : false;

// check requirement variables
function check_variables(&$vals)
{
    $requirements['xoops'] = array(
        'uname',
        'email',
        'umode',
        'uorder',
        'rank',
        'notify_method',
        'notify_mode',
        'user_mailok',
    );
    $requirements['xoonips'] = array(
        'private_item_number_limit',
        'private_index_number_limit',
        'private_item_storage_limit',
    );

    // get requirement fields from xoonips configs
    $check_keys['xoops'] = array(
        // config key => post variable
        'account_realname_optional' => 'name',
    );
    $check_keys['xoonips'] = array(
        // config key => post variable
        'account_company_name_optional' => 'company_name',
        'account_division_optional' => 'division',
        'account_country_optional' => 'country',
        'account_address_optional' => 'address',
        'account_zipcode_optional' => 'zipcode',
        'account_tel_optional' => 'tel',
        'account_fax_optional' => 'fax',
    );
    foreach ($check_keys as $type => $keys) {
        $config_keys = array();
        foreach ($keys as $key => $name) {
            $config_keys[$key] = 's';
        }
        $config_vals = xoonips_admin_get_configs($config_keys, 'n');
        foreach ($keys as $key => $name) {
            if ($config_vals[$key] == 'off') {
                // 'optional off' means required
                $requirements[$type][] = $name;
            }
        }
    }

    // check missing fields
    $missing_fields = array();
    foreach ($requirements as $type => $reqs) {
        foreach ($reqs as $name) {
            $value = trim(strval($vals[$type][$name]));
            if ($value === '') {
                $missing_fields = $name;
            }
        }
    }
    if (count($missing_fields) > 0) {
        xoops_cp_header();
        echo 'You must complete all required fields';
        xoops_cp_footer();
        exit();
    }

    // check password
    if ($vals['extra']['pass2'] != '') {
        if ($vals['extra']['pass'] != $vals['extra']['pass2']) {
            xoops_cp_header();
            echo _AM_XOONIPS_MSG_PASSWORD_MISMATCH;
            xoops_cp_footer();
            exit();
        } else {
            if (!empty($vals['extra']['pass'])) {
                $vals['xoops']['pass'] = md5($vals['extra']['pass']);
            }
        }
    }

    // checkboxes
    $checkboxes = array('user_viewemail', 'attachsig');
    foreach ($checkboxes as $type => $key) {
        $vals['xoops'][$key] = (is_null($vals['xoops'][$key]) ? 0 : 1);
    }

    // item storage limit
    $vals['xoonips']['private_item_storage_limit'] *= 1000000.0;
}

function update_groups($uid, $new_groups)
{
    global $xoopsUser;
    $member_handler = &xoops_gethandler('member');
    $edit_user = &$member_handler->getUser($uid);
    $old_groups = $edit_user->getGroups();
    if ($uid == $xoopsUser->getVar('uid') && (in_array(XOOPS_GROUP_ADMIN, $old_groups)) && !(in_array(XOOPS_GROUP_ADMIN, $new_groups))) {
        array_push($new_groups, XOOPS_GROUP_ADMIN);
    }
    foreach ($old_groups as $gid) {
        $member_handler->removeUsersFromGroup($gid, array($uid));
    }
    foreach ($new_groups as $gid) {
        $member_handler->addUserToGroup($gid, $uid);
    }

    return true;
}

function pickup_user($uid)
{
    // get user certification mode
    $config_keys = array(
        'certify_user' => 's',
    );
    $config_values = xoonips_admin_get_configs($config_keys, 'n');
    $is_certified = ($config_values['certify_user'] == 'on') ? false : true;
    // pickup
    $xm_handler = &xoonips_gethandler('xoonips', 'member');

    return $xm_handler->pickupXoopsUser($uid, $is_certified);
}

function check_user_exists($uname)
{
    $u_handler = &xoonips_getormhandler('xoonips', 'xoops_users');
    $criteria = new Criteria('uname', addslashes($uname));
    $u_count = $u_handler->getCount($criteria);
    if ($u_count != 0) {
        xoops_cp_header();
        echo 'User name '.$uname.' already exists';
        xoops_cp_footer();
        exit();
    }
}

// check variables
check_variables($vals);

// check user exists
if ($uid == 0) {
    check_user_exists($vals['xoops']['uname']);
}

// update db values
// >> xoops user information
$u_handler = &xoonips_getormhandler('xoonips', 'xoops_users');
if ($uid == 0) {
    $u_obj = &$u_handler->create();
} else {
    $u_obj = &$u_handler->get($uid);
}
if (!is_object($u_obj)) {
    redirect_header($xoonips_admin['mypage_url'], 3, _AM_XOONIPS_MSG_UNEXPECTED_ERROR);
    exit();
}
foreach ($vals['xoops'] as $key => $val) {
    $u_obj->set($key, $val);
}
if (!$u_handler->insert($u_obj)) {
    redirect_header($xoonips_admin['mypage_url'], 3, _AM_XOONIPS_MSG_UNEXPECTED_ERROR);
    exit();
}
$uid = $u_obj->getVar('uid', 'n');

// >> xoops group information
if (!update_groups($uid, $vals['groups']['groups'])) {
    redirect_header($xoonips_admin['mypage_url'], 3, _AM_XOONIPS_MSG_UNEXPECTED_ERROR);
    exit();
}

// xoonips user information
if ($is_newuser) {
    if (!pickup_user($uid)) {
        redirect_header($xoonips_admin['mypage_url'], 3, _AM_XOONIPS_MSG_UNEXPECTED_ERROR);
        exit();
    }
}
$xu_handler = &xoonips_getormhandler('xoonips', 'users');
$xu_obj = &$xu_handler->get($uid);
foreach ($vals['xoonips'] as $key => $val) {
    $xu_obj->set($key, $val);
}
if (!$xu_handler->insert($xu_obj)) {
    redirect_header($xoonips_admin['mypage_url'], 3, _AM_XOONIPS_MSG_UNEXPECTED_ERROR);
    exit();
}

redirect_header($xoonips_admin['mypage_url'], 3, _AM_XOONIPS_MSG_DBUPDATED);
