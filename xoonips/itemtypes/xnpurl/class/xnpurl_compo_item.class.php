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
require_once XOOPS_ROOT_PATH.'/modules/xnpurl/iteminfo.php';

/**
 * @brief Handler object that create,insert,update,get,delete XNPUrlCompo object.
 */
class XNPUrlCompoHandler extends XooNIpsItemInfoCompoHandler
{
    public function __construct(&$db)
    {
        parent::__construct($db, 'xnpurl');
    }

    public function &create()
    {
        $url = new XNPUrlCompo();

        return $url;
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
            return 'xnpurl_transfer_item_detail.html';
        case XOONIPS_TEMPLATE_TYPE_TRANSFER_ITEM_LIST:
            return 'xnpurl_transfer_item_list.html';
        default:
            return '';
        }
    }

    /**
     * return template variables of item.
     *
     * @param string $type defined symbol
     *                     XOONIPS_TEMPLATE_TYPE_TRANSFER_ITEM_DETAIL
     *                     , XOONIPS_TEMPLATE_TYPE_ITEM_LIST
     *                     , XOONIPS_TEMPLATE_TYPE_ITEM_DETAIL
     *                     or XOONIPS_TEMPLATE_TYPE_TRANSFER_ITEM_LISTL
     *
     * @return array of template variables
     */
    public function getTemplateVar($type, $item_id, $uid)
    {
        $url = &$this->get($item_id);
        if (!is_object($url)) {
            return array();
        }
        $result = $this->getBasicTemplateVar($type, $url, $uid);

        $detail = &$url->getVar('detail');
        $result['detail'] = $detail->getVarArray('s');
        $result['detail']['url'] = preg_replace('/[\\x00-\\x20\\x22\\x27]/', '', $detail->getVar('url', 's'));
        $url_banner_file = $url->getVar('url_banner_file');
        if ($url_banner_file->get('item_id') == $item_id) {
            $result['detail']['banner_image_url'] = XOOPS_URL
                .'/modules/xoonips/image.php?file_id='
                .$url_banner_file->get('file_id');
        }

        return $result;
    }
}

/**
 * @brief Data object that have one ore more XooNIpsTableObject for Url type.
 */
class XNPUrlCompo extends XooNIpsItemInfoCompo
{
    public function __construct()
    {
        parent::__construct('xnpurl');
    }
}
