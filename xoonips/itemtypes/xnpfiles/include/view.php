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
$xoonips_path = dirname($itemtype_path).'/xoonips';

$langman = &xoonips_getutility('languagemanager');
$langman->read('main.php', $itemtype_dirname);

// magic string for item title auto fillin
define('XNPFILES_ITEM_TITLE_EMPTY_MAGIC', str_repeat(md5('@@@@@@@@@@@@@@@ XNPFILES_ITEM_TITLE_EMPTY_MAGIC @@@@@@@@@@@@@@@@'), 3));

function xnpfilesGetTypes()
{
    $files_handler = &xoonips_getormhandler('xnpfiles', 'item_detail');
    $files_objs = &$files_handler->getObjects(null, false, 'data_file_filetype', true);
    $html = array();
    foreach ($files_objs as $files_obj) {
        $ext = $files_obj->getVar('data_file_filetype', 's');
        if ($ext != '') {
            $html[$ext] = $ext;
        }
    }

    return $html;
}

/**
 * get DetailInformation by item_id.
 */
function xnpfilesGetDetailInformation($item_id)
{
    global $xoopsDB;
    if (empty($item_id)) {
        return array('data_file_name' => '', 'data_file_mimetype' => '', 'data_file_filetype' => '');
    }

    $sql = 'select * from '.$xoopsDB->prefix('xnpfiles_item_detail')." where files_id=$item_id";
    $result = $xoopsDB->query($sql);
    if ($result == false) {
        echo $xoopsDB->error();

        return false;
    }

    $detail = $xoopsDB->fetchArray($result);

    return $detail;
}

/**
 * @param string $columns
 */
function xnpfilesGetDetailDistinctInfo($columns)
{
    global $xoopsDB;

    $sql = "select distinct($columns) from ".$xoopsDB->prefix('xnpfiles_item_detail')." order by $columns";
    $result = $xoopsDB->query($sql);
    if ($result == false) {
        echo $xoopsDB->error();

        return false;
    }

    $files = array();
    while (false != ($row = $xoopsDB->fetchRow($result))) {
        $files[] = $row;
    }

    return $files;
}

function xnpfilesGetMetaInformation($item_id)
{
    $ret = array();
    $basic = xnpGetBasicInformationArray($item_id);
    $detail = xnpfilesGetDetailInformation($item_id);
    if (!empty($basic)) {
        $ret[_MD_XOONIPS_ITEM_TITLE_LABEL] = implode("\n", $basic['titles']);
        $ret[_MD_XOONIPS_ITEM_CONTRIBUTOR_LABEL] = $basic['contributor'];
        $ret[_MD_XOONIPS_ITEM_KEYWORDS_LABEL] = implode("\n", $basic['keywords']);
        $ret[_MD_XOONIPS_ITEM_DESCRIPTION_LABEL] = $basic['description'];
        $ret[_MD_XOONIPS_ITEM_DOI_LABEL] = $basic['doi'];
        $ret[_MD_XOONIPS_ITEM_CREATION_DATE_LABEL] = $basic['creation_date'];
        $ret[_MD_XOONIPS_ITEM_LAST_UPDATE_DATE_LABEL] = $basic['last_update_date'];
    }
    if (!empty($basic)) {
        $ret[_MD_XNPFILES_DATE_LABEL] = xnpDate($basic['publication_year'], $basic['publication_month'], $basic['publication_mday']);
    }

    return $ret;
}

function xnpfilesGetListBlock($item_basic)
{
    // get uid
    global $xoopsUser;
    $myuid = is_object($xoopsUser) ? $xoopsUser->getVar('uid', 'n') : UID_GUEST;

    // set to template
    global $xoopsTpl;
    $tpl = new XoopsTpl();

    // copy variables in $xoopsTpl to $tpl
    $tpl->assign($xoopsTpl->get_template_vars());

    $xnpfiles_handler = &xoonips_getormcompohandler('xnpfiles', 'item');
    $tpl->assign('xoonips_item', $xnpfiles_handler->getTemplateVar(XOONIPS_TEMPLATE_TYPE_ITEM_LIST, $item_basic['item_id'], $myuid));

    // return as HTML
    return $tpl->fetch('db:xnpfiles_list_block.html');
}

function xnpfilesGetPrinterFriendlyListBlock($item_basic)
{
    return xnpfilesGetListBlock($item_basic);
}

function xnpfilesGetDetailBlock($item_id)
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
    $tpl->assign('files_file', xnpGetAttachmentDetailBlock($item_id, 'files_file'));

    $xnpfiles_handler = &xoonips_getormcompohandler('xnpfiles', 'item');
    $tpl->assign('xoonips_item', $xnpfiles_handler->getTemplateVar(XOONIPS_TEMPLATE_TYPE_ITEM_DETAIL, $item_id, $myuid));

    // return as HTML
    return $tpl->fetch('db:xnpfiles_detail_block.html');
}

function xnpfilesGetPrinterFriendlyDetailBlock($item_id)
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
    $tpl->assign('basic', xnpGetBasicInformationPrinterFriendlyBlock($item_id));
    $tpl->assign('index', xnpGetIndexPrinterFriendlyBlock($item_id));
    $tpl->assign('files_file', xnpGetAttachmentPrinterFriendlyBlock($item_id, 'files_file'));

    $xnpfiles_handler = &xoonips_getormcompohandler('xnpfiles', 'item');
    $tpl->assign('xoonips_item', $xnpfiles_handler->getTemplateVar(XOONIPS_TEMPLATE_TYPE_ITEM_DETAIL, $item_id, $myuid));

    // return as HTML
    return $tpl->fetch('db:xnpfiles_detail_block.html');
}

function xnpfilesGetRegisterBlock()
{
    $formdata = &xoonips_getutility('formdata');

    // get BasicInformation / Preview / index block
    $basic = xnpGetBasicInformationRegisterBlock();
    if ($basic['title'] == '') {
        $basic['title'] = ' ';
    }
    $index = xnpGetIndexRegisterBlock();
    $data_file = xnpGetAttachmentRegisterBlock('files_file');

    $post_id = $formdata->getValue('get', 'post_id', 's', false);
    if (!is_null($post_id)) {
        $file_id = $formdata->getValue('post', 'files_fileFileID', 'i', false);
        if ($file_id == 0) {
            $data_file_name = '';
            $data_file_mimetype = '';
            $data_file_filetype = '';
        } else {
            $file_handler = &xoonips_getormhandler('xoonips', 'file');
            $file_obj = &$file_handler->get($file_id);
            $data_file_name = $file_obj->get('original_file_name', 's');
            $data_file_mimetype = $file_obj->get('mime_type', 's');
            $file_pathinfo = pathinfo($data_file_name);
            $data_file_filetype = $file_pathinfo['extension'];
        }
    }

    if (isset($data_file_name)) {
        $data_file_name = array(
        'name' => _MD_XOONIPS_ITEM_ATTACHMENT_LABEL,
        'value' => $data_file_name,
        );
    } else {
        $data_file_name = '';
    }
    if (isset($data_file_mimetype)) {
        $data_file_mimetype = array(
        'name' => _MD_XOONIPS_ITEM_ATTACHMENT_LABEL,
        'value' => $data_file_mimetype,
        );
    } else {
        $data_file_mimetype = '';
    }
    if (isset($data_file_filetype)) {
        $data_file_filetype = array(
        'name' => _MD_XOONIPS_ITEM_ATTACHMENT_LABEL,
        'value' => $data_file_filetype,
        );
    } else {
        $data_file_filetype = '';
    }

    // set to template
    global $xoopsTpl;
    $tpl = new XoopsTpl();
    // copy variables in $xoopsTpl to $tpl
    $tpl->assign($xoopsTpl->get_template_vars());

    $tpl->assign('basic', $basic);
    $tpl->assign('index', $index);
    $tpl->assign('data_file', $data_file);
    $tpl->assign('data_file_name', $data_file_name);
    $tpl->assign('data_file_mimetype', $data_file_mimetype);
    $tpl->assign('data_file_filetype', $data_file_filetype);
    $tpl->assign('title_empty_magic', XNPFILES_ITEM_TITLE_EMPTY_MAGIC);

    // return as HTML
    return $tpl->fetch('db:xnpfiles_register_block.html');
}

function xnpfilesGetEditBlock($item_id)
{
    $formdata = &xoonips_getutility('formdata');

    // get BasicInformation / Preview / index block
    $basic = xnpGetBasicInformationEditBlock($item_id);
    $index = xnpGetIndexEditBlock($item_id);
    $data_file = xnpGetAttachmentEditBlock($item_id, 'files_file');

    $file_handler = &xoonips_getormhandler('xoonips', 'file');
    $post_id = $formdata->getValue('get', 'post_id', 's', false);
    if (!is_null($post_id)) {
        $file_id = $formdata->getValue('post', 'files_fileFileID', 'i', false);
        $file_obj = &$file_handler->get($file_id);
    } else {
        $criteria = new Criteria('item_id', $item_id);
        $file_objs = &$file_handler->getObjects($criteria);
        $file_obj = &$file_objs[0];
    }
    $data_file_name = $file_obj->get('original_file_name', 's');
    $data_file_mimetype = $file_obj->get('mime_type', 's');
    $file_pathinfo = pathinfo($data_file_name);
    $data_file_filetype = $file_pathinfo['extension'];
    if (isset($data_file_name)) {
        $data_file_name = array(
        'name' => _MD_XOONIPS_ITEM_ATTACHMENT_LABEL,
        'value' => $data_file_name,
        );
    } else {
        $data_file_name = '';
    }
    if (isset($data_file_mimetype)) {
        $data_file_mimetype = array(
        'name' => _MD_XOONIPS_ITEM_ATTACHMENT_LABEL,
        'value' => $data_file_mimetype,
        );
    } else {
        $data_file_mimetype = '';
    }
    if (isset($data_file_filetype)) {
        $data_file_filetype = array(
        'name' => _MD_XOONIPS_ITEM_ATTACHMENT_LABEL,
        'value' => $data_file_filetype,
        );
    } else {
        $data_file_filetype = '';
    }
    // get DetailInformation
    if (!empty($item_id)) {
        $detail = xnpfilesGetDetailInformation($item_id);
    } else {
        $detail = array();
    }

    // set to template
    global $xoopsTpl;
    $tpl = new XoopsTpl();
    // copy variables in $xoopsTpl to $tpl
    $tpl->assign($xoopsTpl->get_template_vars());

    $tpl->assign('basic', $basic);
    $tpl->assign('index', $index);
    $tpl->assign('data_file', $data_file);
    $tpl->assign('data_file_name', $data_file_name);
    $tpl->assign('data_file_mimetype', $data_file_mimetype);
    $tpl->assign('data_file_filetype', $data_file_filetype);
    $tpl->assign('title_empty_magic', XNPFILES_ITEM_TITLE_EMPTY_MAGIC);

    // return as HTML
    return $tpl->fetch('db:xnpfiles_register_block.html');
}

function xnpfilesGetConfirmBlock($item_id)
{
    $formdata = &xoonips_getutility('formdata');
    $textutil = &xoonips_getutility('text');
    // get BasicInformation / Preview / index block
    $basic = xnpGetBasicInformationConfirmBlock($item_id);
    $index = xnpGetIndexConfirmBlock($item_id);
    $file_file = $formdata->getFile('files_file', false);
    $data_file = xnpGetAttachmentConfirmBlock($item_id, 'files_file');
    $file_id = $formdata->getValue('post', 'files_fileFileID', 'i', false);
    // get detail information
    if (is_null($file_file) && $file_id == 0) {
        $data_file_name = '';
        $data_file_mimetype = '';
        $data_file_filetype = '';
    } else {
        if (is_null($file_file)) {
            $file_handler = &xoonips_getormhandler('xoonips', 'file');
            $file_obj = &$file_handler->get($file_id);
            $data_file_name = $file_obj->get('original_file_name', 's');
            $data_file_mimetype = $file_obj->get('mime_type', 's');
        } else {
            $data_file_name = $file_file['name'];
            $data_file_mimetype = $file_file['type'];
        }
        $file_pathinfo = pathinfo($data_file_name);
        $data_file_filetype = $file_pathinfo['extension'];
    }
    if (xnpHasWithout($basic) || xnpHasWithout($data_file)) {
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
    $tpl->assign('data_file', $data_file);
    $tpl->assign('data_file_name', $textutil->html_special_chars($data_file_name));
    $tpl->assign('data_file_mimetype', $textutil->html_special_chars($data_file_mimetype));
    $tpl->assign('data_file_filetype', $textutil->html_special_chars($data_file_filetype));

    // return as HTML
    return $tpl->fetch('db:xnpfiles_confirm_block.html');
}

/**
 * check DetailInformation input
 * called from confirm/registered page.
 */
function xnpfilesCheckRegisterParameters(&$message)
{
    $xnpsid = $_SESSION['XNPSID'];
    $formdata = &xoonips_getutility('formdata');
    $messages = array();
    $files_file = $formdata->getFile('files_file', false);
    $file_id = $formdata->getValue('post', 'files_fileFileID', 'i', false);
    $title = $formdata->getValue('post', 'title', 's', false);
    $xoonipsCheckedXID = $formdata->getValue('post', 'xoonipsCheckedXID', 's', false);

    if (empty($file_id) && empty($files_file['name'])) {
        if ($title == XNPFILES_ITEM_TITLE_EMPTY_MAGIC) {
            $formdata->set('post', 'title', '');
            $messages[] = _MD_XOONIPS_ITEM_TITLE_REQUIRED;
        }
        $messages[] = _MD_XNPFILES_DATA_FILE_REQUIRED;
    } else {
        if ($title == XNPFILES_ITEM_TITLE_EMPTY_MAGIC) {
            if (is_null($files_file)) {
                $file_handler = &xoonips_getormhandler('xoonips', 'file');
                $file_obj = &$file_handler->get($file_id);
                $file_name = $file_obj->get('original_file_name', 's');
            } else {
                $file_name = $files_file['name'];
            }
            if (mb_substr_count($file_name, '.') > 0) {
                $file_name = mb_substr($file_name, 0, mb_strrpos($file_name, '.'));
            }
            $formdata->set('post', 'title', $file_name);
        }
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
function xnpfilesCheckEditParameters(&$message)
{
    return xnpfilesCheckRegisterParameters($message);
}

function xnpfilesInsertItem(&$item_id)
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
                $result = xnpUpdateAttachment($item_id, 'files_file');
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

    // register Detail Information
    $file_id = $formdata->getValue('post', 'files_fileFileID', 'i', false);
    $file_handler = &xoonips_getormhandler('xoonips', 'file');
    $file_obj = &$file_handler->get($file_id);
    $file_name = $file_obj->get('original_file_name');
    $file_mimetype = $file_obj->get('mime_type');
    $file_pathinfo = pathinfo($file_name);
    $file_type = $file_pathinfo['extension'];
    $detail_handler = &xoonips_getormhandler('xnpfiles', 'item_detail');
    $detail_obj = &$detail_handler->create();
    $detail_obj->set('files_id', $item_id);
    $detail_obj->set('data_file_name', $file_name);
    $detail_obj->set('data_file_mimetype', $file_mimetype);
    $detail_obj->set('data_file_filetype', $file_type);
    if (!$detail_handler->insert($detail_obj)) {
        echo 'cannot insert item_detail';

        return false;
    }

    return true;
}

function xnpfilesUpdateItem($item_id)
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
                $result = xnpUpdateAttachment($item_id, 'files_file');
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

    // update item detail information
    $file_id = $formdata->getValue('post', 'files_fileFileID', 'i', false);
    $file_handler = &xoonips_getormhandler('xoonips', 'file');
    $file_obj = &$file_handler->get($file_id);
    $file_name = $file_obj->get('original_file_name');
    $file_mimetype = $file_obj->get('mime_type');
    $file_pathinfo = pathinfo($file_name);
    $file_type = $file_pathinfo['extension'];
    $detail_handler = &xoonips_getormhandler('xnpfiles', 'item_detail');
    $detail_obj = &$detail_handler->get($item_id);
    $detail_obj->set('data_file_name', $file_name);
    $detail_obj->set('data_file_mimetype', $file_mimetype);
    $detail_obj->set('data_file_filetype', $file_type);
    if (!$detail_handler->insert($detail_obj)) {
        echo 'cannot insert item_detail';

        return false;
    }

    return true;
}

function xnpfilesGetDetailInformationQuickSearchQuery(&$wheres, &$join, $keywords)
{
    global $xoopsDB;
    $data_table = $xoopsDB->prefix('xnpfiles_item_detail');
    $file_table = $xoopsDB->prefix('xoonips_file');

    $wheres = xnpGetKeywordsQueries(array("$data_table.data_file_name", "$data_table.data_file_mimetype", "$data_table.data_file_filetype"), $keywords);

    return true;
}

function xnpfilesGetAdvancedSearchQuery(&$where, &$join)
{
    global $xoopsDB;
    $formdata = &xoonips_getutility('formdata');

    $basic_table = $xoopsDB->prefix('xoonips_item_basic');
    $data_table = $xoopsDB->prefix('xnpfiles_item_detail');
    $file_table = $xoopsDB->prefix('xoonips_file');

    $wheres = array();
    $joins = array();
    $xnpfiles_data_file_filetype = $formdata->getValue('post', 'xnpfiles_data_file_filetype', 's', false);
    $data_file_name = $formdata->getValue('post', 'data_file_name', 's', false);
    $w = xnpGetBasicInformationAdvancedSearchQuery('xnpfiles');
    if ($w) {
        $wheres[] = $w;
    }
    if (!empty($xnpfiles_data_file_filetype)) {
        $wheres[] = $data_table.'.data_file_filetype = \''.addslashes($xnpfiles_data_file_filetype).'\'';
    }
    if (!empty($data_file_name)) {
        $wheres[] = $data_table.'.data_file_name = \''.addslashes($data_file_name).'\'';
    }
    $w = xnpGetKeywordQuery($data_table.'.data_file_mimetype', 'data_file_mimetype');
    if ($w) {
        $wheres[] = $w;
    }
    $w = '';
    foreach ($_POST as $key => $value) {
        if (substr($key, 0, 19) == 'data_file_filetype_') {
            if ($w != '') {
                $w .= ' or ';
            }
            $w .= $data_table.'.data_file_filetype = \''.substr($key, 19).'\'';
        }
    }
    if ($w) {
        $wheres[] = $w;
    }

    $where = implode(' and ', $wheres);
    $join = implode(' ', $joins);
}

function xnpfilesGetAdvancedSearchBlock(&$search_var)
{
    // get BasicInformation / Preview / IndexKeywords block
    $basic = xnpGetBasicInformationAdvancedSearchBlock('xnpfiles', $search_var);
    $data_file_name = xnpfilesGetAttachmentFilenameAdvancedSearchBlock('data_file_name');
    $data_file_mimetype = xnpfilesGetAttachmentMimetypeAdvancedSearchBlock('data_file_mimetype');
    $data_file_filetype = xnpfilesGetAttachmentFiletypeAdvancedSearchBlock('data_file_filetype');
    $search_var[] = 'data_file_mimetype';
    $search_var[] = 'data_file_filetype';
    $search_var[] = 'xnpfiles_data_file';

    // set to template
    global $xoopsTpl;
    $tpl = new XoopsTpl();
    // copy variables in $xoopsTpl to $tpl
    $tpl->assign($xoopsTpl->get_template_vars());

    $tpl->assign('basic', $basic);
    $tpl->assign('module_name', 'xnpfiles');
    $tpl->assign('data_file_name', $data_file_name);
    $tpl->assign('data_file_mimetype', $data_file_mimetype);
    $tpl->assign('data_file_filetype', $data_file_filetype);
    $tpl->assign('module_display_name', xnpGetItemTypeDisplayNameByDirname(basename(dirname(__DIR__)), 's'));

    // return as HTML
    return $tpl->fetch('db:xnpfiles_search_block.html');
}

/**
 * @param string $name
 */
function xnpfilesGetAttachmentFilenameAdvancedSearchBlock($name)
{
    // create html
    $html = "<input type='text' name='${name}' value='' />";

    return array('name' => _MD_XOONIPS_ITEM_ATTACHMENT_LABEL, 'value' => $html);
}

/**
 * @param string $name
 */
function xnpfilesGetAttachmentMimetypeAdvancedSearchBlock($name)
{
    // get attachment file
    // create html
    $files = xnpfilesGetDetailDistinctInfo('data_file_mimetype');
    if (count($files) == 0) {
        $html = '<select name="data_file_mimetype"><option value="">Any</option>
				</select>';
    } else {
        $mimetypes = $files;
        $html = '<select name="data_file_mimetype">';
        $html .= '<option value="">Any</option>
		';
        foreach ($mimetypes as $key => $value) {
            $html .= '<option label="'.$value[0].'" value="'.$value[0].'">'.$value[0].'</option>
			';
        }
        $html .= '</select>';
    }

    return array('name' => _MD_XOONIPS_ITEM_ATTACHMENT_LABEL, 'value' => $html);
}

/**
 * @param string $name
 */
function xnpfilesGetAttachmentFiletypeAdvancedSearchBlock($name)
{
    // get attachment file
    // create html
    $files = xnpfilesGetDetailDistinctInfo('data_file_filetype');
    if (count($files) == 0) {
        $html = '<select name="data_file_filetype_Any">
				<option value="any">Any</option>
				</select>';
    } else {
        $mimetypes = $files;
        $html = '<table><tr>';
        $html .= '<td><input type="checkbox" name="data_file_filetype_Any" value="any"/>Any</td>
		';
        $cnt = 1;
        foreach ($mimetypes as $key => $value) {
            if ($value[0] == '') {
                $html .= '<td><input type="checkbox" name="data_file_filetype_'.$value[0].'"/>none</td>
				';
            } else {
                $html .= '<td><input type="checkbox" name="data_file_filetype_'.$value[0].'"/>'.$value[0].'</td>
				';
            }
            ++$cnt;
            if ($cnt >= 10) {
                $html .= '</tr><tr>';
                $cnt = 0;
            }
        }
        $html .= '</tr></table>';
    }

    return array('name' => _MD_XOONIPS_ITEM_ATTACHMENT_LABEL, 'value' => $html);
}

function xnpfilesGetDetailInformationTotalSize($iids)
{
    return xnpGetTotalFileSize($iids);
}

/**
 * write item detail information xml form export.
 *
 * @see xnpExportItem
 *
 * @param string   $export_path export file path
 * @param resource $fhdl        output file handle
 * @param int      $item_id     target item id
 * @param bool     $attachment  true if export attachment or image file
 *
 * @return null|boolean false if failure
 */
function xnpfilesExportItem($export_path, $fhdl, $item_id, $attachment)
{
    global $xoopsDB;

    if (!$fhdl) {
        return false;
    }

    // get DetailInformation
    $result = $xoopsDB->query('select * from '.$xoopsDB->prefix('xnpfiles_item_detail')." where files_id=$item_id");
    if (!$result) {
        return null;
    }
    $detail = $xoopsDB->fetchArray($result);
    if (!fwrite($fhdl, "<detail id=\"${item_id}\">\n".'<data_file_name>'.htmlspecialchars($detail['data_file_name'], ENT_QUOTES)."</data_file_name>\n".'<data_file_mimetype>'.htmlspecialchars($detail['data_file_mimetype'], ENT_QUOTES)."</data_file_mimetype>\n".'<data_file_filetype>'.htmlspecialchars($detail['data_file_filetype'], ENT_QUOTES)."</data_file_filetype>\n")) {
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

function xnpfilesGetModifiedFields($item_id)
{
    $ret = array();
    $formdata = &xoonips_getutility('formdata');

    $basic = xnpGetBasicInformationArray($item_id);

    $detail = xnpfilesGetDetailInformation($item_id);

    if ($detail) {
        // was data file modified?
        if (xnpIsAttachmentModified('files_file', $item_id)) {
            array_push($ret, _MD_XNPFILES_DATA_FILE_LABEL);
        }
    }

    return $ret;
}

function xnpfilesGetTopBlock($itemtype)
{
    return xnpGetTopBlock($itemtype['name'], $itemtype['display_name'], 'images/icon_files.gif', _MD_XNPFILES_EXPLANATION, 'xnpfiles_data_file_filetype', xnpfilesGetTypes());
}

function xnpfilesSupportMetadataFormat($metadataPrefix, $item_id)
{
    if ($metadataPrefix == 'oai_dc' || $metadataPrefix == 'junii2') {
        return true;
    }

    return false;
}

function xnpfilesGetMetadata($prefix, $item_id)
{
    $mydirpath = dirname(__DIR__);
    $mydirname = basename($mydirpath);
    if (!in_array($prefix, array('oai_dc', 'junii2'))) {
        return false;
    }

    // detail information
    $detail_handler = &xoonips_getormhandler($mydirname, 'item_detail');
    $detail_obj = &$detail_handler->get($item_id);
    if (empty($detail_obj)) {
        return false;
    }
    $detail = $detail_obj->getArray();
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
    $files = $file_handler->getFilesInfo($item_id, 'files_file');
    foreach ($files as $file) {
        if (!in_array($file['mime_type'], $mimetypes)) {
            $mimetypes[] = $file['mime_type'];
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
