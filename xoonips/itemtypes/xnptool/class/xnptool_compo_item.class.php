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

if (!defined('XOOPS_ROOT_PATH')) {
    exit();
}

require_once XOOPS_ROOT_PATH.'/modules/xoonips/class/xoonips_compo_item.class.php';
require_once XOOPS_ROOT_PATH.'/modules/xnptool/include/view.php';
require_once XOOPS_ROOT_PATH.'/modules/xnptool/iteminfo.php';

/**
 * @brief Handler object that create,insert,update,get,delete XNPToolCompo object.
 */
class XNPToolCompoHandler extends XooNIpsItemInfoCompoHandler
{
    public function XNPToolCompoHandler(&$db)
    {
        parent::XooNIpsItemInfoCompoHandler($db, 'xnptool');
    }

    public function &create()
    {
        $tool = new XNPToolCompo();

        return $tool;
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
            return 'xnptool_transfer_item_detail.html';
        case XOONIPS_TEMPLATE_TYPE_TRANSFER_ITEM_LIST:
            return 'xnptool_transfer_item_list.html';
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
     *                        , XOONIPS_TEMPLATE_TYPE_ITEM_DETAIL
     *                        or XOONIPS_TEMPLATE_TYPE_ITEM_LIST
     * @param int    $item_id
     * @param int    $uid     user id who get item
     *
     * @return array of template variables
     */
    public function getTemplateVar($type, $item_id, $uid)
    {
        $tool = &$this->get($item_id);
        if (!is_object($tool)) {
            return array();
        }
        $result = $this->getBasicTemplateVar($type, $tool, $uid);

        $textutil = &xoonips_getutility('text');
        $detail = &$tool->getVar('detail');
        $result['detail'] = $detail->getVarArray('s');
        $result['detail']['tool_type'] = $textutil->html_special_chars($this->get_tool_type_label($detail->getVar('tool_type', 's')));
        $result['detail']['tool_type_value'] = $detail->getVar('tool_type', 's');
        if ($detail->getVar('use_cc', 'n')) {
            $result['detail']['rights'] = $detail->getVar('rights', 'n');
        }

        switch ($type) {
        case XOONIPS_TEMPLATE_TYPE_ITEM_LIST:
            $result['developer'] = array();
            foreach ($tool->getVar('developer') as $developer) {
                $result['developer'][] = $developer->getVarArray('s');
            }

            return $result;
        case XOONIPS_TEMPLATE_TYPE_TRANSFER_ITEM_DETAIL:
        case XOONIPS_TEMPLATE_TYPE_ITEM_DETAIL:
        case XOONIPS_TEMPLATE_TYPE_TRANSFER_ITEM_LIST:
            $result['xnptool_developer'] = xoonips_get_multiple_field_template_vars($detail->getDevelopers(), 'xnptool', 'developer');

            if (is_array($tool->getVar('preview'))) {
                $result['detail']['previews'] = array();
                foreach ($tool->getVar('preview') as $preview) {
                    $result['detail']['previews'][] = $this->getPreviewTemplateVar($preview);
                }
            }

            $tool_data = $tool->getVar('tool_data');
            if ($tool_data->get('item_id') == $item_id) {
                $result['detail']['tool_data'] = $this->getAttachmentTemplateVar($tool->getVar('tool_data'));
            }

            return $result;
        }

        return $result;
    }

    public function get_tool_type_label($type)
    {
        $keyval = xnptool_get_type_array();

        return $keyval[$type];
    }
}

/**
 * @brief Data object that have one ore more XooNIpsTableObject for Tool type.
 */
class XNPToolCompo extends XooNIpsItemInfoCompo
{
    public function XNPToolCompo()
    {
        parent::XooNIpsItemInfoCompo('xnptool');
    }
}
