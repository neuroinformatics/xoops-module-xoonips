<?php

// $Revision: 1.1.2.7 $
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

include_once XOOPS_ROOT_PATH.'/modules/xoonips/class/xoonips_compo_item.class.php';
include_once XOOPS_ROOT_PATH.'/modules/xnpbook/iteminfo.php';

/**
 * @brief Handler object that create,insert,update,get,delete
 * XNPBookCompo object.
 */
class XNPBookCompoHandler extends XooNIpsItemInfoCompoHandler
{
    public function XNPBookCompoHandler(&$db)
    {
        parent::XooNIpsItemInfoCompoHandler($db, 'xnpbook');
    }

    public function &create()
    {
        $book = new XNPBookCompo();

        return $book;
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
            return 'xnpbook_transfer_item_detail.html';
        case XOONIPS_TEMPLATE_TYPE_TRANSFER_ITEM_LIST:
            return 'xnpbook_transfer_item_list.html';
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
     *                        or XOONIPS_TEMPLATE_TYPE_ITEM_LIST
     * @param int    $item_id
     * @param int    $uid     user id who get item
     *
     * @return array of template variables
     */
    public function getTemplateVar($type, $item_id, $uid)
    {
        $book = &$this->get($item_id);
        if (!is_object($book)) {
            return array();
        }
        $result = $this->getBasicTemplateVar($type, $book, $uid);

        $detail = &$book->getVar('detail');
        switch ($type) {
        case XOONIPS_TEMPLATE_TYPE_ITEM_LIST:
            $result['author'] = array();
            foreach ($book->getVar('author') as $author) {
                $result['author'][] = $author->getVarArray('s');
            }

            return $result;
        case XOONIPS_TEMPLATE_TYPE_TRANSFER_ITEM_DETAIL:
        case XOONIPS_TEMPLATE_TYPE_ITEM_DETAIL:
        case XOONIPS_TEMPLATE_TYPE_TRANSFER_ITEM_LIST:
            $result['xnpbook_author']
                = xoonips_get_multiple_field_template_vars($detail->getAuthors(),
                                                          'xnpbook',
                                                          'author');
            $result['detail']
                = array('editor' => $detail->getVar('editor', 's'),
                         'publisher' => $detail->getVar('publisher', 's'),
                         'isbn' => $detail->getVar('isbn', 's'),
                         'url' => $detail->getVar('url', 's'),
                         'attachment_dl_limit' => $detail->get('attachment_dl_limit'),
                         'attachment_dl_notify' => $detail->get('attachment_dl_notify'), );
            $book_pdf = $book->getVar('book_pdf');
            if ($book_pdf->get('item_id') == $item_id) {
                $result['detail']['book_pdf']
                    = $this->getAttachmentTemplateVar(
                        $book->getVar('book_pdf'));
            }

            return $result;
        }
    }
}

/**
 * @brief Data object that have one ore more XooNIpsTableObject for Book type.
 */
class XNPBookCompo extends XooNIpsItemInfoCompo
{
    public function XNPBookCompo()
    {
        parent::XooNIpsItemInfoCompo('xnpbook');
    }
}
