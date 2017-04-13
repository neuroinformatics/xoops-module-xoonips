<?php

// $Revision: 1.1.4.1.2.20 $
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

// for xnpGetFileInfo
require_once dirname(dirname(__DIR__)).'/include/lib.php';

/**
 * @brief data object of file
 *
 * @li getVar('file_id') :
 * @li getVar('item_id') :
 * @li getVar('original_file_name') :
 * @li getVar('mime_type') :
 * @li getVar('file_name') :
 * @li getVar('file_size') :
 * @li getVar('thumbnail_file') :
 * @li getVar('caption') :
 * @li getVar('sess_id') :
 * @li getVar('file_type_id') :
 * @li getVar('search_module_name') :
 * @li getVar('search_module_version') :
 * @li getVar('header') :
 * @li getVar('timestamp') :
 * @li getVar('is_deleted') :
 * @li getVar('download_count') :
 */
class XooNIpsOrmFile extends XooNIpsTableObject
{
    /**
     * file path string of this file.
     */
    public $filepath = null;

    public function XooNIpsOrmFile()
    {
        parent::XooNIpsTableObject();
        $this->initVar('file_id', XOBJ_DTYPE_INT, 0, false);
        $this->initVar('item_id', XOBJ_DTYPE_INT, null, false);
        $this->initVar('original_file_name', XOBJ_DTYPE_TXTBOX, null, true, 255);
        $this->initVar('mime_type', XOBJ_DTYPE_TXTBOX, null, false, 255);
        $this->initVar('file_name', XOBJ_DTYPE_TXTBOX, null, false, 10);
        $this->initVar('file_size', XOBJ_DTYPE_INT, null, false);
        $this->initVar('thumbnail_file', XOBJ_DTYPE_OTHER, null, false, 65535);
        $this->initVar('caption', XOBJ_DTYPE_TXTBOX, null, false, 255);
        $this->initVar('sess_id', XOBJ_DTYPE_TXTBOX, null, false, 32);
        $this->initVar('file_type_id', XOBJ_DTYPE_INT, null, true);
        $this->initVar('search_module_name', XOBJ_DTYPE_TXTBOX, null, false, 255);
        $this->initVar('search_module_version', XOBJ_DTYPE_OTHER, null, false);
        $this->initVar('header', XOBJ_DTYPE_TXTBOX, null, false, 32);
        $this->initVar('timestamp', XOBJ_DTYPE_TXTBOX, null, false, 19);
        $this->initVar('is_deleted', XOBJ_DTYPE_INT, 0, false);
        $this->initVar('download_count', XOBJ_DTYPE_INT, 0, false);
    }

    public function setFilepath($path)
    {
        $this->filepath = $path;
    }

    public function getFilepath()
    {
        return $this->filepath;
    }
}

/**
 * @brief data object of file
 */
class XooNIpsOrmFileHandler extends XooNIpsTableObjectHandler
{
    public function XooNIpsOrmFileHandler(&$db)
    {
        parent::XooNIpsTableObjectHandler($db);
        $this->__initHandler('XooNIpsOrmFile', 'xoonips_file', 'file_id');
    }

    /**
     * @todo define upload_dir key name
     */
    public function createFilepath($file)
    {
        // copy file to $upload_dir
        $xconfig_handler = &xoonips_getormhandler('xoonips', 'config');
        $upload_dir = $xconfig_handler->getValue('upload_dir');
        if (substr($upload_dir, -1) == '/') {
            $dstfilepath = $upload_dir.$file->get('file_id');
        } else {
            $dstfilepath = $upload_dir.'/'.$file->get('file_id');
        }

        return $dstfilepath;
    }

    /**
     * insert a metadata of file and move a file to sysytem's upload_dir.
     *
     * @param XooNIpsOrmFile $file  file to be inserted
     * @param bool           $force force insertion flag
     *
     * @return bool false if failure
     */
    public function insert(&$file, $force = false)
    {
        if (!$file->isDirty()) {
            // nothing to do
            return true;
        }
        // fill thumbnail field if empty
        if ($file->isNew()) {
            $thumbnail = $file->get('thumbnail_file');
            if (empty($thumbnail)) {
                $file_path = $file->getFilepath();
                $mimetype = $file->get('mime_type');
                $fileutil = &xoonips_getutility('file');
                $thumbnail = $fileutil->get_thumbnail($file_path, $mimetype);
                if (!empty($thumbnail)) {
                    $file->set('thumbnail_file', $thumbnail);
                }
            }
        }
        if (parent::insert($file, $force)) {
            if (!$file->isNew()) {
                // no need to move file if update
                return true;
            }
            if (!$file->get('is_deleted')) {
                // move file in the only case of not deleted file
                return $this->moveFile($file);
            } else {
                return true;
            }
        }

        return false;
    }

    /**
     * delete a metadata of file and remove a file.
     *
     * @param XooNIpsOrmFile $file file to delete
     *
     * @return bool false if failure
     */
    public function delete(&$file)
    {
        if (parent::delete($file)) {
            return true;
        }

        return false;
    }

    /**
     * get a metadata of file. returned object has a file path of this file.
     *
     * @param XooNIpsOrmFile $file file to be inserted
     *
     * @return bool false if failure
     */
    public function &get($id)
    {
        $file = parent::get($id);
        if ($file) {
            $file->setFilepath($this->createFilepath($file));
        }

        return $file;
    }

    /**
     * gets File objects join xoonips_file_type.
     *
     * @param object              $criteria
     * @param bool                $id_as_key
     * @param string              $fieldlist fieldlist for distinct select
     * @param bool                $distinct
     * @param XooNIpsJoinCriteria $joindef   join criteria object
     *
     * @return array objects
     */
    public function &getObjects($criteria = null, $id_as_key = false, $fieldlist = '', $distinct = false, $joindef = null)
    {
        if (is_null($joindef) || !(is_subclass_of($joindef, 'xoonipsjoincriteria') || strtolower(get_class($joindef)) == 'xoonipsjoincriteria')) {
            $joindef = new XooNIpsJoinCriteria('xoonips_file_type', 'file_type_id', 'file_type_id');
        } else {
            $joindef->cascade(new XooNIpsJoinCriteria('xoonips_file_type', 'file_type_id', 'file_type_id'), 'xoonips_file');
        }
        $table = $this->db->prefix('xoonips_file');
        if (trim($fieldlist) == '') {
            $fieldlist = "$table.file_id, $table.item_id, $table.original_file_name, $table.mime_type, $table.file_name, $table.file_size, $table.thumbnail_file, $table.caption, $table.sess_id, $table.file_type_id, $table.search_module_name, $table.search_module_version, $table.header, $table.timestamp, $table.is_deleted, $table.download_count";
        }
        $files = parent::getObjects($criteria, $id_as_key, $fieldlist, $distinct, $joindef);
        if ($files) {
            for ($i = 0; $i < count($files); ++$i) {
                $files[$i]->setFilepath($this->createFilepath($files[$i]));
            }
        }

        return $files;
    }

    /**
     * move file
     * $file must have file id.
     *
     * @todo define upload_dir key name
     *
     * @param XooNIpsOrmFile $file XooNIpsOrmFile object
     *
     * @return bool false if fault
     */
    public function moveFile($file)
    {
        $file_id = $file->get('file_id');
        if (empty($file_id)) {
            return false;
        }
        if (rename($file->getFilepath(), $this->createFilepath($file))) {
            $file->setFilepath($this->createFilepath($file));

            return true;
        }
        trigger_error('can\'t move file: file_id='.$file->get('file_id'));

        return false;
    }

    /**
     * delete file
     * $file must have file id.
     * Notice that the function returns true if file has been already deleted.
     *
     * @param XooNIpsOrmFile $file XooNIpsOrmFile object
     *
     * @return bool false if file id is empty
     */
    public function deleteFile($file)
    {
        $file_id = $file->get('file_id');
        if (!file_exists($this->createFilepath($file))) {
            return true;
        }
        if (empty($file_id)) {
            return false;
        }
        if (unlink($this->createFilepath($file))) {
            return true;
        }

        return false;
    }

    /**
     * clone file information and copy file.
     *
     * @param XooNIpsOrmFile $file XooNIpsOrmFile object
     *
     * @return reference of cloned file orm object
     */
    public function &fileClone($file)
    {
        // clone file orm
        $copyfile = &$file->xoopsClone();
        $copyfile->setFilepath(tempnam('/tmp', 'XNP'));

        // copy file
        copy($this->createFilepath($file), $copyfile->getFilepath());

        return $copyfile;
    }

    /**
     * get total download count of specified file type of item.
     *
     * @param int $item_id          item id to get total download count
     * @param int $file_type_nameid file id to get total download count
     * @param int total donwload count
     */
    public function getTotalDownloadCount($item_id, $file_type_name)
    {
        list($tmp) = xnpGetFileInfo('sum(t_file.download_count)', 't_file_type.name=\''.addslashes($file_type_name).'\' and sess_id is NULL ', $item_id);

        return $tmp[0];
    }

    /**
     * get total file size of items.
     *
     * @param array item_ids item_id[]
     *
     * @return int size(bytes)
     */
    public function getTotalSizeOfItems($item_ids)
    {
        $total_size = 0;
        if (count($item_ids)) {
            $criteria = new CriteriaCompo();
            $criteria->add(new Criteria('item_id', '('.implode(',', $item_ids).')', 'in'));
            $criteria->add(new Criteria('is_deleted', 0));
            $files = &$this->getObjects($criteria);
            foreach ($files as $file) {
                $total_size += $file->get('file_size');
            }
        }

        return $total_size;
    }
}
