<?php
// $Revision: 1.1.4.1.2.8 $
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
if ( ! defined( 'XOONIPS_PATH' ) ) {
  exit();
}

// class file
require_once XOONIPS_PATH.'/class/base/JSON.php';
require_once dirname( dirname( __FILE__ ) ).'/class/amazon.class.php';

// change internal encoding to UTF-8
if ( extension_loaded( 'mbstring' ) ) {
  mb_language( 'uni' );
  mb_internal_encoding( 'UTF-8' );
  mb_http_output( 'pass' );
}

$is_error = false;
$error_message = '';
if ( ! isset( $_SERVER['HTTP_REFERER'] ) || preg_match( '/\\/modules\\/xoonips\\//', $_SERVER['HTTP_REFERER'] ) == 0 ) {
  $is_error = true;
  $error_message = 'Turn REFERER on';
}

if ( ! $is_error && ! isset( $_GET['asin'] ) ) {
  $is_error = true;
  $error_message = 'asin required';
}

if ( ! $is_error ) {
  $asin = trim( $_GET['asin'] );
}

function get_simplified_url($url) {
  $durl = urldecode($url);
  $ret = parse_url($durl);
  if ($ret === false)
    return $url;
  $host = $ret['host'];
  if ($host == 'www.amazon.co.jp')
    $host = 'amazon.jp';
  $asin = false;
  if (isset($ret['path']) && preg_match('/\/dp\/([0-9a-zA-Z]+)/', $ret['path'], $matches))
    $asin = $matches[1];
  if (empty($asin) && isset($ret['query'])) {
    $queries = explode('&', $ret['query']);
    foreach ($queries as $query) {
       list($key, $value) = explode('=', $query);
       if ($key == 'ASIN') {
         $asin = $value;
         break;
       }
    }
  }
  if ($asin !== false)
    return sprintf('%s://%s/dp/%s', $ret['scheme'], $host, $asin);
  return $url;
}

function &get_amazon_data( $asin ) {
  $ret = array();
  $amazon = new XooNIps_Amazon_ECS40();
  if ( ! $amazon->set_isbn( $asin ) ) {
    return $ret;
  }
  if ( ! $amazon->fetch() ) {
    return $ret;
  }
  if ( ! $amazon->parse() ) {
    return $ret;
  }
  if ( ! isset( $amazon->_data[$asin] ) ) {
    return $ret;
  }
  $item =& $amazon->_data[$asin];
  // asin
  $ret['asin'] = $item['ASIN'];
  // isbn
  $ret['isbn'] = $item['ISBN'];
  // ean
  $ret['ean'] = $item['EAN'];
  // url
  $ret['url'] = get_simplified_url($item['DetailPageURL']);
  // author
  $ret['author'] = $item['Author'];
  // year
  $ret['year'] = '';
  // - PublicationDate is yyyy-mm-dd or yyyy-mm form
  $pdate = explode( '-', $item['PublicationDate'] );
  $pdate_count = count( $pdate );
  if ( $pdate_count == 2 || $pdate_count == 3 ) {
    $ret['year'] = sscanf( $pdate[0], '%d' );
  }
  // publisher
  $ret['publisher'] = $item['Publisher'];
  // title
  $ret['title'] = $item['Title'];

  return $ret;
}

if ( ! $is_error ) {
  $data =& get_amazon_data( $asin );
  if ( empty( $data ) ) {
    $data['error'] = 'failed to get amazon resources';
  }
} else {
  $data = array();
  $data['error'] = $error_message;
}

// json
$json = new Services_JSON();
$encode = $json->encode( $data );

// output
header( 'Content-Type: text/javascript+json; charset=utf-8' );
echo $encode;

