<?php

// $Revision: 1.1.4.1.2.10 $
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
if (!defined('XOOPS_ROOT_PATH')) {
    exit();
}

/**
 * @brief Data object of system preference
 *
 * @li getVar('name') : key
 * @li getVar('value') : value
 */
class XooNIpsOrmConfig extends XooNIpsTableObject
{
    public function XooNIpsOrmConfig()
    {
        parent::XooNIpsTableOBject();
        $this->initVar('id', XOBJ_DTYPE_INT, 0, false);
        $this->initVar('name', XOBJ_DTYPE_TXTBOX, null, true, 255);
        $this->initVar('value', XOBJ_DTYPE_BINARY, null, false, 65535);
    }
}

/**
 * @brief Handler object of system preference
 */
class XooNIpsOrmConfigHandler extends XooNIpsTableObjectHandler
{
    public function XooNIpsOrmConfigHandler(&$db)
    {
        parent::XooNIpsTableObjectHandler($db);
        $this->__initHandler('XooNIpsOrmConfig', 'xoonips_config', 'id', false);
    }

  /**
   * get a configuration value.
   *
   * @param string $key configuration key
   *
   * @return string configuration value
   */
  public function getValue($key)
  {
      $config_obj = &$this->getConfig($key);
      if (!is_object($config_obj)) {
          return null;
      }

      return $config_obj->get('value');
  }

  /**
   * set a configuration value.
   *
   * @param string $key   configuration key
   * @param string $val   configuration value
   * @param bool   $force force update
   *
   * @return bool FALSE if failed
   */
  public function setValue($key, $val, $force = false)
  {
      $config_obj = &$this->getConfig($key);
      if (!is_object($config_obj)) {
          return false;
      }
      $config_obj->set('value', $val);

      return $this->insert($config_obj, $force);
  }

  /**
   * get a configuration value object.
   *
   * @param string key configuration key
   *
   * @return object XooNIpsOrmConfig. return false if key was not found.
   */
  public function &getConfig($key)
  {
      $config_objs = &$this->getObjects(new Criteria('name', addslashes($key)));
      if (!$config_objs || count($config_objs) != 1) {
          $result = false;

          return $result;
      }

      return $config_objs[0];
  }
}
