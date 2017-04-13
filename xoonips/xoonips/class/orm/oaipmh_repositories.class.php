<?php

// $Revision: 1.1.4.1.2.6 $
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
if (!defined('XOOPS_ROOT_PATH')) {
    exit();
}

/**
 * @brief data object of OAI-PMH repositories
 *
 * @li getVar('repository_id') :
 * @li getVar('URL') :
 * @li getVar('last_access_date') :
 * @li getVar('last_success_date') :
 * @li getVar('last_access_result') :
 * @li getVar('sort') :
 * @li getVar('enabled') :
 * @li getVar('deleted') :
 * @li getVar('repository_name') :
 * @li getVar('metadata_count') :
 */
class XooNIpsOrmOaipmhRepositories extends XooNIpsTableObject
{
    public function XooNIpsOrmOaipmhRepositories()
    {
        $this->initVar('repository_id', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('URL', XOBJ_DTYPE_TXTBOX, null, false, 255);
        $this->initVar('last_access_date', XOBJ_DTYPE_INT, null, false);
        $this->initVar('last_success_date', XOBJ_DTYPE_INT, null, false);
        $this->initVar('last_access_result', XOBJ_DTYPE_TXTBOX, null, false, 65535);
        $this->initVar('sort', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('enabled', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('deleted', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('repository_name', XOBJ_DTYPE_TXTBOX, null, false, 65535);
        $this->initVar('metadata_count', XOBJ_DTYPE_INT, 0, false);
    }
}

/**
 * @brief handler object of OAI-PMH repositories
 */
class XooNIpsOrmOaipmhRepositoriesHandler extends XooNIpsTableObjectHandler
{
    public function XooNIpsOrmOaipmhRepositoriesHandler(&$db)
    {
        parent::XooNIpsTableObjectHandler($db);
        $this->__initHandler('XooNIpsOrmOaipmhRepositories', 'xoonips_oaipmh_repositories', 'repository_id', true);
    }

    public function &getRepositories($fmt)
    {
        $criteria = new Criteria('deleted', '1', '!=');
        $criteria->setSort('sort');
        $objs = &$this->getObjects($criteria, true, 'repository_id,URL,enabled');
        $urls = array();
        foreach ($objs as $id => $obj) {
            $url = $obj->getVar('URL', $fmt);
            $enabled = $obj->getVar('enabled', $fmt);
            $urls[$id] = array(
            'URL' => $url,
            'enabled' => $enabled,
            );
        }

        return $urls;
    }

    public function setRepositories(&$repositories)
    {
        // parse new repositories
        $new_repos_url = array();
        $new_repos_enabled = array();
        $new_repos_exist = array();
        foreach ($repositories as $repo) {
            $url = trim($repo);
            // remove empty line
            if (empty($url)) {
                continue;
            }
            // remove duplicated url
            if (in_array($url, $new_repos_url)) {
                continue;
            }
            // check 'enable' or 'disable'
            if (preg_match('/^(;|#)(.*)$/', $url, $matches)) {
                $enabled = 0;
                    // remove empty comment line
                if (trim($matches[2]) == '') {
                    continue;
                }
            } else {
                $enabled = 1;
            }
            $new_repos_url[] = $url;
            $new_repos_enabled[] = $enabled;
            $new_repos_exist[] = false;
        }
        // get all recorded repositories
        $criteria = new CriteriaElement();
        $criteria->setSort('sort');
        $recorded_objs = &$this->getObjects($criteria, true);
        foreach ($recorded_objs as $obj) {
            // find existing repository
            $rec_url = $obj->getVar('URL', 'n');
            $found = false;
            $found_repo = null;
            foreach ($new_repos_url as $sort => $new_url) {
                // use first match entry
                if ($new_repos_exist[$sort] == false) {
                    if ($rec_url == $new_url) {
                        $found = true;
                        $new_repos_exist[$sort] = true;
                        break;
                    }
                }
            }
            if ($found) {
                // update entry
                $obj->set('sort', $sort);
                $obj->set('enabled', $new_repos_enabled[$sort]);
                $obj->set('deleted', 0);
            } else {
                // remove entry ( set deleted flag )
                $obj->set('sort', 0);
                $obj->set('enabled', 0);
                $obj->set('deleted', 1);
            }
            $this->insert($obj);
        }
        // add new repositories
        foreach ($new_repos_exist as $sort => $new_exist) {
            if (!$new_exist) {
                $obj = &$this->create();
                $obj->set('sort', $sort);
                $obj->set('URL', $new_repos_url[$sort]);
                $obj->set('enabled', $new_repos_enabled[$sort]);
                $obj->set('deleted', 0);
                $this->insert($obj);
            }
        }
    }

    public function &getLastResults($fmt)
    {
        $criteria = new CriteriaCompo(new Criteria('deleted', '1', '!='));
        $criteria->add(new Criteria('enabled', '1'));
        $criteria->add(new Criteria('last_access_date', 'NULL', '!='));
        $criteria->setSort('sort');
        $fields = array(
        'repository_id',
        'URL',
        'last_access_date',
        'last_access_result',
        );
        $objs = &$this->getObjects($criteria, true, implode(',', $fields));
        $logs = array();
        foreach ($objs as $id => $obj) {
            $log = array();
            foreach ($fields as $field) {
                $log[$field] = $obj->getVar($field, $fmt);
            }
            $log['last_access_date'] = date('Y-m-d H:i:s', intval($log['last_access_date']));
            $logs[] = &$log;
            unset($log);
        }

        return $logs;
    }
}
