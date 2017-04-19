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

// functions to display blocks and edit blocks.
// load global definitions and functions
require_once dirname(__DIR__).'/condefs.php';
require_once dirname(__DIR__).'/include/functions.php';

// initialize xoonips session
$uid = is_object($GLOBALS['xoopsUser']) ? $GLOBALS['xoopsUser']->getVar('uid', 'n') : UID_GUEST;
$xsession_handler = &xoonips_getormhandler('xoonips', 'session');
$xsession_handler->initSession($uid);
unset($uid);
unset($xsession_handler);

// load xoonips login block
require_once __DIR__.'/xoonips_login.php';

// load xoonips user menu block
require_once __DIR__.'/xoonips_usermenu.php';

// load xoonips group admin menu block
require_once __DIR__.'/xoonips_groupadmin.php';

// load xoonips moderator menu block
require_once __DIR__.'/xoonips_moderator.php';

// load index tree block
require_once __DIR__.'/xoonips_tree.php';

// load quick search block
require_once __DIR__.'/xoonips_quicksearch.php';

// load item type list block
require_once __DIR__.'/xoonips_itemtypes.php';

// load user list block
require_once __DIR__.'/xoonips_userlist.php';

// load ranking block
require_once __DIR__.'/xoonips_ranking.php';
