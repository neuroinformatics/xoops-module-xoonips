<?php

// $Revision: 1.1.2.9 $
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

class XNPDataImportItem extends XooNIpsImportItem
{
    public $_has_data_file = false;
    public $_has_preview = false;

    public function XNPDataImportItem()
    {
        $handler = &xoonips_getormcompohandler('xnpdata', 'item');
        $this->_item = &$handler->create();
    }

    public function setHasDataFile()
    {
        $this->_has_data_file = true;
    }

    public function unsetHasDataFile()
    {
        $this->_has_data_file = false;
    }

    public function hasDataFile()
    {
        return $this->_has_data_file;
    }

    public function setHasPreview()
    {
        $this->_has_preview = true;
    }

    public function unsetHasPreview()
    {
        $this->_has_preview = false;
    }

    public function hasPreview()
    {
        return $this->_has_preview;
    }

    /**
     * get total file size(bytes) of this item.
     *
     * @return int file size in bytes
     */
    public function getTotalFileSize()
    {
        $size = 0;
        $mainfile = &$this->getVar('data_file');
        if (!$mainfile) {
            return 0;
        }
        $size = $mainfile->get('file_size');
        foreach ($this->getVar('preview') as $preview) {
            $size += $preview->get('file_size');
        }

        return $size;
    }

    public function &getClone()
    {
        $clone = &parent::getClone();
        $clone->_has_data_file = $this->_has_data_file;
        $clone->_has_preview = $this->_has_preview;

        return $clone;
    }
}

class XNPDataImportItemHandler extends XooNIpsImportItemHandler
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
    public $_data_file = null;

    /**
     * flag of attachment file parsed.
     */
    public $_data_file_flag = false;

    /**
     * attachment file object(XooNIpsFile).
     */
    public $_preview = null;

    /**
     * flag of attachment file parsed.
     */
    public $_preview_flag = false;

    /**
     * attachment_dl_limit flag.
     */
    public $_attachment_dl_limit_flag = false;

    /**
     * attachment_dl__notify_limit flag.
     */
    public $_attachment_dl_notify_limit_flag = false;

    public $_file_type_attribute = null;

    public function XNPDataImportItemHandler()
    {
        parent::XooNIpsImportItemHandler();
    }

    public function create()
    {
        return new XNPDataImportItem();
    }

    /**
     * parser start element handler(see php manual for detail).
     */
    public function xmlStartElementHandler($parser, $name, $attribs)
    {
        global $xoopsDB;
        parent::xmlStartElementHandler($parser, $name, $attribs);

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
                        'unsupported version('
                        .$attribs['VERSION'].') '
                        .$this->_get_parser_error_at());
                }
            } else {
                $this->_detail_version = '1.00';
            }
            break;
            if ($this->_conference_file_flag) {
                $this->_import_item->setErrors(
                    E_XOONIPS_ATTACHMENT_HAS_REDUNDANT,
                    "multiple $name attachments is not allowed"
                    .$this->_get_parser_error_at());
                break;
            }
            $file_type_handler = &xoonips_getormhandler('xoonips',
                                                         'file_type');
            $file_handler = &xoonips_getormhandler('xoonips', 'file');
            $criteria = new Criteria(
                'name', addslashes($attribs['FILE_TYPE_NAME']));
            $file_type = &$file_type_handler->getObjects($criteria);
            if (count($file_type) == 0) {
                $this->_import_item->setErrors(
                    E_XOONIPS_ATTR_NOT_FOUND,
                    'file_type_name is not found:'
                    .$attribs['FILE_TYPE_NAME']
                    .$this->_get_parser_error_at());
                break;
            }

        case 'ITEM/DETAIL/FILE':
            $this->_file_type_attribute = $attribs['FILE_TYPE_NAME'];

            $file_type_handler = &xoonips_getormhandler('xoonips',
                                                         'file_type');
            $file_handler = &xoonips_getormhandler('xoonips', 'file');
            $criteria = new Criteria(
                'name', addslashes($attribs['FILE_TYPE_NAME']));
            $file_type = &$file_type_handler->getObjects($criteria);
            if (count($file_type) == 0) {
                $this->_import_item->setErrors(
                    E_XOONIPS_ATTR_NOT_FOUND,
                    'file_type_name is not found:'
                    .$attribs['FILE_TYPE_NAME']
                    .$this->_get_parser_error_at());
            }
            if (strstr($attribs['FILE_NAME'], '..')) {
                $this->_import_item->setErrors(
                    E_XOONIPS_ATTR_INVALID_VALUE,
                    'invalid file_name attribute:'
                    .$attribs['FILE_NAME']
                    .$this->_get_parser_error_at());
            }
            $unicode = &xoonips_getutility('unicode');
            if ($this->_file_type_attribute == 'data_file') {
                if ($this->_data_file_flag) {
                    $this->_import_item->setErrors(
                        E_XOONIPS_ATTACHMENT_HAS_REDUNDANT,
                        "multiple $name attachments is not allowed"
                        .$this->_get_parser_error_at());
                    break;
                }
                $this->_data_file = &$file_handler->create();
                $this->_data_file->setFilepath(
                    $this->_attachment_dir.'/'.$attribs['FILE_NAME']);
                $this->_data_file->set(
                    'original_file_name',
                    $unicode->decode_utf8($attribs['ORIGINAL_FILE_NAME'],
                                          xoonips_get_server_charset(), 'h'));
                $this->_data_file->set('mime_type',
                                            $attribs['MIME_TYPE']);
                $this->_data_file->set('file_size',
                                            $attribs['FILE_SIZE']);
                $this->_data_file->set('sess_id', session_id());
                $this->_data_file->set('file_type_id',
                                            $file_type[0]->get(
                                                'file_type_id'));
            } elseif ($this->_file_type_attribute == 'preview') {
                $this->_preview = &$file_handler->create();
                $this->_preview->setFilepath(
                    $this->_attachment_dir.'/'.$attribs['FILE_NAME']);
                $this->_preview->set(
                    'original_file_name',
                    $unicode->decode_utf8($attribs['ORIGINAL_FILE_NAME'],
                                          xoonips_get_server_charset(), 'h'));
                $this->_preview->set('mime_type', $attribs['MIME_TYPE']);
                $this->_preview->set('file_size', $attribs['FILE_SIZE']);
                $this->_preview->set('sess_id', session_id());
                $this->_preview->set('file_type_id',
                                          $file_type[0]->get(
                                              'file_type_id'));
            } else {
                $this->_import_item->setErrors(
                    E_XOONIPS_ATTR_NOT_FOUND,
                    'file_type_name is not found:'
                    .$this->_file_type_attribute
                    .$this->_get_parser_error_at());
                break;
            }
            break;
        }
    }

    /**
     * parser end element handler(see php manual for detail).
     */
    public function xmlEndElementHandler($parser, $name)
    {
        global $xoopsDB;
        $detail = &$this->_import_item->getVar('detail');
        $unicode = &xoonips_getutility('unicode');

        switch (implode('/', $this->_tag_stack)) {
        case 'ITEM/DETAIL':
            foreach (array('data_type', 'rights', 'readme', 'use_cc', 'cc_commercial_use', 'cc_modification') as $key) {
                if (is_null($detail->get($key, 'n'))) {
                    $this->_import_item->setErrors(
                        E_XOONIPS_TAG_NOT_FOUND,
                        " no $key ".$this->_get_parser_error_at());
                }
            }
            //error if no experimenters
            if (count($this->_import_item->getVar('experimenter')) == 0) {
                $this->_import_item->setErrors(E_XOONIPS_TAG_NOT_FOUND, ' no experimenter'
                                                   .$this->_get_parser_error_at());
            }
            break;
        case 'ITEM/DETAIL/EXPERIMENTERS/EXPERIMENTER':
            if ($this->_detail_version != '1.03') {
                break;
            }
            $experimenters = &$this->_import_item->getVar('experimenter');

            $experimenter_handler = &xoonips_getormhandler('xnpdata', 'experimenter');
            $experimenter = &$experimenter_handler->create();

            $experimenter->set('experimenter', $unicode->decode_utf8($this->_cdata,
                                                         xoonips_get_server_charset(), 'h'));
            $experimenter->set('experimenter_order', count($experimenters));

            $experimenters[] = $experimenter;
            break;
        case 'ITEM/DETAIL/EXPERIMENTER':
            if ($this->_detail_version != '1.00'
                && $this->_detail_version != '1.01'
                && $this->_detail_version != '1.02') {
                //<experimenter> is only for 1.00, 1.01 and 1.02
                break;
            }
            $experimenter_handler = &xoonips_getormhandler('xnpdata', 'experimenter');
            $experimenters = &$this->_import_item->getVar('experimenter');
            $experimenter = &$experimenter_handler->create();
            $experimenter->set('experimenter', trim($unicode->decode_utf8($this->_cdata,
                                                              xoonips_get_server_charset(), 'h')));
            $experimenter->set('experimenter_order', 0);
            $experimenters[0] = $experimenter;
            break;
        case 'ITEM/DETAIL/DATA_TYPE':
        case 'ITEM/DETAIL/RIGHTS':
        case 'ITEM/DETAIL/README':
        case 'ITEM/DETAIL/USE_CC':
        case 'ITEM/DETAIL/CC_COMMERCIAL_USE':
        case 'ITEM/DETAIL/CC_MODIFICATION':
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
            if ($this->_file_type_attribute == 'data_file') {
                $this->_data_file_flag = true;
                if (!$file_handler->insert($this->_data_file)) {
                    $this->_import_item->setErrors(
                        E_XOONIPS_DB_QUERY,
                        "can't insert attachment file:"
                        .$this->_data_file->get('original_file_name')
                        .$this->_get_parser_error_at());
                }
                $this->_data_file = $file_handler->get(
                    $this->_data_file->get('file_id'));
                $this->_import_item->setVar('data_file',
                                                 $this->_data_file);
                $this->_import_item->setHasDataFile();
                $this->_file_type_attribute = null;
            } elseif ($this->_file_type_attribute == 'preview') {
                $this->_preview_flag = true;
                if (!$file_handler->insert($this->_preview)) {
                    $this->_import_item->setErrors(
                        E_XOONIPS_DB_QUERY, "can't insert attachment file:"
                        .$this->_preview->get('original_file_name')
                        .$this->_get_parser_error_at());
                }
                $this->_preview = $file_handler->get(
                    $this->_preview->get('file_id'));
                $previews = &$this->_import_item->getVar('preview');
                $previews[] = $this->_preview;
                $this->_import_item->setHasPreview();
                $this->_file_type_attribute = null;
            } else {
                die('unknown file type:'.$this->_file_type_attribute);
            }
            break;

        case 'ITEM/DETAIL/FILE/CAPTION':
            $unicode = &xoonips_getutility('unicode');
            if ($this->_file_type_attribute == 'data_file') {
                $this->_data_file->set(
                    'caption',
                    $unicode->decode_utf8(
                        $this->_cdata, xoonips_get_server_charset(), 'h'));
            } elseif ($this->_file_type_attribute == 'preview') {
                $this->_preview->set(
                    'caption',
                    $unicode->decode_utf8(
                        $this->_cdata, xoonips_get_server_charset(), 'h'));
            }
            break;
        case 'ITEM/DETAIL/FILE/THUMBNAIL':
            if ($this->_file_type_attribute == 'data_file') {
                $this->_data_file->set('thumbnail_file',
                                            base64_decode($this->_cdata));
            } elseif ($this->_file_type_attribute == 'preview') {
                $this->_preview->set('thumbnail_file',
                                          base64_decode($this->_cdata));
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
        if ('xnpdataimportitem' != strtolower(get_class($item))) {
            return;
        }

        $this->_set_file_delete_flag($item);

        // nothing to do if no data file
        if ($item->hasDataFile()) {
            $data_file = &$item->getVar('data_file');
            $this->_fix_item_id_of_file($item, $data_file);
            $this->_create_text_search_index($data_file);
        }
//         foreach (
//           array(
//             'experimenter'             ,
//           ) as $name ){
//             $rows = $this->getVar('detail_'.$name);
//             $cleans = array();
//             foreach ( $rows as $val ){
//                 $ar = $this->stripSurplusString( $this->childColumnLengths[strtoupper($name)], array( $name => $val ) );
//                 if ( count( $ar ) ){
//                     $this -> setErrors( E_XOONIPS_DATA_TOO_LONG, "detail $name is too long :" . $val . $this -> getErrorAt( __LINE__, __FILE__, __FUNCTION__ ) );
//                     $cleans[] = $ar[$name];
//                     $retval = false;
//                 }
//                 else
//                     $cleans[] = $val;
//             }
//             $this->cleanVars['detail_'.$name] = $cleans;
//         }

        // nothing to do if no previews
        $previews = &$item->getVar('preview');
        foreach (array_keys($previews) as $key) {
            if ($previews[$key]->get('file_id') > 0) {
                $this->_fix_item_id_of_file($item, $previews[$key]);
                $this->_create_text_search_index($previews[$key]);
            }
        }

        parent::onImportFinished($item, $import_items);
    }

    public function insert(&$item)
    {
        $handler = &xoonips_getormcompohandler('xnpdata', 'item');

        return $handler->insert($item);
    }

    public function setNew(&$item)
    {
        $handler = &xoonips_getormcompohandler('xnpdata', 'item');

        return $handler->setNew($item);
    }

    public function unsetNew(&$item)
    {
        $handler = &xoonips_getormcompohandler('xnpdata', 'item');

        return $handler->unsetNew($item);
    }

    public function setDirty(&$item)
    {
        $handler = &xoonips_getormcompohandler('xnpdata', 'item');

        return $handler->setDirty($item);
    }

    public function unsetDirty(&$item)
    {
        $handler = &xoonips_getormcompohandler('xnpdata', 'item');

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

        $text .= "\ndetail.data_type ".$detail->get('data_type', 'n');
        foreach ($import_item->getVar('experimenter') as $experimenter) {
            $text .= "\ndetail.experimenter ".$experimenter->get('experimenter');
        }
        $text .= "\ndetail.readme ".mb_ereg_replace(
            '\n', '\n', mb_ereg_replace(
                '\\\\', '\\\\', $detail->get('readme', 'n')));
        $text .= "\ndetail.rights ".mb_ereg_replace(
            '\n', '\n', mb_ereg_replace(
                '\\\\', '\\\\', $detail->get('rights', 'n')));
        $text .= "\ndetail.use_cc ".$detail->get('use_cc', 'n');
        $text .= "\ndetail.cc_commercial_use "
            .$detail->get('cc_commercial_use', 'n');
        $text .= "\ndetail.cc_modification "
            .$detail->get('cc_modification', 'n');
        $text .= "\ndetail.attachment_dl_limit "
            .$detail->get('attachment_dl_limit', 'n');
        $text .= "\ndetail.attachment_dl_notify "
            .$detail->get('attachment_dl_notify', 'n');

        return $text;
    }

    public function import(&$item)
    {
        if ($item->getUpdateFlag()) {
            $detail = &$item->getVar('detail');
            $detail->unsetNew();
            $detail->setDirty();

            //copy attachment file
            if ($item->hasDataFile()) {
                $data_file = &$item->getVar('data_file');
                $file_handler = &xoonips_getormhandler('xoonips', 'file');
                $clonefile = &$file_handler->fileClone($data_file);
                $clonefile->setDirty();
                $item->setVar('data_file', $clonefile);
            }
            if ($item->hasPreview()) {
                $file_handler = &xoonips_getormhandler('xoonips', 'file');
                $previews = array();
                foreach ($item->getVar('preview') as $preview) {
                    $clonefile = &$file_handler->fileClone($preview);
                    $clonefile->setDirty();
                    $previews[] = &$preview;
                }
                $item->setVar('preview', $previews);
            }
        }
        parent::import($item);
    }
}
