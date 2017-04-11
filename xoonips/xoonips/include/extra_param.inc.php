<?php

// $Revision: 1.1.2.11 $
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

/**
 * @return array associative array of extra parameters
 */
function xoonips_extra_param_restore()
{
    $formdata = &xoonips_getutility('formdata');
    $extra_param_name = $formdata->getValueArray('post', 'extra_param_name', 's', false);
    $extra_params = array();
    foreach ($extra_param_name as $name) {
        if (!isset($_POST[$name])) {
            continue;
        }
        if (is_array($_POST[$name])) {
            $extra_params[$name] = $formdata->getValueArray('post', $name, 's', false);
        } else {
            $extra_params[$name] = $formdata->getValue('post', $name, 's', false);
        }
    }
    if (!empty($extra_params)) {
        return $extra_params;
    }
  // try to get serialized extra_param request
  $extra_param = $formdata->getValue('post', 'extra_param', 's', false);
    $extra_params = @unserialize($extra_param);
    if (is_array($extra_params)) {
        return $extra_params;
    }

    return array();
}
