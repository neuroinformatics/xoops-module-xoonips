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

defined('XOOPS_ROOT_PATH') || exit('XOOPS root path not defined');

//  OAIPMHHandler Class for JUNII2

// 2007-06-11
// Implementation of 'ListSets' verb response is contributed by KEIO University.

class JUNII2Handler extends OAIPMHHandler
{
    public function JUNII2Handler()
    {
        /* constructer for PHP3, PHP4 */
        $this->metadataPrefix = 'junii2';
    }

    public function __construct()
    {
        /* constructer for PHP5 */
        self::JUNII2Handler();
    }

    public function __destruct()
    {
    }

    public function metadataFormat($identifier = null)
    {
        if ($identifier != null) {
            $parsed = parent::parseIdentifier($identifier);
            if (!$parsed) {
                return false;
            }        //$identifier is wrong

            $tmparray = array();
            if (xnp_get_item_types($tmparray) == RES_OK) {
                foreach ($tmparray as $i) {
                    if ($i['item_type_id'] == $parsed['item_type_id']) {
                        $itemtype = $i;
                        $item_type = $itemtype['display_name'];
                        break;
                    }
                }
            }

            include_once XOOPS_ROOT_PATH.'/modules/'.$itemtype['viewphp'];

            $f = $itemtype['name'].'SupportMetadataFormat';
            if (!function_exists($f)) {
                return false;
            }
            if (!$f($this->metadataPrefix, $parsed['item_id'])) {
                return false;
            }
        }

        return '<metadataFormat>
<metadataPrefix>junii2</metadataPrefix>
<schema>http://irdb.nii.ac.jp/oai/junii2.xsd</schema>
<metadataNamespace>http://irdb.nii.ac.jp/oai</metadataNamespace>
</metadataFormat>';
    }

    /**
     * generate XML from <record> to </record>.
     * error check
     * - nijc_code of Identifier = Value of Site Configuration
     * - item_type_id of Identifier => match installed itemtypes?
     * - item_id of Identiifer => match installed items?
     * - itemtypes generate metadeta of items.
     *
     * @param $identifier: identifier of item that generate XML
     * @param $index_tree_list: array of convert from index id to index path string
     *
     * @return array( generated XML, true ) success generate <record>
     * @return array( error XML, false ) In success generate <record>, return <errro>...</error>
     */
    public function record($identifier, $index_tree_list)
    {
        $xconfig_handler = &xoonips_getormhandler('xoonips', 'config');

        $parsed = $identifier;

        if ($parsed['is_deleted'] == 1) {
            //return only header if item is deleted
            return array("<record>\n".$this->oaipmh_header($identifier, $index_tree_list)
                          ."</record>\n", true, );
        }

        //return error if nijc_code mismatched
        $nijc_code = $xconfig_handler->getValue('repository_nijc_code');
        if (empty($nijc_code) || $nijc_code != $parsed['nijc_code']) {
            return array(parent::error('idDoesNotExist', ''), false);
        }

        //return error if item_id mismatched
        $item = array();
        $result = xnp_get_item($_SESSION['XNPSID'], $parsed['item_id'], $item);
        if ($result != RES_OK) {
            return array(parent::error('idDoesNotExist', 'item_id not found'), false);
        }

        //return error if item_type_id mismatched
        if ($result == RES_OK && $item['item_type_id'] != $parsed['item_type_id']) {
            return array(parent::error('idDoesNotExist', 'item_type_id not found'), false);
        }

        include_once XOOPS_ROOT_PATH.'/modules/'.$parsed['item_type_viewphp'];

        $f = $parsed['item_type_name'].'GetMetadata';
        if (!function_exists($f)) {
            return array(parent::error('idDoesNotExist', "function $f not defined"), false);
        }

        return array("<record>\n".$this->oaipmh_header($identifier, $index_tree_list)
                      .$f($this->metadataPrefix, $parsed['item_id'])
                      ."</record>\n", true, );
    }

    /**
     * Process demand of GetRecord, and return part of <GetRecord> in result.
     * generation of <record> uses record function.
     *
     * @see record
     *
     * @param args: hash contained argument of demand, array( 'identifier' => identifier of item )
     *
     * @return <GetRecord> in XML  success
     * @return <error>     in XML  failure
     */
    public function GetRecord($args)
    {
        $result = false;
        $id_str = $this->convertIdentifierFormat($args['identifier']);
        $param_identifier = parent::parseIdentifier($id_str);
        $identifiers = array();
        if (RES_OK == xnp_selective_harvesting(0, 0, null, $param_identifier['item_id'], 1, $identifiers) && count($identifiers) > 0) {
            $index_tree_list = xnpListIndexTree(XOONIPS_LISTINDEX_PUBLICONLY, true);
            $identifiers[0]['nijc_code'] = $param_identifier['nijc_code'];
            $identifiers[0]['item_type_id'] = $param_identifier['item_type_id'];
            list($xml, $result) = $this->record($identifiers[0], $index_tree_list);
            if (!$result) {
                return $xml;
            }

            return "<GetRecord>\n".$xml."</GetRecord>\n";
        }

        return parent::error('idDoesNotExist', '');
    }

    /**
     * process demand of GetIdentifiers, and return part of <GetIdentifiers> in result.
     * Generation of <record> uses record function.
     *
     * @see record
     *
     * @param args: hash contained demand argument. array( 'identifier' => identifier of item )
     *
     * @return <GetIdentifiers> in XML  success
     * @return <error>          in XML  failure
     */
    public function ListIdentifiers($args)
    {
        $xconfig_handler = &xoonips_getormhandler('xoonips', 'config');

        $from = 0;
        $until = 0;
        $set = null;
        $start_iid = 0;
        $limit_row = REPOSITORY_RESPONSE_LIMIT_ROW;
        $expire_term = REPOSITORY_RESUMPTION_TOKEN_EXPIRE_TERM;

        foreach (array('from', 'until', 'resumptionToken', 'set') as $k) {
            if (isset($args[$k])) {
                ${$k} = $args[$k];
            }
        }

        if ($from != 0) {
            $from = ISO8601toUTC($from);
        }
        if ($until != 0) {
            $until = ISO8601toUTC($until);
        }

        if (isset($args['resumptionToken'])) {
            $resumptionToken = $args['resumptionToken'];
        }
        if (isset($resumptionToken)) {
            $result = getResumptionToken($resumptionToken);
            if (!$result) {
                return parent::error('badResumptionToken', '');
            }
            if (isset($result['args']['from'])) {
                $from = ISO8601toUTC($result['args']['from']);
            }
            if (isset($result['args']['until'])) {
                $until = ISO8601toUTC($result['args']['until']);
            }
            if (isset($result['last_item_id'])) {
                $start_iid = $result['last_item_id'] + 1;
            }
            if (isset($result['limit_row'])) {
                $limit_row = $result['limit_row'];
            }
            if (isset($result['args']['set'])) {
                $set = $result['args']['set'];
            }
            if (isset($result['publish_date'])) {
                //expire resumptionToken if repository is modified after resumptionToken has published
                $identifiers = array();
                if (RES_OK == xnp_selective_harvesting((int) $result['publish_date'], 0, null, 0, 1, $identifiers)) {
                    if (count($identifiers) > 0) {
                        expireResumptionToken($resumptionToken);

                        return parent::error('badResumptionToken', 'repository has been modified');
                    }
                }
            }
        }

        $identifiers = array();
        if (RES_OK != xnp_selective_harvesting((int) $from, (int) $until, $set, $start_iid, (int) $limit_row, $identifiers) || count($identifiers) == 0) {
            return parent::error('noRecordsMatch', '');
        }

        $iids = array();
        foreach ($identifiers as $i) {
            $iids[] = $i['item_id'];
        }

        if (count($iids) == $limit_row) {
            $resumptionToken = session_id();
            setResumptionToken($resumptionToken, $this->metadataPrefix, 'ListIdentifiers', $args, max($iids), $limit_row, time() + $expire_term);
            $resumptionToken = "<resumptionToken>${resumptionToken}</resumptionToken>\n";
        } else {
            $resumptionToken = '';
        }
        $index_tree_list = xnpListIndexTree(XOONIPS_LISTINDEX_PUBLICONLY, true);
        $nijc_code = $xconfig_handler->getValue('repository_nijc_code');
        if (!empty($nijc_code)) {
            $headers = array();
            foreach ($identifiers as $identifier) {
                $headers[] = $this->oaipmh_header($identifier, $index_tree_list);
            }

            return "<ListIdentifiers>\n".implode("\n", $headers)
                .$resumptionToken."</ListIdentifiers>\n";
        } else {
            return parent::error('noRecordsMatch', '');
        }
    }

    /**
     * process demando of ListRecords, and return part of <ListRecords> in results.
     * generation of <record> uses record function.
     *
     * @see record
     *
     * @param args: hash contained demand argument. array( 'identifier' => identifier of items )
     *
     * @return <ListRecords> in XML  success
     * @return <error>       in XML  failure
     */
    public function ListRecords($args)
    {
        $xconfig_handler = &xoonips_getormhandler('xoonips', 'config');

        $from = 0;
        $until = 0;
        $set = null;
        $start_iid = 0;
        $limit_row = REPOSITORY_RESPONSE_LIMIT_ROW;
        $expire_term = REPOSITORY_RESUMPTION_TOKEN_EXPIRE_TERM;

        foreach (array('from', 'until', 'resumptionToken', 'set') as $k) {
            if (isset($args[$k])) {
                ${$k} = $args[$k];
            }
        }

        if ($from != 0) {
            $from = ISO8601toUTC($from);
        }
        if ($until != 0) {
            $until = ISO8601toUTC($until);
        }
        if (isset($resumptionToken)) {
            $result = getResumptionToken($resumptionToken);
            if (!$result) {
                return parent::error('badResumptionToken', '');
            }
            if (isset($result['args']['from'])) {
                $from = ISO8601toUTC($result['args']['from']);
            }
            if (isset($result['args']['until'])) {
                $until = ISO8601toUTC($result['args']['until']);
            }
            if (isset($result['last_item_id'])) {
                $start_iid = $result['last_item_id'] + 1;
            }
            if (isset($result['limit_row'])) {
                $limit_row = $result['limit_row'];
            }
            if (isset($result['args']['set'])) {
                $set = $result['args']['set'];
            }
            if (isset($result['publish_date'])) {
                //expire resumptionToken if repository is modified after resumptionToken has published
                $iids = array();
                if (RES_OK == xnp_selective_harvesting((int) $result['publish_date'], 0, null, 0, 1, $iids)) {
                    if (count($iids) > 0) {
                        expireResumptionToken($resumptionToken);

                        return parent::error('badResumptionToken', 'repository has been modified');
                    }
                }
            }
        }

        $identifiers = array();
        if (RES_OK != xnp_selective_harvesting((int) $from, (int) $until, $set, $start_iid, (int) $limit_row, $identifiers) || count($identifiers) == 0) {
            return parent::error('noRecordsMatch', '');
        }
        $iids = array();
        foreach ($identifiers as $i) {
            $iids[] = $i['item_id'];
        }

        if (isset($resumptionToken) && count($iids) < $limit_row) {
            expireResumptionToken($resumptionToken);
        } elseif (count($iids) == 0) {
            return parent::error('noRecordsMatch', '');
        }

        if (count($iids) == $limit_row) {
            $resumptionToken = session_id();
            setResumptionToken($resumptionToken, $this->metadataPrefix, 'ListRecords', $args, max($iids), $limit_row, time() + $expire_term);
            $resumptionToken = "<resumptionToken>${resumptionToken}</resumptionToken>\n";
        } else {
            $resumptionToken = '';
        }
        $index_tree_list = xnpListIndexTree(XOONIPS_LISTINDEX_PUBLICONLY, true);

        $nijc_code = $xconfig_handler->getValue('repository_nijc_code');
        if (!empty($nijc_code)) {
            $records = array();
            $errurs = array();
            foreach ($identifiers as $item) {
                list($xml, $result) = $this->record($item, $index_tree_list);
                if ($result) {
                    $records[] = $xml;
                } else {
                    $errors[] = $xml;
                }
            }
            if (count($identifiers) == 0) {
                return parent::error('noRecordsMatch', '');
            } else {
                return "<ListRecords>\n".implode("\n", $records)
                    .$resumptionToken."</ListRecords>\n";
            }
        } else {
            return parent::error('idDoesNotExist', 'nijc_code is not configured');
        }
    }
}
