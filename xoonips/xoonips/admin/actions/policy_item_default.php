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

// class files
require_once '../class/base/pattemplate.class.php';

// title
$title = _AM_XOONIPS_POLICY_ITEM_TITLE;
$description = _AM_XOONIPS_POLICY_ITEM_DESC;

// breadcrumbs
$breadcrumbs = array(
    array('type' => 'top', 'label' => _AM_XOONIPS_TITLE, 'url' => $xoonips_admin['admin_url'].'/'),
    array('type' => 'link', 'label' => _AM_XOONIPS_POLICY_TITLE, 'url' => $xoonips_admin['myfile_url']),
    array('type' => 'label', 'label' => $title, 'url' => ''),
);

// menu
$menu = array(
    array(
      'label' => _AM_XOONIPS_POLICY_ITEM_PUBLIC_TITLE,
      'url' => $xoonips_admin['myfile_url'].'?page=item&amp;action=public',
    ),
    array(
      'label' => _AM_XOONIPS_POLICY_ITEM_TYPE_TITLE,
      'url' => $xoonips_admin['myfile_url'].'?page=item&amp;action=type',
    ),
    array(
      'label' => _AM_XOONIPS_POLICY_ITEM_IMEXPORT_TITLE,
      'url' => $xoonips_admin['myfile_url'].'?page=item&amp;action=imexport',
    ),
    array(
      'label' => _AM_XOONIPS_POLICY_ITEM_COMMENT_TITLE,
      'url' => $xoonips_admin['myfile_url'].'?page=item&amp;action=comment',
    ),
);

// templates
$tmpl = new PatTemplate();
$tmpl->setBaseDir('templates');
$tmpl->readTemplatesFromFile('adminmenu.tmpl.html');
$tmpl->addVar('header', 'TITLE', $title);
$tmpl->setAttribute('description', 'visibility', 'visible');
$tmpl->addVar('description', 'DESCRIPTION', $description);
$tmpl->setAttribute('breadcrumbs', 'visibility', 'visible');
$tmpl->addRows('breadcrumbs_items', $breadcrumbs);
$tmpl->addRows('menu', $menu);

xoops_cp_header();
$tmpl->displayParsedTemplate('main');
xoops_cp_footer();
