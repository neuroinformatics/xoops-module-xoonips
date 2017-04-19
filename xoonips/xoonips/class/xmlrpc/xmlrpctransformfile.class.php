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

require_once XOOPS_ROOT_PATH.'/modules/xoonips/class/orm/file.class.php';
class XooNIpsXmlRpcTransformFile extends XooNIpsXmlRpcTransformElement
{
    public function getObject($array)
    {
        $obj = new XooNIpsOrmFile();
        //$file_handler=&xoonips_getormhandler('xoonips', 'file');
        //$obj=&$file_handler->create();
        //
        // filetype to file_type_id
        $file_type_handler = &xoonips_getormhandler('xoonips', 'file_type');
        $filetypes = &$file_type_handler->getObjects(new Criteria('name', $array['filetype']));
        if (!$filetypes || count($filetypes) != 1) {
            return false;
        }

        $unicode = &xoonips_getutility('unicode');
        $obj->assignVar('file_id', $array['id']);
        $obj->assignVar('file_type_id', $filetypes[0]->get('file_type_id'));
        $obj->assignVar('original_file_name', $unicode->decode_utf8($array['originalname'], xoonips_get_server_charset(), 'h'));
        $obj->assignVar('file_size', intval($array['size']));
        $obj->assignVar('mime_type', $array['mimetype']);
        $obj->assignVar('caption', $unicode->decode_utf8($array['caption'], xoonips_get_server_charset(), 'h'));
        $obj->assignVar('thumbnail_file', $array['thumbnail']);

        return $obj;
    }
}
