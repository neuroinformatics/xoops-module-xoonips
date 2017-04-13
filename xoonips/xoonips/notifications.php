<?php

// $Revision: 1.1.4.1.2.3 $
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

$xoopsOption['pagetype'] = 'notification';
require 'include/common.inc.php';

if (!is_object($xoopsUser)) {
    redirect_header('user.php', 3, _NOPERM);
    exit();
}

$uid = $xoopsUser->getVar('uid');
$xoopsOption['template_main'] = 'xoonips_notifications.html';
require XOOPS_ROOT_PATH.'/header.php';
$xoopsTpl->assign('lang_notifications', _MD_XOONIPS_ACCOUNT_NOTIFICATIONS);
$xoopsTpl->assign('xoonips_editprofile_url', XOOPS_URL.'/modules/xoonips/edituser.php?uid='.$uid);
require XOOPS_ROOT_PATH.'/footer.php';
