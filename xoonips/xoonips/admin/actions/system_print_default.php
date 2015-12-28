<?php
// $Revision: 1.1.4.1.2.4 $
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
if ( ! defined( 'XOOPS_ROOT_PATH' ) ) {
  exit();
}

// title
$title = _AM_XOONIPS_SYSTEM_PRINT_TITLE;
$description = _AM_XOONIPS_SYSTEM_PRINT_DESC;

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
require_once( '../class/base/gtickets.php' );
$ticket_area = 'xoonips_admin_system_print';
$token_ticket = $xoopsGTicket->getTicketHtml( __LINE__, 1800, $ticket_area );

// get configs
$config_keys = array(
  'printer_friendly_header' => 's',
);
$config_values = xoonips_admin_get_configs( $config_keys, 'e' );
// >> printer_friendly_header
$printer_friendly_header_title = _AM_XOONIPS_SYSTEM_PRINT_PRINTER_FRIENDLY_HEADER_TITLE;
$printer_friendly_header_desc = _AM_XOONIPS_SYSTEM_PRINT_PRINTER_FRIENDLY_HEADER_DESC;
$printer_friendly_header = $config_values['printer_friendly_header'];

// templates
require_once( '../class/base/pattemplate.class.php' );
$tmpl = new PatTemplate();
$tmpl->setBaseDir( 'templates' );
$tmpl->readTemplatesFromFile( 'system_print.tmpl.html' );

// assign template variables
$tmpl->addVar( 'header', 'TITLE', $title );
$tmpl->addVar( 'main', 'TITLE', $title );
$tmpl->setAttribute( 'description', 'visibility', 'visible' );
$tmpl->addVar( 'description', 'DESCRIPTION', $description );
$tmpl->setAttribute( 'breadcrumbs', 'visibility', 'visible' );
$tmpl->addRows( 'breadcrumbs_items', $breadcrumbs );
$tmpl->addVar( 'main', 'token_ticket', $token_ticket );
$tmpl->addVar( 'main', 'submit', _AM_XOONIPS_LABEL_UPDATE );

// >> header part of printer friendly
$tmpl->addVar( 'main', 'printer_friendly_header_title', $printer_friendly_header_title );
$tmpl->addVar( 'main', 'printer_friendly_header_desc', $printer_friendly_header_desc );
$tmpl->addVar( 'main', 'printer_friendly_header', $printer_friendly_header );

// display
xoops_cp_header();
$tmpl->displayParsedTemplate( 'main' );
xoops_cp_footer();

?>
