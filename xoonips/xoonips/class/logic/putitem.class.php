<?php

// $Revision: 1.1.4.1.2.8 $
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
include_once XOOPS_ROOT_PATH.'/modules/xoonips/include/lib.php';

/**
 * subclass of XooNIpsLogic(putItem).
 */
class XooNIpsLogicPutItem extends XooNIpsLogic
{
    /**
     * execute putItem.
     *
     * @param[in]  $vars[0] session ID
     * @param[in]  $vars[1] XooNIpsItemCompo item information
     * @param[in]  array $vars[2] XooNIpsFile[] meta information of attachment files
     * @param[out] $response->result true:success, false:failed
     * @param[out] $response->error  error information
     * @param[out] $response$response->success item id of registered item
     */
    public function execute(&$vars, &$response)
    {
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
            // file size check
            if (is_array($vars[2])) {
                $upload_max_filesize = $this->returnBytes(ini_get('upload_max_filesize'));
                for ($i = 0; $i < count($vars[2]); ++$i) {
                    if (filesize($vars[2][$i]->getFilepath()) > $upload_max_filesize) {
                        $error->add(XNPERR_INVALID_PARAM, 'too large file(file_id='.$vars[2][$i]->get('file_id').')');
                    }
                    $vars[2][$i]->set('file_size', filesize($vars[2][$i]->getFilepath()));
                }
            }
        }
        if ($error->get(0)) {
            // return if parameter error
            $response->setResult(false);

            return;
        } else {
            $response->setResult(false);
            $sessionid = $vars[0];
            $item = $vars[1];
            $files = $vars[2];
        }
        list($result, $uid, $session) = $this->restoreSession($sessionid);
        if (!$result) {
            $response->setResult(false);
            $error->add(XNPERR_INVALID_SESSION);

            return false;
        }
        if ($uid == UID_GUEST) {
            $response->setResult(false);
            $error->add(XNPERR_ACCESS_FORBIDDEN, 'guest user cannot putItem'); // test E4
            return false;
        }
        // start transaction
        $transaction = XooNIpsTransaction::getInstance();
        $transaction->start();

        // set uid, title_id, keyword_id, creation_date, last_update_date
        $now = time();
        $basic = $item->getVar('basic');
        $basic->setVar('uid', $uid, true);
        $basic->setVar('creation_date', $now, true);
        $basic->setVar('last_update_date', $now, true);
        $item->setVar('basic', $basic);

        $titles = $item->getVar('titles');
        for ($i = 0; $i < count($titles); ++$i) {
            $titles[$i]->setVar('title_id', $i, true);
        }
        $item->setVar('titles', $titles);
        $keywords = $item->getVar('keywords');
        for ($i = 0; $i < count($keywords); ++$i) {
            $keywords[$i]->setVar('keyword_id', $i, true);
        }
        $item->setVar('keywords', $keywords);
        // ext_id duplicated?
        $item_basic_handler = &xoonips_getormhandler('xoonips', 'item_basic');
        $ext_id = $basic->get('doi');
        if (strlen($ext_id)) {
            $basics = &$item_basic_handler->getObjects(new Criteria('doi', addslashes($ext_id)));
            if (count($basics)) {
                $transaction->rollback();
                $response->setResult(false);
                $error->add(XNPERR_INCOMPLETE_PARAM, "$ext_id is duplicated");

                return false;
            }
        }
        //          // filled?
        //          $missing = array();
        //          if (!$item->isFilledRequired($missing)) {
        //              $response->setResult(false);
        //              $error->add(XNPERR_INCOMPLETE_PARAM); // test E3
        //              return false;
        //          }
        // can access that indexes? calculate get add_to_private, add_to_gids, add_to_public
        $index_handler = &xoonips_getormhandler('xoonips', 'index');
        $index_item_link_handler = &xoonips_getormhandler('xoonips', 'index_item_link');
        $index_item_links = $item->getVar('indexes');
        $add_to_private = false;
        $add_to_gids = array();
        $add_to_public = false;
        foreach ($index_item_links as $index_item_link) {
            $index_id = $index_item_link->get('index_id');
            $index = $index_handler->get($index_id);
            if (!$index) {
                $error->add(XNPERR_INVALID_PARAM, "no such index(index_id=$index_id)"); // test e5
            } else {
                if (!$index_handler->getPerm($index_id, $uid, 'read')) {
                    $error->add(XNPERR_ACCESS_FORBIDDEN, "cannot access index(index_id=$index_id)"); // test e5
                } else {
                    $open_level = $index->get('open_level');
                    if ($open_level == OL_PRIVATE) {
                        $add_to_private = true;
                    } elseif ($open_level == OL_GROUP_ONLY) {
                        $add_to_gids[$index->get('gid')] = true;
                    } elseif ($open_level == OL_PUBLIC) {
                        $add_to_public = true;
                    }
                }
            }
        }
        $add_to_gids = array_keys($add_to_gids);
        // error if no private index is selected.
        if (!$add_to_private) {
            $error->add(XNPERR_INVALID_PARAM, 'select at least 1 private index'); // test e5
        }
        // item_type_id -> item_type_name, detail_item_type_handler, detail_item_type, detail_item_handler
        $item_type_handler = &xoonips_getormhandler('xoonips', 'item_type');
        $basic = $item->getVar('basic');
        $item_type_id = $basic->get('item_type_id');
        $item_type = $item_type_handler->get($item_type_id);
        if (!$item_type) {
            $response->setResult(false);
            $transaction->rollback();
            $error->add(XNPERR_INVALID_PARAM, "bad itemtype(item_type_id=$item_type_id)"); // test E6*
            return false;
        }
        $item_type_name = $item_type->get('name');
        $detail_item_type_handler = &xoonips_getormhandler($item_type_name, 'item_type');
        if (!$detail_item_type_handler) {
            $response->setResult(false);
            $transaction->rollback();
            $error->add(XNPERR_SERVER_ERROR, "cannot get item type handler(item_type_id=$item_type_id)"); // test E7*
            return false;
        }
        $detail_item_type = $detail_item_type_handler->get($item_type_id);
        if (!$detail_item_type) {
            $response->setResult(false);
            $transaction->rollback();
            $error->add(XNPERR_SERVER_ERROR, "cannot get item type(item_type_id=$item_type_id)"); // test E8*
            return false;
        }
        $detail_item_handler = &xoonips_getormcompohandler($item_type_name, 'item');
        // can access that related_tos?
        $related_tos = $item->getVar('related_tos');
        foreach ($related_tos as $related_to) {
            $item_id = $related_to->get('item_id');
            $item_basic = $item_basic_handler->get($item_id);
            if (!$item_basic) {
                $error->add(XNPERR_INVALID_PARAM, "no such related_to(item_id=$item_id)"); // test e5
            } elseif (!$detail_item_handler->getPerm($item_id, $uid, 'read')) {
                $error->add(XNPERR_ACCESS_FORBIDDEN, "cannot access related_tos(item_id=$item_id)"); // test e5
            }
        }
        if ($error->get()) {
            $transaction->rollback();
            $response->setResult(false);

            return false;
        }
        // error if add to public/group and no rights/readme input
        if ($add_to_public || count($add_to_gids)) {
            if (!$detail_item_handler->isValidForPubicOrGroupShared($item)) {
                $response->setResult(false);
                $error->add(XNPERR_INCOMPLETE_PARAM, 'item cannot be public nor group-shared');

                return false;
            }
        }

        if (!$this->isPublicationDateValid($response,
            $basic->get('publication_year'),
            $basic->get('publication_month'),
            $basic->get('publication_mday'),
            $detail_item_type->getRequired('publication_year'),
            $detail_item_type->getRequired('publication_month'),
            $detail_item_type->getRequired('publication_mday'))) {
            $response->setResult(false);

            return false;
        }

        // item number/storage limit check
        $size = 0;
        foreach ($files as $file) {
            $size += $file->get('file_size');
        }
        if (!$this->isEnoughSpace($error, $uid, $size, $item->getVar('indexes'))) {
            $transaction->rollback();
            $response->setResult(false); // test E11
            return false;
        }
        // get filetypes
        $file_type_handler = &xoonips_getormhandler('xoonips', 'file_type');
        $file_types = &$file_type_handler->getObjects(null, true);
        // $item->setVar( '...', $files[...] ) // check exactly 1:1 ? correct filetype ? multiple files for non-multiple field?
        $pseudo2files = array();
        for ($i = 0; $i < count($files); ++$i) {
            $pseudo_file_id = $files[$i]->get('file_id');
            if (isset($pseudo2files[$pseudo_file_id])) {
                $error->add(XNPERR_INVALID_PARAM, "pseudo file_id conflicts(pseudo file_id=$pseudo_file_id)"); // test e12
            } else {
                $pseudo2files[$pseudo_file_id] = array(
                    'used' => false,
                    'file' => $files[$i],
                );
            }
        }
        foreach ($detail_item_type->getFileTypeNames() as $field_name) {
            $detail_files = $item->getVar($field_name);
            if (!$detail_item_type->getMultiple($field_name)) {
                $detail_file = $detail_files;
                if ($detail_file->get('file_id') == 0) {
                    $item->setVar($field_name, false);
                    continue; // this filetype maybe optional and omitted.
                }

                $detail_files = array(
                    $detail_file,
                );
            }
            for ($i = 0; $i < count($detail_files); ++$i) {
                $pseudo_id = $detail_files[$i]->get('file_id');
                if ($pseudo_id == 0) {
                    unset($detail_files[$i]);
                    continue;
                }
                if (!isset($pseudo_id) || !isset($pseudo2files[$pseudo_id])) {
                    $error->add(XNPERR_INVALID_PARAM, "unknown pseudo file_id in $field_name(pseudo file_id=$pseudo_id)"); // test e12
                } elseif ($pseudo2files[$pseudo_id]['used']) {
                    $error->add(XNPERR_INVALID_PARAM, "file referred twice(pseudo file_id=$pseudo_id)"); // test e12
                } else {
                    $pseudo2files[$pseudo_id]['used'] = true;
                    $file_type_id = $pseudo2files[$pseudo_id]['file']->get('file_type_id');
                    if (!isset($file_types[$file_type_id])) {
                        $error->add(XNPERR_INVALID_PARAM, "bad filetype(file_type_id=$file_type_id pseudo file_id=$pseudo_id)"); // test e12
                    } elseif ($file_types[$file_type_id]->get('name') != $field_name) {
                        $error->add(XNPERR_INVALID_PARAM, "filetype not match(pseudo file_id=$pseudo_id)"); // test e12
                    } else {
                        $detail_files[$i] = $pseudo2files[$pseudo_id]['file'];
                        $detail_files[$i]->setVar('mime_type', $this->guessMimeType($detail_files[$i]), true);
                        if ($field_name == $detail_item_type->getPreviewFileName()) {
                            $this->createThumbnail($error, $detail_files[$i]);
                        }
                    }
                }
            }
            if ($detail_item_type->getMultiple($field_name)) {
                $item->setVar($field_name, $detail_files);
            } elseif (count($detail_files)) {
                if (count($detail_files) >= 2) {
                    $error->add(XNPERR_INVALID_PARAM, "too many files for $field_name"); // test e12
                }
                $item->setVar($field_name, $detail_files[0]);
            }
        }
        if (count($pseudo2files) != 0) {
            foreach ($pseudo2files as $pseudo_id => $info) {
                if ($info['used'] == false) {
                    $error->add(XNPERR_INVALID_PARAM, "redundant file(pseudo file_id=$pseudo_id)"); // test e12
                }
            }
        }
        if ($error->get()) {
            $transaction->rollback();
            $response->setResult(false);

            return false;
        }

        // insert item
        $detail_item_handler->setNew($item);
        $detail_item_handler->setDirty($item);
        if (!$detail_item_handler->insert($item)) {
            $error->add(XNPERR_SERVER_ERROR, 'cannot insert item'); // test E13
            $transaction->rollback();
            $response->setResult(false);

            return false;
        }
        $detail_item_handler->unsetNew($item);
        $basic = $item->getVar('basic');
        $item_id = $basic->get('item_id');
        // event log ( insert item )
        $eventlog_handler = &xoonips_getormhandler('xoonips', 'event_log');
        if (!$eventlog_handler->recordInsertItemEvent($item_id)) {
            $transaction->rollback();
            $error->add(XNPERR_SERVER_ERROR, 'cannot insert event'); // test E14
            return false;
        }
        // item insert/update/certify_required/certified event, change certify_state, update item_status.
        if (!$this->touchItem1($error, $item, $uid)) {
            $transaction->rollback();
            $response->setResult(false); // test E16
            return false;
        }
        // commit
        $transaction->commit();

        // update search_text
        $file_handler = &xoonips_getormhandler('xoonips', 'file');
        $files = &$file_handler->getObjects(new Criteria('item_id', $item_id));
        if (false === $files) {
            $transaction->rollback();
            $error->add(XNPERR_SERVER_ERROR, 'cannot get file');

            return false;
        } else {
            $admin_file_handler = &xoonips_gethandler('xoonips', 'admin_file');
            foreach ($files as $file) {
                $admin_file_handler->updateFileSearchText($file->get('file_id'), true);
            }
        }

        // notification and rss
        $this->touchItem2($error, $item, $uid);

        $response->setSuccess($item_id);
        $response->setResult(true);

        return true;
    }
}
