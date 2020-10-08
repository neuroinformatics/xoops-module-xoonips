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

class XooNIpsActionTransferAdminInitialize extends XooNIpsActionTransfer
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
        return 'transfer_admin_item_select';
    }

    public function preAction()
    {
        xoonips_allow_both_method();
    }

    public function doAction()
    {
        global $xoopsUser;

        if (1 == count($this->get_from_user_options())) {
            redirect_header(XOOPS_URL.'/modules/xoonips/admin/maintenance.php?page=item', 3, _AM_XOONIPS_MAINTENANCE_TRANSFER_ITEM_ERROR_ONLY_1_USER);
        }

        $this->_view_params['from_uid'] = $this->_formdata->getValue('post', 'from_uid', 'i', false);
        $this->_view_params['from_index_id'] = $this->_formdata->getValue('post', 'from_index_id', 'i', false);
        $this->_view_params['to_uid'] = $this->_formdata->getValue('post', 'to_uid', 'i', false);
        $this->_view_params['to_index_id'] = $this->_formdata->getValue('post', 'to_index_id', 'i', false);
        $tmp = $this->_formdata->getValueArray('post', 'checked_item_ids', 'i', false);
        $this->_view_params['selected_item_ids'] = !is_null($tmp) ? $tmp : array();
        $this->_view_params['page'] = $this->_formdata->getValue('post', 'page', 's', false);
        $this->_view_params['from_user_options'] = $this->get_from_user_options();

        switch ($this->_formdata->getValue('post', 'op', 's', false)) {
        case 'from_uid_changed':
            $this->_view_params['from_index_id'] = $this->get_private_index_id($this->_view_params['from_uid']);
            $this->_view_params['selected_item_ids'] = array();
            $this->_view_params['page'] = 1;
            break;
        case 'from_index_id_changed':
            $this->_view_params['selected_item_ids'] = array();
            $this->_view_params['page'] = 1;
            break;
        case 'to_uid_changed':
            $this->_view_params['to_index_id'] = $this->get_private_index_id($this->_view_params['to_uid']);
            break;
        case 'page_changed':
            break;
        default:
            $uids = array_keys($this->_view_params['from_user_options']);
            $this->_view_params['from_uid'] = $uids[0];
            $this->_view_params['from_index_id'] = $this->get_private_index_id($uids[0]);
            $this->_view_params['to_uid'] = $uids[1];
            $this->_view_params['to_index_id'] = $this->get_private_index_id($uids[1]);
            $this->_view_params['selected_item_ids'] = array();
            $this->_view_params['page'] = 1;
            break;
        }

        $this->_view_params['from_user_options'] = $this->get_from_user_options();

        $this->_view_params['from_index_options'] = $this->getIndexOptionsTemplateVar($this->get_private_index_id($this->_view_params['from_uid']));

        $this->_view_params['to_user_options'] = $this->get_to_user_options();

        $uids = array_keys($this->_view_params['to_user_options']);
        if (!in_array($this->_view_params['to_uid'], $uids)) {
            $this->_view_params['to_uid'] = $uids[0];
            $this->_view_params['to_index_id'] = $this->get_private_index_id($uids[0]);
        }

        $this->_view_params['to_index_options'] = $this->getIndexOptionsTemplateVar($this->get_private_index_id($this->_view_params['to_uid']));

        $this->_view_params['from_index_item_ids'] = $this->sort_item_ids_by_title($this->get_from_index_item_ids());

        $this->_view_params['can_not_transfer_items'] =
            $this->get_untransferrable_reasons_and_items($this->_view_params['from_uid'], $this->get_from_index_item_ids());

        $this->_view_params['child_items'] = array();
        foreach (xoonips_transfer_get_transferrable_item_information($this->_view_params['from_uid'], $this->get_from_index_item_ids()) as $info) {
            $this->_view_params['child_items'][$info['item_id']] = array();
            foreach ($info['child_items'] as $child_item) {
                $this->_view_params['child_items'][$info['item_id']][] = $child_item['item_id'];
            }
        }
    }

    public function get_from_index_item_ids()
    {
        $result = array();

        $index_item_link_handler = &xoonips_getormhandler('xoonips', 'index_item_link');
        $criteria = new Criteria('index_id', $this->_view_params['from_index_id']);
        $links = &$index_item_link_handler->getObjects($criteria);
        foreach ($links as $link) {
            $result[] = $link->get('item_id');
        }

        return $result;
    }

    public function get_from_user_options()
    {
        $u_handler = &xoonips_getormhandler('xoonips', 'xoops_users');
        $join = new XooNIpsJoinCriteria('xoonips_users', 'uid', 'uid', 'INNER', 'xu');
        $criteria = new CriteriaCompo(new Criteria('level', 0, '>'));
        $criteria->add(new Criteria('activate', 1, '=', 'xu'));
        $criteria->setSort('uname');
        $criteria->setOrder('ASC');
        $res = &$u_handler->open($criteria, 'xu.uid, uname', false, $join);
        $result = array();
        while ($obj = &$u_handler->getNext($res)) {
            $uid = $obj->get('uid');
            $result[$uid] = $obj->getVar('uname', 's');
        }
        $u_handler->close($res);

        return $result;
    }

    public function get_to_user_options()
    {
        $u_handler = &xoonips_getormhandler('xoonips', 'xoops_users');
        $join = new XooNIpsJoinCriteria('xoonips_users', 'uid', 'uid', 'INNER', 'xu');
        $criteria = new CriteriaCompo(new Criteria('level', 0, '>'));
        $criteria->add(new Criteria('activate', 1, '=', 'xu'));
        $criteria->add(new Criteria('uid', $this->_view_params['from_uid'], '!=', 'xu'));
        $criteria->setSort('uname');
        $criteria->setOrder('ASC');
        $res = &$u_handler->open($criteria, 'xu.uid, uname', false, $join);
        $result = array();
        while ($obj = &$u_handler->getNext($res)) {
            $uid = $obj->get('uid');
            $result[$uid] = $obj->getVar('uname', 's');
        }
        $u_handler->close($res);

        return $result;
    }

    /**
     * @return int
     */
    public function get_private_index_id($uid)
    {
        $user_hanlder = &xoonips_getormhandler('xoonips', 'users');
        $user = &$user_hanlder->get($uid);
        if (!$user) {
            return false;
        }

        return $user->get('private_index_id');
    }
}
