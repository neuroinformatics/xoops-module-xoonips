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

defined('XOOPS_ROOT_PATH') || exit('XOOPS root path not defined');

require_once XOOPS_ROOT_PATH.'/modules/xoonips/class/xoonips_compo_item.class.php';
require_once XOOPS_ROOT_PATH.'/modules/xnpmodel/include/view.php';
require_once XOOPS_ROOT_PATH.'/modules/xnpmodel/iteminfo.php';

/**
 * @brief Handler object that create,insert,update,get,delete XNPModelCompo object.
 */
class XNPModelCompoHandler extends XooNIpsItemInfoCompoHandler
{
    public function __construct(&$db)
    {
        parent::__construct($db, 'xnpmodel');
    }

    public function &create()
    {
        $model = new XNPModelCompo();

        return $model;
    }

    /**
     * return template filename.
     *
     * @param string $type defined symbol
     *                     XOONIPS_TEMPLATE_TYPE_TRANSFER_ITEM_DETAIL
     *                     or XOONIPS_TEMPLATE_TYPE_TRANSFER_ITEM_LISTL
     *
     * @return string filename
     */
    public function getTemplateFileName($type)
    {
        switch ($type) {
        case XOONIPS_TEMPLATE_TYPE_TRANSFER_ITEM_DETAIL:
            return 'xnpmodel_transfer_item_detail.html';
        case XOONIPS_TEMPLATE_TYPE_TRANSFER_ITEM_LIST:
            return 'xnpmodel_transfer_item_list.html';
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
        $model = &$this->get($item_id);
        if (!is_object($model)) {
            return array();
        }
        $result = $this->getBasicTemplateVar($type, $model, $uid);

        $textutil = &xoonips_getutility('text');
        $detail = &$model->getVar('detail');
        switch ($type) {
        case XOONIPS_TEMPLATE_TYPE_ITEM_LIST:
            $result['creator'] = array();
            foreach ($model->getVar('creator') as $creator) {
                $result['creator'][] = $creator->getVarArray('s');
            }

            return $result;
        case XOONIPS_TEMPLATE_TYPE_TRANSFER_ITEM_DETAIL:
        case XOONIPS_TEMPLATE_TYPE_ITEM_DETAIL:
        case XOONIPS_TEMPLATE_TYPE_TRANSFER_ITEM_LIST:
            $result['xnpmodel_creator'] = xoonips_get_multiple_field_template_vars($detail->getCreators(), 'xnpmodel', 'creator');
            $result['detail'] = $detail->getVarArray('s');
            $result['detail']['model_type'] = $textutil->html_special_chars($this->get_model_type_label($detail->getVar('model_type', 's')));
            $result['detail']['model_type_value'] = $detail->getVar('model_type', 's');

            if ($detail->getVar('use_cc', 'n')) {
                $result['detail']['rights'] = $detail->getVar('rights', 'n');
            }

            if (is_array($model->getVar('preview'))) {
                $result['detail']['previews'] = array();
                foreach ($model->getVar('preview') as $preview) {
                    $result['detail']['previews'][] = $this->getPreviewTemplateVar($preview);
                }
            }

            $model_data = $model->getVar('model_data');
            if ($model_data->get('item_id') == $item_id) {
                $result['detail']['model_data'] = $this->getAttachmentTemplateVar($model->getVar('model_data'));
            }

            return $result;
        }

        return $result;
    }

    public function get_model_type_label($type)
    {
        $keyval = xnpmodel_get_type_array();

        return $keyval[$type]; //"TODO convert type name '{$type}' to display name";
    }
}

/**
 * @brief Data object that have one ore more XooNIpsTableObject for Model type.
 */
class XNPModelCompo extends XooNIpsItemInfoCompo
{
    public function __construct()
    {
        parent::__construct('xnpmodel');
    }
}
