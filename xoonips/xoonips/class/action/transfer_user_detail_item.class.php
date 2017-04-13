<?php

// $Revision: 1.1.2.13 $
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

class XooNIpsActionTransferUserDetailItem extends XooNIpsActionTransfer
{
    public function XooNIpsActionTransferUserDetailItem()
    {
        parent::XooNIpsAction();
    }

    public function _get_logic_name()
    {
        return null;
    }

    public function _get_view_name()
    {
        return 'transfer_user_requested_item_detail';
    }

    public function preAction()
    {
        xoonips_deny_guest_access();
        xoonips_allow_get_method();
    }

    public function doAction()
    {
        global $xoopsUser;

        // get item_id
        $item_id = $this->_formdata->getValue('get', 'item_id', 'i', false);

        // permission check
        $item_compo_handler = &xoonips_getormcompohandler('xoonips', 'item');
        if (!$item_compo_handler->getPerm($item_id, $xoopsUser->getVar('uid'), 'read')) {
            $this->show_no_permission_error_page();
        }

        // get item_info_compo of $item_id
        $item_basic_handler = &xoonips_getormhandler('xoonips', 'item_basic');
        $item_basic = $item_basic_handler->get($item_id);

        $item_type_handler = &xoonips_getormhandler('xoonips', 'item_type');
        $item_type = $item_type_handler->get($item_basic->get('item_type_id'));

        $info_compo_handler = &xoonips_getormcompohandler($item_type->get('name'), 'item');
        $info_compo = $info_compo_handler->get($item_id);

        // read language file of item
        $langman = &xoonips_getutility('languagemanager');
        $langman->read('main.php', $item_type->get('name'));

        // set params
        $this->_view_params['template_file_name'] = $info_compo_handler->getTemplateFileName(XOONIPS_TEMPLATE_TYPE_TRANSFER_ITEM_DETAIL);

        $this->_view_params['template_vars'] = $info_compo_handler->getTemplateVar(XOONIPS_TEMPLATE_TYPE_TRANSFER_ITEM_DETAIL, $item_id, $xoopsUser->getVar('uid'));
    }

    /**
     * show error message and close button.
     */
    public function show_no_permission_error_page()
    {
        xoops_header();
        echo  _MD_XOONIPS_ITEM_FORBIDDEN
            .'<br /><input type="button"'
            .' onclick="javascript:window.close();" value="'._CLOSE.'" />';
        xoops_footer();
        exit;
    }
}
