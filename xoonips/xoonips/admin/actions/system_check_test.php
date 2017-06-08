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

// status
define('_XASC_STATUS_OK', 0);
define('_XASC_STATUS_NOTICE', 1);
define('_XASC_STATUS_FAIL', 2);
// error type
define('_XASC_ERRORTYPE_NONE', 0);
define('_XASC_ERRORTYPE_PHP', 1);
define('_XASC_ERRORTYPE_MYSQL', 2);
define('_XASC_ERRORTYPE_COMMAND', 4);
define('_XASC_ERRORTYPE_XOONIPS', 8);

class XooNIpsAdminSystemCheckResult
{
    public $name;
    public $status;
    public $label;
    public $result;
    public $messages = array();

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function setResult($status, $label, $result)
    {
        $this->status = $status;
        $this->label = $label;
        $this->result = $result;
    }

    public function setMessage($message)
    {
        $this->messages[] = $message;
    }

    public function render()
    {
        $textutil = &xoonips_getutility('text');

        $arrow = '<span style="font-weight: bold;">&raquo;</span>';
        $stat = array(
            _XASC_STATUS_OK => array(
                'image' => 'icon_ok.png',
                'color' => 'black',
            ),
            _XASC_STATUS_NOTICE => array(
                'image' => 'icon_notice.png',
                'color' => 'red',
             ),
            _XASC_STATUS_FAIL => array(
                'image' => 'icon_error.png',
                'color' => 'red',
            ),
        );
        // status
        $html = '<img src="../images/'.$stat[$this->status]['image'].'" alt=""/>';
        $html .= '&nbsp;';
        // name
        $html .= $textutil->html_special_chars($this->name).' : ';
        // label
        if ($this->label != '') {
            $html .= $textutil->html_special_chars($this->label).' : ';
        }
        // result
        $html .= '<span style="font-weight: bold; color: '.$stat[$this->status]['color'].';">'.$textutil->html_special_chars($this->result).'</span>';
        // message
        foreach ($this->messages as $message) {
            $html .= '<br />&nbsp;&nbsp;'.$arrow.'&nbsp;&nbsp;';
            $html .= $textutil->html_special_chars($message);
        }

        return $html;
    }
}
class XooNIpsAdminSystemCheckCategory
{
    public $name;
    public $results = array();
    public $errortype = _XASC_ERRORTYPE_NONE;
    public $errorlevel = _XASC_STATUS_OK;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function registerResult(&$result)
    {
        $this->results[] = $result;
    }

    public function setError($type, $level)
    {
        $this->errortype |= $type;
        $this->errorlevel = max($this->errorlevel, $level);
    }

    public function renderResults()
    {
        $html = '';
        foreach ($this->results as $result) {
            if (!empty($html)) {
                $html .= '<br />';
            }
            $html .= $result->render();
        }

        return $html;
    }
}
class XooNIpsAdminSystemCheck
{
    public $categories = array();

    public function __construct()
    {
    }

    public function registerCategory(&$category)
    {
        $this->categories[] = $category;
    }

    public function renderTotalResult($errortype, $errorlevel)
    {
        $textutil = &xoonips_getutility('text');
        $arrow = '<span style="font-weight: bold;">&raquo;</span>';
        $stat = array(
            _XASC_STATUS_OK => array(
                'image' => 'icon_ok.png',
                'color' => 'black',
                'label' => _AM_XOONIPS_SYSTEM_CHECK_LABEL_OK,
            ),
            _XASC_STATUS_NOTICE => array(
                'image' => 'icon_notice.png',
                'color' => 'black',
                'label' => _AM_XOONIPS_SYSTEM_CHECK_LABEL_NOTICE,
            ),
            _XASC_STATUS_FAIL => array(
                'image' => 'icon_error.png',
                'color' => 'red',
                'label' => _AM_XOONIPS_SYSTEM_CHECK_LABEL_FAIL,
            ),
        );
        $messages = array(
            _XASC_ERRORTYPE_PHP => _AM_XOONIPS_SYSTEM_CHECK_MSG_PHP,
            _XASC_ERRORTYPE_MYSQL => _AM_XOONIPS_SYSTEM_CHECK_MSG_MYSQL,
            _XASC_ERRORTYPE_COMMAND => _AM_XOONIPS_SYSTEM_CHECK_MSG_COMMAND,
            _XASC_ERRORTYPE_XOONIPS => _AM_XOONIPS_SYSTEM_CHECK_MSG_XOONIPS,
        );
        // status
        $html = '<img src="../images/'.$stat[$errorlevel]['image'].'" alt=""/>';
        $html .= '&nbsp;';
        // name
        $html .= '<span style="font-weight: bold; color: '.$stat[$errorlevel]['color'].';">'.$textutil->html_special_chars($stat[$errorlevel]['label']).'</span>';
        // message
        foreach (array(1, 2, 4, 8) as $type) {
            if ($errortype & $type) {
                $html .= '<br />&nbsp;&nbsp;'.$arrow.'&nbsp;&nbsp;';
                $html .= $textutil->html_special_chars($messages[$type]);
            }
        }

        return $html;
    }

    public function getResults()
    {
        $ret = array();
        $errortype = _XASC_ERRORTYPE_NONE;
        $errorlevel = _XASC_STATUS_OK;
        foreach ($this->categories as $category) {
            $ret[] = array(
                'name' => $category->name,
                'result' => $category->renderResults(),
            );
            $errortype |= $category->errortype;
            $errorlevel = max($category->errorlevel, $errorlevel);
        }
        // total result
        $ret[] = array(
            'name' => _AM_XOONIPS_SYSTEM_CHECK_LABEL_RESULTS,
            'result' => $this->renderTotalResult($errortype, $errorlevel),
        );

        return $ret;
    }
}

$check_categories = array(
    'phpini' => _AM_XOONIPS_SYSTEM_CHECK_CATEGORY_PHPINI,
    'phpext' => _AM_XOONIPS_SYSTEM_CHECK_CATEGORY_PHPEXT,
    'mysql' => _AM_XOONIPS_SYSTEM_CHECK_CATEGORY_MYSQL,
    'command' => _AM_XOONIPS_SYSTEM_CHECK_CATEGORY_COMMAND,
    'xoonips' => _AM_XOONIPS_SYSTEM_CHECK_CATEGORY_XOONIPS,
);

$check_results_obj = new XooNIpsAdminSystemCheck();
foreach ($check_categories as $category => $label) {
    $category_obj = new XooNIpsAdminSystemCheckCategory($label);
    require 'actions/system_check_test_'.$category.'.php';
    $funcname = 'xoonips_admin_system_check_'.$category;
    $funcname($category_obj);
    $check_results_obj->registerCategory($category_obj);
    unset($category_obj);
}

$results = $check_results_obj->getResults();

// title
$title = _AM_XOONIPS_SYSTEM_CHECK_TITLE;
$description = _AM_XOONIPS_SYSTEM_CHECK_DESC;

// breadcrumbs
$breadcrumbs = array(
    array(
        'type' => 'top',
        'label' => _AM_XOONIPS_TITLE,
        'url' => $xoonips_admin['admin_url'].'/',
    ),
    array(
        'type' => 'link',
        'label' => _AM_XOONIPS_SYSTEM_TITLE,
        'url' => $xoonips_admin['myfile_url'],
    ),
    array(
        'type' => 'label',
        'label' => $title,
        'url' => '',
    ),
);

// token ticket
require_once '../class/base/gtickets.php';
$ticket_area = 'xoonips_admin_system_check';
$token_ticket = $xoopsGTicket->getTicketHtml(__LINE__, 1800, $ticket_area);

// templates
require_once '../class/base/pattemplate.class.php';
$tmpl = new PatTemplate();
$tmpl->setBaseDir('templates');
$tmpl->readTemplatesFromFile('system_check.tmpl.html');

// assign template variables
$tmpl->addVar('header', 'TITLE', $title);
$tmpl->addVar('main', 'TITLE', $title);
$tmpl->setAttribute('description', 'visibility', 'visible');
$tmpl->addVar('description', 'DESCRIPTION', $description);
$tmpl->setAttribute('breadcrumbs', 'visibility', 'visible');
$tmpl->addRows('breadcrumbs_items', $breadcrumbs);
$tmpl->addVar('main', 'token_ticket', $token_ticket);
$tmpl->setAttribute('first', 'visibility', 'hidden');
$tmpl->setAttribute('results_table', 'visibility', 'visible');
$tmpl->addVar('results_table', 'title', _AM_XOONIPS_SYSTEM_CHECK_FORM_TITLE);
$tmpl->addVar('results_table', 'recheck', _AM_XOONIPS_SYSTEM_CHECK_LABEL_RECHECK);
$tmpl->addRows('results', $results);

// display
xoops_cp_header();
$tmpl->displayParsedTemplate('main');
xoops_cp_footer();
