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

/**
 * The MySQL Information class.
 *
 * @copyright copyright &copy; 2005-2008 RIKEN Japan
 */
class XooNIpsUtilityMysqlinfo extends XooNIpsUtility
{
    /**
     * database instance.
     *
     * @var object
     */
    public $_db;

    /**
     * mysql version.
     *
     * @var array
     */
    public $_version = array();

    /**
     * mysql acceptable charsets.
     *
     * @var array
     */
    public $_charsets = array();

    /**
     * constructor.
     *
     * this class is singleton
     */
    public function __construct()
    {
        $this->setSingleton();

        $this->_db = $GLOBALS['xoopsDB'];
        $support_http_charset = array(
        'EUC-JP',
        'Shift_JIS',
        'ISO-8859-1',
        'UTF-8',
        );

        // get server version
        $sql = 'SHOW VARIABLES LIKE \'version\'';
        $res = $this->_db->queryF($sql);
        list($key, $value) = $this->_db->fetchRow($res);
        $this->_version['full'] = $value;
        if (!preg_match('/^(\\d+)\\.(\\d+)\\.(\\d+)(.*)$/', $this->_version['full'], $regs)) {
            die('Could not get version number : '.$this->_version['full']);
        }
        $this->_version['major'] = intval($regs[1]);
        $this->_version['minor'] = intval($regs[2]);
        $this->_version['micro'] = intval($regs[3]);
        $this->_version['additional'] = $regs[4];

        // get acceptable charsets
        $this->_charsets['client'] = array(
        'EUC-JP' => array(
        'ujis',
        ),
        'Shift_JIS' => array(
        'sjis',
        ),
        'ISO-8859-1' => array(
        'latin1',
        ),
        'UTF-8' => array(),
        );
        $this->_charsets['database'] = array(
        'EUC-JP' => array(
        'ujis',
        ),
        'Shift_JIS' => array(
        'sjis',
        ),
        'ISO-8859-1' => array(
        'latin1',
        ),
        'UTF-8' => array(),
        );
        // -- set version depending charsets
        if ($this->isVersion41orHigher()) {
            // for 4.1 or higher
            foreach ($support_http_charset as $http_charset) {
                $this->_charsets['database'][$http_charset][] = 'utf8';
            }
            $this->_charsets['database']['EUC-JP'][] = 'sjis';
            $this->_charsets['client']['UTF-8'][] = 'utf8';
            if ($this->isMSJapaneseSupport()) {
                // for 5.0.3 or higher
                $japanese_http_charsets = array(
                'EUC-JP',
                'Shift_JIS',
                );
                $japanese_mysql_charsets = array(
                'cp932',
                'eucjpms',
                );
                foreach ($japanese_http_charsets as $http_charset) {
                    foreach ($japanese_mysql_charsets as $mysql_charset) {
                        $this->_charsets['database'][$http_charset][] = $mysql_charset;
                    }
                }
                $this->_charsets['client']['EUC-JP'][] = 'eucjpms';
                $this->_charsets['client']['Shift_JIS'][] = 'cp932';
            }
        }
    }

    /**
     * get the mysql version.
     *
     * @param string $name what kind of version (full, major, minor, micro, additional)
     *
     * @return mixed version number of string
     */
    public function getVersion($name)
    {
        return $this->_version[$name];
    }

    /**
     * get the mysql variable.
     *
     * @param string name variable name
     *
     * @return string variable
     */
    public function getVariable($name)
    {
        $sql = 'SHOW VARIABLES LIKE \''.$name.'\'';
        $res = $this->_db->queryF($sql);
        while (list($key, $value) = $this->_db->fetchRow($res)) {
            $variable[$key] = $value;
        }

        return $variable[$name];
    }

    /**
     * check the mysql version 4.1 or higher.
     *
     * @return bool TRUE if 4.1 or higher
     */
    public function isVersion41orHigher()
    {
        if ($this->_version['major'] >= 5 || ($this->_version['major'] == 4 && $this->_version['minor'] >= 1)) {
            return true;
        }

        return false;
    }

    /**
     * check the Microsoft extended Japanese suportments.
     *
     * @return bool FALSE if unsupported
     */
    public function isMSJapaneseSupport()
    {
        if (($this->_version['major'] >= 5 && $this->_version['minor'] >= 1) || ($this->_version['major'] == 5 && $this->_version['minor'] == 0 && $this->_version['micro'] >= 3)) {
            return true;
        }

        return false;
    }

    /**
     * get acceptable charsets.
     *
     * @param bool is_database TRUE if check for database charset
     * @param string http_charset http charset
     *
     * @return array mysql charset array
     */
    public function getAcceptableCharsets($is_database, $http_charset)
    {
        $name = ($is_database) ? 'database' : 'client';

        return $this->_charsets[$name][$http_charset];
    }
}
