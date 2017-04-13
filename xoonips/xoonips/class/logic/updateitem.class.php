<?php

// $Revision: 1.1.4.1.2.6 $
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
 * subclass of XooNIpsLogic(updateItem).
 */
class XooNIpsLogicUpdateItem extends XooNIpsLogic
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
        if (count($vars) > 2) {
            $error->add(XNPERR_EXTRA_PARAM);
        } elseif (count($vars) < 2) {
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
        }
        if ($error->get(0)) {
            // return if parameter error
            $response->setResult(false);

            return;
        } else {
            $sessionid = $vars[0];
            $item = $vars[1];
        }
        list($result, $uid, $session) = $this->restoreSession($sessionid);
        if (!$result) {
            $response->setResult(false);
            $error->add(XNPERR_INVALID_SESSION);

            return false;
        }
        // item_id -> item_type_id
        $basic = $item->getVar('basic');
        $item_id = $basic->getVar('item_id');
        $basic_handler = &xoonips_getormhandler('xoonips', 'item_basic');
        $old_basic = $basic_handler->get($item_id);
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
        // check ext_id(doi)
        // error if readable item(public, group, own private item) has same doi
        $basic = $item->getVar('basic');
        if (strlen($basic->get('doi')) > 0) {
            $item_basic_handler = &xoonips_getormhandler('xoonips', 'item_basic');
            $criteria = new CriteriaCompo(new Criteria('doi', addslashes($basic->get('doi'))));
            $criteria->add(new Criteria('item_id', (int) $basic->get('item_id'), '<>'));
            $objs = &$item_basic_handler->getObjects($criteria);
            if (count($objs) > 0) {
                // error if other item(in public, group, private of all users) has same doi
                $response->setResult(false);
                $error->add(XNPERR_INCOMPLETE_PARAM, $basic->get('doi').' already exists');

                return false;
            }
        }
        // check permission
        if (!$item_handler->getPerm($item_id, $uid, 'write')) {
            $item_lock_handler = &xoonips_getormhandler('xoonips', 'item_lock');
            $response->setResult(false);
            if ($item_lock_handler->isLocked($item_id)) {
                $error->add(XNPERR_ACCESS_FORBIDDEN, 'cannot update item because item is '.$this->getLockTypeString($item_lock_handler->getLockType($item_id)));
            } else {
                $error->add(XNPERR_ACCESS_FORBIDDEN);
            }

            return false;
        }
        // item_id -> old_item
        $old_item = $item_handler->get($item_id);
        if (!$old_item) {
            $response->setResult(false);
            $error->add(XNPERR_NOT_FOUND);

            return false;
        }
        // item_type_id -> item_type_name, detail_item_type_handler
        $item_type_name = $item_type->getVar('name');
        $detail_item_type_handler = &xoonips_getormhandler($item_type_name, 'item_type');
        if (!$detail_item_type_handler) {
            $response->setResult(false);
            $error->add(XNPERR_ERROR, "unsupported itemtype($item_type_id)");

            return false;
        }
        $detail_item_type = $detail_item_type_handler->get($item_type_id);
        if (!$detail_item_type) {
            $response->setResult(false);
            $error->add(XNPERR_ERROR, "unsupported itemtype($item_type_id)");

            return false;
        }
        // error if unchangable fields changed:itemtype, username, last_modified_date, registration_date, url, file_id
        // but logic cannot detect it...
        $user_handler = &xoonips_getormcompohandler('xoonips', 'user');
        $user = $user_handler->get($old_basic->getVar('uid'));
        if (!$user) {
            $error->add(XNPERR_SERVER_ERROR, 'cannot get item owner');
        }
        if ($old_basic->getVar('uid') != $basic->getVar('uid')) {
            $error->add(XNPERR_INVALID_PARAM, 'cannot change username');
        }
        if ($old_basic->getVar('last_update_date') != $basic->getVar('last_update_date')) {
            $error->add(XNPERR_INVALID_PARAM, 'cannot change last_update_date');
        }
        if ($old_basic->getVar('creation_date') != $basic->getVar('creation_date')) {
            $error->add(XNPERR_INVALID_PARAM, 'cannot change registration_date');
        }
        if ($old_basic->getVar('item_type_id') != $basic->getVar('item_type_id')) {
            $error->add(XNPERR_INVALID_PARAM, 'cannot change itemtype');
            $response->setResult(false);

            return false;
        }
        $old_file_ids = array();
        $new_file_ids = array();
        foreach ($detail_item_type->getFileTypeNames() as $file_type_name) {
            $old_files = $old_item->getVar($file_type_name);
            $new_files = $item->getVar($file_type_name);
            if (is_array($old_files)) {
                foreach ($old_files as $old_file) {
                    $old_file_ids[] = $old_file->getVar('file_id');
                }
            } elseif ($old_files && $old_files->getVar('file_id')) {
                $old_file_ids[] = $old_files->getVar('file_id');
            }
            if (is_array($new_files)) {
                foreach ($new_files as $new_file) {
                    $new_file_ids[] = $new_file->getVar('file_id');
                }
            } elseif ($new_files && $new_files->getVar('file_id')) {
                $new_file_ids[] = $new_files->getVar('file_id');
            }
        }
        sort($old_file_ids);
        sort($new_file_ids);
        if (implode(',', $old_file_ids) != implode(',', $new_file_ids)) {
            $error->add(XNPERR_INVALID_PARAM, 'cannot change file_id');
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
                $error->add(XNPERR_NOT_FOUND, "index not found($index_id)");
            } else {
                if (!$index_handler->getPerm($index_id, $uid, 'read')) {
                    $error->add(XNPERR_ACCESS_FORBIDDEN, "cannot access index($index_id)");
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
            $related_item_id = $related_to->getVar('item_id');
            $related_item_basic = $item_basic_handler->get($related_item_id);
            if (!$related_item_basic) {
                $error->add(XNPERR_INVALID_PARAM, "related_to has non-existent item(item_id=$related_item_id)");
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
            $detail = $item->getVar('detail');
            if ($detail_item_type->getFieldByName('detail', 'rights')) {
                if ($detail_item_type->getFieldByName('detail', 'use_cc')) {
                    $use_cc = $detail->get('use_cc');
                } else {
                    $use_cc = 0;
                }
                if ($detail->get('rights') == '' && $use_cc == 0) {
                    $response->setResult(false);
                    $error->add(XNPERR_INCOMPLETE_PARAM, 'rights is required');

                    return false;
                }
            }
            // error if add to public/group and no readme input
            if ($detail_item_type->getFieldByName('detail', 'readme')) {
                if ($detail->get('readme') == '') {
                    $response->setResult(false);
                    $error->add(XNPERR_INCOMPLETE_PARAM, 'readme is required');

                    return false;
                }
            }
        }
        // check item storage/number limit(private/group)
        $size = $this->getSizeOfItem($item);
        if (!$this->isEnoughSpace($error, $uid, $size, $item->getVar('indexes'), $size, $old_item->getVar('indexes'))) {
            $response->setResult(false);

            return false;
        }
        // only private index changed?
        $only_private_index_changed = $this->isOnlyPrivateIndexChanged($error, $detail_item_type->getIteminfo(), $item, $old_item);
        // start transaction
        $transaction = XooNIpsTransaction::getInstance();
        $transaction->start();
        // insert
        if (!$item_handler->insert($item)) {
            $transaction->rollback();
            $response->setResult(false);
            $error->add(XNPERR_SERVER_ERROR, 'cannot update item');

            return false;
        }
        $item_handler->unsetNew($item);
        // update item_basic.last_update_date
        $basic->setVar('last_update_date', time());
        if (!$basic_handler->insert($basic)) {
            $error->add(XNPERR_SERVER_ERROR, 'cannot update item_basic');
            $response->setResult(false);

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
        if (!$only_private_index_changed && !$this->touchItem($error, $item, $uid)) {
            $transaction->rollback();
            $response->setResult(false);

            return false;
        }
        // commit
        $transaction->commit();
        $response->setSuccess($item_id);
        $response->setResult(true);

        return true;
    }

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
                    if ($new_orm == false && $old_orm != false || !$new_orm->equals($old_orm)) {
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
}
