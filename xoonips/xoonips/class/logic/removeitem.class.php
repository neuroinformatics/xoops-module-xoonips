<?php

// $Revision: 1.1.4.1.2.10 $
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

include_once XOOPS_ROOT_PATH.'/modules/xoonips/class/base/logic.class.php';
include_once XOOPS_ROOT_PATH.'/modules/xoonips/class/base/transaction.class.php';
include_once XOOPS_ROOT_PATH.'/modules/xoonips/class/xoonips_item_event_dispatcher.class.php';

/**
 * subclass of XooNIpsLogic(removeItem).
 */
class XooNIpsLogicRemoveItem extends XooNIpsLogic
{
    /**
     * execute removeItem.
     *
     * @param[in]  $vars[0] session ID
     * @param[in]  $vars[1] item identifier
     * @param[in]  $vars[2] ext_id or item_id
     * @param[out] $response->result true:success, false:failed
     * @param[out] $response->error  error information
     * @param[out] $response->success item id of deleted item
     */
    public function execute(&$vars, &$response)
    {
        /*
        check permission
        insert event DELETE_ITEM
        delete item
        delete files
        delete search_text

        XooNIpsItemCompoHandler::delete() does:
        1. delete item from index keywords
        3. delete item
        4. delete title
        5. delete keyword
        6. delete item from related_to
        7. delete item changelog

        update item_status
        */
        // parameter check
        $error = &$response->getError();
        if (count($vars) > 3) {
            $error->add(XNPERR_EXTRA_PARAM);
        } elseif (count($vars) < 3) {
            $error->add(XNPERR_MISSING_PARAM);
        } else {
            if (isset($vars[0]) && strlen($vars[0]) > 32) {
                $error->add(XNPERR_INVALID_PARAM, 'too long parameter 1');
            }
            if ($vars[2] != 'item_id' && $vars[2] != 'ext_id') {
                $error->add(XNPERR_INVALID_PARAM, 'invalid parameter 3');
            }
            if ($vars[2] == 'item_id' && !is_int($vars[1]) && !ctype_digit($vars[1])) {
                $error->add(XNPERR_INVALID_PARAM, 'not integer parameter 2');
            }
            if ($vars[2] == 'item_id' && strlen($vars[1]) > 10) {
                $error->add(XNPERR_INVALID_PARAM, 'too long parameter 2');
            }
        }
        if ($error->get(0)) {
            // return if parameter error
            $response->setResult(false);

            return;
        } else {
            $sessionid = $vars[0];
            $id = $vars[1];
            $id_type = $vars[2];
        }
        list($result, $uid, $session) = $this->restoreSession($sessionid);
        if (!$result) {
            $response->setResult(false);
            $error->add(XNPERR_INVALID_SESSION);

            return false;
        }
        // get item and item_id
        $item_handler = &xoonips_getormcompohandler('xoonips', 'item');
        $item_basic_handler = &xoonips_getormhandler('xoonips', 'item_basic');
        if ($id_type == 'item_id') {
            $item = $item_handler->get($id);
        } elseif ($id_type == 'ext_id') {
            if (strlen($id) == 0) {
                $response->setResult(false);
                $error->add(XNPERR_INVALID_PARAM, 'ext_id is empty');

                return false;
            } else {
                $basics = &$item_basic_handler->getObjects(new Criteria('doi', addslashes($id)));
                if (false === $basics) {
                    $response->setResult(false);
                    $error->add(XNPERR_SERVER_ERROR, 'cannot get basic information');

                    return false;
                } elseif (count($basics) >= 2) {
                    $response->setResult(false);
                    $error->add(XNPERR_SERVER_ERROR, 'ext_id is duplicated');

                    return false;
                } elseif (count($basics) == 1) {
                    $item = $item_handler->get($basics[0]->get('item_id'));
                } else {
                    $item = false;
                }
            }
        } else {
            $response->setResult(false);
            $error->add(XNPERR_INVALID_PARAM, "bad id_type($id_type)");

            return false;
        }
        if (!$item) {
            $response->setResult(false);
            $error->add(XNPERR_NOT_FOUND);

            return false;
        }
        $basic = $item->getVar('basic');
        $item_id = $basic->get('item_id');
        // can delete?
        if (!$item_handler->getPerm($item_id, $uid, 'delete')) {
            $item_lock_handler = &xoonips_getormhandler('xoonips', 'item_lock');
            $response->setResult(false);
            if ($item_lock_handler->isLocked($item_id)) {
                $error->add(XNPERR_ACCESS_FORBIDDEN,
                    'cannot remove item because item is '.
                    $this->getLockTypeString(
                        $item_lock_handler->getLockType($item_id)));
            } else {
                $error->add(XNPERR_ACCESS_FORBIDDEN);
            }

            return false;
        }
        // item -> detail_item
        $item_type_id = $basic->get('item_type_id');
        $item_type_handler = &xoonips_getormhandler('xoonips', 'item_type');
        $item_type = $item_type_handler->get($item_type_id);
        if (!$item_type) {
            $response->setResult(false);
            $error->add(XNPERR_SERVER_ERROR, "cannot get itemtype(item_type_id=$item_type_id)");

            return false;
        }
        $item_type_name = $item_type->get('name');
        $detail_item_handler = &xoonips_getormcompohandler($item_type_name, 'item');
        if (!$detail_item_handler) {
            $response->setResult(false);
            $error->add(XNPERR_ERROR, "unsupported itemtype($item_type_id)");

            return false;
        }
        $detail_item = $detail_item_handler->get($item_id);
        if (!$detail_item) {
            $response->setResult(false);
            $error->add(XNPERR_SERVER_ERROR, 'cannot get item');

            return false;
        }
        // get files
        $delete_later = array();
        $detail_item_type_handler = &xoonips_getormhandler($item_type_name, 'item_type');
        $detail_item_type = $detail_item_type_handler->get($item_type_id);
        foreach ($detail_item_type->getFileTypeNames() as $field_name) {
            $files = $detail_item->getVar($field_name);
            if ($files) {
                if (!$detail_item_type->getMultiple($field_name)) {
                    $files = array(
                        $files,
                    );
                }
                foreach ($files as $file) {
                    $delete_later[] = $file;
                }
            }
        }

        // start transaction
        $transaction = XooNIpsTransaction::getInstance();
        $transaction->start();
        // delete detail item
        if (!$detail_item_handler->delete($detail_item)) {
            $transaction->rollback();
            $response->setResult(false);
            $error->add(XNPERR_SERVER_ERROR, 'cannot delete item');

            return false;
        }
        // delete item from related_to
        $r_handler = &xoonips_getormhandler('xoonips', 'related_to');
        if (!$r_handler->deleteChildItemIds($item_id)) {
            $transaction->rollback();
            $response->setResult(false);
            $error->add(XNPERR_SERVER_ERROR, 'cannot delete item_id in the related_tos');

            return false;
        }
        // update item_status
        $item_status_handler = &xoonips_getormhandler('xoonips', 'item_status');
        $item_status = $item_status_handler->get($item_id);
        if ($item_status && $item_status->get('is_deleted') == 0) {
            $item_status->setVar('is_deleted', 1, true);
            $item_status->setVar('deleted_timestamp', time(), true);
            if (!$item_status_handler->insert($item_status)) {
                $transaction->rollback();
                $response->setResult(false);
                $error->add(XNPERR_SERVER_ERROR, 'cannot update item_status');

                return false;
            }
        }
        // event log ( delete item )
        $eventlog_handler = &xoonips_getormhandler('xoonips', 'event_log');
        if (!$eventlog_handler->recordDeleteItemEvent($item_id)) {
            $transaction->rollback();
            $response->setResult(false);
            $error->add(XNPERR_SERVER_ERROR, 'cannot insert event');

            return false;
        }
        // commit
        $transaction->commit();
        // unlink files. cannot rollback
        // delete search_text. cannot rollback because search_text contains fulltext column(MyISAM).
        $search_text_handler = &xoonips_getormhandler('xoonips', 'search_text');
        $file_handler = &xoonips_getormhandler('xoonips', 'file');
        foreach ($delete_later as $file) {
            if ($file->isNew()) {
                continue;
            } // no need to delete file becaue this is a created item.
            $file_id = $file->get('file_id');
            if (!$file_handler->deleteFile($file)) {
                $error->add(XNPERR_SERVER_ERROR, "cannot delete file(file_id=$file_id)");
            }
            $search_text = $search_text_handler->get($file_id);
            if ($search_text && !$search_text_handler->delete($search_text)) {
                $error->add(XNPERR_SERVER_ERROR, "cannot delete search_text(file_id=$file_id)");
            }
        }
        if ($error->get()) {
            $response->setResult(false);

            return false;
        }
        // call item event listener
        $this->_include_view_php();
        $dispatcher = &XooNIpsItemEventDispatcher::getInstance();
        $dispatcher->onDelete($item_id);

        $response->setSuccess($item_id);
        $response->setResult(true);

        return true;
    }

    public function _include_view_php()
    {
        $handler = &xoonips_getormhandler('xoonips', 'item_type');
        foreach ($handler->getObjects() as $item_type) {
            $path = XOOPS_ROOT_PATH.'/modules/'.$item_type->get('viewphp');
            if (!file_exists($path)) {
                continue;
            }
            if (!is_file($path)) {
                continue;
            }
            include_once XOOPS_ROOT_PATH.'/modules/'.$item_type->get('viewphp');
        }
    }
}
