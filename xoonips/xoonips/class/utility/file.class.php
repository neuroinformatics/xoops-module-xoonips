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
defined('XOOPS_ROOT_PATH') || exit('XOOPS root path not defined');

/**
 * file utlities.
 *
 * @copyright copyright &copy; 2005-2009 RIKEN Japan
 */
class XooNIpsUtilityFile extends XooNIpsUtility
{
    /**
     * magic file path.
     *
     * @var string
     */
    public $magic_file_path;

    /**
     * map of mime type.
     *
     * @var array
     */
    public $mimetype_map = array(
    '' => array(
      'dtd' => 'application/xml-dtd',
      'jnlp' => 'application/x-java-jnlp-file',
      'html' => 'text/html',
      'xhtml' => 'application/xhtml+xml',
      'xml' => 'application/xml',
      'xsl' => 'application/xml',
    ),
    'application/msword' => array(
      'ppt' => 'application/vnd.ms-powerpoint',
      'xls' => 'application/vnd.ms-excel',
    ),
    'application/x-zip' => array(
      'jar' => 'x-java-archive',
      'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
      'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
      'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
      'odc' => 'application/vnd.oasis.opendocument.chart',
      'odb' => 'application/vnd.oasis.opendocument.database',
      'odf' => 'application/vnd.oasis.opendocument.formula',
      'odg' => 'application/vnd.oasis.opendocument.graphics',
      'otg' => 'application/vnd.oasis.opendocument.graphics-template',
      'odi' => 'application/vnd.oasis.opendocument.image',
      'odp' => 'application/vnd.oasis.opendocument.presentation',
      'otp' => 'application/vnd.oasis.opendocument.presentation-template',
      'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
      'ots' => 'application/vnd.oasis.opendocument.spreadsheet-template',
      'odt' => 'application/vnd.oasis.opendocument.text',
      'odm' => 'application/vnd.oasis.opendocument.text-master',
      'ott' => 'application/vnd.oasis.opendocument.text-template',
      'oth' => 'application/vnd.oasis.opendocument.text-web',
      'sxw' => 'application/vnd.sun.xml.writer',
      'stw' => 'application/vnd.sun.xml.writer.template',
      'sxc' => 'application/vnd.sun.xml.calc',
      'stc' => 'application/vnd.sun.xml.calc.template',
      'sxd' => 'application/vnd.sun.xml.draw',
      'std' => 'application/vnd.sun.xml.draw.template',
      'sxi' => 'application/vnd.sun.xml.impress sxi',
      'sti' => 'application/vnd.sun.xml.impress.template',
      'sxg' => 'application/vnd.sun.xml.writer.global',
      'sxm' => 'application/vnd.sun.xml.math',
    ),
    'application/octet-stream' => array(
      'wmv' => 'video/x-ms-wmv',
    ),
    'text/html' => array(
      'css' => 'text/css',
      'dtd' => 'application/xml-dtd',
      'sgml' => 'text/sgml',
      'sgm' => 'text/sgml',
      'xml' => 'application/xml',
      'xsl' => 'application/xml',
    ),
    'text/plain' => array(
      'c' => 'text/x-c',
      'cc' => 'text/x-c++',
      'cpp' => 'text/x-c++',
      'css' => 'text/css',
      'cxx' => 'text/x-c++',
      'dtd' => 'application/xml-dtd',
      'htm' => 'text/html',
      'html' => 'text/html',
      'js' => 'application/x-javascript',
      'php' => 'text/html',
      'sh' => 'application/x-shellscript',
      'sgml' => 'text/sgml',
      'sgm' => 'text/sgml',
      'tex' => 'application/x-tex',
      'xml' => 'application/xml',
      'xsl' => 'application/xml',
    ),
    'text/x-c' => array(
      'cc' => 'text/x-c++',
      'cpp' => 'text/x-c++',
      'css' => 'text/css',
      'cxx' => 'text/x-c++',
      'dtd' => 'application/xml-dtd',
      'htm' => 'text/html',
      'html' => 'text/html',
      'js' => 'application/x-javascript',
      'php' => 'text/html',
      'sgml' => 'text/sgml',
      'sgm' => 'text/sgml',
      'xml' => 'application/xml',
      'xsl' => 'application/xml',
    ),
    'text/x-c++' => array(
      'c' => 'text/x-c',
      'css' => 'text/css',
      'dtd' => 'application/xml-dtd',
      'htm' => 'text/html',
      'html' => 'text/html',
      'js' => 'application/x-javascript',
      'php' => 'text/html',
      'sgml' => 'text/sgml',
      'sgm' => 'text/sgml',
      'xml' => 'application/xml',
      'xsl' => 'application/xml',
    ),
    );

    /**
     * constructor.
     */
    public function __construct()
    {
        $this->setSingleton();
        $xconfig_handler = &xoonips_getormhandler('xoonips', 'config');
        $this->magic_file_path = $xconfig_handler->getValue('magic_file_path');
        // append additional mimetype mapping
        $this->mimetype_map['application/zip'] = $this->mimetype_map['application/x-zip'];
    }

    /**
     * get file mime type.
     *
     * @param string $file_path local file path
     * @param string $file_name original file name
     *
     * @return string mime type
     */
    public function get_mimetype($file_path, $file_name)
    {
        // get file inforamation
        if (extension_loaded('fileinfo')) {
            if ($this->magic_file_path == '') {
                $finfo = @finfo_open(FILEINFO_MIME);
            } else {
                $finfo = @finfo_open(FILEINFO_MIME, $this->magic_file_path);
            }
            if (!$finfo) {
                return false;
            }
            $mimetype = finfo_file($finfo, $file_path);
            finfo_close($finfo);
        } else {
            // try to use mime_content_type()
            $mimetype = mime_content_type($file_path);
        }
        // trim additional information
        $mimetype = preg_replace('/[,; ].*$/', '', $mimetype);
        // get original extension
        $pathi = pathinfo($file_name);
        $ext = isset($pathi['extension']) ? $pathi['extension'] : '';
        // override mimetype
        if ($ext != '' && isset($this->mimetype_map[$mimetype][$ext])) {
            $mimetype = $this->mimetype_map[$mimetype][$ext];
        }
        if ($mimetype == '') {
            // fail safe
            $mimetype = 'application/octet-stream';
        }

        return $mimetype;
    }

    /**
     * get thumbnail data.
     *
     * @param string $file_path file path
     * @param string $mimetype  mime type of file
     *
     * @return string created thumbnail
     */
    public function get_thumbnail($file_path, $mimetype)
    {
        $image_id = '';
        switch ($mimetype) {
        case 'image/png':
            $image_id = @imagecreatefrompng($file_path);
            break;
        case 'image/gif':
            $image_id = @imagecreatefromgif($file_path);
            break;
        case 'image/jpeg':
            $image_id = @imagecreatefromjpeg($file_path);
            break;
        }
        if ($image_id === '') {
            return null;
        }
        $width = imagesx($image_id);
        $height = imagesy($image_id);
        // maximum file size in thumbnail
        $max_width = 100;
        $max_height = 100;
        if ($max_width < $width || $max_height < $height) {
            // If size of image file is too large, need to reduce it.
            $scale_x = $max_width / $width;
            $scale_y = $max_height / $height;
            $scale = min($scale_x, $scale_y);
            $new_width = round($width * $scale);
            $new_height = round($height * $scale);
            // resize
            $new_image_id = imagecreatetruecolor($new_width, $new_height);
            $result = imagecopyresampled($new_image_id, $image_id, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
            imagedestroy($image_id);
            $image_id = $new_image_id;
            $width = $new_width;
            $height = $new_height;
        }
        $tmpfile = tempnam('/tmp', 'XooNIpsThumbnail');
        if ($tmpfile === false) {
            return null;
        }
        @unlink($tmpfile);
        $result = imagepng($image_id, $tmpfile);
        imagedestroy($image_id);
        if ($result == false) {
            return null;
        }
        $thumbnail = file_get_contents($tmpfile);
        @unlink($tmpfile);

        return $thumbnail;
    }
}
