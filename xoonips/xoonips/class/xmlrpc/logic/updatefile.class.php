<?php
// $Revision: 1.1.4.1.2.5 $
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

include_once XOOPS_ROOT_PATH . '/modules/xoonips/class/xoonipserror.class.php';
include_once XOOPS_ROOT_PATH . '/modules/xoonips/class/xoonipsresponse.class.php';
include_once XOOPS_ROOT_PATH . '/modules/xoonips/class/xmlrpc/xmlrpcresponse.class.php';
include_once XOOPS_ROOT_PATH . '/modules/xoonips/class/xmlrpc/xmlrpctransform.class.php';
include_once XOOPS_ROOT_PATH . '/modules/xoonips/class/xmlrpc/logic/xmlrpclogic.class.php';

/**
 * @brief Class that executes logic specified by XML-RPC updateFile request
 *
 *
 *
 */
class XooNIpsXmlRpcLogicUpdateFile extends XooNIpsXmlRpcLogic
{

    /**
     *
     * @param[in] XooNIpsXmlRpcRequest $request
     * @param[out] XooNIpsXmlRpcResponse $response result of logic(success/fault, response, error)
     */
    function execute(&$request, &$response) 
    {
        $error = &$response->getError();
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
        //
        $params = &$request->getParams();
        $vars = array();
        if ( count($params) < 5 ){
            $response->setResult(false);
            $error->add(XNPERR_MISSING_PARAM);
            return false;
        }
        else if ( count($params) > 5 ){
            $response->setResult(false);
            $error->add(XNPERR_EXTRA_PARAM);
            return false;
        }
        //
        // parameter 1(sessionid)
        $vars[0] = $params[0];
        //
        // parameter 2(itemid)
        $unicode =& xoonips_getutility( 'unicode' );
        $vars[1] = $unicode->decode_utf8($params[1],xoonips_get_server_charset(),'h');
        //
        // parameter 3(id_type)
        $vars[2] = $params[2];
        //
        // parameter 4(fieldName)
        $vars[3] = $params[3];
        //
        //
        // transform array to object
        // parameter 5(file structure) to XooNIpsFile object
        // using XooNIpsTransformFile
        // and write file data to temporary file
        $factory = &XooNIpsXmlRpcTransformFactory::getInstance();
        $trans = &$factory->create('xoonips', 'file');
        $fileobj = $trans->getObject($params[4]);
        if (!$fileobj) {
            $response->setResult(false);
            $error->add(XNPERR_INVALID_PARAM, 'can not get file from parameter #5');
            return false;
        }
        $tmpfile = tempnam("/tmp", "FOO");
        $h = fopen( $tmpfile, "wb" );
        if ( $h ){
            $len = fwrite( $h,  $params[4]['data'] );
            fclose( $h );
        }
        if (!$h || $len != strlen($params[4]['data'])) {
            $response->setResult(false);
            $error->add(XNPERR_SERVER_ERROR, "can't write to file $tmpfile");
            return false;
        }
        $fileobj->setFilepath($tmpfile);
        $vars[4] = $fileobj;
        // execute logic
        $xoonips_response = new XooNIpsResponse();
        $logic->execute($vars, $xoonips_response);
        //
        $response->setResult($xoonips_response->getResult());
        $response->setError($xoonips_response->getError());
        $response->setSuccess($xoonips_response->getSuccess());
    }
}
?>
