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

//  Update script for XooNIps Url item type module
function xoops_module_update_xnpurl($xoopsMod, $oldversion)
{
    global $xoopsDB;

    echo '<code>Updating modules...</code><br />';
    switch ($oldversion) {
    case 200:
    case 310:
        $sql = 'ALTER TABLE '.$xoopsDB->prefix('xnpurl_item_detail').' TYPE = innodb';
        $result = $xoopsDB->query($sql);
        if (!$result) {
            echo 'ERROR: line='.__LINE__.' sql='.$sql.' '.$xoopsDB->error();
        }
    case 311:
    case 330:
    case 331:
    case 332:
        // Notice:
        //   version 333-339 are reserved number for future releases of
        //   RELENG_3_3 branch. don't change database structure after
        //   3.40 released.
    case 333:
    case 334:
    case 335:
    case 336:
    case 337:
    case 338:
    case 339:
        $sql = 'ALTER TABLE '.$xoopsDB->prefix('xnpurl_item_detail').' ADD COLUMN (url_count int(10) unsigned NOT NULL default 0)';
        $result = $xoopsDB->query($sql);
        if (!$result) {
            echo 'ERROR: line='.__LINE__.' sql='.$sql.' '.$xoopsDB->error();
        }
    case 340:
    default:
    }

    return true;
}
