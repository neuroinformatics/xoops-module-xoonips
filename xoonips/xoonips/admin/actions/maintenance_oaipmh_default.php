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
$title = _AM_XOONIPS_MAINTENANCE_OAIPMH_TITLE;
$description = _AM_XOONIPS_MAINTENANCE_OAIPMH_DESC;

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

// logic
$repo_handler = &xoonips_getormhandler('xoonips', 'oaipmh_repositories');
$results = &$repo_handler->getLastResults('s');
$evenodd = 'odd';
foreach (array_keys($results) as $id) {
    $results[$id]['evenodd'] = $evenodd;
    $evenodd = ('even' == $evenodd) ? 'odd' : 'even';
}
$has_results = true;
if (0 == count($results)) {
    $has_results = false;
}

// templates
require_once '../class/base/pattemplate.class.php';
$tmpl = new PatTemplate();
$tmpl->setBaseDir('templates');
$tmpl->readTemplatesFromFile('maintenance_oaipmh.tmpl.html');

// assign template variables
$tmpl->addVar('header', 'TITLE', $title);
$tmpl->setAttribute('description', 'visibility', 'visible');
$tmpl->addVar('description', 'DESCRIPTION', $description);
$tmpl->setAttribute('breadcrumbs', 'visibility', 'visible');
$tmpl->addRows('breadcrumbs_items', $breadcrumbs);
// >> configure repository url
$tmpl->addVar('main', 'configure_title', _AM_XOONIPS_MAINTENANCE_OAIPMH_CONFIGURE_TITLE);
$tmpl->addVar('main', 'configure_desc', _AM_XOONIPS_MAINTENANCE_OAIPMH_CONFIGURE_DESC);
// >> results
$tmpl->addVar('main', 'date', _AM_XOONIPS_LABEL_DATE);
$tmpl->addVar('main', 'url', _AM_XOONIPS_LABEL_URL);
$tmpl->addVar('main', 'result', _AM_XOONIPS_MAINTENANCE_OAIPMH_LABEL_LASTRESULT);
$tmpl->addVar('main', 'results_title', _AM_XOONIPS_MAINTENANCE_OAIPMH_RESULTS_TITLE);
$tmpl->addVar('empty_results', 'empty_results', _AM_XOONIPS_MAINTENANCE_OAIPMH_RESULTS_EMPTY);
if ($has_results) {
    $tmpl->addRows('results', $results);
} else {
    $tmpl->setAttribute('results', 'visibility', 'hidden');
    $tmpl->setAttribute('empty_results', 'visibility', 'visible');
}
// >> run harvester
$tmpl->addVar('main', 'harvest_title', _AM_XOONIPS_MAINTENANCE_OAIPMH_HARVEST_TITLE);
$tmpl->addVar('main', 'harvest_desc', _AM_XOONIPS_MAINTENANCE_OAIPMH_HARVEST_DESC);
$tmpl->addVar('main', 'harvest', _AM_XOONIPS_MAINTENANCE_OAIPMH_LABEL_HARVEST);

// display
xoops_cp_header();
$tmpl->displayParsedTemplate('main');
xoops_cp_footer();
