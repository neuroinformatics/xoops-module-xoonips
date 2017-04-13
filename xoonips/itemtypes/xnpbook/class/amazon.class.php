<?php

// $Revision: 1.1.4.1.2.6 $
// ------------------------------------------------------------------------- //
//  XooNIps - Neuroinformatics Base Platform System                          //
//  Copyright (C) 2005-2013 RIKEN, Japan All rights reserved.                //
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

// class files
require_once XOOPS_ROOT_PATH.'/modules/xoonips/class/base/xmlparser.class.php';

/**
 * The Amazon Product Advertising API data handling class.
 *
 * @author Yoshihiro OKUMURA <orrisroot@users.sourceforge.jp>,
 *          Masaya KINOSHITA
 */
class XooNIps_Amazon_ECS40 extends XooNIpsXMLParser
{
    /**
     * parsed data.
     *
     * @var array
     */
    public $_data;

    /**
     * parsing condition.
     *
     * @var array
     */
    public $_condition = array();

    /**
     * isbn.
     *
     * @var string
     */
    public $_isbn = '';

    /**
     * secret access key for amazon API.
     *
     * @var string
     */
    public $_secret_access_key = '';

    public function XooNIps_Amazon_ECS40()
    {
        // get module config
        $mydirname = basename(dirname(__DIR__));
        $mhandler = &xoops_gethandler('module');
        $module = &$mhandler->getByDirname($mydirname);
        $chandler = &xoops_gethandler('config');
        $mconfig = $chandler->getConfigsByCat(false, $module->mid());

        // call parent constructor
        parent::XooNIpsXMLParser();
        // set fetcher conditions
        $this->_fetch_url = 'http://ecs.amazonaws.com/onca/xml';
        $this->_fetch_arguments['Service'] = 'AWSECommerceService';
        $this->_fetch_arguments['Version'] = '2007-07-16';
        $this->_fetch_arguments['Operation'] = 'ItemLookup';
        $this->_fetch_arguments['IdType'] = 'ISBN';
        $this->_fetch_arguments['SearchIndex'] = 'Books';
        $this->_fetch_arguments['ResponseGroup'] = 'Medium';
        $this->_fetch_arguments['AWSAccessKeyId'] = $mconfig['AccessKey'];
        $this->_fetch_arguments['Timestamp'] = gmdate('Y-m-d\\TH:i:s\\Z');
        $this->_fetch_arguments['AssociateTag'] = $mconfig['AssociateTag'];
        // secret access key for amazon API
        $this->_secret_access_key = $mconfig['SecretAccessKey'];
        // set parser conditions
        $this->_parser_doctype = '';
        $this->_parser_public_id = '';
    }

    /**
     * set the isbn.
     *
     * @param string $isbn isbn10 or isbn13
     *
     * @return bool TRUE if success
     */
    public function set_isbn($isbn)
    {
        $tmp = preg_replace('/[\\- ]/', '', $isbn);
        if (strlen($tmp) == 10) {
            $tmp = $this->_isbn10_to_isbn13($tmp);
        }
        if (strlen($tmp) != 13) {
            return false;
        }
        $char = substr($tmp, 3, 1);
        switch ($char) {
        case '0':
        case '1':
            // us
            $this->_fetch_url = 'http://ecs.amazonaws.com/onca/xml';
            break;
        case '2':
            // france
            $this->_fetch_url = 'http://ecs.amazonaws.fr/onca/xml';
            break;
        case '3':
            // german
            $this->_fetch_url = 'http://ecs.amazonaws.de/onca/xml';
            break;
        case '4':
            // japan
            $this->_fetch_url = 'http://ecs.amazonaws.jp/onca/xml';
            break;
        default:
            // us
            $this->_fetch_url = 'http://ecs.amazonaws.com/onca/xml';
            break;
        }
        $this->_fetch_arguments['ItemId'] = $tmp;
        $this->_isbn = $isbn;

        return true;
    }

    /**
     * override fetch().
     */
    public function fetch()
    {
        if (empty($this->_isbn)) {
            $this->_error_message = 'ISBN does not set';

            return false;
        }

        return parent::fetch();
    }

    /**
     * encode url for Amazon API.
     *
     * @param string $str encoding string
     *
     * @return string encoded string
     */
    public function encode_url($str)
    {
        // RFC 3986
        return str_replace('%7E', '~', rawurlencode($str));
    }

    /**
     * create url string for Amazon API.
     *
     * @param string $url    fetch url
     * @param array  $params url encoded parameters
     *
     * @return string created url string
     */
    public function create_url($url, $arguments)
    {
        // load 'hash_hmac()' compatibility function for PHP 4
        if (!function_exists('hash_hmac')) {
            include_once dirname(__DIR__).'/include/compat/hash_hmac.php';
        }
        // create sigunature
        sort($arguments);
        $sign_param = implode('&', $arguments);
        $parsed_url = parse_url($url);
        $string_to_sign = "GET\n{$parsed_url['host']}\n{$parsed_url['path']}\n{$sign_param}";
        $signature = base64_encode(hash_hmac('sha256', $string_to_sign, $this->_secret_access_key, true));

        // create request url
        $arguments[] = $this->encode_url('Signature').'='.$this->encode_url($signature);
        sort($arguments);

        return $url.'?'.implode('&', $arguments);
    }

    /**
     * override function of start element handler.
     *
     * @param resource $parser parser resource
     * @param string   $tag    xml tag
     */
    public function parser_start_element($attribs)
    {
        switch ($this->_parser_condition) {
        case '/ItemLookupResponse/Items/Item':
            $this->_condition['item'] = array(
            'ASIN' => '',
            'ISBN' => '',
            'EAN' => '',
            'DetailPageURL' => '',
            'Title' => '',
            'Author' => array(),
            'PublicationDate' => '',
            'Publisher' => '',
            );
            break;
        case '/ItemLookupResponse/Items/Item/ItemAttributes/Author':
            $this->_condition['author'] = '';
            break;
        }
    }

    /**
     * override function of end element handler.
     *
     * @param resource $parser parser resource
     * @param string   $tag    xml tag
     */
    public function parser_end_element()
    {
        switch ($this->_parser_condition) {
        case '/ItemLookupResponse/Items/Item':
            $this->_data[$this->_isbn] = $this->_condition['item'];
            break;
        case '/ItemLookupResponse/Items/Item/ItemAttributes/Author':
            $this->_condition['item']['Author'][] = $this->_condition['author'];
            break;
        }
    }

    /**
     * override function of character data handler.
     *
     * @param resource $parser parser resource
     * @param string   $cdata  character data
     */
    public function parser_character_data($cdata)
    {
        switch ($this->_parser_condition) {
        case '/ItemLookupResponse/Items/Item/ASIN':
            // ASIN
            $this->_condition['item']['ASIN'] .= $cdata;
            break;
        case '/ItemLookupResponse/Items/Item/DetailPageURL':
            // DetailPageURL
            $this->_condition['item']['DetailPageURL'] .= $cdata;
            break;
        case '/ItemLookupResponse/Items/Item/ItemAttributes/Author':
            // Author
            $this->_condition['author'] .= $cdata;
            break;
        case '/ItemLookupResponse/Items/Item/ItemAttributes/EAN':
            // EAN
            $this->_condition['item']['EAN'] .= $cdata;
            break;
        case '/ItemLookupResponse/Items/Item/ItemAttributes/ISBN':
            // ISBN
            $this->_condition['item']['ISBN'] .= $cdata;
            break;
        case '/ItemLookupResponse/Items/Item/ItemAttributes/PublicationDate':
            // PublicationDate
            $this->_condition['item']['PublicationDate'] .= $cdata;
            break;
        case '/ItemLookupResponse/Items/Item/ItemAttributes/Publisher':
            // Publisher
            $this->_condition['item']['Publisher'] .= $cdata;
            break;
        case '/ItemLookupResponse/Items/Item/ItemAttributes/Title':
            // Title
            $this->_condition['item']['Title'] .= $cdata;
            break;
        }
    }

    /**
     * convert isbn10 to isbn13.
     *
     * @param string $isbn10
     *
     * @return string isbn13
     */
    public function _isbn10_to_isbn13($isbn10)
    {
        $check = 0;
        $tmp = substr($isbn10, 0, 9);
        $ar = str_split('978'.$tmp, 1);
        for ($i = 0; $i < 12; ++$i) {
            $check += $ar[$i] * ($i % 2 == 0 ? 1 : 3);
        }
        $check = (10 - ($check % 10)) % 10;
        $isbn13 = '978'.$tmp.$check;

        return $isbn13;
    }
}

// http://webservices.amazon.co.jp/onca/xml?Service=AWSECommerceService&AWSAccessKeyId=18SQ7EGXWJARBA4JX382&Version=2007-07-16&Operation=ItemLookup&IdType=ISBNResponseGroup=Medium&SearchIndex=Books&ItemId=9784915512636
// $us_isbn10 = '0553212788';
// $us_isbn13 = '9780553212785';
// $fr_isbn10 = '2100511076';
// $fr_isbn13 = '9782100511075';
// $de_isbn10 = '3827417236';
// $de_isbn13 = '9783827417237';
// $ja_isbn10 = '4915512630';
// $ja_isbn13 = '9784915512636';
// $ja_isbn10_2 = '4847040880';
// $ja_isbn13_2 = '9784847040887';
// $isbn = $ja_isbn13_2;
// $amazon = new XooNIps_Amazon_ECS40();
// if ( ! $amazon->set_isbn( $isbn ) ) {
//  die( $amazon->get_error_message() );
// }
// if ( ! $amazon->fetch() ) {
//  die( $amazon->get_error_message() );
// }
// if ( ! $amazon->parse() ) {
//  die( $amazon->get_error_message() );
// }
// $data =& $amazon->_data;
// var_dump( $data );
