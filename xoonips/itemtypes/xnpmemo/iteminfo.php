<?php

// $Revision: 1.1.4.1.2.5 $
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

$iteminfo['description'] = 'XooNIps Memo Item Type';
$iteminfo['files']['main'] = 'memo_file';
$iteminfo['files']['preview'] = null;
$iteminfo['files']['others'] = array();

//
// define compo
$iteminfo['ormcompo']['module'] = 'xnpmemo';
$iteminfo['ormcompo']['name'] = 'item';
$iteminfo['ormcompo']['primary_orm'] = 'basic';
$iteminfo['ormcompo']['primary_key'] = 'item_id';

//
// define orm of compo
$iteminfo['orm'][] = array('module' => 'xnpmemo', 'name' => 'item_detail',     'field' => 'detail',      'foreign_key' => 'memo_id', 'multiple' => false, 'required' => true);
$iteminfo['orm'][] = array('module' => 'xoonips', 'name' => 'file',            'field' => 'memo_file',    'foreign_key' => 'item_id', 'criteria' => iteminfo_file_criteria('memo_file'), 'multiple' => false);

//
// define database table information
$iteminfo['ormfield']['detail'] = array(array('name' => 'memo_id', 'type' => 'int', 'required' => ' false'),
                                         array('name' => 'item_link', 'type' => 'string', 'required' => ' false'), );

//
// detail information (modify below for each item types)
$iteminfo['io']['xmlrpc']['item'][] = array('orm' => array('field' => array(array('orm' => 'detail', 'field' => 'memo_id'))),
                                             'xmlrpc' => array('field' => array('detail_field', 'memo_id'),
                                                                'display_name' => '_MD_XNPMEMO_XMLRPC_DISPLAY_NAME_MEMO_ID',
                                                                'type' => 'string',
                                                                'multiple' => false,
                                                                'readonly' => true, ), );
$iteminfo['io']['xmlrpc']['item'][] = array('orm' => array('field' => array(array('orm' => 'detail', 'field' => 'item_link'))),
                                             'xmlrpc' => array('field' => array('detail_field', 'item_link'),
                                                                'display_name' => '_MD_XNPMEMO_XMLRPC_DISPLAY_NAME_ITEM_LINK',
                                                                'type' => 'string',
                                                                'multiple' => false, ), );
$iteminfo['io']['xmlrpc']['item'][] = array('orm' => array('field' => array(array('orm' => 'memo_file', 'field' => 'file_id'))),
                                             'xmlrpc' => array('field' => array('detail_field', 'memo_file'),
                                                                'display_name' => '_MD_XNPMEMO_XMLRPC_DISPLAY_NAME_MEMO_FILE',
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
                                                                                     array('orm' => 'detail', 'field' => 'item_link'), )),
                                                   'xmlrpc' => array('field' => array('text'),
                                                                      'type' => 'string', ),
                                                   'eval' => array('orm2xmlrpc' => '$in_var[0] = implode( ";", $in_var[0] ); $out_var[0] = implode( "/", $in_var );',
                                                                    'xmlrpc2orm' => ';', ), );
