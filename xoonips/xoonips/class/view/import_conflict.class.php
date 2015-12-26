<?php
// $Revision: 1.1.2.10 $
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

include_once dirname( dirname( __FILE__ ) ) . '/base/view.class.php';

class XooNIpsViewImportConflict extends XooNIpsView{
    
    var $_item_per_page = 50;
    
    function XooNIpsViewImportConflict($params){
        parent::XooNIpsView($params);
    }
    
    function render(){
        global $xoopsOption, $xoopsConfig, $xoopsUser, $xoopsUserIsAdmin, $xoopsLogger, $xoopsTpl;
        $conflict_items = array();
        $c=0;
        foreach( $this -> _params['import_items'] as $item ){
            // skip no conflict item.
            if( !$this->isConflictItem($item) ) continue;
            
            $c++;
            if( $c <= ( $this->getPageNo() - 1 ) * $this->_item_per_page )
                continue;
            if( $this->getPageNo() * $this->_item_per_page < $c ) break;
            
            $vars = array();
            $handler =& xoonips_gethandler( 'xoonips', 'import_item' );
            $vars['import_item'] = array(
                'pseudo_id' => $item -> getPseudoId(),
                'update_flag' => $item -> getUpdateFlag(),
                'item_text' => $item -> getItemAbstractText() );
            
            $vars['conflict_updatable_items'] = array();
            $handler =& xoonips_getormcompohandler( 'xoonips', 'item' );
            foreach( $item -> getDuplicateUpdatableItemId() as $id ){
                $vars['conflict_updatable_items'][] = array(
                    'item_id' => $id,
                    'item_text'
                    => $handler -> getItemAbstractTextById( $id ) );
            }
        
            $vars['conflict_import_items'] = array();
            $handler =& xoonips_gethandler( 'xoonips', 'import_item' );
            foreach( $item -> getDuplicatePseudoId() as $id ){
                foreach( $this -> _params['import_items'] as $item ){
                    if( $item ->getPseudoId() == $id ){
                        $vars['conflict_import_items'][] = array(
                            'item_id' => $id,
                            'item_text' => $item -> getItemAbstractText() );
                        break;
                    }
                }
            }
            
            $vars['conflict_unupdatable_items'] = array();
            $handler =& xoonips_getormcompohandler( 'xoonips', 'item' );
            foreach( $item -> getDuplicateUnupdatableItemId() as $id ){
                $vars['conflict_unupdatable_items'][] = array(
                    'item_id' => $id,
                    'item_text'
                    => $handler -> getItemAbstractTextById( $id ) );
            }
            
            $handler =& xoonips_getormcompohandler( 'xoonips', 'item' );
            $lock_handler =& xoonips_getormhandler( 'xoonips', 'item_lock' );
            $vars['conflict_certify_request_locked_items'] = array();
            foreach( $item -> getDuplicateLockedItemId() as $id ){
                if( $lock_handler -> isLocked( $id ) ){
                    if( $lock_handler -> getLockType( $id )
                        == XOONIPS_LOCK_TYPE_CERTIFY_REQUEST ){
                        $vars['conflict_certify_request_locked_items'][]
                            = array(
                                'item_id' => $id,
                                'item_text'
                                => $handler
                                -> getItemAbstractTextById( $id ) );
                    }else
                        die( 'unknown lock type:'
                             .$lock_handler -> getLockType( $id ) );
                }
            }
            
            $conflict_items[] = $vars;
        }
        
        $handler =& xoonips_gethandler( 'xoonips', 'import_item' );
        $xoopsOption['template_main'] = 'xoonips_import_conflict.html';
        include XOOPS_ROOT_PATH.'/header.php';
        $xoopsTpl -> assign(
            'import_as_new_flag',
            isset( $this -> _params['import_as_new_flag'] )
            ? $this -> _params['import_as_new_flag'] : '0' );
        $xoopsTpl -> assign('number_of_conflict_items',
                            $handler -> numberOfConflictItem(
                                $this -> _params['import_items'] ) );
        $xoopsTpl -> assign( 'conflict_items', $conflict_items );
        $xoopsTpl -> assign(
            'private_item_number_limit_over',
            isset( $this -> _params['private_item_number_limit_over'] )
            ? $this -> _params['private_item_number_limit_over'] : false );
        $xoopsTpl -> assign( 
            'private_item_storage_limit_over',
            isset( $this -> _params['private_item_storage_limit_over'] )
            ? $this -> _params['private_item_storage_limit_over'] : false );
        $xoopsTpl -> assign( 'page', $this->getPageNo() );
        $xoopsTpl -> assign(
            'page_max',
            ceil( $this->getConflictItemCount()/ $this->_item_per_page ) );
        
        include XOOPS_ROOT_PATH.'/footer.php';
    }
    
    /**
     * get current page number to show from input of view
     * (if no page number, returns '1')
     * @return integer page number
     */
    function getPageNo(){
        return isset( $this -> _params['page'] )
            ? $this -> _params['page']
            : '1';
    }
    
    /**
     * return boolean value of confliction item
     * @param XooNIpsImportItem $item
     * @return true(conflict) or false(not conflict)
     */
    function isConflictItem($item){
        return  count( $item -> getDuplicateUpdatableItemId() ) > 0
            || count( $item -> getDuplicatePseudoId() ) > 0
            || count( $item -> getDuplicateUnupdatableItemId() ) > 0
            || count( $item -> getDuplicateLockedItemId() ) > 0;
    }
    
    /**
     * return number of conflict items
     * @return integer number of conflict items
     */
    function getConflictItemCount(){
        $result = 0;
        foreach( $this -> _params['import_items'] as $item ){
            if( $this->isConflictItem($item) ) $result++;
        }
        return $result;
    }
}

?>
