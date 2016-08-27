<?php
// $Revision: 1.54.2.1.2.31 $
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

$itemtype_path = dirname( __DIR__ );
$itemtype_dirname = basename( $itemtype_path );
$xoonips_path = dirname( $itemtype_path ).'/xoonips';

$langman =& xoonips_getutility( 'languagemanager' );
$langman->read( 'main.php', $itemtype_dirname );

/**
 *
 * @return array of specifiable Model Types. array( internal_name => display_name ... ) <br />
 * display names are explode( "\t", _MD_XNPMODEL_MODEL_TYPE_SELECT ) <br />
 * element order: matlab, neuron, original_program, satellite, genesis, a_cell, other<br />
 * <br />
 * return false if count(display names) != count(internal names)<br />
 * <br />
 *
 */
function xnpmodel_get_type_array() {
  $key = array(
    'matlab',
    'neuron',
    'original_program',
    'satellite',
    'genesis',
    'a_cell',
    'other',
  );
  $value = explode( "\t", _MD_XNPMODEL_MODEL_TYPE_SELECT );
  $ret = array();
  if ( count( $key ) != count( $value ) ) {
    return FALSE;
  }
  for ( $i = 0; $i < count( $key ); $i++ ) {
    $ret[$key[$i]] = $value[$i];
  }
  return $ret;
}

/** get DetailInformation by item_id
  *
  * @return
  *     array( 'model_type' => arary( 'value' => internal name of model type,
  *                                   'display_value' => display name of model type,
  *                                   'select' => array( internal_name => display_name ,...  ),
  *            'creator'    => arary( 'value' => creator name ) )
  * @return empty array with keys if bad $item_id
  * @return false: failure
  */
function xnpmodelGetDetailInformation( $item_id ) {
  global $xoopsDB;

  $result = $xoopsDB->query( 'select * from '.$xoopsDB->prefix( 'xnpmodel_item_detail' )." where model_id=$item_id" );
  $model = $xoopsDB->fetchArray( $result );
  if ( ! is_array( $model ) ) {
    return false;
  }

  foreach ( $model as $k => $v ) {
    $$k = $v;
  }

  $model_types = xnpmodel_get_type_array();
  $detail = array(
    'model_type' => array(
      'value' => $model_type,
      'select' => xnpmodel_get_type_array(),
      'display_value' => $model_types[$model_type],
    ),
    'readme' => array(
      'value' => $readme,
    ),
    'rights' => array(
      'value' => $rights,
    ),
    'use_cc' => array(
      'value' => $use_cc,
    ),
    'cc_commercial_use' => array(
      'value' => $cc_commercial_use,
    ),
    'cc_modification' => array(
      'value' => $cc_modification,
    ),
    'attachment_dl_limit' => array(
      'value' => $attachment_dl_limit,
    ),
    'attachment_dl_notify' => array(
      'value' => $attachment_dl_notify,
    ),
  );
  return $detail;
}

function xnpmodelGetListBlock( $item_basic ) {
  // get uid
  global $xoopsUser;
  $myuid = is_object( $xoopsUser ) ? $xoopsUser->getVar( 'uid', 'n' ) : UID_GUEST;

  // set to template
  global $xoopsTpl;

  $tpl = new XoopsTpl();
  // copy variables in $xoopsTpl to $tpl
  $tpl->assign( $xoopsTpl->get_template_vars() );

  $xnpmodel_handler =& xoonips_getormcompohandler( 'xnpmodel', 'item' );
  $tpl->assign( 'xoonips_item', $xnpmodel_handler->getTemplateVar( XOONIPS_TEMPLATE_TYPE_ITEM_LIST, $item_basic['item_id'], $myuid ) );

  // return as HTML
  return $tpl->fetch( 'db:xnpmodel_list_block.html' );
}

function xnpmodelGetPrinterFriendlyListBlock( $item_basic ) {
  return xnpmodelGetListBlock( $item_basic );
}


function xnpmodelGetDetailBlock( $item_id ) {
  // get uid
  global $xoopsUser;
  $myuid = is_object( $xoopsUser ) ? $xoopsUser->getVar( 'uid', 'n' ) : UID_GUEST;

  global $xoopsTpl;

  // get DetailInformation
  $detail_handler =& xoonips_getormhandler( 'xnpmodel', 'item_detail' );
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
  $tpl->assign( 'model_data', xnpGetAttachmentDetailBlock( $item_id, 'model_data' ) );
  $tpl->assign( 'readme', xnpGetTextFileDetailBlock( $item_id, 'readme', $detail_orm->getVar( 'readme', 'n' ) ) );
  $tpl->assign( 'rights', xnpGetRightsDetailBlock( $item_id, $detail_orm->getVar( 'use_cc', 'n' ), $detail_orm->getVar( 'rights', 'n' ), $detail_orm->getVar( 'cc_commercial_use', 'n' ), $detail_orm->getVar( 'cc_modification', 'n' ) ) );

  $xnpmodel_handler =& xoonips_getormcompohandler( 'xnpmodel', 'item' );
  $tpl->assign( 'xoonips_item', $xnpmodel_handler->getTemplateVar( XOONIPS_TEMPLATE_TYPE_ITEM_DETAIL, $item_id, $myuid ) );

  // return as HTML
  return $tpl->fetch( 'db:xnpmodel_detail_block.html' );
}

function xnpmodelGetDownloadConfirmationBlock( $item_id, $download_file_id ) {
  $detail = xnpmodelGetDetailInformation( $item_id );
  return xnpGetDownloadConfirmationBlock( $item_id, $download_file_id, $detail['attachment_dl_notify']['value'], true, $detail['use_cc']['value'], $detail['rights']['value'] );
}

function xnpmodelGetDownloadConfirmationRequired( $item_id ) {
  return true;
}

function xnpmodelGetPrinterFriendlyDetailBlock( $item_id ) {
  // get uid
  global $xoopsUser;
  $myuid = is_object( $xoopsUser ) ? $xoopsUser->getVar( 'uid', 'n' ) : UID_GUEST;

  global $xoopsTpl;

  // get DetailInformation
  $detail_handler =& xoonips_getormhandler( 'xnpmodel', 'item_detail' );
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
  $tpl->assign( 'model_data', xnpGetAttachmentPrinterFriendlyBlock( $item_id, 'model_data' ) );
  $tpl->assign( 'readme', xnpGetTextFilePrinterFriendlyBlock( $item_id, 'readme', $detail_orm->getVar( 'readme', 'n' ) ) );
  $tpl->assign( 'rights', xnpGetRightsPrinterFriendlyBlock( $item_id, $detail_orm->getVar( 'use_cc', 'n' ), $detail_orm->getVar( 'rights', 'n' ), $detail_orm->getVar( 'cc_commercial_use', 'n' ), $detail_orm->getVar( 'cc_modification', 'n' ) ) );

  $xnpmodel_handler =& xoonips_getormcompohandler( 'xnpmodel', 'item' );
  $tpl->assign( 'xoonips_item', $xnpmodel_handler->getTemplateVar( XOONIPS_TEMPLATE_TYPE_ITEM_DETAIL, $item_id, $myuid ) );

  // return as HTML
  return $tpl->fetch( 'db:xnpmodel_detail_block.html' );
}

function xnpmodelGetRegisterBlock() {
  global $xoopsDB;
  $formdata =& xoonips_getutility( 'formdata' );

  // retrive creator, model_type from GET/POST array
  foreach ( array( 'model_type', 'readme', 'rights' ) as $k ) {
    $tmp = $formdata->getValue( 'both', $k, 's', false );
    if ( $tmp !== NULL ) {
      $$k = $tmp;
    } else {
      $$k = false;
    }
  }

  // get BasicInformation / Preview / Readme / License / index block
  $basic = xnpGetBasicInformationRegisterBlock();
  $model_types = xnpmodel_get_type_array();
  if ( $model_type == false ) {
    list( $model_type ) = each( $model_types );
  }
  $detail = array(
    'model_type' => array(
      'value' => $model_type,
      'display_value' => $model_types[$model_type],
      'select' => xnpmodel_get_type_array(),
    ),
  );

  $preview = xnpGetPreviewRegisterBlock();
  $index = xnpGetIndexRegisterBlock();
  $attachment = xnpGetAttachmentRegisterBlock( 'model_data' );
  $readme = xnpGetTextFileRegisterBlock( 'readme' );
  $rights = xnpGetRightsRegisterBlock();

  // set to template
  global $xoopsTpl;

  $tpl = new XoopsTpl();
  // copy variables in $xoopsTpl to $tpl
  $tpl->assign( $xoopsTpl->get_template_vars() );

  $tpl->assign( 'basic', $basic );
  $tpl->assign( 'preview', $preview );
  $tpl->assign( 'index', $index );
  $tpl->assign( 'attachment', $attachment );
  $tpl->assign( 'attachment_dl_limit', xnpGetDownloadLimitationOptionRegisterBlock( 'xnpmodel' ) );
  $tpl->assign( 'attachment_dl_notify', xnpGetDownloadNotificationOptionRegisterBlock( 'xnpmodel' ) );
  $tpl->assign( 'detail', $detail );
  $tpl->assign( 'readme', $readme );
  $tpl->assign( 'rights', $rights );
  $tpl->assign( 'xnpmodel_creator', xoonips_get_multiple_field_template_vars( xoonips_get_orm_from_post( 'xnpmodel', 'creator' ), 'xnpmodel', 'creator' ) );

  // return as HTML
  return $tpl->fetch( 'db:xnpmodel_register_block.html' );
}

function xnpmodelGetEditBlock( $item_id ) {
  global $xoopsDB;
  $textutil =& xoonips_getutility( 'text' );
  $formdata =& xoonips_getutility( 'formdata' );

  // get DetailInformation
  $result = $xoopsDB->query( 'select * from '.$xoopsDB->prefix( 'xnpmodel_item_detail' )." where model_id=$item_id" );
  foreach ( $model = $xoopsDB->fetchArray( $result ) as $k => $v ) {
    $$k = $v;
  }
  // overwrite DetailInformation with POST/GET variables
  foreach ( array( 'model_type', 'readme', 'rights' ) as $k ) {
    $tmp = $formdata->getValue( 'both', $k, 's', false );
    if ( $tmp !== NULL ) {
      $$k = $tmp;
    }
  }

  // get BasicInformation / Preview / index block
  $basic = xnpGetBasicInformationEditBlock( $item_id );

  $model_types = xnpmodel_get_type_array();
  $detail = xnpmodelGetDetailInformation( $item_id );
  $detail['model_type']['value'] = $model_type;
  $detail['model_type']['display_value'] = $model_types[$model_type];
  $detail['model_type']['select'] = $model_types;
  $preview = xnpGetPreviewEditBlock( $item_id );
  $index = xnpGetIndexEditBlock( $item_id );
  $attachment = xnpGetAttachmentEditBlock( $item_id, 'model_data' );

  $readme = xnpGetTextFileEditBlock( $item_id, 'readme', $textutil->html_special_chars( $readme ) );
  $rights = xnpGetRightsEditBlock( $item_id, $detail['use_cc']['value'], $detail['rights']['value'], $detail['cc_commercial_use']['value'], $detail['cc_modification']['value'] );

  // set to template
  global $xoopsTpl;

  $tpl = new XoopsTpl();
  // copy variables in $xoopsTpl to $tpl
  $tpl->assign( $xoopsTpl->get_template_vars() );

  $tpl->assign( 'basic', $basic );
  $tpl->assign( 'preview', $preview );
  $tpl->assign( 'index', $index );
  $tpl->assign( 'attachment', $attachment );
  $tpl->assign( 'attachment_dl_limit', xnpGetDownloadLimitationOptionEditBlock( 'xnpmodel', xnpmodelGetAttachmentDownloadLimitOption( $item_id ) ) );
  $tpl->assign( 'attachment_dl_notify', xnpGetDownloadNotificationOptionEditBlock( 'xnpmodel', xnpmodelGetAttachmentDownloadNotifyOption( $item_id ) ) );
  $tpl->assign( 'detail', $detail );
  $tpl->assign( 'readme', $readme );
  $tpl->assign( 'rights', $rights );

  if ( ! $formdata->getValue( 'get', 'post_id', 's', false ) ) {
    $detail_handler =& xoonips_getormhandler( 'xnpmodel', 'item_detail' );
    $detail_orm =& $detail_handler->get( $item_id );
    $tpl->assign( 'xnpmodel_creator', xoonips_get_multiple_field_template_vars( $detail_orm->getCreators(), 'xnpmodel', 'creator' ) );
  } else {
    $tpl->assign( 'xnpmodel_creator', xoonips_get_multiple_field_template_vars( xoonips_get_orm_from_post( 'xnpmodel', 'creator' ), 'xnpmodel', 'creator' ) );
  }

  // return as HTML
  return $tpl->fetch( 'db:xnpmodel_register_block.html' );
}


function xnpmodelGetConfirmBlock( $item_id ) {
  $textutil =& xoonips_getutility( 'text' );
  $formdata =& xoonips_getutility( 'formdata' );
  $creator_handler =& xoonips_getormhandler( 'xnpmodel', 'creator' );
  $creator_objs =& $formdata->getObjectArray( 'post', $creator_handler->getTableName(), $creator_handler, false );

  // retrive creator, model_type from GET/POST array
  foreach ( array( 'model_type', 'creator', 'readme', 'rights' ) as $k ) {
    $tmp = $formdata->getValue( 'both', $k, 's', false );
    if ( $tmp !== NULL ) {
      $$k = $tmp;
    } else {
      $$k = false;
    }
  }

  // get BasicInformation / Preview / index block
  $basic = xnpGetBasicInformationConfirmBlock( $item_id );
  // TODO xnpConfirmHtml
  $model_types = xnpmodel_get_type_array();
  // reject if illegal model_type
  if ( isset( $model_types[$model_type] ) ) {
    $detail['model_type'] = array(
      'value' => $textutil->html_special_chars( $model_type ),
      'display_value' => $textutil->html_special_chars( $model_types[$model_type] ),
    );
  }

  $preview = xnpGetPreviewConfirmBlock( $item_id );
  $attachment = xnpGetAttachmentConfirmBlock( $item_id, 'model_data' );
  $index = xnpGetIndexConfirmBlock( $item_id );
  $lengths = xnpGetColumnLengths( 'xnpmodel_item_detail' );
  $readme = xnpGetTextFileConfirmBlock( $item_id, 'readme', $lengths['readme'] );
  $rights = xnpGetRightsConfirmBlock( $item_id, $lengths['rights'] );

  if ( xnpHasWithout( $basic ) || xnpHasWithout( $readme ) || xnpHasWithout( $rights ) || xnpHasWithout( $detail ) || xoonips_is_multiple_field_too_long( $creator_objs, 'xnpmodel', 'creator' ) ) {
    global $system_message;
    $system_message = $system_message."\n".'<br /><font color="#ff0000">'._MD_XOONIPS_ITEM_WARNING_FIELD_TRIM.'</font><br />';
  }

  $detail['creator_str'] = array(
    'value' => '',
  );
  if ( ! empty( $detail['creator'] ) ) {
    $detail['creator_str']['value'] = "<table>\n";
    $creator = explode( "\n", $detail['creator']['value'] );
    $i = 0;
    foreach ( $creator as $value ) {
      $detail['creator_str']['value'] .= '<tr class="oddeven'.fmod( $i, 2 ).'"><td>'.$value."</td></tr>\n";
      $i++;
    }
    $detail['creator_str']['value'] .= '</table>';
    $detail['creator_cnt'] = array(
      'value' => strval( fmod( count( $creator ), 2 ) ),
    );
  }
  // set to template
  global $xoopsTpl;

  $tpl = new XoopsTpl();
  // copy variables in $xoopsTpl to $tpl
  $tpl->assign( $xoopsTpl->get_template_vars() );

  $tpl->assign( 'basic', $basic );
  $tpl->assign( 'preview', $preview );
  $tpl->assign( 'index', $index );
  $tpl->assign( 'attachment', $attachment );
  $tpl->assign( 'attachment_dl_limit', xnpGetDownloadLimitationOptionConfirmBlock( 'xnpmodel' ) );
  $tpl->assign( 'attachment_dl_notify', xnpGetDownloadNotificationOptionConfirmBlock( 'xnpmodel' ) );
  $tpl->assign( 'detail', $detail );
  $tpl->assign( 'readme', $readme );
  $tpl->assign( 'rights', $rights );
  $tpl->assign( 'model_type', $model_type );
  $tpl->assign( 'xnpmodel_creator', xoonips_get_multiple_field_template_vars( $creator_objs, 'xnpmodel', 'creator' ) );

  // return as HTML
  return $tpl->fetch( 'db:xnpmodel_confirm_block.html' );
}

function xnpmodelInsertItem( &$item_id ) {
  global $xoopsDB;
  $formdata =& xoonips_getutility( 'formdata' );

  $xnpsid = $_SESSION['XNPSID'];

  // register BasicInformation, Index, Attachment
  $item_id = 0;
  $result = xnpInsertBasicInformation( $item_id );
  if ( $result ) {
    $result = xnpUpdateIndex( $item_id );
    if ( $result ) {
      $result = xnpUpdatePreview( $item_id );
      if ( $result ) {
        $result = xnpUpdateAttachment( $item_id, 'model_data' );
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

  // register DetailInformation
  list( $rights, $use_cc, $cc_commercial_use, $cc_modification ) = xnpGetRights();

  // it makes strings with constant length
  $ar = array(
    'model_type' => $formdata->getValue( 'post', 'model_type', 's', false ),
    'readme' => xnpGetTextFile( 'readme' ),
    'rights' => $rights,
  );
  xnpTrimColumn( $ar, 'xnpmodel_item_detail', array_keys( $ar ), _CHARSET );

  $keys = implode( ',', array( 'attachment_dl_limit', 'attachment_dl_notify', 'model_type', 'readme', 'rights', 'use_cc', 'cc_commercial_use', 'cc_modification' ) );
  $attachment_dl_limit = $formdata->getValue( 'post', 'attachment_dl_limit', 'i', false );
  $attachment_dl_notify = $formdata->getValue( 'post', 'attachment_dl_notify', 'i', false );
  $vals = implode( '\',\'', array( $attachment_dl_limit, $attachment_dl_limit ? $attachment_dl_notify : 0, addslashes( $ar['model_type'] ), addslashes( $ar['readme'] ), addslashes( $ar['rights'] ), $use_cc, $cc_commercial_use, $cc_modification ) );

  $sql = 'insert into '.$xoopsDB->prefix( 'xnpmodel_item_detail' )." ( model_id, $keys ) values ( $item_id, '$vals' ) ";
  $result = $xoopsDB->queryF( $sql );
  if ( $result == false ) {
    echo 'cannot insert item_detail: '.$xoopsDB->error();
    return false;
  }

  // insert creator
  $creator_handler =& xoonips_getormhandler( 'xnpmodel', 'creator' );
  $creator_objs =& $formdata->getObjectArray( 'post', $creator_handler->getTableName(), $creator_handler, false );
  if ( ! $creator_handler->updateAllObjectsByForeignKey( 'model_id', $item_id, $creator_objs ) ) {
    return false;
  }
  return true;
}

function xnpmodelUpdateItem( $item_id ) {
  global $xoopsDB;
  $formdata =& xoonips_getutility( 'formdata' );

  $xnpsid = $_SESSION['XNPSID'];

  // edit BasicInformation, Index, Preview, Attachment
  $result = xnpUpdateBasicInformation( $item_id );
  if ( $result ) {
    $result = xnpUpdateIndex( $item_id );
    if ( $result ) {
      $result = xnpUpdatePreview( $item_id );
      if ( $result ) {
        $result = xnpUpdateAttachment( $item_id, 'model_data' );
        if ( $result ) {
          $result = xnp_insert_change_log( $xnpsid, $item_id, $formdata->getValue( 'post', 'change_log', 's', false ) );
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

  // it makes strings with constant length
  $ar = array(
    'model_type' => $formdata->getValue( 'post', 'model_type', 's', false ),
    'readme' => xnpGetTextFile( 'readme' ),
    'rights' => $rights,
  );
  xnpTrimColumn( $ar, 'xnpmodel_item_detail', array_keys( $ar ), _CHARSET );

  // insert Detail Information
  $attachment_dl_limit = $formdata->getValue( 'post', 'attachment_dl_limit', 'i', false );
  $attachment_dl_notify = $formdata->getValue( 'post', 'attachment_dl_notify', 'i', false );
  $keyval = array(
    'attachment_dl_limit'.'=\''.$attachment_dl_limit.'\'',
    'attachment_dl_notify'.'=\''.( $attachment_dl_limit ? $attachment_dl_notify : 0 ).'\'',
    'model_type'.'=\''.addslashes( $ar['model_type'] ).'\'',
    'readme'.'=\''.addslashes( $ar['readme'] ).'\'',
    'rights'.'=\''.addslashes( $ar['rights'] ).'\'',
    'use_cc'.'=\''.$use_cc.'\'',
    'cc_commercial_use'.'=\''.$cc_commercial_use.'\'',
    'cc_modification'.'=\''.$cc_modification.'\'',
  );

  // edit DetailInformation
  $sql = 'update '.$xoopsDB->prefix( 'xnpmodel_item_detail' ).' set '.implode( ', ', $keyval )." where model_id=$item_id";
  $result = $xoopsDB->queryF( $sql );
  if ( $result == false ) {
    echo 'cannot update item_detail';
    echo "\n$sql";
    return false;
  }

  // insert/update creator
  $creator_handler =& xoonips_getormhandler( 'xnpmodel', 'creator' );
  $creator_objs =& $formdata->getObjectArray( 'post', $creator_handler->getTableName(), $creator_handler, false );
  if ( ! $creator_handler->updateAllObjectsByForeignKey( 'model_id', $item_id, $creator_objs ) ) {
    return false;
  }
  return true;
}

function xnpmodelCheckRegisterParameters( &$msg ) {
  $formdata =& xoonips_getutility( 'formdata' );
  $xnpsid = $_SESSION['XNPSID'];

  $result = true;
  $model_data = $formdata->getFile( 'model_data', false );
  $model_dataFileID = $formdata->getValue( 'post', 'model_dataFileID', 'i', false );
  $xoonipsCheckedXID = $formdata->getValue( 'post', 'xoonipsCheckedXID', 's', false );

  $creators = xoonips_get_multi_field_array_from_post( 'xnpmodel', 'creator' );
  if ( empty( $creators ) ) {
    $messages[] = _MD_XNPMODEL_CREATOR_REQUIRED;
  }

  if ( ( ! isset( $model_data ) || $model_data['name'] == '' ) && $model_dataFileID == '' ) {
    // model_data is not filled
    $msg = $msg.'<br /><font color="#ff0000">'._MD_XNPMODEL_MODEL_FILE_REQUIRED.'</font>';
    $result = false;
  }
  // require Readme and License if register to public indexes
  $xids = explode( ',', $xoonipsCheckedXID );
  $indexes = array();
  if ( $xids[0] != $xoonipsCheckedXID ) {
    foreach ( $xids as $i ) {
      $index = array();
      if ( xnp_get_index( $xnpsid, $i, $index ) == RES_OK ) {
        $indexes[] = $index;
      } else {
        $msg = $msg.'<br /><font color="#ff0000">'.xnp_get_last_error_string().'</font>';
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
          $msg = $msg.'<br /><font color="#ff0000">'._MD_XNPMODEL_README_REQUIRED.'</font>';
          $result = false;
        }
        if ( $rightsEncText == '' && $rightsUseCC == '0' ) {
          // license is not filled
          $msg = $msg.'<br /><font color="#ff0000">'._MD_XNPMODEL_RIGHTS_REQUIRED.'</font>';
          $result = false;
        }
        break;
      }
    }
  }
  return $result;
}

function xnpmodelCheckEditParameters( &$msg ) {
  return xnpmodelCheckRegisterParameters( $msg );
}

function xnpmodelGetMetaInformation( $item_id ) {
  $ret = array();
  $creator_array = array();

  $basic = xnpGetBasicInformationArray( $item_id );
  $detail = xnpmodelGetDetailInformation( $item_id );
  if ( ! empty( $basic ) ) {
    $ret[_MD_XOONIPS_ITEM_TITLE_LABEL] = implode( "\n", $basic['titles'] );
    $ret[_MD_XOONIPS_ITEM_CONTRIBUTOR_LABEL] = $basic['contributor'];
    $ret[_MD_XOONIPS_ITEM_KEYWORDS_LABEL] = implode( "\n", $basic['keywords'] );
    $ret[_MD_XOONIPS_ITEM_DESCRIPTION_LABEL] = $basic['description'];
    $ret[_MD_XOONIPS_ITEM_DOI_LABEL] = $basic['doi'];
    $ret[_MD_XOONIPS_ITEM_LAST_UPDATE_DATE_LABEL] = $basic['last_update_date'];
    $ret[_MD_XOONIPS_ITEM_CREATION_DATE_LABEL] = $basic['creation_date'];
  }
  if ( ! empty( $detail ) ) {
    $ret[_MD_XNPMODEL_MODEL_TYPE_LABEL] = $detail['model_type']['display_value'];
  }
  $xnpmodel_handler =& xoonips_getormcompohandler( 'xnpmodel', 'item' );
  $xnpmodel =& $xnpmodel_handler->get( $item_id );
  foreach ( $xnpmodel->getVar( 'creator' ) as $creator ) {
    $creator_array[] = $creator->getVar( 'creator', 'n' );
  }
  $ret[_MD_XNPMODEL_CREATOR_LABEL] = implode( "\n", $creator_array );

  return $ret;
}

function xnpmodelGetAdvancedSearchBlock( &$search_var ) {

  $basic = xnpGetBasicInformationAdvancedSearchBlock( 'xnpmodel', $search_var );

  $search_var[] = 'xnpmodel_model_type';
  $search_var[] = 'xnpmodel_creator';
  $search_var[] = 'xnpmodel_caption';
  $search_var[] = 'xnpmodel_model_file';

  // set to template
  global $xoopsTpl;

  $tpl = new XoopsTpl();
  // copy variables in $xoopsTpl to $tpl
  $tpl->assign( $xoopsTpl->get_template_vars() );
  $tpl->assign( 'basic', $basic );
  $tpl->assign( 'module_name', 'xnpmodel' );
  $model_type = xnpmodel_get_type_array();
  $tpl->assign( 'model_type_option', $model_type );
  $tpl->assign( 'module_display_name', xnpGetItemTypeDisplayNameByDirname( basename( dirname( __DIR__ ) ), 's' ) );

  // return as HTML
  return $tpl->fetch( 'db:xnpmodel_search_block.html' );
}

function xnpmodelGetAdvancedSearchQuery( &$where, &$join ) {
  global $xoopsDB;
  $formdata =& xoonips_getutility( 'formdata' );
  $basic_table = $xoopsDB->prefix( 'xoonips_item_basic' );
  $model_table = $xoopsDB->prefix( 'xnpmodel_item_detail' );
  $model_creator_table = $xoopsDB->prefix( 'xnpmodel_creator' );
  $file_table = $xoopsDB->prefix( 'xoonips_file' );
  $search_text_table = $xoopsDB->prefix( 'xoonips_search_text' );

  $wheres = array();
  $joins = array();
  $w = xnpGetBasicInformationAdvancedSearchQuery( 'xnpmodel' );
  if ( $w ) {
    $wheres[] = $w;
  }
  $w = xnpGetKeywordQuery( $model_table.'.model_type', 'xnpmodel_model_type' );
  if ( $w ) {
    $wheres[] = $w;
  }
  $w = xnpGetKeywordQuery( $model_creator_table.'.creator', 'xnpmodel_creator' );
  if ( $w ) {
    $wheres[] = $w;
  }
  $w = xnpGetKeywordQuery( $file_table.'.caption', 'xnpmodel_caption' );
  if ( $w ) {
    $wheres[] = $w;
    $wheres[] = " $file_table.file_type_id = 1";
  }
  $xnpmodel_model_file = $formdata->getValue( 'post', 'xnpmodel_model_file', 's', false );
  if ( ! empty( $xnpmodel_model_file ) ) {
    $search_text_table = $xoopsDB->prefix('xoonips_search_text');
    $file_table = $xoopsDB->prefix('xoonips_file');
    $searchutil =& xoonips_getutility('search');
    $fulltext_query = $xnpmodel_model_file;
    $fulltext_encoding = mb_detect_encoding($fulltext_query);
    $fulltext_criteria = new CriteriaCompo($searchutil->getFulltextSearchCriteria('search_text', $fulltext_query, $fulltext_encoding, $search_text_table));
    $fulltext_criteria->add(new Criteria('is_deleted', 0, '=', $file_table));
    $wheres[] = $fulltext_criteria->render();
  }
  $where = implode( ' AND ', $wheres );
  $join = " INNER JOIN $model_creator_table ON ".$model_creator_table.'.model_id  = '.$xoopsDB->prefix( 'xoonips_item_basic' ).'.item_id ';
}


function xnpmodelGetDetailInformationQuickSearchQuery( &$wheres, &$join, $keywords ) {
  global $xoopsDB;
  $model_table = $xoopsDB->prefix( 'xnpmodel_item_detail' );
  $model_creator_table = $xoopsDB->prefix( 'xnpmodel_creator' );
  $file_table = $xoopsDB->prefix( 'xoonips_file' );

  $join = " INNER JOIN $model_creator_table ON ".$model_creator_table.'.model_id  = '.$xoopsDB->prefix( 'xoonips_item_basic' ).'.item_id ';
  $colnames = array(
    $model_creator_table.'.creator',
    $file_table.'.caption',
  );
  $wheres = xnpGetKeywordsQueries( $colnames, $keywords );
  return true;
}

function xnpmodelGetDetailInformationTotalSize( $iids ) {
  return xnpGetTotalFileSize( $iids );
}

/**
 *
 * create XML for exporting detail information
 * see xnpExportItem for detail
 * @see xnpExportItem
 *
 * @param export_path folder that export file is written to.
 * @param fhdl file handle that items are exported to.
 * @param item_id item id that is exported
 * @param attachment true if attachment files are exported, else false.
 * @return true: success
 * @return false:error
 */
function xnpmodelExportItem( $export_path, $fhdl, $item_id, $attachment ) {
  // get DetailInformation
  if ( ! $fhdl ) {
    return false;
  }

  $handler =& xoonips_getormhandler( 'xnpmodel', 'item_detail' );
  $detail =& $handler->get( $item_id );
  if ( ! $detail ) {
    return false;
  }

  $creators = '';
  foreach ( $detail->getCreators() as $creator ) {
    $creators .= '<creator>'.$creator->getVar( 'creator', 's' ).'</creator>';
  }

  if ( ! fwrite( $fhdl, "<detail id=\"${item_id}\" version=\"1.03\">\n".'<model_type>'.$detail->getVar( 'model_type', 's' )."</model_type>\n"."<creators>{$creators}</creators>\n".'<readme>'.$detail->getVar( 'readme', 's' )."</readme>\n".'<rights>'.$detail->getVar( 'rights', 's' )."</rights>\n".'<use_cc>'.intval( $detail->get( 'use_cc', 's' ) )."</use_cc>\n".'<cc_commercial_use>'.intval( $detail->get( 'cc_commercial_use' ) )."</cc_commercial_use>\n".'<cc_modification>'.intval( $detail->get( 'cc_modification' ) )."</cc_modification>\n".'<attachment_dl_limit>'.intval( $detail->get( 'attachment_dl_limit' ) )."</attachment_dl_limit>\n".'<attachment_dl_notify>'.intval( $detail->get( 'attachment_dl_notify' ) )."</attachment_dl_notify>\n" ) ) {
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

/**
 * bool xnpmodelGetLicenseRequired( int item_id )
 *
 *
 */
function xnpmodelGetLicenseRequired( $item_id ) {
  global $xoopsDB;

  // get DetailInformation
  $result = $xoopsDB->query( 'select * from '.$xoopsDB->prefix( 'xnpmodel_item_detail' )." where model_id=$item_id" );
  if ( ! $result ) {
    return NULL;
  }
  $detail = $xoopsDB->fetchArray( $result );
  return isset( $detail['rights'] ) && $detail['rights'] != '';
}

/**
 * string xnpmodelGetLicenseStatement( int item_id )
 *
 *
 */
function xnpmodelGetLicenseStatement( $item_id ) {
  global $xoopsDB;

  // get DetailInformation
  $result = $xoopsDB->query( 'select * from '.$xoopsDB->prefix( 'xnpmodel_item_detail' )." where model_id=$item_id" );
  if ( ! $result ) {
    return NULL;
  }
  $detail = $xoopsDB->fetchArray( $result );
  return array( isset( $detail['rights'] ) ? $detail['rights'] : '', $detail['use_cc'] );;
}

function xnpmodelGetModifiedFields( $item_id ) {
  $ret = array();
  $formdata =& xoonips_getutility( 'formdata' );

  $detail = xnpmodelGetDetailInformation( $item_id );
  if ( $detail ) {
    foreach ( array( 'model_type' => _MD_XNPMODEL_MODEL_TYPE_LABEL ) as $k => $v ) {
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
      if ( $tmp != $detail[$k]['value'] ) {
        array_push( $ret, $v );
      }
    }

    // is rights modified ?
    $rightsUseCC = $formdata->getValue( 'post', 'rightsUseCC', 'i', false );
    $rightsEncText = $formdata->getValue( 'post', 'rightsEncText', 's', false );
    if ( $rightsUseCC !== NULL ) {
      if ( $rightsUseCC == 0 ) {
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

    // was model file modified?
    if ( xnpIsAttachmentModified( 'model_data', $item_id ) ) {
      array_push( $ret, _MD_XNPMODEL_MODEL_FILE_LABEL );
    }

    $creator_handler =& xoonips_getormhandler( 'xnpmodel', 'creator' );
    $creator_objs =& $formdata->getObjectArray( 'post', $creator_handler->getTableName(), $creator_handler, false );
    $detail_handler =& xoonips_getormhandler( 'xnpmodel', 'item_detail' );
    $detail_orm =& $detail_handler->get( $item_id );
    $creator_old_objs =& $detail_orm->getCreators();
    if ( ! xoonips_is_same_objects( $creator_old_objs, $creator_objs ) ) {
      array_push( $ret, _MD_XNPMODEL_CREATOR_LABEL );
    }
  }
  return $ret;
}

function xnpmodelGetTopBlock( $itemtype ) {
  return xnpGetTopBlock( $itemtype['name'], $itemtype['display_name'], 'images/icon_model.gif', _MD_XNPMODEL_EXPLANATION, 'xnpmodel_model_type', xnpmodel_get_type_array() );
}

// return 1 if downloadable for login user only
// return 0 if downloadable for everyone
function xnpmodelGetAttachmentDownloadLimitOption( $item_id ) {
  global $xoopsDB;
  $sql = 'select attachment_dl_limit from '.$xoopsDB->prefix( 'xnpmodel_item_detail' )." where model_id=${item_id}";
  $result = $xoopsDB->query( $sql );
  if ( $result ) {
    list( $option ) = $xoopsDB->fetchRow( $result );
    return $option;
  }
  return 0;
}

// return 1 if downloading is notified
// return 0 if downloading is not notified
function xnpmodelGetAttachmentDownloadNotifyOption( $item_id ) {
  global $xoopsDB;
  $sql = 'select attachment_dl_notify from '.$xoopsDB->prefix( 'xnpmodel_item_detail' )." where model_id=${item_id}";
  $result = $xoopsDB->query( $sql );
  if ( $result ) {
    list( $notify ) = $xoopsDB->fetchRow( $result );
    return $notify;
  }
  return 0;
}

function xnpmodelSupportMetadataFormat( $metadataPrefix, $item_id ) {
  if ( $metadataPrefix == 'oai_dc' || $metadataPrefix == 'junii2' ) {
    return true;
  }
  return false;
}

function xnpmodelGetMetadata($prefix, $item_id) {
  $mydirpath = dirname(dirname(__FILE__));
  $mydirname = basename($mydirpath);
  if (!in_array($prefix, array('oai_dc', 'junii2')))
    return false;
  // detail information 
  $detail_handler =& xoonips_getormhandler($mydirname, 'item_detail');
  $creator_handler =& xoonips_getormhandler($mydirname, 'creator');
  $detail_obj =& $detail_handler->get($item_id);
  if (empty($detail_obj))
    return false;
  $detail = $detail_obj->getArray();
  $criteria = new Criteria('model_id', $item_id);
  $criteria->setSort('creator_order');
  $creator_objs =& $creator_handler->getObjects($criteria);
  $detail['creators'] = array();
  foreach ($creator_objs as $creator_obj)
    $detail['creators'][] = $creator_obj->get('creator');
  $types = xnpmodel_get_type_array();
  $detail['model_type_display'] = $types[$detail['model_type']];
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
    $files = $file_handler->getFilesInfo($item_id, 'model_data');
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
