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

require_once XOOPS_ROOT_PATH.'/modules/xoonips/class/xmlrpc/view/xmlrpcview.class.php';
require_once XOOPS_ROOT_PATH.'/modules/xoonips/class/xmlrpc/xmlrpcfault.class.php';

/**
 * @brief Class that generate response of XML-RPC getItem request
 */
class XooNIpsXmlRpcViewGetItem extends XooNIpsXmlRpcViewElement
{
    public function __construct(&$response)
    {
        parent::__construct($response);
        $factory = &XooNIpsXmlRpcItemViewFactory::getInstance();
        $item = &$response->getSuccess();
        $view = &$factory->create('getItem', $item);
        if ($view) {
            $this->addView($view);
        }
    }

    /**
     * @brief return XoopsXmlRpcTag that has response of this request
     *
     * @return XoopsXmlRpcTag
     */
    public function render()
    {
        if (isset($this->views[0])) {
            return $this->views[0]->render();
        } else {
            $resp = new XooNIpsXmlRpcFault(106);

            return $resp;
        }
    }
}
