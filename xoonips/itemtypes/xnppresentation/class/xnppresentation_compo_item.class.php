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

if (!defined('XOOPS_ROOT_PATH')) {
    exit();
}

require_once XOOPS_ROOT_PATH.'/modules/xoonips/class/xoonips_compo_item.class.php';
require_once XOOPS_ROOT_PATH.'/modules/xnppresentation/include/view.php';
require_once XOOPS_ROOT_PATH.'/modules/xnppresentation/iteminfo.php';

/**
 * @brief Handler object that create,insert,update,get,delete XNPPresentationCompo object.
 */
class XNPPresentationCompoHandler extends XooNIpsItemInfoCompoHandler
{
    public function __construct(&$db)
    {
        parent::__construct($db, 'xnppresentation');
    }

    public function &create()
    {
        $presentation = new XNPPresentationCompo();

        return $presentation;
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
            return 'xnppresentation_transfer_item_detail.html';
        case XOONIPS_TEMPLATE_TYPE_TRANSFER_ITEM_LIST:
            return 'xnppresentation_transfer_item_list.html';
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
        $presentation = &$this->get($item_id);
        if (!is_object($presentation)) {
            return array();
        }
        $result = $this->getBasicTemplateVar($type, $presentation, $uid);

        $textutil = &xoonips_getutility('text');
        $detail = &$presentation->getVar('detail');
        $result['detail'] = $detail->getVarArray('s');
        $result['detail']['presentation_type'] = $textutil->html_special_chars($this->get_presentation_type_label($detail->get('presentation_type')));
        $result['detail']['presentation_type_value'] = $detail->getVar('presentation_type', 's');
        if ($detail->getVar('use_cc', 'n')) {
            $result['detail']['rights'] = $detail->getVar('rights', 'n');
        }

        switch ($type) {
        case XOONIPS_TEMPLATE_TYPE_ITEM_LIST:
        case XOONIPS_TEMPLATE_TYPE_TRANSFER_ITEM_LIST:
            $result['creator'] = array();
            foreach ($presentation->getVar('creator') as $creator) {
                $result['creator'][] = $creator->getVarArray('s');
            }

            return $result;
        case XOONIPS_TEMPLATE_TYPE_ITEM_DETAIL:
        case XOONIPS_TEMPLATE_TYPE_TRANSFER_ITEM_DETAIL:
            $result['xnppresentation_creator'] = xoonips_get_multiple_field_template_vars($detail->getCreators(), 'xnppresentation', 'creator');

            if (is_array($presentation->getVar('preview'))) {
                $result['detail']['previews'] = array();
                foreach ($presentation->getVar('preview') as $preview) {
                    $result['detail']['previews'][] = $this->getPreviewTemplateVar($preview);
                }
            }

            $presentation_file = $presentation->getVar('presentation_file');
            if ($presentation_file->get('item_id') == $item_id) {
                $result['detail']['presentation_file'] = $this->getAttachmentTemplateVar($presentation->getVar('presentation_file'));
            }

            return $result;
        }

        return $result;
    }

    public function get_presentation_type_label($type)
    {
        $keyval = xnppresentationGetTypes();

        return $keyval[$type]; //"TODO convert type name '{$type}' to display name";
    }
}

/**
 * @brief Data object that have one ore more XooNIpsTableObject for Presentation type.
 */
class XNPPresentationCompo extends XooNIpsItemInfoCompo
{
    public function __construct()
    {
        parent::__construct('xnppresentation');
    }
}
