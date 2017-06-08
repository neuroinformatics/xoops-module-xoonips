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

// display ranking block / new arrival block
function xoonips_ranking_show($is_arrival)
{
    global $xoopsUser;

    // load xoonips config handler
    $xconfig_handler = &xoonips_getormhandler('xoonips', 'config');

    // hide block if user is guest and public index viewing policy is 'platform'
    if (!is_object($xoopsUser)) {
        $target_user = $xconfig_handler->getValue('public_item_target_user');
        if ($target_user != 'all') {
            // 'platform'
            return false;
        }
    }

    $uid = is_object($xoopsUser) ? $xoopsUser->getVar('uid', 'n') : UID_GUEST;

    // hide block if user is invalid xoonips user
    $xsession_handler = &xoonips_getormhandler('xoonips', 'session');
    if (!$xsession_handler->validateUser($uid, false)) {
        return false;
    }

    // load utility class
    $textutil = &xoonips_getutility('text');
    $etc = '...';

    // decide maximum string length by block position
    if (defined('XOOPS_CUBE_LEGACY')) {
        // get xoonips module id
        $mydirname = basename(dirname(__DIR__));
        $module_handler = &xoops_gethandler('module');
        $module = &$module_handler->getByDirname($mydirname);
        $mid = $module->getVar('mid', 's');
        // get block array
        $block_arr = &XoopsBlock::getByModule($mid);
    } else {
        global $block_arr;
    }
    $myfunc = $is_arrival ? 'b_xoonips_ranking_new_show' : 'b_xoonips_ranking_show';
    $maxlen = 56;
    // default
    foreach ($block_arr as $b) {
        $func = $b->getVar('show_func', 'n');
        if ($func == $myfunc) {
            $side = $b->getVar('side', 'n');
            if ($side == XOOPS_SIDEBLOCK_LEFT || $side == XOOPS_SIDEBLOCK_RIGHT) {
                $maxlen = 16;
                break;
            } elseif ($side == XOOPS_CENTERBLOCK_LEFT || $side == XOOPS_CENTERBLOCK_RIGHT) {
                $maxlen = 24;
                break;
            }
        }
    }

    // get configs
    $config_names = array(
        'num_rows',
        'visible',
        'order',
    );
    $new_str = $is_arrival ? 'new_' : '';
    foreach ($config_names as $name) {
        $config[$name] = $xconfig_handler->getValue('ranking_'.$new_str.$name);
    }
    $config['visible'] = explode(',', $config['visible']);
    $config['order'] = explode(',', $config['order']);

    // update rankings
    $ranking_handler = &xoonips_gethandler('xoonips', 'ranking');
    $ranking_handler->update();

    // get rankings from database
    // - set item permission criteria and join criteria
    $iperm_criteria = new CriteriaCompo();
    $iperm_criteria->add(new Criteria('title_id', 0, '=', 'tt'));
    $iperm_criteria->add(new Criteria('open_level', OL_PUBLIC, '=', 'tx'));
    $iperm_criteria->add(new Criteria('certify_state', CERTIFIED, '=', 'txil'));
    $iperm_join = new XooNIpsJoinCriteria('xoonips_item_basic', 'item_id', 'item_id', 'INNER', 'tb');
    $iperm_join->cascade(new XooNIpsJoinCriteria('xoonips_item_title', 'item_id', 'item_id', 'INNER', 'tt'));
    $iperm_join->cascade(new XooNIpsJoinCriteria('xoonips_index_item_link', 'item_id', 'item_id', 'INNER', 'txil'));
    $iperm_join->cascade(new XooNIpsJoinCriteria('xoonips_index', 'index_id', 'index_id', 'INNER', 'tx'), 'txil', true);
    // - set ranking number label
    $rank_tmp = explode(',', _MB_XOONIPS_RANKING_RANK_STR);
    $rank_str = array();
    for ($i = 0; $i < $config['num_rows']; ++$i) {
        $rank_str[] = ($i + 1).$rank_tmp[min($i, count($rank_tmp) - 1)];
    }

    $block['rankings'] = array();
    if ($is_arrival) {
        // new arrival block
        // ranking new item
        if ($config['visible'][0]) {
            $table = 'ranking_new_item';
            $label = _MB_XOONIPS_RANKING_NEW_ITEM;
            $fields = 'tb.item_id, tt.title, DATE_FORMAT(timestamp,\'%m/%d\'), tb.doi';
            $criteria = $iperm_criteria;
            $criteria->setGroupby('tb.item_id');
            $criteria->setLimit($config['num_rows']);
            $criteria->setSort('timestamp');
            $criteria->setOrder('DESC');
            $join = $iperm_join;

            $handler = &xoonips_getormhandler('xoonips', $table);
            $res = &$handler->open($criteria, $fields, false, $join);
            $items = array();
            $i = 0;
            while ($obj = &$handler->getNext($res)) {
                $item_id = $obj->getVar('item_id', 'n');
                $title = $textutil->html_special_chars($obj->getExtraVar('title'));
                $title = $textutil->truncate($title, $maxlen, $etc);
                $count = $textutil->html_special_chars($obj->getExtraVar('DATE_FORMAT(timestamp,\'%m/%d\')'));
                $doi = $textutil->html_special_chars($obj->getExtraVar('doi'));
                $id = ($doi == '' && XNP_CONFIG_DOI_FIELD_PARAM_NAME != '') ? 'item_id='.$item_id : XNP_CONFIG_DOI_FIELD_PARAM_NAME.'='.urlencode($doi);
                $url = XOOPS_URL.'/modules/xoonips/detail.php?'.$id;
                $items[] = array(
                    'title' => $title,
                    'url' => $url,
                    'num' => $count,
                    'rank_str' => $rank_str[$i],
                );
                ++$i;
            }
            $handler->close($res);
            $block['rankings'][$config['order'][0]] = array(
                'items' => $items,
                'title' => $label,
            );
            unset($items);
        }

        // ranking new group
        if ($config['visible'][1]) {
            $table = 'ranking_new_group';
            $label = _MB_XOONIPS_RANKING_NEW_GROUP;
            $fields = 'tg.gid, tg.gname, DATE_FORMAT(timestamp,\'%m/%d\')';
            $criteria = new CriteriaElement();
            $criteria->setLimit($config['num_rows']);
            $criteria->setSort('timestamp');
            $criteria->setOrder('DESC');
            $join = new XooNIpsJoinCriteria('xoonips_groups', 'gid', 'gid', 'INNER', 'tg');

            $handler = &xoonips_getormhandler('xoonips', $table);
            $res = &$handler->open($criteria, $fields, false, $join);
            $items = array();
            $i = 0;
            while ($obj = &$handler->getNext($res)) {
                $gid = $obj->getVar('gid', 'n');
                $title = $textutil->html_special_chars($obj->getExtraVar('gname'));
                $title = $textutil->truncate($title, $maxlen, $etc);
                $count = $textutil->html_special_chars($obj->getExtraVar('DATE_FORMAT(timestamp,\'%m/%d\')'));
                $url = XOOPS_URL.'/modules/xoonips/groups.php';
                $items[] = array(
                    'title' => $title,
                    'url' => $url,
                    'num' => $count,
                    'rank_str' => $rank_str[$i],
                );
                ++$i;
            }
            $handler->close($res);
            $block['rankings'][$config['order'][1]] = array(
                'items' => $items,
                'title' => $label,
            );
            unset($items);
        }
    } else {
        // ranking block
        // ranking viewed item
        if ($config['visible'][0]) {
            $table = 'ranking_viewed_item';
            $label = _MB_XOONIPS_RANKING_VIEWED_ITEM;
            $fields = 'tb.item_id, tt.title, count';
            $criteria = $iperm_criteria;
            $criteria->add(new Criteria('count', 0, '<>'));
            $criteria->setLimit($config['num_rows']);
            $criteria->setSort('count');
            $criteria->setOrder('DESC');
            $join = $iperm_join;
            $handler = &xoonips_getormhandler('xoonips', $table);
            $res = &$handler->open($criteria, $fields, true, $join);
            $items = array();
            $i = 0;
            while ($obj = &$handler->getNext($res)) {
                $item_id = $obj->getVar('item_id', 'n');
                $title = $textutil->html_special_chars($obj->getExtraVar('title'));
                $title = $textutil->truncate($title, $maxlen, $etc);
                $count = $obj->getVar('count', 'n');
                $doi = $textutil->html_special_chars($obj->getExtraVar('doi'));
                $id = ($doi == '' && XNP_CONFIG_DOI_FIELD_PARAM_NAME != '') ? 'item_id='.$item_id : XNP_CONFIG_DOI_FIELD_PARAM_NAME.'='.urlencode($doi);
                $url = XOOPS_URL.'/modules/xoonips/detail.php?'.$id;
                $items[] = array(
                    'title' => $title,
                    'url' => $url,
                    'num' => $count,
                    'rank_str' => $rank_str[$i],
                );
                ++$i;
            }
            $handler->close($res);
            $block['rankings'][$config['order'][0]] = array(
                'items' => $items,
                'title' => $label,
            );
            unset($items);
        }

        // ranking downloaded item
        if ($config['visible'][1]) {
            $table = 'ranking_downloaded_item';
            $label = _MB_XOONIPS_RANKING_DOWNLOADED_ITEM;
            $fields = 'tb.item_id, tt.title, count';
            $criteria = $iperm_criteria;
            $criteria->add(new Criteria('count', 0, '<>'));
            $criteria->setLimit($config['num_rows']);
            $criteria->setSort('count');
            $criteria->setOrder('DESC');
            $join = $iperm_join;
            $handler = &xoonips_getormhandler('xoonips', $table);
            $res = &$handler->open($criteria, $fields, true, $join);
            $items = array();
            $i = 0;
            while ($obj = &$handler->getNext($res)) {
                $item_id = $obj->getVar('item_id', 'n');
                $title = $textutil->html_special_chars($obj->getExtraVar('title'));
                $title = $textutil->truncate($title, $maxlen, $etc);
                $count = $obj->getVar('count', 'n');
                $doi = $textutil->html_special_chars($obj->getExtraVar('doi'));
                $id = ($doi == '' && XNP_CONFIG_DOI_FIELD_PARAM_NAME != '') ? 'item_id='.$item_id : XNP_CONFIG_DOI_FIELD_PARAM_NAME.'='.urlencode($doi);
                $url = XOOPS_URL.'/modules/xoonips/detail.php?'.$id;
                $items[] = array(
                    'title' => $title,
                    'url' => $url,
                    'num' => $count,
                    'rank_str' => $rank_str[$i],
                );
                ++$i;
            }
            $handler->close($res);
            $block['rankings'][$config['order'][1]] = array(
                'items' => $items,
                'title' => $label,
            );
            unset($items);
        }

        // ranking contiributing user
        if ($config['visible'][2]) {
            $table = 'ranking_contributing_user';
            $label = _MB_XOONIPS_RANKING_CONTRIBUTING_USER;
            $fields = 'tu.uid, tu.uname, COUNT(*) AS count';
            $criteria = new CriteriaElement();
            $criteria->setLimit($config['num_rows']);
            $criteria->setSort('count');
            $criteria->setOrder('DESC');
            $criteria->setGroupby('tu.uid');
            $join = new XooNIpsJoinCriteria('users', 'uid', 'uid', 'INNER', 'tu');
            $handler = &xoonips_getormhandler('xoonips', $table);
            $res = &$handler->open($criteria, $fields, true, $join);
            $items = array();
            $i = 0;
            while ($obj = &$handler->getNext($res)) {
                $uid = $obj->getVar('uid', 'n');
                $title = $textutil->html_special_chars($obj->getExtraVar('uname'));
                $title = $textutil->truncate($title, $maxlen, $etc);
                $count = intval($obj->getExtraVar('count'));
                $url = XOOPS_URL.'/modules/xoonips/showusers.php?uid='.$uid;
                $items[] = array(
                    'title' => $title,
                    'url' => $url,
                    'num' => $count,
                    'rank_str' => $rank_str[$i],
                );
                ++$i;
            }
            $handler->close($res);
            $block['rankings'][$config['order'][2]] = array(
                'items' => $items,
                'title' => $label,
            );
            unset($items);
        }

        // ranking searched keyword
        if ($config['visible'][3]) {
            $table = 'ranking_searched_keyword';
            $label = _MB_XOONIPS_RANKING_SEARCHED_KEYWORD;
            $fields = 'keyword, count';
            $criteria = new CriteriaCompo(new Criteria('count', 0, '<>'));
            $criteria->add(new Criteria('keyword', '', '!='));
            // ignore empty
            $criteria->setLimit($config['num_rows']);
            $criteria->setSort('count');
            $criteria->setOrder('DESC');
            $handler = &xoonips_getormhandler('xoonips', $table);
            $res = &$handler->open($criteria, $fields, true);
            $items = array();
            $i = 0;
            while ($obj = &$handler->getNext($res)) {
                $keyword = $obj->getVar('keyword', 'n');
                $title = $textutil->html_special_chars($keyword);
                $title = preg_replace_callback(
                    '/[\\x00-\\x20]/s', function ($m) {
                        return urlencode($m[0]);
                    }, $title
                );
                $title = $textutil->truncate($title, $maxlen, $etc);
                $count = $obj->getVar('count', 'n');
                $url = XOOPS_URL.'/modules/xoonips/itemselect.php?op=quicksearch&amp;search_itemtype=all&amp;keyword='.urlencode($keyword);
                $items[] = array(
                    'title' => $title,
                    'url' => $url,
                    'num' => $count,
                    'rank_str' => $rank_str[$i],
                );
                ++$i;
            }
            $handler->close($res);
            $block['rankings'][$config['order'][3]] = array(
                'items' => $items,
                'title' => $label,
            );
            unset($items);
        }

        if ($config['visible'][4]) {
            // ranking active group
            $table = 'ranking_active_group';
            $label = _MB_XOONIPS_RANKING_CONTRIBUTED_GROUP;
            $fields = 'tg.gid, tg.gname, count';
            $criteria = new Criteria('count', 0, '<>');
            $criteria->setLimit($config['num_rows']);
            $criteria->setSort('count');
            $criteria->setOrder('DESC');
            $join = new XooNIpsJoinCriteria('xoonips_groups', 'gid', 'gid', 'INNER', 'tg');
            $handler = &xoonips_getormhandler('xoonips', $table);
            $res = &$handler->open($criteria, $fields, false, $join);
            $items = array();
            $i = 0;
            while ($obj = &$handler->getNext($res)) {
                $gid = $obj->getVar('gid', 'n');
                $title = $textutil->html_special_chars($obj->getExtraVar('gname'));
                $title = $textutil->truncate($title, $maxlen, $etc);
                $count = $obj->getVar('count', 'n');
                $url = XOOPS_URL.'/modules/xoonips/groups.php';
                $items[] = array(
                    'title' => $title,
                    'url' => $url,
                    'num' => $count,
                    'rank_str' => $rank_str[$i],
                );
                ++$i;
            }
            $handler->close($res);
            $block['rankings'][$config['order'][4]] = array(
                'items' => $items,
                'title' => $label,
            );
            unset($items);
        }
    }

    ksort($block['rankings']);

    return $block;
}

/**
 * ranking block.
 */
function b_xoonips_ranking_show()
{
    return xoonips_ranking_show(false);
}

/**
 * new arrival block.
 */
function b_xoonips_ranking_new_show()
{
    return xoonips_ranking_show(true);
}
