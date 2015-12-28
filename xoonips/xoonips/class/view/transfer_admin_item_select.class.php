<?php
// $Revision: 1.1.2.13 $
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

/**
 * 
 * HTML view to select item to transfer for administrator.
 * 
 * 
 * 
 * 
 */
class XooNIpsViewTransferAdminItemSelect extends XooNIpsViewTransfer{
    
    var $_number_of_item_per_page = 50;//final
    
    /**
     * create view
     * 
     * @param arrray $params associative array of view
     * - $params['from_uid']:integer user id transfer from
     * - $params['to_uid']: integer user id transfer to
     * - $params['from_index_id']: integer index id transfer from
     * - $params['to_index_id']: integer index id transfer to
     * - $params['from_index_item_ids']: array of integer item id
     *   of index of from_index_id
     * - $params['can_not_transfer_items']: array like below
     * - $params['can_not_transfer_items']['locked']:
     *   array of item id that can not be transferd because of item lock.
     * - $params['can_not_transfer_items']['have_another_parent']:
     *   array of item id that can not be transferd 
     *   because item has dependency to some other items.
     * - $params['child_items']: associative array
     * - $params['child_items'][(item_id:integer)]:
     *   array of item_id of child items.
     * - $params['selected_item_ids']:
     *   array of item id that is selected to transfer.
     * - $params['page']: integer page number to show.
     * - $params['from_user_options']: associative array
     * - $params['from_user_options'][(uid:integer)]:
     *   string uname(login name) transfer from
     * - $params['to_user_options']: associative array
     * - $params['to_user_options'][(uid:integer)]:
     *   string uname(login name) transfer to
     * - $params['from_index_options']: array like below
     * - $params['from_index_options'][]: associative array like below
     * - $params['from_index_options'][]['index_id']: integer index id
     * - $params['from_index_options'][]['depth']:
     *   integer depth of the index(zero if the index is child of IID_ROOT)
     * - $params['from_index_options'][]['title']: string name of the index
     * - $params['from_index_options'][]['item_count']:
     *   integer number of items in the index
     * - $params['to_index_options']:
     *   array same structure with 'from_index_options'
     */
    function XooNIpsViewTransferAdminItemSelect($params){
        parent::XooNIpsView($params);
    }
    
    function render(){
        global $xoopsUser, $xoopsConfig, $xoopsUserIsAdmin, $xoopsLogger;
        
        $xoopsTpl = new XoopsTpl();
        $this -> setXooNIpsStyleSheet($xoopsTpl);
        
        $transfer_item_options = $this -> get_transfer_item_template_vars();
        $xoopsTpl -> assign(
            'transfer_item_options',
            array_slice( $transfer_item_options,
                         ( $this -> _params['page']-1 ) * 50, 50 ) );
        $xoopsTpl -> assign(
            'transfer_item_options_hidden',
            array_merge( array_slice( $transfer_item_options,
                                      0, ( $this -> _params['page']-1 ) * 50),
                         array_slice( $transfer_item_options,
                                      ( $this -> _params['page'] ) * 50 )) );
        foreach( $this -> _params as $key => $val ){
            $xoopsTpl -> assign( $key, $val );
        }
        $xoopsTpl -> assign( 'pages', $this -> get_page_number_array() );
        $xoopsTpl -> assign( 'maxpage', $this -> get_max_page() );
        
        $form = $xoopsTpl -> fetch(
            'db:xoonips_transfer_admin_item_select.html');
        
        global $xoonips_admin;
        
        $title = _AM_XOONIPS_MAINTENANCE_ITEM_TRANSFER_TITLE;
        // $description = "";
        
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
          array( 'type' => 'label', 'label' => $title, 'url' => '' )
        );

        // templates
        require_once( '../class/base/pattemplate.class.php' );
        $tmpl = new PatTemplate();
        
        $tmpl->setBaseDir( 'templates' );
        $tmpl->readTemplatesFromFile( 'maintenance_item_transfer.tmpl.html' );

        // assign template variables
        $tmpl->addVar( 'header', 'TITLE', $title );
        $tmpl->addVar( 'main', 'TITLE', $title );
        // $tmpl->setAttribute( 'description', 'visibility', 'visible' );
        // $tmpl->addVar( 'description', "DESCRIPTION", $description );
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
        
        $item_handler =& xoonips_getormcompohandler( 'xoonips', 'item' );
        $item_type_handler =& xoonips_getormhandler( 'xoonips', 'item_type' );
        
        foreach( $this -> _params['from_index_item_ids'] as $item_id ){
            $childs = array_key_exists(
                $item_id, $this -> _params['child_items'] )
                ? $this -> _params['child_items'][$item_id] : array();
            $item =& $item_handler -> get( $item_id );
            $basic =& $item -> getVar( 'basic' );
            $itemtype
                =& $item_type_handler -> get( $basic -> get( 'item_type_id' ) );
            
            $child_titles = array();
            foreach( $childs as $child_item_id ){
                $child_item =& $item_handler -> get( $child_item_id );
                $child_titles[] = $this -> concatenate_titles(
                    $child_item -> getVar( 'titles' ) );
            }
            $result[] = array(
                'checked' => in_array( $item_id,
                                       $this -> _params['selected_item_ids'] ),
                'item_id' => $item_id,
                'item_type_name' => $itemtype -> getVar( 'display_name', 's' ),
                'title' => $this -> concatenate_titles(
                    $item -> getVar( 'titles' ) ),
                'child_titles' => $child_titles
                );
        }
        return $result;
    }
    
    function get_max_page(){
        return ceil( count( $this -> _params['from_index_item_ids'] )
                     / $this -> _number_of_item_per_page );
    }
    
    function get_page_number_array(){
        $result = array();
        for( $i = 1; $i <= $this -> get_max_page(); $i++ ){
            $result[] = $i;
        }
        return $result;
    }
}
?>
