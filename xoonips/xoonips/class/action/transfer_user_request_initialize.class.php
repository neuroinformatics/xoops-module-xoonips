<?php

// $Revision: 1.1.2.11 $
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

class XooNIpsActionTransferUserRequestInitialize extends XooNIpsActionTransfer
{
    public function XooNIpsActionTransferUserRequestInitialize()
    {
        parent::XooNIpsAction();
    }

    public function _get_logic_name()
    {
        return null;
    }

    public function _get_view_name()
    {
        return 'transfer_user_item_list';
    }

    public function preAction()
    {
        global $xoopsUser;

        xoonips_deny_guest_access();
        xoonips_allow_both_method();

        if (!is_null($this->_formdata->getValue('post', 'to_uid', 'i', false))) {
            xoonips_validate_request(
                $this->is_valid_transferee_user(
                    $this->_formdata->getValue('post', 'to_uid', 'i', false)));
        }

        if (!is_null($this->_formdata->getValueArray('post', 'item_ids_to_transfer', 'i', false))) {
            xoonips_validate_request(
                $this->is_readable_all_items(
                    $this->_formdata->getValueArray('post', 'item_ids_to_transfer', 'i', false),
                    $xoopsUser->getVar('uid')));
        }
    }

    public function doAction()
    {
        global $xoopsUser;

        $item_type_handler = &xoonips_getormhandler('xoonips', 'item_type');
        if ($item_type_handler->getCount() <= 1) {
            redirect_header(
                XOOPS_URL.'/modules/xoonips/',
                3, _MD_XOONIPS_TRANSFER_USER_REQUEST_ERROR_NO_ITEMTYPE
                );
        }

        $this->_view_params['to_uid']
            = $this->_formdata->getValue('post', 'to_uid', 'i', false);

        $item_ids_to_transfer
            = $this->_formdata->getValueArray('post', 'item_ids_to_transfer', 'i', false);

        $this->_view_params['items_to_transfer']
            = xoonips_transfer_get_transferrable_item_information(
                $xoopsUser->getVar('uid'),
                $item_ids_to_transfer);

        $this->_view_params['to_user_options']
            = xoonips_transfer_get_users_for_dropdown(
                $xoopsUser->getVar('uid'));

        if (empty($this->_view_params['to_user_options'])) {
            redirect_header(
                XOOPS_URL
                .'/modules/xoonips/',
                3, _MD_XOONIPS_TRANSFER_USER_REQUEST_ERROR_NO_TRANSFEREE_USER
                );
        }

        $this->_view_params['transfer_enable']
            = $this->is_all_transferrable_items(
                $xoopsUser->getVar('uid'),
                $item_ids_to_transfer);
    }
}
