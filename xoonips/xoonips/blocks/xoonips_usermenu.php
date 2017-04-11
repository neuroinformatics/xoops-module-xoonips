<?php

// $Revision: 1.1.4.1.2.10 $
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

// xoonips usermenu block
function b_xoonips_user_show()
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

    $block = array();

  // get xoonips module id
  $module_handler = &xoops_gethandler('module');
    $module = &$module_handler->getByDirname('xoonips');
    if (!is_object($module)) {
        exit('Access Denied');
    }
    $mid = $module->getVar('mid', 's');

  // get xoonips user information
  $xuser_handler = &xoonips_getormhandler('xoonips', 'users');
    $xuser_obj = &$xuser_handler->get($uid);
    if (!is_object($xuser_obj)) {
        // not xoonips user
    return false;
    }
    $is_certified = $xuser_obj->getVar('activate', 'n');
    if ($is_certified != 1) {
        // user is not certified
    return false;
    }
    $uname = $xoopsUser->getVar('uname', 's');
    $private_index_id = $xuser_obj->getVar('private_index_id', 's');
    $is_admin = $xoopsUser->isAdmin($mid);

  // get count of private messages
  $pm_handler = &xoops_gethandler('privmessage');
    $criteria = new CriteriaCompo(new Criteria('read_msg', 0));
    $criteria->add(new Criteria('to_userid', $uid));
    $new_messages = $pm_handler->getCount($criteria);

  // get configuration value of 'private_import_enabled'
  $xconfig_handler = &xoonips_getormhandler('xoonips', 'config');
    $private_import_enabled = $xconfig_handler->getValue('private_import_enabled');

  // count transfer requested items
  if (xoonips_get_version() >= 340) {
      $tr_handler = &xoonips_getormhandler('xoonips', 'transfer_request');
      $criteria = new Criteria('to_uid', $uid);
      $tr_count = $tr_handler->getCount($criteria);
  } else {
      $tr_count = 0;
  }

  // assign block template variables
  $block['private_index_id'] = $private_index_id;
    $block['is_su'] = isset($_SESSION['xoonips_old_uid']);
    $block['uid'] = $uid;
    $block['new_messages'] = $new_messages;

    $block['lang_youraccount'] = _MB_XOONIPS_USER_VIEW_ACCOUNT;
    $block['lang_editaccount'] = _MB_XOONIPS_USER_EDIT_ACCOUNT;
    $block['lang_register_item'] = _MB_XOONIPS_USER_REGISTER_ITEM;
    $block['lang_showusers'] = _MB_XOONIPS_USER_SHOW_USERS;
    $block['lang_grouplist'] = _MB_XOONIPS_USER_GROUP_LIST;
    $block['lang_notifications'] = _MB_XOONIPS_USER_NOTIFICATION;
    $block['lang_inbox'] = _MB_XOONIPS_USER_INBOX;
    $block['lang_listing_item'] = _MB_XOONIPS_USER_LISTING_ITEM;
    $block['lang_edit_index'] = _MB_XOONIPS_USER_EDIT_PRIVATE_INDEX;
    $block['lang_advanced_search'] = _MB_XOONIPS_USER_ADVANCED_SEARCH;
    $block['lang_logout'] = _MB_XOONIPS_USER_LOGOUT;
    $block['lang_su_start'] = _MB_XOONIPS_USER_SU_START;
    $block['lang_su_end'] = sprintf(_MB_XOONIPS_USER_SU_END, $uname);
    $block['lang_adminmenu'] = _MB_XOONIPS_USER_ADMINMENU;
    $block['lang_transfer_request'] = _MB_XOONIPS_USER_TRANSFER_USER_REQUEST;
    $block['lang_transfer_accept'] = _MB_XOONIPS_USER_TRANSFER_USER_ACCEPT;
    $block['lang_transfer_request_count'] = $tr_count;
    $block['lang_oaipmh_search'] = _MB_XOONIPS_USER_OAIPMH_SEARCH;
    $block['xoonips_isadmin'] = $is_admin;

    if ($is_admin || $private_import_enabled == 'on') {
        // set to $block['lang_import'] if user is permitted to import.
    // - config of private_import_enabled is true
    // - or user is administrator
    $block['lang_import'] = _MB_XOONIPS_USER_IMPORT;
    }

    return $block;
}
