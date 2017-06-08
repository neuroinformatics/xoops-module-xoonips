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

require_once XOOPS_ROOT_PATH.'/class/xml/rpc/xmlrpcparser.php';

/**
 * XML-RPC Parser.
 *
 * use XooNIpsRpcDateTimeHandler to parse <dateTime.iso8601>
 */
class XooNIpsXmlRpcParser extends XoopsXmlRpcParser
{
    /**
     * Constructor of the class.
     *
     * @author
     *
     * @see
     */
    public function __construct(&$input)
    {
        parent::__construct($input);
        $this->addTagHandler(new RpcMethodNameHandler());
        $this->addTagHandler(new RpcIntHandler());
        $this->addTagHandler(new RpcDoubleHandler());
        $this->addTagHandler(new RpcBooleanHandler());
        $this->addTagHandler(new RpcStringHandler());
        $this->addTagHandler(new XooNIpsRpcDateTimeHandler());
        $this->addTagHandler(new RpcBase64Handler());
        $this->addTagHandler(new RpcNameHandler());
        $this->addTagHandler(new XooNIpsRpcValueHandler());
        $this->addTagHandler(new RpcMemberHandler());
        $this->addTagHandler(new RpcStructHandler());
        $this->addTagHandler(new RpcArrayHandler());
    }
}

class XooNIpsRpcDateTimeHandler extends RpcDateTimeHandler
{
    /**
     * parse sISO-8601 date time string.
     */
    public function handleCharacterData(&$parser, &$data)
    {
        $parser->setTempValue(ISO8601toUnixTimestamp(trim($data)));
    }
}

/**
 * value handler. supports string tag omission.
 */
class XooNIpsRpcValueHandler extends RpcValueHandler
{
    /**
     * content of value tag. no need to be a stack.
     */
    public $_tempValue;

    public function handleBeginElement(&$parser, &$attributes)
    {
        $this->_tempValue = null;
        parent::handleBeginElement($parser, $attributes);
    }

    public function handleCharacterData(&$parser, &$data)
    {
        parent::handleCharacterData($parser, $data);
        if (is_null($this->_tempValue)) {
            $this->_tempValue = $data;
        } else {
            $this->_tempValue .= $data;
        }
    }

    /**
     * set content to $parser->_tempValue if $parser->_tempValue is not set.
     */
    public function handleEndElement(&$parser)
    {
        if (!isset($parser->_tempValue)) { // maybe no tag handled in <value>...</value>
            if (!is_null($this->_tempValue)) {
                $parser->setTempValue($this->_tempValue);
            } else {
                $parser->setTempValue('');
            }
        }
        parent::handleEndElement($parser);
        $this->_tempValue = null;
    }
}
