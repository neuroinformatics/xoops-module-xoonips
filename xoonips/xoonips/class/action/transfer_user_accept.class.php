<?php

// $Revision: 1.1.2.15 $
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

include_once 'transfer.class.php';
include_once dirname(dirname(__DIR__))
    .'/include/transfer.inc.php';
include_once dirname(__DIR__).'/base/gtickets.php';
include_once dirname(dirname(__DIR__))
    .'/include/notification.inc.php';

class XooNIpsActionTransferUserAccept extends XooNIpsActionTransfer
{
    /**
     * map of owner(transferer) uid and item_ids.
     */
    public $_uid_item_ids_map = null;

    /**
     * map of uid to notify, transferer, transferee and item id array.
     */
    public $_notify_uid_transferer_transferee_item_ids_map = null;

    public function XooNIpsActionTransferUserAccept()
    {
        parent::XooNIpsAction();
    }

    public function _get_logic_name()
    {
        return 'TransferUserAccept';
    }

    public function _get_view_name()
    {
        return null;
    }

    public function preAction()
    {
        xoonips_deny_guest_access();
        xoonips_allow_post_method();

        if (!$GLOBALS['xoopsGTicket']->check(true, 'xoonips_transfer_user_requested_item_confirm', false)) {
            die('ticket error');
        }

        global $xoopsUser;

        $item_ids = $this->get_item_ids_to_transfer();
        if (empty($item_ids)) {
            redirect_header(
                XOOPS_URL.'/',
                3,
                _MD_XOONIPS_TRANSFER_USER_ACCEPT_ERROR_NO_ITEM
                );
        }

        $result = array();
        foreach ($this->get_item_ids_to_transfer() as $item_id) {
            foreach ($this->get_notify_uids($item_id) as $uid) {
                $result[$uid][$this->get_transferer_uid($item_id)][$this->get_transferee_uid($item_id)][] = $item_id;
            }
        }
        $this->_notify_uid_transferer_transferee_item_ids_map = $result;

        $this->_uid_item_ids_map = $this->getMapOfUidTOItemId(
            $this->get_item_ids_to_transfer());

        $item_ids_to_transfer
            = $this->get_item_ids_to_transfer();

        if ($this->get_limit_check_result(
            $xoopsUser->getVar('uid'),
            $this->get_item_ids_to_transfer())) {
            redirect_header(
                XOOPS_URL
                .'/modules/xoonips/transfer_item.php'
                .'?action=list_item',
                3,
                _MD_XOONIPS_TRANSFER_USER_ACCEPT_ERROR_NUMBER_OR_STORAGE_EXCEED
                );
        }

        if (!$this->is_user_in_group_of_items(
            $xoopsUser->getVar('uid'),
            $this->get_item_ids_to_transfer())) {
            redirect_header(
                XOOPS_URL.'/', 3,
                _MD_XOONIPS_TRANSFER_USER_ACCEPT_ERROR_BAD_SUBSCRIBE_GROUP);
        }

        $this->_params[] = $this->get_item_ids_to_transfer();
        $this->_params[] = $xoopsUser->getVar('uid');
        $this->_params[] = $this->_formdata->getValue('post', 'index_id', 'i', false);
    }

    public function postAction()
    {
        global $xoopsUser;

        if ($this->_response->getResult()) {
            $this->notify_transfer_accepted();

            redirect_header(XOOPS_URL.'/',
                             3, _MD_XOONIPS_TRANSFER_USER_ACCEPT_COMPLETE);
        } else {
            redirect_header(XOOPS_URL
                             .'/modules/xoonips/transfer_item.php'
                             .'?action=list_item',
                             3, _MD_XOONIPS_TRANSFER_USER_ACCEPT_ERROR);
        }
    }

    public function get_item_ids_to_transfer()
    {
        $result = $this->_formdata->getValueArray('post', 'item_ids_to_transfer', 'i', false);
        if (is_null($result)) {
            return array();
        }

        return $result;
    }

    public function notify_transfer_accepted()
    {
        global $xoopsUser;

        // notify to moderator and group admin
        foreach ($this->_notify_uid_transferer_transferee_item_ids_map
                 as $uid_to_notify => $value) {
            foreach ($value as $transferer_uid => $transferee_and_item_ids) {
                foreach ($transferee_and_item_ids
                         as $transferee_uid => $item_ids) {
                    xoonips_notification_item_transfer(
                        $transferer_uid, $transferee_uid, $item_ids,
                        array($uid_to_notify));
                }
            }
        }

        // notify to transferer
        foreach ($this->_uid_item_ids_map as $transferer_uid => $item_ids) {
            xoonips_notification_user_item_transfer_accepted(
                $transferer_uid,
                $xoopsUser->getVar('uid'),//transferee user id
                $item_ids);
        }
    }

    public function get_transferee_uid($item_id)
    {
        $handler = &xoonips_getormhandler('xoonips', 'transfer_request');
        $request = &$handler->getObjects(new Criteria('item_id',
                                                         $item_id));

        if (false === $request) {
            return false;
        }
        if (count($request) == 0) {
            return false;
        }

        return $request[0]->get('to_uid');
    }

    public function get_transferer_uid($item_id)
    {
        $handler = &xoonips_getormhandler('xoonips', 'item_basic');
        $basic = &$handler->getObjects(new Criteria('item_id',
                                                       $item_id));

        if (!$basic) {
            return false;
        }
        if (count($basic) == 0) {
            return false;
        }

        return $basic[0]->get('uid');
    }

    public function get_notify_uids($item_id)
    {
        global $xoopsDB;

        $result = array();
        $index_item_link_handler = &xoonips_getormhandler('xoonips',
                                                           'index_item_link');

        $links = $index_item_link_handler->getByItemid($item_id,
                                                          array(OL_PUBLIC));
        if (is_array($links) && count($links) > 0) {
            $result = xoonips_notification_get_moderator_uids();
        }

        $links = $index_item_link_handler->getByItemid(
             $item_id, array(OL_GROUP_ONLY));
        if (!is_array($links) && count($links) == 0) {
            return $result;
        }

        foreach ($links as $link) {
            $index_handler = &xoonips_getormhandler('xoonips', 'index');
            $join = new XooNIpsJoinCriteria('xoonips_groups_users_link', 'gid', 'gid', 'INNER', 'tgul');
            $criteria = new CriteriaCompo(
                new Criteria('index_id', $link->get('index_id')));
            $criteria->add(new Criteria('is_admin', 1));
            $rows = &$index_handler->getObjects($criteria, false, 'tgul.uid',
                                                  true, $join);
            foreach ($rows as $row) {
                $result[] = $row->get('uid');
            }
        }

        return $result;
    }
}
