<?php

// $Revision: 1.57.2.1.2.31 $
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

function xnpdataGetTypes()
{
    return array('excel' => 'Excel', 'movie' => 'Movie', 'text' => 'Text', 'picture' => 'Picture', 'other' => 'Other');
}

/** get DetailInformation by item_id
 *
 */
function xnpdataGetDetailInformation($item_id)
{
    global $xoopsDB;
    if (empty($item_id)) {
        return array('data_type' => '', 'attachment_dl_limit' => '', 'attachment_dl_notify' => '', 'rights' => '', 'readme' => '', 'use_cc' => '', 'cc_commercial_use' => '', 'cc_modification' => '');
    }

    $sql = 'select * from '.$xoopsDB->prefix('xnpdata_item_detail')." where data_id=$item_id";
    $result = $xoopsDB->query($sql);
    if ($result == false) {
        return false;
    }

    $types = xnpdataGetTypes();
    $detail = $xoopsDB->fetchArray($result);
    if (!$detail) {
        return false;
    }
    $detail['data_type_str'] = $types[$detail['data_type']];

    return $detail;
}

function xnpdataGetMetaInformation($item_id)
{
    $ret = array();
    $experimenter_array = array();

    $basic = xnpGetBasicInformationArray($item_id);
    $detail = xnpdataGetDetailInformation($item_id);
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
        $ret[_MD_XNPDATA_DATA_TYPE_LABEL] = $detail['data_type_str'];
    }
    if (!empty($basic)) {
        $ret[_MD_XNPDATA_DATE_LABEL] = xnpDate($basic['publication_year'], $basic['publication_month'], $basic['publication_mday']);
    }
    if (!empty($detail)) {
        $ret[_MD_XOONIPS_ITEM_README_LABEL] = $detail['readme'];
        $ret[_MD_XOONIPS_ITEM_RIGHTS_LABEL] = $detail['rights'];
    }
    $xnpdata_handler = &xoonips_getormcompohandler('xnpdata', 'item');
    $xnpdata = &$xnpdata_handler->get($item_id);
    foreach ($xnpdata->getVar('experimenter') as $experimenter) {
        $experimenter_array[] = $experimenter->getVar('experimenter', 'n');
    }
    $ret[_MD_XNPDATA_EXPERIMENTER_LABEL] = implode("\n", $experimenter_array);

    return $ret;
}

function xnpdataGetListBlock($item_basic)
{
    // get uid
  global $xoopsUser;
    $myuid = is_object($xoopsUser) ? $xoopsUser->getVar('uid', 'n') : UID_GUEST;

  // set to template
  global $xoopsTpl;
    $tpl = new XoopsTpl();
  // copy variables in $xoopsTpl to $tpl
  $tpl->assign($xoopsTpl->get_template_vars());

    $xnpdata_handler = &xoonips_getormcompohandler('xnpdata', 'item');
    $tpl->assign('xoonips_item', $xnpdata_handler->getTemplateVar(XOONIPS_TEMPLATE_TYPE_ITEM_LIST, $item_basic['item_id'], $myuid));

  // return as HTML
  return $tpl->fetch('db:xnpdata_list_block.html');
}

function xnpdataGetPrinterFriendlyListBlock($item_basic)
{
    return xnpdataGetListBlock($item_basic);
}

function xnpdataGetDetailBlock($item_id)
{
    // get uid
  global $xoopsUser;
    $myuid = is_object($xoopsUser) ? $xoopsUser->getVar('uid', 'n') : UID_GUEST;

    global $xoopsTpl;

  // get DetailInformation
  $detail_handler = &xoonips_getormhandler('xnpdata', 'item_detail');
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
    $tpl->assign('data_file', xnpGetAttachmentDetailBlock($item_id, 'data_file'));
    $tpl->assign('readme', xnpGetTextFileDetailBlock($item_id, 'readme', $detail_orm->getVar('readme', 'n')));
    $tpl->assign('rights', xnpGetRightsDetailBlock($item_id, $detail_orm->getVar('use_cc', 'n'), $detail_orm->getVar('rights', 'n'), $detail_orm->getVar('cc_commercial_use', 'n'), $detail_orm->getVar('cc_modification', 'n')));

    $xnpdata_handler = &xoonips_getormcompohandler('xnpdata', 'item');
    $tpl->assign('xoonips_item', $xnpdata_handler->getTemplateVar(XOONIPS_TEMPLATE_TYPE_ITEM_DETAIL, $item_id, $myuid));

  // return as HTML
  return $tpl->fetch('db:xnpdata_detail_block.html');
}

function xnpdataGetDownloadConfirmationBlock($item_id, $download_file_id)
{
    $detail = xnpdataGetDetailInformation($item_id);

    return xnpGetDownloadConfirmationBlock($item_id, $download_file_id, $detail['attachment_dl_notify'], true, $detail['use_cc'], $detail['rights']);
}

function xnpdataGetDownloadConfirmationRequired($item_id)
{
    return true;
}

function xnpdataGetPrinterFriendlyDetailBlock($item_id)
{
    // get uid
  global $xoopsUser;
    $myuid = is_object($xoopsUser) ? $xoopsUser->getVar('uid', 'n') : UID_GUEST;

    global $xoopsTpl;

  // get DetailInformation
  $detail_handler = &xoonips_getormhandler('xnpdata', 'item_detail');
    $detail_orm = &$detail_handler->get($item_id);
    if (!$detail_orm) {
        return '';
    }

  // set to template
  $tpl = new XoopsTpl();
    $tpl->assign($xoopsTpl->get_template_vars());
  // copy variables in $xoopsTpl to $tpl
  $tpl->assign('editable', xnp_get_item_permission($_SESSION['XNPSID'], $item_id, OP_MODIFY));
    $tpl->assign('basic', xnpGetBasicInformationPrinterFriendlyBlock($item_id));
    $tpl->assign('index', xnpGetIndexPrinterFriendlyBlock($item_id));
    $tpl->assign('preview', xnpGetPreviewPrinterFriendlyBlock($item_id));
    $tpl->assign('data_file', xnpGetAttachmentPrinterFriendlyBlock($item_id, 'data_file'));
  // $tpl->assign( 'detail', $item_detail );
  $tpl->assign('readme', xnpGetTextFilePrinterFriendlyBlock($item_id, 'readme', $detail_orm->getVar('readme', 'n')));
    $tpl->assign('rights', xnpGetRightsPrinterFriendlyBlock($item_id, $detail_orm->getVar('use_cc', 'n'), $detail_orm->getVar('rights', 'n'), $detail_orm->getVar('cc_commercial_use', 'n'), $detail_orm->getVar('cc_modification', 'n')));

    $xnpdata_handler = &xoonips_getormcompohandler('xnpdata', 'item');
    $tpl->assign('xoonips_item', $xnpdata_handler->getTemplateVar(XOONIPS_TEMPLATE_TYPE_ITEM_DETAIL, $item_id, $myuid));

  // return as HTML
  return $tpl->fetch('db:xnpdata_detail_block.html');
}

function xnpdataGetRegisterBlock()
{
    $textutil = &xoonips_getutility('text');
    $formdata = &xoonips_getutility('formdata');
  // get DetailInformation
  if ($formdata->getValue('get', 'post_id', 's', false)) {
      $detail = array(
      'data_type' => $textutil->html_special_chars($formdata->getValue('post', 'data_type', 's', true)),
    );
  } else {
      $detail = array(
      'data_type' => '',
    );
  }

  // get BasicInformation / Preview / index block
  $basic = xnpGetBasicInformationRegisterBlock();
    $preview = xnpGetPreviewRegisterBlock();
    $index = xnpGetIndexRegisterBlock();
    $data_file = xnpGetAttachmentRegisterBlock('data_file');
    $readme = xnpGetTextFileRegisterBlock('readme');
    $rights = xnpGetRightsRegisterBlock();
    $attachment_dl_limit = xnpGetDownloadLimitationOptionRegisterBlock('xnpdata');
    $attachment_dl_notify = xnpGetDownloadNotificationOptionRegisterBlock('xnpdata');

  // set to template
  global $xoopsTpl;
    $tpl = new XoopsTpl();
  // copy variables in $xoopsTpl to $tpl
  $tpl->assign($xoopsTpl->get_template_vars());

    $tpl->assign('basic', $basic);
    $tpl->assign('preview', $preview);
    $tpl->assign('index', $index);
    $tpl->assign('attachment_dl_limit', $attachment_dl_limit);
    $tpl->assign('attachment_dl_notify', $attachment_dl_notify);
    $tpl->assign('data_file', $data_file);
    $tpl->assign('detail', $detail);
    $tpl->assign('readme', $readme);
    $tpl->assign('rights', $rights);
    $tpl->assign('data_type', xnpdataGetTypes());
    $tpl->assign('data_type_selected', $formdata->getValue('post', 'data_type', 's', false));
    $tpl->assign('xnpdata_experimenter', xoonips_get_multiple_field_template_vars(xoonips_get_orm_from_post('xnpdata', 'experimenter'), 'xnpdata', 'experimenter'));

  // return as HTML
  return $tpl->fetch('db:xnpdata_register_block.html');
}

function xnpdataGetEditBlock($item_id)
{
    $textutil = &xoonips_getutility('text');
    $formdata = &xoonips_getutility('formdata');

  // get BasicInformation / Preview / index block
  $basic = xnpGetBasicInformationEditBlock($item_id);
    $preview = xnpGetPreviewEditBlock($item_id);
    $index = xnpGetIndexEditBlock($item_id);
    $data_file = xnpGetAttachmentEditBlock($item_id, 'data_file');
  // get DetailInformation
  $data_type = $formdata->getValue('post', 'data_type', 's', false);
    if (isset($data_type)) {
        $detail = array(
      'data_type' => $data_type,
      'readme' => '',
      'rights' => '',
      'use_cc' => '',
      'cc_commercial_use' => '',
      'cc_modification' => '',
    );
    } elseif (!empty($item_id)) {
        $detail = xnpdataGetDetailInformation($item_id);
    } else {
        $detail = array();
    }
    $readme = xnpGetTextFileEditBlock($item_id, 'readme', $detail['readme']);
    $rights = xnpGetRightsEditBlock($item_id, $detail['use_cc'], $detail['rights'], $detail['cc_commercial_use'], $detail['cc_modification']);
    $attachment_dl_limit = xnpGetDownloadLimitationOptionEditBlock('xnpdata', xnpdataGetAttachmentDownloadLimitOption($item_id));
    $attachment_dl_notify = xnpGetDownloadNotificationOptionEditBlock('xnpdata', xnpdataGetAttachmentDownloadNotifyOption($item_id));

  // set to template
  global $xoopsTpl;
    $tpl = new XoopsTpl();
  // copy variables in $xoopsTpl to $tpl
  $tpl->assign($xoopsTpl->get_template_vars());

    $tpl->assign('basic', $basic);
    $tpl->assign('preview', $preview);
    $tpl->assign('index', $index);
    $tpl->assign('attachment_dl_limit', $attachment_dl_limit);
    $tpl->assign('attachment_dl_notify', $attachment_dl_notify);
    $tpl->assign('data_file', $data_file);
    $tpl->assign('detail', array_map(array($textutil, 'html_special_chars'), $detail));
    $tpl->assign('readme', $readme);
    $tpl->assign('rights', $rights);
    $tpl->assign('data_type', xnpdataGetTypes());
    $tpl->assign('data_type_selected', $detail['data_type']);

    if (!$formdata->getValue('get', 'post_id', 's', false)) {
        $detail_handler = &xoonips_getormhandler('xnpdata', 'item_detail');
        $detail_orm = &$detail_handler->get($item_id);
        $tpl->assign('xnpdata_experimenter', xoonips_get_multiple_field_template_vars($detail_orm->getExperimenters(), 'xnpdata', 'experimenter'));
    } else {
        $tpl->assign('xnpdata_experimenter', xoonips_get_multiple_field_template_vars(xoonips_get_orm_from_post('xnpdata', 'experimenter'), 'xnpdata', 'experimenter'));
    }

  // return as HTML
  return $tpl->fetch('db:xnpdata_register_block.html');
}

function xnpdataGetConfirmBlock($item_id)
{
    $formdata = &xoonips_getutility('formdata');
    $experimenter_handler = &xoonips_getormhandler('xnpdata', 'experimenter');
    $experimenter_objs = &$formdata->getObjectArray('post', $experimenter_handler->getTableName(), $experimenter_handler, false);

    $textutil = &xoonips_getutility('text');
  // get BasicInformation / Preview / index block
  $ar = array();
    $basic = xnpGetBasicInformationConfirmBlock($item_id);
    $preview = xnpGetPreviewConfirmBlock($item_id);
    $index = xnpGetIndexConfirmBlock($item_id);
    $data_file = xnpGetAttachmentConfirmBlock($item_id, 'data_file');

    $lengths = xnpGetColumnLengths('xnpdata_item_detail');
    $readme = xnpGetTextFileConfirmBlock($item_id, 'readme', $lengths['readme']);
    $rights = xnpGetRightsConfirmBlock($item_id, $lengths['rights']);
    $attachment_dl_limit = xnpGetDownloadLimitationOptionConfirmBlock('xnpdata');
    $attachment_dl_notify = xnpGetDownloadNotificationOptionConfirmBlock('xnpdata');
  // get DetailInformation
  $detail = array(
    'data_type' => array(
      'value' => $formdata->getValue('post', 'data_type', 's', false),
    ),
  );
    xnpConfirmHtml($detail, 'xnpdata_item_detail', array_keys($detail), _CHARSET);
    $types = xnpdataGetTypes();
    $detail['data_type_str'] = array(
    'value' => $textutil->html_special_chars($types[$detail['data_type']['value']], ENT_QUOTES),
  );

    if (xnpHasWithout($basic) || xnpHasWithout($readme) || xnpHasWithout($rights) || xnpHasWithout($detail) || xoonips_is_multiple_field_too_long($experimenter_objs, 'xnpdata', 'experimenter')) {
        global $system_message;
        $system_message = $system_message."\n".'<br /><font color="#ff0000">'._MD_XOONIPS_ITEM_WARNING_FIELD_TRIM.'</font><br />';
    }

  // set to template
  global $xoopsTpl;
    $tpl = new XoopsTpl();
  // copy variables in $xoopsTpl to $tpl
  $tpl->assign($xoopsTpl->get_template_vars());

    $tpl->assign('basic', $basic);
    $tpl->assign('preview', $preview);
    $tpl->assign('index', $index);
    $tpl->assign('attachment_dl_limit', $attachment_dl_limit);
    $tpl->assign('attachment_dl_notify', $attachment_dl_notify);
    $tpl->assign('data_file', $data_file);
    $tpl->assign('detail', $detail);
    $tpl->assign('readme', $readme);
    $tpl->assign('rights', $rights);
    $tpl->assign('xnpdata_experimenter', xoonips_get_multiple_field_template_vars($experimenter_objs, 'xnpdata', 'experimenter'));

  // return as HTML
  return $tpl->fetch('db:xnpdata_confirm_block.html');
}

/** check DetailInformation input
 * called from confirm/registered page.
 */
function xnpdataCheckRegisterParameters(&$message)
{
    $xnpsid = $_SESSION['XNPSID'];
    $formdata = &xoonips_getutility('formdata');

    $messages = array();
    $experimenter = xoonips_get_multi_field_array_from_post('xnpdata', 'experimenter');
    $data_file = $formdata->getFile('data_file', false);
    $data_fileFileID = $formdata->getValue('post', 'data_fileFileID', 'i', false);
    $xoonipsCheckedXID = $formdata->getValue('post', 'xoonipsCheckedXID', 's', false);
    $readmeEncText = $formdata->getValue('post', 'readmeEncText', 's', false);
    $rightsEncText = $formdata->getValue('post', 'rightsEncText', 's', false);
    $rightsUseCC = $formdata->getValue('post', 'rightsUseCC', 'i', false);

    if (empty($experimenter)) {
        $messages[] = _MD_XNPDATA_EXPERIMENTER_REQUIRED;
    }
    if (empty($data_fileFileID) && empty($data_file['name'])) {
        $messages[] = _MD_XNPDATA_DATA_FILE_REQUIRED;
    }

  // require Readme and License if register to public indexes
  $xids = explode(',', $xoonipsCheckedXID);
    $indexes = array();
    if ($xids[0] != $xoonipsCheckedXID) {
        foreach ($xids as $i) {
            $index = array();
            if (xnp_get_index($xnpsid, $i, $index) == RES_OK) {
                $indexes[] = $index;
            } else {
                $messages[] = '<font color="#ff0000">'.xnp_get_last_error_string().'</font>';
                $result = false;
                break;
            }
        }
    }
    if (count($indexes) > 0) {
        foreach ($indexes as $i) {
            if ($i['open_level'] <= OL_GROUP_ONLY) {
                if ($readmeEncText == '') {
                    // readme is not filled
          $messages[] = '<font color="#ff0000">'._MD_XNPDATA_README_REQUIRED.'</font>';
                }
                if ($rightsEncText == '' && $rightsUseCC == '0') {
                    // rights is not filled
          $messages[] = '<font color="#ff0000">'._MD_XNPDATA_RIGHTS_REQUIRED.'</font>';
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

/** check DetailInformation input
 */
function xnpdataCheckEditParameters(&$message)
{
    return xnpdataCheckRegisterParameters($message);
}

function xnpdataInsertItem(&$item_id)
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
            $result = xnpUpdatePreview($item_id);
            if ($result) {
                $result = xnpUpdateAttachment($item_id, 'data_file');
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

  // register DetailInformation
  list($rights, $use_cc, $cc_commercial_use, $cc_modification) = xnpGetRights();

  // it makes string with constant length
  $ar = array(
    'data_type' => $formdata->getValue('post', 'data_type', 's', false),
    'readme' => xnpGetTextFile('readme'),
    'rights' => $rights,
  );
    xnpTrimColumn($ar, 'xnpdata_item_detail', array_keys($ar), _CHARSET);

    $keys = implode(',', array('attachment_dl_limit', 'attachment_dl_notify', 'data_type', 'readme', 'rights', 'use_cc', 'cc_commercial_use', 'cc_modification'));
    $attachment_dl_limit = $formdata->getValue('post', 'attachment_dl_limit', 'i', false);
    $attachment_dl_notify = $formdata->getValue('post', 'attachment_dl_notify', 'i', false);
    $vals = implode('\',\'', array($attachment_dl_limit, $attachment_dl_limit ? $attachment_dl_notify : 0, addslashes($ar['data_type']), addslashes($ar['readme']), addslashes($ar['rights']), $use_cc, $cc_commercial_use, $cc_modification));

    $sql = 'insert into '.$xoopsDB->prefix('xnpdata_item_detail')." ( data_id, $keys ) values ( $item_id, '$vals' ) ";
    $result = $xoopsDB->queryF($sql);
    if ($result == false) {
        echo 'cannot insert item_detail';

        return false;
    }

  // insert experimenter
  $experimenter_handler = &xoonips_getormhandler('xnpdata', 'experimenter');
    $experimenter_objs = &$formdata->getObjectArray('post', $experimenter_handler->getTableName(), $experimenter_handler, false);
    if (!$experimenter_handler->updateAllObjectsByForeignKey('data_id', $item_id, $experimenter_objs)) {
        return false;
    }

    return true;
}

function xnpdataUpdateItem($item_id)
{
    global $xoopsDB;
    $formdata = &xoonips_getutility('formdata');
    $xnpsid = $_SESSION['XNPSID'];

  // edit BasicInformation, Index, Preview, Attachment
  $result = xnpUpdateBasicInformation($item_id);
    if ($result) {
        $result = xnpUpdateIndex($item_id);
        if ($result) {
            $result = xnpUpdatePreview($item_id);
            if ($result) {
                $result = xnpUpdateAttachment($item_id, 'data_file');
                if ($result) {
                    $result = xnp_insert_change_log($xnpsid, $item_id, $formdata->getValue('post', 'change_log', 's', false));
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

  // register DetailInformation
  list($rights, $use_cc, $cc_commercial_use, $cc_modification) = xnpGetRights();

  // trim strings
  $ar = array(
    'data_type' => $formdata->getValue('post', 'data_type', 's', false),
    'readme' => xnpGetTextFile('readme'),
    'rights' => $rights,
  );
    xnpTrimColumn($ar, 'xnpdata_item_detail', array_keys($ar), _CHARSET);

  // register DetailInformation
  $attachment_dl_limit = $formdata->getValue('post', 'attachment_dl_limit', 'i', false);
    $attachment_dl_notify = $formdata->getValue('post', 'attachment_dl_notify', 'i', false);
    $ar = array(
    'attachment_dl_limit'.'=\''.$attachment_dl_limit.'\'',
    'attachment_dl_notify'.'=\''.($attachment_dl_limit ? $attachment_dl_notify : 0).'\'',
    'data_type'.'=\''.addslashes($ar['data_type']).'\'',
    'readme'.'=\''.addslashes($ar['readme']).'\'',
    'rights'.'=\''.addslashes($ar['rights']).'\'',
    'use_cc'.'=\''.$use_cc.'\'',
    'cc_commercial_use'.'=\''.$cc_commercial_use.'\'',
    'cc_modification'.'=\''.$cc_modification.'\'',
  );
    $table = $xoopsDB->prefix('xnpdata_item_detail');
    $sql = implode(',', $ar);
    $sql = 'update '.$table." set $sql where data_id = $item_id ";
    $result = $xoopsDB->queryF($sql);
    if ($result == false) {
        echo $xoopsDB->error();

        return false;
    }

  // insert/update experimenter
  $experimenter_handler = &xoonips_getormhandler('xnpdata', 'experimenter');
    $experimenter_objs = &$formdata->getObjectArray('post', $experimenter_handler->getTableName(), $experimenter_handler, false);
    if (!$experimenter_handler->updateAllObjectsByForeignKey('data_id', $item_id, $experimenter_objs)) {
        return false;
    }

    return true;
}

function xnpdataGetDetailInformationQuickSearchQuery(&$wheres, &$join, $keywords)
{
    global $xoopsDB;
    $data_table = $xoopsDB->prefix('xnpdata_item_detail');
    $data_experimenter_table = $xoopsDB->prefix('xnpdata_experimenter');
    $file_table = $xoopsDB->prefix('xoonips_file');

    $join = " INNER JOIN $data_experimenter_table ON ".$data_experimenter_table.'.data_id  = '.$xoopsDB->prefix('xoonips_item_basic').'.item_id ';
    $wheres = xnpGetKeywordsQueries(array("$data_experimenter_table.experimenter", "$file_table.caption"), $keywords);

    return true;
}

function xnpdataGetAdvancedSearchQuery(&$where, &$join)
{
    global $xoopsDB;
    $formdata = &xoonips_getutility('formdata');

    $basic_table = $xoopsDB->prefix('xoonips_item_basic');
    $data_table = $xoopsDB->prefix('xnpdata_item_detail');
    $data_experimenter_table = $xoopsDB->prefix('xnpdata_experimenter');
    $file_table = $xoopsDB->prefix('xoonips_file');
    $search_text_table = $xoopsDB->prefix('xoonips_search_text');

    $wheres = array();
    $joins = array();
    $xnpdata_data_type = $formdata->getValue('post', 'xnpdata_data_type', 's', false);
    $xnpdata_data_file = $formdata->getValue('post', 'xnpdata_data_file', 's', false);
    $w = xnpGetBasicInformationAdvancedSearchQuery('xnpdata');
    if ($w) {
        $wheres[] = $w;
    }
    if (!empty($xnpdata_data_type)) {
        $wheres[] = $data_table.'.data_type = \''.addslashes($xnpdata_data_type).'\'';
    }
    $w = xnpGetKeywordQuery($data_experimenter_table.'.experimenter', 'xnpdata_experimenter');
    if ($w) {
        $wheres[] = $w;
    }
    $w = xnpGetKeywordQuery($file_table.'.caption', 'xnpdata_caption');
    if ($w) {
        $wheres[] = $w;
        $wheres[] = " $file_table.file_type_id = 1";
    }

    if (!empty($xnpdata_data_file)) {
        $search_text_table = $xoopsDB->prefix('xoonips_search_text');
        $file_table = $xoopsDB->prefix('xoonips_file');
        $searchutil = &xoonips_getutility('search');
        $fulltext_query = $xnpdata_data_file;
        $fulltext_encoding = mb_detect_encoding($fulltext_query);
        $fulltext_criteria = new CriteriaCompo($searchutil->getFulltextSearchCriteria('search_text', $fulltext_query, $fulltext_encoding, $search_text_table));
        $fulltext_criteria->add(new Criteria('is_deleted', 0, '=', $file_table));
        $wheres[] = $fulltext_criteria->render();
    }

    $where = implode(' AND ', $wheres);
    $join = " INNER JOIN $data_experimenter_table ON ".$data_experimenter_table.'.data_id  = '.$xoopsDB->prefix('xoonips_item_basic').'.item_id ';
}

function xnpdataGetAdvancedSearchBlock(&$search_var)
{
    // get BasicInformation / Preview / IndexKeywords block
  $basic = xnpGetBasicInformationAdvancedSearchBlock('xnpdata', $search_var);
    $search_var[] = 'xnpdata_data_type';
    $search_var[] = 'xnpdata_experimenter';
    $search_var[] = 'xnpdata_data_file';

  // set to template
  global $xoopsTpl;
    $tpl = new XoopsTpl();
  // copy variables in $xoopsTpl to $tpl
  $tpl->assign($xoopsTpl->get_template_vars());

    $tpl->assign('data_type', array_merge(array('' => 'Any'), xnpdataGetTypes()));
    $tpl->assign('data_type_selected', 'none');
    $tpl->assign('basic', $basic);
    $tpl->assign('module_name', 'xnpdata');
    $tpl->assign('module_display_name', xnpGetItemTypeDisplayNameByDirname(basename(dirname(__DIR__)), 's'));

  // return as HTML
  return $tpl->fetch('db:xnpdata_search_block.html');
}

function xnpdataGetDetailInformationTotalSize($iids)
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
function xnpdataExportItem($export_path, $fhdl, $item_id, $attachment)
{
    // get DetailInformation
  if (!$fhdl) {
      return false;
  }

    $handler = &xoonips_getormhandler('xnpdata', 'item_detail');
    $detail = &$handler->get($item_id);
    if (!$detail) {
        return false;
    }

    $experimenters = '';
    foreach ($detail->getExperimenters() as $experimenter) {
        $experimenters .= '<experimenter>'.$experimenter->getVar('experimenter', 's').'</experimenter>';
    }

    if (!fwrite($fhdl, "<detail id=\"${item_id}\" version=\"1.03\">\n".'<data_type>'.$detail->getVar('data_type', 's')."</data_type>\n"."<experimenters>{$experimenters}</experimenters>\n".'<rights>'.$detail->getVar('rights', 's')."</rights>\n".'<readme>'.$detail->getVar('readme', 's')."</readme>\n".'<use_cc>'.intval($detail->get('use_cc', 's'))."</use_cc>\n".'<cc_commercial_use>'.intval($detail->get('cc_commercial_use'))."</cc_commercial_use>\n".'<cc_modification>'.intval($detail->get('cc_modification'))."</cc_modification>\n".'<attachment_dl_limit>'.intval($detail->get('attachment_dl_limit'))."</attachment_dl_limit>\n".'<attachment_dl_notify>'.intval($detail->get('attachment_dl_notify'))."</attachment_dl_notify>\n")) {
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

function xnpdataGetLicenseRequired($item_id)
{
    global $xoopsDB;

  // get DetailInformation
  $result = $xoopsDB->query('select * from '.$xoopsDB->prefix('xnpdata_item_detail')." where data_id=$item_id");
    if (!$result) {
        return null;
    }
    $detail = $xoopsDB->fetchArray($result);

    return isset($detail['rights']) && $detail['rights'] != '';
}
function xnpdataGetLicenseStatement($item_id)
{
    global $xoopsDB;

  // get DetailInformation
  $result = $xoopsDB->query('select * from '.$xoopsDB->prefix('xnpdata_item_detail')." where data_id=$item_id");
    if (!$result) {
        return null;
    }
    $detail = $xoopsDB->fetchArray($result);

    return array(isset($detail['rights']) ? $detail['rights'] : '', $detail['use_cc']);
}

function xnpdataGetModifiedFields($item_id)
{
    $ret = array();
    $formdata = &xoonips_getutility('formdata');

    $publicationDateMonth = $formdata->getValue('post', 'publicationDateMonth', 'i', false);
    $publicationDateDay = $formdata->getValue('post', 'publicationDateDay', 'i', false);
    $publicationDateYear = $formdata->getValue('post', 'publicationDateYear', 'i', false);

    $basic = xnpGetBasicInformationArray($item_id);
    if ($basic) {
        if (intval($basic['publication_month']) != intval($publicationDateMonth) || intval($basic['publication_mday']) != intval($publicationDateDay) || intval($basic['publication_year']) != intval($publicationDateYear)) {
            array_push($ret, _MD_XNPDATA_DATE_LABEL);
        }
    }
    $detail = xnpdataGetDetailInformation($item_id);
    if ($detail) {
        foreach (array('data_type' => _MD_XNPDATA_DATA_TYPE_LABEL) as $k => $v) {
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
        $tmp = $formdata->getValue('post', "${k}EncText", 's', false);
        if (!array_key_exists($k, $detail) || $tmp === null) {
            continue;
        }
        if ($detail[$k] != $tmp) {
            array_push($ret, $v);
        }
    }
    // is rights modified ?
    $rightsUseCC = $formdata->getValue('post', 'rightsUseCC', 'i', false);
        $rightsEncText = $formdata->getValue('post', 'rightsEncText', 's', false);
        if ($rightsUseCC !== null) {
            if ($rightsUseCC == 0) {
                if (array_key_exists('rights', $detail) && $rightsEncText != null && $rightsEncText != $detail['rights']) {
                    array_push($ret, _MD_XOONIPS_ITEM_RIGHTS_LABEL);
                }
            } elseif ($rightsUseCC == 1) {
                foreach (array('rightsCCCommercialUse' => 'cc_commercial_use', 'rightsCCModification' => 'cc_modification') as $k => $v) {
                    $tmp = $formdata->getValue('post', $k, 'i', false);
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

        $experimenter_handler = &xoonips_getormhandler('xnpdata', 'experimenter');
        $experimenter_objs = &$formdata->getObjectArray('post', $experimenter_handler->getTableName(), $experimenter_handler, false);
        $detail_handler = &xoonips_getormhandler('xnpdata', 'item_detail');
        $detail_orm = &$detail_handler->get($item_id);
        $experimenter_old_objs = &$detail_orm->getExperimenters();
        if (!xoonips_is_same_objects($experimenter_old_objs, $experimenter_objs)) {
            array_push($ret, _MD_XNPDATA_EXPERIMENTER_LABEL);
        }

    // was data file modified?
    if (xnpIsAttachmentModified('data_file', $item_id)) {
        array_push($ret, _MD_XNPDATA_DATA_FILE_LABEL);
    }
    }

    return $ret;
}

function xnpdataGetTopBlock($itemtype)
{
    return xnpGetTopBlock($itemtype['name'], $itemtype['display_name'], 'images/icon_data.gif', _MD_XNPDATA_EXPLANATION, 'xnpdata_data_type', xnpdataGetTypes());
}

// return 1 if downloadable for login user only
// return 0 if downloadable for everyone
function xnpdataGetAttachmentDownloadLimitOption($item_id)
{
    global $xoopsDB;
    $sql = 'select attachment_dl_limit from '.$xoopsDB->prefix('xnpdata_item_detail')." where data_id=${item_id}";
    $result = $xoopsDB->query($sql);
    if ($result) {
        list($option) = $xoopsDB->fetchRow($result);

        return $option;
    }

    return 0;
}

// return 1 if downloading is notified
// return 0 if downloading is not notified
function xnpdataGetAttachmentDownloadNotifyOption($item_id)
{
    global $xoopsDB;
    $sql = 'select attachment_dl_notify from '.$xoopsDB->prefix('xnpdata_item_detail')." where data_id=${item_id}";
    $result = $xoopsDB->query($sql);
    if ($result) {
        list($notify) = $xoopsDB->fetchRow($result);

        return $notify;
    }

    return 0;
}

function xnpdataSupportMetadataFormat($metadataPrefix, $item_id)
{
    if ($metadataPrefix == 'oai_dc' || $metadataPrefix == 'junii2') {
        return true;
    }

    return false;
}

function xnpdataGetMetadata($prefix, $item_id)
{
    $mydirpath = dirname(__DIR__);
    $mydirname = basename($mydirpath);
    if (!in_array($prefix, array('oai_dc', 'junii2'))) {
        return false;
    }
  // detail information
  $detail_handler = &xoonips_getormhandler($mydirname, 'item_detail');
    $experimenter_handler = &xoonips_getormhandler($mydirname, 'experimenter');
    $detail_obj = &$detail_handler->get($item_id);
    if (empty($detail_obj)) {
        return false;
    }
    $detail = $detail_obj->getArray();
    $criteria = new Criteria('data_id', $item_id);
    $criteria->setSort('experimenter_order');
    $experimenter_objs = &$experimenter_handler->getObjects($criteria);
    $detail['experimenters'] = array();
    foreach ($experimenter_objs as $experimenter_obj) {
        $detail['experimenters'][] = $experimenter_obj->get('experimenter');
    }
    $types = xnpdataGetTypes();
    $detail['data_type_display'] = $types[$detail['data_type']];
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
        $files = $file_handler->getFilesInfo($item_id, 'data_file');
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
