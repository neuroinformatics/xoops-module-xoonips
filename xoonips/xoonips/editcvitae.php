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

$xoopsOption['pagetype'] = 'user';
require 'include/common.inc.php';

xoonips_deny_guest_access();

$op = 'open';

$textutil = &xoonips_getutility('text');
$formdata = &xoonips_getutility('formdata');
$xmember_handler = xoonips_gethandler('xoonips', 'member');
$cvitaes_handler = xoonips_getormhandler('xoonips', 'cvitaes');

$myUid = is_object($xoopsUser) ? $xoopsUser->getVar('uid') : UID_GUEST;
$uid = $formdata->getValue('post', 'uid', 'i', false, UID_GUEST);
if ($uid <= 0) {
    $uid = $myUid;
}

// error if argument 'uid' is not equal to own UID
if ($uid != $myUid && !($xmember_handler->isAdmin($myUid) || $xmember_handler->isModerator($myUid))) {
    redirect_header(XOOPS_URL.'/', 3, _NOPERM);
}

$xoopsOption['template_main'] = 'xoonips_editcvitae.html';
require XOOPS_ROOT_PATH.'/header.php';

$op = $formdata->getValue('both', 'op', 's', false);

$op_delete = $formdata->getValue('post', 'op_delete', 's', false);
$op_modify = $formdata->getValue('post', 'op_modify', 's', false);
if (isset($op_delete)) {
    $op = 'delete';
}
if (isset($op_modify)) {
    $op = 'modify';
}

// operation
if ($op == 'open' || $op == '') {
} elseif ($op == 'register') {
    $cvitae_title = $formdata->getValue('post', 'cvitae_title', 's', false);
    if (empty($cvitae_title)) {
        $ent = 1;
        $xoopsTpl->assign('ent', $ent);
    } else {
        $cvitae_from_date = $formdata->getValueArray('post', 'cvitae_from_date', 'i', true);
        $from_month = isset($cvitae_from_date['Date_Month']) ? $cvitae_from_date['Date_Month'] : 0;
        $from_year = isset($cvitae_from_date['Date_Year']) ? $cvitae_from_date['Date_Year'] : 0;
        $cvitae_to_date = $formdata->getValueArray('post', 'cvitae_to_date', 'i', true);
        $to_month = isset($cvitae_to_date['Date_Month']) ? $cvitae_to_date['Date_Month'] : 0;
        $to_year = isset($cvitae_to_date['Date_Year']) ? $cvitae_to_date['Date_Year'] : 0;
        if (0 > $from_month || 12 < $from_month || 0 > $from_year || 0 > $to_month || 12 < $to_month || 0 > $to_year) {
            // out of date range
            xoonips_error_exit(400);
        }
        $cvitaes_obj = $cvitaes_handler->create();
        $cvitaes_obj->set('uid', $uid);
        $cvitaes_obj->set('from_month', $from_month);
        $cvitaes_obj->set('from_year', $from_year);
        $cvitaes_obj->set('to_month', $to_month);
        $cvitaes_obj->set('to_year', $to_year);
        $cvitaes_obj->set('cvitae_title', $cvitae_title);
        if (!$cvitaes_handler->insert($cvitaes_obj)) {
            xoonips_error_exit(500);
        }
        redirect_header('editcvitae.php', 1, _MD_XOONIPS_CURRICULUM_VITAE_INSERT);
    }
} elseif ($op == 'modify') {
    $check = $formdata->getValueArray('post', 'check', 'i', false);
    foreach ($check as $cvitae_id) {
        $cvitaes_obj = $cvitaes_handler->get($cvitae_id);
        if (!is_object($cvitaes_obj)) {
            // selected cv not found
            xoonips_error_exit(400);
        }
        $cvitaes_uid = $cvitaes_obj->get('uid');
        if ($uid != $cvitaes_uid) {
            // selected cv is not own cv
            xoonips_error_exit(400);
        }
        $cvitae_from_date = $formdata->getValueArray('post', $cvitae_id.'_from', 'i', true);
        $from_month = isset($cvitae_from_date['Date_Month']) ? $cvitae_from_date['Date_Month'] : 0;
        $from_year = isset($cvitae_from_date['Date_Year']) ? $cvitae_from_date['Date_Year'] : 0;
        $cvitae_to_date = $formdata->getValueArray('post', $cvitae_id.'_to', 'i', true);
        $to_month = isset($cvitae_to_date['Date_Month']) ? $cvitae_to_date['Date_Month'] : 0;
        $to_year = isset($cvitae_to_date['Date_Year']) ? $cvitae_to_date['Date_Year'] : 0;
        if (0 > $from_month || 12 < $from_month || 0 > $from_year || 0 > $to_month || 12 < $to_month || 0 > $to_year) {
            // out of date range
            xoonips_error_exit(400);
        }
        $cvitae_title = $formdata->getValue('post', 'cvitae_title'.$cvitae_id, 's', true);
        $cvitaes_obj->set('from_month', $from_month);
        $cvitaes_obj->set('from_year', $from_year);
        $cvitaes_obj->set('to_month', $to_month);
        $cvitaes_obj->set('to_year', $to_year);
        $cvitaes_obj->set('cvitae_title', $cvitae_title);
        if (!$cvitaes_handler->insert($cvitaes_obj)) {
            xoonips_error_exit(500);
        }
    }
} elseif ($op == 'delete') {
    $check = $formdata->getValueArray('post', 'check', 'i', false);
    foreach ($check as $cvitae_id) {
        $cvitaes_obj = $cvitaes_handler->get($cvitae_id);
        if (!is_object($cvitaes_obj)) {
            // selected cv not found
            xoonips_error_exit(400);
        }
        $cvitaes_uid = $cvitaes_obj->get('uid');
        if ($uid != $cvitaes_uid) {
            // selected cv is not own cv
            xoonips_error_exit(400);
        }
        if (!$cvitaes_handler->delete($cvitaes_obj)) {
            xoonips_error_exit(500);
        }
    }
} elseif ($op == 'up' || $op == 'down') {
    $move_id = $formdata->getValue('post', 'updown_cvitae', 'i', true);
    $steps = $formdata->getValueArray('post', 'steps', 'i', true);
    $step = isset($steps[$move_id]) ? $steps[$move_id] : 0;
    if (0 > $step || 10 < $step) {
        // out of step range
        xoonips_error_exit(400);
    }
    $cvitaes_objs = $cvitaes_handler->getCVs($uid);
    $cvitaes_length = count($cvitaes_objs);
    $cvitaes_found = false;
    $cvitaes_idx_from = 0;
    foreach ($cvitaes_objs as $idx => $cvitaes_obj) {
        if ($move_id == $cvitaes_obj->get('cvitae_id')) {
            $cvitaes_found = true;
            $cvitaes_idx_from = $idx;
            break;
        }
    }
    if (!$cvitaes_found) {
        // selected cv not found
        xoonips_error_exit(400);
    }
    $cvitaes_move_step = 'up' == $op ? -1 * $step : $step;
    $cvitaes_idx_to = $cvitaes_move_step + $cvitaes_idx_from;
    if (0 > $cvitaes_idx_to) {
        $cvitaes_idx_to = 0;
    } elseif ($cvitaes_length <= $cvitaes_idx_to) {
        $cvitaes_idx_to = $cvitaes_length - 1;
    }
    if ($cvitaes_idx_from != $cvitaes_idx_to) {
        // sort cv entries
        $cvitaes_obj = array_splice($cvitaes_objs, $cvitaes_idx_from, 1);
        array_splice($cvitaes_objs, $cvitaes_idx_to, 0, $cvitaes_obj);
        // update cvitae_order
        foreach ($cvitaes_objs as $idx => $cvitaes_obj) {
            $cvitaes_obj->set('cvitae_order', $idx + 1);
            if (!$cvitaes_handler->insert($cvitaes_obj)) {
                xoonips_error_exit(500);
            }
        }
    }
}

// display confirm form
$cvitaes_objs = $cvitaes_handler->getCVs($uid);
$rcount = count($cvitaes_objs);
$xoopsTpl->assign('rcount', $rcount);
foreach ($cvitaes_objs as $cvitaes_obj) {
    $cvdata['cvitae_id'] = (int) $cvitaes_obj->get('cvitae_id');
    $cvdata['cvitae_from_date'] = $textutil->html_special_chars(xnpMktime($cvitaes_obj->get('from_year'), $cvitaes_obj->get('from_month'), 0));
    $cvdata['cvitae_to_date'] = $textutil->html_special_chars(xnpMktime($cvitaes_obj->get('to_year'), $cvitaes_obj->get('to_month'), 0));
    $cvdata['cvitae_title'] = $textutil->html_special_chars($cvitaes_obj->get('cvitae_title'));
    $xoopsTpl->append('cv_array', $cvdata);
}
$xoopsTpl->assign('updown_options', array(1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7, 8 => 8, 9 => 9, 10 => 10));

require XOOPS_ROOT_PATH.'/footer.php';
exit();

function xnpMktime($year, $month, $day)
{
    $int_year = intval($year);
    $int_month = intval($month);
    $int_day = intval($day);
    if ($int_month == 0) {
        $date = sprintf('%04s--%02s', $int_year, $int_day);
    } else {
        $date = sprintf('%04s-%02s-%02s', $int_year, $int_month, $int_day);
    }

    return $date;
}
