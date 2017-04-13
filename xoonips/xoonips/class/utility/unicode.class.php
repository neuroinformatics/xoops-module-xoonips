<?php

// $Revision: 1.1.2.6 $
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
if (!defined('XOOPS_ROOT_PATH')) {
    exit();
}

/**
 * character set handling singleton class.
 *
 * @packpage xoonips_utility
 *
 * @copyright copyright &copy; 2008 RIKEN Japan
 */
class XooNIpsUtilityUnicode extends XooNIpsUtility
{
    /**
     * unicode mapping.
     *
     * @var array
     */
    public $unicode_map = array();

    /**
     * constructor.
     */
    public function __construct()
    {
        $this->setSingleton();
    }

    /**
     * convert encoding with HTML numeric entities or url encoded string.
     *
     * @param string $str           input utf8 string
     * @param string $to_encoding   output encoding
     * @param string $fallback      unmapped character encoding method
     *                              'h' : encode to HTML numeric entities
     *                              'u' : encode to UTF-8 based url string
     * @param string $from_encoding encoding of source string
     *
     * @return string converted string
     */
    public function convert_encoding($str, $to_encoding, $fallback, $from_encoding = '')
    {
        $utf8 = $this->encode_utf8($str, $from_encoding);

        return $this->decode_utf8($utf8, $to_encoding, $fallback);
    }

    /**
     * decode utf8 string to other encoding with HTML numeric entities or url
     * encoded string.
     *
     * @param string $str         input utf8 string
     * @param string $to_encoding output encoding
     * @param string $fallback    unmapped character encoding method
     *                            'h' : encode to HTML numeric entities
     *                            'u' : encode to UTF-8 based url string
     *
     * @return string converted string
     */
    public function decode_utf8($str, $to_encoding, $fallback)
    {
        // convert to output encoding
        switch ($to_encoding) {
        case 'SJIS-win':
        case 'Shift_JIS':
            $ret = $this->_utf8_to_charset($str, 'CP932', $fallback);
            break;
        case 'ISO-8859-1':
            $ret = $this->_utf8_to_charset($str, '8859-1', $fallback);
            break;
        case 'Windows-1252':
            $ret = $this->_utf8_to_charset($str, 'CP1252', $fallback);
            break;
        case 'UTF-8':
            $ret = $str;
            break;
        case 'EUC-JP':
        case 'eucJP-win':
            $tmp = $this->_utf8_to_charset($str, 'CP932', $fallback);
            $ret = @mb_convert_encoding($tmp, 'eucJP-win', 'SJIS-win');
            if ($ret === false) {
                // 'eucJP-win' and 'SJIS-win' are unsupported
                $ret = mb_convert_encoding($tmp, 'EUC-JP', 'Shift_JIS');
            }
            break;
        case 'ASCII':
        default:
            $ret = $this->_utf8_to_charset($str, 'US-ASCII', $fallback);
            break;
        }

        return $ret;
    }

    /**
     * convert encoding to UTF-8.
     *
     * @param string $str           input string
     * @param string $from_encoding encoding of source string
     *
     * @return string converted UTF-8 string
     */
    public function encode_utf8($str, $from_encoding = '')
    {
        // detect from encoding
        if (empty($from_encoding)) {
            $from_encoding = mb_detect_encoding($str);
        }
        if ($from_encoding == 'UTF-8') {
            return $str;
            // nothing to do
        }
        // convert to UTF-8
        return mb_convert_encoding($str, 'UTF-8', $from_encoding);
    }

    /**
     * convert encoding from UTF-8 to each charset with HTML numeric entities.
     *
     * @param string $utf8     input UTF-8 string
     * @param string $charset  output encoding
     * @param string $fallback unmapped character encoding method
     *                         'h' : encode to HTML numeric entities
     *                         'u' : encode to UTF-8 based url string
     *
     * @return string converted string
     */
    public function _utf8_to_charset($utf8, $charset, $fallback)
    {
        if (!$this->_load_charset($charset)) {
            return '';
        }
        $chars = unpack('C*', $utf8);
        $cnt = count($chars);
        $res = '';
        for ($i = 1; $i <= $cnt; ++$i) {
            $res .= $this->_to_char($chars, $i, $charset, $fallback);
        }

        return $res;
    }

    /**
     * load character set mapping file.
     *
     * @param string $charset name of character set
     *
     * @return bool false if failure
     */
    public function _load_charset($charset)
    {
        if (isset($this->unicode_map[$charset])) {
            return true;
            // already loaded
        }
        $include_path = dirname(dirname(__DIR__)).'/include';
        $mapfile_path = $include_path.'/unicode/'.$charset.'.TXT';
        $lines = @file_get_contents($mapfile_path);
        if (empty($lines)) {
            error_log('Failed to read character set : '.$charset);

            return false;
            // map file not found
        }
        $lines = preg_replace('/#.*$/m', '', $lines);
        // skip comment line
        $lines = preg_replace("/\n\n/m", '', $lines);
        // skip empty line
        $lines = explode("\n", $lines);
        foreach ($lines as $line) {
            if (preg_match('/^0x([A-Fa-f0-9]{1,4})\\s+0x([A-Fa-f0-9]{1,4})\\s*$/', $line, $parts)) {
                $asc = hexdec($parts[1]);
                $unicode = hexdec($parts[2]);
                $this->unicode_map[$charset][$unicode] = $asc;
            }
        }

        return true;
    }

    /**
     * return specific multibyte character.
     *
     * @param int $num character code
     *
     * @return string multibyte character
     */
    public function _my_chr($num)
    {
        return($num < 256) ? chr($num) : $this->_my_chr(intval($num / 256)).chr($num % 256);
    }

    /**
     * return UTF-8 mapped multibyte character.
     *
     * @param array  $chars    unicode characters
     * @param int    $idx      character index of $chars array
     * @param string $charset  conversion character set
     * @param string $fallback unmapped character encoding method
     *                         'h' : encode to HTML numeric entities
     *                         'u' : encode to UTF-8 based url string
     *
     * @return string multibyte character
     */
    public function _to_char(&$chars, &$idx, $charset, $fallback)
    {
        $idx_orig = $idx;
        // get unicode
        if (($chars[$idx] >= 240) && ($chars[$idx] <= 255)) {
            // 4 bytes
            $unicode = (intval($chars[$idx] - 240) << 18) + (intval($chars[++$idx] - 128) << 12) + (intval($chars[++$idx] - 128) << 6) + (intval($chars[++$idx] - 128));
        } elseif (($chars[$idx] >= 224) && ($chars[$idx] <= 239)) {
            // 3 bytes
            $unicode = (intval($chars[$idx] - 224) << 12) + (intval($chars[++$idx] - 128) << 6) + (intval($chars[++$idx] - 128));
        } elseif (($chars[$idx] >= 192) && ($chars[$idx] <= 223)) {
            // 2 bytes
            $unicode = (intval($chars[$idx] - 192) << 6) + (intval($chars[++$idx] - 128));
        } else {
            // 1 bytes
            $unicode = $chars[$idx];
        }
        // get unicode mapped string
        if (isset($this->unicode_map[$charset][$unicode])) {
            return $this->_my_chr($this->unicode_map[$charset][$unicode]);
        }
        // unmapped character encoding
        if ($fallback == 'h') {
            return '&#'.$unicode.';';
        } elseif ($fallback == 'u') {
            $utf8 = '';
            for ($i = $idx_orig; $i <= $idx; ++$i) {
                $utf8 .= chr($chars[$i]);
            }

            return urlencode($utf8);
        }
        // else ignore
        return '';
    }
}
