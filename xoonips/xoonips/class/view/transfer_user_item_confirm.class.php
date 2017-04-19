<?php

// $Revision:$
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

require_once __DIR__.'/transfer.class.php';
require_once dirname(__DIR__).'/base/gtickets.php';

/**
 * HTML view to list items to transfer for a user.
 */
class XooNIpsViewTransferUserItemConfirm extends XooNIpsViewTransfer
{
    /**
     * create view.
     *
     * @param arrray $params associative array of view<br />
     *                       - $params['to_uid']:
     *                       integer user id transfer to<br />
     *                       - $params['item_ids_to_transfer']:
     *                       array of integer of item id to transfer<br />
     *                       - $params['child_item_ids_to_transfer']: associative array<br />
     *                       - $params['child_item_ids_to_transfer'][(item id:integer)]:
     *                       array of child item id<br />
     *                       - $params['is_user_in_groups']:
     *                       boolean true if to_uid is subscribed to group
     *                       of all transfered items.<br />
     *                       - $params['gids_to_subscribe']:
     *                       array group id(s) to subscribe transferee to complete transfer.<br />
     */
    public function __construct($params)
    {
        parent::__construct($params);
    }

    public function render()
    {
        global $xoopsOption, $xoopsConfig, $xoopsUser, $xoopsUserIsAdmin, $xoopsLogger, $xoopsTpl;
        $textutil = &xoonips_getutility('text');

        //create handler to include item_type.class.php
        $item_lock_handler = &xoonips_getormhandler('xoonips', 'item_lock');

        $xoopsOption['template_main'] = 'xoonips_transfer_user_item_confirm.html';
        include XOOPS_ROOT_PATH.'/header.php';
        $this->setXooNIpsStyleSheet($xoopsTpl);

        $xoopsTpl->assign('token_hidden', $GLOBALS['xoopsGTicket']->getTicketHtml(__LINE__, 600, 'xoonips_transfer_user_item_confirm'));
        $xoopsTpl->assign('to_uname', $textutil->html_special_chars($this->get_uname_by_uid($this->_params['to_uid'])));
        $xoopsTpl->assign('transfer_items', $this->get_transfer_item_template_vars());
        $xoopsTpl->assign(
            'not_subscribed_group_message', sprintf(_MD_XOONIPS_TRANSFER_USER_ITEM_CONFIRM_USER_IS_NOT_SUBSCRIBED_TO_GROUPS, $textutil->html_special_chars($this->get_uname_by_uid($this->_params['to_uid'])), $this->get_gname_csv())
        );
        foreach ($this->_params as $key => $val) {
            $xoopsTpl->assign($key, $val);
        }
        include XOOPS_ROOT_PATH.'/footer.php';
    }

    public function get_child_item_ids_to_transfer()
    {
        $item_ids = array();
        foreach (array_values($this->_params['child_item_ids_to_transfer']) as $child_item_ids) {
            $item_ids = array_merge($item_ids, $child_item_ids);
        }

        return array_unique($item_ids);
    }

    public function get_transfer_item_template_vars()
    {
        $result = array();

        $item_ids = array_merge($this->_params['item_ids_to_transfer'], $this->get_child_item_ids_to_transfer());
        sort($item_ids);

        foreach ($item_ids as $item_id) {
            $result[] = $this->get_item_template_vars($item_id);
        }

        return $result;
    }

    /**
     * get array of item for template vars.
     *
     * @param int $item_id
     */
    public function get_item_template_vars($item_id)
    {
        $item_handler = &xoonips_getormcompohandler('xoonips', 'item');
        $item_type_handler = &xoonips_getormhandler('xoonips', 'item_type');
        $item = &$item_handler->get($item_id);
        $basic = &$item->getVar('basic');
        $itemtype = &$item_type_handler->get($basic->get('item_type_id'));

        return array(
            'item_id' => $item_id,
            'item_type_name' => $itemtype->getVar('display_name', 's'),
            'title' => $this->concatenate_titles($item->getVar('titles')),
        );
    }

    public function get_gname_csv()
    {
        $result = array();
        $gids = array();
        foreach ($this->_params['gids_to_subscribe'] as $gid) {
            $gids[] = intval($gid);
        }
        if (count($gids) == 0) {
            return '';
        }
        $xgroup_handler = &xoonips_gethandler('xoonips', 'group');
        $xgroup_objs = &$xgroup_handler->getGroupObjects($gids);
        if (empty($xgroup_objs)) {
            return '';
        }
        foreach ($xgroup_objs as $xgroup_obj) {
            $result[] = $xgroup_obj->getVar('gname', 's');
        }

        return implode(',', $result);
    }
}
