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

require_once dirname(dirname(__DIR__)).'/class/base/action.class.php';

class XooNIpsActionOaipmhSearchDefault extends XooNIpsAction
{
    public function __construct()
    {
        parent::__construct();
    }

    public function _get_logic_name()
    {
        return null;
    }

    public function _get_view_name()
    {
        return 'oaipmh_search_default';
    }

    public function preAction()
    {
        xoonips_allow_both_method();
    }

    public function doAction()
    {
        global $xoopsUser;
        $textutil = &xoonips_getutility('text');

        $repository_id = $this->_formdata->getValue('post', 'repository_id', 'i', false);
        if (!is_null($repository_id)) {
            xoonips_validate_request($this->isValidRepositoryId($repository_id));
        }

        $this->_view_params['repository_id'] = $repository_id;
        $this->_view_params['keyword'] = $textutil->html_special_chars($this->_formdata->getValue('post', 'keyword', 's', false));
        $this->_view_params['repositories'] = $this->getRepositoryArrays();
        $this->_view_params['total_repository_count'] = $this->getTotalRepositoryCount();
        $this->_view_params['total_metadata_count'] = $this->getTotalMetadataCount();
    }

    /**
     * note: repository name is truncated in 70 chars.
     *
     * @return array of associative array of repository
     */
    public function getRepositoryArrays()
    {
        $textutil = &xoonips_getutility('text');
        $handler = &xoonips_getormhandler('xoonips', 'oaipmh_repositories');

        $criteria = new CriteriaCompo();
        $criteria->add(new Criteria('last_success_date', null, '!='));
        $criteria->add(new Criteria('enabled', 1));
        $criteria->add(new Criteria('deleted', 0));
        $rows = &$handler->getObjects($criteria);
        if (!$rows) {
            return array();
        }
        $result = array();
        foreach ($rows as $row) {
            $result[] = array(
                'repository_id' => $row->getVar('repository_id', 's'),
                'repository_name' => $textutil->truncate(('' != trim($row->getVar('repository_name', 's')) ? $row->getVar('repository_name', 's') : $row->getVar('URL', 's')), 70, '...'),
                'metadata_count' => $row->getVar('metadata_count', 's'), );
        }

        return $result;
    }

    /**
     * number of all of metadata to search.
     *
     * @return int
     */
    public function getTotalMetadataCount()
    {
        $result = 0;
        foreach ($this->getRepositoryArrays() as $repo) {
            $result += $repo['metadata_count'];
        }

        return $result;
    }

    /**
     * number of repositories to search.
     *
     * @return int
     */
    public function getTotalRepositoryCount()
    {
        return count($this->getRepositoryArrays());
    }

    /**
     * @param $id repository id
     *
     * @return bool true if valid repository id
     */
    public function isValidRepositoryId($id)
    {
        if (0 == $id) {
            return true;
        }
        $handler = &xoonips_getormhandler('xoonips', 'oaipmh_repositories');

        $rows = &$handler->getObjects(new Criteria('repository_id', addslashes($id)));

        return $rows && count($rows) > 0;
    }
}
