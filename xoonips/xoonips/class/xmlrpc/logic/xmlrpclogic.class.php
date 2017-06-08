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

/**
 * @brief Class that executes logic specified by XML-RPC request
 */
class XooNIpsXmlRpcLogic
{
    public function __construct()
    {
    }

    /**
     * load and execute xoonips logic. see [xoonips:00025].
     *
     * @param[in]  XooNIpsXmlRpcRequest $request
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

            return;
        }
        // execute logic
        $vars = &$request->getParams();
        $xoonips_response = new XooNIpsResponse();
        $logic->execute($vars, $xoonips_response);

        $response->setResult($xoonips_response->getResult());
        $response->setError($xoonips_response->getError());
        $response->setSuccess($xoonips_response->getSuccess());
    }

    public function convertIndexObjectToIndexStructure($index_compo, &$response)
    {
        $index = $index_compo->getVar('index');

        $titles = $index_compo->getVar('titles');
        $title = $titles[0]->get('title');

        switch ($index->get('open_level')) {
        case 1: //public
            $open_level = 'public';
            break;

        case 2: //group
            $groups_handler = &xoonips_getormhandler('xoonips', 'groups');
            $group = $groups_handler->get($index->get('gid'));
            if ($group) {
                $open_level = $group->get('gname');
            } else {
                $response->addError(XNPERR_SERVER_ERROR, 'group of index is not found. index_id:'.$index->get('index_id'));

                return false;
            }
            break;

        case 3: //private
            $open_level = 'private';
            if ($index->get('parent_index_id') == IID_ROOT && $index->get('uid') == $_SESSION['xoopsUserId']) {
                $title = XNP_PRIVATE_INDEX_TITLE; // title of /Private is not username but "Private"
            }
            break;

        default:
            $response->addError(XNPERR_SERVER_ERROR, 'unknown open level:'.$index->get('open_level'));

            return false;
        }

        $index_compo_handler = &xoonips_getormcompohandler('xoonips', 'index');
        $user_handler = &xoonips_getormhandler('xoonips', 'users');
        $user = $user_handler->get($_SESSION['xoopsUserId']);
        $paths = $index_compo_handler->getIndexPathNames($index->get('index_id'), $user ? $user->get('private_index_id') : false);

        return array(
            'id' => $index->get('index_id'),
            'name' => $title,
            'parent' => $index->get('parent_index_id'),
            'open_level' => $open_level,
            'path' => '/'.implode('/', $paths),
        );
    }
}
class XooNIpsXmlRpcLogicFactory
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
        $include_file = XOOPS_ROOT_PATH.'/modules/xoonips/class/xmlrpc/logic/'.strtolower($name).'.class.php';
        if (file_exists($include_file)) {
            require_once $include_file;
        } else {
            // return generic logic if logic corresponding to $name is not found
            $logic = new XooNIpsXmlRpcLogic();

            return $logic;
        }

        $class = 'XooNIpsXmlRpcLogic'.ucfirst($name);
        if (class_exists($class)) {
            $logic = new $class();
        } else {
            // return generic logic if logic corresponding to $name is not found
            return $generic_logic;
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
