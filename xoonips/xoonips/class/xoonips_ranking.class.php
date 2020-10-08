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

require_once __DIR__.'/base/transaction.class.php';

class XooNIpsRankingHandler
{
    /**
     * ranking orm object handlers.
     *
     * @var array
     */
    public $handlers = array();

    /**
     * ranking table base names.
     *
     * @var array
     */
    public $basenames;

    /**
     * constractor.
     */
    public function __construct()
    {
        // set base name of ranking tables
        $this->basenames = array(
            'viewed_item',       /* most accessed items */
            'downloaded_item',   /* most downloaded items */
            'contributing_user', /* most active contributors */
            'searched_keyword',  /* most searched keywords */
            'active_group',      /* most active groups */
            'new_item',          /* newly arrived items */
            'new_group',         /* newly created groups */
        );
        // load object handlers
        foreach ($this->basenames as $basename) {
            $name = $basename;
            $this->handlers[$name] = &xoonips_getormhandler('xoonips', 'ranking_'.$name);
            $name = 'sum_'.$basename;
            $this->handlers[$name] = &xoonips_getormhandler('xoonips', 'ranking_'.$name);
        }
        $this->handlers['event_log'] = &xoonips_getormhandler('xoonips', 'event_log');
        $this->handlers['config'] = &xoonips_getormhandler('xoonips', 'config');
    }

    /**
     * update rankings.
     *
     * @return bool|null false if failure
     */
    public function update()
    {
        // lock ranking data
        $h = $this->_lock();
        if (false === $h) {
            return false;
        }
        // update ranking
        $ret = $this->_recalc(true);
        // unlock ranking data
        $this->_unlock($h);

        return $ret;
    }

    /**
     * get item viewed count.
     *
     * @param int $item_id item id
     *
     * @return int viewed count
     */
    public function get_count_viewed_item($item_id)
    {
        $obj = &$this->handlers['viewed_item']->get($item_id);
        $res = (is_object($obj)) ? $obj->getVar('count', 'n') : 0;

        return $res;
    }

    /**
     * recalc rankings.
     *
     * @param bool $is_update flag for update mode
     *
     * @return bool|null false if failure
     */
    public function _recalc($is_update)
    {
        $config_names = array(
            'last_update',
            'days',
            'days_enabled',
            'sum_last_update',
        );

        $config = array();
        foreach ($config_names as $name) {
            $config[$name] = $this->_get_config($name);
        }

        $now = time();
        if ($now <= $config['last_update'] && $is_update) {
            return true;
        }

        $add_days_criteria = new CriteriaCompo();
        $sub_days_criteria = new CriteriaCompo();
        // condition 1: on update delta
        //  delta = ( last update <= timestamp < now )
        //  all = delta + current
        // condition 2: on update delta & days limit
        //  delta = ( last update <= timestamp < now )
        //          - ( last update - days <= timestamp < now - days )
        //  all = delta + current
        // condition 3: on rebuild
        //  all = ( timestamp < now )
        // condition 4: on rebuild & days limit
        //  all = ( now - days <= timestamp < now )
        // condition 5: on rebuild & use sum data
        //  delta = ( sum last update <= timestamp < now )
        //  all = sum + delta
        if ($is_update) {
            // update rankings
            $add_days_criteria->add(new Criteria('timestamp', $config['last_update'], '>='));
            $add_days_criteria->add(new Criteria('timestamp', $now, '<'));
            if ($config['days_enabled']) {
                $sub_days_criteria->add(new Criteria('timestamp', $config['last_update'] - $config['days'] * 86400, '>='));
                $sub_days_criteria->add(new Criteria('timestamp', $now - $config['days'] * 86400, '<'));
            }
        } else {
            // rebuild rankings
            // delete existing rankings
            foreach ($this->basenames as $basename) {
                $this->handlers[$basename]->deleteAll(null, true);
            }
            $add_days_criteria->add(new Criteria('timestamp', $now, '<'));
            if ($config['days_enabled']) {
                $add_days_criteria->add(new Criteria('timestamp', $now - $config['days'] * 86400, '>='));
            } else {
                if (0 != $config['sum_last_update']) {
                    // rankings are ( sum_start ... sum_last_updated of ranking_sum ) +
                    //              ( sum_last_update ... now of event_log )
                    $add_days_criteria->add(new Criteria('timestamp', $config['sum_last_update'], '>='));
                    // copy data of ranking_sum_* table into ranking_* table.
                    foreach ($this->basenames as $basename) {
                        if (!$this->handlers[$basename]->copy_from_sum_table()) {
                            die('fatal error in '.__FILE__.' at '.__LINE__);
                        }
                    }
                }
            }
        }
        $this->_recalc_sql($add_days_criteria, $sub_days_criteria, false);
        $this->_set_config('last_update', $now);

        return true;
    }

    /**
     * recalc rankings of sql body part.
     *
     * @param object &$add_days_criteria
     * @param object &$sub_days_criteria
     * @param bool   $update_sum         flag for update sum tables
     *
     * @return bool|null false if failure
     */
    public function _recalc_sql(&$add_days_criteria, &$sub_days_criteria, $update_sum)
    {
        global $xoopsDB;
        $config_names = array(
            'visible',
            'new_visible',
            'num_rows',
            'new_num_rows',
        );
        $config = array();
        foreach ($config_names as $name) {
            $config[$name] = $this->_get_config($name);
        }
        $config['visible'] = explode(',', $config['visible']);
        $config['new_visible'] = explode(',', $config['new_visible']);

        // days criteria
        $days_criteria = new CriteriaCompo();
        $days_criteria->add($add_days_criteria);
        if ('' != $sub_days_criteria->render()) {
            $days_criteria->add($sub_days_criteria, 'OR');
        }
        $add_days_sql = $add_days_criteria->render();
        if ('' == $add_days_sql) {
            $add_days_sql = '0';
        }
        $sub_days_sql = $sub_days_criteria->render();
        if ('' == $sub_days_sql) {
            $sub_days_sql = '0';
        }

        $etids = array();
        if ($config['visible'][0] || $update_sum) {
            $etids[] = ETID_VIEW_ITEM;
        }
        if ($config['visible'][1] || $update_sum) {
            $etids[] = ETID_DOWNLOAD_FILE;
        }
        if ($config['visible'][2] || $update_sum) {
            $etids[] = ETID_CERTIFY_ITEM;
        }
        if ($config['visible'][3] || $update_sum) {
            $etids[] = ETID_QUICK_SEARCH;
            $etids[] = ETID_ADVANCED_SEARCH;
        }
        if ($config['visible'][4] || $update_sum) {
            $etids[] = ETID_REQUEST_CERTIFY_ITEM;
        }
        $new_etids = array();
        if ($config['new_visible'][0] || $update_sum) {
            $new_etids[] = ETID_CERTIFY_ITEM;
        }
        if ($config['new_visible'][1] || $update_sum) {
            $new_etids[] = ETID_INSERT_GROUP;
        }
        $sum = $update_sum ? 'sum_' : '';

        $log_table = $xoopsDB->prefix('xoonips_event_log');
        $new_group_table = $xoopsDB->prefix('xoonips_ranking_'.$sum.'new_group');
        $new_item_table = $xoopsDB->prefix('xoonips_ranking_'.$sum.'new_item');

        if (count($etids)) {
            $etids_str = '('.implode(',', $etids).')';
            $criteria = new CriteriaCompo();
            $criteria->add(new Criteria('event_type_id', $etids_str, 'IN'));
            $criteria->add($days_criteria);
            $cnt = $this->handlers['event_log']->getCount($criteria);
            if ($cnt) {
                // log changed. need recalc.
                // ranking viewed item
                if ($config['visible'][0] || $update_sum) {
                    $etid = ETID_VIEW_ITEM;
                    $basename = $sum.'viewed_item';
                    $fields = 'tb.item_id, SUM('.$add_days_sql.') - SUM('.$sub_days_sql.') AS count';
                    $criteria = new CriteriaCompo();
                    $criteria->add(new Criteria('event_type_id', $etid));
                    $criteria->add($days_criteria);
                    $criteria->setGroupby('tb.item_id');
                    $join_criteria = new XooNIpsJoinCriteria('xoonips_item_basic', 'item_id', 'item_id', 'INNER', 'tb');
                    $res = &$this->handlers['event_log']->open($criteria, $fields, false, $join_criteria);
                    while ($obj = &$this->handlers['event_log']->getNext($res)) {
                        $item_id = $obj->getVar('item_id', 'n');
                        $delta = $obj->getExtraVar('count');
                        if (!$this->handlers[$basename]->increment($item_id, $delta)) {
                            die('fatal error in '.__FILE__.' at '.__LINE__);
                        }
                    }
                    $this->handlers['event_log']->close($res);
                }

                // ranking downloaded item
                if ($config['visible'][1] || $update_sum) {
                    $etid = ETID_DOWNLOAD_FILE;
                    $basename = $sum.'downloaded_item';
                    $fields = 'tb.item_id, SUM('.$add_days_sql.') - SUM('.$sub_days_sql.') AS count';
                    $criteria = new CriteriaCompo();
                    $criteria->add(new Criteria('event_type_id', $etid));
                    $criteria->add($days_criteria);
                    $criteria->setGroupby('tb.item_id');
                    $join_criteria = new XooNIpsJoinCriteria('xoonips_item_basic', 'item_id', 'item_id', 'INNER', 'tb');
                    $res = &$this->handlers['event_log']->open($criteria, $fields, false, $join_criteria);
                    while ($obj = &$this->handlers['event_log']->getNext($res)) {
                        $item_id = $obj->getVar('item_id', 'n');
                        $delta = $obj->getExtraVar('count');
                        if (!$this->handlers[$basename]->increment($item_id, $delta)) {
                            die('fatal error in '.__FILE__.' at '.__LINE__);
                        }
                    }
                    $this->handlers['event_log']->close($res);
                }

                // ranking contributing user
                if ($config['visible'][2] || $update_sum) {
                    $etid = ETID_CERTIFY_ITEM;
                    $basename = $sum.'contributing_user';
                    $fields = 'tb.item_id, tb.uid, timestamp';
                    $criteria = new CriteriaCompo();
                    $criteria->add(new Criteria('event_type_id', $etid));
                    $criteria->add($add_days_criteria);
                    $join_criteria = new XooNIpsJoinCriteria('xoonips_item_basic', 'item_id', 'item_id', 'INNER', 'tb');
                    $res = &$this->handlers['event_log']->open($criteria, $fields, false, $join_criteria);
                    while ($obj = &$this->handlers['event_log']->getNext($res)) {
                        $item_id = $obj->getVar('item_id', 'n');
                        $uid = $obj->getVar('uid', 'n');
                        $timestamp = $obj->getVar('timestamp', 'n');
                        if (!$this->handlers[$basename]->replace($item_id, $uid, $timestamp)) {
                            die('fatal error in '.__FILE__.' at '.__LINE__);
                        }
                    }
                    $this->handlers['event_log']->close($res);
                    // TODO: slow if $contributing_user_table.timestamp is not indexed.
                    //       use event_log.timestamp instead?
                    $table = $xoopsDB->prefix('xoonips_ranking_'.$basename);
                    $xoopsDB->queryF('DELETE FROM '.$table.' WHERE '.str_replace('timestamp', 'UNIX_TIMESTAMP(timestamp)', $sub_days_sql));
                }

                // ranking searched keyword
                if ($config['visible'][3] || $update_sum) {
                    $etid = '('.ETID_QUICK_SEARCH.','.ETID_ADVANCED_SEARCH.')';
                    $basename = $sum.'searched_keyword';
                    $fields = 'search_keyword, IF('.$add_days_sql.',1,-1) AS count';
                    $criteria = new CriteriaCompo();
                    $criteria->add(new Criteria('event_type_id', $etid, 'IN'));
                    $criteria->add($days_criteria);
                    $res = &$this->handlers['event_log']->open($criteria, $fields);
                    while ($obj = &$this->handlers['event_log']->getNext($res)) {
                        $keyword = $obj->getVar('search_keyword', 'n');
                        $delta = $obj->getExtraVar('count');
                        // extract keywords from log
                        $matches = array();
                        if (0 == preg_match('/(?:^|&)keyword=([^&]*)(?:&|$)/', $keyword, $matches)) {
                            continue;
                        }
                        $keyword = urldecode($matches[1]);
                        preg_match_all('/([^ "()]+)|(\\()|(\\))|"([^"]+)"/', $keyword, $match, PREG_SET_ORDER);
                        $len = count($match);
                        for ($i = 0; $i < $len; ++$i) {
                            if (isset($match[$i][1])) {
                                $keyword = $match[$i][1];
                            } elseif (isset($match[$i][4])) {
                                $keyword = $match[$i][4];
                            } else {
                                continue;
                            }
                            $keyword = strtolower($keyword);
                            if ('and' == $keyword || 'or' == $keyword) {
                                continue;
                            }
                            if (strlen($keyword) > 2) {
                                if (!$this->handlers[$basename]->increment($keyword, $delta)) {
                                    die('fatal error in '.__FILE__.' at '.__LINE__);
                                }
                            }
                        }
                    }
                    $this->handlers['event_log']->close($res);
                }

                // ranking active group
                if ($config['visible'][4] || $update_sum) {
                    $etid = ETID_REQUEST_CERTIFY_ITEM;
                    $basename = $sum.'active_group';
                    $fields = 'tx.gid, IF('.$add_days_sql.',1,-1) AS dir, COUNT( DISTINCT timestamp, item_id, tx.gid ) AS count';
                    $criteria = new CriteriaCompo();
                    $criteria->add(new Criteria('event_type_id', $etid));
                    $criteria->add($days_criteria);
                    $criteria->add(new Criteria('ISNULL(tx.gid)', '0', '='));
                    $criteria->setGroupby('timestamp, item_id, tx.gid');
                    $join_criteria = new XooNIpsJoinCriteria('xoonips_index', 'index_id', 'index_id', 'INNER', 'tx');
                    $res = &$this->handlers['event_log']->open($criteria, $fields, false, $join_criteria);
                    while ($obj = &$this->handlers['event_log']->getNext($res)) {
                        $gid = $obj->getVar('gid', 'n');
                        $dir = $obj->getExtraVar('dir');
                        $delta = $obj->getExtraVar('count');
                        $delta *= $dir;
                        if (!$this->handlers[$basename]->increment($gid, $delta)) {
                            echo $this->handlers[$basename]->getLastSQL();
                            die('fatal error in '.__FILE__.' at '.__LINE__);
                        }
                    }
                    $this->handlers['event_log']->close($res);
                }
            }
        }

        if (count($new_etids)) {
            $new_etids_str = '('.implode(',', $new_etids).')';
            $criteria = new CriteriaCompo();
            $criteria->add(new Criteria('event_type_id', $new_etids_str, 'IN'));
            $criteria->add($days_criteria);
            $cnt = $this->handlers['event_log']->getCount($criteria);
            if ($cnt) {
                // log changed. need recalc.
                // ranking new item
                if ($config['new_visible'][0] || $update_sum) {
                    // TODO: support 'REPLACE INTO ... (...) SELECT ...' query by object
                    //       handler
                    $sql = 'REPLACE INTO '.$new_item_table.' ( item_id, timestamp ) SELECT t.item_id, FROM_UNIXTIME(timestamp) FROM '.$log_table.' AS t INNER JOIN '.$xoopsDB->prefix('xoonips_index_item_link').' AS txil ON t.item_id=txil.item_id INNER JOIN '.$xoopsDB->prefix('xoonips_index').' AS tx ON txil.index_id=tx.index_id WHERE tx.open_level='.OL_PUBLIC.' AND event_type_id='.ETID_CERTIFY_ITEM.' AND ( '.$add_days_sql.' ) GROUP BY t.item_id ORDER BY timestamp DESC LIMIT '.$config['new_num_rows'];
                    $result = $xoopsDB->queryF($sql);
                    if (false == $result) {
                        die('fatal error in '.__FILE__.' at '.__LINE__);
                    }
                    // remove old entry
                    if (!$this->handlers[$sum.'new_item']->trim($config['new_num_rows'])) {
                        die('fatal error in '.__FILE__.' at '.__LINE__);
                    }
                }

                // ranking new group
                if ($config['new_visible'][1] || $update_sum) {
                    // TODO: support 'REPLACE INTO ... (...) SELECT ...' query by object
                    //       handler
                    $sql = 'REPLACE INTO '.$new_group_table.' ( gid, timestamp ) SELECT t.gid, FROM_UNIXTIME(timestamp) FROM '.$log_table.' AS t INNER JOIN '.$xoopsDB->prefix('xoonips_groups').' AS tg ON t.gid=tg.gid WHERE tg.gid IS NOT NULL AND event_type_id='.ETID_INSERT_GROUP.' AND ( '.$add_days_sql.' ) ORDER BY timestamp DESC LIMIT '.$config['new_num_rows'];
                    $result = $xoopsDB->queryF($sql);
                    if (false == $result) {
                        die('fatal error in '.__FILE__.' at '.__LINE__);
                    }
                    // remove old entry
                    if (!$this->handlers[$sum.'new_group']->trim($config['new_num_rows'])) {
                        die('fatal error in '.__FILE__.' at '.__LINE__);
                    }
                }
            }
        }
    }

    /**
     * lock rankings table for update.
     *
     * @return int timeout time
     */
    public function _lock()
    {
        global $xoopsDB;
        $table = $xoopsDB->prefix('xoonips_config');
        $now = time();
        $timeout = $now + 180;
        $result = $xoopsDB->queryF('UPDATE '.$table.' SET value='.$timeout.' WHERE name=\'ranking_lock_timeout\' AND value < '.$now);
        if (false != $result && $xoopsDB->getAffectedRows()) {
            // transaction
            $transaction = &XooNIpsTransaction::getInstance();
            $transaction->start();
            // lock exclusively
            $xoopsDB->queryF('SELECT value FROM '.$table.' WHERE name=\'ranking_last_update\' FOR UPDATE');

            return $timeout;
        }

        return false;
    }

    /**
     * unlock rankings table for update.
     *
     * @param int $timeout timeout time
     *
     * @return bool false if failure
     */
    public function _unlock($timeout)
    {
        // transaction
        $transaction = &XooNIpsTransaction::getInstance();
        $transaction->commit();
        $old_timeout = $this->_get_config('lock_timeout');
        if ($old_timeout != $timeout) {
            return false;
        }
        $this->_set_config('lock_timeout', 0);

        return true;
    }

    /**
     * get ranking configuration.
     *
     * @param string $key ranking configuration key
     *
     * @return mixed configuration value
     */
    public function _get_config($key)
    {
        $val = $this->handlers['config']->getValue('ranking_'.$key);
        if (is_null($val)) {
            die('fatal error in '.__FILE__.' at '.__LINE__);
        }

        return $val;
    }

    /**
     * set ranking configuration.
     *
     * @param string $key ranking configuration key
     * @param mixed  $val ranking configuration value
     *
     * @return bool false if failure
     */
    public function _set_config($key, $val)
    {
        // force insertion
        return $this->handlers['config']->setValue('ranking_'.$key, $val, true);
    }
}
