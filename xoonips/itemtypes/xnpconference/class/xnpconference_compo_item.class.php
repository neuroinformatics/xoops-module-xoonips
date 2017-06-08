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

defined('XOOPS_ROOT_PATH') || exit('XOOPS root path not defined');

require_once XOOPS_ROOT_PATH.'/modules/xoonips/class/xoonips_compo_item.class.php';
require_once XOOPS_ROOT_PATH.'/modules/xnpconference/iteminfo.php';
require_once dirname(__DIR__).'/include/view.php';

/**
 * @brief Handler object that create,insert,update,get,delete XNPConferenceCompo object.
 */
class XNPConferenceCompoHandler extends XooNIpsItemInfoCompoHandler
{
    public function __construct(&$db)
    {
        parent::__construct($db, 'xnpconference');
    }

    public function &create()
    {
        $conference = new XNPConferenceCompo();

        return $conference;
    }

    /**
     * return template filename.
     *
     * @param string $type defined symbol
     *                     XOONIPS_TEMPLATE_TYPE_TRANSFER_ITEM_DETAIL
     *                     or XOONIPS_TEMPLATE_TYPE_TRANSFER_ITEM_LISTL
     *
     * @return template filename
     */
    public function getTemplateFileName($type)
    {
        switch ($type) {
        case XOONIPS_TEMPLATE_TYPE_TRANSFER_ITEM_DETAIL:
            return 'xnpconference_transfer_item_detail.html';
        case XOONIPS_TEMPLATE_TYPE_TRANSFER_ITEM_LIST:
            return 'xnpconference_transfer_item_list.html';
        default:
            return '';
        }
    }

    /**
     * return template variables of item.
     *
     * @param string $type    defined symbol
     *                        XOONIPS_TEMPLATE_TYPE_TRANSFER_ITEM_DETAIL
     *                        , XOONIPS_TEMPLATE_TYPE_TRANSFER_ITEM_LIST
     *                        , XOONIPS_TEMPLATE_TYPE_ITEM_DETAIL
     *                        or XOONIPS_TEMPLATE_TYPE_ITEM_LIST
     * @param int    $item_id
     * @param int    $uid     user id who get item
     *
     * @return array of template variables
     */
    public function getTemplateVar($type, $item_id, $uid)
    {
        $conference = &$this->get($item_id);
        if (!is_object($conference)) {
            return array();
        }
        $result = $this->getBasicTemplateVar($type, $conference, $uid);

        $textutil = &xoonips_getutility('text');
        $detail = &$conference->getVar('detail');

        switch ($type) {
        case XOONIPS_TEMPLATE_TYPE_ITEM_LIST:
            $result['author'] = array();
            foreach ($conference->getVar('author') as $author) {
                $result['author'][] = $author->getVarArray('s');
            }
            $result['detail'] = $detail->getVarArray('s');
            $result['detail']['presentation_type'] = $this->get_presentation_type_label($detail->get('presentation_type'));

            return $result;
        case XOONIPS_TEMPLATE_TYPE_TRANSFER_ITEM_DETAIL:
        case XOONIPS_TEMPLATE_TYPE_ITEM_DETAIL:
        case XOONIPS_TEMPLATE_TYPE_TRANSFER_ITEM_LIST:
            $result['xnpconference_author'] = xoonips_get_multiple_field_template_vars($detail->getAuthors(), 'xnpconference', 'author');
            $result['detail'] = $detail->getVarArray('s');
            $result['detail']['presentation_type'] = $textutil->html_special_chars($this->get_presentation_type_label($detail->get('presentation_type')));
            $result['detail']['presentation_type_value'] = $detail->get('presentation_type', 's');

            $tmp = $detail->getVar('conference_from_year', 'n');
            if (!empty($tmp)) {
                $conference_from = date(DATE_FORMAT, mktime(0, 0, 0, $detail->getVar('conference_from_month', 'n'), $detail->getVar('conference_from_mday', 'n'), $detail->getVar('conference_from_year', 'n')));
                $conference_to = date(DATE_FORMAT, mktime(0, 0, 0, $detail->getVar('conference_to_month', 'n'), $detail->getVar('conference_to_mday', 'n'), $detail->getVar('conference_to_year', 'n')));
            } else {
                $conference_from = date(DATE_FORMAT, mktime(0, 0, 0, $basic['publication_month']['value'], $basic['publication_mday']['value'], $basic['publication_year']['value']));
                $conference_to = $conference_from;
            }
            $result['detail']['conference_date'] = 'From:&nbsp;'.$conference_from.'&nbsp;&nbsp;&nbsp;To:&nbsp;'.$conference_to;

            $conference_paper = $conference->getVar('conference_paper');
            if ($conference_paper->get('item_id') == $item_id) {
                $result['detail']['conference_paper'] = $this->getAttachmentTemplateVar($conference->getVar('conference_paper'));
            }
            $conference_file = $conference->getVar('conference_file');
            if ($conference_file->get('item_id') == $item_id) {
                $result['detail']['conference_file'] = $this->getAttachmentTemplateVar($conference->getVar('conference_file'));
            }
            break;
        }

        return $result;
    }

    public function get_presentation_type_label($type)
    {
        $keyval = xnpconferenceGetTypes();

        return $keyval[$type];
    }

    /**
     * get confernece date string.
     *
     * @param XooNIpsConferenceItemDetail $detai
     *
     * @return string conference date(from and to)
     */
    public function get_conference_date($detail)
    {
        $conference_from = date(DATE_FORMAT, mktime(0, 0, 0, $detail->get('conference_from_month'), $detail->get('conference_from_mday'), $detail->get('conference_from_year')));
        $conference_to = date(DATE_FORMAT, mktime(0, 0, 0, $detail->get('conference_to_month'), $detail->get('conference_to_mday'), $detail->get('conference_to_year')));

        return 'From: '.$conference_from.' To: '.$conference_to;
    }
}

/**
 * @brief Data object that have one ore more XooNIpsTableObject for Conference type.
 */
class XNPConferenceCompo extends XooNIpsItemInfoCompo
{
    public function __construct()
    {
        parent::__construct('xnpconference');
    }
}
