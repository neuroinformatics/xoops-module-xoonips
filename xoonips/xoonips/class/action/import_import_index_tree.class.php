<?php

// $Revision: 1.1.2.14 $
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

include_once dirname(__DIR__).'/base/action.class.php';
include_once dirname(__DIR__).'/base/logicfactory.class.php';
require_once dirname(__DIR__).'/base/gtickets.php';
include_once dirname(dirname(__DIR__))
    .'/include/imexport.php';

class XooNIpsActionImportImportIndexTree extends XooNIpsAction
{
    public $_view_name = null;
    public $_collection = null;

    public function XooNIpsActionImportImportIndexTree()
    {
        parent::XooNIpsAction();
    }

    public function _get_logic_name()
    {
        return null;
    }

    public function _get_view_name()
    {
        return null;
    }

    public function preAction()
    {
        xoonips_deny_guest_access();
        xoonips_allow_post_method();
    }

    public function doAction()
    {
        include_once dirname(dirname(__DIR__))
            .'/include/imexport.php';
        global $xoopsDB, $xoopsConfig, $xoopsUser,$xoopsLogger, $xoopsUserIsAdmin;

        if (!isset($_SESSION['xoonips_import_file_path'])
            || !isset($_SESSION['xoonips_import_index_ids'])) {
            header('Location: '.XOOPS_URL
                    .'/modules/xoonips/import.php?action=default');
        }

        $uploadfile = $_SESSION['xoonips_import_file_path'];
        $import_index_ids
            = unserialize($_SESSION['xoonips_import_index_ids']);

        unset($_SESSION['xoonips_import_file_path']);
        unset($_SESSION['xoonips_import_index_ids']);

        $unzip = &xoonips_getutility('unzip');
        if (!$unzip->open($uploadfile)) {
            redirect_header('import.php?action=default', 3, _MD_XOONIPS_IMPORT_FILE_NOT_FOUND);
            exit();
        }

        include XOOPS_ROOT_PATH.'/header.php';

        //
        // start transaction
        //
        $xoopsDB->query('START TRANSACTION');

        $error = false; //true if error
        $fnames = $unzip->get_file_list();
        $created_xids = array();
        foreach ($fnames as $fname) {
            //
            // import index tree and exit.
            //
            $xml = $unzip->get_data($fname);
            foreach ($import_index_ids as $index_id) {
                $id_table = array();
                if (xnpImportIndex($xml, $index_id, $id_table, $error_message)) {
                    $created_xids = array_merge($created_xids, $id_table);
                } else {
                    echo "ERROR $error_message";
                    $error = true;
                }
            }
        }
        $unzip->close();
        unlink($uploadfile);

        if ($error) {
            //
            // rollback
            //
            $xoopsDB->query('ROLLBACK');

            echo '<p>'._MD_XOONIPS_IMPORT_INDEX_TREE_FAILED.'</p>';
        } else {
            //
            // commit
            //
            $xoopsDB->query('COMMIT');

            echo _MD_XOONIPS_IMPORT_INDEX_TREE_CREATED;

            $index_handler = &xoonips_getormhandler('xoonips', 'index');
            echo "<p>\n";
            foreach ($created_xids as $index_id) {
                echo "<a href='".XOOPS_URL
                    .'/modules/xoonips/listitem.php?index_id='
                    .$index_id."'>"
                    .htmlspecialchars($this->_get_index_path_str($index_id, $xoopsUser->getVar('uid')), ENT_QUOTES)
                    ."</a><br />\n";
            }
            echo "</p>\n";
        }
        include XOOPS_ROOT_PATH.'/footer.php';
    }

    public function _get_index_path_str($index_id, $uid = false)
    {
        global $xoopsDB;

        $path = array();

        if ($uid) {
            $user_handler = &xoonips_getormhandler('xoonips', 'users');
            $user = &$user_handler->get($uid);
            if (!$user) {
                return '';
            }
        }

        $index_handler = &xoonips_getormhandler('xoonips', 'index');

        $index = &$this->_get_index($index_id);
        if (!$index) {
            return '';
        }
        do {
            if ($user
                && $index->get('index_id')
                == $user->get('private_index_id')) {
                $path[] = 'Private';
            } else {
                $path[] = $index->getExtraVar('title');
            }
            $index = &$this->_get_index($index->get('parent_index_id'));
        } while ($index);
        array_pop($path);

        return '/'.implode('/', array_reverse($path));
    }

    public function &_get_index($index_id)
    {
        global $xoopsDB;
        $falseVar = false;

        $index_handler = &xoonips_getormhandler('xoonips', 'index');
        $criteria = new CriteriaCompo(new Criteria('index_id', $index_id));
        $criteria->add(new Criteria('title_id', 0));
        $join = new XooNIpsJoinCriteria('xoonips_item_title', 'index_id', 'item_id');
        $indexes = &$index_handler->getObjects($criteria, false,
                                                  '', false, $join);

        if (count($indexes) == 0) {
            return $falseVar;
        }

        return $indexes[0];
    }
}
