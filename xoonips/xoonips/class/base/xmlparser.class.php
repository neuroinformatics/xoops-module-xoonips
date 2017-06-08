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
class XooNIpsXMLParser
{
    /**
     * the xml data, fetch() function will set results to this variable.
     *
     * @var string
     */
    public $_xml_data = '';

    /**
     * the error messages.
     *
     * @var string
     */
    public $_error_message = '';

    /**
     * the fetcher target url.
     *
     * @var string
     */
    public $_fetch_url = '';

    /**
     * the fetcher arguments.
     *
     * @var array
     */
    public $_fetch_arguments = array();

    /**
     * the parser character set.
     *
     * @var string
     */
    public $_parser_charset = 'UTF-8';

    /**
     * the xml document type.
     *
     * @var string
     */
    public $_parser_doctype = '';

    /**
     * the public id of xml document.
     *
     * @var string
     */
    public $_parser_public_id = '';

    /**
     * the system id of xml document.
     *
     * @var string
     */
    public $_parser_system_id = '';

    /**
     * the perser condition.
     *
     * @var string
     */
    public $_parser_condition = '';

    /**
     * constructor
     * normally, the is called from child classes only.
     */
    public function __construct()
    {
    }

    /**
     * get error message.
     *
     * @return string error message reference
     */
    public function get_error_message()
    {
        return $this->_error_message;
    }

    /**
     * fetch the xml data from target url.
     *
     * @return bool false if failure
     */
    public function fetch()
    {
        if (empty($this->_fetch_url)) {
            $this->_error_message = 'target url is empty';

            return false;
        }
        // create fetch url
        $arguments = array();
        if (!empty($this->_fetch_arguments)) {
            foreach ($this->_fetch_arguments as $k => $v) {
                $arguments[] = $this->encode_url($k).'='.$this->encode_url($v);
            }
        }
        $url = $this->create_url($this->_fetch_url, $arguments);

        // fetch data using snoopy class
        $snoopy = &xoonips_getutility('snoopy');
        if (!$snoopy->fetch($url)) {
            $this->_error_message = 'Failed to fetch results : "'.$url.'"';

            return false;
        }
        $this->_xml_data = &$snoopy->results;
        // echo $url."\n";
        // echo $this->_xml_data."\n";
        return true;
    }

    /**
     * parse the xml data.
     *
     * @return bool false if failure
     */
    public function parse()
    {
        if (!$this->_parser_check_doctype()) {
            return false;
        }
        $this->_parser_condition = '';
        $parser = xml_parser_create($this->_parser_charset);
        xml_set_object($parser, $this);
        xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
        xml_set_element_handler($parser, '_parser_start_element_handler', '_parser_end_element_handler');
        xml_set_character_data_handler($parser, '_parser_character_data_handler');
        xml_parse($parser, $this->_xml_data);
        xml_parser_free($parser);

        return true;
    }

    /**
     * encode url.
     *
     * @param string $str encoding string
     *
     * @return string encoded url
     */
    public function encode_url($str)
    {
        return urlencode($str);
    }

    /**
     * create url string.
     *
     * @param string $url       fetch url
     * @param array  $arguments url encoded parameters
     *
     * @return string created url string
     */
    public function create_url($url, $arguments)
    {
        return empty($arguments) ? $url : $url.'?'.implode('&', $arguments);
    }

    /**
     * virtual function of the start elemnt handler.
     *
     * @param string $attribs xml attribute
     */
    public function parser_start_element($attribs)
    {
    }

    /**
     * virtual function of the end elemnt handler.
     */
    public function parser_end_element()
    {
    }

    /**
     * virtual function of the character data handler.
     *
     * @param string $cdata character data
     */
    public function parser_character_data($cdata)
    {
    }

    /**
     * check doctype of xml
     * this function is a part of parse() function.
     *
     * @return bool false if failure
     */
    public function _parser_check_doctype()
    {
        if (empty($this->_xml_data)) {
            $this->_error_message = 'the parsing xml file is empty';

            return false;
        }
        $public_search = '/<!DOCTYPE\\s+(\\w+)\\s+PUBLIC\\s"([^"]+)"\\s+"([^"]+)"\\s*>/is';
        $system_search = '/<!DOCTYPE\\s+(\\w+)\\s+SYSTEM\\s"([^"]+)"\\s*>/is';
        if (preg_match($public_search, $this->_xml_data, $matches)) {
            $name = $matches[1];
            $public_id = $matches[2];
            $system_id = $matches[3];
        } elseif (preg_match($system_search, $this->_xml_data, $matches)) {
            $name = $matches[1];
            $public_id = '';
            $system_id = $matches[2];
        } else {
            // doctype not found
            $name = '';
            $public_id = '';
            $system_id = '';
        }

        // compare doctype
        if (!empty($this->_parser_doctype)) {
            if ($name != $this->_parser_doctype) {
                $this->_error_message = 'unknown doctype : '.$name;

                return false;
            }
        }
        // compare public id
        if (!empty($this->_parser_public_id)) {
            if ($public_id != $this->_parser_public_id) {
                $this->_error_message = 'unknown public id : '.$public_id;

                return false;
            }
        }
        // compare system id
        if (!empty($this->_parser_system_id)) {
            if ($system_id != $this->_parser_system_id) {
                $this->_error_message = 'unknown system id : '.$system_id;

                return false;
            }
        }

        return true;
    }

    /**
     * callback handler of start element of xml data
     * this function is a part of parse() function.
     *
     * @param resource $parser  parser resource
     * @param string   $name    xml element tag
     * @param string   $attribs xml attributes
     */
    public function _parser_start_element_handler($parser, $name, $attribs)
    {
        // update condition
        $this->_parser_condition .= '/'.$name;
        // call start element handler
        $this->parser_start_element($attribs);
    }

    /**
     * callback handler of end element of xml data
     * this function is a part of parse() function.
     *
     * @param resource $parser parser resource
     * @param string   $name   xml element tag
     */
    public function _parser_end_element_handler($parser, $name)
    {
        // call end element handler
        $this->parser_end_element();
        // update condition
        $all_len = strlen($this->_parser_condition);
        $tag_len = strlen($name);
        $this->_parser_condition = substr($this->_parser_condition, 0, $all_len - $tag_len - 1);
    }

    /**
     * callback handler of character data handler of xml data
     * this function is a part of parse() function.
     *
     * @param resource $parser parser resource
     * @param string   $cdata  character data
     */
    public function _parser_character_data_handler($parser, $cdata)
    {
        $this->parser_character_data(trim($cdata));
    }
}
