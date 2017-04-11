<?php

// $Revision: 1.1.4.1.2.8 $
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

include XOOPS_ROOT_PATH.'/modules/xoonips/include/iteminfo.inc.php';

$iteminfo['description'] = 'XooNIps Model Item Type';
$iteminfo['files']['main'] = 'model_data';
$iteminfo['files']['preview'] = 'preview';
$iteminfo['files']['others'] = array();

//
// define compo
$iteminfo['ormcompo']['module'] = 'xnpmodel';
$iteminfo['ormcompo']['name'] = 'item';
$iteminfo['ormcompo']['primary_orm'] = 'basic';
$iteminfo['ormcompo']['primary_key'] = 'item_id';

//
// define orm of compo
$creator_order_criteria = new Criteria(1, 1);
$creator_order_criteria->setSort('creator_order');
$iteminfo['orm'][] = array('module' => 'xnpmodel', 'name' => 'item_detail', 'field' => 'detail',     'foreign_key' => 'model_id', 'multiple' => false, 'required' => true);
$iteminfo['orm'][] = array('module' => 'xnpmodel', 'name' => 'creator',    'field' => 'creator',    'foreign_key' => 'model_id', 'multiple' => true, 'criteria' => $creator_order_criteria);
$iteminfo['orm'][] = array('module' => 'xoonips', 'name' => 'file',        'field' => 'model_data', 'foreign_key' => 'item_id', 'criteria' => iteminfo_file_criteria('model_data'), 'multiple' => false);
$iteminfo['orm'][] = array('module' => 'xoonips', 'name' => 'file',        'field' => 'preview',    'foreign_key' => 'item_id', 'criteria' => iteminfo_file_criteria('preview'), 'multiple' => true);

//
// define database table information
$iteminfo['ormfield']['detail'] = array(array('name' => 'model_id', 'type' => 'int', 'required' => ' false'),
                                         array('name' => 'model_type', 'type' => 'string', 'required' => ' true'),
                                         array('name' => 'readme', 'type' => 'string', 'required' => ' false'),
                                         array('name' => 'rights', 'type' => 'string', 'required' => ' false'),
                                         array('name' => 'use_cc', 'type' => 'int', 'required' => ' false'),
                                         array('name' => 'cc_commercial_use', 'type' => 'int', 'required' => ' false'),
                                         array('name' => 'cc_modification', 'type' => 'int', 'required' => ' false'),
                                         array('name' => 'attachment_dl_limit', 'type' => 'int', 'required' => ' false'),
                                         array('name' => 'attachment_dl_notify', 'type' => 'int', 'required' => ' false'), );

$iteminfo['ormfield']['creator'] = array(array('name' => 'creator', 'type' => 'string', 'required' => true),
                                          array('name' => 'creator_order', 'type' => 'int', 'required' => true), );

//
// detail information (modify below for each item types)
$iteminfo['io']['xmlrpc']['item'][] = array('orm' => array('field' => array(array('orm' => 'detail', 'field' => 'model_id'))),
                                             'xmlrpc' => array('field' => array('detail_field', 'model_id'),
                                                                'display_name' => '_MD_XNPMODEL_XMLRPC_DISPLAY_NAME_MODEL_ID',
                                                                'type' => 'string',
                                                                'multiple' => false,
                                                                'readonly' => true, ), );
$iteminfo['io']['xmlrpc']['item'][] = array('orm' => array('field' => array(array('orm' => 'detail', 'field' => 'model_type'))),
                                             'xmlrpc' => array('field' => array('detail_field', 'model_type'),
                                                                'display_name' => '_MD_XNPMODEL_XMLRPC_DISPLAY_NAME_MODEL_TYPE',
                                                                'type' => 'string',
                                                                'options' => array(
                                                                    array('option' => 'matlab', 'display_name' => '_MD_XNPMODEL_XMLRPC_DISPLAY_NAME_MODEL_TYPE_MATLAB'),
                                                                    array('option' => 'neuron', 'display_name' => '_MD_XNPMODEL_XMLRPC_DISPLAY_NAME_MODEL_TYPE_NEURON'),
                                                                    array('option' => 'original_program', 'display_name' => '_MD_XNPMODEL_XMLRPC_DISPLAY_NAME_MODEL_TYPE_ORIGINAL_PROGRAM'),
                                                                    array('option' => 'satellite', 'display_name' => '_MD_XNPMODEL_XMLRPC_DISPLAY_NAME_MODEL_TYPE_SATELLITE'),
                                                                    array('option' => 'genesis', 'display_name' => '_MD_XNPMODEL_XMLRPC_DISPLAY_NAME_MODEL_TYPE_GENESIS'),
                                                                    array('option' => 'a_cell', 'display_name' => '_MD_XNPMODEL_XMLRPC_DISPLAY_NAME_MODEL_TYPE_A_CELL'),
                                                                    array('option' => 'other', 'display_name' => '_MD_XNPMODEL_XMLRPC_DISPLAY_NAME_MODEL_TYPE_OTHER'),
                                                                 ),
                                                                'multiple' => false,
                                                                'required' => true, ), );
$iteminfo['io']['xmlrpc']['item'][] = array('orm' => array('field' => array(array('orm' => 'creator', 'field' => 'creator'))),
                                             'xmlrpc' => array('field' => array('detail_field', 'creator'),
                                                                'display_name' => '_MD_XNPMODEL_XMLRPC_DISPLAY_NAME_CREATOR',
                                                                'type' => 'string',
                                                                'multiple' => true,
                                                                'required' => true, ), );
$iteminfo['io']['xmlrpc']['item'][] = array('orm' => array('field' => array(array('orm' => 'detail', 'field' => 'readme'))),
                                             'xmlrpc' => array('field' => array('detail_field', 'readme'),
                                                                'display_name' => '_MD_XNPMODEL_XMLRPC_DISPLAY_NAME_README',
                                                                'type' => 'string',
                                                                'multiple' => false, ), );
$iteminfo['io']['xmlrpc']['item'][] = array('orm' => array('field' => array(array('orm' => 'detail', 'field' => 'rights'))),
                                             'xmlrpc' => array('field' => array('detail_field', 'rights'),
                                                                'display_name' => '_MD_XNPMODEL_XMLRPC_DISPLAY_NAME_RIGHTS',
                                                                'type' => 'string',
                                                                'multiple' => false, ), );
$iteminfo['io']['xmlrpc']['item'][] = array('orm' => array('field' => array(array('orm' => 'detail', 'field' => 'use_cc'))),
                                             'xmlrpc' => array('field' => array('detail_field', 'use_cc'),
                                                                'display_name' => '_MD_XNPMODEL_XMLRPC_DISPLAY_NAME_USE_CC',
                                                                'type' => 'string',
                                                                'multiple' => false, ), );
$iteminfo['io']['xmlrpc']['item'][] = array('orm' => array('field' => array(array('orm' => 'detail', 'field' => 'cc_commercial_use'))),
                                             'xmlrpc' => array('field' => array('detail_field', 'cc_commercial_use'),
                                                                'display_name' => '_MD_XNPMODEL_XMLRPC_DISPLAY_NAME_CC_COMMERCIAL_USE',
                                                                'type' => 'string',
                                                                'multiple' => false, ), );
$iteminfo['io']['xmlrpc']['item'][] = array('orm' => array('field' => array(array('orm' => 'detail', 'field' => 'cc_modification'))),
                                             'xmlrpc' => array('field' => array('detail_field', 'cc_modification'),
                                                                'display_name' => '_MD_XNPMODEL_XMLRPC_DISPLAY_NAME_CC_MODIFICATION',
                                                                'type' => 'string',
                                                                'multiple' => false, ), );
$iteminfo['io']['xmlrpc']['item'][] = array('orm' => array('field' => array(array('orm' => 'detail', 'field' => 'attachment_dl_limit'))),
                                             'xmlrpc' => array('field' => array('detail_field', 'attachment_dl_limit'),
                                                                'display_name' => '_MD_XNPMODEL_XMLRPC_DISPLAY_NAME_ATTACHMENT_DL_LIMIT',
                                                                'type' => 'string',
                                                                'multiple' => false, ), );
$iteminfo['io']['xmlrpc']['item'][] = array('orm' => array('field' => array(array('orm' => 'detail', 'field' => 'attachment_dl_notify'))),
                                             'xmlrpc' => array('field' => array('detail_field', 'attachment_dl_notify'),
                                                                'display_name' => '_MD_XNPMODEL_XMLRPC_DISPLAY_NAME_ATTACHMENT_DL_NOTIFY',
                                                                'type' => 'string',
                                                                'multiple' => false, ), );
$iteminfo['io']['xmlrpc']['item'][] = array('orm' => array('field' => array(array('orm' => 'model_data', 'field' => 'file_id'))),
                                             'xmlrpc' => array('field' => array('detail_field', 'model_data'),
                                                                'display_name' => '_MD_XNPMODEL_XMLRPC_DISPLAY_NAME_MODEL_DATA',
                                                                'type' => 'int',
                                                                'multiple' => false,
                                                                'required' => true, ), );
$iteminfo['io']['xmlrpc']['item'][] = array('orm' => array('field' => array(array('orm' => 'preview', 'field' => 'file_id'))),
                                             'xmlrpc' => array('field' => array('detail_field', 'preview'),
                                                                'display_name' => '_MD_XNPMODEL_XMLRPC_DISPLAY_NAME_PREVIEW',
                                                                'type' => 'int',
                                                                'multiple' => true, ), );

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
                                                                                     array('orm' => 'detail', 'field' => 'model_type'),
                                                                                     array('orm' => 'creator', 'field' => 'creator'), )),
                                                   'xmlrpc' => array('field' => array('text'),
                                                                      'type' => 'string', ),
                                                   'eval' => array('orm2xmlrpc' => '$in_var[0] = implode( ";", $in_var[0] ); $in_var[2] = implode( ";", $in_var[2] ); $out_var[0] = implode( "/", $in_var );',
                                                                    'xmlrpc2orm' => ';', ), );
