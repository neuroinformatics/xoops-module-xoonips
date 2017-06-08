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

require_once XOOPS_ROOT_PATH.'/class/xoopsblock.php';

// check token ticket
require_once '../class/base/gtickets.php';
$ticket_area = 'xoonips_admin_system_basic';
if (!$xoopsGTicket->check(true, $ticket_area, false)) {
    redirect_header($xoonips_admin['mypage_url'], 3, $xoopsGTicket->getErrors());
    exit();
}

// get requests
$post_keys = array(
    'moderator_gid' => array('i', false, true),
    'upload_dir' => array('s', false, true),
    'magic_file_path' => array('s', false, true),
);
$post_vals = xoonips_admin_get_requests('post', $post_keys);

// set config keys
$config_keys = array();
foreach ($post_keys as $key => $attributes) {
    list($data_type, $is_array, $required) = $attributes;
    $config_keys[$key] = $data_type;
}
// get old configs
$config_vals = xoonips_admin_get_configs($config_keys, 'e');

function update_block_permissions($old_gid, $new_gid)
{
    // get handlers
    $gperm_handler = &xoops_gethandler('groupperm');
    $module_handler = &xoops_gethandler('module');
    $module = &$module_handler->getByDirname('xoonips');
    $mid = $module->getVar('mid');
    $block_objs = &XoopsBlock::getByModule($mid);
    foreach ($block_objs as $block_obj) {
        // find moderator menu block
        if ($block_obj->getVar('show_func') == 'b_xoonips_moderator_show') {
            $bid = $block_obj->getVar('bid');
            // if old_gid don't have module admin right,
            // delete the right to access from old_gid.
            if (!$gperm_handler->checkRight('module_admin', $mid, $old_gid)) {
                $criteria = new CriteriaCompo();
                $criteria->add(new Criteria('gperm_groupid', $old_gid));
                $criteria->add(new Criteria('gperm_itemid', $bid));
                $criteria->add(new Criteria('gperm_name', 'block_read'));
                $gperm_handler->deleteAll($criteria);
            }
            // if there is no right to access moderator block in new_gid,
            // the right gives new_gid.
            if (!$gperm_handler->checkRight('block_read', $bid, $new_gid)) {
                $gperm_handler->addRight('block_read', $bid, $new_gid);
            }
            break;
        }
    }
}

// update db values
foreach ($config_keys as $key => $type) {
    xoonips_admin_set_config($key, $post_vals[$key], $type);
}

// update block permissions
update_block_permissions($config_vals['moderator_gid'], $post_vals['moderator_gid']);

redirect_header($xoonips_admin['mypage_url'], 3, _AM_XOONIPS_MSG_DBUPDATED);
