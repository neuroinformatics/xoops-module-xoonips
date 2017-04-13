<?php

// $Revision: 1.1.4.1.2.12 $
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
require '../include/common.inc.php';

// remove ob filters
$handlers = ob_list_handlers();
while (!empty($handlers)) {
    ob_end_clean();
    $handlers = ob_list_handlers();
}

$langman = &xoonips_getutility('languagemanager');
$langman->read('main.php', 'xoonips');

$error_mode = 'log';

$text = 'Button';
$text_shadow = false;
$font_size = 8;
$font_angle = 0;
$font_color = array(0, 0, 0);
$font_file = 'default.ttf';
$padding_x = 10;
$padding_y = 6;
$background_image_file = 'icon_button_normal.png';

// get params
(method_exists(MyTextSanitizer, sGetInstance) and $myts = &MyTextSanitizer::sGetInstance()) || $myts = &MyTextSanitizer::getInstance();
$formdata = &xoonips_getutility('formdata');
$mode = $formdata->getValue('get', 'mode', 'n', false, $background_image_file);
switch ($mode) {
case 'normal':
    $background_image_file = 'icon_button_normal.png';
    break;
case 'down':
    $background_image_file = 'icon_button_down.png';
    break;
case 'over':
    $background_image_file = 'icon_button_over.png';
    break;
case 'focus':
    $background_image_file = 'icon_button_focus.png';
    break;
}
$label = $formdata->getValue('get', 'label', 'n', false);
if (!is_null($label)) {
    switch ($label) {
    case 'download':
        $text = _MD_XOONIPS_ITEM_DOWNLOAD_LABEL;
        break;
    }
}

$background_image_path = __DIR__.'/'.$background_image_file;

// functions
function fatal_error($message)
{
    global $error_mode;
    if ($error_mode == 'log') {
        error_log($message);
        die();
    } else {
        die($message);
    }
}

$unicode = &xoonips_getutility('unicode');
$text_utf8 = $unicode->encode_utf8($text, _CHARSET);

// error check
if (!extension_loaded('gd')) {
    fatal_error('PHP GD extension does not loaded');
}
$gdinfo = gd_info();
$gd_required = array(
    'FreeType Support',
    'PNG Support',
);
foreach ($gd_required as $req_type) {
    if (!$gdinfo[$req_type]) {
        fatal_error('GD : '.$req_type.' disabled');
    }
}
if (!file_exists($background_image_path)) {
    fatal_error('background image file not found : '.$background_iamge_file);
}
$font_path = $langman->font_path($font_file);

// calculate drawing image size
$text_bbox = imagettfbbox($font_size, $font_angle, $font_path, $text_utf8);
$text_xmax = max($text_bbox[0], $text_bbox[2], $text_bbox[4], $text_bbox[6]);
$text_xmin = min($text_bbox[0], $text_bbox[2], $text_bbox[4], $text_bbox[6]);
$text_ymax = max($text_bbox[1], $text_bbox[3], $text_bbox[5], $text_bbox[7]);
$text_ymin = min($text_bbox[1], $text_bbox[3], $text_bbox[5], $text_bbox[7]);
$text_width = $text_xmax - $text_xmin;
$text_height = $text_ymax - $text_ymin;
$image_width = $text_width + $padding_x * 2;
$image_height = $text_height + $padding_y * 2;
$x = $padding_x - $text_xmin + (($text_xmin == $text_bbox[0]) ? 0 : $text_bbox[0]);
$y = $image_height - $padding_y - $text_ymax + (($text_ymax == $text_bbox[1]) ? 0 : $text_bbox[1]);

// create image resource
$im = imagecreatetruecolor($image_width, $image_height);

// change alpha attributes and create transparent color
imageantialias($im, true);
imagealphablending($im, false);
imagesavealpha($im, true);
$transparent = imagecolorallocatealpha($im, 255, 255, 255, 0);
// fill all area with transparent color
imagefill($im, 0, 0, $transparent);

// stretch and copy background image
list($imbg_width, $imbg_height) = getimagesize($background_image_path);
$imbg = imagecreatefrompng($background_image_path);
if (!imagecopyresampled($im, $imbg, 0, 0, 0, 0, $image_width, $image_height, $imbg_width, $imbg_height)) {
    fatal_error('imagecopyresampled() failed');
}
imagedestroy($imbg);

// draw text
imagealphablending($im, true);
$font_color = imagecolorallocate($im, $font_color[0], $font_color[1], $font_color[2]);
if ($text_shadow) {
    // shadow
    $shadow_color = imagecolorallocate($im, 128, 128, 128);
    imagettftext($im, $font_size, $font_angle, $x + 1, $y + 1, $shadow_color, $font_path, $text_utf8);
}
imagettftext($im, $font_size, $font_angle, $x, $y, $font_color, $font_path, $text_utf8);

// display
header('Content-type: image/png');
imagepng($im);

// cleanup
imagedestroy($im);
