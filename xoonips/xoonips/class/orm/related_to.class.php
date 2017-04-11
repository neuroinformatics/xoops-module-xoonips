<?php

// $Revision: 1.1.4.1.2.8 $
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

/*
alter table following from xoonips 3.24
ALTER TABLE `x_xoonips_related_to` DROP PRIMARY KEY ;
ALTER TABLE `x_xoonips_related_to` ADD `related_to_id` INT( 10 ) NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST ;
ALTER TABLE `x_xoonips_related_to` CHANGE `parent_id` `parent_id` INT( 10 ) NOT NULL ,CHANGE `item_id` `item_id` INT( 10 ) NOT NULL ;
*/
/**
 * @brief data object of related item
 *
 * @li getVar('related_to_id') :
 * @li getVar('parent_id') :
 * @li getVar('item_id') :
 */
class XooNIpsOrmRelatedTo extends XooNIpsTableObject
{
    public function XooNIpsOrmRelatedTo()
    {
        $this->initVar('related_to_id', XOBJ_DTYPE_INT, 0, false);
        $this->initVar('parent_id', XOBJ_DTYPE_TXTBOX, null, false);
        $this->initVar('item_id', XOBJ_DTYPE_INT, null, true);
    }
}

/**
 * @brief handler class of related item
 */
class XooNIpsOrmRelatedToHandler extends XooNIpsTableObjectHandler
{
    public function XooNIpsOrmRelatedToHandler(&$db)
    {
        parent::XooNIpsTableObjectHandler($db);
        $this->__initHandler('XooNIpsOrmRelatedTo', 'xoonips_related_to', 'related_to_id');
    }

  /**
   * @brief gets a value object
   * use getObjects instead of this
   *
   * @param int $id
   *
   * @return false always false
   */
  public function &get($id)
  {
      return false;
  }

  /**
   * get child item ids.
   *
   * @param int $parent_id parent item id
   *
   * @return array item ids
   */
  public function getChildItemIds($parent_id)
  {
      $objs = &$this->_getObjectsByParentItemId($parent_id);

      return array_keys($objs);
  }

  /**
   * insert/update child item ids.
   *
   * @acccess public
   *
   * @param int   $parent_id parent item id
   * @param array $item_ids
   *
   * @return bool false if failure
   */
  public function insertChildItemIds($parent_id, $item_ids)
  {
      $objs_old = &$this->_getObjectsByParentItemId($parent_id);
      $objs_new = array();
      foreach ($item_ids as $item_id) {
          if ($parent_id == $item_id) {
              continue;
          } // ignore myself
      if (isset($objs_old[$item_id])) {
          $obj = &$objs_old[$item_id];
      } else {
          $obj = &$this->create();
          $obj->set('parent_id', $parent_id);
          $obj->set('item_id', $item_id);
      }
          $objs_new[] = &$obj;
          unset($obj);
      }

      return $this->updateAllObjectsByForeignKey('parent_id', $parent_id, $objs_new);
  }

  /**
   * delete child item ids.
   *
   * @param int $item_id child item id
   *
   * @return bool false if failure
   */
  public function deleteChildItemIds($item_id)
  {
      $criteria = new Criteria('item_id', $item_id);

      return $this->deleteAll($criteria);
  }

  /**
   * get objects by parent item id.
   *
   * @param int $item_id parent item id
   *
   * @return array objects
   */
  public function _getObjectsByParentItemId($parent_id)
  {
      $criteria = new Criteria('parent_id', $parent_id);
      $res = &$this->open($criteria);
      $objs = array();
      while ($obj = &$this->getNext($res)) {
          $item_id = $obj->get('item_id');
          $objs[$item_id] = &$obj;
          unset($obj);
      }
      $this->close($res);

      return $objs;
  }
}
