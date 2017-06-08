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

defined('XOOPS_ROOT_PATH') || exit('XOOPS root path not defined');

// load metadata.php message catalog
$langman = &xoonips_getutility('languagemanager');
$langman->read('metadata.php');

// definition of class for OAI-PMH

// 2007-06-11
// * Implementation of 'ListSets' verb response is contributed by KEIO
//   University.
// 2007-10-01
// * Fixed OAI-PMH malformed response and 'badArgument' bugs contributed by
//   KEIO University.

class OAIPMH
{
    /**
     * hash to manage OAIPMHHandler ( key=name of metadataPrefix, value=instance of OAIPMHHandler).
     */
    public $handlers;

    /**
     * base URL of repository.
     */
    public $baseURL;

    /**
     * name of repository.
     */
    public $repositoryName;

    /**
     * array of administrator's e-mail address.
     */
    public $adminEmails;

    /**
     * @param baseURL
     * @param repositoryName
     * @param adminEmails: administrator's e-mail address (charactor strings or array)
     */
    public function OAIPMH($baseURL = null, $repositoryName = null, $adminEmails = null)
    {
        /* constructer for PHP3, PHP4 */
        $this->baseURL = $baseURL;
        $this->repositoryName = $repositoryName;
        if (!is_array($adminEmails)) {
            $this->adminEmails = array($adminEmails);
        } else {
            $this->adminEmails = $adminEmails;
        }

        $this->handlers = array();
    }

    /**
     * @param baseURL
     * @param repositoryName
     * @param adminEmails: administrator's e-mail address (charactor strings or array)
     */
    public function __construct($baseURL = null, $repositoryName = null, $adminEmails = null)
    {
        /* constructer for PHP5 */
        self::OAIPMH($baseURL, $repositoryName, $adminEmails);
    }

    public function __destruct()
    {
    }

    /**
     * register OAIPMHHandler.
     *
     * @param class: instance of OAIPMHHandler
     */
    public function addHandler($class)
    {
        $this->handlers[$class->getName()] = $class;
    }

    /**
     * delete OAIPMHHandler.
     *
     * @param class: instance of OAIPMHHandler
     */
    public function removeHandler($class)
    {
        $this->handlers[$class->getName()] = null;
    }

    /**
     * return list of OAIPMHHandler.
     *
     * @return array of instances in registered handler
     */
    public function listHandlers()
    {
        return $this->handlers;
    }

    /**
     * generate <request>.
     *
     * @param attrs: hash parameters about demand of list <br /> ex:arary( 'verb' => 'GetRecord', 'identifier' => 'xxxx' )
     *
     * @return string of <request>
     */
    public function request($attrs)
    {
        $request = '<request';
        foreach (array('verb', 'identifier', 'metadataPrefix', 'from', 'until', 'set', 'resumptionToken') as $k) {
            if (array_key_exists($k, $attrs)) {
                // keio igarashi add 2007.10.01 from
                // remove ["]
                $attrs[$k] = str_replace('"', '', $attrs[$k]);
                // keio igarashi add 2007.10.01 to
                $request .= " ${k}=\"".$attrs[$k].'"';
            }
        }
        $request .= '>';
        $request .= $this->baseURL;
        $request .= "</request>\n";

        return $request;
    }

    /**
     * generate <ListMetadataFormats>.
     *
     * @return <ListMetadataFormats>
     */
    public function ListMetadataFormats($attrs)
    {
        $lines = array();
        foreach ($this->handlers as $hdl) {
            $fmt = false;
            if (isset($attrs['identifier'])) {
                $conv_str = $hdl->convertIdentifierFormat($attrs['identifier']);
                if ($conv_str != '') {
                    $fmt = $hdl->metadataFormat($conv_str);
                }
            } else {
                $fmt = $hdl->metadataFormat();
            }
            if (!$fmt) {
                continue;
            }        // item specified by $identifier doesn't support this format.
            $lines[] = $fmt;
        }

        if (count($lines) == 0) {
            return $this->header()
                .$this->request($attrs)
                .$this->error('noMetadataFormats', '')
                .$this->footer();
        }

        if (isset($attrs['identifier'])) {
            $identifier = $attrs['identifier'];
        } else {
            $identifier = null;
        }

        return $this->header()
            .$this->request(array('verb' => 'ListMetadataFormats', 'identifier' => $identifier))
            ."<ListMetadataFormats>\n".implode("\n", $lines)
            ."</ListMetadataFormats>\n".$this->footer();
    }

    /**
     * generate <Identify>.
     *
     * @return <Identify>
     */
    public function Identify()
    {
        $xml = '<repositoryName>'.$this->repositoryName.'</repositoryName>
<baseURL>'.$this->baseURL.'</baseURL>
<protocolVersion>2.0</protocolVersion>
<deletedRecord>transient</deletedRecord>
<granularity>YYYY-MM-DDThh:mm:ssZ</granularity>
<earliestDatestamp>'.gmdate("Y-m-d\TH:i:s\Z", 0).'</earliestDatestamp>
';
        foreach ($this->adminEmails as $email) {
            $xml .= "<adminEmail>${email}</adminEmail>
";
        }

        return $this->header()
            .$this->request(array('verb' => 'Identify'))
            ."<Identify>\n".$xml."</Identify>\n".$this->footer();
    }

    /**
     * @return string of xml, <OAI-PMH>, <responseDate>
     */
    public function header()
    {
        $date = gmdate("Y-m-d\TH:i:s\Z", time());

        return '<?xml version="1.0" encoding="UTF-8" ?>
<OAI-PMH xmlns="http://www.openarchives.org/OAI/2.0/"
         xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/
         http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd">
<responseDate>'.$date.'</responseDate>
';
    }

    /**
     * @return </OAI-PMH>
     */
    public function footer()
    {
        return '</OAI-PMH>
';
    }

    //call same name function of OAIPMHHandler class.
    //judges $metadataPrefix which OAIPMHHandler to use.
    //return error 'badArgument', if arguments are few.
    //return error 'cannnotDisseminateFormat', if Handler for $metadetaPrefix isn't registered.
    //The errors occered in handler aren't concerned(Handler return <error>.
    public function GetRecord($args)
    {
        $attrs = array_merge($args, array('verb' => 'GetRecord'));
        if (count($args) == 3 && isset($args['verb']) && isset($args['metadataPrefix']) && isset($args['identifier'])) {
            // keio igarashi add 2007.10.01 from
            // ["] is error in identifier. and fix badArguments -> badArgument
            if (strpos($args['identifier'], '"')) {
                return $this->header()
                    .$this->request($attrs)
                    //.$this->error( 'badArguments', '' )
                    .$this->error('badArgument', '')
                    .$this->footer();
            }
            // keio igarashi add 2007.10.01 to
        } else {
            return $this->header()
                .$this->request($attrs)
                .$this->error('badArgument', '')
                .$this->footer();
        }

        if (!isset($args['identifier'])) {
            return $this->header()
                .$this->request($attrs)
                .$this->error('badArgument', 'identifier is not specified')
                .$this->footer();
        }
        if (!array_key_exists($args['metadataPrefix'], $this->handlers)) {
            return $this->header()
                .$this->request($attrs)
                .$this->error('cannotDisseminateFormat', '')
                .$this->footer();
        }

        return $this->header()
            .$this->request($attrs)
            .$this->handlers[$args['metadataPrefix']]->GetRecord($args)
            .$this->footer();
    }

    public function ListIdentifiers($args)
    {
        $attrs = array_merge($args, array('verb' => 'ListIdentifiers'));
        if (!isset($args['metadataPrefix']) && !isset($args['resumptionToken'])) {
            return $this->header()
                .$this->request($attrs)
                .$this->error('badArgument', '')
                .$this->footer();
        }

        if (isset($args['resumptionToken'])) {
            $result = getResumptionToken($args['resumptionToken']);
            // keio igarashi add 2007.10.01 from
            // add resumptionToken process
            //if( !$result ) {
            if (!$result || !array_search($args['resumptionToken'], $result)) {
                // keio igarashi add 2007.10.01 to
                return $this->header()
                    .$this->request($attrs)
                    // keio igarashi add 2007.10.01 from
                    // add badArgument error
                    .$this->error('badArgument', '')
                    // keio igarashi add 2007.10.01 to
                    .$this->error('badResumptionToken', '')
                    .$this->footer();
            }
            $metadataPrefix = $result['metadata_prefix'];
        } else {
            $metadataPrefix = $args['metadataPrefix'];
        }

        return $this->header()
            .$this->request($attrs)
            .$this->handlers[$metadataPrefix]->ListIdentifiers($args)
            .$this->footer();
    }

    public function ListRecords($args)
    {
        $attrs = array_merge($args, array('verb' => 'ListRecords'));
        if (!isset($args['metadataPrefix']) && !isset($args['resumptionToken'])) {
            return $this->header()
                .$this->request($attrs)
                .$this->error('badArgument', '')
                .$this->footer();
        }
        if (isset($args['resumptionToken'])) {
            $result = getResumptionToken($args['resumptionToken']);
            // keio igarashi add 2007.10.01 from
            // add resumptionToken process
            //if( !$result ) {
            if (!$result || !array_search($args['resumptionToken'], $result)) {
                // keio igarashi add 2007.10.01 to
                return $this->header()
                    .$this->request($attrs)
                    // keio igarashi add 2007.10.01 from
                    // add badArgument error
                    .$this->error('badArgument', '')
                    // keio igarashi add 2007.10.01 to
                    .$this->error('badResumptionToken', '')
                    .$this->footer();
            }
            $metadataPrefix = $result['metadata_prefix'];
        } else {
            $metadataPrefix = $args['metadataPrefix'];
        }

        if (!isset($this->handlers[$metadataPrefix])) {
            return $this->header()
                .$this->request($attrs)
                .$this->error('cannotDisseminateFormat', '')
                .$this->footer();
        }
        // keio igarashi add 2007.10.01 from
        // add [from] [until] check process
        if ((isset($args['from']) && !preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}/', $args['from'])) || (isset($args['until']) && !preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}/', $args['until']))) {
            return $this->header()
                .$this->request($attrs)
                .$this->error('badArgument', '')
                .$this->footer();
        }
        if (isset($args['from']) && isset($args['until'])) {
            $from_format = '1';
            $until_format = '1';
            if (preg_match('/[0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}:[0-9]{2}Z/', $args['from'])) {
                $from_format = '2';
            }
            if (preg_match('/[0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}:[0-9]{2}Z/', $args['until'])) {
                $until_format = '2';
            }
            if ($from_format != $until_format) {
                return $this->header()
                    .$this->request($attrs)
                    .$this->error('badArgument', '')
                    .$this->footer();
            }
        }
        // keio igarashi add 2007.10.01 to
        return $this->header()
            .$this->request($attrs)
            .$this->handlers[$metadataPrefix]->ListRecords($args)
            .$this->footer();
    }

    public function ListSets($args)
    {
        $attrs = array_merge($args, array('verb' => 'ListSets'));
        $limit_row = REPOSITORY_RESPONSE_LIMIT_ROW;
        $path_alllist = null;
        $msg = null;
        $start_index = 0;
        $resumptionToken = null;
        $resumptionToken_msg = '';
        // get item type list
        $itemtypes = null;
        xnp_get_item_types($itemtypes);
        // check resumptionToken parameter.
        if (isset($args['resumptionToken'])) {
            $resumptionToken = $args['resumptionToken'];
            $result = getResumptionToken($resumptionToken);
            // echo "result="; var_dump($result); echo "\n";
            if (count($result) > 1) {
                if (isset($result['last_item_id'])) {
                    $start_index = $result['last_item_id'];
                }
                if (isset($result['args']['item_all_count'])) {
                    $item_all_count = $result['args']['item_all_count'];
                }
                //expire resumptionToken if index tree is modified after resumptionToken has published
                $path_alllist = xnpListIndexTree(XOONIPS_LISTINDEX_PUBLICONLY);
                if (!$path_alllist || (count($path_alllist) + count($itemtypes)) != $item_all_count) {
                    expireResumptionToken($args['resumptionToken']);
                    $msg = $this->error('badResumptionToken', 'repository has been modified');
                }
            } else {
                $msg = $this->error('badResumptionToken', '');
            }
        }
        if (!$msg) {
            // list sets
            if (!$path_alllist) {
                $path_alllist = xnpListIndexTree(XOONIPS_LISTINDEX_PUBLICONLY);
            }
            $spec_list = $path_alllist;
            // add item type
            if ($itemtypes) {
                foreach ($itemtypes as $v) {
                    $spec_list[] = $v;
                }
            }
            // create string by item index and item type
            $ct = min($start_index + $limit_row, count($spec_list));
            for ($i = $start_index; $i < $ct; ++$i) {
                $v = $spec_list[$i];
                if (isset($v['id_fullpath'])) {
                    $path_ar = explode(',', $v['id_fullpath']);
                    $spec = 'index'.implode(':index', $path_ar);
                    $setname = $v['fullpath'];
                } else {
                    $spec = $v['name'];
                    $setname = $v['display_name'];
                }
                if (extension_loaded('mbstring')) {
                    $setname = mb_convert_encoding($setname, 'UTF-8', _CHARSET);
                } else {
                    $setname = utf8_encode($setname);
                }
                $setname = htmlspecialchars($setname, ENT_QUOTES);
                $msg .= "    <set>\n"
                    .'        <setSpec>'.$spec."</setSpec>\n"
                    .'        <setName>'.$setname."</setName>\n"
                    ."    </set>\n";
            }
            // set or expire resumption token
            $leftcount = count($spec_list) - $start_index;
            if ($resumptionToken && $leftcount < $limit_row) {
                expireResumptionToken($resumptionToken);
            }
            if ($leftcount > $limit_row) {
                $args['item_all_count'] = count($spec_list);        // set optional value(count).
                $resumptionToken = session_id();
                setResumptionToken($resumptionToken, '', 'ListSets', $args, $ct, $limit_row, time() + REPOSITORY_RESUMPTION_TOKEN_EXPIRE_TERM);
                $resumptionToken_msg = "<resumptionToken>${resumptionToken}</resumptionToken>\n";
            } else {
                $resumptionToken_msg = '';
            }

            return $this->header()
                .$this->request($attrs)
                ."<ListSets>\n"
                .$msg
                .$resumptionToken_msg
                ."</ListSets>\n"
                .$this->footer();
        } else {    // error
            return $this->header()
                .$this->request($attrs)
                .$msg
                .$this->footer();
        }
    }

    /**
     * @param string $errorcode
     * @param string $text
     */
    public function error($errorcode, $text)
    {
        return "<error code='${errorcode}'>${text}</error>\n";
    }
}

class OAIPMHHandler
{
    public $metadataPrefix;

    public function OAIPMHHandler()
    {
        /* constructer for PHP3, PHP4 */
    }

    public function __construct()
    {
        /* constructer for PHP5 */
    }

    public function __destruct()
    {
    }

    public function getName()
    {
        return $this->metadataPrefix;
    }

    /**
     * Analize $identifier, and return by dividing $identifier into nijc_code, item_id, item_type_id.<br />
     *   support format: [nijc code]/[item type id].[item id].
     *
     * @param identifier: id to analize
     *
     * @return array( 'nijc_code' => NIJC issue code, 'item_type_id' => id of itemtypes, 'item_id' => id of item ) or <br />
     * @return false: failure in analysis
     */
    public function parseIdentifier($identifier)
    {
        $match = array();
        if (preg_match("/([^\/]+)\/([0-9]+)\.([0-9]+)/", $identifier, $match) == 0) {
            return false;
        }

        return array('nijc_code' => $match[1], 'item_type_id' => $match[2], 'item_id' => $match[3]);
    }

    /**
     * Analize $identifier, and return by dividing $identifier into nijc_code, sort id<br />
     *   support format: [own nijc code]:[XNP_CONFIG_DOI_FIELD_PARAM_NAME]/[sort id(doi)].
     *
     * @param identifier: id to analize
     *
     * @return array( 'nijc_code' => NIJC issue code, 'sort_id' => sort id of item (doi)
     * @return false: failure in analysis
     */
    public function parseIdentifier2($identifier)
    {
        $match = array();
        if (preg_match("/([^\/]+):".XNP_CONFIG_DOI_FIELD_PARAM_NAME.'\\/([^<>]+)/', $identifier, $match) == 0) {
            return false;
        }

        return array('nijc_code' => $match[1], 'doi' => $match[2]);
    }

    /**
     * convert id format from "[nijc_code]:[XNP_CONFIG_DOI_FIELD_PARAM_NAME]/[sort id(doi)]" to "[nijc_code]/[item type id].[item id]".
     *
     * @param id OAI-PMH identifier
     *
     * @return converted id string
     * @return "":       nijc_code not equal own nijc_code or doi above XNP_CONFIG_DOI_FIELD_PARAM_MAXLEN
     */
    public function convertIdentifierFormat($id)
    {
        $xconfig_handler = &xoonips_getormhandler('xoonips', 'config');
        $id_str = $id;
        $parsed = $this->parseIdentifier2($id_str);
        if ($parsed) {
            if (strlen($parsed['doi']) > XNP_CONFIG_DOI_FIELD_PARAM_MAXLEN) {
                return '';
            }
            // check valid support nijc_code.
            if ($parsed['nijc_code'] != $xconfig_handler->getValue('repository_nijc_code')) {
                return '';
            }

            $iids = array();
            $item_info = '';
            $res = xnpGetItemIdByDoi($parsed['doi'], $iids);
            if ($res == RES_OK && isset($iids[0])
                && xnp_get_item($_SESSION['XNPSID'], $iids[0], $item_info) == RES_OK
            ) {
                $id_str = $parsed['nijc_code'].'/'.$item_info['item_type_id'].'.'.$iids[0];
            } else {
                return '';
            }
        }

        return $id_str;
    }

    public function oaipmh_header($identifier, $index_tree_list)
    {
        global $xoopsDB;
        $xconfig_handler = &xoonips_getormhandler('xoonips', 'config');
        $parsed = $identifier;
        $lines = array();
        $status = array();
        if (xnp_get_item_status($parsed['item_id'], $status) == RES_OK) {
            $datestamp = max($status['created_timestamp'], $status['modified_timestamp'], $status['deleted_timestamp']);
            if ($status['is_deleted'] == 1) {
                $lines[] = '<header status="deleted">';
            } else {
                $lines[] = '<header>';
            }

            $nijc_code = $xconfig_handler->getValue('repository_nijc_code');
            $basic_info = xnpGetItemBasicInfo($parsed['item_id']);
            $id_str = '';
            if ($basic_info && $basic_info['doi'] != '') {
                if (!empty($nijc_code)) {
                    $id_str = "$nijc_code:".XNP_CONFIG_DOI_FIELD_PARAM_NAME.'/'.$basic_info['doi'];
                }
            }
            if ($id_str == '') {
                $id_str = $nijc_code.'/'.$parsed['item_type_id'].'.'.$parsed['item_id'];
            }

            $lines[] = "<identifier>${id_str}</identifier>";
            $lines[] = '<datestamp>'.gmdate("Y-m-d\TH:i:s\Z", $datestamp).'</datestamp>';
            $sql = 'SELECT link.index_id FROM '
                .$xoopsDB->prefix('xoonips_index_item_link').' as link LEFT JOIN '
                .$xoopsDB->prefix('xoonips_index').' as idx on link.index_id=idx.index_id '
                .' WHERE link.item_id='.intval($parsed['item_id'])
                .' AND open_level='.OL_PUBLIC;
            $result = $xoopsDB->query($sql);
            if ($result) {
                while (list($xid) = $xoopsDB->fetchRow($result)) {
                    // echo "xid=$xid\n";
                    $xid_path = explode(',', $index_tree_list[$xid]['id_fullpath']);
                    $lines[] = '<setSpec>index'.implode(':index', $xid_path).'</setSpec>';
                }
            }
            $lines[] = '<setSpec>'.$parsed['item_type_name'].'</setSpec>';
            $lines[] = '</header>';

            return implode("\n", $lines);
        }
    }

    /**
     * @param identifier
     *
     * @return boolean   <metadataFormat> ... </metadataFormat>
     * @return boolean not support this format
     */
    public function metadataFormat($identifier)
    {
        return false;
    }

    public function GetRecord($args)
    {
    }

    public function ListIdentifiers($args)
    {
    }

    public function ListRecords($attr)
    {
    }

    public function ListSets($args)
    {
    }

    /**
     * @param string $errorcode
     */
    public function error($errorcode, $text)
    {
        return "<error code='${errorcode}'>${text}</error>\n";
    }
}

class HarvesterHandler
{
    public $parser;
    public $lastError;

    public function __construct($_parser)
    {
        $this->lastError = null;
        $this->parser = $_parser;
        xml_set_object($this->parser, $this);
        xml_set_element_handler($this->parser, 'startElementHandler', 'endElementHandler');
        xml_set_character_data_handler($this->parser, 'characterDataHandler');
    }

    public function startElementHandler($parser, $name, $attribs)
    {
    }

    public function endElementHandler($parser, $name)
    {
    }

    public function characterDataHandler($parser, $data)
    {
    }

    public function last_error()
    {
        return $this->lastError;
    }
}

/**
 * record resumptionToken and the related information.
 *
 * @param resumption_token
 * @param metadata_prefix
 * @param verb
 * @param args: hash in arguments specified verb
 * @param last_item_id: maximum value of item_id to applied in last demands
 * @param limit_row: number of results applied this resumptionToken
 * @param expire_date: Expiration date of this resumptionToken
 * @param publish_date: Of the date of this resumptionToken's issue
 * @param string $resumption_token
 * @param string $metadata_prefix
 * @param string $verb
 * @param integer $expire_date
 *
 * @return nothing
 */
function setResumptionToken($resumption_token, $metadata_prefix, $verb, $args, $last_item_id, $limit_row, $expire_date, $publish_date = null)
{
    global $xoopsDB;

    if ($publish_date == null) {
        $publish_date = time();
    }
    (method_exists('MyTextSanitizer', 'sGetInstance') and $myts = &MyTextSanitizer::sGetInstance()) || $myts = &MyTextSanitizer::getInstance();
    $table = $xoopsDB->prefix('xoonips_oaipmh_resumption_token');
    $sql = "INSERT INTO ${table} VALUES ('".$myts->addSlashes($resumption_token)
        ."', '${metadata_prefix}', '${verb}', '".$myts->addSlashes(serialize($args))
        ."', ${last_item_id}, ${limit_row}, ${publish_date}, ${expire_date} )";

    return $xoopsDB->queryF($sql);
}

/**
 * return the related information of resumptionToken.
 *
 * @param resumption_token
 *
 * @return array(
 *                'metadata_prefix' => metadataPrefix,
 *                'verb' => verb,
 *                'args' => hash in arguments specified verb,
 *                'last_item_id' => maximum value of item_id to applied in last demands,
 *                'limit_row' => number of results applied this resumptionToken,
 *                'expire_date' => Expiration date of this resumptionToken,
 *                'publish_date' => Of the date of this resumptionToken's issue )
 * @return false: not found resumptionToken
 */
function getResumptionToken($resumption_token)
{
    global $xoopsDB;

    (method_exists('MyTextSanitizer', 'sGetInstance') and $myts = &MyTextSanitizer::sGetInstance()) || $myts = &MyTextSanitizer::getInstance();
    $table = $xoopsDB->prefix('xoonips_oaipmh_resumption_token');
    $sql = "SELECT resumption_token, metadata_prefix, args, last_item_id, publish_date, expire_date, verb FROM ${table} WHERE resumption_token=\"".$myts->stripSlashesGPC($resumption_token)
        .'"';
    $result = $xoopsDB->query($sql);
    $ret = $xoopsDB->fetchArray($result);
    $ret['args'] = unserialize($ret['args']);

    return $ret;
}

/**
 * expire resumptionToken.
 * expire other tokens of cutting at expiration date, too.
 *
 * @param resumption_token: token to expire
 */
function expireResumptionToken($resumptionToken)
{
    global $xoopsDB;

    (method_exists('MyTextSanitizer', 'sGetInstance') and $myts = &MyTextSanitizer::sGetInstance()) || $myts = &MyTextSanitizer::getInstance();
    $table = $xoopsDB->prefix('xoonips_oaipmh_resumption_token');
    $sql = "DELETE FROM ${table} WHERE resumption_token=\"".$myts->stripSlashesGPC($resumptionToken)
        .'" OR expire_date < UNIX_TIMESTAMP( NOW() )';
    $result = $xoopsDB->queryF($sql);
}

/**
 * change expression of ISO8601 to UTC. return false when we can't change.
 * Usage: ISO8601toUTC( "2005-08-01T12:00:00Z" );
 * Usage: ISO8601toUTC( "2005-08-01" );.
 */
function ISO8601toUTC($str)
{
    if (preg_match('/([0-9]{4})-([0-9]{2})-([0-9]{2})(T([0-9]{2}):([0-9]{2}):([0-9]{2})Z)?/', $str, $match) == 0) {
        return 0;
    }
    if (!isset($match[5])) {
        $match[5] = 0;
    }
    if (!isset($match[6])) {
        $match[6] = 0;
    }
    if (!isset($match[7])) {
        $match[7] = 0;
    }

    return gmmktime($match[5], $match[6], $match[7], $match[2], $match[3], $match[1]);
}

// todo: $a = explode(',',$metajuniig[$num10]); $metajunii = $a[$num1];
function xnpGetMetadataJunii($id)
{
    $metajuniig = explode('/', _MD_XOONIPS_METADATA_JUNII);
    $i = 0;
    $num = $id;
    $num10 = ($num - ($num % 10)) / 10;
    $num1 = $num % 10;
    foreach ($metajuniig as $key => $value1) {
        $metajuniis = explode(',', $value1);
        $j = 0;
        foreach ($metajuniis as $key => $value2) {
            if (($i == $num10) && ($j == $num1)) {
                $metajunii = $value2;
            }
            ++$j;
        }
        ++$i;
    }

    return trim($metajunii);
}

function xnpGetMetadataJunii2($id)
{
    $metajuniig = explode('/', _MD_XOONIPS_METADATA_JUNII2);
    $i = 0;
    $num = $id;
    $num10 = ($num - ($num % 10)) / 10;
    $num1 = $num % 10;
    foreach ($metajuniig as $key => $value1) {
        $metajuniis = explode(',', $value1);
        $j = 0;
        foreach ($metajuniis as $key => $value2) {
            if (($i == $num10) && ($j == $num1)) {
                $metajunii = $value2;
            }
            ++$j;
        }
        ++$i;
    }

    return trim($metajunii);
}
