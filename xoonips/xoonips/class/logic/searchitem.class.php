<?php

// $Revision: 1.1.4.1.2.11 $
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

include_once XOOPS_ROOT_PATH.'/modules/xoonips/class/base/logic.class.php';

/**
 * subclass of XooNIpsLogic(searchItem).
 */
class XooNIpsLogicSearchItem extends XooNIpsLogic
{
    public function getSearchCacheIdByQuery($query)
    {
        if (isset($_SESSION['xoonips_search_cache']) && isset($_SESSION['xoonips_search_cache']["_$query"])) {
            return $_SESSION['xoonips_search_cache']["_$query"];
        }

        return false;
    }

    public function putSearchCache($query, $search_cache_id)
    {
        if (!isset($_SESSION['xoonips_search_cache'])) {
            $_SESSION['xoonips_search_cache'] = array();
        }
        $_SESSION['xoonips_search_cache']["_$query"] = $search_cache_id;
    }

    public function deleteSearchCache($search_cache_id)
    {
        foreach (array_keys($_SESSION['xoonips_search_cache']) as $key) {
            if ($_SESSION['xoonips_search_cache'][$key] == $search_cache_id) {
                unset($_SESSION['xoonips_search_cache'][$key]);

                return;
            }
        }
    }

    /**
     * execute searchItem.
     *
     * @param[in]  $vars[0] session ID
     * @param[in]  $vars[1] query string
     * @param[in]  $vars[2] offset of the first row to return (The offset of the initial row is 0)
     * @param[in]  $vars[3] maximum number of rows to return (0:all rows)
     * @param[in]  $vars[4] sort key('title'|'ext_id'|'last_modified_date'|'registration_date'|'creation_date')
     * @param[in]  $vars[5] sort order('asc'|'desc')
     * @param[out] $response->result true:success, false:failed
     * @param[out] $response->error  error information
     * @param[out] $response->success['count'] number of matched item
     * @param[out] $response->success[n] search result
     * @param[out] $response->success[n]['id'] item id of matched item
     * @param[out] $response->success[n]['matchfor'] bit flag represents what part of item matched
     */
    public function execute(&$vars, &$response)
    {
        // parameter check
        $error = &$response->getError();
        if (count($vars) > 6) {
            $error->add(XNPERR_EXTRA_PARAM);
        }
        if (count($vars) < 6) {
            $error->add(XNPERR_MISSING_PARAM);
        }

        if (isset($vars[0]) && strlen($vars[0]) > 32) {
            $error->add(XNPERR_INVALID_PARAM, 'too long parameter 1');
        }
        $ar = explode(':', $vars[1]);
        if (count($ar) < 2 || !in_array($ar[0], array(
            'index',
            'keyword',
        ))) {
            $error->add(XNPERR_INVALID_PARAM, 'bad query parameter 2');
        }
        if (!is_int($vars[2]) && !ctype_digit($vars[2])) {
            $error->add(XNPERR_INVALID_PARAM, 'not integer parameter 2');
        }
        if (!is_int($vars[3]) && !ctype_digit($vars[3])) {
            $error->add(XNPERR_INVALID_PARAM, 'not integer parameter 3');
        }
        if (!in_array($vars[4], array(
            'title',
            'ext_id',
            'last_modified_date',
            'registration_date',
            'creation_date',
        ))) {
            $error->add(XNPERR_INVALID_PARAM, 'unknown sort key parameter 4');
        }
        if (!in_array($vars[5], array(
            'asc',
            'desc',
        ))) {
            $error->add(XNPERR_INVALID_PARAM, 'unknown sort order parameter 5');
        }

        if ($error->get(0)) {
            // return if parameter error
            $response->setResult(false);

            return;
        } else {
            $sessionid = $vars[0];
            $query = $vars[1];
            $start = $vars[2];
            $limit = $vars[3];
            $sortkey = $vars[4];
            $ascdesc = $vars[5];
        }
        list($result, $uid, $session) = $this->restoreSession($sessionid);
        if (!$result) {
            $response->setResult(false);
            $error->add(XNPERR_INVALID_SESSION);

            return false;
        }
        $response->setResult(false);

        // convert ($start, $limit, $sortkey, $ascdesc) to ($criteria, $join_sort_table)
        $criteria = new CriteriaCompo();
        $criteria->setOrder($ascdesc);
        $criteria->setStart($start);
        $criteria->setLimit($limit);
        switch ($sortkey) {
            case 'title':
                $join_sort_table = 'xoonips_item_title';
                $criteria->add(new Criteria('title_id', 0));
                $criteria->setSort('title');
                break;

            case 'ext_id':
                $join_sort_table = false;
                $criteria->setSort('doi');
                break;

            case 'last_modified_date':
                $join_sort_table = false;
                $criteria->setSort('last_update_date');
                break;

            case 'registration_date':
                $join_sort_table = false;
                $criteria->setSort('creation_date');
                break;

            case 'creation_date':
                $join_sort_table = false;
                $criteria->setSort(array(
                    'publication_year',
                    'publication_month',
                    'publication_mday',
                ));
                break;

            default:
                $error->add(XNPERR_INVALID_PARAM, 'unknown sort key parameter 4');
                $response->setResult(false);

                return false;
                break;
        }

        // get valid item_type_ids -> $criteria
        $factory = &XooNIpsLogicFactory::getInstance();
        $logic = &$factory->create('getItemTypes');
        $vars = array($sessionid);
        $logic->execute($vars, $response);
        if ($response->getResult() == false) {
            return false;
        }
        $itemtypes = $response->getSuccess();
        if (count($itemtypes) == 0) {
            // no itemtype found. return empty array.
            $ar = array();
            $response->setSuccess($ar);
            $response->setResult(true);

            return true;
        }
        $itemtype_ids = array();
        foreach ($itemtypes as $itemtype) {
            $itemtype_ids[] = $itemtype->get('item_type_id');
        }
        $criteria->add(new Criteria('item_type_id', '('.implode(',', $itemtype_ids).')', 'IN'));

        if (substr($query, 0, 6) == 'index:') {
            // index id -> search_cache_items
            $index_id = substr($query, 6);
            if (!ctype_digit($index_id)) {
                $error->add(XNPERR_INVALID_PARAM, "bad index id(index_id=$index_id)");
                $response->setResult(false);

                return false;
            }
            $criteria->add(new Criteria('index_id', $index_id));
            $index_handler = &xoonips_getormhandler('xoonips', 'index');
            $index = $index_handler->get($index_id);
            if (false == $index) {
                $error->add(XNPERR_NOT_FOUND, "index not found(index_id=$index_id)");
                $response->setResult(false);

                return false;
            }
            if (!$index_handler->getPerm($index_id, $uid, 'read')) {
                $error->add(XNPERR_ACCESS_FORBIDDEN, "forbidden(index_id=$index_id)");
                $response->setResult(false);

                return false;
            }
            // xoonips_index_item_link <- $join(xoonips_item_basic) <- $join2($join_sort_table)
            $index_item_link_handler = &xoonips_getormhandler('xoonips', 'index_item_link');
            $join = new XooNIpsJoinCriteria('xoonips_item_basic', 'item_id', 'item_id');
            if ($join_sort_table) {
                $join2 = new XooNIpsJoinCriteria($join_sort_table, 'item_id', 'item_id');
                $join->cascade($join2);
            }

            $index_item_links = &$index_item_link_handler->getObjects($criteria, false, '', false, $join);
            $item_compo_handler = &xoonips_getormcompohandler('xoonips', 'item');
            $search_cache_item_handler = &xoonips_getormhandler('xoonips', 'search_cache_item');
            $search_cache_items = array();
            foreach ($index_item_links as $index_item_link) {
                if (!$item_compo_handler->getPerm($index_item_link->get('item_id'), $uid, 'read')) {
                    continue;
                } // no read permission
                $search_cache_item = $search_cache_item_handler->create();
                $search_cache_item->set('item_id', $index_item_link->get('item_id'));
                $search_cache_item->set('matchfor_index', 1);
                $search_cache_item->set('matchfor_item', 0);
                $search_cache_item->set('matchfor_file', 0);
                $search_cache_items[] = $search_cache_item;
            }
            $response->setSuccess($search_cache_items);
            $response->setResult(true);

            return true;
        } elseif (substr($query, 0, 8) == 'keyword:') {
            $search_cache_id = $this->getSearchCacheIdByQuery($query);
            $search_cache_handler = &xoonips_getormhandler('xoonips', 'search_cache');
            $eventlog_handler = &xoonips_getormhandler('xoonips', 'event_log');
            if ($search_cache_id) {
                $search_cache = $search_cache_handler->get($search_cache_id);
                if ($search_cache === false) {
                    // maybe invalidated.
                    $search_cache_id = false;
                } else {
                    $c = new CriteriaCompo();
                    $event_type_ids = array( // this events change search result. if one of this event is newer than search cache, don't use search cache.
                        ETID_INSERT_ITEM,
                        ETID_UPDATE_ITEM,
                        ETID_DELETE_ITEM,
                        ETID_DELETE_GROUP,
                        ETID_INSERT_GROUP_MEMBER,
                        ETID_DELETE_GROUP_MEMBER,
                        ETID_DELETE_INDEX,
                        ETID_CERTIFY_ITEM,
                        ETID_REJECT_ITEM,
                        ETID_TRANSFER_ITEM,
                    );
                    $c->add(new Criteria('event_type_id', '('.implode(',', $event_type_ids).')', 'in'));
                    $c->add(new Criteria("timestamp - unix_timestamp('".$search_cache->get('timestamp')."')", 0, '>='));
                    $c->setSort('timestamp');
                    $c->setOrder('desc');
                    $c->setLimit(1);
                    $event_logs = &$eventlog_handler->getObjects($c);
                    if (false === $event_logs) {
                        $response->setResult(false);
                        $error->add(XNPERR_SERVER_ERROR, 'cannot get event logs');

                        return false;
                    } elseif (!empty($event_logs)) {
                        $search_cache_id = false;
                        // delete old search results from search cache
                        $c = new CriteriaCompo(new Criteria('sess_id', $sessionid));
                        $c->add(new Criteria('unix_timestamp(timestamp)', $event_logs[0]->get('timestamp'), '<'));
                        $search_caches = &$search_cache_handler->getObjects($c);
                        if (false === $search_caches) {
                            $response->setResult(false);
                            $error->add(XNPERR_SERVER_ERROR, 'cannot get search cache ids');

                            return false;
                        }
                        $search_cache_item_handler = &xoonips_getormhandler('xoonips', 'search_cache_item');
                        foreach ($search_caches as $search_cache) {
                            $id = $search_cache->get('search_cache_id');
                            $this->deleteSearchCache($id);
                            $search_cache_handler->delete($search_cache);
                            $search_cache_item_handler->deleteAll(new Criteria('search_cache_id', $id));
                        }
                    }
                }
            }

            if (!$search_cache_id) {
                $search_cache = $search_cache_handler->create();
                $search_cache->setVar('sess_id', $sessionid, true);
                if (!$search_cache_handler->insert($search_cache)) {
                    $response->setResult(false);
                    $error->add(XNPERR_SERVER_ERROR, 'cannot create search_cache_id');

                    return false;
                }
                $search_cache_id = $search_cache->get('search_cache_id');
                $keyword = substr($query, 8);
                $this->keywordSearch($error, $search_cache_id, $uid, $keyword);
                if (!$eventlog_handler->recordQuickSearchEvent('all', $keyword)) {
                    $error->add(XNPERR_SERVER_ERROR, 'cannot insert event');
                    $response->setResult(false);

                    return false;
                }
            }

            //
            // access permission: ol_public && ( is_moderator || certify_state=certified) || ol_group_only && gid=gulink.gid && ( is_admin || certify_state==certified) || ol_private && uid=uid // how to reuse?
            // -> criteria
            $c = new CriteriaCompo();
            $c->add(new Criteria('open_level', OL_PUBLIC));
            $member_handler = &xoonips_gethandler('xoonips', 'member');
            if (!$member_handler->isModerator($uid)) {
                $c->add(new Criteria('certify_state', CERTIFIED));
            }
            $groups_users_link_handler = &xoonips_getormhandler('xoonips', 'groups_users_link');
            $links = &$groups_users_link_handler->getObjects(new Criteria('uid', $uid));
            foreach ($links as $link) {
                $c->add(new Criteria('open_level', OL_GROUP_ONLY), 'OR');
                $c->add(new Criteria('gid', $link->get('gid')));
                if (!$link->get('is_admin')) {
                    $c->add(new Criteria('certify_state', CERTIFIED));
                }
            }
            $c->add(new Criteria('open_level', OL_PRIVATE), 'OR');
            $c->add(new Criteria('tx.uid', $uid));
            $criteria->add($c);
            $criteria->add(new Criteria('search_cache_id', $search_cache_id));
            $criteria->setGroupby('search_cache_item_id');

            // join:
            //   xoonips_search_cache_item  <-  $join(xoonips_index_item_link)
            //                              <-  $join2(xoonips_index)
            //                              <-  $join3(xoonips_item_basic)
            //                              <-  $join4($join_sort_table)
            $join = new XooNIpsJoinCriteria('xoonips_index_item_link', 'item_id', 'item_id', 'LEFT', 'txil');
            $join2 = new XooNIpsJoinCriteria('xoonips_index', 'index_id', 'index_id', 'LEFT', 'tx');
            $join3 = new XooNIpsJoinCriteria('xoonips_item_basic', 'item_id', 'item_id');
            $join4 = new XooNIpsJoinCriteria($join_sort_table, 'item_id', 'item_id');
            $join->cascade($join2, 'txil', true);
            $join->cascade($join3, 'xoonips_search_cache_item');
            if ($join_sort_table) {
                $join->cascade($join4, 'xoonips_search_cache_item');
            }
            $search_cache_item_handler = &xoonips_getormhandler('xoonips', 'search_cache_item');
            $search_cache_items = &$search_cache_item_handler->getObjects($criteria, false, '', false, $join);
            if ($search_cache_items === false) {
                $error->add(XNPERR_INVALID_PARAM, 'cannot get searched item');
                $response->setResult(false);

                return false;
            }
            $this->putSearchCache($query, $search_cache_id);

            $response->setSuccess($search_cache_items);
            $response->setResult(true);

            return true;
        } else {
            $error->add(XNPERR_INVALID_PARAM, 'query must begin with index: or keyword:');
        }

        if ($error->get()) {
            $response->setResult(false);

            return false;
        }
    }

    public function keywordSearch(&$error, $search_cache_id, $uid, $query)
    {
        $search_cache_item_handler = &xoonips_getormhandler('xoonips', 'search_cache_item');
        // search item
        $item_type_handler = &xoonips_getormhandler('xoonips', 'item_type');
        $item_types = &$item_type_handler->getObjects(new Criteria('isnull(mid)', 0));
        foreach ($item_types as $item_type) {
            $detail_item_handler = &xoonips_getormcompohandler($item_type->get('name'), 'item');
            if (!$detail_item_handler) {
                continue;
            }
            $step = 1000;
            for ($offset = 0; ; $offset += $step) {
                $item_ids = $detail_item_handler->search($query, $step, $offset, $uid);
                if ($item_ids === false) {
                    $error->add(XNPERR_SERVER_ERROR, 'cannot search item');

                    return;
                }
                foreach ($item_ids as $item_id) {
                    $search_cache_item = $search_cache_item_handler->create();
                    $search_cache_item->setVar('search_cache_id', $search_cache_id, true);
                    $search_cache_item->setVar('item_id', $item_id, true);
                    $search_cache_item->setVar('matchfor_index', 0, true);
                    $search_cache_item->setVar('matchfor_item', 1, true);
                    $search_cache_item->setVar('matchfor_file', 0, true);
                    if (!$search_cache_item_handler->insert($search_cache_item)) {
                        $error->add(XNPERR_SERVER_ERROR, 'cannot get items');

                        return;
                    }
                }
                if (count($item_ids) != $step) {
                    break;
                }
            }
        }
        // search file
        $step = 1000;
        $search_text_handler = &xoonips_getormhandler('xoonips', 'search_text');
        $file_handler = &xoonips_getormhandler('xoonips', 'file');
        for ($offset = 0; ; $offset += $step) {
            $item_ids = $search_text_handler->search($query, $step, $offset, $uid);
            if ($item_ids === false) {
                $error->add(XNPERR_SERVER_ERROR, 'cannot search file');

                return;
            }
            if (count($item_ids)) {
                // update search_cache_item
                $criteria = new CriteriaCompo();
                $criteria->add(new Criteria('item_id', '('.implode(',', $item_ids).')', 'in'));
                $criteria->add(new Criteria('search_cache_id', $search_cache_id));
                $search_cache_items = &$search_cache_item_handler->getObjects($criteria);
                if ($search_cache_items === false) {
                    $error->add(XNPERR_SERVER_ERROR, 'cannot get search_cache_item');

                    return;
                }
                $len = count($search_cache_items);
                $update_item_ids = array();
                for ($i = 0; $i < $len; ++$i) {
                    $search_cache_items[$i]->setVar('matchfor_file', 1, true);
                    if (!$search_cache_item_handler->insert($search_cache_items[$i])) {
                        $error->add(XNPERR_SERVER_ERROR, 'cannot update search_cache_item');

                        return;
                    }
                    $update_item_ids[] = $search_cache_items[$i]->get('item_id');
                }
                // insert search_cache_item
                $insert_item_ids = array_diff($item_ids, $update_item_ids);
                foreach ($insert_item_ids as $item_id) {
                    $search_cache_item = $search_cache_item_handler->create();
                    $search_cache_item->setVar('search_cache_id', $search_cache_id, true);
                    $search_cache_item->setVar('item_id', $item_id, true);
                    $search_cache_item->setVar('matchfor_index', 0, true);
                    $search_cache_item->setVar('matchfor_item', 0, true);
                    $search_cache_item->setVar('matchfor_file', 1, true);
                    if (!$search_cache_item_handler->insert($search_cache_item)) {
                        $error->add(XNPERR_SERVER_ERROR, 'cannot insert search_cache_item');

                        return;
                    }
                }
            }
            if (count($item_ids) != $step) {
                break;
            }
        }

        return;
    }
}
