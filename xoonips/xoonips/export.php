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

// $op
//   'export' .... Export and download
//   'list'   .... page of agreement licence(item to export)
//   'config' .... set condition to Export
//
// $export_type
//   'index' .... Export strcuture of index tree specified by $index_id
//   'item'  .... Export items specified by $ids or registered to $index_id
//
// $index_id
//   User wants to export specified index_id
//
// $ids
//   array of item_id to be exported
//
// $recursive_index
// $recursive_item
//   Do indexes export recursively?
//   refer $recursive_index if $export_type is 'index'
//   refer $recursive_item if $export_type is 'item'
//   1 .... Yes
//   0 .... No
//   Undefine .... No
//
// $attachment
//   Do attachment files export?
//   1 .... Yes
//   0 .... No
//   Undefine .... No
session_cache_limiter('none'); // Escape IE's Bug 1 -> http://jp2.php.net/header  Harry 10-Dec-2004 03:26
$xoopsOption['pagetype'] = 'user';
require 'include/common.inc.php';

require_once 'include/lib.php';
require_once 'include/AL.php';
require_once 'include/imexport.php';

xoonips_deny_guest_access();

$uid = $_SESSION['xoopsUserId'];

$formdata = &xoonips_getutility('formdata');

$textutil = &xoonips_getutility('text');

$request_vars = array(
    'op' => array('s', ''),
    'index_id' => array('i', ''),
    'export_type' => array('s', ''),
    'recursive_item' => array('i', 0),
    'recursive_index' => array('i', 0),
    'attachment' => array('i', 0),
);
foreach ($request_vars as $key => $meta) {
    list($type, $default) = $meta;
    $$key = $formdata->getValue('both', $key, $type, false, $default);
}
$ids = $formdata->getValueArray('both', 'ids', 'i', false);

if ($op == 'export') {
    if ($export_type == '') {
        die('export_type is not defined');
    }

    if (xnp_get_config_value('export_attachment', $value) != RES_OK) {
        $value = 'off';
    }
    if ($value == 'off' && $attachment != 0) {
        die('illegal request');
    }

    do {
        $export_dir = xoonips_create_temporary_dir();
        if (!$export_dir) {
            $system_message = "can't make directory '${export_dir}'";
            $op = 'list';
            break;
        }

        //chdir($export_dir);
        if ($export_type == 'index') {
            //
            // exprot index tree structure only
            //
            $index = array();
            $res = xnp_get_index($_SESSION['XNPSID'], intval($index_id), $index);
            if ($res != RES_OK) {
                $system_message = "can't get indexes";
                $op = 'list';
                break;
            }

            $tmpfile = tempnam('/tmp', 'XNP');
            $filename = "${export_dir}/index.xml";

            $fp = fopen($tmpfile, 'w');
            if (!$fp) {
                $system_message = "can't open file '${tmpfile}' for write.";
                $op = 'list';
                break;
            }

            if (xoonips_export_index_xml($fp, $index_id, $recursive_index == 1)) {
                fclose($fp);
                if (!xoonips_convert_file_encoding_to_utf8($tmpfile, $filename)) {
                    $op = 'list';
                    $system_message = 'error in create file.';
                    break;
                }
                unlink($tmpfile);
            } else {
                $system_message = "error in writing to '${filename}'";
                fclose($fp);
                unlink($filename);
                $op = 'list'; //return to previous page(list of export item)
                break;
            }
        } elseif ($export_type == 'item') {
            //
            // export items
            //
            foreach ($ids as $item_id) {
                $result = xnpExportItem($export_dir, $item_id, $attachment == 1, false, $recursive_item ? $index_id : false);
                if (!$result) {
                    //error: can't export.
                    $system_message = 'error in exporting items';
                    $op = 'list'; //return to previous page(list of export item)
                    break 2; //exit from foreach and outer while loop
                } else {
                    $zippedFiles[] = $result['xml'];
                    $removeFiles[] = "${export_dir}/${result['xml']}";

                    if (!empty($result['attachments'])) {
                        foreach ($result['attachments'] as $file) {
                            $zippedFiles[] = $file;
                            $removeFiles[] = "${result['path']}/${file}";
                        }
                    }
                }
            }
        }

        $zippedFiles = array();
        $removeFiles = array();
        if ($dh = opendir($export_dir)) {
            while (false !== ($f = readdir($dh))) {
                if ($f == '.' || $f == '..' || $f == 'files') {
                    continue;
                }
                $zippedFiles[] = "$f";
                $removeFiles[] = "${export_dir}/${f}";
            }
            closedir($dh);
        }
        if ($dh = @opendir($export_dir.'/files')) {
            while (false !== ($f = readdir($dh))) {
                if ($f == '.' || $f == '..' || $f == 'files') {
                    continue;
                }
                $zippedFiles[] = "files/${f}";
                $removeFiles[] = "${export_dir}/files/${f}";
            }
            closedir($dh);
        }
        if (count($zippedFiles) == 0) {
            $system_message = _MD_XOONIPS_EXPORT_EXPORT_ITEMS_EMPTY;
            $op = 'list'; //return to previous page(list of export item)
            break;
        }

        // do ZIP
        $zipFile = tempnam('/tmp', 'FOO');
        unlink($zipFile);
        $removeFiles[] = $zipFile.'.zip';
        $zip = &xoonips_getutility('zip');
        $zip->open($zipFile.'.zip');
        foreach ($zippedFiles as $fname) {
            $zip->add($export_dir.'/'.$fname, $fname);
        }
        $result = $zip->close();

        if (!$result) {
            $system_message = _MD_XOONIPS_ITEM_CANNOT_CREATE_ZIP;
            $op = 'list'; //return to previous page(list of export item)
            break;
        }
        $size = filesize($zipFile.'.zip');

        header('Cache-Control: none');  // To escape a IE's BUG(2)

        header('Content-Disposition: attachment; filename="export.zip"');
        header('Content-Type: application/zip');
        if ($size != false) {
            header("Content-Length: $size");
        }

        readfile($zipFile.'.zip');

        foreach ($removeFiles as $f) {
            unlink($f);
        }
        @rmdir($export_dir.'/files'); //remove files/ folder(xnpExportFile makes this dir. see also imexport.php.)
        rmdir($export_dir);
        exit();
    } while (false);
} elseif ($op == 'list') {
    if ($export_type == 'index') {
        $indexpath = xnpGetIndexPathString($_SESSION['XNPSID'], $index_id);
        $export_index = _MD_XOONIPS_ITEM_INDEX_LABEL;
        $yesno = ($recursive_index == 1) ? _YES : _NO;
        $export_recursive = _MD_XOONIPS_EXPORT_RECURSIVE;
        $submit = _MD_XOONIPS_EXPORT_LICENSE_AGREEMENT_SUBMIT;
        $pankuzu = _MD_XOONIPS_EXPORT_PANKUZU_EXPORT
            ._MI_XOONIPS_ACCOUNT_PANKUZU_SEPARATOR
            ._MD_XOONIPS_EXPORT_PANKUZU_EXPORT_INDEX;
        $message = _MD_XOONIPS_EXPORT_EXPORT_INDEX;

        include XOOPS_ROOT_PATH.'/header.php';
        echo <<<EOT
            <p>
            $pankuzu
            </p>
            <p>
            $message
            </p>
            <form id='export_list' action='export.php' method='post'>
            $export_index : $indexpath <br />
            $export_recursive : $yesno <br />
            <p style='text-align:center;'>
            <input class="formButton" name="submit" type="submit" value="$submit" />
            </p>        
            <input type='hidden' name='op' value='export' />
            <input type='hidden' name='export_type' value='$export_type' />
            <input type='hidden' name='index_id' value='$index_id' />
            <input type='hidden' name='recursive_index' value='$recursive_index' />
            </form>
EOT;
        include XOOPS_ROOT_PATH.'/footer.php';
    } elseif ($export_type == 'item') {
        $xoopsOption['template_main'] = 'xoonips_export_license.html';

        include XOOPS_ROOT_PATH.'/header.php';

        if ($index_id != '') {
            $ids = xoonips_get_all_item_ids_to_export($index_id, $xoopsUser->getVar('uid'), $recursive_item);
        }

        $ids = array_unique($ids);
        $tmp = array();
        $itemtypes = array();
        $res = xnp_get_item_types($tmp);
        foreach ($tmp as $i) {
            $itemtypes[$i['item_type_id']] = $i;
        }

        // split items between required to agree license and no needs.
        // $items['export'] = array( does not required items  );
        // $items['license_required'] = array( required items );
        $items = array();
        foreach ($ids as $i) {
            $item_basic = array();
            $res = xnp_get_item($_SESSION['XNPSID'], $i, $item_basic); //TODO TO FIX THAT XNP_GET_ITEM CAN'T GET ITEM
            if ($res == RES_OK
                && array_key_exists($item_basic['item_type_id'], $itemtypes)
            ) {
                $func_license_required = $itemtypes[$item_basic['item_type_id']]['name'].'GetLicenseRequired';
                $func_license = $itemtypes[$item_basic['item_type_id']]['name'].'GetLicenseStatement';
                $func_html = $itemtypes[$item_basic['item_type_id']]['name'].'GetListBlock';
                $func_export = $itemtypes[$item_basic['item_type_id']]['name'].'ExportItem';
                include_once XOOPS_ROOT_PATH.'/modules/'.$itemtypes[$item_basic['item_type_id']]['viewphp'];
                $license_required = function_exists($func_license_required) ? $func_license_required($i) : false;
                list($license, $use_cc) = function_exists($func_license) ? $func_license($i) : array('', false);
                $html = function_exists($func_html) ? $func_html($item_basic) : '';
                if (!function_exists($func_export) || !export_item_enable($i)) {
                    $key = 'not_export';
                } elseif ($license_required) {
                    $key = 'license_required';
                } else {
                    $key = 'export';
                }
                if (!array_key_exists($key, $items)) {
                    $items[$key] = array();
                }
                $items[$key][] = array('item_id' => $i,
                                        //'license_required' => $license_required,
                                        'license' => ($use_cc ? $license : $textutil->html_special_chars($license)),
                                        'use_cc' => $use_cc,
                                        'detail_html' => $html,
                                        //'export_flag' => function_exists( $func_export )
                );
            }
        }
        if ($index_id != '') {
            $xoopsTpl->assign('index_id', $index_id);
        }
        if (isset($system_message)) {
            $xoopsTpl->assign('system_message', '<span style="color: red;">'.$system_message.'</span>');
        }

        $xoopsTpl->assign('export_type', $export_type);
        $xoopsTpl->assign('item', $items);
        $xoopsTpl->assign('attachment', $attachment);
        if ($export_type == 'item') {
            $xoopsTpl->assign('recursive_item', $recursive_item);
        } elseif ($export_type == 'index') {
            $xoopsTpl->assign('recursive_index', $recursive_index);
        }
        include XOOPS_ROOT_PATH.'/footer.php';
    } else {
        die('unknown export_type');
    }
} elseif ($op == 'config') {
    include XOOPS_ROOT_PATH.'/header.php';

    echo _MD_XOONIPS_EXPORT_PANKUZU_EXPORT;
    echo _MI_XOONIPS_ACCOUNT_PANKUZU_SEPARATOR;
    echo _MD_XOONIPS_EXPORT_PANKUZU_EXPORT_CONFIG;

    // TODO call item type function to get items that the itemtype needs to export.
    // (especially for bidner)
    // foreach $id in $ids do
    //   $itemtype =& (get itemtype object of $id);
    //   $func = $itemtype -> get( 'name' ).'GetExportItemId( $id )';
    //   $ids = arraymerge( $func( $item_id ) );
    // done
    $add_export_ids = array();
    foreach ($ids as $id) {
        $basic_handler = &xoonips_getormhandler('xoonips', 'item_basic');
        $item_type_handler = &xoonips_getormhandler('xoonips', 'item_type');
        $basic = &$basic_handler->get($id);
        $itemtype = &$item_type_handler->get($basic->get('item_type_id'));
        include_once '../'.$itemtype->get('name').'/include/view.php';
        $func = $itemtype->get('name').'GetExportItemId';
        if (!function_exists($func)) {
            continue;
        }
        $add_export_ids = array_merge($add_export_ids, $func($id));
    }
    $ids = array_unique(array_merge($ids, $add_export_ids));

    $yes = _YES;
    $no = _NO;
    $export_type_item_type = $index_id != '' ? 'radio' : 'hidden';
    $export_type_item = _MD_XOONIPS_EXPORT_TYPE_ITEM;
    $export_type_index = _MD_XOONIPS_EXPORT_TYPE_INDEX;
    $export_recursive = _MD_XOONIPS_EXPORT_RECURSIVE;
    $export_attachment = _MD_XOONIPS_EXPORT_ATTACHMENT;
    $submit = _SUBMIT;

    echo <<<EOT
        <form id="export_config" action="export.php" method="post">
        <table id="export_config_table" class="outer">
         <tr>
          <th style="text-align:left;" colspan="2">
           <input type="$export_type_item_type" name="export_type" value="item" checked="checked" />$export_type_item
          </th>
         </tr>
EOT;
    if ($index_id != '') {
        echo <<<EOT
         <tr class="odd">
          <td>
           $export_recursive
          </td>
          <td>
           <input type="radio" name="recursive_item" value="1" checked="checked" />$yes
           <input type="radio" name="recursive_item" value="0" />$no
          </td>
         </tr>
EOT;
    }

    $value = '';
    if (xnp_get_config_value('export_attachment', $value) != RES_OK) {
        $value = 'off';
    }
    if ($value == 'on') {
        echo <<<EOT
        <tr class="even">
         <td>
          $export_attachment
         </td>
         <td>
          <input type="radio" name="attachment" value="1" checked="checked" />$yes
          <input type="radio" name="attachment" value="0" />$no
         </td>
        </tr>
EOT;
    } else {
        echo <<<EOT
        <tr class="even">
         <td>
          $export_attachment
         </td>
         <td>
          <input type="hidden" name="attachment" value="0" />$no
         </td>
        </tr>
EOT;
    }

    if ($index_id != '') {
        echo <<<EOT
        <tr>
         <th style="text-align:left;" colspan="2">
          <input type="radio" name="export_type" value="index" />$export_type_index
         </th>
        </tr>
         <tr class="odd">
          <td>
           $export_recursive
          </td>
          <td>
           <input type="radio" name="recursive_index" value="1" checked="checked" />$yes
           <input type="radio" name="recursive_index" value="0" />$no
          </td>
         </tr>
EOT;
    }
    echo <<<EOT
        </table>
        
        <p style="text-align:center;">
         <input class="formButton" name="submit" type="submit" value="$submit" />
        </p>        
EOT;

    if ($index_id != '') {
        echo '<input type="hidden" name="index_id" value="'.$index_id.'"/>'."\n";
    }

    foreach ($ids as $i) {
        echo '<input type="hidden" name="ids[]" value="'.$i.'"/>'."\n";
    }

    echo <<<EOT
        <input type="hidden" name="op" value="list" />
        </form>
EOT;
    include XOOPS_ROOT_PATH.'/footer.php';
} else {
    die('unknown op');
}

/**
 * xoopsUser has item export permission or not.
 *
 * @param int $item_id id of item to check
 *
 * @return bool true(permitted), false(forbidden)
 */
function export_item_enable($item_id)
{
    global $xoopsUser;
    $handler = &xoonips_getormcompohandler('xoonips', 'item');

    return $handler->getPerm($item_id, $xoopsUser ? $xoopsUser->getVar('uid') : UID_GUEST, 'export');
}

/**
 * create temporary dir and return its path.
 *
 * @return string temprary dir path or false(if failed)
 */
function xoonips_create_temporary_dir()
{
    $tmpfile = tempnam('/tmp', 'XNP');
    unlink($tmpfile);
    $tmpdir = $tmpfile.'D'; //folder zipped files to be stored.
    if (!mkdir($tmpdir)) {
        return false;
    }

    return $tmpdir;
}

/**
 * convert file encoding.
 *
 * @param $infile input file path(any encoindg)
 * @param $outfile output file path(UTF-8)
 *
 * @return bool true(succeed) or false(failed)
 */
function xoonips_convert_file_encoding_to_utf8($tmpfile, $filename)
{
    $fp_r = fopen($tmpfile, 'r');
    $fp_w = fopen($filename, 'w');
    if (!$fp_r || !$fp_w) {
        unlink($filename);
        if ($fp_r) {
            fclose($fp_r);
        }
        if ($fp_w) {
            fclose($fp_w);
        }

        return false;
    }
    while (!feof($fp_r)) {
        $unicode = &xoonips_getutility('unicode');
        fputs($fp_w, $unicode->encode_utf8(fgets($fp_r, 131072), xoonips_get_server_charset()));
    }
    fclose($fp_r);
    fclose($fp_w);

    return true;
}

/**
 * export index tree XML to file.
 *
 * @param $fp file handle
 * @param $index_id integer index id to export
 * @param $recursive bool true if recursive export
 */
function xoonips_export_index_xml($fp, $index_id, $recursive)
{
    return fwrite($fp, "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n")
        && fwrite($fp, "<indexes>\n")
        && xnpExportIndex($fp, $index_id, $recursive)
        && fwrite($fp, "</indexes>\n");
}

/**
 * find all item ids in the index of $index_id.
 * if $recursive_item is given.
 *
 * @param int  $index_id       index id to export
 * @param int  $index_id       user id to export
 * @param bool $recursive_item true(recursive) or false(not recursive)
 *
 * @return array integer item id(s)
 */
function xoonips_get_all_item_ids_to_export($index_id, $uid, $recursive_item = false)
{
    $ids = array();
    $tmp_idx = array($index_id);
    while (count($tmp_idx) > 0) {
        $i = array_shift($tmp_idx);

        $index_item_link_handler = &xoonips_getormhandler('xoonips', 'index_item_link');
        $index_item_links = $index_item_link_handler->getByIndexId($i, $uid);
        foreach ($index_item_links as $link) {
            $ids[] = $link->get('item_id');
        }

        if (!$recursive_item) {
            return $ids;
        }

        $child = array();
        $res = xnp_get_indexes($_SESSION['XNPSID'], $i, array(), $child);
        if ($res == RES_OK) {
            foreach ($child as $c) {
                array_push($tmp_idx, $c['item_id']);
            }
        }
    }

    return $ids;
}
