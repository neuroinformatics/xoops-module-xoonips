<?php
// $Revision: 1.1.2.17 $
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

include_once XOOPS_ROOT_PATH . '/class/template.php';
include_once __DIR__ . '/transfer.class.php';
include_once dirname( __DIR__ ).'/base/gtickets.php';

/**
 * 
 * HTML view to list item to transfer for administrator.
 * 
 * 
 * 
 * 
 */
class XooNIpsViewTransferAdminItemList extends XooNIpsViewTransfer{
    /**
     * create view
     * 
     * @param arrray $params associative array of view
     * - $params['from_uid']:integer user id transfer from
     * - $params['to_uid']: integer user id transfer to
     * - $params['from_index_id']: integer index id transfer from
     * - $params['to_index_id']: integer index id transfer to
     * - $params['item_ids_to_transfer']: array of integer item id to transfer
     * - $params['child_items']: associative array of item to transfer
     * - $params['child_items'][(item_id:integer)]:
     *  array of item_id of child items.
     * - $params['limit_check_result']:
     *  boolean true if number of item or storage is out of bounds.
     * - $params['group_ids_to_subscribe']:
     *  array of integer group id(s) that subscribe to_uid
     */
    function XooNIpsViewTransferAdminItemList($params){
        parent::XooNIpsView($params);
    }
    
    function render(){
        global $xoopsConfig;
        $textutil=&xoonips_getutility('text');
        $xoopsTpl = new XoopsTpl();
        $this -> setXooNIpsStyleSheet($xoopsTpl);
        
        $xoopsTpl -> assign(
            'token_hidden',
            $GLOBALS['xoopsGTicket']->getTicketHtml( __LINE__, 600,
              'xoonips_transfer_admin_list_item', 600 ) );
        $xoopsTpl -> assign(
            'from_uname',
            $textutil->html_special_chars($this -> get_uname_by_uid( $this -> _params['from_uid'])));
        $xoopsTpl -> assign(
            'to_uname',
            $textutil->html_special_chars($this -> get_uname_by_uid( $this -> _params['to_uid'])));
        $xoopsTpl -> assign( 'from_index_path',
                             $this -> get_index_path_by_index_id(
                                 $this -> _params['from_index_id']) );
        $xoopsTpl -> assign('to_index_path',
                            $this -> get_index_path_by_index_id(
                                $this -> _params['to_index_id']) );
        $xoopsTpl -> assign( 'transfer_items',
                             $this -> get_transfer_item_template_vars() );
        $xoopsTpl -> assign( 'group_names_to_subscribe',
                             $this -> get_gnames_to_subscribe() );
        $xoopsTpl -> assign( 'group_names_to_subscribe_messages',
                             $this -> get_gnames_to_subscribe_messages() );
        $xoopsTpl -> assign( 'child_item_ids_to_transfer',
                             $this -> get_child_item_ids_to_transfer() );
        foreach( $this -> _params as $key => $val ){
            $xoopsTpl -> assign( $key, $val );
        }
        $form = $xoopsTpl -> fetch('db:xoonips_transfer_admin_item_list.html');

        global $xoonips_admin;
        
        $title = _AM_XOONIPS_MAINTENANCE_TRANSFER_ADMIN_ITEM_LIST_PAGE_TITLE;
        $description = _AM_XOONIPS_MAINTENANCE_TRANSFER_ADMIN_ITEM_LIST_MESSAGE;
        
        // breadcrumbs
        $breadcrumbs = array(
          array( 'type' => 'top',
                 'label' => _AM_XOONIPS_TITLE,
                 'url' => $xoonips_admin['admin_url'].'/' ),
          array( 'type' => 'link',
                 'label' => _AM_XOONIPS_MAINTENANCE_TITLE,
                 'url' => $xoonips_admin['myfile_url'] ),
          array( 'type' => 'link',
                 'label' => _AM_XOONIPS_MAINTENANCE_ITEM_TITLE,
                 'url' => $xoonips_admin['mypage_url'] ),
          array( 'type' => 'label',
                 'label' => $title,
                 'url' => '' )
        );

        // templates
        require_once( '../class/base/pattemplate.class.php' );
        $tmpl = new PatTemplate();
        $tmpl->setBaseDir( 'templates' );
        $tmpl->readTemplatesFromFile( 'maintenance_item_transfer.tmpl.html' );

        // assign template variables
        $tmpl->addVar( 'header', "TITLE", $title );
        $tmpl->addVar( 'main', "TITLE", $title );
        $tmpl->setAttribute( 'description', 'visibility', 'visible' );
        $tmpl->addVar( 'description', "DESCRIPTION", $description );
        $tmpl->setAttribute( 'breadcrumbs', 'visibility', 'visible' );
        $tmpl->addRows( 'breadcrumbs_items', $breadcrumbs );
        $tmpl->addVar( 'main', 'BODY', $form );

        // display
        xoops_cp_header();
        $tmpl->displayParsedTemplate( 'main' );
        xoops_cp_footer();

    }
    
    function get_transfer_item_template_vars(){
        $result = array();
        $item_ids = array_unique( $this -> _params['item_ids_to_transfer'] );
        sort( $item_ids );
        
        $item_handler =& xoonips_getormcompohandler( 'xoonips', 'item' );
        $item_type_handler =& xoonips_getormhandler( 'xoonips', 'item_type' );
        foreach( $item_ids as $item_id ){
            $item =& $item_handler -> get( $item_id );
            $basic =& $item -> getVar( 'basic' );
            $itemtype 
                =& $item_type_handler -> get( $basic -> get( 'item_type_id' ) );
            
            $child_titles = array();
            $childs = array_key_exists( 
                $item_id,
                $this -> _params['child_items'] ) 
                ? $this -> _params['child_items'][$item_id] : array();
            foreach( $childs as $child_item_id ){
                $child_item =& $item_handler -> get( $child_item_id );
                $child_titles[]
                    = $this -> concatenate_titles(
                        $child_item -> getVar( 'titles' ) );
            }

            $result[] = array( 
                'item_id' => $item_id,
                'item_type_name' => $itemtype -> getVar( 'display_name', 's' ),
                'title' => $this -> concatenate_titles(
                    $item -> getVar( 'titles' ) ),
                'child_titles' => $child_titles
                );
        }
        return $result;
    }
    
    function get_gnames_to_subscribe(){
        $result = array();
        $gids = array();
        foreach( $this -> _params['group_ids_to_subscribe'] as $gid ){
            $gids[] = intval( $gid );
        }
        if ( empty( $gids ) ) {
          return $result;
        }
        $xgroup_handler =& xoonips_gethandler( 'xoonips', 'group' );
        $xgroup_objs =& $xgroup_handler->getGroupObjects( $gids );
        foreach( $xgroup_objs as $xgroup_obj ){
            $result[] = $xgroup_obj->getVar( 'gname', 's' );
        }
        return $result;
    }
    
    function get_gnames_to_subscribe_messages(){
        $textutil=&xoonips_getutility('text');
        $result = array();
        foreach( $this -> get_gnames_to_subscribe() as $gname ){
            $result[] = sprintf(
                _AM_XOONIPS_MAINTENANCE_TRANSFER_ADMIN_ITEM_LIST_SUBSCRIBE_USER_TO_GROUP,
                $gname,
                $textutil->html_special_chars($this->get_uname_by_uid( $this -> _params['to_uid'])));
        }
        return $result;
    }
    
    function get_child_item_ids_to_transfer(){
        $item_ids = array();
        foreach( array_values( $this -> _params['child_items'] )
                 as $child_item_ids ){
            $item_ids = array_merge( $item_ids, $child_item_ids );
        }
        return array_unique( $item_ids );
    }
}
?>
