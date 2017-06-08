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

// class files loader for OAI-PMH

define('XOONIPS_METADATA_CATEGORY_ID', 'ID');
define('XOONIPS_METADATA_CATEGORY_TITLE', 'TITLE');
define('XOONIPS_METADATA_CATEGORY_CREATOR', 'CREATOR');
define('XOONIPS_METADATA_CATEGORY_RESOURCE_LINK', 'RESOURCE_LINK');
define('XOONIPS_METADATA_CATEGORY_LAST_UPDATE_DATE', 'LAST_UPDATE_DATE');
define('XOONIPS_METADATA_CATEGORY_CREATION_DATE', 'CREATION_DATE');
define('XOONIPS_METADATA_CATEGORY_DATE', 'DATE');

// class files
$xoonips_class_path = __DIR__;
require_once $xoonips_class_path.'/oaipmh_base.class.php';
require_once $xoonips_class_path.'/oaipmh_oaidc.class.php';
require_once $xoonips_class_path.'/oaipmh_junii.class.php';
require_once $xoonips_class_path.'/oaipmh_junii2.class.php';
require_once $xoonips_class_path.'/oaipmh_handler.class.php';
require_once $xoonips_class_path.'/oaipmh_identify_handler.class.php';
require_once $xoonips_class_path.'/oaipmh_list_metadata_formats_handler.class.php';
require_once $xoonips_class_path.'/oaipmh_list_records_handler.class.php';
