<?php

// $Revision: 1.50.2.1.2.30 $
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

function xnppresentationGetTypes()
{
    return array('powerpoint' => 'PowerPoint', 'lotus' => 'Lotus', 'justsystem' => 'JustSystem', 'html' => 'HTML', 'pdf' => 'PDF', 'other' => 'Other');
}

/**
 * retrieve Detail Information that specified by item_id.
 */
function xnppresentationGetDetailInformation($item_id)
{
    global $xoopsDB;
    if (empty($item_id)) {
        return array('presentation_type' => '', 'attachment_dl_limit' => '', 'attachment_dl_notify' => '', 'rights' => '', 'readme' => '', 'use_cc' => '', 'cc_commercial_use' => '', 'cc_modification' => '');
    }

    $sql = 'select * from '.$xoopsDB->prefix('xnppresentation_item_detail')." where presentation_id=$item_id";
    $result = $xoopsDB->query($sql);
    if ($result == false) {
        echo " $sql ".mysql_error();

        return false;
    }
    $types = xnppresentationGetTypes();
    $detail = $xoopsDB->fetchArray($result);
    $detail['presentation_type_str'] = $types[$detail['presentation_type']];

    return $detail;
}

function xnppresentationGetMetaInformation($item_id)
{
    $ret = array();
    $creator_array = array();

    $basic = xnpGetBasicInformationArray($item_id);
    $detail = xnppresentationGetDetailInformation($item_id);
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
        $ret[_MD_XNPPRESENTATION_PRESENTATION_TYPE_LABEL] = $detail['presentation_type_str'];
    }
    if (!empty($basic)) {
        $ret[_MD_XNPPRESENTATION_DATE_LABEL] = xnpDate($basic['publication_year'], $basic['publication_month'], $basic['publication_mday']);
    }
    if (!empty($detail)) {
        $ret[_MD_XOONIPS_ITEM_README_LABEL] = $detail['readme'];
        $ret[_MD_XOONIPS_ITEM_RIGHTS_LABEL] = $detail['rights'];
    }
    $xnppresentation_handler = &xoonips_getormcompohandler('xnppresentation', 'item');
    $xnppresentation = &$xnppresentation_handler->get($item_id);
    foreach ($xnppresentation->getVar('creator') as $creator) {
        $creator_array[] = $creator->getVar('creator', 'n');
    }
    $ret[_MD_XNPPRESENTATION_CREATOR_LABEL] = implode("\n", $creator_array);

    return $ret;
}

function xnppresentationGetListBlock($item_basic)
{
    // get uid
    global $xoopsUser;
    $myuid = is_object($xoopsUser) ? $xoopsUser->getVar('uid', 'n') : UID_GUEST;

    // set to template
    global $xoopsTpl;

    $tpl = new XoopsTpl();
    // copy variables in $xoopsTpl to $tpl
    $tpl->assign($xoopsTpl->get_template_vars());

    $xnppresentation_handler = &xoonips_getormcompohandler('xnppresentation', 'item');
    $tpl->assign('xoonips_item', $xnppresentation_handler->getTemplateVar(XOONIPS_TEMPLATE_TYPE_ITEM_LIST, $item_basic['item_id'], $myuid));

    // return as HTML
    return $tpl->fetch('db:xnppresentation_list_block.html');
}

function xnppresentationGetPrinterFriendlyListBlock($item_basic)
{
    return xnppresentationGetListBlock($item_basic);
}

function xnppresentationGetDetailBlock($item_id)
{
    // get uid
    global $xoopsUser;
    $myuid = is_object($xoopsUser) ? $xoopsUser->getVar('uid', 'n') : UID_GUEST;

    global $xoopsTpl;

    // get DetailInformation
    $detail_handler = &xoonips_getormhandler('xnppresentation', 'item_detail');
    $detail_orm = &$detail_handler->get($item_id);
    if (!$detail_orm) {
        return '';
    }

    // set to template
    $tpl = new XoopsTpl();
    // copy variables in $xoopsTpl to $tpl
    $tpl->assign($xoopsTpl->get_template_vars());

    $tpl->assign('editable', xnp_get_item_permission($_SESSION['XNPSID'], $item_id, OP_MODIFY));
    $tpl->assign('basic', xnpGetBasicInformationDetailBlock($item_id));
    $tpl->assign('index', xnpGetIndexDetailBlock($item_id));
    $tpl->assign('preview', xnpGetPreviewDetailBlock($item_id));
    $tpl->assign('presentation_file', xnpGetAttachmentDetailBlock($item_id, 'presentation_file'));
    $tpl->assign('readme', xnpGetTextFileDetailBlock($item_id, 'readme', $detail_orm->getVar('readme', 'n')));
    $tpl->assign('rights', xnpGetRightsDetailBlock($item_id, $detail_orm->getVar('use_cc', 'n'), $detail_orm->getVar('rights', 'n'), $detail_orm->getVar('cc_commercial_use', 'n'), $detail_orm->getVar('cc_modification', 'n')));

    $xnppresentation_handler = &xoonips_getormcompohandler('xnppresentation', 'item');
    $tpl->assign('xoonips_item', $xnppresentation_handler->getTemplateVar(XOONIPS_TEMPLATE_TYPE_ITEM_DETAIL, $item_id, $myuid));

    // return as HTML
    return $tpl->fetch('db:xnppresentation_detail_block.html');
}

function xnppresentationGetDownloadConfirmationBlock($item_id, $download_file_id)
{
    $detail = xnppresentationGetDetailInformation($item_id);

    return xnpGetDownloadConfirmationBlock($item_id, $download_file_id, $detail['attachment_dl_notify'], true, $detail['use_cc'], $detail['rights']);
}

function xnppresentationGetDownloadConfirmationRequired($item_id)
{
    return true;
}

function xnppresentationGetPrinterFriendlyDetailBlock($item_id)
{
    // get uid
    global $xoopsUser;
    $myuid = is_object($xoopsUser) ? $xoopsUser->getVar('uid', 'n') : UID_GUEST;

    global $xoopsTpl;

    // get DetailInformation
    $detail_handler = &xoonips_getormhandler('xnppresentation', 'item_detail');
    $detail_orm = &$detail_handler->get($item_id);
    if (!$detail_orm) {
        return '';
    }

    // set to template
    $tpl = new XoopsTpl();
    // copy variables in $xoopsTpl to $tpl
    $tpl->assign($xoopsTpl->get_template_vars());

    $tpl->assign('editable', xnp_get_item_permission($_SESSION['XNPSID'], $item_id, OP_MODIFY));
    $tpl->assign('basic', xnpGetBasicInformationPrinterFriendlyBlock($item_id));
    $tpl->assign('index', xnpGetIndexPrinterFriendlyBlock($item_id));
    $tpl->assign('preview', xnpGetPreviewPrinterFriendlyBlock($item_id));
    $tpl->assign('presentation_file', xnpGetAttachmentPrinterFriendlyBlock($item_id, 'presentation_file'));
    $tpl->assign('readme', xnpGetTextFilePrinterFriendlyBlock($item_id, 'readme', $detail_orm->getVar('readme', 'n')));
    $tpl->assign('rights', xnpGetRightsPrinterFriendlyBlock($item_id, $detail_orm->getVar('use_cc', 'n'), $detail_orm->getVar('rights', 'n'), $detail_orm->getVar('cc_commercial_use', 'n'), $detail_orm->getVar('cc_modification', 'n')));

    $xnppresentation_handler = &xoonips_getormcompohandler('xnppresentation', 'item');
    $tpl->assign('xoonips_item', $xnppresentation_handler->getTemplateVar(XOONIPS_TEMPLATE_TYPE_ITEM_DETAIL, $item_id, $myuid));

    // return as HTML
    return $tpl->fetch('db:xnppresentation_detail_block.html');
}

function xnppresentationGetRegisterBlock()
{
    global $xoopsDB;
    (method_exists('MyTextSanitizer', 'sGetInstance') and $myts = &MyTextSanitizer::sGetInstance()) || $myts = &MyTextSanitizer::getInstance();

    // retrieve detail information
    $formdata = &xoonips_getutility('formdata');
    if ($formdata->getValue('get', 'post_id', 's', false)) {
        $detail = array(
        'presentation_type' => $formdata->getValue('post', 'presentation_type', 's', true),
        );
    } else {
        $detail = array(
        'presentation_type' => 'other',
        );
    }

    // retrieve blocks of BasicInformation / Preview / index block
    $basic = xnpGetBasicInformationRegisterBlock();
    $preview = xnpGetPreviewRegisterBlock();
    $index = xnpGetIndexRegisterBlock();
    $presentation_file = xnpGetAttachmentRegisterBlock('presentation_file');
    $readme = xnpGetTextFileRegisterBlock('readme');
    $rights = xnpGetRightsRegisterBlock();

    // assign to template
    global $xoopsTpl;
    $tpl = new XoopsTpl();
    $tpl->assign($xoopsTpl->get_template_vars());
    // variables assigned to xoopsTpl are copied to tpl
    $tpl->assign('basic', $basic);
    $tpl->assign('preview', $preview);
    $tpl->assign('index', $index);
    $tpl->assign('presentation_file', $presentation_file);
    $tpl->assign('attachment_dl_limit', xnpGetDownloadLimitationOptionRegisterBlock('xnppresentation'));
    $tpl->assign('attachment_dl_notify', xnpGetDownloadNotificationOptionRegisterBlock('xnppresentation'));
    $tpl->assign('detail', $detail);
    $tpl->assign('readme', $readme);
    $tpl->assign('rights', $rights);
    $tpl->assign('presentation_type', xnppresentationGetTypes());
    $tpl->assign('presentation_type_selected', $detail['presentation_type']);
    $tpl->assign('xnppresentation_creator', xoonips_get_multiple_field_template_vars(xoonips_get_orm_from_post('xnppresentation', 'creator'), 'xnppresentation', 'creator'));

    // return HTML content
    return $tpl->fetch('db:xnppresentation_register_block.html');
}

function xnppresentationGetEditBlock($item_id)
{
    global $xoopsDB;
    $myts = &MyTextsanitizer::getInstance();
    $formdata = &xoonips_getutility('formdata');

    // get DetailInformation
    $result = $xoopsDB->query('select * from '.$xoopsDB->prefix('xnppresentation_item_detail')." where presentation_id=$item_id");
    foreach ($model = $xoopsDB->fetchArray($result) as $k => $v) {
        $$k = $v;
    }
    // overwrite DetailInformation with POST/GET variables
    foreach (array('presentation_type', 'creator', 'readme', 'rights') as $k) {
        if (array_key_exists($k, $_GET)) {
            $$k = $myts->stripSlashesGPC($_GET[$k]);
        } elseif (array_key_exists($k, $_POST)) {
            $$k = $myts->stripSlashesGPC($_POST[$k]);
        }
    }

    // retrieve blocks of BasicInformation / Preview / index block
    $basic = xnpGetBasicInformationEditBlock($item_id);
    $preview = xnpGetPreviewEditBlock($item_id);
    $index = xnpGetIndexEditBlock($item_id);
    $presentation_file = xnpGetAttachmentEditBlock($item_id, 'presentation_file');

    // retrieve detail information
    if (!is_null($formdata->getValue('get', 'post_id', 's', false))) {
        (method_exists('MyTextSanitizer', 'sGetInstance') and $myts = &MyTextSanitizer::sGetInstance()) || $myts = &MyTextSanitizer::getInstance();
        $detail = array(
        'presentation_type' => $formdata->getValue('post', 'presentation_type', 's', true),
        'readme' => '',
        'rights' => '',
        'use_cc' => '',
        'cc_commercial_use' => '',
        'cc_modification' => '',
        );
    } elseif (!empty($item_id)) {
        $detail = xnppresentationGetDetailInformation($item_id);
    } else {
        $detail = array();
    }

    $readme = xnpGetTextFileEditBlock($item_id, 'readme', isset($detail['readme']) ? $detail['readme'] : '');
    $rights = xnpGetRightsEditBlock($item_id, $detail['use_cc'], $detail['rights'], $detail['cc_commercial_use'], $detail['cc_modification']);

    // assign to template
    global $xoopsTpl;
    $tpl = new XoopsTpl();
    $tpl->assign($xoopsTpl->get_template_vars());
    // variables assigned to xoopsTpl are copied to tpl
    $tpl->assign('basic', $basic);
    $tpl->assign('preview', $preview);
    $tpl->assign('index', $index);
    $tpl->assign('presentation_file', $presentation_file);
    $tpl->assign('attachment_dl_limit', xnpGetDownloadLimitationOptionEditBlock('xnppresentation', xnppresentationGetAttachmentDownloadLimitOption($item_id)));
    $tpl->assign('attachment_dl_notify', xnpGetDownloadNotificationOptionEditBlock('xnppresentation', xnppresentationGetAttachmentDownloadNotifyOption($item_id)));
    $tpl->assign('detail', $detail);
    $tpl->assign('readme', $readme);
    $tpl->assign('rights', $rights);
    $tpl->assign('presentation_type', xnppresentationGetTypes());
    $tpl->assign('presentation_type_selected', $detail['presentation_type']);

    if (!$formdata->getValue('get', 'post_id', 's', false)) {
        $detail_handler = &xoonips_getormhandler('xnppresentation', 'item_detail');
        $detail_orm = &$detail_handler->get($item_id);
        $tpl->assign('xnppresentation_creator', xoonips_get_multiple_field_template_vars($detail_orm->getCreators(), 'xnppresentation', 'creator'));
    } else {
        $tpl->assign('xnppresentation_creator', xoonips_get_multiple_field_template_vars(xoonips_get_orm_from_post('xnppresentation', 'creator'), 'xnppresentation', 'creator'));
    }

    // return HTML content
    return $tpl->fetch('db:xnppresentation_register_block.html');
}

function xnppresentationGetConfirmBlock($item_id)
{
    $formdata = &xoonips_getutility('formdata');
    $creator_handler = &xoonips_getormhandler('xnppresentation', 'creator');
    $creator_objs = &$formdata->getObjectArray('post', $creator_handler->getTableName(), $creator_handler, false);

    // retrieve blocks of BasicInformation / Preview / index block
    $basic = xnpGetBasicInformationConfirmBlock($item_id);
    $preview = xnpGetPreviewConfirmBlock($item_id);
    $index = xnpGetIndexConfirmBlock($item_id);
    $presentation_file = xnpGetAttachmentConfirmBlock($item_id, 'presentation_file');
    $lengths = xnpGetColumnLengths('xnppresentation_item_detail');
    $readme = xnpGetTextFileConfirmBlock($item_id, 'readme', $lengths['readme']);
    $rights = xnpGetRightsConfirmBlock($item_id, $lengths['rights']);
    // retrieve detail information
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $detail = array(
        'presentation_type' => array(
        'value' => $formdata->getValue('post', 'presentation_type', 's', false),
        ),
        );
        xnpConfirmHtml($detail, 'xnppresentation_item_detail', array_keys($detail), _CHARSET);
        $types = xnppresentationGetTypes();
        $detail['presentation_type_str'] = array(
          'value' => htmlspecialchars($types[$detail['presentation_type']['value']], ENT_QUOTES),
        );
    } else {
        $detail = array();
    }

    // trim strings
    if (xnpHasWithout($basic) || xnpHasWithout($readme) || xnpHasWithout($rights) || xnpHasWithout($detail) || xnpHasWithout($preview) || xnpHasWithout($presentation_file) || xoonips_is_multiple_field_too_long($creator_objs, 'xnppresentation', 'creator')) {
        global $system_message;
        $system_message = $system_message."\n<br /><font color='#ff0000'>"._MD_XOONIPS_ITEM_WARNING_FIELD_TRIM.'</font><br />';
    }

    // assign to template
    global $xoopsTpl;
    $tpl = new XoopsTpl();
    $tpl->assign($xoopsTpl->get_template_vars());
    // variables assigned to xoopsTpl are copied to tpl
    $tpl->assign('basic', $basic);
    $tpl->assign('preview', $preview);
    $tpl->assign('index', $index);
    $tpl->assign('presentation_file', $presentation_file);
    $tpl->assign('attachment_dl_limit', xnpGetDownloadLimitationOptionConfirmBlock('xnppresentation'));
    $tpl->assign('attachment_dl_notify', xnpGetDownloadNotificationOptionConfirmBlock('xnppresentation'));
    $tpl->assign('detail', $detail);
    $tpl->assign('readme', $readme);
    $tpl->assign('rights', $rights);
    $tpl->assign('xnppresentation_creator', xoonips_get_multiple_field_template_vars($creator_objs, 'xnppresentation', 'creator'));

    // return HTML content
    return $tpl->fetch('db:xnppresentation_confirm_block.html');
}

/**
 * make sure that enterd detail information is correctly or not.
 * called from register confirmation and edit confirmation.
 */
function xnppresentationCheckRegisterParameters(&$message)
{
    $xnpsid = $_SESSION['XNPSID'];
    $messages = array();
    $formdata = &xoonips_getutility('formdata');
    $creator = xoonips_get_multi_field_array_from_post('xnppresentation', 'creator');
    $presentation_fileFileID = $formdata->getValue('post', 'presentation_fileFileID', 'i', true);
    $presentation_file = $formdata->getFile('presentation_file', false);
    $xoonipsCheckedXID = $formdata->getValue('post', 'xoonipsCheckedXID', 's', true);

    if (empty($creator)) {
        $messages[] = _MD_XNPPRESENTATION_CREATOR_REQUIRED;
    }
    if (empty($presentation_fileFileID) && empty($presentation_file['name'])) {
        $messages[] = _MD_XNPPRESENTATION_PRESENTATION_FILE_REQUIRED;
    }

    // notify that license statement is required when register into public indexes.
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
    if (count($indexes) > 0) {
        foreach ($indexes as $i) {
            if ($i['open_level'] <= OL_GROUP_ONLY) {
                $readmeEncText = $formdata->getValue('post', 'readmeEncText', 's', true);
                $rightsEncText = $formdata->getValue('post', 'rightsEncText', 's', true);
                $rightsUseCC = $formdata->getValue('post', 'rightsUseCC', 'i', true);
                if ($readmeEncText == '') {
                    // readme is not filled
                    $messages[] = '<font color=\'#ff0000\'>'._MD_XNPPRESENTATION_README_REQUIRED.'</font>';
                }
                if ($rightsEncText == '' && $rightsUseCC == '0') {
                    // license is not filled
                    $messages[] = '<font color=\'#ff0000\'>'._MD_XNPPRESENTATION_RIGHTS_REQUIRED.'</font>';
                }
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
 * make sure that enterd detail information is correctly or not.
 */
function xnppresentationCheckEditParameters(&$message)
{
    return xnppresentationCheckRegisterParameters($message);
}

function xnppresentationInsertItem(&$item_id)
{
    global $xoopsDB;
    $xnpsid = $_SESSION['XNPSID'];
    $formdata = &xoonips_getutility('formdata');

    // retister BasicInformation, Index and Attachment
    $item_id = 0;
    $result = xnpInsertBasicInformation($item_id);
    if ($result) {
        $result = xnpUpdateIndex($item_id);
        if ($result) {
            $result = xnpUpdatePreview($item_id);
            if ($result) {
                $result = xnpUpdateAttachment($item_id, 'presentation_file');
                if ($result) {
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

    // register detail information
    list($rights, $use_cc, $cc_commercial_use, $cc_modification) = xnpGetRights();
    (method_exists('MyTextSanitizer', 'sGetInstance') and $myts = &MyTextSanitizer::sGetInstance()) || $myts = &MyTextSanitizer::getInstance();

    // it makes string with constant length
    $ar = array(
    'presentation_type' => $formdata->getValue('post', 'presentation_type', 's', true),
    'readme' => xnpGetTextFile('readme'),
    'rights' => $rights,
    );
    xnpTrimColumn($ar, 'xnppresentation_item_detail', array_keys($ar), _CHARSET);

    (method_exists('MyTextSanitizer', 'sGetInstance') and $myts = &MyTextSanitizer::sGetInstance()) || $myts = &MyTextSanitizer::getInstance();
    $keys = implode(',', array('attachment_dl_limit', 'attachment_dl_notify', 'presentation_type', 'readme', 'rights', 'use_cc', 'cc_commercial_use', 'cc_modification'));
    $attachment_dl_limit = $formdata->getValue('post', 'attachment_dl_limit', 'i', true);
    $attachment_dl_notify = $formdata->getValue('post', 'attachment_dl_notify', 'i', true);
    $vals = implode('\',\'', array($attachment_dl_limit, $attachment_dl_limit ? $attachment_dl_notify : 0, addslashes($ar['presentation_type']), addslashes($ar['readme']), addslashes($ar['rights']), $use_cc, $cc_commercial_use, $cc_modification));

    $sql = 'insert into '.$xoopsDB->prefix('xnppresentation_item_detail')." ( presentation_id, $keys ) values ( $item_id, '$vals' ) ";
    $result = $xoopsDB->queryF($sql);
    if ($result == false) {
        echo 'cannot insert item_detail';

        return false;
    }

    // insert creator
    $creator_handler = &xoonips_getormhandler('xnppresentation', 'creator');
    $creator_objs = &$formdata->getObjectArray('post', $creator_handler->getTableName(), $creator_handler, false);
    if (!$creator_handler->updateAllObjectsByForeignKey('presentation_id', $item_id, $creator_objs)) {
        return false;
    }

    return true;
}

function xnppresentationUpdateItem($item_id)
{
    global $xoopsDB;
    $xnpsid = $_SESSION['XNPSID'];
    $formdata = &xoonips_getutility('formdata');
    (method_exists('MyTextSanitizer', 'sGetInstance') and $myts = &MyTextSanitizer::sGetInstance()) || $myts = &MyTextSanitizer::getInstance();

    // modify BasicInformation, Index, Preview and Attachment.
    $result = xnpUpdateBasicInformation($item_id);
    if ($result) {
        $result = xnpUpdateIndex($item_id);
        if ($result) {
            $result = xnpUpdatePreview($item_id);
            if ($result) {
                $result = xnpUpdateAttachment($item_id, 'presentation_file');
                if ($result) {
                    $result = xnp_insert_change_log($xnpsid, $item_id, $formdata->getValue('post', 'change_log', 's', true));
                    $result = !$result;
                    if (!$result) {
                        echo ' xnp_insert_change_log failed.';
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

    list($rights, $use_cc, $cc_commercial_use, $cc_modification) = xnpGetRights();
    // trim strings
    $ar = array(
    'presentation_type' => $formdata->getValue('post', 'presentation_type', 's', true),
    'readme' => xnpGetTextFile('readme'),
    'rights' => $rights,
    );
    xnpTrimColumn($ar, 'xnppresentation_item_detail', array_keys($ar), _CHARSET);

    // register detail information
    $attachment_dl_limit = $formdata->getValue('post', 'attachment_dl_limit', 'i', true);
    $attachment_dl_notify = $formdata->getValue('post', 'attachment_dl_notify', 'i', true);
    $sql = implode(',', array('attachment_dl_limit'.'=\''.$attachment_dl_limit.'\'', 'attachment_dl_notify'.'=\''.($attachment_dl_limit ? $attachment_dl_notify : 0).'\'', 'presentation_type'.'=\''.addslashes($ar['presentation_type']).'\'', 'readme'.'=\''.addslashes($ar['readme']).'\'', 'rights'.'=\''.addslashes($ar['rights']).'\'', 'use_cc'.'=\''.$use_cc.'\'', 'cc_commercial_use'.'=\''.$cc_commercial_use.'\'', 'cc_modification'.'=\''.$cc_modification.'\''));
    $result = $xoopsDB->queryF('update '.$xoopsDB->prefix('xnppresentation_item_detail')." set $sql where presentation_id = $item_id ");
    if ($result == false) {
        return false;
    }

    // insert/update creator
    $formdata = &xoonips_getutility('formdata');
    $creator_handler = &xoonips_getormhandler('xnppresentation', 'creator');
    $creator_objs = &$formdata->getObjectArray('post', $creator_handler->getTableName(), $creator_handler, false);
    if (!$creator_handler->updateAllObjectsByForeignKey('presentation_id', $item_id, $creator_objs)) {
        return false;
    }

    return true;
}

function xnppresentationGetDetailInformationQuickSearchQuery(&$wheres, &$join, $keywords)
{
    global $xoopsDB;
    $presentation_table = $xoopsDB->prefix('xnppresentation_item_detail');
    $presentation_creator_table = $xoopsDB->prefix('xnppresentation_creator');
    $file_table = $xoopsDB->prefix('xoonips_file');

    $join = " INNER JOIN $presentation_creator_table ON ".$presentation_creator_table.'.presentation_id  = '.$xoopsDB->prefix('xoonips_item_basic').'.item_id ';
    $wheres = xnpGetKeywordsQueries(array("$presentation_creator_table.creator", "$file_table.caption"), $keywords);

    return true;
}

function xnppresentationGetAdvancedSearchQuery(&$where, &$join)
{
    global $xoopsDB;
    $formdata = &xoonips_getutility('formdata');
    $basic_table = $xoopsDB->prefix('xoonips_item_basic');
    $presentation_table = $xoopsDB->prefix('xnppresentation_item_detail');
    $presentation_creator_table = $xoopsDB->prefix('xnppresentation_creator');
    $file_table = $xoopsDB->prefix('xoonips_file');
    $search_text_table = $xoopsDB->prefix('xoonips_search_text');

    $wheres = array();
    $joins = array();

    $xnppresentation_presentation_type = $formdata->getValue('post', 'xnppresentation_presentation_type', 's', false);
    $xnppresentation_presentation_file = $formdata->getValue('post', 'xnppresentation_presentation_file', 's', false);
    $w = xnpGetBasicInformationAdvancedSearchQuery('xnppresentation');
    if ($w) {
        $wheres[] = $w;
    }
    if (!empty($xnppresentation_presentation_type)) {
        (method_exists('MyTextSanitizer', 'sGetInstance') and $myts = &MyTextSanitizer::sGetInstance()) || $myts = &MyTextSanitizer::getInstance();
        $wheres[] = $presentation_table.'.presentation_type = \''.addslashes($xnppresentation_presentation_type).'\'';
    }
    $w = xnpGetKeywordQuery($presentation_creator_table.'.creator', 'xnppresentation_creator');
    if ($w) {
        $wheres[] = $w;
    }
    $w = xnpGetKeywordQuery($file_table.'.caption', 'xnppresentation_caption');
    if ($w) {
        $wheres[] = $w;
        $wheres[] = " $file_table.file_type_id = 1";
    }
    if (!empty($xnppresentation_presentation_file)) {
        $search_text_table = $xoopsDB->prefix('xoonips_search_text');
        $file_table = $xoopsDB->prefix('xoonips_file');
        $searchutil = &xoonips_getutility('search');
        $fulltext_query = $xnppresentation_presentation_file;
        $fulltext_encoding = mb_detect_encoding($fulltext_query);
        $fulltext_criteria = new CriteriaCompo($searchutil->getFulltextSearchCriteria('search_text', $fulltext_query, $fulltext_encoding, $search_text_table));
        $fulltext_criteria->add(new Criteria('is_deleted', 0, '=', $file_table));
        $wheres[] = $fulltext_criteria->render();
    }

    $where = implode(' AND ', $wheres);
    $join = " INNER JOIN $presentation_creator_table ON ".$presentation_creator_table.'.presentation_id  = '.$xoopsDB->prefix('xoonips_item_basic').'.item_id ';
}

function xnppresentationGetAdvancedSearchBlock(&$search_var)
{
    // retrieve blocks of BasicInformation / Preview / IndexKeywords
    $basic = xnpGetBasicInformationAdvancedSearchBlock('xnppresentation', $search_var);
    $search_var[] = 'xnppresentation_presentation_type';
    $search_var[] = 'xnppresentation_creator';
    $search_var[] = 'xnppresentation_presentation_file';

    // assign to template
    global $xoopsTpl;
    $tpl = new XoopsTpl();
    $tpl->assign($xoopsTpl->get_template_vars());
    // variables assigned to xoopsTpl are copied to tpl
    $tpl->assign('presentation_type', array_merge(array('' => 'Any'), xnppresentationGetTypes()));
    $tpl->assign('presentation_type_selected', 'none');
    $tpl->assign('basic', $basic);
    $tpl->assign('module_name', 'xnppresentation');
    $tpl->assign('module_display_name', xnpGetItemTypeDisplayNameByDirname(basename(dirname(__DIR__)), 's'));

    // return HTML content
    return $tpl->fetch('db:xnppresentation_search_block.html');
}

function xnppresentationGetDetailInformationTotalSize($iids)
{
    return xnpGetTotalFileSize($iids);
}

function xnppresentationGetLicenseRequired($item_id)
{
    global $xoopsDB;

    // retrieve detail information
    $result = $xoopsDB->query('select * from '.$xoopsDB->prefix('xnppresentation_item_detail')." where presentation_id=$item_id");
    if (!$result) {
        return null;
    }
    $detail = $xoopsDB->fetchArray($result);

    return isset($detail['rights']) && $detail['rights'] != '';
}

function xnppresentationGetLicenseStatement($item_id)
{
    global $xoopsDB;

    // retrieve detail information
    $result = $xoopsDB->query('select * from '.$xoopsDB->prefix('xnppresentation_item_detail')." where presentation_id=$item_id");
    if (!$result) {
        return null;
    }
    $detail = $xoopsDB->fetchArray($result);

    return array(isset($detail['rights']) ? $detail['rights'] : '', $detail['use_cc']);
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
function xnppresentationExportItem($export_path, $fhdl, $item_id, $attachment)
{
    // get DetailInformation
    if (!$fhdl) {
        return false;
    }

    $handler = &xoonips_getormhandler('xnppresentation', 'item_detail');
    $detail = &$handler->get($item_id);
    if (!$detail) {
        return false;
    }

    $creators = '';
    foreach ($detail->getCreators() as $creator) {
        $creators .= '<creator>'.$creator->getVar('creator', 's').'</creator>';
    }

    if (!fwrite($fhdl, "<detail id=\"${item_id}\" version=\"1.03\">\n".'<presentation_type>'.$detail->getVar('presentation_type', 's')."</presentation_type>\n"."<creators>{$creators}</creators>\n".'<readme>'.$detail->getVar('readme', 's')."</readme>\n".'<rights>'.$detail->getVar('rights', 's')."</rights>\n".'<use_cc>'.intval($detail->get('use_cc', 's'))."</use_cc>\n".'<cc_commercial_use>'.intval($detail->get('cc_commercial_use'))."</cc_commercial_use>\n".'<cc_modification>'.intval($detail->get('cc_modification'))."</cc_modification>\n".'<attachment_dl_limit>'.intval($detail->get('attachment_dl_limit'))."</attachment_dl_limit>\n".'<attachment_dl_notify>'.intval($detail->get('attachment_dl_notify'))."</attachment_dl_notify>\n")) {
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

function xnppresentationGetModifiedFields($item_id)
{
    $formdata = &xoonips_getutility('formdata');
    $ret = array();
    $basic = xnpGetBasicInformationArray($item_id);
    if ($basic) {
        $publicationDateMonth = $formdata->getValue('post', 'publicationDateMonth', 'i', true);
        $publicationDateDay = $formdata->getValue('post', 'publicationDateDay', 'i', true);
        $publicationDateYear = $formdata->getValue('post', 'publicationDateYear', 'i', true);
        if (intval($basic['publication_month']) != intval($publicationDateMonth) || intval($basic['publication_mday']) != intval($publicationDateDay) || intval($basic['publication_year']) != intval($publicationDateYear)) {
            array_push($ret, _MD_XNPPRESENTATION_DATE_LABEL);
        }
    }
    $detail = xnppresentationGetDetailInformation($item_id);
    if ($detail) {
        foreach (array('presentation_type' => _MD_XNPPRESENTATION_PRESENTATION_TYPE_LABEL) as $k => $v) {
            $tmp = $formdata->getValue('post', $k, 's', false);
            if (!array_key_exists($k, $detail) || $tmp === null) {
                continue;
            }
            if ($detail[$k] != $tmp) {
                array_push($ret, $v);
            }
        }
        // is readme modified ?
        foreach (array('readme' => _MD_XOONIPS_ITEM_README_LABEL) as $k => $v) {
            $tmp = $formdata->getValue('post', "${k}EncText", 's', true);
            if (!array_key_exists($k, $detail) || $tmp === null) {
                continue;
            }
            if ($detail[$k] != $tmp) {
                array_push($ret, $v);
            }
        }

        // is rights modified ?
        $rightsUseCC = $formdata->getValue('post', 'rightsUseCC', 'i', true);
        $rightsEncText = $formdata->getValue('post', 'rightsEncText', 's', true);
        if ($rightsUseCC !== null) {
            if ($rightsUseCC == 0) {
                if (array_key_exists('rights', $detail) && $rightsEncText != null && $rightsEncText != $detail['rights']) {
                    array_push($ret, _MD_XOONIPS_ITEM_RIGHTS_LABEL);
                }
            } elseif ($rightsUseCC == 1) {
                foreach (array('rightsCCCommercialUse' => 'cc_commercial_use', 'rightsCCModification' => 'cc_modification') as $k => $v) {
                    $tmp = $formdata->getValue('post', $k, 'i', true);
                    if (!array_key_exists($v, $detail) || $tmp === null) {
                        continue;
                    }
                    if ($tmp != $detail[$v]) {
                        array_push($ret, _MD_XOONIPS_ITEM_RIGHTS_LABEL);
                        break;
                    }
                }
            }
        }

        // is modified data files ?
        if (xnpIsAttachmentModified('presentation_file', $item_id)) {
            array_push($ret, _MD_XNPPRESENTATION_PRESENTATION_FILE_LABEL);
        }

        $creator_handler = &xoonips_getormhandler('xnppresentation', 'creator');
        $creator_objs = &$formdata->getObjectArray('post', $creator_handler->getTableName(), $creator_handler, false);
        $detail_handler = &xoonips_getormhandler('xnppresentation', 'item_detail');
        $detail_orm = &$detail_handler->get($item_id);
        $creator_old_objs = &$detail_orm->getCreators();
        if (!xoonips_is_same_objects($creator_old_objs, $creator_objs)) {
            array_push($ret, _MD_XNPPRESENTATION_CREATOR_LABEL);
        }
    }

    return $ret;
}

function xnppresentationGetTopBlock($itemtype)
{
    return xnpGetTopBlock($itemtype['name'], $itemtype['display_name'], 'images/icon_presentation.gif', _MD_XNPPRESENTATION_EXPLANATION, 'xnppresentation_presentation_type', xnppresentationGetTypes());
}

// return 1 if downloadable for login user only
// return 0 if downloadable for everyone
function xnppresentationGetAttachmentDownloadLimitOption($item_id)
{
    global $xoopsDB;
    $sql = 'select attachment_dl_limit from '.$xoopsDB->prefix('xnppresentation_item_detail')." where presentation_id=${item_id}";
    $result = $xoopsDB->query($sql);
    if ($result) {
        list($option) = $xoopsDB->fetchRow($result);

        return $option;
    }

    return 0;
}

// return 1 if downloading is notified
// return 0 if downloading is not notified
function xnppresentationGetAttachmentDownloadNotifyOption($item_id)
{
    global $xoopsDB;
    $sql = 'select attachment_dl_notify from '.$xoopsDB->prefix('xnppresentation_item_detail')." where presentation_id=${item_id}";
    $result = $xoopsDB->query($sql);
    if ($result) {
        list($notify) = $xoopsDB->fetchRow($result);

        return $notify;
    }

    return 0;
}

function xnppresentationSupportMetadataFormat($metadataPrefix, $item_id)
{
    if ($metadataPrefix == 'oai_dc' || $metadataPrefix == 'junii2') {
        return true;
    }

    return false;
}

function xnppresentationGetMetadata($prefix, $item_id)
{
    $mydirpath = dirname(__DIR__);
    $mydirname = basename($mydirpath);
    if (!in_array($prefix, array('oai_dc', 'junii2'))) {
        return false;
    }
    // detail information
    $detail_handler = &xoonips_getormhandler($mydirname, 'item_detail');
    $creator_handler = &xoonips_getormhandler($mydirname, 'creator');
    $detail_obj = &$detail_handler->get($item_id);
    if (empty($detail_obj)) {
        return false;
    }
    $detail = $detail_obj->getArray();
    $criteria = new Criteria('presentation_id', $item_id);
    $criteria->setSort('creator_order');
    $creator_objs = &$creator_handler->getObjects($criteria);
    $detail['creators'] = array();
    foreach ($creator_objs as $creator_obj) {
        $detail['creators'][] = $creator_obj->get('creator');
    }
    $types = xnppresentationGetTypes();
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
        $files = $file_handler->getFilesInfo($item_id, 'presentation_file');
        foreach ($files as $file) {
            if (!in_array($file['mime_type'], $mimetypes)) {
                $mimetypes[] = $file['mime_type'];
            }
        }
    }
    $previews = $file_handler->getFilesInfo($item_id, 'preview');
    // rights
    $detail['rights_cc_url'] = '';
    if ($detail['use_cc'] == 1) {
        $cond = 'by';
        if ($detail['cc_commercial_use'] == 0) {
            $cond .= '-nc';
        }
        if ($detail['cc_modification'] == 0) {
            $cond .= '-nd';
        } elseif ($detail['cc_modification'] == 1) {
            $cond .= '-sa';
        }
        $detail['rights_cc_url'] = sprintf('http://creativecommons.org/licenses/%s/2.5/', $cond);
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
    $tpl->assign('previews', $previews);
    $tpl->assign('related_tos', $related_tos);
    $tpl->assign('repository', $repository);
    $xml = $tpl->fetch('db:'.$mydirname.'_oaipmh_'.$prefix.'.xml');

    return $xml;
}
