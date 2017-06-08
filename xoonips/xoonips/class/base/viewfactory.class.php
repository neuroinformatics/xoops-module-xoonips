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

/**
 * factory class to create XooNIpsView.
 */
class XooNIpsViewFactory
{
    public function __construct()
    {
    }

    /**
     * return XooNIpsViewFactory instance.
     *
     * @return XooNIpsViewFactory
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
     * return XooNIpsView corresponding to $view.
     *
     * @param string $name     view name
     * @param string $response XooNIpsResponse
     * @retval XooNIpsView corresponding to $name
     * @retval false unknown view
     */
    public function &create($name, $response)
    {
        static $falseVar = false;
        $view = null;

        $name = trim($name);
        if (false !== strstr($name, '..')) {
            return $falseVar;
        }
        $include_file = XOOPS_ROOT_PATH.'/modules/xoonips/class/view/'
            .strtolower($name).'.class.php';
        if (file_exists($include_file)) {
            require_once $include_file;
        } else {
            return $falseVar;
        }

        $class = 'XooNIpsView'
            .str_replace(' ', '', ucwords(str_replace('_', ' ', $name)));
        if (class_exists($class)) {
            $view = new $class($response);
        }

        if (!isset($view)) {
            trigger_error('View does not exist. Name: '.$name, E_USER_ERROR);
        }
        // return result
        if (isset($view)) {
            return $view;
        } else {
            return $falseVar;
        }
    }
}
