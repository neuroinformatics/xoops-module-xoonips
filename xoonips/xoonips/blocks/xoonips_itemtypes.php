<?php

// $Revision: 1.1.4.1.2.10 $
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

// xoonips itemtypes block
function b_xoonips_itemtypes_show()
{
    global $xoopsUser;

  // hide block if user is guest and public index viewing policy is 'platform'
  if (!is_object($xoopsUser)) {
      $xconfig_handler = &xoonips_getormhandler('xoonips', 'config');
      $target_user = $xconfig_handler->getValue('public_item_target_user');
      if ($target_user != 'all') {
          // 'platform'
      return false;
      }
  }

    $uid = is_object($xoopsUser) ? $xoopsUser->getVar('uid', 'n') : UID_GUEST;

  // hide block if user is invalid xoonips user
  $xsession_handler = &xoonips_getormhandler('xoonips', 'session');
    if (!$xsession_handler->validateUser($uid, false)) {
        return false;
    }

    require_once XOOPS_ROOT_PATH.'/modules/xoonips/include/lib.php';

  // get installed itemtypes
  $block = array();
    $block['explain'] = array();
    $item_type_handler = &xoonips_getormhandler('xoonips', 'item_type');
    $item_type_objs = &$item_type_handler->getObjectsSortByWeight();
    foreach ($item_type_objs as $item_type_obj) {
        $name = $item_type_obj->get('name');
        $file = XOOPS_ROOT_PATH.'/modules/'.$item_type_obj->get('viewphp');
        if (file_exists($file)) {
            require_once $file;
        }
        $fname = $name.'GetTopBlock';
        if (function_exists($fname)) {
            // call xxxGetTopBlock function in view.php
      $itemtype = $item_type_obj->getVarArray('s');
            $html = $fname($itemtype);
            if (!empty($html)) {
                $block['explain'][] = $html;
            }
        }
    }
    if (empty($block['explain'])) {
        // visible itemtype not found
    return false;
    }

    return $block;
}
