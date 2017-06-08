<?php

if (!defined('XOOPS_ROOT_PATH')) {
    die();
}

class Xoonips_UserPreload extends XCube_ActionFilter
{
    public function preBlockFilter()
    {
        // Delete conflicted Legacy Delagetes
        $this->mRoot->mDelegateManager->delete('Legacypage.Userinfo.Access', 'User_LegacypageFunctions::userinfo');
        $this->mRoot->mDelegateManager->delete('Legacypage.Edituser.Access', 'User_LegacypageFunctions::edituser');
        $this->mRoot->mDelegateManager->delete('Legacypage.Register.Access', 'User_LegacypageFunctions::register');
        $this->mRoot->mDelegateManager->delete('Site.CheckLogin', 'User_LegacypageFunctions::checkLogin');
        $this->mRoot->mDelegateManager->delete('Site.CheckLogin.Success', 'User_LegacypageFunctions::checkLoginSuccess');
        $this->mRoot->mDelegateManager->delete('Site.Logout', 'User_LegacypageFunctions::logout');

        // Add XooNIps specific Delegetes
        $this->mRoot->mDelegateManager->add('Legacypage.Userinfo.Access', 'XooNIps_UserPreloadFunctions::userinfo');
        $this->mRoot->mDelegateManager->add('Legacypage.Edituser.Access', 'XooNIps_UserPreloadFunctions::edituser');
        $this->mRoot->mDelegateManager->add('Legacypage.Register.Access', 'XooNIps_UserPreloadFunctions::register');
        $this->mRoot->mDelegateManager->add('Site.CheckLogin', 'XooNIps_UserPreloadFunctions::checkLogin');
        $this->mRoot->mDelegateManager->add('Site.CheckLogin.Success', 'XooNIps_UserPreloadFunctions::checkLoginSuccess');
        $this->mRoot->mDelegateManager->add('Site.CheckLogin.Fail', 'XooNIps_UserPreloadFunctions::checkLoginFail');
        $this->mRoot->mDelegateManager->add('Site.Logout', 'XooNIps_UserPreloadFunctions::logout');
        $this->mRoot->mDelegateManager->add('Site.Logout.Success', 'XooNIps_UserPreloadFunctions::logoutSuccess');
    }
}

class XooNIps_UserPreloadFunctions
{
    /***********************/
    /**
     * Public Functions.
     **/
    /***********************/

    /**
     * custom 'Legacypage.Userinfo.Access' Delegate for XooNIps.
     *
     * Note: this Delegate is conflicted with 'User_LegacypageFunctions::userinfo'
     */
    public static function userinfo()
    {
        $script = 'userinfo.php';
        $uid = xoops_getrequest('uid');
        if (!empty($uid)) {
            $script .= '?uid='.$uid;
        }
        self::_doRedirect($script);
    }

    /**
     * custom 'Legacypage.Edituser.Access' Delegate for XooNIps.
     *
     * Note: this Delegate is conflicted with 'User_LegacypageFunctions::edituser'
     */
    public static function edituser()
    {
        $script = 'edituser.php';
        if (!empty($uid)) {
            $script .= '?uid='.$uid;
        }
        self::_doRedirect($script);
    }

    /**
     * custom 'Legacypage.Register.Access' Delegate for XooNIps.
     *
     * Note: this Delegate is conflicted with 'User_LegacypageFunctions::register'
     */
    public static function register()
    {
        $script = 'registeruser.php';
        self::_doRedirect($script);
    }

    /**
     * custom 'Site.CheckLogin' Delegate for XooNIps.
     *
     * Note: this Delegate is conflicted with
     *       'User_LegacypageFunctions::checkLogin'
     *
     * @param object &$xoopsUser target user
     */
    public static function checkLogin(&$xoopsUser)
    {
        // call original function 'User_LegacypageFunctions::checkLogin'
        require_once XOOPS_ROOT_PATH.'/modules/user/kernel/LegacypageFunctions.class.php';
        User_LegacypageFunctions::checkLogin($xoopsUser);
        if (is_object($xoopsUser)) {
            // check XooNIps session
            self::_loadXooNIps();
            $uid = $xoopsUser->get('uid');
            $xsession_handler = &xoonips_getormhandler('xoonips', 'session');
            $xsession_handler->initSession($uid);
            if (!$xsession_handler->validateUser($uid, false)) {
                // delete xoopsUser if validation falure
                unset($xoopsUser);
                // clear session
                $root = &XCube_Root::getSingleton();
                $root->mSession->regenerate();
                $_SESSION = array();
            }
        }
    }

    /**
     * custom 'Site.CheckLogin.Success' Delegate for XooNIps.
     *
     * Note: this Delegate is conflicted with
     *       'User_LegacypageFunctions::checkLoginSuccess'
     *
     * @param object &$xoopsUser target user
     */
    public static function checkLoginSuccess(&$xoopsUser)
    {
        // call original function 'User_LegacypageFunctions::checkLoginSuccess'
        require_once XOOPS_ROOT_PATH.'/modules/user/kernel/LegacypageFunctions.class.php';
        User_LegacypageFunctions::checkLoginSuccess($xoopsUser);
        if (is_object($xoopsUser)) {
            self::_loadXooNIps();
            $uid = $xoopsUser->get('uid');
            // record login success event log
            $eventlog_handler = &xoonips_getormhandler('xoonips', 'event_log');
            $eventlog_handler->recordLoginSuccessEvent($uid);
        }
    }

    /**
     * custom 'Site.CheckLogin.Fail' Delegate for XooNIps.
     *
     * @param object &$xoopsUser target user
     */
    public static function checkLoginFail(&$xoopsUser)
    {
        self::_loadXooNIps();
        // record login failure event log
        $uname = xoops_getrequest('uname');
        $eventlog_handler = &xoonips_getormhandler('xoonips', 'event_log');
        $eventlog_handler->recordLoginFailureEvent($uname);
    }

    /**
     * custom 'Site.Logout' Delegate for XooNIps.
     *
     * Note: this Delegate is conflicted with 'User_LegacypageFunctions::logout'
     *
     * @param bool   &$successFlag result, false if logout failure
     * @param object $xoopsUser    target user
     */
    public static function logout(&$successFlag, $xoopsUser)
    {
        // redirect to terminate su page if now in su mode
        if (isset($_SESSION['xoonips_old_uid'])) {
            $script = 'su.php?op=end';
            self::_doRedirect($script);
        }
        // call original function 'User_LegacypageFunctions::logout'
        require_once XOOPS_ROOT_PATH.'/modules/user/kernel/LegacypageFunctions.class.php';
        User_LegacypageFunctions::logout($successFlag, $xoopsUser);
    }

    /**
     * custom 'Site.Logout.Success' Delegate for XooNIps.
     *
     * @param object $xoopsUser target user
     */
    public static function logoutSuccess($xoopsUser)
    {
        self::_loadXooNIps();
        // record logout event log
        $uid = $xoopsUser->get('uid');
        $eventlog_handler = &xoonips_getormhandler('xoonips', 'event_log');
        $eventlog_handler->recordLogoutEvent($uid);
    }

    /***********************/
    /**
     * Private Functions.
     **/
    /***********************/

    /**
     * redirect to xoonips script url.
     *
     * @param string $script
     */
    private static function _doRedirect($script)
    {
        $mydirname = basename(dirname(__DIR__));
        $url = sprintf('%s/modules/%s/%s', XOOPS_URL, $mydirname, $script);
        $root = &XCube_Root::getSingleton();
        $root->mController->executeForward($url);
    }

    /**
     * load xoonips features.
     */
    private static function _loadXooNIps()
    {
        $mydirname = basename(dirname(__DIR__));
        require_once XOOPS_ROOT_PATH.'/modules/'.$mydirname.'/condefs.php';
        require_once XOOPS_ROOT_PATH.'/modules/'.$mydirname.'/include/functions.php';
    }
}
