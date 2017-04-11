<?php

// $Revision: 1.1.4.1.2.6 $
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
define('XOOPS_XMLRPC', 1);
define('DEBUG_XMLRPC', 0);

error_reporting(0);
include 'include/common.inc.php';
restore_error_handler();

include_once XOOPS_ROOT_PATH.'/class/xml/rpc/xmlrpctag.php';
include_once XOOPS_ROOT_PATH.'/modules/xoonips/class/xmlrpc/xmlrpcparser.class.php';
include_once XOOPS_ROOT_PATH.'/modules/xoonips/class/xmlrpc/logic/xmlrpclogic.class.php';
include_once XOOPS_ROOT_PATH.'/modules/xoonips/class/base/logicfactory.class.php';
include_once XOOPS_ROOT_PATH.'/modules/xoonips/class/xmlrpc/xmlrpcfault.class.php';
include_once XOOPS_ROOT_PATH.'/modules/xoonips/class/xmlrpc/xmlrpcrequest.class.php';
include_once XOOPS_ROOT_PATH.'/modules/xoonips/class/xmlrpc/xmlrpcresponse.class.php';
include_once XOOPS_ROOT_PATH.'/modules/xoonips/class/xmlrpc/view/xmlrpcview.class.php';
include_once XOOPS_ROOT_PATH.'/modules/xoonips/class/xmlrpc/view/xmlrpcviewfactory.class.php';

error_reporting(E_ALL & ~E_NOTICE);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', 'syslog');

$allow_methods = array('XooNIps.getChildIndexes',
                      'XooNIps.getFile',
                      'XooNIps.getFileMetadata',
                      'XooNIps.getIndex',
                      'XooNIps.getItem',
                      'XooNIps.getItemPermission',
                      'XooNIps.getItemtype',
                      'XooNIps.getItemtypes',
                      'XooNIps.getPreference',
                      'XooNIps.getRootIndex',
                      'XooNIps.getSimpleItems',
                      'XooNIps.login',
                      'XooNIps.logout',
                      'XooNIps.putItem',
                      'XooNIps.removeItem',
                      'XooNIps.searchItem',
                      'XooNIps.updateItem2', );

$rpc_response = new XoopsXmlRpcResponse();
$parser = new XooNIpsXmlRpcParser(file_get_contents('php://input'));
if (!$parser->parse()) {
    $rpc_response->add(new XooNIpsXmlRpcFault(102));
} elseif (!in_array($parser->getMethodName(), $allow_methods)) {
    $rpc_response->add(new XooNIpsXmlRpcFault(107));
} else {
    global $xoopsModule;
    $module = &$xoopsModule;
    $methods = explode('.', $parser->getMethodName());
    if ($methods[0] == 'XooNIps') {
        $request = new XooNIpsXmlRpcRequest($methods[1], $parser->getParam());
        $response = new XooNIpsXmlRpcResponse();
        $factory = &XooNIpsXmlRpcLogicFactory::getInstance();
        $logic = &$factory->create($methods[1]);
        if (is_object($logic)) {
            $logic->execute($request, $response);
            if ($response->getResult()) {
                // succeed in XooNIpsLogic
                // create view and render
                $factory = &XooNIpsXmlRpcViewFactory::getInstance();
                $view = &$factory->create($methods[1], $response);
                if (!is_object($view)) {
                    $rpc_response->add(new XooNIpsXmlRpcFault(106));
                } else {
                    $rpc_response->add($view->render());
                }
            } else {
                //failed in XooNIpsLogic
                $error = $response->getError();
                $rpc_response->add(new XooNIpsXmlRpcFault(106, serialize($error->getAll())));
            }
        } else {
            $rpc_response->add(new XooNIpsXmlRpcFault(107));
        }
    } else {
        $rpc_response->add(new XooNIpsXmlRpcFault(107));
    }
}

$payload = $rpc_response->render();
header('Server: XooNIps XML-RPC Server');
header('Content-type: text/xml');
header('Content-Length: '.strlen($payload));
echo $payload;
