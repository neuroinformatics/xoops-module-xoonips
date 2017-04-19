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

require_once dirname(dirname(__DIR__)).'/xoonips/class/xoonips_import_item.class.php';

class XNPBookImportItem extends XooNIpsImportItem
{
    public $_has_book_pdf = false;

    public function __construct()
    {
        $handler = &xoonips_getormcompohandler('xnpbook', 'item');
        $this->_item = &$handler->create();
    }

    public function setHasBookPdf()
    {
        $this->_has_book_pdf = true;
    }

    public function unsetHasBookPdf()
    {
        $this->_has_book_pdf = false;
    }

    public function hasBookPdf()
    {
        return $this->_has_book_pdf;
    }

    /**
     * get total file size(bytes) of this item.
     *
     * @return int file size in bytes
     */
    public function getTotalFileSize()
    {
        $file = &$this->getVar('book_pdf');
        if (!$file) {
            return 0;
        }

        return $file->get('file_size');
    }

    public function &getClone()
    {
        $clone = &parent::getClone();
        $clone->_has_book_pdf = $this->_has_book_pdf;

        return $clone;
    }
}

class XNPBookImportItemHandler extends XooNIpsImportItemHandler
{
    /**
     * array of supported version of import file.
     */
    public $_import_file_version = array('1.00', '1.01', '1.02', '1.03');

    /**
     * version string of detail information.
     */
    public $_detail_version = null;

    /**
     * attachment file object(XooNIpsFile).
     */
    public $_book_pdf_file = null;

    /**
     * flag of attachment file parsed.
     */
    public $_book_pdf_file_flag = false;

    /**
     * attachment_dl_limit flag.
     */
    public $_attachment_dl_limit_flag = false;

    /**
     * attachment_dl__notify_limit flag.
     */
    public $_attachment_dl_notify_limit_flag = false;

    public function __construct()
    {
        parent::__construct();
    }

    public function create()
    {
        return new XNPBookImportItem();
    }

    /**
     * @param
     */
    public function xmlStartElementHandler($parser, $name, $attribs)
    {
        global $xoopsDB;
        parent::xmlStartElementHandler($parser, $name, $attribs);

        switch (implode('/', $this->_tag_stack)) {
        case 'ITEM/DETAIL':
            //
            // validate version and set it to 'detail_version' variable
            //
            if (!empty($attribs['VERSION'])) {
                if (in_array($attribs['VERSION'], $this->_import_file_version)) {
                    $this->_detail_version = $attribs['VERSION'];
                } else {
                    $this->_import_item->setErrors(E_XOONIPS_INVALID_VALUE, 'unsupported version('.$attribs['VERSION'].') '.$this->_get_parser_error_at());
                }
            } else {
                $this->_detail_version = '1.00';
            }
            break;
        case 'ITEM/DETAIL/FILE':
            if ($this->_book_pdf_file_flag) {
                $this->_import_item->setErrors(E_XOONIPS_ATTACHMENT_HAS_REDUNDANT, "multiple $name attachments is not allowed".$this->_get_parser_error_at());
                break;
            }
            $file_type_handler = &xoonips_getormhandler('xoonips', 'file_type');
            $file_handler = &xoonips_getormhandler('xoonips', 'file');
            $criteria = new Criteria('name', addslashes($attribs['FILE_TYPE_NAME']));
            $file_type = &$file_type_handler->getObjects($criteria);
            if (count($file_type) == 0) {
                $this->_import_item->setErrors(E_XOONIPS_ATTR_NOT_FOUND, 'file_type_id is not found:'.$attribs['FILE_TYPE_NAME'].$this->_get_parser_error_at());
                break;
            }

            $unicode = &xoonips_getutility('unicode');
            $this->_book_pdf_file = &$file_handler->create();
            $this->_book_pdf_file->setFilepath($this->_attachment_dir.'/'.$attribs['FILE_NAME']);
            $this->_book_pdf_file->set('original_file_name', $unicode->decode_utf8($attribs['ORIGINAL_FILE_NAME'], xoonips_get_server_charset(), 'h'));
            $this->_book_pdf_file->set('mime_type', $attribs['MIME_TYPE']); $this->_book_pdf_file->set('file_size', $attribs['FILE_SIZE']);
            $this->_book_pdf_file->set('sess_id', session_id());
            $this->_book_pdf_file->set('file_type_id', $file_type[0]->get('file_type_id'));
            break;
        case 'ITEM/DETAIL/FILE/CAPTION':
        case 'ITEM/DETAIL/FILE/THUMBNAIL':
            break;
        }
    }

    /**
     * @param
     */
    public function xmlEndElementHandler($parser, $name)
    {
        global $xoopsDB;
        $detail = &$this->_import_item->getVar('detail');
        $unicode = &xoonips_getutility('unicode');
        switch (implode('/', $this->_tag_stack)) {
        case 'ITEM/DETAIL':
            foreach (array('editor', 'publisher', 'isbn', 'url') as $key) {
                if (is_null($detail->get($key, 'n'))) {
                    $this->_import_item->setErrors(E_XOONIPS_TAG_NOT_FOUND, " no $key".$this->_get_parser_error_at());
                }
            }
            if (is_null($detail->get('attachment_dl_limit'))) {
                if ($this->_detail_version == '1.00') {
                    //
                    // set zero to attachment_dl_limit
                    // if it is not declared in xml
                    //
                    $detail->set('attachment_dl_limit', 0);
                } else {
                    $this->_import_item->setErrors(E_XOONIPS_TAG_NOT_FOUND, ' no attachment_dl_limit tag '.$this->_get_parser_error_at());
                }
            }
            if (is_null($detail->get('attachment_dl_notify'))) {
                if ($this->_detail_version == '1.00'
                    || $this->_detail_version == '1.01'
                ) {
                    $detail->set('attachment_dl_notify', 0);
                } else {
                    $this->_import_item->setErrors(E_XOONIPS_TAG_NOT_FOUND, ' no attachment_dl_notify tag '.$this->_get_parser_error_at());
                }
            }
            break;
        case 'ITEM/DETAIL/AUTHOR':
            if ($this->_detail_version != '1.00'
                && $this->_detail_version != '1.01'
                && $this->_detail_version != '1.02'
            ) {
                //<author> is only for 1.00, 1.01 and 1.02
                break;
            }

            $cdata = $unicode->decode_utf8($this->_cdata, xoonips_get_server_charset(), 'h');
            if (trim($cdata) == '') {
                break;
            }
            $author_handler = &xoonips_getormhandler('xnpbook', 'author');
            $authors = &$this->_import_item->getVar('author');
            $author = &$author_handler->create();

            $author->set('author', $cdata);
            $author->set('author_order', 0);

            $authors[0] = $author;
            break;
        case 'ITEM/DETAIL/AUTHORS/AUTHOR':
            if ($this->_detail_version != '1.03') {
                break;
            }
            $cdata = $unicode->decode_utf8($this->_cdata, xoonips_get_server_charset(), 'h');
            if (trim($cdata) == '') {
                break;
            }
            $authors = &$this->_import_item->getVar('author');

            $author_handler = &xoonips_getormhandler('xnpbook', 'author');
            $author = &$author_handler->create();

            $author->set('author', $cdata);
            $author->set('author_order', count($authors));

            $authors[] = $author;
            break;
        case 'ITEM/DETAIL/EDITOR':
            $detail->set('editor', $unicode->decode_utf8($this->_cdata, xoonips_get_server_charset(), 'h'), true);
            break;
        case 'ITEM/DETAIL/PUBLISHER':
            $detail->set('publisher', $unicode->decode_utf8($this->_cdata, xoonips_get_server_charset(), 'h'), true);
            break;
        case 'ITEM/DETAIL/ISBN':
            $detail->set('isbn', $unicode->decode_utf8($this->_cdata, xoonips_get_server_charset(), 'h'), true);
            break;
        case 'ITEM/DETAIL/URL':
            $detail->set('url', $unicode->decode_utf8($this->_cdata, xoonips_get_server_charset(), 'h'), true);
            break;
        case 'ITEM/DETAIL/ATTACHMENT_DL_LIMIT':
            if ($this->_attachment_dl_limit_flag) {
                $this->_import_item->setErrors(E_XOONIPS_TAG_REDUNDANT, 'attachment_dl_limit is redundant'.$this->_get_parser_error_at());
            } elseif (ctype_digit($this->_cdata)) {
                $detail->set('attachment_dl_limit', intval($this->_cdata));
                $this->_attachment_dl_limit_flag = true;
            } else {
                $this->_import_item->setErrors(E_XOONIPS_INVALID_VALUE, 'invalid value('.$this->_cdata.') of attachment_dl_limit'.$this->_get_parser_error_at());
            }
            break;
        case 'ITEM/DETAIL/ATTACHMENT_DL_NOTIFY':
            if ($this->_attachment_dl_notify_limit_flag) {
                $this->_import_item->setErrors(E_XOONIPS_TAG_REDUNDANT, 'attachment_dl_notify is redundant'.$this->_get_parser_error_at());
            } elseif (ctype_digit($this->_cdata)) {
                $detail->set('attachment_dl_notify', intval($this->_cdata));
                $this->_attachment_dl_notify_limit_flag = true;
            } else {
                $this->_import_item->setErrors(E_XOONIPS_INVALID_VALUE, 'invalid value('.$this->_cdata.') of attachment_dl_notify'.$this->_get_parser_error_at());
            }
            break;
        case 'ITEM/DETAIL/FILE':
            $this->_book_pdf_file_flag = true;
            $file_handler = &xoonips_getormhandler('xoonips', 'file');
            if (!$file_handler->insert($this->_book_pdf_file)) {
                $this->_import_item->setErrors(E_XOONIPS_DB_QUERY, "can't insert attachment file:".$this->_book_pdf_file->get('original_file_name').$this->_get_parser_error_at());
            }
            $this->_book_pdf_file = $file_handler->get($this->_book_pdf_file->get('file_id'));
            $this->_import_item->setVar('book_pdf', $this->_book_pdf_file);
            $this->_import_item->setHasBookPdf();
            break;
        case 'ITEM/DETAIL/FILE/CAPTION':
            $unicode = &xoonips_getutility('unicode');
            $this->_book_pdf_file->set('caption', $unicode->decode_utf8($this->_cdata, xoonips_get_server_charset(), 'h'));
            break;
        case 'ITEM/DETAIL/FILE/THUMBNAIL':
            $this->_book_pdf_file->set('thumbnail_file', base64_decode($this->_cdata));
            break;
        }

        parent::xmlEndElementHandler($parser, $name);
    }

    /**
     * Update item_id and sess_id of xoonips_file.
     *
     * @param $item xooNIpsImportItem that is imported
     * @param $import_items array of all of XooNIpsImportItems
     */
    public function onImportFinished(&$item, &$import_items)
    {
        if ('xnpbookimportitem' != strtolower(get_class($item))) {
            return;
        }

        $this->_set_file_delete_flag($item);

        if ($item->hasBookPdf()) {
            $book_pdf = &$item->getVar('book_pdf');
            $this->_fix_item_id_of_file($item, $book_pdf);
            $this->_create_text_search_index($book_pdf);
        }
        parent::onImportFinished($item, $import_items);
    }

    public function insert(&$item)
    {
        $handler = &xoonips_getormcompohandler('xnpbook', 'item');

        return $handler->insert($item);
    }

    public function setNew(&$item)
    {
        $handler = &xoonips_getormcompohandler('xnpbook', 'item');

        return $handler->setNew($item);
    }

    public function unsetNew(&$item)
    {
        $handler = &xoonips_getormcompohandler('xnpbook', 'item');

        return $handler->unsetNew($item);
    }

    public function setDirty(&$item)
    {
        $handler = &xoonips_getormcompohandler('xnpbook', 'item');

        return $handler->setDirty($item);
    }

    public function unsetDirty(&$item)
    {
        $handler = &xoonips_getormcompohandler('xnpbook', 'item');

        return $handler->unsetDirty($item);
    }

    /**
     * reeturn import log text of import item.
     *
     * @param $import_item reference of XooNIpsImportItem object
     *
     * @return string import log text
     */
    public function getImportLog($import_item)
    {
        $author_handler = &xoonips_getormhandler('xnpbook', 'author');
        $text = parent::getImportLog($import_item);
        $detail = &$import_item->getVar('detail');
        $authors = &$import_item->getVar('author');
        foreach ($authors as $author) {
            $text .= "\ndetail.author ".$author->get('author');
        }
        $text .= "\ndetail.editor ".$detail->get('editor');
        $text .= "\ndetail.publisher ".$detail->get('publisher');
        $text .= "\ndetail.isbn ".$detail->get('isbn');
        $text .= "\ndetail.url ".$detail->get('url');
        $text .= "\ndetail.attachment_dl_limit "
            .$detail->get('attachment_dl_limit');
        $text .= "\ndetail.attachment_dl_notify "
            .$detail->get('attachment_dl_notify');

        return $text;
    }

    public function import(&$item)
    {
        if ($item->getUpdateFlag()) {
            $detail = &$item->getVar('detail');
            $detail->unsetNew();
            $detail->setDirty();

            //copy attachment file
            $book_pdf = &$item->getVar('book_pdf');
            if ($item->hasBookPdf()) {
                $file_handler = &xoonips_getormhandler('xoonips', 'file');
                $clonefile = &$file_handler->fileClone($book_pdf);
                $clonefile->setDirty();
                $item->setVar('book_pdf', $clonefile);

                $book_pdf = &$item->getVar('book_pdf');
            }
        }
        parent::import($item);
    }
}
