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

require_once XOOPS_ROOT_PATH.'/class/xml/rpc/xmlrpctag.php';

class XooNIpsXmlRpcFault extends XoopsXmlRpcFault
{
    public function render()
    {
        switch ($this->_code) {
        case 101:
            $string = 'Invalid server URI';
            break;
        case 102:
            $string = 'Parser parse error';
            break;
        case 103:
            $string = 'Module not found';
            break;
        case 104:
            $string = 'User authentication failed';
            break;
        case 105:
            $string = 'Module API not found';
            break;
        case 106:
            $string = 'Method response error';
            break;
        case 107:
            $string = 'Method not supported';
            break;
        case 108:
            $string = 'Invalid parameter';
            break;
        case 109:
            $string = 'Missing parameters';
            break;
        case 110:
            $string = 'Selected blog application does not exist';
            break;
        case 111:
            $string = 'Method permission denied';
            break;
        default:
            $string = 'Method response error';
            break;
        }
        $string .= "\n".$this->_extra;

        return '<fault><value><struct><member><name>faultCode</name><value><int>'.$this->_code.'</int></value></member><member><name>faultString</name><value><string>'.$this->encode($string).'</string></value></member></struct></value></fault>';
    }
}
