<?php

// $Revision: 1.1.2.9 $
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

include 'include/common.inc.php';

include_once __DIR__.'/class/base/actionfactory.class.php';

$formdata = &xoonips_getutility('formdata');
$op = $formdata->getValue('get', 'action', 'n', false);
if (is_null($op)) {
    header('Location: '.XOOPS_URL.'/modules/xoonips/oaipmh_search.php?action=default');
}

xoonips_validate_request(in_array($op, array('default', 'detail', 'search', 'metadata_detail')));

$factory = &XooNIpsActionFactory::getInstance();
if ($op == 'metadata_detail') {
    $action = &$factory->create('xoonips_search_metadata_detail');
} else {
    $action = &$factory->create('oaipmh_search_'.$op);
}
if (!$action) {
    header('Location: '.XOOPS_URL.'/');
}
$action->action();
