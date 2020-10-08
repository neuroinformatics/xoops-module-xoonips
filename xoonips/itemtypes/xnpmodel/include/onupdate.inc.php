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

function xoops_module_update_xnpmodel($xoopsMod, $oldversion)
{
    global $xoopsDB;
    $table = $xoopsDB->prefix('xnpmodel_item_detail');

    echo '<code>Updating modules...</code><br />';
    switch ($oldversion) {
    case 200:
    case 310:
        $sql = 'ALTER TABLE '.$xoopsDB->prefix('xnpmodel_item_detail').' TYPE = innodb';
        $result = $xoopsDB->query($sql);
        if (!$result) {
            echo '&nbsp;&nbsp;'.$xoopsDB->error().'<br />';

            return false;
        }
        // no break
    case 311:
        $sql = 'ALTER TABLE '.$xoopsDB->prefix('xnpmodel_item_detail').' ADD COLUMN attachment_dl_notify int(1) unsigned default 0 ';
        $result = $xoopsDB->query($sql);
        if (!$result) {
            echo '&nbsp;&nbsp;'.$xoopsDB->error().'<br />';

            return false;
        }
        // no break
    case 312:
    case 330:
    case 331:
    case 332:
    case 333:
    case 334:
    case 335:
    case 336:
    case 337:
    case 338:
    case 339:
        // support creators
        $key_name = 'model_id';
        $table_detail = 'xnpmodel_item_detail';
        $table_creator = 'xnpmodel_creator';

        $sql = 'CREATE TABLE '.$xoopsDB->prefix($table_creator).' (';
        $sql .= '`model_creator_id` int(10) unsigned NOT NULL auto_increment,';
        $sql .= '`model_id` int(10) unsigned NOT NULL,';
        $sql .= '`creator` varchar(255) NOT NULL,';
        $sql .= '`creator_order` int(10) unsigned NOT NULL default \'0\',';
        $sql .= '  PRIMARY KEY  (`model_creator_id`)';
        $sql .= ') TYPE=InnoDB';
        $result = $xoopsDB->query($sql);
        if (!$result) {
            echo '&nbsp;&nbsp;'.$xoopsDB->error().'<br />';

            return false;
        }

        $result = $xoopsDB->query('select '.$key_name.',creator from '.$xoopsDB->prefix($table_detail).' where creator!=\'\'');
        while (list($id, $creator) = $xoopsDB->fetchRow($result)) {
            $creator_array = array_map('trim', explode(',', $creator));
            $i = 0;
            foreach ($creator_array as $creator) {
                if (empty($creator)) {
                    continue;
                }
                $sql = 'insert into '.$xoopsDB->prefix($table_creator);
                $sql .= '('.$key_name.',creator,creator_order) values (';
                $sql .= $id.','.$xoopsDB->quoteString($creator).','.$i.')';
                if (false == $xoopsDB->queryF($sql)) {
                    echo '&nbsp;&nbsp;'.$xoopsDB->error().'<br />';

                    return false;
                }
                ++$i;
            }
        }
        $sql = 'ALTER TABLE '.$xoopsDB->prefix($table_detail).' DROP COLUMN creator';
        $result = $xoopsDB->query($sql);
        if (!$result) {
            echo '&nbsp;&nbsp;'.$xoopsDB->error().'<br />';

            return false;
        }
        // no break
    case 340:
    default:
    }

    return true;
}
