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

// xoonips moderator menu block
function b_xoonips_moderator_show()
{
    global $xoopsUser;

    // hide block if user is guest
    if (!is_object($xoopsUser)) {
        return false;
    }

    $uid = $xoopsUser->getVar('uid', 'n');

    // hide block if user is invalid xoonips user
    $xsession_handler = &xoonips_getormhandler('xoonips', 'session');
    if (!$xsession_handler->validateUser($uid, false)) {
        return false;
    }

    // check moderator user
    $xmember_handler = &xoonips_gethandler('xoonips', 'member');
    if (!$xmember_handler->isModerator($uid)) {
        // user is not moderator
        return false;
    }

    // count certification requested users
    $xu_ohandler = &xoonips_getormhandler('xoonips', 'users');
    $join = new XooNIpsJoinCriteria('users', 'uid', 'uid', 'INNER', 'u');
    $criteria = new CriteriaCompo(new Criteria('activate', 0));
    $criteria->add(new Criteria('level', 0, '>', 'u'));
    $cu_count = $xu_ohandler->getCount($criteria, $join);

    // count certification requested items
    $xil_handler = &xoonips_getormhandler('xoonips', 'index_item_link');
    $join = new XooNIpsJoinCriteria('xoonips_index', 'index_id', 'index_id', 'INNER', 'x');
    $criteria = new CriteriaCompo(new Criteria('certify_state', CERTIFY_REQUIRED));
    $criteria->add(new Criteria('open_level', OL_PUBLIC, '=', 'x'));
    $ci_count = $xil_handler->getCount($criteria, $join);

    // count group items open to public certification requested indexes
    if (xoonips_get_version() >= 340) {
        $xgxl_handler = &xoonips_getormhandler('xoonips', 'index_group_index_link');
        $gi_count = $xgxl_handler->getCount();
    } else {
        $gi_count = 0;
    }

    // assign block template variables
    $block = array();
    $block['lang_edit_group'] = _MB_XOONIPS_MODERATOR_EDIT_GROUPS;
    $block['lang_certify_users'] = _MB_XOONIPS_MODERATOR_CERTIFY_USERS;
    $block['lang_certify_users_count'] = $cu_count;
    $block['lang_certify_items'] = _MB_XOONIPS_MODERATOR_CERTIFY_PUBLIC_ITEMS;
    $block['lang_certify_items_count'] = $ci_count;
    $block['lang_groupcertify_items'] = _MB_XOONIPS_MODERATOR_GROUP_CERTIFY_PUBLIC_ITEMS;
    $block['lang_groupcertify_items_count'] = $gi_count;
    $block['lang_edit_public_index'] = _MB_XOONIPS_MODERATOR_EDIT_PUBLIC_INDEX;
    $block['lang_event_log'] = _MB_XOONIPS_MODERATOR_EVENT_LOG;
    $block['xid'] = IID_PUBLIC;

    return $block;
}
