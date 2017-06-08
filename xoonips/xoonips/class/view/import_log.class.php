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

require_once dirname(__DIR__).'/base/view.class.php';

class XooNIpsViewImportLog extends XooNIpsView
{
    public function __construct($params)
    {
        parent::__construct($params);
    }

    public function render()
    {
        global $xoopsOption, $xoopsConfig, $xoopsUser, $xoopsUserIsAdmin, $xoopsLogger, $xoopsTpl;

        $textutil = &xoonips_getutility('text');
        $xoopsOption['template_main'] = 'xoonips_import_log.html';
        if ($this->_params['result']) {
            require XOOPS_ROOT_PATH.'/header.php';
            $xoopsTpl->assign('result', $this->_params['result']);
            $xoopsTpl->assign('filename', $textutil->html_special_chars($this->_params['filename']));
            $xoopsTpl->assign('number_of_items', $this->_number_of_items());
            $xoopsTpl->assign('uname', $textutil->html_special_chars($this->_params['uname']));
            $xoopsTpl->assign('errors', $this->_params['errors']);
            $xoopsTpl->assign('log', $textutil->html_special_chars($this->_get_item_log()));
            require XOOPS_ROOT_PATH.'/footer.php';
        } else {
            require XOOPS_ROOT_PATH.'/header.php';
            $xoopsTpl->assign('result', false);
            $xoopsTpl->assign('filename', $this->_params['filename']);
            $xoopsTpl->assign('number_of_items', $this->_number_of_items());
            $xoopsTpl->assign('uname', $this->_params['uname']);
            $xoopsTpl->assign('errors', $this->_params['errors']);
            $xoopsTpl->assign('log', $this->_get_item_log());
            require XOOPS_ROOT_PATH.'/footer.php';
        }
    }

    public function _get_item_log()
    {
        $log = '';
        $item_type_handler = &xoonips_getormhandler('xoonips', 'item_type');
        foreach ($this->_params['import_items'] as $item) {
            $basic = &$item->getVar('basic');
            $itemtype = &$item_type_handler->get($basic->get('item_type_id'));
            $handler = &xoonips_gethandler($itemtype->get('name'), 'import_item');
            $log .= "\n\n[item]\n".$handler->getImportLog($item);
            foreach ($item->getErrors() as $e) {
                $log .= "\nerror $e";
            }

            foreach (array_merge($item->getDuplicateUnupdatableItemId(), $item->getDuplicateUpdatableItemId(), $item->getDuplicateLockedItemId()) as $item_id) {
                $log .= "\nwarning conflict with "
                    .xnpGetItemDetailURL($item_id);
            }
        }

        return $log;
    }

    public function _number_of_items()
    {
        return count($this->_params['import_items']);
    }

    public function _get_result_log()
    {
        $log = '';
        $item_type_handler = &xoonips_getormhandler('xoonips', 'item_type');
        foreach ($this->_params['import_items'] as $item) {
            foreach (array_unique($item->getErrorCodes()) as $code) {
                $log .= "\nerror ".$code.' '
                    .$item->getVar('pseudo_id');
            }
        }

        return $log;
    }
}
