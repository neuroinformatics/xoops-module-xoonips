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

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
// private functions. these functions used from AbstractLayer only
// @namespace  '_xnpal_'
// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

$GLOBALS['_xnpal_last_error_string'] = '';

/**
 * set last error message.
 */
function _xnpal_setLastErrorString($str)
{
    $GLOBALS['_xnpal_last_error_string'] = $str;
}

/**
 * get next sort number (maximum sort_number+1) of index under parentXID
 * it does not check parentXID availability.
 * this function can not handle under ROOT index.
 *
 * @param int $parentXID   parent index_id
 * @param int &$sortNumber reference of next sort number
 *
 * @return int rES_OK if success
 */
function _xnpal_getNewSortNumber($parentXID, &$sortNumber)
{
    global $xoopsDB;

    $sql = 'SELECT max(sort_number) FROM '.$xoopsDB->prefix('xoonips_index')." WHERE parent_index_id=$parentXID";
    $result = $xoopsDB->query($sql);
    if (!$result) {
        _xnpal_setLastErrorString('Error in _xnpal_getNewSortNumber'.' at '.__LINE__.' in '.__FILE__."\n".xnp_get_last_error_string());

        return RES_DB_QUERY_ERROR;
    }
    list($sortNumber) = $xoopsDB->fetchRow($result);
    $sortNumber = (int) $sortNumber;
    if (0 == (int) $sortNumber) { // NULL if parentXID is leaf node.
        $sortNumber = 1; // sort_number have to be more than 1, because 0 is reserved number.
    } else {
        $sortNumber = $sortNumber + 1;
    }

    return RES_OK;
}

/**
 * create index (internal function).
 */
function _xnpal_insertIndexInternal($sid, $index, &$xid)
{
    global $xoopsDB;

    // 1. insert basic information (index used item_basic table)
    // 2. insert index information (index_id must be same with item_id)
    $iid = 0;
    $result = RES_ERROR;

    //1.
    $index['item_type_id'] = ITID_INDEX;
    $index['uid'] = $index['contributor_uid'];
    $result = xnp_insert_item($sid, $index, $iid);
    if (RES_OK == $result) {
        //2.
        $sql = 'INSERT INTO '.$xoopsDB->prefix('xoonips_index').' ( index_id, parent_index_id, uid, gid, open_level, sort_number ) values ( '
            .$iid.','
            .$index['parent_index_id'].','
            .(OL_PRIVATE == $index['open_level'] ? $index['owner_uid'] : 'NULL').','
            .(OL_GROUP_ONLY == $index['open_level'] ? $index['owner_gid'] : 'NULL').','
            .$index['open_level'].','
            .$index['sort_number'].')';
        if ($result = $xoopsDB->queryF($sql)) {
            // set index id
            $xid = $iid;
            _xnpal_setLastErrorString('');
            $result = RES_OK;
        } else {
            xnp_delete_item($sid, $iid);
            _xnpal_setLastErrorString("error in _xnpal_insertIndexInternal: sql=${sql}".' at '.__LINE__.' in '.__FILE__."\n".xnp_get_last_error_string());
            $result = RES_ERROR; // error, insertion failure
        }
    } else {
        _xnpal_setLastErrorString("error result=${result} in _xnpal_insertIndexInternal: can't insert basic information".' at '.__LINE__.' in '.__FILE__."\n".xnp_get_last_error_string());
        $result = RES_ERROR;
    }

    return $result;
}

/**
 * update titles.
 *
 * 1.remove surples title entries more than given titles
 * 2.update titles
 * 3.insert new titles
 *
 * @param caller string of caller function
 * @param item_id ID of the item that be updated
 * @param titles string of new title or array of new titles(no need to be addslashes)
 *
 * @return true:succeed, false: failed
 */
function _xnpal_updateTitles($caller, $item_id, $titles)
{
    global $xoopsDB;

    if (!is_array($titles)) {
        $titles = array($titles);
    }

    //1. remove titles
    $sql = 'DELETE FROM '.$xoopsDB->prefix('xoonips_item_title')." WHERE item_id=${item_id} and title_id >= ".count($titles);
    $result = $xoopsDB->queryF($sql);
    if (!$result) {
        _xnpal_setLastErrorString("can't remove surplus titles in _xnpal_updateTitles(called by ${caller}) ".$xoopsDB->error()." sql=$sql at ".__LINE__.' in '.__FILE__."\n");

        return false;
    }
    //3. update title and insert new title
    $i = 0;
    foreach ($titles as $title) {
        $sql = 'INSERT INTO '.$xoopsDB->prefix('xoonips_item_title')." (item_id, title_id, title) values ( ${item_id}, ${i}, '".addslashes($title)."' )";
        $result = $xoopsDB->queryF($sql);
        if (!$result) {
            $sql = 'UPDATE '.$xoopsDB->prefix('xoonips_item_title')." SET title='".addslashes($title)."'"." WHERE item_id=${item_id} AND title_id=${i}";
            $result = $xoopsDB->queryF($sql);
            if (!$result) {
                _xnpal_setLastErrorString("can't update title in _xnpal_updateTitles(called by ${caller}) ".$xoopsDB->error()." sql=$sql at ".__LINE__.' in '.__FILE__."\n");

                return false;
            }
        }
        ++$i;
    }

    return true;
}

/**
 * update keywords.
 *
 * 1.remove surples keyword entries more than given keywords
 * 2.update keywords
 * 3.insert new keywords
 *
 * @param caller string of caller function
 * @param item_id ID of the item that be updated
 * @param keywords string of new keyword or array of new keywords.(no need to be addslashes)
 *
 * @return true:succeed, false: failed
 */
function _xnpal_updateKeywords($caller, $item_id, $keywords)
{
    global $xoopsDB;

    if (!is_array($keywords)) {
        $keywords = array($keywords);
    }

    //1. remove keywords
    $sql = 'DELETE FROM '.$xoopsDB->prefix('xoonips_item_keyword')." WHERE item_id=${item_id} and keyword_id >= ".count($keywords);
    $result = $xoopsDB->queryF($sql);
    if (!$result) {
        _xnpal_setLastErrorString("can't remove surplus keywords in _xnpal_updateKeywords(called by ${caller}) ".$xoopsDB->error()." sql=$sql at ".__LINE__.' in '.__FILE__."\n");

        return false;
    }
    // update keyword and insert new keyword
    $i = 0;
    foreach ($keywords as $keyword) {
        $sql = 'INSERT INTO '.$xoopsDB->prefix('xoonips_item_keyword')." (item_id, keyword_id, keyword) values ( ${item_id}, ${i}, '".addslashes($keyword)."' )";
        $result = $xoopsDB->queryF($sql);
        if (!$result) {
            $sql = 'UPDATE '.$xoopsDB->prefix('xoonips_item_keyword')." SET keyword='".addslashes($keyword)."'"." WHERE item_id=${item_id} AND keyword_id=${i}";
            $result = $xoopsDB->queryF($sql);
            if (!$result) {
                _xnpal_setLastErrorString("can't update keyword in _xnpal_updateKeywords(called by ${caller}) ".$xoopsDB->error()." sql=$sql at ".__LINE__.' in '.__FILE__."\n");

                return false;
            }
        }
        ++$i;
    }

    return true;
}

/**
 * register item (Basic Information)
 * this function required more than Platform user privileges.
 *
 * @param sid session ID
 * @param item registration item information
 * @param itemid reference of registered item id
 * @param direct boolean if set last_update_date, creation_date of item parameter
 *
 * @return int
 * @return int
 * @return int
 * @return int
 * @return int
 */
function _xnpal_insertItemInternal($sid, $item, &$itemid, $direct)
{
    global $xoopsDB;

    if (!xnp_is_valid_session_id($sid)) {
        return RES_NO_SUCH_SESSION;
    }
    if (!_xnpal_isActivatedBySession($sid)) {
        return RES_NO_WRITE_ACCESS_RIGHT;
    }

    $ret = RES_ERROR;

    $sql = 'INSERT INTO '.$xoopsDB->prefix('xoonips_item_basic')
        .' (item_type_id, description, doi, uid, creation_date, last_update_date, publication_year, publication_month, publication_mday, lang) VALUES ('
        .implode(
            ', ',
            array(isset($item['item_type_id']) ? (int) $item['item_type_id'] : 0,
                "'".addslashes(isset($item['description']) ? $item['description'] : '')."'",
                "'".addslashes(isset($item['doi']) ? $item['doi'] : '')."'",
                $item['uid'],
                ($direct ? $item['creation_date'] : 'UNIX_TIMESTAMP(NOW())'),
                ($direct ? $item['last_update_date'] : 'UNIX_TIMESTAMP(NOW())'),
                isset($item['publication_year']) ? (int) $item['publication_year'] : 0,
                isset($item['publication_month']) ? (int) $item['publication_month'] : 0,
                isset($item['publication_mday']) ? (int) $item['publication_mday'] : 0,
            "'".addslashes(isset($item['lang']) ? $item['lang'] : '')."'", )
        ).')';
    $result = $xoopsDB->queryF($sql);
    if ($result) {
        // get inserted item id
        $itemid = $xoopsDB->getInsertID();
        //insert titles and keywords
        if (isset($item['titles'])) {
            if (!is_array($item['titles'])) {
                $item['titles'] = array($item['titles']);
            }
            if (count($item['titles']) > 0) {
                if (!_xnpal_updateTitles(__FUNCTION__, $itemid, $item['titles'])) {
                    _xnpal_setLastErrorString("can't insert title in ".__FUNCTION__.' at '.__LINE__.' in '.__FILE__."\n".xnp_get_last_error_string());
                    xnp_delete_item($sid, $itemid);

                    return RES_DB_QUERY_ERROR;
                }
            }
        }
        if (isset($item['keywords'])) {
            if (!is_array($item['keywords'])) {
                $item['keywords'] = array($item['keywords']);
            }
            if (count($item['keywords']) > 0) {
                if (!_xnpal_updateKeywords(__FUNCTION__, $itemid, $item['keywords'])) {
                    _xnpal_setLastErrorString("can't insert keyword in ".__FUNCTION__.' at '.__LINE__.' in '.__FILE__."\n".xnp_get_last_error_string());
                    xnp_delete_item($sid, $itemid);

                    return RES_DB_QUERY_ERROR;
                }
            }
        }
        if (ITID_INDEX == $item['item_type_id']) {
            //nothing to do if index.
            _xnpal_setLastErrorString('');
            $ret = RES_OK;
        } else {
            //insert into private index
            $sql = 'SELECT private_index_id FROM '.$xoopsDB->prefix('xoonips_users').' WHERE uid='.$item['uid'];
            if ($result = $xoopsDB->query($sql)) {
                list($private_xid) = $xoopsDB->fetchRow($result);
                $ret = xnp_register_item($sid, $private_xid, $itemid);
            } else {
                _xnpal_setLastErrorString("error can't retrieve private_index_id in xnp_insert_item ".' at '.__LINE__.' in '.__FILE__."\n".xnp_get_last_error_string());
                xnp_delete_item($sid, $itemid);

                return RES_ERROR;
            }
        }
    } else {
        _xnpal_setLastErrorString("error can't insert item in xnp_insert_item ${sql} ".$xoopsDB->error().' at '.__LINE__.' in '.__FILE__."\n".xnp_get_last_error_string());
        $ret = RES_DB_QUERY_ERROR;
    }

    return $ret;
}

/**
 * @param int $uid
 */
function _xnpal_updateIndexInternal($sess_id, $uid, $newIndex, $oldIndex, $newParentIndex, $oldParentIndex)
{
    global $xoopsDB;
    $move = ($newIndex['parent_index_id'] != $oldIndex['parent_index_id']);
    $result = RES_ERROR;
    // if parent_index_id will be changed then do error check
    if ($move) {
        if (IID_ROOT == $oldIndex['item_id'] || IID_ROOT == $oldIndex['parent_index_id']) {
            _xnpal_setLastErrorString('in updateIndex: cannot change parent_index_id of system-created-index');

            return RES_ERROR; // this is root index or parent index
        } elseif (IID_ROOT == $newIndex['parent_index_id']) {
            _xnpal_setLastErrorString('in updateIndex: cannot change parent_index_id to ROOT');

            return RES_ERROR; // reject if new parent index will be root index
        }
    }
    // check writable permission
    if (!_xnpal_isWritableInternal($sess_id, $uid, $newParentIndex)) {
        _xnpal_setLastErrorString('in updateIndex: no access right. cannot move.');

        return RES_ERROR; // no access right
    }
    // check duplication of sort_number except moving
    if (!$move && $newIndex['sort_number'] != $oldIndex['sort_number']) {
        $conflictIndexes = array();
        $cond = 'tx.sort_number='.$newIndex['sort_number'];
        $result = _xnpal_getIndexesInternal($sess_id, $cond, $uid, $conflictIndexes, '');
        if (RES_OK == $result) {
            if (count($conflictIndexes)) {
                _xnpal_setLastErrorString('in updateIndex: sort_number conflicts');

                return RES_ERROR; // duplicated sortNumber
            }
        } else {
            return $result; // cannot _xnpal_getIndexesInternal()
        }
    }
    // reject if titles are empty
    if ('' == $newIndex['titles'][DEFAULT_INDEX_TITLE_OFFSET]) {
        _xnpal_setLastErrorString('in _xnpal_updateIndexInternal: empty title.');

        return RES_ERROR;
    }

    if ($move) {
        $descXID = array();
        // recursion check for descendant index ids
        $result = _xnpal_getDescendantIndexID($oldIndex['item_id'], $descXID);
        if (RES_OK != $result) {
            _xnpal_setLastErrorString('in _xnpal_updateIndexInternal: _xnpal_getDescendantIndexID failed');

            return RES_ERROR;
        }
        $descXIDLen = count($descXID);
        for ($i = 0; $i < $descXIDLen; ++$i) {
            if ($descXID[$i] == $newIndex['parent_index_id']) {
                // reject if descendant index id will be parent index id
                _xnpal_setLastErrorString('in _xnpal_updateIndexInternal: circular parent');

                return RES_ERROR;
            }
        }
        // generate sort_number
        $result = _xnpal_getNewSortNumber($newParentIndex['item_id'], $sortNumber);
        if (RES_OK != $result) {
            _xnpal_setLastErrorString('');

            return $result;
        }
        $newIndex['sort_number'] = $sortNumber;
    }
    $ownerOpenLevelString = ($newParentIndex['open_level']);
    $ownerUIDString = (OL_PRIVATE == $newParentIndex['open_level'] ? $newParentIndex['owner_uid'] : 'NULL');
    $ownerGIDString = (OL_GROUP_ONLY == $newParentIndex['open_level'] ? $newParentIndex['owner_gid'] : 'NULL');
    $sql = 'UPDATE '.$xoopsDB->prefix('xoonips_index set').
        ' parent_index_id = '.($newIndex['parent_index_id']).
        ', uid = '.$ownerUIDString.
        ', gid = '.$ownerGIDString.
        ', open_level = '.$ownerOpenLevelString.
        ', sort_number = '.$newIndex['sort_number'].
        ' where index_id = '.$newIndex['item_id'];
    if ($xoopsDB->queryF($sql)) {
        $sql = 'UPDATE '.$xoopsDB->prefix('xoonips_item_basic').' set'.
            ' item_type_id = '.$newIndex['item_type_id'].
            ', uid = '.$uid.
            ', last_update_date = '.$newIndex['last_update_date'].
            ', creation_date = '.$newIndex['creation_date'].
            ", description = '".addslashes($newIndex['description'])."'".
            ' where item_id = '.$newIndex['item_id'];
        if ($xoopsDB->queryF($sql)) {
            if (!_xnpal_updateTitles(__FUNCTION__, $newIndex['item_id'], $newIndex['titles'])) {
                _xnpal_setLastErrorString("can't update titles. update index incompletely in ".__FUNCTION__.' at '.__LINE__.' in '.__FILE__);
                $ret = RES_DB_QUERY_ERROR;
            } elseif (!_xnpal_updateKeywords(__FUNCTION__, $newIndex['item_id'], $newIndex['keywords'])) {
                _xnpal_setLastErrorString("can't insert keywords. update index incompletely  in ".__FUNCTION__.' at '.__LINE__.' in '.__FILE__);
                $ret = RES_DB_QUERY_ERROR;
            } else {
                _xnpal_setLastErrorString('');
                $result = RES_OK;
            }
        } else {
            _xnpal_setLastErrorString("error in _xnpal_updateIndexInternal: sql=${sql}".' at '.__LINE__.' in '.__FILE__."\n".xnp_get_last_error_string());
            $result = RES_ERROR;
        }
    } else {
        _xnpal_setLastErrorString("error in _xnpal_updateIndexInternal: sql=${sql}".' at '.__LINE__.' in '.__FILE__."\n".xnp_get_last_error_string());
        $result = RES_ERROR;
    }

    if (RES_OK == $result) {
        if ($newParentIndex['owner_uid'] != $oldParentIndex['owner_uid']
            || $newParentIndex['owner_gid'] != $oldParentIndex['owner_gid']
            || $newParentIndex['open_level'] != $oldParentIndex['open_level']
        ) {
            // if parent index location will changed, then this index and descendant
            // indexes will be changed respectively.
            $descXID = array();
            $result = _xnpal_getDescendantIndexID($oldIndex['item_id'], $descXID);
            if (RES_OK != $result) {
                _xnpal_setLastErrorString('in _xnpal_updateIndexInternal: _xnpal_getDescendantIndexID failed');

                return RES_ERROR;
            }
            $descXIDLen = count($descXID);
            for ($i = 0; $i < $descXIDLen; ++$i) {
                $sql = 'UPDATE '.$xoopsDB->prefix('xoonips_index').' set '.
                    ' uid='.$ownerUIDString.
                    ', gid='.$ownerGIDString.
                    ', open_level='.$ownerOpenLevelString.
                    ' WHERE index_id='.($descXID[$i]);
                _xnpal_querySimple('updateIndex', $sql);
            }
        }
    }

    if (OL_PUBLIC == $newIndex['open_level']) {
        // call insertMetadataEventAuto() for certified items under newIndex
        $descXID = array();
        $result = _xnpal_getDescendantIndexID($newIndex['item_id'], $descXID);
        if (RES_OK != $result) {
            _xnpal_setLastErrorString('in updateIndex: _xnpal_getDescendantIndexID failed');

            return RES_ERROR;
        }

        $xid_str = _xnpal_getCsvStr($descXID);
        if (0 == count($descXID)) {
            return RES_OK;
        }

        $sql = 'select distinct item_id from '.$xoopsDB->prefix('xoonips_index_item_link ').
            ' where certify_state = '.CERTIFIED.
            "  and index_id in ( $xid_str )";
        $result2 = $xoopsDB->query($sql);
        if ($result2) {
            $result = RES_OK;
            while (list($iid) = $xoopsDB->fetchRow($result2)) {
                $result = insertMetadataEventAuto($iid);
                if (RES_OK != $result) {
                    break;
                }
            }
        } else {
            _xnpal_setLastErrorString("error in _xnpal_updateIndexInternal: sql=${sql}".' at '.__LINE__.' in '.__FILE__."\n".xnp_get_last_error_string());

            return RES_DB_QUERY_ERROR;
        }
    }
    if (RES_OK == $result) {
        _xnpal_setLastErrorString('');
    }

    return $result;
}

/**
 * update metadata status(creation data, last modified, deleted flag) for
 * OAI-PMH repository.
 */
function _xnpal_updateItemStatus()
{
    global $xoopsDB;
    $ret = RES_ERROR;
    if (public_item_target_user_all()) {
        // update if item is public and item_status was deleted or not found
        //  select distinct txil.item_id, tis.is_deleted from x_xoonips_index as tx,x_xoonips_index_item_link as txil left join x_xoonips_item_status as tis on txil.item_id = tis.item_id  where  tx.index_id=txil.index_id and  txil.certify_state = 2 and  tx.open_level = 1;
        $sql = 'select distinct txil.item_id, tis.is_deleted '
            .' from '.$xoopsDB->prefix('xoonips_index').' as tx, '
            .$xoopsDB->prefix('xoonips_index_item_link').' as txil '
            .' left join '.$xoopsDB->prefix('xoonips_item_status').' as tis on txil.item_id = tis.item_id '
            .' where  tx.index_id=txil.index_id '
            .' and  txil.certify_state = '.CERTIFIED
            .' and  tx.open_level = '.OL_PUBLIC;
        if ($result = $xoopsDB->query($sql)) {
            $ret = RES_OK;
            while (list($iid, $isDeleted) = $xoopsDB->fetchRow($result)) {
                if (null == $isDeleted) {
                    $sql = 'insert into '.$xoopsDB->prefix('xoonips_item_status')
                        .'( item_id, created_timestamp, is_deleted ) values '
                        ."( ${iid}, unix_timestamp(now()), 0 )";
                } else {
                    $sql = 'update '.$xoopsDB->prefix('xoonips_item_status')
                        .' set created_timestamp=unix_timestamp(now()), is_deleted=0 '
                        ." where item_id=${iid}";
                }
                if ($result2 = $xoopsDB->queryF($sql)) {
                    _xnpal_setLastErrorString('');
                    $ret = RES_OK;
                } else {
                    return RES_DB_QUERY_ERROR;
                }
            }
        } else {
            return RES_DB_QUERY_ERROR;
        }
        // update item is not public and item_status.is_deleted=0
        // select tis.item_id, count(tx.index_id) as public_count from x_xoonips_item_status as tis  left join x_xoonips_index_item_link as tl on tl.item_id = tis.item_id and certify_state = 2 left join x_xoonips_index           as tx on tx.index_id =tl.index_id and tx.open_level = 1 where is_deleted=0 group by tis.item_id having pubilc_count=0 ;
        $sql = 'select tis.item_id, count(tx.index_id) as public_count '
            .' from '.$xoopsDB->prefix('xoonips_item_status').' as tis  '
            .' left join '.$xoopsDB->prefix('xoonips_index_item_link').' as tl on tl.item_id = tis.item_id and certify_state = '.CERTIFIED
            .' left join '.$xoopsDB->prefix('xoonips_index').'           as tx on tx.index_id =tl.index_id and tx.open_level = '.OL_PUBLIC
            .' where is_deleted=0 group by tis.item_id having public_count=0 ';
        if ($result = $xoopsDB->query($sql)) {
            $ret = RES_OK;
            while (list($iid) = $xoopsDB->fetchRow($result)) {
                $sql = 'update '.$xoopsDB->prefix('xoonips_item_status').' '
                    .' set is_deleted=1, deleted_timestamp=unix_timestamp(now()) '
                    ." where item_id=${iid}";
                if ($result2 = $xoopsDB->queryF($sql)) {
                    _xnpal_setLastErrorString('');
                    $ret = RES_OK;
                } else {
                    $ret = RES_DB_QUERY_ERROR;
                    berak;
                }
            }
        } else {
            $ret = RES_DB_QUERY_ERROR;
        }
    } else {
        $sql = 'delete from '.$xoopsDB->prefix('xoonips_item_status');
        if ($result = $xoopsDB->queryF($sql)) {
            _xnpal_setLastErrorString('');
            $ret = RES_OK;
        } else {
            $ret = RES_DB_QUERY_ERROR;
        }
    }

    return $ret;
}

/**
 * just query SQL.
 *
 * @param sql sql
 * @param string $functionName
 *
 * @return int
 */
function _xnpal_querySimple($functionName, $sql)
{
    global $xoopsDB;
    $result = $xoopsDB->queryF($sql);
    if (!$result) {
        _xnpal_setLastErrorString("error in $functionName, ".$xoopsDB->error()." sql=$sql".' at '.__LINE__.' in '.__FILE__."\n".xnp_get_last_error_string());

        return RES_DB_QUERY_ERROR;
    }
    _xnpal_setLastErrorString('');

    return RES_OK;
}

/**
 * query SQL and get first integer result (if value is null then use zero).
 *
 * @param sql string
 * @param uint reference of result variable
 * @param string $functionName
 *
 * @return int
 */
function _xnpal_queryGetUnsignedInt($functionName, $sql, &$uint)
{
    global $xoopsDB;
    $result = $xoopsDB->query($sql);
    if (!$result) {
        _xnpal_setLastErrorString("error in $functionName, ".$xoopsDB->error()." sql=$sql".' at '.__LINE__.' in '.__FILE__."\n".xnp_get_last_error_string());

        return RES_DB_QUERY_ERROR;
    }
    if (0 == $xoopsDB->getRowsNum($result)) {
        return RES_ERROR;
    }
    list($uint) = $xoopsDB->fetchRow($result);
    _xnpal_setLastErrorString('');

    return RES_OK;
}

/**
 * get descendant index ids.
 *
 * @param xid index id
 * @param descXID refernce of results
 */
function _xnpal_getDescendantIndexID($xid, &$descXID)
{
    global $xoopsDB;
    // descXID[0] - descXID[i-1] : searched nodes
    // descXID[i] 〜 descXID[iFill-1] : not searched nodes
    $descXID = array($xid);
    $i = 0;
    $iFill = 1;
    while ($i < $iFill) {
        $xid = $descXID[$i++]; // get a not searched node
        // add child list to not searched not
        $sql = 'SELECT index_id FROM '.$xoopsDB->prefix('xoonips_index')." WHERE parent_index_id=$xid";
        $result = $xoopsDB->query($sql);
        if (!$result) {
            _xnpal_setLastErrorString('error in _xnpal_getDescendantIndexID, '.$xoopsDB->error()." sql=$sql".' at '.__LINE__.' in '.__FILE__."\n".xnp_get_last_error_string());

            return RES_DB_QUERY_ERROR;
        }
        while (list($xid) = $xoopsDB->fetchRow($result)) {
            $descXID[$iFill++] = $xid;
        }
    }
    _xnpal_setLastErrorString('');

    return RES_OK;
}

/**
 * create implode( ',', descXID ) style strings.
 *
 * @param int descXID. if not integer data found, cast to integer value
 */
function _xnpal_getCsvStr($descXID)
{
    if (count($descXID)) {
        $ar = array();
        foreach ($descXID as $val) {
            $ar[] = (int) $val;
        }

        return implode(',', $ar);
    }

    return '';
}

/**
 * check xoonips user availablity.
 *
 * @param int $uid user id
 *
 * @return bool false if not exists
 */
function _xnpal_uidExists($uid)
{
    global $xoopsDB;
    $sql = 'SELECT uid FROM '.$xoopsDB->prefix('xoonips_users')." WHERE uid=$uid";
    $result = $xoopsDB->query($sql);
    if (!$result) {
        _xnpal_setLastErrorString('error in _xnpal_uidExists, '.$xoopsDB->error()." sql=$sql".' at '.__LINE__.' in '.__FILE__."\n".xnp_get_last_error_string());

        return false;
    }

    return  0 != $xoopsDB->getRowsNum($result);
}

/**
 * check ownerUID, ownerGID and openLevel indexes writable privileges.
 *
 * @param string $sess_id   session id
 * @param int    $uid       user id
 * @param int    $ownerUID  index owner user
 * @param int    $ownerGID  index owner group
 * @param int    $openLevel open level of index
 *
 * @return bool
 */
function _xnpal_isWritableInternal2($sess_id, $uid, $ownerUID, $ownerGID, $openLevel)
{
    if (OL_PUBLIC == $openLevel) {
    } elseif (OL_GROUP_ONLY == $openLevel) {
        $xgroup_handler = &xoonips_gethandler('xoonips', 'group');
        if ($xgroup_handler->isGroupAdmin($uid, $ownerGID)) {
            return true;
        }
    } elseif (OL_PRIVATE == $openLevel) {
        if ($uid == $ownerUID) {
            return true;
        }
    }
    if (xnp_is_moderator($sess_id, $uid)) {
        return true;
    }

    return false;
}

function _xnpal_isWritableInternal($sess_id, $uid, $index)
{
    return _xnpal_isWritableInternal2($sess_id, $uid, $index['owner_uid'], $index['owner_gid'], $index['open_level']);
}

/**
 * @param int $me
 */
function _xnpal_insertMetadataEvent($me, $iid)
{
    global $xoopsDB;
    if (ME_CREATED == $me) {
        $sql = 'replace '.$xoopsDB->prefix('xoonips_item_status ').
        ' ( item_id, created_timestamp, modified_timestamp, deleted_timestamp, is_deleted ) values '.
        " ( $iid, unix_timestamp(now()), NULL, NULL, 0 )";
    } elseif (ME_MODIFIED == $me) {
        $sql = 'update '.$xoopsDB->prefix('xoonips_item_status ').
        " set modified_timestamp=unix_timestamp(now()), is_deleted=0 where item_id=$iid";
    } elseif (ME_DELETED == $me) {
        $sql = 'update '.$xoopsDB->prefix('xoonips_item_status ').
        " set deleted_timestamp=unix_timestamp(now()), is_deleted=1 where item_id=$iid";
    } else {
        return RES_ERROR;
    }

    return _xnpal_querySimple('_xnpal_insertMetadataEvent', $sql);
}

/**
 * is this moderators' session?
 *
 * @param sess_id session id
 *
 * @return bool false if this is not moderators' session
 */
function _xnpal_isModeratorBySession($sess_id)
{
    if (RES_OK == _xnpal_sessionID2UID($sess_id, $sess_uid)) {
        return xnp_is_moderator($sess_id, $sess_uid);
    }

    return false;
}

/**
 * is this certified xoonips users' session?
 *
 * @param string $sess_id session id
 *
 * @return bool false if not activated xoonips user
 */
function _xnpal_isActivatedBySession($sess_id)
{
    if (RES_OK == _xnpal_sessionID2UID($sess_id, $sess_uid)) {
        return xnp_is_activated($sess_id, $sess_uid);
    }
    _xnpal_setLastErrorString("error can't get uid from session id in _xnpal_isActivatedBySession".' at '.__LINE__.' in '.__FILE__."\n");

    return false;
}

/**
 * get user id from session id.
 *
 * @param string $sess_id  session id
 * @param int    $sess_uid returned use id
 *
 * @return int false if failure
 */
function _xnpal_sessionID2UID($sess_id, &$sess_uid)
{
    global $xoopsDB;
    if ($sess_id == session_id()) { // get current user's session
        if (isset($_SESSION['xoopsUserId'])) {
            $sess_uid = $_SESSION['xoopsUserId'];
            _xnpal_setLastErrorString('');

            return RES_OK;
        }
    } else { // get other users' session
        $esc_sess_id = addslashes(session_id());
        $sql = 'select sess_data from '.$xoopsDB->prefix('session')." where sess_id='$esc_sess_id'";
        $result = $xoopsDB->query($sql);

        if (!$result) {
            return RES_DB_QUERY_ERROR;
        }
        if ($xoopsDB->getRowsNum($result)) {
            list($sess_data) = $xoopsDB->fetchRow($result);
            $bak = $_SESSION;
            session_decode($sess_data);
            $s = $_SESSION;
            $_SESSION = $bak;
            if (isset($s['xoopsUserId'])) {
                $sess_uid = $s['xoopsUserId'];
                _xnpal_setLastErrorString('');

                return RES_OK;
            }
        }
    }
    // invalid session_id() or $_SESSION['xoopsUserId'] not found.
    // maybe this is guest.
    if (public_item_target_user_all()) {
        $sess_uid = UID_GUEST;
        _xnpal_setLastErrorString('');

        return RES_OK;
    }

    return RES_NO_SUCH_SESSION;
}

/**
 * @param int $xid
 */
function _xnpal_deleteIndexInternal($sess_id, $xid, &$index, &$descXID, &$affectedIIDs)
{
    $functionName = 'deleteIndex';
    global $xoopsDB;
    $uid = $_SESSION['xoopsUserId'];

    $affectedIIDs = array();
    $index = array();
    $result = xnp_get_index($sess_id, $xid, $index);
    if (RES_OK != $result) {
        return $result;
    }

    $parentXid = $index['parent_index_id'];
    if (IID_ROOT == $index['item_id'] || IID_ROOT == $parentXid) {
        _xnpal_setLastErrorString('in deleteIndex: cannot delete system-created-index.'.' at '.__LINE__.' in '.__FILE__."\n".xnp_get_last_error_string());

        return RES_ERROR;
    }

    if (!_xnpal_isWritableInternal($sess_id, $uid, $index)) {
        _xnpal_setLastErrorString('in deleteIndex: no write access right.'.' at '.__LINE__.' in '.__FILE__."\n".xnp_get_last_error_string());

        return RES_NO_WRITE_ACCESS_RIGHT;
    }

    // list up deleting index ids
    $result = _xnpal_getDescendantIndexID($xid, $descXID);
    if (RES_OK != $result) {
        _xnpal_setLastErrorString('in deleteIndex: _xnpal_getDescendantIndexID failed'.' at '.__LINE__.' in '.__FILE__."\n".xnp_get_last_error_string());

        return RES_ERROR;
    }

    $affectedIIDs = array();
    if (OL_PUBLIC == $index['open_level']) {
        // save certified items under newIndex for run insertMetadataEventAuto()
        $sql = 'select count(*) from '.$xoopsDB->prefix('xoonips_item_basic');
        $iidsLen = 0;
        $result = _xnpal_queryGetUnsignedInt($functionName, $sql, $iidsLen);
        if (RES_OK != $result) {
            return $result;
        }
        if (count($descXID)) {
            $xid_str = _xnpal_getCsvStr($descXID);
            $sql = 'select item_id from '.$xoopsDB->prefix('xoonips_index_item_link').
                ' where certify_state='.CERTIFIED.
                " and index_id in ( $xid_str ) ";
            $result = $xoopsDB->query($sql);
            if (!$result) {
                return RES_DB_QUERY_ERROR;
            }
            while (list($iid) = $xoopsDB->fetchRow($result)) {
                $affectedIIDs[] = $iid;
            }
        }
    }

    for ($i = count($descXID) - 1; $i >= 0; --$i) {
        $xid = $descXID[$i];
        $linkTable = $xoopsDB->prefix('xoonips_index_item_link');
        $indexTable = $xoopsDB->prefix('xoonips_index');
        // if descXID[i] is located under Private index and this linked item is
        // only one. then move item to parent index
        if (OL_PRIVATE == $index['open_level']) {
            $sql = 'SELECT t1.index_item_link_id, t1.item_id, count(*) '.
            " FROM $linkTable  AS t1 ".
            " LEFT JOIN $linkTable AS t2 ON t1.item_id = t2.item_id ".
            " LEFT JOIN $indexTable AS tx2 on t2.index_id = tx2.index_id ".
            " WHERE t1.index_id=$xid ".
            ' and tx2.open_level = '.OL_PRIVATE.
            ' group by t1.item_id ';
            $result = $xoopsDB->query($sql);
            if (!$result) {
                _xnpal_setLastErrorString('in deleteIndex at '.__LINE__.' in '.__FILE__."\n".xnp_get_last_error_string());

                return RES_ERROR;
            }
            while (list($link_id, $item_id, $count) = $xoopsDB->fetchRow($result)) {
                if (1 == $count) {
                    $sql2 = "UPDATE $linkTable set index_id= $parentXid where index_item_link_id = $link_id";
                    $result2 = _xnpal_querySimple('deleteIndex', $sql2);
                    if (RES_OK != $result2) {
                        break;
                    }
                }
            }
        }
        // remove items in descXID[i] and descXID[i]
        $sql = "DELETE from $linkTable where index_id=$xid";
        $result = _xnpal_querySimple($functionName, $sql);
        if (RES_OK == $result) {
            $sql = 'DELETE from '.$xoopsDB->prefix('xoonips_item_basic')." where item_id = $xid";
            $result = _xnpal_querySimple($functionName, $sql);
            if (RES_OK == $result) {
                $sql = 'DELETE from '.$xoopsDB->prefix('xoonips_index')." where index_id = $xid";
                $result = _xnpal_querySimple($functionName, $sql);
            }
            // delete title
            $sql = 'DELETE from '.$xoopsDB->prefix('xoonips_item_title')." where item_id = $xid";
            $result = _xnpal_querySimple($functionName, $sql);
            // delete keyword
            $sql = 'DELETE from '.$xoopsDB->prefix('xoonips_item_keyword')." where item_id = $xid";
            $result = _xnpal_querySimple($functionName, $sql);
        }
    }

    // run insertMetadataEventAuto() for affected items
    $len = count($affectedIIDs);
    for ($i = 0; $i < $len; ++$i) {
        insertMetadataEventAuto($affectedIIDs[$i]);
    }
    if (RES_OK == $result) {
        _xnpal_setLastErrorString('');
    }

    return $result;
}

/**
 * get indexes by searche criteria.
 *
 * @param cond condition of SQL. if zero is not used.
 *  it can uses tx(index), ti(item), tlink(group_user_link) for table names
 * @param uid  user id
 * @param indexes returened indexes
 * @param criteriaString string part of SQL
 *
 * @return int RES_OK if success
 */
function _xnpal_getIndexesInternal($sess_id, $cond, $uid, &$indexes, $criteriaString)
{
    global $xoopsDB;
    $groupTable = $xoopsDB->prefix('xoonips_groups');
    $groupUserLinkTable = $xoopsDB->prefix('xoonips_groups_users_link');
    $indexTable = $xoopsDB->prefix('xoonips_index');
    $itemTable = $xoopsDB->prefix('xoonips_item_basic');
    $titleTable = $xoopsDB->prefix('xoonips_item_title');
    if (false == $cond) {
        $cond = ' 1 ';
    }
    $accessRightCond = '1';
    if (!xnp_is_moderator($sess_id, $uid)) {
        $accessRightCond =
            ' (  tx.open_level=1 '.
            ' OR tx.open_level=2 AND tlink.uid is not NULL AND tx.gid != '.GID_DEFAULT.
            " OR tx.open_level=3 AND tx.uid = $uid )";
    } // アクセス権を表すSQL

    $sql = 'SELECT tx.index_id as item_id, tx.parent_index_id, tx.uid, tx.gid, tx.open_level, tx.sort_number '.
        ' , ti.item_type_id, ti.creation_date, ti.uid, ti.description, ti.last_update_date, tt.title as title '.
        " FROM      $titleTable as tt, ".
        " $indexTable  AS tx ".
        " LEFT JOIN $itemTable   AS ti on tx.index_id = ti.item_id ".
        " LEFT JOIN $groupUserLinkTable  AS tlink on tlink.gid = tx.gid and tlink.uid = $uid ".
        " LEFT JOIN $groupTable  AS tg on tx.gid = tg.gid ".
        " WHERE $accessRightCond AND ( tx.open_level != 2 OR tx.open_level = 2 AND tg.gid IS NOT NULL ) ".
        ' AND tt.title_id='.DEFAULT_ORDER_TITLE_OFFSET.' AND tt.item_id=ti.item_id'.
        " AND $cond $criteriaString";

    $result = $xoopsDB->query($sql);
    if (!$result) {
        _xnpal_setLastErrorString('error in _xnpal_getIndexesInternal '.$xoopsDB->error()." sql=$sql".' at '.__LINE__.' in '.__FILE__."\n".xnp_get_last_error_string());

        return RES_DB_QUERY_ERROR;
    }

    $index_buf = array();
    $orderd_ids = array(); //array of sorted item_id(s) to sort $index_buf in the end of this function
    while (list($index_id, $parent_index_id, $owner_uid, $gid, $open_level, $sort_number,
            $item_type_id, $creation_date, $contributor_uid, $description, $last_update_date) = $xoopsDB->fetchRow($result)) {
        $index = array(
            'item_id' => $index_id,
            'parent_index_id' => $parent_index_id,
            'owner_uid' => (null == $owner_uid) ? 0 : $owner_uid,
            'owner_gid' => (null == $gid) ? 0 : $gid,
            'open_level' => $open_level,
            'sort_number' => $sort_number,
            'item_type_id' => $item_type_id,
            'creation_date' => $creation_date,
            'contributor_uid' => $contributor_uid,
            'titles' => array(),
            'keywords' => array(),
            'description' => $description,
            'last_update_date' => $last_update_date,
        );
        $index_buf[$index_id] = $index;
        $orderd_ids[] = $index_id;
    }

    if (count($index_buf) > 0) {
        //get titles of selected item
        $sql = 'SELECT item_id, title FROM '.$xoopsDB->prefix('xoonips_item_title')
            .' WHERE item_id IN ( '.implode(',', array_keys($index_buf)).' ) ORDER BY item_id ASC, title_id ASC';
        $result = $xoopsDB->query($sql);
        if (!$result) {
            _xnpal_setLastErrorString('error in _xnpal_getIndexesInternal '.$xoopsDB->error()." sql=$sql".' at '.__LINE__.' in '.__FILE__."\n".xnp_get_last_error_string());

            return RES_DB_QUERY_ERROR;
        }
        while ($row = $xoopsDB->fetchArray($result)) {
            $index_buf[$row['item_id']]['titles'][] = $row['title'];
        }
        $textutil = &xoonips_getutility('text');
        foreach ($index_buf as $k => $index) {// rename to "Private" if owwner_uid of index == $uid
            if (IID_ROOT == $index['parent_index_id'] && OL_PRIVATE == $index['open_level'] && $index['owner_uid'] == $uid) {
                $index_buf[$k]['titles'][DEFAULT_INDEX_TITLE_OFFSET] = XNP_PRIVATE_INDEX_TITLE;
            }
            $index_buf[$k]['html_title'] = $textutil->html_special_chars($index_buf[$k]['titles'][DEFAULT_INDEX_TITLE_OFFSET]);
        }

        //get keywords of selected item
        $sql = 'SELECT item_id, keyword FROM '.$xoopsDB->prefix('xoonips_item_keyword')
            .' WHERE item_id IN ( '.implode(',', array_keys($index_buf)).' ) ORDER BY item_id ASC, keyword_id ASC';
        $result = $xoopsDB->query($sql);
        if (!$result) {
            _xnpal_setLastErrorString('error in _xnpal_getIndexesInternal '.$xoopsDB->error()." sql=$sql".' at '.__LINE__.' in '.__FILE__."\n".xnp_get_last_error_string());

            return RES_DB_QUERY_ERROR;
        }
        while ($row = $xoopsDB->fetchArray($result)) {
            $index_buf[$row['item_id']]['keywords'][] = $row['keyword'];
        }
    }

    // convert the associative array(index_buf) to the array(indexes) (keep order specified by criteriaString)
    foreach ($orderd_ids as $id) {
        $indexes[] = $index_buf[$id];
    }

    _xnpal_setLastErrorString('');

    return RES_OK;
}

// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -
// AbstractLayer API
//
// public functions.
// @namespace  'xnp_'
// - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

/**
 * get last error message.
 *
 * @return string error message
 */
function xnp_get_last_error_string()
{
    return $GLOBALS['_xnpal_last_error_string'];
}

/**
 * convert criteria to SQL.
 *
 * @param array $cri criteria
 *
 * @return string 'WHERE' part of SQL
 */
function xnp_criteria2str($cri)
{
    $sql = '';
    if (isset($cri['orders']) && count($cri['orders']) > 0) {
        $orders = array();
        foreach ($cri['orders'] as $o) {
            if (isset($o['name']) && isset($o['order']) && in_array($o['order'], array(0, 1))) {
                if (strlen($o['name']) > 64 || !preg_match('/\A[0-9a-z-+_]+\z/i', $o['name'])) {
                    xoonips_error_exit(500);
                }
                $orders[] = '`'.str_replace('`', '``', $o['name']).'` '.(0 == $o['order'] ? 'ASC' : 'DESC');
            }
        }
        if (count($orders) > 0) {
            $sql .= ' ORDER BY '.implode(', ', $orders);
        }
    }
    if (isset($cri['rows']) && $cri['rows'] > 0) {
        $sql .= ' LIMIT ';
        if (isset($cri['start']) && $cri['start'] > 0) {
            $sql .= $cri['start'].', ';
        }
        $sql .= $cri['rows'];
    }

    return $sql;
}

/**
 * get initial certification state of index creation or item update
 * from xoonips configuration.
 */
function xnp_get_initial_certify_state_from_config()
{
    $certify_item_val = '';
    $ret = NOT_CERTIFIED;

    if (RES_OK == xnp_get_config_value(XNP_CONFIG_CERTIFY_ITEM_KEY, $certify_item_val)) {
        if (XNP_CONFIG_CERTIFY_ITEM_AUTO == $certify_item_val) {
            //certify automatic
            $ret = CERTIFIED;
        } elseif (XNP_CONFIG_CERTIFY_ITEM_ON == $certify_item_val) {
            //certify by moderator or group admin
            $ret = CERTIFY_REQUIRED;
        }
    }

    return $ret;
}

/**
 * get public item ids.
 *   regular user does not get other user's created items.
 *
 * @param int   $sid   session id
 * @param int   $uid   target user id
 * @param array &$iids public item ids (result)
 *
 * @return int status belows:
 *             RES_OK, RES_NO_SUCH_SESSION, RES_DB_QUERY_ERROR, RES_ERROR
 */
function xnp_get_own_public_item_id($sid, $uid, &$iids)
{
    global $xoopsDB;

    $iids = array();

    if (!xnp_is_valid_session_id($sid)) {
        return RES_NO_SUCH_SESSION;
    }

    $ret = RES_ERROR;

    if (RES_OK != ($ret = _xnpal_sessionID2UID($sid, $sess_uid))) {
        return $ret;
    }
    if ($sess_uid != $uid && !_xnpal_isModeratorBySession($sid)) {
        return RES_NO_READ_ACCESS_RIGHT;
    } //no permissions to access these items

    $sql = 'SELECT DISTINCT tlink.item_id'
        .' FROM '.$xoopsDB->prefix('xoonips_index_item_link').' AS tlink'
        .' LEFT JOIN '.$xoopsDB->prefix('xoonips_index').' AS tx ON tlink.index_id=tx.index_id'
        .' LEFT JOIN '.$xoopsDB->prefix('xoonips_item_basic').' AS ti ON tlink.item_id=ti.item_id'
        .' LEFT JOIN '.$xoopsDB->prefix('xoonips_groups_users_link').' AS tgulink ON ( tgulink.gid = tx.gid AND tx.open_level = '.OL_GROUP_ONLY
        .') OR tx.open_level= '.OL_PUBLIC
        .' WHERE open_level= '.OL_PUBLIC
        .' AND certify_state= '.CERTIFIED
        .' AND item_type_id != '.ITID_INDEX
        ." AND ( ti.uid=${sess_uid}"
        ." OR is_admin=1 AND tgulink.uid=${sess_uid}"
        .')';

    if ($result = $xoopsDB->query($sql)) {
        while (list($iid) = $xoopsDB->fetchRow($result)) {
            $iids[] = $iid;
        }
        _xnpal_setLastErrorString('');
        $ret = RES_OK;
    } else {
        _xnpal_setLastErrorString('error in xnp_get_own_public_item_id'.' at '.__LINE__.' in '.__FILE__."\n".xnp_get_last_error_string());
        $ret = RES_ERROR;
    }

    return $ret;
}

/**
 * get private item ids except public items.
 *
 * @param int   $sid   session id
 * @param int   $uid   target user id
 * @param array &$iids private item ids (result)
 *
 * @return int status belows:
 *             RES_OK, RES_NO_SUCH_SESSION, RES_DB_QUERY_ERROR, RES_ERROR
 */
function xnp_get_private_item_id($sid, $uid, &$iids)
{
    global $xoopsDB;

    $iids = array();

    if (!xnp_is_valid_session_id($sid)) {
        return RES_NO_SUCH_SESSION;
    }

    $ret = RES_ERROR;

    if (RES_OK != ($ret = _xnpal_sessionID2UID($sid, $sess_uid))) {
        return $ret;
    }

    // get public item ids.
    $sql = 'SELECT DISTINCT tlink.item_id'
        .' FROM '.$xoopsDB->prefix('xoonips_index_item_link').' AS tlink'
        .' LEFT JOIN '.$xoopsDB->prefix('xoonips_index').' AS tx ON tlink.index_id=tx.index_id'
        .' LEFT JOIN '.$xoopsDB->prefix('xoonips_item_basic').' AS ti ON tlink.item_id=ti.item_id'
        .' LEFT JOIN '.$xoopsDB->prefix('xoonips_groups_users_link').' AS tgulink ON tgulink.gid = tx.gid'
        .' WHERE open_level<='.OL_GROUP_ONLY
        .' AND certify_state='.CERTIFIED
        .' AND item_type_id !='.ITID_INDEX
        ." AND ( ti.uid=${sess_uid}"
        ." OR is_admin=1 AND tgulink.uid=${sess_uid} )";

    if ($result = $xoopsDB->query($sql)) {
        $notin = array();
        while (list($iid) = $xoopsDB->fetchRow($result)) {
            $notin[] = $iid;
        }

        // get private items except public item
        $sql = 'SELECT item_id FROM '.$xoopsDB->prefix('xoonips_item_basic')
            .' WHERE item_type_id !='.ITID_INDEX
            .' AND uid='.$sess_uid;
        if (count($notin) > 0) {
            $sql .= ' AND item_id NOT IN ( '.implode(', ', $notin).' )';
        }
        if ($result = $xoopsDB->query($sql)) {
            while (list($iid) = $xoopsDB->fetchRow($result)) {
                // result
                $iids[] = $iid;
            }
        } else {
            _xnpal_setLastErrorString("error in xnp_get_private_item_id sql=${sql} ".' at '.__LINE__.' in '.__FILE__."\n".xnp_get_last_error_string());
            $ret = RES_DB_QUERY_ERROR;
        }
        _xnpal_setLastErrorString('');
        $ret = RES_OK;
    } else {
        _xnpal_setLastErrorString('error in xnp_get_private_item_id '."sql=${sql} ".' at '.__LINE__.' in '.__FILE__."\n".xnp_get_last_error_string());
        $ret = RES_DB_QUERY_ERROR;
    }

    return $ret;
}

/**
 * get information of the item relation
 * if user does not have read privilege then the result does not conation it.
 *
 * @param int   $sid      sesion id
 * @param int   $parentid target item id
 * @param array &$itemids related item ids (result)
 *
 * @return int status belows
 *             RES_OK, RES_ERROR, RES_NO_SUCH_SESSION, RES_DB_QUERY_ERROR,
 *             RES_NO_WRITE_ACCESS_RIGHT
 */
function xnp_get_related_to($sid, $parentid, &$itemids)
{
    global $xoopsDB;

    $itemids = array();

    if (!xnp_is_valid_session_id($sid)) {
        return RES_NO_SUCH_SESSION;
    }
    if (!xnp_get_item_permission($sid, $parentid, OP_READ)) {
        return RES_NO_READ_ACCESS_RIGHT;
    }

    $ret = RES_ERROR;

    $sql = 'SELECT item_id FROM '.$xoopsDB->prefix('xoonips_related_to')
        ." WHERE parent_id=${parentid}";
    if ($result = $xoopsDB->query($sql)) {
        while (list($iid) = $xoopsDB->fetchRow($result)) {
            if (xnp_get_item_permission($sid, $iid, OP_READ)) {
                $itemids[] = $iid;
            }
        }
        _xnpal_setLastErrorString('');
        $ret = RES_OK;
    } else {
        _xnpal_setLastErrorString('error in xnp_get_related_to'.' at '.__LINE__.' in '.__FILE__."\n".xnp_get_last_error_string());
        $ret = RES_DB_QUERY_ERROR;
    }

    return $ret;
}

/**
 * 承認待ち状態のアイテムのうち，承認権限のあるアイテムのIDと登録先インデックスのIDをペアで取得します.
 * アイテムiids[i]が現在xids[i]への登録の承認待ち状態にあることを返します．.
 *
 * @param sid セッションID
 * @param xids 承認待ちアイテムの登録先インデックスのIDを受け取る配列のリファレンス
 * @param iids 承認待ちアイテムのIDを受け取る配列のリファレンス
 *
 * @return int
 * @return int
 * @return int
 * @return int
 */
function xnp_get_uncertified_link($sid, &$xids, &$iids)
{
    global $xoopsDB;

    $iids = array();

    if (!xnp_is_valid_session_id($sid)) {
        return RES_NO_SUCH_SESSION;
    }

    $ret = RES_ERROR;

    if (RES_OK != ($ret = _xnpal_sessionID2UID($sid, $sess_uid))) {
        return $ret;
    }

    $is_moderator = xnp_is_moderator($sid, $sess_uid);
    $sql = 'SELECT DISTINCT tlink.index_id, tlink.item_id'
        .' FROM '.$xoopsDB->prefix('xoonips_index_item_link').' AS tlink'
        .' LEFT JOIN '.$xoopsDB->prefix('xoonips_index').' AS tx ON tlink.index_id = tx.index_id'
        .' LEFT JOIN '.$xoopsDB->prefix('xoonips_item_basic').' AS ti ON tlink.item_id = ti.item_id'
        .($is_moderator ? '' : ' LEFT JOIN '.$xoopsDB->prefix('xoonips_groups_users_link').' AS tgulink ON tgulink.gid = tx.gid')
        .' WHERE open_level<='.OL_GROUP_ONLY
        .' AND certify_state='.CERTIFY_REQUIRED
        .' AND item_type_id !='.ITID_INDEX
        .($is_moderator ? '' : " AND is_admin=1 AND tgulink.uid=${sess_uid}");

    if ($result = $xoopsDB->query($sql)) {
        while (list($xid, $iid) = $xoopsDB->fetchRow($result)) {
            $xids[] = $xid;
            $iids[] = $iid;
        }
        _xnpal_setLastErrorString('');
        $ret = RES_OK;
    } else {
        _xnpal_setLastErrorString("error in xnp_get_uncertified_link , sql=${sql}".' at '.__LINE__.' in '.__FILE__."\n".xnp_get_last_error_string());
        $ret = RES_DB_QUERY_ERROR;
    }

    return $ret;
}

/**
 * 変更履歴ログを記録する．.
 *
 * @param sid セッションID
 * @param itemid 変更履歴を記録するアイテムのID
 * @param log ログ内容
 *
 * @return int
 * @return int
 * @return int
 * @return int
 * @return int
 */
function xnp_insert_change_log($sid, $itemid, $log)
{
    global $xoopsDB;
    if (!xnp_is_valid_session_id($sid)) {
        return RES_NO_SUCH_SESSION;
    }
    if (!xnp_get_item_permission($sid, $itemid, OP_MODIFY)) {
        return RES_NO_WRITE_ACCESS_RIGHT;
    }
    $ret = RES_ERROR;
    $now = time();
    // insert change log
    $sql = 'INSERT INTO '.$xoopsDB->prefix('xoonips_changelog')." (item_id, log_date, log) VALUES (${itemid}, UNIX_TIMESTAMP(NOW()), '".addslashes($log)."' )";
    if ($result = $xoopsDB->queryF($sql)) {
        // update last update date
        $sql = 'UPDATE '.$xoopsDB->prefix('xoonips_item_basic').' SET last_update_date=UNIX_TIMESTAMP(NOW())'
            ." WHERE item_id=${itemid}";
        $xoopsDB->queryF($sql);
        _xnpal_setLastErrorString('');
        $ret = RES_OK;
    } else {
        _xnpal_setLastErrorString('error in xnp_insert_change_log '.' at '.__LINE__.' in '.__FILE__."\n".xnp_get_last_error_string());
        $ret = RES_DB_QUERY_ERROR;
    }

    return $ret;
}

/**
 * インデックスキーワードを登録する.
 *
 * @param sid セッションID
 * @param index 登録するインデックスキーワード情報(ハッシュ)
 * @param xid 登録したインデックスのItemIDを受け取る変数のリファレンス
 *
 * @return int 成功
 */
function xnp_insert_index($sid, $index, &$xid)
{
    global $xoopsDB;

    $result = RES_ERROR;

    $result = _xnpal_sessionID2UID($sid, $uid); // sid から uid を得る
    if (RES_OK != $result) {
        return $result;
    }

    if (IID_ROOT == $index['parent_index_id']) {
        _xnpal_setLastErrorString('error in xnp_insert_index: parentXID must not Root '.' at '.__LINE__.' in '.__FILE__."\n".xnp_get_last_error_string());

        return RES_ERROR;
    }

    // parentXID のアクセス権を調べる
    $parentIndex = array();
    $result = xnp_get_index($sid, $index['parent_index_id'], $parentIndex);
    if (RES_OK == $result) {
        if (!_xnpal_isWritableInternal($sid, $uid, $parentIndex)) {
            _xnpal_setLastErrorString('in xnp_insert_index: cannot write to parentindex'.' at '.__LINE__.' in '.__FILE__."\n".xnp_get_last_error_string());
            $result = RES_ERROR;  // エラー: 親インデックスへの書き込み権限が無い。
        } else {
            if (!isset($index['titles'][DEFAULT_INDEX_TITLE_OFFSET]) || '' == trim($index['titles'][DEFAULT_INDEX_TITLE_OFFSET])) {
                // titleを空文字列にできない
                _xnpal_setLastErrorString('error in xnp_insert_index: empty title'.' at '.__LINE__.' in '.__FILE__."\n".xnp_get_last_error_string());
                $result = RES_ERROR;
            } else {
                $sortNumber = 0;
                $result = _xnpal_getNewSortNumber($index['parent_index_id'], $sortNumber);
                if (RES_OK == $result) {
                    $index['open_level'] = $parentIndex['open_level'];
                    $index['owner_gid'] = $parentIndex['owner_gid'];
                    $index['owner_uid'] = $parentIndex['owner_uid'];
                    $index['contributor_uid'] = $uid;
                    $index['sort_number'] = $sortNumber;
                    // インデックスを作成する。
                    $result = _xnpal_insertIndexInternal($sid, $index, $xid);
                } else {
                    // error: _xnpal_getNewSortNumber failed.
                    $result = RES_ERROR;
                }
            }
        }
    } else {
        _xnpal_setLastErrorString('error in xnp_insert_index: get_index failed'.' at '.__LINE__.' in '.__FILE__."\n".xnp_get_last_error_string());
        $result = RES_ERROR;
    }

    return $result;
}

/**
 * @param int $itemid
 */
function xnp_insert_item($sid, $item, &$itemid)
{
    return _xnpal_insertItemInternal($sid, $item, $itemid, false);
}

/**
 * Platformユーザ承認状態取得.
 *
 * XOOPSの管理者は常にtrueを返す.
 *
 * @param sid string
 * @param uid integer
 *
 * @return bool 承認済み
 * @return bool 未承認
 */
function xnp_is_activated($sid, $uid)
{
    global $xoopsDB;
    global $xoopsUser;

    //XOOPSの管理者は常にtrueを返す.
    //if( isset( $xoopsUser ) && $xoopsUser != null && $xoopsUser->isAdmin() ) return true;
    $sql = 'select uid from '.$xoopsDB->prefix('groups_users_link').' where groupid='.XOOPS_GROUP_ADMIN." and uid=$uid";
    $result = $xoopsDB->query($sql);
    if (false == $result) {
        return false;
    }
    if ($xoopsDB->getRowsNum($result)) {
        return true;
    }

    $ret = false;

    $sql = 'SELECT * FROM '.$xoopsDB->prefix('xoonips_users')
        ." WHERE activate=1 and uid=${uid}";
    $result = 0;
    if ($result = $xoopsDB->query($sql)) {
        if ($xoopsDB->getRowsNum($result) > 0) {
            $ret = true;
        } else {
            $ret = false;
        }
    } else {
        _xnpal_setLastErrorString("error in xnp_is_activated. ${sql}".' at '.__LINE__.' in '.__FILE__."\n".xnp_get_last_error_string());
        $ret = false;
    }

    return $ret;
}

/**
 * モデレータ権限の有無を返す.
 *
 * @param sid セッションID
 * @param uid 問い合わせたいユーザのUID
 *
 * @return bool 権限あり
 * @return bool 権限なし
 */
function xnp_is_moderator($sid, $uid)
{
    global $xoopsDB;

    if (!xnp_is_valid_session_id($sid)) {
        return false;
    }
    if (!_xnpal_uidExists($uid)) {
        return false;
    }

    $ret = false;

    $sql = 'SELECT value FROM '.$xoopsDB->prefix('xoonips_config')
        ." WHERE name='moderator_gid'";
    if ($result = $xoopsDB->query($sql)) {
        list($moderator_gid) = $xoopsDB->fetchRow($result);
        $sql = 'SELECT * from '.$xoopsDB->prefix('groups_users_link')
            ." WHERE groupid=${moderator_gid}"
            ." AND uid=${uid}";

        if (($result = $xoopsDB->query($sql))
            && $xoopsDB->getRowsNum($result) > 0
        ) {
            $ret = true;
        } else {
            $ret = false;
        }
    } else {
        $ret = false;
    }

    return $ret;
}

/**
 * validate a session id.
 * return false if given session id is not found in database.
 *
 * @param sid session id that is validated
 *
 * @return bool valid
 * @return bool invalid
 */
function xnp_is_valid_session_id($sid)
{
    global $xoopsDB;

    //XOOPSのセッション管理テーブルに，引数sidを照会する
    //sess_dataフィールド内のxoopsUserIdの有無で判別する
    $sql = 'SELECT sess_data FROM '.$xoopsDB->prefix('session')." WHERE sess_id='".addslashes($sid)."'";
    if (SID_GUEST == $sid && public_item_target_user_all()) {
        return true;
    }
    if ($result = $xoopsDB->query($sql)) {
        if ($xoopsDB->getRowsNum($result) > 0) {
            //sess_dataをunserialize
            $session_old = array();
            foreach (array_keys($_SESSION) as $key) { // avoid bug http://bugs.php.net/bug.php?id=37926
                $session_old[$key] = $_SESSION[$key];
            }
            list($sess_data) = $xoopsDB->fetchRow($result);
            if (!session_decode($sess_data)) {
                $_SESSION = array();
                foreach (array_keys($session_old) as $key) {  // avoid bug http://bugs.php.net/bug.php?id=36239
                    $_SESSION[$key] = $session_old[$key];
                }
                //デコードに失敗したので，無効と判断
                _xnpal_setLastErrorString("error can't decode session in xnp_is_valid_session");

                return false;
            }

            //ゲストか否か
            if (array_key_exists('xoopsUserId', $_SESSION)) {
                //ログインユーザである->valid
                $_SESSION = array();
                foreach (array_keys($session_old) as $key) {
                    $_SESSION[$key] = $session_old[$key];
                }

                return true;
            } else {
                //設定でゲストがアクセスOKか？
                $_SESSION = array();
                foreach (array_keys($session_old) as $key) {
                    $_SESSION[$key] = $session_old[$key];
                }

                return public_item_target_user_all();
            }
        } else {
            //セッションがDBに未登録
            //→不正なセッション
            return false;
        }
    }

    return false;
}

/**
 * インデックスにアイテムを追加する.
 * システム設定に従い，承認自動化が有効であれば追加と同時に承認を行なう.
 *
 * @param sid セッションID
 * @param xid 処理対象のインデックスのID
 * @param iid インデックスに追加したいアイテムのID
 *
 * @return int
 * @return int
 * @return int
 * @return int
 * @return int
 */
function xnp_register_item($sid, $xid, $iid)
{
    global $xoopsDB;

    if (!xnp_is_valid_session_id($sid)) {
        return RES_NO_SUCH_SESSION;
    }

    $ret = RES_ERROR;

    if (!xnp_get_index_permission($sid, $xid, OP_REGISTER)) {
        return RES_NO_WRITE_ACCESS_RIGHT;
    }

    $certfy = xnp_get_initial_certify_state_from_config();
    $sql = 'INSERT IGNORE INTO '.$xoopsDB->prefix('xoonips_index_item_link')
        ." (index_id, item_id, certify_state) values ( ${xid}, ${iid}, ".$certfy.')';
    if ($result = $xoopsDB->queryF($sql)) {
        if ($xoopsDB->getAffectedRows() > 0) {
            // 影響されたレコードがある＝INSERTした（アイテムをインデックスに新たに登録した）ということなので，last_update_dateを更新する
            // update last update date
            $sql = 'UPDATE '.$xoopsDB->prefix('xoonips_item_basic').' SET last_update_date=UNIX_TIMESTAMP(NOW())'
                ." WHERE item_id=${xid}";
            $result = $xoopsDB->queryF($sql);
            if ($result) {
                _xnpal_setLastErrorString('');
                $ret = insertMetadataEventAuto($iid);
                if (RES_OK != $ret) {
                    _xnpal_setLastErrorString('error in xnp_register_item at '.__LINE__.' in '.__FILE__."\n".xnp_get_last_error_string());
                }
            } else {
                _xnpal_setLastErrorString("error can't update last_update_date in xnp_register_item ".$xoopsDB->error().' at '.__LINE__.' in '.__FILE__);
                $ret = RES_DB_QUERY_ERROR;
            }
        } else {
            //影響された行数が０＝アイテムが既にインデックスに登録されていたので，last_update_dateの更新もしない
            $ret = RES_OK;
        }
    } else {
        _xnpal_setLastErrorString("error can't insert index-item link in xnp_register_item ${sql}".$xoopsDB->error().' at '.__LINE__.' in '.__FILE__);
        $ret = RES_DB_QUERY_ERROR;
    }

    return $ret;
}

/**
 * タイムスタンプが指定範囲日時内にあるメタデータのID(OAI-PMHのidentifierのこと)を返す.
 *
 * @param from, until  選択範囲(1970/1/1からの経過秒数)  from=0なら最も古い時刻から until=0なら現在まで
 * @param set          set条件．":"区切りのindex[インデックスID]で階層をしめしたもの．指定した以降の階層にあるデータが対象となる
 * @param startIID     startIID<=item_idであるようなitem_idのみを得る
 * @param limit        integer
 * @param iids         item_idを返す配列のリファレンス(item_idの小さいものから配列に入る)．書式は以下のとおり<br />
 *                        iids[0]['item_id'] :      item id<br />
 *                        iids[0]['item_type_id'] : item type id<br />
 *                        iids[0]['item_type_name']: <br />
 *                                                  item type name(internal). ex. xnpbook, xnpmodel<br />
 *                        iids[0]['item_type_display']:<br />
 *                                                  item type display name. ex. Book<br />
 *                        iids[0]['item_type_viewphp']:
 *                                                  item type view php file name. ex. xnpbook/include/view.php<br />
 *                        iids[0]['doi']     :      doi<br />
 *                        iids[0]['is_deleted']:    is deleted<br />
 *                        iids[0]['nijc_code']:     nijc_code<br />
 *                             .<br />
 *                        iids[n]['item_id']<br />
 *                        iids[n]['item_type_id'] : item type id<br />
 *                        iids[n][ .]
 * @param int $from
 * @param int $until
 */
function xnp_selective_harvesting($from, $until, $set, $startIID, $limit, &$iids)
{
    global $xoopsDB;

    $iids = array();

    $ret = RES_ERROR;

    if ($limit < 0) {
        return RES_ERROR;
    }

    $nijc_code = null;
    if (RES_OK != xnp_get_config_value(XNP_CONFIG_REPOSITORY_NIJC_CODE, $nijc_code)) {
        return RES_ERROR;
    }
    $sql_from = $xoopsDB->prefix('xoonips_item_status').' AS stat, '
        .$xoopsDB->prefix('xoonips_item_basic').' AS basic '
        .' LEFT JOIN '.$xoopsDB->prefix('xoonips_item_type').' AS itemtype on basic.item_type_id=itemtype.item_type_id ';
    $where = '';
    $child_xids = array();
    if ($set && 'index' != substr($set, 0, 5)) {  // item type mode
        $itid = 0;
        $sql = 'SELECT item_type_id FROM '.
            $xoopsDB->prefix('xoonips_item_type').' WHERE name='.$xoopsDB->quoteString($set);
        if (RES_OK != _xnpal_queryGetUnsignedInt('xnp_selective_harvesting', $sql, $itid)) {
            return $ret;
        }
        $where .= " itemtype.item_type_id=$itid AND ";
    } elseif ($set) { // index number mode
        $set_indexes = explode(':', $set);
        if (count($set_indexes) > 0) {
            $idx_handler = &xoonips_getormhandler('xoonips', 'index');
            $parent_xid = IID_ROOT;
            $xid = IID_ROOT; // dummy
            // check index path
            foreach ($set_indexes as $set_index) {
                // check each index pattern
                if (!preg_match('/^index([0-9]+)$/', $set_index, $matches)) {
                    return $ret;
                }
                $xid = intval($matches[1]);
                // check first index id must be /Public
                if (IID_ROOT == $parent_xid && IID_PUBLIC != $xid) {
                    return $ret;
                }
                $idx_obj = &$idx_handler->get($xid);
                // check index exists
                if (false === $idx_obj) {
                    return $ret;
                }
                $pxid = $idx_obj->get('parent_index_id');
                // check parent index id
                if ($pxid != $parent_xid) {
                    return $ret;
                }
                $parent_xid = $xid;
            }
            $where .= ' link.index_id='.$xid.' AND ';
            $sql_from .= ' INNER JOIN '.$xoopsDB->prefix('xoonips_index_item_link').' AS link on basic.item_id=link.item_id '
                   .' INNER JOIN '.$xoopsDB->prefix('xoonips_index').' AS idx on link.index_id=idx.index_id ';
        }
    }
    $sql = 'SELECT distinct stat.item_id, basic.item_type_id, basic.doi, itemtype.name as item_type_name, itemtype.display_name as item_type_display, itemtype.viewphp as item_type_viewphp, stat.is_deleted FROM '
        .$sql_from
        .' WHERE basic.item_id=stat.item_id AND '
        .' basic.item_type_id=itemtype.item_type_id AND '
        .$where;
    if (0 != $from) {
        $sql .= "${from} <= unix_timestamp(timestamp) AND ";
    }
    if (0 != $until) {
        $sql .= " unix_timestamp(timestamp) <= ${until} AND ";
    }
    $sql .=
        " stat.item_id >= ${startIID} "
        .' order by stat.item_id '
        ." limit ${limit}";
    if ($result = $xoopsDB->query($sql)) {
        while ($ar = $xoopsDB->fetchArray($result)) {
            $ar['nijc_code'] = $nijc_code;
            $iids[] = $ar;
        }
        _xnpal_setLastErrorString('');
        if (count($iids) > 0) {
            $ret = RES_OK;
        }
    }

    return $ret;
}

/**
 * インデックスキーワードの順番を入れ替える.
 *
 * @param sid セッションID
 * @param xid1 入れ替えたいインデックスキーワードのXID
 * @param xid2 入れ替えたいインデックスキーワードのXID
 *
 * @return int 成功
 */
function xnp_swap_index_sort_number($sid, $xid1, $xid2)
{
    global $xoopsDB;
    $xid1 = (int) $xid1;
    $xid2 = (int) $xid2;

    $functionName = 'xnp_swap_index_sort_number';

    $result = _xnpal_sessionID2UID($sid, $uid); // sid から uid を得る
    if (RES_OK == $result) {
        /*
          xid1, xid2 の親が異なるなら、エラー。
          xid1, xid2 の両方に書き込み権限があることを確認。
          操作順序は、
            tmp1 = x1.sort_number;
            tmp2 = x2.sort_number;
            x1.sort_number = 0; // (parent_index_id,sort_number)はuniqueなので、一旦1に変更する。
            x2.sort_number = tmp1;
            x1.sort_number = tmp2;
        */
        $index1 = array();
        $index2 = array();
        $result = xnp_get_index($sid, $xid1, $index1);
        if (RES_OK == $result) {
            $result = xnp_get_index($sid, $xid2, $index2);
            if (RES_OK == $result) {
                if ($index1['parent_index_id'] == $index2['parent_index_id']) {
                    if (_xnpal_isWritableInternal($sid, $uid, $index1) && _xnpal_isWritableInternal($sid, $uid, $index2)) {
                        $indexTable = $xoopsDB->prefix('xoonips_index');
                        $sql1 = 'UPDATE '.$indexTable." set sort_number=0 WHERE index_id=${xid1}";
                        $sql2 = 'UPDATE '.$indexTable.' set sort_number='.$index1['sort_number']." WHERE index_id={$xid2}";
                        $sql3 = 'UPDATE '.$indexTable.' set sort_number='.$index2['sort_number']." WHERE index_id={$xid1}";

                        if ($result = $xoopsDB->queryF($sql1)) {
                            if ($result = $xoopsDB->queryF($sql2)) {
                                if ($result = $xoopsDB->queryF($sql3)) {
                                }
                            }
                        }
                        if ($result) {
                            $result = RES_OK;
                        } else {
                            $result = RES_DB_QUERY_ERROR;
                        }
                    } else {
                        _xnpal_setLastErrorString('swapIndexSortNumber: not writable'.' at '.__LINE__.' in '.__FILE__."\n".xnp_get_last_error_string());
                        $result = RES_ERROR;
                    }
                } else {
                    _xnpal_setLastErrorString('swapIndexSortNumber: not brother'.' at '.__LINE__.' in '.__FILE__."\n".xnp_get_last_error_string());
                    $result = RES_ERROR;
                }
            } else {
            }
        } else {
        }
    }

    return $result;
}

/**
 * インデックスからアイテムを削除する.
 *
 * @param sid セッションID
 * @param xid 処理対象のインデックスのID
 * @param iid インデックスから削除したいアイテムのID
 *
 * @return int
 * @return int
 * @return int
 * @return int
 * @return int
 */
function xnp_unregister_item($sid, $xid, $iid)
{
    global $xoopsDB;
    $xid = (int) $xid;
    $iid = (int) $iid;

    if (!xnp_is_valid_session_id($sid)) {
        return RES_NO_SUCH_SESSION;
    }

    $ret = RES_ERROR;

    if (!xnp_get_index_permission($sid, $xid, OP_UNREGISTER)) {
        return RES_NO_WRITE_ACCESS_RIGHT;
    }

    // unregister the item.
    $sql = 'DELETE FROM '.$xoopsDB->prefix('xoonips_index_item_link')
        ." WHERE index_id=${xid} AND item_id=${iid}";
    if ($result = $xoopsDB->queryF($sql)) {
        // update last update date
        $sql = 'UPDATE '.$xoopsDB->prefix('xoonips_item_basic').' SET last_update_date=UNIX_TIMESTAMP(NOW())'
            ." WHERE item_id=${xid}";
        if ($result = $xoopsDB->queryF($sql)) {
            _xnpal_setLastErrorString('');
            $ret = RES_OK;
        } else {
            _xnpal_setLastErrorString("error can't update last_updated_date in xnp_unregister_item".' at '.__LINE__.' in '.__FILE__."\n".xnp_get_last_error_string());
            $ret = RES_DB_QUERY_ERROR;
        }
    }
    if (RES_OK == $ret) {
        $ret = insertMetadataEventAuto($iid);
        if (RES_OK == $ret) {
            _xnpal_setLastErrorString('');
        }
    }

    return $ret;
}

/**
 * アカウント情報を変更する.
 * $account['uid']に変更対象ユーザのIDをセットしてください．.
 *
 * @param sid セッションID
 * @param account 変更したいアカウント情報
 *
 * @return int
 * @return int
 * @return int
 * @return int
 * @return int
 */
function xnp_update_account($sid, $account)
{
    global $xoopsDB;

    $account['uid'] = isset($account['uid']) ? (int) $account['uid'] : 0;

    if (!xnp_is_valid_session_id($sid)) {
        return RES_NO_SUCH_SESSION;
    }
    if (!_xnpal_uidExists($account['uid'])) {
        return RES_NO_SUCH_USER;
    }

    $ret = RES_ERROR;

    $keys = array(
        'uname',           'name',           'email',    'url',
        'user_avatar',     'user_regdate',   'user_icq', 'user_from',
        'user_sig',        'user_viewemail', 'actkey',   'user_aim',
        'user_yim',        'user_msnm',      'pass',     'posts',
        'attachsig',       'rank',           'level',    'theme',
        'timezone_offset', 'last_login',     'umode',    'uorder',
        'notify_method',   'notify_mode',    'user_occ', 'bio',
        'user_intrest',    'user_mailok',
    );

    $sets = array();
    foreach ($keys as $k) {
        if (array_key_exists($k, $account)) {
            $sets[] = $k."='".addslashes($account[$k])."'";
        }
    }
    $sql = 'UPDATE '.$xoopsDB->prefix('users').' SET '.implode(',', $sets)
        .' WHERE uid = '.$account['uid'];

    //xoopsのユーザテーブルに書き込む
    if ($result = $xoopsDB->queryF($sql)) {
        _xnpal_setLastErrorString('');
        $ret = RES_OK;
    } else {
        _xnpal_setLastErrorString("error can't update users in xnp_update_account sql=${sql}".' at '.__LINE__.' in '.__FILE__."\n".xnp_get_last_error_string());

        return $ret;
    }

    //xoonipsのユーザテーブルに残りの情報を上書きする
    $keys = array(
        'activate' => 'activate',
        'address' => 'address',
        'division' => 'division',
        'tel' => 'tel',
        'company_name' => 'company_name',
        'country' => 'country',
        'zipcode' => 'zipcode',
        'fax' => 'fax',
        'notice_mail' => 'notice_mail',
        'notice_mail_since' => 'notice_mail_since',
        'private_item_number_limit' => 'item_number_limit',
        'private_index_number_limit' => 'index_number_limit',
        'private_item_storage_limit' => 'item_storage_limit',
    );
    $sets = array();
    foreach ($keys as $col => $k) {
        if (array_key_exists($k, $account)) {
            $sets[] = $col."='".addslashes($account[$k])."'";
        }
    }
    $sql = 'UPDATE '.$xoopsDB->prefix('xoonips_users').' SET '.implode(',', $sets)
        .' WHERE uid = '.$account['uid'];
    if ($result = $xoopsDB->queryF($sql)) {
        _xnpal_setLastErrorString('');
        $ret = RES_OK;
    } else {
        _xnpal_setLastErrorString("error can't update xoonips_users in xnp_update_account".' at '.__LINE__.' in '.__FILE__."\n".xnp_get_last_error_string());
        $ret = RES_DB_QUERY_ERROR;
    }

    return $ret;
}

/**
 * インデックスキーワードを変更する.
 *
 * @param sid セッションID
 * @param index 変更するインデックスキーワード
 *
 * @return RES_OK 成功
 */
/*
親XIDを書き換えるときは、以下の点に注意。
親インデックスへの書き込み権限が必要。

自分またはその子孫のXIDを親XIDとして設定することはできない
親の公開領域が変わる場合は、このインデックスとその子孫の公開領域も変わる。
*/
function xnp_update_index($sid, $newIndex)
{
    global $xoopsDB;

    $result = RES_ERROR;

    $result = _xnpal_sessionID2UID($sid, $uid); // sid から uid を得る
    if (RES_OK != $result) {
        return $result;
    }

    $oldIndex = array();
    $result = xnp_get_index($sid, $newIndex['item_id'], $oldIndex);
    if (RES_OK == $result) {
        $newParentIndex = array();
        $result = xnp_get_index($sid, $newIndex['parent_index_id'], $newParentIndex);
        if (RES_OK == $result) {
            $oldParentIndex = array();
            $result = xnp_get_index($sid, $oldIndex['parent_index_id'], $oldParentIndex);
            if (RES_OK == $result) {
                $newIndex['titles'] = isset($newIndex['titles']) && is_array($newIndex['titles']) ? $newIndex['titles'] : array();
                $newIndex['keywords'] = isset($newIndex['keywords']) && is_array($newIndex['keywords']) ? $newIndex['keywords'] : array();
                $newIndex['item_id'] = isset($newIndex['item_id']) ? (int) $newIndex['item_id'] : 0;
                $newIndex['item_type_id'] = isset($newIndex['item_type_id']) ? (int) $newIndex['item_type_id'] : 0;
                $newIndex['contributor_uid'] = isset($newIndex['contributor_uid']) ? (int) $newIndex['contributor_uid'] : 0;
                $newIndex['description'] = isset($newIndex['description']) ? (string) $newIndex['description'] : '';
                $newIndex['last_update_date'] = isset($newIndex['last_update_date']) ? (int) $newIndex['last_update_date'] : 0;
                $newIndex['creation_date'] = isset($newIndex['creation_date']) ? (int) $newIndex['creation_date'] : 0;
                $newIndex['parent_index_id'] = isset($newIndex['parent_index_id']) ? (int) $newIndex['parent_index_id'] : 0;
                $newIndex['owner_uid'] = isset($newIndex['owner_uid']) ? (int) $newIndex['owner_uid'] : 0;
                $newIndex['owner_gid'] = isset($newIndex['owner_gid']) ? (int) $newIndex['owner_gid'] : 0;
                $newIndex['open_level'] = isset($newIndex['open_level']) ? (int) $newIndex['open_level'] : 0;
                $newIndex['sort_number'] = isset($newIndex['sort_number']) ? (int) $newIndex['sort_number'] : 0;
                $result = _xnpal_updateIndexInternal($sid, $uid, $newIndex, $oldIndex, $newParentIndex, $oldParentIndex);
            } else {
            }
        } else {
        }
    } else {
    }
    if (RES_OK == $result) {
        _xnpal_setLastErrorString('');
    }

    return $result;
}

/**
 * 適切にinsertMeatadataEventを行う
 * repository         item
 * is_deleted==0  &&  public    : isCreate ? ME_CREATED : ME_MODIFIED;
 * is_deleted==0  &&  nonpublic : ME_DELETED
 * is_deleted!=0  &&  public    : ME_CREATED
 * is_deleted!=0  &&  nonpubic  : -.
 */
function insertMetadataEventAuto($iid, $isCreate = false)
{
    global $xoopsDB;

    $status = array();
    $res = xnp_get_item_status($iid, $status);
    if (RES_OK != $res) {
        if (RES_NO_SUCH_ITEM == $res) {
            $status['is_deleted'] = 1;
        } else {
            return $res;
        }
    }
    //guestからアクセスできるか？(/Publicをゲストに公開する設定が有効，且つ/Publicに属するアイテムか？)
    $value = '';
    if (RES_OK != ($res = xnp_get_config_value(XNP_CONFIG_PUBLIC_ITEM_TARGET_USER_KEY, $value))) {
        return $res;
    }
    if (XNP_CONFIG_PUBLIC_ITEM_TARGET_USER_ALL != $value) {
        $isPublic = false;
    } else {
        $sql = 'SELECT * FROM '.$xoopsDB->prefix('xoonips_index').' AS tx, '
            .$xoopsDB->prefix('xoonips_index_item_link').' AS tlink'
            ." WHERE tlink.item_id=${iid}"
            .' AND tlink.index_id=tx.index_id'
            .' AND tlink.certify_state='.CERTIFIED
            .' AND tx.open_level='.OL_PUBLIC;
        $result = $xoopsDB->query($sql);
        if ($result) {
            $isPublic = ($xoopsDB->getRowsNum($result) > 0);
        } else {
            $isPublic = false;
        }
    }

    if (0 == $status['is_deleted']) {
        if ($isPublic) {
            $me = $isCreate ? ME_CREATED : ME_MODIFIED;
        } else {
            $me = ME_DELETED;
        }
    } else {
        if ($isPublic) {
            $me = ME_CREATED;
        } else {
            _xnpal_setLastErrorString('');

            return RES_OK;
        }
    }

    $res = _xnpal_insertMetadataEvent($me, $iid);
    if (RES_OK == $res) {
        _xnpal_setLastErrorString('');
    }

    return $res;
}

/**
 * SQLを実行し，結果の行数を返す.
 */
function countResultRows($sql, &$count)
{
    global $xoopsDB;
    $result = $xoopsDB->query($sql);
    if (!$result) {
        _xnpal_setLastErrorString('error in _xnpal_uidExists, '.$xoopsDB->error()." sql=$sql".' at '.__LINE__.' in '.__FILE__."\n".xnp_get_last_error_string());

        return RES_DB_QUERY_ERROR;
    }
    $count = $xoopsDB->getRowsNum($result);
    _xnpal_setLastErrorString('');

    return RES_OK;
}

/**
 * public_item_target_userの設定値が'all'ならtrueをかえす
 * 設定値の取得に失敗した場合，'all'以外の場合はfalseをかえす.
 */
function public_item_target_user_all()
{
    $public_item_target_user_all = false;
    if (RES_OK == xnp_get_config_value(XNP_CONFIG_PUBLIC_ITEM_TARGET_USER_KEY, $value)) {
        $public_item_target_user_all = (0 == strcmp($value, XNP_CONFIG_PUBLIC_ITEM_TARGET_USER_ALL));
    }

    return $public_item_target_user_all;
}

/**
 * インデックスを削除する<br />
 *   int xnp_delete_index( string sess_id, int index_id );.
 *
 * @param sess_id XOOPSのセッションID
 * @param index_id 削除するインデックス
 *
 * @return RES_OK
 */
function xnp_delete_index($sess_id, $index_id)
{
    $index = array();
    $descXID = array();
    $affectedIIDs = array();

    return _xnpal_deleteIndexInternal($sess_id, (int) $index_id, $index, $descXID, $affectedIIDs);
}

/**
 * アイテムIDの一覧取得.
 * アクセス可能なアイテムのIDを返す.
 *
 * @param sess_id セッションID
 * @param criteria 結果の範囲指定，ソート条件指定
 * @param iids 取得結果を書き込む配列
 *
 * @return int
 * @return int
 * @return int
 * @return int
 */
function xnp_dump_item_id($sess_id, $criteria, &$iids)
{
    global $xoopsDB;

    if (!xnp_is_valid_session_id($sess_id)) {
        return RES_NO_SUCH_SESSION;
    }
    $ret = _xnpal_sessionID2UID($sess_id, $uid);
    if (RES_OK != $ret) {
        return $ret;
    }

    $sql = 'SELECT DISTINCT ti.item_id as item_id, tt.title as title';
    $sql .= ' FROM ';
    $sql .= $xoopsDB->prefix('xoonips_index_item_link').' AS tlink ';
    $sql .= ' LEFT JOIN '.$xoopsDB->prefix('xoonips_index').' AS tx ON tlink.index_id = tx.index_id';
    $sql .= ' LEFT JOIN '.$xoopsDB->prefix('xoonips_item_basic').' AS ti ON tlink.item_id = ti.item_id';
    $sql .= ' LEFT JOIN '.$xoopsDB->prefix('xoonips_item_title').' AS tt ON tt.item_id=ti.item_id';
    $sql .= ' LEFT JOIN '.$xoopsDB->prefix('xoonips_groups_users_link').' as tgulink ON tx.gid=tgulink.gid';
    $sql .= ' WHERE ( '.(public_item_target_user_all() ? '1' : '0');
    $sql .= ' AND tx.open_level='.OL_PUBLIC.' AND '.(!isset($_SESSION['xoopsUserId']) ? '1' : '0');
    $sql .= ' AND certify_state='.CERTIFIED;
    $sql .= ' OR '.(!public_item_target_user_all() ? '1' : '0');
    $sql .= ' AND tx.open_level='.OL_PUBLIC.' AND '.(isset($_SESSION['xoopsUserId']) ? '1' : '0');
    $sql .= ' AND certify_state='.CERTIFIED;
    $sql .= ' OR tx.open_level='.OL_GROUP_ONLY;
    $sql .= " AND tgulink.uid=$uid";
    $sql .= ' AND ( certify_state='.CERTIFIED;
    $sql .= (xnp_is_moderator($sess_id, $uid) ? ' OR 1' : ' OR 0'); //モデレータならOR 1，それ以外は OR 0
    $sql .= ' OR tgulink.is_admin=1 )'; //グループ管理者か？
    if (UID_GUEST != $uid) {
        $sql .= " AND tgulink.uid=$uid";
    }
    $sql .= ' OR tx.open_level='.OL_PRIVATE;
    $sql .= " AND tx.uid=$uid";
    $sql .= ' OR tx.uid IS NULL ';
    $sql .= ' AND tx.open_level='.OL_PUBLIC;
    $sql .= ' AND ( certify_state='.CERTIFIED;
    $sql .= (xnp_is_moderator($sess_id, $uid) ? ' OR 1 )' : ' OR 0 )'); //モデレータならOR 1，それ以外は OR 0
    $sql .= (xnp_is_moderator($sess_id, $uid) ? ' OR 1' : ' OR 0'); //モデレータならOR 1，それ以外は OR 0
    $sql .= ') ';
    $sql .= ' AND ti.item_type_id != '.ITID_INDEX;
    $sql .= ' AND tt.title_id='.DEFAULT_ORDER_TITLE_OFFSET;
    $sql .= xnp_criteria2str($criteria);

    $result = $xoopsDB->query($sql);
    if (!$result) {
        _xnpal_setLastErrorString('error in dumpItemID, '.$xoopsDB->error()." sql=$sql".' at '.__LINE__.' in '.__FILE__."\n".xnp_get_last_error_string());

        return RES_DB_QUERY_ERROR;
    }

    $iids = array();
    while (list($iid) = $xoopsDB->fetchRow($result)) {
        $iids[] = $iid;
    }

    _xnpal_setLastErrorString('');

    return RES_OK;
}

/**
 * criteria_tで指定された範囲のユーザIDを返す.
 * uidsにユーザIDの配列を確保してそこに書き込む.
 *
 * @param sess_id セッションID
 * @param criteria 結果の範囲指定，ソート条件指定
 * @param uids ユーザのUIDの出力先配列
 *
 * @return int
 * @return int
 * @return int
 * @return int
 *
 * @see freeUID
 */
function xnp_dump_uids($sess_id, $criteria, &$uids)
{
    global $xoopsDB;
    if (!xnp_is_valid_session_id($sess_id)) {
        return RES_NO_SUCH_SESSION;
    }

    $sql = 'SELECT uid FROM '.$xoopsDB->prefix('xoonips_users');
    $sql .= ' '.xnp_criteria2str($criteria);
    $result = $xoopsDB->query($sql);
    if ($result) {
        $uids = array();
        while (list($uid) = $xoopsDB->fetchRow($result)) {
            $uids[] = $uid;
        }
        _xnpal_setLastErrorString('');

        return RES_OK;
    } else {
        _xnpal_setLastErrorString('error in xnp_dump_uids, '.$xoopsDB->error()." sql=$sql".' at '.__LINE__.' in '.__FILE__."\n".xnp_get_last_error_string());

        return RES_DB_QUERY_ERROR;
    }
}

/**
 * アイテムIDの中から，公開のものを抽出.
 *
 * int xnp_extract_public_item_id( string sess_id, array iids, array public_iids )
 *
 * @param sess_id セッションID
 * @param iids item_idの配列
 * @param public_iids 取得結果を受け取る配列
 *
 * @return int
 * @return int
 * @return int
 * @return int
 */
function xnp_extract_public_item_id($sess_id, $iids, &$public_iids)
{
    global $xoopsDB;
    $public_iids = array();

    if (!xnp_is_valid_session_id($sess_id)) {
        return RES_NO_SUCH_SESSION;
    }

    $ret = _xnpal_sessionID2UID($sess_id, $uid);
    if (RES_OK != $ret) {
        return $ret;
    }
    $iids_str = _xnpal_getCsvStr($iids);

    $public_iids = array();
    if (0 == count($iids)) {
        return RES_OK;
    }
    $sql =
      'select ti.item_id, count(tx.index_id) '.
      '  from      '.$xoopsDB->prefix('xoonips_item_basic').'      as ti   '.
      '  left join '.$xoopsDB->prefix('xoonips_index_item_link').' as txil on ti.item_id=txil.item_id and txil.certify_state = '.CERTIFIED.
      '  left join '.$xoopsDB->prefix('xoonips_index').'           as tx   on txil.index_id=tx.index_id and tx.open_level = '.OL_PUBLIC.
      '  where '.
      " ti.item_id in ( $iids_str )".
      '  group by ti.item_id ';

    $result = $xoopsDB->query($sql);
    if (!$result) {
        _xnpal_setLastErrorString('error in xnp_extract_public_item_id, '.$xoopsDB->error()." sql=$sql".' at '.__LINE__.' in '.__FILE__."\n".xnp_get_last_error_string());

        return RES_DB_QUERY_ERROR;
    }
    while (list($iid, $ct) = $xoopsDB->fetchRow($result)) {
        if (0 != $ct) {
            $public_iids[] = $iid;
        }
    }

    return RES_OK;
}

/**
 * アカウント情報を得る。<br />.
 */
function xnp_get_account($sess_id, $uid, &$account)
{
    $accounts = array();
    $account = array();
    $result = xnp_get_accounts($sess_id, array((int) $uid), array(), $accounts);
    if (isset($accounts[0])) {
        $account = $accounts[0];
    }

    return $result;
}

/**
 * 条件に一致するアカウントの情報を得る。<br />.
 *
 * @param int[] $uids
 */
function xnp_get_accounts($sess_id, $uids, $criteria, &$accounts)
{
    global $xoopsDB;
    $accounts = array();
    if (!xnp_is_valid_session_id($sess_id)) {
        return RES_NO_SUCH_SESSION;
    }

    $accounts = array();
    if (0 == count($uids)) {
        return RES_OK;
    }

    $sql = 'SELECT u1.uid, u1.name, u1.uname, u1.email, u1.url, u1.user_avatar, u1.user_regdate, u1.user_icq, u1.user_from, u1.user_sig, u1.user_viewemail, u1.actkey, u1.user_aim, u1.user_yim, u1.user_msnm, u1.pass, u1.posts, u1.attachsig, u1.rank, u1.level, u1.theme, u1.timezone_offset, u1.last_login, u1.umode, u1.uorder, u1.notify_method, u1.notify_mode, u1.user_occ, u1.bio, u1.user_intrest, u1.user_mailok, u2.activate, u2.address, u2.division, u2.tel, u2.company_name, u2.country, u2.zipcode, u2.fax, u2.notice_mail, u2.notice_mail_since, u2.private_index_id, u2.private_item_number_limit, u2.private_index_number_limit, u2.private_item_storage_limit '.
     ' FROM '.$xoopsDB->prefix('users').' AS u1, '.$xoopsDB->prefix('xoonips_users').' AS u2 '.
     ' WHERE u1.uid = u2.uid ';
    if (count($uids)) {
        $sql .= ' AND u1.uid in ( '._xnpal_getCsvStr($uids).' ) ';
    }
    $sql .= xnp_criteria2str($criteria);
    $sql .= ' ORDER BY u1.uname ASC';

    $result = $xoopsDB->query($sql);
    if (!$result) {
        _xnpal_setLastErrorString('error in getAccounts, '.$xoopsDB->error()." sql=$sql".' at '.__LINE__.' in '.__FILE__."\n".xnp_get_last_error_string());

        return RES_DB_QUERY_ERROR;
    }

    while ($row = $xoopsDB->fetchRow($result)) {
        $account = array();
        $account['uid'] = $row[0];
        $account['name'] = $row[1];
        $account['uname'] = $row[2];
        $account['email'] = $row[3];
        $account['url'] = $row[4];
        $account['user_avatar'] = $row[5];
        $account['user_regdate'] = $row[6];
        $account['user_icq'] = $row[7];
        $account['user_from'] = $row[8];
        $account['user_sig'] = $row[9];
        $account['user_viewemail'] = $row[10];
        $account['actkey'] = $row[11];
        $account['user_aim'] = $row[12];
        $account['user_yim'] = $row[13];
        $account['user_msnm'] = $row[14];
        $account['pass'] = $row[15];
        $account['posts'] = $row[16];
        $account['attachsig'] = $row[17];
        $account['rank'] = $row[18];
        $account['level'] = $row[19];
        $account['theme'] = $row[20];
        $account['timezone_offset'] = $row[21];
        $account['last_login'] = $row[22];
        $account['umode'] = $row[23];
        $account['uorder'] = $row[24];
        $account['notify_method'] = $row[25];
        $account['notify_mode'] = $row[26];
        $account['user_occ'] = $row[27];
        $account['bio'] = $row[28];
        $account['user_interest'] = $row[29];
        $account['user_mailok'] = $row[30];
        $account['activate'] = $row[31];
        $account['address'] = $row[32];
        $account['division'] = $row[33];
        $account['tel'] = $row[34];
        $account['company_name'] = $row[35];
        $account['country'] = $row[36];
        $account['zipcode'] = $row[37];
        $account['fax'] = $row[38];
        $account['notice_mail'] = $row[39];
        $account['notice_mail_since'] = $row[40];
        $account['private_index_id'] = $row[41];
        $account['item_number_limit'] = $row[42];
        $account['index_number_limit'] = $row[43];
        $account['item_storage_limit'] = $row[44];
        $accounts[] = $account;
    }
    _xnpal_setLastErrorString('');

    return RES_OK;
}

/**
 * 全てのインデックスを得る<br />
 *   int xnp_get_all_indexes( string sess_id, array criteria, array indexes );.
 *
 * @param sid XOOPSのセッションID
 * @param criteria 結果の範囲指定，ソート条件指定
 * @param indexes インデックスの一覧を返す配列
 *
 * @return int
 */
function xnp_get_all_indexes($sess_id, $criteria, &$indexes)
{
    $indexes = array();

    $result = _xnpal_sessionID2UID($sess_id, $uid); // sid から uid を得る
    if (RES_OK == $result) {
        $result = _xnpal_getIndexesInternal($sess_id, false, $uid, $indexes, xnp_criteria2str($criteria));
    }

    return $result;
}

/**
 * アイテムの承認状態を取得します．.
 *
 * @refer certify_t
 *
 * @param sid セッションID
 * @param xid 対象アイテムが登録されているインデックスのID
 * @param iid 対象アイテムのID
 * @param state integer
 *
 * @return int
 * @return int
 */
function xnp_get_certify_state($sess_id, $xid, $iid, &$state)
{
    $xid = (int) $xid;
    $iid = (int) $iid;

    global $xoopsDB;
    $sql = 'SELECT certify_state'.
      ' FROM '.$xoopsDB->prefix('xoonips_index_item_link').
      " WHERE item_id = $iid ".
        " AND index_id = $xid ";
    $ret = _xnpal_queryGetUnsignedInt('xnp_get_certify_state', $sql, $state);

    return $ret;
}

/**
 * アイテムの変更履歴ログを取得する．.
 *
 * @param sess_id セッションID
 * @param itemid 変更履歴を取得するアイテムのID
 * @param logs ログ内容を受け取る配列
 *
 * @return int
 * @return int
 * @return int
 * @return int
 * @return int
 */
function xnp_get_change_logs($sess_id, $item_id, &$logs)
{
    global $xoopsDB;
    $logs = array();

    $item_id = (int) $item_id;

    if (!xnp_is_valid_session_id($sess_id)) {
        return RES_NO_SUCH_SESSION;
    }
    if (!xnp_get_item_permission($sess_id, $item_id, OP_READ)) {
        return RES_NO_READ_ACCESS_RIGHT;
    }

    $sql = 'SELECT log_date, log FROM '.$xoopsDB->prefix('xoonips_changelog').
        " WHERE item_id=$item_id ORDER BY log_date DESC, log_id DESC";
    $result = $xoopsDB->query($sql);
    if (!$result) {
        _xnpal_setLastErrorString('error in getChangeLogs '.$xoopsDB->error()." sql=$sql".' at '.__LINE__.' in '.__FILE__."\n".xnp_get_last_error_string());

        return RES_DB_QUERY_ERROR;
    }

    $logs = array();
    while ($row = $xoopsDB->fetchArray($result)) {
        $logs[] = $row;
    }

    _xnpal_setLastErrorString('');

    return RES_OK;
}

/**
 * 設定名keyに対応する値をvauleに取得する．.
 *
 * @param key string
 * @param value 設定値を受け取る変数
 *
 * @return int
 * @return int
 * @return int
 */
function xnp_get_config_value($key, &$value)
{
    global $xoopsDB;
    $esckey = addslashes($key);
    $sql = 'select value from '.$xoopsDB->prefix('xoonips_config')." where name = '$esckey' ";

    $result = $xoopsDB->query($sql);
    if (!$result) {
        _xnpal_setLastErrorString('error in xnp_get_config_value '.$xoopsDB->error()." sql=$sql".' at '.__LINE__.' in '.__FILE__."\n".xnp_get_last_error_string());

        return RES_DB_QUERY_ERROR;
    }
    $value = null;
    if ($xoopsDB->getRowsNum($result) > 0) {
        list($value) = $xoopsDB->fetchRow($result);
        _xnpal_setLastErrorString('');

        return RES_OK;
    } else {
        _xnpal_setLastErrorString("error in xnp_get_config_value, no such key '$key'".' at '.__LINE__.' in '.__FILE__."\n".xnp_get_last_error_string());

        return RES_ERROR;
    }
}

/**
 * インデックスを取得する<br />
 *   int xnp_get_index( string sess_id, int index_id, array index );.
 *
 * @param sess_id XOOPSのセッションID
 * @param index_id 取得するインデックスのID
 * @param index 取得結果を受け取る連想配列
 *
 * @return int
 */
function xnp_get_index($sess_id, $index_id, &$index)
{
    $index_id = (int) $index_id;
    $index = array();

    $result = _xnpal_sessionID2UID($sess_id, $uid); // sid から uid を得る
    if (RES_OK == $result) {
        $cond = " index_id = $index_id ";
        $indexes = array();
        $result = _xnpal_getIndexesInternal($sess_id, $cond, $uid, $indexes, '');
        if (RES_OK == $result && !isset($indexes[0])) {
            _xnpal_setLastErrorString("error can't found index(id=${index_id}) in xnp_get_index");
            $result = RES_ERROR;
        } else {
            $index = $indexes[0];
        }
    }

    return $result;
}

function xnp_get_index_id_by_item_id($sess_id, $item_id, &$xids)
{
    $item_id = (int) $item_id;
    $xids = array();

    global $xoopsDB;
    if (!xnp_is_valid_session_id($sess_id)) {
        return RES_NO_SUCH_SESSION;
    }
    if (!xnp_get_item_permission($sess_id, $item_id, OP_READ)) {
        return RES_NO_READ_ACCESS_RIGHT;
    }

    $sql = 'SELECT index_id FROM '.$xoopsDB->prefix('xoonips_index_item_link').
        " WHERE item_id=$item_id";

    $result = $xoopsDB->query($sql);
    if (!$result) {
        _xnpal_setLastErrorString('error in getIndexIDByItemID '.$xoopsDB->error()." sql=$sql".' at '.__LINE__.' in '.__FILE__."\n".xnp_get_last_error_string());

        return RES_DB_QUERY_ERROR;
    }
    while (list($xid) = $xoopsDB->fetchRow($result)) {
        $xids[] = $xid;
    }
    _xnpal_setLastErrorString('');

    return RES_OK;
}

/**
 * あるインデックスの全ての子インデックスを得る<br />
 *   int xnp_get_indexes( string sess_id, int parent_xid, array criteria, array indexes );.
 *
 * @param sess_id XOOPSのセッションID
 * @param parent_xid 親のindexID
 * @param criteria 結果の範囲指定，ソート条件指定
 * @param indexes 結果を受け取る配列
 *
 * @return int
 */
function xnp_get_indexes($sess_id, $parent_xid, $criteria, &$indexes)
{
    $parent_xid = (int) $parent_xid;
    $indexes = array();

    $result = _xnpal_sessionID2UID($sess_id, $uid); // sid から uid を得る
    if (RES_OK == $result) {
        $cond = "parent_index_id = $parent_xid";
        $result = _xnpal_getIndexesInternal($sess_id, $cond, $uid, $indexes, xnp_criteria2str($criteria));
    }
    _xnpal_setLastErrorString('');

    return $result;
}

/**
 * アイテム情報取得.
 *
 * @param sess_id セッションID
 * @param iid 取得したいアイテムのID
 * @param item 結果のアイテム情報を受け取る連想配列
 *
 * @return int
 * @return int
 * @return int
 * @return int
 * @return int
 */
function xnp_get_item($sess_id, $iid, &$item)
{
    $items = array();
    $item = array();
    $result = xnp_get_items($sess_id, array((int) $iid), array(), $items);

    if (0 == count($items)) {
        return RES_NO_SUCH_ITEM;
    }
    $item = $items[0];

    return $result;
}

/**
 * Readアクセス可能なインデックス毎の、直下のアイテム数を得る<br />.
 */
function xnp_get_item_count_group_by_index($sess_id, &$counts)
{
    global $xoopsDB;
    $counts = array();

    $ret = _xnpal_sessionID2UID($sess_id, $uid);
    if (RES_OK != $ret) {
        return $ret;
    }

    $indexItemLinkTable = $xoopsDB->prefix('xoonips_index_item_link');
    $indexTable = $xoopsDB->prefix('xoonips_index');
    $itemTable = $xoopsDB->prefix('xoonips_item_basic');
    $groupsUsersLinkTable = $xoopsDB->prefix('xoonips_groups_users_link');

    // todo: item_type_idのチェックを外す
    if (xnp_is_moderator($sess_id, $uid)) {
        $sql = "SELECT index_id, COUNT(*) from $indexItemLinkTable AS tl ".
            " LEFT JOIN $itemTable AS ti on ti.item_id=tl.item_id ".
            ' WHERE ti.item_type_id <> '.ITID_INDEX.
            ' GROUP BY index_id';
    } else {
        $certified = ' tl.certify_state='.CERTIFIED;
        $sql = 'SELECT  tx.index_id, COUNT(tl.index_id) '.
            "  FROM      $indexTable AS tx".
            "  LEFT JOIN $groupsUsersLinkTable AS tgl ON tx.gid=tgl.gid AND tgl.uid=$uid".
            "  LEFT JOIN $indexItemLinkTable AS tl ON tx.index_id=tl.index_id ".
            "  LEFT JOIN $itemTable AS ti ON ti.item_id=tl.item_id ".
            '  WHERE '.
            '   (tx.open_level='.OL_PUBLIC.' AND '.
                     "($certified OR ti.uid=$uid )".
            ' OR tx.open_level='.OL_GROUP_ONLY.' AND tgl.uid IS NOT NULL AND '.
                     "($certified OR ti.uid=$uid OR tgl.is_admin=1 )".
            ' OR tx.open_level='.OL_PRIVATE." AND tx.uid=$uid".
            '  ) AND ti.item_type_id <> '.ITID_INDEX.
            '  GROUP BY tx.index_id';
    }
    $result = $xoopsDB->query($sql);
    if (!$result) {
        _xnpal_setLastErrorString('error in getItemCountGroupByIndex '.$xoopsDB->error()." sql=$sql".' at '.__LINE__.' in '.__FILE__."\n".xnp_get_last_error_string());

        return RES_DB_QUERY_ERROR;
    }
    $counts = array();
    while (list($xid, $count) = $xoopsDB->fetchRow($result)) {
        $counts[$xid] = $count;
    }
    _xnpal_setLastErrorString('');

    return RES_OK;
}

/**
 * アイテムへのアクセス権限をチェックする.
 *
 * @refer itemop_t
 *
 * @param sess_id セッションID
 * @param iid チェック対象となるアイテムのID
 * @param op integer
 *
 * @return bool 権限あり
 * @return bool 権限なし
 */
function xnp_get_item_permission($sess_id, $iid, $op)
{
    $iid = (int) $iid;

    global $xoopsDB;
    $uid = 0;
    if (RES_OK != _xnpal_sessionID2UID($sess_id, $uid)) {
        return false;
    }
    if (OP_READ == $op) {
        $sql = 'SELECT DISTINCT tlink.item_id FROM '.$xoopsDB->prefix('xoonips_index_item_link').' AS tlink';
        $sql .= ' LEFT JOIN '.$xoopsDB->prefix('xoonips_index').' AS tx ON tlink.index_id = tx.index_id';
        $sql .= ' LEFT JOIN '.$xoopsDB->prefix('xoonips_item_basic').' AS ti ON tlink.item_id = ti.item_id';
        $sql .= ' LEFT JOIN '.$xoopsDB->prefix('xoonips_groups_users_link').' as tgulink ON tx.gid=tgulink.gid';
        $sql .= ' WHERE ( '.(public_item_target_user_all() ? '1' : '0');
        $sql .= ' AND tx.open_level='.OL_PUBLIC." AND $uid=".UID_GUEST;
        $sql .= ' AND certify_state='.CERTIFIED;
        $sql .= ' OR '.(!public_item_target_user_all() ? '1' : '0');
        $sql .= ' AND tx.open_level='.OL_PUBLIC." AND $uid<>".UID_GUEST;
        $sql .= ' AND certify_state='.CERTIFIED;
        $sql .= ' OR tx.open_level='.OL_GROUP_ONLY;
        $sql .= " AND tgulink.uid=$uid";
        $sql .= ' AND ( certify_state='.CERTIFIED;
        $sql .= (xnp_is_moderator($sess_id, $uid) ? ' OR 1' : ' OR 0'); //モデレータならOR 1，それ以外は OR 0
        $sql .= ' OR tgulink.is_admin=1 )'; //グループ管理者か？
        if (UID_GUEST != $uid) {
            $sql .= " AND tgulink.uid=$uid";
        }
        $sql .= ' OR tx.open_level='.OL_PRIVATE;
        $sql .= " AND tx.uid=$uid";
        $sql .= ' OR '.(xnp_is_moderator($sess_id, $uid) ? '1' : '0');
        $sql .= ' OR tx.uid IS NULL ';
        $sql .= ' AND tx.open_level='.OL_PUBLIC;
        $sql .= ' AND ( certify_state='.CERTIFIED;
        $sql .= (xnp_is_moderator($sess_id, $uid) ? ' OR 1 )' : ' OR 0 )'); //モデレータならOR 1，それ以外は OR 0
        $sql .= (xnp_is_moderator($sess_id, $uid) ? ' OR 1' : ' OR 0'); //モデレータならOR 1，それ以外は OR 0
        $sql .= ") AND tlink.item_id=$iid ";
        if (RES_OK == _xnpal_queryGetUnsignedInt('getItemPermission', $sql, $item_id)) {
            return $item_id == $iid;
        }
    } elseif (OP_MODIFY == $op || OP_DELETE == $op) {
        // modifying items by moderator is permitted then returns true;
        if (OP_MODIFY == $op && xnp_is_moderator($sess_id, $uid)
            && RES_OK == xnp_get_config_value('moderator_modify_any_items', $val) && 'on' == $val
        ) {
            return true;
        }

        // modifying items by group owner is permitted then returns true;
        $item_compo_handler = &xoonips_getormcompohandler('xoonips', 'item');
        if (OP_MODIFY == $op
            && $item_compo_handler->getPerm($iid, $uid, 'write')
        ) {
            return true;
        }

        //TODO 条件追加：todo自分のアイテムでも承認待ち状態なら編集・削除できない
        $sql = 'SELECT item_id FROM '.$xoopsDB->prefix('xoonips_item_basic');
        $sql .= " WHERE uid=$uid";
        $sql .= " AND item_id=$iid";
        if (RES_OK == _xnpal_queryGetUnsignedInt('getItemPermission', $sql, $item_id)) {
            return $item_id == $iid;
        }
    }

    return false;
}

/**
 * インデックスへのアクセス権限をチェックする.
 *
 * @see indexop_t
 *
 * @param sid セッションID
 * @param xid チェック対象となるインデックスのID
 * @param op integer
 *
 * @return bool 権限あり
 * @return bool 権限なし
 */
function xnp_get_index_permission($sess_id, $xid, $op)
{
    global $xoopsDB;
    $xid = (int) $xid;

    if (IID_ROOT == $xid) {
        return false;
    }

    if (_xnpal_isModeratorBySession($sess_id)) {
    } elseif (RES_OK == _xnpal_sessionID2UID($sess_id, $uid)) {
        $sql = 'SELECT index_id FROM '.$xoopsDB->prefix('xoonips_index as tx');
        $sql .= ' LEFT JOIN '.$xoopsDB->prefix('xoonips_users').' AS tuser ON tx.uid=tuser.uid';
        $sql .= ' LEFT JOIN '.$xoopsDB->prefix('xoonips_groups').' AS tgroup ON tx.gid=tgroup.gid';
        $sql .= ' LEFT JOIN '.$xoopsDB->prefix('xoonips_groups_users_link').' AS tlink ON tx.gid=tlink.gid';
        $sql .= ' WHERE ( tx.open_level=1';
        $sql .= " OR tx.open_level=2 AND tlink.uid=$uid";
        $sql .= " OR tx.open_level=3 AND tx.uid=$uid )";
        $sql .= " AND index_id=$xid";
        if (RES_OK == _xnpal_queryGetUnsignedInt('getIndexPermission', $sql, $tmp)
            && $tmp == $xid
        ) {
        } else {
            return false;
        }
    } else {
        return false;
    }

    return true;
}

/**
 * item_statusを得る.
 *
 * int xnp_get_item_status( int iid, array status )
 *
 * @param iid  item ID
 * @param status  状態を受け取る連想配列。以下のキーを含む。 created_timestamp, modified_timestamp, deleted_timestamp, is_deleted
 *
 * @return int
 * @return int
 */
function xnp_get_item_status($iid, &$status)
{
    $iid = (int) $iid;

    global $xoopsDB;
    $sql = 'select created_timestamp, modified_timestamp, deleted_timestamp, is_deleted from '
     .$xoopsDB->prefix('xoonips_item_status')." where item_id=$iid";
    $result = $xoopsDB->query($sql);
    if (!$result) {
        _xnpal_setLastErrorString('error in getMetadataEvent '.$xoopsDB->error()." sql=$sql".' at '.__LINE__.' in '.__FILE__."\n".xnp_get_last_error_string());

        return RES_DB_QUERY_ERROR;
    } elseif (0 == $xoopsDB->getRowsNum($result)) {
        return RES_NO_SUCH_ITEM;
    }

    list($status['created_timestamp'],
          $status['modified_timestamp'],
          $status['deleted_timestamp'],
          $status['is_deleted']) = $xoopsDB->fetchRow($result);
    _xnpal_setLastErrorString('');

    return RES_OK;
}

/**
 * @param types
 *
 * @return int
 * @return int
 * @return int
 * @return int
 * @return int
 */
function xnp_get_item_types(&$types)
{
    global $xoopsDB;
    $sql = 'SELECT item_type_id, name, mid, display_name, viewphp ';
    $sql .= ' FROM '.$xoopsDB->prefix('xoonips_item_type').' order by item_type_id';
    $result = $xoopsDB->query($sql);
    if (!$result) {
        _xnpal_setLastErrorString('error in getItemType '.$xoopsDB->error()." sql=$sql".' at '.__LINE__.' in '.__FILE__."\n".xnp_get_last_error_string());

        return RES_DB_QUERY_ERROR;
    }
    $types = array();
    while ($row = $xoopsDB->fetchArray($result)) {
        $types[] = $row;
    }
    _xnpal_setLastErrorString('');

    return RES_OK;
}

/**
 * アイテム情報取得.
 *
 * @param sid セッションID
 * @param iids 取得したいアイテムのIDの配列
 * @param criteria 結果の範囲指定，ソート条件指定
 * @param items 検索結果のを書き込む配列
 *
 * @return int
 * @return int
 * @return int
 * @return int
 */
function xnp_get_items($sess_id, $iids, $criteria, &$items)
{
    global $xoopsDB;
    $items = array();

    if (!xnp_is_valid_session_id($sess_id)) {
        return RES_NO_SUCH_SESSION;
    }

    $ret = _xnpal_sessionID2UID($sess_id, $uid);
    if (RES_OK != $ret) {
        return $ret;
    }

    $items = array();
    if (!isset($iids) || 0 == count($iids)) {
        return RES_OK;
    }

    $sql = 'SELECT DISTINCT ti.item_id as item_id, item_type_id, tt.title as title, description, doi, ti.uid as uid, creation_date, last_update_date, publication_year, publication_month, publication_mday, lang ';
    $sql .= ' FROM ';
    $sql .= $xoopsDB->prefix('xoonips_index_item_link').' AS tlink';
    $sql .= ' LEFT JOIN '.$xoopsDB->prefix('xoonips_index').' AS tx ON tlink.index_id = tx.index_id';
    $sql .= ' LEFT JOIN '.$xoopsDB->prefix('xoonips_item_basic').' AS ti ON tlink.item_id = ti.item_id';
    $sql .= ' LEFT JOIN '.$xoopsDB->prefix('xoonips_item_title').' AS tt ON tt.item_id=ti.item_id';
    $sql .= ' LEFT JOIN '.$xoopsDB->prefix('xoonips_groups_users_link').' as tgulink ON tx.gid=tgulink.gid';
    $sql .= ' WHERE tlink.item_id IN ( '._xnpal_getCsvStr($iids).' )';
    $sql .= ' AND title_id='.DEFAULT_ORDER_TITLE_OFFSET;
    $sql .= xnp_criteria2str($criteria);

    $result = $xoopsDB->query($sql);
    if (!$result) {
        _xnpal_setLastErrorString('error in xnp_get_items '.$xoopsDB->error());

        return RES_DB_QUERY_ERROR;
    }
    $items_buf = array();
    $ordered_ids = array(); //array of sorted item_id(s) to sort $items_buf in the end of this function
    $item_compo_handler = &xoonips_getormcompohandler('xoonips', 'item');
    while ($row = $xoopsDB->fetchArray($result)) {
        if (!$item_compo_handler->getPerm($row['item_id'], $uid, 'read')) {
            continue;
        }
        $items_buf[$row['item_id']] = $row;
        $items_buf[$row['item_id']]['titles'] = array();
        $items_buf[$row['item_id']]['keywords'] = array();
        $ordered_ids[] = $row['item_id'];
    }

    //get titles of selected item
    if (count($items_buf) > 0) {
        $sql = 'SELECT item_id, title FROM '.$xoopsDB->prefix('xoonips_item_title')
            .' WHERE item_id IN ( '.implode(',', array_keys($items_buf)).' ) ORDER BY item_id ASC, title_id ASC';
        $result = $xoopsDB->query($sql);
        if (!$result) {
            _xnpal_setLastErrorString('error in xnp_get_items '.$xoopsDB->error()." sql=$sql".' at '.__LINE__.' in '.__FILE__."\n".xnp_get_last_error_string());

            return RES_DB_QUERY_ERROR;
        }
        while ($row = $xoopsDB->fetchArray($result)) {
            $items_buf[$row['item_id']]['titles'][] = $row['title'];
        }
        //get keywords of selected item
        $sql = 'SELECT item_id, keyword FROM '.$xoopsDB->prefix('xoonips_item_keyword')
            .' WHERE item_id IN ( '.implode(',', array_keys($items_buf)).' ) ORDER BY item_id ASC, keyword_id ASC';
        $result = $xoopsDB->query($sql);
        if (!$result) {
            _xnpal_setLastErrorString('error in xnp_get_items '.$xoopsDB->error()." sql=$sql".' at '.__LINE__.' in '.__FILE__."\n".xnp_get_last_error_string());

            return RES_DB_QUERY_ERROR;
        }
        while ($row = $xoopsDB->fetchArray($result)) {
            $items_buf[$row['item_id']]['keywords'][] = $row['keyword'];
        }
    }

    // convert the associative array(index_buf) to the array(indexes) (keep order specified by criteriaString)
    foreach ($ordered_ids as $id) {
        $items[] = $items_buf[$id];
    }

    _xnpal_setLastErrorString('');

    return RES_OK;
}
