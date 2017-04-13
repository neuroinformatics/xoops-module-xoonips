<?php

// $Revision: 1.1.4.1.2.9 $
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

require XOOPS_ROOT_PATH.'/modules/xoonips/include/iteminfo.inc.php';

$iteminfo['description'] = 'XooNIps Paper Item Type';
$iteminfo['files']['main'] = 'paper_pdf_reprint';
$iteminfo['files']['preview'] = null;
$iteminfo['files']['others'] = array();

//
// define compo
$iteminfo['ormcompo']['module'] = 'xnppaper';
$iteminfo['ormcompo']['name'] = 'item';
$iteminfo['ormcompo']['primary_orm'] = 'basic';
$iteminfo['ormcompo']['primary_key'] = 'item_id';

//
// define orm of compo
$author_order_criteria = new Criteria(1, 1);
$author_order_criteria->setSort('author_order');
$iteminfo['orm'][] = array('module' => 'xnppaper', 'name' => 'item_detail', 'field' => 'detail', 'foreign_key' => 'paper_id', 'multiple' => false, 'required' => true);
$iteminfo['orm'][] = array('module' => 'xnppaper', 'name' => 'author',      'field' => 'author', 'foreign_key' => 'paper_id', 'multiple' => true, 'criteria' => $author_order_criteria);
$iteminfo['orm'][] = array('module' => 'xoonips',  'name' => 'file',        'field' => 'paper_pdf_reprint',                  'foreign_key' => 'item_id', 'criteria' => iteminfo_file_criteria('paper_pdf_reprint'), 'multiple' => false);

//
// define database table information
$iteminfo['ormfield']['detail'] = array(array('name' => 'paper_id', 'type' => 'int', 'required' => ' false'),
                                         array('name' => 'journal', 'type' => 'string', 'required' => ' true'),
                                         array('name' => 'volume', 'type' => 'int', 'required' => ' false'),
                                         array('name' => 'number', 'type' => 'int', 'required' => ' false'),
                                         array('name' => 'page', 'type' => 'string', 'required' => ' false'),
                                         array('name' => 'abstract', 'type' => 'string', 'required' => ' false'),
                                         array('name' => 'pubmed_id', 'type' => 'string', 'required' => ' false'), );

$iteminfo['ormfield']['author'] = array(array('name' => 'author', 'type' => 'string', 'required' => true),
                                         array('name' => 'author_order', 'type' => 'int', 'required' => true), );

//
// publication_year(creation_date in XML-RPC) is required
foreach ($iteminfo['io']['xmlrpc']['item'] as $key => $val) {
    if ($val['xmlrpc']['field'][0] == 'creation_year') {
        $iteminfo['io']['xmlrpc']['item'][$key]['xmlrpc']['required'] = true;
        $iteminfo['io']['xmlrpc']['item'][$key]['xmlrpc']['display_name'] = '_MD_XNPPAPER_XMLRPC_DISPLAY_NAME_CREATION_YEAR';
    } elseif ($val['xmlrpc']['field'][0] == 'creation_month') {
        $iteminfo['io']['xmlrpc']['item'][$key]['xmlrpc']['display_name'] = '_MD_XNPPAPER_XMLRPC_DISPLAY_NAME_CREATION_MONTH';
    } elseif ($val['xmlrpc']['field'][0] == 'creation_mday') {
        $iteminfo['io']['xmlrpc']['item'][$key]['xmlrpc']['display_name'] = '_MD_XNPPAPER_XMLRPC_DISPLAY_NAME_CREATION_MDAY';
    }
}

//
// detail information (modify below for each item types)
$iteminfo['io']['xmlrpc']['item'][] = array('orm' => array('field' => array(array('orm' => 'detail', 'field' => 'paper_id'))),
                                             'xmlrpc' => array('field' => array('detail_field', 'paper_id'),
                                                                'display_name' => '_MD_XNPPAPER_XMLRPC_DISPLAY_NAME_PAPER_ID',
                                                                'type' => 'string',
                                                                'multiple' => false,
                                                                'readonly' => true, ), );
$iteminfo['io']['xmlrpc']['item'][] = array('orm' => array('field' => array(array('orm' => 'author', 'field' => 'author'))),
                                             'xmlrpc' => array('field' => array('detail_field', 'author'),
                                                                'display_name' => '_MD_XNPPAPER_XMLRPC_DISPLAY_NAME_AUTHOR',
                                                                'type' => 'string',
                                                                'multiple' => true,
                                                                'required' => true, ), );
$iteminfo['io']['xmlrpc']['item'][] = array('orm' => array('field' => array(array('orm' => 'detail', 'field' => 'journal'))),
                                             'xmlrpc' => array('field' => array('detail_field', 'journal'),
                                                                'display_name' => '_MD_XNPPAPER_XMLRPC_DISPLAY_NAME_JOURNAL',
                                                                'type' => 'string',
                                                                'multiple' => false,
                                                                'required' => true, ), );
$iteminfo['io']['xmlrpc']['item'][] = array('orm' => array('field' => array(array('orm' => 'detail', 'field' => 'volume'))),
                                             'xmlrpc' => array('field' => array('detail_field', 'volume'),
                                                                'display_name' => '_MD_XNPPAPER_XMLRPC_DISPLAY_NAME_VOLUME',
                                                                'type' => 'string',
                                                                'multiple' => false, ), );
$iteminfo['io']['xmlrpc']['item'][] = array('orm' => array('field' => array(array('orm' => 'detail', 'field' => 'number'))),
                                             'xmlrpc' => array('field' => array('detail_field', 'number'),
                                                                'display_name' => '_MD_XNPPAPER_XMLRPC_DISPLAY_NAME_NUMBER',
                                                                'type' => 'string',
                                                                'multiple' => false, ), );
$iteminfo['io']['xmlrpc']['item'][] = array('orm' => array('field' => array(array('orm' => 'detail', 'field' => 'page'))),
                                             'xmlrpc' => array('field' => array('detail_field', 'page'),
                                                                'display_name' => '_MD_XNPPAPER_XMLRPC_DISPLAY_NAME_PAGE',
                                                                'type' => 'string',
                                                                'multiple' => false, ), );
$iteminfo['io']['xmlrpc']['item'][] = array('orm' => array('field' => array(array('orm' => 'detail', 'field' => 'abstract'))),
                                             'xmlrpc' => array('field' => array('detail_field', 'abstract'),
                                                                'display_name' => '_MD_XNPPAPER_XMLRPC_DISPLAY_NAME_ABSTRACT',
                                                                'type' => 'string',
                                                                'multiple' => false, ), );
$iteminfo['io']['xmlrpc']['item'][] = array('orm' => array('field' => array(array('orm' => 'detail', 'field' => 'pubmed_id'))),
                                             'xmlrpc' => array('field' => array('detail_field', 'pubmed_id'),
                                                                'display_name' => '_MD_XNPPAPER_XMLRPC_DISPLAY_NAME_PUBMED_ID',
                                                                'type' => 'string',
                                                                'multiple' => false, ), );
$iteminfo['io']['xmlrpc']['item'][] = array('orm' => array('field' => array(array('orm' => 'paper_pdf_reprint', 'field' => 'file_id'))),
                                             'xmlrpc' => array('field' => array('detail_field', 'paper_pdf_reprint'),
                                                                'display_name' => '_MD_XNPPAPER_XMLRPC_DISPLAY_NAME_PAPER_PDF_REPRINT',
                                                                'type' => 'int',
                                                                'multiple' => false, ), );

//-------------------------
// SimpleItem
//-------------------------
$iteminfo['io']['xmlrpc']['simpleitem'][] = array('orm' => array('field' => array(array('orm' => 'basic',    'field' => 'item_id'))),
                                                   'xmlrpc' => array('field' => array('item_id'),
                                                                      'type' => 'int',
                                                                      'multiple' => false, ), );
$iteminfo['io']['xmlrpc']['simpleitem'][] = array('orm' => array('field' => array(array('orm' => 'basic',    'field' => 'item_type_id'))),
                                                   'xmlrpc' => array('field' => array('itemtypeid'),
                                                                      'type' => 'int',
                                                                      'multiple' => false, ), );
$iteminfo['io']['xmlrpc']['simpleitem'][] = array('orm' => array('field' => array(array('orm' => 'basic',   'field' => 'uid'))),
                                                   'xmlrpc' => array('field' => array('username'),
                                                                      'type' => 'string',
                                                                      'multiple' => false, ),
                                                   'eval' => array('orm2xmlrpc' => '$u_handler=&xoops_gethandler("user"); $user=&$u_handler->get($in_var[0]); $out_var[0]=$user->getVar("uname");',
                                                                    'xmlrpc2orm' => ';', ), );
$iteminfo['io']['xmlrpc']['simpleitem'][] = array('orm' => array('field' => array(array('orm' => 'titles',   'field' => 'title'))),
                                                   'xmlrpc' => array('field' => array('titles'),
                                                                      'type' => 'string',
                                                                      'multiple' => true, ), );
$iteminfo['io']['xmlrpc']['simpleitem'][] = array('orm' => array('field' => array(array('orm' => 'basic',    'field' => 'last_update_date'))),
                                                   'xmlrpc' => array('field' => array('last_modified_date'),
                                                                      'type' => 'dateTime.iso8601',
                                                                      'multiple' => false, ), );
$iteminfo['io']['xmlrpc']['simpleitem'][] = array('orm' => array('field' => array(array('orm' => 'basic',    'field' => 'creation_date'))),
                                                   'xmlrpc' => array('field' => array('registration_date'),
                                                                      'type' => 'dateTime.iso8601',
                                                                      'multiple' => false, ), );
$iteminfo['io']['xmlrpc']['simpleitem'][] = array('orm' => array('field' => array(array('orm' => 'titles', 'field' => 'title'),
                                                                                     array('orm' => 'detail', 'field' => 'pubmed_id'),
                                                                                     array('orm' => 'author', 'field' => 'author'),
                                                                                     array('orm' => 'detail', 'field' => 'journal'),
                                                                                     array('orm' => 'basic', 'field' => 'publication_year'),
                                                                                     array('orm' => 'detail', 'field' => 'volume'),
                                                                                     array('orm' => 'detail', 'field' => 'number'),
                                                                                     array('orm' => 'detail', 'field' => 'page'),
                                                                                     array('orm' => 'detail', 'field' => 'abstract'), )),
                                                   'xmlrpc' => array('field' => array('text'),
                                                                      'type' => 'string', ),
                                                   'eval' => array('orm2xmlrpc' => '$in_var[0] = implode( ";", $in_var[0] ); $in_var[2] = implode( ";", $in_var[2] ); $out_var[0] = implode( "/", $in_var );',
                                                                    'xmlrpc2orm' => ';', ), );
