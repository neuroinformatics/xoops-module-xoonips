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
if (!defined('XOOPS_ROOT_PATH')) {
    exit();
}

/**
 * language resource manager class.
 *
 * @copyright copyright &copy; 2008 RIKEN Japan
 */
class XooNIpsUtilityLanguagemanager extends XooNIpsUtility
{
    /**
     * default module directory name.
     *
     * @var string default module directory name
     */
    public $default_mydirname = 'xoonips';

    /**
     * default language resouce name.
     *
     * @var string default language resource name
     */
    public $default_language = 'english';

    /**
     * current language resouce name.
     *
     * @var string current language resource name
     */
    public $language = 'english';

    /**
     * D3 Language Manager class.
     *
     * @var object instance of D3LanguageManager class
     */
    public $d3langman_instance = null;

    /**
     * constractor.
     */
    public function __construct()
    {
        $this->setSingleton();
        $myxoopsConfig = &xoonips_get_xoops_configs(XOOPS_CONF);
        $this->language = preg_replace('/[^0-9a-zA-Z_-]/', '', $myxoopsConfig['language']);
        if (defined('XOOPS_TRUST_PATH') && XOOPS_TRUST_PATH != '') {
            $langmanpath = XOOPS_TRUST_PATH.'/libs/altsys/class/D3LanguageManager.class.php';
            if (file_exists($langmanpath)) {
                include_once $langmanpath;
                $this->d3langman_instance = &D3LanguageManager::getInstance();
            }
        }
    }

    /**
     * read language resouce on current language.
     *
     * @param string $resouce        resource file name
     * @param string $mydirname      module directory name
     * @param string $mytrustdirname module trust directory name
     * @param bool   $read_once      true if read by require_once
     */
    public function read($resource, $mydirname = null, $mytrustdirname = null, $read_once = true)
    {
        if (is_null($mydirname)) {
            $mydirname = $this->default_mydirname;
        }
        if (is_object($this->d3langman_instance)) {
            $this->d3langman_instance->read($resource, $mydirname, $mytrustdirname, $read_once);
        } else {
            $langfile = $this->_get_path($resource, $mydirname, $mytrustdirname);
            if ($read_once) {
                include_once $langfile;
            } else {
                include $langfile;
            }
        }
    }

    /**
     * read language resouce file on current language.
     *
     * @param string $resouce        resource file name
     * @param string $mydirname      module directory name
     * @param string $mytrustdirname module trust directory name
     *
     * @return string resource file content
     */
    public function get($resource, $mydirname = null, $mytrustdirname = null)
    {
        return file_get_contents($this->_get_path($resource, $mydirname, $mytrustdirname));
    }

    /**
     * read XOOPS page type language resouce on current language.
     *
     * @param string $pagetype  page type message catalog
     * @param bool   $read_once true if read by require_once
     */
    public function read_pagetype($pagetype, $read_once = true)
    {
        $accept_pagetype = array(
        // Note: don't load global.php and pmsg.php files manually. these files
        //       are not exists on CUBE 2.1 Legacy
        'admin.php',
        'calendar.php',
        'comment.php',
        'mail.php',
        'misc.php',
        'notification.php',
        'search.php',
        'timezone.php',
        'user.php',
        );
        if (!in_array($pagetype, $accept_pagetype)) {
            die('invalid pagetype message catalog');
        }
        $langfile = XOOPS_ROOT_PATH.'/language/'.$this->language.'/'.$pagetype;
        if (!file_exists($langfile)) {
            $langfile = XOOPS_ROOT_PATH.'/language/'.$this->default_language.'/'.$pagetype;
        }
        if ($read_once) {
            include_once $langfile;
        } else {
            include $langfile;
        }
    }

    /**
     * get mail_template directory name on current language.
     *
     * @param string $mydirname      module directory name
     * @param string $mytrustdirname module trust directory name
     *
     * @return string accessible mail_template directory name
     */
    public function mail_template_dir($mydirname = null, $mytrustdirname = null)
    {
        if (is_null($mydirname)) {
            $mydirname = $this->default_mydirname;
        }
        $resource = 'mail_template/';
        $langpath = $this->_get_path($resource, $mydirname, $mytrustdirname);

        return $langpath;
    }

    /**
     * get font path on current language.
     *
     * @param string $fontname font file name
     *
     * @return string accessible font path
     */
    public function font_path($fontname)
    {
        // set alternative language name
        $alternative = _MD_XOONIPS_FONT_LANGUAGE;

        return $this->_get_path($fontname, $this->default_mydirname, null, $alternative);
    }

    /**
     * get accessible file or directory path on current language.
     *
     * @param string $mydirname      module directory name
     * @param string $mytrustdirname module trust directory name
     * @param string $alternative    alternative language name
     *
     * @return string accessible file or directory path
     */
    public function _get_path($resource, $mydirname, $mytrustdirname, $alternative = null)
    {
        $is_directory = (substr($resource, -1, 1) == '/') ? true : false;
        $d3file = XOOPS_ROOT_PATH.'/modules/'.$mydirname.'/mytrustdirname.php';
        if (empty($mytrustdirname) && file_exists($d3file)) {
            include $d3file;
        }
        $_basepath = empty($mytrustdirname) ? XOOPS_ROOT_PATH : XOOPS_TRUST_PATH;
        $_dirname = empty($mytrustdirname) ? $mydirname : $mytrustdirname;
        $langfiles = array();
        $langfiles[] = $_basepath.'/modules/'.$_dirname.'/language/'.$this->language.'/'.$resource;
        if (!empty($alternative)) {
            $langfiles[] = $_basepath.'/modules/'.$_dirname.'/language/'.$alternative.'/'.$resource;
        }
        $langfiles[] = $_basepath.'/modules/'.$_dirname.'/language/'.$this->default_language.'/'.$resource;
        foreach ($langfiles as $langfile) {
            if (($is_directory && is_dir($langfile)) || (!$is_directory && file_exists($langfile))) {
                return $langfile;
            }
        }
        die('language file or directory not found');
    }
}
