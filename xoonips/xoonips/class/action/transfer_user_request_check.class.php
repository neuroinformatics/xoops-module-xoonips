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

class XooNIpsActionTransferUserRequestCheck extends XooNIpsActionTransfer
{
    public function XooNIpsActionTransferUserRequestCheck()
    {
        parent::XooNIpsAction();
    }

    public function _get_logic_name()
    {
        return null;
    }

    public function _get_view_name()
    {
        return 'transfer_user_item_confirm';
    }

    public function preAction()
    {
        global $xoopsUser;

        xoonips_deny_guest_access();
        xoonips_allow_post_method();

        xoonips_validate_request(
            $this->is_valid_transferee_user(
                $this->_formdata->getValue('post', 'to_uid', 'i', false)));

        xoonips_validate_request(
            $this->is_readable_all_items(
                $this->get_item_ids_to_transfer(),
                $xoopsUser->getVar('uid')));
    }

    public function doAction()
    {
        global $xoopsUser;

        $this->_view_params['to_uid'] = $this->_formdata->getValue('post', 'to_uid', 'i', false);

        $items = xoonips_transfer_get_transferrable_item_information(
            $xoopsUser->getVar('uid'),
            $this->get_item_ids_to_transfer());
        $this->redirect_if_can_not_transfer_items($items);

        $this->_view_params['item_ids_to_transfer']
            = $this->sort_item_ids_by_title(
                $this->get_item_ids_to_transfer());

        $this->_view_params['child_item_ids_to_transfer']
            = $this->get_child_item_ids_to_transfer(
                $xoopsUser->getVar('uid'),
                $this->get_item_ids_to_transfer());

        $this->_view_params['is_user_in_groups'] =
            $this->is_user_in_group_of_items(
                $this->_view_params['to_uid'],
                $this->get_item_ids_to_transfer());

        $this->_view_params['gids_to_subscribe'] =
            $this->get_gids_to_subscribe(
                $this->_view_params['to_uid'],
                $this->get_item_ids_to_transfer());
    }

    public function get_item_ids_to_transfer()
    {
        return $this->_formdata->getValueArray('post', 'item_ids_to_transfer', 'i', false);
    }

    public function redirect_if_can_not_transfer_items($items)
    {
        foreach ($items as $item) {
            if ($item['transfer_enable']) {
                continue;
            }
            redirect_header(XOOPS_URL
                             .'/modules/xoonips/transfer_item.php',
                             3,
                             _MD_XOONIPS_TRANSFER_USER_CAN_NOT_TRANSFER_ITEM);
        }
    }

    public function get_child_item_ids_to_transfer($from_uid, $item_id_to_transfer)
    {
        $items = xoonips_transfer_get_transferrable_item_information(
            $from_uid, $item_id_to_transfer);
        $result = array();
        foreach ($items as $item) {
            $result[$item['item_id']] = array();
            foreach ($item['child_items'] as $child_item) {
                $result[$item['item_id']][] = $child_item['item_id'];
            }
        }

        return $result;
    }
}
