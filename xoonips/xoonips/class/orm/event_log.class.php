<?php

// $Revision: 1.1.4.1.2.27 $
// ------------------------------------------------------------------------- //
//  XooNIps - Neuroinformatics Base Platform System                          //
//  Copyright (C) 2005-2013 RIKEN, Japan All rights reserved.                //
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
 * @brief Data object of XooNIps event
 *
 * @li getVar('event_id') :
 * @li getVar('event_type_id') :
 * @li getVar('timestamp') :
 * @li getVar('exec_uid') :
 * @li getVar('remote_host') :
 * @li getVar('index_id') :
 * @li getVar('item_id') :
 * @li getVar('file_id') :
 * @li getVar('uid') :
 * @li getVar('gid') :
 * @li getVar('search_keyword') :
 * @li getVar('additional_info') :
 */
class XooNIpsOrmEventLog extends XooNIpsTableObject
{
    public function XooNIpsOrmEventLog()
    {
        parent::XooNIpsTableObject();
        $this->initVar('event_id', XOBJ_DTYPE_INT, null, false, null);
        $this->initVar('event_type_id', XOBJ_DTYPE_INT, null, true, null);
        $this->initVar('timestamp', XOBJ_DTYPE_INT, null, true, null);
        $this->initVar('exec_uid', XOBJ_DTYPE_INT, null, false, null);
        $this->initVar('remote_host', XOBJ_DTYPE_TXTBOX, null, false, 255);
        $this->initVar('index_id', XOBJ_DTYPE_INT, null, false, null);
        $this->initVar('item_id', XOBJ_DTYPE_INT, null, false, null);
        $this->initVar('file_id', XOBJ_DTYPE_INT, null, false, null);
        $this->initVar('uid', XOBJ_DTYPE_INT, null, false, null);
        $this->initVar('gid', XOBJ_DTYPE_INT, null, false, null);
        $this->initVar('search_keyword', XOBJ_DTYPE_TXTBOX, null, false, 65535);
        $this->initVar('additional_info', XOBJ_DTYPE_TXTBOX, null, false, 65535);
    }

    public function cleanVars()
    {
        $event_type_id = $this->get('event_type_id');
        if ($event_type_id < 1 || ETID_MAX < $event_type_id) {
            $result = false;
            $this->setErrors("invalid event_type_id(${event_type_id})");
        } else {
            $result = true;
      // php-indent: disable
      static $fields = array('timestamp', 'exec_uid', 'index_id', 'item_id', 'file_id', 'uid', 'gid', 'search_keyword', 'additional_info');
            static $eventValidFields = array(
        array(0, 0, 0, 0, 0, 0, 0, 0, 0), //  0: (dummy)
        array(1, 1, 0, 0, 0, 0, 0, 0, 1), //  1: ETID_LOGIN_FAILURE
        array(1, 1, 0, 0, 0, 0, 0, 0, 0), //  2: ETID_LOGIN_SUCCESS
        array(1, 1, 0, 0, 0, 0, 0, 0, 0), //  3: ETID_LOGOUT
        array(1, 1, 0, 1, 0, 0, 0, 0, 0), //  4: ETID_INSERT_ITEM
        array(1, 1, 0, 1, 0, 0, 0, 0, 0), //  5: ETID_UPDATE_ITEM
        array(1, 1, 0, 1, 0, 0, 0, 0, 0), //  6: ETID_DELETE_ITEM
        array(1, 1, 0, 1, 0, 0, 0, 0, 0), //  7: ETID_VIEW_ITEM
        array(1, 1, 0, 1, 1, 0, 0, 0, 0), //  8: ETID_DOWNLOAD_FILE
        array(1, 1, 1, 1, 0, 0, 0, 0, 0), //  9: ETID_REQUEST_CERTIFY_ITEM
        array(1, 1, 1, 0, 0, 0, 0, 0, 0), // 10: ETID_INSERT_INDEX
        array(1, 1, 1, 0, 0, 0, 0, 0, 0), // 11: ETID_UPDATE_INDEX
        array(1, 1, 1, 0, 0, 0, 0, 0, 0), // 12: ETID_DELETE_INDEX
        array(1, 1, 1, 1, 0, 0, 0, 0, 0), // 13: ETID_CERTIFY_ITEM
        array(1, 1, 1, 1, 0, 0, 0, 0, 0), // 14: ETID_REJECT_ITEM
        array(1, 0, 0, 0, 0, 1, 0, 0, 0), // 15: ETID_REQUEST_INSERT_ACCOUNT
        array(1, 1, 0, 0, 0, 1, 0, 0, 0), // 16: ETID_CERTIFY_ACCOUNT
        array(1, 1, 0, 0, 0, 0, 1, 0, 0), // 17: ETID_INSERT_GROUP
        array(1, 1, 0, 0, 0, 0, 1, 0, 0), // 18: ETID_UPDATE_GROUP
        array(1, 1, 0, 0, 0, 0, 1, 0, 0), // 19: ETID_DELETE_GROUP
        array(1, 1, 0, 0, 0, 1, 1, 0, 0), // 20: ETID_INSERT_GROUP_MEMBER
        array(1, 1, 0, 0, 0, 1, 1, 0, 0), // 21: ETID_DELETE_GROUP_MEMBER
        array(1, 1, 0, 0, 0, 0, 0, 0, 0), // 22: ETID_VIEW_TOP_PAGE
        array(1, 1, 0, 0, 0, 0, 0, 1, 0), // 23: ETID_QUICK_SEARCH
        array(1, 1, 0, 0, 0, 0, 0, 1, 0), // 24: ETID_ADVANCED_SEARCH
        array(1, 1, 0, 0, 0, 1, 0, 0, 0), // 25: ETID_START_SU
        array(1, 1, 0, 0, 0, 1, 0, 0, 0), // 26: ETID_END_SU
        array(1, 1, 0, 1, 0, 1, 0, 0, 0), // 27: ETID_REQUEST_TRANSFER_ITEM
        array(1, 1, 1, 1, 0, 1, 0, 0, 0), // 28: ETID_TRANSFER_ITEM
        array(1, 1, 0, 1, 0, 0, 0, 0, 0), // 29: ETID_REJECT_TRANSFER_ITEM
        array(1, 1, 1, 0, 0, 0, 0, 0, 0), // 30: ETID_CERTIFY_GROUP_INDEX
        array(1, 1, 1, 0, 0, 0, 0, 0, 0), // 31: ETID_REJECT_GROUP_INDEX
        array(1, 1, 1, 1, 0, 0, 1, 0, 1), // 32: ETID_GROUP_INDEX_TO_PUBLIC
        array(1, 1, 0, 0, 0, 1, 0, 0, 0), // 33: ETID_DELETE_ACCOUNT
        array(1, 1, 0, 0, 0, 1, 0, 0, 1), // 34: ETID_UNCERTIFY_ACCOUNT
      );
      // php-indent: enable
      foreach ($fields as $i => $field) {
          // check unnecessary && specified values
        if ($eventValidFields[$event_type_id][$i] == 0 && !is_null($this->vars[$field]['value'])) {
            $result = false;
            $this->setErrors("cannot specify $field if event_type_id=$event_type_id");
        }
        // check necessary && unspecified values
        $this->vars[$field]['required'] = (bool) ($eventValidFields[$event_type_id][$i]);
      }
        }

        return $result && parent::cleanVars();
    }
}

/**
 * @brief Handler object of XooNIps event
 */
class XooNIpsOrmEventLogHandler extends XooNIpsTableObjectHandler
{
    public function XooNIpsOrmEventLogHandler(&$db)
    {
        parent::XooNIpsTableObjectHandler($db);
        $this->__initHandler('XooNIpsOrmEventLog', 'xoonips_event_log', 'event_id');
    }

  /**
   * create a new object.
   *
   * @param bool isNew mark the new object as 'new'?
   *
   * @return object XooNIpsOrmEventLog reference to the new object
   */
  public function &create($isNew = true)
  {
      $obj = parent::create($isNew);
      if ($obj === false) {
          return $obj;
      }
      if ($isNew) {
          $obj->set('remote_host', $this->getRemoteHost());
          $obj->set('timestamp', time());
      }

      return $obj;
  }

  /**
   * get remote host.
   *
   * @return string remote host name or address
   */
  public function getRemoteHost()
  {
      $remote_host = '';
      if (isset($_SERVER['REMOTE_HOST'])) {
          $remote_host = $_SERVER['REMOTE_HOST'];
      } elseif (isset($_SERVER['REMOTE_ADDR'])) {
          $remote_host = $_SERVER['REMOTE_ADDR'];
      }
    // check proxy environment
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $remote_host = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } elseif (isset($_SERVER['HTTP_VIA'])) {
        $remote_host = $_SERVER['HTTP_VIA'];
    }
      if (preg_match('/^\\d{1,3}\\.\\d{1,3}\\.\\d{1,3}\\.\\d{1,3}$/', $remote_host)) {
          if (function_exists('gethostbyaddr')) {
              $remote_host = gethostbyaddr($remote_host);
          }
      }

      return $remote_host;
  }

  /**
   * get execution user id.
   *
   * @return int execution user id
   */
  public function getExecUid()
  {
      $exec_uid = UID_GUEST;
      if (isset($GLOBALS['xoopsUser']) && is_object($GLOBALS['xoopsUser'])) {
          $exec_uid = intval($GLOBALS['xoopsUser']->getVar('uid', 'n'));
      }

      return $exec_uid;
  }

  /**
   * record login failure event (ETID_LOGIN_FAILURE: 1).
   *
   * @param string $uname trying login uname
   *
   * @return bool false if failure
   */
  public function recordLoginFailureEvent($uname)
  {
      $obj = &$this->create();
      $obj->set('event_type_id', ETID_LOGIN_FAILURE);
      $obj->set('exec_uid', UID_GUEST);
      $obj->set('additional_info', $uname);

      return $this->insert($obj, true);
  }

  /**
   * record login success event (ETID_LOGIN_SUCCESS: 2).
   *
   * @param int $uid login user id
   *
   * @return bool false if failure
   */
  public function recordLoginSuccessEvent($uid)
  {
      $obj = &$this->create();
      $obj->set('event_type_id', ETID_LOGIN_SUCCESS);
      $obj->set('exec_uid', $uid);

      return $this->insert($obj, true);
  }

  /**
   * record logout event (ETID_LOGOUT: 3).
   *
   * @param int $uid       logout user id
   * @param int $timestamp logout timestamp for session GC
   *
   * @return bool false if failure
   */
  public function recordLogoutEvent($uid, $timestamp = null)
  {
      $obj = &$this->create();
      $obj->set('event_type_id', ETID_LOGOUT);
      $obj->set('exec_uid', $uid);
      if (!is_null($timestamp)) {
          // override timestamp for session GC
      $obj->set('timestamp', $timestamp);
      }
    // force insertion
    return $this->insert($obj, true);
  }

  /**
   * record insert item event (ETID_INSERT_ITEM: 4).
   *
   * @param int $item_id inserted item id
   *
   * @return bool false if failure
   */
  public function recordInsertItemEvent($item_id)
  {
      $obj = &$this->create();
      $obj->set('event_type_id', ETID_INSERT_ITEM);
      $obj->set('exec_uid', $this->getExecUid());
      $obj->set('item_id', $item_id);

      return $this->insert($obj);
  }

  /**
   * record update item event (ETID_UPDATE_ITEM: 5).
   *
   * @param int $item_id updated item id
   *
   * @return bool false if failure
   */
  public function recordUpdateItemEvent($item_id)
  {
      $obj = &$this->create();
      $obj->set('event_type_id', ETID_UPDATE_ITEM);
      $obj->set('exec_uid', $this->getExecUid());
      $obj->set('item_id', $item_id);

      return $this->insert($obj);
  }

  /**
   * record delete item event (ETID_DELETE_ITEM: 6).
   *
   * @param int $item_id deleted item id
   *
   * @return bool false if failure
   */
  public function recordDeleteItemEvent($item_id)
  {
      $obj = &$this->create();
      $obj->set('event_type_id', ETID_DELETE_ITEM);
      $obj->set('exec_uid', $this->getExecUid());
      $obj->set('item_id', $item_id);

      return $this->insert($obj);
  }

  /**
   * record view item event (ETID_VIEW_ITEM: 7).
   *
   * @param int $item_id viewed item id
   *
   * @return bool false if failure
   */
  public function recordViewItemEvent($item_id)
  {
      $obj = &$this->create();
      $obj->set('event_type_id', ETID_VIEW_ITEM);
      $obj->set('exec_uid', $this->getExecUid());
      $obj->set('item_id', $item_id);
    // force insertion
    return $this->insert($obj, true);
  }

  /**
   * record view item event (ETID_DOWNLOAD_FILE: 8).
   *
   * @param int $item_id downloaded item id
   * @param int $file_id downloaded file id
   *
   * @return bool false if failure
   */
  public function recordDownloadFileEvent($item_id, $file_id)
  {
      $obj = &$this->create();
      $obj->set('event_type_id', ETID_DOWNLOAD_FILE);
      $obj->set('exec_uid', $this->getExecUid());
      $obj->set('item_id', $item_id);
      $obj->set('file_id', $file_id);
    // force insertion
    return $this->insert($obj, true);
  }

  /**
   * record request certify item event (ETID_REQUEST_CERTIFY_ITEM: 9).
   *
   * @param int $item_id  requested item id
   * @param int $index_id requested index id
   *
   * @return bool false if failure
   */
  public function recordRequestCertifyItemEvent($item_id, $index_id)
  {
      $obj = &$this->create();
      $obj->set('event_type_id', ETID_REQUEST_CERTIFY_ITEM);
      $obj->set('exec_uid', $this->getExecUid());
      $obj->set('item_id', $item_id);
      $obj->set('index_id', $index_id);

      return $this->insert($obj);
  }

  /**
   * record insert index event (ETID_INSERT_INDEX: 10).
   *
   * @param int $index_id inserted index id
   *
   * @return bool false if failure
   */
  public function recordInsertIndexEvent($index_id)
  {
      $obj = &$this->create();
      $obj->set('event_type_id', ETID_INSERT_INDEX);
      $obj->set('exec_uid', $this->getExecUid());
      $obj->set('index_id', $index_id);

      return $this->insert($obj);
  }

  /**
   * record update index event (ETID_UPDATE_INDEX: 11).
   *
   * @param int $index_id updated index id
   *
   * @return bool false if failure
   */
  public function recordUpdateIndexEvent($index_id)
  {
      $obj = &$this->create();
      $obj->set('event_type_id', ETID_UPDATE_INDEX);
      $obj->set('exec_uid', $this->getExecUid());
      $obj->set('index_id', $index_id);

      return $this->insert($obj);
  }

  /**
   * record delete index event (ETID_DELETE_INDEX: 12).
   *
   * @param int $index_id deleted index id
   *
   * @return bool false if failure
   */
  public function recordDeleteIndexEvent($index_id)
  {
      $obj = &$this->create();
      $obj->set('event_type_id', ETID_DELETE_INDEX);
      $obj->set('exec_uid', $this->getExecUid());
      $obj->set('index_id', $index_id);

      return $this->insert($obj);
  }

  /**
   * record certify item event (ETID_CERTIFY_ITEM: 13).
   *
   * @param int $item_id  certified item id
   * @param int $index_id certified index id
   *
   * @return bool false if failure
   */
  public function recordCertifyItemEvent($item_id, $index_id)
  {
      $obj = &$this->create();
      $obj->set('event_type_id', ETID_CERTIFY_ITEM);
      $obj->set('exec_uid', $this->getExecUid());
      $obj->set('item_id', $item_id);
      $obj->set('index_id', $index_id);

      return $this->insert($obj);
  }

  /**
   * record certify item event (ETID_REJECT_ITEM: 14).
   *
   * @param int $item_id  rejected item id
   * @param int $index_id rejected index id
   *
   * @return bool false if failure
   */
  public function recordRejectItemEvent($item_id, $index_id)
  {
      $obj = &$this->create();
      $obj->set('event_type_id', ETID_REJECT_ITEM);
      $obj->set('exec_uid', $this->getExecUid());
      $obj->set('item_id', $item_id);
      $obj->set('index_id', $index_id);

      return $this->insert($obj);
  }

  /**
   * record request insert account event (ETID_REQUEST_INSERT_ACCOUNT: 15).
   *
   * @param int $uid requested user id
   *
   * @return bool false if failure
   */
  public function recordRequestInsertAccountEvent($uid)
  {
      $obj = &$this->create();
      $obj->set('event_type_id', ETID_REQUEST_INSERT_ACCOUNT);
      $obj->set('uid', $uid);

      return $this->insert($obj);
  }

  /**
   * record certify account event (ETID_CERTIFY_ACCOUNT: 16).
   *
   * @param int $uid certified user id
   *
   * @return bool false if failure
   */
  public function recordCertifyAccountEvent($uid)
  {
      $obj = &$this->create();
      $obj->set('event_type_id', ETID_CERTIFY_ACCOUNT);
      $obj->set('exec_uid', $this->getExecUid());
      $obj->set('uid', $uid);

      return $this->insert($obj);
  }

  /**
   * record insert group event (ETID_INSERT_GROUP: 17).
   *
   * @param int $gid created group id
   *
   * @return bool false if failure
   */
  public function recordInsertGroupEvent($gid)
  {
      $obj = &$this->create();
      $obj->set('event_type_id', ETID_INSERT_GROUP);
      $obj->set('exec_uid', $this->getExecUid());
      $obj->set('gid', $gid);

      return $this->insert($obj);
  }

  /**
   * record update group event (ETID_UPDATE_GROUP: 18).
   *
   * @param int $gid updated group id
   *
   * @return bool false if failure
   */
  public function recordUpdateGroupEvent($gid)
  {
      $obj = &$this->create();
      $obj->set('event_type_id', ETID_UPDATE_GROUP);
      $obj->set('exec_uid', $this->getExecUid());
      $obj->set('gid', $gid);

      return $this->insert($obj);
  }

  /**
   * record delete group event (ETID_DELETE_GROUP: 19).
   *
   * @param int $gid deleted group id
   *
   * @return bool false if failure
   */
  public function recordDeleteGroupEvent($gid)
  {
      $obj = &$this->create();
      $obj->set('event_type_id', ETID_DELETE_GROUP);
      $obj->set('exec_uid', $this->getExecUid());
      $obj->set('gid', $gid);

      return $this->insert($obj);
  }

  /**
   * record insert group member event (ETID_INSERT_GROUP_MEMBER: 20).
   *
   * @param int $uid subscribed user id
   * @param int $gid subscribed group id
   *
   * @return bool false if failure
   */
  public function recordInsertGroupMemberEvent($uid, $gid)
  {
      $obj = &$this->create();
      $obj->set('event_type_id', ETID_INSERT_GROUP_MEMBER);
      $obj->set('exec_uid', $this->getExecUid());
      $obj->set('uid', $uid);
      $obj->set('gid', $gid);

      return $this->insert($obj);
  }

  /**
   * record delete group member event (ETID_DELETE_GROUP_MEMBER: 21).
   *
   * @param int $uid unsubscribed user id
   * @param int $gid unsubscribed group id
   *
   * @return bool false if failure
   */
  public function recordDeleteGroupMemberEvent($uid, $gid)
  {
      $obj = &$this->create();
      $obj->set('event_type_id', ETID_DELETE_GROUP_MEMBER);
      $obj->set('exec_uid', $this->getExecUid());
      $obj->set('uid', $uid);
      $obj->set('gid', $gid);

      return $this->insert($obj);
  }

  /**
   * record view top page event (ETID_VIEW_TOP_PAGE: 22).
   *
   * @return bool false if failure
   */
  public function recordViewTopPageEvent()
  {
      // get start page script name
    global $xoopsRequestUri;
      $myxoopsConfig = &xoonips_get_xoops_configs(XOOPS_CONF);
      $startpage_url = XOOPS_URL.'/index.php';
      if (isset($myxoopsConfig['startpage']) && $myxoopsConfig['startpage'] != '' && $myxoopsConfig['startpage'] != '--') {
          $module_handler = &xoops_gethandler('module');
          $startpage_module = &$module_handler->get($myxoopsConfig['startpage']);
          $startpage_dirname = $startpage_module->dirname();
          $startpage_url = XOOPS_URL.'/modules/'.$startpage_dirname.'/index.php';
      }
      $startpage_script = '';
      if (preg_match('/^(\\S+):\\/\\/([^\\/]+)((\\/[^\\/]+)*\\/index.php)$/', $startpage_url, $matches)) {
          $startpage_script = $matches[3];
      }
    // get current script name
    $current_script = (isset($_SERVER['SCRIPT_NAME'])) ? $_SERVER['SCRIPT_NAME'] : '';
    // compare start page script name with current script name
    if ($startpage_script != $current_script) {
        // current url is not top page
      return false;
    }

    // record event
    $obj = &$this->create();
      $obj->set('event_type_id', ETID_VIEW_TOP_PAGE);
      $obj->set('exec_uid', $this->getExecUid());
    // force insertion
    return $this->insert($obj, true);
  }

  /**
   * record xoonips search event (ETID_QUICK_SEARCH: 23).
   *
   * @param string $search_itemtype 'all' or itemtype name
   * @param string $keyword         searched keyword
   * @param int    $repository_url  repository url to search
   *
   * @return bool false if failure
   */
  public function recordQuickSearchEvent($search_itemtype, $keyword, $repository_url = '')
  {
      $search_keyword = 'search_itemtype='.urlencode($search_itemtype).'&keyword='.urlencode($keyword).(empty($repository_url) ? '' : '&repository_url='.urlencode($repository_url));
      $obj = &$this->create();
      $obj->set('event_type_id', ETID_QUICK_SEARCH);
      $obj->set('exec_uid', $this->getExecUid());
      $obj->set('search_keyword', $search_keyword);
    // force insertion
    $result = $this->insert($obj, true);

      return $result;
  }

  /**
   * record advanced search event (ETID_ADVANCED_SEARCH: 24).
   *
   * @param array $keywords searched keywords
   *
   * @return bool false if failure
   */
  public function recordAdvancedSearchEvent($keywords)
  {
      $obj = &$this->create();
      $obj->set('event_type_id', ETID_ADVANCED_SEARCH);
      $obj->set('exec_uid', $this->getExecUid());
      $obj->set('search_keyword', implode('&', $keywords));
    // force insertion
    return $this->insert($obj, true);
  }

  /**
   * record start su event (ETID_START_SU: 25).
   *
   * @param int $original_uid original user id
   * @param int $target_uid   switched user id
   *
   * @return bool false if failure
   */
  public function recordStartSuEvent($original_uid, $target_uid)
  {
      $obj = &$this->create();
      $obj->set('event_type_id', ETID_START_SU);
      $obj->set('exec_uid', $original_uid);
      $obj->set('uid', $target_uid);

      return $this->insert($obj);
  }

  /**
   * record end su event (ETID_END_SU: 26).
   *
   * @param int $original_uid original user id
   * @param int $target_uid   switched user id
   * @param int $timestamp    end su timestamp for session GC
   *
   * @return bool false if failure
   */
  public function recordEndSuEvent($original_uid, $target_uid, $timestamp = null)
  {
      $obj = &$this->create();
      $obj->set('event_type_id', ETID_END_SU);
      $obj->set('exec_uid', $original_uid);
      $obj->set('uid', $target_uid);
      if (!is_null($timestamp)) {
          // override timestamp for session GC
      $obj->set('timestamp', $timestamp);
      }
    // force insertion
    return $this->insert($obj, true);
  }

  /**
   * record request transfer item event (ETID_REQUEST_TRANSFER_ITEM: 27).
   *
   * @param int $item_id requested item id
   * @param int $uid     requested to user id
   *
   * @return bool false if failure
   */
  public function recordRequestTransferItemEvent($item_id, $to_uid)
  {
      $obj = &$this->create();
      $obj->set('event_type_id', ETID_REQUEST_TRANSFER_ITEM);
      $obj->set('exec_uid', $this->getExecUid());
      $obj->set('item_id', $item_id);
      $obj->set('uid', $to_uid);

      return $this->insert($obj);
  }

  /**
   * record transfer item event (ETID_TRANSFER_ITEM: 28).
   *
   * @param int $item_id  transferred item id
   * @param int $index_id transferred index id
   * @param int $to_uid   transferred to user id
   *
   * @return bool false if failure
   */
  public function recordTransferItemEvent($item_id, $index_id, $to_uid)
  {
      $obj = &$this->create();
      $obj->set('event_type_id', ETID_TRANSFER_ITEM);
      $obj->set('exec_uid', $this->getExecUid());
      $obj->set('item_id', $item_id);
      $obj->set('index_id', $index_id);
      $obj->set('uid', $to_uid);

      return $this->insert($obj);
  }

  /**
   * record reject transfer item event (ETID_REJECT_TRANSFER_ITEM: 29).
   *
   * @param int $item_id rejected item id
   *
   * @return bool false if failure
   */
  public function recordRejectTransferItemEvent($item_id)
  {
      $obj = &$this->create();
      $obj->set('event_type_id', ETID_REJECT_TRANSFER_ITEM);
      $obj->set('exec_uid', $this->getExecUid());
      $obj->set('item_id', $item_id);

      return $this->insert($obj);
  }

  /**
   * record certify group index event (ETID_CERTIFY_GROUP_INDEX: 30).
   *
   * @param int $group_index_id certified group index id
   *
   * @return bool false if failure
   */
  public function recordCertifyGroupIndexEvent($group_index_id)
  {
      $obj = &$this->create();
      $obj->set('event_type_id', ETID_CERTIFY_GROUP_INDEX);
      $obj->set('exec_uid', $this->getExecUid());
      $obj->set('index_id', intval($group_index_id));

      return $this->insert($obj);
  }

  /**
   * record reject group index event (ETID_REJECT_GROUP_INDEX: 31).
   *
   * @param int $group_index_id rejected group index id
   *
   * @return bool false if failure
   */
  public function recordRejectGroupIndexEvent($group_index_id)
  {
      $obj = &$this->create();
      $obj->set('event_type_id', ETID_REJECT_GROUP_INDEX);
      $obj->set('exec_uid', $this->getExecUid());
      $obj->set('index_id', intval($group_index_id));

      return $this->insert($obj);
  }

  /**
   * record group index to public event (ETID_GROUP_INDEX_TO_PUBLIC: 32).
   *
   * @param int $pubilc_index_id public index id that add group index to
   * @param int $group_index_id  group index id that is added to public index
   * @param int $gid
   *
   * @return bool false if failure
   */
  public function recordGroupIndexToPublicEvent($public_index_id, $group_index_id, $gid)
  {
      $obj = &$this->create();
      $obj->set('event_type_id', ETID_GROUP_INDEX_TO_PUBLIC);
      $obj->set('exec_uid', $this->getExecUid());
      $obj->set('index_id', intval($public_index_id));
      $obj->set('gid', intval($gid));
      $obj->set('additional_info', intval($group_index_id));

      return $this->insert($obj);
  }

  /**
   * record delete account event (ETID_DELETE_ACCOUNT: 33).
   *
   * @param int $uid user id to be deleted
   *
   * @return bool false if failure
   */
  public function recordDeleteAccountEvent($uid)
  {
      $obj = &$this->create();
      $obj->set('event_type_id', ETID_DELETE_ACCOUNT);
      $obj->set('exec_uid', $this->getExecUid());
      $obj->set('uid', intval($uid));

      return $this->insert($obj);
  }

  /**
   * record reject transfer item event (ETID_UNCERTIFY_ACCOUNT: 34).
   *
   * @param int    $uid      uncertified user id
   * @param string $comments reviewers' comments
   *
   * @return bool false if failure
   */
  public function recordUncertifyAccountEvent($uid, $comments)
  {
      $obj = &$this->create();
      $obj->set('event_type_id', ETID_UNCERTIFY_ACCOUNT);
      $obj->set('exec_uid', $this->getExecUid());
      $obj->set('uid', intval($uid));
      $obj->set('additional_info', $comments);

      return $this->insert($obj);
  }
}
