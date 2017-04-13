<?php

// $Revision: 1.1.4.1.2.3 $
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

define('XNPERR_ERROR', 100); // generic error
define('XNPERR_INVALID_SESSION', 101); // invalid session
define('XNPERR_AUTH_FAILURE', 102); // authentication failure
define('XNPERR_ACCESS_FORBIDDEN', 103); // access forbidden(no access right)
define('XNPERR_NOT_FOUND', 104); // requested content is not found
define('XNPERR_INCOMPLETE_PARAM', 105); // given parameter is incomplete.
define('XNPERR_MISSING_PARAM', 106); // missing parameters
define('XNPERR_EXTRA_PARAM', 107); // extra parameters
define('XNPERR_INVALID_PARAM', 108); // invalid parameter(data type, value format)
define('XNPERR_SERVER_ERROR', 109); // error in server
define('XNPERR_NUMBER_OF_ITEM_LIMIT_EXCEEDS', 111);
define('XNPERR_STORAGE_OF_ITEM_LIMIT_EXCEEDS', 112);

/**
 * @brief Class that has error informations of logic
 *
 * @see XooNIpsResponse
 *
 * error codes shown below is reserved.
 * @li 100 generic error
 * @li 101 invalid session
 * @li 102 authentication failue
 * @li 103 access forbidden(no access right)
 * @li 104 requested content is not found
 * @li 105 given parameter is incomplete.
 * @li 106 missing parameters
 * @li 107 extra parameters
 * @li 108 invalid parameter(data type, value format)
 * @li 109 error in server
 * @li 110 no such method
 */
class XooNIpsError
{
    public $error = array();

    /**
     * @brief create XooNIpsError with error code and error message
     *
     * @param int    $code  error code
     * @param string $extra extra information of err(null if omitted)
     */
    public function __construct($code = null, $extra = null)
    {
        if (!is_null($code)) {
            $this->add($code, $extra);
        }
    }

    /**
     * @brief add error code and error message
     *
     * @param int    $code  error code
     * @param string $extra extra information of err(null if omitted)
     */
    public function add($code, $extra = null)
    {
        $this->error[] = array(
            'code' => intval($code),
            'extra' => is_null($extra) ? '' : $extra,
        );
    }

    /**
     * @brief return error informations
     *
     * @return array following associative array
     * @code
     * array( [0] => array( 'code' => CODE, 'extra' => EXTRA ),
     *        [1] => array( same above                       ),
     *         ... );
     * @endcode
     */
    public function getAll()
    {
        return $this->error;
    }

    /**
     * @brief return error information
     *
     * @param int index of error(>=0)
     * @retval array associative array( 'code' => CODE, 'extra' => EXTRA ) that correspond to $i
     * @retval false no errors correspond to $i
     */
    public function get($i = 0)
    {
        if (isset($this->error[intval($i)])) {
            return $this->error[intval($i)];
        }

        return false;
    }
}
