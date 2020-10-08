<?php

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
defined('XOOPS_ROOT_PATH') || exit('XOOPS root path not defined');

$itemtype_path = dirname(__DIR__);
$itemtype_dirname = basename($itemtype_path);

$langman = &xoonips_getutility('languagemanager');
$langman->read('main.php', $itemtype_dirname);

function _xnpbook_get_detail_request($do_escape = false)
{
    $textutil = &xoonips_getutility('text');
    $detail = array();
    $formdata = &xoonips_getutility('formdata');
    $keys = array(
    'editor' => 's',
    'publisher' => 's',
    'isbn' => 's',
    'url' => 's',
    'attachment_dl_limit' => 'i',
    'attachment_dl_notify' => 'i',
    );
    foreach ($keys as $key => $type) {
        $tmp = $formdata->getValue('post', $key, $type, false);
        if (is_null($tmp)) {
            $detail[$key] = null;
        } else {
            if ('url' == $key) {
                $detail[$key] = preg_replace(array('/javascript:/i', '/[\\x00-\\x20\\x22\\x27]/'), array('', ''), $tmp);
            } elseif ('isbn' == $key) {
                $detail[$key] = preg_replace('/[\\- ]/', '', $tmp);
            } else {
                $detail[$key] = $tmp;
            }
            if ($do_escape && 's' == $type) {
                $detail[$key] = $textutil->html_special_chars($detail[$key]);
            }
        }
    }
    if (isset($detail['attachment_dl_limit'])) {
        if (0 == $detail['attachment_dl_limit']) {
            $detail['attachment_dl_notify'] = 0;
        }
    }

    return $detail;
}

/**
 * @param string $msg
 */
function _xnpbook_append_message($html, $msg)
{
    if ('' != $html) {
        $html .= '<br />';
    }

    return $html.'<span style="color:#ff0000;">'.$msg.'</span>';
}

/**
 * get Detail Information by item_id.
 *
 * @return detail information of xnpbook item
 */
function xnpbookGetDetailInformation($item_id)
{
    $hItemDetail = &xoonips_getormhandler('xnpbook', 'item_detail');
    $oItemDetail = &$hItemDetail->get($item_id);
    if (!is_object($oItemDetail)) {
        return false;
    }
    $detail = array();
    foreach ($oItemDetail->getKeysArray() as $key) {
        $detail[$key] = array(
        'value' => $oItemDetail->getVar($key, 'n'),
        );
    }
    $detail['url']['value'] = preg_replace(array('/javascript:/i', '/[\\x00-\\x20\\x22\\x27]/'), array('', ''), $detail['url']['value']);

    return $detail;
}

function xnpbookGetListBlock($item_basic)
{
    // get uid
    global $xoopsUser;
    $myuid = is_object($xoopsUser) ? $xoopsUser->getVar('uid', 'n') : UID_GUEST;

    // set to template
    global $xoopsTpl;

    $tpl = new XoopsTpl();
    // - copy variables in $xoopsTpl to $tpl
    $tpl->assign($xoopsTpl->get_template_vars());

    $xnpbook_handler = &xoonips_getormcompohandler('xnpbook', 'item');
    $tpl->assign('xoonips_item', $xnpbook_handler->getTemplateVar(XOONIPS_TEMPLATE_TYPE_ITEM_LIST, $item_basic['item_id'], $myuid));

    // return as HTML
    return $tpl->fetch('db:xnpbook_list_block.html');
}

function xnpbookGetPrinterFriendlyListBlock($item_basic)
{
    return xnpbookGetListBlock($item_basic);
}

function xnpbookGetDetailBlock($item_id)
{
    // get uid
    global $xoopsUser;
    $myuid = is_object($xoopsUser) ? $xoopsUser->getVar('uid', 'n') : UID_GUEST;

    // set to template
    global $xoopsTpl;
    $tpl = new XoopsTpl();
    // copy variables in $xoopsTpl to $tpl
    $tpl->assign($xoopsTpl->get_template_vars());
    $tpl->assign('editable', xnp_get_item_permission($_SESSION['XNPSID'], $item_id, OP_MODIFY));
    $tpl->assign('basic', xnpGetBasicInformationDetailBlock($item_id));
    $tpl->assign('index', xnpGetIndexDetailBlock($item_id));
    $tpl->assign('attachment', xnpGetAttachmentDetailBlock($item_id, 'book_pdf'));

    $xnpbook_handler = &xoonips_getormcompohandler('xnpbook', 'item');
    $tpl->assign('xoonips_item', $xnpbook_handler->getTemplateVar(XOONIPS_TEMPLATE_TYPE_ITEM_DETAIL, $item_id, $myuid));

    // return as HTML
    return $tpl->fetch('db:xnpbook_detail_block.html');
}

function xnpbookGetDownloadConfirmationBlock($item_id, $download_file_id)
{
    $detail = xnpbookGetDetailInformation($item_id);

    return xnpGetDownloadConfirmationBlock($item_id, $download_file_id, $detail['attachment_dl_notify']['value'], false, false, false, false, false);
}

function xnpbookGetDownloadConfirmationRequired($item_id)
{
    $detail = xnpbookGetDetailInformation($item_id);

    return $detail['attachment_dl_notify']['value'];
}

function xnpbookGetPrinterFriendlyDetailBlock($item_id)
{
    // get uid
    global $xoopsUser;
    $myuid = is_object($xoopsUser) ? $xoopsUser->getVar('uid', 'n') : UID_GUEST;

    // get BasicInformation / RegisteredItem block
    $basic = xnpGetBasicInformationDetailBlock($item_id);
    $index = xnpGetIndexPrinterFriendlyBlock($item_id);
    $attachment = xnpGetAttachmentPrinterFriendlyBlock($item_id, 'book_pdf');

    // set to template
    global $xoopsTpl;
    $tpl = new XoopsTpl();
    // copy variables in $xoopsTpl to $tpl
    $tpl->assign($xoopsTpl->get_template_vars());
    $tpl->assign('basic', $basic);
    $tpl->assign('index', $index);
    $tpl->assign('attachment', $attachment);

    $xnpbook_handler = &xoonips_getormcompohandler('xnpbook', 'item');
    $tpl->assign('xoonips_item', $xnpbook_handler->getTemplateVar(XOONIPS_TEMPLATE_TYPE_ITEM_DETAIL, $item_id, $myuid));

    // return as HTML
    return $tpl->fetch('db:xnpbook_detail_block.html');
}

function xnpbookGetRegisterBlock()
{
    global $xoopsDB;
    $system_message = '';

    // get BasicInformation / Preview / Readme / License / Rights / index block
    $basic = xnpGetBasicInformationRegisterBlock();
    $index = xnpGetIndexRegisterBlock();
    $attachment = xnpGetAttachmentRegisterBlock('book_pdf');
    // retrive variables from POST array
    $req = _xnpbook_get_detail_request(true);
    $detail = array();
    foreach ($req as $key => $val) {
        if (null !== $val) {
            $detail[$key]['value'] = $val;
        }
    }

    // check amazon access key and secret access key
    $mydirname = basename(dirname(__DIR__));
    $mhandler = &xoops_gethandler('module');
    $module = &$mhandler->getByDirname($mydirname);
    $chandler = &xoops_gethandler('config');
    $mconfig = $chandler->getConfigsByCat(false, $module->mid());
    $amazon_key_exist = true;
    if (empty($mconfig['AccessKey']) || empty($mconfig['SecretAccessKey']) || empty($mconfig['AssociateTag'])) {
        $amazon_key_exist = false;
    }

    // set to template
    global $xoopsTpl;
    $tpl = new XoopsTpl();
    // copy variables in $xoopsTpl to $tpl
    $tpl->assign($xoopsTpl->get_template_vars());
    $tpl->assign('basic', $basic);
    $tpl->assign('index', $index);
    $tpl->assign('attachment', $attachment);
    $tpl->assign('attachment_dl_limit', xnpGetDownloadLimitationOptionRegisterBlock('xnpbook'));
    $tpl->assign('attachment_dl_notify', xnpGetDownloadNotificationOptionRegisterBlock('xnpbook'));
    $tpl->assign('detail', $detail);
    $tpl->assign('system_message', $tpl->get_template_vars('system_message').$system_message);
    $tpl->assign('is_register', true);
    $tpl->assign('myurl', XOOPS_URL.'/modules/xoonips/register.php');
    $tpl->assign('xnpbook_author', xoonips_get_multiple_field_template_vars(xoonips_get_orm_from_post('xnpbook', 'author'), 'xnpbook', 'author'));
    $tpl->assign('amazon_key_exist', $amazon_key_exist);

    // return as HTML
    return $tpl->fetch('db:xnpbook_register_block.html');
}

function xnpbookGetEditBlock($item_id)
{
    $textutil = &xoonips_getutility('text');
    $formdata = &xoonips_getutility('formdata');

    // get BasicInformation / Preview / index block
    $basic = xnpGetBasicInformationEditBlock($item_id);
    $index = xnpGetIndexEditBlock($item_id);
    $attachment = xnpGetAttachmentEditBlock($item_id, 'book_pdf');
    // get DetailInformation
    $detail = xnpbookGetDetailInformation($item_id);
    // override values if post form request
    foreach (_xnpbook_get_detail_request(true) as $key => $val) {
        if (null !== $val) {
            $detail[$key]['value'] = $val;
        }
    }
    // html special chars for each value
    foreach ($detail as $key => $val) {
        $detail[$key]['value'] = $textutil->html_special_chars($detail[$key]['value']);
    }

    // check amazon access key and secret access key
    $mydirname = basename(dirname(__DIR__));
    $mhandler = &xoops_gethandler('module');
    $module = &$mhandler->getByDirname($mydirname);
    $chandler = &xoops_gethandler('config');
    $mconfig = $chandler->getConfigsByCat(false, $module->mid());
    $amazon_key_exist = true;
    if (empty($mconfig['AccessKey']) || empty($mconfig['SecretAccessKey']) || empty($mconfig['AssociateTag'])) {
        $amazon_key_exist = false;
    }

    // set to template
    global $xoopsTpl;
    $tpl = new XoopsTpl();
    // copy variables in $xoopsTpl to $tpl
    $tpl->assign($xoopsTpl->get_template_vars());
    $tpl->assign('basic', $basic);
    $tpl->assign('index', $index);
    $tpl->assign('attachment', $attachment);
    $tpl->assign('attachment_dl_limit', xnpGetDownloadLimitationOptionEditBlock('xnpbook', xnpbookGetAttachmentDownloadLimitOption($item_id)));
    $tpl->assign('attachment_dl_notify', xnpGetDownloadNotificationOptionEditBlock('xnpbook', xnpbookGetAttachmentDownloadNotifyOption($item_id)));
    $tpl->assign('detail', $detail);
    $tpl->assign('system_message', $tpl->get_template_vars('system_message'));
    $tpl->assign('is_register', false);
    $tpl->assign('myurl', XOOPS_URL.'/modules/xoonips/edit.php');

    $formdata = &xoonips_getutility('formdata');
    if (!$formdata->getValue('get', 'post_id', 's', false)) {
        $detail_handler = &xoonips_getormhandler('xnpbook', 'item_detail');
        $detail_orm = &$detail_handler->get($item_id);
        $tpl->assign('xnpbook_author', xoonips_get_multiple_field_template_vars($detail_orm->getAuthors(), 'xnpbook', 'author'));
    } else {
        $tpl->assign('xnpbook_author', xoonips_get_multiple_field_template_vars(xoonips_get_orm_from_post('xnpbook', 'author'), 'xnpbook', 'author'));
    }
    $tpl->assign('amazon_key_exist', $amazon_key_exist);

    // return as HTML
    return $tpl->fetch('db:xnpbook_register_block.html');
}

function xnpbookGetConfirmBlock($item_id)
{
    $textutil = &xoonips_getutility('text');
    $formdata = &xoonips_getutility('formdata');
    $author_handler = &xoonips_getormhandler('xnpbook', 'author');
    $author_objs = &$formdata->getObjectArray('post', $author_handler->getTableName(), $author_handler, false);

    // get BasicInformation / Preview / index block
    $basic = xnpGetBasicInformationConfirmBlock($item_id);
    $index = xnpGetIndexConfirmBlock($item_id);
    $attachment = xnpGetAttachmentConfirmBlock($item_id, 'book_pdf');

    // retrieve detail information
    $detail = array();
    if (!empty($item_id)) {
        $detail = xnpbookGetDetailInformation($item_id);
    }
    $req = _xnpbook_get_detail_request();
    foreach ($req as $key => $val) {
        $detail[$key]['value'] = $val;
    }

    // trim strings
    xnpConfirmHtml($detail, 'xnpbook_item_detail', array_keys($detail), _CHARSET);
    if (xnpHasWithout($basic) || xnpHasWithout($attachment) || xnpHasWithout($detail) || xoonips_is_multiple_field_too_long($author_objs, 'xnpbook', 'author')) {
        global $system_message;
        $system_message = _xnpbook_append_message($system_message, _MD_XOONIPS_ITEM_WARNING_FIELD_TRIM);
    }

    // set to template
    global $xoopsTpl;
    $tpl = new XoopsTpl();
    // copy variables in $xoopsTpl to $tpl
    $tpl->assign($xoopsTpl->get_template_vars());
    $tpl->assign('basic', $basic);
    $tpl->assign('index', $index);
    $tpl->assign('attachment', $attachment);
    $tpl->assign('attachment_dl_limit', xnpGetDownloadLimitationOptionConfirmBlock('xnpbook'));
    $tpl->assign('attachment_dl_notify', xnpGetDownloadNotificationOptionConfirmBlock('xnpbook'));
    $tpl->assign('detail', $detail);
    $tpl->assign('xnpbook_author', xoonips_get_multiple_field_template_vars($author_objs, 'xnpbook', 'author'));

    // return as HTML
    return $tpl->fetch('db:xnpbook_confirm_block.html');
}

function xnpbookInsertItem(&$item_id)
{
    // set fixed value for month and day
    $_POST['publicationDateMonth'] = 1;
    $_POST['publicationDateDay'] = 1;

    // register BasicInformation, Index, Attachment
    $item_id = 0;
    $result = xnpInsertBasicInformation($item_id);
    if ($result) {
        $result = xnpUpdateIndex($item_id);
        if ($result) {
            $result = xnpUpdateAttachment($item_id, 'book_pdf');
        }
        if (!$result) {
            xnpDeleteBasicInformation($_SESSION['XNPSID'], $item_id);
        }
    }
    if (!$result) {
        return false;
    }

    // limit length
    $ar = _xnpbook_get_detail_request();
    xnpTrimColumn($ar, 'xnpbook_item_detail', array_keys($ar), _CHARSET);
    $hItemDetail = &xoonips_getormhandler('xnpbook', 'item_detail');
    $oItemDetail = &$hItemDetail->create();
    $oItemDetail->setVar('book_id', $item_id, true);
    // not gpc
    $oItemDetail->setVars($ar, true);
    // not gpc
    if (!$hItemDetail->insert($oItemDetail)) {
        error_log('xnpbook: cannot insert item_detail - '.implode(', ', $oItemDetail->getErrors()));

        return false;
    }

    // insert author
    $formdata = &xoonips_getutility('formdata');
    $author_handler = &xoonips_getormhandler('xnpbook', 'author');
    $author_objs = &$formdata->getObjectArray('post', $author_handler->getTableName(), $author_handler, false);
    if (!$author_handler->updateAllObjectsByForeignKey('book_id', $item_id, $author_objs)) {
        return false;
    }

    return true;
}

function xnpbookUpdateItem($book_id)
{
    // set fixed value for month and day
    $_POST['publicationDateMonth'] = 1;
    $_POST['publicationDateDay'] = 1;
    $formdata = &xoonips_getutility('formdata');

    // edit BasicInformation, Index, Preview, Attachment
    $result = xnpUpdateBasicInformation($book_id);
    if ($result) {
        $result = xnpUpdateIndex($book_id);
        if ($result) {
            $result = xnpUpdateAttachment($book_id, 'book_pdf');
            if ($result) {
                $result = xnp_insert_change_log($_SESSION['XNPSID'], $book_id, $formdata->getValue('post', 'change_log', 's', false));
                $result = !$result;
                if (!$result) {
                    error_log('xnp_insert_change_log failed.');
                }
            } else {
                error_log('xnpUpdateAttachment failed.');
            }
        } else {
            error_log('xnpUpdateIndex failed.');
        }
    } else {
        error_log('xnpUpdateBasicInformation failed.');
    }

    if (!$result) {
        return false;
    }

    // limit length
    $ar = _xnpbook_get_detail_request();
    xnpTrimColumn($ar, 'xnpbook_item_detail', array_keys($ar), _CHARSET);
    $hItemDetail = &xoonips_getormhandler('xnpbook', 'item_detail');
    $oItemDetail = &$hItemDetail->get($book_id);
    $oItemDetail->setVars($ar, true);
    // not gpc
    if (!$hItemDetail->insert($oItemDetail)) {
        error_log('xnpbook: cannot update item_detail - '.implode(', ', $oItemDetail->getErrors()));

        return false;
    }

    // insert/update author
    $formdata = &xoonips_getutility('formdata');
    $author_handler = &xoonips_getormhandler('xnpbook', 'author');
    $author_objs = &$formdata->getObjectArray('post', $author_handler->getTableName(), $author_handler, false);
    if (!$author_handler->updateAllObjectsByForeignKey('book_id', $book_id, $author_objs)) {
        return false;
    }

    return true;
}

function xnpbookGetSearchBlock($item_id)
{
}

function xnpbookCheckRegisterParameters(&$msg)
{
    $result = true;
    $formdata = &xoonips_getutility('formdata');
    $publisher = $formdata->getValue('post', 'publisher', 's', false);
    if ('' == $publisher) {
        // publisher is not filled
        $msg = _xnpbook_append_message($msg, _MD_XNPBOOK_PUBLISHER_REQUIRED);
        $result = false;
    }
    $publicationDateYear = $formdata->getValue('post', 'publicationDateYear', 'i', false);
    if (0 == $publicationDateYear) {
        // year is not filled
        $msg = _xnpbook_append_message($msg, _MD_XNPBOOK_YEAR_REQUIRED);
        $result = false;
    }

    return $result;
}

function xnpbookCheckEditParameters(&$msg)
{
    return xnpbookCheckRegisterParameters($msg);
}

function xnpbookGetMetaInformation($item_id)
{
    $ret = array();
    $author_array = array();

    $basic = xnpGetBasicInformationArray($item_id);
    $detail = xnpbookGetDetailInformation($item_id);

    if (!empty($basic)) {
        $ret[_MD_XOONIPS_ITEM_TITLE_LABEL] = implode("\n", $basic['titles']);
        $ret[_MD_XOONIPS_ITEM_CONTRIBUTOR_LABEL] = $basic['contributor'];
        $ret[_MD_XOONIPS_ITEM_KEYWORDS_LABEL] = implode("\n", $basic['keywords']);
        $ret[_MD_XOONIPS_ITEM_DESCRIPTION_LABEL] = $basic['description'];
        $ret[_MD_XOONIPS_ITEM_DOI_LABEL] = $basic['doi'];
        $ret[_MD_XOONIPS_ITEM_LAST_UPDATE_DATE_LABEL] = $basic['last_update_date'];
        $ret[_MD_XOONIPS_ITEM_CREATION_DATE_LABEL] = $basic['creation_date'];
    }
    if (!empty($detail)) {
        $ret[_MD_XNPBOOK_EDITOR_LABEL] = $detail['editor']['value'];
        $ret[_MD_XNPBOOK_PUBLISHER_LABEL] = $detail['publisher']['value'];
        $ret[_MD_XNPBOOK_YEAR_LABEL] = $basic['publication_year'];
        $ret[_MD_XNPBOOK_URL_LABEL] = $detail['url']['value'];
        $ret[_MD_XNPBOOK_ISBN_LABEL] = $detail['isbn']['value'];
    }
    $xnpbook_handler = &xoonips_getormcompohandler('xnpbook', 'item');
    $xnpbook = &$xnpbook_handler->get($item_id);
    foreach ($xnpbook->getVar('author') as $author) {
        $author_array[] = $author->getVar('author', 'n');
    }
    $ret[_MD_XNPBOOK_AUTHOR_LABEL] = implode("\n", $author_array);

    return $ret;
}

function xnpbookGetAdvancedSearchBlock(&$search_var)
{
    $basic = xnpGetBasicInformationAdvancedSearchBlock('xnpbook', $search_var);

    $search_var[] = 'xnpbook_author';
    $search_var[] = 'xnpbook_editor';
    $search_var[] = 'xnpbook_publisher';
    $search_var[] = 'xnpbook_isbn';
    $search_var[] = 'xnpbook_book_pdf';

    // set to template
    global $xoopsTpl;

    $tpl = new XoopsTpl();
    $tpl->assign($xoopsTpl->get_template_vars());
    // copy variables in $xoopsTpl to $tpl
    $tpl->assign('basic', $basic);
    $tpl->assign('module_name', 'xnpbook');
    $tpl->assign('module_display_name', xnpGetItemTypeDisplayNameByDirname(basename(dirname(__DIR__)), 's'));

    // return as HTML
    return $tpl->fetch('db:xnpbook_search_block.html');
}

function xnpbookGetAdvancedSearchQuery(&$where, &$join)
{
    global $xoopsDB;

    $book_table = $xoopsDB->prefix('xnpbook_item_detail');
    $book_author = $xoopsDB->prefix('xnpbook_author');

    $wheres = array();
    $w = xnpGetBasicInformationAdvancedSearchQuery('xnpbook');
    if ($w) {
        $wheres[] = $w;
    }
    $w = xnpGetKeywordQuery($book_author.'.author', 'xnpbook_author');
    if ($w) {
        $wheres[] = $w;
    }
    $w = xnpGetKeywordQuery($book_table.'.editor', 'xnpbook_editor');
    if ($w) {
        $wheres[] = $w;
    }
    $w = xnpGetKeywordQuery($book_table.'.publisher', 'xnpbook_publisher');
    if ($w) {
        $wheres[] = $w;
    }
    $w = xnpGetKeywordQuery($book_table.'.isbn', 'xnpbook_isbn');
    if ($w) {
        $wheres[] = $w;
    }
    $formdata = &xoonips_getutility('formdata');
    $xnpbook_book_pdf = $formdata->getValue('post', 'xnpbook_book_pdf', 's', false);
    if (!empty($xnpbook_book_pdf)) {
        $search_text_table = $xoopsDB->prefix('xoonips_search_text');
        $file_table = $xoopsDB->prefix('xoonips_file');
        $searchutil = &xoonips_getutility('search');
        $fulltext_query = $xnpbook_book_pdf;
        $fulltext_encoding = mb_detect_encoding($fulltext_query);
        $fulltext_criteria = new CriteriaCompo($searchutil->getFulltextSearchCriteria('search_text', $fulltext_query, $fulltext_encoding, $search_text_table));
        $fulltext_criteria->add(new Criteria('is_deleted', 0, '=', $file_table));
        $wheres[] = $fulltext_criteria->render();
    }

    $where = implode(' AND ', $wheres);
    $join = " INNER JOIN $book_author ON ".$book_author.'.book_id  = '.$xoopsDB->prefix('xoonips_item_basic').'.item_id ';
}

function xnpbookGetDetailInformationQuickSearchQuery(&$wheres, &$join, $keywords)
{
    global $xoopsDB;

    $book_table = $xoopsDB->prefix('xnpbook_item_detail');
    $book_author_table = $xoopsDB->prefix('xnpbook_author');

    $colnames = array(
    $book_table.'.editor',
    $book_table.'.publisher',
    "$book_author_table.author",
    );

    $join = " INNER JOIN $book_author_table ON ".$book_author_table.'.book_id  = '.$xoopsDB->prefix('xoonips_item_basic').'.item_id ';
    $wheres = xnpGetKeywordsQueries($colnames, $keywords);

    return true;
}

function xnpbookGetDetailInformationTotalSize($iids)
{
    return xnpGetTotalFileSize($iids);
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
function xnpbookExportItem($export_path, $fhdl, $item_id, $attachment)
{
    // get DetailInformation
    if (!$fhdl) {
        return false;
    }

    $handler = &xoonips_getormhandler('xnpbook', 'item_detail');
    $detail = &$handler->get($item_id);
    if (!$detail) {
        return false;
    }

    $authors = '';
    foreach ($detail->getAuthors() as $author) {
        $authors .= '<author>'.$author->getVar('author', 's').'</author>';
    }

    if (!fwrite($fhdl, "<detail id=\"${item_id}\" version=\"1.03\">\n"."<authors>{$authors}</authors>\n".'<editor>'.$detail->getVar('editor', 's')."</editor>\n".'<publisher>'.$detail->getVar('publisher', 's')."</publisher>\n".'<isbn>'.$detail->getVar('isbn', 's')."</isbn>\n".'<url>'.$detail->getVar('url', 's')."</url>\n".'<attachment_dl_limit>'.intval($detail->get('attachment_dl_limit'))."</attachment_dl_limit>\n".'<attachment_dl_notify>'.intval($detail->get('attachment_dl_notify'))."</attachment_dl_notify>\n")) {
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
function xnpbookGetLicenseRequired($item_id)
{
    return false;
}
function xnpbookGetLicenseStatement($item_id)
{
    return null;
}

function xnpbookGetModifiedFields($item_id)
{
    $ret = array();
    $detail = xnpbookGetDetailInformation($item_id);
    $formdata = &xoonips_getutility('formdata');
    if ($detail) {
        foreach (array('editor' => _MD_XNPBOOK_EDITOR_LABEL, 'publisher' => _MD_XNPBOOK_PUBLISHER_LABEL, 'isbn' => _MD_XNPBOOK_ISBN_LABEL, 'url' => _MD_XNPBOOK_URL_LABEL) as $k => $v) {
            $tmp = $formdata->getValue('post', $k, 's', false);
            if (!array_key_exists($k, $detail) || null === $tmp) {
                continue;
            }
            if ($detail[$k]['value'] != $tmp) {
                array_push($ret, $v);
            }
        }

        // was pdf file modified?
        if (xnpIsAttachmentModified('book_pdf', $item_id)) {
            array_push($ret, _MD_XNPBOOK_PDF_LABEL);
        }

        $formdata = &xoonips_getutility('formdata');
        $author_handler = &xoonips_getormhandler('xnpbook', 'author');
        $author_objs = &$formdata->getObjectArray('post', $author_handler->getTableName(), $author_handler, false);
        $detail_handler = &xoonips_getormhandler('xnpbook', 'item_detail');
        $detail_orm = &$detail_handler->get($item_id);
        $author_old_objs = &$detail_orm->getAuthors();
        if (!xoonips_is_same_objects($author_old_objs, $author_objs)) {
            array_push($ret, _MD_XNPBOOK_AUTHOR_LABEL);
        }
    }

    return $ret;
}

function xnpbookGetTopBlock($itemtype)
{
    return xnpGetTopBlock($itemtype['name'], $itemtype['display_name'], 'images/icon_book.gif', _MD_XNPBOOK_EXPLANATION, false, false);
}

// return 1 if downloadable for login user only
// return 0 if downloadable for everyone
function xnpbookGetAttachmentDownloadLimitOption($item_id)
{
    $hItemDetail = &xoonips_getormhandler('xnpbook', 'item_detail');
    $oItemDetail = &$hItemDetail->get($item_id);
    if (!is_object($oItemDetail)) {
        return 0;
    }

    return $oItemDetail->getVar('attachment_dl_limit', 's');
}

// return 1 if downloading is notified
// return 0 if downloading is not notified
function xnpbookGetAttachmentDownloadNotifyOption($item_id)
{
    $hItemDetail = &xoonips_getormhandler('xnpbook', 'item_detail');
    $oItemDetail = &$hItemDetail->get($item_id);
    if (!is_object($oItemDetail)) {
        return 0;
    }

    return $oItemDetail->getVar('attachment_dl_notify', 's');
}

function xnpbookSupportMetadataFormat($metadataPrefix, $item_id)
{
    if ('oai_dc' == $metadataPrefix || 'junii2' == $metadataPrefix) {
        return true;
    }

    return false;
}

function xnpbookGetMetadata($prefix, $item_id)
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
    $criteria = new Criteria('book_id', $item_id);
    $criteria->setSort('author_order');
    $author_objs = &$author_handler->getObjects($criteria);
    $detail['authors'] = array();
    foreach ($author_objs as $author_obj) {
        $detail['authors'][] = $author_obj->get('author');
    }
    // basic information
    $basic = xnpGetBasicInformationArray($item_id);
    $basic['publication_date_iso8601'] = xnpISO8601($basic['publication_year'], $basic['publication_month'], $basic['publication_mday']);
    // indexes
    $indexes = array();
    if (RES_OK == xnp_get_index_id_by_item_id($_SESSION['XNPSID'], $item_id, $xids)) {
        foreach ($xids as $xid) {
            if (RES_OK == xnp_get_index($_SESSION['XNPSID'], $xid, $index)) {
                $indexes[] = xnpGetIndexPathServerString($_SESSION['XNPSID'], $xid);
            }
        }
    }
    // files
    $files = array();
    $mimetypes = array();
    if (0 == $detail['attachment_dl_limit']) {
        $file_handler = &xoonips_gethandler('xoonips', 'file');
        $files = $file_handler->getFilesInfo($item_id, 'book_pdf');
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

function xnpbook_get_list_block_array($item_id)
{
    $item_basic_handler = &xoonips_getormhandler('xoonips', 'item_basic');
    $item_basic = $item_basic_handler->get($item_id);

    $item_title_handler = &xoonips_getormhandler('xoonips', 'title');
    $criteria = new Criteria('item_id', $item_id);
    $criteria->setSort('title_id');
    $item_titles = &$item_title_handler->getObjects($criteria);
    $titles = array();
    foreach ($item_titles as $t) {
        $titles[] = $t->getVarArray('n');
    }

    $item_detail_handler = &xoonips_getormhandler('xnpbook', 'item_detail');
    $item_detail = $item_detail_handler->get($item_id);

    return array('basic' => $item_basic->getVarArray('n'), 'titles' => $titles, 'detail' => $item_detail->getVarArray('n'), 'pending' => xnpIsPending($item_id) ? true : false);
}
