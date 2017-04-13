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
require_once '../../../include/cp_header.php';

// load common file.
require 'actions/common.inc.php';

// page definition
$pages = array();
$pages['main'] = array();
$pages['account'] = array(
    'post' => array('rupdate', 'iupdate', 'vupdate'),
);
$pages['group'] = array(
    'post' => array('vupdate'),
);
$pages['item'] = array(
    'get' => array('public', 'type', 'imexport', 'comment'),
    'post' => array('pmupdate', 'poupdate', 'tupdate', 'imexeupdate', 'imexiupdate', 'compmupdate'),
);
$pages['moderator'] = array(
    'post' => array('update'),
);
$pages['position'] = array(
    'post' => array('delete', 'update', 'add'),
);
$pages['ranking'] = array(
    'post' => array('update', 'sort'),
);

// initialize
xoonips_admin_initialize(__FILE__, 'policy', $pages);

// call action file
require $xoonips_admin['myaction_path'];
