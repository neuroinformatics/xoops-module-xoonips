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
require_once XOOPS_ROOT_PATH.'/modules/xnpstimulus/include/view.php';
require_once XOOPS_ROOT_PATH.'/modules/xnpstimulus/iteminfo.php';

/**
 * @brief Handler object that create,insert,update,get,delete XNPStimulusCompo object.
 */
class XNPStimulusCompoHandler extends XooNIpsItemInfoCompoHandler
{
    public function __construct(&$db)
    {
        parent::__construct($db, 'xnpstimulus');
    }

    public function &create()
    {
        $stimulus = new XNPStimulusCompo();

        return $stimulus;
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
            return 'xnpstimulus_transfer_item_detail.html';
        case XOONIPS_TEMPLATE_TYPE_TRANSFER_ITEM_LIST:
            return 'xnpstimulus_transfer_item_list.html';
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
        $stimulus = &$this->get($item_id);
        if (!is_object($stimulus)) {
            return array();
        }
        $result = $this->getBasicTemplateVar($type, $stimulus, $uid);

        $textutil = &xoonips_getutility('text');
        $detail = &$stimulus->getVar('detail');
        $result['detail'] = $detail->getVarArray('s');
        $result['detail']['stimulus_type'] = $textutil->html_special_chars($this->get_stimulus_type_label($detail->getVar('stimulus_type', 's')));
        $result['detail']['stimulus_type_value'] = $detail->getVar('stimulus_type', 's');
        if ($detail->getVar('use_cc', 'n')) {
            $result['detail']['rights'] = $detail->getVar('rights', 'n');
        }

        switch ($type) {
        case XOONIPS_TEMPLATE_TYPE_ITEM_LIST:
            $result['developer'] = array();
            foreach ($stimulus->getVar('developer') as $developer) {
                $result['developer'][] = $developer->getVarArray('s');
            }

            return $result;
        case XOONIPS_TEMPLATE_TYPE_TRANSFER_ITEM_DETAIL:
        case XOONIPS_TEMPLATE_TYPE_ITEM_DETAIL:
        case XOONIPS_TEMPLATE_TYPE_TRANSFER_ITEM_LIST:
            $result['xnpstimulus_developer'] = xoonips_get_multiple_field_template_vars($detail->getDevelopers(), 'xnpstimulus', 'developer');

            if (is_array($stimulus->getVar('preview'))) {
                $result['detail']['previews'] = array();
                foreach ($stimulus->getVar('preview') as $preview) {
                    $result['detail']['previews'][] = $this->getPreviewTemplateVar($preview);
                }
            }

            $stimulus_data = $stimulus->getVar('stimulus_data');
            if ($stimulus_data->get('item_id') == $item_id) {
                $result['detail']['stimulus_data'] = $this->getAttachmentTemplateVar($stimulus->getVar('stimulus_data'));
            }

            return $result;
        }

        return $result;
    }

    public function get_stimulus_type_label($type)
    {
        $keyval = xnpstimulus_get_type_array();

        return $keyval[$type]; //"TODO convert type name '{$type}' to display name";
    }
}

/**
 * @brief Data object that have one ore more XooNIpsTableObject for Stimulus type.
 */
class XNPStimulusCompo extends XooNIpsItemInfoCompo
{
    public function __construct()
    {
        parent::__construct('xnpstimulus');
    }
}
