<?php
// $Revision: 1.47.2.1.2.28 $
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
if ( ! defined( 'XOOPS_ROOT_PATH' ) ) {
  exit();
}

$itemtype_path = dirname( dirname( __FILE__ ) );
$itemtype_dirname = basename( $itemtype_path );
$xoonips_path = dirname( $itemtype_path ).'/xoonips';

$langman =& xoonips_getutility( 'languagemanager' );
$langman->read( 'main.php', $itemtype_dirname );

/**
 * return an array ov available tool types.<br />
 * that structue is shown below.<br />
 * array( value of tool type for processing => value of tool type for displaying, ... )<br />
 * values of displaying are defined by _MD_XNPTOOL_TOOL_TYPE_SELECT.<br />
 * _MD_XNPTOOL_TOOL_TYPE_SELECT is tab(\t) separated value.<br />
 * _MD_XNPTOOL_TOOL_TYPE_SELECT has four values which correspond to values for displaying as below.<br />
 * matlab, mathematica, program, other
 * <br />
 * number of values for displaying != number of values for processing then return false.<br />
 * <br />
 */
function xnptool_get_type_array() {
  $key = array(
    'matlab',
    'mathematica',
    'program',
    'other',
  );
  $value = explode( "\t", _MD_XNPTOOL_TOOL_TYPE_SELECT );
  $ret = array();
  if ( count( $key ) != count( $value ) ) {
    return FALSE;
  }
  for ( $i = 0; $i < count( $key ); $i++ ) {
    $ret[$key[$i]] = $value[$i];
  }
  return $ret;
}

/** retrieve Detail Information that specified by item_id
  * return array(only keys, no values) if item_id is wrong.
  * @return array as result
  * @return false if failed
  */
function xnptoolGetDetailInformation( $item_id ) {
  global $xoopsDB;

  $item = array();

  $result = $xoopsDB->query( 'select * from '.$xoopsDB->prefix( 'xnptool_item_detail' )." where tool_id=$item_id" );
  $item = $xoopsDB->fetchArray( $result );

  $tool_types = xnptool_get_type_array();
  return array( 'tool_type' => array(
    'value' => $item['tool_type'],
    'select' => xnptool_get_type_array(),
    'display_value' => $tool_types[$item['tool_type']],
  ), 'readme' => array(
    'value' => $item['readme'],
  ), 'rights' => array(
    'value' => $item['rights'],
  ), 'use_cc' => array(
    'value' => $item['use_cc'],
  ), 'cc_commercial_use' => array(
    'value' => $item['cc_commercial_use'],
  ), 'cc_modification' => array(
    'value' => $item['cc_modification'],
  ), 'attachment_dl_limit' => array(
    'value' => $item['attachment_dl_limit'],
  ), 'attachment_dl_notify' => array(
    'value' => $item['attachment_dl_notify'],
  ), );
  return false;
}

function xnptoolGetListBlock( $item_basic ) {
  // get uid
  global $xoopsUser;
  $myuid = is_object( $xoopsUser ) ? $xoopsUser->getVar( 'uid', 'n' ) : UID_GUEST;

  // set to template
  global $xoopsTpl;

  $tpl = new XoopsTpl();
  // copy variables in $xoopsTpl to $tpl
  $tpl->assign( $xoopsTpl->get_template_vars() );

  $xnptool_handler =& xoonips_getormcompohandler( 'xnptool', 'item' );
  $tpl->assign( 'xoonips_item', $xnptool_handler->getTemplateVar( XOONIPS_TEMPLATE_TYPE_ITEM_LIST, $item_basic['item_id'], $myuid ) );

  // return as HTML
  return $tpl->fetch( 'db:xnptool_list_block.html' );
}

function xnptoolGetPrinterFriendlyListBlock( $item_basic ) {
  return xnptoolGetListBlock( $item_basic );
}

function xnptoolGetDetailBlock( $item_id ) {
  // get uid
  global $xoopsUser;
  $myuid = is_object( $xoopsUser ) ? $xoopsUser->getVar( 'uid', 'n' ) : UID_GUEST;

  global $xoopsTpl;

  // get DetailInformation
  $detail_handler =& xoonips_getormhandler( 'xnptool', 'item_detail' );
  $detail_orm =& $detail_handler->get( $item_id );
  if ( ! $detail_orm ) {
    return '';
  }

  // set to template
  $tpl = new XoopsTpl();
  // copy variables in $xoopsTpl to $tpl
  $tpl->assign( $xoopsTpl->get_template_vars() );

  $tpl->assign( 'editable', xnp_get_item_permission( $_SESSION['XNPSID'], $item_id, OP_MODIFY ) );
  $tpl->assign( 'basic', xnpGetBasicInformationDetailBlock( $item_id ) );
  $tpl->assign( 'index', xnpGetIndexDetailBlock( $item_id ) );
  $tpl->assign( 'preview', xnpGetPreviewDetailBlock( $item_id ) );
  $tpl->assign( 'tool_data', xnpGetAttachmentDetailBlock( $item_id, 'tool_data' ) );
  $tpl->assign( 'readme', xnpGetTextFileDetailBlock( $item_id, 'readme', $detail_orm->getVar( 'readme', 'n' ) ) );
  $tpl->assign( 'rights', xnpGetRightsDetailBlock( $item_id, $detail_orm->getVar( 'use_cc', 'n' ), $detail_orm->getVar( 'rights', 'n' ), $detail_orm->getVar( 'cc_commercial_use', 'n' ), $detail_orm->getVar( 'cc_modification', 'n' ) ) );

  $xnptool_handler =& xoonips_getormcompohandler( 'xnptool', 'item' );
  $tpl->assign( 'xoonips_item', $xnptool_handler->getTemplateVar( XOONIPS_TEMPLATE_TYPE_ITEM_DETAIL, $item_id, $myuid ) );

  // return as HTML
  return $tpl->fetch( 'db:xnptool_detail_block.html' );
}

/**
 * @param item_id item id
 * @param download_file_id if download_file_id != false, let browser download this file automatically.
 * @return html: require confirmation before downloading
 */
function xnptoolGetDownloadConfirmationBlock( $item_id, $download_file_id ) {
  $detail = xnptoolGetDetailInformation( $item_id );
  return xnpGetDownloadConfirmationBlock( $item_id, $download_file_id, $detail['attachment_dl_notify']['value'], true, $detail['use_cc']['value'], $detail['rights']['value'] );
}

/**
 * @return true: require confirmation before downloading
 */
function xnptoolGetDownloadConfirmationRequired( $item_id ) {
  return true;
}

function xnptoolGetPrinterFriendlyDetailBlock( $item_id ) {
  // get uid
  global $xoopsUser;
  $myuid = is_object( $xoopsUser ) ? $xoopsUser->getVar( 'uid', 'n' ) : UID_GUEST;

  global $xoopsTpl;

  // get DetailInformation
  $detail_handler =& xoonips_getormhandler( 'xnptool', 'item_detail' );
  $detail_orm =& $detail_handler->get( $item_id );
  if ( ! $detail_orm ) {
    return '';
  }

  // set to template
  $tpl = new XoopsTpl();
  // copy variables in $xoopsTpl to $tpl
  $tpl->assign( $xoopsTpl->get_template_vars() );

  $tpl->assign( 'editable', xnp_get_item_permission( $_SESSION['XNPSID'], $item_id, OP_MODIFY ) );
  $tpl->assign( 'basic', xnpGetBasicInformationPrinterFriendlyBlock( $item_id ) );
  $tpl->assign( 'index', xnpGetIndexPrinterFriendlyBlock( $item_id ) );
  $tpl->assign( 'preview', xnpGetPreviewPrinterFriendlyBlock( $item_id ) );
  $tpl->assign( 'tool_data', xnpGetAttachmentPrinterFriendlyBlock( $item_id, 'tool_data' ) );
  $tpl->assign( 'readme', xnpGetTextFilePrinterFriendlyBlock( $item_id, 'readme', $detail_orm->getVar( 'readme', 'n' ) ) );
  $tpl->assign( 'rights', xnpGetRightsPrinterFriendlyBlock( $item_id, $detail_orm->getVar( 'use_cc', 'n' ), $detail_orm->getVar( 'rights', 'n' ), $detail_orm->getVar( 'cc_commercial_use', 'n' ), $detail_orm->getVar( 'cc_modification', 'n' ) ) );

  $xnptool_handler =& xoonips_getormcompohandler( 'xnptool', 'item' );
  $tpl->assign( 'xoonips_item', $xnptool_handler->getTemplateVar( XOONIPS_TEMPLATE_TYPE_ITEM_DETAIL, $item_id, $myuid ) );

  // return as HTML
  return $tpl->fetch( 'db:xnptool_detail_block.html' );
}

function xnptoolGetRegisterBlock() {
  $formdata =& xoonips_getutility( 'formdata' );
  // retrieve detail information
  $detail = array();
  $tool_types = xnptool_get_type_array();
  $post_id = $formdata->getValue( 'get', 'post_id', 's', false );
  if ( is_null( $post_id ) ) {
    $tool_type = false;
  } else {
    $tool_type = $formdata->getValue( 'post', 'tool_type', 's', false );
  }
  if ( $tool_type == false ) {
    list( $tool_type ) = each( $tool_types );
  }
  $detail['tool_type'] = array(
    'value' => $tool_type,
    'display_value' => $tool_types[$tool_type],
    'select' => $tool_types,
  );

  // retrieve blocks of BasicInformation / Preview / Readme / License / index
  $basic = xnpGetBasicInformationRegisterBlock();
  $preview = xnpGetPreviewRegisterBlock();
  $index = xnpGetIndexRegisterBlock();
  $attachment = xnpGetAttachmentRegisterBlock( 'tool_data' );
  $readme = xnpGetTextFileRegisterBlock( 'readme' );
  $rights = xnpGetRightsRegisterBlock();

  // assign to template
  global $xoopsTpl;

  $tpl = new XoopsTpl();
  // variables assigned to xoopsTpl are copied to tpl
  $tpl->assign( $xoopsTpl->get_template_vars() );

  $tpl->assign( 'basic', $basic );
  $tpl->assign( 'preview', $preview );
  $tpl->assign( 'index', $index );
  $tpl->assign( 'attachment', $attachment );
  $tpl->assign( 'attachment_dl_limit', xnpGetDownloadLimitationOptionRegisterBlock( 'xnptool' ) );
  $tpl->assign( 'attachment_dl_notify', xnpGetDownloadNotificationOptionRegisterBlock( 'xnptool' ) );
  $tpl->assign( 'detail', $detail );
  $tpl->assign( 'readme', $readme );
  $tpl->assign( 'rights', $rights );
  if ( isset( $tool_date ) ) {
    $tpl->assign( 'gmtime', mktime( 0, 0, 0, $tool_date['Date_Month'], $tool_date['Date_Day'], $tool_date['Date_Year'] ) );
  } else {
    $tpl->assign( 'gmtime', time() );
  }
  $tpl->assign( 'xnptool_developer', xoonips_get_multiple_field_template_vars( xoonips_get_orm_from_post( 'xnptool', 'developer' ), 'xnptool', 'developer' ) );

  // return HTML content
  return $tpl->fetch( 'db:xnptool_register_block.html' );
}

function xnptoolGetEditBlock( $item_id ) {
  $formdata =& xoonips_getutility( 'formdata' );

  // retrieve detail information
  $detail = xnptoolGetDetailInformation( $item_id );
  $tool_types = xnptool_get_type_array();
  $post_id = $formdata->getValue( 'get', 'post_id', 's', false );
  if ( ! is_null( $post_id ) ) {
    $tool_type = $formdata->getValue( 'post', 'tool_type', 's', false );
    if ( $tool_type == false ) {
      list( $tool_type ) = each( $tool_types );
    }
    $detail['tool_type'] = array(
      'value' => $tool_type,
      'display_value' => $tool_types[$tool_type],
      'select' => $tool_types,
    );
  }

  // retrieve blocks of BasicInformation / Preview / index block
  $basic = xnpGetBasicInformationEditBlock( $item_id );

  $preview = xnpGetPreviewEditBlock( $item_id );
  $index = xnpGetIndexEditBlock( $item_id );
  $attachment = xnpGetAttachmentEditBlock( $item_id, 'tool_data' );

  $readme = xnpGetTextFileEditBlock( $item_id, 'readme', $detail['readme']['value'] );
  $rights = xnpGetRightsEditBlock( $item_id, $detail['use_cc']['value'], $detail['rights']['value'], $detail['cc_commercial_use']['value'], $detail['cc_modification']['value'] );

  $attachment['name'] = _MD_XNPTOOL_TOOL_FILE;

  // assign to template
  global $xoopsTpl;

  $tpl = new XoopsTpl();
  // variables assigned to xoopsTpl are copied to tpl
  $tpl->assign( $xoopsTpl->get_template_vars() );

  $tpl->assign( 'basic', $basic );
  $tpl->assign( 'preview', $preview );
  $tpl->assign( 'index', $index );
  $tpl->assign( 'attachment', $attachment );
  $tpl->assign( 'attachment_dl_limit', xnpGetDownloadLimitationOptionEditBlock( 'xnptool', xnptoolGetAttachmentDownloadLimitOption( $item_id ) ) );
  $tpl->assign( 'attachment_dl_notify', xnpGetDownloadNotificationOptionEditBlock( 'xnptool', xnptoolGetAttachmentDownloadNotifyOption( $item_id ) ) );
  $tpl->assign( 'detail', $detail );
  $tpl->assign( 'readme', $readme );
  $tpl->assign( 'rights', $rights );

  if ( ! $formdata->getValue( 'get', 'post_id', 's', false ) ) {
    $detail_handler =& xoonips_getormhandler( 'xnptool', 'item_detail' );
    $detail_orm =& $detail_handler->get( $item_id );
    $tpl->assign( 'xnptool_developer', xoonips_get_multiple_field_template_vars( $detail_orm->getDevelopers(), 'xnptool', 'developer' ) );
  } else {
    $tpl->assign( 'xnptool_developer', xoonips_get_multiple_field_template_vars( xoonips_get_orm_from_post( 'xnptool', 'developer' ), 'xnptool', 'developer' ) );
  }

  // return HTML content
  return $tpl->fetch( 'db:xnptool_register_block.html' );
}


function xnptoolGetConfirmBlock( $item_id ) {
  $textutil =& xoonips_getutility( 'text' );
  $formdata =& xoonips_getutility( 'formdata' );
  $developer_handler =& xoonips_getormhandler( 'xnptool', 'developer' );
  $developer_objs =& $formdata->getObjectArray( 'post', $developer_handler->getTableName(), $developer_handler, false );

  // retrive detail information
  $detail = array();
  $tool_type = $formdata->getValue( 'post', 'tool_type', 's', false );
  if ( $tool_type !== false ) {
    $tool_types = xnptool_get_type_array();
    $detail['tool_type'] = array(
      'value' => $textutil->html_special_chars( $tool_type ),
      'display_value' => $textutil->html_special_chars( $tool_types[$tool_type] ),
    );
  }
  if ( isset( $tool_date ) ) {
    $detail['tool_date'] = array(
      'value' => mktime( 0, 0, 0, $tool_date['Date_Month'], $tool_date['Date_Day'], $tool_date['Date_Year'] ),
    );
  } else {
    $detail['tool_date'] = array(
      'value' => time(),
    );
  }

  // retrieve blocks of BasicInformation / Preview / index block
  $basic = xnpGetBasicInformationConfirmBlock( $item_id );
  xnpConfirmHtml( $detail, 'xnptool_item_detail', array_keys( $detail ), _CHARSET );
  $preview = xnpGetPreviewConfirmBlock( $item_id );
  $attachment = xnpGetAttachmentConfirmBlock( $item_id, 'tool_data' );
  $index = xnpGetIndexConfirmBlock( $item_id );
  $lengths = xnpGetColumnLengths( 'xnptool_item_detail' );
  $readme = xnpGetTextFileConfirmBlock( $item_id, 'readme', $lengths['readme'] );
  $rights = xnpGetRightsConfirmBlock( $item_id, $lengths['rights'] );

  if ( xnpHasWithout( $basic ) || xnpHasWithout( $detail ) || xnpHasWithout( $preview ) || xnpHasWithout( $attachment ) || xnpHasWithout( $readme ) || xnpHasWithout( $rights ) || xoonips_is_multiple_field_too_long( $developer_objs, 'xnptool', 'developer' ) ) {
    global $system_message;
    $system_message = $system_message."\n<br /><font color='#ff0000'>"._MD_XOONIPS_ITEM_WARNING_FIELD_TRIM.'</font><br />';
  }

  // assign to template
  global $xoopsTpl;

  $tpl = new XoopsTpl();
  // variables assigned to xoopsTpl are copied to tpl
  $tpl->assign( $xoopsTpl->get_template_vars() );

  $tpl->assign( 'basic', $basic );
  $tpl->assign( 'preview', $preview );
  $tpl->assign( 'index', $index );
  $tpl->assign( 'attachment', $attachment );
  $tpl->assign( 'attachment_dl_limit', xnpGetDownloadLimitationOptionConfirmBlock( 'xnptool' ) );
  $tpl->assign( 'attachment_dl_notify', xnpGetDownloadNotificationOptionConfirmBlock( 'xnptool' ) );
  $tpl->assign( 'detail', $detail );
  $tpl->assign( 'readme', $readme );
  $tpl->assign( 'rights', $rights );
  if ( isset( $tool_date ) ) {
    $tpl->assign( 'tool_date', $tool_date );
    if ( $tool_date['Date_Year'] ) {
      $tpl->assign( 'system_message', $tpl->get_template_vars( 'system_message' ).'<br/><font color=\'#ff0000\'>'._MD_XOONIPS_ITEM_TITLE_REQUIRED.'</font>' );
    }
  }
  $tpl->assign( 'xnptool_developer', xoonips_get_multiple_field_template_vars( $developer_objs, 'xnptool', 'developer' ) );

  // return HTML content
  return $tpl->fetch( 'db:xnptool_confirm_block.html' );
}

function xnptoolInsertItem( &$item_id ) {
  global $xoopsDB;
  $formdata =& xoonips_getutility( 'formdata' );

  $xnpsid = $_SESSION['XNPSID'];

  // retister BasicInformation, Index and Attachment
  $item_id = 0;
  $result = xnpInsertBasicInformation( $item_id );
  if ( $result ) {
    $result = xnpUpdateIndex( $item_id );
    if ( $result ) {
      $result = xnpUpdatePreview( $item_id );
      if ( $result ) {
        $result = xnpUpdateAttachment( $item_id, 'tool_data' );
        if ( $result ) {
        }
      }
    }
    if ( ! $result ) {
      xnpDeleteBasicInformation( $xnpsid, $item_id );
    }
  }
  if ( ! $result ) {
    return false;
  }

  // register detail information
  list( $rights, $use_cc, $cc_commercial_use, $cc_modification ) = xnpGetRights();
  // trim strings
  $ar = array(
    'tool_type' => $formdata->getValue( 'post', 'tool_type', 's', false ),
    'readme' => xnpGetTextFile( 'readme' ),
    'rights' => $rights,
  );
  xnpTrimColumn( $ar, 'xnptool_item_detail', array_keys( $ar ), _CHARSET );

  $keys = implode( ',', array( 'tool_type', 'readme', 'rights', 'use_cc', 'cc_commercial_use', 'cc_modification', 'attachment_dl_limit', 'attachment_dl_notify', ) );
  $attachment_dl_limit = $formdata->getValue( 'post', 'attachment_dl_limit', 'i', false );
  $attachment_dl_notify = $formdata->getValue( 'post', 'attachment_dl_notify', 'i', false );
  $vals = implode( '\',\'', array( addslashes( $ar['tool_type'] ), addslashes( $ar['readme'] ), addslashes( $ar['rights'] ), $use_cc, $cc_commercial_use, $cc_modification, $attachment_dl_limit, $attachment_dl_limit ? $attachment_dl_notify : 0, ) );

  // insert DetailInformation
  $sql = 'insert into '.$xoopsDB->prefix( 'xnptool_item_detail' )." ( tool_id, $keys ) values ( $item_id, '$vals' ) ";
  $result = $xoopsDB->queryF( $sql );
  if ( $result == false ) {
    echo 'cannot insert item_detail: '.$xoopsDB->error();
    return false;
  }

  // insert developer
  $developer_handler =& xoonips_getormhandler( 'xnptool', 'developer' );
  $developer_objs =& $formdata->getObjectArray( 'post', $developer_handler->getTableName(), $developer_handler, false );
  if ( ! $developer_handler->updateAllObjectsByForeignKey( 'tool_id', $item_id, $developer_objs ) ) {
    return false;
  }
  return true;
}

function xnptoolUpdateItem( $tool_id ) {
  global $xoopsDB;
  $formdata =& xoonips_getutility( 'formdata' );

  $xnpsid = $_SESSION['XNPSID'];

  // modify BasicInformation, Index, Preview and Attachment.
  $result = xnpUpdateBasicInformation( $tool_id );
  if ( $result ) {
    $result = xnpUpdateIndex( $tool_id );
    if ( $result ) {
      $result = xnpUpdatePreview( $tool_id );
      if ( $result ) {
        $result = xnpUpdateAttachment( $tool_id, 'tool_data' );
        if ( $result ) {
          $result = xnp_insert_change_log( $xnpsid, $tool_id, $formdata->getValue( 'post', 'change_log', 's', false ) );
          $result = ! $result;
          if ( ! $result ) {
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
  if ( ! $result ) {
    return false;
  }

  list( $rights, $use_cc, $cc_commercial_use, $cc_modification ) = xnpGetRights();
  // trim string
  $ar = array(
    'tool_type' => $formdata->getValue( 'post', 'tool_type', 's', false ),
    'readme' => xnpGetTextFile( 'readme' ),
    'rights' => $rights,
  );
  xnpTrimColumn( $ar, 'xnptool_item_detail', array_keys( $ar ), _CHARSET );

  $attachment_dl_limit = $formdata->getValue( 'post', 'attachment_dl_limit', 'i', false );
  $attachment_dl_notify = $formdata->getValue( 'post', 'attachment_dl_notify', 'i', false );
  $keyval = array(
    'tool_type'.'=\''.addslashes( $ar['tool_type'] ).'\'',
    'readme'.'=\''.addslashes( $ar['readme'] ).'\'',
    'rights'.'=\''.addslashes( $ar['rights'] ).'\'',
    'use_cc'.'=\''.$use_cc.'\'',
    'cc_commercial_use'.'=\''.$cc_commercial_use.'\'',
    'cc_modification'.'=\''.$cc_modification.'\'',
    'attachment_dl_limit'.'=\''.$attachment_dl_limit.'\'',
    'attachment_dl_notify'.'=\''.( $attachment_dl_limit ? $attachment_dl_notify : 0 ).'\'',
  );

  // modify detail information
  $sql = 'update '.$xoopsDB->prefix( 'xnptool_item_detail' ).' set '.implode( ', ', $keyval )." where tool_id=$tool_id";
  $result = $xoopsDB->queryF( $sql );
  if ( $result == false ) {
    echo 'cannot update item_detail';
    echo "\n$sql";
    return false;
  }

  // insert/update developer
  $developer_handler =& xoonips_getormhandler( 'xnptool', 'developer' );
  $developer_objs =& $formdata->getObjectArray( 'post', $developer_handler->getTableName(), $developer_handler, false );
  if ( ! $developer_handler->updateAllObjectsByForeignKey( 'tool_id', $tool_id, $developer_objs ) ) {
    return false;
  }

  return true;
}

function xnptoolGetSearchBlock( $item_id ) {
  // todo: details to be defnied
}

function xnptoolCheckRegisterParameters( &$msg ) {
  $formdata =& xoonips_getutility( 'formdata' );
  $xnpsid = $_SESSION['XNPSID'];
  $result = true;
  $developer = xoonips_get_multi_field_array_from_post( 'xnptool', 'developer' );
  $tool_data = $formdata->getFile( 'tool_data', false );
  $tool_dataFileID = $formdata->getValue( 'post', 'tool_dataFileID', 'i', false );
  $xoonipsCheckedXID = $formdata->getValue( 'post', 'xoonipsCheckedXID', 's', false );

  if ( empty( $developer ) ) {
    // developer is not filled
    $msg = $msg.'<br/><font color=\'#ff0000\'>'._MD_XNPTOOL_DEVELOPER_REQUIRED.'</font>';
    $result = false;
  }
  if ( ( empty( $tool_data ) || $tool_data['name'] == '' ) && $tool_dataFileID == '' ) {
    // tool_data is not filled
    $msg = $msg.'<br/><font color=\'#ff0000\'>'._MD_XNPTOOL_TOOL_FILE_REQUIRED.'</font>';
    $result = false;
  }
  // notify that license statement is required when register into public indexes.
  $xids = explode( ',', $xoonipsCheckedXID );
  $indexes = array();
  if ( $xids[0] != $xoonipsCheckedXID ) {
    foreach ( $xids as $i ) {
      $index = array();
      if ( xnp_get_index( $xnpsid, $i, $index ) == RES_OK ) {
        $indexes[] = $index;
      } else {
        $msg = $msg.'<br/><font color=\'#ff0000\'>'.xnp_get_last_error_string().'</font>';
        $result = false;
        break;
      }
    }
  }
  if ( count( $indexes ) > 0 ) {
    foreach ( $indexes as $i ) {
      if ( $i['open_level'] <= OL_GROUP_ONLY ) {
        $readmeEncText = $formdata->getValue( 'post', 'readmeEncText', 's', false );
        $rightsEncText = $formdata->getValue( 'post', 'rightsEncText', 's', false );
        $rightsUseCC = $formdata->getValue( 'post', 'rightsUseCC', 'i', false );
        if ( $readmeEncText == '' ) {
          // readme is not filled
          $msg = $msg.'<br/><font color=\'#ff0000\'>'._MD_XNPTOOL_README_REQUIRED.'</font>';
          $result = false;
        }
        if ( $rightsEncText == '' && $rightsUseCC == '0' ) {
          // license is not filled
          $msg = $msg.'<br/><font color=\'#ff0000\'>'._MD_XNPTOOL_RIGHTS_REQUIRED.'</font>';
          $result = false;
        }
        break;
      }
    }
  }
  return $result;
}

function xnptoolCheckEditParameters( &$msg ) {
  return xnptoolCheckRegisterParameters( $msg );
}

function xnptoolGetMetaInformation( $item_id ) {
  $ret = array();
  $developer_array = array();

  $basic = xnpGetBasicInformationArray( $item_id );
  $detail = xnptoolGetDetailInformation( $item_id );

  if ( ! empty( $basic ) ) {
    $ret[_MD_XOONIPS_ITEM_TITLE_LABEL] = implode( "\n", $basic['titles'] );
    $ret[_MD_XOONIPS_ITEM_CONTRIBUTOR_LABEL] = $basic['contributor'];
    $ret[_MD_XOONIPS_ITEM_KEYWORDS_LABEL] = implode( "\n", $basic['keywords'] );
    $ret[_MD_XOONIPS_ITEM_DESCRIPTION_LABEL] = $basic['description'];
    $ret[_MD_XOONIPS_ITEM_DOI_LABEL] = $basic['doi'];
    $ret[_MD_XOONIPS_ITEM_LAST_UPDATE_DATE_LABEL] = $basic['last_update_date'];
    $ret[_MD_XOONIPS_ITEM_CREATION_DATE_LABEL] = $basic['creation_date'];
    $ret[_MD_XNPTOOL_DATE_LABEL] = xnpDate( $basic['publication_year'], $basic['publication_month'], $basic['publication_mday'] );
  }
  if ( ! empty( $detail ) ) {
    $ret[_MD_XNPTOOL_TOOL_TYPE_LABEL] = $detail['tool_type']['display_value'];
    $ret[_MD_XOONIPS_ITEM_README_LABEL] = $detail['readme']['value'];
    $ret[_MD_XOONIPS_ITEM_RIGHTS_LABEL] = $detail['rights']['value'];
  }

  $xnptool_handler =& xoonips_getormcompohandler( 'xnptool', 'item' );
  $xnptool =& $xnptool_handler->get( $item_id );
  foreach ( $xnptool->getVar( 'developer' ) as $developer ) {
    $developer_array[] = $developer->getVar( 'developer', 'n' );
  }
  $ret[_MD_XNPTOOL_DEVELOPER_LABEL] = implode( "\n", $developer_array );

  return $ret;
}

function xnptoolGetAdvancedSearchBlock( &$search_var ) {

  $basic = xnpGetBasicInformationAdvancedSearchBlock( 'xnptool', $search_var );

  $search_var[] = 'xnptool_tool_type';
  $search_var[] = 'xnptool_developer';
  $search_var[] = 'xnptool_caption';
  $search_var[] = 'xnptool_tool_file';

  // assign to template
  global $xoopsTpl;

  $tpl = new XoopsTpl();
  // variables assigned to xoopsTpl are copied to tpl
  $tpl->assign( $xoopsTpl->get_template_vars() );
  $tpl->assign( 'basic', $basic );
  $tpl->assign( 'module_name', 'xnptool' );
  $tool_type = xnptool_get_type_array();
  $tpl->assign( 'tool_type_option', $tool_type );
  $tpl->assign( 'module_display_name', xnpGetItemTypeDisplayNameByDirname( basename( dirname( dirname( __FILE__ ) ) ), 's' ) );

  // return HTML content
  return $tpl->fetch( 'db:xnptool_search_block.html' );
}

function xnptoolGetAdvancedSearchQuery( &$where, &$join ) {
  global $xoopsDB;
  $formdata =& xoonips_getutility( 'formdata' );
  $tool_table = $xoopsDB->prefix( 'xnptool_item_detail' );
  $tool_developer_table = $xoopsDB->prefix( 'xnptool_developer' );
  $file_table = $xoopsDB->prefix( 'xoonips_file' );
  $search_text_table = $xoopsDB->prefix( 'xoonips_search_text' );

  $wheres = array();
  $joins = array();
  $w = xnpGetBasicInformationAdvancedSearchQuery( 'xnptool' );
  if ( $w ) {
    $wheres[] = $w;
  }
  $w = xnpGetKeywordQuery( $tool_table.'.tool_type', 'xnptool_tool_type' );
  if ( $w ) {
    $wheres[] = $w;
  }
  $w = xnpGetKeywordQuery( $tool_developer_table.'.developer', 'xnptool_developer' );
  if ( $w ) {
    $wheres[] = $w;
  }
  $w = xnpGetKeywordQuery( $file_table.'.caption', 'xnptool_caption' );
  if ( $w ) {
    $wheres[] = $w;
    $wheres[] = " $file_table.file_type_id = 1";
  }
  $xnptool_tool_file = $formdata->getValue( 'post', 'xnptool_tool_file', 's', false );
  if ( ! empty( $xnptool_tool_file ) ) {
    $search_text_table = $xoopsDB->prefix('xoonips_search_text');
    $file_table = $xoopsDB->prefix('xoonips_file');
    $searchutil =& xoonips_getutility('search');
    $fulltext_query = $xnptool_tool_file;
    $fulltext_encoding = mb_detect_encoding($fulltext_query);
    $fulltext_criteria = new CriteriaCompo($searchutil->getFulltextSearchCriteria('search_text', $fulltext_query, $fulltext_encoding, $search_text_table));
    $fulltext_criteria->add(new Criteria('is_deleted', 0, '=', $file_table));
    $wheres[] = $fulltext_criteria->render();
  }
  $where = implode( ' AND ', $wheres );
  $join = " INNER JOIN $tool_developer_table ON ".$tool_developer_table.'.tool_id  = '.$xoopsDB->prefix( 'xoonips_item_basic' ).'.item_id ';
}

function xnptoolGetDetailInformationQuickSearchQuery( &$wheres, &$join, $keywords ) {
  global $xoopsDB;
  $tool_table = $xoopsDB->prefix( 'xnptool_item_detail' );
  $tool_developer_table = $xoopsDB->prefix( 'xnptool_developer' );
  $file_table = $xoopsDB->prefix( 'xoonips_file' );

  $colnames = array(
    "$tool_developer_table.developer",
    "$file_table.caption",
  );
  $wheres = xnpGetKeywordsQueries( $colnames, $keywords );
  $join = " INNER JOIN $tool_developer_table ON ".$tool_developer_table.'.tool_id  = '.$xoopsDB->prefix( 'xoonips_item_basic' ).'.item_id ';

  return true;
}

function xnptoolGetDetailInformationTotalSize( $iids ) {
  return xnpGetTotalFileSize( $iids );
}

function xnptoolGetLicenseRequired( $item_id ) {
  global $xoopsDB;

  // retrieve detail information
  $result = $xoopsDB->query( 'select * from '.$xoopsDB->prefix( 'xnptool_item_detail' )." where tool_id=$item_id" );
  if ( ! $result ) {
    return NULL;
  }
  $detail = $xoopsDB->fetchArray( $result );
  return isset( $detail['rights'] ) && $detail['rights'] != '';
}

function xnptoolGetLicenseStatement( $item_id ) {
  global $xoopsDB;

  // retrieve detail information
  $result = $xoopsDB->query( 'select * from '.$xoopsDB->prefix( 'xnptool_item_detail' )." where tool_id=$item_id" );
  if ( ! $result ) {
    return NULL;
  }
  $detail = $xoopsDB->fetchArray( $result );
  return array( isset( $detail['rights'] ) ? $detail['rights'] : '', $detail['use_cc'] );;
}

/**
 * create XML for exporting detail information
 * see xnpExportItem for detail
 * @see xnpExportItem
 *
 * @param string $export_path folder that export file is written to.
 * @param resource $fhdl file handle that items are exported to.
 * @param int $item_id item id that is exported
 * @param bool $attachment true if attachment files are exported, else false.
 * @return bool false if failure
 */
function xnptoolExportItem( $export_path, $fhdl, $item_id, $attachment ) {
  // get detail information
  if ( ! $fhdl ) {
    return false;
  }

  $handler =& xoonips_getormhandler( 'xnptool', 'item_detail' );
  $detail =& $handler->get( $item_id );
  if ( ! $detail ) {
    return false;
  }

  $developers = '';
  foreach ( $detail->getDevelopers() as $developer ) {
    $developers .= '<developer>'.$developer->getVar( 'developer', 's' ).'</developer>';
  }

  $xml = array();
  $xml[] = sprintf( '<detail id="%u" version="1.03">', $item_id );
  $xml[] = sprintf( '<tool_type>%s</tool_type>', $detail->getVar( 'tool_type', 's' ) );
  $xml[] = sprintf( '<developers>%s</developers>', $developers );
  $xml[] = sprintf( '<readme>%s</readme>', $detail->getVar( 'readme', 's' ) );
  $xml[] = sprintf( '<rights>%s</rights>', $detail->getVar( 'rights', 's' ) );
  $xml[] = sprintf( '<use_cc>%u</use_cc>', $detail->getVar( 'use_cc', 's' ) );
  $xml[] = sprintf( '<cc_commercial_use>%s</cc_commercial_use>', $detail->getVar( 'cc_commercial_use', 's' ) );
  $xml[] = sprintf( '<cc_modification>%s</cc_modification>', $detail->getVar( 'cc_modification', 's' ) );
  $xml[] = sprintf( '<attachment_dl_limit>%u</attachment_dl_limit>', $detail->getVar( 'attachment_dl_limit', 's' ) );
  $xml[] = sprintf( '<attachment_dl_notify>%u</attachment_dl_notify>', $detail->getVar( 'attachment_dl_notify', 's' ) );
  if ( ! fwrite( $fhdl, implode( "\n", $xml )."\n" ) ) {
    return false;
  }
  if ( ! ( $attachment ? xnpExportFile( $export_path, $fhdl, $item_id ) : true ) ) {
    return false;
  }
  if ( ! fwrite( $fhdl, "</detail>\n" ) ) {
    return false;
  }

  return true;
}

function xnptoolGetModifiedFields( $item_id ) {
  $ret = array();
  $formdata =& xoonips_getutility( 'formdata' );

  $basic = xnpGetBasicInformationArray( $item_id );
  if ( $basic ) {
    $publicationDateYear = $formdata->getValue( 'post', 'publicationDateYear', 'i', false );
    $publicationDateMonth = $formdata->getValue( 'post', 'publicationDateMonth', 'i', false );
    $publicationDateDay = $formdata->getValue( 'post', 'publicationDateDay', 'i', false );
    if ( intval( $basic['publication_month'] ) != intval( $publicationDateMonth ) || intval( $basic['publication_mday'] ) != intval( $publicationDateDay ) || intval( $basic['publication_year'] ) != intval( $publicationDateYear ) ) {
      array_push( $ret, _MD_XNPTOOL_DATE_LABEL );
    }
  }
  $detail = xnptoolGetDetailInformation( $item_id );
  if ( $detail ) {
    foreach ( array( 'tool_type' => _MD_XNPTOOL_TOOL_TYPE ) as $k => $v ) {
      $tmp = $formdata->getValue( 'post', $k, 's', false );
      if ( ! array_key_exists( $k, $detail ) || $tmp === NULL ) {
        continue;
      }
      if ( $detail[$k]['value'] != $tmp ) {
        array_push( $ret, $v );
      }
    }
    // is readme modified ?
    foreach ( array( 'readme' => _MD_XOONIPS_ITEM_README_LABEL ) as $k => $v ) {
      $tmp = $formdata->getValue( 'post', "${k}EncText", 's', false );
      if ( ! array_key_exists( $k, $detail ) || $tmp === NULL ) {
        continue;
      }
      if ( $detail[$k]['value'] != $tmp ) {
        array_push( $ret, $v );
      }
    }

    // is rights modified ?
    $rightsUseCC = $formdata->getValue( 'post', 'rightsUseCC', 'i', false );
    $rightsEncText = $formdata->getValue( 'post', 'rightsEncText', 's', false );
    if ( $rightsUseCC !== NULL ) {
      if ( $rightsUseCC != $detail['use_cc']['value'] ) {
        array_push( $ret, _MD_XOONIPS_ITEM_RIGHTS_LABEL );
      } else if ( $rightsUseCC == 0 ) {
        if ( array_key_exists( 'rights', $detail ) && $rightsEncText != NULL && $rightsEncText != $detail['rights']['value'] ) {
          array_push( $ret, _MD_XOONIPS_ITEM_RIGHTS_LABEL );
        }
      } else if ( $rightsUseCC == 1 ) {
        foreach ( array( 'rightsCCCommercialUse' => 'cc_commercial_use', 'rightsCCModification' => 'cc_modification' ) as $k => $v ) {
          $tmp = $formdata->getValue( 'post', $k, 'i', false );
          if ( ! array_key_exists( $v, $detail ) || $tmp === NULL ) {
            continue;
          }
          if ( $tmp != $detail[$v]['value'] ) {
            array_push( $ret, _MD_XOONIPS_ITEM_RIGHTS_LABEL );
            break;
          }
        }
      }
    }

    // is modified data files ?
    if ( xnpIsAttachmentModified( 'tool_data', $item_id ) ) {
      array_push( $ret, _MD_XNPTOOL_TOOL_FILE );
    }

    $developer_handler =& xoonips_getormhandler( 'xnptool', 'developer' );
    $developer_objs =& $formdata->getObjectArray( 'post', $developer_handler->getTableName(), $developer_handler, false );
    $detail_handler =& xoonips_getormhandler( 'xnptool', 'item_detail' );
    $detail_orm =& $detail_handler->get( $item_id );
    $developer_old_objs =& $detail_orm->getDevelopers();
    if ( ! xoonips_is_same_objects( $developer_old_objs, $developer_objs ) ) {
      array_push( $ret, _MD_XNPTOOL_DEVELOPER_LABEL );
    }
  }
  return $ret;
}

function xnptoolGetTopBlock( $itemtype ) {
  return xnpGetTopBlock( $itemtype['name'], $itemtype['display_name'], 'images/icon_tool.gif', _MD_XNPTOOL_EXPLANATION, 'xnptool_tool_type', xnptool_get_type_array() );
}


// return 1 if downloadable for login user only
// return 0 if downloadable for everyone
function xnptoolGetAttachmentDownloadLimitOption( $item_id ) {
  global $xoopsDB;
  $sql = 'select attachment_dl_limit from '.$xoopsDB->prefix( 'xnptool_item_detail' )." where tool_id=${item_id}";
  $result = $xoopsDB->query( $sql );
  if ( $result ) {
    list( $option ) = $xoopsDB->fetchRow( $result );
    return $option;
  }
  return 0;
}

// return 1 if downloading is notified
// return 0 if downloading is not notified
function xnptoolGetAttachmentDownloadNotifyOption( $item_id ) {
  global $xoopsDB;
  $sql = 'select attachment_dl_notify from '.$xoopsDB->prefix( 'xnptool_item_detail' )." where tool_id=${item_id}";
  $result = $xoopsDB->query( $sql );
  if ( $result ) {
    list( $notify ) = $xoopsDB->fetchRow( $result );
    return $notify;
  }
  return 0;
}

function xnptoolSupportMetadataFormat( $metadataPrefix, $item_id ) {
  if ( $metadataPrefix == 'oai_dc' || $metadataPrefix == 'junii2' ) {
    return true;
  }
  return false;
}

function xnptoolGetMetadata($prefix, $item_id) {
  $mydirpath = dirname(dirname(__FILE__));
  $mydirname = basename($mydirpath);
  if (!in_array($prefix, array('oai_dc', 'junii2')))
    return false;
  // detail information 
  $detail_handler =& xoonips_getormhandler($mydirname, 'item_detail');
  $developer_handler =& xoonips_getormhandler($mydirname, 'developer');
  $detail_obj =& $detail_handler->get($item_id);
  if (empty($detail_obj))
    return false;
  $detail = $detail_obj->getArray();
  $criteria = new Criteria('tool_id', $item_id);
  $criteria->setSort('developer_order');
  $developer_objs =& $developer_handler->getObjects($criteria);
  $detail['developers'] = array();
  foreach ($developer_objs as $developer_obj)
    $detail['developers'][] = $developer_obj->get('developer');
  $types = xnptool_get_type_array();
  $detail['tool_type_display'] = $types[$detail['tool_type']];
  // basic information
  $basic = xnpGetBasicInformationArray($item_id);
  $basic['publication_date_iso8601'] = xnpISO8601($basic['publication_year'], $basic['publication_month'], $basic['publication_mday']);
  // indexes
  $indexes = array();
  if (xnp_get_index_id_by_item_id($_SESSION['XNPSID'], $item_id, $xids) == RES_OK)
    foreach ($xids as $xid)
      if (xnp_get_index($_SESSION['XNPSID'], $xid, $index) == RES_OK)
        $indexes[] = xnpGetIndexPathServerString($_SESSION['XNPSID'], $xid);
  // files
  $files = array();
  $mimetypes = array();
  $file_handler =& xoonips_gethandler('xoonips', 'file');
  if ($detail['attachment_dl_limit'] == 0) {
    $files = $file_handler->getFilesInfo($item_id, 'tool_data');
    foreach ($files as $file) {
      if (!in_array($file['mime_type'], $mimetypes))
        $mimetypes[] = $file['mime_type'];
    }
  }
  $previews = $file_handler->getFilesInfo($item_id, 'preview');
  // rights
  $detail['rights_cc_url'] = '';
  if ($detail['use_cc'] == 1) {
    $cond = 'by';
    if ($detail['cc_commercial_use'] == 0)
      $cond .= '-nc';
    if ($detail['cc_modification'] == 0)
      $cond .= '-nd';
    else if ($detail['cc_modification'] == 1)
      $cond .= '-sa';
    $detail['rights_cc_url'] = sprintf('http://creativecommons.org/licenses/%s/2.5/', $cond);
  }
  // related to
  $related_to_handler =& xoonips_getormhandler('xoonips', 'related_to');
  $related_to_ids = $related_to_handler->getChildItemIds($item_id);
  $related_tos = array();
  foreach ($related_to_ids as $related_to_id) {
    $related_tos[] = array(
      'item_id' => $related_to_id,
      'item_url' => XOOPS_URL.'/modules/xoonips/detail.php?item_id='.$related_to_id
    );
  }
  // repository configs
  $xconfig_handler =& xoonips_getormhandler( 'xoonips', 'config' );
  $myxoopsConfigMetaFooter =& xoonips_get_xoops_configs(XOOPS_CONF_METAFOOTER);
  $repository = array(
    'download_file_compression' => $xconfig_handler->getValue('download_file_compression'),
    'nijc_code' => $xconfig_handler->getValue('repository_nijc_code'),
    'publisher' => $xconfig_handler->getValue('repository_publisher'),
    'institution' => $xconfig_handler->getValue('repository_institution'),
    'meta_author' => $myxoopsConfigMetaFooter['meta_author']
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
