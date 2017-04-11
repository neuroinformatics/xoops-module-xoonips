<?php

// $Revision: 1.1.4.1.2.6 $
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

include_once XOOPS_ROOT_PATH.'/modules/xoonips/class/xmlrpc/view/xmlrpcview.class.php';
include_once XOOPS_ROOT_PATH.'/modules/xoonips/class/xmlrpc/xmlrpcfault.class.php';

/**
 * @brief Class that generate response of XML-RPC getFile request
 */
class XooNIpsXmlRpcViewGetFile extends XooNIpsXmlRpcViewElement
{
    /**
     * @brief return XoopsXmlRpcTag that has response of this request
     *
     * @return XoopsXmlRpcTag
     */
    public function render()
    {
        $file = $this->response->getSuccess();
        $filetype_handler = &xoonips_getormhandler('xoonips', 'file_type');
        $filetype = $filetype_handler->get($file->get('file_type_id'));
        if (!$filetype) {
            return new XooNIpsXmlRpcFault(106, 'file_type not found: id='.$file->get('file_type_id'));
        }
        if (!file_exists($file->getFilepath())) {
            return new XooNIpsXmlRpcFault(106, 'file not found: id='.$file->get('file_type_id'));
        }
        $resp = new XoopsXmlRpcStruct();
        $resp->add('id', new XoopsXmlRpcInt($file->get('file_id')));
        $resp->add('filetype', new XoopsXmlRpcString($filetype->get('name')));
        $unicode = &xoonips_getutility('unicode');
        $resp->add('originalname', new XoopsXmlRpcString(htmlspecialchars($unicode->encode_utf8($file->get('original_file_name'), xoonips_get_server_charset()), ENT_QUOTES, 'UTF-8')));
        $resp->add('size', new XoopsXmlRpcInt($file->get('file_size')));
        $resp->add('mimetype', new XoopsXmlRpcString($file->get('mime_type')));
        $resp->add('caption', new XoopsXmlRpcString(htmlspecialchars($unicode->encode_utf8($file->get('caption'), xoonips_get_server_charset()), ENT_QUOTES, 'UTF-8')));
        $resp->add('thumbnail', new XoopsXmlRpcBase64($file->get('thumbnail_file')));
        $resp->add('data', new XoopsXmlRpcBase64(file_get_contents($file->getFilepath())));

        return $resp;
    }
}
