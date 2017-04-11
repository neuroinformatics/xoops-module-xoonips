<?php

// $Revision: 1.1.2.11 $
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

include_once dirname(dirname(__DIR__))
    .'/xoonips/class/xoonips_import_item.class.php';

class XNPConferenceImportItem extends XooNIpsImportItem
{
    public $_has_conference_file = false;

    public $_has_conference_paper = false;

    public function XNPConferenceImportItem()
    {
        $handler = &xoonips_getormcompohandler('xnpconference', 'item');
        $this->_item = &$handler->create();
    }

    public function setHasConferenceFile()
    {
        $this->_has_conference_file = true;
    }

    public function unsetHasConferenceFile()
    {
        $this->_has_conference_file = false;
    }

    public function hasConferenceFile()
    {
        return $this->_has_conference_file;
    }

    public function setHasConferencePaper()
    {
        $this->_has_conference_paper = true;
    }

    public function unsetHasConferencePaper()
    {
        $this->_has_conference_paper = false;
    }

    public function hasConferencePaper()
    {
        return $this->_has_conference_paper;
    }

    /**
     * get total file size(bytes) of this item.
     *
     * @return int file size in bytes
     */
    public function getTotalFileSize()
    {
        $size = 0;
        $conference_file = &$this->getVar('conference_file');
        if (!$conference_file) {
            return 0;
        }
        $size += $conference_file->get('file_size');
        $conference_paper = &$this->getVar('conference_paper');
        if (!$conference_paper) {
            return 0;
        }
        $size += $conference_paper->get('file_size');

        return $size;
    }

    public function &getClone()
    {
        $clone = &parent::getClone();
        $clone->_has_conference_file = $this->_has_conference_file;
        $clone->_has_conference_paper = $this->_has_conference_paper;

        return $clone;
    }
}

class XNPConferenceImportItemHandler extends XooNIpsImportItemHandler
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
     * attachment file object(XooNIpsAttachment).
     */
    public $_conference_file = null;

    /**
     * flag of attachment file parsed.
     */
    public $_conference_file_flag = false;

    /**
     * attachment file object(XooNIpsAttachment).
     */
    public $_conference_paper = null;

    /**
     * flag of attachment file parsed.
     */
    public $_conference_paper_flag = false;

    /**
     * attachment_dl_limit flag.
     */
    public $_attachment_dl_limit_flag = false;

    /**
     * attachment_dl__notify_limit flag.
     */
    public $_attachment_dl_notify_limit_flag = false;

    public $_file_type_attribute = null;

    public function XNPConferenceImportItemHandler()
    {
        parent::XooNIpsImportItemHandler();
    }

    public function create()
    {
        return new XNPConferenceImportItem();
    }

    /**
     * @param
     */
    public function xmlStartElementHandler($parser, $name, $attribs)
    {
        global $xoopsDB;
        parent::xmlStartElementHandler($parser, $name, $attribs);
        $detail = &$this->_import_item->getVar('detail');

        switch (implode('/', $this->_tag_stack)) {
        case 'ITEM/DETAIL':
            //
            // validate version and set it to 'version' variable
            //
            if (!empty($attribs['VERSION'])) {
                if (in_array($attribs['VERSION'],
                              $this->_import_file_version)) {
                    $this->_detail_version = $attribs['VERSION'];
                } else {
                    $this->_import_item->setErrors(
                        E_XOONIPS_INVALID_VALUE,
                        'unsupported version('.$attribs['VERSION'].') '
                        .$this->_get_parser_error_at());
                }
            } else {
                $this->_detail_version = '1.00';
            }
            break;
        case 'ITEM/DETAIL/FILE':
            $this->_file_type_attribute = $attribs['FILE_TYPE_NAME'];

            $file_type_handler = &xoonips_getormhandler('xoonips',
                                                         'file_type');
            $file_handler = &xoonips_getormhandler('xoonips', 'file');
            $criteria = new Criteria('name',
                                      addslashes($attribs['FILE_TYPE_NAME']));
            $file_type = &$file_type_handler->getObjects($criteria);
            if (count($file_type) == 0) {
                $this->_import_item->setErrors(
                    E_XOONIPS_ATTR_NOT_FOUND,
                    'file_type_id is not found:'.$attribs['FILE_TYPE_NAME']
                    .$this->_get_parser_error_at());
                break;
            }

            $unicode = &xoonips_getutility('unicode');
            if ($this->_file_type_attribute == 'conference_file') {
                if ($this->_conference_file_flag) {
                    $this->_import_item->setErrors(
                        E_XOONIPS_ATTACHMENT_HAS_REDUNDANT,
                        "multiple $name attachments is not allowed"
                        .$this->_get_parser_error_at());
                    break;
                }
                $this->_conference_file = &$file_handler->create();
                $this->_conference_file->setFilepath(
                    $this->_attachment_dir.'/'.$attribs['FILE_NAME']);
                $this->_conference_file->set(
                    'original_file_name',
                    $unicode->decode_utf8($attribs['ORIGINAL_FILE_NAME'],
                                          xoonips_get_server_charset(), 'h'));
                $this->_conference_file->set('mime_type',
                                                  $attribs['MIME_TYPE']);
                $this->_conference_file->set('file_size',
                                                  $attribs['FILE_SIZE']);
                $this->_conference_file->set('sess_id', session_id());
                $this->_conference_file->set(
                    'file_type_id', $file_type[0]->get('file_type_id'));
            } elseif ($this->_file_type_attribute == 'conference_paper') {
                $this->_conference_paper = &$file_handler->create();
                $this->_conference_paper->setFilepath(
                    $this->_attachment_dir.'/'.$attribs['FILE_NAME']);
                $this->_conference_paper->set(
                    'original_file_name',
                    $unicode->decode_utf8($attribs['ORIGINAL_FILE_NAME'],
                                          xoonips_get_server_charset(), 'h'));
                $this->_conference_paper->set('mime_type',
                                                   $attribs['MIME_TYPE']);
                $this->_conference_paper->set('file_size',
                                                   $attribs['FILE_SIZE']);
                $this->_conference_paper->set('sess_id', session_id());
                $this->_conference_paper->set(
                    'file_type_id', $file_type[0]->get('file_type_id'));
            } else {
                $this->_import_item->setErrors(
                    E_XOONIPS_ATTR_NOT_FOUND,
                    'file_type_id is not found:'.$attribs['FILE_TYPE_NAME']
                    .$this->_get_parser_error_at());
                break;
            }
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
            if ($this->_detail_version == '1.00') {
            } elseif ($this->_detail_version == '1.01') {
                $keys[] = 'attachment_dl_limit';
            } else {
                $keys[] = 'attachment_dl_limit';
                $keys[] = 'attachment_dl_notify';
            }
            foreach (array(
                'presentation_type',
                'conference_title',
                'place',
                'abstract',
                'conference_from_year',
                'conference_from_month',
                'conference_from_mday',
                'conference_to_year',
                'conference_to_month',
                'conference_to_mday',
                ) as $key) {
                if (is_null($detail->get($key, 'n'))) {
                    $this->_import_item->setErrors(
                        E_XOONIPS_TAG_NOT_FOUND, " no $key"
                        .$this->_get_parser_error_at());
                }
            }
            //error if no authors
            if (count($this->_import_item->getVar('author')) == 0) {
                $this->_import_item->setErrors(E_XOONIPS_TAG_NOT_FOUND, ' no author'
                                                   .$this->_get_parser_error_at());
            }
            break;
        case 'ITEM/DETAIL/AUTHORS/AUTHOR':
            if ($this->_detail_version != '1.01'
                && $this->_detail_version != '1.02') {
                break;
            }

            $authors = &$this->_import_item->getVar('author');

            $author_handler = &xoonips_getormhandler('xnpconference', 'author');
            $author = &$author_handler->create();

            $author->set('author', $unicode->decode_utf8($this->_cdata,
                                                         xoonips_get_server_charset(), 'h'));
            $author->set('author_order', count($authors));

            $authors[] = $author;
            break;
        case 'ITEM/DETAIL/AUTHOR':
            // /item/detail/author is only available in ver 1.00
            if ($this->_detail_version != '1.00') {
                break;
            }
            $authors = &$this->_import_item->getVar('author');

            $author_handler = &xoonips_getormhandler('xnpconference', 'author');
            $author = &$author_handler->create();

            $author->set('author', $unicode->decode_utf8($this->_cdata,
                                                         xoonips_get_server_charset(), 'h'));
            $author->set('author_order', 0);

            $authors[0] = $author;
            break;
        case 'ITEM/DETAIL/PRESENTATION_TYPE':
        case 'ITEM/DETAIL/CONFERENCE_TITLE':
        case 'ITEM/DETAIL/PLACE':
        case 'ITEM/DETAIL/ABSTRACT':
        case 'ITEM/DETAIL/CONFERENCE_FROM_YEAR':
        case 'ITEM/DETAIL/CONFERENCE_FROM_MONTH':
        case 'ITEM/DETAIL/CONFERENCE_FROM_MDAY':
        case 'ITEM/DETAIL/CONFERENCE_TO_YEAR':
        case 'ITEM/DETAIL/CONFERENCE_TO_MONTH':
        case 'ITEM/DETAIL/CONFERENCE_TO_MDAY':
            $unicode = &xoonips_getutility('unicode');
            $detail->set(strtolower(end($this->_tag_stack)),
                            $unicode->decode_utf8(
                                $this->_cdata,
                                xoonips_get_server_charset(), 'h'), true);
            break;
        case 'ITEM/DETAIL/ATTACHMENT_DL_LIMIT':
            if ($this->_attachment_dl_limit_flag) {
                $this->_import_item->setErrors(
                    E_XOONIPS_TAG_REDUNDANT,
                    'attachment_dl_limit is redundant'
                    .$this->_get_parser_error_at());
            } elseif (ctype_digit($this->_cdata)) {
                $detail->set('attachment_dl_limit',
                                intval($this->_cdata));
                $this->_attachment_dl_limit_flag = true;
            } else {
                $this->_import_item->setErrors(
                    E_XOONIPS_INVALID_VALUE,
                    'invalid value('.$this->_cdata
                    .') of attachment_dl_limit'
                    .$this->_get_parser_error_at());
            }
            break;
        case 'ITEM/DETAIL/ATTACHMENT_DL_NOTIFY':
            if ($this->_attachment_dl_notify_limit_flag) {
                $this->_import_item->setErrors(
                    E_XOONIPS_TAG_REDUNDANT,
                    'attachment_dl_notify is redundant'
                    .$this->_get_parser_error_at());
            } elseif (ctype_digit($this->_cdata)) {
                $detail->set('attachment_dl_notify',
                                intval($this->_cdata));
                $this->_attachment_dl_notify_limit_flag = true;
            } else {
                $this->_import_item->setErrors(
                    E_XOONIPS_INVALID_VALUE,
                    'invalid value('.$this->_cdata
                    .') of attachment_dl_notify'
                    .$this->_get_parser_error_at());
            }
            break;
        case 'ITEM/DETAIL/FILE':
            $file_handler = &xoonips_getormhandler('xoonips', 'file');
            if ($this->_file_type_attribute == 'conference_file') {
                $this->_conference_file_flag = true;
                if (!$file_handler->insert($this->_conference_file)) {
                    $this->_import_item->setErrors(
                        E_XOONIPS_DB_QUERY,
                        "can't insert attachment file:"
                        .$this->_conference_file->get(
                            'original_file_name')
                        .$this->_get_parser_error_at());
                }
                $this->_conference_file = $file_handler->get(
                    $this->_conference_file->get('file_id'));
                $this->_import_item->setVar(
                    'conference_file', $this->_conference_file);
                $this->_import_item->setHasConferenceFile();
                $this->_file_type_attribute = null;
            } elseif ($this->_file_type_attribute == 'conference_paper') {
                $this->_conference_paper_flag = true;
                if (!$file_handler->insert($this->_conference_paper)) {
                    $this->_import_item->setErrors(
                        E_XOONIPS_DB_QUERY,
                        "can't insert attachment file:"
                        .$this->_conference_paper->get(
                            'original_file_name')
                        .$this->_get_parser_error_at());
                }
                $this->_conference_paper
                    = $file_handler->get(
                        $this->_conference_paper->get('file_id'));
                $this->_import_item->setVar('conference_paper',
                                                 $this->_conference_paper);
                $this->_import_item->setHasConferencePaper();
                $this->_file_type_attribute = null;
            } else {
                die('unknown file type:'.$this->_file_type_attribute);
            }
            break;

        case 'ITEM/DETAIL/FILE/CAPTION':
            $unicode = &xoonips_getutility('unicode');
            if ($this->_file_type_attribute == 'conference_file') {
                $this->_conference_file->set(
                    'caption',
                    $unicode->decode_utf8(
                        $this->_cdata, xoonips_get_server_charset(), 'h'));
            } elseif ($this->_file_type_attribute == 'conference_paper') {
                $this->_conference_paper->set(
                    'caption',
                    $unicode->decode_utf8(
                        $this->_cdata, xoonips_get_server_charset(), 'h'));
            }
            break;
        case 'ITEM/DETAIL/FILE/THUMBNAIL':
            if ($this->_file_type_attribute == 'conference_file') {
                $this->_conference_file->set(
                    'thumbnail_file', base64_decode($this->_cdata));
            } elseif ($this->_file_type_attribute == 'conference_paper') {
                $this->_conference_paper->set(
                    'thumbnail_file', base64_decode($this->_cdata));
            }
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
        if ('xnpconferenceimportitem' != strtolower(get_class($item))) {
            return;
        }

        $this->_set_file_delete_flag($item);

        if ($item->hasConferenceFile()) {
            $conference_file = &$item->getVar('conference_file');
            $this->_fix_item_id_of_file($item, $conference_file);
            $this->_create_text_search_index($conference_file);
        }

        if ($item->hasConferencePaper()) {
            $conference_paper = &$item->getVar('conference_paper');
            $this->_fix_item_id_of_file($item, $conference_paper);
            $this->_create_text_search_index($conference_paper);
        }

        parent::onImportFinished($item, $import_items);
    }

    public function insert(&$item)
    {
        $handler = &xoonips_getormcompohandler('xnpconference', 'item');

        return $handler->insert($item);
    }

    public function setNew(&$item)
    {
        $handler = &xoonips_getormcompohandler('xnpconference', 'item');

        return $handler->setNew($item);
    }

    public function unsetNew(&$item)
    {
        $handler = &xoonips_getormcompohandler('xnpconference', 'item');

        return $handler->unsetNew($item);
    }

    public function setDirty(&$item)
    {
        $handler = &xoonips_getormcompohandler('xnpconference', 'item');

        return $handler->setDirty($item);
    }

    public function unsetDirty(&$item)
    {
        $handler = &xoonips_getormcompohandler('xnpconference', 'item');

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

        $text .= "\ndetail.conference_id ".$detail->get('conference_id');
        $text .= "\ndetail.presentation_type "
            .$detail->get('presentation_type');
        foreach ($import_item->getVar('author') as $author) {
            $text .= "\ndetail.author ".$author->get('author');
        }
        $text .= "\ndetail.conference_title "
            .$detail->get('conference_title');
        $text .= "\ndetail.place "
            .$detail->get('place');
        $text .= "\ndetail.abstract "
            .$detail->get('abstract');
        $text .= "\ndetail.conference_from_year "
            .$detail->get('conference_from_year');
        $text .= "\ndetail.conference_from_month "
            .$detail->get('conference_from_month');
        $text .= "\ndetail.conference_from_mday "
            .$detail->get('conference_from_mday');
        $text .= "\ndetail.conference_to_year "
            .$detail->get('conference_to_year');
        $text .= "\ndetail.conference_to_month "
            .$detail->get('conference_to_month');
        $text .= "\ndetail.conference_to_mday "
            .$detail->get('conference_to_mday');
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
            $conference_file = &$item->getVar('conference_file');
            if ($item->hasConferenceFile()) {
                $file_handler = &xoonips_getormhandler('xoonips', 'file');
                $clonefile = &$file_handler->fileClone($conference_file);
                $clonefile->setDirty();
                $item->setVar('conference_file', $clonefile);
            }

            //copy attachment file
            $conference_paper = &$item->getVar('conference_paper');
            if ($item->hasConferencePaper()) {
                $file_handler = &xoonips_getormhandler('xoonips', 'file');
                $clonefile = &$file_handler->fileClone($conference_paper);
                $clonefile->setDirty();
                $item->setVar('conference_paper', $clonefile);
            }
        }
        parent::import($item);
    }
}
