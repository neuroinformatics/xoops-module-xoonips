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

class ListRecordsHandler extends HarvesterHandler
{
    public $resumptionToken;
    public $identifier;
    public $title;
    public $metadataPrefix;
    public $search_text;
    public $baseURL;
    public $delete_flag;
    public $_creator;
    public $_last_update_date;
    public $_creation_date;
    public $_date;
    public $_resource_url;
    public $_namespaces;
    public $_metadata;
    public $_cdata_buf;
    public $_datestamp;

    public function __construct($_parser, $_baseURL, $_metadataPrefix)
    {
        parent::__construct($_parser);

        $this->resumptionToken = null;
        $this->identifier = null;
        $this->title = array();
        $this->metadataPrefix = $_metadataPrefix;
        $this->search_text = array();
        $this->tagstack = array();
        $this->baseURL = $_baseURL;
        $this->delete_flag = false;
        $this->_creator = array();
        $this->_last_update_date = '';
        $this->_creation_date = '';
        $this->_date = '';
        $this->_resource_url = array();
        $this->_namespaces = array();
        $this->_metadata = array();
        $this->_cdata_buf = '';
        $this->_datestamp = '';
    }

    public function startElementHandler($parser, $name, $attribs)
    {
        array_push($this->tagstack, $name);
        $this->_cdata_buf = '';
        if ($name == 'RECORD') {
            global $xoopsDB;
            $xoopsDB->setLogger(new XoopsLogger());

            // initialize following value for each records
            $this->title = null;
            $this->search_text = array();
        }
        if ($name == 'HEADER') {
            if (isset($attribs['STATUS'])
                && $attribs['STATUS'] == 'deleted'
            ) {
                $this->delete_flag = true;
            } else {
                $this->delete_flag = false;
            }
        }
    }

    public function endElementHandler($parser, $name)
    {
        array_push($this->search_text, ' ');
        if (end($this->tagstack) == 'RESUMPTIONTOKEN') {
            $this->resumptionToken = $this->_cdata_buf;
            array_pop($this->tagstack);

            return;
        } elseif (end($this->tagstack) == 'IDENTIFIER'
            && prev($this->tagstack) == 'HEADER'
        ) {
            $this->addMetadataField(end($this->tagstack), $this->_cdata_buf, XOONIPS_METADATA_CATEGORY_ID);
            $this->identifier = $this->_cdata_buf;
            array_pop($this->tagstack);

            return;
        } elseif (end($this->tagstack) == 'DATESTAMP'
            && prev($this->tagstack) == 'HEADER'
        ) {
            $this->_datestamp = $this->_cdata_buf;
            array_pop($this->tagstack);

            return;
        } elseif ($this->getElementName(end($this->tagstack)) == 'TITLE'
        ) {
            $this->addMetadataField(end($this->tagstack), $this->_cdata_buf, XOONIPS_METADATA_CATEGORY_TITLE);
            $this->title[] = $this->_cdata_buf;
            array_pop($this->tagstack);

            return;
        } elseif ($this->getElementName(end($this->tagstack)) == 'CREATOR'
        ) {
            $this->addMetadataField(end($this->tagstack), $this->_cdata_buf, XOONIPS_METADATA_CATEGORY_CREATOR);
            $this->_creator[] = $this->_cdata_buf;
            array_pop($this->tagstack);

            return;
        } elseif ($name == 'RECORD') {
            $repository_handler = &xoonips_getormhandler('xoonips', 'oaipmh_repositories');
            $metadata_handler = &xoonips_getormhandler('xoonips', 'oaipmh_metadata');
            $unicode = &xoonips_getutility('unicode');

            $criteria = new Criteria('URL', $this->baseURL);
            $repositories = &$repository_handler->getObjects($criteria);
            if (!$repositories) {
                $this->search_text = array();
                array_pop($this->tagstack);

                return;
            }

            $metadata = &$metadata_handler->getByIdentifier($this->identifier);
            if ($metadata && $this->delete_flag) {
                $this->deleteMetadataFields($metadata->get('metadata_id'));
                $metadata_handler->delete($metadata);
                $this->search_text = array();
                array_pop($this->tagstack);

                return;
            }

            if (!$metadata) {
                $metadata = &$metadata_handler->create();
            }

            $searchutil = &xoonips_getutility('search');
            $str = implode(' ', $this->search_text);
            $str = mb_convert_encoding($str, 'UTF-8', mb_detect_encoding($str));
            $str = $searchutil->getFulltextData($str);

            $metadata->set('repository_id', $repositories[0]->get('repository_id'));
            $metadata->set('identifier', mb_strcut($unicode->decode_utf8($this->identifier, xoonips_get_server_charset(), 'h'), 0, 255));
            $metadata->set('datestamp', $this->_datestamp);
            $metadata->set('format', mb_strcut($unicode->decode_utf8($this->metadataPrefix, xoonips_get_server_charset(), 'h'), 0, 255));
            $metadata->set('search_text', $str);
            $metadata->set('title', count($this->title) > 0 ? $unicode->decode_utf8($this->title[0], xoonips_get_server_charset(), 'h') : '');
            $metadata->set('creator', count($this->_creator) > 0 ? mb_strcut($unicode->decode_utf8($this->_creator[0], xoonips_get_server_charset(), 'h'), 0, 255) : '');
            $metadata->set('last_update_date', $this->_last_update_date);
            $metadata->set('creation_date', $this->_creation_date);
            $metadata->set('date', $this->_date);
            $metadata->set('link', count($this->_resource_url) > 0 ? $unicode->decode_utf8($this->_resource_url[0], xoonips_get_server_charset(), 'h') : '');
            $metadata->set('last_update_date_for_sort', $this->dateForSort($this->_last_update_date));
            $metadata->set('creation_date_for_sort', $this->dateForSort($this->_creation_date));
            $metadata->set('date_for_sort', $this->dateForSort($this->_date));
            $result = $metadata_handler->insert($metadata, true);
            if (!$result) {
                die('cannot insert metadata');
            }

            $this->insertMetadataFeilds($metadata->get('metadata_id'));

            // cleanup members
            $this->identifier = null;
            $this->title = array();
            $this->search_text = array();
            $this->delete_flag = false;
            $this->_creator = array();
            $this->_last_update_date = '';
            $this->_creation_date = '';
            $this->_date = '';
            $this->_resource_url = array();
            $this->_metadata = array();
            $this->_cdata_buf = '';
            $this->_datestamp = '';

            array_pop($this->tagstack);
        } else {
            array_pop($this->tagstack);
        }
    }

    public function characterDataHandler($parser, $data)
    {
        $this->search_text[] = $data;
        $this->_cdata_buf .= $data;
    }

    public function getResumptionToken()
    {
        return $this->resumptionToken;
    }

    public function getIdentifier()
    {
        return $this->identifier;
    }

    public function dateForSort($dateString)
    {
        $textutil = &xoonips_getutility('text');

        return gmstrftime('%Y-%m-%d %T', $textutil->iso8601_to_timestamp($dateString));
    }

    /**
     * Get namespace prefix and namespace URI from attributes.
     *
     * @param array attrs array of associative array of attributes
     *   ( key => value )
     *
     * @return array array of namespace array(namespace prefix => URI)
     */
    public function getNamespaceArray($attrs)
    {
        $result = array();
        foreach ($attrs as $key => $val) {
            $tmp = explode(':', $key);
            // skip other attribute
            if (strtolower($tmp[0]) != 'xmlns') {
                continue;
            }
            if (count($tmp) == 1) {
                // add namespace as default
                $result[''] = $val;
            } else {
                // add namespace
                $result[$tmp[1]] = $val;
            }
        }

        return $result;
    }

    /**
     * Get namespace prefix from element name.
     * Empty string if no namespace prefix.
     *
     * @param string $elementname element name(including namespace prefix)
     *
     * @return string namespace prefix
     */
    public function getNamespacePrefix($elementname)
    {
        $tmp = explode(':', $elementname);
        if (count($tmp) == 1) {
            return '';
        }

        return $tmp[0];
    }

    /**
     * Get element name without namespace prefix.
     *
     * @param string $elementname element name(including namespace prefix)
     *
     * @return string element name
     */
    public function getElementName($elementname)
    {
        $tmp = explode(':', $elementname);
        if (count($tmp) == 1) {
            return $tmp[0];
        } elseif (count($tmp) == 2) {
            return $tmp[1];
        }

        return '';
    }

    /**
     * add metadta field to _metadata member.
     *
     * @param string $elementname  element name(includeing namespace prefix)
     * @param string $value
     * @param string $categoryname (default is '')
     */
    public function addMetadataField($elementname, $value, $categoryname = '')
    {
        $this->_metadata[] = array(
            'name' => $this->getElementName($elementname),
            'category_name' => $categoryname,
            'value' => $value,
            'namespace' => $this->getNamespacePrefix($elementname),
            'namespace_uri' => $this->getNamespaceUri($this->getNamespacePrefix($elementname)),
        );
    }

    public function getNamespaceUri($namespacePrefix)
    {
        if (array_key_exists($namespacePrefix, $this->_namespaces)) {
            return $this->_namespaces[$namespacePrefix];
        } else {
            return '';
        }
    }

    /**
     * insert metadata fields.
     *
     * @param int metadata id of metadata fields
     */
    public function insertMetadataFeilds($metadata_id)
    {
        $unicode = &xoonips_getutility('unicode');
        $handler = &xoonips_getormhandler('xoonips', 'oaipmh_metadata_field');
        $this->deleteMetadataFields($metadata_id);

        foreach ($this->_metadata as $key => $field) {
            $orm = &$handler->create();
            $orm->set('name', $field['name']);
            $orm->set('metadata_id', $metadata_id);
            $orm->set('format', $this->metadataPrefix);
            $orm->set('category_name', $field['category_name']);
            $orm->set('value', $unicode->decode_utf8($field['value'], xoonips_get_server_charset(), 'h'));
            $orm->set('ordernum', intval($key) + 1);
            $orm->set('namespace', $field['namespace']);
            $orm->set('namespace_uri', $field['namespace_uri']);
            $result = $handler->insert($orm, true);
        }
    }

    /**
     * delete all metadata field of metadata.
     *
     * @param int metadata id to delete
     */
    public function deleteMetadataFields($metadata_id)
    {
        $handler = &xoonips_getormhandler('xoonips', 'oaipmh_metadata_field');
        $criteria = new Criteria('metadata_id', intval($metadata_id));
        $handler->deleteAll($criteria, true);
    }
}
