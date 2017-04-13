<?php

// $Revision: 1.1.2.12 $
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

require_once 'transfer.class.php';
require_once dirname(dirname(__DIR__)).'/include/transfer.inc.php';

class XooNIpsActionTransferUserListItem extends XooNIpsActionTransfer
{
    public function __construct()
    {
        parent::__construct();
    }

    public function _get_logic_name()
    {
        return null;
    }

    public function _get_view_name()
    {
        return 'transfer_user_requested_item_confirm';
    }

    public function preAction()
    {
        xoonips_deny_guest_access();
        xoonips_allow_get_method();
    }

    public function doAction()
    {
        xoonips_deny_guest_access();

        global $xoopsUser;

        $item_ids_to_transfer = $this->get_transfer_request_item_ids($xoopsUser->getVar('uid'));

        if (!$this->is_user_in_group_of_items($xoopsUser->getVar('uid'), $item_ids_to_transfer)) {
            $gnames = $this->get_unsubscribed_group_names($xoopsUser->getVar('uid'), $item_ids_to_transfer);
            $msg = sprintf(_MD_XOONIPS_TRANSFER_USER_LIST_ITEM_ERROR_BAD_SUBSCRIBE_GROUP_NAME, $gnames[0]);
            redirect_header(XOOPS_URL.'/', 3, $msg);
        }

        $this->_view_params['item_ids_to_transfer'] = $this->sort_item_ids_by_title($item_ids_to_transfer);

        $this->_view_params['limit_check_result'] = $this->get_limit_check_result($xoopsUser->getVar('uid'), $item_ids_to_transfer);

        $user_hanlder = &xoonips_getormhandler('xoonips', 'users');
        $user = &$user_hanlder->get($xoopsUser->getVar('uid'));
        $this->_view_params['index_options'] = $this->getIndexOptionsTemplateVar($user->get('private_index_id'));
    }

    /**
     * get array of item id to transfer to user($uid).
     *
     * @param int $uid transferee's uid
     *
     * @return array integer array of item id to be transfered
     */
    public function get_transfer_request_item_ids($uid)
    {
        $transfer_handler = &xoonips_getormhandler('xoonips', 'transfer_request');

        $transfers = &$transfer_handler->getObjects(new Criteria('to_uid', $uid));
        if (false === $transfers) {
            return array();
        }

        $result = array();
        foreach ($transfers as $t) {
            $result[] = $t->get('item_id');
        }

        return $result;
    }

    /**
     * return array of group name that user is not subscribed.
     *
     * @param int   $uid      user id
     * @param array $item_ids array of integer of item id
     *
     * @return array of group name string
     */
    public function get_unsubscribed_group_names($uid, $item_ids)
    {
        $item_group_ids = xoonips_transfer_get_group_ids_of_items($item_ids);

        $xgroup_handler = &xoonips_gethandler('xoonips', 'group');
        $gids = $xgroup_handler->getGroupIds($uid);
        $result = array();
        foreach ($item_group_ids as $gid) {
            if (!in_array($gid, $gids)) {
                $xg_obj = &$xgroup_handler->getGroupObject($gid);
                $result[] = $xg_obj->get('gname');
            }
        }

        return $result;
    }
}
