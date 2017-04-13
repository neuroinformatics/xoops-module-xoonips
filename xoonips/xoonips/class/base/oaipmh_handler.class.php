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

class OAIPMHHarvester
{
    public $_dateFormat;
    public $_earliestDatestamp;
    public $_baseUrl;
    public $_metadataPrefix;
    public $_lastError;
    public $_lastStatus;
    public $_repositoryName;

    //public
    public function OAIPMHHarvester($_baseUrl)
    {
        $this->_baseUrl = $_baseUrl;
        $this->_lastError = null;
        $this->_lastStatus = null;
        $this->_metadataPrefix = null;
        $this->_dateFormat = null;
        $this->_earliestDatestamp = null;
        $this->_repositoryName = '';
    }

    public function __construct($_baseUrl)
    {
        /* constructer for PHP5 */
        $this->OAIPMHHarvester($_baseUrl);
    }

    public function harvest()
    {
        global $xoopsDB;
        $handler = &xoonips_getormhandler('xoonips', 'oaipmh_repositories');

        (method_exists(MyTextSanitizer, sGetInstance) and $ts = &MyTextSanitizer::sGetInstance()) || $ts = &MyTextSanitizer::getInstance();

        $criteria = new Criteria('URL', $this->_baseUrl);
        $repository = &$handler->getObjects($criteria);
        if (!$repository) {
            return false;
        }
        $repository[0]->set('last_access_date', time());
        $repository[0]->set('last_access_result', null);
        if ($handler->insert($repository[0], true)) {
            if ($this->Identify()) {
                $unicode = &xoonips_getutility('unicode');
                $repository[0]->set('repository_name', $unicode->decode_utf8($this->_repositoryName, xoonips_get_server_charset(), 'h'));
                $handler->insert($repository[0], true);
                if ($this->ListMetadataFormats()) {
                    $args = array('metadataPrefix' => $this->_metadataPrefix);
                    if ('' == $repository[0]->get('last_success_date')) {
                        $args['from'] = gmdate($this->_dateFormat, $this->_earliestDatestamp);
                    } else {
                        $args['from'] = gmdate($this->_dateFormat, $repository[0]->get('last_success_date'));
                    }

                    if ($this->ListRecords($args)) {
                        //update repositories table
                        $repository[0]->set('last_access_result', $this->_lastStatus);
                        $repository[0]->set('last_success_date', time());
                        $repository[0]->set('metadata_count', $this->getMetadataCount($repository[0]->get('repository_id')));

                        return $handler->insert($repository[0], true);
                    }
                }
            }
        } else {
            $this->_lastError = $xoopsDB->error();
        }

        //update repositories table(last_access_result)
        $repository[0]->set('last_access_result', $this->_lastError);
        $handler->insert($repository[0], true);

        return false;
    }

    public function last_error()
    {
        return $this->_lastError;
    }

    //private
    public function Identify()
    {
        $snoopy = &xoonips_getutility('snoopy');
        if (!is_object($snoopy)) {
            $this->_lastError = 'snoopy object is null';

            return false;
        }
        $url = $this->_baseUrl.'?verb=Identify';
        if (!$snoopy->fetch($url)) {
            $this->_lastError = "can't retrieve ".$url;

            return false;
        }
        $this->_lastError = $http_status = $snoopy->response_code;

        $this->parser = xml_parser_create('UTF-8');
        if (!$this->parser) {
            $this->_lastError = "can't create XML parser";

            return false;
        }
        $handler = new IdentifyHandler($this->parser);

        $result = $this->parse($snoopy->results);
        if (!$result) {
            // some error has occured in parse( $snoopy->results );
            return false;
        }
        xml_parser_free($this->parser);

        $this->_dateFormat = $handler->getDateFormat();
        if (!$this->_dateFormat) {
            $this->_lastError = 'value of <granularity> is wrong';

            return false;
        }

        $this->_earliestDatestamp = $handler->getEarliestDatestamp();
        $this->_repositoryName = $handler->getRepositoryName();

        return true;
    }

    public function ListMetadataFormats()
    {
        $snoopy = &xoonips_getutility('snoopy');
        if (!is_object($snoopy)) {
            $this->_lastError = 'snoopy object is null';

            return false;
        }
        $url = $this->_baseUrl.'?verb=ListMetadataFormats';
        if (!$snoopy->fetch($url)) {
            $this->_lastError = "can't retrieve ".$url;

            return false;
        }
        $this->_lastError = $http_status = $snoopy->response_code;

        $this->parser = xml_parser_create('UTF-8');
        if (!$this->parser) {
            $this->_lastError = "can't create XML parser";

            return false;
        }
        $handler = new ListMetadataFormatsHandler($this->parser);

        $result = $this->parse($snoopy->results);
        if (!$result) {
            // some error has occured in parse( $snoopy->results );
            return false;
        }
        xml_parser_free($this->parser);

        $this->_metadataPrefix = $handler->getMetadataPrefix();
        if (!$this->_metadataPrefix) {
            $this->_metadataPrefix = null;
            $this->_lastError = "can't retrieve <metadataPrefix>";

            return false;
        }

        return true;
    }

    public function ListRecords($args)
    {
        global $xoopsDB;
        if (!isset($args['metadataPrefix'])) {
            $this->_lastError = "'metadataPrefix' is not specified.";

            return false;
        }
        $resumptionToken = null;
        do {
            $url = $this->_baseUrl.'?verb=ListRecords';
            if ($resumptionToken == null) {
                foreach (array('metadataPrefix', 'from', 'until', 'set') as $k) {
                    if (isset($args[$k])) {
                        $url .= '&'.urlencode($k).'='.urlencode($args[$k]);
                    }
                }
            } else {
                $url .= '&resumptionToken='.htmlspecialchars($resumptionToken, ENT_QUOTES);
            }
            $snoopy = &xoonips_getutility('snoopy');
            if (!is_object($snoopy)) {
                $this->_lastError = "can't retrieve ${url}";
                $xoopsDB->setLogger(XoopsLogger::instance());

                return false;
            }
            if (!$snoopy->fetch($url)) {
                $this->_lastError = "can't retrieve ${url}";
                $xoopsDB->setLogger(XoopsLogger::instance());

                return false;
            }
            $this->_lastError = $this->_lastStatus = $snoopy->response_code;

            $this->parser = xml_parser_create('UTF-8');
            if (!$this->parser) {
                $this->_lastError = "can't create XML parser";
                $xoopsDB->setLogger(XoopsLogger::instance());

                return false;
            }

            $listRecordsHandler = &$this->createListRecordsHandler($this->parser, $this->_baseUrl, $args['metadataPrefix']);
            $result = $this->parse($snoopy->results);
            if (!$result) {
                //some erorr has occured
                if ($listRecordsHandler->getIdentifier() != null) {
                    $this->_lastError .= '[identifier]'
                        .$listRecordsHandler->getIdentifier();
                }
                $xoopsDB->setLogger(XoopsLogger::instance());
                //some error has occured in parse( $snoopy->results );
                return false;
            }
            xml_parser_free($this->parser);
            $resumptionToken = $listRecordsHandler->getResumptionToken();
        } while ($resumptionToken != null);
        $xoopsDB->setLogger(XoopsLogger::instance());

        return true;
    }

    public function parse(&$data)
    {
        if (!xml_parse($this->parser, $data)) {
            $this->_lastError = '[XMLParser]'
                .xml_error_string(xml_get_error_code($this->parser))
                .' at line '.xml_get_current_line_number($this->parser)
                .', column '.xml_get_current_column_number($this->parser);
            xml_parser_free($this->parser);

            return false;
        }

        return true;
    }

    /**
     * return number of metadata of repository.
     *
     * @param int $repository_id id of repository to get metadata count
     *
     * @return int number of metadata or zero if failure
     */
    public function getMetadataCount($repository_id)
    {
        $metadata_handler = &xoonips_getormhandler('xoonips', 'oaipmh_metadata');

        $metadata_criteria = new Criteria('repository_id', intval($repository_id));
        $rows = &$metadata_handler->getObjects($metadata_criteria, false, 'count(*)');
        if (!$rows) {
            return 0;
        } else {
            return $rows[0]->getExtraVar('count(*)');
        }
    }

    /**
     * @param
     * @param
     * @param
     *
     * @return
     */
    public function &createListRecordsHandler($parser, $baseUrl, $metadataPrefix)
    {
        $result = false;
        switch ($metadataPrefix) {
        case 'oai_dc':
            include_once __DIR__.'/oaipmh_oaidc_list_records_handler.class.php';
            $result = new OaidcListRecordsHandler($parser, $baseUrl);
            break;
        case 'junii':
            include_once __DIR__.'/oaipmh_junii_list_records_handler.class.php';
            $result = new JuniiListRecordsHandler($parser, $baseUrl);
            break;
        case 'junii2':
            include_once __DIR__.'/oaipmh_junii2_list_records_handler.class.php';
            $result = new Junii2ListRecordsHandler($parser, $baseUrl);
            break;
        }

        return $result;
    }
}
