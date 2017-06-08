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
 * HTML view to make sure transfer items for a transferee user.
 */
class XooNIpsViewTransferUserRequestedItemConfirm extends XooNIpsViewTransfer
{
    /**
     * create view.
     *
     * @param arrray $params associative array of view
     *                       - $params['item_ids_to_transfer']:
     *                       array of integer of item id to transfer
     *                       - $params['index_options']: array of transferee's private index
     *                       - $params['index_options'][]: associative array like below
     *                       - $params['index_options'][]['index_id']: integer index id
     *                       - $params['index_options'][]['depth']:
     *                       integer depth of the index(zero if the index is child of IID_ROOT)
     *                       - $params['index_options'][]['title']: string name of the index
     *                       - $params['index_options'][]['item_count']:
     *                       integer number of items in the index
     *                       - $params['limit_check_result']:
     *                       boolean true if number of item or storage is out of bounds
     */
    public function __construct($params)
    {
        parent::__construct($params);
    }

    public function render()
    {
        global $xoopsOption, $xoopsConfig, $xoopsUser, $xoopsConfig, $xoopsUserIsAdmin, $xoopsLogger, $xoopsTpl;

        $xoopsOption['template_main'] = 'xoonips_transfer_user_requested_item_confirm.html';
        require XOOPS_ROOT_PATH.'/header.php';
        $this->setXooNIpsStyleSheet($xoopsTpl);

        $xoopsTpl->assign('token_hidden', $GLOBALS['xoopsGTicket']->getTicketHtml(__LINE__, 600, 'xoonips_transfer_user_requested_item_confirm'));
        $xoopsTpl->assign('transfer_items', $this->get_transfer_item_template_vars());
        foreach ($this->_params as $key => $val) {
            $xoopsTpl->assign($key, $val);
        }
        require XOOPS_ROOT_PATH.'/footer.php';
    }

    public function get_transfer_item_template_vars()
    {
        $result = array();

        $item_ids = $this->_params['item_ids_to_transfer'];
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
        $textutil = &xoonips_getutility('text');
        $item_handler = &xoonips_getormcompohandler('xoonips', 'item');
        $item_type_handler = &xoonips_getormhandler('xoonips', 'item_type');
        $item = &$item_handler->get($item_id);
        $basic = &$item->getVar('basic');
        $itemtype = &$item_type_handler->get($basic->get('item_type_id'));

        return array(
            'item_id' => $item_id,
            'item_type_name' => $itemtype->getVar('display_name', 's'),
            'owner_uname' => $textutil->html_special_chars($this->get_uname_by_uid($basic->get('uid'))),
            'title' => $this->concatenate_titles($item->getVar('titles')),
        );
    }
}
