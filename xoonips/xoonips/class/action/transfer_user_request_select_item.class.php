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
require_once dirname(dirname(__DIR__)).'/include/extra_param.inc.php';

class XooNIpsActionTransferUserRequestSelectItem extends XooNIpsActionTransfer
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
        return 'transfer_user_item_list';
    }

    public function preAction()
    {
        global $xoopsUser;

        xoonips_deny_guest_access();
        xoonips_allow_post_method();

        $extra_params = xoonips_extra_param_restore();
        xoonips_validate_request($this->is_valid_transferee_user(@$extra_params['to_uid']));

        if (count($this->get_selected()) > 0 || count($this->get_selected_hidden()) > 0) {
            xoonips_validate_request($this->is_readable_all_items(array_merge($this->get_selected(), $this->get_selected_hidden()), $xoopsUser->getVar('uid')));
        }
    }

    public function doAction()
    {
        global $xoopsUser;

        $extra_params = xoonips_extra_param_restore();
        if (array_key_exists('to_uid', $extra_params)) {
            $this->_view_params['to_uid'] = $extra_params['to_uid'];
        }

        $item_ids_to_transfer = array();
        if ('add_selected_item' == $this->_formdata->getValue('post', 'op', 's', false)) {
            $item_ids_to_transfer = array_merge($this->get_selected(), $this->get_selected_hidden());
        } else {
            $item_ids_to_transfer = $this->_formdata->getValueArray('post', 'selected_original', 'i', false);
        }

        $item_ids_to_transfer = $this->sort_item_ids_by_title($item_ids_to_transfer);

        $this->_view_params['items_to_transfer'] = xoonips_transfer_get_transferrable_item_information($xoopsUser->getVar('uid'), $item_ids_to_transfer);

        $this->_view_params['to_user_options'] = xoonips_transfer_get_users_for_dropdown($xoopsUser->getVar('uid'));

        $this->_view_params['transfer_enable'] = $this->is_all_transferrable_items($xoopsUser->getVar('uid'), $item_ids_to_transfer);
    }

    public function get_selected()
    {
        $result = $this->_formdata->getValueArray('post', 'selected', 'i', false);

        return is_array($result) ? $result : array();
    }

    public function get_selected_hidden()
    {
        $result = $this->_formdata->getValueArray('post', 'selected_hidden', 'i', false);

        return is_array($result) ? $result : array();
    }
}
