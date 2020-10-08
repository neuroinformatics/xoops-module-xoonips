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

require_once XOOPS_ROOT_PATH.'/modules/xoonips/class/xmlrpc/view/xmlrpcview.class.php';

/**
 * @brief Class that generate response of XML-RPC getItemtype request
 */
class XooNIpsXmlRpcViewGetItemtype extends XooNIpsXmlRpcViewElement
{
    /**
     * @brief return XoopsXmlRpcTag that has response of this request
     *
     * @return XoopsXmlRpcTag
     */
    public function render()
    {
        $resp = new XoopsXmlRpcStruct();
        $itemtype = $this->response->getSuccess();
        $resp->add('id', new XoopsXmlRpcInt($itemtype->get('item_type_id')));
        $resp->add('name', new XoopsXmlRpcString($itemtype->get('name')));
        $resp->add('title', new XoopsXmlRpcString($itemtype->get('display_name')));
        $resp->add('description', new XoopsXmlRpcString($itemtype->getDescription()));
        $fields = new XoopsXmlRpcArray();
        $iteminfo = $itemtype->getIteminfo();

        // include language file of itemtype
        $langman = &xoonips_getutility('languagemanager');
        $modulename = $iteminfo['ormcompo']['module'];
        $langman->read('main.php', $modulename);

        $unicode = &xoonips_getutility('unicode');

        foreach ($iteminfo['io']['xmlrpc']['item'] as $i) {
            // data_type mapping
            switch ($i['xmlrpc']['type']) {
            case 'dateTime.iso8601':
                $datatype = 'calendar';
                break;
            case 'boolean':
                $datatype = 'int';
                break;
            default:
                $datatype = $i['xmlrpc']['type'];
                break;
            }

            $field = new XoopsXmlRpcStruct();
            $field->add('name', new XoopsXmlRpcString(implode('.', $i['xmlrpc']['field'])));
            $field->add('display_name', new XoopsXmlRpcString($unicode->encode_utf8(constant($i['xmlrpc']['display_name']), xoonips_get_server_charset())));
            $field->add('type', new XoopsXmlRpcString($datatype));
            if (isset($i['xmlrpc']['options'])) {
                $options = new XoopsXmlRpcArray();
                foreach ($i['xmlrpc']['options'] as $option_key => $option_val) {
                    $option = new XoopsXmlRpcStruct();
                    $option->add('option', new XoopsXmlRpcString($option_val['option']));
                    $option->add('display_name', new XoopsXmlRpcString($unicode->encode_utf8(constant($option_val['display_name']), xoonips_get_server_charset())));
                    $options->add($option);
                    unset($option);
                }
                $field->add('options', $options);
                unset($options);
            } else {
                $options = new XoopsXmlRpcArray();
                $field->add('options', $options);
                unset($options);
            }
            $field->add('required', new XoopsXmlRpcBoolean(isset($i['xmlrpc']['required']) ? $i['xmlrpc']['required'] : false));
            $field->add('multiple', new XoopsXmlRpcBoolean(isset($i['xmlrpc']['multiple']) ? $i['xmlrpc']['multiple'] : false));
            $field->add('readonly', new XoopsXmlRpcBoolean(isset($i['xmlrpc']['readonly']) ? $i['xmlrpc']['readonly'] : false));
            $fields->add($field);
            unset($field);
        }
        $resp->add('fields', $fields);

        if (0 == strlen($itemtype->getMainFileName())) {
            $resp->add('mainfile', new XoopsXmlRpcString(''));
        } else {
            $iteminfo = $itemtype->getIteminfo();
            foreach ($iteminfo['io']['xmlrpc']['item'] as $f) {
                if ($f['orm']['field'][0]['orm'] == $itemtype->getMainFileName()) {
                    $resp->add('mainfile', new XoopsXmlRpcString(implode('.', $f['xmlrpc']['field'])));
                    break;
                }
            }
        }

        if (0 == strlen($itemtype->getPreviewFileName())) {
            $resp->add('previewfile', new XoopsXmlRpcString(''));
        } else {
            $iteminfo = $itemtype->getIteminfo();
            foreach ($iteminfo['io']['xmlrpc']['item'] as $f) {
                if ($f['orm']['field'][0]['orm'] == $itemtype->getPreviewFileName()) {
                    $resp->add('previewfile', new XoopsXmlRpcString(implode('.', $f['xmlrpc']['field'])));
                    break;
                }
            }
        }

        return $resp;
    }
}
