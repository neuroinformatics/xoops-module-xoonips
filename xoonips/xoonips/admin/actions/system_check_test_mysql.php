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

function xoonips_admin_system_check_mysql(&$category)
{
    // mysql class
  $mysqlinfo = &xoonips_getutility('mysqlinfo');

  // version
  $name = 'MySQL version';
    $res = new XooNIpsAdminSystemCheckResult($name);
    $version = $mysqlinfo->getVersion('full');
    $res->setResult(_XASC_STATUS_OK, $version, _AM_XOONIPS_SYSTEM_CHECK_LABEL_OK);
    $category->registerResult($res);
    unset($res);

    if ($mysqlinfo->isVersion41orHigher()) {
        $keys = array(
      'character_set_database' => true,
      'character_set_client' => false,
      'character_set_connection' => false,
      'character_set_results' => false,
    );
        foreach ($keys as $key => $is_database) {
            $res = new XooNIpsAdminSystemCheckResult($key);
            $charset = $mysqlinfo->getVariable($key);
            $accept_charsets = $mysqlinfo->getAcceptableCharsets($is_database, _CHARSET);
            if (in_array($charset, $accept_charsets)) {
                $res->setResult(_XASC_STATUS_OK, $charset, _AM_XOONIPS_SYSTEM_CHECK_LABEL_OK);
            } else {
                $res->setResult(_XASC_STATUS_FAIL, $charset, _AM_XOONIPS_SYSTEM_CHECK_LABEL_FAIL);
                $res->setMessage('This variable have to set '.implode(' or ', $accept_charsets));
                $category->setError(_XASC_ERRORTYPE_MYSQL, _XASC_STATUS_FAIL);
            }
            $category->registerResult($res);
            unset($res);
        }
    } else {
        $key = 'character_set';
        $res = new XooNIpsAdminSystemCheckResult($key);
        $charset = $mysqlinfo->getVariable($key);
        $accept_charsets = $mysqlinfo->getAcceptableCharsets(true, _CHARSET);
        if (in_array($charset, $accept_charsets)) {
            $res->setResult(_XASC_STATUS_OK, $charset, _AM_XOONIPS_SYSTEM_CHECK_LABEL_OK);
        } else {
            $res->setResult(_XASC_STATUS_FAIL, $charset, _AM_XOONIPS_SYSTEM_CHECK_LABEL_FAIL);
            $res->setMessage('This variable have to set '.implode(' or ', $accept_charsets));
            $category->setError(_XASC_ERRORTYPE_MYSQL, _XASC_STATUS_FAIL);
        }
        $category->registerResult($res);
        unset($res);
    }
}
