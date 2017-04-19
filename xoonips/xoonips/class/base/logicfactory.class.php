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

/**
 * factory class to create XooNIpsLogic.
 */
class XooNIpsLogicFactory
{
    public function __construct()
    {
    }

    /**
     * return XooNIpsLogicFactory instance.
     *
     * @return XooNIpsLogicFactory
     */
    public static function &getInstance()
    {
        static $singleton = null;
        if (!isset($singleton)) {
            $singleton = new self();
        }

        return $singleton;
    }

    /**
     * return XooNIpsLogic corresponding to $logic.
     *
     * @param string $name logic name
     * @retval XooNIpsLogic corresponding to $name
     * @retval false unknown logic
     */
    public function &create($name)
    {
        static $falseVar = false;
        $logic = null;

        $name = trim($name);
        if (false !== strstr($name, '..')) {
            return $falseVar;
        }
        $include_file = XOOPS_ROOT_PATH.'/modules/xoonips/class/logic/'.strtolower($name).'.class.php';
        if (file_exists($include_file)) {
            include_once $include_file;
        } else {
            return $falseVar;
        }

        $class = 'XooNIpsLogic'.ucfirst($name);
        if (class_exists($class)) {
            $logic = new $class();
        }

        if (!isset($logic)) {
            trigger_error('Handler does not exist. Name: '.$name, E_USER_ERROR);
        }
        // return result
        if (isset($logic)) {
            return $logic;
        } else {
            return $falseVar;
        }
    }
}
