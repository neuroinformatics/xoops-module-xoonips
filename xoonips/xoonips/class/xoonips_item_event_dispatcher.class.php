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

class XooNIpsItemEventDispatcher
{
    public $_listeners = array();

    public static function &getInstance()
    {
        static $singleton_;
        if ($singleton_ == null) {
            $singleton_ = new self();
        }

        return $singleton_;
    }

    public function registerEvent(&$instance)
    {
        if (!in_array($instance, $this->_listeners)) {
            $this->_listeners[] = &$instance;
        }
    }

    public function onUpdate($item_id)
    {
        foreach ($this->_listeners as $l) {
            $l->onUpdate($item_id);
        }
    }

    public function onDelete($item_id)
    {
        foreach ($this->_listeners as $l) {
            $l->onDelete($item_id);
        }
    }

    public function onRegister($item_id)
    {
        foreach ($this->_listeners as $l) {
            $l->onRegister($item_id);
        }
    }
}
