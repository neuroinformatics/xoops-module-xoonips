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
require_once XOOPS_ROOT_PATH.'/kernel/notification.php';

//- - - - - - - - - - - - - - - - - - - - - - - - - - - -
//
// Handlers
//
//- - - - - - - - - - - - - - - - - - - - - - - - - - - -
class XooNIpsNotificationHandler extends XoopsNotificationHandler
{
    public function __construct(&$db)
    {
        parent::__construct($db);
    }

    public function triggerEvent2($category, $item_id, $event, $subject, $template_dir, $template, $extra_tags = array(), $user_list = array(), $module_id = null, $omit_user_id = null)
    {
        if (!isset($module_id)) {
            global $xoopsModule;
            $module = &$xoopsModule;
            $module_id = !empty($xoopsModule) ? $xoopsModule->getVar('mid') : 0;
        } else {
            $module_handler = &xoops_gethandler('module');
            $module = &$module_handler->get($module_id);
        }

        // Check if event is enabled
        $config_handler = &xoops_gethandler('config');
        $mod_config = &$config_handler->getConfigsByCat(0, $module->getVar('mid'));
        if (empty($mod_config['notification_enabled'])) {
            return false;
        }
        $category_info = &notificationCategoryInfo($category, $module_id);
        $event_info = &notificationEventInfo($category, $event, $module_id);
        if (!in_array(notificationGenerateConfig($category_info, $event_info, 'option_name'), $mod_config['notification_events']) && empty($event_info['invisible'])) {
            return false;
        }

        if (!isset($omit_user_id)) {
            global $xoopsUser;
            if (!empty($xoopsUser)) {
                $omit_user_id = $xoopsUser->getVar('uid');
            } else {
                $omit_user_id = 0;
            }
        }
        $criteria = new CriteriaCompo();
        $criteria->add(new Criteria('not_modid', intval($module_id)));
        $criteria->add(new Criteria('not_category', $category));
        $criteria->add(new Criteria('not_itemid', intval($item_id)));
        $criteria->add(new Criteria('not_event', $event));
        $mode_criteria = new CriteriaCompo();
        $mode_criteria->add(new Criteria('not_mode', XOOPS_NOTIFICATION_MODE_SENDALWAYS), 'OR');
        $mode_criteria->add(new Criteria('not_mode', XOOPS_NOTIFICATION_MODE_SENDONCETHENDELETE), 'OR');
        $mode_criteria->add(new Criteria('not_mode', XOOPS_NOTIFICATION_MODE_SENDONCETHENWAIT), 'OR');
        $criteria->add($mode_criteria);
        if (!empty($user_list)) {
            $user_criteria = new CriteriaCompo();
            foreach ($user_list as $user) {
                $user_criteria->add(new Criteria('not_uid', $user), 'OR');
            }
            $criteria->add($user_criteria);
        }
        $notifications = &$this->getObjects($criteria);
        if (empty($notifications)) {
            return;
        }

        // Add some tag substitutions here

        $not_config = $module->getInfo('notification');
        $tags = array();
        if (!empty($not_config)) {
            if (!empty($not_config['tags_file'])) {
                $tags_file = XOOPS_ROOT_PATH.'/modules/'
                    .$module->getVar('dirname').'/'.
                    $not_config['tags_file'];
                if (file_exists($tags_file)) {
                    require_once $tags_file;
                    if (!empty($not_config['tags_func'])) {
                        $tags_func = $not_config['tags_func'];
                        if (function_exists($tags_func)) {
                            $tags = $tags_func($category,
                                               intval($item_id), $event);
                        }
                    }
                }
            }
            // RMV-NEW
            if (!empty($not_config['lookup_file'])) {
                $lookup_file = XOOPS_ROOT_PATH.'/modules/'.$module->getVar('dirname').'/'.$not_config['lookup_file'];
                if (file_exists($lookup_file)) {
                    require_once $lookup_file;
                    if (!empty($not_config['lookup_func'])) {
                        $lookup_func = $not_config['lookup_func'];
                        if (function_exists($lookup_func)) {
                            $item_info = $lookup_func($category,
                                                      intval($item_id));
                        }
                    }
                }
            }
        }
        $tags['X_ITEM_NAME'] = !empty($item_info['name']) ? $item_info['name'] : '['._NOT_ITEMNAMENOTAVAILABLE.']';
        $tags['X_ITEM_URL'] = !empty($item_info['url']) ? $item_info['url'] : '['._NOT_ITEMURLNOTAVAILABLE.']';
        $tags['X_ITEM_TYPE'] = !empty($category_info['item_name']) ? $category_info['title'] : '['._NOT_ITEMTYPENOTAVAILABLE.']';
        $tags['X_MODULE'] = $module->getVar('name');
        $tags['X_MODULE_URL'] = XOOPS_URL.'/modules/'.$module->getVar('dirname').'/';
        $tags['X_NOTIFY_CATEGORY'] = $category;
        $tags['X_NOTIFY_EVENT'] = $event;

        foreach ($notifications as $notification) {
            if (empty($omit_user_id) || $notification->getVar('not_uid') != $omit_user_id) {
                // user-specific tags
                //$tags['X_UNSUBSCRIBE_URL'] = 'TODO';
                // TODO: don't show unsubscribe link if it is 'one-time' ??
                $tags['X_UNSUBSCRIBE_URL'] = XOOPS_URL.'/notifications.php';
                $tags = array_merge($tags, $extra_tags);

                $notification->notifyUser($template_dir, $template.'.tpl', $subject, $tags);
            }
        }
    }

    public function getTemplateDirByMid($mid = null)
    {
        if (null == $mid) {
            global $xoopsModule;
            $module = &$xoopsModule;
        } else {
            $module_handler = &xoops_gethandler('module');
            $module = $module_handler->get($mid);
        }
        $langman = &xoonips_getutility('languagemanager');

        return $langman->mail_template_dir($module->getVar('dirname'));
    }
}
