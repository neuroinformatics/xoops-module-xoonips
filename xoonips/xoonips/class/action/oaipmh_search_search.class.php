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

require_once dirname(dirname(__DIR__)).'/class/base/action.class.php';

class XooNIpsActionOaipmhSearchSearch extends XooNIpsAction
{
    public $_orderDir = 'asc';
    public $_orderBy = 'title';
    public $_page = 0;
    public $_searchCacheId = 0;
    public $_metadataPerPage = 20;
    public $_logicName = null;

    public function __construct()
    {
        parent::__construct();
    }

    public function _get_logic_name()
    {
        return $this->_logicName;
    }

    public function _get_view_name()
    {
        return 'oaipmh_search_result';
    }

    public function preAction()
    {
        xoonips_allow_post_method();

        $repository_id = $this->_formdata->getValue('post', 'repository_id', 'i', true);
        xoonips_validate_request($this->isValidRepositoryId($repository_id));

        $order_by = $this->_formdata->getValue('post', 'order_by', 's', false);
        xoonips_validate_request(in_array($order_by, array('title', 'identifier', 'last_update_date', 'creation_date', 'date')));

        $order_dir = $this->_formdata->getValue('post', 'order_dir', 's', false);
        xoonips_validate_request(in_array($order_dir, array('asc', 'desc')));

        $metadata_per_page = $this->_formdata->getValue('post', 'metadata_per_page', 'i', false);
        xoonips_validate_request(in_array($metadata_per_page, array(20, 50, 100)));

        $page = $this->_formdata->getValue('post', 'page', 'i', false);
        xoonips_validate_request($page > 0);

        $search_flag = $this->_formdata->getValue('post', 'search_flag', 'i', false);
        xoonips_validate_request(in_array($search_flag, array(0, 1)));

        $search_cache_id = $this->_formdata->getValue('post', 'search_cache_id', 'i', false);
        if (!is_null($search_cache_id)) {
            xoonips_validate_request($this->searchCacheExists($search_cache_id));
        }

        $keyword = $this->_formdata->getValue('post', 'keyword', 's', false);
        if (0 == $repository_id && '' == $keyword) {
            $this->_searchCacheId = 0;
            $this->_logicName = null;

            return;
        }

        $this->_orderDir = $order_dir;
        $this->_orderBy = $order_by;
        $this->_metadataPerPage = $metadata_per_page;
        $this->_page = $page;
        $this->_searchCacheId = is_null($search_cache_id) ? '0' : $search_cache_id;

        $this->_params[] = session_id();
        $this->_params[] = $repository_id;
        $this->_params[] = $keyword;
        $this->_params[] = $this->_orderBy;
        $this->_params[] = $this->_orderDir;
    }

    public function doAction()
    {
        if ((bool) $this->_formdata->getValue('post', 'search_flag', 'i', false) || !$this->searchCacheExists($this->_formdata->getValue('post', 'search_cache_id', 's', false))) {
            $this->_logicName = 'oaipmhSearch';
            parent::doAction();
        }
        //global $xoopsDB;var_dump(xoops_getenv('HTTP_REFERER'),$_SERVER['REQUEST_METHOD'],get_class($xoopsDB));
        if ($this->_response->getResult()) {
            $this->_searchCacheId = $this->_response->getSuccess();
        }
        if ($this->_response->getResult()
            && (bool) $this->_formdata->getValue('post', 'search_flag', 'i', false)
        ) {
            $event_handler = &xoonips_getormhandler('xoonips', 'event_log');
            $event_handler->recordQuickSearchEvent('metadata', $this->_formdata->getValue('post', 'keyword', 'n', false), $this->getRepositoryUrl($this->_formdata->getValue('post', 'repository_id', 'i', false)));
        }
    }

    public function postAction()
    {
        $textutil = &xoonips_getutility('text');
        if ($this->_page > ceil($this->getTotalMetadataCount() / $this->_metadataPerPage)) {
            $this->_page = 1;
        }
        $this->_view_params['search_cache_id'] = $this->_searchCacheId;
        $this->_view_params['order_by'] = $this->_orderBy;
        $this->_view_params['order_dir'] = $this->_orderDir;
        $this->_view_params['metadata_per_page'] = $this->_metadataPerPage;
        $this->_view_params['total_metadata_count'] = $this->getTotalMetadataCount();
        $this->_view_params['start_metadata_count'] = $this->getStartMetadataCount();
        $this->_view_params['end_metadata_count'] = $this->getEndMetadataCount();
        $this->_view_params['page'] = $this->_page;
        $this->_view_params['maxpage'] = ceil($this->getTotalMetadataCount() / $this->_metadataPerPage);
        $this->_view_params['pages'] = $this->getSelectablePageNumber($this->_view_params['page'], $this->_view_params['maxpage']);
        $this->_view_params['metadata'] = $this->getMetadataArrays($this->_searchCacheId, $this->_orderBy, $this->_orderDir, $this->getStartMetadataCount(), $this->getEndMetadataCount());
        $this->_view_params['repository_id'] = $this->_formdata->getValue('post', 'repository_id', 'i', false);
        $this->_view_params['keyword'] = $textutil->html_special_chars($this->_formdata->getValue('post', 'keyword', 's', false));
    }

    public function getStartMetadataCount()
    {
        if ($this->getEndMetadataCount() == 0) {
            return 0;
        }

        return ($this->_page - 1) * $this->_metadataPerPage + 1;
    }

    public function getEndMetadataCount()
    {
        return min($this->_page * $this->_metadataPerPage, $this->getTotalMetadataCount());
    }

    /**
     * metadata array from search cache id.
     *
     * @param int    $search_cache_id search cache id
     * @param string $order_by        sort field name
     * @param string $order_dir       'asc' or 'desc'
     * @param int    $start_count     number of first row to get(first row is 1)
     * @param int    $end_count       number of last row to get
     *
     * @return array of metadata associative array
     */
    public function getMetadataArrays($search_cache_id, $order_by, $order_dir, $start_count, $end_count)
    {
        global $xoopsDB;

        $textutil = &xoonips_getutility('text');
        $cache_handler = &xoonips_getormhandler('xoonips', 'search_cache');
        $cache_metadata_handler = &xoonips_getormhandler('xoonips', 'search_cache_metadata');
        $metadata_handler = &xoonips_getormhandler('xoonips', 'oaipmh_metadata');
        $repository_handler = &xoonips_getormhandler('xoonips', 'oaipmh_repositories');

        if (!$cache_handler->get($search_cache_id)) {
            return array();
        }

        $criteria = new Criteria('search_cache_id', $search_cache_id);
        $criteria->setStart(max(0, $start_count - 1));
        $criteria->setLimit($end_count - $start_count + 1);

        $criteria->setSort($this->getOrderByColumn($order_by));
        $criteria->setOrder($order_dir);
        $join = new XooNIpsJoinCriteria('xoonips_oaipmh_metadata', 'identifier', 'identifier', 'INNER', 'tmeta');

        $metadata_cache = &$cache_metadata_handler->getObjects($criteria, false, '', false, $join);
        if (!$metadata_cache) {
            return array();
        }

        $result = array();
        foreach ($metadata_cache as $cache) {
            $repository = &$repository_handler->get($cache->getExtraVar('repository_id'));
            if (!$repository) {
                continue;
            }

            $result[] = array(
                'id' => $cache->getVar('identifier', 's'),
                'metadata_id' => $textutil->html_special_chars($cache->getExtraVar('metadata_id')),
                'title' => $textutil->html_special_chars($cache->getExtraVar('title')),
                'repository_name' => $repository->getVar('repository_name', 's'),
                'last_update_date' => $textutil->html_special_chars($cache->getExtraVar('last_update_date')),
                'creation_date' => $textutil->html_special_chars($cache->getExtraVar('creation_date')),
                'date' => $textutil->html_special_chars($cache->getExtraVar('date')),
                'link' => $textutil->html_special_chars($cache->getExtraVar('link')),
            );
        }

        return $result;
    }

    /**
     * is search cache id is exists.
     *
     * @return bool
     */
    public function searchCacheExists($cache_id)
    {
        $cache_handler = &xoonips_getormhandler('xoonips', 'search_cache');
        $cache = &$cache_handler->get(intval($cache_id));

        return $cache !== false;
    }

    /**
     * get number of metadata of search result.
     *
     * @return int
     */
    public function getTotalMetadataCount()
    {
        $cache_metadata_handler = &xoonips_getormhandler('xoonips', 'search_cache_metadata');
        $result = &$cache_metadata_handler->getObjects(new Criteria('search_cache_id', $this->_searchCacheId), false, 'count(*)');
        if (!$result) {
            return 0;
        }

        return $result[0]->getExtraVar('count(*)');
    }

    /**
     * return boolean value of modification of metadata.
     *
     * @param int $timestamp timestamp of search cache id
     *
     * @return true(modified), false(not modified)
     */
    public function isMetadataModified($timestamp)
    {
        $event_handler = &xoonips_getormhandler('xoonips', 'event_log');
        // this events modify search result.
        // if one of this event is newer than search cache,
        // don't use search cache.
        $event_type_ids = array(
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
        $criteria = new CriteriaCompo();
        $criteria->add(new Criteria('event_type_id', '('.implode(',', $event_type_ids).')', 'IN'));
        $criteria->add(new Criteria('timestamp', $timestamp, '>='));
        $result = &$event_handler->getObjects($criteria);
        if (!$result || count($result) == 0) {
            return false;
        }

        return true;
    }

    public function sortIdentifiers($identifiers, $order_by, $order_dir)
    {
        global $xoopsDB;
        $metadata_handler = &xoonips_getormhandler('xoonips', 'oaipmh_metadata');

        $esc_id = array();
        foreach ($identifiers as $id) {
            $esc_id[] = $xoopsDB->quoteString($esc_id);
        }
        $criteria = new Criteria('identifier', '('.implode(',', $esc_id).')', 'IN');
        $criteria->setSort($order_by);
        $criteria->setOrder($order_dir);

        $result = array();
        $metadata = &$metadata_handler->getObjects($criteria);
        if (!$metadata) {
            return array();
        }
        foreach ($metadata as $meta) {
            $result[] = $meta->get('identifier');
        }

        return $result;
    }

    public function getOrderByColumn($order_by)
    {
        switch ($order_by) {
        case 'title':
            return 'title';
        case 'identifier':
            return 'tmeta.identifier';
        case 'last_update_date':
            return 'last_update_date_for_sort';
        case 'creation_date':
            return 'creation_date_for_sort';
        case 'date':
            return 'date_for_sort';
        default:
            return 'title';
        }
    }

    /**
     * @param $page integer current page number
     * @param $maxpage integer max page number
     *
     * @return array of integer page numbers
     */
    public function getSelectablePageNumber($page, $maxpage)
    {
        //centering current page number(5th of $pages)
        $pages = array(min(max(1, $page - 4), max(1, $maxpage - 9)));
        for ($i = 1; $i < 10 && $pages[$i - 1] < $maxpage; ++$i) {
            $pages[$i] = $pages[$i - 1] + 1;
        }

        return $pages;
    }

    /**
     * @param int $repository_id
     *
     * @return repository url or null(reository not found)
     */
    public function getRepositoryUrl($repository_id)
    {
        $repository_handler = &xoonips_getormhandler('xoonips', 'oaipmh_repositories');
        $repository = &$repository_handler->get(intval($repository_id));
        if (!$repository) {
            return null;
        }

        return $repository->get('URL');
    }

    /**
     * @param $id repository id
     *
     * @return bool true if valid repository id or zero
     */
    public function isValidRepositoryId($id)
    {
        if ($id == 0) {
            return true;
        }
        $handler = &xoonips_getormhandler('xoonips', 'oaipmh_repositories');

        $rows = &$handler->getObjects(new Criteria('repository_id', addslashes($id)));

        return $rows && count($rows) > 0;
    }
}
