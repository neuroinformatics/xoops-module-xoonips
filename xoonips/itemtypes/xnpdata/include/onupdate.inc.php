<?php

// $Revision: 1.1.4.1.2.8 $
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

//  Update script for XooNIps Data item type module
function xoops_module_update_xnpdata($xoopsMod, $oldversion)
{
    global $xoopsDB;
    $table = $xoopsDB->prefix('xnpdata_item_detail');

    echo '<code>Updating modules...</code><br />';
    switch ($oldversion) {
    // remember that version is multiplied with 100 to get an integer
  case 200:
  case 310:
    $sql = 'ALTER TABLE '.$xoopsDB->prefix('xnpdata_item_detail').' TYPE = innodb';
    $result = $xoopsDB->query($sql);
    if (!$result) {
        echo '&nbsp;&nbsp;'.$xoopsDB->error().'<br />';

        return false;
    }
  case 311:
    $sql = 'ALTER TABLE '.$xoopsDB->prefix('xnpdata_item_detail').' ADD COLUMN attachment_dl_notify int(1) unsigned default 0 ';
    $result = $xoopsDB->query($sql);
    if (!$result) {
        echo '&nbsp;&nbsp;'.$xoopsDB->error().'<br />';

        return false;
    }
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
    // support experimenters
    $key_name = 'data_id';
    $table_detail = 'xnpdata_item_detail';
    $table_experimenter = 'xnpdata_experimenter';

    $sql = 'CREATE TABLE '.$xoopsDB->prefix($table_experimenter).' (';
    $sql .= '`data_experimenter_id` int(10) unsigned NOT NULL auto_increment,';
    $sql .= '`data_id` int(10) unsigned NOT NULL,';
    $sql .= '`experimenter` varchar(255) NOT NULL,';
    $sql .= '`experimenter_order` int(10) unsigned NOT NULL default \'0\',';
    $sql .= '  PRIMARY KEY  (`data_experimenter_id`)';
    $sql .= ') TYPE=InnoDB';
    $result = $xoopsDB->query($sql);
    if (!$result) {
        echo '&nbsp;&nbsp;'.$xoopsDB->error().'<br />';

        return false;
    }

    $result = $xoopsDB->query('select '.$key_name.',experimenter from '.$xoopsDB->prefix($table_detail).' where experimenter!=\'\'');
    while (list($id, $experimenter) = $xoopsDB->fetchRow($result)) {
        $experimenter_array = array_map('trim', explode(',', $experimenter));
        $i = 0;
        foreach ($experimenter_array as $experimenter) {
            if (empty($experimenter)) {
                continue;
            }
            $sql = 'insert into '.$xoopsDB->prefix($table_experimenter);
            $sql .= '('.$key_name.',experimenter,experimenter_order) values (';
            $sql .= $id.','.$xoopsDB->quoteString($experimenter).','.$i.')';
            if ($xoopsDB->queryF($sql) == false) {
                echo '&nbsp;&nbsp;'.$xoopsDB->error().'<br />';

                return false;
            }
            ++$i;
        }
    }
    $sql = 'ALTER TABLE '.$xoopsDB->prefix($table_detail).' DROP COLUMN experimenter';
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
