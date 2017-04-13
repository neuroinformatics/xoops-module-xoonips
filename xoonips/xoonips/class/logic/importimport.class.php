<?php

// $Revision: 1.1.2.6 $
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
require_once XOOPS_ROOT_PATH.'/modules/xoonips/class/base/transaction.class.php';

class XooNIpsLogicImportImport extends XooNIpsLogic
{
    public function __construct()
    {
        parent::__construct();
    }

    public function execute(&$vars, &$response)
    {
        global $xoopsUser;

        $success = array();
        $error = false;

        $transaction = XooNIpsTransaction::getInstance();
        $transaction->start();

        $itemtype_handler = &xoonips_getormhandler('xoonips', 'item_type');
        foreach (array_keys($vars[0]) as $key) {
            assert(!($vars[0][$key]->getImportAsNewFlag() && $vars[0][$key]->getUpdateFlag()));
            //skip this item if don't import as new and update
            if (!$vars[0][$key]->getImportAsNewFlag()
                && !$vars[0][$key]->getUpdateFlag()
            ) {
                continue;
            }

            $item_handler = &xoonips_getormcompohandler('xoonips', 'item');
            if ($vars[0][$key]->getUpdateFlag() && !$item_handler->getPerm($vars[0][$key]->getUpdateItemId(), $xoopsUser->getVar('uid'), 'write')) {
                //no write permission to updating exist item -> error
                $vars[0][$key]->setErrors(E_XOONIPS_UPDATE_CERTIFY_REQUEST_LOCKED, "can't update locked item(".$vars[0][$key]->getUpdateItemId().')');
                $error = true;
                break;
            }

            $basic = &$vars[0][$key]->getVar('basic');
            $itemtype = &$itemtype_handler->get($basic->get('item_type_id'));
            $handler = &xoonips_gethandler($itemtype->get('name'), 'import_item');
            $handler->import($vars[0][$key]);
            $error = $error || count($vars[0][$key]->getErrors()) > 0;
        }

        if ($error) {
            $transaction->rollback();
        } else {
            foreach (array_keys($vars[0]) as $key) {
                $basic = &$vars[0][$key]->getVar('basic');
                $itemtype = &$itemtype_handler->get($basic->get('item_type_id'));
                $handler = &xoonips_gethandler($itemtype->get('name'), 'import_item');
                $handler->onImportFinished($vars[0][$key], $vars[0]);
                $error = $error || count($vars[0][$key]->getErrors()) > 0;
            }
            $transaction->commit();

            $this->_remove_files();
        }

        $success['import_items'] = &$vars[0];
        $response->setResult(!$error);
        $response->setSuccess($success);
    }

    /**
     * remove all deleted(is_deleted=1) files from file system.
     */
    public function _remove_files()
    {
        $handler = &xoonips_getormhandler('xoonips', 'file');
        $criteria = new Criteria('is_deleted', 1);
        $delete_files = &$handler->getObjects($criteria);
        if ($delete_files) {
            foreach ($delete_files as $file) {
                $handler->deleteFile($file);
            }
        }
    }
}
