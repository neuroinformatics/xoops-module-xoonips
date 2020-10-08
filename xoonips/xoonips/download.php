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
/* Downloads attached file with or without compress option
 *
 * input:
 *    $_GET['file_id'] or $_POST['file_id']
 *    $_GET[XNP_CONFIG_DOI_FIELD_PARAM_NAME] or
        $_POST[XNP_CONFIG_DOI_FIELD_PARAM_NAME]
 *    one-time-token 'xoonips_download_token'.$file_id
 *
 * 1. Temporary file in the download procedure will be made in /tmp.
 * 2. Temporary file will be removed after script shutdown.
 */

// local functions
function download_error($num, $msg = '')
{
    $header_message = array(
        '403' => 'Forbidden',
        '404' => 'Not Found',
        '500' => 'Internal Server Error',
    );
    $error = sprintf('%d %s', $num, $header_message[$num]);
    header('HTTP/1.0 '.$error);
    if (empty($msg)) {
        echo $error;
    } else {
        echo 'Error : '.$msg;
    }
    exit();
}

function download_create_zipfile($file_id, $item_id, $file_name, $metadata, $file_path)
{
    $file_handler = &xoonips_gethandler('xoonips', 'file');
    if (false === $file_path) {
        $file_path = $file_handler->getFilePath($file_id);
    }
    if (!file_exists($file_path)) {
        // file not found
        return false;
    }
    // open metafile
    $dirutil = &xoonips_getutility('directory');
    $metafile_path = $dirutil->get_template('XooNIpsDownloadMetaFile');
    $metafile_fp = $dirutil->mkstemp($metafile_path);
    if (false === $metafile_fp) {
        // failed to create temporary file for metadata
        return false;
    }
    register_shutdown_function('download_unlink', $metafile_path);
    // write metafile
    $unicode = &xoonips_getutility('unicode');
    $metafile_body = '';
    foreach ($metadata as $key => $val) {
        $metafile_body .= $key;
        // convert dos and mac new line code to unix
        $val = str_replace("\r", "\n", str_replace("\r\n", "\n", $val));
        $ar = explode("\n", $val);
        $metafile_body .= ': ';
        if (count($ar) <= 1) {
            $metafile_body .= $val;
        } else {
            $metafile_body .= "\r\n  ".implode("\r\n  ", $ar);
        }
        $metafile_body .= "\r\n";
    }
    $metafile_body .= _MD_XOONIPS_ITEM_DETAIL_URL.': '.xnpGetItemDetailURL($item_id)."\r\n";
    $metafile_body = $unicode->encode_utf8($metafile_body);
    fwrite($metafile_fp, $metafile_body);
    // close metafile
    fclose($metafile_fp);

    // open zipfile
    $zipfile_path = $dirutil->get_template('XooNIpsDownloadZipFile');
    $zipfile_fp = $dirutil->mkstemp($zipfile_path);
    if (false === $zipfile_fp) {
        // failed to create temporary file for zip
        return false;
    }
    register_shutdown_function('download_unlink', $zipfile_path);
    fclose($zipfile_fp);
    $ziputil = &xoonips_getutility('zip');
    if (false == $ziputil->open($zipfile_path)) {
        // failed to open zip file
        return false;
    }
    // write zipfile
    $ziputil->add($file_path, $file_name);
    $ziputil->add($metafile_path, 'metainfo.txt');
    // close zipfile
    $ziputil->close();

    return $zipfile_path;
}

function download_unlink($file_path)
{
    @unlink($file_path);
}

// - main routine
// avoid IE bug1 -> http://jp2.php.net/header  Harry 10-Dec-2004 03:26
session_cache_limiter('none');

require 'include/common.inc.php';
require 'class/base/gtickets.php';

$formdata = &xoonips_getutility('formdata');
$download = &xoonips_getutility('download');
$file_handler = &xoonips_gethandler('xoonips', 'file');

$uid = is_object($xoopsUser) ? $xoopsUser->getVar('uid', 's') : UID_GUEST;
$is_post = 'POST' == $formdata->getRequestMethod() ? true : false;

// get parameters
$file_id = $formdata->getValue('both', 'file_id', 'i', false);
if (is_null($file_id)) {
    // try to get file_id from doi parameter - NTTDK and Keio University 20080825
    $doi = $formdata->getValue('both', XNP_CONFIG_DOI_FIELD_PARAM_NAME, 's', false);
    $file_id = $file_handler->getFileIdByDOI($doi);
    if (false === $file_id) {
        download_error(404);
    }
}

// get file object
$xf_handler = &xoonips_getormhandler('xoonips', 'file');
$xf_obj = &$xf_handler->get($file_id);
if (!is_object($xf_obj)) {
    download_error(404);
}

// check permission
$item_id = $xf_obj->get('item_id');
$item_compo_handler = &xoonips_getormcompohandler('xoonips', 'item');
if (!$item_compo_handler->getPerm($item_id, $uid, 'read')) {
    download_error(403);
}

// set page urls
$detail_url = sprintf('%s/modules/xoonips/detail.php?item_id=%d&op=download&download_file_id=%d', XOOPS_URL, $item_id, $file_id);
$download_url = sprintf('%s/modules/xoonips/download.php?file_id=%d', XOOPS_URL, $file_id);

// get file type information
$filetype_id = $xf_obj->get('file_type_id');
$filetype_handler = &xoonips_getormhandler('xoonips', 'file_type');
$filetype_obj = &$filetype_handler->get($filetype_id);
if (!is_object($filetype_obj)) {
    download_error(500, _MD_XOONIPS_ITEM_BAD_FILE_TYPE);
}
$itemtype_mid = $filetype_obj->get('mid');
if (is_null($itemtype_mid)) {
    download_error(500, _MD_XOONIPS_ITEM_BAD_FILE_TYPE);
}

// get item type information
$itemtype_handler = &xoonips_getormhandler('xoonips', 'item_type');
$criteria = new Criteria('mid', $itemtype_mid);
$itemtype_objs = &$itemtype_handler->getObjects($criteria);
if (1 != count($itemtype_objs)) {
    download_error(500, _MD_XOONIPS_ITEM_BAD_FILE_TYPE);
}
$itemtype_name = $itemtype_objs[0]->get('name');
$itemtype_viewphp = $itemtype_objs[0]->get('viewphp');
$itemtype_displayname = $itemtype_objs[0]->get('display_name');

// load item type's view.php
if (is_null($itemtype_viewphp)) {
    // maybe this is index
    download_error(500, _MD_XOONIPS_ITEM_BAD_FILE_TYPE);
}
require_once XOOPS_ROOT_PATH.'/modules/'.$itemtype_viewphp;

// check the download limitation
$dllimit_func = $itemtype_name.'GetAttachmentDownloadLimitOption';
if (function_exists($dllimit_func) && $dllimit_func($item_id)) {
    // only registered user can download file
    if (UID_GUEST == $uid) {
        // if user is guest than redirect to login page
        $parsed = parse_url(XOOPS_URL);
        $xoops_path = isset($parsed['path']) ? $parsed['path'] : '';
        $xoops_redirect = str_replace(XOOPS_URL, $xoops_path, $detail_url);
        header('Location:'.XOOPS_URL.'/modules/xoonips/user.php?xoops_redirect='.urlencode($xoops_redirect));
        exit();
    }
}

// check the download confirmation
$dlconfirm = false;
$dlconfirm_func = $itemtype_name.'GetDownloadConfirmationRequired';
if (function_exists($dlconfirm_func) && $dlconfirm_func($item_id)) {
    // let users confirm and download
    $ticket_area = 'xoonips_download_token'.$file_id;
    if (!$xoopsGTicket->check($is_post, $ticket_area, false)) {
        if (isset($_REQUEST['XOOPS_G_TIKET'])) {
            // bad token ticket, maybe this ticket already used
            redirect_header($detail_url, 5, _MD_XOONIPS_ITEM_ATTACHMENT_BAD_TOKEN_LABEL);
        } else {
            // no token ticket pair found
            header('Location:'.$detail_url);
        }
        exit();
    }
    $dlconfirm = true;
}

// check the download notification
$dlnotify = false;
$dlnotify_func = $itemtype_name.'GetAttachmentDownloadNotifyOption';
if (function_exists($dlnotify_func) && $dlnotify_func($item_id)) {
    $dlnotify = true;
}

// get download mode : file only or zip in metadata.txt
$xconfig_handler = &xoonips_getormhandler('xoonips', 'config');
$download_file_compression = $xconfig_handler->getValue('download_file_compression');
if (is_null($download_file_compression)) {
    download_error(500, _MD_XOONIPS_DOWNLOAD_ABNORMAL_CONFIGURATION);
}
$do_compress = 'on' == $download_file_compression;

$filename = $xf_obj->get('original_file_name');
$zip_filename = $itemtype_displayname.'_'.$file_id.'.zip';

$pathinfo_filename = $do_compress ? $zip_filename : $filename;
if (!$download->check_pathinfo($pathinfo_filename)) {
    // don't redirect if download notification required
    if (!$dlnotify) {
        // redirect page if safari
        $ticket_area = 'xoonips_download_token'.$file_id;
        $url = $download_url;
        if ($dlconfirm) {
            // add token ticket if confirmation required
            $url .= '&'.$xoopsGTicket->getTicketParamString(__LINE__, true, 1800, $ticket_area);
        }
        $url = $download->append_pathinfo($url, $pathinfo_filename);
        header('Location: '.$url);
        exit();
    }
}

$dl_filepath = false;

if (class_exists('XCube_DelegateUtils')) {
    XCube_DelegateUtils::call('Module.Xoonips.FileDownload.Prepare', $file_id, $item_id, $itemtype_name, new XCube_Ref($dl_filepath));
}

if ($do_compress) {
    // get metadata of attachment file
    $meta_func = $itemtype_name.'GetMetaInformation';
    $metadata = $meta_func($item_id);
    // create zip file
    $filename = $download->convert_to_client($filename, 'u');
    $dl_filepath = download_create_zipfile($file_id, $item_id, $filename, $metadata, $dl_filepath);

    if (false === $dl_filepath) {
        download_error(500, _MD_XOONIPS_ITEM_CANNOT_CREATE_TMPFILE);
    }
    if (is_null($dl_filename)) {
        $dl_filename = $zip_filename;
    }
    $dl_mimetype = 'application/x-zip';
} else {
    if (false === $dl_filepath) {
        $dl_filepath = $file_handler->getFilePath($file_id);
    }
    $dl_filename = $download->convert_to_client($filename, 'u');
    $dl_mimetype = $xf_obj->get('mime_type');
}

// record download file event log.
$eventlog_handler = &xoonips_getormhandler('xoonips', 'event_log');
$eventlog_handler->recordDownloadFileEvent($item_id, $file_id);

$download->download_file($dl_filepath, $dl_filename, $dl_mimetype);

$download_count = $xf_obj->get('download_count');
$xf_obj->set('download_count', $download_count + 1);
$xf_handler->insert($xf_obj, true);

if ($dlnotify) {
    xoonips_notification_user_file_downloaded($file_id, $uid);
}
exit();
