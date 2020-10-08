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

require_once dirname(__DIR__).'/base/logic.class.php';

class XooNIpsLogicImportCheckImport extends XooNIpsLogic
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param $vars[0] array of XooNIpsImportItem
     * @param $vars[1] integer user id who import
     * @param $vars[2] boolean import as new flag
     *
     * @return success['import_items']
     * @return success['private_item_number_limit_over']
     * @return success['private_item_storage_limit_over']
     */
    public function execute(&$vars, &$response)
    {
        // set import_as_new flag
        if ($vars[2]) { //import as new option is true
            foreach (array_keys($vars[0]) as $key) {
                $vars[0][$key]->setImportAsNewFlag(true);
                $vars[0][$key]->setUpdateFlag(false);
            }
        } else { //import as new option is false
            foreach (array_keys($vars[0]) as $key) {
                if ($vars[0][$key]->getDoiConflictFlag()) {
                    continue;
                }
                if ($vars[0][$key]->getUpdateFlag()) {
                    continue;
                }
                if (count($vars[0][$key]->getDuplicateLockedItemId()) > 0) {
                    continue;
                }
                if (count($vars[0][$key]->getDuplicateUpdatableItemId()) > 0
                ) {
                    continue;
                }
                $vars[0][$key]->setImportAsNewFlag(true);
            }
        }

        $success = array(
            'import_items' => $vars[0],
            'private_item_number_limit_over' => $this->_is_private_item_number_limit_over($vars[0], $vars[1]),
            'private_item_storage_limit_over' => $this->_is_private_item_storage_limit_over($vars[0], $vars[1]),
        );
        $response->setResult(true);
        $response->setSuccess($success);
    }

    public function _is_private_item_number_limit_over(&$import_items, $uid)
    {
        $import_item_count = 0;
        foreach ($import_items as $item) {
            if ($item->getImportAsNewFlag()) {
                ++$import_item_count;
            }
        }
        $index_item_link_handler = &xoonips_getormhandler('xoonips', 'index_item_link');
        $user_handler = &xoonips_getormhandler('xoonips', 'users');
        $user = &$user_handler->get($uid);
        if (!$user) {
            return true;
        }

        return $import_item_count + count($index_item_link_handler->getAllPrivateOnlyItemId($uid)) > $user->get('private_item_number_limit');
    }

    public function _is_private_item_storage_limit_over(&$import_items, $uid)
    {
        $import_item_filesize = $this->_get_total_file_size_of_import_items($import_items);
        $remove_filesize = $this->_get_total_file_size_of_update_items($import_items);
        $user_handler = &xoonips_getormhandler('xoonips', 'users');
        $user = &$user_handler->get($uid);
        if (!$user) {
            return true;
        }

        return $import_item_filesize - $remove_filesize + $this->_filesize_private() > $user->get('private_item_storage_limit');
    }

    public function _get_toal_file_size_of_item($item_id)
    {
        $size = 0;
        $handler = &xoonips_getormhandler('xoonips', 'file');
        $join = new XooNIpsJoinCriteria('xoonips_index_item_link', 'item_id', 'item_id', 'LEFT', 'tlink');
        $criteria = new CriteriaCompo(new Criteria('tlink.item_id', $item_id));
        $criteria->add(new Criteria('certify_state', CERTIFIED, '!='));
        $files = &$handler->getObjects($criteria, '', false, '', $join);
        if (!$files || 0 == count($files)) {
            return 0;
        }
        foreach ($files as $f) {
            $size += $f->get('file_size');
        }

        return $size;
    }

    public function _get_total_file_size_of_update_items($import_items)
    {
        $remove_filesize = 0;
        foreach ($import_items as $item) {
            //remove old file of updated item
            if ($item->getUpdateFlag()) {
                foreach ($item->getDuplicateUpdatableItemId() as $item_id) {
                    $remove_filesize += $this->_get_toal_file_size_of_item($item_id);
                }
            }
        }

        return $remove_filesize;
    }

    public function _get_total_file_size_of_import_items($import_items)
    {
        $filesize = 0;
        foreach ($import_items as $item) {
            $filesize += $item->getTotalFileSize();
        }

        return $filesize;
    }

    public function _filesize_private()
    {
        $iids = array();
        if (RES_OK != xnp_get_private_item_id($_SESSION['XNPSID'], $_SESSION['xoopsUserId'], $iids)) {
            return 0;
        }

        return $this->_filesize_by_item_id($iids);
    }

    public function _filesize_by_item_id($iids)
    {
        if (0 == count($iids)) {
            return 0;
        }
        $itemtypes = array();
        if (RES_OK != xnp_get_item_types($itemtypes)) {
            return 0;
        }

        $ret = 0.0;
        foreach ($itemtypes as $i) {
            if (ITID_INDEX == $i['item_type_id']) {
                continue;
            }
            $modname = $i['name'];
            global $xoopsDB;

            $table = $xoopsDB->prefix("${modname}_item_detail");
            $id_name = preg_replace('/^xnp/', '', $modname).'_id';
            $query =
                "SELECT ${id_name} FROM $table where ${id_name} IN ("
                .implode(', ', $iids).
                ')';
            $result = $xoopsDB->query($query);
            if ($result) {
                $mod_iids = array();
                while (list($id) = $xoopsDB->fetchRow($result)) {
                    $mod_iids[] = $id;
                }
                require_once "../$modname/include/view.php";
                $fname = "${modname}GetDetailInformationTotalSize";
                if (function_exists($fname)) {
                    $ret += $fname($mod_iids);
                }
            }
        }

        return $ret;
    }
}
