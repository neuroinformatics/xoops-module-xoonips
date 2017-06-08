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

$iteminfo['description'] = 'XooNIps Conference Item Type';
$iteminfo['files']['main'] = 'conference_file';
$iteminfo['files']['preview'] = null; // null if preview is not used
$iteminfo['files']['others'] = array('conference_paper');

//
//
// define compo
$iteminfo['ormcompo']['module'] = 'xnpconference';
$iteminfo['ormcompo']['name'] = 'item';
$iteminfo['ormcompo']['primary_orm'] = 'basic';
$iteminfo['ormcompo']['primary_key'] = 'item_id';

//
// define orm of compo
$author_order_criteria = new Criteria(1, 1);
$author_order_criteria->setSort('author_order');
$iteminfo['orm'][] = array('module' => 'xnpconference', 'name' => 'item_detail', 'field' => 'detail',      'foreign_key' => 'conference_id', 'multiple' => false, 'required' => true);
$iteminfo['orm'][] = array('module' => 'xnpconference', 'name' => 'author',     'field' => 'author',      'foreign_key' => 'conference_id', 'multiple' => true, 'criteria' => $author_order_criteria);
$iteminfo['orm'][] = array('module' => 'xoonips', 'name' => 'file', 'field' => 'conference_file', 'foreign_key' => 'item_id', 'criteria' => iteminfo_file_criteria('conference_file'), 'multiple' => false);
$iteminfo['orm'][] = array('module' => 'xoonips', 'name' => 'file', 'field' => 'conference_paper', 'foreign_key' => 'item_id', 'criteria' => iteminfo_file_criteria('conference_paper'), 'multiple' => false);

//
// define database table information
$iteminfo['ormfield']['detail'] = array(array('name' => 'conference_id', 'type' => 'int', 'required' => false),
                                         array('name' => 'presentation_type', 'type' => 'string', 'required' => false),
                                         array('name' => 'conference_title', 'type' => 'string', 'required' => false),
                                         array('name' => 'place', 'type' => 'string', 'required' => false),
                                         array('name' => 'abstract', 'type' => 'string', 'required' => false),
                                         array('name' => 'conference_from_year', 'type' => 'int', 'required' => false),
                                         array('name' => 'conference_from_month', 'type' => 'int', 'required' => false),
                                         array('name' => 'conference_from_mday', 'type' => 'int', 'required' => false),
                                         array('name' => 'conference_to_year', 'type' => 'int', 'required' => false),
                                         array('name' => 'conference_to_month', 'type' => 'int', 'required' => false),
                                         array('name' => 'conference_to_mday', 'type' => 'int', 'required' => false),
                                         array('name' => 'attachment_dl_limit', 'type' => 'int', 'required' => false),
                                         array('name' => 'attachment_dl_notify', 'type' => 'int', 'required' => false), );

$iteminfo['ormfield']['author'] = array(array('name' => 'author', 'type' => 'string', 'required' => true),
                                         array('name' => 'author_order', 'type' => 'int', 'required' => true), );

//
// publication_year(creation_date in XML-RPC) is required
foreach ($iteminfo['io']['xmlrpc']['item'] as $key => $val) {
    if ($val['xmlrpc']['field'][0] == 'titles') {
        $iteminfo['io']['xmlrpc']['item'][$key]['xmlrpc']['display_name'] = '_MD_XNPCONFERENCE_XMLRPC_DISPLAY_NAME_TITLES';
    }
}
//
// detail information (modify below for each item types)
$iteminfo['io']['xmlrpc']['item'][] = array('orm' => array('field' => array(array('orm' => 'detail', 'field' => 'conference_id'))),
                                             'xmlrpc' => array('field' => array('detail_field', 'conference_id'),
                                                                'display_name' => '_MD_XNPCONFERENCE_XMLRPC_DISPLAY_NAME_CONFERENCE_ID',
                                                                'type' => 'string',
                                                                'readonly' => true, ), );

$iteminfo['io']['xmlrpc']['item'][] = array('orm' => array('field' => array(array('orm' => 'detail', 'field' => 'presentation_type'))),
                                             'xmlrpc' => array('field' => array('detail_field', 'presentation_type'),
                                                                'display_name' => '_MD_XNPCONFERENCE_XMLRPC_DISPLAY_NAME_PRESENTATION_TYPE',
                                                                'type' => 'string',
                                                                'required' => true,
                                                                'options' => array(
                                                                    array('option' => 'powerpoint', 'display_name' => '_MD_XNPCONFERENCE_XMLRPC_DISPLAY_NAME_PRESENTATION_TYPE_POWERPOINT'),
                                                                    array('option' => 'pdf', 'display_name' => '_MD_XNPCONFERENCE_XMLRPC_DISPLAY_NAME_PRESENTATION_TYPE_PDF'),
                                                                    array('option' => 'illustrator', 'display_name' => '_MD_XNPCONFERENCE_XMLRPC_DISPLAY_NAME_PRESENTATION_TYPE_ILLUSTRATOR'),
                                                                    array('option' => 'other', 'display_name' => '_MD_XNPCONFERENCE_XMLRPC_DISPLAY_NAME_PRESENTATION_TYPE_OTHER'),
                                                                 ), ), );

$iteminfo['io']['xmlrpc']['item'][] = array('orm' => array('field' => array(array('orm' => 'author', 'field' => 'author'))),
                                             'xmlrpc' => array('field' => array('detail_field', 'author'),
                                                                'display_name' => '_MD_XNPCONFERENCE_XMLRPC_DISPLAY_NAME_AUTHOR',
                                                                'type' => 'string',
                                                                'multiple' => true,
                                                                'required' => true, ), );

$iteminfo['io']['xmlrpc']['item'][] = array('orm' => array('field' => array(array('orm' => 'detail', 'field' => 'conference_title'))),
                                             'xmlrpc' => array('field' => array('detail_field', 'conference_title'),
                                                                'display_name' => '_MD_XNPCONFERENCE_XMLRPC_DISPLAY_NAME_CONFERENCE_TITLE',
                                                                'type' => 'string',
                                                                'required' => true, ), );

$iteminfo['io']['xmlrpc']['item'][] = array('orm' => array('field' => array(array('orm' => 'detail', 'field' => 'place'))),
                                             'xmlrpc' => array('field' => array('detail_field', 'place'),
                                                                'display_name' => '_MD_XNPCONFERENCE_XMLRPC_DISPLAY_NAME_PLACE',
                                                                'type' => 'string',
                                                                'required' => true, ), );

$iteminfo['io']['xmlrpc']['item'][] = array('orm' => array('field' => array(array('orm' => 'detail', 'field' => 'abstract'))),
                                             'xmlrpc' => array('field' => array('detail_field', 'abstract'),
                                                                'display_name' => '_MD_XNPCONFERENCE_XMLRPC_DISPLAY_NAME_ABSTRACT',
                                                                'type' => 'string', ), );

$iteminfo['io']['xmlrpc']['item'][] = array('orm' => array('field' => array(array('orm' => 'detail', 'field' => 'conference_from_year'),
                                                                               array('orm' => 'detail', 'field' => 'conference_from_month'),
                                                                               array('orm' => 'detail', 'field' => 'conference_from_mday'), )),
                                             'xmlrpc' => array('field' => array('detail_field', 'conference_from'),
                                                                'display_name' => '_MD_XNPCONFERENCE_XMLRPC_DISPLAY_NAME_CONFERENCE_FROM',
                                                                'type' => 'dateTime.iso8601',
                                                                'required' => true, ),
                                             'eval' => array('orm2xmlrpc' => '$out_var[0] = gmmktime(0, 0, 0, $in_var[1], $in_var[2], $in_var[0] );',
                                                              'xmlrpc2orm' => '$out_var[0] = gmdate("Y", $in_var[0]);'
                                                              .'$out_var[1] = date("n", $in_var[0]);'
                                                              .'$out_var[2] = date("j", $in_var[0]);', ), );

$iteminfo['io']['xmlrpc']['item'][] = array('orm' => array('field' => array(array('orm' => 'detail', 'field' => 'conference_to_year'),
                                                                               array('orm' => 'detail', 'field' => 'conference_to_month'),
                                                                               array('orm' => 'detail', 'field' => 'conference_to_mday'), )),
                                             'xmlrpc' => array('field' => array('detail_field', 'conference_to'),
                                                                'display_name' => '_MD_XNPCONFERENCE_XMLRPC_DISPLAY_NAME_CONFERENCE_TO',
                                                                'type' => 'dateTime.iso8601',
                                                                'required' => true, ),
                                             'eval' => array('orm2xmlrpc' => '$out_var[0] = gmmktime(0, 0, 0, $in_var[1], $in_var[2], $in_var[0] );',
                                                              'xmlrpc2orm' => '$out_var[0] = gmdate("Y", $in_var[0]);'
                                                              .'$out_var[1] = date("n", $in_var[0]);'
                                                              .'$out_var[2] = date("j", $in_var[0]);', ), );

$iteminfo['io']['xmlrpc']['item'][] = array('orm' => array('field' => array(array('orm' => 'detail', 'field' => 'attachment_dl_limit'))),
                                             'xmlrpc' => array('field' => array('detail_field', 'attachment_dl_limit'),
                                                                'display_name' => '_MD_XNPCONFERENCE_XMLRPC_DISPLAY_NAME_ATTACHMENT_DL_LIMIT',
                                                                'type' => 'string', ), );

$iteminfo['io']['xmlrpc']['item'][] = array('orm' => array('field' => array(array('orm' => 'detail', 'field' => 'attachment_dl_notify'))),
                                             'xmlrpc' => array('field' => array('detail_field', 'attachment_dl_notify'),
                                                                'display_name' => '_MD_XNPCONFERENCE_XMLRPC_DISPLAY_NAME_ATTACHMENT_DL_NOTIFY',
                                                                'type' => 'string', ), );

$iteminfo['io']['xmlrpc']['item'][] = array('orm' => array('field' => array(array('orm' => 'conference_file', 'field' => 'file_id'))),
                                             'xmlrpc' => array('field' => array('detail_field', 'conference_file'),
                                                                'display_name' => '_MD_XNPCONFERENCE_XMLRPC_DISPLAY_NAME_CONFERENCE_FILE',
                                                                'type' => 'int',
                                                                'required' => true, ), );

$iteminfo['io']['xmlrpc']['item'][] = array('orm' => array('field' => array(array('orm' => 'conference_paper', 'field' => 'file_id'))),
                                             'xmlrpc' => array('field' => array('detail_field', 'conference_paper'),
                                                                'display_name' => '_MD_XNPCONFERENCE_XMLRPC_DISPLAY_NAME_CONFERENCE_PAPER',
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
                                                                                     array('orm' => 'detail', 'field' => 'conference_id'),
                                                                                     array('orm' => 'detail', 'field' => 'presentation_type'),
                                                                                     array('orm' => 'author', 'field' => 'author'),
                                                                                     array('orm' => 'detail', 'field' => 'conference_title'),
                                                                                     array('orm' => 'detail', 'field' => 'place'),
                                                                                     array('orm' => 'detail', 'field' => 'abstract'),
                                                                                     array('orm' => 'detail', 'field' => 'conference_from_year'),
                                                                                     array('orm' => 'detail', 'field' => 'conference_from_month'),
                                                                                     array('orm' => 'detail', 'field' => 'conference_from_mday'),
                                                                                     array('orm' => 'detail', 'field' => 'conference_to_year'),
                                                                                     array('orm' => 'detail', 'field' => 'conference_to_month'),
                                                                                     array('orm' => 'detail', 'field' => 'conference_to_mday'), )),
                                                   'xmlrpc' => array('field' => array('text'),
                                                                      'type' => 'string', ),
                                                   'eval' => array('orm2xmlrpc' => '$in_var[0] = implode( ";", $in_var[0] ); $in_var[3] = implode( ";", $in_var[3] ); $out_var[0] = implode( "/", $in_var );',
                                                                    'xmlrpc2orm' => ';', ), );
