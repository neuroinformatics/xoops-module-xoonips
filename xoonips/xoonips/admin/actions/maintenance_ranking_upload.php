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

// check token ticket
require_once '../class/base/gtickets.php';
$ticket_area = 'xoonips_admin_maintenance_ranking';
if (!$xoopsGTicket->check(true, $ticket_area, false)) {
    redirect_header($xoonips_admin['mypage_url'], 3, $xoopsGTicket->getErrors());
    exit();
}

$keys = array(
    'ranking_upload_file' => array('d', true, true),
);
$vals = xoonips_admin_get_requests('files', $keys);
// uploaded file check
$uploaded_file = $vals['ranking_upload_file'];
if ($uploaded_file['name'] == '' || $uploaded_file['size'] == 0) {
    redirect_header($xoonips_admin['mypage_url'], 3, _AM_XOONIPS_MSG_ILLACCESS);
    exit();
}

// extract sum
$file_path = $uploaded_file['tmp_name'];
$admin_ranking_handler = &xoonips_gethandler('xoonips', 'admin_ranking');
if (!$admin_ranking_handler->load_sum_file($file_path)) {
    redirect_header($xoonips_admin['mypage_url'], 3, _AM_XOONIPS_MAINTENANCE_RANKING_LOCKED);
    exit();
}

redirect_header($xoonips_admin['mypage_url'], 1, _AM_XOONIPS_MSG_DBUPDATED);
