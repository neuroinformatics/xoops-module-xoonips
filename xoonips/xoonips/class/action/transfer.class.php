<?php
// $Revision: 1.1.2.19 $
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

include_once dirname( dirname( __FILE__ ) ) . '/base/action.class.php';
class XooNIpsActionTransfer extends XooNIpsAction{
    function XooNIpsActionTransfer(){
        parent::XooNIpsAction();
    }

  /**
   * return user's private index information array for template vars.
   * return id, name and depth of all index under the indexes.
   * 
   * @access protected
   * @param int $private_root_index_id user's private root index id
   * @return array array of index information like
   *  array( array( 'index_id' => index_id,
   *                'title' => index_name,
   *                'number_of_indexes' => number_of_indexes_in_its_index,
   *                'number_of_items' => number_of_items_in_its_index,
   *                'depth' => depth(0~) ), array(...), ... )
   */
  function getIndexOptionsTemplateVar( $private_root_index_id ) {
    $myuid = $GLOBALS['xoopsUser']->getVar( 'uid' );
    $xu_handler =& xoonips_getormhandler( 'xoonips', 'users' );
    $xu_obj =& $xu_handler->get( $myuid );
    $index_handler =& xoonips_gethandler( 'xoonips', 'index' );
    $result = $index_handler->getIndexStructure( $private_root_index_id, 's', $myuid, 'read' );
    // override index title from user's root index to 'Private'
    $result[0]['title'] = XNP_PRIVATE_INDEX_TITLE;
    return $result;
  }
    
    /**
     * return true if user is subscribed to all groups of given items.
     * 
     * @param integer $uid user id
     * @param array $item_ids array of integer of item id
     * @return boolean
     * 
     */
    function is_user_in_group_of_items($uid, $item_ids){
        return 0 == count( $this -> get_gids_to_subscribe($uid, $item_ids) );
    }
    
    function get_gids_to_subscribe($uid, $item_ids){
        $item_group_ids = xoonips_transfer_get_group_ids_of_items( $item_ids );
        $xgroup_handler =& xoonips_gethandler( 'xoonips', 'group' );
        $gids = $xgroup_handler->getGroupIds( $uid );
        // return array_diff( $item_group_ids, $gids ); 
        $result = array();
        foreach ( $item_group_ids as $gid ) {
          if ( ! in_array( $gid, $gids ) ) {
            $result[] = $gid;
          }
        } 
        return $result;
    }
    
    /**
     * return true if number or storage size of item exceed
     */
    function get_limit_check_result($to_uid, $transfer_item_ids){
        return xoonips_transfer_is_private_item_number_exceeds_if_transfer(
            $to_uid, $transfer_item_ids )
            || xoonips_transfer_is_private_item_storage_exceeds_if_transfer(
                $to_uid, $transfer_item_ids );
    }
    
    /**
     * get item id arrays by uid
     * @access public
     * @param array $item_ids  array of integer of item_id
     * @return array 
     *  array(uid_of_contributor => array( item_id, item_id, ...),
     *       uid_of_contributor => array( item_id, item_id, ...),
     *       .... );
     */
    function getMapOfUidTOItemId($item_ids){
        $result = array();
        $handler =& xoonips_getormhandler( 'xoonips', 'item_basic' );
        
        if( !is_array($item_ids) || count($item_ids) == 0 ) return array();
        
        foreach( $handler->getObjects(
            new Criteria('item_id', '(' . implode(', ', $item_ids) . ')', 'IN'))
                 as $row ){
            if( !array_key_exists($row -> get( 'uid' ), $result ) ){
                $result[$row -> get( 'uid' )] = array();
            }
            $result[$row -> get( 'uid' )][] = $row -> get( 'item_id' );
        }
        return $result;
    }
    
    /**
     * sort item ids by item title
     * @access protected
     * @param array $item_ids
     * @return array sorted $item_ids by title
     */
    function sort_item_ids_by_title($item_ids){
        if ( empty( $item_ids ) ){
            return array();
        }
        $title_handler =& xoonips_getormhandler( 'xoonips', 'title' );
        $criteria = new CriteriaCompo();
        $criteria->add( new Criteria( 'item_id', 
                                      '('.implode(',', $item_ids).')', 'in' ) );
        $criteria->add( new Criteria( 'title_id', 
                                      DEFAULT_ORDER_TITLE_OFFSET ) );
        $criteria->setOrder('asc');
        $criteria->setSort('title');
        $titles =& $title_handler->getObjects( $criteria );
        
        $result = array();
        foreach ( $titles as $title ){
            $result[] = $title->get( 'item_id' );
        }
        return $result;
    }
    
    /**
     * get untransferrable items grouped by reasons
     * @access protected
     * @param array $from_uid uid of transferer
     * @param array $item_ids
     * @return array 
     *  array(request_certify => array( item_id, item_id, ...),
     *       request_transfer => array( item_id, item_id, ...),
     *       have_another_parent => array( item_id, item_id, ...),
     *       child_request_certify => array( item_id, item_id, ...),
     *       child_request_transfer => array( item_id, item_id, ...),
     */
    function get_untransferrable_reasons_and_items( $from_uid, $item_ids ){
        $result = array();
        $result['request_certify'] = array();
        $result['request_transfer'] = array();
        $result['have_another_parent'] = array();
        $result['child_request_certify'] = array();
        $result['child_request_transfer'] = array();
        $result['child_have_another_parent'] = array();
        
        foreach( xoonips_transfer_get_transferrable_item_information(
            $from_uid, $item_ids ) as $info ){
            if( $info['lock_type'] == XOONIPS_LOCK_TYPE_CERTIFY_REQUEST ){
                $result['request_certify'][] = $info['item_id'];
            }
            if( $info['lock_type'] == XOONIPS_LOCK_TYPE_TRANSFER_REQUEST ){
                $result['request_transfer'][] = $info['item_id'];
            }
            if( $info['have_another_parent'] ){
                $result['have_another_parent'][] = $info['item_id'];
            }
            foreach ( $info['child_items'] as $child_info ){
                if( $child_info['lock_type']
                    == XOONIPS_LOCK_TYPE_CERTIFY_REQUEST ){
                    $result['child_request_certify'][] = $info['item_id'];
                    break;
                }
            }
            foreach ( $info['child_items'] as $child_info ){
                if( $child_info['lock_type']
                    == XOONIPS_LOCK_TYPE_TRANSFER_REQUEST ){
                    $result['child_request_transfer'][] = $info['item_id'];
                    break;
                }
            }
        }
        return $result;
    }
    
    /**
     * return true if all given items are transferrable.
     * @access protected
     * @param array $from_uid uid of transferer
     * @param array $item_ids 
     * @return bool 
     */
    function is_all_transferrable_items( $from_uid, $item_ids ){
        $infos = xoonips_transfer_get_transferrable_item_information(
            $from_uid, $item_ids );
        foreach ( $infos as $info ){
            if ( ! $info['transfer_enable'] ){
                return false;
            }
        }
        return true;
    }
    
    /**
     * 
     * @access protected
     * @param int $uid uid of transferee
     * @param int $uid_transferer (optional)uid of transferer
     * @return true if uid is valid transferee user(except transferer)
     */
    function is_valid_transferee_user( $uid, $uid_transferer=null ){
        if( !is_null( $uid_transferer ) ){
            if( $uid == $uid_transferer ){
                return false;
            }
        }
        
        $handler =& xoonips_getormcompohandler( 'xoonips', 'user' );
        return $handler->isCertifiedUser( $uid );
    }
    
    /**
     * all of items can be readable by specified user
     * @param array $item_ids array of integer of item id to read
     * @param int $uid user id to read item
     * @return bool true if all of items are readable
     */
    function is_readable_all_items( $item_ids, $uid ){
        $handler =& xoonips_getormcompohandler( 'xoonips', 'item' );
        if( !is_array( $item_ids ) ) return false;
        
        foreach( $item_ids as $id ){
            if( !$handler -> getPerm( $id, $uid, 'read' ) ){
                return false;
            }
        }
        return true;
    }
}
