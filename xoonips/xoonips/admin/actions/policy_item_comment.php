<?php

// $Revision: 1.1.2.5 $
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

// title
$title = _AM_XOONIPS_POLICY_ITEM_COMMENT_TITLE;
$description = _AM_XOONIPS_POLICY_ITEM_COMMENT_DESC;

// breadcrumbs
$breadcrumbs = array(
    array(
        'type' => 'top',
        'label' => _AM_XOONIPS_TITLE,
        'url' => $xoonips_admin['admin_url'].'/',
    ),
    array(
        'type' => 'link',
        'label' => _AM_XOONIPS_POLICY_TITLE,
        'url' => $xoonips_admin['myfile_url'],
    ),
    array(
        'type' => 'link',
        'label' => _AM_XOONIPS_POLICY_ITEM_TITLE,
        'url' => $xoonips_admin['myfile_url'].'?page=item',
    ),
    array(
        'type' => 'label',
        'label' => $title,
        'url' => '',
    ),
);

// token ticket
require_once '../class/base/gtickets.php';
$ticket_area = 'xoonips_admin_policy_item_comment';
$token_ticket = $xoopsGTicket->getTicketHtml(__LINE__, 1800, $ticket_area);

// get configs
$config_keys = array(
    'item_comment_dirname' => 's',
    'item_comment_forum_id' => 'i',
);
$config_values = xoonips_admin_get_configs($config_keys, 'e');

// get d3forum module list
require XOOPS_ROOT_PATH.'/class/xoopslists.php';
$module_handler = &xoops_gethandler('module');
$mod_dirnames = &XoopsLists::getModulesList();
$d3forum_not_found = true;
$d3forums = array();
// set empty d3forum module name
$selected = ($config_values['item_comment_dirname'] == '');
if ($selected) {
    $d3forum_not_found = false;
}
$d3forums[] = array(
    'dirname' => '',
    'label' => '----------',
    'selected' => $selected,
);
foreach ($mod_dirnames as $mod_dirname) {
    $trustdir_php = XOOPS_ROOT_PATH.'/modules/'.$mod_dirname.'/mytrustdirname.php';
    if (file_exists($trustdir_php)) {
        include $trustdir_php;
        if ($mytrustdirname == 'd3forum') {
            $module = &$module_handler->getByDirname($mod_dirname);
            if (is_object($module) && $module->getVar('isactive', 'n') == 1) {
                // set found d3forum module name
                $selected = ($config_values['item_comment_dirname'] == $mod_dirname);
                if ($selected) {
                    $d3forum_not_found = false;
                }
                $d3forums[] = array(
                    'dirname' => $mod_dirname,
                    'label' => $mod_dirname,
                    'selected' => $selected,
                );
            }
        }
    }
}
if ($d3forum_not_found) {
    // selected d3forum dirname not found
    $d3forum_dirname_notfound = _AM_XOONIPS_POLICY_ITEM_COMMENT_DIRNAME_NOTFOUND.' : '.$config_values['item_comment_dirname'];
    $d3forums[0]['selected'] = true;
    $d3forum_forumid = $config_values['item_comment_forum_id'];
    $d3forum_forumid_notfound = _AM_XOONIPS_POLICY_ITEM_COMMENT_FORUMID_NOTFOUND.' : '.$config_values['item_comment_forum_id'];
} else {
    // selected d3forum dirname found or empty dirname
    $d3forum_forumid = $config_values['item_comment_forum_id'];
    $d3forum_dirname_notfound = '';
    if ($config_values['item_comment_dirname'] == '') {
        // empty dirname
        $d3forum_forumid_notfound = '';
    } else {
        // selected d3forum dirname found
        $sql = sprintf('SELECT forum_id FROM %s WHERE forum_id=%u', $xoopsDB->prefix($config_values['item_comment_dirname'].'_forums'), $config_values['item_comment_forum_id']);
        $res = $xoopsDB->query($sql);
        if ($res === false) {
            die('unexpected error');
        }
        list($forum_id) = $xoopsDB->fetchRow($res);
        $xoopsDB->freeRecordSet($res);
        if (empty($forum_id)) {
            $d3forum_forumid_notfound = _AM_XOONIPS_POLICY_ITEM_COMMENT_FORUMID_NOTFOUND.' : '.$config_values['item_comment_forum_id'];
        } else {
            $d3forum_forumid_notfound = '';
        }
    }
}

// templates
require_once '../class/base/pattemplate.class.php';
$tmpl = new PatTemplate();
$tmpl->setBaseDir('templates');
$tmpl->readTemplatesFromFile('policy_item_comment.tmpl.html');

// assign template variables
$tmpl->addVar('header', 'TITLE', $title);
$tmpl->setAttribute('description', 'visibility', 'visible');
$tmpl->addVar('description', 'DESCRIPTION', $description);
$tmpl->setAttribute('breadcrumbs', 'visibility', 'visible');
$tmpl->addRows('breadcrumbs_items', $breadcrumbs);
$tmpl->addVar('main', 'TOKEN_TICKET', $token_ticket);
$tmpl->addVar('main', 'SUBMIT', _AM_XOONIPS_LABEL_UPDATE);
$tmpl->addVar('main', 'COMMENT_TITLE', _AM_XOONIPS_POLICY_ITEM_COMMENT_TITLE);

// directory setting
$tmpl->addVar('main', 'D3FORUM_DIRNAME_TITLE', _AM_XOONIPS_POLICY_ITEM_COMMENT_DIRNAME_TITLE);
$tmpl->addVar('main', 'D3FORUM_DIRNAME_DESC', _AM_XOONIPS_POLICY_ITEM_COMMENT_DIRNAME_DESC);
$tmpl->addRows('d3forums', $d3forums);
$tmpl->addVar('main', 'D3FORUM_DIRNAME_NOTFOUND', $d3forum_dirname_notfound);
// forum id setting
$tmpl->addVar('main', 'D3FORUM_FORUMID_TITLE', _AM_XOONIPS_POLICY_ITEM_COMMENT_FORUMID_TITLE);
$tmpl->addVar('main', 'D3FORUM_FORUMID_DESC', _AM_XOONIPS_POLICY_ITEM_COMMENT_FORUMID_DESC);
$tmpl->addVar('main', 'D3FORUM_FORUMID', $d3forum_forumid);
$tmpl->addVar('main', 'D3FORUM_FORUMID_NOTFOUND', $d3forum_forumid_notfound);

// display
xoops_cp_header();
$tmpl->displayParsedTemplate('main');
xoops_cp_footer();
