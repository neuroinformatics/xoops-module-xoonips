<?php

// $Revision: 1.1.4.1.2.4 $
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
 * factory class to create XooNIpsXmlRpcViewElement.
 */
class XooNIpsXmlRpcItemViewFactory
{
    public function __construct()
    {
    }

    /**
     * return XooNIpsXmlRpcItemViewFactory instance.
     *
     * @return XooNIpsXmlRpcItemViewFactory
     */
    public function &getInstance()
    {
        static $singleton = null;
        if (!isset($singleton)) {
            $singleton = new self();
        }

        return $singleton;
    }

    /**
     * return XooNIpsXmlRpcItemView corresponding to $logic and $item(itemtype).
     *
     * @param string $logic logic name
     * @param  XooNIpsItemCompo item object
     * @retval XooNIpsXmlRpcItemViewElement corresponding to $logic
     * @retval false unknown logic or unknown item
     */
    public function &create($logic, &$item)
    {
        static $falseVar = false;
        $item_type_handler = &xoonips_getormhandler('xoonips', 'item_type');
        $basic = &$item->getVar('basic');
        $itemtype = &$item_type_handler->get($basic->get('item_type_id'));
        if (!$itemtype) {
            return $falseVar;
        }

        $name = $itemtype->get('name');

        $include_file = XOOPS_ROOT_PATH."/modules/${name}/class/xmlrpc/view/".strtolower($logic).'.class.php';
        if (file_exists($include_file)) {
            include_once $include_file;
        } else {
            return $falseVar;
        }

        if (strncmp('xnp', $name, 3) == 0) {
            $tok = substr($name, 3);
            $class = 'XNP'.ucfirst($tok).'XmlRpcItemView'.ucfirst($logic);
            $ret = new $class($item);

            return $ret;
        }

        return $falseVar;
    }
}

/**
 * factory class to create XooNIpsXmlRpcViewElement.
 */
class XooNIpsXmlRpcViewFactory
{
    public function __construct()
    {
    }

    /**
     * return XooNIpsXmlRpcViewFactory instance.
     *
     * @return XooNIpsXmlRpcViewFactory
     */
    public function &getInstance()
    {
        static $singleton = null;
        if (!isset($singleton)) {
            $singleton = new self();
        }

        return $singleton;
    }

    /**
     * return XooNIpsXmlRpcViewElement corresponding to $logic.
     *
     * @param string $logic logic name
     * @param  XooNIpsXmlRpcResponse response of logic
     * @retval XooNIpsXmlRpcViewElement corresponding to $logic
     * @retval false unknown logic
     */
    public function &create($logic, $response)
    {
        $lc_logic = strtolower(trim($logic));
        $include_file = XOOPS_ROOT_PATH."/modules/xoonips/class/xmlrpc/view/{$lc_logic}.class.php";
        if (file_exists($include_file)) {
            include_once $include_file;
        }
        $class = 'XooNIpsXmlRpcView'.ucfirst(trim($logic));
        $view = new $class($response);

        return $view;
    }
}
