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

require_once XOOPS_ROOT_PATH.'/modules/xoonips/class/base/logic.class.php';
require_once XOOPS_ROOT_PATH.'/modules/xoonips/class/base/transaction.class.php';

/**
 * subclass of XooNIpsLogic(oampmhSearch).
 */
class XooNIpsLogicOaipmhSearch extends XooNIpsLogic
{
    /**
     * execute removeFile.
     *
     * @param[in]  $vars[0] session
     * @param[in]  $vars[1] repository_id
     * @param[in]  $vars[2] keyword
     * @param[in]  $vars[3] order by
     * @param[in]  $vars[4] order dir
     * @param[out] $response->result true:success, false:failed
     * @param[out] $response->error  error information
     * @param[out] $response->success metadata search cache id
     */
    public function execute(&$vars, &$response)
    {
        // parameter check
        $error = &$response->getError();
        if (count($vars) > 5) {
            $error->add(XNPERR_EXTRA_PARAM);
        }
        if (count($vars) < 5) {
            $error->add(XNPERR_MISSING_PARAM);
        }

        if (isset($vars[0]) && strlen($vars[0]) > 32) {
            $error->add(XNPERR_INVALID_PARAM, 'too long parameter 1');
        }
        if (isset($vars[1]) && !is_int($vars[1]) && !ctype_digit($vars[1])) {
            $error->add(XNPERR_INVALID_PARAM, 'not integer parameter 2');
        }
        if (isset($vars[1]) && isset($vars[2]) && 0 == intval($vars[1]) && empty($vars[2])) {
            $error->add(XNPERR_INVALID_PARAM, 'parameter 2(repository_id) or parameter 3(keyword)'.'is required.');
        }
        if (isset($vars[3]) && !in_array($vars[3], array('title', 'identifier', 'last_update_date', 'creation_date', 'date'))
        ) {
            $error->add(XNPERR_INVALID_PARAM, 'invalid parameter 4(order by)');
        }
        if (isset($vars[4]) && !in_array($vars[4], array('asc', 'desc'))) {
            $error->add(XNPERR_INVALID_PARAM, 'invalid parameter 5(order dir)');
        }

        if ($error->get(0)) {
            // return if parameter error
            $response->setResult(false);

            return;
        } else {
            $sessionid = $vars[0];
            $repository_id = intval($vars[1]);
            $keyword = $vars[2];
            $order_by = $vars[3];
            $order_dir = $vars[4];
        }
        list($result, $uid, $session) = $this->restoreSession($sessionid);
        if (!$result) {
            $response->setResult(false);
            $error->add(XNPERR_INVALID_SESSION);

            return false;
        }

        $cache_id = $this->searchMetadata($repository_id, $keyword, $this->getOrderByColumn($order_by), $order_dir);
        if (!$cache_id) {
            $error = &$response->getError();
            $error->add(XNPERR_SERVER_ERROR, 'failure in search');
            $response->setResult(false);

            return false;
        } else {
            $response->setSuccess($cache_id);
            $response->setResult(true);

            return true;
        }
    }

    /**
     * create cache and insert identifiers.
     *
     * @param array $identifiers array of metadata identifier
     *
     * @return search cache id
     */
    public function createCache($identifiers)
    {
        $cache_handler = &xoonips_getormhandler('xoonips', 'search_cache');
        $cache_metadata_handler = &xoonips_getormhandler('xoonips', 'search_cache_metadata');

        $cache = &$cache_handler->create();
        $cache->set('sess_id', session_id());
        //$cache -> set( 'timestamp', time() );
        $cache_handler->insert($cache);

        foreach ($identifiers as $id) {
            $metadata = &$cache_metadata_handler->create();
            $metadata->set('search_cache_id', $cache->get('search_cache_id'));
            $metadata->set('identifier', $id);
            $cache_metadata_handler->insert($metadata);
        }

        return $cache->get('search_cache_id');
    }

    /**
     * search metadata and return array of identifiers.
     *
     * @param int    $repository_id zero is no repository specified
     * @param string $keyword       search keyword string
     *
     * @return search cache id or false
     */
    public function searchMetadata($repository_id, $keyword, $order_by, $order_dir)
    {
        $metadata_handler = &xoonips_getormhandler('xoonips', 'oaipmh_metadata');
        $repository_handler = &xoonips_getormhandler('xoonips', 'oaipmh_repositories');
        $cache_handler = &xoonips_getormhandler('xoonips', 'search_cache');
        $cache_metadata_handler = &xoonips_getormhandler('xoonips', 'search_cache_metadata');

        if (0 == $repository_id && '' == strval($keyword)) {
            return $search_cache_id;
        }

        $result = array();
        if ('' == strval($keyword)) {
            $metadata = &$metadata_handler->getObjects(new Criteria('repository_id', $repository_id));
            foreach ($metadata as $data) {
                $result[] = $data->get('identifier');
            }
        } else {
            if (!xnpSearchExec('quicksearch', $keyword, 'metadata', false, $errormessage, $item_ids, $search_var, $search_cache_id, 'metadata')
            ) {
                return false;
            }
            if (0 == $repository_id) {
                return $search_cache_id;
            }

            global $xoopsDB;
            if (!$cache_handler->get($search_cache_id)) {
                return false;
            }

            $criteria = new CriteriaCompo();
            $criteria->add(new Criteria('search_cache_id', $search_cache_id));
            $criteria->add(new Criteria('repository_id', $repository_id));
            $join = new XooNIpsJoinCriteria('xoonips_oaipmh_metadata', 'identifier', 'identifier', 'INNER');

            $metadata_cache = $cache_metadata_handler->getObjects($criteria, false, '', false, $join);

            foreach ($metadata_cache as $cache) {
                $result[] = $cache->get('identifier');
            }
        }

        return $this->createCache($result);
    }

    public function getOrderByColumn($order_by)
    {
        switch ($order_by) {
        case 'title':
        case 'identifier':
            return $order_by;
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
}
