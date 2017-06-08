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
defined('XOOPS_ROOT_PATH') || exit('XOOPS root path not defined');

/**
 * @brief data object of index
 *
 * @li getVar('index_id') :
 * @li getVar('parent_index_id') :
 * @li getVar('uid') :
 * @li getVar('gid') :
 * @li getVar('open_level') :
 * @li getVar('sort_number') :
 */
class XooNIpsOrmIndex extends XooNIpsTableObject
{
    public function __construct()
    {
        parent::__construct();
        $this->initVar('index_id', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('parent_index_id', XOBJ_DTYPE_INT, null, true);
        $this->initVar('uid', XOBJ_DTYPE_INT, null, false);
        $this->initVar('gid', XOBJ_DTYPE_INT, null, false);
        $this->initVar('open_level', XOBJ_DTYPE_INT, null, true);
        $this->initVar('sort_number', XOBJ_DTYPE_INT, null, true);
    }

    public function cleanVars()
    {
        $result = true;
        $open_level = $this->get('open_level');
        if ($open_level == OL_PUBLIC) {
            if (isset($this->vars['uid']['value'])) {
                trigger_error('cannot specify uid if open_level is OL_PUBLIC');
                $result = false;
            }
            if (isset($this->vars['gid']['value'])) {
                trigger_error('cannot specify gid if open_level is OL_PUBLIC');
                $result = false;
            }
        } elseif ($open_level == OL_GROUP_ONLY) {
            if (isset($this->vars['uid']['value'])) {
                trigger_error('cannot specify uid if open_level is OL_GROUP_ONLY');
                $result = false;
            }
            $this->vars['gid']['required'] = true;
        } elseif ($open_level == OL_PRIVATE) {
            $this->vars['uid']['required'] = true;
            if (isset($this->vars['gid']['value'])) {
                trigger_error('cannot specify gid if open_level is OL_PRIVATE');
                $result = false;
            }
        } else {
            trigger_error("unknown open_level($open_level)");
            $result = false;
        }

        return $result && parent::cleanVars();
    }

    /**
     * get parent index object of this index.
     *
     * @return XooNIpsOrmIndex parent index object or null
     */
    public function getParentIndex()
    {
        $handler = &xoonips_getormhandler('xoonips', 'index');

        return $handler->get($this->get('parent_index_id'));
    }

    /**
     * get index title.
     *
     * @param string $fmt format
     *
     * @return string title
     */
    public function getTitle($fmt)
    {
        $handler = &xoonips_getormhandler('xoonips', 'title');
        $criteria = new CriteriaCompo(new Criteria('item_id', $this->get('index_id')));
        $criteria->add(new Criteria('title_id', DEFAULT_INDEX_TITLE_OFFSET));
        $title_objs = &$handler->getObjects($criteria);
        if (count($title_objs) != 1) {
            return false;
        }

        return $title_objs[0]->getVar('title', $fmt);
    }

    /**
     * get all of children.
     *
     * @return XooNIpsOrmIndex[] child indexes
     */
    public function &getAllChildren()
    {
        $index_handler = &xoonips_getormhandler('xoonips', 'index');
        $criteria = new Criteria('parent_index_id', $this->get('index_id'));
        $criteria->setSort('sort_number');

        return $index_handler->getObjects($criteria);
    }

    /**
     * lock this index.
     *
     * @return bool false if lock failure
     */
    public function lock()
    {
        $item_lock_handler = &xoonips_getormhandler('xoonips', 'item_lock');

        return $item_lock_handler->lock($this->get('index_id'));
    }

    /**
     * unlock this index.
     *
     * @return bool false if unlock failure
     */
    public function unlock()
    {
        $item_lock_handler = &xoonips_getormhandler('xoonips', 'item_lock');

        return $item_lock_handler->unlock($this->get('index_id'));
    }
}

/**
 * @brief handler object of title
 */
class XooNIpsOrmIndexHandler extends XooNIpsTableObjectHandler
{
    public function __construct(&$db)
    {
        parent::__construct($db);
        $this->__initHandler('XooNIpsOrmIndex', 'xoonips_index', 'index_id', false);
    }

    /**
     * return true if permitted to this index.
     *
     * @param id id of index
     * @param uid uid who access to this index
     * @param operation read|write|delete|create(create child index)|export|register_item
     *
     * @return true if permitted
     */
    public function getPerm($id, $uid, $operation)
    {
        if (!in_array($operation, array('read', 'write', 'delete', 'create', 'export', 'register_item'))) {
            // bad operation.
            return false;
        }
        $index = $this->get($id);
        if (false == $index) {
            // no such index
            return false;
        }
        if ($id == IID_ROOT) {
            // IID_ROOT is hidden index
            return false;
        }

        if ($operation == 'write' || $operation == 'delete') {
            $item_lock_handler = &xoonips_getormhandler('xoonips', 'item_lock');
            if ($item_lock_handler->isLocked($id)) {
                // cannot write/delete locked index
                return false;
            }
        }

        $item_lock_handler = &xoonips_getormhandler('xoonips', 'item_lock');
        if (($operation == 'create' || $operation == 'register_item') && $item_lock_handler->isLocked($id) && $item_lock_handler->getLockType($id) == XOONIPS_LOCK_TYPE_PUBLICATION_GROUP_INDEX) {
            // cannot create new child of locked index
            return false;
        }

        switch ($index->get('open_level')) {
        case OL_PUBLIC:
            if ($operation == 'read') {
                if ($uid == UID_GUEST) {
                    $xconfig_handler = &xoonips_getormhandler('xoonips', 'config');
                    if ($xconfig_handler->getValue(XNP_CONFIG_PUBLIC_ITEM_TARGET_USER_KEY) != XNP_CONFIG_PUBLIC_ITEM_TARGET_USER_ALL) {
                        // guest not allowed
                        return false;
                    }
                }

                return true;
            }
            if ($operation == 'register_item') {
                if ($uid == UID_GUEST) {
                    return false;
                }

                return true;
            }
            break;
        case OL_GROUP_ONLY:
            $xgroup_handler = &xoonips_gethandler('xoonips', 'group');
            if ($operation == 'read' || $operation == 'register_item') {
                return $xgroup_handler->isGroupMember($uid, $index->get('gid'));
            } elseif ($operation == 'write' || $operation == 'delete' || $operation == 'create' || $operation == 'export') {
                return $xgroup_handler->isGroupAdmin($uid, $index->get('gid'));
            }
            break;
        case OL_PRIVATE:
            if ($index->get('uid') == $uid) {
                return true;
            }
            break;
        default:
            // must not happen
            return false;
        }

        // moderator or admin?
        $member_handler = &xoonips_gethandler('xoonips', 'member');
        if ($member_handler->isModerator($uid) || $member_handler->isAdmin($uid)) {
            return true;
        }

        return false;
    }

    /**
     * rename index title.
     *
     * @param int    $xid   index id
     * @param string $title
     *
     * @return bool false if failure
     */
    public function renameIndexTitle($xid, $title)
    {
        $it_handler = &xoonips_getormhandler('xoonips', 'title');
        $criteria = new CriteriaCompo(new Criteria('item_id', $xid));
        $criteria->add(new Criteria('title_id', DEFAULT_INDEX_TITLE_OFFSET));
        $it_objs = &$it_handler->getObjects($criteria);
        if (count($it_objs) != 1) {
            return false;
        }
        $it_obj = &$it_objs[0];
        $it_obj->set('title', $title);
        if (!$it_handler->insert($it_obj)) {
            return false;
        }

        return true;
    }

    /**
     * create user root index.
     *
     * @param int uid user id
     *
     * @return int created index id, false if failure
     */
    public function createUserRootIndex($uid)
    {
        // check existing user index
        $criteria = new CriteriaCompo(new Criteria('uid', $uid));
        $criteria->add(new Criteria('parent_index_id', IID_ROOT));
        if ($this->getCount($criteria) != 0) {
            // already exists
            return false;
        }

        // get account id (uname)
        $user_handler = &xoonips_getormhandler('xoonips', 'xoops_users');
        $criteria = new Criteria('uid', $uid);
        $user_objs = &$user_handler->getObjects($criteria);
        if (count($user_objs) != 1) {
            // xoops user not found
            return false;
        }
        $user_obj = &$user_objs[0];
        $uname = $user_obj->getVar('uname', 'n');

        return $this->_createRootIndex($uname, true, $uid);
    }

    /**
     * create group root index.
     *
     * @param int gid group id
     *
     * @return int created index id, false if failure
     */
    public function createGroupRootIndex($gid)
    {
        // check existing group index
        $criteria = new CriteriaCompo(new Criteria('gid', $gid));
        $criteria->add(new Criteria('parent_index_id', IID_ROOT));
        if ($this->getCount($criteria) != 0) {
            // already exists
            return false;
        }

        // get group id (gname)
        $xgroup_handler = &xoonips_gethandler('xoonips', 'group');
        $xgroup_obj = &$xgroup_handler->getGroupObject($gid);
        if (!is_object($xgroup_obj)) {
            // group not found
            return false;
        }
        $gname = $xgroup_obj->getVar('gname', 'n');

        return $this->_createRootIndex($gname, false, $gid);
    }

    /**
     * create root index.
     *
     * @param string $title   index title
     * @param bool   $is_user true: for user index, false: for group index
     * @param int    $ugid    user id or group id
     *
     * @return int created index id, false if failure
     */
    public function _createRootIndex($title, $is_user, $ugid)
    {
        // transaction
        require_once XOOPS_ROOT_PATH.'/modules/xoonips/class/base/transaction.class.php';
        $transaction = &XooNIpsTransaction::getInstance();
        $transaction->start();

        // create item basic
        $ib_handler = &xoonips_getormhandler('xoonips', 'item_basic');
        $ib_obj = &$ib_handler->create();
        $ib_obj->set('item_type_id', ITID_INDEX);
        if ($is_user) {
            $ib_obj->set('uid', $ugid);
        } else {
            // uid is session owner for group index
            $uid = UID_GUEST;
            if (isset($GLOBALS['xoopsUser']) && is_object($GLOBALS['xoopsUser'])) {
                $uid = intval($GLOBALS['xoopsUser']->getVar('uid', 'n'));
            }
            $ib_obj->set('uid', $uid);
        }
        if (!$ib_handler->insert($ib_obj)) {
            $transaction->rollback();

            return false;
        }

        // create item title
        $it_handler = &xoonips_getormhandler('xoonips', 'title');
        $item_id = $ib_obj->getVar('item_id', 'n');
        $it_obj = &$it_handler->create();
        $it_obj->set('item_id', $item_id);
        $it_obj->set('title_id', DEFAULT_INDEX_TITLE_OFFSET);
        $it_obj->set('title', $title);
        if (!$it_handler->insert($it_obj)) {
            $transaction->rollback();

            return false;
        }

        // get sort number
        $sort_number = 0;
        if ($is_user) {
            // for user index
            $criteria = new CriteriaCompo(new Criteria('parent_index_id', IID_ROOT));
            $criteria->add(new Criteria('open_level', OL_PRIVATE));
            if ($this->getCount($criteria) == 0) {
                // first creation
                // this value will be used for admin on install xoonips module
                $sort_number = 2147483647;
            } else {
                $idx_objs = &$this->getObjects($criteria, false, 'MIN(sort_number) AS min_value');
                $idx_obj = &$idx_objs[0];
                $sort_number = intval($idx_obj->getExtraVar('min_value')) - 1;
                unset($idx_objs);
                unset($idx_obj);
            }
        } else {
            // for group index
            $criteria = new CriteriaCompo(new Criteria('parent_index_id', IID_ROOT));
            $criteria2 = new CriteriaCompo();
            $criteria2->add(new Criteria('open_level', OL_PUBLIC));
            $criteria2->add(new Criteria('open_level', OL_GROUP_ONLY), 'OR');
            $criteria->add($criteria2);
            if ($this->getCount($criteria) == 0) {
                // not reaeched
                $transaction->rollback();

                return false;
            } else {
                $idx_objs = &$this->getObjects($criteria, false, 'MAX(sort_number) AS max_value');
                $idx_obj = &$idx_objs[0];
                $sort_number = intval($idx_obj->getExtraVar('max_value')) + 1;
                unset($idx_objs);
                unset($idx_obj);
            }
        }

        // create index
        $idx_obj = &$this->create();
        $idx_obj->set('index_id', $item_id);
        $idx_obj->set('parent_index_id', IID_ROOT);
        if ($is_user) {
            $idx_obj->set('uid', $ugid);
            $idx_obj->set('open_level', OL_PRIVATE);
        } else {
            $idx_obj->set('gid', $ugid);
            $idx_obj->set('open_level', OL_GROUP_ONLY);
        }
        $idx_obj->set('sort_number', $sort_number);
        if (!$this->insert($idx_obj)) {
            $transaction->rollback();

            return false;
        }
        $transaction->commit();

        return $idx_obj->getVar('index_id', 'n');
    }

    /**
     * lock descendents.
     *
     * @param int $id index_id
     */
    public function lockAllDescendents($id)
    {
        $index_handler = &xoonips_getormhandler('xoonips', 'index');
        if ($id == IID_ROOT) {
            return true;
        }
        foreach ($this->getAllDescendents($id) as $index) {
            if (!$index->lock()) {
                trigger_error('cannot lock descendents: '.$index->get('index_id'));

                return false;
            }
        }

        return true;
    }

    /**
     * unlock descendents.
     *
     * @param int $id index_id
     */
    public function unlockAllDescendents($id)
    {
        $index_handler = &xoonips_getormhandler('xoonips', 'index');
        if ($id == IID_ROOT) {
            return true;
        }
        foreach ($this->getAllDescendents($id) as $index) {
            if (!$index->unlock()) {
                trigger_error('cannot unlock descendents: '.$index->get('index_id'));

                return false;
            }
        }

        return true;
    }

    /**
     * get all parent indexes.
     *
     * @param int $id index_id
     *
     * @return XooNIpsOrmIndex[]
     */
    public function getAllParents($index_id)
    {
        $current = $this->get($index_id);
        if (!$current || $current->get('index_id') == IID_ROOT) {
            return array();
        }
        $parent = $current->getParentIndex();
        if ($parent) {
            return array_merge($this->getAllParents($parent->get('index_id')), array($current));
        } else {
            return array($current);
        }
    }

    /**
     * get all descendents parent indexes path string.
     *
     * @param int $id index_id
     *
     * @return XooNIpsOrmIndex[]
     */
    public function getAllDescendents($index_id)
    {
        $index_handler = &xoonips_getormhandler('xoonips', 'index');
        $index = &$index_handler->get($index_id);
        if (!$index) {
            return array();
        }

        $result = array();
        foreach ($index->getAllChildren() as $child) {
            $result = array_merge($result, array($child), $index_handler->getAllDescendents($child->get('index_id')));
        }

        return $result;
    }

    /**
     * set sort_number automatically before insert.
     *
     * @see TableObject::insert
     */
    public function insert(&$obj, $force = false)
    {
        if (!is_null($obj->get('sort_number'))) {
            return parent::insert($obj, $force);
        }
        // for regular index
        $row = $this->getObjects(new Criteria('parent_index_id', $obj->get('parent_index_id')), false, 'MAX(sort_number) as max_value');
        if ($row) {
            $obj->set('sort_number', $row[0]->getExtraVar('max_value') + 1);
        } else {
            $obj->set('sort_number', 1);
        }

        return parent::insert($obj, $force);
    }
}
