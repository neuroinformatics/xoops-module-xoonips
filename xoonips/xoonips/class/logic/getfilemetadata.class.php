<?php

// $Revision: 1.1.2.5 $
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
include_once XOOPS_ROOT_PATH
.'/modules/xoonips/class/base/transaction.class.php';
include_once XOOPS_ROOT_PATH.'/modules/xoonips/include/notification.inc.php';

/**
 * subclass of XooNIpsLogic(getFileMetadata).
 */
class XooNIpsLogicGetFileMetadata extends XooNIpsLogic
{
    /**
     * execute getFile.
     *
     * @param[in]  $vars[0] session ID
     * @param[in]  $vars[1] file ID
     * @param[out] $response->result true:success, false:failed
     * @param[out] $response->error  error information
     * @param[out] $response->success file metadata
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

            return false;
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
            $error->add(XNPERR_NOT_FOUND);

            return false;
        }
        $item_id = $file->get('item_id');
        if (empty($item_id)) {
            $response->setResult(false);
            $error->add(XNPERR_NOT_FOUND); // maybe belong to other session
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
            $error->add(XNPERR_SERVER_ERROR,
                        'cannot get itemtype of that item');

            return false;
        }
        // item_type, item_id -> detail
        $detail_item_handler = &xoonips_getormcompohandler(
            $item_type->get('name'), 'item');
        $detail_item = $detail_item_handler->get($item_id);
        if (!$detail_item) {
            $response->setResult(false);
            $error->add(XNPERR_SERVER_ERROR, 'cannot get item');

            return false;
        }
        if (!$detail_item_handler->hasDownloadPermission($uid, $file_id)) {
            $response->setResult(false);
            $error->add(XNPERR_ACCESS_FORBIDDEN);

            return false;
        }
        $file_type_handler = &xoonips_getormhandler('xoonips', 'file_type');
        $file_type = $file_type_handler->get($file->get('file_type_id'));
        if ($file_type === false) {
            $response->setResult(false);
            $error->add(XNPERR_SERVER_ERROR, 'unknown file type');

            return false;
        }

        $iteminfo = $detail_item_handler->getItemInfo();
        if ($iteminfo['files']['main'] == $file_type->get('name')) {
            $download_count = $file->get('download_count');
            $download_count_sum = $file_handler->getTotalDownloadCount(
                $item_id, $file_type->get('name'));
        } else {
            $download_count = 0;
            $download_count_sum = 0;
        }

        if ($iteminfo['files']['preview'] == $file_type->get('name')) {
            $caption = $file->get('caption');
            $thumbnail = $file->get('thumbnail_file');
        } else {
            $caption = '';
            $thumbnail = '';
        }

        $result = array(
            'id' => $file_id,
            'filetype' => $file_type->get('name'),
            'originalname' => $file->get('original_file_name'),
            'size' => $file->get('file_size'),
            'mimetype' => $file->get('mime_type'),
            'caption' => $caption,
            'thumbnail' => $thumbnail,
            'registration_date' => $basic->get('creation_date'),
            'last_modified_date' => $file->get('timestamp'),
            'download_count' => $download_count,
            'download_count_sum' => $download_count_sum,
        );
        $response->setSuccess($result);
        $response->setResult(true);

        return true;
    }
}
