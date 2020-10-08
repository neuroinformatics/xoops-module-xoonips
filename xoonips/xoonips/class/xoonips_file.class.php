<?php

// ------------------------------------------------------------------------- //
//  XooNIps - Neuroinformatics Base Platform System                          //
//  Copyright (C) 2005-2013 RIKEN, Japan All rights reserved.                //
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

/**
 * XooNIps File Handler Class.
 */
class XooNIpsFileHandler
{
    /**
     * orm handler of xoonips_file table.
     *
     * @var object
     */
    public $xf_handler;

    /**
     * orm handler of xoonips_search_text table.
     *
     * @var object
     */
    public $xst_handler;

    /**
     * file uploaded path.
     *
     * @var string
     */
    public $upload_dir;

    /**
     * constractor.
     */
    public function __construct()
    {
        $this->xf_handler = &xoonips_getormhandler('xoonips', 'file');
        $this->xst_handler = &xoonips_getormhandler('xoonips', 'search_text');
        $xc_handler = &xoonips_getormhandler('xoonips', 'config');
        $this->upload_dir = $xc_handler->getValue('upload_dir');
        if (strlen($this->upload_dir) > 1 && '/' == substr($this->upload_dir, -1)) {
            $this->upload_dir = substr($this->upload_dir, 0, strlen($this->upload_dir) - 1);
        }
    }

    /**
     * get file path.
     *
     * @param int $file_id file id
     *
     * @return string uploaded file path
     */
    public function getFilePath($file_id)
    {
        $file_path = $this->upload_dir.'/'.$file_id;

        return $file_path;
    }

    /**
     * get files info by item_id and file_type_name.
     *
     * @param int    $item_id        item id
     * @param string $file_type_name file type name
     *
     * @return array files info
     */
    public function getFilesInfo($item_id, $file_type_name = false)
    {
        $join = new XooNIpsJoinCriteria('xoonips_file_type', 'file_type_id', 'file_type_id', 'INNER', 'ft');
        $criteria = new CriteriaCompo(new Criteria('item_id', $item_id));
        $criteria->add(new Criteria('is_deleted', 0));
        if (false !== $file_type_name) {
            $criteria->add(new Criteria('name', $file_type_name, '=', 'ft'));
        }
        $files_info = array();
        $res = &$this->xf_handler->open($criteria, '', false, $join);
        while ($xf_obj = &$this->xf_handler->getNext($res)) {
            $info = $xf_obj->getArray();
            $info['file_path'] = $this->getFilePath($info['file_id']);
            $info['file_url'] = XOONIPS_URL.'/download.php?file_id='.$info['file_id'];
            $info['image_url'] = XOONIPS_URL.'/image.php?file_id='.$info['file_id'];
            $files_info[] = $info;
        }
        $this->xf_handler->close($res);

        return $files_info;
    }

    /**
     * get file id by doi - NTTDK and Keio University 20080825.
     *
     * @param string $doi doi parameter
     *
     * @return int file id
     */
    public function getFileIdByDOI($doi)
    {
        if (empty($doi)) {
            return false;
        }
        $join = new XooNIpsJoinCriteria('xoonips_item_basic', 'item_id', 'item_id', 'INNER', 'ib');
        $criteria = new CriteriaCompo(new Criteria('is_deleted', 0));
        $criteria->setSort('file_id');
        $separate_param = XNP_CONFIG_DOWNLOAD_DOI_FIELD_SEPARATE_PARAM;
        if (preg_match('/'.preg_quote($separate_param, '/').'/', $doi)) {
            $tmp = explode($separate_param, $doi, 2);
            $doi = $tmp[0];
            if (preg_match('/[1-9]/', intval($tmp[1]))) {
                $start = intval($tmp[1]) - 1;
                $criteria->setStart($start);
                $criteria->setLimit($start + 1);
            }
        }
        $mimetypes = explode(',', XNP_CONFIG_DOWNLOAD_FILE_TYPE_LIMIT);
        $mimetypes = array_map(array(&$GLOBALS['xoopsDB'], 'quoteString'), $mimetypes);
        $criteria->add(new Criteria('mime_type', '('.implode(',', $mimetypes).')', 'IN'));
        $criteria->add(new Criteria('doi', $doi, '=', 'ib'));
        $xf_objs = &$this->xf_handler->getObjects($criteria, false, 'file_id', false, $join);
        if (0 == count($xf_objs)) {
            return false;
        }
        $file_id = $xf_objs[0]->get('file_id');

        return $file_id;
    }
}
