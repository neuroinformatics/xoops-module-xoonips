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

/**
 * @brief class that generates XML-RPC response
 */
class XooNIpsXmlRpcViewElement
{
    /**
     * array of XooNIpsXmlRpcViewElement.
     *
     * @see XooNIpsXmlRpcViewElement
     *
     * @private
     */
    public $views = null;

    /**
     * data to output.
     *
     * @private
     */
    public $response = null;

    /**
     * @param[in] XooNIpsResponse $response response of logic
     */
    public function __construct(&$response)
    {
        $this->response = &$response;
    }

    /**
     * @brief generate response
     *
     * @return XoopsXmlRpcTag
     */
    public function render()
    {
    }

    /**
     * @param XooNIpsXmlRpcViewElement view
     */
    public function addView($view)
    {
        $this->views[] = &$view;
    }
}
class XooNIpsXmlRpcItemViewGetSimpleItems extends XooNIpsXmlRpcItemView
{
    public function render($io_xmlrpc = null)
    {
        $iteminfo = $this->item->getIteminfo();

        return parent::render($iteminfo['io']['xmlrpc']['simpleitem']);
    }
}
class XooNIpsXmlRpcItemViewGetItem extends XooNIpsXmlRpcItemView
{
    public function render($io_xmlrpc = null)
    {
        $iteminfo = $this->item->getIteminfo();

        return parent::render($iteminfo['io']['xmlrpc']['item']);
    }
}

/**
 * @brief Class that generate item response according
 *  to iteminfo of each item type
 */
class XooNIpsXmlRpcItemView extends XooNIpsXmlRpcViewElement
{
    public $item = null;

    /**
     * return true if server and client language are japanese.
     *
     * @return bool true if server and client language are japanese
     */
    public function isServerAndClientJapanese()
    {
        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])
            && substr_count($_SERVER['HTTP_ACCEPT_LANGUAGE'], 'ja') > 0
            && _CHARSET == 'EUC-JP'
        ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param string $type type
     * @param mixed  $var  value of field
     *
     * @return XoopsXmlRpcTag or false
     */
    public function createTag($type, $var)
    {
        $tag = null;
        switch ($type) {
        case 'int':
            $tag = new XoopsXmlRpcInt($var);
            break;

        case 'string':
            $textutil = &xoonips_getutility('text');
            $tag = new XoopsXmlRpcString($textutil->xml_special_chars($var, _CHARSET));
            break;

        case 'dateTime.iso8601':
            $tag = new XoopsXmlRpcDatetime($var);
            break;

        default:
            trigger_error("unsupported XML-RPC data type '$type'.");

            return false;
        }

        return $tag;
    }

    /**
     * @param[in] XooNIpsItem $item Item object to be rendered
     */
    public function __construct(&$item)
    {
        $this->item = &$item;
    }

    /**
     * @param array $io_xmlrpc array of transfrom rule defined
     *                         in $iteminfo['io']['xmlrpc'][???](if not specified,
     *                         use $this->iteminfo)
     *
     * @return XoopsXmlRpcStruct <struct> of $item
     */
    public function render($io_xmlrpc = null)
    {
        if (is_null($io_xmlrpc)) {
            $iteminfo = $this->item->iteminfo['io']['xmlrpc']['item'];
        } else {
            $iteminfo = $io_xmlrpc;
        }
        $resp = new XoopsXmlRpcStruct();

        $tags = array();
        $tags['detail_field'] = new XoopsXmlRpcArray();
        foreach ($iteminfo as $output) {
            foreach ($this->render_field($output) as $result) {
                if ($output['xmlrpc']['field'][0] == 'detail_field') {
                    $tags[$output['xmlrpc']['field'][0]]->add($result);
                } else {
                    $tags[$output['xmlrpc']['field'][0]] = $result;
                }
                unset($result);
            }
        }
        foreach ($tags as $key => $array_tag) {
            if ($key != 'detail_field') {
                $resp->add($key, $array_tag);
            }
        }
        $key = 'detail_field';
        if (isset($tags[$key])) {
            $resp->add($key, $tags[$key]);
        }

        return $resp;
    }

    /**
     * create XoopsXmlRpcTag of a field.
     *
     * @param assoc array $output
     *
     * @return array of XoopsXmlRpcTag
     */
    public function render_field($output)
    {
        $result = array();
        if (isset($output['xmlrpc']['multiple']) ? $output['xmlrpc']['multiple'] : false
        ) {
            $orm = $this->item->getVar($output['orm']['field'][0]['orm']);
            if ($output['xmlrpc']['field'][0] != 'detail_field') {
                $result[0] = new XoopsXmlRpcArray();
            }
            if (is_array($orm)) {
                $pos = 0;
                foreach ($orm as $o) {
                    $in_var = array($o->get($output['orm']['field'][0]['field']));
                    $out_var = array();
                    $context = array('position' => $pos);
                    eval(isset($output['eval']['orm2xmlrpc']) ? $output['eval']['orm2xmlrpc'] : '$out_var[0] = $in_var[0];');
                    if ($output['xmlrpc']['field'][0] == 'detail_field') {
                        $struct = new XoopsXmlRpcStruct();
                        $struct->add('name', new XoopsXmlRpcString($output['xmlrpc']['field'][1]));
                        $struct->add('value', $this->createTag($output['xmlrpc']['type'], $out_var[0]));
                        $result[] = $struct;
                    } else {
                        $result[0]->add($this->createTag($output['xmlrpc']['type'], $out_var[0]));
                    }
                    ++$pos;
                }
            }
        } else {
            $froms = $output['orm']['field'];
            if (isset($froms['orm']) && isset($froms['field'])) {
                $froms = array($froms);
            }
            $in_var = array();
            $out_var = array();
            foreach ($froms as $from) {
                $orm = $this->item->getVar($from['orm']);
                if (is_array($orm)) {
                    $array_vars = array();
                    foreach ($orm as $o) {
                        $array_vars[] = $o->get($from['field']);
                    }
                    $in_var[] = $array_vars;
                } else {
                    $in_var[] = $orm->get($from['field']);
                }
            }
            eval(isset($output['eval']['orm2xmlrpc']) ? $output['eval']['orm2xmlrpc'] : '$out_var[0] = $in_var[0];');
            if ($output['xmlrpc']['field'][0] == 'detail_field') {
                $struct = new XoopsXmlRpcStruct();
                $struct->add('name', new XoopsXmlRpcString($output['xmlrpc']['field'][1]));
                $struct->add('value', $this->createTag($output['xmlrpc']['type'], $out_var[0]));
                $result[] = $struct;
            } else {
                $result[] = $this->createTag($output['xmlrpc']['type'], $out_var[0]);
            }
        }

        return $result;
    }
}
