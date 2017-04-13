<?php

// $Revision: 1.1.2.8 $
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

require_once dirname(__DIR__).'/base/view.class.php';

/**
 * base class of transfer view.
 */
class XooNIpsViewTransfer extends XooNIpsView
{
    /**
     * create view.
     */
    public function __construct($params)
    {
        parent::__construct($params);
    }

    /**
     * add link element to smarty template
     *  to $xoopsTpl to include style.css.
     */
    public function setXooNIpsStyleSheet(&$xoopsTpl)
    {
        if (!is_object($xoopsTpl)) {
            return;
        }

        $header = '<link rel="stylesheet" type="text/css" href="style.css" />';
        $header .= $xoopsTpl->get_template_vars('xoops_module_header');
        $xoopsTpl->assign('xoops_module_header', $header);
    }

    /**
     * get concatenated title string.
     *
     * @param array  $titles    array of XooNIpsTitle
     * @param string $delimiter delimieter string of each titles
     *
     * @return string
     */
    public function concatenate_titles($titles, $delimiter = '/')
    {
        $result = array();
        foreach ($titles as $t) {
            $result[] = $t->getVar('title', 's');
        }

        return implode($delimiter, $result);
    }

    /**
     * get uname by uid.
     *
     * @param int $uid
     *
     * @return user's uname(login name) or empty string(if illegal uid)
     */
    public function get_uname_by_uid($uid)
    {
        $handler = &xoops_gethandler('user');
        $user = &$handler->get($uid);
        if (false === $user) {
            return '';
        }

        return $user->getVar('uname');
    }

    /**
     * get index path string.
     *
     * @param int $index_id
     *
     * @return index path string like '/user/foo/bar'
     */
    public function get_index_path_by_index_id($index_id)
    {
        $user_handler = &xoonips_getormhandler('xoonips', 'users');

        $index_handler = &xoonips_getormhandler('xoonips', 'index');
        $index = $index_handler->get($index_id);
        if ($index == false) {
            return '';
        }
        $user = &$user_handler->get($index->get('uid'));

        $handler = &xoonips_getormcompohandler('xoonips', 'index');
        $index_names = $handler->getIndexPathNames($index_id, $user->get('private_index_id'), 's');

        return '/'.implode('/', $index_names);
    }
}
