<?php
// $Revision: 1.1.2.4 $
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

if ( ! defined( 'XOOPS_ROOT_PATH' ) ) exit();

/**
 * @brief Data object of Paper author information
 *
 */
class XNPPaperOrmAuthor extends XooNIpsTableObject
{
    function XNPPaperOrmAuthor()
    {
        parent::XooNIpsTableObject();
        $this->initVar('paper_author_id', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('paper_id', XOBJ_DTYPE_INT, 0, true);
        $this->initVar('author', XOBJ_DTYPE_TXTBOX, '', true);
        $this->initVar('author_order', XOBJ_DTYPE_INT, 0, true);
    }
}

/**
 * @brief Handler class that create, insert, update, get and delete detail information
 *
 *
 */
class XNPPaperOrmAuthorHandler extends XooNIpsTableObjectHandler
{
    function XNPPaperOrmAuthorHandler(&$db) 
    {
        parent::XooNIpsTableObjectHandler($db);
        $this->__initHandler('XNPPaperOrmAuthor', 'xnppaper_author', 'paper_author_id', false);
    }
}
?>
