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

function _xnppaper_get_form_request()
{
    $formdata = &xoonips_getutility('formdata');
    $ret['journal'] = $formdata->getValue('post', 'journal', 's', false);
    $ret['volume'] = $formdata->getValue('post', 'volume', 's', false);
    $ret['number'] = $formdata->getValue('post', 'number', 's', false);
    $ret['page'] = $formdata->getValue('post', 'page', 's', false);
    $ret['abstract'] = $formdata->getValue('post', 'abstract', 's', false);
    $ret['pubmed_id'] = $formdata->getValue('post', 'pubmed_id', 's', false);

    return $ret;
}

function &_xnppaper_get_detail_information_objs($item_id)
{
    $ret = array();
    $ret['detail'] = array();
    $ret['authors'] = array();
    $detail_handler = &xoonips_getormhandler('xnppaper', 'item_detail');
    $ret['detail'] = &$detail_handler->get($item_id);
    if (is_object($ret['detail'])) {
        $ret['authors'] = &$ret['detail']->getAuthors();
    }

    return $ret;
}

function _xnppaper_get_detail_information_array($meta, $fmt)
{
    $ret = array();
    $ret['detail'] = array();
    $ret['authors'] = array();
    if (is_object($meta['detail'])) {
        $ret['detail'] = $meta['detail']->getVarArray($fmt);
    }
    foreach ($meta['authors'] as $author_obj) {
        $ret['authors'][] = $author_obj->getVarArray($fmt);
    }

    return $ret;
}

function _xnppaper_concat_array($data, $key, $sep)
{
    $str = '';
    foreach ($data as $datum) {
        $str .= $datum[$key];
        $str .= $sep;
    }

    return trim($str);
}

function &xnppaper_create_pubmed_link($pmid)
{
    $pubmed_id = trim(strval($pmid));
    if (preg_match('/^(\\d+)$/', $pubmed_id)) {
        $pubmed_link = '<a href="http://www.ncbi.nlm.nih.gov/entrez/query.fcgi?cmd=Retrieve&amp;db=pubmed&amp;list_uids='.$pubmed_id.'" target="_blank">'.$pubmed_id.'</a>';
    } else {
        $pubmed_link = htmlspecialchars($pmid, ENT_QUOTES);
    }

    return $pubmed_link;
}

/**
 * retrieve Detail Information that specified by item_id.
 */
function xnppaperGetDetailInformation($item_id)
{
    global $xoopsDB;
    if (empty($item_id)) {
        return array('journal' => '', 'volume' => '', 'number' => '', 'page' => '', 'abstract' => '', 'pubmed_id' => '');
    }

    $sql = 'select * from '.$xoopsDB->prefix('xnppaper_item_detail')." where paper_id=$item_id";
    $result = $xoopsDB->query($sql);
    if ($result == false) {
        return false;
    }

    return $xoopsDB->fetchArray($result);
}

function xnppaperGetMetaInformation($item_id)
{
    $ret = array();
    $author_array = array();
    $basic = xnpGetBasicInformationArray($item_id, 'n');
    $meta_objs = &_xnppaper_get_detail_information_objs($item_id);
    $meta = _xnppaper_get_detail_information_array($meta_objs, 'n');
    $detail = $meta['detail'];
    if (!empty($basic)) {
        $ret[_MD_XOONIPS_ITEM_TITLE_LABEL] = implode("\n", $basic['titles']);
        $ret[_MD_XOONIPS_ITEM_CONTRIBUTOR_LABEL] = $basic['contributor'];
        $ret[_MD_XOONIPS_ITEM_KEYWORDS_LABEL] = implode("\n", $basic['keywords']);
        $ret[_MD_XOONIPS_ITEM_DESCRIPTION_LABEL] = $basic['description'];
        $ret[_MD_XOONIPS_ITEM_DOI_LABEL] = $basic['doi'];
        $ret[_MD_XOONIPS_ITEM_CREATION_DATE_LABEL] = $basic['creation_date'];
        $ret[_MD_XOONIPS_ITEM_LAST_UPDATE_DATE_LABEL] = $basic['last_update_date'];
    }
    if (!empty($detail)) {
        $ret[_MD_XNPPAPER_JOURNAL_LABEL] = $detail['journal'];
    }
    if (!empty($basic)) {
        $ret[_MD_XNPPAPER_YEAR_OF_PUBLICATION_LABEL] = $basic['publication_year'];
    }
    if (!empty($detail)) {
        $ret[_MD_XNPPAPER_VOLUME_LABEL] = $detail['volume'];
        $ret[_MD_XNPPAPER_NUMBER_LABEL] = $detail['number'];
        $ret[_MD_XNPPAPER_PAGE_LABEL] = $detail['page'];
        $ret[_MD_XNPPAPER_ABSTRACT_LABEL] = $detail['abstract'];
        $ret[_MD_XNPPAPER_PUBMED_ID_LABEL] = $detail['pubmed_id'];
    }
    $xnppaper_handler = &xoonips_getormcompohandler('xnppaper', 'item');
    $xnppaper = &$xnppaper_handler->get($item_id);
    foreach ($xnppaper->getVar('author') as $author) {
        $author_array[] = $author->getVar('author', 'n');
    }
    $ret[_MD_XNPPAPER_AUTHOR_LABEL] = implode("\n", $author_array);

    return $ret;
}

function xnppaperGetListBlock($item_basic)
{
    // get uid
    global $xoopsUser;
    $myuid = is_object($xoopsUser) ? $xoopsUser->getVar('uid', 'n') : UID_GUEST;
    // set to template
    global $xoopsTpl;

    $tpl = new XoopsTpl();
    // copy variables in $xoopsTpl to $tpl
    $tpl->assign($xoopsTpl->get_template_vars());

    $xnppaper_handler = &xoonips_getormcompohandler('xnppaper', 'item');
    $tpl->assign('xoonips_item', $xnppaper_handler->getTemplateVar(XOONIPS_TEMPLATE_TYPE_ITEM_LIST, $item_basic['item_id'], $myuid));

    // return as HTML
    return $tpl->fetch('db:xnppaper_list_block.html');
}

function xnppaperGetPrinterFriendlyListBlock($item_basic)
{
    return xnppaperGetListBlock($item_basic);
}

function xnppaperGetDetailBlock($item_id)
{
    // get uid
    global $xoopsUser;
    $myuid = is_object($xoopsUser) ? $xoopsUser->getVar('uid', 'n') : UID_GUEST;
    global $xoopsTpl;
    $mhandler = &xoops_gethandler('module');
    $chandler = &xoops_gethandler('config');

    $module = $mhandler->getByDirname('xnppaper');
    $assoc = $chandler->getConfigsByCat(false, $module->mid());

    // set to template
    $tpl = new XoopsTpl();
    // copy variables in $xoopsTpl to $tpl
    $tpl->assign($xoopsTpl->get_template_vars());

    $tpl->assign('editable', xnp_get_item_permission($_SESSION['XNPSID'], $item_id, OP_MODIFY));
    $tpl->assign('basic', xnpGetBasicInformationDetailBlock($item_id));
    $tpl->assign('index', xnpGetIndexDetailBlock($item_id));
    $tpl->assign('paper_pdf_reprint', xnpGetAttachmentDetailBlock($item_id, 'paper_pdf_reprint'));

    // Make sure that this user access to item_id is permitted or not
    $tpl->assign('show_pdf', ($assoc['pdf_access_rights'] <= xnpGetAccessRights($item_id)));
    $tpl->assign('show_abstract', ($assoc['abstract_access_rights'] <= xnpGetAccessRights($item_id)));

    $xnppaper_handler = &xoonips_getormcompohandler('xnppaper', 'item');
    $tpl->assign('xoonips_item', $xnppaper_handler->getTemplateVar(XOONIPS_TEMPLATE_TYPE_ITEM_DETAIL, $item_id, $myuid));

    // return as HTML
    return $tpl->fetch('db:xnppaper_detail_block.html');
}

function xnppaperGetPrinterFriendlyDetailBlock($item_id)
{
    // get uid
    global $xoopsUser;
    $myuid = is_object($xoopsUser) ? $xoopsUser->getVar('uid', 'n') : UID_GUEST;
    global $xoopsTpl;
    $mhandler = &xoops_gethandler('module');
    $chandler = &xoops_gethandler('config');

    $module = $mhandler->getByDirname('xnppaper');
    $assoc = $chandler->getConfigsByCat(false, $module->mid());

    // set to template
    $tpl = new XoopsTpl();
    // copy variables in $xoopsTpl to $tpl
    $tpl->assign($xoopsTpl->get_template_vars());

    $tpl->assign('editable', xnp_get_item_permission($_SESSION['XNPSID'], $item_id, OP_MODIFY));
    $tpl->assign('basic', xnpGetBasicInformationPrinterFriendlyBlock($item_id));
    $tpl->assign('index', xnpGetIndexPrinterFriendlyBlock($item_id));
    $tpl->assign('paper_pdf_reprint', xnpGetAttachmentPrinterFriendlyBlock($item_id, 'paper_pdf_reprint'));

    // Make sure that this user access to item_id is permitted or not
    $tpl->assign('show_pdf', ($assoc['pdf_access_rights'] <= xnpGetAccessRights($item_id)));
    $tpl->assign('show_abstract', ($assoc['abstract_access_rights'] <= xnpGetAccessRights($item_id)));

    $xnppaper_handler = &xoonips_getormcompohandler('xnppaper', 'item');
    $tpl->assign('xoonips_item', $xnppaper_handler->getTemplateVar(XOONIPS_TEMPLATE_TYPE_ITEM_DETAIL, $item_id, $myuid));

    // return as HTML
    return $tpl->fetch('db:xnppaper_detail_block.html');
}

function xnppaperGetRegisterBlock()
{
    global $xoopsDB;
    $formdata = &xoonips_getutility('formdata');
    $textutil = &xoonips_getutility('text');
    if ($formdata->getValue('get', 'post_id', 's', false)) {
        $detail = _xnppaper_get_form_request();
        foreach ($detail as $key => $val) {
            $detail[$key] = $textutil->html_special_chars($detail[$key]);
        }
    } else {
        $detail = array();
    }

    // retrieve blocks of BasicInformation / index block
    $basic = xnpGetBasicInformationRegisterBlock();
    $index = xnpGetIndexRegisterBlock();
    $paper_pdf_reprint = xnpGetAttachmentRegisterBlock('paper_pdf_reprint');

    // assingn to template
    global $xoopsTpl;
    $tpl = new XoopsTpl();
    $tpl->assign($xoopsTpl->get_template_vars());
    // variables assigned to xoopsTpl are copied to $tpl
    $tpl->assign('basic', $basic);
    $tpl->assign('index', $index);
    $tpl->assign('paper_pdf_reprint', $paper_pdf_reprint);
    $tpl->assign('detail', $detail);
    $tpl->assign('xnppaper_author', xoonips_get_multiple_field_template_vars(xoonips_get_orm_from_post('xnppaper', 'author'), 'xnppaper', 'author'));

    // for pubmed fillin
    $tpl->assign('is_register', true);
    $tpl->assign('myurl', XOOPS_URL.'/modules/xoonips/edit.php');

    // return HTML
    return $tpl->fetch('db:xnppaper_register_block.html');
}

function xnppaperGetEditBlock($item_id)
{
    global $xoopsDB;
    $formdata = &xoonips_getutility('formdata');
    $textutil = &xoonips_getutility('text');

    // retrieve blocks of BasicInformation / index
    $basic = xnpGetBasicInformationEditBlock($item_id);
    $index = xnpGetIndexEditBlock($item_id);
    $paper_pdf_reprint = xnpGetAttachmentEditBlock($item_id, 'paper_pdf_reprint');

    // retrieve DetailInformation
    $title = $formdata->getValue('post', 'title', 's', false);
    if (isset($title)) {
        $detail = _xnppaper_get_form_request();
    } elseif (!empty($item_id)) {
        $detail = xnppaperGetDetailInformation($item_id);
        $item = array();
        $result = xnp_get_item($_SESSION['XNPSID'], $item_id, $item);
    } else {
        $detail = array();
    }
    foreach ($detail as $key => $val) {
        $detail[$key] = $textutil->html_special_chars($detail[$key]);
    }

    // assign to template
    global $xoopsTpl;
    $tpl = new XoopsTpl();
    $tpl->assign($xoopsTpl->get_template_vars());
    // variables assigned to xoopsTpl are copied to $tpl
    $tpl->assign('basic', $basic);
    $tpl->assign('index', $index);
    $tpl->assign('paper_pdf_reprint', $paper_pdf_reprint);
    $tpl->assign('detail', $detail);

    if (!$formdata->getValue('get', 'post_id', 's', false)) {
        $detail_handler = &xoonips_getormhandler('xnppaper', 'item_detail');
        $detail_orm = &$detail_handler->get($item_id);
        $tpl->assign('xnppaper_author', xoonips_get_multiple_field_template_vars($detail_orm->getAuthors(), 'xnppaper', 'author'));
    } else {
        $tpl->assign('xnppaper_author', xoonips_get_multiple_field_template_vars(xoonips_get_orm_from_post('xnppaper', 'author'), 'xnppaper', 'author'));
    }

    // for pubmed fillin
    $tpl->assign('is_register', false);
    $tpl->assign('myurl', XOOPS_URL.'/modules/xoonips/edit.php');

    // return HTML
    return $tpl->fetch('db:xnppaper_register_block.html');
}

function xnppaperGetConfirmBlock($item_id)
{
    $textutil = &xoonips_getutility('text');
    $formdata = &xoonips_getutility('formdata');
    $author_handler = &xoonips_getormhandler('xnppaper', 'author');
    $author_objs = &$formdata->getObjectArray('post', $author_handler->getTableName(), $author_handler, false);

    // retrieve blocks of BasicInformation / index
    $basic = xnpGetBasicInformationConfirmBlock($item_id);
    $index = xnpGetIndexConfirmBlock($item_id);
    $paper_pdf_reprint = xnpGetAttachmentConfirmBlock($item_id, 'paper_pdf_reprint');

    // retrieve DetailInformation
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $detail = array(
            'journal' => array(
                'value' => $textutil->html_special_chars($formdata->getValue('post', 'journal', 's', false)),
            ),
            'volume' => array(
                'value' => $formdata->getValue('post', 'volume', 'i', false),
            ),
            'number' => array(
                'value' => $formdata->getValue('post', 'number', 'i', false),
            ),
            'page' => array(
                'value' => $textutil->html_special_chars($formdata->getValue('post', 'page', 's', false)),
            ),
            'abstract' => array(
                'value' => $textutil->html_special_chars($formdata->getValue('post', 'abstract', 's', false)),
            ),
            'pubmed_id' => array(
                'value' => $textutil->html_special_chars($formdata->getValue('post', 'pubmed_id', 's', false)),
            ),
        );
    } else {
        $detail = array();
    }
    // trim strings
    xnpConfirmHtml($detail, 'xnppaper_item_detail', array_keys($detail), _CHARSET);
    if (xnpHasWithout($basic) || xnpHasWithout($paper_pdf_reprint) || xnpHasWithout($detail) || xoonips_is_multiple_field_too_long($author_objs, 'xnppaper', 'author')) {
        global $system_message;
        $system_message = $system_message."\n<br /><font color='#ff0000'>"._MD_XOONIPS_ITEM_WARNING_FIELD_TRIM.'</font><br />';
    }
    // TODO: formdata integration
    if ($detail['volume']['value'] == 0) {
        $detail['volume']['value'] = '';
    }
    if ($detail['number']['value'] == 0) {
        $detail['number']['value'] = '';
    }

    // assign to template
    global $xoopsTpl;
    $tpl = new XoopsTpl();
    // variables assigned to xoopsTpl are copied to $tpl
    $tpl->assign($xoopsTpl->get_template_vars());

    $tpl->assign('basic', $basic);
    $tpl->assign('index', $index);
    $tpl->assign('paper_pdf_reprint', $paper_pdf_reprint);
    $tpl->assign('detail', $detail);
    $tpl->assign('xnppaper_author', xoonips_get_multiple_field_template_vars($author_objs, 'xnppaper', 'author'));

    // return HTML
    return $tpl->fetch('db:xnppaper_confirm_block.html');
}

/**
 * make sure that enterd detail information is correctly or not.
 * called from register confirmation and edit confirmation.
 */
function xnppaperCheckRegisterParameters(&$message)
{
    $messages = array();
    $formdata = &xoonips_getutility('formdata');
    $author = xoonips_get_multi_field_array_from_post('xnppaper', 'author');
    $journal = $formdata->getValue('post', 'journal', 's', false);
    $publicationDateYear = $formdata->getValue('post', 'publicationDateYear', 'i', false);

    if (empty($author)) {
        $messages[] = _MD_XNPPAPER_AUTHOR_REQUIRED;
    }
    if (empty($journal)) {
        $messages[] = _MD_XNPPAPER_JOURNAL_REQUIRED;
    }
    if (empty($publicationDateYear)) {
        $messages[] = _MD_XNPPAPER_YEAR_OF_PUBLICATION_REQUIRED;
    }
    if (count($messages) == 0) {
        return true;
    }
    $message = implode('', $messages);

    return false;
}

/**
 * make sure that enterd detail information is correctly or not.
 */
function xnppaperCheckEditParameters(&$message)
{
    return xnppaperCheckRegisterParameters($message);
}

function xnppaperInsertItem(&$item_id)
{
    global $xoopsDB;
    $formdata = &xoonips_getutility('formdata');
    $xnpsid = $_SESSION['XNPSID'];

    // register BasicInformation, Index, Attachment
    $item_id = 0;
    $result = xnpInsertBasicInformation($item_id);
    if ($result) {
        $result = xnpUpdateIndex($item_id);
        if ($result) {
            $result = xnpUpdateAttachment($item_id, 'paper_pdf_reprint');
            if ($result) {
            }
        }
        if (!$result) {
            xnpDeleteBasicInformation($xnpsid, $item_id);
        }
    }
    if (!$result) {
        return false;
    }

    // limit length
    $journal = $formdata->getValue('post', 'journal', 's', false);
    $volume = $formdata->getValue('post', 'volume', 's', false);
    $number = $formdata->getValue('post', 'number', 's', false);
    $page = $formdata->getValue('post', 'page', 's', false);
    $abstract = $formdata->getValue('post', 'abstract', 's', false);
    $pubmed_id = $formdata->getValue('post', 'pubmed_id', 's', false);

    $ar = array(
    'journal' => $journal,
    'page' => $page,
    'abstract' => $abstract,
    'pubmed_id' => $pubmed_id,
    );
    xnpTrimColumn($ar, 'xnppaper_item_detail', array_keys($ar), _CHARSET);

    $keys = implode(',', array('journal', 'volume', 'number', 'page', 'abstract', 'pubmed_id'));
    $vals = implode(',', array('\''.addslashes($ar['journal']).'\'', strlen($volume) == 0 ? 'null' : (int) $volume, strlen($number) == 0 ? 'null' : (int) $number, strlen($page) == 0 ? 'null' : '\''.addslashes($ar['page']).'\'', strlen($abstract) == 0 ? 'null' : '\''.addslashes($ar['abstract']).'\'', strlen($pubmed_id) == 0 ? 'null' : '\''.addslashes($ar['pubmed_id']).'\''));

    // register detail information
    $sql = 'insert into '.$xoopsDB->prefix('xnppaper_item_detail')." ( paper_id, $keys ) values ( $item_id, $vals ) ";
    $result = $xoopsDB->queryF($sql);
    if ($result == false) {
        return false;
    }
    // insert author
    $author_handler = &xoonips_getormhandler('xnppaper', 'author');
    $author_objs = &$formdata->getObjectArray('post', $author_handler->getTableName(), $author_handler, false);
    if (!$author_handler->updateAllObjectsByForeignKey('paper_id', $item_id, $author_objs)) {
        return false;
    }

    return true;
}

function xnppaperUpdateItem($item_id)
{
    global $xoopsDB;
    $formdata = &xoonips_getutility('formdata');

    $xnpsid = $_SESSION['XNPSID'];

    // modify BasicInformation, Index, Attachment
    $result = xnpUpdateBasicInformation($item_id);
    if ($result) {
        $result = xnpUpdateIndex($item_id);
        if ($result) {
            $result = xnpUpdateAttachment($item_id, 'paper_pdf_reprint');
            if ($result) {
                $result = xnp_insert_change_log($xnpsid, $item_id, $formdata->getValue('post', 'change_log', 's', false));
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

    // trim strings
    $journal = $formdata->getValue('post', 'journal', 's', false);
    $volume = $formdata->getValue('post', 'volume', 's', false);
    $number = $formdata->getValue('post', 'number', 's', false);
    $page = $formdata->getValue('post', 'page', 's', false);
    $abstract = $formdata->getValue('post', 'abstract', 's', false);
    $pubmed_id = $formdata->getValue('post', 'pubmed_id', 's', false);
    $ar = array(
    'journal' => $journal,
    'page' => $page,
    'abstract' => $abstract,
    'pubmed_id' => $pubmed_id,
    );
    xnpTrimColumn($ar, 'xnppaper_item_detail', array_keys($ar), _CHARSET);

    // register detail information
    $sql = implode(',', array('journal'.'=\''.addslashes($ar['journal']).'\'', 'volume'.'='.(strlen($volume) == 0 ? 'null' : (int) $volume), 'number'.'='.(strlen($number) == 0 ? 'null' : (int) $number), 'page'.'='.(strlen($page) == 0 ? 'null' : '\''.addslashes($ar['page']).'\''), 'abstract'.'='.(strlen($abstract) == 0 ? 'null' : '\''.addslashes($ar['abstract']).'\''), 'pubmed_id'.'='.(strlen($pubmed_id) == 0 ? 'null' : '\''.addslashes($ar['pubmed_id']).'\'')));
    $result = $xoopsDB->queryF('update '.$xoopsDB->prefix('xnppaper_item_detail')." set $sql where paper_id = $item_id ");
    if ($result == false) {
        return false;
    }

    // insert/update author
    $author_handler = &xoonips_getormhandler('xnppaper', 'author');
    $author_objs = &$formdata->getObjectArray('post', $author_handler->getTableName(), $author_handler, false);
    if (!$author_handler->updateAllObjectsByForeignKey('paper_id', $item_id, $author_objs)) {
        return false;
    }

    return true;
}

function xnppaperGetDetailInformationQuickSearchQuery(&$wheres, &$join, $keywords)
{
    global $xoopsDB;
    $paper_table = $xoopsDB->prefix('xnppaper_item_detail');
    $paper_author_table = $xoopsDB->prefix('xnppaper_author');
    $file_table = $xoopsDB->prefix('xoonips_file');

    $join = " INNER JOIN $paper_author_table ON ".$paper_author_table.'.paper_id  = '.$xoopsDB->prefix('xoonips_item_basic').'.item_id ';
    $wheres = xnpGetKeywordsQueries(array("$paper_table.journal", "$paper_table.pubmed_id", "$paper_author_table.author"), $keywords);

    return true;
}

function xnppaperGetAdvancedSearchQuery(&$where, &$join)
{
    global $xoopsDB;
    $formdata = &xoonips_getutility('formdata');
    $paper_table = $xoopsDB->prefix('xnppaper_item_detail');
    $paper_author_table = $xoopsDB->prefix('xnppaper_author');
    $file_table = $xoopsDB->prefix('xoonips_search_text');

    $wheres = array();
    $w = xnpGetBasicInformationAdvancedSearchQuery('xnppaper');
    if ($w) {
        $wheres[] = $w;
    }
    $w = xnpGetKeywordQuery($paper_table.'.journal', 'xnppaper_journal');
    if ($w) {
        $wheres[] = $w;
    }
    $w = xnpGetKeywordQuery($paper_table.'.volume', 'xnppaper_volume');
    if ($w) {
        $wheres[] = $w;
    }
    $w = xnpGetKeywordQuery($paper_table.'.number', 'xnppaper_number');
    if ($w) {
        $wheres[] = $w;
    }
    $w = xnpGetKeywordQuery($paper_table.'.page', 'xnppaper_page');
    if ($w) {
        $wheres[] = $w;
    }
    $w = xnpGetKeywordQuery($paper_table.'.pubmed_id', 'xnppaper_pubmed_id');
    if ($w) {
        $wheres[] = $w;
    }
    $w = xnpGetKeywordQuery($paper_author_table.'.author', 'xnppaper_author');
    if ($w) {
        $wheres[] = $w;
    }

    $xnppaper_paper_pdf_reprint = $formdata->getValue('post', 'xnppaper_paper_pdf_reprint', 's', false);
    if (!empty($xnppaper_paper_pdf_reprint)) {
        $search_text_table = $xoopsDB->prefix('xoonips_search_text');
        $file_table = $xoopsDB->prefix('xoonips_file');
        $searchutil = &xoonips_getutility('search');
        $fulltext_query = $xnppaper_paper_pdf_reprint;
        $fulltext_encoding = mb_detect_encoding($fulltext_query);
        $fulltext_criteria = new CriteriaCompo($searchutil->getFulltextSearchCriteria('search_text', $fulltext_query, $fulltext_encoding, $search_text_table));
        $fulltext_criteria->add(new Criteria('is_deleted', 0, '=', $file_table));
        $wheres[] = $fulltext_criteria->render();
    }

    $where = implode(' AND ', $wheres);
    $join = '';
    $join = " INNER JOIN $paper_author_table on ".$paper_author_table.'.paper_id  = '.$xoopsDB->prefix('xoonips_item_basic').'.item_id ';
}

function xnppaperGetAdvancedSearchBlock(&$search_var)
{
    // retrieve blocs of BasicInformation / IndexKeywords
    $basic = xnpGetBasicInformationAdvancedSearchBlock('xnppaper', $search_var);
    $search_var[] = 'xnppaper_author';
    $search_var[] = 'xnppaper_journal';
    $search_var[] = 'xnppaper_volume';
    $search_var[] = 'xnppaper_number';
    $search_var[] = 'xnppaper_page';
    $search_var[] = 'xnppaper_pubmed_id';
    $search_var[] = 'xnppaper_paper_pdf_reprint';

    // assign to template
    global $xoopsTpl;
    $tpl = new XoopsTpl();
    // variables assigned to xoopsTpl are copied to $tpl
    $tpl->assign($xoopsTpl->get_template_vars());

    $tpl->assign('basic', $basic);
    $tpl->assign('module_name', 'xnppaper');
    $tpl->assign('module_display_name', xnpGetItemTypeDisplayNameByDirname(basename(dirname(__DIR__)), 's'));

    // return HTML
    return $tpl->fetch('db:xnppaper_search_block.html');
}

function xnppaperGetDetailInformationTotalSize($iids)
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
function xnppaperExportItem($export_path, $fhdl, $item_id, $attachment)
{
    // get DetailInformation
    if (!$fhdl) {
        return false;
    }

    $handler = &xoonips_getormhandler('xnppaper', 'item_detail');
    $detail = &$handler->get($item_id);
    if (!$detail) {
        return false;
    }

    $authors = '';
    foreach ($detail->getAuthors() as $author) {
        $authors .= '<author>'.$author->getVar('author', 's').'</author>';
    }

    if (!fwrite($fhdl, "<detail id=\"${item_id}\" version=\"1.02\">\n"."<authors>{$authors}</authors>\n".'<journal>'.$detail->getVar('journal', 's')."</journal>\n".'<volume>'.$detail->getVar('volume', 's')."</volume>\n".'<number>'.$detail->getVar('number', 's')."</number>\n".'<page>'.$detail->getVar('page', 's')."</page>\n".'<abstract>'.$detail->getVar('abstract', 's')."</abstract>\n".'<pubmed_id>'.$detail->getVar('pubmed_id', 's')."</pubmed_id>\n")) {
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

function xnppaperGetModifiedFields($item_id)
{
    $ret = array();
    $formdata = &xoonips_getutility('formdata');
    $basic = xnpGetBasicInformationArray($item_id);
    if ($basic) {
        $publicationDateMonth = $formdata->getValue('post', 'publicationDateMonth', 'i', false);
        $publicationDateDay = $formdata->getValue('post', 'publicationDateDay', 'i', false);
        $publicationDateYear = $formdata->getValue('post', 'publicationDateYear', 'i', false);
        if (intval($basic['publication_month']) != intval($publicationDateMonth) || intval($basic['publication_mday']) != intval($publicationDateDay) || intval($basic['publication_year']) != intval($publicationDateYear)) {
            array_push($ret, _MD_XNPPAPER_YEAR_OF_PUBLICATION_LABEL);
        }
    }
    $detail = xnppaperGetDetailInformation($item_id);
    if ($detail) {
        foreach (array('journal' => _MD_XNPPAPER_JOURNAL_LABEL, 'volume' => _MD_XNPPAPER_VOLUME_LABEL, 'number' => _MD_XNPPAPER_NUMBER_LABEL, 'page' => _MD_XNPPAPER_PAGE_LABEL, 'abstract' => _MD_XNPPAPER_ABSTRACT_LABEL, 'pubmed_id' => _MD_XNPPAPER_PUBMED_ID_LABEL) as $k => $v) {
            $tmp = $formdata->getValue('post', $k, 's', false);
            if (!array_key_exists($k, $detail) || $tmp === null) {
                continue;
            }
            if ($detail[$k] != $tmp) {
                array_push($ret, $v);
            }
        }

        // is modified pdf files?
        if (xnpIsAttachmentModified('paper_pdf_reprint', $item_id)) {
            array_push($ret, _MD_XNPPAPER_PDF_REPRINT_LABEL);
        }

        $author_handler = &xoonips_getormhandler('xnppaper', 'author');
        $author_objs = &$formdata->getObjectArray('post', $author_handler->getTableName(), $author_handler, false);
        $detail_handler = &xoonips_getormhandler('xnppaper', 'item_detail');
        $detail_orm = &$detail_handler->get($item_id);
        $author_old_objs = &$detail_orm->getAuthors();
        if (!xoonips_is_same_objects($author_old_objs, $author_objs)) {
            array_push($ret, _MD_XNPPAPER_AUTHOR_LABEL);
        }
    }

    return $ret;
}

function xnppaperGetTopBlock($itemtype)
{
    return xnpGetTopBlock($itemtype['name'], $itemtype['display_name'], 'images/icon_paper.gif', _MD_XNPPAPER_EXPLANATION, false, false);
}

function xnppaperSupportMetadataFormat($metadataPrefix, $item_id)
{
    if ($metadataPrefix == 'oai_dc' || $metadataPrefix == 'junii2') {
        return true;
    }

    return false;
}

function xnppaperGetMetadata($prefix, $item_id)
{
    $mydirpath = dirname(__DIR__);
    $mydirname = basename($mydirpath);
    if (!in_array($prefix, array('oai_dc', 'junii2'))) {
        return false;
    }
    // module config
    $mhandler = &xoops_gethandler('module');
    $chandler = &xoops_gethandler('config');
    $module = $mhandler->getByDirname($mydirname);
    $mconfig = $chandler->getConfigsByCat(false, $module->get('mid'));
    // detail information
    $detail_handler = &xoonips_getormhandler($mydirname, 'item_detail');
    $author_handler = &xoonips_getormhandler($mydirname, 'author');
    $detail_obj = &$detail_handler->get($item_id);
    if (empty($detail_obj)) {
        return false;
    }
    $detail = $detail_obj->getArray();
    $criteria = new Criteria('paper_id', $item_id);
    $criteria->setSort('author_order');
    $author_objs = &$author_handler->getObjects($criteria);
    $detail['authors'] = array();
    foreach ($author_objs as $author_obj) {
        $detail['authors'][] = $author_obj->get('author');
    }
    $detail['start_page'] = '';
    $detail['end_page'] = '';
    if (!empty($detail['page'])) {
        if (preg_match('/^(\d+)\s*[- ,_]+\s*(\d+)$/', $detail['page'], $matches)) {
            $detail['start_page'] = intval($matches[1]);
            $slen = strlen($matches[1]);
            $elen = strlen($matches[2]);
            if ($slen <= $elen) {
                $detail['end_page'] = intval($matches[2]);
            } else {
                $detail['end_page'] = intval(substr($matches[1], 0, $slen - $elen).$matches[2]);
            }
        } else {
            $detail['start_page'] = $detail['end_page'] = intval($detail['page']);
        }
    }
    if ($mconfig['abstract_access_rights'] != 1) {
        // abstract has no rights to the public
        $detail['abstract'] = '';
    }
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
    if ($mconfig['pdf_access_rights'] == 1) {
        $files = $file_handler->getFilesInfo($item_id, 'paper_pdf_reprint');
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
