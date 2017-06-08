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

require_once XOOPS_ROOT_PATH.'/modules/xoonips/class/xoonipserror.class.php';
require_once XOOPS_ROOT_PATH.'/modules/xoonips/class/xoonipsresponse.class.php';
require_once XOOPS_ROOT_PATH.'/modules/xoonips/class/xmlrpc/xmlrpcresponse.class.php';
require_once XOOPS_ROOT_PATH.'/modules/xoonips/class/xmlrpc/xmlrpctransform.class.php';
require_once XOOPS_ROOT_PATH.'/modules/xoonips/class/xmlrpc/logic/xmlrpclogic.class.php';

/**
 * @brief Class that executes logic specified by XML-RPC putItem request
 */
class XooNIpsXmlRpcLogicPutItem extends XooNIpsXmlRpcLogic
{
    /**
     * @param[in] XooNIpsXmlRpcRequest $request
     * @param[out] XooNIpsXmlRpcResponse $response result of logic(success/fault, response, error)
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

            return false;
        }

        $params = &$request->getParams();
        $vars = array();
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
        //
        // parameter 1(sessionid)
        $vars[0] = $params[0];
        //
        // transform array to object
        // parameter 2(item structure) to XooNIpsItemCompo object
        // using XooNIpsTransformCompo<itemtype>
        $item_type_id = intval($params[1]['itemtype']);
        $itemtype_handler = &xoonips_getormhandler('xoonips', 'item_type');
        $itemtype = &$itemtype_handler->get($item_type_id);
        if (!$itemtype) {
            $response->setResult(false);
            $response->setError(new XooNIpsError(XNPERR_INVALID_PARAM, "item type of $item_type_id is not found"));

            return false;
        }

        $factory = &XooNIpsXmlRpcTransformCompoFactory::getInstance();
        $trans = &$factory->create($itemtype->get('name'));
        // check that all required fields is filled
        $missing = array();
        if (!$trans->isFilledRequired($params[1], $missing)) {
            $response->setResult(false);
            $err = &$response->getError();
            foreach ($missing as $m) {
                $err->add(XNPERR_INCOMPLETE_PARAM, $m);
            }

            return false;
        }
        // check mulitple of each variable
        $fields = array();
        if (!$trans->checkMultipleFields($params[1], $fields)) {
            $response->setResult(false);
            $err = &$response->getError();
            foreach ($fields as $m) {
                $err->add(XNPERR_INCOMPLETE_PARAM, $m);
            }

            return false;
        }
        // check fields
        if (!$trans->checkFields($params[1], $response->getError())) {
            // some fields have invalid value
            return false;
        }
        // transform array to item object, and set it to $vars.
        $vars[1] = $trans->getObject($params[1]);

        //
        // transform array to object
        // parameter 3(file structure) to XooNIpsFile object
        // using XooNIpsTransformFile
        // and write file data to temporary file
        $factory = &XooNIpsXmlRpcTransformFactory::getInstance();
        $trans = &$factory->create('xoonips', 'file');
        if (is_array($params[2])) {
            $vars[2] = array();
            foreach ($params[2] as $p) {
                $fileobj = $trans->getObject($p);
                if (!$fileobj) {
                    $response->setResult(false);
                    $response->setError(new XooNIpsError(XNPERR_INVALID_PARAM, "can't get file from XML"));

                    return false;
                }
                $tmpfile = tempnam('/tmp', 'FOO');
                $h = fopen($tmpfile, 'wb');
                if ($h) {
                    $len = fwrite($h, $p['data']);
                    fclose($h);
                }
                if (!$h || $len != strlen($p['data'])) {
                    $response->setResult(false);
                    $response->setError(new XooNIpsError(XNPERR_SERVER_ERROR, "can't write to file $tmpfile"));

                    return false;
                }
                $fileobj->setFilepath($tmpfile);
                $vars[2][] = $fileobj;
            }
        }
        // execute logic
        $xoonips_response = new XooNIpsResponse();
        $logic->execute($vars, $xoonips_response);

        $response->setResult($xoonips_response->getResult());
        $response->setError($xoonips_response->getError());
        $response->setSuccess($xoonips_response->getSuccess());
    }
}
