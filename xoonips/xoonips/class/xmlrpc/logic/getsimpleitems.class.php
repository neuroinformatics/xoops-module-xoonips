<?php

// $Revision: 1.1.2.6 $
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

require_once XOOPS_ROOT_PATH.'/modules/xoonips/class/xoonipserror.class.php';
require_once XOOPS_ROOT_PATH.'/modules/xoonips/class/xoonipsresponse.class.php';
require_once XOOPS_ROOT_PATH.'/modules/xoonips/class/xmlrpc/xmlrpcresponse.class.php';
require_once XOOPS_ROOT_PATH.'/modules/xoonips/class/xmlrpc/xmlrpctransform.class.php';
require_once XOOPS_ROOT_PATH.'/modules/xoonips/class/xmlrpc/logic/xmlrpclogic.class.php';

/**
 * @brief Class that executes logic specified by XML-RPC updateFile request
 */
class XooNIpsXmlRpcLogicGetSimpleItems extends XooNIpsXmlRpcLogic
{
    /**
     * load and execute xoonips logic.
     *
     * @param[in]  XooNIpsXmlRpcRequest $request
     * @param[out] XooNIpsXmlRpcResponse $response
     *  result of logic(success/fault, response, error)
     */
    public function execute(&$request, &$response)
    {
        // load logic instance
        $factory = &XooNIpsLogicFactory::getInstance();
        $logic = &$factory->create($request->getMethodName());
        if (!is_object($logic)) {
            $response->setResult(false);
            $error = &$response->getError();
            $logic = $request->getMethodName();
            $error->add(XNPERR_SERVER_ERROR, "can't create a logic of $logic");

            return;
        }
        // execute logic
        $params = &$request->getParams();
        $xoonips_response = new XooNIpsResponse();

        if (count($params) < 3) {
            $response->setResult(false);
            $error = &$response->getError();
            $error->add(XNPERR_MISSING_PARAM);

            return false;
        } elseif (count($params) > 3) {
            $response->setResult(false);
            $error = &$response->getError();
            $error->add(XNPERR_EXTRA_PARAM);

            return false;
        }
        $vars = array();
        $vars[0] = $params[0];
        $unicode = &xoonips_getutility('unicode');
        $vars[1] = array();
        foreach ($params[1] as $key => $val) {
            $vars[1][$key] = $unicode->decode_utf8($val, xoonips_get_server_charset(), 'h');
        }
        $vars[2] = $params[2];

        $logic->execute($vars, $xoonips_response);

        $response->setResult($xoonips_response->getResult());
        $response->setError($xoonips_response->getError());
        $response->setSuccess($xoonips_response->getSuccess());
    }
}
