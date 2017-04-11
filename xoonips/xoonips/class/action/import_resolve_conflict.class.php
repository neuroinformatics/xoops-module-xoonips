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

include_once dirname(__DIR__).'/base/action.class.php';
include_once dirname(__DIR__).'/base/logicfactory.class.php';
require_once dirname(__DIR__).'/base/gtickets.php';

class XooNIpsActionImportResolveConflict extends XooNIpsAction
{
    public $_view_name = null;

    public $_collection = null;

    public function XooNIpsActionImportResolveConflict()
    {
        parent::XooNIpsAction();
    }

    public function _get_logic_name()
    {
        return 'importCheckImport';
    }

    public function _get_view_name()
    {
        return $this->_view_name;
    }

    public function preAction()
    {
        global $xoopsUser;

        xoonips_allow_post_method();
        xoonips_deny_guest_access();

        $page = $this->_formdata->getValue('post', 'page', 'i', false);
        xoonips_validate_request($page > 0);

        $resolve_flag = $this->_formdata->getValue('post', 'resolve_conflict_flag', 'i', false);
        xoonips_validate_request(1 == $resolve_flag || 0 == $resolve_flag);

        $itemtype_handler = &xoonips_getormhandler('xoonips', 'item_type');
        foreach ($itemtype_handler->getObjects() as $itemtype) {
            if ('xoonips_index' == $itemtype->get('name')) {
                continue;
            }
            $handler = &xoonips_gethandler($itemtype->get('name'),
                                            'import_item');
            $handler->create();
        }

        $sess_hander = &xoonips_getormhandler('xoonips', 'session');
        $sess = &$sess_hander->get(session_id());
        $session = unserialize($sess->get('sess_data'));
        $this->_collection = unserialize(
            gzuncompress(base64_decode($session['xoonips_import_items'])));
        xoonips_validate_request($this->_collection);

        $this->_collection->setImportAsNewOption(
            !is_null($this->_formdata->getValue('post', 'import_as_new', 'i', false)));
        $items = &$this->_collection->getItems();
        foreach (array_keys($items) as $key) {
            if (in_array($items[$key]->getPseudoId(),
                          $this->getUpdatablePseudoId())) {
                // set update flag of displayed item
                $items[$key]->setUpdateFlag(
                    in_array($items[$key]->getPseudoId(),
                              $this->getUpdatePseudoId()));
            }
        }

        $this->_params[] = $this->_collection->getItems();
        $this->_params[] = $xoopsUser->getVar('uid');
        $this->_params[] = $this->_collection->getImportAsNewOption();
    }

    public function doAction()
    {
        $resolve_flag = $this->_formdata->getValue('post', 'resolve_conflict_flag', 'i', false);
        if ($resolve_flag) {
            parent::doAction();
        }
    }

    public function postAction()
    {
        $success = &$this->_response->getSuccess();

        $sess_handler = &xoonips_getormhandler('xoonips', 'session');
        $sess = &$sess_handler->get(session_id());
        $session = unserialize($sess->get('sess_data'));
        $session['xoonips_import_items']
            = base64_encode(gzcompress(serialize($this->_collection)));
        $sess->set('sess_data', serialize($session));
        $sess_handler->insert($sess);

        if ($this->_formdata->getValue('post', 'resolve_conflict_flag', 'i', false)
            && !$success['private_item_number_limit_over']
            && !$success['private_item_storage_limit_over']) {
            $this->_view_params['ticket_html']
                = $GLOBALS['xoopsGTicket']->getTicketHtml(__LINE__, 600, 'import');
            $this->_view_name = 'import_confirm';
        } else {//for page navigation or number and storage limits over
            $this->_view_params['import_as_new_flag']
                = $this->_collection->getImportAsNewOption();
            $this->_view_params['page'] = $page;
            $this->_view_params['import_items']
                = $this->_collection->getItems();
            $this->_view_params['private_item_number_limit_over']
                = $success['private_item_number_limit_over'];
            $this->_view_params['private_item_storage_limit_over']
                = $success['private_item_storage_limit_over'];
            $this->_view_name = 'import_conflict';
        }
    }

    /**
     * get array of updatable_pseudo_id from POST form data.
     *
     * @return array updatable pseudo ids or empty array
     */
    public function getUpdatablePseudoId()
    {
        $updatable_pseudo_id = $this->_formdata->getValueArray('post', 'updatable_pseudo_id', 'i', false);
        if (is_null($updatable_pseudo_id)
            || !is_array($updatable_pseudo_id)) {
            return array();
        }

        return $updatable_pseudo_id;
    }

    /**
     * get array of update_pseudo_id from POST form data.
     *
     * @return array update pseudo ids or empty array
     */
    public function getUpdatePseudoId()
    {
        $update_pseudo_id = $this->_formdata->getValueArray('post', 'update_pseudo_id', 'i', false);
        if (is_null($update_pseudo_id)
            || !is_array($update_pseudo_id)) {
            return array();
        }

        return $update_pseudo_id;
    }
}
