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

require_once XOOPS_ROOT_PATH.'/modules/xoonips/class/base/logic.class.php';
require_once XOOPS_ROOT_PATH.'/modules/xoonips/class/base/transaction.class.php';

/**
 * subclass of XooNIpsLogic(removeFile).
 */
class XooNIpsLogicRemoveFile extends XooNIpsLogic
{
    /**
     * execute removeFile.
     *
     * @param[in]  $vars[0] session ID
     * @param[in]  $vars[1] file ID
     * @param[out] $response->result true:success, false:failed
     * @param[out] $response->error  error information
     * @param[out] $response->success file ID of deleted file
     */
    public function execute(&$vars, &$response)
    {
        // parameter check
        $error = &$response->getError();
        if (count($vars) > 2) {
            $error->add(XNPERR_EXTRA_PARAM);
        }
        if (count($vars) < 2) {
            $error->add(XNPERR_MISSING_PARAM);
        }

        if (isset($vars[0]) && strlen($vars[0]) > 32) {
            $error->add(XNPERR_INVALID_PARAM, 'too long parameter 1');
        }
        if (!is_int($vars[1]) && !ctype_digit($vars[1])) {
            $error->add(XNPERR_INVALID_PARAM, 'not integer parameter 2');
        }

        if ($error->get(0)) {
            // return if parameter error
            $response->setResult(false);

            return;
        } else {
            $sessionid = $vars[0];
            $file_id = intval($vars[1]);
        }
        list($result, $uid, $session) = $this->restoreSession($sessionid);
        if (!$result) {
            $response->setResult(false);
            $error->add(XNPERR_INVALID_SESSION);

            return false;
        }
        // file_id -> file, item_id
        $file_handler = &xoonips_getormhandler('xoonips', 'file');
        $file = $file_handler->get($file_id);
        if (!$file) {
            $response->setResult(false);
            $error->add(XNPERR_NOT_FOUND); // not found
            return false;
        }
        $item_id = $file->getVar('item_id');
        if (empty($item_id)) {
            $response->setResult(false);
            $error->add(XNPERR_ACCESS_FORBIDDEN); // maybe belong to other session
            return false;
        }
        // item_id -> basic -> item_type_id -> item_type_name -> item_handler
        $basic_handler = &xoonips_getormhandler('xoonips', 'item_basic');
        $basic = $basic_handler->get($item_id);
        if (!$basic) {
            $response->setResult(false);
            $error->add(XNPERR_SERVER_ERROR, "non-existent item(item_id=$item_id) owns that file");

            return false;
        }
        $item_type_id = $basic->get('item_type_id');
        $item_type_handler = &xoonips_getormhandler('xoonips', 'item_type');
        $item_type = $item_type_handler->get($item_type_id);
        if (!$item_type) {
            $response->setResult(false);
            $error->add(XNPERR_INVALID_PARAM, "bad itemtype(item_type_id=$item_type_id)");

            return false;
        }
        $item_type_name = $item_type->get('name');
        $item_handler = &xoonips_getormcompohandler($item_type_name, 'item');
        if (!$item_handler) {
            $response->setResult(false);
            $error->add(XNPERR_SERVER_ERROR, "cannot get item handler(item_type_id=$item_type_id)");

            return false;
        }
        // can modify?
        if (!$item_handler->getPerm($item_id, $uid, 'write')) {
            $item_lock_handler = &xoonips_getormhandler('xoonips', 'item_lock');
            $response->setResult(false);
            if ($item_lock_handler->isLocked($item_id)) {
                $error->add(XNPERR_ACCESS_FORBIDDEN, 'cannot remove file because item is '.$this->getLockTypeString($item_lock_handler->getLockType($item_id)));
            } else {
                $error->add(XNPERR_ACCESS_FORBIDDEN);
            }

            return false;
        }
        // already deleted?
        if ($file->getVar('is_deleted')) {
            $response->setResult(false);
            $error->add(XNPERR_NOT_FOUND, 'already deleted or replaced');

            return false;
        }
        // file -> file_type_name
        $file_type_handler = &xoonips_getormhandler('xoonips', 'file_type');
        $file_type = $file_type_handler->get($file->getVar('file_type_id'));
        if (!$file_type) {
            $response->setResult(false);
            $error->add(XNPERR_SERVER_ERROR, 'that file has unkonwn file type');

            return false;
        }
        $file_type_name = $file_type->getVar('name');
        // item_type -> detail_item_type
        $detail_item_type_handler = &xoonips_getormhandler($item_type->getVar('name'), 'item_type');
        $detail_item_type = $detail_item_type_handler->get($item_type_id);
        if (!$detail_item_type) {
            $response->setResult(false);
            $error->add(XNPERR_SERVER_ERROR, 'cannot get detail itemtype of that item');

            return false;
        }
        // is that file optional? or required?
        if ($detail_item_type->getRequired($file_type_name)) {
            // is that the last one?
            $criteria = new CriteriaCompo();
            $criteria->add(new Criteria('item_id', $item_id));
            $criteria->add(new Criteria('file_type_id', $file->getVar('file_type_id')));
            $criteria->add(new Criteria('is_deleted', 0));
            $count = $file_handler->getCount($criteria);
            if ($count == 0) {
                $response->setResult(false);
                $error->add(XNPERR_SERVER_ERROR, 'cannot count files');

                return false;
            } elseif ($count == 1) {
                $response->setResult(false);
                $error->add(XNPERR_ERROR, 'that file is not optional and the last one');

                return false;
            }
        }
        // start transaction
        $transaction = XooNIpsTransaction::getInstance();
        $transaction->start();
        // set is_deleted = 1;
        $file->setVar('is_deleted', 1);
        if (!$file_handler->insert($file)) {
            $transaction->rollback();
            $response->setResult(false);
            $error->add(XNPERR_SERVER_ERROR, 'cannot update file table');

            return false;
        }
        // event log ( update item )
        $eventlog_handler = &xoonips_getormhandler('xoonips', 'event_log');
        if (!$eventlog_handler->recordUpdateItemEvent($item_id)) {
            $error->add(XNPERR_SERVER_ERROR, 'cannot insert event');
            $response->setResult(false);

            return false;
        }
        // item insert/update/certify_required/certified event, change certify_state, send notification, update RSS, update item_status.
        $item = $item_handler->get($item_id);
        if (!$this->touchItem($error, $item, $uid)) {
            $transaction->rollback();
            $response->setResult(false);

            return false;
        }
        // delete search_text
        $search_text_handler = &xoonips_getormhandler('xoonips', 'search_text');
        $search_text = $search_text_handler->get($file_id);
        if ($search_text) {
            if (!$search_text_handler->delete($search_text)) {
                $transaction->rollback();
                $response->setResult(false);
                $error->add(XNPERR_SERVER_ERROR, 'cannot remove search text');

                return false;
            }
        }
        // commit
        $transaction->commit();
        // unlink file
        $file_handler->deleteFile($file);
        $response->setSuccess($file_id);
        $response->setResult(true);

        return true;
    }
}
