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

require_once dirname(__DIR__).'/base/logic.class.php';
require_once __DIR__.'/transfer.class.php';

class XooNIpsLogicTransferUserAccept extends XooNIpsLogicTransfer
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * user accepted transfer request.
     *
     * @param[in]  $vars[0] array of item_id
     * @param[in]  $vars[1] uid of new item owner
     * @param[in]  $vars[2] index_id where items are registered to
     * @param[out] XooNIpsError error
     *
     * @return bool true if succeeded
     */
    public function execute_without_transaction(&$vars, &$error)
    {
        $item_ids = $vars[0];
        $to_uid = $vars[1];
        $index_id = $vars[2];

        if (false == $this->is_private_index_id_of($index_id, $to_uid)) {
            $error->add(XNPERR_SERVER_ERROR, 'bad index id');

            return false;
        }

        $from_uid_of_item = array();
        foreach ($item_ids as $item_id) {
            if (false == $this->remove_item_from_transfer_request($error, $item_id)) {
                return false;
            }

            if (false == $this->move_item_to_other_private_index($error, $item_id, $index_id)) {
                return false;
            }

            if (false == $this->remove_item_from_achievements_if_needed($error, $item_id)) {
                return false;
            }

            // update owner, last_udpate_date
            $item_basic_handler = &xoonips_getormhandler('xoonips', 'item_basic');
            $item_basic = $item_basic_handler->get($item_id);
            $from_uid = $item_basic->get('uid');
            $from_uid_of_item[$item_id] = $from_uid;
            $item_basic->set('uid', $to_uid);
            $item_basic->set('last_update_date', time());
            if (false == $item_basic_handler->insert($item_basic)) {
                $error->add(XNPERR_SERVER_ERROR, 'cannot update item');

                return false;
            }

            if (false == $this->insert_changelog($error, $item_id, $from_uid, $to_uid)) {
                return false;
            }

            // insert event log
            $eventlog_handler = &xoonips_getormhandler('xoonips', 'event_log');
            if (false == $eventlog_handler->recordTransferItemEvent($item_id, $index_id, $to_uid)) {
                $error->add(XNPERR_SERVER_ERROR, 'cannot insert event');

                return false;
            }

            $item_lock_handler = &xoonips_getormhandler('xoonips', 'item_lock');
            if (false == $item_lock_handler->unlock($item_id)) {
                $error->add(XNPERR_SERVER_ERROR, 'cannot unlock item');

                return false;
            }

            if (false == $this->update_item_status_if_public_certified($error, $item_id)) {
                return false;
            }
        }
        foreach ($item_ids as $item_id) {
            if (false == $this->remove_related_to_if_no_read_permission($item_id, $from_uid_of_item[$item_id], $to_uid)) {
                return false;
            }
        }

        return true;
    }

    public function insert_changelog(&$error, $item_id, $from_uid, $to_uid)
    {
        // insert changelog
        $xoops_user_handler = &xoops_gethandler('user');
        $from_user = $xoops_user_handler->get($from_uid);
        $to_user = $xoops_user_handler->get($to_uid);
        $changelog_handler = &xoonips_getormhandler('xoonips', 'changelog');
        $changelog = $changelog_handler->create();
        $changelog->set('uid', $from_uid);
        $changelog->set('item_id', $item_id);
        $changelog->set('log_date', time());
        $changelog->set('log', sprintf(_MD_XOONIPS_TRANSFER_CHANGE_LOG_AUTOFILL_TEXT, $from_user->getVar('uname', 'n'), $to_user->getVar('uname', 'n')));
        if (false == $changelog_handler->insert($changelog)) {
            $error->add(XNPERR_SERVER_ERROR, 'cannot insert changelog');

            return false;
        }

        return true;
    }
}
