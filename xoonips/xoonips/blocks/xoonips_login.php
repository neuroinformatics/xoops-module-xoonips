<?php

// $Revision: 1.1.4.1.2.5 $
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

// xoonips login block
function b_xoonips_login_show()
{
    global $xoopsUser;

    // hide block during site login
    if (is_object($xoopsUser)) {
        return false;
    }

    // get xoops configurations
    $myxoopsConfig = &xoonips_get_xoops_configs(XOOPS_CONF);
    $usercookie = $myxoopsConfig['usercookie'];
    $use_ssl = $myxoopsConfig['use_ssl'];
    $sslloginlink = $myxoopsConfig['sslloginlink'];

    // set variables
    $block = array();
    $block['lang_username'] = _MB_XOONIPS_LOGIN_USERNAME;
    if ($usercookie != '' && isset($_COOKIE[$usercookie])) {
        $block['unamevalue'] = $_COOKIE[$usercookie];
    } else {
        $block['unamevalue'] = '';
    }
    $block['lang_password'] = _MB_XOONIPS_LOGIN_PASSWORD;
    $block['lang_login'] = _MB_XOONIPS_LOGIN_LOGIN;
    $block['lang_lostpass'] = _MB_XOONIPS_LOGIN_LOSTPASS;
    $block['lang_registernow'] = _MB_XOONIPS_LOGIN_USERREG;
    if ($use_ssl == 1 && $sslloginlink != '') {
        $block['use_ssl'] = $use_ssl;
        $block['sslloginlink'] = $sslloginlink;
    }
    // $block['lang_rememberme'] = _MB_XOONIPS_LOGIN_REMEMBERME;
    return $block;
}
