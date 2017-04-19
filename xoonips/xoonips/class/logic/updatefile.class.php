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
 * subclass of XooNIpsLogic(updateFile).
 */
class XooNIpsLogicUpdateFile extends XooNIpsLogic
{
    /**
     * execute updateFile.
     *
     * @param[in]  $vars[0] session ID
     * @param[in]  $vars[1] item ID
     * @param[in]  $vars[2] identifier type of $vars[1] parameter('item_id'|'ext_id')
     * @param[in]  $vars[3] field name to add/update file
     * @param[in]  $vars[4] XooNIpsFile file information
     * @param[out] $response->result true:success, false:failed
     * @param[out] $response->error  error information
     * @param[out] $response->success new file id
     */
    public function execute(&$vars, &$response)
    {
        /*
        check permission
        check field name
        check filesize <= upload_max_filesize
        get XooNIpsFile if file_id exist ($oldfile)
        create XooNIpsFile from item_id and file information ($newfile)
        start transaction
        insert $newfile
        create search_text and update xoonips_file table
        if oldfile exists, set is_deleted of oldfile to 1
        update certify_state, notify certify_required, auto_certify, update item_status, udpate RSS
        commit
        delete oldfile
        */
        // parameter check
        $error = &$response->getError();
        if (count($vars) > 5) {
            $error->add(XNPERR_EXTRA_PARAM);
        } elseif (count($vars) < 5) {
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
            // file size check
            $upload_max_filesize = $this->returnBytes(ini_get('upload_max_filesize'));
            if (filesize($vars[4]->getFilepath()) > $upload_max_filesize) {
                $error->add(XNPERR_INVALID_PARAM, 'too large file');
            }
            $vars[4]->set('file_size', filesize($vars[4]->getFilepath()));
        }
        if ($error->get(0)) {
            // return if parameter error
            $response->setResult(false);

            return false;
        } else {
            $sessionid = $vars[0];
            $id = $vars[1];
            $id_type = $vars[2];
            $field_name = $vars[3];
            $file = $vars[4];
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
        }
        if (!$item) {
            $response->setResult(false);
            $error->add(XNPERR_NOT_FOUND, "id=$id");

            return false;
        }
        $basic = $item->getVar('basic');
        $item_id = $basic->get('item_id');
        // can modify?
        if (!$item_handler->getPerm($item_id, $uid, 'write')) {
            $item_lock_handler = &xoonips_getormhandler('xoonips', 'item_lock');
            $response->setResult(false);
            if ($item_lock_handler->isLocked($item_id)) {
                $error->add(XNPERR_ACCESS_FORBIDDEN, 'cannot update file because item is '.$this->getLockTypeString($item_lock_handler->getLockType($item_id)));
            } else {
                $error->add(XNPERR_ACCESS_FORBIDDEN);
            }

            return false;
        }

        // item -> itemtype, detail_item_type
        $item_type_handler = &xoonips_getormhandler('xoonips', 'item_type');
        $item_type = $item_type_handler->get($basic->get('item_type_id'));
        if (!$item_type) {
            $response->setResult(false);
            $error->add(XNPERR_SERVER_ERROR, 'cannot get itemtype of that item');

            return false;
        }
        $detail_item_type_handler = &xoonips_getormhandler($item_type->getVar('name'), 'item_type');
        $detail_item_type = $detail_item_type_handler->get($item_type->getVar('item_type_id'));
        if (!$detail_item_type) {
            $response->setResult(false);
            $error->add(XNPERR_SERVER_ERROR, 'cannot get detail itemtype of that item');

            return false;
        }
        // is file_type_id proper?
        $file_type_handler = &xoonips_getormhandler('xoonips', 'file_type');
        $file_type_id = $file->getVar('file_type_id');
        $file_type = $file_type_handler->get($file_type_id);
        if (!$file_type) {
            $response->setResult(false);
            $error->add(XNPERR_SERVER_ERROR, "bad filetype($file_type_id)");

            return false;
        }
        $file_type_name = $file_type->getVar('name');
        if (!in_array($file_type_name, $detail_item_type->getFileTypeNames())) {
            $response->setResult(false);
            $error->add(XNPERR_INVALID_PARAM, "bad filetype($file_type_name)");

            return false;
        }
        // add file to non-multiple field?
        $file_handler = &xoonips_getormhandler('xoonips', 'file');
        if (!$detail_item_type->getMultiple($file_type_name) && !$file->getVar('file_id')) {
            $c = new CriteriaCompo();
            $c->add(new Criteria('item_id', $item_id));
            $c->add(new Criteria('file_type_id', $file_type_id));
            $c->add(new Criteria('is_deleted', 0));
            $same_file_num = $file_handler->getCount($c);
            if ($same_file_num > 0) { // already file exists
                $response->setResult(false);
                $error->add(XNPERR_INVALID_PARAM, "multiple file not allowed for $file_type_name");

                return false;
            }
        }
        // get oldfile from oldfile_id
        $oldfile_id = $file->getVar('file_id');
        if ($oldfile_id) {
            $oldfile = $file_handler->get($oldfile_id);
            if ($oldfile->getVar('is_deleted')) {
                $response->setResult(false);
                $error->add(XNPERR_INVALID_PARAM, "cannot update deleted file(file_id=$oldfile_id)");

                return false;
            } else {
                if ($oldfile->getVar('item_id') != $item_id) {
                    $response->setResult(false);
                    $error->add(XNPERR_INVALID_PARAM, "item(item_id=$item_id) does not have that file(file_id=$oldfile_id)");

                    return false;
                }
                // check if $oldfile.item_type_id == $file.item_type_id
                $file_type_handler = &xoonips_getormhandler('xoonips', 'file_type');
                $file_type = $file_type_handler->get($oldfile->getVar('file_type_id'));
                if (!$file_type) {
                    $response->setResult(false);
                    $error->add(XNPERR_SERVER_ERROR, 'old file has unknown file type id');

                    return false;
                }
                if ($file_type->getVar('name') != $file_type_name) {
                    $response->setResult(false);
                    $error->add(XNPERR_INVALID_PARAM, 'cannot change filetype');

                    return false;
                }
            }
        } else {
            $oldfile = false;
        }
        // check item storage limit(private/group)
        $old_size = $this->getSizeOfItem($item);
        if ($oldfile) {
            $new_size = $old_size - $oldfile->getVar('file_size') + $file->getVar('file_size');
        } else {
            $new_size = $old_size + $file->getVar('file_size');
        }
        if (!$this->isEnoughSpace($error, $uid, $new_size, $item->getVar('indexes'), $old_size, $item->getVar('indexes'))) {
            $response->setResult(false);
            //$error->add(XNPERR_SERVER_ERROR, "not enough disk space"); // isEnoughSpace() adds errors.
            return false;
        }
        // set mime-type, thumbnail
        $file->setVar('mime_type', $this->guessMimeType($file));
        if ($detail_item_type->getPreviewFileName() == $file_type_name) {
            if (!$this->createThumbnail($error, $file)) {
                $response->setResult(false);

                return false;
            }
        }
        $data = file_get_contents($file->getFilepath());
        if ($data === false) {
            $response->setResult(false);
            $error->add(XNPERR_SERVER_ERROR, 'cannot get file content');

            return false;
        }
        $file->setVar('file_id', null);
        $file->setVar('item_id', $item_id);
        $file->setNew();
        // start transaction
        $transaction = XooNIpsTransaction::getInstance();
        $transaction->start();
        // insert $file
        if (!$file_handler->insert($file)) {
            $response->setResult(false);
            $error->add(XNPERR_SERVER_ERROR, 'cannot insert file');

            return false;
        }
        // create file search_text
        $admin_file_handler = &xoonips_gethandler('xoonips', 'admin_file');
        $admin_file_handler->updateFileSearchText($file->get('file_id'), true);

        if ($oldfile) {
            // set oldfile.is_deleted = 1;
            $oldfile->setVar('is_deleted', 1);
            if (!$file_handler->insert($oldfile)) {
                $transaction->rollback();
                $response->setResult(false);
                $error->add(XNPERR_SERVER_ERROR, 'cannot update old file');

                return false;
            }
            // delete search_text of old file
            $search_text_handler = &xoonips_getormhandler('xoonips', 'search_text');
            $search_text = $search_text_handler->get($oldfile->get('file_id'));
            if ($search_text && !$search_text_handler->delete($search_text)) {
                $transaction->rollback();
                $response->setResult(false);
                $error->add(XNPERR_INVALID_PARAM, 'cannot remove search text');

                return false;
            }
        }
        // event log ( update item )
        $eventlog_handler = &xoonips_getormhandler('xoonips', 'event_log');
        if (!$eventlog_handler->recordUpdateItemEvent($item_id)) {
            $error->add(XNPERR_SERVER_ERROR, 'cannot insert event');
            $response->setResult(false);

            return false;
        }
        // item insert/update/certify_required/certified event, change certify_state, send notification, update RSS, update item_status.
        if (!$this->touchItem($error, $item, true)) {
            $transaction->rollback();
            $response->setResult(false);

            return false;
        }
        // commit
        $transaction->commit();
        // unlink old file
        if ($oldfile) {
            $file_handler->deleteFile($oldfile);
        }
        // return
        $response->setSuccess($file->getVar('file_id'));
        $response->setResult(true);

        return true;
    }
}
