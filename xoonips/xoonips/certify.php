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
require_once 'include/AL.php';
require_once 'include/notification.inc.php';
require 'class/base/gtickets.php';

$xnpsid = $_SESSION['XNPSID'];

xoonips_deny_guest_access('user.php');

$uid = $_SESSION['xoopsUserId'];

$xgroup_handler = &xoonips_gethandler('xoonips', 'group');

$is_moderator = xnp_is_moderator($xnpsid, $uid);
$admin_gids = $xgroup_handler->getGroupIds($uid, true);
$is_groupadmin = (0 != count($admin_gids));

// Only Moderator and Group administrator can access this page.
if (!$is_moderator) {
    if (!xnp_is_activated($xnpsid, $uid)) {
        redirect_header(XOOPS_URL.'/', 3, _MD_XOONIPS_MODERATOR_NOT_ACTIVATED);
        exit();
    }

    if (!$is_groupadmin) {
        redirect_header(XOOPS_URL.'/', 3, _MD_XOONIPS_ITEM_FORBIDDEN);
        exit();
    }
}

// get requests
$formdata = &xoonips_getutility('formdata');
$op = $formdata->getValue('post', 'op', 's', false, '');
$menu_id = $formdata->getValue('get', 'menu_id', 'i', false);
$index_ids = $formdata->getValueArray('post', 'index_ids', 'i', false);
$item_id = $formdata->getValue('post', 'item_id', 'i', false);
$bulk = $formdata->getValue('post', 'bulk', 'i', false);
// check request variables
if ('certify' == $op || 'uncertify' == $op) {
    if (0 == $bulk && is_null($item_id)) {
        die('illegal request');
    }
} elseif ('' != $op) {
    die('illegal request');
}

if (1 == $menu_id) {
    // pankuzu for administrator
    $pankuzu = _MI_XOONIPS_ACCOUNT_PANKUZU_MODERATOR._MI_XOONIPS_ACCOUNT_PANKUZU_SEPARATOR._MI_XOONIPS_ITEM_PANKUZU_CERTIFY_PUBLIC_ITEMS;
    if (!$is_moderator) {
        redirect_header(XOOPS_URL.'/', 3, _NOPERM);
        exit();
    }
} elseif (2 == $menu_id) {
    // pankuzu for group administrator
    $pankuzu = _MI_XOONIPS_ACCOUNT_PANKUZU_GROUP_ADMINISTRATOR._MI_XOONIPS_ACCOUNT_PANKUZU_SEPARATOR._MI_XOONIPS_ITEM_PANKUZU_CERTIFY_GROUP_ITEMS;
    if (!$is_groupadmin) {
        redirect_header(XOOPS_URL.'/', 3, _NOPERM);
        exit();
    }
} else {
    $pankuzu = '';
}

// accept or reject certify,  send email
if ('certify' == $op || 'uncertify' == $op) {
    // check token ticket
    if (!$xoopsGTicket->check(true, 'xoonips_certify_item')) {
        exit();
    }
    $succeeded_index_ids = array();
    if (1 == $bulk) {
        $item_ids = $formdata->getValueArray('post', 'item_ids', 'i', false);
        $xil_objs = getIndexItemLinks($is_moderator, $admin_gids, $menu_id, $item_ids);
        foreach ($xil_objs as $xil_obj) {
            $item_id = $xil_obj->get('item_id');
            $index_id = $xil_obj->get('index_id');
            if ('uncertify' == $op) {
                if (xoonips_reject_item($uid, $item_id, $index_id)) {
                    $succeeded_index_ids[$item_id][] = $index_id;
                }
            } elseif ('certify' == $op) {
                if (xoonips_certify_item($uid, $item_id, $index_id)) {
                    $succeeded_index_ids[$item_id][] = $index_id;
                }
            }
        }
    } else {
        if ('uncertify' == $op) {
            foreach ($index_ids as $index_id) {
                if (xoonips_reject_item($uid, $item_id, $index_id)) {
                    $succeeded_index_ids[$item_id][] = $index_id;
                }
            }
        } elseif ('certify' == $op) {
            foreach ($index_ids as $index_id) {
                if (xoonips_certify_item($uid, $item_id, $index_id)) {
                    $succeeded_index_ids[$item_id][] = $index_id;
                }
            }
        }
    }
    if (!empty($succeeded_index_ids)) {
        foreach ($succeeded_index_ids as $item_id => $index_ids) {
            if ('uncertify' == $op) {
                xoonips_notification_item_rejected($item_id, $index_ids);
                xoonips_notification_user_item_rejected($item_id, $index_ids);
            } elseif ('certify' == $op) {
                xoonips_notification_item_certified($item_id, $index_ids);
                xoonips_notification_user_item_certified($item_id, $index_ids);
            }
        }
    }
}

$xil_objs = getIndexItemLinks($is_moderator, $admin_gids, $menu_id, array());
$items = array();
$indexes = array();
require XOOPS_ROOT_PATH.'/header.php';
foreach ($xil_objs as $xil_obj) {
    $iid = $xil_obj->get('item_id');
    $xid = $xil_obj->get('index_id');
    if (!isset($items[$iid])) {
        $itemlib_handler = &XooNIpsItemLibraryHandler::getInstance();
        $itemlib_obj = &$itemlib_handler->get($iid);
        if (!is_object($itemlib_obj)) {
            continue;
        }
        $items[$iid] = array(
          'item_id' => $iid,
          'indexes' => array(),
          'item_body' => $itemlib_obj->getItemListBlock(),
        );
    }
    if (!isset($indexes[$xid])) {
        $indexes[$xid] = array(
          'id' => $xid,
          'path' => xnpGetIndexPathString($xnpsid, $xid),
       );
    }
    $items[$iid]['indexes'][] = $indexes[$xid];
}

$xoopsOption['template_main'] = 'xoonips_certify.html';

if (isset($menu_id)) {
    $xoopsTpl->assign('menu_id', $menu_id);
}
$xoopsTpl->assign('pankuzu', $pankuzu);
$xoopsTpl->assign('certify_button_label', _MD_XOONIPS_ITEM_CERTIFY_BUTTON_LABEL);
$xoopsTpl->assign('uncertify_button_label', _MD_XOONIPS_ITEM_UNCERTIFY_BUTTON_LABEL);
$xoopsTpl->assign('item_label', _MD_XOONIPS_ITEM_ITEM_LABEL);
$xoopsTpl->assign('index_label', _MD_XOONIPS_ITEM_INDEX_LABEL);
if (count($items) > 0) {
    $xoopsTpl->assign('items', $items);
}
$xoopsTpl->assign('xoonips_editprofile_url', XOOPS_URL.'/modules/xoonips/edituser.php?uid='.$uid);
// token ticket
$token_ticket = $xoopsGTicket->getTicketHtml(__LINE__, 1800, 'xoonips_certify_item');
$xoopsTpl->assign('token_ticket', $token_ticket);

require XOOPS_ROOT_PATH.'/footer.php';

function getIndexItemLinks($is_moderator, $admin_gids, $menu_id, $item_ids = array())
{
    $xil_handler = &xoonips_getormhandler('xoonips', 'index_item_link');
    $join = new XooNIpsJoinCriteria('xoonips_index', 'index_id', 'index_id', 'INNER', 'x');
    $criteria = new CriteriaCompo(new Criteria('certify_state', CERTIFY_REQUIRED));
    switch ($menu_id) {
    case 1:
        // public
        $criteria->add(new Criteria('open_level', OL_PUBLIC, '=', 'x'));
        break;
    case 2:
        // group only
        $criteria->add(new Criteria('gid', '('.implode(',', $admin_gids).')', 'IN', 'x'));
        $criteria->add(new Criteria('open_level', OL_GROUP_ONLY, '=', 'x'));
        break;
    default:
        // public / group only
        if ($is_moderator) {
            $criteria_public = new CriteriaCompo(new Criteria('open_level', OL_PUBLIC, '=', 'x'));
        } else {
            $criteria_public = false;
        }
        if ($is_groupadmin) {
            $criteria_group = new CriteriaCompo(new Criteria('gid', '('.implode(',', $admin_gids).')', 'IN', 'x'));
            $criteria_group->add(new Criteria('open_level', OL_GROUP_ONLY, '=', 'x'));
        } else {
            $criteria_group = false;
        }
        if ($criteria_public) {
            if ($criteria_group) {
                $criteria_public->add($criteria_group, 'OR');
            }
            $criteria->add($criteria_public);
        } else {
            $criteria->add($criteria_group);
        }
    }
    if (!empty($item_ids)) {
        $criteria->add(new Criteria('item_id', '('.implode(',', $item_ids).')', 'IN'));
    }
    $xil_objs = $xil_handler->getObjects($criteria, false, '', false, $join);

    return $xil_objs;
}
