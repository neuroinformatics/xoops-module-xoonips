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
require_once dirname(__DIR__).'/base/gtickets.php';

class XooNIpsActionTransferUserReject extends XooNIpsActionTransfer
{
    public function __construct()
    {
        parent::__construct();
    }

    public function _get_logic_name()
    {
        return 'TransferUserReject';
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

        $item_ids = $this->get_item_ids_to_transfer();
        if (empty($item_ids)) {
            redirect_header(XOOPS_URL.'/', 3, _MD_XOONIPS_TRANSFER_USER_REJECT_ERROR_NO_ITEM);
        }

        $this->_params[] = $this->_formdata->getValueArray('post', 'item_ids_to_transfer', 'i', false);
    }

    public function postAction()
    {
        if ($this->_response->getResult()) {
            $this->notify_transfer_rejected();

            redirect_header(XOOPS_URL.'/', 3, _MD_XOONIPS_TRANSFER_USER_REJECT_COMPLETE);
        } else {
            redirect_header(XOOPS_URL.'/modules/xoonips/transfer_item.php'.'?action=list_item', 3, _MD_XOONIPS_TRANSFER_USER_REJECT_ERROR);
        }
    }

    public function notify_transfer_rejected()
    {
        global $xoopsUser;

        foreach ($this->getMapOfUidTOItemId($this->get_item_ids_to_transfer()) as $transferer_uid => $item_ids) {
            xoonips_notification_user_item_transfer_rejected($transferer_uid, $xoopsUser->getVar('uid'), $item_ids);
        }
    }

    public function get_item_ids_to_transfer()
    {
        return $this->_formdata->getValueArray('post', 'item_ids_to_transfer', 'i', false);
    }
}
