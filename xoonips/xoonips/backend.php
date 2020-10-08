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
require 'include/common.inc.php';

function xoonips_get_backend()
{
    $formdata = &xoonips_getutility('formdata');
    $itemtype = $formdata->getValue('get', 'itemtype', 's', true);
    $action = $formdata->getValue('get', 'action', 's', true);
    // check item type name
    $item_type_handler = &xoonips_getormhandler('xoonips', 'item_type');
    $criteria = new CriteriaCompo(new Criteria('name', $itemtype));
    $criteria->add(new Criteria('mid', null, '!='));
    if (1 != $item_type_handler->getCount($criteria)) {
        die('illegal request');
    }
    // check action name
    if (!preg_match('/^[a-z][_a-z]*$/', $action)) {
        die('illegal request');
    }
    $backend = '../'.$itemtype.'/backend/'.$action.'.php';
    if (!file_exists($backend)) {
        die('illegal request');
    }

    return $backend;
}

require xoonips_get_backend();
