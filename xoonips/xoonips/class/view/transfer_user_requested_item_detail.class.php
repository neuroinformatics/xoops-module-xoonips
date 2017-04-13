<?php

// $Revision: 1.1.2.9 $
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

require_once __DIR__.'/transfer.class.php';

/**
 * HTML view to show transfer item detail.
 */
class XooNIpsViewTransferUserRequestedItemDetail extends XooNIpsViewTransfer
{
    /**
     * create view.
     *
     * @param arrray $params associative array of view
     *                       - $params['template_file_name']:
     *                       - $params['template_vars']:
     */
    public function __construct($params)
    {
        parent::__construct($params);
    }

    public function render()
    {
        global $xoopsConfig;
        include_once XOOPS_ROOT_PATH.'/class/template.php';

        $xoopsTpl = new XoopsTpl();
        xoops_header(false);
        $xoopsTpl->assign('template_file_name', 'db:'.$this->_params['template_file_name']);
        $xoopsTpl->assign('template_vars', $this->_params['template_vars']);
        $xoopsTpl->display('db:xoonips_transfer_user_requested_item_detail.html');
        xoops_footer();
    }

    public function get_xoonips_user_template_vars()
    {
        $xoops_user = &$this->_params['user']->getVar('xoops_user');

        return array('uname' => $xoops_user->get('uname'),
                      'name' => $xoops_user->get('name'), );
    }

    public function get_xoonips_item_type_template_vars()
    {
        return array('display_name' => $this->_params['item_type']->getVar('display_name', 's'));
    }

    public function get_xoonips_item_template_vars()
    {
        $basic = &$this->_params['item']->getVar('basic');

        $result = array(
            'basic' => array(
            'item_id' => $basic->get('item_id'),
            'description' => $basic->getVar('description', 's'),
            'doi' => $basic->get('doi'),
            'creation_date' => $basic->get('creation_date'),
            'last_update_date' => $basic->get('last_update_date'),
            'publication_year' => $basic->get('publication_year'),
            'publication_month' => $basic->get('publication_month'),
            'publication_mday' => $basic->get('publication_mday'),
            'lang' => $this->get_lang_label(), ),
            'title' => array(),
            'keyword' => array(),
            'changelog' => array(),
            'index_item_link' => array(),
            'related_tos' => array(), );

        foreach ($this->_params['item']->getVar('titles') as $title) {
            $result['title'][] = array('title' => $title->getVar('title', 's'));
        }

        foreach ($this->_params['item']->getVar('keywords') as $keyword) {
            $result['keyword'][] = array('keyword' => $keyword->getVar('keyword', 's'));
        }

        foreach ($this->_params['item']->getVar('changelogs') as $changelog) {
            $result['changelog'][] = array(
                'log_date' => $changelog->get('log_date'),
                'log' => $changelog->getVar('log', 's'), );
        }

        foreach ($this->_params['item']->getVar('indexes') as $link) {
            $result['index_item_link'][] = array('path' => $this->get_index_path_by_index_id($link->get('index_id')), 's');
        }

        $item_type_handler = &xoonips_getormhandler('xoonips', 'item_type');
        $basic_handler = &xoonips_getormhandler('xoonips', 'item_basic');
        foreach ($this->_params['item']->getVar('related_tos') as $related_to) {
            $related_basic = &$basic_handler->get($related_to->get('item_id'));
            $related_item_type = &$item_type_handler->get($related_basic->get('item_type_id'));
            $item_compo_handler = &xoonips_getormcompohandler($related_item_type->get('name'), 'item');
            $result['related_tos'][] = array(
                'filename' => 'db:'.$item_compo_handler->getTemplateFileName(XOONIPS_TEMPLATE_TYPE_TRANSFER_ITEM_LIST),
                'var' => $item_compo_handler->getTemplateVar(XOONIPS_TEMPLATE_TYPE_TRANSFER_ITEM_LIST, $related_basic->get('item_id')),
            );
        }

        return $result;
    }

    public function get_xoonips_item_detail_template_vars()
    {
        $basic = &$this->_params['item']->getVar('basic');
        $handler = &xoonips_getormcompohandler($this->_params['item_type']->get('name'), 'item');

        return $handler->getTemplateVar(XOONIPS_TEMPLATE_TYPE_TRANSFER_ITEM_DETAIL, $basic->get('item_id'));
    }

    public function array_combine($keys, $values)
    {
        $result = array();
        reset($keys);
        reset($values);
        while (current($keys) && current($values)) {
            $result[current($keys)] = current($values);
            next($keys);
            next($values);
        }

        return $result;
    }

    public function get_lang_label()
    {
        $languages = $this->array_combine(
            explode(',', _MD_XOONIPS_ITEM_LANG_OPTION_IDS),
            explode(',', _MD_XOONIPS_ITEM_LANG_OPTION_NAMES)
        );

        $basic = &$this->_params['item']->getVar('basic');
        if (in_array($basic->get('lang'), array_keys($languages))) {
            return $languages[$basic->get('lang')];
        }

        return '';
    }
}
