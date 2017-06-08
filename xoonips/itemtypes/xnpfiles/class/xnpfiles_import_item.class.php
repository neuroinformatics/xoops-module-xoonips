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

require_once dirname(dirname(__DIR__)).'/xoonips/class/xoonips_import_item.class.php';

class XNPFilesImportItem extends XooNIpsImportItem
{
    public $_has_files_file = false;

    public function __construct()
    {
        $handler = &xoonips_getormcompohandler('xnpfiles', 'item');
        $this->_item = &$handler->create();
    }

    public function setHasFilesFile()
    {
        $this->_has_files_file = true;
    }

    public function unsetHasFilesFile()
    {
        $this->_has_files_file = false;
    }

    public function hasFilesFile()
    {
        return $this->_has_files_file;
    }

    /**
     * get total file size(bytes) of this item.
     *
     * @return int file size in bytes
     */
    public function getTotalFileSize()
    {
        $file = &$this->getVar('files_file');
        if (!$file) {
            return 0;
        }

        return $file->get('file_size');
    }

    public function &getClone()
    {
        $clone = &parent::getClone();
        $clone->_has_files_file = $this->_has_files_file;

        return $clone;
    }
}

class XNPFilesImportItemHandler extends XooNIpsImportItemHandler
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
    public $_files_file = null;

    /**
     * flag of attachment file parsed.
     */
    public $_files_file_flag = false;

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
        return new XNPFilesImportItem();
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
            break;
        case 'ITEM/DETAIL/FILE':
            if ($this->_files_file_flag) {
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
            $this->_files_file = &$file_handler->create();
            $this->_files_file->setFilepath($this->_attachment_dir.'/'.$attribs['FILE_NAME']);
            $this->_files_file->set('original_file_name', $unicode->decode_utf8($attribs['ORIGINAL_FILE_NAME'], xoonips_get_server_charset(), 'h'));
            $this->_files_file->set('mime_type', $attribs['MIME_TYPE']);
            $this->_files_file->set('file_size', $attribs['FILE_SIZE']);
            $this->_files_file->set('sess_id', session_id());
            $this->_files_file->set('file_type_id', $file_type[0]->get('file_type_id'));
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
            $keys = array(
                'data_file_name',
                'data_file_mimetype',
                'data_file_filetype',
            );
            foreach ($keys as $key) {
                if (is_null($detail->get($key, 'n'))) {
                    $this->_import_item->setErrors(E_XOONIPS_TAG_NOT_FOUND, " no $key".$this->_get_parser_error_at());
                }
            }
            break;
        case 'ITEM/DETAIL/DATA_FILE_NAME':
            $detail->set('data_file_name', $unicode->decode_utf8($this->_cdata, xoonips_get_server_charset(), 'h'), true);
            break;
        case 'ITEM/DETAIL/DATA_FILE_MIMETYPE':
            $detail->set('data_file_mimetype', $unicode->decode_utf8($this->_cdata, xoonips_get_server_charset(), 'h'), true);
            break;
        case 'ITEM/DETAIL/DATA_FILE_FILETYPE':
            $detail->set('data_file_filetype', $unicode->decode_utf8($this->_cdata, xoonips_get_server_charset(), 'h'), true);
            break;
        case 'ITEM/DETAIL/FILE':
            $this->_files_file_flag = true;
            $file_handler = &xoonips_getormhandler('xoonips', 'file');
            if (!$file_handler->insert($this->_files_file)) {
                $this->_import_item->setErrors(E_XOONIPS_DB_QUERY, "can't insert attachment file:".$this->_files_file->get('original_file_name').$this->_get_parser_error_at());
            }
            $this->_files_file = $file_handler->get($this->_files_file->get('file_id'));
            $this->_import_item->setVar('files_file', $this->_files_file);
            $this->_import_item->setHasFilesFile();
            break;
        case 'ITEM/DETAIL/FILE/CAPTION':
            $this->_files_file->set('caption', $unicode->decode_utf8($this->_cdata, xoonips_get_server_charset(), 'h'));
            break;
        case 'ITEM/DETAIL/FILE/THUMBNAIL':
            $this->_files_file->set('thumbnail_file', base64_decode($this->_cdata));
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
        if ('xnpfilesimportitem' != strtolower(get_class($item))) {
            return;
        }

        $this->_set_file_delete_flag($item);

        // nothing to do if no file
        if ($item->hasFilesFile()) {
            $files_file = &$item->getVar('files_file');
            $this->_fix_item_id_of_file($item, $files_file);
            $this->_create_text_search_index($files_file);
        }

        parent::onImportFinished($item, $import_items);
    }

    public function insert(&$item)
    {
        $handler = &xoonips_getormcompohandler('xnpfiles', 'item');

        return $handler->insert($item);
    }

    public function setNew(&$item)
    {
        $handler = &xoonips_getormcompohandler('xnpfiles', 'item');

        return $handler->setNew($item);
    }

    public function unsetNew(&$item)
    {
        $handler = &xoonips_getormcompohandler('xnpfiles', 'item');

        return $handler->unsetNew($item);
    }

    public function setDirty(&$item)
    {
        $handler = &xoonips_getormcompohandler('xnpfiles', 'item');

        return $handler->setDirty($item);
    }

    public function unsetDirty(&$item)
    {
        $handler = &xoonips_getormcompohandler('xnpfiles', 'item');

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
        $text .= "\ndetail.data_file_name "
            .$detail->get('data_file_name');
        $text .= "\ndetail.data_file_mimetype "
            .$detail->get('data_file_mimetype');
        $text .= "\ndetail.data_file_filetype "
            .$detail->get('data_file_filetype');

        return $text;
    }

    public function import(&$item)
    {
        if ($item->getUpdateFlag()) {
            $detail = &$item->getVar('detail');
            $detail->unsetNew();
            $detail->setDirty();

            // copy attachment file
            $files_file = &$item->getVar('files_file');
            if ($item->hasFilesFile()) {
                $file_handler = &xoonips_getormhandler('xoonips', 'file');
                $clonefile = &$file_handler->fileClone($files_file);
                $clonefile->setDirty();
                $item->setVar('files_file', $clonefile);

                $files_file = &$item->getVar('files_file');
            }
        }
        parent::import($item);
    }
}
