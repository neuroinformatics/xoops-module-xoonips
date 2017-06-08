<?php

// $Revision:$
//  ------------------------------------------------------------------------ //
//  XooNIps - Neuroinformatics Base Platform System                          //
//  Copyright (C) 2005-2011 RIKEN, Japan All rights reserved.                //
//  http://xoonips.sourceforge.jp/                                           //
//  ------------------------------------------------------------------------ //
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
//  ------------------------------------------------------------------------ //
defined('XOOPS_ROOT_PATH') || exit('XOOPS root path not defined');

/**
 * @brief data object of index item link
 *
 * @li getVar('index_group_index_link_id') :
 * @li getVar('index_id') :
 * @li getVar('item_id') :
 * @li getVar('certify_state') :
 */
class XooNIpsOrmIndexGroupIndexLink extends XooNIpsTableObject
{
    public function __construct()
    {
        parent::__construct();
        $this->initVar('index_group_index_link_id', XOBJ_DTYPE_INT, null, false);
        $this->initVar('index_id', XOBJ_DTYPE_INT, null, true);
        $this->initVar('group_index_id', XOBJ_DTYPE_INT, null, false);
        $this->initVar('gid', XOBJ_DTYPE_INT, 0, false);
        $this->initVar('uid', XOBJ_DTYPE_INT, 0, false);
    }
}

/**
 * @brief handler object of index item link
 */
class XooNIpsOrmIndexGroupIndexLinkHandler extends XooNIpsTableObjectHandler
{
    public function __construct(&$db)
    {
        parent::__construct($db);
        $this->__initHandler('XooNIpsOrmIndexGroupIndexLink', 'xoonips_index_group_index_link', 'index_group_index_link_id');
    }

    /**
     * get this objects by item id.
     *
     * @param int $item_id
     *
     * @return array of XooNIpsOrmIndexGroupIndexLink
     */
    public function getObjectsByItemId($item_id)
    {
        $index_item_link_handler = &xoonips_getormhandler('xoonips', 'index_item_link');
        $join = new XooNIpsJoinCriteria('xoonips_index', 'index_id', 'index_id', 'LEFT', 'tindex');

        $criteria = new CriteriaCompo();
        $criteria->add(new Criteria('item_id', $item_id));
        $criteria->add(new Criteria('open_level', OL_GROUP_ONLY));

        foreach ($index_item_link_handler->getObjects($criteria, false, '', true, $join) as $row) {
            $result = $this->getObjectsByGroupIndexId($row->get('index_id'));
            if ($result) {
                return $result;
            }
        }

        return array();
    }

    /**
     * get this objects by group index id.
     *
     * @param int $group_index_id
     *
     * @return array of XooNIpsOrmIndexGroupIndexLink
     */
    public function getObjectsByGroupIndexId($group_index_id)
    {
        if (IID_ROOT == $group_index_id) {
            return array();
        }

        $result = $this->getObjects(new Criteria('group_index_id', $group_index_id));
        if ($result) {
            return $result;
        }

        $index_handler = &xoonips_getormhandler('xoonips', 'index');
        $index = &$index_handler->get($group_index_id);
        if (!$index) {
            return array();
        }

        return $this->getObjectsByGroupIndexId($index->get('parent_index_id'));
    }

    /**
     * get index_group_index_link object(s) that the item is registerd(certify state is ignored).
     *
     * @param $item_id id of item
     * @param $open_levels array of open levels of index to get (default is OL_PRIVATE, OL_GROUP_ONLY and OL_PUBLIC)
     *
     * @return array of index_group_index_link object(s)
     */
    public function getByItemId($item_id, $open_levels = array(OL_PRIVATE, OL_GROUP_ONLY, OL_PUBLIC))
    {
        $join = new XooNIpsJoinCriteria('xoonips_index', 'index_id', 'index_id', 'LEFT', 'tindex');
        $criteria = new CriteriaCompo();
        $criteria->add(new Criteria('item_id', $item_id));
        $criteria->add(new Criteria('open_level', '('.implode(',', $open_levels).')', 'IN'));
        $objs = &$this->getObjects($criteria, false, '', false, $join);

        return $objs;
    }

    /**
     * get ids of private-only or not certified in any group/public index item.
     * useful for private item number/storage limit check.
     *
     * @param uid
     *
     * @return array of integer of item id(s)
     */
    public function getPrivateItemIdsByUid($uid)
    {
        $index_group_index_link_handler = &xoonips_getormhandler('xoonips', 'index_group_index_link');
        $join = new XooNIpsJoinCriteria('xoonips_item_basic', 'item_id', 'item_id');
        $criteria = new Criteria('uid', $uid);
        $index_group_index_links = &$index_group_index_link_handler->getObjects($criteria, false, '', null, $join);
        $iids = array();
        $certified_iids = array();
        foreach ($index_group_index_links as $index_group_index_link) {
            $item_id = $index_group_index_link->get('item_id');
            $iids[$item_id] = $item_id;
            if ($index_group_index_link->get('certify_state') == CERTIFIED) {
                $certified_iids[$item_id] = $item_id;
            }
        }
        $private_iids = array_diff_assoc($iids, $certified_iids);

        return array_keys($private_iids);
    }

    /**
     * Get array of XooNIpsOrmIndexGroupIndexLink by public index id.
     *
     * @param int $index_id public index id
     *
     * @return array of XooNIpsOrmIndexGroupIndexLink(index_group_index_link_id is a key of an array)
     */
    public function getByIndexId($index_id)
    {
        $criteria = new Criteria('index_id', intval($index_id));
        $result = array();
        $links = &$this->getObjects($criteria);
        foreach ($links as $link) {
            $result[$link->get('index_group_index_link_id')] = $link;
        }

        return $result;
    }

    /**
     * Get array of XooNIpsOrmIndexGroupIndexLink by group index id.
     *
     * @param int $group_index_id group index id
     *
     * @return array of XooNIpsOrmIndexGroupIndexLink(index_group_index_link_id is a key of an array)
     */
    public function getByGroupIndexId($group_index_id)
    {
        $criteria = new Criteria('group_index_id', intval($group_index_id));
        $result = array();
        $links = &$this->getObjects($criteria);
        foreach ($links as $link) {
            $result[$link->get('index_group_index_link_id')] = $link;
        }

        return $result;
    }

    /**
     * get XooNIpsOrmIndexGroupIndexLink object having specified public_index_id and group_index_id.
     *
     * @param public_index_id id of index
     * @param group_index_id id of index
     *
     * @return XooNIpsOrmIndexGroupIndexLink object
     */
    public function &getByPublicIndexIdAndGroupIndexId($public_index_id, $group_index_id)
    {
        $criteria = new CriteriaCompo();
        $criteria->add(new Criteria('index_id', $index_id));
        $criteria->add(new Criteria('group_index_id', $group_index_id));
        $results = &$this->getObjects($criteria);
        if (empty($results)) {
            $ret = false;

            return $ret;
        }

        return $results[0];
    }

    /**
     * make public group indexes to public index.
     *
     * @param int   $to_index_id     public index id
     * @param array $group_index_ids group index ids
     * @result bool true if all indexes make public
     */
    public function requireToMakePublic($to_index_id, $group_index_ids)
    {
        $index_handler = &xoonips_getormhandler('xoonips', 'index');

        foreach ($group_index_ids as $group_index_id) {
            // check exist xoonips_index_group_index_link
            $index_group_index_link_handler = &xoonips_getormhandler('xoonips', 'index_group_index_link');

            $criteria1 = new CriteriaCompo();
            $criteria1->add(new Criteria('index_id', $to_index_id));
            $criteria1->add(new Criteria('group_index_id', $group_index_id));
            if ($index_group_index_link_handler->getObjects($criteria1)) {
                // skip if already inserted.
                continue;
            }

            $group_index = &$index_handler->get($group_index_id);
            if (!$group_index) {
                trigger_error("cannot get group index(id=$group_index_id)");

                return false;
            }

            // do when not exist
            // insert index_group_index_link
            $index_group_index_link_handler = &xoonips_getormhandler('xoonips', 'index_group_index_link');
            $index_group_index_link = &$index_group_index_link_handler->create();
            $index_group_index_link->set('index_id', $to_index_id);
            $index_group_index_link->set('group_index_id', $group_index_id);
            $index_group_index_link->set('gid', $group_index->get('gid'));
            $index_group_index_link->set('uid', $_SESSION['xoopsUserId']);
            if (!$index_group_index_link_handler->insert($index_group_index_link)) {
                trigger_error('cannot insert index_group_index_link');

                return false;
            }

            // lock index and all certified items under the index
            $index_handler = &xoonips_getormhandler('xoonips', 'index');
            $group_index->lock();
            $index_handler->lockAllDescendents($group_index_id);

            $item_lock_handler = &xoonips_getormhandler('xoonips', 'item_lock');
            $index_item_link_handler = &xoonips_getormhandler('xoonips', 'index_item_link');
            $descendents = array(
            $group_index_id,
            );
            foreach ($index_handler->getAllDescendents($group_index_id) as $index) {
                $descendents[] = $index->get('index_id');
            }
            $criteria1 = new CriteriaCompo();
            $criteria1->add(new Criteria('index_id', '('.join(',', $descendents).')', 'IN'));
            $criteria1->add(new Criteria('certify_state', CERTIFIED));
            foreach ($index_item_link_handler->getObjects($criteria1) as $row) {
                if (!$item_lock_handler->lock($row->get('item_id'))) {
                    trigger_error('cannot lock: '.$row->get('item_id'));

                    return false;
                }
            }

            // $eventlog_handler =& xoonips_getormhandler( 'xoonips', 'event_log' );
            // if( !$eventlog_handler->recordGroupIndexToPublicEvent($to_index_id, $group_index_id, $group_index->get('gid') ) ){
            //   trigger_error("cannot record group index to public event");
            //   return false;
            // }
        }

        return true;
    }

    /**
     * make public group indexes to public index.
     *
     * @param int   $to_index_id     public index id
     * @param array $group_index_ids group index ids
     * @result bool true if all indexes make public
     */
    public function makePublic($to_index_id, $group_index_ids)
    {
        $index_handler = &xoonips_getormhandler('xoonips', 'index');

        foreach ($group_index_ids as $group_index_id) {
            $index_group_index_link_handler = &xoonips_getormhandler('xoonips', 'index_group_index_link');

            $criteria1 = new CriteriaCompo();
            $criteria1->add(new Criteria('index_id', $to_index_id));
            $criteria1->add(new Criteria('group_index_id', $group_index_id));
            if (!$index_group_index_link_handler->deleteAll($criteria1)) {
                trigger_error("cannot delete row of index_group_index_link:$to_index_id, $group_index_ids");

                return false;
            }

            $group_index = &$index_handler->get($group_index_id);
            if (!$group_index) {
                trigger_error("cannot get group index(id=$group_index_id)");

                return false;
            }

            // create public index
            $index_compo_handler = &xoonips_getormcompohandler('xoonips', 'index');
            if (!$index_compo_handler->duplicateIndexStructure($to_index_id, $group_index_id)) {
                trigger_error("cannot duplicate index structure: $to_index_id, $group_index_id");

                return false;
            }

            // $eventlog_handler =& xoonips_getormhandler( 'xoonips', 'event_log' );
            // if( !$eventlog_handler->recordGroupIndexToPublicEvent($to_index_id, $group_index_id, $group_index->get('gid') ) ){
            //   trigger_error("cannot record group index to public event");
            //   return false;
            // }
            if (!$this->unlock($group_index_id)) {
                trigger_error("cannot unlock: $group_index_id");

                return false;
            }
        }

        return true;
    }

    /**
     * reject to make public group indexes to public index.
     *
     * @param int   $to_index_id     public index id
     * @param array $group_index_ids group index ids
     * @result bool true if all indexes make public
     */
    public function rejectMakePublic($to_index_id, $group_index_ids)
    {
        $index_handler = &xoonips_getormhandler('xoonips', 'index');

        foreach ($group_index_ids as $group_index_id) {
            $index_group_index_link_handler = &xoonips_getormhandler('xoonips', 'index_group_index_link');

            $criteria1 = new CriteriaCompo();
            $criteria1->add(new Criteria('index_id', $to_index_id));
            $criteria1->add(new Criteria('group_index_id', $group_index_id));
            if (!$index_group_index_link_handler->deleteAll($criteria1)) {
                trigger_error("cannot delete row of index_group_index_link:$to_index_id, $group_index_id");

                return false;
            }

            if (!$this->unlock($group_index_id)) {
                trigger_error("cannot unlock: $group_index_id");

                return false;
            }
        }

        return true;
    }

    /**
     * unlock index and items
     * - unlock the index and all of descendents
     * - unlock all of items in the index and all of descendents.
     *
     * @param int $index_id index id to unlock
     *
     * @return bool false if unlock failure
     */
    public function unlock($index_id)
    {
        $index_handler = &xoonips_getormhandler('xoonips', 'index');
        $indllllex = &$index_handler->get($index_id);

        // unlock index and all certified items under the index
        $index_handler = &xoonips_getormhandler('xoonips', 'index');
        $index = &$index_handler->get($index_id);
        if (!$index || !$index->unlock() || !$index_handler->unlockAllDescendents($index_id)) {
            trigger_error("cannot unlock descendents: $index_id");

            return false;
        }

        $item_lock_handler = &xoonips_getormhandler('xoonips', 'item_lock');
        $index_item_link_handler = &xoonips_getormhandler('xoonips', 'index_item_link');
        $descendents = array(
        $index_id,
        );
        foreach ($index_handler->getAllDescendents($index_id) as $index) {
            $descendents[] = $index->get('index_id');
        }
        foreach ($index_item_link_handler->getObjects(new Criteria('index_id', '('.join(',', $descendents).')', 'IN')) as $row) {
            if (!$item_lock_handler->unlock($row->get('item_id'))) {
                trigger_error('cannot unlock item: '.$row->get('item_id'));

                return false;
            }
        }

        return true;
    }

    /**
     * @param array  $to_index_ids      public index ids
     * @param array  $group_index_ids   group index ids
     * @param string $notification_name group_item_certify_request|group_item_certified|group_item_rejected
     * @result void
     */
    public function notifyMakePublicGroupIndex($to_index_ids, $group_index_ids, $notification_name)
    {
        global $xoopsModule, $xoopsUser;

        $member_handler = &xoops_gethandler('member');
        $notification_handler = &xoops_gethandler('notification');
        $config_handler = &xoonips_getormhandler('xoonips', 'config');
        $index_handler = &xoonips_getormhandler('xoonips', 'index');
        $xgroup_handler = &xoonips_gethandler('xoonips', 'group');
        $index_item_link_handler = &xoonips_getormhandler('xoonips', 'index_item_link');
        $index_compo_handler = &xoonips_getormcompohandler('xoonips', 'index');

        if (empty($to_index_ids) || empty($group_index_ids)) {
            return;
        }

        $group_index = &$index_handler->get(@$group_index_ids[0]);
        if (!$group_index) {
            trigger_error('group index not found: '.@$group_index_ids[0]);

            return false;
        }
        $group = &$xgroup_handler->getGroupObject($group_index->get('gid'));
        if (!$group) {
            trigger_error('group not found: gid='.$group_index->get('gid'));

            return false;
        }

        // define tags here for notification message
        $tags['ORIGIN_GROUP_NAME'] = $group->get('gname');
        $tags['ORIGIN_GROUP_ADMIN_NAME'] = $xoopsUser->getVar('name');
        $tags['GROUP_INDEX_SUMMARY'] = join("\n", $this->get_index_summaries($group_index_ids));
        $tags['CERTIFY_URL'] = XOOPS_URL.'/modules/'.$xoopsModule->dirname().'/groupcertify.php';
        $tags['NEW_PUBLIC_INDEX'] = join("\n", $this->get_index_summaries($to_index_ids));
        $tags['INDEX'] = '';
        foreach ($to_index_ids as $id) {
            $tags['INDEX'] .= '/'.join('/', $index_compo_handler->getIndexPathNames($id))."\n";
        }

        // send message using notification
        // get administrator uids
        $moderator_uids = $member_handler->getUsersByGroup($config_handler->getValue('moderator_gid'));
        if (!is_array($moderator_uids)) {
            $moderator_uids = array();
        }

        if ($notification_name == 'group_item_certify_request') {
            // when admin certify required
            // to all moderator
            $result = $notification_handler->triggerEvent('administrator', 0, $notification_name, $tags, $moderator_uids);
        } elseif ($notification_name == 'group_item_certified' || $notification_name == 'group_item_rejected') {
            // when auto certify
            // to all moderators and target group administrators
            $target_users = array_unique(array_merge($moderator_uids, $xgroup_handler->getUserIds($group->get('gid'), true)));
            $notification_handler->triggerEvent('user', 0, $notification_name, $tags, $target_users);
        }
    }

    /**
     * get index summary string like '/FOO/BAR(999)'
     * - index path
     * - number of item in its index.
     *
     * @param array $index_ids array of integer of index id to get sumary
     *
     * @return array summary strings
     */
    public function get_index_summaries($index_ids)
    {
        $index_compo_handler = &xoonips_getormcompohandler('xoonips', 'index');
        $index_item_link_handler = &xoonips_getormhandler('xoonips', 'index_item_link');
        $result = array();

        foreach ($index_ids as $index_id) {
            // store for notification
            $rows = &$index_item_link_handler->getObjects(new Criteria('index_id', $index_id), false, 'count(*)');
            if ($rows && $rows[0]->getExtraVar('count(*)') > 0) {
                $result[] = sprintf('%s(%d)', '/'.join('/', $index_compo_handler->getIndexPathNames($index_id)), $rows[0]->getExtraVar('count(*)'));
            } else {
                $result[] = sprintf('%s', '/'.join('/', $index_compo_handler->getIndexPathNames($index_id)));
            }
        }

        return $result;
    }
}
