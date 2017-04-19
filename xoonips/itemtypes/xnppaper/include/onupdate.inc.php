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

//  Update script for XooNIps Paper item type module
function xoops_module_update_xnppaper($xoopsMod, $oldversion)
{
    global $xoopsDB;
    switch ($oldversion) {
    // remember that version is multiplied with 100 to get an integer
    case 200:
    case 310:
        $sql = 'ALTER TABLE '.$xoopsDB->prefix('xnppaper_item_detail').' TYPE = innodb';
        $result = $xoopsDB->query($sql);
        if (!$result) {
            echo '&nbsp;&nbsp;'.$xoopsDB->error().'<br />';

            return false;
        }
    case 311:
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
        // support authors
        $key_name = 'paper_id';
        $table_detail = 'xnppaper_item_detail';
        $table_author = 'xnppaper_author';

        $sql = 'CREATE TABLE '.$xoopsDB->prefix($table_author).' (';
        $sql .= '`paper_author_id` int(10) unsigned NOT NULL auto_increment,';
        $sql .= '`paper_id` int(10) unsigned NOT NULL,';
        $sql .= '`author` varchar(255) NOT NULL,';
        $sql .= '`author_order` int(10) unsigned NOT NULL default \'0\',';
        $sql .= '  PRIMARY KEY  (`paper_author_id`)';
        $sql .= ') TYPE=InnoDB';
        $result = $xoopsDB->query($sql);
        if (!$result) {
            echo '&nbsp;&nbsp;'.$xoopsDB->error().'<br />';

            return false;
        }

        $result = $xoopsDB->query('select '.$key_name.',author from '.$xoopsDB->prefix($table_detail).' where author!=\'\'');
        while (list($id, $author) = $xoopsDB->fetchRow($result)) {
            $author_array = array_map('trim', explode("\n", $author));
            $i = 0;
            foreach ($author_array as $val) {
                if (empty($val)) {
                    continue;
                }
                $sql = 'insert into '.$xoopsDB->prefix($table_author);
                $sql .= '('.$key_name.',author,author_order) values (';
                $sql .= $id.','.$xoopsDB->quoteString($val).','.$i.')';
                if ($xoopsDB->queryF($sql) == false) {
                    echo '&nbsp;&nbsp;'.$xoopsDB->error().'<br />';

                    return false;
                }
                ++$i;
            }
        }

        $sql = 'ALTER TABLE '.$xoopsDB->prefix($table_detail).' DROP COLUMN author';
        $result = $xoopsDB->query($sql);
        if (!$result) {
            echo '&nbsp;&nbsp;'.$xoopsDB->error().'<br />';

            return false;
        }
    case 340:
    default:
    }

    return true;
}
