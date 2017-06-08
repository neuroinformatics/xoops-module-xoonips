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

/**
 * subclass of XooNIpsLogic(updateItem2).
 */
class XooNIpsLogicUpdateItem2 extends XooNIpsLogic
{
    /**
     * @param[in]  $vars[0] session ID
     * @param[in]  $vars[1] XooNIpsItem item information
     * @param[out] $response->result true:success, false:failed
     * @param[out] $response->error  error information
     * @param[out] $response->success item id of updated item
     */
    public function execute(&$vars, &$response)
    {
        // parameter check
        $error = &$response->getError();
        if (count($vars) > 4) {
            $error->add(XNPERR_EXTRA_PARAM);
        } elseif (count($vars) < 4) {
            $error->add(XNPERR_MISSING_PARAM);
        } else {
            if (isset($vars[0]) && strlen($vars[0]) > 32) {
                $error->add(XNPERR_INVALID_PARAM, 'too long parameter 1');
            }
            if (!is_subclass_of($vars[1], 'XooNIpsItemInfoCompo')) {
                $error->add(XNPERR_INVALID_PARAM, 'parameter2 must be subclass of XooNIpsItemCompo');
            }
            $basic = $vars[1]->getVar('basic');
            if ($basic->get('item_id') == false) {
                $error->add(XNPERR_MISSING_PARAM, 'parameter 2 missing basic.item_id');
            }
            if ($basic->get('item_type_id') == false) {
                $error->add(XNPERR_MISSING_PARAM, 'parameter 2 missing basic.item_type_id');
            }

            $upload_max_filesize =
                $this->returnBytes(ini_get('upload_max_filesize'));
            for ($i = 0; $i < count($vars[2]); ++$i) {
                if (filesize($vars[2][$i]->getFilepath()) > $upload_max_filesize
                ) {
                    $error->add(XNPERR_INVALID_PARAM, 'too large file(file_id='.$vars[2][$i]->get('file_id').')');
                }
                $vars[2][$i]->set('file_size', filesize($vars[2][$i]->getFilepath()));
            }
            if (!is_array($vars[3])) {
                $error->add(XNPERR_INVALID_PARAM, 'parameter 4 must be array of int');
            } else {
                foreach ($vars[3] as $file_id) {
                    if (!is_int($file_id) && !ctype_digit($file_id)) {
                        $error->add(XNPERR_INVALID_PARAM, 'parameter 4 must be array of int');
                        break;
                    }
                }
            }
        }
        if ($error->get(0)) {
            // return if parameter error
            $response->setResult(false);

            return;
        } else {
            $sessionid = $vars[0];
            $item = $vars[1];
            $add_files = $vars[2];
            $delete_file_ids = $vars[3];
        }
        list($result, $uid, $session) = $this->restoreSession($sessionid);
        if (!$result) {
            $response->setResult(false);
            $error->add(XNPERR_INVALID_SESSION);

            return false;
        }
        // start transaction
        $transaction = XooNIpsTransaction::getInstance();
        $transaction->start();

        // item exists?
        $basic = $item->getVar('basic');
        $item_id = $basic->get('item_id');
        $item_compo_handler = &xoonips_getormcompohandler('xoonips', 'item');
        $basic_handler = &xoonips_getormhandler('xoonips', 'item_basic');
        $old_basic = $basic_handler->get($item_id);
        if ($old_basic == false) {
            $response->setResult(false);
            $error->add(XNPERR_NOT_FOUND);

            return false;
        }

        // check permission
        if (!$item_compo_handler->getPerm($item_id, $uid, 'write')) {
            $item_lock_handler = &xoonips_getormhandler('xoonips', 'item_lock');
            $response->setResult(false);
            if ($item_lock_handler->isLocked($item_id)) {
                $error->add(XNPERR_ACCESS_FORBIDDEN, 'cannot update item because item is '.$this->getLockTypeString($item_lock_handler->getLockType($item_id)));
            } else {
                $error->add(XNPERR_ACCESS_FORBIDDEN);
            }

            return false;
        }

        // item_id -> item_type_id
        $item_type_id = $old_basic->get('item_type_id');
        // item_type_id -> item_handler
        $item_type_handler = &xoonips_getormhandler('xoonips', 'item_type');
        $item_type = $item_type_handler->get($item_type_id);
        if (!$item_type) {
            $response->setResult(false);
            $error->add(XNPERR_ERROR, "unsupported itemtype($item_type_id)");

            return false;
        }
        $item_handler = &xoonips_getormcompohandler($item_type->get('name'), 'item');
        //
        // check ext_id(doi) confliction
        $basic = $item->getVar('basic');
        $ext_id = $basic->get('doi');
        if (strlen($ext_id)) {
            $item_basic_handler = &xoonips_getormhandler('xoonips', 'item_basic');
            $criteria = new CriteriaCompo(new Criteria('doi', addslashes($ext_id)));
            $criteria->add(new Criteria('item_id', $item_id, '<>'));
            $objs = &$item_basic_handler->getObjects($criteria);
            if (count($objs) > 0) {
                // error if other item(in public, group,
                // private of all users) has same doi
                $response->setResult(false);
                $error->add(XNPERR_INCOMPLETE_PARAM, "$ext_id is duplicated");

                return false;
            }
        }

        // item_id -> old_item
        $old_item = $item_handler->get($item_id);
        if (!$old_item) {
            $response->setResult(false);
            $error->add(XNPERR_NOT_FOUND);

            return false;
        }
        // item_type_id -> item_type_name, detail_item_type_handler
        $item_type_name = $item_type->get('name');
        $detail_item_type_handler = &xoonips_getormhandler($item_type_name, 'item_type');
        if (!$detail_item_type_handler) {
            $response->setResult(false);
            $error->add(XNPERR_ERROR, "unsupported itemtype(item_type_id=$item_type_id)");

            return false;
        }
        $detail_item_type = $detail_item_type_handler->get($item_type_id);
        if (!$detail_item_type) {
            $response->setResult(false);
            $error->add(XNPERR_ERROR, "unsupported itemtype(item_type_id=$item_type_id)");

            return false;
        }
        // error if unchangable fields changed:
        //  itemtype, username, last_modified_date, registration_date, url
        $user_handler = &xoonips_getormcompohandler('xoonips', 'user');
        $user = $user_handler->get($old_basic->get('uid'));
        if (!$user) {
            $error->add(XNPERR_SERVER_ERROR, 'cannot get item owner');
        }
        if ($old_basic->get('uid') != $basic->get('uid')) {
            $error->add(XNPERR_INVALID_PARAM, 'cannot change username');
        }
        if ($old_basic->get('last_update_date') != $basic->get('last_update_date')
        ) {
            $error->add(XNPERR_INVALID_PARAM, 'cannot change last_update_date');
        }
        if ($old_basic->get('creation_date') != $basic->get('creation_date')
        ) {
            $error->add(XNPERR_INVALID_PARAM, 'cannot change registration_date');
        }
        if ($old_basic->get('item_type_id') != $basic->get('item_type_id')
        ) {
            $error->add(XNPERR_INVALID_PARAM, 'cannot change itemtype');
            $response->setResult(false);

            return false;
        }
        if ($error->get()) {
            $response->setResult(false);

            return false;
        }

        // can access that indexes?
        $index_handler = &xoonips_getormhandler('xoonips', 'index');
        $index_item_link_handler = &xoonips_getormhandler('xoonips', 'index_item_link');
        $index_item_links = $item->getVar('indexes');
        $add_to_private = false;
        $add_to_group = false;
        $add_to_public = false;
        foreach ($index_item_links as $index_item_link) {
            $index_id = $index_item_link->get('index_id');
            $index = $index_handler->get($index_id);
            if (false == $index) {
                $error->add(XNPERR_NOT_FOUND, "no such index(index_id=$index_id)");
            } else {
                if (!$index_handler->getPerm($index_id, $uid, 'read')) {
                    $error->add(XNPERR_ACCESS_FORBIDDEN, "cannot access index(index_id=$index_id)");
                }
                $open_level = $index->get('open_level');
                if ($open_level == OL_PRIVATE) {
                    $add_to_private = true;
                } elseif ($open_level == OL_GROUP_ONLY) {
                    $add_to_group = true;
                } elseif ($open_level == OL_PUBLIC) {
                    $add_to_public = true;
                }
            }
        }
        // error if no private index is selected.
        if (!$add_to_private) {
            $error->add(XNPERR_INVALID_PARAM, 'select at least 1 private index');
        }
        // related_to items exist?
        $related_tos = $item->getVar('related_tos');
        $item_basic_handler = &xoonips_getormhandler('xoonips', 'item_basic');
        foreach ($related_tos as $related_to) {
            $related_item_id = $related_to->get('item_id');
            $related_item_basic = $item_basic_handler->get($related_item_id);
            if (!$related_item_basic) {
                $error->add(XNPERR_INVALID_PARAM, "no such related_to(item_id=$related_item_id)");
            } elseif (!$item_handler->getPerm($related_item_id, $uid, 'read')) {
                $error->add(XNPERR_ACCESS_FORBIDDEN, "cannot access related_tos(item_id=$related_item_id)");
            }
        }
        if ($error->get()) {
            $response->setResult(false);

            return false;
        }

        // error if add to public/group and no rights input
        if ($add_to_public || $add_to_group) {
            if (!$item_handler->isValidForPubicOrGroupShared($item)) {
                $response->setResult(false);
                $error->add(XNPERR_INCOMPLETE_PARAM, 'item cannot be public nor group-shared');

                return false;
            }
        }

        if (!$this->isPublicationDateValid(
            $response,
            $basic->get('publication_year'),
            $basic->get('publication_month'),
            $basic->get('publication_mday'),
            $detail_item_type->getRequired('publication_year'),
            $detail_item_type->getRequired('publication_month'),
            $detail_item_type->getRequired('publication_mday')
        )
        ) {
            $response->setResult(false);

            return false;
        }

        // get old and new file ids
        $old_file_id_to_types = $this->extractFileIdToTypesFromItem($detail_item_type, $old_item);
        $new_file_id_to_types = $this->extractFileIdToTypesFromItem($detail_item_type, $item);
        //remove files to be deleted from $new_file_id_to_types
        foreach ($delete_file_ids as $file_id) {
            unset($new_file_id_to_types[$file_id]);
        }

        $valid_file_type_names = $detail_item_type->getFileTypeNames();
        $file_type_handler = &xoonips_getormhandler('xoonips', 'file_type');
        foreach ($add_files as $file) {
            $file_id = $file->get('file_id');
            if (isset($old_file_ids[$file_id])) {
                $response->setResult(false);
                $error->add(XNPERR_INVALID_PARAM, 'file id conflicts');

                return false;
            }
            $file_type = $file_type_handler->get($file->get('file_type_id'));
            if (!in_array($file_type->get('name'), $valid_file_type_names)) {
                $response->setResult(false);
                $error->add(XNPERR_INVALID_PARAM, "filetype not match(pseudo file_id=$file_id)");

                return false;
            }
        }

        // check item storage/number limit(private/group)
        $new_size = $this->getSizeOfItem($item);
        $old_size = $this->getSizeOfItem($old_item);
        if (!$this->isEnoughSpace($error, $uid, $new_size, $item->getVar('indexes'), $old_size, $old_item->getVar('indexes'))) {
            $response->setResult(false);

            return false;
        }

        if (!$this->isFilesConsistent($error, $old_file_id_to_types, $new_file_id_to_types, $add_files, $delete_file_ids)) {
            $response->setResult(false);

            return false;
        }

        // avoid XooNIpsRelatedObjectHandler::insert()
        // from deleting old mainfile.
        foreach ($detail_item_type->getFileTypeNames() as $file_type_name) {
            $file = $old_item->getVar($file_type_name);
            if (!empty($file) && !is_array($file)) {
                // avoid error in inserting empty file
                if ($file->get('file_id') == 0) {
                    $file->unsetNew();
                    $file->unsetDirty();
                }
            }
            $item->setVar($file_type_name, $file);
        }

        // insert
        if (!$item_handler->insert($item)) {
            $transaction->rollback();
            $response->setResult(false);
            $error->add(XNPERR_SERVER_ERROR, 'cannot update item');

            return false;
        }

        if (count($add_files) == 0
            && count($delete_file_ids) == 0
            && $this->isOnlyPrivateIndexChanged($error, $detail_item_type->getIteminfo(), $item, $old_item)
        ) {
            $transaction->commit();
            $response->setSuccess($item_id);
            $response->setResult(true);

            return true;
        }

        // add new files
        $file_handler = &xoonips_getormhandler('xoonips', 'file');
        for ($i = 0; $i < count($add_files); ++$i) {
            $add_files[$i]->setVar('mime_type', $this->guessMimeType($add_files[$i]), true);
            if ($new_file_id_to_types[$add_files[$i]->get('file_id')] == $detail_item_type->getPreviewFileName()
            ) {
                $this->createThumbnail($error, $add_files[$i]);
            }
            $add_files[$i]->setNew();
            $add_files[$i]->set('item_id', $item_id);
            if (!$file_handler->insert($add_files[$i])) {
                $transaction->rollback();
                $response->setResult(false);
                $error->add(XNPERR_SERVER_ERROR, 'cannot insert file');

                return false;
            }
        }

        $item_handler->unsetNew($item);
        $basic->setVar('last_update_date', time(), true);
        if (!$basic_handler->insert($basic)) {
            $transaction->rollback();
            $error->add(XNPERR_SERVER_ERROR, 'cannot update item_basic');
            $response->setResult(false);

            return false;
        }

        // event log ( update item )
        $eventlog_handler = &xoonips_getormhandler('xoonips', 'event_log');
        if (!$eventlog_handler->recordUpdateItemEvent($item_id)) {
            $transaction->rollback();
            $error->add(XNPERR_SERVER_ERROR, 'cannot insert event');
            $response->setResult(false);

            return false;
        }

        // item insert/update/certify_required/certified event,
        // change certify_state,
        // send notification, update RSS, update item_status.
        if (!$only_private_index_changed
            && !$this->touchItem1($error, $item, $uid)
        ) {
            $transaction->rollback();
            $response->setResult(false);

            return false;
        }

        // remove files
        foreach ($delete_file_ids as $file_id) {
            $file = $file_handler->get($file_id);
            $file->set('is_deleted', 1);
            if (!$file_handler->insert($file)) {
                $transaction->rollback();
                $error->add(XNPERR_SERVER_ERROR, 'cannot update file');
                $response->setResult(false);

                return false;
            }
        }
        // commit
        $transaction->commit();

        foreach ($delete_file_ids as $file_id) {
            $file = $file_handler->get($file_id);

            // unlink file
            $file_handler->deleteFile($file);

            // delete search_text
            $search_text_handler = &xoonips_getormhandler('xoonips', 'search_text');
            $search_text = $search_text_handler->get($file_id);
            if ($search_text) {
                $search_text_handler->delete($search_text);
            }
        }

        // update search_text
        $files = &$file_handler->getObjects(new Criteria('item_id', $item_id));
        if (!empty($files)) {
            $admin_file_handler = &xoonips_gethandler('xoonips', 'admin_file');
            foreach ($files as $file) {
                $admin_file_handler->updateFileSearchText($file->get('file_id'), true);
            }
        }

        if ($add_to_group || $add_to_public) {
            $this->touchItem2($error, $item, $uid);
        }

        $response->setSuccess($item_id);
        $response->setResult(true);

        return true;
    }

    /**
     * return true if only private index was changed.
     */
    public function isOnlyPrivateIndexChanged(&$error, $iteminfo, $new_item, $old_item)
    {
        foreach ($iteminfo['orm'] as $orminfo) {
            $key = $orminfo['field'];
            if ($key != 'indexes') {
                $new_orm = $new_item->getVar($key);
                $old_orm = $old_item->getVar($key);
                if ($orminfo['multiple']) {
                    if (count($new_orm) != count($old_orm)) {
                        return false;
                    }
                    for ($i = 0; $i < count($new_orm); ++$i) {
                        if (!$new_orm[$i]->equals($old_orm[$i])) {
                            return false;
                        }
                    }
                } else {
                    if ($new_orm == false && $old_orm != false
                        || !$new_orm->equals($old_orm)
                    ) {
                        return false;
                    }
                }
            }
        }
        $index_item_link_handler = &xoonips_getormhandler('xoonips', 'index_item_link');
        $new_index_ids = array();
        foreach ($new_item->getVar('indexes') as $link) {
            $new_index_ids[] = $link->get('index_id');
        }
        $old_index_ids = array();
        foreach ($old_item->getVar('indexes') as $link) {
            $old_index_ids[] = $link->get('index_id');
        }
        $changed_index_ids = array_merge(array_diff($new_index_ids, $old_index_ids), array_diff($old_index_ids, $new_index_ids));
        if (empty($changed_index_ids)) {
            return true; // not changed
        }
        $index_handler = &xoonips_getormhandler('xoonips', 'index');
        $criteria = new CriteriaCompo(new Criteria('open_level', OL_PRIVATE, '<>'));
        $criteria->add(new Criteria('index_id', '('.implode(',', $changed_index_ids).')', 'in'));
        $indexes = &$index_handler->getObjects($criteria);
        if ($indexes === false) {
            $error->add(XNPERR_SERVER_ERROR, 'cannot get changed nonprivate index');

            return false;
        }

        return empty($indexes);
    }

    /**
     * extract associative array (key is file_id, value is file type name)
     *   from XooNIpsItemInfoCompo.
     *
     * @param XooNIpsOrmItemType item_type
     * @param XooNIpsItemInfoCompo item_compo
     *
     * @return array associative array
     */
    public function extractFileIdToTypesFromItem($item_type, $item_compo)
    {
        $file_id_to_filetypes = array();
        foreach ($item_type->getFileTypeNames() as $file_type_name) {
            $files = $item_compo->getVar($file_type_name);
            if (is_array($files)) {
                foreach ($files as $file) {
                    $file_id_to_filetypes[intval($file->get('file_id'))] = $file_type_name;
                }
            } elseif ($files && $files->get('file_id')) {
                $file_id_to_filetypes[intval($files->get('file_id'))] = $file_type_name;
            }
        }

        return $file_id_to_filetypes;
    }

    /**
     * check consistency of all file_ids and file types of updateItem2 input.
     *
     * @param XooNIpsError error
     * @param array old_file_id_to_types
     * @param array new_file_id_to_types
     * @param array add_files
     * @param array delete_file_ids
     *
     * @return bool true if consistent
     */
    public function isFilesConsistent(&$error, $old_file_id_to_types, $new_file_id_to_types, $add_files, $delete_file_ids)
    {
        $file_type_handler = &xoonips_getormhandler('xoonips', 'file_type');
        $file_handler = &xoonips_getormhandler('xoonips', 'file');
        $add_file_id_to_types = array();
        foreach ($add_files as $file) {
            $file_type = $file_type_handler->get($file->get('file_type_id'));
            $add_file_id_to_types[intval($file->get('file_id'))] = $file_type->get('name');
        }

        $all_file_ids = array_merge(
            array_keys($old_file_id_to_types),
            array_keys($new_file_id_to_types),
            array_keys($add_file_id_to_types),
            $delete_file_ids
        );

        $is_consistent = true;
        foreach ($all_file_ids as $file_id) {
            if (isset($old_file_id_to_types[$file_id])
                && isset($new_file_id_to_types[$file_id])
                && !isset($add_file_id_to_types[$file_id])
                && !in_array($file_id, $delete_file_ids)
                && $old_file_id_to_types[$file_id] == $new_file_id_to_types[$file_id]
            ) {
                continue; // not modify this file
            }
            if (!isset($old_file_id_to_types[$file_id])
                && isset($new_file_id_to_types[$file_id])
                && isset($add_file_id_to_types[$file_id])
                && !in_array($file_id, $delete_file_ids)
                && $add_file_id_to_types[$file_id] == $new_file_id_to_types[$file_id]
            ) {
                continue; // add this file
            }
            if (isset($old_file_id_to_types[$file_id])
                && !isset($new_file_id_to_types[$file_id])
                && !isset($add_file_id_to_types[$file_id])
                && in_array($file_id, $delete_file_ids)
            ) {
                continue; // delete this file
            }

            $error->add(XNPERR_INVALID_PARAM, "add or delete file id inconsistent(file_id=$file_id)");
            $is_consistent = false;
        }

        return $is_consistent;
    }
}
