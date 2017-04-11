<?php

// $Revision: 1.1.1.24 $
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

include_once $xoonips_path.'/include/extra_param.inc.php';
include_once $xoonips_path.'/class/xoonips_item_event_dispatcher.class.php';
include_once dirname(__DIR__).'/class/item_event_listener.class.php';

$dispatcher = &XooNIpsItemEventDispatcher::getInstance();
$dispatcher->registerEvent(new XNPBinderItemEventListener());

/**
 * Get DetailInformation from item_id.
 *
 * Return item's array registered in binder specified by $item_id.
 *
 * @return array => Execution result
 * @return false => Miss
 */
function xnpbinderGetDetailInformation($item_id)
{
    global $xoopsDB;
    $result = $xoopsDB->query('select item_id from '.$xoopsDB->prefix('xnpbinder_binder_item_link')." where binder_id=$item_id order by sort_num");
    if ($result == false) {
        return false;
    }

    $ids = array();
    while ($row = $xoopsDB->fetchRow($result)) {
        $ids[] = $row[0];
    }

    $items = array();
    $cri = array();
    if (xnp_get_items($_SESSION['XNPSID'], $ids, $cri, $items) != RES_OK) {
        // can't retrieve items
    return false;
    }

    return $items;
}

function xnpbinderGetListBlock($item_basic)
{
    // get uid
  global $xoopsUser;
    $myuid = is_object($xoopsUser) ? $xoopsUser->getVar('uid', 'n') : UID_GUEST;

  // Variables are set to template.
  global $xoopsTpl;
    $tpl = new XoopsTpl();
  // Variables set to $xoopsTpl is copied to $tpl.
  $tpl->assign($xoopsTpl->get_template_vars());

    $xnpbinder_handler = &xoonips_getormcompohandler('xnpbinder', 'item');
    $tpl->assign('xoonips_item', $xnpbinder_handler->getTemplateVar(XOONIPS_TEMPLATE_TYPE_ITEM_LIST, $item_basic['item_id'], $myuid));

  // Output in HTML.
  return $tpl->fetch('db:xnpbinder_list_block.html');
}

function xnpbinderGetDetailBlock($item_id)
{
    // Get Block of BasicInformation / RegisteredItem.
  $basic = xnpGetBasicInformationDetailBlock($item_id);
    $index = xnpGetIndexDetailBlock($item_id);
    $detail = xnpbinder_get_registered_items($item_id);

  // Variables are set to template.
  global $xoopsTpl;
    $tpl = new XoopsTpl();
  // Variables set to $xoopsTpl ( $xoops_url etc.. ) is copied to $tpl.
  $tpl->assign($xoopsTpl->get_template_vars());

    $tpl->assign('editable', xnp_get_item_permission($_SESSION['XNPSID'], $item_id, OP_MODIFY));
    $tpl->assign('basic', $basic);
    $tpl->assign('index', $index);
    $tpl->assign('detail', $detail);

  // Output in HTML.
  return $tpl->fetch('db:xnpbinder_detail_block.html');
}

function xnpbinderGetPrinterFriendlyDetailBlock($item_id)
{
    // Get Block of BasicInformation / RegisteredItem.
  $basic = xnpGetBasicInformationPrinterFriendlyBlock($item_id);
    $index = xnpGetIndexPrinterFriendlyBlock($item_id);
    $detail = xnpbinder_get_registered_items($item_id);

  // Variables are set to template.
  global $xoopsTpl;
    $tpl = new XoopsTpl();
  // Variables set to $xoopsTpl ( $xoops_url etc.. ) is copied to $tpl.
  $tpl->assign($xoopsTpl->get_template_vars());

    $tpl->assign('basic', $basic);
    $tpl->assign('index', $index);
    $tpl->assign('detail', $detail);

  // Output in HTML.
  return $tpl->fetch('db:xnpbinder_detail_block.html');
}

function xnpbinderGetRegisterBlock()
{
    $extra_param = xoonips_extra_param_restore();
    if ($extra_param) {
        $_POST['title'] = $extra_param['title'];
        $_POST['keywords'] = $extra_param['keywords'];
        $_POST['description'] = $extra_param['description'];
        $_POST['doi'] = $extra_param['doi'];
        $_POST['xoonipsCheckedXID'] = $extra_param['xoonipsCheckedXID'];
    }

  // Get Block of BasicInformation / RegisteredItem.
  $basic = xnpGetBasicInformationRegisterBlock();
    $index = xnpGetIndexRegisterBlock();
    $items = xnpbinder_get_to_be_registered_items();

  // Variables are set to template.
  global $xoopsTpl;
    $tpl = new XoopsTpl();
  // Variables set to $xoopsTpl ( $xoops_url etc.. ) is copied to $tpl.
  $tpl->assign($xoopsTpl->get_template_vars());

    $tpl->assign('basic', $basic);
    $tpl->assign('index', $index);
    $tpl->assign('detail', $items);
    $tpl->assign('submit_url', 'register.php');

  // Output in HTML.
  return $tpl->fetch('db:xnpbinder_register_block.html');
}

function xnpbinderGetEditBlock($item_id)
{
    $items = array();

    $extra_param = xoonips_extra_param_restore();
    if ($extra_param) {
        $_POST['item_id'] = $extra_param['item_id'];
        $_POST['title'] = $extra_param['title'];
        $_POST['keywords'] = $extra_param['keywords'];
        $_POST['description'] = $extra_param['description'];
        $_POST['doi'] = $extra_param['doi'];
        $_POST['change_log'] = $extra_param['change_log'];
        $_POST['xoonipsCheckedXID'] = $extra_param['xoonipsCheckedXID'];
    }

  // Get block of BasicInformation / Preview / index
  $basic = xnpGetBasicInformationEditBlock($item_id);
    $index = xnpGetIndexEditBlock($item_id);
    $formdata = &xoonips_getutility('formdata');
    $op_post = $formdata->getValue('post', 'op', 's', false);
    $xoonips_item_id_post = $formdata->getValueArray('post', 'xoonips_item_id', 'i', false);

    if (isset($op_post) && ($op_post == 'add_selected_item' || $op_post == 'delete' || $op_post == '')) {
        $items = xnpbinder_get_to_be_registered_items();
    } else {
        $items = xnpbinder_get_registered_items($item_id);
    }

  // Variables are set to template.
  global $xoopsTpl;
    $tpl = new XoopsTpl();
  // Variables set to $xoopsTpl ( $xoops_url etc.. ) is copied to $tpl.
  $tpl->assign($xoopsTpl->get_template_vars());

    $tpl->assign('item_id', $item_id);
    $tpl->assign('basic', $basic);
    $tpl->assign('index', $index);
    $tpl->assign('detail', $items);
    $tpl->assign('submit_url', 'edit.php');

  // Output in HTML.
  return $tpl->fetch('db:xnpbinder_register_block.html');
}

function xnpbinderGetConfirmBlock($item_id)
{
    $textutil = &xoonips_getutility('text');
    $formdata = &xoonips_getutility('formdata');

    $post_vars = array();
    foreach ($_POST as $key1 => $val1) {
        if (is_array($val1)) {
            $post_vars[$key1] = array_map(array($textutil, 'html_special_chars'), $val1);
        } else {
            $post_vars[$key1] = $textutil->html_special_chars($val1);
        }
    }

    if (xnpbinder_public_binder_has_not_public_item($formdata->getValueArray('post', 'xoonips_item_id', 'i', false), xnpbinder_get_add_to_index_id_form_data())) {
        global $xoopsTpl;
        $tpl = new XoopsTpl();
        $tpl->assign($xoopsTpl->get_template_vars());
        $tpl->assign('post_vars', $post_vars);
        $tpl->assign('message', sprintf(_MD_XNPBINDER_REMOVE_NONPUBLIC_ITEM, number_of_nonpublic_item($formdata->getValueArray('post', 'xoonips_item_id', 'i', false))));
        $tpl->assign('form_data_name_yes', 'delete_nonpublic_yes');

        return $tpl->fetch('db:xnpbinder_confirm_block2.html');
    } elseif (xnpbinder_group_binder_has_private_item($formdata->getValueArray('post', 'xoonips_item_id', 'i', false), xnpbinder_get_add_to_index_id_form_data())) {
        global $xoopsTpl;
        $tpl = new XoopsTpl();
        $tpl->assign($xoopsTpl->get_template_vars());
        $tpl->assign('post_vars', $post_vars);
        $tpl->assign('message', sprintf(_MD_XNPBINDER_REMOVE_PRIVATE_ITEM, number_of_private_item($formdata->getValueArray('post', 'xoonips_item_id', 'i', false))));
        $tpl->assign('form_data_name_yes', 'delete_private_yes');

        return $tpl->fetch('db:xnpbinder_confirm_block2.html');
    } else {
        // Get block of BasicInformation / Preview / index
    $basic = xnpGetBasicInformationConfirmBlock($item_id);
        $index = xnpGetIndexConfirmBlock($item_id);
        $items = xnpbinder_get_to_be_registered_items();

        if (xnpHasWithout($basic)) {
            global $system_message;
            $system_message = $system_message."\n<br /><font color='#ff0000'>"._MD_XOONIPS_ITEM_WARNING_FIELD_TRIM.'</font><br />';
        }

    // Variables are set to template.
    global $xoopsTpl;
        $tpl = new XoopsTpl();
    // Variables set to $xoopsTpl ( $xoops_url etc.. ) is copied to $tpl.
    $tpl->assign($xoopsTpl->get_template_vars());

        $tpl->assign('basic', $basic);
        $tpl->assign('index', $index);
        $tpl->assign('detail', $items);

    // Output in HTML.
    return $tpl->fetch('db:xnpbinder_confirm_block.html');
    }
}

function xnpbinderInsertItem(&$binder_id)
{
    global $xoopsDB;

  // Insert BasicInformation, Index, Preview, Attachment
  $binder_id = 0;
    $result = xnpInsertBasicInformation($binder_id);
    if ($result) {
        $result = xnpUpdateIndex($binder_id);
        if (!$result) {
            xnpDeleteBasicInformation($_SESSION['XNPSID'], $binder_id);
        }
    }
    if (!$result) {
        return false;
    }

  // Insert DetailInformation
  $detail_handler = &xoonips_getormhandler('xnpbinder', 'item_detail');
    $detail = &$detail_handler->create();
    $detail->setVar('binder_id', $binder_id, true);
    if (!$detail_handler->insert($detail)) {
        echo 'cannot insert item_detail';

        return false;
    }
    $xoonips_item_id = xnpbinder_get_xoonips_item_id();
    foreach ($xoonips_item_id as $iid) {
        $result = $xoopsDB->queryF('insert into '.$xoopsDB->prefix('xnpbinder_binder_item_link')." ( binder_id, item_id ) values ( $binder_id, $iid )");
        if ($result == false) {
            echo 'cannot insert item_detail';

            return false;
        }
    }

    return true;
}

function xnpbinderUpdateItem($binder_id)
{
    global $xoopsDB;

    $formdata = &xoonips_getutility('formdata');

  // Edit BasicInformation, Index, Preview, Attachment
  $result = xnpUpdateBasicInformation($binder_id);
    if ($result) {
        $result = xnpUpdateIndex($binder_id);
        if ($result) {
            $result = xnp_insert_change_log($_SESSION['XNPSID'], $binder_id, $formdata->getValue('post', 'change_log', 's', false));
            $result = !$result;
            if (!$result) {
                echo ' xnp_insert_change_log failed.';
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

  // Insert DetailInformation
  $result = $xoopsDB->queryF('delete from '.$xoopsDB->prefix('xnpbinder_binder_item_link')." where binder_id=$binder_id");
    $xoonips_item_id = xnpbinder_get_xoonips_item_id();
    foreach ($xoonips_item_id as $iid) {
        $result = $xoopsDB->queryF('insert into '.$xoopsDB->prefix('xnpbinder_binder_item_link')." ( binder_id, item_id ) values ( $binder_id, $iid )");
        if ($result == false) {
            echo 'cannot insert item_detail';

            return false;
        }
    }

    return true;
}

function xnpbinderGetSearchBlock($item_id)
{
    // todo: It is not decide yet.
}

function xnpbinderCorrectRegisterParameters()
{
    $formdata = &xoonips_getutility('formdata');
    $delete_private_no = $formdata->getValue('post', 'delete_private_no', 's', false);
    $delete_private_yes = $formdata->getValue('post', 'delete_private_yes', 's', false);
    $delete_nonpublic_no = $formdata->getValue('post', 'delete_nonpublic_no', 's', false);
    $delete_nonpublic_yes = $formdata->getValue('post', 'delete_nonpublic_yes', 's', false);
    $xoonips_item_id = $formdata->getValueArray('post', 'xoonips_item_id', 'i', false);
    $xoonipsCheckedXID = $formdata->getValue('post', 'xoonipsCheckedXID', 's', false);
    if (isset($delete_nonpublic_yes) && isset($xoonips_item_id)) {
        // delete non-public items in binder
    $public_item_id = array();
        if (xnp_extract_public_item_id($_SESSION['XNPSID'], $xoonips_item_id, $public_item_id) == RES_OK) {
            $_POST['xoonips_item_id'] = $xoonips_item_id = $public_item_id;
        }
    }
    if (isset($delete_nonpublic_no) && isset($xoonipsCheckedXID)) {
        // not to register in public index
    // remove public index id from $_POST['xoonipsCheckedXID'];
    $ids = array();
        foreach (explode(',', $xoonipsCheckedXID) as $i) {
            $ids[] = intval($i);
        }

        $index_handler = &xoonips_getormhandler('xoonips', 'index');
        $criteria = new CriteriaCompo(new Criteria('open_level', OL_PUBLIC, '!='));
        $criteria->add(xnpbinder_criteria_where_in('index_id', $ids));
    // new Criteria( 'index_id', '(' . implode( ',', $ids ) . ')' , 'IN' ) );
    $indexes = &$index_handler->getObjects($criteria, true);
        $_POST['xoonipsCheckedXID'] = implode(',', array_keys($indexes));
    }
    if (isset($delete_private_yes) && isset($xoonips_item_id)) {
        // delete private items in binder
    $private_ids = xnpbinder_extract_private_item_id($xoonips_item_id);
        $_POST['xoonips_item_id'] = $xoonips_item_id = array_diff($xoonips_item_id, $private_ids);
    }
    if (isset($delete_private_no) && isset($xoonipsCheckedXID)) {
        // remove public & group index from $_POST['xoonipsCheckedXID'];
    $ids = array();
        foreach (explode(',', $xoonipsCheckedXID) as $i) {
            $index_ids[] = intval($i);
        }

        $index_handler = &xoonips_getormhandler('xoonips', 'index');
        $criteria = new CriteriaCompo(new Criteria('open_level', OL_PUBLIC, '!='));
        $criteria->add(new Criteria('open_level', OL_GROUP_ONLY, '!='));
        $criteria->add(xnpbinder_criteria_where_in('index_id', $index_ids));
    // new Criteria( 'index_id', '(' . implode( ',', $index_ids ) . ')' , 'IN' ) );
    $private_indexes = &$index_handler->getObjects($criteria, true);
        $private_index_ids = array();
        foreach ($private_indexes as $index) {
            $private_index_ids[] = $index->get('index_id');
        }
        $_POST['xoonipsCheckedXID'] = implode(',', $private_index_ids);
    }
}

function xnpbinderCheckRegisterParameters(&$msg)
{
    $message = '';
    $formdata = &xoonips_getutility('formdata');
    if (xnpbinder_no_binder_item($formdata->getValueArray('post', 'xoonips_item_id', 'i', false))) {
        $message = $message.'<br /><span style="color: red;">'._MD_XNPBINDER_ITEM_REQUIRED.'</span>';
    }

    if (xnpbinder_public_binder_has_not_public_item($formdata->getValueArray('post', 'xoonips_item_id', 'i', false), xnpbinder_get_add_to_index_id_form_data())) {
        $message = $message.'<br /><span style="color: red;">'._MD_XNPBINDER_PUBLIC_BINDER_HAS_NOT_PUBLIC_ITEM.'</span>';
    }

    if (xnpbinder_group_binder_has_private_item($formdata->getValueArray('post', 'xoonips_item_id', 'i', false), xnpbinder_get_add_to_index_id_form_data())) {
        $message = $message.'<br /><span style="color: red;">'._MD_XNPBINDER_GROUP_BINDER_HAS_PRIVATE_ITEM.'</span>';
    }
    $msg .= $message;

    return empty($message);
}

function xnpbinderCheckEditParameters(&$msg)
{
    return xnpbinderCheckRegisterParameters($msg);
}

function xnpbinderCorrectEditParameters()
{
    xnpbinderCorrectRegisterParameters();
}

function xnpbinderGetAdvancedSearchBlock(&$search_var)
{
    global $xoopsTpl;

    $tpl = new XoopsTpl();
    $tpl->assign($xoopsTpl->get_template_vars());
  // copy variables in $xoopsTpl to $tpl
  $tpl->assign('basic', xnpGetBasicInformationAdvancedSearchBlock('xnpbinder', $search_var));
    $tpl->assign('module_name', 'xnpbinder');
    $tpl->assign('module_display_name', xnpGetItemTypeDisplayNameByDirname(basename(dirname(__DIR__)), 's'));
  // return as HTML
  return $tpl->fetch('db:xnpbinder_search_block.html');
}

function xnpbinderGetAdvancedSearchQuery(&$where, &$join)
{
    // global $xoopsDB;
  // $binder_table = $xoopsDB->prefix('xnpbinder_binder_item_link');
  $wheres = array();
    $w = xnpGetBasicInformationAdvancedSearchQuery('xnpbinder');
    if ($w) {
        $wheres[] = $w;
    }
  // $w = xnpGetKeywordQuery($binder_table.'.item_link', 'xnpbinder_item_link'); if( $w ) $wheres[] = $w;
  $where = implode(' and ', $wheres);
    $join = '';
}

function xnpbinderGetDetailInformationQuickSearchQuery(&$wheres, &$join, $keywords)
{
    $wheres = $join = '';

    return true;
}

function xnpbinderGetMetaInformation($item_id)
{
    // binder's meta information is not required.
  return array();
}

function xnpbinderGetModifiedFields($item_id)
{
    $ret = array();
    $formdata = &xoonips_getutility('formdata');
    $new_iids = is_array($formdata->getValueArray('post', 'xoonips_item_id', 'i', false)) ? $formdata->getValueArray('post', 'xoonips_item_id', 'i', false) : array();
    $old_iids = array();
    $result = xnp_get_item_id_by_binder_id($_SESSION['XNPSID'], $item_id, array(), $old_iids);
    if ($result == RES_OK) {
        $diff = array_diff($new_iids, $old_iids) + array_diff($old_iids, $new_iids);
        if (count($diff)) {
            array_push($ret, _MD_XNPBINDER_ITEM_LABEL);
        }
    }

    return $ret;
}

function xnpbinderGetTopBlock($itemtype)
{
    return xnpGetTopBlock($itemtype['name'], $itemtype['display_name'], 'images/icon_binder.gif', _MD_XNPBINDER_EXPLANATION, false, false);
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
function xnpbinderExportItem($export_path, $fhdl, $item_id, $attachment)
{
    if (!$fhdl) {
        return false;
    }

    $link_handler = &xoonips_getormhandler('xnpbinder', 'binder_item_link');
    $criteria = new Criteria('binder_id', intval($item_id));
    $links = &$link_handler->getObjects($criteria);
    if (!$links) {
        return false;
    }
    if (!fwrite($fhdl, "<detail id=\"${item_id}\">\n")) {
        return false;
    }
    foreach ($links as $link) {
        if (!fwrite($fhdl, '<binder_item_link>'.$link->get('item_id')."</binder_item_link>\n")) {
            return false;
        }
    }
    if (!fwrite($fhdl, "</detail>\n")) {
        return false;
    }

    return true;
}

/**
 * get item ids must be exported with other item.
 *
 * return id of child items of a binder(its id is $item_id)
 *
 * @param $item_id integer item id to export
 *
 * @return array of integer item id(s) exported with other item. False if failed.
 */
function xnpbinderGetExportItemId($item_id)
{
    $result = array();
    $link_handler = &xoonips_getormhandler('xnpbinder', 'binder_item_link');
    $criteria = new Criteria('binder_id', intval($item_id));
    $links = &$link_handler->getObjects($criteria);
    if (!$links) {
        return false;
    }
    foreach ($links as $link) {
        // don't export binder item of this binder
    $item_type_handler = &xoonips_getormhandler('xoonips', 'item_type');
        if ('xnpbinder' == $item_type_handler->get('name')) {
            continue;
        }
        $result[] = $link->get('item_id');
    }

    return $result;
}

function xnpbinderSupportMetadataFormat($metadataPrefix, $item_id)
{
    if ($metadataPrefix == 'oai_dc' || $metadataPrefix == 'junii2') {
        return true;
    }

    return false;
}

function xnpbinderGetMetadata($prefix, $item_id)
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
    $detail['links'] = xnpbidner_get_child_item_urls($item_id);
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
    $tpl->assign('repository', $repository);
    $xml = $tpl->fetch('db:'.$mydirname.'_oaipmh_'.$prefix.'.xml');

    return $xml;
}

/**
 * return url of child items of a binder.
 *
 * @param int $item_id item id of binder
 * @reutrn array string of URL of child items
 */
function xnpbidner_get_child_item_urls($binder_item_id)
{
    include_once dirname(dirname(__DIR__)).'/xoonips/include/lib.php';

    $binder_item_link_handler = &xoonips_getormhandler('xnpbinder', 'binder_item_link');
    $criteria = new Criteria('binder_id', $binder_item_id);
    $links = &$binder_item_link_handler->getObjects($criteria);
    if (!$links) {
        return array();
    }

    $result = array();
    foreach ($links as $child) {
        $basic_handler = &xoonips_getormhandler('xoonips', 'item_basic');
        $basic = &$basic_handler->get($child->get('item_id'));
        $doi = $basic->get('doi');
        $result[] = xnpGetItemDetailURL($basic->get('item_id'));
    }

    return $result;
}

/**
 * get form data(named xoonips_item_id) from extra_param or POST.
 *
 * If extra_param is given in form data, it returns extra_param['xoonips_item_id'].
 * Otherwise returns $_POST['xoonips_item_id'].
 *
 * @return array of integer(item id of binder's item)
 */
function xnpbinder_get_xoonips_item_id()
{
    $extra_param = xoonips_extra_param_restore();
    if ($extra_param) {
        return is_array(@$extra_param['xoonips_item_id']) ? $extra_param['xoonips_item_id'] : array();
    }
    $formdata = &xoonips_getutility('formdata');
    $result = $formdata->getValueArray('post', 'xoonips_item_id', 'i', false, array());

    return $result;
}

/**
 * get an array of item id(s) that is in a binder.
 *
 * @param sess_id string session id
 * @param binder_id integer binder id
 * @param cri
 * @param iids array of item ids that is in a binder
 *
 * @return RES_OK
 * @return RES_DB_NOT_INITIALIZED
 * @return RES_NO_SUCH_SESSION
 * @return RES_DB_QUERY_ERROR
 * @return RES_ERROR
 */
function xnp_get_item_id_by_binder_id($sess_id, $binder_id, $cri, &$iids)
{
    $binder_id = (int) $binder_id;
    $iids = array();

    global $xoopsDB;
    if (!xnp_is_valid_session_id($sess_id)) {
        return RES_NO_SUCH_SESSION;
    }

    $sql = 'SELECT t1.item_id FROM '.$xoopsDB->prefix('xoonips_item_basic').' as t1, '.$xoopsDB->prefix('xnpbinder_binder_item_link').' as t2, '.$xoopsDB->prefix('xoonips_item_title').' as t3 ';
    $sql .= ' WHERE t1.item_id = t2.item_id';
    $sql .= " AND t2.binder_id=$binder_id";
    $sql .= ' AND t3.title_id='.DEFAULT_ORDER_TITLE_OFFSET.' AND t3.item_id=t1.item_id';
    $sql .= xnp_criteria2str($cri);
    $result = $xoopsDB->query($sql);
    if (!$result) {
        return RES_DB_QUERY_ERROR;
    }
    while (list($iid) = $xoopsDB->fetchRow($result)) {
        $iids[] = $iid;
    }

    return RES_OK;
}

/**
 * select items from given items by specified open_level.
 *
 * @param $open_level integer OL_PUBLIC|OL_GROUP_ONLY|OL_PRIVATE
 *
 * @return array of item id
 */
function xnpbinder_get_item_id_by_open_level($open_level, $item_ids = array())
{
    $index_item_link_handler = &xoonips_getormhandler('xoonips', 'index_item_link');
    $join = new XooNIpsJoinCriteria('xoonips_index', 'index_id', 'index_id');
    $criteria = new CriteriaCompo(new Criteria('open_level', $open_level));
    $criteria->add(new Criteria('certify_state', CERTIFIED));
    if (count($item_ids) > 0) {
        $criteria->add(xnpbinder_criteria_where_in('item_id', $item_ids));
    }
  // new Criteria( 'item_id', '(' . implode( ',', $ids ) . ')', 'IN' ) );
  $index_item_links = &$index_item_link_handler->getObjects($criteria, false, '', false, $join);

    $selected_ids = array();
    foreach ($index_item_links as $link) {
        $selected_ids[] = $link->get('item_id');
    }

    return $selected_ids;
}

/**
 * count a number of non public items of given item id array.
 *
 * @param $item_ids
 *
 * @return int number of non public items
 */
function number_of_nonpublic_item($item_ids)
{
    $public_item_id = array();
    if (xnp_extract_public_item_id($_SESSION['XNPSID'], $item_ids, $public_item_id) != RES_OK) {
        return 0;
    }

    return count($item_ids) - count($public_item_id);
}

/**
 * count a number of private items of given item id array.
 *
 * @param $item_ids
 *
 * @return int number of private items
 */
function number_of_private_item($item_ids)
{
    $public_id = xnpbinder_get_item_id_by_open_level(OL_PUBLIC, $item_ids);
    $group_only_id = xnpbinder_get_item_id_by_open_level(OL_GROUP_ONLY, $item_ids);
    $private_id = xnpbinder_get_item_id_by_open_level(OL_PRIVATE, $item_ids);

    return count(array_diff($item_ids, $public_id, $group_only_id));
}

/**
 * If item_id registered from now on is given by GET/POST,
 * item's information is read from database and it returns as association_array.
 * op=='delete'  =>  IDs in selected[] are deleted from item_id[].
 *
 * Structure of association_array
 *   array( 'html' => HTML of item's information, 'item_id' => number of item_id )
 */
function xnpbinder_get_to_be_registered_items()
{
    $items = array();
    $cri = array();
    $item_id = array();
    $formdata = &xoonips_getutility('formdata');
    $xnpbinder_selected_to_delete = $formdata->getValueArray('post', 'xnpbinder_selected_to_delete', 'i', false);
    if (!$xnpbinder_selected_to_delete) {
        $xnpbinder_selected_to_delete = array();
    }
    $selected = $formdata->getValueArray('post', 'selected', 'i', false);
    $selected_hidden = $formdata->getValueArray('post', 'selected_hidden', 'i', false);
    $xoonips_item_id = xnpbinder_get_xoonips_item_id();
    $op = $formdata->getValue('both', 'op', 's', false, '');
    if ($op == 'delete') {
        $xoonips_item_id = array_diff(xnpbinder_get_xoonips_item_id(), $xnpbinder_selected_to_delete);
    // delete selected items from item_id
    } elseif ($op == 'add_selected_item') {
        // remove deselected items and add selected new item.
    $xoonips_item_id = xnpbinder_get_xoonips_item_id() ? xnpbinder_get_xoonips_item_id() : array();
        $xoonips_item_id = array_unique(array_merge(array_diff(array_merge($selected, $selected_hidden), $xoonips_item_id), array_intersect($xoonips_item_id, array_merge($selected, $selected_hidden))));
        $selected = array();
    } else {
        // default: restore previous binder child items
    $xoonips_item_id = xnpbinder_get_xoonips_item_id() ? xnpbinder_get_xoonips_item_id() : array();
    }

    xnp_get_items($_SESSION['XNPSID'], $xoonips_item_id, $cri, $items);
    $itemtypes = array();
  // $itemtypes[<item_type_id>]=array( detail of an itemtype );
  $tmp = array();
    if (xnp_get_item_types($tmp) != RES_OK) {
        redirect_header(XOOPS_URL.'/modules/xoonips/index.php', 3, 'ERROR xnp_get_item_types ');
        break;
    } else {
        foreach ($tmp as $i) {
            $itemtypes[$i['item_type_id']] = $i;
        }
    }

    $item_details = array();
    foreach ($items as $i) {
        if (array_key_exists($i['item_type_id'], $itemtypes)) {
            $itemtype = $itemtypes[$i['item_type_id']];
            include_once XOOPS_ROOT_PATH.'/modules/'.$itemtype['viewphp'];
            eval('$body = '.$itemtype['name'].'GetListBlock( $i );');
            $item_details[] = array(
        'item_id' => $i['item_id'],
        'html' => $body,
      );
        }
    }

    return $item_details;
}

/**
 * Item's information registered in binder specified by $binder_id
 * is read from database and it returns as association_array.
 *
 * Structure of assciation_array
 *   array( 'html' => HTML of item's information, 'item_id' => number of item_id )
 *
 * @param binder_id "ID of the binder for processing"
 *
 * @return array => Execution result
 * @return false => Miss
 */
function xnpbinder_get_registered_items($binder_id)
{
    $items = array();
    $cri = array();
    $item_id = array();

    if (xnp_get_item_id_by_binder_id($_SESSION['XNPSID'], $binder_id, $cri, $item_id) != RES_OK) {
        return false;
    }

    if (xnp_get_items($_SESSION['XNPSID'], $item_id, $cri, $items) != RES_OK) {
        return false;
    }

    $itemtypes = array();
  // $itemtypes[<item_type_id>]=array( detail of an itemtype );
  $tmp = array();
    if (xnp_get_item_types($tmp) != RES_OK) {
        redirect_header(XOOPS_URL.'/modules/xoonips/index.php', 3, 'ERROR xnp_get_item_types ');
        break;
    } else {
        foreach ($tmp as $i) {
            $itemtypes[$i['item_type_id']] = $i;
        }
    }

    $item_details = array();
    foreach ($items as $i) {
        if (array_key_exists($i['item_type_id'], $itemtypes)) {
            $itemtype = $itemtypes[$i['item_type_id']];
            include_once XOOPS_ROOT_PATH.'/modules/'.$itemtype['viewphp'];
            eval('$body = '.$itemtype['name'].'GetListBlock( $i );');
            $item_details[] = array(
        'item_id' => $i['item_id'],
        'html' => $body,
      );
        }
    }

    return $item_details;
}

/**
 * return id of items that is registerd to ONLY private index.
 *
 * @param $item_ids array integer item id
 *
 * @return array integer
 */
function xnpbinder_extract_private_item_id($item_ids)
{
    return array_diff(xnpbinder_extract_item_id($item_ids, OL_PRIVATE), xnpbinder_extract_item_id($item_ids, OL_GROUP_ONLY), xnpbinder_extract_item_id($item_ids, OL_PUBLIC));
}

/**
 * return id of items that is registerd to public index.
 *
 * @param $item_ids array integer item id
 *
 * @return array integer
 */
function xnpbinder_extract_public_item_id($item_ids)
{
    return xnpbinder_extract_item_id($item_ids, OL_PUBLIC);
}

/**
 * return id of items that is registerd to  specified open_level index.
 *
 * @param $item_ids array integer item id
 * @param $open_level integer OL_PUBLIC, OL_GROUP_ONLY, OL_PRIVATE
 *
 * @return array integer
 */
function xnpbinder_extract_item_id($item_ids, $open_level)
{
    $ids = array();
    $index_item_link_handler = &xoonips_getormhandler('xoonips', 'index_item_link');
    $join = new XooNIpsJoinCriteria('xoonips_index', 'index_id', 'index_id');
    $criteria = new CriteriaCompo(new Criteria('open_level', intval($open_level)));
    $criteria->add(xnpbinder_criteria_where_in('item_id', $item_ids));
    $rows = &$index_item_link_handler->getObjects($criteria, false, 'distinct(item_id) as item_id', false, $join);
    foreach ($rows as $row) {
        $ids[] = $row->get('item_id');
    }

    return $ids;
}

/**
 * create 'WHERE IN' Criteria from INTEGER ARRAY.
 *
 * @param $column see Criteria
 * @param $vars array of integer
 * @param $operator see Criteria
 * @param $prefix see Criteria
 * @param $function see Criteria
 *
 * @return Criteria
 */
function xnpbinder_criteria_where_in($column, $vars, $operator = '=', $prefix = '', $function = '')
{
    $int_vars = array();
    foreach ($vars as $v) {
        $int_vars[] = intval($v);
    }

    return new Criteria($column, '('.implode(',', $int_vars).')', 'IN', $prefix, $function);
}

/**
 * check that binder has one or more items.
 *
 * @param array $child_item_ids integer item id of child item of binder
 *
 * @return bool true(binder has no items) or false(binder has some items)
 */
function xnpbinder_no_binder_item($child_item_ids)
{
    return !isset($child_item_ids) || !is_array($child_item_ids) || count($child_item_ids) == 0;
}

/**
 * check that binder has one or more items.
 *
 * @param array $child_item_ids integer item id of child item of binder
 * @param array $index_ids      integer index id that binder is registerd to
 *
 * @return bool true() or false(has no items)
 */
function xnpbinder_public_binder_has_not_public_item($child_item_ids, $index_ids)
{
    if (count($child_item_ids) == 0) {
        return false;
    }
    $item_handler = &xoonips_getormcompohandler('xoonips', 'item');
    $binder_handler = &xoonips_getormcompohandler('xnpbinder', 'item');
    $criteria = xnpbinder_criteria_where_in('item_id', $child_item_ids);
    $child_items = &$item_handler->getObjects($criteria);

    return $binder_handler->publicBinderHasNotPublicItems($child_items, $index_ids);
}

function xnpbinder_group_binder_has_private_item($child_item_ids, $index_ids)
{
    if (count($child_item_ids) == 0) {
        return false;
    }
    $item_handler = &xoonips_getormcompohandler('xoonips', 'item');
    $binder_handler = &xoonips_getormcompohandler('xnpbinder', 'item');
    $criteria = xnpbinder_criteria_where_in('item_id', $child_item_ids);
    $child_items = &$item_handler->getObjects($criteria);

    return $binder_handler->groupBinderHasPrivateItems($child_items, $index_ids);
}

function xnpbinder_get_add_to_index_id_form_data()
{
    $formdata = &xoonips_getutility('formdata');
    $add_to_index_id = $formdata->getValue('post', 'add_to_index_id', 'i', false);

    return array_unique(array_merge(is_null($add_to_index_id) ? array() : array($add_to_index_id), explode(',', $formdata->getValue('post', 'xoonipsCheckedXID', 's', false))));
}
