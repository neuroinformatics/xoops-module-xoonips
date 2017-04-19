<?php

// $Revision:$
// ------------------------------------------------------------------------- //
//  XooNIps - Neuroinformatics Base Platform System                          //
//  Copyright (C) 2005-2013 RIKEN, Japan All rights reserved.                //
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

$itemtype_path = dirname(__DIR__);
$itemtype_dirname = basename($itemtype_path);
$xoonips_path = dirname($itemtype_path).'/xoonips';

$langman = &xoonips_getutility('languagemanager');
$langman->read('main.php', $itemtype_dirname);

// convert \r to \n. trim each lines. delete empty lines. remove last \n.
function xnpconferenceTrimAuthorString($author)
{
    $author = str_replace("\r", "\n", $author);
    $ar = explode("\n", $author);
    $ar2 = array();
    foreach ($ar as $val) {
        $val = trim($val);
        if ($val != '') {
            $ar2[] = $val;
        }
    }

    return implode("\n", $ar2);
}

function xnpconferenceGetTypes()
{
    return array('powerpoint' => 'PowerPoint', 'pdf' => 'PDF', 'illustrator' => 'Illustrator', 'other' => 'Other');
}

/**
 * get DetailInformation by item_id.
 */
function xnpconferenceGetDetailInformation($item_id)
{
    global $xoopsDB;
    if (empty($item_id)) {
        return array('presentation_type' => '', 'conference_title' => '', 'place' => '', 'abstract' => '', 'conference_from_year' => '', 'conference_from_month' => '', 'conference_from_mday' => '', 'conference_to_year' => '', 'conference_to_month' => '', 'conference_to_mday' => '', 'attachment_dl_limit' => '', 'attachment_dl_notify' => '');
    }

    $sql = 'select * from '.$xoopsDB->prefix('xnpconference_item_detail')." where conference_id=$item_id";
    $result = $xoopsDB->query($sql);
    if ($result == false) {
        echo mysql_error();

        return false;
    }
    $types = xnpconferenceGetTypes();
    $detail = $xoopsDB->fetchArray($result);
    $detail['presentation_type_str'] = $types[$detail['presentation_type']];

    return $detail;
}

function xnpconferenceGetMetaInformation($item_id)
{
    $ret = array();
    $author_array = array();

    $basic = xnpGetBasicInformationArray($item_id);
    $detail = xnpconferenceGetDetailInformation($item_id);
    if (!empty($basic)) {
        $ret[_MD_XNPCONFERENCE_PRESENTATION_TITLE_LABEL] = implode("\n", $basic['titles']);
        $ret[_MD_XOONIPS_ITEM_CONTRIBUTOR_LABEL] = $basic['contributor'];
        $ret[_MD_XOONIPS_ITEM_KEYWORDS_LABEL] = implode("\n", $basic['keywords']);
        $ret[_MD_XOONIPS_ITEM_DESCRIPTION_LABEL] = $basic['description'];
        $ret[_MD_XOONIPS_ITEM_DOI_LABEL] = $basic['doi'];
        $ret[_MD_XOONIPS_ITEM_CREATION_DATE_LABEL] = $basic['creation_date'];
        $ret[_MD_XOONIPS_ITEM_LAST_UPDATE_DATE_LABEL] = $basic['last_update_date'];
    }
    if (!empty($detail)) {
        $ret[_MD_XNPCONFERENCE_PRESENTATION_TYPE_LABEL] = $detail['presentation_type_str'];
        $ret[_MD_XNPCONFERENCE_CONFERENCE_TITLE_LABEL] = $detail['conference_title'];
        $ret[_MD_XNPCONFERENCE_PLACE_LABEL] = $detail['place'];
        $ret[_MD_XNPCONFERENCE_ABSTRACT_LABEL] = $detail['abstract'];
    }
    if (!empty($basic)) {
        $ret[_MD_XNPCONFERENCE_DATE_LABEL] = xnpDate($basic['publication_year'], $basic['publication_month'], $basic['publication_mday']);
    }
    $xnpconference_handler = &xoonips_getormcompohandler('xnpconference', 'item');
    $xnpconference = &$xnpconference_handler->get($item_id);
    foreach ($xnpconference->getVar('author') as $author) {
        $author_array[] = $author->getVar('author', 'n');
    }

    return $ret;
}

function xnpconferenceGetListBlock($item_basic)
{
    // get uid
    global $xoopsUser;
    $myuid = is_object($xoopsUser) ? $xoopsUser->getVar('uid', 'n') : UID_GUEST;

    // set to template
    global $xoopsTpl;
    $tpl = new XoopsTpl();
    // copy variables in $xoopsTpl to $tpl
    $tpl->assign($xoopsTpl->get_template_vars());

    $xnpconference_handler = &xoonips_getormcompohandler('xnpconference', 'item');
    $tpl->assign('xoonips_item', $xnpconference_handler->getTemplateVar(XOONIPS_TEMPLATE_TYPE_ITEM_LIST, $item_basic['item_id'], $myuid));

    // return as HTML
    return $tpl->fetch('db:xnpconference_list_block.html');
}

function xnpconferenceGetPrinterFriendlyListBlock($item_basic)
{
    return xnpconferenceGetListBlock($item_basic);
}

function xnpconferenceGetDetailBlock($item_id)
{
    // get uid
    global $xoopsUser;
    $myuid = is_object($xoopsUser) ? $xoopsUser->getVar('uid', 'n') : UID_GUEST;

    // set to template
    global $xoopsTpl;
    $tpl = new XoopsTpl();
    $tpl->assign($xoopsTpl->get_template_vars());
    // copy variables in $xoopsTpl to $tpl
    $tpl->assign('editable', xnp_get_item_permission($_SESSION['XNPSID'], $item_id, OP_MODIFY));
    $tpl->assign('basic', xnpGetBasicInformationDetailBlock($item_id));
    $tpl->assign('index', xnpGetIndexDetailBlock($item_id));
    $tpl->assign('presentation_file', xnpGetAttachmentDetailBlock($item_id, 'conference_file'));
    $tpl->assign('conference_paper', xnpGetAttachmentDetailBlock($item_id, 'conference_paper'));
    $tpl->assign('dl_flag', (int) xnpconferenceGetAttachmentDownloadLimitOption($item_id));

    $xnpconference_handler = &xoonips_getormcompohandler('xnpconference', 'item');
    $tpl->assign('xoonips_item', $xnpconference_handler->getTemplateVar(XOONIPS_TEMPLATE_TYPE_ITEM_DETAIL, $item_id, $myuid));

    // return as HTML
    return $tpl->fetch('db:xnpconference_detail_block.html');
}

function xnpconferenceGetDownloadConfirmationBlock($item_id, $download_file_id)
{
    $detail = xnpconferenceGetDetailInformation($item_id);

    return xnpGetDownloadConfirmationBlock($item_id, $download_file_id, $detail['attachment_dl_notify'], false, false, false);
}

function xnpconferenceGetDownloadConfirmationRequired($item_id)
{
    $detail = xnpconferenceGetDetailInformation($item_id);

    return $detail['attachment_dl_notify'];
}

function xnpconferenceGetPrinterFriendlyDetailBlock($item_id)
{
    // get uid
    global $xoopsUser;
    $myuid = is_object($xoopsUser) ? $xoopsUser->getVar('uid', 'n') : UID_GUEST;

    // set to template
    global $xoopsTpl;
    $tpl = new XoopsTpl();
    $tpl->assign($xoopsTpl->get_template_vars());
    // copy variables in $xoopsTpl to $tpl
    $tpl->assign('editable', false);
    $tpl->assign('basic', xnpGetBasicInformationPrinterFriendlyBlock($item_id));
    $tpl->assign('index', xnpGetIndexPrinterFriendlyBlock($item_id));
    $tpl->assign('presentation_file', xnpGetAttachmentPrinterFriendlyBlock($item_id, 'conference_file'));
    $tpl->assign('conference_paper', xnpGetAttachmentPrinterFriendlyBlock($item_id, 'conference_paper'));
    $tpl->assign('dl_flag', (int) xnpconferenceGetAttachmentDownloadLimitOption($item_id));

    $xnpconference_handler = &xoonips_getormcompohandler('xnpconference', 'item');
    $tpl->assign('xoonips_item', $xnpconference_handler->getTemplateVar(XOONIPS_TEMPLATE_TYPE_ITEM_DETAIL, $item_id, $myuid));
    // return as HTML
    return $tpl->fetch('db:xnpconference_detail_block.html');
}

function xnpconferenceGetRegisterBlock()
{
    $textutil = &xoonips_getutility('text');
    $formdata = &xoonips_getutility('formdata');

    // get DetailInformation
    $tpl = new XoopsTpl();
    $presentation_type = $formdata->getValue('post', 'presentation_type', 's', false);
    $conferenceFromYear = $formdata->getValue('post', 'conferenceFromYear', 'i', false);
    $conferenceToYear = $formdata->getValue('post', 'conferenceToYear', 'i', false);
    if (isset($presentation_type)) {
        if (isset($conferenceFromYear)) {
            $tpl->assign('gmtimeFrom', mktime(0, 0, 0, $formdata->getValue('post', 'conferenceFromMonth', 'i', false, 1), $formdata->getValue('post', 'conferenceFromDay', 'i', false, 1), $conferenceFromYear));
        }
        if (isset($conferenceToYear)) {
            $tpl->assign('gmtimeTo', mktime(0, 0, 0, $formdata->getValue('post', 'conferenceToMonth', 'i', false, 1), $formdata->getValue('post', 'conferenceToDay', 'i', false, 1), $conferenceToYear));
        }
        $detail = array(
        'presentation_type' => $textutil->html_special_chars($formdata->getValue('post', 'presentation_type', 's', false)),
        'conference_title' => $textutil->html_special_chars($formdata->getValue('post', 'conference_title', 's', false)),
        'place' => $textutil->html_special_chars($formdata->getValue('post', 'place', 's', false)),
        'abstract' => $textutil->html_special_chars($formdata->getValue('post', 'abstract', 's', false)),
        'conference_date' => array(
        'name' => _MD_XOONIPS_ITEM_PUBLICATION_DATE_LABEL,
        'value' => $tpl->fetch('db:xnpconference_date.html'),
        ),
        );
    } else {
        $tpl->assign('gmtimeFrom', time());
        $tpl->assign('gmtimeTo', time());
        $detail = array(
        'presentation_type' => 'other',
        'conference_date' => array(
        'name' => _MD_XOONIPS_ITEM_PUBLICATION_DATE_LABEL,
        'value' => $tpl->fetch('db:xnpconference_date.html'),
        ),
        'conference_year' => array(
        'name' => _MD_XOONIPS_ITEM_PUBLICATION_YEAR_LABEL,
        'value' => $tpl->fetch('db:xnpconference_year.html'),
        ),
        'conference_month' => array(
        'name' => _MD_XOONIPS_ITEM_PUBLICATION_MONTH_LABEL,
        'value' => $tpl->fetch('db:xnpconference_month.html'),
        ),
        'conference_mday' => array(
        'name' => _MD_XOONIPS_ITEM_PUBLICATION_MDAY_LABEL,
        'value' => $tpl->fetch('db:xnpconference_mday.html'),
        ),
        );
    }

    // get BasicInformation / Preview / index block
    $basic = xnpGetBasicInformationRegisterBlock();
    $index = xnpGetIndexRegisterBlock();
    $presentation_file = xnpGetAttachmentRegisterBlock('conference_file');
    $conference_paper = xnpGetAttachmentRegisterBlock('conference_paper');
    $attachment_dl_limit = xnpGetDownloadLimitationOptionRegisterBlock('xnpconference');
    $attachment_dl_notify = xnpGetDownloadNotificationOptionRegisterBlock('xnpconference');

    // set to template
    global $xoopsTpl;
    $tpl = new XoopsTpl();
    $tpl->assign($xoopsTpl->get_template_vars());
    // copy variables in $xoopsTpl to $tpl
    $tpl->assign('basic', $basic);
    $tpl->assign('index', $index);
    $tpl->assign('presentation_file', $presentation_file);
    $tpl->assign('conference_paper', $conference_paper);
    $tpl->assign('attachment_dl_limit', $attachment_dl_limit);
    $tpl->assign('attachment_dl_notify', $attachment_dl_notify);
    $tpl->assign('detail', $detail);
    $tpl->assign('xnpconference_author', xoonips_get_multiple_field_template_vars(xoonips_get_orm_from_post('xnpconference', 'author'), 'xnpconference', 'author'));

    $tpl->assign('presentation_type', xnpconferenceGetTypes());
    $tpl->assign('presentation_type_selected', $detail['presentation_type']);
    $tpl->assign('conference_date', $detail['conference_date']);
    // return as HTML
    return $tpl->fetch('db:xnpconference_register_block.html');
}

function xnpconferenceGetEditBlock($item_id)
{
    $textutil = &xoonips_getutility('text');
    $formdata = &xoonips_getutility('formdata');

    // get BasicInformation / Preview / index block
    $basic = xnpGetBasicInformationEditBlock($item_id);
    $index = xnpGetIndexEditBlock($item_id);
    $presentation_file = xnpGetAttachmentEditBlock($item_id, 'conference_file');
    $conference_paper = xnpGetAttachmentEditBlock($item_id, 'conference_paper');

    // get DetailInformation
    $presentation_type = $formdata->getValue('post', 'presentation_type', 's', false);
    if (isset($presentation_type)) {
        $detail = array(
        'presentation_type' => $presentation_type,
        'conference_title' => $formdata->getValue('post', 'conference_title', 's', false),
        'place' => $formdata->getValue('post', 'place', 's', false),
        'abstract' => $formdata->getValue('post', 'abstract', 's', false),
        'conference_from_year' => $formdata->getValue('post', 'conferenceFromYear', 'i', false),
        'conference_from_month' => $formdata->getValue('post', 'conferenceFromMonth', 'i', false),
        'conference_from_mday' => $formdata->getValue('post', 'conferenceFromDay', 'i', false),
        'conference_to_year' => $formdata->getValue('post', 'conferenceToYear', 'i', false),
        'conference_to_month' => $formdata->getValue('post', 'conferenceToMonth', 'i', false),
        'conference_to_mday' => $formdata->getValue('post', 'conferenceToDay', 'i', false),
        );
        foreach ($detail as $key => $val) {
            $$key = $val;
        }
    } elseif (!empty($item_id)) {
        $detail = xnpconferenceGetDetailInformation($item_id);
    } else {
        $detail = array();
    }

    $basic2 = xnpGetBasicInformationDetailBlock($item_id);
    $tpl = new XoopsTpl();
    if (!empty($detail['conference_from_year'])) {
        $tpl->assign('gmtimeFrom', mktime(0, 0, 0, $detail['conference_from_month'], $detail['conference_from_mday'], $detail['conference_from_year']));
        $tpl->assign('gmtimeTo', mktime(0, 0, 0, $detail['conference_to_month'], $detail['conference_to_mday'], $detail['conference_to_year']));
    } else {
        $tpl->assign('gmtimeFrom', mktime(0, 0, 0, $basic2['publication_month']['value'], $basic2['publication_mday']['value'], $basic2['publication_year']['value']));
        $tpl->assign('gmtimeTo', mktime(0, 0, 0, $basic2['publication_month']['value'], $basic2['publication_mday']['value'], $basic2['publication_year']['value']));
    }
    $detail = array_map(array($textutil, 'html_special_chars'), $detail);
    $detail['conference_date'] = array(
    'name' => _MD_XOONIPS_ITEM_PUBLICATION_DATE_LABEL,
    'value' => $tpl->fetch('db:xnpconference_date.html'),
    );

    $attachment_dl_limit = xnpGetDownloadLimitationOptionEditBlock('xnpconference', xnpconferenceGetAttachmentDownloadLimitOption($item_id));
    $attachment_dl_notify = xnpGetDownloadNotificationOptionEditBlock('xnpconference', xnpconferenceGetAttachmentDownloadNotifyOption($item_id));

    // set to template
    global $xoopsTpl;
    $tpl = new XoopsTpl();
    // copy variables in $xoopsTpl to $tpl
    $tpl->assign($xoopsTpl->get_template_vars());

    $tpl->assign('basic', $basic);
    $tpl->assign('index', $index);
    $tpl->assign('presentation_file', $presentation_file);
    $tpl->assign('conference_paper', $conference_paper);
    $tpl->assign('attachment_dl_limit', $attachment_dl_limit);
    $tpl->assign('attachment_dl_notify', $attachment_dl_notify);
    $tpl->assign('detail', $detail);
    $tpl->assign('conference_date', $detail['conference_date']);

    $formdata = &xoonips_getutility('formdata');
    if (!$formdata->getValue('get', 'post_id', 's', false)) {
        $detail_handler = &xoonips_getormhandler('xnpconference', 'item_detail');
        $detail_orm = &$detail_handler->get($item_id);
        $tpl->assign('xnpconference_author', xoonips_get_multiple_field_template_vars($detail_orm->getAuthors(), 'xnpconference', 'author'));
    } else {
        $tpl->assign('xnpconference_author', xoonips_get_multiple_field_template_vars(xoonips_get_orm_from_post('xnpconference', 'author'), 'xnpconference', 'author'));
    }

    $tpl->assign('presentation_type', xnpconferenceGetTypes());
    $tpl->assign('presentation_type_selected', $detail['presentation_type']);

    // return as HTML
    return $tpl->fetch('db:xnpconference_register_block.html');
}

function xnpconferenceGetConfirmBlock($item_id)
{
    $textutil = &xoonips_getutility('text');
    $formdata = &xoonips_getutility('formdata');
    $author_handler = &xoonips_getormhandler('xnpconference', 'author');
    $author_objs = &$formdata->getObjectArray('post', $author_handler->getTableName(), $author_handler, false);

    // get BasicInformation / Preview / index block
    $basic = xnpGetBasicInformationConfirmBlock($item_id);
    $index = xnpGetIndexConfirmBlock($item_id);
    $presentation_file = xnpGetAttachmentConfirmBlock($item_id, 'conference_file');
    $conference_paper = xnpGetAttachmentConfirmBlock($item_id, 'conference_paper');
    $attachment_dl_limit = xnpGetDownloadLimitationOptionConfirmBlock('xnpconference');
    $attachment_dl_notify = xnpGetDownloadNotificationOptionConfirmBlock('xnpconference');
    // get DetailInformation
    $conference_title = $formdata->getValue('post', 'conference_title', 's', false);
    if (isset($conference_title)) {
        $detail = array(
        'presentation_type' => array(
        'value' => $textutil->html_special_chars($formdata->getValue('post', 'presentation_type', 's', false)),
        ),
        'conference_title' => array(
        'value' => $textutil->html_special_chars($formdata->getValue('post', 'conference_title', 's', false)),
        ),
        'place' => array(
        'value' => $textutil->html_special_chars($formdata->getValue('post', 'place', 's', false)),
        ),
        'abstract' => array(
        'value' => $textutil->html_special_chars($formdata->getValue('post', 'abstract', 's', false)),
        ),
        'conference_from_year' => array(
        'value' => $formdata->getValue('post', 'conferenceFromYear', 'i', false),
        ),
        'conference_from_month' => array(
        'value' => $formdata->getValue('post', 'conferenceFromMonth', 'i', false),
        ),
        'conference_from_mday' => array(
        'value' => $formdata->getValue('post', 'conferenceFromDay', 'i', false),
        ),
        'conference_to_year' => array(
        'value' => $formdata->getValue('post', 'conferenceToYear', 'i', false),
        ),
        'conference_to_month' => array(
        'value' => $formdata->getValue('post', 'conferenceToMonth', 'i', false),
        ),
        'conference_to_mday' => array(
        'value' => $formdata->getValue('post', 'conferenceToDay', 'i', false),
        ),
        );
        // trim strings
        xnpConfirmHtml($detail, 'xnpconference_item_detail', array_keys($detail), _CHARSET);
        $types = xnpconferenceGetTypes();
        $detail['presentation_type_str']['value'] = $textutil->html_special_chars($types[$detail['presentation_type']['value']]);
        $conference_from = date(DATE_FORMAT, mktime(0, 0, 0, $detail['conference_from_month']['value'], $detail['conference_from_mday']['value'], $detail['conference_from_year']['value']));
        $conference_to = date(DATE_FORMAT, mktime(0, 0, 0, $detail['conference_to_month']['value'], $detail['conference_to_mday']['value'], $detail['conference_to_year']['value']));
        $conference_date = 'From: '.$conference_from.' To: '.$conference_to;
    } else {
        $detail = array();
    }

    if (xnpHasWithout($basic) || xnpHasWithout($presentation_file) || xnpHasWithout($conference_paper) || xnpHasWithout($detail) || xoonips_is_multiple_field_too_long($author_objs, 'xnpconference', 'author')) {
        global $system_message;
        $system_message = $system_message."\n<br /><font color='#ff0000'>"._MD_XOONIPS_ITEM_WARNING_FIELD_TRIM.'</font><br />';
    }

    // set to template
    global $xoopsTpl;
    $tpl = new XoopsTpl();
    // copy variables in $xoopsTpl to $tpl
    $tpl->assign($xoopsTpl->get_template_vars());

    $tpl->assign('basic', $basic);
    $tpl->assign('index', $index);
    $tpl->assign('presentation_file', $presentation_file);
    $tpl->assign('conference_paper', $conference_paper);
    $tpl->assign('attachment_dl_limit', $attachment_dl_limit);
    $tpl->assign('attachment_dl_notify', $attachment_dl_notify);
    $tpl->assign('detail', $detail);
    $tpl->assign('conference_date', array('name' => _MD_XOONIPS_ITEM_PUBLICATION_DATE_LABEL, 'value' => $conference_date));
    $tpl->assign('xnpconference_author', xoonips_get_multiple_field_template_vars($author_objs, 'xnpconference', 'author'));

    // return as HTML
    return $tpl->fetch('db:xnpconference_confirm_block.html');
}

/**
 * check DetailInformation input
 * called from confirm/registered page.
 */
function xnpconferenceCheckRegisterParameters(&$message)
{
    $xnpsid = $_SESSION['XNPSID'];
    $formdata = &xoonips_getutility('formdata');
    $messages = array();
    $conference_title = $formdata->getValue('post', 'conference_title', 's', false);
    $place = $formdata->getValue('post', 'place', 's', false);
    $author = xoonips_get_multi_field_array_from_post('xnpconference', 'author');
    $conference_fileFileID = $formdata->getValue('post', 'conference_fileFileID', 'i', false);
    $conference_file = $formdata->getFile('conference_file', false);
    $xoonipsCheckedXID = $formdata->getValue('post', 'xoonipsCheckedXID', 's', false);
    $conferenceFromMonth = $formdata->getValue('post', 'conferenceFromMonth', 'i', false);
    $conferenceFromDay = $formdata->getValue('post', 'conferenceFromDay', 'i', false);
    $conferenceFromYear = $formdata->getValue('post', 'conferenceFromYear', 'i', false);
    $conferenceToMonth = $formdata->getValue('post', 'conferenceToMonth', 'i', false);
    $conferenceToDay = $formdata->getValue('post', 'conferenceToDay', 'i', false);
    $conferenceToYear = $formdata->getValue('post', 'conferenceToYear', 'i', false);

    if (empty($conference_title)) {
        $messages[] = _MD_XNPCONFERENCE_CONFERENCE_TITLE_REQUIRED;
    }
    if (empty($place)) {
        $messages[] = _MD_XNPCONFERENCE_PLACE_REQUIRED;
    }
    if (empty($author)) {
        $messages[] = _MD_XNPCONFERENCE_AUTHOR_REQUIRED;
    }
    if (empty($conference_fileFileID) && empty($conference_file['name'])) {
        $messages[] = _MD_XNPCONFERENCE_PRESENTATION_FILE_REQUIRED;
    }
    $conference_from = date('U', mktime(0, 0, 0, $conferenceFromMonth, $conferenceFromDay, $conferenceFromYear));
    $conference_to = date('U', mktime(0, 0, 0, $conferenceToMonth, $conferenceToDay, $conferenceToYear));
    if ($conference_from > $conference_to) {
        $messages[] = _MD_XNPCONFERENCE_DATE_ERROR;
    }

    // require Readme, License and Rights if register to public indexes
    $xids = explode(',', $xoonipsCheckedXID);
    $indexes = array();
    if ($xids[0] != $xoonipsCheckedXID) {
        foreach ($xids as $i) {
            $index = array();
            if (xnp_get_index($xnpsid, $i, $index) == RES_OK) {
                $indexes[] = $index;
            } else {
                $messages[] = '<font color=\'#ff0000\'>'.xnp_get_last_error_string().'</font>';
                $result = false;
                break;
            }
        }
    }

    if (count($messages) == 0) {
        return true;
    }
    $message = "<br />\n".implode("<br />\n", $messages);

    return false;
}

/**
 * check DetailInformation input.
 */
function xnpconferenceCheckEditParameters(&$message)
{
    return xnpconferenceCheckRegisterParameters($message);
}

function xnpconferenceInsertItem(&$item_id)
{
    global $xoopsDB;
    $formdata = &xoonips_getutility('formdata');
    $xnpsid = $_SESSION['XNPSID'];

    // register BasicInformation, Index, Attachment
    $item_id = 0;
    $_POST['publicationDateYear'] = addslashes($formdata->getValue('post', 'conferenceFromYear', 'i', false));
    $_POST['publicationDateMonth'] = addslashes($formdata->getValue('post', 'conferenceFromMonth', 'i', false));
    $_POST['publicationDateDay'] = addslashes($formdata->getValue('post', 'conferenceFromDay', 'i', false));
    $result = xnpInsertBasicInformation($item_id);
    if ($result) {
        $result = xnpUpdateIndex($item_id);
        if ($result) {
            $result = xnpUpdatePreview($item_id);
            if ($result) {
                $result = xnpUpdateAttachment($item_id, 'conference_file');
                if ($result) {
                    $result = xnpUpdateAttachment($item_id, 'conference_paper');
                    if ($result) {
                    }
                }
            }
        }
        if (!$result) {
            xnpDeleteBasicInformation($xnpsid, $item_id);
        }
    }
    if (!$result) {
        return false;
    }

    $keys = implode(',', array('attachment_dl_limit', 'attachment_dl_notify', 'presentation_type', 'conference_title', 'place', 'abstract', 'conference_from_year', 'conference_from_month', 'conference_from_mday', 'conference_to_year', 'conference_to_month', 'conference_to_mday'));

    // trim strings
    $ar = array(
    'presentation_type' => $formdata->getValue('post', 'presentation_type', 's', false),
    'conference_title' => $formdata->getValue('post', 'conference_title', 's', false),
    'place' => $formdata->getValue('post', 'place', 's', false),
    'abstract' => $formdata->getValue('post', 'abstract', 's', false),
    );
    xnpTrimColumn($ar, 'xnpconference_item_detail', array_keys($ar), _CHARSET);
    $attachment_dl_limit = $formdata->getValue('post', 'attachment_dl_limit', 'i', false);
    $attachment_dl_notify = $formdata->getValue('post', 'attachment_dl_notify', 'i', false);
    $vals = implode('\',\'', array($attachment_dl_limit, $attachment_dl_limit ? $attachment_dl_notify : 0, addslashes($ar['presentation_type']), addslashes($ar['conference_title']), addslashes($ar['place']), addslashes($ar['abstract']), addslashes($formdata->getValue('post', 'conferenceFromYear', 'i', false)), addslashes($formdata->getValue('post', 'conferenceFromMonth', 'i', false)), addslashes($formdata->getValue('post', 'conferenceFromDay', 'i', false)), addslashes($formdata->getValue('post', 'conferenceToYear', 'i', false)), addslashes($formdata->getValue('post', 'conferenceToMonth', 'i', false)), addslashes($formdata->getValue('post', 'conferenceToDay', 'i', false))));

    // register DetailInformation
    $sql = 'insert into '.$xoopsDB->prefix('xnpconference_item_detail')." ( conference_id, $keys ) values ( $item_id, '$vals' ) ";
    $result = $xoopsDB->queryF($sql);
    if ($result == false) {
        echo 'cannot insert item_detail';

        return false;
    }

    // insert author
    $author_handler = &xoonips_getormhandler('xnpconference', 'author');
    $author_objs = &$formdata->getObjectArray('post', $author_handler->getTableName(), $author_handler, false);
    if (!$author_handler->updateAllObjectsByForeignKey('conference_id', $item_id, $author_objs)) {
        return false;
    }

    return true;
}

function xnpconferenceUpdateItem($item_id)
{
    global $xoopsDB;
    $formdata = &xoonips_getutility('formdata');
    $xnpsid = $_SESSION['XNPSID'];

    // edit BasicInformation, Index, Preview, Attachment
    $_POST['publicationDateYear'] = addslashes($formdata->getValue('post', 'conferenceFromYear', 'i', false));
    $_POST['publicationDateMonth'] = addslashes($formdata->getValue('post', 'conferenceFromMonth', 'i', false));
    $_POST['publicationDateDay'] = addslashes($formdata->getValue('post', 'conferenceFromDay', 'i', false));
    $result = xnpUpdateBasicInformation($item_id);
    if ($result) {
        $result = xnpUpdateIndex($item_id);
        if ($result) {
            $result = xnpUpdatePreview($item_id);
            if ($result) {
                $result = xnpUpdateAttachment($item_id, 'conference_file');
                if ($result) {
                    $result = xnpUpdateAttachment($item_id, 'conference_paper');
                    if ($result) {
                        $result = xnp_insert_change_log($xnpsid, $item_id, $formdata->getValue('post', 'change_log', 's', false));
                        $result = !$result;
                        if (!$result) {
                            echo ' xnp_insert_change_log failed.';
                        }
                    }
                } else {
                    echo ' xnpUpdateAttachment failed.';
                }
            } else {
                echo ' xnpUpdatePreview failed.';
            }
        } else {
            echo ' xnpUpdateIndex failed.';
        }
    } else {
        echo ' xnpUpdateBasicInformation failed.';
    }
    if (!$result) {
        return false;
    }

    // register DetailInformation
    // trim strings
    $ar = array(
    'presentation_type' => $formdata->getValue('post', 'presentation_type', 's', false),
    'conference_title' => $formdata->getValue('post', 'conference_title', 's', false),
    'place' => $formdata->getValue('post', 'place', 's', false),
    'abstract' => $formdata->getValue('post', 'abstract', 's', false),
    );
    xnpTrimColumn($ar, 'xnpconference_item_detail', array_keys($ar), _CHARSET);

    $attachment_dl_limit = $formdata->getValue('post', 'attachment_dl_limit', 'i', false);
    $attachment_dl_notify = $formdata->getValue('post', 'attachment_dl_notify', 'i', false);
    $sql = implode(',', array('attachment_dl_limit'.'=\''.$attachment_dl_limit.'\'', 'attachment_dl_notify'.'=\''.($attachment_dl_limit ? $attachment_dl_notify : 0).'\'', 'presentation_type'.'=\''.addslashes($ar['presentation_type']).'\'', 'conference_title'.'=\''.addslashes($ar['conference_title']).'\'', 'place'.'=\''.addslashes($ar['place']).'\'', 'abstract'.'=\''.addslashes($ar['abstract']).'\'', 'conference_from_year'.'=\''.addslashes($formdata->getValue('post', 'conferenceFromYear', 'i', false)).'\'', 'conference_from_month'.'=\''.addslashes($formdata->getValue('post', 'conferenceFromMonth', 'i', false)).'\'', 'conference_from_mday'.'=\''.addslashes($formdata->getValue('post', 'conferenceFromDay', 'i', false)).'\'', 'conference_to_year'.'=\''.addslashes($formdata->getValue('post', 'conferenceToYear', 'i', false)).'\'', 'conference_to_month'.'=\''.addslashes($formdata->getValue('post', 'conferenceToMonth', 'i', false)).'\'', 'conference_to_mday'.'=\''.addslashes($formdata->getValue('post', 'conferenceToDay', 'i', false)).'\''));
    $result = $xoopsDB->queryF('update '.$xoopsDB->prefix('xnpconference_item_detail')." set $sql where conference_id = $item_id ");
    if ($result == false) {
        return false;
    }

    // insert/update author
    $author_handler = &xoonips_getormhandler('xnpconference', 'author');
    $author_objs = &$formdata->getObjectArray('post', $author_handler->getTableName(), $author_handler, false);
    if (!$author_handler->updateAllObjectsByForeignKey('conference_id', $item_id, $author_objs)) {
        return false;
    }

    return true;
}

function xnpconferenceGetDetailInformationQuickSearchQuery(&$wheres, &$join, $keywords)
{
    global $xoopsDB;
    $conference_table = $xoopsDB->prefix('xnpconference_item_detail');
    $conference_author_table = $xoopsDB->prefix('xnpconference_author');
    $file_table = $xoopsDB->prefix('xoonips_file');

    $join = " join $conference_author_table on ".$conference_author_table.'.conference_id  = '.$xoopsDB->prefix('xoonips_item_basic').'.item_id ';
    $wheres = xnpGetKeywordsQueries(array("$conference_table.conference_title", "$conference_table.place", "$file_table.caption", "$conference_author_table.author"), $keywords);

    return true;
}

function xnpconferenceGetAdvancedSearchQuery(&$where, &$join)
{
    global $xoopsDB;
    $formdata = &xoonips_getutility('formdata');

    $basic_table = $xoopsDB->prefix('xoonips_item_basic');
    $conference_table = $xoopsDB->prefix('xnpconference_item_detail');
    $conference_author_table = $xoopsDB->prefix('xnpconference_author');
    $file_table = $xoopsDB->prefix('xoonips_file');

    $wheres = array();
    $joins = array();

    $xnpconference_presentation_type = $formdata->getValue('post', 'xnpconference_presentation_type', 's', false);
    $xnpconference_publication_date_from = $formdata->getValue('post', 'xnpconference_publication_date_from', 'i', false);
    $xnpconference_publication_date_to = $formdata->getValue('post', 'xnpconference_publication_date_to', 'i', false);

    $w = xnpGetBasicInformationAdvancedSearchQuery('xnpconference');
    if ($w) {
        $wheres[] = $w;
    }
    if (!empty($xnpconference_presentation_type)) {
        $wheres[] = $conference_table.'.presentation_type = \''.addslashes($xnpconference_presentation_type).'\'';
    }
    $w = xnpGetKeywordQuery($conference_table.'.conference_title', 'xnpconference_conference_title');
    if ($w) {
        $wheres[] = $w;
    }
    $w = xnpGetKeywordQuery($conference_table.'.place', 'xnpconference_place');
    if ($w) {
        $wheres[] = $w;
    }
    $w = xnpGetKeywordQuery($conference_author_table.'.author', 'xnpconference_creator');
    if ($w) {
        $wheres[] = $w;
    }
    $w = xnpGetKeywordQuery($conference_table.'.abstract', 'xnpconference_abstract');
    if ($w) {
        $wheres[] = $w;
    }
    $w = '';
    if (!empty($xnpconference_publication_date_from)) {
        $w .= xnpGetFromQuery($conference_table.'.'.'conference_from', 'xnpconference_publication_date_from');
    }
    if (!empty($xnpconference_publication_date_to)) {
        if ($w != '') {
            $w .= ' AND ';
        }
        $w .= xnpGetToQuery($conference_table.'.'.'conference_to', 'xnpconference_publication_date_to');
    }
    if ($w != '') {
        $wheres[] = $w;
    }
    $w = xnpGetKeywordQuery($file_table.'.caption', 'xnpconference_caption');
    if ($w) {
        $wheres[] = $w;
        $wheres[] = " $file_table.file_type_id = 1";
    }

    $where = implode(' and ', $wheres);
    $join = " join $conference_author_table on ".$conference_author_table.'.conference_id  = '.$xoopsDB->prefix('xoonips_item_basic').'.item_id ';
}

function xnpconferenceGetAdvancedSearchBlock(&$search_var)
{
    // get BasicInformation / Preview / IndexKeywords block
    $basic = xnpGetBasicInformationAdvancedSearchBlock('xnpconference', $search_var);
    $search_var[] = 'xnpconference_presentation_type';
    $search_var[] = 'xnpconference_conference_title';
    $search_var[] = 'xnpconference_place';
    $search_var[] = 'xnpconference_author';
    $search_var[] = 'xnpconference_abstract';

    // set to template
    global $xoopsTpl;
    $tpl = new XoopsTpl();
    // copy variables in $xoopsTpl to $tpl
    $tpl->assign($xoopsTpl->get_template_vars());

    $tpl->assign('presentation_type', array_merge(array('' => 'Any'), xnpconferenceGetTypes()));
    $tpl->assign('presentation_type_selected', 'none');
    $tpl->assign('basic', $basic);
    $tpl->assign('module_name', 'xnpconference');
    $tpl->assign('module_display_name', xnpGetItemTypeDisplayNameByDirname(basename(dirname(__DIR__)), 's'));

    // return as HTML
    return $tpl->fetch('db:xnpconference_search_block.html');
}

function xnpconferenceGetDetailInformationTotalSize($iids)
{
    return xnpGetTotalFileSize($iids);
}

function xnpconferenceGetLicenseRequired($item_id)
{
    global $xoopsDB;

    // get DetailInformation
    $result = $xoopsDB->query('select * from '.$xoopsDB->prefix('xnpconference_item_detail')." where conference_id=$item_id");
    if (!$result) {
        return null;
    }
    $detail = $xoopsDB->fetchArray($result);

    return isset($detail['license']) && $detail['license'] != '';
}

function xnpconferenceGetLicenseStatement($item_id)
{
    global $xoopsDB;

    // get DetailInformation
    $result = $xoopsDB->query('select * from '.$xoopsDB->prefix('xnpconference_item_detail')." where conference_id=$item_id");
    if (!$result) {
        return null;
    }
    $detail = $xoopsDB->fetchArray($result);

    return isset($detail['license']) ? $detail['license'] : '';
}

/**
 * create XML for exporting detail information
 * see xnpExportItem for detail.
 *
 * @see xnpExportItem
 *
 * @param export_path folder that export file is written to
 * @param fhdl file handle that items are exported to
 * @param item_id item id that is exported
 * @param attachment true if attachment files are exported, else false
 *
 * @return true:       success
 * @return false:error
 */
function xnpconferenceExportItem($export_path, $fhdl, $item_id, $attachment)
{
    // get DetailInformation
    if (!$fhdl) {
        return false;
    }

    $handler = &xoonips_getormhandler('xnpconference', 'item_detail');
    $detail = &$handler->get($item_id);
    if (!$detail) {
        return false;
    }

    $authors = '';
    foreach ($detail->getAuthors() as $author) {
        $authors .= '<author>'.$author->getVar('author', 's').'</author>';
    }

    if (!fwrite($fhdl, "<detail id=\"${item_id}\" version=\"1.02\">\n".'<conference_from_year>'.$detail->getVar('conference_from_year', 's')."</conference_from_year>\n".'<conference_from_month>'.$detail->getVar('conference_from_month', 's')."</conference_from_month>\n".'<conference_from_mday>'.$detail->getVar('conference_from_mday', 's')."</conference_from_mday>\n".'<conference_to_year>'.$detail->getVar('conference_to_year', 's')."</conference_to_year>\n".'<conference_to_month>'.$detail->getVar('conference_to_month', 's')."</conference_to_month>\n".'<conference_to_mday>'.$detail->getVar('conference_to_mday', 's')."</conference_to_mday>\n".'<presentation_type>'.$detail->getVar('presentation_type', 's')."</presentation_type>\n".'<conference_title>'.$detail->getVar('conference_title', 's')."</conference_title>\n".'<place>'.$detail->getVar('place', 's')."</place>\n"."<authors>{$authors}</authors>\n".'<abstract>'.$detail->getVar('abstract', 's')."</abstract>\n".'<attachment_dl_limit>'.intval($detail->get('attachment_dl_limit'))."</attachment_dl_limit>\n".'<attachment_dl_notify>'.intval($detail->get('attachment_dl_notify'))."</attachment_dl_notify>\n")) {
        return false;
    }
    if (!($attachment ? xnpExportFile($export_path, $fhdl, $item_id) : true)) {
        return false;
    }
    if (!fwrite($fhdl, "</detail>\n")) {
        return false;
    }

    return true;
}

function xnpconferenceGetModifiedFields($item_id)
{
    $ret = array();
    $detail = xnpconferenceGetDetailInformation($item_id);
    $formdata = &xoonips_getutility('formdata');
    if ($detail) {
        foreach (array('presentation_type' => _MD_XNPCONFERENCE_PRESENTATION_TYPE_LABEL, 'conference_title' => _MD_XNPCONFERENCE_CONFERENCE_TITLE_LABEL, 'place' => _MD_XNPCONFERENCE_PLACE_LABEL, 'abstract' => _MD_XNPCONFERENCE_ABSTRACT_LABEL) as $k => $v) {
            $tmp = $formdata->getValue('post', $k, 's', false);
            if (!array_key_exists($k, $detail) || $tmp === null) {
                continue;
            }
            if ($detail[$k] != $tmp) {
                array_push($ret, $v);
            }
        }

        // was data file modified?
        if (xnpIsAttachmentModified('conference_file', $item_id)) {
            array_push($ret, _MD_XNPCONFERENCE_PRESENTATION_FILE_LABEL);
        }
        if (xnpIsAttachmentModified('conference_paper', $item_id)) {
            array_push($ret, _MD_XNPCONFERENCE_CONFERENCE_PAPER_LABEL);
        }

        // conference date
        $date_map = array(
        'conference_from_year' => 'conferenceFromYear',
        'conference_from_month' => 'conferenceFromMonth',
        'conference_from_mday' => 'conferenceFromDay',
        'conference_to_year' => 'conferenceToYear',
        'conference_to_month' => 'conferenceToMonth',
        'conference_to_mday' => 'conferenceToDay',
        );
        foreach ($date_map as $k => $v) {
            if ($detail[$k] != $formdata->getValue('post', $v, 'i', false)) {
                array_push($ret, _MD_XNPCONFERENCE_DATE_LABEL);
                break;
            }
        }

        $author_handler = &xoonips_getormhandler('xnpconference', 'author');
        $author_objs = &$formdata->getObjectArray('post', $author_handler->getTableName(), $author_handler, false);
        $detail_handler = &xoonips_getormhandler('xnpconference', 'item_detail');
        $detail_orm = &$detail_handler->get($item_id);
        $author_old_objs = &$detail_orm->getAuthors();
        if (!xoonips_is_same_objects($author_old_objs, $author_objs)) {
            array_push($ret, _MD_XNPCONFERENCE_AUTHOR_LABEL);
        }
    }

    return $ret;
}

function xnpconferenceGetTopBlock($itemtype)
{
    return xnpGetTopBlock($itemtype['name'], $itemtype['display_name'], 'images/icon_conference.gif', _MD_XNPCONFERENCE_EXPLANATION, 'xnpconference_presentation_type', xnpconferenceGetTypes());
}

// return 1 if downloadable for login user only
// return 0 if downloadable for everyone
function xnpconferenceGetAttachmentDownloadLimitOption($item_id)
{
    global $xoopsDB;
    $sql = 'select attachment_dl_limit from '.$xoopsDB->prefix('xnpconference_item_detail')." where conference_id=${item_id}";
    $result = $xoopsDB->query($sql);
    if ($result) {
        list($option) = $xoopsDB->fetchRow($result);

        return $option;
    }

    return 0;
}

// return 1 if downloading is notified
// return 0 if downloading is not notified
function xnpconferenceGetAttachmentDownloadNotifyOption($item_id)
{
    global $xoopsDB;
    $sql = 'select attachment_dl_notify from '.$xoopsDB->prefix('xnpconference_item_detail')." where conference_id=${item_id}";
    $result = $xoopsDB->query($sql);
    if ($result) {
        list($notify) = $xoopsDB->fetchRow($result);

        return $notify;
    }

    return 0;
}

function xnpconferenceSupportMetadataFormat($metadataPrefix, $item_id)
{
    if ($metadataPrefix == 'oai_dc' || $metadataPrefix == 'junii2') {
        return true;
    }

    return false;
}

function xnpconferenceGetMetadata($prefix, $item_id)
{
    $mydirpath = dirname(__DIR__);
    $mydirname = basename($mydirpath);
    if (!in_array($prefix, array('oai_dc', 'junii2'))) {
        return false;
    }

    // detail information
    $detail_handler = &xoonips_getormhandler($mydirname, 'item_detail');
    $author_handler = &xoonips_getormhandler($mydirname, 'author');
    $detail_obj = &$detail_handler->get($item_id);
    if (empty($detail_obj)) {
        return false;
    }
    $detail = $detail_obj->getArray();
    $criteria = new Criteria('conference_id', $item_id);
    $criteria->setSort('author_order');
    $author_objs = &$author_handler->getObjects($criteria);
    $detail['authors'] = array();
    foreach ($author_objs as $author_obj) {
        $detail['authors'][] = $author_obj->get('author');
    }
    $detail['conference_from_iso8601'] = xnpISO8601($detail['conference_from_year'], $detail['conference_from_month'], $detail['conference_from_mday']);
    $detail['conference_to_iso8601'] = xnpISO8601($detail['conference_to_year'], $detail['conference_to_month'], $detail['conference_to_mday']);
    $types = xnpconferenceGetTypes();
    $detail['presentation_type_display'] = $types[$detail['presentation_type']];
    // basic information
    $basic = xnpGetBasicInformationArray($item_id);
    $basic['publication_date_iso8601'] = xnpISO8601($basic['publication_year'], $basic['publication_month'], $basic['publication_mday']);
    // indexes
    $indexes = array();
    if (xnp_get_index_id_by_item_id($_SESSION['XNPSID'], $item_id, $xids) == RES_OK) {
        foreach ($xids as $xid) {
            if (xnp_get_index($_SESSION['XNPSID'], $xid, $index) == RES_OK) {
                $indexes[] = xnpGetIndexPathServerString($_SESSION['XNPSID'], $xid);
            }
        }
    }
    // files
    $files = array();
    $mimetypes = array();
    $file_handler = &xoonips_gethandler('xoonips', 'file');
    if ($detail['attachment_dl_limit'] == 0) {
        $files = $file_handler->getFilesInfo($item_id, 'conference_file');
        foreach ($files as $file) {
            if (!in_array($file['mime_type'], $mimetypes)) {
                $mimetypes[] = $file['mime_type'];
            }
        }
    }
    // related to
    $related_to_handler = &xoonips_getormhandler('xoonips', 'related_to');
    $related_to_ids = $related_to_handler->getChildItemIds($item_id);
    $related_tos = array();
    foreach ($related_to_ids as $related_to_id) {
        $related_tos[] = array(
        'item_id' => $related_to_id,
        'item_url' => XOOPS_URL.'/modules/xoonips/detail.php?item_id='.$related_to_id,
        );
    }
    // repository configs
    $xconfig_handler = &xoonips_getormhandler('xoonips', 'config');
    $myxoopsConfigMetaFooter = &xoonips_get_xoops_configs(XOOPS_CONF_METAFOOTER);
    $repository = array(
    'download_file_compression' => $xconfig_handler->getValue('download_file_compression'),
    'nijc_code' => $xconfig_handler->getValue('repository_nijc_code'),
    'publisher' => $xconfig_handler->getValue('repository_publisher'),
    'institution' => $xconfig_handler->getValue('repository_institution'),
    'meta_author' => $myxoopsConfigMetaFooter['meta_author'],
    );
    // conference date
    if ($detail['conference_from_year'] == $detail['conference_to_year']) {
        if ($detail['conference_from_month'] == $detail['conference_to_month']) {
            if ($detail['conference_from_mday'] == $detail['conference_to_mday']) {
                if ($basic['lang'] == 'jpn') {
                    $fmt = "%1\$d\xe5\xb9\xb4%2\$d\xe6\x9c\x88%3\$d\xe6\x97\xa5";
                } else {
                    $fmt = '%7$s %3$d, %1$d';
                }
            } else {
                if ($basic['lang'] == 'jpn') {
                    $fmt = "%1\$d\xe5\xb9\xb4%2\$d\xe6\x9c\x88%3\$d\xe3\x80\x9c%6\$d\xe6\x97\xa5";
                } else {
                    $fmt = '%7$s %3$d-%6$d, %1$d';
                }
            }
        } else {
            if ($basic['lang'] == 'jpn') {
                $fmt = "%1\$d\xe5\xb9\xb4%2\$d\xe6\x9c\x88%3\$d\xe6\x97\xa5\xe3\x80\x9c%5\$d\xe6\x9c\x88%6\$d\xe6\x97\xa5";
            } else {
                $fmt = '%7$s %3$d-%8$s %6$d, %1$d';
            }
        }
    } else {
        if ($basic['lang'] == 'jpn') {
            $fmt = "%1\$d\xe5\xb9\xb4%2\$d\xe6\x9c\x88%3\$d\xe6\x97\xa5\xe3\x80\x9c%4\$d\xe5\xb9\xb4%5\$d\xe6\x9c\x88%6\$d\xe6\x97\xa5";
        } else {
            $fmt = '%7$s %3$d %1$d-%8$s %6$d %4$d';
        }
    }
    $month_str = array(
    1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'May', 6 => 'Jun',
    7 => 'Jul', 8 => 'Aug', 9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dec',
    );
    $detail['conference_date'] = sprintf($fmt, $detail['conference_from_year'], $detail['conference_from_month'], $detail['conference_from_mday'], $detail['conference_to_year'], $detail['conference_to_month'], $detail['conference_to_mday'], $month_str[$detail['conference_from_month']], $month_str[$detail['conference_to_month']]);
    if (_CHARSET != 'UTF-8' && $basic['lang'] = 'jpn') {
        $detail['conference_date'] = mb_convert_encoding($detail['conference_date'], _CHARSET, 'UTF-8');
    }
    // assign template
    global $xoopsTpl;
    $tpl = new XoopsTpl();
    $tpl->plugins_dir[] = XOONIPS_PATH.'/class/smarty/plugins';
    $tpl->assign($xoopsTpl->get_template_vars());
    $tpl->assign('basic', $basic);
    $tpl->assign('detail', $detail);
    $tpl->assign('indexes', $indexes);
    $tpl->assign('files', $files);
    $tpl->assign('mimetypes', $mimetypes);
    $tpl->assign('related_tos', $related_tos);
    $tpl->assign('repository', $repository);
    $xml = $tpl->fetch('db:'.$mydirname.'_oaipmh_'.$prefix.'.xml');

    return $xml;
}
