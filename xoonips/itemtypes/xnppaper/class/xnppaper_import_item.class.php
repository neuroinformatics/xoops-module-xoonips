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

class XNPPaperImportItem extends XooNIpsImportItem
{
    public $_has_paper_pdf_reprint = false;

    public function __construct()
    {
        $handler = &xoonips_getormcompohandler('xnppaper', 'item');
        $this->_item = &$handler->create();
    }

    public function setHasPaperPdfReprint()
    {
        $this->_has_paper_pdf_reprint = true;
    }

    public function unsetHasPaperPdfReprint()
    {
        $this->_has_paper_pdf_reprint = false;
    }

    public function hasPaperPdfReprint()
    {
        return $this->_has_paper_pdf_reprint;
    }

    /**
     * get total file size(bytes) of this item.
     *
     * @return int file size in bytes
     */
    public function getTotalFileSize()
    {
        $size = 0;
        $file = &$this->getVar('paper_pdf_reprint');
        if (!$file) {
            return 0;
        }
        $size = $file->get('file_size');

        return $size;
    }

    public function &getClone()
    {
        $clone = &parent::getClone();
        $clone->_has_paper_pdf_reprint = $this->_has_paper_pdf_reprint;

        return $clone;
    }
}

class XNPPaperImportItemHandler extends XooNIpsImportItemHandler
{
    /**
     * array of supported version of import file.
     */
    public $_import_file_version = array('1.00', '1.01', '1.02');

    /**
     * version string of detail information.
     */
    public $_detail_version = null;

    /**
     * attachment file object(XooNIpsFile).
     */
    public $_paper_pdf_reprint_file = null;

    /**
     * flag of attachment file parsed.
     */
    public $_paper_pdf_reprint_file_flag = false;

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
        return new XNPPaperImportItem();
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
            if ($this->_paper_pdf_reprint_file_flag) {
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
            $this->_paper_pdf_reprint_file = &$file_handler->create();
            $this->_paper_pdf_reprint_file->setFilepath($this->_attachment_dir.'/'.$attribs['FILE_NAME']);
            $this->_paper_pdf_reprint_file->set('original_file_name', $unicode->decode_utf8($attribs['ORIGINAL_FILE_NAME'], xoonips_get_server_charset(), 'h'));
            $this->_paper_pdf_reprint_file->set('mime_type', $attribs['MIME_TYPE']);
            $this->_paper_pdf_reprint_file->set('file_size', $attribs['FILE_SIZE']);
            $this->_paper_pdf_reprint_file->set('sess_id', session_id());
            $this->_paper_pdf_reprint_file->set('file_type_id', $file_type[0]->get('file_type_id'));
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
            foreach (array('journal',
                             'volume',
                             'number',
                             'page',
                             'abstract',
                             'pubmed_id', ) as $key) {
                if (is_null($detail->get($key, 'n'))) {
                    $this->_import_item->setErrors(E_XOONIPS_TAG_NOT_FOUND, " no $key".$this->_get_parser_error_at());
                }
            }
            //error is no authors
            if (count($this->_import_item->getVar('author')) == 0) {
                $this->_import_item->setErrors(E_XOONIPS_TAG_NOT_FOUND, ' no author'.$this->_get_parser_error_at());
            }
            break;
        case 'ITEM/DETAIL/AUTHORS/AUTHOR':
            $authors = &$this->_import_item->getVar('author');

            $author_handler = &xoonips_getormhandler('xnppaper', 'author');
            $author = &$author_handler->create();

            $author->set('author', $unicode->decode_utf8($this->_cdata, xoonips_get_server_charset(), 'h'));
            $author->set('author_order', count($authors));

            $authors[] = $author;
            break;
        case 'ITEM/DETAIL/JOURNAL':
        case 'ITEM/DETAIL/VOLUME':
        case 'ITEM/DETAIL/NUMBER':
        case 'ITEM/DETAIL/PAGE':
        case 'ITEM/DETAIL/ABSTRACT':
        case 'ITEM/DETAIL/PUBMED_ID':
            $detail->set(strtolower(end($this->_tag_stack)), $unicode->decode_utf8($this->_cdata, xoonips_get_server_charset(), 'h'), true);
            break;
        case 'ITEM/DETAIL/FILE':
            $this->_paper_pdf_reprint_file_flag = true;
            $file_handler = &xoonips_getormhandler('xoonips', 'file');
            if (!$file_handler->insert($this->_paper_pdf_reprint_file)) {
                $this->_import_item->setErrors(E_XOONIPS_DB_QUERY, "can't insert attachment file:".$this->_paper_pdf_reprint_file->get('original_file_name').$this->_get_parser_error_at());
            }
            $this->_paper_pdf_reprint_file = $file_handler->get($this->_paper_pdf_reprint_file->get('file_id'));
            $this->_import_item->setVar('paper_pdf_reprint', $this->_paper_pdf_reprint_file);
            $this->_import_item->setHasPaperPdfReprint();
            break;
        case 'ITEM/DETAIL/FILE/CAPTION':
            $this->_paper_pdf_reprint_file->set('caption', $unicode->decode_utf8($this->_cdata, xoonips_get_server_charset(), 'h'));
            break;
        case 'ITEM/DETAIL/FILE/THUMBNAIL':
            $this->_paper_pdf_reprint_file->set('thumbnail_file', base64_decode($this->_cdata));
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
        if ('xnppaperimportitem' != strtolower(get_class($item))) {
            return;
        }

        $this->_set_file_delete_flag($item);

        // nothing to do if no file
        if ($item->hasPaperPdfReprint()) {
            $paper_pdf_reprint = &$item->getVar('paper_pdf_reprint');
            $this->_fix_item_id_of_file($item, $paper_pdf_reprint);
            $this->_create_text_search_index($paper_pdf_reprint);
        }

        parent::onImportFinished($item, $import_items);
    }

    public function insert(&$item)
    {
        $handler = &xoonips_getormcompohandler('xnppaper', 'item');

        return $handler->insert($item);
    }

    public function setNew(&$item)
    {
        $handler = &xoonips_getormcompohandler('xnppaper', 'item');

        return $handler->setNew($item);
    }

    public function unsetNew(&$item)
    {
        $handler = &xoonips_getormcompohandler('xnppaper', 'item');

        return $handler->unsetNew($item);
    }

    public function setDirty(&$item)
    {
        $handler = &xoonips_getormcompohandler('xnppaper', 'item');

        return $handler->setDirty($item);
    }

    public function unsetDirty(&$item)
    {
        $handler = &xoonips_getormcompohandler('xnppaper', 'item');

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
        $text = parent::getImportLog($import_item);
        $detail = &$import_item->getVar('detail');
        foreach ($import_item->getVar('author') as $author) {
            $text .= "\ndetail.author ".$author->get('author');
        }
        $text .= "\ndetail.journal ".$detail->get('journal');
        $text .= "\ndetail.volume ".$detail->get('volume');
        $text .= "\ndetail.number ".$detail->get('number');
        $text .= "\ndetail.page ".$detail->get('page');
        $text .= "\ndetail.abstract ".mb_ereg_replace('\n', '\n', mb_ereg_replace('\\\\', '\\\\', $detail->get('abstract')));
        $text .= "\ndetail.pubmed_id ".$detail->get('pubmed_id');

        return $text;
    }

    public function import(&$item)
    {
        if ($item->getUpdateFlag()) {
            $detail = &$item->getVar('detail');
            $detail->unsetNew();
            $detail->setDirty();

            //copy attachment file
            $paper_pdf_reprint = &$item->getVar('paper_pdf_reprint');
            if ($item->hasPaperPdfReprint()) {
                $file_handler = &xoonips_getormhandler('xoonips', 'file');
                $clonefile = &$file_handler->fileClone($paper_pdf_reprint);
                $clonefile->setDirty();
                $item->setVar('paper_pdf_reprint', $clonefile);

                $paper_pdf_reprint = &$item->getVar('paper_pdf_reprint');
            }
        }
        parent::import($item);
    }
}
