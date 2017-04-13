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

require_once __DIR__.'/transfer.class.php';

/**
 * HTML view to list items to transfer and show select item menu for a user.
 */
class XooNIpsViewTransferUserItemList extends XooNIpsViewTransfer
{
    /**
     * create view.
     *
     * @param arrray $params associative array of view
     *                       - $params['to_uid']: integer user id transfer to
     *                       - $params['items_to_transfer']: array of item information id to transfer
     *                       - $params['items_to_transfer'][]['transfer_enable']:
     *                       boolean true if it can be transfer.
     *                       - $params['items_to_transfer'][]['transfer_explanation']:
     *                       string explanation why it can't be transfer.
     *                       - $params['items_to_transfer'][]['item_id']: integer item id
     *                       - $params['items_to_transfer'][]['lock_type']:
     *                       integer item lock type(see XooNIpsItemLock)
     *                       - $params['items_to_transfer'][]['child_items']:
     *                       array of child item information
     *                       - $params['items_to_transfer'][]['child_items'][]['item_id']:
     *                       integer child item id
     *                       - $params['items_to_transfer'][]['child_items'][]['lock_type']:
     *                       integer item lock type(see XooNIpsItemLock)
     *                       - $params['to_user_options']: associative array
     *                       - $params['to_user_options'][(uid:integer)]:
     *                       string uname(login name) transfer to
     *                       - $params['transfer_enable']:
     *                       boolean true if all items can be transfered.
     */
    public function __construct($params)
    {
        parent::__construct($params);
    }

    public function render()
    {
        global $xoopsOption, $xoopsConfig, $xoopsUser, $xoopsUserIsAdmin, $xoopsLogger, $xoopsTpl;

        //create handler to include item_type.class.php
        $item_lock_handler = &xoonips_getormhandler('xoonips', 'item_lock');

        $xoopsOption['template_main'] = 'xoonips_transfer_user_item_list.html';
        include XOOPS_ROOT_PATH.'/header.php';
        $this->setXooNIpsStyleSheet($xoopsTpl);

        $xoopsTpl->assign('transfer_items', $this->get_transfer_item_template_vars());
        foreach ($this->_params as $key => $val) {
            $xoopsTpl->assign($key, $val);
        }
        include XOOPS_ROOT_PATH.'/footer.php';
    }

    public function get_transfer_item_template_vars()
    {
        $result = array();

        foreach ($this->_params['items_to_transfer'] as $item) {
            $item_vars = $this->get_item_template_vars($item['item_id']);
            $item_vars['transfer_enable'] = $item['transfer_enable'];
            $item_vars['lock_type'] = $item['lock_type'];
            $item_vars['have_another_parent'] = $item['have_another_parent'];
            $item_vars['child_items'] = array();
            foreach ($item['child_items'] as $child_item) {
                $child = $this->get_item_template_vars($child_item['item_id']);
                $child['lock_type'] = $child_item['lock_type'];
                $item_vars['child_items'][] = $child;
            }
            $result[] = $item_vars;
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
}
