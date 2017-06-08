<?php

// $Revision:$
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
/**
 * display thumbnail
 * input:
 *    $_GET['file_id']
 *    $_GET['thumbnail'] 1: thumbnail image.
 */

// ignore protector check
define('PROTECTOR_SKIP_DOS_CHECK', 1);
define('PROTECTOR_SKIP_FILESCHECKER', 1);

require 'include/common.inc.php';

$xnpsid = $_SESSION['XNPSID'];

function image_error($err)
{
    switch ($err) {
    case 403:
        header('HTTP/1.0 403 Forbidden');
        break;
    case 404:
        header('HTTP/1.0 404 Not Found');
        break;
    case 500:
        header('HTTP/1.0 500 Internal Server Error');
        break;
    }
    exit();
}

// -> fileID
$formdata = &xoonips_getutility('formdata');
$fileID = $formdata->getValue('get', 'file_id', 'i', false);
if (empty($fileID)) {
    image_error(404);
}

// fileID -> itemID, sid, file_type_id.
$file_handler = &xoonips_getormhandler('xoonips', 'file');
$file = &$file_handler->get($fileID);
if (!is_object($file)) {
    image_error(404);
}
$item_id = $file->getVar('item_id', 'n');
$file_type_id = $file->getVar('file_type_id', 'n');
$file_name = $file->getVar('original_file_name', 'n');
$sess_id = $file->getVar('sess_id', 'n');
$mime_type = $file->get('mime_type');
$file_size = $file->get('file_size');

$file_type_handler = &xoonips_getormhandler('xoonips', 'file_type');
$file_type = &$file_type_handler->get($file_type_id);
if (!is_object($file_type)) {
    image_error(500);
}
$file_type_name = $file_type->getVar('name', 'n');

// get mime_type from file
$xconfig_handler = &xoonips_getormhandler('xoonips', 'config');
$upload_dir = $xconfig_handler->getValue('upload_dir');
$magic_file_path = $xconfig_handler->getValue('magic_file_path');
$file_path = $upload_dir.'/'.$fileID;
if (!is_readable($file_path)) {
    image_error(404);
}

// check rights of access
$item = array();
if ($xnpsid == $sess_id) {
    // same sid
} else {
    if (!$xoopsUser) {
        $uid = UID_GUEST;
    } elseif ($xoopsUser) {
        $uid = $xoopsUser->getVar('uid', 'n');
    }
    $item_compo_handler = &xoonips_getormcompohandler('xoonips', 'item');
    if ($item_compo_handler->getPerm($item_id, $uid, 'read')) {
        // Visible item
    } else {
        // user don't have rights of access.
        image_error(403);
    }
}

// remove ob fileters
$handlers = ob_list_handlers();
while (!empty($handlers)) {
    ob_end_clean();
    $handlers = ob_list_handlers();
}

// If character's code changes automatic, it becomes invalid.
if (function_exists('mb_http_output')) {
    mb_http_output('pass');
}

// output image
$thumbnail = $formdata->getValue('get', 'thumbnail', 'b', false);
if (!empty($thumbnail)) {
    $thumbnail_file = &$file->getVar('thumbnail_file', 'n');
    $size = strlen($thumbnail_file);

    if ($size > 0) {
        header('Content-Type: image/png');
        header("Content-Length: $size");
        echo $thumbnail_file;
        exit();
    } else {
        // thumbnail doesn't available.
        if (extension_loaded('fileinfo')) {
            if ($magic_file_path == '') {
                $finfo = finfo_open(FILEINFO_NONE);
            } else {
                $finfo = finfo_open(FILEINFO_NONE, $magic_file_path);
            }
            $label = finfo_file($finfo, $file_path);
            finfo_close($finfo);
        } else {
            // try to use mime_content_type();
              $label = mime_content_type($file_path);
        }
        if (preg_match('/^([^\\/]*)\\/(.*)$/', $mime_type, $matches)) {
            if ($matches[1] == 'audio') {
                $img_type = 'audio';
            } elseif ($matches[1] == 'image') {
                $img_type = 'image';
            } elseif ($matches[1] == 'video') {
                $img_type = 'video';
            } elseif ($matches[1] == 'text') {
                $img_type = 'text';
            } elseif ($matches[1] == 'application') {
                $text_types = array('pdf', 'xml', 'msword', 'vnd.ms-excel');
                $image_types = array('vnd.ms-powerpoint', 'postscript');
                $audio_types = array('vnd.rn-realmedia');
                if (in_array($matches[2], $text_types)) {
                    $img_type = 'text';
                } elseif (in_array($matches[2], $audio_types)) {
                    $img_type = 'audio';
                } elseif (in_array($matches[2], $image_types)) {
                    $img_type = 'image';
                } else {
                    $img_type = 'application';
                }
            } else {
                $img_type = 'unknown';
            }
        } else {
            $img_type = 'unknown';
        }
        $img_file = XOOPS_ROOT_PATH.'/modules/xoonips/images/thumbnail_'.$img_type.'.png';
        // create image resource
        $w = 100;
        $h = 100;
        $im = imagecreatetruecolor($w, $h);
        // label setting
        $f = 2;
        // font number
        $lp = 5;
        // label padding
        $fw = imagefontwidth($f);
        // font width
        $fh = imagefontheight($f);
        // font height
        $fmaxlen = ($w - $lp * 2) / $fw;
        // max label length
        $labels = explode(',', $label);
        $label = $labels[0];
        $llen = strlen($label);
        if ($llen > $fmaxlen) {
            $label = substr($label, 0, $fmaxlen - 3);
            $label .= '...';
            $llen = strlen($label);
        }
        $lx = ($w - $llen * $fw) / 2;
        $ly = $h - $fh - $lp;
        // change alpha attributes and create transparent color
        imageantialias($im, true);
        imagealphablending($im, false);
        imagesavealpha($im, true);
        $transparent = imagecolorallocatealpha($im, 255, 255, 255, 0);
        $col_white = imagecolorallocate($im, 255, 255, 255);
        $col_gray = imagecolorallocate($im, 127, 127, 127);
        $col_black = imagecolorallocate($im, 0, 0, 0);
        // fill all area with transparent color
        imagefill($im, 0, 0, $col_white);
        imagealphablending($im, true);
        $imicon = imagecreatefrompng($img_file);
        imagecopy($im, $imicon, $w / 2 - 48 / 2, $h / 2 - 48 / 2, 0, 0, 48, 48);
        imagepolygon($im, array(0, 0, $w - 1, 0, $w - 1, $h - 1, 0, $h - 1), 4, $col_gray);
        if (strlen($label) != 0) {
            imagestring($im, $f, $lx, $ly, $label, $black);
        }
        header('Content-Type: image/png');
        imagepng($im);
        imagedestroy($imicon);
        imagedestroy($im);
        exit();
    }
} else {
    if ($file_type_name != 'preview') {
        /* check the download limitation */
        $item_basic_handler = &xoonips_getormhandler('xoonips', 'item_basic');
        $criteria = new Criteria('item_id', $item_id);
        $items = &$item_basic_handler->getObjects($criteria, false, 'item_type_id');
        if (empty($items)) {
            image_error(500);
        }
        list($item) = $items;
        $item_type_id = $item->getVar('item_type_id', 'n');
        $item_type_handler = &xoonips_getormhandler('xoonips', 'item_type');
        $item_type = &$item_type_handler->get($item_type_id);
        if (is_null($item_type)) {
            image_error(500);
        }
        $viewphp = $item_type->getVar('viewphp', 'n');
        if (empty($viewphp)) {
            image_error(500);
        }
        require_once XOOPS_ROOT_PATH.'/modules/'.$itemtype['viewphp'];
        $fname_dllimit = "${name}GetAttachmentDownloadLimitOption";
        if (function_exists($fname_dllimit) && $fname_dllimit($item_id) == 1) {
            /* require to confirm file download */
            image_error(403);
        }
    }
    $strip_mime_types = explode(';', $mime_type);
    $strip_mime_type = trim($strip_mime_types[0]);
    $show_mime_types = array(
        'image/jpeg',
        'image/png',
        'image/gif',
        'text/plain',
    );
    if (in_array($strip_mime_type, $show_mime_types)) {
        // acceptable to show directly in the browser
        header("Content-Type: $mime_type");
        header("Content-Length: $file_size");
        readfile($file_path);
        exit();
    } else {
        // download file
        $download = &xoonips_getutility('download');
        if (!$download->check_pathinfo($file_name)) {
            $url = XOOPS_URL.'/modules/xoonips/image.php?file_id='.$fileID;
            $url = $download->append_pathinfo($url, $file_name);
            header('Location: '.$url);
            exit();
        }
        $download->download_file($file_path, $file_name, $mime_type);
        exit();
    }
}
