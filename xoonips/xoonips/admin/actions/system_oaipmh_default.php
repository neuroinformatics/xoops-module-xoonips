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
$title = _AM_XOONIPS_SYSTEM_OAIPMH_TITLE;
$description = _AM_XOONIPS_SYSTEM_OAIPMH_DESC;

// breadcrumbs
$breadcrumbs = array(
    array(
        'type' => 'top',
        'label' => _AM_XOONIPS_TITLE,
        'url' => $xoonips_admin['admin_url'].'/',
    ),
    array(
        'type' => 'link',
        'label' => _AM_XOONIPS_SYSTEM_TITLE,
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
$ticket_area = 'xoonips_admin_system_oaipmh';
$token_ticket = $xoopsGTicket->getTicketHtml(__LINE__, 1800, $ticket_area);

// repository
// repository title
$repository_title = _AM_XOONIPS_SYSTEM_OAIPMH_REPOSITORY_TITLE;

// get repository configs
$config_keys = array(
    'repository_name' => 's',
    'repository_nijc_code' => 's',
    'repository_deletion_track' => 'i',
    'repository_institution' => 's',
    'repository_publisher' => 's',
);
$config_values = xoonips_admin_get_configs($config_keys, 'e');
// >> repository instatution
$repository_institution_title = _AM_XOONIPS_SYSTEM_OAIPMH_REPOSITORY_INSTITUTION_TITLE;
$repository_institution_desc = _AM_XOONIPS_SYSTEM_OAIPMH_REPOSITORY_INSTITUTION_DESC;
$repository_institution = $config_values['repository_institution'];
// >> repository publisher
$repository_publisher_title = _AM_XOONIPS_SYSTEM_OAIPMH_REPOSITORY_PUBLISHER_TITLE;
$repository_publisher_desc = _AM_XOONIPS_SYSTEM_OAIPMH_REPOSITORY_PUBLISHER_DESC;
$repository_publisher = $config_values['repository_publisher'];
// >> repository name
$repository_name_title = _AM_XOONIPS_SYSTEM_OAIPMH_REPOSITORY_NAME_TITLE;
$repository_name_desc = _AM_XOONIPS_SYSTEM_OAIPMH_REPOSITORY_NAME_DESC;
$repository_name = $config_values['repository_name'];
// >> repository nijc code
$repository_nijc_code_title = _AM_XOONIPS_SYSTEM_OAIPMH_REPOSITORY_NIJC_CODE_TITLE;
$repository_nijc_code_desc = _AM_XOONIPS_SYSTEM_OAIPMH_REPOSITORY_NIJC_CODE_DESC;
$repository_nijc_code = $config_values['repository_nijc_code'];
// >> repository deletion track
$repository_deletion_track_title = _AM_XOONIPS_SYSTEM_OAIPMH_REPOSITORY_DELETION_TRACK_TITLE;
$repository_deletion_track_desc = _AM_XOONIPS_SYSTEM_OAIPMH_REPOSITORY_DELETION_TRACK_DESC;
$repository_deletion_track = $config_values['repository_deletion_track'];

// harvester
$harvester_title = _AM_XOONIPS_SYSTEM_OAIPMH_HARVESTER_TITLE;
function &get_harvester_repositories()
{
    $repositories_handler = &xoonips_getormhandler('xoonips', 'oaipmh_repositories');
    $urls = &$repositories_handler->getRepositories('e');
    $ret = '';
    $is_first = true;
    foreach ($urls as $url) {
        if ($is_first) {
            $ret = $url['URL'];
            $is_first = false;
        } else {
            $ret .= "\n".$url['URL'];
        }
    }

    return $ret;
}
$harvester_repositories_title = _AM_XOONIPS_SYSTEM_OAIPMH_HARVESTER_REPOSITORIES_TITLE;
$harvester_repositories_desc = _AM_XOONIPS_SYSTEM_OAIPMH_HARVESTER_REPOSITORIES_DESC;
$harvester_repositories = &get_harvester_repositories();

// templates
require_once '../class/base/pattemplate.class.php';
$tmpl = new PatTemplate();
$tmpl->setBaseDir('templates');
$tmpl->readTemplatesFromFile('system_oaipmh.tmpl.html');

// assign template variables
$tmpl->addVar('header', 'TITLE', $title);
$tmpl->setAttribute('description', 'visibility', 'visible');
$tmpl->addVar('description', 'DESCRIPTION', $description);
$tmpl->setAttribute('breadcrumbs', 'visibility', 'visible');
$tmpl->addRows('breadcrumbs_items', $breadcrumbs);
$tmpl->addVar('main', 'token_ticket', $token_ticket);
$tmpl->addVar('main', 'submit', _AM_XOONIPS_LABEL_UPDATE);

$tmpl->addVar('main', 'repository_title', $repository_title);
$tmpl->addVar('main', 'harvester_title', $harvester_title);
// >> repository instatution
$tmpl->addVar('main', 'repository_institution_title', $repository_institution_title);
$tmpl->addVar('main', 'repository_institution_desc', $repository_institution_desc);
$tmpl->addVar('main', 'repository_institution', $repository_institution);
// >> repository publisher
$tmpl->addVar('main', 'repository_publisher_title', $repository_publisher_title);
$tmpl->addVar('main', 'repository_publisher_desc', $repository_publisher_desc);
$tmpl->addVar('main', 'repository_publisher', $repository_publisher);
// >> repository name
$tmpl->addVar('main', 'repository_name_title', $repository_name_title);
$tmpl->addVar('main', 'repository_name_desc', $repository_name_desc);
$tmpl->addVar('main', 'repository_name', $repository_name);
// >> repository nijc code
$tmpl->addVar('main', 'repository_nijc_code_title', $repository_nijc_code_title);
$tmpl->addVar('main', 'repository_nijc_code_desc', $repository_nijc_code_desc);
$tmpl->addVar('main', 'repository_nijc_code', $repository_nijc_code);
// >> repository deletion track
$tmpl->addVar('main', 'repository_deletion_track_title', $repository_deletion_track_title);
$tmpl->addVar('main', 'repository_deletion_track_desc', $repository_deletion_track_desc);
$tmpl->addVar('main', 'repository_deletion_track', $repository_deletion_track);
// >> harvester repositories
$tmpl->addVar('main', 'harvester_repositories_title', $harvester_repositories_title);
$tmpl->addVar('main', 'harvester_repositories_desc', $harvester_repositories_desc);
$tmpl->addVar('main', 'harvester_repositories', $harvester_repositories);

// display
xoops_cp_header();
$tmpl->displayParsedTemplate('main');
xoops_cp_footer();
