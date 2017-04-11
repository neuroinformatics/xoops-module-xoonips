<?php

// $Revision: 1.1.2.9 $
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

include_once dirname(dirname(__DIR__))
    .'/class/base/action.class.php';

class XooNIpsActionXoonipsSearchMetadataDetail extends XooNIpsAction
{
    public function XooNIpsActionXoonipsSearchMetadataDetail()
    {
        parent::XooNIpsAction();
    }

    public function _get_logic_name()
    {
        return null;
    }

    public function _get_view_name()
    {
        return 'oaipmh_metadata_detail';
    }

    public function preAction()
    {
        xoonips_allow_post_method();

        xoonips_validate_request(
            $this->isValidMetadataId(
                $this->_formdata->getValue('post', 'identifier', 's', false)));
    }

    public function doAction()
    {
        $this->_view_params['url_to_back'] = 'itemselect.php';
        $this->_view_params['repository_name']
            = $this->getRepositoryName(
                strval($this->_formdata->getValue('post', 'identifier', 's', false)));
        $this->_view_params['metadata']
            = $this->getMetadataArray(
                strval($this->_formdata->getValue('post', 'identifier', 's', false)));
        $this->_view_params['hidden'] = array(
            array('name' => 'op',
                   'value' => strval($this->_formdata->getValue('post', 'op', 's', false)), ),
            array('name' => 'keyword',
                   'value' => strval($this->_formdata->getValue('post', 'keyword', 's', false)), ),
            array('name' => 'search_itemtype',
                   'value' => strval($this->_formdata->getValue('post', 'search_itemtype', 's', false)), ),
            array('name' => 'search_cache_id',
                   'value' => intval($this->_formdata->getValue('post', 'search_cache_id', 'i', false)), ),
            array('name' => 'order_by',
                   'value' => strval($this->_formdata->getValue('post', 'order_by', 's', false)), ),
            array('name' => 'order_dir',
                   'value' => strval($this->_formdata->getValue('post', 'order_dir', 's', false)), ),
            array('name' => 'item_per_page',
                   'value' => intval($this->_formdata->getValue('post', 'item_per_page', 'i', false)), ),
            array('name' => 'page',
                   'value' => intval($this->_formdata->getValue('post', 'page', 'i', false)), ),
            array('name' => 'search_tab',
                   'value' => strval($this->_formdata->getValue('post', 'search_tab', 's', false)), ), );
    }

    /**
     * metadata field array from search cache id.
     *
     * @param string $identifier
     *
     * @return array of metadata
     */
    public function getMetadataArray($identifier)
    {
        $metadata_handler = &xoonips_getormhandler(
            'xoonips', 'oaipmh_metadata');
        $metadata = &$metadata_handler->getObjects(
            new Criteria('identifier', $identifier));
        if (!$metadata) {
            return array();
        }

        $metadata_field_handler = &xoonips_getormhandler(
            'xoonips', 'oaipmh_metadata_field');
        $criteria = new Criteria('metadata_id',
                                  $metadata[0]->get('metadata_id'));
        $criteria->setSort('ordernum');
        $fields = &$metadata_field_handler->getObjects($criteria);
        if (!$fields) {
            return array();
        }

        $result = array();
        foreach ($fields as $field) {
            $result[] = $field->getVarArray('n');
        }

        return $result;
    }

    /**
     * get repository name of metadata.
     *
     * @param string $identifier identifier of metadata
     *
     * @return repository name or empty string
     */
    public function getRepositoryName($identifier)
    {
        $metadata_handler = &xoonips_getormhandler(
            'xoonips', 'oaipmh_metadata');
        $repository_handler = &xoonips_getormhandler(
            'xoonips', 'oaipmh_repositories');

        $metadata = &$metadata_handler->getObjects(
            new Criteria('identifier', $identifier));
        if (!$metadata || count($metadata) == 0) {
            return '';
        }

        $repository = &$repository_handler
            ->get($metadata[0]->get('repository_id'));
        if (!$repository || count($repository) == 0) {
            return '';
        }

        return $repository->get('repository_name');
    }

    /**
     * @param $id metadata identifier
     *
     * @return bool true if valid metadata identifier
     */
    public function isValidMetadataId($id)
    {
        $handler = &xoonips_getormhandler('xoonips', 'oaipmh_metadata');

        $rows = &$handler->getObjects(
            new Criteria('identifier', addslashes($id)));

        return $rows && count($rows) > 0;
    }
}
