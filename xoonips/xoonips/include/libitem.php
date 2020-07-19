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

// ***************************************************************************
//   other module unit(dependency unit)
require_once XOOPS_ROOT_PATH.'/modules/xoonips/include/AL.php';
// ***************************************************************************

define('XNPITMGR_LISTMODE_ALL', 0);
define('XNPITMGR_LISTMODE_PUBLICONLY', 1);
define('XNPITMGR_LISTMODE_PRIVATEONLY', 2);

/**
 * list index tree.
 *
 *  @param mode XNPITMGR_LISTINDEXTREEMODE_PUBLICONLY<br />
 *                  return public tree only.<br />
 *                XNPITMGR_LISTINDEXTREEMODE_PRIVATEONLY<br />
 *                  eturn all index tree.
 *
 *  @return array: return index tree<br />
 *     format: array[0]['id']       = id(index id)
 *             array[0]['fullpath'] = index title(full path).<br />
 *                   .
 *             array[n]['id']
 *             array[n]['fullpath']
 *  @return false: query error
 */
function xnpitmgrListIndexTree($mode = XNPITMGR_LISTMODE_ALL, $uid = 0)
{
    global $xoopsDB;
    $index = $xoopsDB->prefix('xoonips_index');
    $item_basic = $xoopsDB->prefix('xoonips_item_basic');
    $item_title = $xoopsDB->prefix('xoonips_item_title');
    $where_level = '';
    switch ($mode) {
    case XNPITMGR_LISTMODE_ALL:
        $where_level = '1';
        break;
    case XNPITMGR_LISTMODE_PUBLICONLY:
        $where_level .= 'tx.open_level='.OL_PUBLIC;
        break;
    case XNPITMGR_LISTMODE_PRIVATEONLY:
        if (0 == $uid) {
            $where_level .= 'tx.open_level='.OL_PRIVATE.' OR ti.item_id='.IID_ROOT.' ';
        } else {
            $where_level .= '( tx.open_level='.OL_PRIVATE.' AND tx.uid='.$uid.' ) OR ti.item_id='.IID_ROOT.' ';
        }
        break;
    }

    $sql = 'SELECT tx.index_id, tx.parent_index_id, tx.uid, tx.gid, tx.open_level, tx.sort_number '.
        ' , ti.item_type_id, tt.title '.
        " FROM      $item_title as tt, ".
        " $index  AS tx ".
        " LEFT JOIN $item_basic AS ti on tx.index_id = ti.item_id ".
        " WHERE ($where_level) ".
        ' AND tt.title_id='.DEFAULT_ORDER_TITLE_OFFSET.' AND tt.item_id=ti.item_id'.
        ' ORDER by tx.uid, tx.parent_index_id, tx.sort_number';
    $db_result = $xoopsDB->query($sql);
    if (!$db_result) {
        // echo 'error in '.__FUNCTION__.' '.$xoopsDB->error()." sql=$sql".' at '.__LINE__.' in '.__FILE__."\n";

        return false;
    }
    $tree_items = array();
    $parent_full_path = array();
    $result = array();
    while ($ar = $xoopsDB->fetchArray($db_result)) {
        $index_id = intval($ar['index_id']);
        $tree_items[$index_id] = $ar;
        $pid = intval($ar['parent_index_id']);
        if (!isset($parent_full_path[$pid])) {
            $parent_full_path[$pid] = '';
        }
    }
    // extract to full path
    foreach ($parent_full_path as $k => $v) {
        if (0 == $k) {
            continue;
        }
        $fullpath = '';
        $idx = $k;
        while (0 != $idx) {
            if (!isset($tree_items[$idx])) {
                break;
            }
            $fullpath = $tree_items[$idx]['title'].'/'.$fullpath;
            $idx = $tree_items[$idx]['parent_index_id'];
        }
        $parent_full_path[$k] = $fullpath;
    }

    $result = array();
    // set result from tree_items and parent_full_path.
    foreach ($tree_items as $k => $v) {
        $parent_path = $parent_full_path[$v['parent_index_id']];
        // exclude check.
        if (IID_ROOT == $v['index_id']) {
            continue;
        }
        // delete "ROOT" string.
        $idx = strpos($parent_path, '/');
        $parent_path = substr($parent_path, $idx, strlen($parent_path));
        $a = array();
        $a['id'] = $k;
        $a['fullpath'] = $parent_path.$v['title'];
        $result[] = $a;
    }

    return $result;
}

/**
 * list index tree items.
 *
 * @param index_ids index id(array) of examined object
 *
 * @return item_id array
 * @return false:  error
 */
function xnpitmgrListIndexItems($index_ids)
{
    global $xoopsDB;
    $index_link = $xoopsDB->prefix('xoonips_index_item_link');
    $sql = "select distinct item_id  from $index_link where index_id in (".implode(',', $index_ids).')';
    $db_result = $xoopsDB->query($sql);
    if (!$db_result) {
        // echo 'error in '.__FUNCTION__.' '.$xoopsDB->error()." sql=$sql".' at '.__LINE__.' in '.__FILE__."\n";

        return false;
    }
    $result = array();
    while (list($id) = $xoopsDB->fetchRow($db_result)) {
        $result[] = intval($id);
    }

    return $result;
}

/**
 * get item basic information.
 *
 *  @param item_id item id
 *
 *  @return item detail<br />
 *     format:
 *       $result['item_id']<br />
 *       $result['doi']<br />
 *       $result[''] and set other xoonips_item_basic field value
 *  @return false: error
 */
function xnpitmgrGetItemBasicInfo($item_id)
{
    global $xoopsDB;
    $basic = $xoopsDB->prefix('xoonips_item_basic');
    $sql = "select * from $basic where item_id=$item_id";
    $db_result = $xoopsDB->query($sql);
    if (!$db_result) {
        // echo 'error in '.__FUNCTION__.' '.$xoopsDB->error()." sql=$sql".' at '.__LINE__.' in '.__FILE__."\n";

        return false;
    }
    $result = $xoopsDB->fetchArray($db_result);
    if (!$result) {
        $result = array();
    }

    return $result;
}

/**
 * get item type info from item_type_id.
 *
 *  @param item_type_id
 *
 *  @return array of item type info
 *     format:
 *       $result['item_type_id'] = item type id<br />
 *       $result['name']         = item type module name<br />
 *       $result['display_name'] = item type display name<br />
 *       $result['viewphp']      = item type view php file name<br />
 *               .<br />
 *       $result[n]
 *  @return false: error
 */
function xnpitmgrGetItemTypeNameById($item_type_id)
{
    global $xoopsDB;
    $item_type = $xoopsDB->prefix('xoonips_item_type');
    $sql = "select * from $item_type where item_type_id=$item_type_id";
    $db_result = $xoopsDB->query($sql);
    if (!$db_result) {
        // echo 'error in '.__FUNCTION__.' '.$xoopsDB->error()." sql=$sql".' at '.__LINE__.' in '.__FILE__."\n";

        return false;
    }
    $result = $xoopsDB->fetchArray($db_result);

    return $result;
}

/**
 * get item certify state<br />.
 *
 * @param xid item(item_id param) registered index id
 * @param iid item id
 *
 * @return item   certify state
 * @return false: error
 */
function xnpitmgrGetCertifyState($xid, $iid)
{
    $xid = (int) $xid;
    $iid = (int) $iid;

    global $xoopsDB;
    $sql = 'SELECT certify_state FROM '.$xoopsDB->prefix('xoonips_index_item_link').
      " WHERE item_id = $iid AND index_id = $xid ";
    $db_result = $xoopsDB->query($sql);
    if (!$db_result) {
        // echo 'error in '.__FUNCTION__.' '.$xoopsDB->error()." sql=$sql".' at '.__LINE__.' in '.__FILE__."\n";

        return false;
    }
    list($result) = $xoopsDB->fetchRow($db_result);

    return $result;
}

/**
 * item delete in index.
 *
 * @param xid item(item_id param) registered index id
 * @param iid item id
 *
 * @return true:  success
 * @return false: error
 */
function xnpitmgrUnregisterItem($xid, $iid)
{
    global $xoopsDB;
    $xid = (int) $xid;
    $iid = (int) $iid;
    // unregister the item.
    $ret = false;
    $sql = 'DELETE FROM '.$xoopsDB->prefix('xoonips_index_item_link')
        ." WHERE index_id=${xid} AND item_id=${iid}";
    if ($xoopsDB->queryF($sql)) {
        // update last update date
        $sql = 'UPDATE '.$xoopsDB->prefix('xoonips_item_basic').' SET last_update_date=UNIX_TIMESTAMP(NOW())'
            ." WHERE item_id=${xid}";
        if ($xoopsDB->queryF($sql)) {
            $ret = true;
        } else {
            // echo "error can't update last_updated_date in ".__FUNCTION__.' '.$xoopsDB->error()." sql=$sql".' at '.__LINE__.' in '.__FILE__."\n";
            $ret = false;
        }
    }
    if ($ret) {
        $sql = 'update '.$xoopsDB->prefix('xoonips_item_status ').
         " set deleted_timestamp=unix_timestamp(now()), is_deleted=1 where item_id=$iid";
        if ($xoopsDB->queryF($sql)) {
            $ret = true;
        } else {
            // echo "error can't update last_updated_date in ".__FUNCTION__.' '.$xoopsDB->error()." sql=$sql".' at '.__LINE__.' in '.__FILE__."\n";
            $ret = false;
        }
    }

    return $ret;
}

/**
 * get remote_host.
 *
 *    @return remoto host
 */
function getRemoteHost()
{
    return isset($_SERVER['REMOTE_HOST']) ? $_SERVER['REMOTE_HOST'] : $_SERVER['REMOTE_ADDR'];
}

/**
 * withdraw item.
 *
 *  @param xid     item(item_id param) registered index id
 *  @param item_id item id
 *
 *  @return bool|null  success
 *  @return bool|null failed
 */
function xnpitmgrWithDrawItem($xnpsid, $xid, $item_id)
{
    global $xoopsDB;
    $uid = $_SESSION['xoopsUserId'];
    if (CERTIFIED == xnpitmgrGetCertifyState($xid, $item_id)) {
        $index = array();
        if (xnpitmgrUnregisterItem($xid, $item_id)) {
            $eventlog_handler = &xoonips_getormhandler('xoonips', 'event_log');
            $eventlog_handler->recordRejectItemEvent($item_id, $xid);
            // delete record in DB
            $tab_name = $xoopsDB->prefix('xoonips_item_show');
            $sql = "DELETE FROM $tab_name WHERE item_id='".$item_id."' ";
            $xoopsDB->queryF($sql);

            return true;
        }

        return false;
    } else {
        return null;
    }
}
