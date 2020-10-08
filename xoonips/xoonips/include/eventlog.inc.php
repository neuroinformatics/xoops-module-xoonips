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

require_once dirname(__DIR__).'/class/base/gtickets.php';

/**
 * convert text to csv string.
 *
 * @param string $text input string
 *
 * @return string converted csv string
 */
function _xoonips_eventlog_text2csv($text)
{
    //
    // CSV Specification by wikipedia
    //  see : http://en.wikipedia.org/wiki/Comma-separated_values
    //
    // - Each record is one line terminated by a line feed (ASCII/LF=0x0A) or a
    //   carriage return and line feed pair (ASCII/CRLF=0x0D 0x0A), howeber,
    //   line-breaks can be embedded.
    // - Fields are separated by commas.
    // - Leading and trailing spaces or tabs, adjacent to commas, are trimmed.
    // - Fields with embedded commas must be delimited with double-quote
    //   characters.
    // - Fields with embedded double-quote characters must be delimited with
    //   double-quote characters, and the embedded double-quote characters must
    //   be represented by a pair of double-quote characters.
    // - Fields with embedded line breaks must be delimited by double-quote
    //   characters.
    // - Fields with leading or trailing spaces must be delimited by
    //   double-quote characters. This requirement is contentious and in fact
    //   is specifically prohibited by RFC 4180, which states, "Spaces are
    //   considered part of a field and should not be ignored."
    // - Fields may always be delimited by double-quote characters, whether
    //   necessary or not.
    // - The first record in a csv file may contain column names in each of the
    //   fields.
    $escape_dquote = false;
    $use_dquote = false;
    if (strstr($text, '"')) {
        // contain double-quote character
        $escape_dquote = true;
        $use_dquote = true;
    } elseif (strstr($text, ',')) {
        // contain commas
        $use_dquote = true;
    } elseif (strstr($text, "\n") || strstr($text, "\r")) {
        // contain line-breaks
        $use_dquote = true;
    } elseif (preg_match('/^\s+.*$/', $text)) {
        // contain reading spaces and tabs
        $use_dquote = true;
    } elseif (preg_match('/^.*\s+$/', $text)) {
        // contain trailing spaces and tabs
        $use_dquote = true;
    }
    if ($escape_dquote) {
        $text = str_replace('"', '""', $text);
    }
    if ($use_dquote) {
        $text = '"'.$text.'"';
    }

    return $text;
}

/**
 * convert text array to csv line.
 *
 * @param object $download XooNIpsDownload instance
 * @param array  $oneline  input text array
 *
 * @return string one line of csv text
 */
function _xoonips_eventlog_array2csv(&$download, $oneline)
{
    $text = '';
    $fieldsize = count($oneline);
    for ($i = 0; $i < $fieldsize; ++$i) {
        if (0 != $i) {
            $text .= ',';
        }
        $field = $download->convert_to_client($oneline[$i], 'h');
        $text .= _xoonips_eventlog_text2csv($field);
    }

    return $text."\r\n";
}

/**
 * convert date (year, month, day) range to unix timestamp.
 *
 * @param bool $is_from true if input date is beginning date
 * @param int  $year    year
 * @param int  $month   month
 * @param int  $day     day
 *
 * @return int converted timestamp
 */
function _xoonips_eventlog_date2time($is_from, $year, $month, $day = 0)
{
    $year = intval($year);
    $month = intval($month);
    $day = intval($day);
    if ($is_from) {
        if (0 == $day) {
            $day = 1;
        }
        $hour = 0;
        $min = 0;
        $sec = 0;
    } else {
        if (0 == $day) {
            $no31days = array(2, 4, 6, 9, 11);
            if (in_array($month, $no31days)) {
                if (2 == $month) {
                    if (0 == $year % 4 && (0 == $year % 400 || 0 != $year % 100)) {
                        $day = 29; // leap year
                    } else {
                        $day = 28;
                    }
                } else {
                    $day = 30;
                }
            } else {
                $day = 31;
            }
        }
        $hour = 23;
        $min = 59;
        $sec = 59;
    }

    return mktime($hour, $min, $sec, $month, $day, $year);
}

/**
 * resolve host name from ip address.
 *
 * @param string $remote_host ip address
 *
 * @return string resolved host name
 */
function _xoonips_eventlog_resolve_host($remote_host)
{
    if (preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/', $remote_host)) {
        if (function_exists('gethostbyaddr')) {
            $remote_host = gethostbyaddr($remote_host);
        }
    }

    return $remote_host;
}

/**
 * convert domain from host name or ip address.
 *
 * @param string $remote_host host name or ip address
 *
 * @return string last domain, 'unkown' returned if could not resolve host name
 */
function _xoonips_eventlog_get_domain($remote_host)
{
    $remote_host = _xoonips_eventlog_resolve_host($remote_host);
    if (preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/', $remote_host)) {
        $domain = 'unknown';
    } else {
        $tmp = explode('.', $remote_host);
        $domain = end($tmp);
    }

    return $domain;
}

/**
 * get event logs from database.
 *
 * @param int    $start_time    start time of time range
 * @param int    $end_time      end time of time range
 * @param int    $event_type_id event type id
 * @param string $query_type    type of query belows:
 *                              'all'            : get all logs
 *                              'day'            : count events on every days
 *                              'total'          : some as 'day'
 *                              'month'          : count events on every month
 *                              'domain'         : count events on every domain
 *                              'item'           : count events on every month group by item
 *                              'file'           : count events on every month group by file
 *                              'user'           : count events on every month group by user
 *                              'user_item'      : count events on every month group by user and item
 *                              'item_sort'      : count events group by item
 *                              'file_sort'      : count events group by file
 *                              'user_item_sort' : count events group by user and item
 */
function &_xoonips_eventlog_get($start_time, $end_time, $event_type_id, $query_type)
{
    $tables['event_log'] = $GLOBALS['xoopsDB']->prefix('xoonips_event_log');
    $eventlog_handler = &xoonips_getormhandler('xoonips', 'event_log');
    $criteria = new CriteriaCompo(new Criteria($tables['event_log'].'.timestamp', $start_time, '>='));
    $criteria->add(new Criteria($tables['event_log'].'.timestamp', $end_time, '<='));
    if (0 != $event_type_id) {
        $criteria->add(new Criteria('event_type_id', $event_type_id));
    }
    switch ($query_type) {
    case 'all':
        $criteria->setSort('timestamp');
        $objs = &$eventlog_handler->getObjects($criteria);
        break;
    case 'day':
    case 'total':
        $criteria->setGroupby('event_date');
        $criteria->setSort('event_date');
        $objs = &$eventlog_handler->getObjects($criteria, false, 'FROM_UNIXTIME(timestamp, \'%Y-%m-%d\') AS event_date, COUNT(event_type_id) AS cnt');
        break;
    case 'month':
        $criteria->setGroupby('event_month');
        $criteria->setSort('event_month');
        $objs = &$eventlog_handler->getObjects($criteria, false, 'FROM_UNIXTIME(timestamp, \'%Y-%m\') AS event_month, COUNT(event_type_id) AS cnt');
        break;
    case 'domain':
        $criteria->setGroupby('remote_host');
        $criteria->setSort('remote_host');
        $objs = &$eventlog_handler->getObjects($criteria, false, 'remote_host, COUNT(event_type_id) AS cnt');
        break;
    case 'item':
        $criteria->setGroupby('event_month, item_id');
        $criteria->setSort('cnt');
        $criteria->setOrder('DESC');
        $join_criteria = new XooNIpsJoinCriteria('xoonips_item_basic', 'item_id', 'item_id', 'INNER', 'basic');
        $join_criteria->cascade(new XooNIpsJoinCriteria('xoonips_item_type', 'item_type_id', 'item_type_id', 'INNER', 'itemtype'), 'basic', true);
        $join_criteria->cascade(new XooNIpsJoinCriteria('users', 'uid', 'uid', 'INNER', 'users'), 'basic', true);
        $objs = &$eventlog_handler->getObjects($criteria, false, 'FROM_UNIXTIME(timestamp, \'%Y-%m\') AS event_month, basic.item_id, users.uname AS uname, users.name AS name, itemtype.display_name as itemtype_name, COUNT(event_type_id) AS cnt', false, $join_criteria);
        break;
    case 'file':
        $criteria->setGroupby('event_month, file_id');
        $criteria->setSort('cnt');
        $criteria->setOrder('DESC');
        $join_criteria = new XooNIpsJoinCriteria('xoonips_item_basic', 'item_id', 'item_id', 'INNER', 'basic');
        $join_criteria->cascade(new XooNIpsJoinCriteria('xoonips_item_type', 'item_type_id', 'item_type_id', 'INNER', 'itemtype'), 'basic', true);
        $join_criteria->cascade(new XooNIpsJoinCriteria('users', 'uid', 'uid', 'INNER', 'users'), 'basic', true);
        $join_criteria->cascade(new XooNIpsJoinCriteria('xoonips_file', 'file_id', 'file_id', 'INNER', 'file'), 'xoonips_event_log');
        $objs = &$eventlog_handler->getObjects($criteria, false, 'FROM_UNIXTIME('.$tables['event_log'].'.timestamp, \'%Y-%m\') AS event_month, basic.item_id, file.file_id, file.original_file_name AS fname, users.uname AS uname, users.name AS name, itemtype.display_name as itemtype_name, COUNT(event_type_id) AS cnt', false, $join_criteria);
        break;
    case 'user':
        $criteria->setGroupby('event_month, myuname');
        $criteria->setSort('cnt');
        $criteria->setOrder('DESC');
        $join_criteria = new XooNIpsJoinCriteria('xoonips_item_basic', 'item_id', 'item_id', 'INNER', 'basic');
        $join_criteria->cascade(new XooNIpsJoinCriteria('users', 'exec_uid', 'uid', 'INNER', 'users'), 'xoonips_event_log');
        $objs = &$eventlog_handler->getObjects($criteria, false, 'CASE WHEN ISNULL(uname) THEN CONCAT(\'guest[\',remote_host,\']\') ELSE uname END AS myuname, FROM_UNIXTIME(timestamp, \'%Y-%m\') AS event_month, users.uname AS uname, COUNT(event_type_id) AS cnt', false, $join_criteria);
        break;
    case 'user_item':
        $criteria->setGroupby('event_month, myuname, item_id');
        $criteria->setSort('cnt');
        $criteria->setOrder('DESC');
        $join_criteria = new XooNIpsJoinCriteria('xoonips_item_basic', 'item_id', 'item_id', 'INNER', 'basic');
        $join_criteria->cascade(new XooNIpsJoinCriteria('xoonips_item_type', 'item_type_id', 'item_type_id', 'INNER', 'itemtype'), 'basic', true);
        $join_criteria->cascade(new XooNIpsJoinCriteria('users', 'exec_uid', 'uid', 'INNER', 'users'), 'xoonips_event_log');
        $objs = &$eventlog_handler->getObjects($criteria, false, 'CASE WHEN ISNULL(uname) THEN CONCAT(\'guest[\',remote_host,\']\') ELSE uname END AS myuname, FROM_UNIXTIME(timestamp, \'%Y-%m\') AS event_month, basic.item_id as item_id, users.uname AS uname, itemtype.display_name as itemtype_name, COUNT(event_type_id) AS cnt', false, $join_criteria);
        break;
    case 'item_sort':
        $criteria->setGroupby('item_id');
        $criteria->setSort('cnt');
        $criteria->setOrder('DESC');
        $objs = &$eventlog_handler->getObjects($criteria, false, 'item_id, COUNT(event_type_id) AS cnt');
        break;
    case 'file_sort':
        $criteria->setGroupby('file_id');
        $criteria->setSort('cnt');
        $criteria->setOrder('DESC');
        $objs = &$eventlog_handler->getObjects($criteria, false, 'file_id, COUNT(file_id) AS cnt');
        break;
    case 'user_item_sort':
        $criteria->setGroupby('myuname, item_id');
        $criteria->setSort('cnt');
        $criteria->setOrder('DESC');
        $join_criteria = new XooNIpsJoinCriteria('xoonips_item_basic', 'item_id', 'item_id', 'INNER', 'basic');
        $join_criteria->cascade(new XooNIpsJoinCriteria('users', 'exec_uid', 'uid', 'INNER', 'users'), 'xoonips_event_log');
        $objs = &$eventlog_handler->getObjects($criteria, false, 'CASE WHEN ISNULL(uname) THEN CONCAT(\'guest[\',remote_host,\']\') ELSE uname END AS myuname, basic.item_id as item_id, users.uname AS uname, COUNT(event_type_id) AS cnt', false, $join_criteria);
        break;
    }

    return $objs;
}

/**
 * get user's names from database.
 *
 * @return string[] user's names(uname)
 */
function _xoonips_eventlog_get_usermap()
{
    $uhandler = &xoonips_getormhandler('xoonips', 'xoops_users');
    $uobjs = &$uhandler->getObjects(null, 'uid, uname');
    $users = array();
    foreach ($uobjs as $uobj) {
        $uid = $uobj->getVar('uid', 'n');
        $uname = $uobj->getVar('uname', 'n');
        $users[$uid] = $uname;
    }
    // append guest
    $users[UID_GUEST] = 'guest';

    return $users;
}

/**
 * get item title from database.
 *
 * @param int $item_id item id
 *
 * @return string title
 */
function xoonips_eventlog_get_item_title($item_id)
{
    $thandler = &xoonips_getormhandler('xoonips', 'title');
    $criteria = new Criteria('item_id', $item_id);
    $criteria->setSort('title_id');
    $objs = &$thandler->getObjects($criteria);
    foreach ($objs as $obj) {
        $title = $obj->getVar('title', 'n');
        $titles = isset($titles) ? $titles."\r\n".$title : $title;
    }

    return $titles;
}

/**
 * count users on the database.
 *
 * @return int number of users
 */
function xoonips_eventlog_count_users()
{
    $xuhandler = &xoonips_getormhandler('xoonips', 'users');
    $join_criteria = new XooNIpsJoinCriteria('users', 'uid', 'uid', 'INNER', 'users');

    return $xuhandler->getCount(null, $join_criteria);
}

/**
 * get user objects from database.
 *
 * @param int $start number of skip results
 * @param int $limit number of results, zero means unlimit
 *
 * @return array object instance
 */
function &xoonips_eventlog_get_users($start, $limit)
{
    $xuhandler = &xoonips_getormhandler('xoonips', 'users');
    $join_criteria = new XooNIpsJoinCriteria('users', 'uid', 'uid', 'INNER', 'users');
    $criteria = new CriteriaElement();
    $criteria->setSort('uname');
    $criteria->setStart($start);
    if ($limit > 0) {
        $criteria->setLimit($limit);
    }
    $objs = &$xuhandler->getObjects($criteria, false, '', false, $join_criteria);

    return $objs;
}

/**
 * count items on the database.
 *
 * @return int number of items
 */
function xoonips_eventlog_count_items()
{
    $ib_handler = &xoonips_getormhandler('xoonips', 'item_basic');
    $criteria = new Criteria('item_type_id', ITID_INDEX, '>');

    return $ib_handler->getCount($criteria);
}

/**
 * get item_basic objects from database.
 *
 * @param int $start number of skip results
 * @param int $limit number of results, zero means unlimit
 *
 * @return array object instance
 */
function &xoonips_eventlog_get_items($start, $limit)
{
    global $xoopsDB;
    $tables['item_basic'] = $xoopsDB->prefix('xoonips_item_basic');
    $ib_handler = &xoonips_getormhandler('xoonips', 'item_basic');
    $criteria = new Criteria($tables['item_basic'].'.item_type_id', ITID_INDEX, '>');
    $criteria->setSort($tables['item_basic'].'.item_id');
    $criteria->setStart($start);
    if ($limit > 0) {
        $criteria->setLimit($limit);
    }
    $join_criteria = new XooNIpsJoinCriteria('xoonips_item_type', 'item_type_id', 'item_type_id', 'INNER');
    $join_criteria->cascade(new XooNIpsJoinCriteria('users', 'uid', 'uid', 'INNER'), 'xoonips_item_basic');
    $objs = &$ib_handler->getObjects($criteria, false, '', false, $join_criteria);

    return $objs;
}

/**
 * get operating conditions from log type id.
 *
 * @param int $log_type_id id of log type
 *
 * @return array conditions
 */
function _xoonips_eventlog_logtype2vars($log_type_id)
{
    static $typemap = array(
    // log_type_id => array( 'filename suffix', 'event type id', 'query type', 'csv title', 'graph title' )
    // - all event logs
    0 => array('', 0, 'all', '', ''),
    // - number of top page access (day)
    1 => array('top', ETID_VIEW_TOP_PAGE, 'day', _MD_XOONIPS_EVENTLOG_ACCESS_TOP_DAYS_LABEL, _MD_XOONIPS_EVENTLOG_ACCESS_TOP_DAYS_TITLE),
    // - number of top page access (month)
    2 => array('topmon', ETID_VIEW_TOP_PAGE, 'month', _MD_XOONIPS_EVENTLOG_ACCESS_TOP_MONTHS_LABEL, _MD_XOONIPS_EVENTLOG_ACCESS_TOP_MONTHS_TITLE),
    // - number of top page access (total)
    3 => array('toptotal', ETID_VIEW_TOP_PAGE, 'total', _MD_XOONIPS_EVENTLOG_ACCESS_TOP_TOTAL_LABEL, _MD_XOONIPS_EVENTLOG_ACCESS_TOP_DAYS_TOTAL_TITLE),
    // - number of top page access (domain)
    4 => array('topdom', ETID_VIEW_TOP_PAGE, 'domain', _MD_XOONIPS_EVENTLOG_ACCESS_TOP_DOMAINS_LABEL, _MD_XOONIPS_EVENTLOG_ACCESS_TOP_DOMAINS_TITLE),
    // - number of item detail page access (day)
    5 => array('item', ETID_VIEW_ITEM, 'day', _MD_XOONIPS_EVENTLOG_ACCESS_ITEM_DAYS_LABEL, _MD_XOONIPS_EVENTLOG_ACCESS_ITEM_DAYS_TITLE),
    // - number of item detail page access (month)
    6 => array('itemmon', ETID_VIEW_ITEM, 'month', _MD_XOONIPS_EVENTLOG_ACCESS_ITEM_MONTHS_LABEL, _MD_XOONIPS_EVENTLOG_ACCESS_ITEM_MONTHS_TITLE),
    // - number of item detail page access (total)
    7 => array('itemtotal', ETID_VIEW_ITEM, 'total', _MD_XOONIPS_EVENTLOG_ACCESS_ITEM_TOTAL_LABEL, _MD_XOONIPS_EVENTLOG_ACCESS_ITEM_DAYS_TOTAL_TITLE),
    // - number of item detail page access (domain)
    8 => array('itemdom', ETID_VIEW_ITEM, 'domain', _MD_XOONIPS_EVENTLOG_ACCESS_ITEM_DOMAINS_LABEL, _MD_XOONIPS_EVENTLOG_ACCESS_ITEM_DOMAINS_TITLE),
    // - number of item detail page access (per item)
    18 => array('itemperitem', ETID_VIEW_ITEM, 'item', _MD_XOONIPS_EVENTLOG_ACCESS_ITEM_PER_ITEM_LABEL, ''),
    // - number of item detail page access (per user)
    19 => array('itemperuser', ETID_VIEW_ITEM, 'user_item', _MD_XOONIPS_EVENTLOG_ACCESS_ITEM_PER_USER_LABEL, ''),
    // - number of item attachment file download (day)
    9 => array('dl', ETID_DOWNLOAD_FILE, 'day', _MD_XOONIPS_EVENTLOG_DL_ITEM_DAYS_LABEL, _MD_XOONIPS_EVENTLOG_DL_ITEM_DAYS_TITLE),
    // - number of item attachment file download (month)
    10 => array('dlmon', ETID_DOWNLOAD_FILE, 'month', _MD_XOONIPS_EVENTLOG_DL_ITEM_MONTHS_LABEL, _MD_XOONIPS_EVENTLOG_DL_ITEM_MONTHS_TITLE),
    // - number of item attachment file download (total)
    11 => array('dltotal', ETID_DOWNLOAD_FILE, 'total', _MD_XOONIPS_EVENTLOG_DL_ITEM_TOTAL_LABEL, _MD_XOONIPS_EVENTLOG_DL_ITEM_DAYS_TOTAL_TITLE),
    // - number of item attachment file download (domain)
    12 => array('dldom', ETID_DOWNLOAD_FILE, 'domain', _MD_XOONIPS_EVENTLOG_DL_ITEM_DOMAINS_LABEL, _MD_XOONIPS_EVENTLOG_DL_ITEM_DOMAINS_TITLE),
    // - number of item attachment file download (per item)
    15 => array('dlperitem', ETID_DOWNLOAD_FILE, 'item', _MD_XOONIPS_EVENTLOG_DL_ITEM_PER_ITEM_LABEL, ''),
    // - number of item attachment file download (per file)
    16 => array('dlperfile', ETID_DOWNLOAD_FILE, 'file', _MD_XOONIPS_EVENTLOG_DL_ITEM_PER_FILE_LABEL, ''),
    // - number of item attachment file download (per user)
    17 => array('dlperuser', ETID_DOWNLOAD_FILE, 'user', _MD_XOONIPS_EVENTLOG_DL_ITEM_PER_USER_LABEL, ''),
    // - number of newly registered users and items (user)
    13 => array('newuser', ETID_CERTIFY_ACCOUNT, 'month', _MD_XOONIPS_EVENTLOG_NEW_USERS_LABEL, _MD_XOONIPS_EVENTLOG_NEW_USER_MONTHS_TITLE),
    // - number of newly registered users and items (item)
    14 => array('newitem', ETID_INSERT_ITEM, 'month', _MD_XOONIPS_EVENTLOG_NEW_ITEMS_LABEL, _MD_XOONIPS_EVENTLOG_NEW_ITEM_MONTHS_TITLE),
    );

    return $typemap[$log_type_id];
}

/**
 * create month labels from time range.
 *
 * @param int $start_time start time of time range
 * @param int $end_time   end time of time range
 *
 * @return array array of month label
 */
function _xoonips_eventlog_make_month_labels($start_time, $end_time)
{
    $start_label = date('Y-m', $start_time);
    $end_label = date('Y-m', $end_time);
    $label = $start_label;
    $month_labels = array();
    while (1) {
        $month_labels[] = $label;
        if ($label == $end_label) {
            break;
        }
        list($year, $month) = array_map('intval', explode('-', $label));
        if (12 == $month) {
            ++$year;
            $month = 1;
        } else {
            ++$month;
        }
        $label = sprintf('%04d-%02d', $year, $month);
    }

    return $month_labels;
}

/**
 * get header line of csv.
 *
 * @param int    $start_time  start time of time range
 * @param int    $end_time    end time of time range
 * @param int    $log_type_id id of log type
 * @param string $query_type  type of query
 *
 * @return string header line of csv
 */
function _xoonips_eventlog_get_csvheader($start_time, $end_time, $log_type_id, $query_type)
{
    $query_map = array(
        'all' => _MD_XOONIPS_EVENTLOG_EVENT_COLUMNS,
        'day' => _MD_XOONIPS_EVENTLOG_EVENT_COLUMNS_DATE,
        'month' => _MD_XOONIPS_EVENTLOG_EVENT_COLUMNS_DATE,
        'total' => _MD_XOONIPS_EVENTLOG_EVENT_COLUMNS_DATE,
        'domain' => _MD_XOONIPS_EVENTLOG_EVENT_COLUMNS_DOMAINS,
    );
    $logtype_map = array(
        15 => _MD_XOONIPS_EVENTLOG_EVENT_COLUMNS_DL_PER_ITEM,
        16 => _MD_XOONIPS_EVENTLOG_EVENT_COLUMNS_DL_PER_FILE,
        17 => _MD_XOONIPS_EVENTLOG_EVENT_COLUMNS_DL_PER_USER,
        18 => _MD_XOONIPS_EVENTLOG_EVENT_COLUMNS_ACCESS_ITEM,
        19 => _MD_XOONIPS_EVENTLOG_EVENT_COLUMNS_ACCESS_USER,
    );
    if (isset($query_map[$query_type])) {
        $header = $query_map[$query_type];
    } elseif (isset($logtype_map[$log_type_id])) {
        $month_labels = _xoonips_eventlog_make_month_labels($start_time, $end_time);
        $header = $logtype_map[$log_type_id];
        $header .= implode(',', $month_labels);
        $header .= ','._MD_XOONIPS_EVENTLOG_EVENT_COLUMNS_TOTAL;
    } else {
        $header = '';
    }

    return $header;
}

/**
 * get date from page request.
 *
 * @param bool $is_post true if request method is 'POST'
 * @param bool $has_day true if request has 'day' information
 *
 * @return array date variables
 */
function xoonips_eventlog_get_request_date($is_post, $has_day)
{
    $prefix = array('StartDate', 'EndDate');
    $postfix = array('Year', 'Month', 'Day');
    $result = array();
    $formdata = &xoonips_getutility('formdata');
    foreach ($prefix as $pre) {
        if ($is_post) {
            foreach ($postfix as $post) {
                $var = $pre.$post;
                $data[$post] = $formdata->getValue('post', $var, 'i', false, 0);
            }
            if (!$has_day) {
                $data['Day'] = 0;
            }
            $parsed_time = _xoonips_eventlog_date2time(('StartDate' == $pre), $data['Year'], $data['Month'], $data['Day']);
        } else {
            $parsed_time = $formdata->getValue('get', $pre, 'i', false, 0);
        }
        $parsed_label = ($has_day) ? date('Ymd', $parsed_time) : date('Ym', $parsed_time);
        $result[$pre] = array(
            'label' => $parsed_label,
            'value' => $parsed_time,
        );
    }

    return $result;
}

/**
 * download event log.
 *
 * @param bool $is_post     true if request method is 'POST'
 * @param int  $log_type_id id of log type
 */
function xoonips_eventlog_download($is_post, $log_type_id)
{
    $ticket_area = 'xoonips_eventlog_download';
    $mimetype = 'text/csv';
    $url = XOOPS_URL.'/modules/xoonips/event_log.php';

    // get operating conditions
    list($suffix, $event_type_id, $query_type, $csv_title, $graph_title) = _xoonips_eventlog_logtype2vars($log_type_id);

    // get request
    $has_days = (0 == $log_type_id);
    $time_range = xoonips_eventlog_get_request_date($is_post, $has_days);
    $start_time = $time_range['StartDate']['value'];
    $end_time = $time_range['EndDate']['value'];
    $start_label = $time_range['StartDate']['label'];
    $end_label = $time_range['EndDate']['label'];
    $filename = $start_label.'-'.$end_label.(('' == $suffix) ? '' : '-'.$suffix).'.csv';

    // check time range
    if ($start_time > $end_time) {
        redirect_header($url, 3, 'invalid time range');
        exit();
    }

    // check token ticket for get request
    if (!$is_post) {
        if (!$GLOBALS['xoopsGTicket']->check($is_post, $ticket_area, false)) {
            redirect_header($url, 3, $GLOBALS['xoopsGTicket']->getErrors());
            exit();
        }
    }

    // check download condition
    $download = &xoonips_getutility('download');
    if (!$download->check_pathinfo($filename)) {
        // require PATH_INFO on KHTML browser (Safari)
        $token_ticket = $GLOBALS['xoopsGTicket']->getTicketParamString(__LINE__, true, 1800, $ticket_area);
        $reload_url = $download->append_pathinfo($url, $filename);
        $reload_url .= '?mode=download&log_type_id='.$log_type_id.'&StartDate='.$start_time.'&EndDate='.$end_time.'&'.$token_ticket;
        header('Location: '.$reload_url);
        exit();
    }

    // begin to create and output csv data
    $csv_header = _xoonips_eventlog_get_csvheader($start_time, $end_time, $log_type_id, $query_type);
    $objs = &_xoonips_eventlog_get($start_time, $end_time, $event_type_id, $query_type);

    // output HTTP headers
    $download->output_header($filename, $mimetype, 0);

    // output csv title and header
    if ('' != $csv_title) {
        echo $csv_title."\r\n";
    }
    echo $csv_header."\r\n";

    // output body
    switch ($query_type) {
    case 'all':
        $uid2uname = _xoonips_eventlog_get_usermap();
        $etype2str = explode(',', _MD_XOONIPS_EVENTLOG_EVENT_TYPES);
        foreach ($objs as $obj) {
            $line = array();
            $line[] = $etype2str[$obj->getVar('event_type_id', 'n')];
            $line[] = date(DATETIME_FORMAT, $obj->getVar('timestamp', 'n'));
            $exec_uid = $obj->getVar('exec_uid', 'n');
            $line[] = $exec_uid;
            $line[] = $obj->getVar('remote_host', 'n');
            $line[] = $obj->getVar('index_id', 'n');
            $line[] = $obj->getVar('item_id', 'n');
            $line[] = $obj->getVar('file_id', 'n');
            $line[] = $obj->getVar('uid', 'n');
            $line[] = $obj->getVar('gid', 'n');
            $line[] = $obj->getVar('search_keyword', 'n');
            $line[] = isset($uid2uname[$exec_uid]) ? $uid2uname[$exec_uid] : '';
            $line[] = $obj->getVar('additional_info', 'n');
            echo _xoonips_eventlog_array2csv($download, $line);
        }
        break;
    case 'day':
    case 'total':
        $logs = array();
        foreach ($objs as $obj) {
            $event_date = $obj->getExtraVar('event_date');
            $event_count = $obj->getExtraVar('cnt');
            $logs[$event_date] = $event_count;
        }
        $days = intval(($end_time - $start_time) / 86400);
        $total = 0;
        for ($i = 0; $i < $days; ++$i) {
            $label = date('Y-m-d', $start_time + 86400 * $i);
            $count = (array_key_exists($label, $logs)) ? $logs[$label] : 0;
            $total += $count;
            $num = ('total' == $query_type) ? $total : $count;
            echo _xoonips_eventlog_array2csv($download, array($label, $num));
        }
        break;
    case 'month':
        $logs = array();
        foreach ($objs as $obj) {
            $event_month = $obj->getExtraVar('event_month');
            $event_count = $obj->getExtraVar('cnt');
            $logs[$event_month] = $event_count;
        }
        $month_labels = _xoonips_eventlog_make_month_labels($start_time, $end_time);
        foreach ($month_labels as $label) {
            $count = (array_key_exists($label, $logs)) ? $logs[$label] : 0;
            echo _xoonips_eventlog_array2csv($download, array($label, $count));
        }
        break;
    case 'domain':
        $logs = array();
        foreach ($objs as $obj) {
            $remote_host = $obj->getVar('remote_host', 'n');
            $event_count = $obj->getExtraVar('cnt');
            $event_domain = _xoonips_eventlog_get_domain($remote_host);
            if (isset($logs[$event_domain])) {
                $logs[$event_domain] += $event_count;
            } else {
                $logs[$event_domain] = $event_count;
            }
        }
        if (count($logs) > 0) {
            arsort($logs, SORT_NUMERIC);
        }
        foreach ($logs as $domain => $cnt) {
            echo _xoonips_eventlog_array2csv($download, array($domain, $cnt));
        }
        break;
    case 'item':
        $logs = array();
        $names = array();
        foreach ($objs as $obj) {
            $item_id = $obj->getVar('item_id', 'n');
            $event_month = $obj->getExtraVar('event_month');
            $uname = $obj->getExtraVar('uname');
            $name = $obj->getExtraVar('name');
            $itemtype_name = $obj->getExtraVar('itemtype_name');
            $cnt = $obj->getExtraVar('cnt');
            $logs[$item_id][$event_month] = $cnt;
            if (!isset($names[$item_id])) {
                $names[$item_id]['name'] = ('' != $name) ? $name : $uname;
                $names[$item_id]['item_type'] = $itemtype_name;
                $names[$item_id]['title'] = xoonips_eventlog_get_item_title($item_id);
            }
        }
        $objs = &_xoonips_eventlog_get($start_time, $end_time, $event_type_id, 'item_sort');
        $month_labels = _xoonips_eventlog_make_month_labels($start_time, $end_time);
        foreach ($objs as $obj) {
            $line = array();
            $item_id = $obj->getVar('item_id', 'n');
            $line[] = $item_id;
            $line[] = $names[$item_id]['title'];
            $line[] = $names[$item_id]['name'];
            $line[] = $names[$item_id]['item_type'];
            foreach ($month_labels as $label) {
                $line[] = isset($logs[$item_id][$label]) ? $logs[$item_id][$label] : '';
            }
            $line[] = $obj->getExtraVar('cnt');
            echo _xoonips_eventlog_array2csv($download, $line);
        }
        break;
    case 'file':
        $month_labels = _xoonips_eventlog_make_month_labels($start_time, $end_time);
        $file_info = array();
        foreach ($objs as $obj) {
            $file_id = $obj->getVar('file_id', 'n');
            $item_id = $obj->getVar('item_id', 'n');
            $event_month = $obj->getExtraVar('event_month');
            $uname = $obj->getExtraVar('uname');
            $name = $obj->getExtraVar('name');
            $cnt = $obj->getExtraVar('cnt');
            $file_months[$file_id][$event_month] = $cnt;
            if (!isset($file_info[$file_id])) {
                $file_info[$file_id]['fname'] = $obj->getExtraVar('fname');
                $file_info[$file_id]['title'] = xoonips_eventlog_get_item_title($item_id);
                $file_info[$file_id]['uname'] = ('' == $name) ? $uname : $uname;
                $file_info[$file_id]['itemtype'] = $obj->getExtraVar('itemtype_name');
            }
        }
        // sort seed
        $file_total = array();
        $objs = &_xoonips_eventlog_get($start_time, $end_time, $event_type_id, 'file_sort');
        foreach ($objs as $obj) {
            $file_id = $obj->getVar('file_id', '');
            $cnt = $obj->getExtraVar('cnt');
            $file_total[$file_id] = $cnt;
        }
        // output logs
        foreach ($file_total as $file_id => $total) {
            $line = array();
            $line[] = $file_id;
            $line[] = $file_info[$file_id]['fname'];
            $line[] = $file_info[$file_id]['title'];
            $line[] = $file_info[$file_id]['uname'];
            $line[] = $file_info[$file_id]['itemtype'];
            foreach ($month_labels as $label) {
                $line[] = isset($file_months[$file_id][$label]) ? $file_months[$file_id][$label] : '';
            }
            $line[] = $total;
            echo _xoonips_eventlog_array2csv($download, $line);
        }
        break;
    case 'user':
        $month_labels = _xoonips_eventlog_make_month_labels($start_time, $end_time);
        $uname_total = array();
        foreach ($objs as $obj) {
            $myuname = $obj->getExtraVar('myuname');
            $event_month = $obj->getExtraVar('event_month');
            $cnt = $obj->getExtraVar('cnt');
            $uname_total[$myuname][$event_month] = $cnt;
        }
        foreach ($uname_total as $myuname => $months) {
            $line = array();
            $line[] = $myuname;
            $total = 0;
            foreach ($month_labels as $label) {
                if (isset($months[$label])) {
                    $line[] = $months[$label];
                    $total += $months[$label];
                } else {
                    $line[] = '';
                }
            }
            $line[] = $total;
            echo _xoonips_eventlog_array2csv($download, $line);
        }
        break;
    case 'user_item':
        $logs = array();
        $names = array();
        $month_labels = _xoonips_eventlog_make_month_labels($start_time, $end_time);
        foreach ($objs as $obj) {
            $item_id = $obj->getVar('item_id', 'n');
            $event_month = $obj->getExtraVar('event_month');
            $myuname = $obj->getExtraVar('myuname');
            $itemtype_name = $obj->getExtraVar('itemtype_name');
            $cnt = $obj->getExtraVar('cnt');
            $logs[$myuname][$item_id][$event_month] = $cnt;
            if (!isset($names[$item_id])) {
                $names[$item_id]['item_type'] = $itemtype_name;
                $names[$item_id]['title'] = xoonips_eventlog_get_item_title($item_id);
            }
        }
        // sort seed
        $uname_total = array();
        $objs = &_xoonips_eventlog_get($start_time, $end_time, $event_type_id, 'user');
        foreach ($objs as $obj) {
            $myuname = $obj->getExtraVar('myuname');
            $event_month = $obj->getExtraVar('event_month');
            $cnt = $obj->getExtraVar('cnt');
            $uname_total[$myuname][$event_month] = $cnt;
        }
        $myuname_sort = array();
        $objs = &_xoonips_eventlog_get($start_time, $end_time, $event_type_id, 'user_item_sort');
        foreach ($objs as $obj) {
            $myuname = $obj->getExtraVar('myuname');
            $cnt = $obj->getExtraVar('cnt');
            $item_id = $obj->getVar('item_id', 'n');
            $myuname_sort[$myuname][$item_id] = $cnt;
        }
        foreach ($uname_total as $myuname => $months) {
            $line = array();
            $line[] = $myuname;
            $line[] = ''; // title
            $line[] = ''; // item type
            $total = 0;
            foreach ($month_labels as $label) {
                if (isset($months[$label])) {
                    $line[] = $months[$label];
                    $total += $months[$label];
                } else {
                    $line[] = '';
                }
            }
            $line[] = $total;
            echo _xoonips_eventlog_array2csv($download, $line);
            foreach ($myuname_sort[$myuname] as $item_id => $total) {
                $item_months = $logs[$myuname][$item_id];
                $line = array();
                $line[] = ''; // uname
                $line[] = $names[$item_id]['title'];
                $line[] = $names[$item_id]['item_type'];
                foreach ($month_labels as $label) {
                    $line[] = isset($item_months[$label]) ? $item_months[$label] : '';
                }
                $line[] = $total;
                echo _xoonips_eventlog_array2csv($download, $line);
            }
        }
        break;
    }
    exit();
}

/**
 * draw graph (private).
 *
 * @param array  &$xdata X data
 * @param array  &$ydata Y data
 * @param string $title  graph title
 * @param string $xlabel X label
 * @param string $mode   query type
 */
function _xoonips_eventlog_graph_draw(&$xdata, &$ydata, $title, $xlabel, $mode)
{
    // load graph library
    require_once dirname(__DIR__).'/class/base/graph.class.php';
    $unicode = &xoonips_getutility('unicode');

    // count number of data
    $num = count($ydata);

    // create graph object
    $xgraph = new XooNIpsGraph(500, 400);

    // initilize parameters
    $xgraph->setAxisGrid('x', 'none');
    $xgraph->setAxisGridLines('y', 6);
    $xgraph->frame->setType('axis');
    $xgraph->setTitle($unicode->encode_utf8($title));
    $xgraph->axis['bottom']->setLabel($unicode->encode_utf8($xlabel));

    switch ($mode) {
    case 'month':
        // hide x ticks
        if ($num > 24) {
            $xgraph->setAxisTicksColor('x', 'none');
        }
        // rotate x tick labels
        if ($num > 6) {
            $xgraph->setAxisAngle('x', 60);
        }
        // set x tick interval
        if ($num < 24) {
            $xinterval = 1;
        } elseif ($num < 60) {
            $xinterval = 4;
        } else {
            $xinterval = 12;
        }
        $xgraph->axis['bottom']->setTickInterval($xinterval);
        // create y data
        $xgraph_data = new XooNIpsGraphDataBar($ydata);
        $xgraph_data->setBarSize(0.5);
        $xgraph->setXAxisOffset(0.5);
        $xgraph_data->setColor('red');
        break;
    case 'day':
    case 'total':
        // hide x ticks
        if ($num > 70) {
            $xgraph->setAxisTicksColor('x', 'none');
        }
        // rotate x tick labels
        if ($num > 130) {
            $xgraph->setAxisAngle('x', 60);
        }
        // set x tick interval
        if ($num < 32) {
            $xinterval = 3;
        } elseif ($num < 70) {
            $xinterval = 5;
        } else {
            $xinterval = 1;
        }
        $xgraph->axis['bottom']->setTickInterval($xinterval);
        // create y data
        $xgraph_data = new XooNIpsGraphDataLine($ydata);
        $xgraph_data->setColor('blue');
        $xgraph_data->setShadowColor('none');
        break;
    case 'domain':
        // create y data
        $xgraph_data = new XooNIpsGraphDataBar($ydata);
        $xgraph_data->setBarSize(0.5);
        $xgraph->setXAxisOffset(0.5);
        $xgraph_data->setColor('green');
        break;
    }

    // set x tick labels
    $xgraph->setXData($xdata);

    // add y data and set prefered maximum/minimum y axis range
    $xgraph->addYData($xgraph_data);
    $xgraph->setPreferedYAxisRange();

    // draw
    $xgraph->draw();
}

/**
 * draw graph.
 *
 * @param int $log_type_id log type id
 */
function xoonips_eventlog_graph($log_type_id)
{
    if ($log_type_id < 1 || $log_type_id > 14) {
        die('invalid log type id');
    }
    $time_range = xoonips_eventlog_get_request_date(false, false);
    $start_time = $time_range['StartDate']['value'];
    $end_time = $time_range['EndDate']['value'];
    $start_label = $time_range['StartDate']['label'];
    $end_label = $time_range['EndDate']['label'];
    if ($start_time > $end_time) {
        die('invalid time range');
    }
    $st_year = date('Y', $start_time);
    $st_month = date('m', $start_time);
    $en_year = date('Y', $end_time);
    $en_month = date('m', $end_time);
    if ($st_year == $en_year && $st_month == $en_month) {
        $xlabel = sprintf('%04d.%02d', $st_year, $st_month);
    } else {
        $xlabel = sprintf('%04d.%02d-%04d.%02d', $st_year, $st_month, $en_year, $en_month);
    }
    $xlabel_sub = array(
        'day' => _MD_XOONIPS_EVENTLOG_DAYS_LABEL,
        'total' => _MD_XOONIPS_EVENTLOG_DAYS_LABEL,
        'month' => _MD_XOONIPS_EVENTLOG_MONTHS_LABEL,
        'domain' => _MD_XOONIPS_EVENTLOG_DOMAINS_LABEL,
    );
    // get operating conditions
    list($suffix, $event_type_id, $query_type, $csv_title, $title) = _xoonips_eventlog_logtype2vars($log_type_id);
    $xlabel .= '['.$xlabel_sub[$query_type].']';
    // create data
    $objs = &_xoonips_eventlog_get($start_time, $end_time, $event_type_id, $query_type);
    $ydata = array();
    $xdata = array();
    switch ($query_type) {
    case 'day':
    case 'total':
        $logs = array();
        foreach ($objs as $obj) {
            $event_date = $obj->getExtraVar('event_date');
            $event_count = $obj->getExtraVar('cnt');
            $logs[$event_date] = $event_count;
        }
        // prepare for days
        $days = intval(($end_time - $start_time) / 86400);
        // create access log of all days from $arr variable
        $amount = 0;
        for ($i = 0; $i < $days; ++$i) {
            $label = date('Y-m-d', $start_time + 86400 * $i);
            list($year, $month, $day) = explode('-', $label);
            // get access count if given.
            $count = (array_key_exists($label, $logs)) ? $logs[$label] : 0;
            $amount += intval($count);
            // create x label
            if ($days < 70) {
                $day_label = strval(intval($day));
            } elseif ($days < 190) {
                if ('01' == $day) {
                    $day_label = $year.'.'.$month;
                } else {
                    $day_label = '';
                }
            } else {
                if ('01' == $day && ((1 == intval($month) % 3))) {
                    $day_label = $year.'.'.$month;
                } else {
                    $day_label = '';
                }
            }
            // set data
            $xdata[] = $day_label;
            $ydata[] = ('total' == $query_type) ? $amount : $count;
        }
        break;
    case 'month':
        $logs = array();
        foreach ($objs as $obj) {
            $event_month = $obj->getExtraVar('event_month');
            $event_count = $obj->getExtraVar('cnt');
            $logs[$event_month] = $event_count;
        }
        // get each day, month, year
        $month_arr = array('01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12');
        // calc months
        $years = intval($en_year) - intval($st_year);
        if ($years <= 0) {
            $months = intval($en_month) - intval($st_month) + 1;
        } else {
            $months = intval($en_month) - intval($st_month) + 1 + $years * 12;
        }
        $amount = 0;
        for ($i = 0; $i < $months; ++$i) {
            $tmp = $st_month + $i - 1;
            $month = ($tmp % 12) + 1;
            $year = $st_year + intval($tmp / 12);
            // create x label
            $month_label = $year.'-'.$month_arr[$month - 1];
            // get access count if given.
            if (array_key_exists($month_label, $logs)) {
                $count = $logs[$month_label];
            } else {
                $count = 0;
            }
            $amount += $count;
            $xdata[] = $month_label;
            $ydata[] = ($is_total) ? $amount : $count;
        }
        break;
    case 'domain':
        $logs = array();
        foreach ($objs as $obj) {
            $remote_host = $obj->getVar('remote_host', 'n');
            $event_count = $obj->getExtraVar('cnt');
            $event_domain = _xoonips_eventlog_get_domain($remote_host);
            if (isset($logs[$event_domain])) {
                $logs[$event_domain] += $event_count;
            } else {
                $logs[$event_domain] = $event_count;
            }
        }
        if (count($logs) > 0) {
            arsort($logs, SORT_NUMERIC);
        }
        foreach ($logs as $domain => $value) {
            $xdata[] = $domain;
            $ydata[] = $value;
        }
    }
    _xoonips_eventlog_graph_draw($xdata, $ydata, $title, $xlabel, $query_type);
    exit();
}

/**
 * download list of registered users or items.
 *
 * @param bool $is_post     true if request method is 'POST'
 * @param int  $log_type_id id of log type
 */
function xoonips_eventlog_download_registered_list($is_post, $log_type_id)
{
    $ticket_area = 'xoonips_eventlog_download';
    $mimetype = 'text/csv';
    $url = XOOPS_URL.'/modules/xoonips/event_log.php';
    $condition_map = array(
        // $log_type_id => array( 'mode', 'csv title format', 'csv header ),
        20 => array('user', 'User List,,%s users', 'Name,Company,Division,Email'),
        21 => array('item', 'Item List,,%s items', 'Item ID,Title,Item Type,Contributor'),
    );

    // check arguments
    if (!isset($condition_map[$log_type_id])) {
        die('invalid log type id ');
    }
    list($mode, $csv_title_fmt, $csv_header) = $condition_map[$log_type_id];
    $filename = date('Y').'-'.$mode.'.csv';

    // check token ticket for get request
    if (!$is_post) {
        if (!$GLOBALS['xoopsGTicket']->check($is_post, $ticket_area, false)) {
            redirect_header($url, 3, $GLOBALS['xoopsGTicket']->getErrors());
            exit();
        }
    }

    // check download condition
    $download = &xoonips_getutility('download');
    if (!$download->check_pathinfo($filename)) {
        // require PATH_INFO on KHTML browser (Safari)
        $token_ticket = $GLOBALS['xoopsGTicket']->getTicketParamString(__LINE__, true, 1800, $ticket_area);
        $reload_url = $download->append_pathinfo($url, $filename);
        $reload_url .= '?mode=download&log_type_id='.$log_type_id.'&'.$token_ticket;
        header('Location: '.$reload_url);
        exit();
    }

    // output HTTP headers
    $download->output_header($filename, $mimetype, 0);

    switch ($mode) {
    case 'user':
        $objs = &xoonips_eventlog_get_users(0, 0);
        // output csv header
        $cnt = count($objs);
        $csv_title = sprintf($csv_title_fmt, $cnt);
        echo $csv_title."\r\n";
        echo $csv_header."\r\n";
        foreach ($objs as $obj) {
            $line = array();
            $line[] = $obj->getExtraVar('uname');
            $line[] = $obj->getVar('company_name', 'n');
            $line[] = $obj->getVar('division', 'n');
            $line[] = $obj->getExtraVar('email');
            echo _xoonips_eventlog_array2csv($download, $line);
        }
        break;
    case 'item':
        $objs = &xoonips_eventlog_get_items(0, 0);
        // output csv header
        $cnt = count($objs);
        $csv_title = sprintf($csv_title_fmt, $cnt);
        echo $csv_title."\r\n";
        echo $csv_header."\r\n";
        foreach ($objs as $obj) {
            $line = array();
            $item_id = $obj->getVar('item_id', 'n');
            $line[] = $item_id;
            $line[] = xoonips_eventlog_get_item_title($item_id);
            $line[] = $obj->getExtraVar('display_name');
            $line[] = $obj->getExtraVar('uname');
            echo _xoonips_eventlog_array2csv($download, $line);
        }
        break;
    }
    exit();
}
