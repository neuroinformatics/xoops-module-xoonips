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
defined('XOOPS_ROOT_PATH') || exit('XOOPS root path not defined');

/**
 * class of XooNIps Session.
 *
 * @li getVar( 'sess_id' ) : session ID
 * @li getVar( 'updated' ) : last update time. time_t
 * @li getVar( 'uid' ) : {@link XooNIpsUser} ID
 * @li getVar( 'su_uid' ) : target uid in su mode. otherwise null.
 */
class XooNIpsOrmSession extends XooNIpsTableObject
{
    public function __construct()
    {
        parent::__construct();
        $this->initVar('sess_id', XOBJ_DTYPE_TXTBOX, null, true, 32);
        $this->initVar('updated', XOBJ_DTYPE_INT, null, true, null);
        $this->initVar('uid', XOBJ_DTYPE_INT, null, true, null);
        $this->initVar('su_uid', XOBJ_DTYPE_INT, null, false, null);
        if (xoonips_get_version() >= 340) {
            $this->initVar('sess_data', XOBJ_DTYPE_TXTBOX, '', false, null);
        }
    }
}

/**
 * XooNIps Session Handler class.
 */
class XooNIpsOrmSessionHandler extends XooNIpsTableObjectHandler
{
    public function __construct(&$db)
    {
        parent::__construct($db);
        $this->__initHandler('XooNIpsOrmSession', 'xoonips_session', 'sess_id', false, true);
    }

    /**
     * get object.
     *
     * @param string $id session id
     *
     * @return XooNIpsOrmSession object
     */
    public function &get($id)
    {
        $objects = &$this->getObjects(new Criteria('sess_id', $id));
        if ($objects === false || count($objects) != 1) {
            $ret = false;

            return $ret;
        }

        return $objects[0];
    }

    /**
     * get objects.
     *
     * @param object              $criteria
     * @param bool                $id_as_key
     * @param string              $fieldlist fieldlist for distinct select
     * @param bool                $distinct
     * @param XooNIpsJoinCriteria $joindef   join criteria object
     *
     * @return array objects
     */
    public function &getObjects($criteria = null, $id_as_key = false, $fieldlist = '', $distinct = false, $joindef = null)
    {
        if ($fieldlist == '') {
            $fieldlist = '*';
        }
        $fieldlist .= ', unix_timestamp('.$this->db->prefix($this->__table_name).'.updated) as updated ';

        return parent::getObjects($criteria, $id_as_key, $fieldlist, $distinct, $joindef);
    }

    /**
     * helper function for sql string creation. change last_updated to updated.
     *
     * @param object $obj
     * @param array  $vars array of variables
     *
     * @return array quoted strings
     */
    public function &_makeVarsArray4SQL(&$obj, &$vars)
    {
        $ret = parent::_makeVarsArray4SQL($obj, $vars);
        $ret['updated'] = 'FROM_UNIXTIME('.$ret['updated'].')';

        return $ret;
    }

    /**
     * init xoonips session.
     *
     * @return bool false if failure
     */
    public function initSession($uid)
    {
        // TODO: remove this. this is backward compatibility for AL.php
        $_SESSION['XNPSID'] = SID_GUEST;

        if ($uid == UID_GUEST) {
            // guest user
            return false;
        }
        $sess_id = session_id();
        $now = time();

        // validate xoonips user
        if (!$this->validateUser($uid, false)) {
            return false;
        }

        $session = &$this->get($sess_id);
        if (is_object($session)) {
            $session->set('updated', $now);
        } else {
            $session = &$this->create();
            $session->set('sess_id', $sess_id);
            $session->set('uid', $uid);
            $session->set('updated', $now);
        }
        // TODO: remove this. this is backward compatibility for AL.php
        $_SESSION['XNPSID'] = $sess_id;
        // force insertion
        return $this->insert($session, true);
    }

    /**
     * validate xoonips user
     * if user is not xoonips user or not certified user then logout now.
     *
     * @param int  $uid       user id
     * @param bool $do_logout logout if invalid xoonips user
     *
     * @return bool validation result
     */
    public function validateUser($uid, $do_logout)
    {
        $myxoopsConfig = &xoonips_get_xoops_configs(XOOPS_CONF);
        if ($uid == UID_GUEST) {
            // guest is ok
            return true;
        }
        $xusers_handler = &xoonips_getormhandler('xoonips', 'users');
        $xusers_obj = &$xusers_handler->get($uid);
        $ret = true;
        $message = '';
        if (!is_object($xusers_obj)) {
            // not xoonips user
            $ret = false;
            $message = _MD_XOONIPS_ITEM_FORBIDDEN;
        } else {
            $is_certified = $xusers_obj->get('activate');
            if ($is_certified == 0) {
                // not certified xoonips user
                $ret = false;
                $message = _MD_XOONIPS_ACCOUNT_NOT_ACTIVATED;
            }
        }
        if (!$ret) {
            if ($do_logout) {
                $_SESSION = array();
                session_destroy();
                if ($myxoopsConfig['use_mysession'] && $myxoopsConfig['session_name'] != '') {
                    setcookie($myxoopsConfig['session_name'], '', time() - 3600, '/', '', 0);
                }
                // clear entry from online users table
                $online_handler = &xoops_gethandler('online');
                $online_handler->destroy($uid);

                // redirect to top page
                redirect_header(XOOPS_URL.'/', 3, $message);
                exit();
            }
        }

        return $ret;
    }

    /**
     * GC expired sessions.
     */
    public function gcSession()
    {
        $myxoopsConfig = &xoonips_get_xoops_configs(XOOPS_CONF);
        $session_expire = $myxoopsConfig['session_expire'];
        $expire_time = time() + (60 * $session_expire);
        $eventlog_handler = &xoonips_getormhandler('xoonips', 'event_log');

        $join = new XooNIpsJoinCriteria('session', 'sess_id', 'sess_id', 'LEFT', 'ts');
        $criteria = new Criteria('ISNULL(ts.sess_id)', 1);
        // set fieldlist for same field name 'sess_id'
        $fieldlist = $GLOBALS['xoopsDB']->prefix('xoonips_session').'.*';
        $sessions = &$this->getObjects($criteria, false, $fieldlist, false, $join);
        foreach ($sessions as $session) {
            $su_uid = $session->get('su_uid');
            $uid = $session->get('uid');
            // terminate switch user mode
            if ($su_uid) {
                if (!$eventlog_handler->recordEndSuEvent($uid, $su_uid, $expire_time)) {
                    return false;
                }
            }
            // insert logout event
            if (!$eventlog_handler->recordLogoutEvent($uid, $expire_time)) {
                return false;
            }
            // remove xoonips session (force deletion)
            if (!$this->delete($session, true)) {
                return false;
            }
        }

        return true;
    }
}
