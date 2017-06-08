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
defined('XOOPS_ROOT_PATH') || exit('XOOPS root path not defined');

/**
 * @brief class of XooNIps User.
 *
 * @li getVar('uid') : user id
 * @li getVar('activate') : account certification flag
 * @li getVar('address') : address
 * @li getVar('division') :
 * @li getVar('tel') :
 * @li getVar('company_name') : name of company, univercity, institute
 * @li getVar('country') :
 * @li getVar('zipcode') :
 * @li getVar('fax') :
 * @li getVar('base_url') :
 * @li getVar('notice_mail') :
 * @li getVar('notice_mail_since') :
 * @li getVar('private_index_id') : private index id
 * @li getVar('private_item_number_limit') :
 * @li getVar('private_index_number_limit') :
 * @li getVar('private_item_storage_limit') :
 * @li getVar('user_order') : disply order
 * @li getVar('posi') : position id
 * @li getVar('appeal') :
 */
class XooNIpsOrmUsers extends XooNIpsTableObject
{
    public function __construct()
    {
        parent::__construct();
        // from XooNIps_users table
        $this->initVar('uid', XOBJ_DTYPE_INT, 0, true, null);
        $this->initVar('activate', XOBJ_DTYPE_INT, 0, true, null);
        $this->initVar('address', XOBJ_DTYPE_TXTBOX, null, false, 255);
        $this->initVar('division', XOBJ_DTYPE_TXTBOX, null, false, 255);
        $this->initVar('tel', XOBJ_DTYPE_TXTBOX, '', false, 32);
        $this->initVar('company_name', XOBJ_DTYPE_TXTBOX, '', false, 255);
        $this->initVar('country', XOBJ_DTYPE_TXTBOX, '', false, 255);
        $this->initVar('zipcode', XOBJ_DTYPE_TXTBOX, '', false, 32);
        $this->initVar('fax', XOBJ_DTYPE_TXTBOX, '', false, 32);
        $this->initVar('base_url', XOBJ_DTYPE_TXTBOX, null, false, 255);
        $this->initVar('notice_mail', XOBJ_DTYPE_INT, 0, false, null);
        $this->initVar('notice_mail_since', XOBJ_DTYPE_INT, 0, false, null);
        $this->initVar('private_index_id', XOBJ_DTYPE_INT, 0, true, null);
        $this->initVar('private_item_number_limit', XOBJ_DTYPE_INT, null, true, null);
        $this->initVar('private_index_number_limit', XOBJ_DTYPE_INT, null, true, null);
        // data type = double
        $this->initVar('private_item_storage_limit', XOBJ_DTYPE_OTHER, null, true, null);
        $this->initVar('user_order', XOBJ_DTYPE_INT, 0, false, null);
        $this->initVar('posi', XOBJ_DTYPE_INT, 0, false, null);
        $this->initVar('appeal', XOBJ_DTYPE_TXTBOX, '', false, 65535);
    }

    /**
     * clean values of all variables of the object for storage.
     * also add slashes whereever needed.
     *
     * @return bool true if successful
     */
    public function cleanVars()
    {
        $retval = true;
        // check required/optional values
        $xconfig_handler = &xoonips_getormhandler('xoonips', 'config');
        // $realname_optional = $xconfig_handler->getValue('account_realname_optional');
        $address_optional = $xconfig_handler->getValue('account_address_optional');
        $division_optional = $xconfig_handler->getValue('account_division_optional');
        $tel_optional = $xconfig_handler->getValue('account_tel_optional');
        $company_name_optional = $xconfig_handler->getValue('account_company_name_optional');
        $country_optional = $xconfig_handler->getValue('account_country_optional');
        $zipcode_optional = $xconfig_handler->getValue('account_zipcode_optional');
        $fax_optional = $xconfig_handler->getValue('account_fax_optional');
        // $this->vars['name']['required'] = ($realname_optional && $realname_optional == 'off');
        $this->vars['address']['required'] = ($address_optional == 'off');
        $this->vars['division']['required'] = ($division_optional == 'off');
        $this->vars['tel']['required'] = ($tel_optional == 'off');
        $this->vars['company_name']['required'] = ($company_name_optional == 'off');
        $this->vars['country']['required'] = ($country_optional == 'off');
        $this->vars['zipcode']['required'] = ($zipcode_optional == 'off');
        $this->vars['fax']['required'] = ($fax_optional == 'off');
        // is private_item_storage_limit double?
        if (!is_numeric($this->get('private_item_storage_limit'))) {
            // todo: define constant string
            $this->setErrors('private_item_storage_limit must be numeric.');
            $retval = false;
        }

        return $retval && parent::cleanVars();
    }
}

/**
 * handler class of XooNIps User.
 */
class XooNIpsOrmUsersHandler extends XooNIpsTableObjectHandler
{
    public function __construct(&$db)
    {
        parent::__construct($db);
        $this->__initHandler('XooNIpsOrmUsers', 'xoonips_users', 'uid', false);
    }

    /**
     * create a new object.
     *
     * @param bool isNew mark the new object as 'new'?
     *
     * @return object XooNIpsOrmUsers reference to the new object
     */
    public function &create($isNew = true)
    {
        $obj = parent::create($isNew);
        if ($obj === false) {
            return $obj;
        }
        if ($isNew) {
            // set default private index/item/storage limit
            $xconfig_handler = &xoonips_getormhandler('xoonips', 'config');
            $keys = array(
            // config key name  => field name of 'xoonips_users' table
            'private_index_number_limit' => 'private_index_number_limit',
            'private_item_number_limit' => 'private_item_number_limit',
            'private_item_storage_limit' => 'private_item_storage_limit',
            );
            foreach ($keys as $key => $field) {
                $value = $xconfig_handler->getValue($key);
                $obj->set($field, $value);
                unset($xcobj);
            }
        }

        return $obj;
    }
}
