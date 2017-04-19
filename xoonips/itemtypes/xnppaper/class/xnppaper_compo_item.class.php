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

if (!defined('XOOPS_ROOT_PATH')) {
    exit();
}

require_once XOOPS_ROOT_PATH.'/modules/xoonips/class/xoonips_compo_item.class.php';
require_once XOOPS_ROOT_PATH.'/modules/xnppaper/iteminfo.php';
require_once XOOPS_ROOT_PATH.'/modules/xnppaper/include/view.php';

/**
 * @brief Handler object that create,insert,update,get,delete XNPPaperCompo object.
 */
class XNPPaperCompoHandler extends XooNIpsItemInfoCompoHandler
{
    public function __construct(&$db)
    {
        parent::__construct($db, 'xnppaper');
    }

    public function &create()
    {
        $paper = new XNPPaperCompo();

        return $paper;
    }

    /**
     * return template filename.
     *
     * @param string $type defined symbol
     *                     XOONIPS_TEMPLATE_TYPE_TRANSFER_ITEM_DETAIL
     *                     or XOONIPS_TEMPLATE_TYPE_TRANSFER_ITEM_LISTL
     *
     * @return template filename
     */
    public function getTemplateFileName($type)
    {
        switch ($type) {
        case XOONIPS_TEMPLATE_TYPE_TRANSFER_ITEM_DETAIL:
            return 'xnppaper_transfer_item_detail.html';
        case XOONIPS_TEMPLATE_TYPE_TRANSFER_ITEM_LIST:
            return 'xnppaper_transfer_item_list.html';
        default:
            return '';
        }
    }

    /**
     * return template variables of item.
     *
     * @param string $type    defined symbol
     *                        XOONIPS_TEMPLATE_TYPE_TRANSFER_ITEM_DETAIL
     *                        , XOONIPS_TEMPLATE_TYPE_TRANSFER_ITEM_LIST
     *                        , XOONIPS_TEMPLATE_TYPE_ITEM_DETAIL
     *                        or XOONIPS_TEMPLATE_TYPE_ITEM_LIST
     * @param int    $item_id
     * @param int    $uid     user id who get item
     *
     * @return array of template variables
     */
    public function getTemplateVar($type, $item_id, $uid)
    {
        $paper = &$this->get($item_id);
        if (!is_object($paper)) {
            return array();
        }
        $result = $this->getBasicTemplateVar($type, $paper, $uid);

        $detail = &$paper->getVar('detail');
        $result['detail'] = $detail->getVarArray('s');
        $result['detail']['pubmed_link'] = xnppaper_create_pubmed_link($detail->getVar('pubmed_id', 'n'));

        switch ($type) {
        case XOONIPS_TEMPLATE_TYPE_TRANSFER_ITEM_LIST:
        case XOONIPS_TEMPLATE_TYPE_ITEM_LIST:
            $result['author'] = array();
            foreach ($paper->getVar('author') as $author) {
                $result['author'][] = $author->getVarArray('s');
            }

            return $result;
        case XOONIPS_TEMPLATE_TYPE_ITEM_DETAIL:
        case XOONIPS_TEMPLATE_TYPE_TRANSFER_ITEM_DETAIL:
            $result['xnppaper_author'] = xoonips_get_multiple_field_template_vars($detail->getAuthors(), 'xnppaper', 'author');
            $paper_pdf_reprint = $paper->getVar('paper_pdf_reprint');
            if ($paper_pdf_reprint->get('item_id') == $item_id) {
                $result['detail']['paper_pdf_reprint'] = $this->getAttachmentTemplateVar($paper->getVar('paper_pdf_reprint'));
            }

            return $result;
        }

        return $result;
    }

    /**
     * return true if user has permission to download file.
     *
     * @param int uid user id
     * @param int file_id file id
     */
    public function hasDownloadPermission($uid, $file_id)
    {
        $file_handler = &xoonips_getormhandler('xoonips', 'file');
        $file = &$file_handler->get($file_id);
        if (!$file) {
            return false;
        } // no such file

        $item_id = $file->get('item_id');
        if (!$item_id) {
            return false;
        } // file is not belong to any item

        $item_compo = $this->get($item_id);
        if (!$item_compo) {
            return false;
        } // bad item

        $detail = $item_compo->getVar('detail');
        if (!$detail) {
            return false;
        } // bad item

        if (!$this->getPerm($item_id, $uid, 'read')) {
            return false;
        } // no permission

        // retrieve config of pdf and abstract
        $mhandler = &xoops_gethandler('module');
        $module = $mhandler->getByDirname('xnppaper');
        $chandler = &xoops_gethandler('config');
        $assoc = $chandler->getConfigsByCat(false, $module->mid());
        $pdf_access_rights = $assoc['pdf_access_rights']; // 1:public, 2:group, 3:private

        if ($pdf_access_rights == 1) { // 1:public
            return true;
        }

        // moderator or admin?
        $member_handler = &xoonips_gethandler('xoonips', 'member');
        if ($member_handler->isModerator($uid) || $member_handler->isAdmin($uid)) {
            return true; // moderator or admin or public
        }

        if ($pdf_access_rights == 2) { // 2:group
            $index_item_link_handler = &xoonips_getormhandler('xoonips', 'index_item_link');
            $index_handler = &xoonips_getormhandler('xoonips', 'index');
            $index_item_links = $index_item_link_handler->getByItemId($item_id, array(OL_GROUP_ONLY));
            foreach ($index_item_links as $index_item_link) {
                if ($index_item_link->get('certify_state') == CERTIFIED) {
                    if ($index_handler->getPerm($index_item_link->get('index_id'), $uid, 'read')) {
                        return true; // group member && item certified
                    }
                    if ($index_item_link_handler->getPerm($index_id, $item_id, $uid, 'certify')) {
                        return true; // group admin && item not certified
                    }
                }
            }
        }

        // private?
        $basic = $item_compo->getVar('basic');
        if ($uid == $basic->get('uid')) {
            return true;
        }

        return false;
    }
}

/**
 * @brief Data object that have one ore more XooNIpsTableObject for Paper type.
 */
class XNPPaperCompo extends XooNIpsItemInfoCompo
{
    public function __construct()
    {
        parent::__construct('xnppaper');
    }
}
