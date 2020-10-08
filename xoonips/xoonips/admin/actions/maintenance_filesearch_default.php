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

// title
$title = _AM_XOONIPS_MAINTENANCE_FILESEARCH_TITLE;
$description = _AM_XOONIPS_MAINTENANCE_FILESEARCH_DESC;

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

$textutil = &xoonips_getutility('text');

function filesearch_adddot($str)
{
    return '.'.$str;
}

$admin_file_handler = &xoonips_gethandler('xoonips', 'admin_file');
$modules = $admin_file_handler->getFileSearchPlugins();
$plugins = array();
$evenodd = 'odd';
foreach ($modules as $module) {
    $plugin['plugin'] = $textutil->html_special_chars($module['display_name']);
    $plugin['mimetype'] = $textutil->html_special_chars(implode(', ', $module['mime_type']));
    $extensions = array_map('filesearch_adddot', $module['extensions']);
    $plugin['suffixes'] = $textutil->html_special_chars(implode(', ', $extensions));
    $plugin['version'] = $textutil->html_special_chars($module['version']);
    $plugin['evenodd'] = $evenodd;
    $evenodd = ('even' == $evenodd) ? 'odd' : 'even';
    $plugins[] = $plugin;
}
$has_plugins = (0 == count($plugins)) ? false : true;

$file_count = $admin_file_handler->getCountFiles();

// templates
require_once '../class/base/pattemplate.class.php';
$tmpl = new PatTemplate();
$tmpl->setBaseDir('templates');
$tmpl->readTemplatesFromFile('maintenance_filesearch.tmpl.html');

// assign template variables
$tmpl->addGlobalVar('MYURL', $xoonips_admin['mypage_url']);
$tmpl->addVar('header', 'TITLE', $title);
$tmpl->setAttribute('description', 'visibility', 'visible');
$tmpl->addVar('description', 'DESCRIPTION', $description);
$tmpl->setAttribute('breadcrumbs', 'visibility', 'visible');
$tmpl->addRows('breadcrumbs_items', $breadcrumbs);
$tmpl->addVar('main', 'plugins_title', _AM_XOONIPS_MAINTENANCE_FILESEARCH_PLUGINS_TITLE);
$tmpl->addVar('plugins_empty', 'empty', _AM_XOONIPS_MAINTENANCE_FILESEARCH_PLUGINS_EMPTY);
$tmpl->addVar('main', 'plugin', _AM_XOONIPS_MAINTENANCE_FILESEARCH_LABEL_PLUGIN);
$tmpl->addVar('main', 'mimetype', _AM_XOONIPS_MAINTENANCE_FILESEARCH_LABEL_MIMETYPE);
$tmpl->addVar('main', 'suffix', _AM_XOONIPS_MAINTENANCE_FILESEARCH_LABEL_SUFFIX);
$tmpl->addVar('main', 'version', _AM_XOONIPS_MAINTENANCE_FILESEARCH_LABEL_VERSION);
if ($has_plugins) {
    $tmpl->addRows('plugins', $plugins);
} else {
    $tmpl->setAttribute('plugins', 'visibility', 'hidden');
    $tmpl->setAttribute('plugins_empty', 'visibility', 'visible');
}
$tmpl->addGlobalVar('RESCAN', _AM_XOONIPS_MAINTENANCE_FILESEARCH_LABEL_RESCAN);
$tmpl->addGlobalVar('RESCANNING', _AM_XOONIPS_MAINTENANCE_FILESEARCH_LABEL_RESCANNING);
$tmpl->addGlobalVar('FILE_COUNT_LABEL', _AM_XOONIPS_MAINTENANCE_FILESEARCH_RESCAN_LABEL_FILECOUNT);
$tmpl->addGlobalVar('FILE_COUNT', $file_count);
$tmpl->addGlobalVar('RESCAN_TITLE', _AM_XOONIPS_MAINTENANCE_FILESEARCH_RESCAN_TITLE);
$tmpl->addGlobalVar('RESCAN_INFO_TITLE', _AM_XOONIPS_MAINTENANCE_FILESEARCH_RESCAN_INFO_TITLE);
$tmpl->addGlobalVar('RESCAN_INFO_DESC', _AM_XOONIPS_MAINTENANCE_FILESEARCH_RESCAN_INFO_DESC);
$tmpl->addGlobalVar('RESCAN_INDEX_TITLE', _AM_XOONIPS_MAINTENANCE_FILESEARCH_RESCAN_INDEX_TITLE);
$tmpl->addGlobalVar('RESCAN_INDEX_DESC', _AM_XOONIPS_MAINTENANCE_FILESEARCH_RESCAN_INDEX_DESC);

// display
xoops_cp_header();
$tmpl->displayParsedTemplate('main');
xoops_cp_footer();
