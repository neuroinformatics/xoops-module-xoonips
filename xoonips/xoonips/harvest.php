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

//  Process of Harvest in OAI-PMH

$xoopsOption['pagetype'] = 'user';
require 'include/common.inc.php';
require_once 'include/lib.php';
require_once 'include/AL.php';
require_once 'include/item_limit_check.php';

// how to show output results
//  $mode='text' : plain text for external command
//  $mode='html' : html for web browser
$mode = 'text';

require_once 'class/base/oaipmh.class.php';

// if connection is guest access then basic authentication required
if (!$xoopsUser) {
    if (!isset($_SERVER['PHP_AUTH_USER'])) {
        // unauthorized error
        header('WWW-Authenticate: Basic realm="XooNIps harvesting"');
        header('HTTP/1.0 401 Unauthorized');
        echo "Unauthorized your access\n";
        exit;
    } else {
        // try to login as user
        $uname = $_SERVER['PHP_AUTH_USER'];
        $pass = $_SERVER['PHP_AUTH_PW'];
        $member_handler = &xoops_gethandler('member');
        $myts = &MyTextsanitizer::getInstance();
        $xoopsUser = &$member_handler->loginUser(addslashes($myts->stripSlashesGPC($uname)), $myts->stripSlashesGPC($pass));
        if (!$xoopsUser) {
            echo 'Unauthorized your account';
            exit;
        }
        $uid = $xoopsUser->getVar('uid');
    }
    $mode = 'text';
} else {
    $uid = $_SESSION['xoopsUserId'];
    $mode = 'html';
}

// check user privileges. moderator or administrator can access this page.
$xmember_handler = &xoonips_gethandler('xoonips', 'member');
$is_admin = $xmember_handler->isAdmin($uid);
$is_moderator = $xmember_handler->isModerator($uid);
if (!$is_admin && !$is_moderator) {
    redirect_header(XOOPS_URL.'/index.php', 3, _MD_XOONIPS_MODERATOR_SHULD_BE_MODERATOR);
}

global $xoopsDB;

if ($mode == 'html') {
    require XOOPS_ROOT_PATH.'/header.php';
    echo "<p>\n";
    echo '<h3>'._MD_XOONIPS_OAIPMH_HARVEST_RESULT."</h3>\n";
    echo "</p>\n";
    echo "<a href='admin/maintenance.php?page=oaipmh'>"._MD_XOONIPS_BACK_TO_OAIPMH_CONFIGURATION.'</a><br />';
    echo "<p>\n";
} elseif ($mode == 'text') {
    header('Content-type: text/plain');
}

$result = $xoopsDB->query('SELECT URL FROM '.$xoopsDB->prefix('xoonips_oaipmh_repositories').' WHERE enabled=1 AND deleted!=1 ORDER BY sort');
set_time_limit(0);
while (list($url) = $xoopsDB->fetchRow($result)) {
    echo "Trying\t$url";
    if ($mode == 'html') {
        echo "<br />\n";
    } elseif ($mode == 'text') {
        echo "\n";
    }
    $h = new OAIPMHHarvester($url);
    if (!$h->harvest()) {
        echo 'ERROR:'.$h->last_error();
    } else {
        echo "Succeed\t${url}";
    }
    if ($mode == 'html') {
        echo "<br />\n";
    } elseif ($mode == 'text') {
        echo "\n";
    }
}

if ($mode == 'html') {
    echo "</p>\n";
    echo "<a href='admin/maintenance.php?page=oaipmh'>"._MD_XOONIPS_BACK_TO_OAIPMH_CONFIGURATION.'</a><br />';
    require XOOPS_ROOT_PATH.'/footer.php';
}
