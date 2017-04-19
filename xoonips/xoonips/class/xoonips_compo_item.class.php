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

require_once XOOPS_ROOT_PATH.'/modules/xoonips/class/base/relatedobject.class.php';

define('XOONIPS_TEMPLATE_TYPE_TRANSFER_ITEM_DETAIL', 'transfer_item_detail');
define('XOONIPS_TEMPLATE_TYPE_TRANSFER_ITEM_LIST', 'transfer_item_list');
define('XOONIPS_TEMPLATE_TYPE_ITEM_DETAIL', 'item_detail');
define('XOONIPS_TEMPLATE_TYPE_ITEM_LIST', 'item_list');

//- - - - - - - - - - - - - - - - - - - - - - - - - - - -
//
// Handlers
//
//- - - - - - - - - - - - - - - - - - - - - - - - - - - -

/**
 * @brief class of item data object handler.
 *
 *
 * @li base class of items depends on xoonips_item_basic table.
 */
class XooNIpsItemCompoHandler extends XooNIpsRelatedObjectHandler
{
    public $db = null;

    public function __construct(&$db)
    {
        $this->db = &$db;
        parent::__construct($db);
        parent::__init_handler('basic', xoonips_getormhandler('xoonips', 'item_basic'), 'item_id');
        $this->addHandler('titles', xoonips_getormhandler('xoonips', 'title'), 'item_id', true);
        $this->addHandler('keywords', xoonips_getormhandler('xoonips', 'keyword'), 'item_id', true);
        $this->addHandler('indexes', xoonips_getormhandler('xoonips', 'index_item_link'), 'item_id', true);
        $this->addHandler('changelogs', xoonips_getormhandler('xoonips', 'changelog'), 'item_id', true);
        $this->addHandler('related_tos', xoonips_getormhandler('xoonips', 'related_to'), 'parent_id', true);
    }

    public function &create()
    {
        $item = new XooNIpsItemCompo();

        return $item;
    }

    /**
     * gets a value object.
     *
     * @param string $ext_id extended item_id
     * @retval XooNIpsItemCompo
     * @retval false
     */
    public function &getByExtId($id)
    {
        static $falseVar = false;

        $handler = &xoonips_getormhandler('xoonips', 'item_basic');
        $objs = &$handler->getObjects(new Criteria('doi', addslashes($id)));
        if (!$objs || 1 != count($objs)) {
            return $falseVar;
        }

        $obj = &$this->get($objs[0]->get('item_id'));
        if ($obj) {
            return $obj;
        }

        return $falseVar;
    }

    /**
     * return true if permitted to this item.
     *
     * @param id id of item
     * @param uid uid who access to this item
     * @param operation read|write|delete
     *
     * @return true if permitted
     */
    public function getPerm($id, $uid, $operation)
    {
        $handler = new XooNIpsItemInfoCompoHandler($this->db);

        return $handler->getPerm($id, $uid, $operation);
    }

    /**
     * get parent item ids.
     *
     * @param int item_id
     *
     * @return
     */
    public function getParentItemIds($item_id)
    {
        $handler = new XooNIpsItemInfoCompoHandler($this->db);

        return $handler->getParentItemIds($item_id);
    }

    public function getItemAbstractTextById($item_id)
    {
        $handler = &xoonips_getormcompohandler('xoonips', 'item');
        $item = &$handler->get($item_id);

        return $item->getItemAbstractText();
    }

    /**
     * return url to show item detail.
     */
    public function getItemDetailUrl($item_id)
    {
        $handler = new XooNIpsItemInfoCompoHandler($this->db);

        return $handler->getItemDetailUrl($item_id);
    }
}

/**
 * @brief class of item data object handler according to iteminfo
 *
 *
 * @li base class of items depends on xoonips_item_basic table.
 */
class XooNIpsItemInfoCompoHandler extends XooNIpsRelatedObjectHandler
{
    public $iteminfo = null;
    public $db = null;

    public function __construct(&$db, $module = null)
    {
        parent::__construct($db);
        $this->db = &$db;
        if (isset($module) && is_null($this->iteminfo)) {
            include XOOPS_ROOT_PATH.'/modules/'.$module.'/iteminfo.php';
            $this->iteminfo = &$iteminfo;
            //
            // add orm handler according to $iteminfo['orm']
            foreach ($this->iteminfo['orm'] as $orminfo) {
                if ($orminfo['field'] == $this->iteminfo['ormcompo']['primary_orm']) { //orm of primary table
                    parent::__init_handler($orminfo['field'], xoonips_getormhandler($orminfo['module'], $orminfo['name']), $orminfo['foreign_key']);
                } else {
                    $this->addHandler($orminfo['field'], xoonips_getormhandler($orminfo['module'], $orminfo['name']), $orminfo['foreign_key'], isset($orminfo['multiple']) ? $orminfo['multiple'] : false, isset($orminfo['criteria']) ? $orminfo['criteria'] : null);
                }
            }
        }
    }

    /**
     * gets a value object.
     *
     * @param string $ext_id extended item_id
     * @retval XooNIpsItemInfoCompo
     * @retval false
     */
    public function &getByExtId($id)
    {
        static $falseVar = false;

        $handler = &xoonips_getormhandler('xoonips', 'item_basic');
        $objs = &$handler->getObjects(new Criteria('doi', addslashes($id)));
        if (!$objs || 1 != count($objs)) {
            return $falseVar;
        }

        $obj = &$this->get($objs[0]->get('item_id'));
        if ($obj) {
            return $obj;
        }

        return $falseVar;
    }

    /**
     * return true if permitted to this item.
     *
     * @param item_id id of item
     * @param uid uid who access to this item
     * @param operation read|write|delete|export
     *
     * @return true if permitted
     */
    public function getPerm($item_id, $uid, $operation)
    {
        if (!in_array(
            $operation, array(
            'read',
            'write',
            'delete',
            'export',
            )
        )
        ) {
            return false; // bad operation
        }

        $item_basic_handler = &xoonips_getormhandler('xoonips', 'item_basic');
        $item_basic = &$item_basic_handler->get($item_id);
        if (!$item_basic || $item_basic->get('item_type_id') == ITID_INDEX) {
            return false; // no such item
        }

        $item_lock_handler = &xoonips_getormhandler('xoonips', 'item_lock');
        if (($operation == 'write' || $operation == 'delete')
            && $item_lock_handler->isLocked($item_id)
        ) {
            return false; // cannot write/delete locked item
        }

        $index_group_index_link_handler = &xoonips_getormhandler('xoonips', 'index_group_index_link');
        if (($operation == 'write' || $operation == 'delete')
            && $index_group_index_link_handler->getObjectsByItemId($item_id)
        ) {
            //cannot write/delete if item is in group index that is publication required.
            return false;
        }

        // moderator or admin
        $member_handler = &xoonips_gethandler('xoonips', 'member');
        if ($member_handler->isModerator($uid) || $member_handler->isAdmin($uid)) {
            return true; // moderator or admin
        }

        if ($uid == UID_GUEST) {
            $xconfig_handler = &xoonips_getormhandler('xoonips', 'config');
            $target_user = $xconfig_handler->getValue(XNP_CONFIG_PUBLIC_ITEM_TARGET_USER_KEY);
            if ($target_user != XNP_CONFIG_PUBLIC_ITEM_TARGET_USER_ALL) {
                return false; // guest not allowed
            }
            // only allowed to read public certified item
            if ($operation != 'read') {
                return false;
            }
        }

        $index_item_link_handler = &xoonips_getormhandler('xoonips', 'index_item_link');
        if ($operation == 'write') {
            // update: item.uid == $uid
            //permit owner
            if ($item_basic->get('uid') == $uid) {
                return true;
            }
            //permit group admin if group share certified
            $criteria = new CriteriaCompo();
            $criteria->add(new Criteria('item_id', $item_id));
            $criteria->add(new Criteria('open_level', OL_GROUP_ONLY));
            $criteria->add(new Criteria('uid', $uid, '=', 'tgul'));
            $criteria->add(new Criteria('is_admin', 1));
            $criteria->add(new Criteria('certify_state', CERTIFIED));
            $join1 = new XooNIpsJoinCriteria('xoonips_index', 'index_id', 'index_id', 'LEFT', 'tx');
            $join2 = new XooNIpsJoinCriteria('xoonips_groups_users_link', 'gid', 'gid', 'LEFT', 'tgul');
            $join1->cascade($join2, 'tx', true);
            $index_item_links = &$index_item_link_handler->getObjects($criteria, false, '', null, $join1);
            if ($index_item_links) {
                return true;
            }

            return false;
        } elseif ($operation == 'delete') {
            // delete: item.uid == $uid && not_group && not_public
            if ($item_basic->get('uid') != $uid) {
                return false;
            }

            // get non-private index_item_link
            // index_item_link <- index
            $criteria = new CriteriaCompo();
            $criteria->add(new Criteria('item_id', $item_id));
            $criteria->add(new Criteria('open_level', OL_PRIVATE, '!='));
            $join = new XooNIpsJoinCriteria('xoonips_index', 'index_id', 'index_id');
            $index_item_links = &$index_item_link_handler->getObjects($criteria, false, '', null, $join);

            return  count($index_item_links) == 0;
        } elseif ($operation == 'export') {
            // export: item.uid == $uid || group && group admin
            if ($item_basic->get('uid') == $uid) {
                return true;
            }

            // group && group admin ?
            // index_item_link <- index <- groups_users_link
            $criteria = new CriteriaCompo();
            $criteria->add(new Criteria('item_id', $item_id));
            $criteria->add(new Criteria('open_level', OL_GROUP_ONLY));
            $criteria->add(new Criteria('uid', $uid, '=', 'tgul'));
            $criteria->add(new Criteria('is_admin', 1));
            $join1 = new XooNIpsJoinCriteria('xoonips_index', 'index_id', 'index_id', 'LEFT', 'tx');
            $join2 = new XooNIpsJoinCriteria('xoonips_groups_users_link', 'gid', 'gid', 'LEFT', 'tgul');
            $join1->cascade($join2, 'tx', true);
            $index_item_links = &$index_item_link_handler->getObjects($criteria, false, '', null, $join1);

            return  count($index_item_links) != 0;
        } elseif ($operation == 'read') {
            // read: item.uid == $uid || group_ceritfied && group_member || group && group admin || public_certified
            if ($item_basic->get('uid') == $uid) {
                return true;
            }

            // index_item_link <- index <- groups_users_link
            $criteria = new CriteriaCompo();
            $criteria->add(new Criteria('open_level', OL_PUBLIC, '=', 'tx'), 'and');
            $criteria->add(new Criteria('certify_state', CERTIFIED, '='), 'and'); // public index && certified
            $criteria->add(new Criteria('open_level', OL_GROUP_ONLY, '=', 'tx'), 'or'); // group index && group admin
            $criteria->add(new Criteria('uid', $uid, '=', 'tgul'), 'and');
            $criteria->add(new Criteria('is_admin', 1, '=', 'tgul'), 'and');
            $criteria->add(new Criteria('open_level', OL_GROUP_ONLY, '=', 'tx'), 'or'); // group index && group member && certified
            $criteria->add(new Criteria('uid', $uid, '=', 'tgul'), 'and');
            $criteria->add(new Criteria('certify_state', CERTIFIED, '='), 'and');
            $criteria = new CriteriaCompo($criteria);
            $criteria->add(new Criteria('item_id', $item_id));
            $join1 = new XooNIpsJoinCriteria('xoonips_index', 'index_id', 'index_id', 'LEFT', 'tx');
            $join2 = new XooNIpsJoinCriteria('xoonips_groups_users_link', 'gid', 'gid', 'LEFT', 'tgul');
            $join1->cascade($join2, 'tx', true);
            $index_item_links = &$index_item_link_handler->getObjects($criteria, false, '', null, $join1);
            if (count($index_item_links) != 0) {
                return true;
            }

            // item transferee?
            $transfer_request_handler = &xoonips_getormhandler('xoonips', 'transfer_request');
            $criteria = new CriteriaCompo();
            $criteria->add(new Criteria('item_id', $item_id));
            $criteria->add(new Criteria('to_uid', $uid));
            $transfer_request = &$transfer_request_handler->getObjects($criteria);

            return !empty($transfer_request);
        }

        return false;
    }

    /**
     * @brief search item
     *
     * @param query query ( string or CriteriaElement )
     * @param limit the maximum number of rows to return(0 = no limit)
     * @param offset the offset of the first row to return(0 = from beginning)
     * @param uid user ID
     *
     * @return array of item id
     */
    public function search($query, $limit, $offset, $uid)
    {
        if (!$this->iteminfo) {
            return array();
        }

        $modulename = $this->iteminfo['ormcompo']['module'];
        $dummy = false;
        $search_cache_id = false;
        // save xoopsUser
        if (isset($GLOBALS['xoopsUser'])) {
            $old_xoopsUser = $GLOBALS['xoopsUser'];
        } else {
            $old_xoopsUser = null;
        }
        // prepare for xnpSearchExec
        $member_handler = &xoops_gethandler('member');
        $GLOBALS['xoopsUser'] = $member_handler->getUser($uid);
        // search
        $item_ids = array();
        if (xnpSearchExec('quicksearch', $query, $modulename, false, $dummy, $dummy, $dummy, $search_cache_id, false, 'item_metadata')) {
            $search_cache_item_handler = &xoonips_getormhandler('xoonips', 'search_cache_item');
            $criteria = new Criteria('search_cache_id', $search_cache_id);
            $criteria->setSort('item_id');
            $criteria->setStart($offset);
            if ($limit) {
                $criteria->setLimit($limit);
            }
            $search_cache_items = &$search_cache_item_handler->getObjects($criteria);
            foreach ($search_cache_items as $search_cache_item) {
                $item_ids[] = $search_cache_item->get('item_id');
            }
        }
        // restore xoopsUser
        $GLOBALS['xoopsUser'] = $old_xoopsUser;

        return $item_ids;
    }

    /**
     * get iteminfo array.
     */
    public function getIteminfo()
    {
        return $this->iteminfo;
    }

    /**
     * return template filename.
     *
     * @param string $type defined symbol
     *                     XOONIPS_TEMPLATE_TYPE_TRANSFER_ITEM_DETAIL
     *                     or XOONIPS_TEMPLATE_TYPE_TRANSFER_ITEM_LISTL
     *
     * @return template filename
     */
    public function getTemplateFileName($type)
    {
        switch ($type) {
        case XOONIPS_TEMPLATE_TYPE_TRANSFER_ITEM_DETAIL:
            return 'xoonips_transfer_item_detail.html';
        case XOONIPS_TEMPLATE_TYPE_TRANSFER_ITEM_LIST:
            return 'xoonips_transfer_item_list.html';
        default:
            return '';
        }
    }

    /**
     * return template variables of item.
     *
     * @param string $type    defined symbol
     *                        XOONIPS_TEMPLATE_TYPE_TRANSFER_ITEM_DETAIL
     *                        , XOONIPS_TEMPLATE_TYPE_TRANSFER_ITEM_LISTL
     *                        , XOONIPS_TEMPLATE_TYPE_ITEM_DETAIL
     *                        or XOONIPS_TEMPLATE_TYPE_ITEM_LIST
     * @param int    $item_id item id
     * @param int    $uid     user id
     *
     * @return
     */
    public function getTemplateVar($type, $item_id, $uid)
    {
        if (!in_array(
            $type,
            array(
                XOONIPS_TEMPLATE_TYPE_TRANSFER_ITEM_DETAIL,
                XOONIPS_TEMPLATE_TYPE_TRANSFER_ITEM_LIST,
                XOONIPS_TEMPLATE_TYPE_ITEM_DETAIL,
                XOONIPS_TEMPLATE_TYPE_ITEM_LIST,
            )
        )
        ) {
            return array();
        }
        $item = &$this->get($item_id);

        return $this->getBasicTemplateVar($type, $item, $uid);
    }

    /**
     * return template variables of item basic part.
     *
     * @param string $type defined symbol
     *                     XOONIPS_TEMPLATE_TYPE_TRANSFER_ITEM_DETAIL
     *                     , XOONIPS_TEMPLATE_TYPE_TRANSFER_ITEM_LISTL
     *                     , XOONIPS_TEMPLATE_TYPE_ITEM_DETAIL
     *                     or XOONIPS_TEMPLATE_TYPE_ITEM_LIST
     * @param object $item item compo object
     * @param int    $uid
     *
     * @return array variables
     */
    public function getBasicTemplateVar($type, &$item, $uid)
    {
        if (!in_array(
            $type,
            array(
                XOONIPS_TEMPLATE_TYPE_TRANSFER_ITEM_DETAIL,
                XOONIPS_TEMPLATE_TYPE_TRANSFER_ITEM_LIST,
                XOONIPS_TEMPLATE_TYPE_ITEM_DETAIL,
                XOONIPS_TEMPLATE_TYPE_ITEM_LIST,
            )
        )
        ) {
            return array();
        }
        $textutil = &xoonips_getutility('text');
        $item_type_handler = &xoonips_getormhandler('xoonips', 'item_type');

        $basic = &$item->getVar('basic');
        $item_id = $basic->get('item_id');
        $result = array(
            'basic' => array(
                'item_id' => $basic->getVar('item_id', 's'),
                'description' => $basic->getVar('description', 's'),
                'doi' => $basic->getVar('doi', 's'),
                'creation_date' => $basic->getVar('creation_date', 's'),
                'last_update_date' => $basic->getVar('last_update_date', 's'),
                'publication_year' => $this->get_year_template_var($basic->get('publication_year'), $basic->get('publication_month'), $basic->get('publication_mday')),
                'publication_month' => $this->get_month_template_var($basic->get('publication_year'), $basic->get('publication_month'), $basic->get('publication_mday')),
                'publication_mday' => $this->get_mday_template_var($basic->get('publication_year'), $basic->get('publication_month'), $basic->get('publication_mday')),
                'lang' => $this->get_lang_label($basic->get('lang')), ),
            'contributor' => array(),
            'item_type' => array(),
            'titles' => array(),
            'keywords' => array(),
            'changelogs' => array(),
            'indexes' => array(),
            'related_tos' => array(), );

        $user_handler = &xoonips_getormhandler('xoonips', 'xoops_users');
        $user = &$user_handler->get($basic->get('uid'));
        if (is_object($user)) {
            $result['contributor']['name'] = $user->getVar('name', 's');
            $result['contributor']['uname'] = $user->getVar('uname', 's');
        } else {
            $result['contributor']['name'] = 'unknown';
            $result['contributor']['uname'] = 'Zombie User';
        }

        $item_type = &$item_type_handler->get($basic->get('item_type_id'));
        $result['item_type']['display_name'] = $item_type->getVar('display_name', 's');

        foreach ($item->getVar('titles') as $title) {
            $result['titles'][] = array('title' => $title->getVar('title', 's'));
        }

        foreach ($item->getVar('keywords') as $keyword) {
            $result['keywords'][] = array('keyword' => $keyword->getVar('keyword', 's'));
        }

        $changelogs = $item->getVar('changelogs');
        usort($changelogs, array('XooNIpsItemInfoCompoHandler', 'usort_desc_by_log_date'));
        foreach ($changelogs as $changelog) {
            $result['changelogs'][] = array('log_date' => $changelog->getVar('log_date', 's'),
                                            'log' => $changelog->getVar('log', 's'), );
        }

        // get indexes only not item listing for loading performance improvement
        if ($type != XOONIPS_TEMPLATE_TYPE_ITEM_LIST && $type != XOONIPS_TEMPLATE_TYPE_TRANSFER_ITEM_LIST) {
            $xoonips_user_handler = &xoonips_getormhandler('xoonips', 'users');
            $xoonips_user = &$xoonips_user_handler->get($basic->get('uid'));
            $private_index_id = $xoonips_user->get('private_index_id');
            foreach ($item->getVar('indexes') as $link) {
                $result['indexes'][] = array(
                    'path' => $this->get_index_path_by_index_id($link->get('index_id'), $private_index_id, 's'),
                  );
            }
        }

        // get related to part only not item listing for disable to recursive calling
        if ($type != XOONIPS_TEMPLATE_TYPE_ITEM_LIST && $type != XOONIPS_TEMPLATE_TYPE_TRANSFER_ITEM_LIST) {
            $basic_handler = &xoonips_getormhandler('xoonips', 'item_basic');
            foreach ($item->getVar('related_tos') as $related_to) {
                $related_basic = &$basic_handler->get($related_to->get('item_id'));
                if (empty($related_basic)) {
                    continue;
                } // ignore invalid item id
                $related_item_type = &$item_type_handler->get($related_basic->get('item_type_id'));
                $related_item_type_id = $related_item_type->get('item_type_id');
                if ($related_item_type_id == ITID_INDEX) {
                    continue;
                } // ignore index id
                $item_compo_handler = &xoonips_getormcompohandler($related_item_type->getVar('name', 's'), 'item');
                if ($item_compo_handler->getPerm($item_id, $uid, 'read')) {
                    $result['related_tos'][] = array(
                        'filename' => 'db:'.$item_compo_handler->getTemplateFileName(XOONIPS_TEMPLATE_TYPE_TRANSFER_ITEM_LIST),
                        'var' => $item_compo_handler->getTemplateVar(
                            XOONIPS_TEMPLATE_TYPE_TRANSFER_ITEM_LIST,
                            $related_basic->get('item_id'), $uid
                        ), );
                }
            }
        }

        //additional type specific template vars
        switch ($type) {
        case XOONIPS_TEMPLATE_TYPE_TRANSFER_ITEM_LIST:
            $result['url'] = $textutil->html_special_chars(xoonips_get_transfer_request_item_detail_url($basic->get('item_id')));
        case XOONIPS_TEMPLATE_TYPE_ITEM_LIST:
            $result['pending'] = xnpIsPending($item_id);
        }

        return $result;
    }

    /**
     * compare function of usort to sort changelog descent by log_date.
     */
    public function usort_desc_by_log_date($a, $b)
    {
        $a_log_date = $a->get('log_date');
        $b_log_date = $b->get('log_date');
        if ($a_log_date == $b_log_date) {
            return 0;
        }

        return ($a_log_date > $b_log_date) ? -1 : 1;
    }

    public function array_combine($keys, $values)
    {
        $result = array();
        reset($keys);
        reset($values);
        while (current($keys) && current($values)) {
            $result[current($keys)] = current($values);
            next($keys);
            next($values);
        }

        return $result;
    }

    public function get_lang_label($lang)
    {
        $languages = $this->array_combine(
            explode(',', _MD_XOONIPS_ITEM_LANG_OPTION_IDS),
            explode(',', _MD_XOONIPS_ITEM_LANG_OPTION_NAMES)
        );

        if (in_array($lang, array_keys($languages))) {
            return $languages[$lang];
        }

        return '';
    }

    public function get_index_path_by_index_id($index_id, $private_index_id, $fmt = 'n')
    {
        $index_handler = &xoonips_getormcompohandler('xoonips', 'index');

        return '/'.implode('/', $index_handler->getIndexPathNames($index_id, $private_index_id, $fmt));
    }

    /**
     * return text presentation of year, month and day of month.
     *
     * @param int $year
     * @param int $month (1-12)
     * @param int $mday  (1-31)
     *
     * @return associative array of date
     *                     array( 'year' => (year string),
     *                     'month' => (month string),
     *                     'mday' => day of month string );
     */
    public function get_date_template_var($year, $month, $mday)
    {
        $result = array('year' => '', 'month' => '', 'mday' => '');
        $int_year = intval($year);
        $int_month = intval($month);
        $int_mday = intval($mday);

        $result['year'] = date('Y', mktime(0, 0, 0, 1, 1, $int_year));
        $result['month'] = date('M', mktime(0, 0, 0, $int_month, 1, $int_year));
        $result['mday'] = date('j', mktime(0, 0, 0, $int_month, $int_mday, $int_year));

        return $result;
    }

    public function get_year_template_var($year, $month, $mday)
    {
        $result = $this->get_date_template_var($year, $month, $mday);

        return $result['year'];
    }

    public function get_month_template_var($year, $month, $mday)
    {
        $result = $this->get_date_template_var($year, $month, $mday);

        return $result['month'];
    }

    public function get_mday_template_var($year, $month, $mday)
    {
        $result = $this->get_date_template_var($year, $month, $mday);

        return $result['mday'];
    }

    /**
     * get attachment file template var.
     *
     * @param XooNIpsFile
     *
     * @return associative array of template var
     */
    public function getAttachmentTemplateVar($file)
    {
        if ($file->get('file_size') >= 1024 * 1024) {
            $fileSizeStr = sprintf('%01.1f MB', $file->get('file_size') / (1024 * 1024));
        } elseif ($file->get('file_size') >= 1024) {
            $fileSizeStr = sprintf('%01.1f KB', $file->get('file_size') / 1024);
        } else {
            $fileSizeStr = sprintf('%d bytes', $file->get('file_size'));
        }

        $file_type_handler = &xoonips_getormhandler('xoonips', 'file_type');
        $file_handler = &xoonips_getormhandler('xoonips', 'file');

        $basic_handler = &xoonips_getormhandler('xoonips', 'item_basic');
        $basic = &$basic_handler->get($file->get('item_id'));

        $file_type = &$file_type_handler->get($file->get('file_type_id'));

        return array(
            'file_id' => $file->getVar('file_id', 's'),
            'file_name' => $file->getVar('original_file_name', 's'),
            'mime_type' => $file->getVar('mime_type', 's'),
            'file_size' => $fileSizeStr,
            'last_update_date' => $file->get('timestamp'),
            'download_count' => $file->get('download_count'),
            'item_creation_date' => $basic->get('creation_date'),
            'total_download_count' => $file_handler->getTotalDownloadCount($file->get('item_id'), $file_type->get('name')), );
    }

    /**
     * get preview file template var.
     *
     * @param XooNIpsFile $preview
     *
     * @return associative array of template var
     */
    public function getPreviewTemplateVar($preview)
    {
        return array(
            'thumbnail_url' => XOOPS_URL.'/modules/xoonips/image.php?file_id='.$preview->get('file_id').'&amp;thumbnail=1',
            'image_url' => XOOPS_URL.'/modules/xoonips/image.php?file_id='.$preview->get('file_id'),
            'caption' => $preview->getVar('caption', 's'),
        );
    }

    /**
     * get parent item ids.
     *
     * @final
     *
     * @param int item_id
     *
     * @return
     */
    public function getParentItemIds($item_id)
    {
        $result = array();
        $item_type_handler = &xoonips_getormhandler('xoonips', 'item_type');
        $item_types = &$item_type_handler->getObjects(new Criteria('item_type_id', ITID_INDEX, '<>'));
        foreach ($item_types as $item_type) {
            $info_compo_handler = &xoonips_getormcompohandler($item_type->get('name'), 'item');
            $result = array_merge($result, $info_compo_handler->getItemTypeSpecificParentItemIds($item_id));
        }

        return $result;
    }

    /**
     * get parent item ids.
     *
     * @param int item_id
     *
     * @return array
     */
    public function getItemTypeSpecificParentItemIds($item_id)
    {
        return array();
    }

    /**
     * return url to show item detail.
     */
    public function getItemDetailUrl($item_id)
    {
        (method_exists('MyTextSanitizer', 'sGetInstance') and $myts = &MyTextSanitizer::sGetInstance()) || $myts = &MyTextSanitizer::getInstance();
        $basic_handler = &xoonips_getormhandler('xoonips', 'item_basic');
        $basic = &$basic_handler->get($item_id);
        if (!$basic) {
            return '';
        }

        global $xoopsModule;
        $base_url = XOOPS_URL.'/modules/'
            .$xoopsModule->dirname().'/detail.php';
        if ($basic->get('doi') == ''
            || XNP_CONFIG_DOI_FIELD_PARAM_NAME == ''
        ) {
            return $base_url.'?item_id='.intval($item_id);
        }

        return $base_url.'?'.XNP_CONFIG_DOI_FIELD_PARAM_NAME.'='.urlencode($basic->get('doi'));
    }

    public function hasDownloadPermission($uid, $file_id)
    {
        $file_handler = &xoonips_getormhandler('xoonips', 'file');
        $file = &$file_handler->get($file_id);
        if (!$file) {
            return false;
        } // no such file

        $item_id = $file->get('item_id');
        if (!$item_id) {
            return false;
        } // file is not belong to any item

        $item_compo = $this->get($item_id);
        if (!$item_compo) {
            return false;
        } // bad item

        $detail = $item_compo->getVar('detail');
        if (!$detail) {
            return false;
        } // bad item

        $iteminfo = $this->getIteminfo();
        if (empty($iteminfo)) {
            return false;
        }

        // get module option 'enable_dl_limit'
        $mhandler = &xoops_gethandler('module');
        $module = $mhandler->getByDirname($iteminfo['ormcompo']['module']);
        $chandler = &xoops_gethandler('config');
        $assoc = $chandler->getConfigsByCat(false, $module->mid());
        if (isset($assoc['enable_dl_limit']) && $assoc['enable_dl_limit'] == '1') {
            // guest enabled?
            if ($uid == UID_GUEST && $detail->get('attachment_dl_limit')) {
                return false;
            }
        }

        return true;
    }

    public function isValidForPubicOrGroupShared($item_compo)
    {
        $iteminfo = $this->getIteminfo();
        $item_type_name = $iteminfo['ormcompo']['module'];
        $detail_item_type_handler = &xoonips_getormhandler($item_type_name, 'item_type');
        $basic = $item_compo->getVar('basic');
        $item_type_id = $basic->get('item_type_id');
        $detail_item_type = $detail_item_type_handler->get($item_type_id);

        // error if add to public/group and no rights input
        $detail = $item_compo->getVar('detail');
        if ($detail_item_type->getFieldByName('detail', 'rights')) {
            if ($detail_item_type->getFieldByName('detail', 'use_cc')) {
                $use_cc = $detail->get('use_cc');
            } else {
                $use_cc = 0;
            }
            if ($detail->get('rights') == '' && $use_cc == 0) {
                return false;
            }
        }
        // error if add to public/group and no readme input
        if ($detail_item_type->getFieldByName('detail', 'readme')) {
            if ($detail->get('readme') == '') {
                return false;
            }
        }

        return true;
    }
}

//- - - - - - - - - - - - - - - - - - - - - - - - - - - -
//
// Data object
//
//- - - - - - - - - - - - - - - - - - - - - - - - - - - -

/**
 * @brief data object of xoonips item(basic fields)
 *
 * @li getVar('basic') : {@link XooNIpsItemCompoBasic}
 * @li getVar('titles') : array of {@link XooNIpsTitle}
 * @li getVar('keywords') : array of {@link XooNIpsKeyword}
 * @li getVar('related_tos') : array of {@link XooNIpsRelatedTo}
 * @li getVar('changelogs') : array of {@link XooNIpsChangelog}
 * @li getVar('indexes') : array of {@link XooNIpsIndexItemLink}
 */
class XooNIpsItemCompo extends XooNIpsRelatedObject
{
    public function __construct()
    {
        // basic
        $basic_handler = &xoonips_getormhandler('xoonips', 'item_basic');
        $this->initVar('basic', $basic_handler->create(), true);
        $this->initVar('titles', $titles = array(), true);
        $this->initVar('keywords', $keywrods = array());
        $this->initVar('related_tos', $related_tos = array());
        $this->initVar('changelogs', $changelogs = array());
        $this->initVar('indexes', $indexes = array());
    }

    public function getItemAbstractText()
    {
        $basic = &$this->getVar('basic');
        $titles = &$this->getVar('titles');
        $indexes = &$this->getVar('indexes');
        $handler = &xoonips_getormhandler('xoonips', 'item_type');
        $itemtype = &$handler->get($basic->get('item_type_id'));

        $user_handler = &xoops_gethandler('user');
        $user = &$user_handler->get($basic->get('uid'));

        $ret = array();
        foreach ($titles as $title) {
            $ret[] = $title->get('title');
        }
        $ret[] = $user->getVar('uname', 'n');
        if ($itemtype) {
            $ret[] = $itemtype->get('display_name');
        }

        $index_handler = &xoonips_getormcompohandler('xoonips', 'index');
        $index_item_link_handler = &xoonips_getormhandler('xoonips', 'index_item_link');
        $criteria = new Criteria('item_id', $basic->get('item_id'));
        $index_item_links = &$index_item_link_handler->getObjects($criteria);
        foreach ($index_item_link_handler->getObjects($criteria) as $link) {
            $ret[] = '/'.implode('/', $index_handler->getIndexPathNames($link->get('index_id')));
        }

        return implode("\n", $ret);
    }
}

/**
 * @brief data object of xoonips item according to iteminfo
 */
class XooNIpsItemInfoCompo extends XooNIpsRelatedObject
{
    public $iteminfo = null;

    public function __construct($module = null)
    {
        if (isset($module) && is_null($this->iteminfo)) {
            include XOOPS_ROOT_PATH.'/modules/'.$module.'/iteminfo.php';
            $this->iteminfo = &$iteminfo;
            // add orm object according to $this -> iteminfo['orm']
            foreach ($this->iteminfo['orm'] as $orminfo) {
                $handler = &xoonips_getormhandler($orminfo['module'], $orminfo['name']);
                if (isset($orminfo['multiple']) ? $orminfo['multiple'] : false) {
                    $ary = array();
                    $this->initVar($orminfo['field'], $ary, isset($orminfo['required']) ? $orminfo['required'] : false);
                    unset($ary);
                } else {
                    $this->initVar($orminfo['field'], $handler->create(), isset($orminfo['required']) ? $orminfo['required'] : false);
                }
            }
        }
    }

    /**
     * get iteminfo array.
     */
    public function getIteminfo()
    {
        return $this->iteminfo;
    }

    /**
     * get child item ids of this item.
     *
     * @return array array of child item ids
     */
    public function getChildItemIds()
    {
        return array();
    }
}
