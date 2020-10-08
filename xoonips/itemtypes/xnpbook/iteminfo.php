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

require XOOPS_ROOT_PATH.'/modules/xoonips/include/iteminfo.inc.php';

$iteminfo['description'] = 'XooNIps Book Item Type';
$iteminfo['files']['main'] = 'book_pdf';
$iteminfo['files']['preview'] = null; // null if preview is not used
$iteminfo['files']['others'] = array();

//
// define compo
$iteminfo['ormcompo']['module'] = 'xnpbook';
$iteminfo['ormcompo']['name'] = 'item';
$iteminfo['ormcompo']['primary_orm'] = 'basic';
$iteminfo['ormcompo']['primary_key'] = 'item_id';

//
// define orm of compo
$author_order_criteria = new Criteria(1, 1);
$author_order_criteria->setSort('author_order');
$iteminfo['orm'][] = array('module' => 'xnpbook', 'name' => 'item_detail', 'field' => 'detail',   'foreign_key' => 'book_id', 'multiple' => false, 'required' => true);
$iteminfo['orm'][] = array('module' => 'xnpbook', 'name' => 'author', 'field' => 'author',   'foreign_key' => 'book_id', 'multiple' => true, 'criteria' => $author_order_criteria);
$iteminfo['orm'][] = array('module' => 'xoonips', 'name' => 'file',        'field' => 'book_pdf', 'foreign_key' => 'item_id', 'criteria' => iteminfo_file_criteria('book_pdf'), 'multiple' => false);

//
// define database table information
$iteminfo['ormfield']['detail'] = array(array('name' => 'editor', 'type' => 'string', 'required' => false),
                                         array('name' => 'publisher', 'type' => 'string', 'required' => true),
                                         array('name' => 'isbn', 'type' => 'string', 'required' => false),
                                         array('name' => 'url', 'type' => 'string', 'required' => false),
                                         array('name' => 'attachment_dl_limit', 'type' => 'int', 'required' => false),
                                         array('name' => 'attachment_dl_notify', 'type' => 'int', 'required' => false), );

$iteminfo['ormfield']['author'] = array(array('name' => 'author', 'type' => 'string', 'required' => true),
                                         array('name' => 'order', 'type' => 'int', 'required' => true), );

//
// publication_year(creation_date in XML-RPC) is required
foreach ($iteminfo['io']['xmlrpc']['item'] as $key => $val) {
    if ('creation_year' == $val['xmlrpc']['field'][0]) {
        $iteminfo['io']['xmlrpc']['item'][$key]['xmlrpc']['required'] = true;
        $iteminfo['io']['xmlrpc']['item'][$key]['xmlrpc']['display_name'] = '_MD_XNPBOOK_XMLRPC_DISPLAY_NAME_CREATION_YEAR';
    } elseif ('creation_month' == $val['xmlrpc']['field'][0]) {
        $iteminfo['io']['xmlrpc']['item'][$key]['xmlrpc']['display_name'] = '_MD_XNPBOOK_XMLRPC_DISPLAY_NAME_CREATION_MONTH';
    } elseif ('creation_mday' == $val['xmlrpc']['field'][0]) {
        $iteminfo['io']['xmlrpc']['item'][$key]['xmlrpc']['display_name'] = '_MD_XNPBOOK_XMLRPC_DISPLAY_NAME_CREATION_MDAY';
    }
}

//
// detail information (modify below for each item types)
$iteminfo['io']['xmlrpc']['item'][] = array('orm' => array('field' => array(array('orm' => 'detail', 'field' => 'book_id'))),
                                             'xmlrpc' => array('field' => array('detail_field', 'book_id'),
                                                                'display_name' => '_MD_XNPBOOK_XMLRPC_DISPLAY_NAME_BOOK_ID',
                                                                'type' => 'string',
                                                                'readonly' => true, ), );
$iteminfo['io']['xmlrpc']['item'][] = array('orm' => array('field' => array(array('orm' => 'author', 'field' => 'author'))),
                                             'xmlrpc' => array('field' => array('detail_field', 'author'),
                                                                'display_name' => '_MD_XNPBOOK_XMLRPC_DISPLAY_NAME_AUTHOR',
                                                                'type' => 'string',
                                                                'multiple' => true, ), );
$iteminfo['io']['xmlrpc']['item'][] = array('orm' => array('field' => array(array('orm' => 'detail', 'field' => 'editor'))),
                                             'xmlrpc' => array('field' => array('detail_field', 'editor'),
                                                                'display_name' => '_MD_XNPBOOK_XMLRPC_DISPLAY_NAME_EDITOR',
                                                                'type' => 'string', ), );
$iteminfo['io']['xmlrpc']['item'][] = array('orm' => array('field' => array(array('orm' => 'detail', 'field' => 'publisher'))),
                                             'xmlrpc' => array('field' => array('detail_field', 'publisher'),
                                                                'display_name' => '_MD_XNPBOOK_XMLRPC_DISPLAY_NAME_PUBLISHER',
                                                                'type' => 'string',
                                                                'required' => true, ), );
$iteminfo['io']['xmlrpc']['item'][] = array('orm' => array('field' => array(array('orm' => 'detail', 'field' => 'isbn'))),
                                             'xmlrpc' => array('field' => array('detail_field', 'isbn'),
                                                                'display_name' => '_MD_XNPBOOK_XMLRPC_DISPLAY_NAME_ISBN',
                                                                'type' => 'string', ), );
$iteminfo['io']['xmlrpc']['item'][] = array('orm' => array('field' => array(array('orm' => 'detail', 'field' => 'url'))),
                                             'xmlrpc' => array('field' => array('detail_field', 'url'),
                                                                'display_name' => '_MD_XNPBOOK_XMLRPC_DISPLAY_NAME_URL',
                                                                'type' => 'string', ), );
$iteminfo['io']['xmlrpc']['item'][] = array('orm' => array('field' => array(array('orm' => 'detail', 'field' => 'attachment_dl_limit'))),
                                             'xmlrpc' => array('field' => array('detail_field', 'attachment_dl_limit'),
                                                                'display_name' => '_MD_XNPBOOK_XMLRPC_DISPLAY_NAME_ATTACHMENT_DL_LIMIT',
                                                                'type' => 'string', ), );
$iteminfo['io']['xmlrpc']['item'][] = array('orm' => array('field' => array(array('orm' => 'detail', 'field' => 'attachment_dl_notify'))),
                                             'xmlrpc' => array('field' => array('detail_field', 'attachment_dl_notify'),
                                                                'display_name' => '_MD_XNPBOOK_XMLRPC_DISPLAY_NAME_ATTACHMENT_DL_NOTIFY',
                                                                'type' => 'string', ), );
$iteminfo['io']['xmlrpc']['item'][] = array('orm' => array('field' => array(array('orm' => 'book_pdf', 'field' => 'file_id'))),
                                             'xmlrpc' => array('field' => array('detail_field', 'book_pdf'),
                                                                'display_name' => '_MD_XNPBOOK_XMLRPC_DISPLAY_NAME_BOOK_PDF',
                                                                'type' => 'int', ), );

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
                                                                                     array('orm' => 'author', 'field' => 'author'),
                                                                                     array('orm' => 'detail', 'field' => 'editor'),
                                                                                     array('orm' => 'detail', 'field' => 'publisher'),
                                                                                     array('orm' => 'detail', 'field' => 'isbn'),
                                                                                     array('orm' => 'detail', 'field' => 'url'), )),
                                                   'xmlrpc' => array('field' => array('text'),
                                                                      'type' => 'string', ),
                                                   'eval' => array('orm2xmlrpc' => '$in_var[0] = implode( ";", $in_var[0] ); $in_var[1] = implode( ";", $in_var[1] ); $out_var[0] = implode( "/", $in_var );',
                                                                    'xmlrpc2orm' => ';', ), );
