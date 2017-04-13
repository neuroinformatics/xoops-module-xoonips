<?php

// $Revision: 1.11.4.1.2.14 $
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
require_once 'include/AL.php';
require_once 'include/lib.php';
require_once 'class/base/pagenavi.class.php';

$myuid = is_object($xoopsUser) ? $xoopsUser->getVar('uid', 'n') : UID_GUEST;

$formdata = &xoonips_getutility('formdata');
$uid = $formdata->getValue('get', 'uid', 'i', false, $myuid);
$item_type_id = $formdata->getValue('post', 'page', 'i', false, 0);
$myxoopsConfigUser = &xoonips_get_xoops_configs(XOOPS_CONF_USER);

if ($uid == UID_GUEST) {
    redirect_header(XOOPS_URL.'/', 3, _US_SORRYNOTFOUND);
    exit();
}

$u_handler = &xoonips_getormhandler('xoonips', 'xoops_users');
$xu_handler = &xoonips_getormhandler('xoonips', 'users');
$xmember_handler = &xoonips_gethandler('xoonips', 'member');

// get user's information
$u_obj = &$u_handler->get($uid);
$xu_obj = &$xu_handler->get($uid);
if (!is_object($u_obj) || !is_object($xu_obj)) {
    redirect_header(XOOPS_URL.'/', 3, _US_SORRYNOTFOUND);
    exit();
}

// part of self introduction
$is_activated = ($u_obj->get('level') > 0);
$is_certified = ($xu_obj->get('activate') == 1);
if (!$is_activated || !$is_certified) {
    // profile of 'not activated / certified user' is not displayed.
    redirect_header(XOOPS_URL.'/', 3, _US_SELECTNG);
    exit();
}
$is_admin = $xmember_handler->isAdmin($myuid);
$is_owner = ($uid == $myuid);
$is_editable = ($is_admin || $is_owner);
$is_deletable = ($is_editable && $myxoopsConfigUser['self_delete'] == 1);
$avatar = '../../uploads/'.$u_obj->getVar('user_avatar', 'e');

(method_exists(MyTextSanitizer, sGetInstance) and $myts = &MyTextSanitizer::sGetInstance()) || $myts = &MyTextSanitizer::getInstance();
$textutil = &xoonips_getutility('text');

// breadcrumbs
$breadcrumbs = array(
    array(
       'name' => _MD_XOONIPS_USERLIST_TITLE,
       'url' => XOONIPS_URL.'/userlist.php',
    ),
    array(
       'name' => $u_obj->getVar('uname', 's'),
       'url' => XOONIPS_URL.'/showusers.php?uid='.$uid,
    ),
);

// publication list
$item_counts = _xoonips_showusers_get_count_items($uid);
$item_type_id = $formdata->getValue('post', 'item_type_id', 'i', false);
$page = $formdata->getValue('post', 'page', 'i', false, 1);
if (count($item_counts) != 0) {
    // validate item type id
    $item_type_order = array_keys($item_counts);
    if (!in_array($item_type_id, $item_type_order)) {
        // override existing item type id
        $item_type_id = $item_type_order[0];
    }
    $limit = 20;
    $sort = 'publication_date';
    $order = 'DESC';
    $navi = new XooNIpsPageNavi($item_counts[$item_type_id]['count'], $limit, $page);
    $navi->setSort($sort);
    $navi->setOrder($order);
    $item_ids = _xoonips_showusers_get_item_ids($item_type_id, $uid, $navi);
    $page = $navi->getPage();
    // set page navi
    $pagenavi = $navi->getTemplateVars(10);
    $pagenavi['onclick'] = 'xoonips_showusers_select_page';
    // set page tabs
    $pagetabs = array();
    foreach ($item_counts as $itid => $item_type) {
        $pagetabs[] = array(
            'id' => $itid,
            'label' => sprintf('%s(%u)', $item_type['label'], $item_type['count']),
            'selected' => ($itid == $item_type_id),
            'onclick' => 'xoonips_showusers_select_itemtype',
        );
    }
} else {
    // publication item not found
    $item_ids = array();
    $pagenavi = false;
    $pagetabs = false;
}

// assign template
$xoopsOption['template_main'] = 'xoonips_showusers.html';
require XOOPS_ROOT_PATH.'/header.php';
$xoopsTpl->assign('xoops_breadcrumbs', $breadcrumbs);
$xoopsTpl->assign('is_owner', $is_owner);
$xoopsTpl->assign('is_editable', $is_editable);
$xoopsTpl->assign('is_deletable', $is_deletable);
// - user information
$xoopsTpl->assign('user_avatarurl', $avatar);
$xoopsTpl->assign('user_signature', $u_obj->getVar('user_sig', 's'));
// - xoonips user information
$xoopsTpl->assign('user', $u_obj->getVarArray('s'));
$xoopsTpl->assign('xuser', $xu_obj->getVarArray('s'));
$xoopsTpl->assign('item_type_id', $item_type_id);
$xoopsTpl->assign('page', $page);
$xoopsTpl->assign('position', _xoonips_showusers_get_position($xu_obj->get('posi')));
$xoopsTpl->assign('cvitaes', _xoonips_showusers_get_cvitaes($uid));
$xoopsTpl->assign('tabs', $pagetabs);
$xoopsTpl->assign('navi', $pagenavi);
$xoopsTpl->assign('publications', itemid2ListBlock($item_ids));
require XOOPS_ROOT_PATH.'/footer.php';
exit();

function _xoonips_showusers_get_position($posi_id)
{
    $positions_handler = &xoonips_getormhandler('xoonips', 'positions');
    $posi_obj = &$positions_handler->get($posi_id);

    return  is_object($posi_obj) ? $posi_obj->getVar('posi_title', 's') : '';
}

function _xoonips_showusers_get_cvitaes($uid)
{
    $cvitaes_handler = &xoonips_getormhandler('xoonips', 'cvitaes');
    $cvitaes_objs = &$cvitaes_handler->getCVs($uid);
    $cvitaes = array();
    foreach ($cvitaes_objs as $cvitaes_obj) {
        $from_year = $cvitaes_obj->get('from_year');
        $from_month = (empty($from_year) ? 0 : $cvitaes_obj->get('from_month'));
        $to_year = $cvitaes_obj->get('to_year');
        $to_month = (empty($to_year) ? 0 : $cvitaes_obj->get('to_month'));
        $cv = array();
        $cv['title'] = $cvitaes_obj->getVar('cvitae_title', 's');
        $cv['from_year'] = (($from_year == 0) ? '' : date('Y', mktime(0, 0, 0, 1, 1, $from_year)));
        $cv['from_month'] = (($from_month == 0) ? '' : date('M.', mktime(0, 0, 0, $from_month, 1, 0)));
        $cv['to_year'] = (($to_year == 0) ? '' : date('Y', mktime(0, 0, 0, 1, 1, $to_year)));
        $cv['to_month'] = (($to_month == 0) ? '' : date('M.', mktime(0, 0, 0, $to_month, 1, 0)));
        $cvitaes[] = $cv;
    }

    return $cvitaes;
}

function _xoonips_showusers_get_count_items($uid)
{
    $item_show_handler = &xoonips_getormhandler('xoonips', 'item_show');
    $nums = $item_show_handler->getCountPublications($uid);
    $item_type_handler = &xoonips_getormhandler('xoonips', 'item_type');
    $item_type_objs = &$item_type_handler->getObjectsSortByWeight();
    $ret = array();
    foreach ($item_type_objs as $item_type_obj) {
        $item_type_id = $item_type_obj->get('item_type_id');
        if (isset($nums[$item_type_id])) {
            $ret[$item_type_id] = array(
                'label' => $item_type_obj->getVar('display_name', 's'),
                'count' => $nums[$item_type_id],
            );
        }
    }

    return $ret;
}

function _xoonips_showusers_get_item_types($fmt)
{
    $item_type_handler = &xoonips_getormhandler('xoonips', 'item_type');
    $item_type_objs = &$item_type_handler->getObjectsSortByWeight();
    $ret = array();
    foreach ($item_type_objs as $item_type_obj) {
        $item_type_id = $obj->get('item_type_id');
        $ret[$item_type_id] = $item_type_obj->getVar('display_name', $fmt);
    }

    return $ret;
}

function _xoonips_showusers_get_item_ids($item_type_id, $uid, &$navi)
{
    $item_basic_handler = &xoonips_getormhandler('xoonips', 'item_basic');
    $join = new XooNIpsJoinCriteria('xoonips_item_show', 'item_id', 'item_id', 'INNER', 'its');
    $join->cascade(new XooNIpsJoinCriteria('xoonips_item_title', 'item_id', 'item_id', 'INNER', 'it'));
    $criteria = new CriteriaCompo(new Criteria('item_type_id', $item_type_id, '='));
    $criteria->add(new Criteria('uid', $uid, '=', 'its'));
    $criteria->add(new Criteria('title_id', 0, '=', 'it'));
    $start = $navi->getStart();
    $limit = $navi->getLimit();
    $sort = $navi->getSort();
    $order = $navi->getOrder();
    $def_sort = array(
        'title' => 'it.title',
        'item_id' => 'its.item_id',
        'ext_id' => 'doi',
        'last_update' => 'last_updated_date',
        'creation_date' => 'creation_date',
        'publication_date' => 'publication_year',
    );
    $def_order = array(
        'ASC' => 'ASC',
        'DESC' => 'DESC',
    );
    $sort = isset($def_sort[$sort]) ? $def_sort[$sort] : 'it.title';
    $order = isset($def_order[$order]) ? $def_order[$order] : 'ASC';
    $criteria->setStart($start);
    $criteria->setLimit($limit);
    $criteria->setSort($sort);
    $criteria->setOrder($order);
    $item_ids = array();
    $res = &$item_basic_handler->open($criteria, 'its.item_id', true, $join);
    while ($item_basic_obj = &$item_basic_handler->getNext($res)) {
        $item_ids[] = $item_basic_obj->get('item_id');
    }
    $item_basic_handler->close($res);

    return $item_ids;
}

// optimize page number
function page_optimize($page, $last)
{
    // empty string is page 1
    if (strlen($page) == 0) {
        return 1;
    }
    // not numerical string is page 1
    if (!is_numeric($page)) {
        return 1;
    }
    // convert decimal point to integer
    $w_page = floor($page);
    // limit minumum page
    if ($page < 1) {
        return 1;
    }
    // limit maximum page
    if ($w_page > $last) {
        $w_page = $last;
    }

    return $w_page;
}

// caclulate number of show items
// get 'item_show_optional' variable from xoonips_config table.
//  on  : count from own items (default)
//  off : count from all public items
$tab = $xoopsDB->prefix('xoonips_config');
$sql = 'SELECT value FROM '.$tab." WHERE name='item_show_optional'";
$res = $xoopsDB->query($sql);
while ($itop = $xoopsDB->fetchArray($res)) {
    if ($itop['value'] === 'on') {
        $val = 1;
    } else {
        $val = 0;
    }
}

$sql = 'SELECT index_id FROM '.$xoopsDB->prefix('xoonips_index').' WHERE uid IS NULL AND gid IS NULL';
$res = $xoopsDB->query($sql);
$inid = '';
$itid = '';
$num_of_data = '';
$sum_of_data = '';
$item_htmls = array();
while ($row = $xoopsDB->fetchArray($res)) {
    $inid = intval($row['index_id']);
    $sql2 = 'SELECT item_id FROM '.$xoopsDB->prefix('xoonips_index_item_link').' WHERE index_id='.$inid.' ';
    $res2 = $xoopsDB->query($sql2);
    while ($row2 = $xoopsDB->fetchArray($res2)) {
        $itid = intval($row2['item_id']);
            // var_dump($itid);
            switch ($val) {
        case 0:
            $sql3 = 'SELECT i2.item_id FROM '.$xoopsDB->prefix('xoonips_item_basic').' as i1, '.$xoopsDB->prefix('xoonips_item_show').' as i2 WHERE i1.item_id='.$itid.' AND i1.uid='.$uid.' AND i1.item_id=i2.item_id AND i1.uid=i2.uid';
            $res3 = $xoopsDB->query($sql3);
            $num_of_data = $xoopsDB->getRowsNum($res3);
            $sum_of_data = $sum_of_data + $num_of_data;
            break;

        case 1:
            $sql3 = 'SELECT item_id FROM '.$xoopsDB->prefix('xoonips_item_show').' WHERE item_id='.$itid.' AND uid='.$uid.'';
            $res3 = $xoopsDB->query($sql3);
            $num_of_data = $xoopsDB->getRowsNum($res3);
            $sum_of_data = $sum_of_data + $num_of_data;
            }
    }
    if (!is_numeric($sum_of_data)) {
        $sum_of_data = 0;
    }
}

// calculate maximum number of pages and offset value
    $w_last = intval($sum_of_data / 20);
if ($w_last < 1) {
    $w_last = 1;
}
    $page = $formdata->getValue('get', 'page', 'i', false, 0);
if ($page <= 0) {
    $w_page = page_optimize(1, $w_last);
} else {
    $w_page = page_optimize($page, $w_last);
}
    $w_offset = intval(($w_page - 1) * 20);
if ($w_offset < 0) {
    $w_offset = 0;
}
    $xoopsTpl->assign('sum_of_data', $sum_of_data);

// create page links
$xoopsTpl->assign('uid', $uid);

if ($w_page > 1) {
    $w_back = intval($w_page - 1);
    $xoopsTpl->assign('w_back', $w_back);
}

if (!isset($pages)) {
    $pages = array(min(max(0, $w_page - 4), max(0, $w_last - 9)));
}

// decide number of iteration
foreach ($pages as $key => $value) {
    if ($value < 10) {
        $times = $value;
    } else {
        $times = 10;
    }
}

$w_link = '';
for ($i = 0; $i < $times; ++$i) {
    $pag = intval($pages[$i]) + 1;
    switch ($pag) {
    case $w_page:
        if ($w_last != 1) {
            $link = '&nbsp;'.$pages[$i].'&nbsp;';
        } else {
            $link = '';
        }
        break;
    default:
        $link = '&nbsp;<a href="showusers.php?uid='.$uid.'&page='.$pag.'">'.$pag.'</a>&nbsp;';
    }
    $w_link[] = array('link' => $link);
}
$xoopsTpl->assign('w_link', $w_link);

if ($w_page < $w_last) {
    $w_next = intval($w_page + 1);
    $xoopsTpl->assign('w_next', $w_next);
}

// show item using itemid2ListBlock
// get 'item_show_optional' variable from xoonips_config table.
//  on  : count from own items (default)
//  off : count from all public items
$tab = $xoopsDB->prefix('xoonips_config');
$sql = 'SELECT value FROM '.$tab." WHERE name='item_show_optional'";
$res = $xoopsDB->query($sql);
while ($itop = $xoopsDB->fetchArray($res)) {
    if ($itop['value'] === 'on') {
        $val = 1;
    } else {
        $val = 0;
    }
}

$item_htmls = array();
if ($sum_of_data !== 0) {
    $tab_name1 = $xoopsDB->prefix('xoonips_item_show');
    $tab_name2 = $xoopsDB->prefix('xoonips_item_basic');
    $tab_name3 = $xoopsDB->prefix('xoonips_index_item_link');
    $tab_name4 = $xoopsDB->prefix('xoonips_index');
    $tab_name5 = $xoopsDB->prefix('xoonips_item_type');
    $tab_name6 = $xoopsDB->prefix('modules');
    switch ($val) {
    case 0:
        $sql = 'SELECT DISTINCT i1.item_id, i2.item_type_id FROM '.$tab_name1.' as i1, '.$tab_name2.' as i2, '.$tab_name3.' as i3, '.$tab_name4.' as i4, '.$tab_name5.' as i5, '.$tab_name6.' as i6';
        $sql .= ' WHERE i1.uid='.$uid.' AND i1.item_id=i2.item_id AND i2.item_id=i3.item_id AND i3.index_id=i4.index_id AND i4.uid IS NULL AND i4.gid IS NULL';
        $sql .= ' AND i3.certify_state=2 AND i2.item_type_id=i5.item_type_id AND i5.mid=i6.mid AND i2.uid='.$uid.'';
        $sql .= ' ORDER BY i6.weight ASC, i2.publication_year DESC, i2.publication_month DESC, i2.publication_mday DESC, i2.last_update_date DESC';
        $res = $xoopsDB->query($sql);
        break;
    case 1:
        $sql = 'SELECT DISTINCT i1.item_id, i2.item_type_id FROM '.$tab_name1.' as i1, '.$tab_name2.' as i2, '.$tab_name3.' as i3, '.$tab_name4.' as i4, '.$tab_name5.' as i5, '.$tab_name6.' as i6';
        $sql .= ' WHERE i1.uid='.$uid.' AND i1.item_id=i2.item_id AND i2.item_id=i3.item_id AND i3.index_id=i4.index_id AND i4.uid IS NULL AND i4.gid IS NULL';
        $sql .= ' AND i3.certify_state=2 AND i2.item_type_id=i5.item_type_id AND i5.mid=i6.mid';
        $sql .= ' ORDER BY i6.weight ASC, i2.publication_year DESC, i2.publication_month DESC, i2.publication_mday DESC, i2.last_update_date DESC';
        $res = $xoopsDB->query($sql);
    }
    $ch_a = '';
    while ($row = $xoopsDB->fetchArray($res)) {
        // show item_type's name
        $ch_b = intval($row['item_type_id']);
        if (empty($ch_a) || $ch_a !== $ch_b) {
            $tab_name = $xoopsDB->prefix('xoonips_item_type');
            $tsql = 'SELECT display_name FROM '.$tab_name.' WHERE item_type_id='.$ch_b.'';
            $tres = $xoopsDB->query($tsql);
            $trow = $xoopsDB->fetchArray($tres);
            $item_type_title = $ts->htmlSpecialChars($trow['display_name']);
            $title_t = '<table><tr><td>&nbsp;&nbsp;'.$item_type_title.'</td></tr></table>';
            $item_htmls[] = array('html' => $title_t, 'th' => 'on');
        }
        // make item block
        $tmp = itemid2ListBlock(intval($row['item_id']));
        foreach ($tmp as $key => $value) {
            $item_htmls[] = array('html' => $value);
        }
        $ch_a = intval($row['item_type_id']);
    }
}
    $xoopsTpl->assign('item_htmls', $item_htmls);

if (isset($_SESSION['xoopsUserId'])) {
    if (intval($_SESSION['xoopsUserId']) == $uid) {
        $piedit = 1;
        $xoopsTpl->assign('piedit', $piedit);
    }
    $aid = intval($_SESSION['xoopsUserId']);
    $xoopsTpl->assign('aid', $aid);
}

$tab_co = $xoopsDB->prefix('xoonips_config');
$cosql = 'SELECT value FROM '.$tab_co." WHERE name='public_item_target_user'";
$cores = $xoopsDB->query($cosql);
$corow = $xoopsDB->fetchArray($cores);
$target = $corow['value'];
$target = $ts->htmlSpecialChars($target);
$xoopsTpl->assign('target', $target);
