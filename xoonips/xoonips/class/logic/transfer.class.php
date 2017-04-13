<?php

// $Revision: 1.1.2.9 $
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
require_once dirname(__DIR__).'/base/transaction.class.php';
require_once dirname(__DIR__).'/xoonipserror.class.php';

/**
 * base class of XooNIpsLogicTransfer*.
 */
class XooNIpsLogicTransfer extends XooNIpsLogic
{
    public function __construct()
    {
        parent::__construct();
    }

    public function execute(&$vars, &$response)
    {
        $transaction = XooNIpsTransaction::getInstance();
        $transaction->start();

        $error = &$response->getError();
        if ($this->execute_without_transaction($vars, $error)) {
            $transaction->commit();
            $response->setResult(true);
        } else {
            $transaction->rollback();
            $response->setResult(false);
        }
    }

    public function execute_without_transaction(&$vars, &$error)
    {
        // abstract
        return false;
    }

    /**
     * remove item from achievements of item owner if needed.
     * call this before update uid of item_basic table.
     *
     * @param XooNIpsError error
     * @param int item_id
     *
     * @return bool true if succeeded
     */
    public function remove_item_from_achievements_if_needed(&$error, $item_id)
    {
        $xconfig_handler = &xoonips_getormhandler('xoonips', 'config');
        $item_show_optional = $xconfig_handler->getValue('item_show_optional');
        if ($item_show_optional == 'on') {
            return true; // can use someone's item as my achievements.
        }

        $item_show_handler = &xoonips_getormhandler('xoonips', 'item_show');
        $item_basic_handler = &xoonips_getormhandler('xoonips', 'item_basic');
        $item_basic = $item_basic_handler->get($item_id);
        $criteria = new CriteriaCompo();
        $criteria->add(new Criteria('item_id', $item_id));
        $criteria->add(new Criteria('uid', $item_basic->get('uid')));
        if (false == $item_show_handler->deleteAll($criteria)) {
            $error->add(XNPERR_SERVER_ERROR, 'cannot remove item_show');

            return false;
        }

        return true;
    }

    /**
     * remove item from all private indexes and add item to $index_id.
     *
     * @param XooNIpsError error
     * @param int item_id
     * @param int index_id
     *
     * @return bool true if succeeded
     */
    public function move_item_to_other_private_index(&$error, $item_id, $index_id)
    {
        // remove from private index
        $index_item_link_handler = &xoonips_getormhandler('xoonips', 'index_item_link');
        $index_item_links = $index_item_link_handler->getByItemId($item_id, array(OL_PRIVATE));
        foreach ($index_item_links as $index_item_link) {
            if (false == $index_item_link_handler->delete($index_item_link)) {
                $error->add(XNPERR_SERVER_ERROR, 'cannot remove from private index');

                return false;
            }
        }

        // add to private index
        $index_item_link = $index_item_link_handler->create();
        $index_item_link->set('index_id', $index_id);
        $index_item_link->set('item_id', $item_id);
        $index_item_link->set('certify_state', NOT_CERTIFIED);
        if (false == $index_item_link_handler->insert($index_item_link)) {
            $error->add(XNPERR_SERVER_ERROR, 'cannot add to private index');

            return false;
        }

        return true;
    }

    /**
     * is item public and certified ?
     *
     * @param int item_id
     *
     * @return bool true if succeeded
     */
    public function _is_public_certified_item($item_id)
    {
        $index_item_link_handler = &xoonips_getormhandler('xoonips', 'index_item_link');
        $index_item_links = $index_item_link_handler->getByItemId($item_id, array(OL_PUBLIC));
        foreach ($index_item_links as $index_item_link) {
            if ($index_item_link->get('certify_state') == CERTIFIED) {
                return true;
            }
        }

        return false;
    }

    /**
     * update modified_timestamp of item_status table
     * if item is public and certified.
     *
     * @param XooNIpsError error
     * @param int item_id
     *
     * @return bool true if succeeded
     */
    public function update_item_status_if_public_certified(&$error, $item_id)
    {
        $item_status_handler = &xoonips_getormhandler('xoonips', 'item_status');
        if ($this->_is_public_certified_item($item_id)) {
            $item_status = $item_status_handler->get($item_id);
            if ($item_status) {
                $item_status->set('modified_timestamp', time());
                if (false == $item_status_handler->insert($item_status)) {
                    $error->add(XNPERR_SERVER_ERROR, 'cannot update item status');

                    return false;
                }
            }
        }

        return true;
    }

    /**
     * remove item from transfer_request table.
     *
     * @param XooNIpsError error
     * @param int item_id
     *
     * @return bool true if succeeded
     */
    public function remove_item_from_transfer_request(&$error, $item_id)
    {
        $transfer_request_handler = &xoonips_getormhandler('xoonips', 'transfer_request');
        $transfer_request = $transfer_request_handler->get($item_id);
        if ($transfer_request == false) {
            $error->add(XNPERR_SERVER_ERROR, 'cannot get transfer_request');

            return false;
        }
        if (false == $transfer_request_handler->delete($transfer_request)) {
            $error->add(XNPERR_SERVER_ERROR, 'cannot delete transfer_request');

            return false;
        }

        return true;
    }

    /**
     * remove relatedto if user lose read permission because of transfer.
     *
     * @param int item_id
     * @param int from_uid
     * @param int to_uid
     *
     * @return bool true if succeeded
     */
    public function remove_related_to_if_no_read_permission($item_id, $from_uid, $to_uid)
    {
        $related_to_handler = &xoonips_getormhandler('xoonips', 'related_to');
        $item_compo_handler = &xoonips_getormcompohandler('xoonips', 'item');
        $item_basic_handler = &xoonips_getormhandler('xoonips', 'item_basic');

        // relation from $item_id to items of $from_uid
        $related_tos = &$related_to_handler->getObjects(new Criteria('parent_id', $item_id));
        if (false === $related_tos) {
            return false;
        }
        foreach ($related_tos as $related_to) {
            if (!$item_compo_handler->getPerm($related_to->get('item_id'), $to_uid, 'read')) {
                if (false == $related_to_handler->delete($related_to)) {
                    return false;
                }
            }
        }

        // relation from items of $from_uid to $item_id
        if (!$item_compo_handler->getPerm($item_id, $from_uid, 'read')) {
            $related_tos = &$related_to_handler->getObjects(new Criteria('item_id', $item_id));
            if (false === $related_tos) {
                return false;
            }
            foreach ($related_tos as $related_to) {
                $item_basic = $item_basic_handler->get($related_to->get('parent_id'));
                if (false === $item_basic) {
                    return false;
                }
                if ($item_basic->get('uid') == $from_uid) {
                    if (false == $related_to_handler->delete($related_to)) {
                        return false;
                    }
                }
            }
        }

        return true;
    }

    public function is_private_index_id_of($index_id, $uid)
    {
        $index_handler = &xoonips_getormhandler('xoonips', 'index');
        $index = $index_handler->get($index_id);
        if ($index == false || $index->get('open_level') != OL_PRIVATE || $index->get('uid') != $uid) {
            return false;
        }

        return true;
    }
}
