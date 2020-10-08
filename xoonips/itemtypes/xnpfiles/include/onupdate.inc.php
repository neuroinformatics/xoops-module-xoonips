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

//  Update script for XooNIps File item type module
function xoops_module_update_xnpfiles($xoopsMod, $oldversion)
{
    global $xoopsDB;
    $table = $xoopsDB->prefix('xnpdata_item_detail');

    echo '<code>Updating modules...</code><br />';
    switch ($oldversion) {
    // remember that version is multiplied with 100 to get an integer
    case 200:
    case 310:
        //
        // correct xoopnips_files.file_type_id
        //
        // - update xoonips_file_type.file_type_name by 'conference_file' that has own module ID.
        // - get the file_type_id(*) of updated row.
        // - for each items that has mid of xnpconference, do bellow
        // - update xoopnips_files.file_type_id by the (*)
        //
        $module_handler = &xoops_gethandler('module');
        $module = &$module_handler->getByDirname('xnpfiles');
        $result = $xoopsDB->query('update '.$xoopsDB->prefix('xoonips_file_type').' set name=\'files_file\', display_name=\'Data file of Files\' where name=\'data_file\' and mid='.$module->mid());
        if (false == $result) {
            echo '&nbsp;&nbsp;'.$xoopsDB->error().'<br />';

            return false;
        }

        $result = $xoopsDB->query('select file_type_id from '.$xoopsDB->prefix('xoonips_file_type').' where mid='.$module->mid());
        if (false == $result) {
            echo '&nbsp;&nbsp;'.$xoopsDB->error().'<br />';

            return false;
        } elseif (0 == $xoopsDB->getRowsNum($result)) {
            echo '&nbsp;&nbsp;can\'t find row of file_type_id<br />';

            return false;
        }
        list($file_type_id) = $xoopsDB->fetchRow($result);

        $result = $xoopsDB->query('select item_type_id from '.$xoopsDB->prefix('xoonips_item_type').' where mid='.$module->mid());
        if (false == $result) {
            echo '&nbsp;&nbsp;'.$xoopsDB->error().'<br />';

            return false;
        } elseif (0 == $xoopsDB->getRowsNum($result)) {
            echo '&nbsp;&nbsp;'.$xoopsDB->error().'<br />';

            return false;
        }
        list($item_type_id) = $xoopsDB->fetchRow($result);

        $update_ids = array();
        // Item id that is updated.
        $result = $xoopsDB->query('select item_id from '.$xoopsDB->prefix('xoonips_item_basic')." where item_type_id=${item_type_id}");
        while (list($id) = $xoopsDB->fetchRow($result)) {
            if ($id) {
                $update_ids[] = $id;
            }
        }
        if (count($update_ids) > 0) {
            $result = $xoopsDB->query('update '.$xoopsDB->prefix('xoonips_file')." set file_type_id=${file_type_id} where item_id in (".implode(', ', $update_ids).')');
            if (false == $result) {
                echo '&nbsp;&nbsp;'.$xoopsDB->error().'<br />';

                return false;
            }
        }

        $sql = 'ALTER TABLE '.$xoopsDB->prefix('xnpfiles_item_detail').' TYPE = innodb';
        $result = $xoopsDB->query($sql);
        if (!$result) {
            echo '&nbsp;&nbsp;'.$xoopsDB->error().'<br />';

            return false;
        }
        // no break
    case 330:
    case 331:
    case 332:
    case 340:
    default:
    }

    return true;
}
