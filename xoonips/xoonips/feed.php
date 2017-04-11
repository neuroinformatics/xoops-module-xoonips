<?php

// $Revision: 1.1.4.1.2.13 $
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
include 'include/common.inc.php';

$formdata = &xoonips_getutility('formdata');
$type = $formdata->getValue('get', 'type', 's', false, 'rss');
if (!in_array($type, array('rdf', 'rss', 'atom'))) {
    $type = 'rss';
}

// private functions
// TODO: implemented into class method.
function _create_item_title($item_id)
{
    global $title_handler;
    $criteria = new CriteriaCompo(new Criteria('item_id', $item_id));
    $criteria->add(new Criteria('title_id', 0));
    $criteria->setSort('title_id');
    $criteria->setOrder('ASC');
    $objs = &$title_handler->getObjects($criteria);
    $title = '';
    if (count($objs) > 0) {
        $title = $objs[0]->getVar('title', 'n');
    }

    return $title;
}

function _create_item_description($item_id)
{
    global $index_item_link_handler;
    global $index_handler;
    $criteria = new CriteriaCompo(new Criteria('certify_state', CERTIFIED));
    $criteria->add(new Criteria('item_id', $item_id));
    $link_objs = &$index_item_link_handler->getObjects($criteria);
    $indexes = array();
    foreach ($link_objs as $link_obj) {
        $xid = $link_obj->getVar('index_id', 'n');
        $titles = array();
        while ($xid != IID_ROOT) {
            $idx_obj = &$index_handler->get($xid);
            $idx = $idx_obj->getVarArray('n');
            if ($idx['open_level'] != OL_PUBLIC) {
                break;
            }
            $titles[] = _create_item_title($xid);
            $xid = $idx['parent_index_id'];
        }
        if (count($titles) > 0) {
            $titles = array_reverse($titles);
            $indexes[] = '/'.implode('/', $titles);
        }
    }
    if (count($indexes) == 0) {
        // this item has been rejected
    return false;
    }

    return _MD_XOONIPS_EVENT_ITEM_IS_SHOWN_IN.' : '.implode(', ', $indexes);
}

function _create_item_link($item_id, $doi)
{
    $url = XOOPS_URL.'/modules/xoonips/detail.php?';
    if ($doi != '') {
        $url .= XNP_CONFIG_DOI_FIELD_PARAM_NAME.'='.urlencode($doi);
    } else {
        $url .= 'item_id='.$item_id;
    }

    return $url;
}

function _set_feed_items(&$feed, $output, $limit, $start)
{
    global $event_handler;
    global $item_handler;
    global $xgroup_handler;
  // get events
  $criteria = new CriteriaCompo();
    $fields = 'event_id,event_type_id,item_id,gid,MAX(timestamp)';
    $groupby = '';
    if ($output['certify_item']) {
        $criteria->add(new Criteria('event_type_id', ETID_CERTIFY_ITEM));
        $groupby .= 'item_id';
    }
    if ($output['insert_group']) {
        if ($output['certify_item']) {
            $criteria->add(new Criteria('event_type_id', ETID_INSERT_GROUP), 'OR');
            $groupby .= ',gid';
        } else {
            $criteria->add(new Criteria('event_type_id', ETID_INSERT_GROUP));
            $groupby .= 'gid';
        }
    }
    $criteria->setSort('timestamp');
    $criteria->setOrder('DESC');
    $criteria->setLimit($limit);
    $criteria->setGroupBy($groupby);
    $criteria->setStart($start);

  // query
  $event_objs = &$event_handler->getObjects($criteria, false, $fields);

  // get records
  $num = 0;
    foreach ($event_objs as $event_obj) {
        $event_type_id = $event_obj->getVar('event_type_id', 'n');
        $timestamp = $event_obj->getExtraVar('MAX(timestamp)');
        $is_error = false;
        switch ($event_type_id) {
    case ETID_CERTIFY_ITEM:
      $item_id = $event_obj->getVar('item_id', 'n');
      $item_obj = &$item_handler->get($item_id);
      if (is_object($item_obj)) {
          $item = $item_obj->getVarArray('n');
          $category = 'Incoming Public Item';
          $title = _create_item_title($item['item_id']);
          $description = _create_item_description($item['item_id']);
          $link = _create_item_link($item['item_id'], $item['doi']);
          if ($description === false) {
              $is_error = true;
          }
      } else {
          $is_error = true;
        // item was already deleted
      }
      break;
    case ETID_INSERT_GROUP:
      $gid = $event_obj->getVar('gid', 'n');
      $xgroup_obj = &$xgroup_handler->getGroupObject($gid);
      if (is_object($xgroup_obj)) {
          $group = $xgroup_obj->getVarArray('n');
          $category = 'Incoming Group';
          $title = _MD_XOONIPS_EVENT_NEW_GROUP.' : '.$group['gname'];
          $description = ($group['gdesc'] === '') ? '(empty)' : $group['gdesc'];
          $link = XOOPS_URL.'/modules/xoonips/groups.php';
        // TODO: detail page
      } else {
          $is_error = true;
        // group was already deleted
      }
      break;
    default:
      $is_error = true;
      break;
    }
        if ($is_error == false) {
            $feed->addItem($category, $title, $description, $link, $timestamp);
            ++$num;
        }
    }

    return $num;
}

// get handlers
$xconfig_handler = &xoonips_getormhandler('xoonips', 'config');
$event_handler = &xoonips_getormhandler('xoonips', 'event_log');
$index_handler = &xoonips_getormhandler('xoonips', 'index');
$item_handler = &xoonips_getormhandler('xoonips', 'item_basic');
$index_item_link_handler = &xoonips_getormhandler('xoonips', 'index_item_link');
$title_handler = &xoonips_getormhandler('xoonips', 'title');
$xgroup_handler = &xoonips_gethandler('xoonips', 'group');

// create feed object
$feed = &xoonips_getutility('feed');
$feed_url = XOOPS_URL.'/modules/xoonips/feed.php?type='.$type;

// get settings
$output['certify_item'] = true;
$output['insert_group'] = true;
$maxretry = 10;
$retry = 0;
$start = 0;
$limit = $xconfig_handler->getValue('rss_item_max');

// get events
while ($limit > 0) {
    $num = _set_feed_items($feed, $output, $limit, $start);
    if ($retry > $maxretry || $num == $limit) {
        break;
    }
    $start += $limit;
    $limit -= $num;
    ++$retry;
}

// output feeds
$feed->render($type, $feed_url);
