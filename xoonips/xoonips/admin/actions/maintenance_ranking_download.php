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

require_once '../class/base/gtickets.php';

// get requests
$request_keys = array(
  'ranking_download_file' => array(
    's',
    false,
    true,
  ),
);
$request_vals = xoonips_admin_get_requests('both', $request_keys);
$filename = $request_vals['ranking_download_file'];
if ($filename == '') {
    redirect_header($xoonips_admin['mypage_url'], 3, _AM_XOONIPS_MSG_ILLACCESS);
    exit();
}

// check token ticket for pathinfo
$ticket_area = 'xoonips_admin_maintenance_ranking';
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (!$xoopsGTicket->check(false, $ticket_area, false)) {
        redirect_header($xoonips_admin['mypage_url'], 3, $xoopsGTicket->getErrors());
        exit();
    }
}

$download = &xoonips_getutility('download');
if (!$download->check_pathinfo($filename)) {
    // reload for KHTML based browser
  $url = $xoonips_admin['mypage_url'];
    $url .= '&action=download';
    $url .= '&ranking_download_file='.$filename;
    $url .= '&'.$xoopsGTicket->getTicketParamString(__LINE__, true, 10, $ticket_area);
    $url = $download->append_pathinfo($url, $filename);
    header('Location: '.$url);
    exit();
}

// logic
$admin_ranking_handler = &xoonips_gethandler('xoonips', 'admin_ranking');
$zipfile_path = $admin_ranking_handler->create_sum_file();

if ($zipfile_path === false) {
    redirect_header($xoonips_admin['mypage_url'], 3, _AM_XOONIPS_MAINTENANCE_RANKING_LOCKED);
    exit();
}

// set file removing on shutdown
function on_shutdown()
{
    global $zipfile_path;
    unlink($zipfile_path);
}
register_shutdown_function('on_shutdown');

// download
$download->download_file($zipfile_path, $filename, 'application/x-zip');
