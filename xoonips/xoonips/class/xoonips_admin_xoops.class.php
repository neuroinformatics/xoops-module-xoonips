<?php

// $Revision: 1.1.2.8 $
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

class XooNIpsAdminXoopsHandler
{
    /**
     * constructor.
     */
    public function __construct()
    {
    }

    /**
     * get module id.
     *
     * @param string $dirname module directory name
     *
     * @return int module id
     */
    public function getModuleId($dirname)
    {
        $module_handler = &xoops_gethandler('module');
        $module = &$module_handler->getByDirname($dirname);
        if (!is_object($module)) {
            return false;
        }
        $mid = $module->getVar('mid', 'n');

        return $mid;
    }

    /**
     * set module read right.
     *
     * @param int $mid   module id
     * @param int $gid   group id
     * @param int $right has read right ?
     *
     * @return bool false if failure
     */
    public function setModuleReadRight($mid, $gid, $right)
    {
        return $this->_set_read_right(true, $mid, $gid, $right);
    }

    /**
     * set block read right.
     *
     * @param int $bid   block id
     * @param int $gid   group id
     * @param int $right has read right ?
     *
     * @return bool false if failure
     */
    public function setBlockReadRight($bid, $gid, $right)
    {
        return $this->_set_read_right(false, $bid, $gid, $right);
    }

    /**
     * get module block ids.
     *
     * @param int $mid       block id
     * @param int $show_func show function name
     *
     * @return array block ids
     */
    public function getBlockIds($mid, $show_func)
    {
        global $xoopsDB;
        $table = $xoopsDB->prefix('newblocks');
        $sql = sprintf('SELECT bid FROM `%s` WHERE `mid`=%u AND `show_func`=\'%s\'', $table, $mid, addslashes($show_func));
        $result = $xoopsDB->query($sql);
        if (!$result) {
            return false;
        }
        $ret = array();
        while ($myrow = $xoopsDB->fetchArray($result)) {
            $ret[] = $myrow['bid'];
        }
        $xoopsDB->freeRecordSet($result);

        return $ret;
    }

    /**
     * set block position.
     *
     * @param int  $bid     block id
     * @param bool $visible visible flag
     * @param int  $side
     *                      0: sideblock - left
     *                      1: sideblock - right
     *                      2: sideblock - left and right
     *                      3: centerblock - left
     *                      4: centerblock - right
     *                      5: centerblock - center
     *                      6: centerblock - left, right, center
     * @param int  $weight  weight
     *
     * @return bool false if failure
     */
    public function setBlockPosition($bid, $visible, $side, $weight)
    {
        $block = new XoopsBlock();
        $block->load($bid);
        if (!is_null($visible)) {
            $block->setVar('visible', $visible ? 1 : 0, true); // not gpc
        }
        if (!is_null($side)) {
            $block->setVar('side', $side, true); // not gpc
        }
        if (!is_null($weight)) {
            $block->setVar('weight', $weight, true); // not gpc
        }

        return $block->store();
    }

    /**
     * set block show page.
     *
     * @param int  $bid     block id
     * @param int  $mid
     *                      -1 : top page
     *                      0 : all pages
     *                      >=1 : module id
     * @param bool $is_show
     *
     * @return bool false if failure
     */
    public function setBlockShowPage($bid, $mid, $is_show)
    {
        global $xoopsDB;
        $table = $xoopsDB->prefix('block_module_link');
        // check current status
        $sql = sprintf('SELECT `block_id`,`module_id` FROM `%s` WHERE `block_id`=%u AND `module_id`=%d', $table, $bid, $mid);
        if (!$result = $xoopsDB->query($sql)) {
            return false;
        }
        $count = $xoopsDB->getRowsNum($result);
        $xoopsDB->freeRecordSet($result);
        if ($count == 0) {
            // not exists
            if ($is_show) {
                $sql = sprintf('INSERT INTO `%s` (`block_id`,`module_id`) VALUES ( %u, %d )', $table, $bid, $mid);
                if (!$result = $xoopsDB->query($sql)) {
                    return false;
                }
            }
        } else {
            // already exists
            if (!$is_show) {
                $sql = sprintf('DELETE FROM `%s` WHERE `block_id`=%u AND `module_id`=%d', $table, $bid, $mid);
                if (!$result = $xoopsDB->query($sql)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * enable xoops notificaiton.
     *
     * @param string $mid      module id
     * @param string $category
     * @param string $event
     *
     * @return bool false if failure
     */
    public function enableNotification($mid, $category, $event)
    {
        global $xoopsDB;
        $config_handler = &xoops_gethandler('config');
        $criteria = new CriteriaCompo();
        $criteria->add(new Criteria('conf_name', 'notification_events'));
        $criteria->add(new Criteria('conf_modid', $mid));
        $criteria->add(new Criteria('conf_catid', 0));
        $config_items = $config_handler->getConfigs($criteria);
        if (count($config_items) != 1) {
            return false;
        } else {
            $config_item = $config_items[0];
            $option_value = $category.'-'.$event;
            $option_values = $config_item->getConfValueForOutput();
            if (!in_array($option_value, $option_values)) {
                $option_values[] = $option_value;
                $config_item->setConfValueForInput($option_values);
                $config_item_handler = new XoopsConfigItemHandler($xoopsDB);
                $config_item_handler->insert($config_item);
            }
        }

        return true;
    }

    /**
     * subscribe user to xoops notificaiton.
     *
     * @param string $mid      module id
     * @param string $uid      user id
     * @param string $category
     * @param string $event
     *
     * @return bool false if failure
     */
    public function subscribeNotification($mid, $uid, $category, $event)
    {
        $notification_handler = &xoops_gethandler('notification');
        $notification_handler->subscribe($category, 0, $event, null, $mid, $uid);

        return true;
    }

    /**
     * unsubscribe user from xoops notificaiton.
     *
     * @param string $mid      module id
     * @param string $uid      user id
     * @param string $category
     * @param string $event
     *
     * @return bool false if failure
     */
    public function unsubscribeNotification($mid, $uid, $category, $event)
    {
        $notification_handler = &xoops_gethandler('notification');
        $criteria = new CriteriaCompo(new Criteria('not_modid', $mid));
        if ($uid != 0) {
            $criteria->add(new Criteria('not_uid', $uid));
        }
        $criteria->add(new Criteria('not_category', $category));
        $criteria->add(new Criteria('not_event', $event));

        return $notification_handler->deleteAll($criteria);
    }

    /**
     * set start module.
     *
     * @param string $dirname module directory name,  '--' means no module
     *
     * @return bool false if failure
     */
    public function setStartupPageModule($dirname)
    {
        $config_handler = &xoops_gethandler('config');
        $criteria = new CriteriaCompo(new Criteria('conf_modid', 0));
        $criteria->add(new Criteria('conf_catid', XOOPS_CONF));
        $criteria->add(new Criteria('conf_name', 'startpage'));
        $configs = &$config_handler->getConfigs($criteria);
        if (count($configs) != 1) {
            return false;
        }
        list($config) = $configs;
        $config->setConfValueForInput($dirname);

        return $config_handler->insertConfig($config);
    }

    /**
     * create xoops group.
     *
     * @param string $name        group name
     * @param string $description group description
     *
     * @return int created group id
     */
    public function createGroup($name, $description)
    {
        $member_handler = &xoops_gethandler('member');
        $group = &$member_handler->createGroup();
        $group->setVar('name', $name, true); // not gpc
        $group->setVar('description', $description, true); // not gpc
        $ret = $member_handler->insertGroup($group);
        if ($ret == false) {
            return false;
        }
        $gid = $group->getVar('groupid', 'n');

        return $gid;
    }

    /**
     * add user to xoops group.
     *
     * @param int $gid group id
     * @param int $uid user id
     *
     * @return bool false if failure
     */
    public function addUserToXoopsGroup($gid, $uid)
    {
        $member_handler = &xoops_gethandler('member');
        if (!$member_handler->addUserToGroup($gid, $uid)) {
            return false;
        }
        $myuid = $GLOBALS['xoopsUser']->getVar('uid', 'n');
        if ($myuid == $uid) {
            // update group cache and session
            $mygroups = $member_handler->getGroupsByUser($uid);
            $GLOBALS['xoopsUser']->setGroups($mygroups);
            if (isset($_SESSION['xoopsUserGroups'])) {
                $_SESSION['xoopsUserGroups'] = $mygroups;
            }
        }

        return true;
    }

    /**
     * fix invalid xoops group permissions
     *  - refer: http://www.xugj.org/modules/d3forum/index.php?topic_id=791.
     *
     * @return bool false if failure
     */
    public function fixGroupPermissions()
    {
        global $xoopsDB;
        // get invalid group ids
        $table = $xoopsDB->prefix('group_permission');
        $table2 = $xoopsDB->prefix('groups');
        $sql = sprintf('SELECT DISTINCT `gperm_groupid` FROM `%s` LEFT JOIN `%s` ON `%s`.`gperm_groupid`=`%s`.`groupid` WHERE `gperm_modid`=1 AND `groupid` IS NULL', $table, $table2, $table, $table2);
        $result = $xoopsDB->query($sql);
        if (!$result) {
            return false;
        }
        $gids = array();
        while ($myrow = $xoopsDB->fetchArray($result)) {
            $gids[] = $myrow['gperm_groupid'];
        }
        $xoopsDB->freeRecordSet($result);
        // remove all invalid group id entries
        if (count($gids) != 0) {
            $sql = sprintf('DELETE FROM `%s` WHERE `gperm_groupid` IN (%s) AND `gperm_modid`=1', $table, implode(',', $gids));
            $result = $xoopsDB->query($sql);
            if (!$result) {
                return false;
            }
        }

        return true;
    }

    /**
     * set xoops module/block read right.
     *
     * @param bool $is_module true is module, false is block
     * @param int  $iid       module id or block id
     * @param int  $gid       group id
     * @param bool $right     has read right?
     *
     * @return bool false if failure
     */
    public function _set_read_right($is_module, $iid, $gid, $right)
    {
        $name = $is_module ? 'module_read' : 'block_read';
        $criteria = new CriteriaCompo(new Criteria('gperm_name', $name));
        $criteria->add(new Criteria('gperm_groupid', $gid));
        $criteria->add(new Criteria('gperm_itemid', $iid));
        $criteria->add(new Criteria('gperm_modid', 1));
        $gperm_handler = &xoops_gethandler('groupperm');
        $gperm_objs = &$gperm_handler->getObjects($criteria);
        if (count($gperm_objs) > 0) {
            // already exists
            $gperm_obj = $gperm_objs[0];
            if (!$right) {
                $gperm_handler->delete($gperm_obj);
            }
        } else {
            // not found
            if ($right) {
                $gperm_handler->addRight($name, $iid, $gid);
            }
        }

        return true;
    }
}
