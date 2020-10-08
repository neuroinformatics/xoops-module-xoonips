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

class IdentifyHandler extends HarvesterHandler
{
    public $_dateFormat;
    public $_earliestDatestamp;
    public $_tagstack;
    public $_repositoryName;

    public function __construct($_parser)
    {
        parent::__construct($_parser);
        $this->_earliestDatestamp = null;
        $this->_dateFormat = null;
        $this->_tagstack = array();
        $this->_repositoryName = '';
    }

    public function startElementHandler($parser, $name, $attribs)
    {
        array_push($this->_tagstack, $name);
    }

    public function endElementHandler($parser, $name)
    {
        array_pop($this->_tagstack);
    }

    public function characterDataHandler($parser, $data)
    {
        if ('GRANULARITY' == end($this->_tagstack)) {
            if ('YYYY-MM-DDThh:mm:ssZ' == $data) {
                $this->_dateFormat = "Y-m-d\TH:i:s\Z";
            } elseif ('YYYY-MM-DD' == $data) {
                $this->_dateFormat = 'Y-m-d';
            } else {
                $this->_dateFormat = false;
            }
        } elseif ('EARLIESTDATESTAMP' == end($this->_tagstack)) {
            $this->_earliestDatestamp = ISO8601toUTC($data);
        } elseif ('REPOSITORYNAME' == end($this->_tagstack)) {
            $this->_repositoryName .= $data;
        }
    }

    public function getDateFormat()
    {
        return $this->_dateFormat;
    }

    public function getEarliestDatestamp()
    {
        return $this->_earliestDatestamp;
    }

    public function getRepositoryName()
    {
        return $this->_repositoryName;
    }
}
