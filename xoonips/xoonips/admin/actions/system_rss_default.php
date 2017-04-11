<?php

// $Revision: 1.1.4.1.2.6 $
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
$title = _AM_XOONIPS_SYSTEM_RSS_TITLE;
$description = _AM_XOONIPS_SYSTEM_RSS_DESC;

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
$ticket_area = 'xoonips_admin_system_rss';
$token_ticket = $xoopsGTicket->getTicketHtml(__LINE__, 1800, $ticket_area);

// get configs
$config_keys = array(
    'rss_item_max' => 'i',
);
$config_values = xoonips_admin_get_configs($config_keys, 'e');
// >> rss_item_max
$feed_item_max_title = _AM_XOONIPS_SYSTEM_RSS_FEED_ITEM_MAX_TITLE;
$feed_item_max_desc = _AM_XOONIPS_SYSTEM_RSS_FEED_ITEM_MAX_DESC;
$feed_item_max = $config_values['rss_item_max'];

$textutil = &xoonips_getutility('text');

// feed url
$feed_url_title = _AM_XOONIPS_SYSTEM_RSS_FEED_URL_TITLE;
$feed_url_desc = _AM_XOONIPS_SYSTEM_RSS_FEED_URL_DESC;
$feed_url_rdf = $textutil->html_special_chars($xoonips_admin['mod_url'].'/feed.php?type=rdf');
$feed_url_rss = $textutil->html_special_chars($xoonips_admin['mod_url'].'/feed.php?type=rss');
$feed_url_atom = $textutil->html_special_chars($xoonips_admin['mod_url'].'/feed.php?type=atom');
$feed_url_html = '<link rel="alternate" type="application/rss+xml" title="RSS 1.0" href="'.$feed_url_rdf.'" />'."\n";
$feed_url_html .= '<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="'.$feed_url_rss.'" />'."\n";
$feed_url_html .= '<link rel="alternate" type="application/atom+xml" title="Atom 1.0" href="'.$feed_url_atom.'" />';
$feed_url_html = $textutil->html_special_chars($feed_url_html);

// templates
require_once '../class/base/pattemplate.class.php';
$tmpl = new PatTemplate();
$tmpl->setBaseDir('templates');
$tmpl->readTemplatesFromFile('system_rss.tmpl.html');

// assign template variables
$tmpl->addVar('header', 'TITLE', $title);
$tmpl->addVar('main', 'TITLE', $title);
$tmpl->setAttribute('description', 'visibility', 'visible');
$tmpl->addVar('description', 'DESCRIPTION', $description);
$tmpl->setAttribute('breadcrumbs', 'visibility', 'visible');
$tmpl->addRows('breadcrumbs_items', $breadcrumbs);
$tmpl->addVar('main', 'token_ticket', $token_ticket);
$tmpl->addVar('main', 'submit', _AM_XOONIPS_LABEL_UPDATE);

// >> feed url
$tmpl->addVar('main', 'FEED_URL_TITLE', $feed_url_title);
$tmpl->addVar('main', 'FEED_URL_DESC', $feed_url_desc);
$tmpl->addVar('main', 'FEED_URL_RDF', $feed_url_rdf);
$tmpl->addVar('main', 'FEED_URL_RSS', $feed_url_rss);
$tmpl->addVar('main', 'FEED_URL_ATOM', $feed_url_atom);
$tmpl->addVar('main', 'FEED_URL_HTML', $feed_url_html);
// >> maximum number of rss items
$tmpl->addVar('main', 'FEED_ITEM_MAX_TITLE', $feed_item_max_title);
$tmpl->addVar('main', 'FEED_ITEM_MAX_DESC', $feed_item_max_desc);
$tmpl->addVar('main', 'FEED_ITEM_MAX', $feed_item_max);

// display
xoops_cp_header();
$tmpl->displayParsedTemplate('main');
xoops_cp_footer();
