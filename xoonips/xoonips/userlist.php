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

$xoopsOption['pagetype'] = 'user';
require 'include/common.inc.php';

$uid = is_object($xoopsUser) ? $xoopsUser->getVar('uid', 'n') : UID_GUEST;

$textutil = &xoonips_getutility('text');

// get position list
$posi_handler = &xoonips_getormhandler('xoonips', 'positions');
$criteria = new Criteria('posi_order', 0, '>=');
$criteria->setSort('posi_order');
$criteria->setOrder('ASC');
$posi_objs = &$posi_handler->getObjects($criteria);
$users = array();
// get user list by positions
$xu_handler = &xoonips_getormhandler('xoonips', 'users');
$userslist = array();
foreach ($posi_objs as $posi_obj) {
    $posi_id = $posi_obj->get('posi_id');
    $posi_title = $posi_obj->get('posi_title');
    $criteria = new CriteriaCompo();
    $criteria->add(new Criteria('posi', $posi_id)); // position id
    $criteria->add(new Criteria('level', 0, '>', 'u')); // activated user
    $criteria->add(new Criteria('activate', 1)); // certified user
    $criteria->add(new Criteria('user_order', 0, '>=')); // user order
    $join_criteria = new XooNIpsJoinCriteria('users', 'uid', 'uid', 'INNER', 'u');
    $criteria->setSort('user_order');
    $criteria->setOrder('ASC');
    $xu_objs = &$xu_handler->getObjects($criteria, false, 'u.uid, u.name, u.uname, company_name', false, $join_criteria);
    $users = array();
    foreach ($xu_objs as $xu_obj) {
        $users[] = array(
            'uid' => $xu_obj->get('uid'),
            'name' => $textutil->html_special_chars($xu_obj->getExtraVar('name')),
            'uname' => $textutil->html_special_chars($xu_obj->getExtraVar('uname')),
            'company_name' => $textutil->html_special_chars($xu_obj->getVar('company_name')),
        );
    }
    if (count($xu_objs) > 0) {
        $userslist[] = array(
            'title' => $textutil->html_special_chars($posi_title),
            'users' => $users,
        );
    }
}

$xoopsOption['template_main'] = 'xoonips_userlist.html';
require XOOPS_ROOT_PATH.'/header.php';

$xoopsTpl->assign('is_user', ($uid != UID_GUEST));
$xoopsTpl->assign('userslist', $userslist);

require XOOPS_ROOT_PATH.'/footer.php';
