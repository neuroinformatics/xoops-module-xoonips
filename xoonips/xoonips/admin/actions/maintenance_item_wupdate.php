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
defined('XOOPS_ROOT_PATH') || exit('XOOPS root path not defined');

// resources
// get _MD_XOONIPS_NOTIFICATION_*SBJ
$langman->read('main.php');

// check token ticket
require_once '../class/base/gtickets.php';
$ticket_area = 'xoonips_admin_maintenance_item_withdraw';
if (!$xoopsGTicket->check(true, $ticket_area, false)) {
    redirect_header($xoonips_admin['mypage_url'], 3, $xoopsGTicket->getErrors());
    exit();
}

// load libraries
require_once '../include/libitem.php';
require_once '../include/notification.inc.php';

// get requests
$get_keys = array(
  'tree' => array('i', true, false),
);
$get_vals = xoonips_admin_get_requests('post', $get_keys);
$tree_ids = $get_vals['tree'];

// logic
$empty_tree_ids = true;
$results = array();
$xnpsid = $_SESSION['XNPSID'];
if (count($tree_ids) > 0) {
    $textutil = &xoonips_getutility('text');

    $treelist = xnpitmgrListIndexTree(XNPITMGR_LISTMODE_PUBLICONLY);
    $treemap = array();
    foreach ($treelist as $tree) {
        $treemap[$tree['id']] = $tree['fullpath'];
    }
    $empty_tree_ids = false;
    $evenodd = 'odd';
    $index_item_link_handler = &xoonips_getormhandler('xoonips', 'index_item_link');
    $event_log_handler = &xoonips_getormhandler('xoonips', 'event_log');
    $item_lock_handler = &xoonips_getormhandler('xoonips', 'item_lock');
    $item_basic_handler = &xoonips_getormhandler('xoonips', 'item_basic');
    $item_show_handler = &xoonips_getormhandler('xoonips', 'item_show');
    $item_status_handler = &xoonips_getormhandler('xoonips', 'item_status');

    $modified_item_ids = array();

    $targetIndexIds = array();
    // execute item withdraw
    foreach ($tree_ids as $xid) {
        $succeed = 0;
        $failed = 0;
        $uncertified = 0;

        $index_item_links = &$index_item_link_handler->getObjects(new Criteria('index_id', $xid));
        if (false === $index_item_links) {
            // no item in tree
            continue;
        }
        foreach ($index_item_links as $index_item_link) {
            $item_id = $index_item_link->get('item_id');
            if ($item_lock_handler->isLocked($item_id)) {
                // item is not certified
                ++$uncertified;
            } else {
                if ($index_item_link_handler->delete($index_item_link)) {
                    // succeed
                    ++$succeed;
                    $event_log_handler->recordRejectItemEvent($item_id, $xid);
                    $modified_item_ids[$item_id] = $item_id;
                    if (!isset($targetIndexIds[$item_id])) {
                        $targetIndexIds[$item_id] = array();
                    }
                    $targetIndexIds[$item_id][] = $xid;
                } else {
                    // error occured
                    ++$failed;
                }
            }
        }

        $results[] = array(
            'id' => $xid,
            'evenodd' => $evenodd,
            'index' => $textutil->html_special_chars($treemap[$xid]),
            'succeed' => $succeed,
            'uncertified' => $uncertified,
            'failed' => $failed,
        );
        $evenodd = ('even' == $evenodd) ? 'odd' : 'even';
    }
    foreach ($targetIndexIds as $item_id => $indexIds) {
        xoonips_notification_item_rejected($item_id, $indexIds);
    }
    // update item_status and item_basic. delete item_show
    foreach ($modified_item_ids as $item_id) {
        // update last_update
        $item_basic = $item_basic_handler->get($item_id);
        $item_basic->set('last_update_date', time());
        $item_basic_handler->insert($item_basic);

        // if item becomes not public...
        $criteria = new CriteriaCompo();
        $criteria->add(new Criteria('item_id', $item_id));
        $criteria->add(new Criteria('open_level', OL_PUBLIC));
        $criteria->add(new Criteria('certify_state', CERTIFIED));
        $join = new XooNIpsJoinCriteria('xoonips_index', 'index_id', 'index_id');
        $index_item_links = &$index_item_link_handler->getObjects($criteria, false, '', false, $join);
        if (0 === count($index_item_links)) {
            // update item_status
            $item_status = $item_status_handler->get($item_id);
            if (false !== $item_status) {
                $item_status->set('is_deleted', 1);
                $item_status->set('deleted_timestamp', time());
                $item_status_handler->insert($item_status);
            }
            // delete item_show
            $item_shows = &$item_show_handler->getObjects(new Criteria('item_id', $item_id));
            foreach ($item_shows as $item_show) {
                $item_show_handler->delete($item_show);
            }
        }
    }
}

// title
$title = _AM_XOONIPS_MAINTENANCE_ITEM_WUPDATE_TITLE;
$description = _AM_XOONIPS_MAINTENANCE_ITEM_WUPDATE_DESC;

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
        'label' => _AM_XOONIPS_MAINTENANCE_ITEM_TITLE,
        'url' => $xoonips_admin['mypage_url'],
    ),
    array(
        'type' => 'link',
        'label' => _AM_XOONIPS_MAINTENANCE_ITEM_WITHDRAW_TITLE,
        'url' => $xoonips_admin['mypage_url'].'&amp;action=withdraw',
    ),
    array(
        'type' => 'label',
        'label' => $title,
        'url' => '',
    ),
);

// templates
require_once '../class/base/pattemplate.class.php';
$tmpl = new PatTemplate();
$tmpl->setBaseDir('templates');
$tmpl->readTemplatesFromFile('maintenance_item_wupdate.tmpl.html');
$tmpl->addVar('header', 'TITLE', $title);
$tmpl->setAttribute('description', 'visibility', 'visible');
$tmpl->addVar('description', 'DESCRIPTION', $description);
$tmpl->setAttribute('breadcrumbs', 'visibility', 'visible');
$tmpl->addRows('breadcrumbs_items', $breadcrumbs);
$tmpl->addVar('main', 'index', _AM_XOONIPS_MAINTENANCE_ITEM_LABEL_INDEX);
$tmpl->addVar('main', 'succeed', _AM_XOONIPS_MAINTENANCE_ITEM_LABEL_SUCCEED);
$tmpl->addVar('main', 'uncertified', _AM_XOONIPS_MAINTENANCE_ITEM_LABEL_UNCERTIFIED);
$tmpl->addVar('main', 'failed', _AM_XOONIPS_MAINTENANCE_ITEM_LABEL_FAILED);
if ($empty_tree_ids) {
    $tmpl->setAttribute('empty_results', 'visibility', 'visible');
    $tmpl->setAttribute('results', 'visibility', 'hidden');
    $tmpl->addVar('empty_results', 'empty', _AM_XOONIPS_MAINTENANCE_ITEM_INDEX_EMPTY);
} else {
    $tmpl->addRows('results', $results);
}
$tmpl->addVar('main', 'back', _AM_XOONIPS_LABEL_BACK);

xoops_cp_header();
$tmpl->displayParsedTemplate('main');
xoops_cp_footer();
