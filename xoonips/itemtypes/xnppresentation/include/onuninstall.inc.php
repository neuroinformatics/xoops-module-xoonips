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

//  Uninstall script for XooNIps Presentation item type module
function xoops_module_uninstall_xnppresentation($xoopsMod)
{
    global $xoopsDB;

    $item_type_id = -1;
    $table = $xoopsDB->prefix('xoonips_item_type');
    $mid = $xoopsMod->getVar('mid');
    $sql = "SELECT item_type_id FROM $table where mid = $mid";
    $result = $xoopsDB->query($sql);
    if ($result) {
        list($item_type_id) = $xoopsDB->fetchRow($result);
    } else {
        echo mysql_error();
        echo $sql;

        return false;
    }

    // make item_status.is_deleted to be Deleted
    $table = $xoopsDB->prefix('xoonips_item_basic');
    $sql = "SELECT item_id from ${table} WHERE item_type_id = $item_type_id";
    $result = $xoopsDB->query($sql);
    if (!$result) {
        echo mysql_error();
        echo $sql;

        return false;
    }
    $ids = array();
    while (list($item_id) = $xoopsDB->fetchRow($result)) {
        $ids[] = $item_id;
    }
    if (count($ids) > 0) {
        $table = $xoopsDB->prefix('xoonips_item_status');
        $sql = "UPDATE ${table} SET deleted_timestamp=UNIX_TIMESTAMP(NOW()), is_deleted=1 WHERE item_id in ( ".implode(',', $ids).')';
        if ($xoopsDB->query($sql) == false) {
            echo mysql_error();
            echo $sql;

            return false;
        }
    }

    // remove basic information
    $table = $xoopsDB->prefix('xoonips_item_basic');
    $sql = "DELETE FROM $table where item_type_id = $item_type_id";
    if ($xoopsDB->query($sql) == false) {
        echo mysql_error();
        echo $sql;

        return false;
    }

    // unregister itemtype
    $table = $xoopsDB->prefix('xoonips_item_type');
    $mid = $xoopsMod->getVar('mid');
    $sql = "DELETE FROM $table where mid = $mid";
    if ($xoopsDB->query($sql) == false) {
        // cannot unregister itemtype
        return false;
    }
    $table = $xoopsDB->prefix('xoonips_file_type');
    $sql = "DELETE FROM $table where mid = $mid";
    if ($xoopsDB->query($sql) == false) {
        // cannot unregister filetype
        return false;
    }

    return true;
}
