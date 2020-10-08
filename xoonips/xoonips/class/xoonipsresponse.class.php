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

require_once __DIR__.'/xoonipserror.class.php';

/**
 * @brief Class that have results of a logic
 *
 * @see XooNIpsError
 *
 * @li set or get a result(success or fault) using get/setResult methods
 * @li set or get error informations using get/setError methods(only if failed)
 * @li set or get response of logic using set/getSuccess(only if succeed)
 */
class XooNIpsResponse
{
    /**
     * result of logic.
     */
    public $result = false;

    /**
     * response of logic.
     */
    public $success = false;

    /**
     * error information of logic.
     */
    public $error = false;

    /**
     * create XooNIpsResponse.
     */
    public function __construct()
    {
        $this->error = new XooNIpsError();
    }

    /**
     * @brief get result
     *
     * @return bool false if failed
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @brief get response of logic if succeed.
     *
     * @return bool response of logic
     */
    public function &getSuccess()
    {
        return $this->success;
    }

    /**
     * @brief get error informatino of logic if failed.
     *
     * @return bool error
     */
    public function &getError()
    {
        return $this->error;
    }

    /**
     * @brief add error code and error message
     *
     * @param int    $code  error code
     * @param string $extra extra information of err(null if omitted)
     */
    public function addError($code, $extra = null)
    {
        $this->error->add($code, $extra);
    }

    /**
     * @brief set error informatino of logic if failed.
     *
     * @param bool $result
     */
    public function setResult($result)
    {
        $this->result = $result;
    }

    /**
     * @brief set error informatino of logic if failed.
     *
     * @param mixed $success
     */
    public function setSuccess(&$success)
    {
        $this->success = &$success;
    }
}
