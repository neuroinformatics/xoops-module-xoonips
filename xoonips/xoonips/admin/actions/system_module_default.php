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
defined('XOOPS_ROOT_PATH') || exit('XOOPS root path not defined');

// title
$title = _AM_XOONIPS_SYSTEM_MODULE_TITLE;
$description = _AM_XOONIPS_SYSTEM_MODULE_DESC;

// breadcrumbs
$breadcrumbs = array(
    array(
        'type' => 'top',
        'label' => _AM_XOONIPS_TITLE,
        'url' => $xoonips_admin['admin_url'].'/',
    ),
    array(
        'type' => 'link',
        'label' => _AM_XOONIPS_SYSTEM_TITLE,
        'url' => $xoonips_admin['myfile_url'],
    ),
    array(
        'type' => 'label',
        'label' => $title,
        'url' => '',
    ),
);

// token ticket
require_once '../class/base/gtickets.php';
$ticket_area = 'xoonips_admin_system_module';

// get module id
$module_id = $xoopsModule->getVar('mid');

$textutil = &xoonips_getutility('text');

// main logic
// - $fct = 'preferences'
// - $op = 'showmod'
$criteria = new CriteriaCompo(new Criteria('conf_modid', $module_id));
// select notification configulations only
$append_confnames = array(
    'notification_enabled',
    'notification_events',
);
if (count($append_confnames) > 0) {
    $criteria_append = new CriteriaCompo();
    foreach ($append_confnames as $confname) {
        $criteria_append->add(new Criteria('conf_name', $confname), 'OR');
    }
    $criteria->add($criteria_append);
}
$config = &$config_handler->getConfigs($criteria);
$count = count($config);
if ($count < 1) {
    die('error : no config');
}
require_once XOOPS_ROOT_PATH.'/class/xoopsformloader.php';
$form = new XoopsThemeForm($title, 'xoonips_admin_system_module', $xoonips_admin['mypage_url']);
$module_handler = &xoops_gethandler('module');
$module = &$module_handler->get($module_id);

// if has comments feature, need comment lang file
if ($module->getVar('hascomments') == 1) {
    $langman->read_pagetype('comment.php');
}
// RMV-NOTIFY
// if has notification feature, need notification lang file
if ($module->getVar('hasnotification') == 1) {
    $langman->read_pagetype('notification.php');
}

$button_tray = new XoopsFormElementTray('');
for ($i = 0; $i < $count; ++$i) {
    $title4tray = (!defined($config[$i]->getVar('conf_desc')) || constant($config[$i]->getVar('conf_desc')) == '') ? constant($config[$i]->getVar('conf_title')) : constant($config[$i]->getVar('conf_title')).'<br /><br /><span style="font-weight:normal;">'.constant($config[$i]->getVar('conf_desc')).'</span>';
    $eletitle = '';
    switch ($config[$i]->getVar('conf_formtype')) {
    case 'textarea':
        if ($config[$i]->getVar('conf_valuetype') == 'array') {
            // this is exceptional.. only when value type is arrayneed a
            // smarter way for this
            $ele = ($config[$i]->getVar('conf_value') != '') ? new XoopsFormTextArea($eletitle, $config[$i]->getVar('conf_name'), $textutil->html_special_chars(implode('|', $config[$i]->getConfValueForOutput())), 5, 50) : new XoopsFormTextArea($eletitle, $config[$i]->getVar('conf_name'), '', 5, 50);
        } else {
            $ele = new XoopsFormTextArea($eletitle, $config[$i]->getVar('conf_name'), $textutil->html_special_chars($config[$i]->getConfValueForOutput()), 5, 50);
        }
        break;
    case 'select':
        $ele = new XoopsFormSelect($eletitle, $config[$i]->getVar('conf_name'), $config[$i]->getConfValueForOutput());
        $options = &$config_handler->getConfigOptions(new Criteria('conf_id', $config[$i]->getVar('conf_id')));
        $opcount = count($options);
        for ($j = 0; $j < $opcount; ++$j) {
            $optval = defined($options[$j]->getVar('confop_value')) ? constant($options[$j]->getVar('confop_value')) : $options[$j]->getVar('confop_value');
            $optkey = defined($options[$j]->getVar('confop_name')) ? constant($options[$j]->getVar('confop_name')) : $options[$j]->getVar('confop_name');
            $ele->addOption($optval, $optkey);
        }
        break;
    case 'select_multi':
        $ele = new XoopsFormSelect($eletitle, $config[$i]->getVar('conf_name'), $config[$i]->getConfValueForOutput(), 5, true);
        $options = &$config_handler->getConfigOptions(new Criteria('conf_id', $config[$i]->getVar('conf_id')));
        $opcount = count($options);
        for ($j = 0; $j < $opcount; ++$j) {
            $optval = defined($options[$j]->getVar('confop_value')) ? constant($options[$j]->getVar('confop_value')) : $options[$j]->getVar('confop_value');
            $optkey = defined($options[$j]->getVar('confop_name')) ? constant($options[$j]->getVar('confop_name')) : $options[$j]->getVar('confop_name');
            $ele->addOption($optval, $optkey);
        }
        break;
    case 'yesno':
        $ele = new XoopsFormRadioYN($eletitle, $config[$i]->getVar('conf_name'), $config[$i]->getConfValueForOutput(), _YES, _NO);
        break;
    case 'group':
        require_once XOOPS_ROOT_PATH.'/class/xoopslists.php';
        $ele = new XoopsFormSelectGroup($eletitle, $config[$i]->getVar('conf_name'), false, $config[$i]->getConfValueForOutput(), 1, false);
        break;
    case 'group_multi':
        require_once XOOPS_ROOT_PATH.'/class/xoopslists.php';
        $ele = new XoopsFormSelectGroup($eletitle, $config[$i]->getVar('conf_name'), false, $config[$i]->getConfValueForOutput(), 5, true);
        break;
        // RMV-NOTIFY: added 'user' and 'user_multi'
    case 'user':
        require_once XOOPS_ROOT_PATH.'/class/xoopslists.php';
        $ele = new XoopsFormSelectUser($eletitle, $config[$i]->getVar('conf_name'), false, $config[$i]->getConfValueForOutput(), 1, false);
        break;
    case 'user_multi':
        require_once XOOPS_ROOT_PATH.'/class/xoopslists.php';
        $ele = new XoopsFormSelectUser($eletitle, $config[$i]->getVar('conf_name'), false, $config[$i]->getConfValueForOutput(), 5, true);
        break;
    case 'password':
        $ele = new XoopsFormPassword($eletitle, $config[$i]->getVar('conf_name'), 50, 255, $textutil->html_special_chars($config[$i]->getConfValueForOutput()));
        break;
    case 'textbox':
    default:
        $ele = new XoopsFormText($eletitle, $config[$i]->getVar('conf_name'), 50, 255, $textutil->html_special_chars($config[$i]->getConfValueForOutput()));
        break;
    }
    $hidden = new XoopsFormHidden('conf_ids[]', $config[$i]->getVar('conf_id'));
    $ele_tray = new XoopsFormElementTray($title4tray, '');
    $ele_tray->addElement($ele);
    $ele_tray->addElement($hidden);
    $form->addElement($ele_tray);
    unset($ele_tray);
    unset($ele);
    unset($hidden);
}
$button_tray->addElement(new XoopsFormHidden('action', 'update'));
$xoopsGTicket->addTicketXoopsFormElement($button_tray, __LINE__, 1800, $ticket_area);
$button_tray->addElement(new XoopsFormButton('', 'button', _AM_XOONIPS_LABEL_UPDATE, 'submit'));
$form->addElement($button_tray);

// templates
require_once '../class/base/pattemplate.class.php';
$tmpl = new PatTemplate();
$tmpl->setBaseDir('templates');
$tmpl->readTemplatesFromFile('system_module.tmpl.html');

// assign template variables
$tmpl->addVar('header', 'TITLE', $title);
$tmpl->addVar('main', 'TITLE', $title);
$tmpl->setAttribute('description', 'visibility', 'visible');
$tmpl->addVar('description', 'DESCRIPTION', $description);
$tmpl->setAttribute('breadcrumbs', 'visibility', 'visible');
$tmpl->addRows('breadcrumbs_items', $breadcrumbs);
$tmpl->addVar('main', 'XOOPS_FORM', $form->render());

// display
xoops_cp_header();
$tmpl->displayParsedTemplate('main');
xoops_cp_footer();
