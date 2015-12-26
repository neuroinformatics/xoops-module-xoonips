<?php
// $Revision: 1.1.4.1.2.7 $
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

if ( ! defined( 'XOOPS_ROOT_PATH' ) ) exit();

include XOOPS_ROOT_PATH . '/modules/xoonips/include/iteminfo.inc.php';

//
//
$iteminfo['description'] = 'XooNIps Data Item Type';
$iteminfo['files']['main'] = 'data_file';
$iteminfo['files']['preview'] = 'preview';
$iteminfo['files']['others'] = array();

//
// define compo
$iteminfo['ormcompo']['module'] ='xnpdata';
$iteminfo['ormcompo']['name'] ='item';
$iteminfo['ormcompo']['primary_orm'] ='basic';
$iteminfo['ormcompo']['primary_key'] ='item_id';

//
// define orm of compo
$experimenter_order_criteria=new Criteria(1,1);
$experimenter_order_criteria->setSort('experimenter_order');
$iteminfo['orm'][] = array( 'module' => 'xnpdata', 'name' => 'item_detail','field' => 'detail',      'foreign_key' => 'data_id', 'multiple' => false, 'required'=>true);
$iteminfo['orm'][] = array( 'module' => 'xnpdata', 'name' => 'experimenter',     'field' => 'experimenter',      'foreign_key' => 'data_id', 'multiple' => true, 'criteria'=>$experimenter_order_criteria );
$iteminfo['orm'][] = array( 'module' => 'xoonips', 'name' => 'file', 'field' => 'data_file', 'foreign_key' => 'item_id', 'criteria' => iteminfo_file_criteria( 'data_file' ), 'multiple' => false );
$iteminfo['orm'][] = array( 'module' => 'xoonips', 'name' => 'file', 'field' => 'preview',   'foreign_key' => 'item_id', 'criteria' => iteminfo_file_criteria( 'preview' ), 'multiple' => true );


// 
// define database table information
$iteminfo['ormfield']['detail'] = array( array( 'name' => 'data_id', 'type' => 'int', 'required' => ' false' ),
                                         array( 'name' => 'data_type', 'type' => 'string', 'required' => ' true' ),
                                         array( 'name' => 'readme', 'type' => 'string', 'required' => ' false' ),
                                         array( 'name' => 'rights', 'type' => 'string', 'required' => ' false' ),
                                         array( 'name' => 'use_cc', 'type' => 'int', 'required' => ' false' ),
                                         array( 'name' => 'cc_commercial_use', 'type' => 'int', 'required' => ' false' ),
                                         array( 'name' => 'cc_modification', 'type' => 'int', 'required' => ' false' ),
                                         array( 'name' => 'attachment_dl_limit', 'type' => 'int', 'required' => ' false' ),
                                         array( 'name' => 'attachment_dl_notify', 'type' => 'int', 'required' => ' false' ) );

$iteminfo['ormfield']['experimenter'] = array( array( 'name' => 'experimenter', 'type' => 'string', 'required' => true ),
                                               array( 'name' => 'experimenter_order', 'type' => 'int', 'required' => true ) );


//
// publication_year, month, mday(creation_year, month, mday in XML-RPC) is required
foreach( $iteminfo['io']['xmlrpc']['item'] as $key=>$val ){
    if( in_array( $val['xmlrpc']['field'][0], array( 'creation_year', 'creation_month', 'creation_mday' ) ) ){
        $iteminfo['io']['xmlrpc']['item'][$key]['xmlrpc']['required'] = true;
    }
}

//
// detail information (modify below for each item types)
$iteminfo['io']['xmlrpc']['item'][] = array( 'orm' => array( 'field' => array( array( 'orm' => 'detail', 'field' => 'data_id' ) ) ), 
                                             'xmlrpc' => array( 'field' => array( 'detail_field','data_id' ),
                                                                'display_name' => '_MD_XNPDATA_XMLRPC_DISPLAY_NAME_DATA_ID',
                                                                'type' => 'string',
                                                                'readonly' => true ) );
$iteminfo['io']['xmlrpc']['item'][] = array( 'orm' => array( 'field' => array( array( 'orm' => 'detail', 'field' => 'data_type' ) ) ), 
                                             'xmlrpc' => array( 'field' => array( 'detail_field','data_type' ),
                                                                'display_name' => '_MD_XNPDATA_XMLRPC_DISPLAY_NAME_DATA_TYPE',
                                                                'type' => 'string',
                                                                'options' => array(
                                                                    array( 'option' => 'excel'   , 'display_name' => '_MD_XNPDATA_XMLRPC_DISPLAY_NAME_DATA_TYPE_EXCEL' ),
                                                                    array( 'option' => 'movie'   , 'display_name' => '_MD_XNPDATA_XMLRPC_DISPLAY_NAME_DATA_TYPE_MOVIE' ),
                                                                    array( 'option' => 'text'    , 'display_name' => '_MD_XNPDATA_XMLRPC_DISPLAY_NAME_DATA_TYPE_TEXT' ),
                                                                    array( 'option' => 'picture' , 'display_name' => '_MD_XNPDATA_XMLRPC_DISPLAY_NAME_DATA_TYPE_PICTURE' ),
                                                                    array( 'option' => 'other'   , 'display_name' => '_MD_XNPDATA_XMLRPC_DISPLAY_NAME_DATA_TYPE_OTHER' ),
                                                                 ),
                                                                 'required' => true ) );
$iteminfo['io']['xmlrpc']['item'][] = array( 'orm' => array( 'field' => array( array('orm' => 'experimenter', 'field' => 'experimenter' ) ) ),
                                             'xmlrpc' => array( 'field' => array( 'detail_field','experimenter' ),
                                                                'display_name' => '_MD_XNPDATA_XMLRPC_DISPLAY_NAME_EXPERIMENTER',
                                                                'type' => 'string',
                                                                'multiple' => true,
                                                                'required' => true ) );
$iteminfo['io']['xmlrpc']['item'][] = array( 'orm' => array( 'field' => array( array( 'orm' => 'detail', 'field' => 'rights' ) ) ), 
                                             'xmlrpc' => array( 'field' => array( 'detail_field','rights' ),
                                                                'display_name' => '_MD_XNPDATA_XMLRPC_DISPLAY_NAME_RIGHTS',
                                                                'type' => 'string' ) );
$iteminfo['io']['xmlrpc']['item'][] = array( 'orm' => array( 'field' => array( array( 'orm' => 'detail', 'field' => 'readme' ) ) ), 
                                             'xmlrpc' => array( 'field' => array( 'detail_field','readme' ),
                                                                'display_name' => '_MD_XNPDATA_XMLRPC_DISPLAY_NAME_README',
                                                                'type' => 'string' ) );
$iteminfo['io']['xmlrpc']['item'][] = array( 'orm' => array( 'field' => array( array( 'orm' => 'detail', 'field' => 'use_cc' ) ) ), 
                                             'xmlrpc' => array( 'field' => array( 'detail_field','use_cc' ),
                                                                'display_name' => '_MD_XNPDATA_XMLRPC_DISPLAY_NAME_USE_CC',
                                                                'type' => 'string' ) );
$iteminfo['io']['xmlrpc']['item'][] = array( 'orm' => array( 'field' => array( array( 'orm' => 'detail', 'field' => 'cc_commercial_use' ) ) ), 
                                             'xmlrpc' => array( 'field' => array( 'detail_field','cc_commercial_use' ),
                                                                'display_name' => '_MD_XNPDATA_XMLRPC_DISPLAY_NAME_CC_COMMERCIAL_USE',
                                                                'type' => 'string' ) );
$iteminfo['io']['xmlrpc']['item'][] = array( 'orm' => array( 'field' => array( array( 'orm' => 'detail', 'field' => 'cc_modification' ) ) ), 
                                             'xmlrpc' => array( 'field' => array( 'detail_field','cc_modification' ),
                                                                'display_name' => '_MD_XNPDATA_XMLRPC_DISPLAY_NAME_CC_MODIFICATION',
                                                                'type' => 'string' ) );
$iteminfo['io']['xmlrpc']['item'][] = array( 'orm' => array( 'field' => array( array( 'orm' => 'detail', 'field' => 'attachment_dl_limit' ) ) ), 
                                             'xmlrpc' => array( 'field' => array( 'detail_field','attachment_dl_limit' ),
                                                                'display_name' => '_MD_XNPDATA_XMLRPC_DISPLAY_NAME_ATTACHMENT_DL_LIMIT',
                                                                'type' => 'string' ) );
$iteminfo['io']['xmlrpc']['item'][] = array( 'orm' => array( 'field' => array( array( 'orm' => 'detail', 'field' => 'attachment_dl_notify' ) ) ), 
                                             'xmlrpc' => array( 'field' => array( 'detail_field','attachment_dl_notify' ),
                                                                'display_name' => '_MD_XNPDATA_XMLRPC_DISPLAY_NAME_ATTACHMENT_DL_NOTIFY',
                                                                'type' => 'string' ) );
$iteminfo['io']['xmlrpc']['item'][] = array( 'orm' => array( 'field' => array( array( 'orm' => 'data_file', 'field' => 'file_id' ) ) ), 
                                             'xmlrpc' => array( 'field' => array( 'detail_field','data_file' ),
                                                                'display_name' => '_MD_XNPDATA_XMLRPC_DISPLAY_NAME_DATA_FILE',
                                                                'type' => 'int',
                                                                'required' => true ) );
$iteminfo['io']['xmlrpc']['item'][] = array( 'orm' => array( 'field' => array( array( 'orm' => 'preview', 'field' => 'file_id' ) ) ), 
                                             'xmlrpc' => array( 'field' => array( 'detail_field','preview' ),
                                                                'type' => 'int',
                                                                'display_name' => '_MD_XNPDATA_XMLRPC_DISPLAY_NAME_PREVIEW',
                                                                'multiple' => true  ) );


//-------------------------
// SimpleItem
//-------------------------
$iteminfo['io']['xmlrpc']['simpleitem'][] = array( 'orm' => array( 'field' => array( array( 'orm' => 'basic',    'field' => 'item_id' ) ) ),      
                                                   'xmlrpc' => array( 'field' => array( 'item_id' ),  
                                                                      'type' => 'int',    
                                                                      'multiple' => false ) );
$iteminfo['io']['xmlrpc']['simpleitem'][] = array( 'orm' => array( 'field' => array( array( 'orm' => 'basic',    'field' => 'item_type_id' ) ) ), 
                                                   'xmlrpc' => array( 'field' => array( 'itemtypeid' ), 
                                                                      'type' => 'int',    
                                                                      'multiple' => false) );
$iteminfo['io']['xmlrpc']['simpleitem'][] = array( 'orm' => array( 'field' => array( array( 'orm' => 'basic',   'field' => 'uid' ) ) ),        
                                                   'xmlrpc' => array( 'field' => array( 'username' ),   
                                                                      'type' => 'string', 
                                                                      'multiple' => false),
                                                   'eval' => array( 'orm2xmlrpc' => '$u_handler=&xoops_gethandler("user"); $user=&$u_handler->get($in_var[0]); $out_var[0]=$user->getVar("uname");',
                                                                    'xmlrpc2orm' => ';' ) );
$iteminfo['io']['xmlrpc']['simpleitem'][] = array( 'orm' => array( 'field' => array( array( 'orm' => 'titles',   'field' => 'title' ) ) ),        
                                                   'xmlrpc' => array( 'field' => array( 'titles' ),   
                                                                      'type' => 'string', 
                                                                      'multiple' => true) );
$iteminfo['io']['xmlrpc']['simpleitem'][] = array( 'orm' => array( 'field' => array( array( 'orm' => 'basic',    'field' => 'last_update_date' ) ) ),
                                                   'xmlrpc' => array( 'field' => array( 'last_modified_date' ),  
                                                                      'type' => 'dateTime.iso8601',
                                                                      'multiple' => false ) );
$iteminfo['io']['xmlrpc']['simpleitem'][] = array( 'orm' => array( 'field' => array( array( 'orm' => 'basic',    'field' => 'creation_date' ) ) ),
                                                   'xmlrpc' => array( 'field' => array( 'registration_date' ),  
                                                                      'type' => 'dateTime.iso8601',
                                                                      'multiple' => false ) );
$iteminfo['io']['xmlrpc']['simpleitem'][] = array( 'orm' => array( 'field' => array( array('orm' => 'titles', 'field' => 'title' ),
                                                                                     array('orm' => 'detail', 'field' => 'data_type' ),
                                                                                     array('orm' => 'experimenter', 'field' => 'experimenter' ) ) ),
                                                   'xmlrpc' => array( 'field' => array( 'text' ), 
                                                                      'type' => 'string' ),
                                                   'eval' => array( 'orm2xmlrpc' => '$in_var[0] = implode( ";", $in_var[0] ); $in_var[2] = implode( ";", $in_var[2] ); $out_var[0] = implode( "/", $in_var );',
                                                                    'xmlrpc2orm' => ';' ) );

?>
