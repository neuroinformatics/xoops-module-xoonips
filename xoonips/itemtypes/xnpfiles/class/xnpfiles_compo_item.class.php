<?php

// $Revision: 1.1.2.5 $
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

if (!defined('XOOPS_ROOT_PATH')) {
    exit();
}

require_once XOOPS_ROOT_PATH.'/modules/xoonips/class/xoonips_compo_item.class.php';
require_once XOOPS_ROOT_PATH.'/modules/xnpfiles/iteminfo.php';

/**
 * @brief Handler object that create,insert,update,get,delete XNPFilesCompo object.
 */
class XNPFilesCompoHandler extends XooNIpsItemInfoCompoHandler
{
    public function XNPFilesCompoHandler(&$db)
    {
        parent::XooNIpsItemInfoCompoHandler($db, 'xnpfiles');
    }

    public function &create()
    {
        $files = new XNPFilesCompo();

        return $files;
    }

    /**
     * return template filename.
     *
     * @param string $type defined symbol
     *                     XOONIPS_TEMPLATE_TYPE_TRANSFER_ITEM_DETAIL
     *                     or XOONIPS_TEMPLATE_TYPE_TRANSFER_ITEM_LISTL
     *
     * @return template filename
     */
    public function getTemplateFileName($type)
    {
        switch ($type) {
        case XOONIPS_TEMPLATE_TYPE_TRANSFER_ITEM_DETAIL:
            return 'xnpfiles_transfer_item_detail.html';
        case XOONIPS_TEMPLATE_TYPE_TRANSFER_ITEM_LIST:
            return 'xnpfiles_transfer_item_list.html';
        default:
            return '';
        }
    }

    /**
     * return template variables of item.
     *
     * @param string $type    defined symbol
     *                        XOONIPS_TEMPLATE_TYPE_TRANSFER_ITEM_DETAIL
     *                        , XOONIPS_TEMPLATE_TYPE_TRANSFER_ITEM_LIST
     *                        or XOONIPS_TEMPLATE_TYPE_ITEM_LIST
     * @param int    $item_id
     * @param int    $uid     user id who get item
     *
     * @return array of template variables
     */
    public function getTemplateVar($type, $item_id, $uid)
    {
        $files = &$this->get($item_id);
        if (!is_object($files)) {
            return array();
        }
        $result = $this->getBasicTemplateVar($type, $files, $uid);

        $detail = &$files->getVar('detail');
        $files_file = &$files->getVar('files_file');
        switch ($type) {
        case XOONIPS_TEMPLATE_TYPE_ITEM_LIST:
            $result['detail'] = $detail->getVarArray('s');
            $result['files_file'] = $files_file->getVarArray('s');

            return $result;
        case XOONIPS_TEMPLATE_TYPE_TRANSFER_ITEM_DETAIL:
        case XOONIPS_TEMPLATE_TYPE_ITEM_DETAIL:
        case XOONIPS_TEMPLATE_TYPE_TRANSFER_ITEM_LIST:
            $result['detail'] = $detail->getVarArray('s');
            $files_file = $files->getVar('files_file');
            if ($files_file->get('item_id') == $item_id) {
                $result['detail']['files_file'] = $this->getAttachmentTemplateVar($files->getVar('files_file'));
            }

            return $result;
        }

        return $result;
    }
}

/**
 * @brief Data object that have one ore more XooNIpsTableObject for Files type.
 */
class XNPFilesCompo extends XooNIpsItemInfoCompo
{
    public function XNPFilesCompo()
    {
        parent::XooNIpsItemInfoCompo('xnpfiles');
    }
}
