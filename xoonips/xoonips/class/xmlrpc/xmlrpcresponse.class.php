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
 * @see XooNIpsApi
 *
 * @brief Class that has result of logic
 *
 * @li set/get result(success or failure) using get/setResult
 * @li set/get error information using get/setError
 * @li set/get result of logic using set/getSuccess
 * @li content of setError¡¢setSuccess is not defined here(depends each logics)
 */
class XooNIpsXmlRpcResponse
{
    /**
     * @protected
     */
    public $vars = array();

    public function __construct()
    {
        $this->set('result', false);
        $this->set('error', new XooNIpsError());
        $this->set('success', null);
    }

    /**
     * @brief set success or failure
     *
     * @param[in] result true:success, false:failure
     */
    public function setResult($result)
    {
        $this->set('result', $result);
    }

    /**
     * @brief get success or failure
     *
     * @retval true success
     * @retval false failure
     */
    public function getResult()
    {
        return $this->get('result');
    }

    /**
     * @brief set error infomation
     *
     * @param XooNIpsError error
     */
    public function setError(&$error)
    {
        $this->set('error', $error);
    }

    /**
     * @brief get error infomation
     *
     * @return XooNIpsError
     */
    public function &getError()
    {
        return $this->get('error');
    }

    /**
     * @brief set result of logic
     */
    public function setSuccess(&$success)
    {
        $this->set('success', $success);
    }

    /**
     * @brief get result of logic
     */
    public function &getSuccess()
    {
        return $this->get('success');
    }

    public function &get($key)
    {
        return $this->vars[$key];
    }

    public function set($key, $value)
    {
        if (empty($key)) {
            return;
        }
        if (!isset($value)) {
            return;
        }
        $this->vars[$key] = &$value;
    }
}
