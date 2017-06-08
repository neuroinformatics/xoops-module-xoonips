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

require_once XOOPS_ROOT_PATH.'/modules/xoonips/class/base/logic.class.php';
require_once XOOPS_ROOT_PATH.'/modules/xoonips/class/base/transaction.class.php';
require_once XOOPS_ROOT_PATH.'/modules/xoonips/include/notification.inc.php';

/**
 * subclass of XooNIpsLogic(getFile).
 */
class XooNIpsLogicGetFile extends XooNIpsLogic
{
    /**
     * execute getFile.
     *
     * @param[in]  $vars[0] session ID
     * @param[in]  $vars[1] file ID
     * @param[in]  $vars[2] agreement to license(0:not agreed, 1:agreed)
     * @param[out] $response->result true:success, false:failed
     * @param[out] $response->error  error information
     * @param[out] $response->success XooNIpsFile file information
     */
    public function execute(&$vars, &$response)
    {
        // parameter check
        $error = &$response->getError();
        if (count($vars) > 3) {
            $error->add(XNPERR_EXTRA_PARAM);
        }
        if (count($vars) < 3) {
            $error->add(XNPERR_MISSING_PARAM);
        }

        if (isset($vars[0]) && strlen($vars[0]) > 32) {
            $error->add(XNPERR_INVALID_PARAM, 'too long parameter 1');
        }
        if (!is_int($vars[1]) && !ctype_digit($vars[1])) {
            $error->add(XNPERR_INVALID_PARAM, 'not integer parameter 2');
        }
        if ($vars[2] != '1' && $vars[2] != '0') {
            $error->add(XNPERR_INVALID_PARAM, 'parameter 3 must be 0 or 1');
        }

        if ($error->get(0)) {
            // return if parameter error
            $response->setResult(false);

            return false;
        } else {
            $sessionid = $vars[0];
            $file_id = intval($vars[1]);
            $agreement = intval($vars[2]);
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
            $error->add(XNPERR_NOT_FOUND);

            return false;
        }
        $item_id = $file->get('item_id');
        if (empty($item_id)) {
            $response->setResult(false);
            $error->add(XNPERR_ACCESS_FORBIDDEN); // maybe belong to other session
            return false;
        }
        // can user access that item?
        $item_handler = &xoonips_getormcompohandler('xoonips', 'item');
        if (!$item_handler->getPerm($item_id, $uid, 'read')) {
            $response->setResult(false);
            $error->add(XNPERR_ACCESS_FORBIDDEN);

            return false;
        }
        // already deleted?
        if ($file->get('is_deleted')) {
            $response->setResult(false);
            $error->add(XNPERR_NOT_FOUND, 'already deleted or replaced');

            return false;
        }
        // item_id -> item, itemtype
        $item_basic_handler = &xoonips_getormhandler('xoonips', 'item_basic');
        $basic = $item_basic_handler->get($item_id);
        if (!$basic) {
            $response->setResult(false);
            $error->add(XNPERR_SERVER_ERROR, 'cannot get item_basic');

            return false;
        }
        $item_type_handler = &xoonips_getormhandler('xoonips', 'item_type');
        $item_type = $item_type_handler->get($basic->get('item_type_id'));
        if (!$item_type) {
            $response->setResult(false);
            $error->add(XNPERR_SERVER_ERROR, 'cannot get itemtype of that item');

            return false;
        }
        // item_type, item_id -> detail
        $detail_item_handler = &xoonips_getormcompohandler($item_type->get('name'), 'item');
        $detail_item = $detail_item_handler->get($item_id);
        if (!$detail_item) {
            $response->setResult(false);
            $error->add(XNPERR_SERVER_ERROR, 'cannot get item');

            return false;
        }
        $detail = $detail_item->getVar('detail');
        // license agreement?
        if (!$agreement && $detail->get('rights')) {
            $response->setResult(false);
            $error->add(XNPERR_ERROR, 'need license agreement');

            return false;
        }

        if (!$detail_item_handler->hasDownloadPermission($uid, $file_id)) {
            $response->setResult(false);
            $error->add(XNPERR_ACCESS_FORBIDDEN);

            return false;
        }
        // start transaction
        $transaction = XooNIpsTransaction::getInstance();
        $transaction->start();
        // increment download count
        $file->setVar('download_count', $file->get('download_count') + 1, true);
        if (!$file_handler->insert($file)) {
            $transaction->rollback();
            $response->setResult(false);
            $error->add(XNPERR_SERVER_ERROR, 'cannot update file table');

            return false;
        }
        // insert event log ( file downloaded )
        $eventlog_handler = &xoonips_getormhandler('xoonips', 'event_log');
        if (!$eventlog_handler->recordDownloadFileEvent($item_id, $file_id)) {
            $transaction->rollback();
            $response->setResult(false);
            $error->add(XNPERR_SERVER_ERROR, 'cannot insert event log');

            return false;
        }

        // get module option 'enable_dl_limit'
        $iteminfo = $detail_item_handler->getIteminfo();
        $mhandler = &xoops_gethandler('module');
        $module = $mhandler->getByDirname($iteminfo['ormcompo']['module']);
        $chandler = &xoops_gethandler('config');
        $assoc = $chandler->getConfigsByCat(false, $module->mid());
        if (isset($assoc['enable_dl_limit']) && $assoc['enable_dl_limit'] == '1') {
            // send download-notification
            if ($detail->get('attachment_dl_limit') && $detail->get('attachment_dl_notify')) {
                xoonips_notification_user_file_downloaded($file_id, $uid);
            }
        }
        // end transaction
        $transaction->commit();
        $response->setSuccess($file);
        $response->setResult(true);

        return true;
    }
}
