<?php

// $Revision: 1.1.4.1.2.5 $
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

/**
 * XmlRpcTransform composer class for Conference type.
 */
class XNPConferenceXmlRpcTransformCompo extends XooNIpsXmlRpcTransformCompo
{
    public function __construct()
    {
        parent::__construct('xnpconference');
    }

    /**
     * override getObject to order author.
     *
     * @see XooNIpsXmlRpcTransformCompo::getObject
     *
     * @param array associative array of XML-RPC argument
     *
     * @return XNPConferenceOrmAuthor
     */
    public function getObject($in_array)
    {
        $obj = parent::getObject($in_array);
        $authors = &$obj->getVar('author');
        for ($i = 0; $i < count($authors); ++$i) {
            $authors[$i]->set('author_order', $i);
        }

        return $obj;
    }
}
