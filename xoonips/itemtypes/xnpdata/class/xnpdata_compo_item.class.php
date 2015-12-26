<?php
// $Revision: 1.1.2.6 $
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

include_once XOOPS_ROOT_PATH . '/modules/xoonips/class/xoonips_compo_item.class.php';
include_once XOOPS_ROOT_PATH . '/modules/xnpdata/iteminfo.php';
include_once dirname( dirname( __FILE__ ) ) . '/include/view.php';

/**
 *
 * @brief Handler object that create,insert,update,get,delete XNPDataCompo object.
 *
 */
class XNPDataCompoHandler extends XooNIpsItemInfoCompoHandler
{
    function XNPDataCompoHandler(&$db) 
    {
        parent::XooNIpsItemInfoCompoHandler($db, 'xnpdata');
    }
    function &create() 
    {
        $data = new XNPDataCompo();
        return $data;
    }

    /**
     * return template filename
     * 
     * @param string $type defined symbol 
     *  XOONIPS_TEMPLATE_TYPE_TRANSFER_ITEM_DETAIL
     *  or XOONIPS_TEMPLATE_TYPE_TRANSFER_ITEM_LISTL
     * @return template filename
     */
    function getTemplateFileName($type){
        switch( $type ){
        case XOONIPS_TEMPLATE_TYPE_TRANSFER_ITEM_DETAIL:
            return 'xnpdata_transfer_item_detail.html';
        case XOONIPS_TEMPLATE_TYPE_TRANSFER_ITEM_LIST:
            return 'xnpdata_transfer_item_list.html';
        default:
            return '';
        }
    }
    
    /**
     * return template variables of item
     * 
     * @param string $type defined symbol 
     *  XOONIPS_TEMPLATE_TYPE_TRANSFER_ITEM_DETAIL
     *  , XOONIPS_TEMPLATE_TYPE_TRANSFER_ITEM_LIST
     *  or XOONIPS_TEMPLATE_TYPE_ITEM_LIST
     * @param int $item_id
     * @param int $uid user id who get item
     * @return array of template variables
     */
    function getTemplateVar($type, $item_id, $uid){
        $data =& $this->get( $item_id );
        if ( ! is_object( $data ) ) {
          return array();
        }
        $result = $this->getBasicTemplateVar($type, $data, $uid);

        $textutil=&xoonips_getutility('text');
        $detail =& $data -> getVar( 'detail' );
        switch( $type ){
        case XOONIPS_TEMPLATE_TYPE_ITEM_LIST:
            $result['experimenter']=array();
            foreach( $data -> getVar( 'experimenter' ) as $experimenter ){
                $result['experimenter'][] = $experimenter->getVarArray('s');
            }
            $result['detail']=$detail->getVarArray('s');
            $result['detail']['data_type']=$textutil->html_special_chars($this -> get_data_type_label($detail -> get( 'data_type' )));
            return $result;
        case XOONIPS_TEMPLATE_TYPE_TRANSFER_ITEM_DETAIL:
        case XOONIPS_TEMPLATE_TYPE_ITEM_DETAIL:
        case XOONIPS_TEMPLATE_TYPE_TRANSFER_ITEM_LIST:
            $result['xnpdata_experimenter']
                =xoonips_get_multiple_field_template_vars($detail->getExperimenters(),
                                                          'xnpdata',
                                                          'experimenter');
            $result['detail']=$detail->getVarArray('s');
            $result['detail']['data_type']=$textutil->html_special_chars($this -> get_data_type_label($detail -> get( 'data_type' )));
            $result['detail']['data_type_value']=$detail -> get( 'data_type', 's');
            
            if( $detail->getVar('use_cc', 'n' ) ){
                $result['detail']['rights']=$detail->getVar('rights', 'n');
            }
            
            if( is_array( $data -> getVar( 'preview' ) ) ){
                $result['detail']['previews'] = array();
                foreach( $data -> getVar( 'preview' ) as $preview ){
                    $result['detail']['previews'][]
                        = $this -> getPreviewTemplateVar( $preview );
                }
            }
            
            $data_file = $data -> getVar( 'data_file' );
            if( $data_file -> get( 'item_id' ) == $item_id ){
                $result['detail']['data_file'] = $this -> getAttachmentTemplateVar($data -> getVar( 'data_file' ) );
            }
            return $result;
        }
    }

    function get_data_type_label( $type ){
        $keyval = xnpdataGetTypes();
        return $keyval[ $type ];
    }
    
}

/**
 *
 * @brief Data object that have one ore more XooNIpsTableObject for Data type.
 *
 */
class XNPDataCompo extends XooNIpsItemInfoCompo
{
    function XNPDataCompo() 
    {
        parent::XooNIpsItemInfoCompo('xnpdata');
    }
}
?>
