<?php

// $Revision: 1.1.4.1.2.3 $
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

// title
$title = _AM_XOONIPS_MAINTENANCE_RANKING_TITLE;
$description = _AM_XOONIPS_MAINTENANCE_RANKING_DESC;

// breadcrumbs
$breadcrumbs = array(
    array(
        'type' => 'top',
        'label' => _AM_XOONIPS_TITLE,
        'url' => $xoonips_admin['admin_url'].'/',
    ),
    array(
        'type' => 'link',
        'label' => _AM_XOONIPS_MAINTENANCE_TITLE,
        'url' => $xoonips_admin['myfile_url'],
    ),
    array(
        'type' => 'label',
        'label' => $title,
        'url' => '',
    ),
);

// token ticket
require_once '../class/base/gtickets.php';
$ticket_area = 'xoonips_admin_maintenance_ranking';
$token_ticket = $xoopsGTicket->getTicketHtml(__LINE__, 1800, $ticket_area);

// max file size
$max_file_size = ini_get('upload_max_filesize');
if (!is_numeric($max_file_size)) {
    if (strpos($max_file_size, 'M') !== false) {
        $max_file_size = intval($max_file_size) * 1024 * 1024;
    } elseif (strpos($max_file_size, 'K') !== false) {
        $max_file_size = intval($max_file_size) * 1024;
    } elseif (strpos($max_file_size, 'G') !== false) {
        $max_file_size = intval($max_file_size) * 1024 * 1024 * 1024;
    } else {
        exit();
    }
}

// download
$download_fname = 'ranking'.date('YmdHis').'.zip';
// upload
// clear
$config_keys = array(
    'ranking_sum_start' => 'i',
    'ranking_sum_last_update' => 'i',
);
$config_values = xoonips_admin_get_configs($config_keys, 'n');
$sum_start = $config_values['ranking_sum_start'];
$sum_last_update = $config_values['ranking_sum_last_update'];
if ($sum_start != 0 && $sum_last_update != 0) {
    $clear_message = sprintf(_AM_XOONIPS_MAINTENANCE_RANKING_CLEAR_MESSAGE, date('Y/m/d', $sum_start), date('Y/m/d', $sum_last_update));
} else {
    $clear_message = _AM_XOONIPS_MAINTENANCE_RANKING_CLEAR_EMPTY;
}

// templates
require_once '../class/base/pattemplate.class.php';
$tmpl = new PatTemplate();
$tmpl->setBaseDir('templates');
$tmpl->readTemplatesFromFile('maintenance_ranking.tmpl.html');

// assign template variables
$tmpl->addVar('header', 'TITLE', $title);
$tmpl->setAttribute('description', 'visibility', 'visible');
$tmpl->addVar('description', 'DESCRIPTION', $description);
$tmpl->setAttribute('breadcrumbs', 'visibility', 'visible');
$tmpl->addRows('breadcrumbs_items', $breadcrumbs);
$tmpl->addVar('main', 'token_ticket', $token_ticket);
$tmpl->addVar('main', 'max_file_size', $max_file_size);
$tmpl->addVar('main', 'download', _AM_XOONIPS_LABEL_DOWNLOAD);
$tmpl->addVar('main', 'upload', _AM_XOONIPS_LABEL_UPLOAD);
$tmpl->addVar('main', 'clear', _AM_XOONIPS_LABEL_CLEAR);
$tmpl->addVar('main', 'file_title', _AM_XOONIPS_MAINTENANCE_RANKING_FILE_TITLE);
$tmpl->addVar('main', 'note', _AM_XOONIPS_MAINTENANCE_RANKING_NOTE);
// >> download
$tmpl->addVar('main', 'download_title', _AM_XOONIPS_MAINTENANCE_RANKING_DOWNLOAD_TITLE);
$tmpl->addVar('main', 'download_desc', _AM_XOONIPS_MAINTENANCE_RANKING_DOWNLOAD_DESC);
$tmpl->addVar('main', 'download_fname', $download_fname);
// >> upload
$tmpl->addVar('main', 'upload_title', _AM_XOONIPS_MAINTENANCE_RANKING_UPLOAD_TITLE);
$tmpl->addVar('main', 'upload_desc', _AM_XOONIPS_MAINTENANCE_RANKING_UPLOAD_DESC);
// >> clear
$tmpl->addVar('main', 'clear_title', _AM_XOONIPS_MAINTENANCE_RANKING_CLEAR_TITLE);
$tmpl->addVar('main', 'clear_desc', _AM_XOONIPS_MAINTENANCE_RANKING_CLEAR_DESC);
$tmpl->addVar('main', 'clear_message', $clear_message);

// display
xoops_cp_header();
$tmpl->displayParsedTemplate('main');
xoops_cp_footer();
