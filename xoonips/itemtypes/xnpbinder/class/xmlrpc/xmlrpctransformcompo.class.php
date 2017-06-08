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

defined('XOOPS_ROOT_PATH') || exit('XOOPS root path not defined');

require_once dirname(dirname(__DIR__)).'/include/view.php';

/**
 * XmlRpcTransform composer class for Binder type.
 */
class XNPBinderXmlRpcTransformCompo extends XooNIpsXmlRpcTransformCompo
{
    public function __construct()
    {
        parent::__construct('xnpbinder');
    }

    /**
     * @brief check that each field has valid value.
     *
     * @param[in] $in_array associative array of item
     * @param[out] $error XooNIpsError to add error
     * @retval ture valid
     * @retval false some invalid fields
     */
    public function checkFields($in_array, &$error)
    {
        parent::checkFields($in_array, $fields);
        $result = true; //set false if error

        $basic_handler = &xoonips_getormhandler('xoonips', 'item_basic');
        $item_ids = array();
        foreach ($in_array['detail_field'] as $field) {
            if (trim($field['name']) != 'item_id') {
                continue;
            }
            $basic = &$basic_handler->get($field['value']);
            if (!$basic) {
                $error->add(XNPERR_INVALID_PARAM, 'item('.$field['value'].') is not exists');
                $result = false;
                continue;
            }
            if (ITID_INDEX == $basic->get('item_type_id')) {
                $error->add(XNPERR_INVALID_PARAM, 'binder can not have index');
                $result = false;
                continue;
            }
            $item_ids[] = $field['value'];
        }

        $index_ids = $in_array['indexes'];

        // use following functions defined in view.php
        if (xnpbinder_no_binder_item($item_ids)) {
            $error->add(XNPERR_INVALID_PARAM, 'binder needs at least one item');
            $result = false;
        }
        if (xnpbinder_public_binder_has_not_public_item($item_ids, $index_ids)) {
            $error->add(XNPERR_INVALID_PARAM, 'public binder cannot have private and group items');
            $result = false;
        }
        if (xnpbinder_group_binder_has_private_item($item_ids, $index_ids)) {
            $error->add(XNPERR_INVALID_PARAM, 'group binder cannot have private item ');
            $result = false;
        }

        return $result;
    }
}
