<?php

// $Revision: 1.1.4.1.2.3 $
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

/**
 * subclass of XooNIpsLogic(getSimpleItems).
 */
class XooNIpsLogicGetSimpleItems extends XooNIpsLogic
{
    /**
     * execute getSimpleItems.
     *
     * @param[in]  $vars[0] sessionid
     * @param[in]  $vars[1] array of id
     * @param[in]  $vars[2] id_type
     * @param[out] $response->result true:success, false:failed
     * @param[out] $response->error  error information
     * @param[out] $response->success XooNIpsItem retrieved item object
     *
     * @return false if fault
     */
    public function execute(&$vars, &$response)
    {
        $error = &$response->getError();
        $response->setResult(false);
        //
        // parameter check
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
        }

        if ($error->get(0)) {
            // return if parameter error
            $response->setResult(false);

            return false;
        } else {
            $sessionid = $vars[0];
            $ids = $vars[1];
            $id_type = $vars[2];
        }
        //
        // validate session
        list($result, $uid, $session) = $this->restoreSession($sessionid);
        if (!$result) {
            $error->add(XNPERR_INVALID_SESSION);
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
        //
        // escape each id
        $esc_ids = array();
        foreach ($ids as $id) {
            if ($id_type == 'item_id') {
                $esc_ids[] = intval($id);
            } elseif ($id_type == 'ext_id') {
                $esc_ids[] = $GLOBALS['xoopsDB']->quoteString($id);
            }
        }

        if ($id_type == 'item_id') {
            $criteria = new Criteria('item_id', '('.implode(', ', $esc_ids).')', 'IN');
        } elseif ($id_type == 'ext_id') {
            $criteria = new Criteria('doi', '('.implode(', ', $esc_ids).')', 'IN');
        }

        // retrieve each item
        $xoonipsitem_handler = &xoonips_getormcompohandler('xoonips', 'item');
        $items = &$xoonipsitem_handler->getObjects($criteria);

        $ret = array(); // return array of items
        if ($items) {
            //
            // creat mapping of ext_id or item_id => item object
            $map = array();
            for ($i = 0; $i < count($items); ++$i) {
                $basic = $items[$i]->getVar('basic');
                if ($id_type == 'item_id') {
                    $map[$basic->get('item_id')] = $items[$i];
                } elseif ($id_type == 'ext_id') {
                    $map[$basic->get('doi')] = $items[$i];
                }
            }

            $itemtype_handler = &xoonips_getormhandler('xoonips', 'item_type');
            foreach ($ids as $id) {
                if (!isset($map[$id])) {
                    continue;
                } // can't retrieve an item that is identified by $id.

                // check access permission
                $item = $map[$id];
                $basic = $item->getVar('basic');
                $perm = $xoonipsitem_handler->getPerm($basic->get('item_id'), $uid, 'read');
                if (!$perm) {
                    continue;
                } // skip access forbidden item
                $itemtype = &$itemtype_handler->get($basic->get('item_type_id'));
                if (!$itemtype) {
                    continue;
                } //
                //
                // retrieve item
                $item_handler = &xoonips_getormcompohandler($itemtype->get('name'), 'item');
                if ($item_handler) {
                    $i = &$item_handler->get($basic->get('item_id'));
                    if (is_object($i)) {
                        $ret[] = $i;
                    }
                }
            }
        }
        $response->setSuccess($ret);
        $response->setResult(true);

        return true;
    }
}
