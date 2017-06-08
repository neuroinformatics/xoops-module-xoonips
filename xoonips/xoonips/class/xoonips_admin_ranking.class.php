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

require_once __DIR__.'/xoonips_ranking.class.php';

class XooNIpsAdminRankingHandler extends XooNIpsRankingHandler
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * delete group ranking data for group deletion.
     *
     * @param int $gid group id
     *
     * @return bool false if failure
     */
    public function deleteGroupRankings($gid)
    {
        $names = array(
            'active_group',
            'new_group',
            'sum_active_group',
            'sum_new_group',
        );
        // lock ranking data
        $h = $this->_lock();
        $criteria = new Criteria('gid', $gid);
        foreach ($names as $name) {
            if (!$this->handlers[$name]->deleteAll($criteria)) {
                $this->_unlock($h);

                return false;
            }
        }
        // unlock ranking data
        $this->_unlock($h);

        return true;
    }

    /**
     * rebuild ranking data.
     *
     * @return bool false if failure
     */
    public function rebuild()
    {
        // lock ranking data
        $h = $this->_lock();
        if ($h === false) {
            return false;
        }
        // rebuild ranking
        $ret = $this->_recalc(false);
        // unlock ranking data
        $this->_unlock($h);

        return $ret;
    }

    /**
     * create sum file.
     *
     * @return string zip compressed sum file path
     */
    public function create_sum_file()
    {
        // STEP1 : fill sum data from event_log during all time range
        //                                     v last_update
        //                   v sum_last_update       v now
        //        --------------log------------------+
        //        ---sum-----+
        //                   +-----------------------+  // add this to sum
        //   ranking_sum_last_update = now;
        //
        //   if ( days_enabled == true ){
        //                          +---cache----+      // before
        //       update cache.
        //                             +----cache----+  // after
        //    } else {
        //        ----------------------cache----+      // before
        //       copy sum to cache.
        //        ----------------------cache--------+  // after
        //    }
        //
        // lock ranking data
        $h = $this->_lock();
        if ($h === false) {
            return false;
        }
        // set ranking_sum_start, ranking_sum_last_update
        $config_names = array(
            'sum_start',
            'sum_last_update',
        );
        $config = array();
        foreach ($config_names as $name) {
            $config[$name] = $this->_get_config($name);
        }
        if ($config['sum_start'] == 0) {
            $field = 'MIN(timestamp)';
            $objs = &$this->handlers['event_log']->getObjects(null, false, $field);
            if (count($objs) == 1) {
                $config['sum_start'] = $objs[0]->getExtraVar($field);
                $this->_set_config('sum_start', $config['sum_start']);
            }
        }
        if ($config['sum_last_update'] == 0) {
            $config['sum_last_update'] = $config['sum_start'];
            $this->_set_config('sum_last_update', $config['sum_last_update']);
        }
        // update sum table
        $now = time();
        $add_days_criteria = new CriteriaCompo();
        $sub_days_criteria = new CriteriaCompo();
        // condition : $sum_last_update <= timestamp and timestamp < $now
        $add_days_criteria->add(new Criteria('timestamp', $config['sum_last_update'], '>='));
        $add_days_criteria->add(new Criteria('timestamp', $now, '<'));
        $this->_recalc_sql($add_days_criteria, $sub_days_criteria, true);
        $config['sum_last_update'] = $now;
        $this->_set_config('sum_last_update', $config['sum_last_update']);
        // synchronize ranking table
        $days_enabled = $this->_get_config('days_enabled');
        if ($days_enabled) {
            $this->_recalc(true);
        } else {
            // copy data of ranking_sum_* table into ranking_* table.
            foreach ($this->basenames as $basename) {
                if (!$this->handlers[$basename]->deleteAll(null, true)) {
                    die('fatal error in '.__FILE__.' at '.__LINE__);
                }
                if (!$this->handlers[$basename]->copy_from_sum_table()) {
                    die('fatal error in '.__FILE__.' at '.__LINE__);
                }
            }
        }
        // unlock ranking data
        $this->_unlock($h);

        // STEP2 : create sum zip file from sum tables for downloading file
        // create zip file
        $dirutil = &xoonips_getutility('directory');
        $zip_file_path = $dirutil->tempnam($dirutil->get_tempdir(), 'xnp_ranking_zip');
        if ($zip_file_path === false) {
            // faled to create empty zip file
            return false;
        }
        $zip = &xoonips_getutility('zip');
        // open zip data
        $zip->open($zip_file_path);
        // add sum table
        foreach ($this->basenames as $basename) {
            $tmp_file_path = $dirutil->get_template('xnp_ranking_sum_'.$basename);
            $fh = $dirutil->mkstemp($tmp_file_path);
            if ($fh === false) {
                // faled to create temporary file
                $zip->close();
                unlink($zip_file_path);

                return false;
            }
            $sum_basename = 'sum_'.$basename;
            $res = &$this->handlers[$sum_basename]->open();
            while ($obj = &$this->handlers[$sum_basename]->getNext($res)) {
                $cols = $this->handlers[$sum_basename]->get_columns();
                $ar = array();
                foreach ($cols as $col) {
                    $val = $obj->getVar($col, 'n');
                    $ar[] = is_numeric($val) ? $val : urlencode($val);
                }
                fputs($fh, implode(',', $ar)."\n");
            }
            fclose($fh);
            $this->handlers[$sum_basename]->close($res);
            $zip->add($tmp_file_path, $basename);
            unlink($tmp_file_path);
        }
        // add config data
        $tmp_file_path = $dirutil->get_template('xnp_ranking_config');
        $fh = $dirutil->mkstemp($tmp_file_path);
        if ($fh === false) {
            // faled to create temporary file
            $zip->close();
            unlink($zip_file_path);

            return false;
        }
        foreach ($config_names as $key) {
            fputs($fh, 'ranking_'.$key.','.$config[$key]."\n");
        }
        fclose($fh);
        $zip->add($tmp_file_path, 'config');
        unlink($tmp_file_path);
        // close zip data
        $zip->close();

        return $zip_file_path;
    }

    /**
     * load sum file.
     *
     * @param string $file_path uploaded sum file path
     *
     * @return bool false if failure
     */
    public function load_sum_file($file_path)
    {
        $config_names = array(
            'ranking_sum_last_update',
            'ranking_sum_start',
        );

        // lock ranking data
        $h = $this->_lock();
        if ($h === false) {
            return false;
        }

        $unzip = &xoonips_getutility('unzip');
        if (!$unzip->open($file_path)) {
            die('can\'t open file '.$uploadfile);
        }
        $filenames = $unzip->get_file_list();
        foreach ($filenames as $name) {
            if (substr($name, -1) == '/') {
                // ignore directory
                continue;
            }
            $data = $unzip->get_data($name);
            $lines = explode("\n", $data);
            unset($data);
            if ($name == 'config') {
                foreach ($lines as $line) {
                    if ($line === '') {
                        continue;
                    }
                    list($key, $val) = explode(',', $line);
                    if (in_array($key, $config_names)) {
                        $ckey = str_replace('ranking_sum_', 'sum_', $key);
                        $this->_set_config($ckey, $val);
                    }
                }
            } elseif (in_array($name, $this->basenames)) {
                // write csv lines to sum table. must be post request
                $this->handlers['sum_'.$name]->deleteAll();
                foreach ($lines as $line) {
                    if ($line === '') {
                        continue;
                    }
                    $ar = explode(',', $line);
                    $obj = &$this->handlers['sum_'.$name]->create();
                    $cols = $this->handlers['sum_'.$name]->get_columns();
                    foreach ($cols as $num => $key) {
                        $val = $ar[$num];
                        $val = is_numeric($val) ? floor($val) : urldecode($val);
                        $obj->set($key, $val);
                    }
                    $this->handlers['sum_'.$name]->insert($obj);
                }
            } else {
                die('unknown ranking data found '.$name);
            }
        }
        $unzip->close();
        // rebuild ranking
        $ret = $this->_recalc(false);
        // unlock ranking data
        $this->_unlock($h);

        return $ret;
    }

    /**
     * clear sum data.
     *
     * @return bool false if failure
     */
    public function clear_sum_data()
    {
        $config_names = array(
            'sum_start',
            'sum_last_update',
        );

        // lock ranking data
        $h = $this->_lock();
        if ($h === false) {
            return false;
        }
        // clear sum data
        foreach ($this->basenames as $basename) {
            // must be post request
            $this->handlers['sum_'.$basename]->deleteAll();
        }
        foreach ($config_names as $config_name) {
            $this->_set_config($config_name, 0);
        }
        // rebuild ranking
        $res = $this->_recalc(false);
        // unlock ranking data
        $this->_unlock($h);

        return $res;
    }
}
