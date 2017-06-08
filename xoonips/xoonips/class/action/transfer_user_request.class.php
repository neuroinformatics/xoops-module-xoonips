<?php

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
require_once dirname(__DIR__).'/base/gtickets.php';

class XooNIpsActionTransferUserRequest extends XooNIpsActionTransfer
{
    public function __construct()
    {
        parent::__construct();
    }

    public function _get_logic_name()
    {
        return 'TransferUserRequest';
    }

    public function _get_view_name()
    {
        return null;
    }

    public function preAction()
    {
        xoonips_deny_guest_access();
        xoonips_allow_post_method();

        if (!$GLOBALS['xoopsGTicket']->check(true, 'xoonips_transfer_user_item_confirm', false)) {
            die('ticket error');
        }

        global $xoopsUser;

        $all_of_item_ids_to_transfer = array_unique(array_merge($this->get_item_ids_to_transfer(), $this->get_child_item_ids_to_transfer()));

        if (!xoonips_transfer_is_transferrable($xoopsUser->getVar('uid'), $this->get_to_uid(), $all_of_item_ids_to_transfer)) {
            redirect_header(XOOPS_URL.'/modules/xoonips/transfer_item.php', 3, _MD_XOONIPS_TRANSFER_USER_REQUEST_ERROR);
        }

        if (!$this->is_user_in_group_of_items($this->get_to_uid(), $all_of_item_ids_to_transfer)) {
            redirect_header(XOOPS_URL.'/modules/xoonips/transfer_item.php', 3, _MD_XOONIPS_TRANSFER_USER_REQUEST_ERROR_BAD_SUBSCRIBE_GROUP);
        }

        $this->_params = array($all_of_item_ids_to_transfer, $xoopsUser->getVar('uid'), $this->get_to_uid());
    }

    public function postAction()
    {
        xoonips_notification_user_item_transfer_request($this->get_to_uid());
        if ($this->_response->getResult()) {
            redirect_header(XOOPS_URL.'/modules/xoonips/transfer_item.php', 3, _MD_XOONIPS_TRANSFER_USER_REQUEST_COMPLETE);
        } else {
            redirect_header(XOOPS_URL.'/modules/xoonips/transfer_item.php', 3, _MD_XOONIPS_TRANSFER_USER_REQUEST_ERROR);
        }
    }

    public function get_to_uid()
    {
        return $this->_formdata->getValue('post', 'to_uid', 'i', false);
    }

    public function get_item_ids_to_transfer()
    {
        $result = $this->_formdata->getValueArray('post', 'item_ids_to_transfer', 'i', false);

        return is_array($result) ? $result : array();
    }

    public function get_child_item_ids_to_transfer()
    {
        $result = $this->_formdata->getValueArray('post', 'child_item_ids_to_transfer', 'i', false);

        return is_array($result) ? $result : array();
    }
}
