<?php

// $Revision: 1.1.2.13 $
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
 * text utlities.
 *
 * @copyright copyright &copy; 2005-2009 RIKEN Japan
 */
class XooNIpsUtilityText extends XooNIpsUtility
{
    /**
     * html character entity references.
     *
     * @var array strings of html character entity reference
     */
    public $_html_char_entity_ref = array(
        '&quot;',     '&amp;',      '&apos;',     '&lt;',       '&gt;',
        '&nbsp;',     '&iexcl;',    '&cent;',     '&pound;',    '&curren;',
        '&yen;',      '&brvbar;',   '&sect;',     '&uml;',      '&copy;',
        '&ordf;',     '&laquo;',    '&not;',      '&shy;',      '&reg;',
        '&macr;',     '&deg;',      '&plusmn;',   '&sup2;',     '&sup3;',
        '&acute;',    '&micro;',    '&para;',     '&middot;',   '&cedil;',
        '&sup1;',     '&ordm;',     '&raquo;',    '&frac14;',   '&frac12;',
        '&frac34;',   '&iquest;',   '&Agrave;',   '&Aacute;',   '&Acirc;',
        '&Atilde;',   '&Auml;',     '&Aring;',    '&AElig;',    '&Ccedil;',
        '&Egrave;',   '&Eacute;',   '&Ecirc;',    '&Euml;',     '&Igrave;',
        '&Iacute;',   '&Icirc;',    '&Iuml;',     '&ETH;',      '&Ntilde;',
        '&Ograve;',   '&Oacute;',   '&Ocirc;',    '&Otilde;',   '&Ouml;',
        '&times;',    '&Oslash;',   '&Ugrave;',   '&Uacute;',   '&Ucirc;',
        '&Uuml;',     '&Yacute;',   '&THORN;',    '&szlig;',    '&agrave;',
        '&aacute;',   '&acirc;',    '&atilde;',   '&auml;',     '&aring;',
        '&aelig;',    '&ccedil;',   '&egrave;',   '&eacute;',   '&ecirc;',
        '&euml;',     '&igrave;',   '&iacute;',   '&icirc;',    '&iuml;',
        '&eth;',      '&ntilde;',   '&ograve;',   '&oacute;',   '&ocirc;',
        '&otilde;',   '&ouml;',     '&divide;',   '&oslash;',   '&ugrave;',
        '&uacute;',   '&ucirc;',    '&uuml;',     '&yacute;',   '&thorn;',
        '&yuml;',     '&OElig;',    '&oelig;',    '&Scaron;',   '&scaron;',
        '&Yuml;',     '&fnof;',     '&circ;',     '&tilde;',    '&Alpha;',
        '&Beta;',     '&Gamma;',    '&Delta;',    '&Epsilon;',  '&Zeta;',
        '&Eta;',      '&Theta;',    '&Iota;',     '&Kappa;',    '&Lambda;',
        '&Mu;',       '&Nu;',       '&Xi;',       '&Omicron;',  '&Pi;',
        '&Rho;',      '&Sigma;',    '&Tau;',      '&Upsilon;',  '&Phi;',
        '&Chi;',      '&Psi;',      '&Omega;',    '&alpha;',    '&beta;',
        '&gamma;',    '&delta;',    '&epsilon;',  '&zeta;',     '&eta;',
        '&theta;',    '&iota;',     '&kappa;',    '&lambda;',   '&mu;',
        '&nu;',       '&xi;',       '&omicron;',  '&pi;',       '&rho;',
        '&sigmaf;',   '&sigma;',    '&tau;',      '&upsilon;',  '&phi;',
        '&chi;',      '&psi;',      '&omega;',    '&thetasym;', '&upsih;',
        '&piv;',      '&ensp;',     '&emsp;',     '&thinsp;',   '&zwnj;',
        '&zwj;',      '&lrm;',      '&rlm;',      '&ndash;',    '&mdash;',
        '&lsquo;',    '&rsquo;',    '&sbquo;',    '&ldquo;',    '&rdquo;',
        '&bdquo;',    '&dagger;',   '&Dagger;',   '&bull;',     '&hellip;',
        '&permil;',   '&prime;',    '&Prime;',    '&lsaquo;',   '&rsaquo;',
        '&oline;',    '&frasl;',    '&euro;',     '&image;',    '&weierp;',
        '&real;',     '&trade;',    '&alefsym;',  '&larr;',     '&uarr;',
        '&rarr;',     '&darr;',     '&harr;',     '&crarr;',    '&lArr;',
        '&uArr;',     '&rArr;',     '&dArr;',     '&hArr;',     '&forall;',
        '&part;',     '&exist;',    '&empty;',    '&nabla;',    '&isin;',
        '&notin;',    '&ni;',       '&prod;',     '&sum;',      '&minus;',
        '&lowast;',   '&radic;',    '&prop;',     '&infin;',    '&ang;',
        '&and;',      '&or;',       '&cap;',      '&cup;',      '&int;',
        '&there4;',   '&sim;',      '&cong;',     '&asymp;',    '&ne;',
        '&equiv;',    '&le;',       '&ge;',       '&sub;',      '&sup;',
        '&nsub;',     '&sube;',     '&supe;',     '&oplus;',    '&otimes;',
        '&perp;',     '&sdot;',     '&lceil;',    '&rceil;',    '&lfloor;',
        '&rfloor;',   '&lang;',     '&rang;',     '&loz;',      '&spades;',
        '&clubs;',    '&hearts;',   '&diams;',
    );

    /**
     * html numeric character references.
     *
     * @var array strings of html numeric character reference
     */
    public $_html_numeric_char_ref = array(
        '&#34;',   '&#38;',   '&#39;',   '&#60;',   '&#62;',
        '&#160;',  '&#161;',  '&#162;',  '&#163;',  '&#164;',
        '&#165;',  '&#166;',  '&#167;',  '&#168;',  '&#169;',
        '&#170;',  '&#171;',  '&#172;',  '&#173;',  '&#174;',
        '&#175;',  '&#176;',  '&#177;',  '&#178;',  '&#179;',
        '&#180;',  '&#181;',  '&#182;',  '&#183;',  '&#184;',
        '&#185;',  '&#186;',  '&#187;',  '&#188;',  '&#189;',
        '&#190;',  '&#191;',  '&#192;',  '&#193;',  '&#194;',
        '&#195;',  '&#196;',  '&#197;',  '&#198;',  '&#199;',
        '&#200;',  '&#201;',  '&#202;',  '&#203;',  '&#204;',
        '&#205;',  '&#206;',  '&#207;',  '&#208;',  '&#209;',
        '&#210;',  '&#211;',  '&#212;',  '&#213;',  '&#214;',
        '&#215;',  '&#216;',  '&#217;',  '&#218;',  '&#219;',
        '&#220;',  '&#221;',  '&#222;',  '&#223;',  '&#224;',
        '&#225;',  '&#226;',  '&#227;',  '&#228;',  '&#229;',
        '&#230;',  '&#231;',  '&#232;',  '&#233;',  '&#234;',
        '&#235;',  '&#236;',  '&#237;',  '&#238;',  '&#239;',
        '&#240;',  '&#241;',  '&#242;',  '&#243;',  '&#244;',
        '&#245;',  '&#246;',  '&#247;',  '&#248;',  '&#249;',
        '&#250;',  '&#251;',  '&#252;',  '&#253;',  '&#254;',
        '&#255;',  '&#338;',  '&#339;',  '&#352;',  '&#353;',
        '&#376;',  '&#402;',  '&#710;',  '&#732;',  '&#913;',
        '&#914;',  '&#915;',  '&#916;',  '&#917;',  '&#918;',
        '&#919;',  '&#920;',  '&#921;',  '&#922;',  '&#923;',
        '&#924;',  '&#925;',  '&#926;',  '&#927;',  '&#928;',
        '&#929;',  '&#931;',  '&#932;',  '&#933;',  '&#934;',
        '&#935;',  '&#936;',  '&#937;',  '&#945;',  '&#946;',
        '&#947;',  '&#948;',  '&#949;',  '&#950;',  '&#951;',
        '&#952;',  '&#953;',  '&#954;',  '&#955;',  '&#956;',
        '&#957;',  '&#958;',  '&#959;',  '&#960;',  '&#961;',
        '&#962;',  '&#963;',  '&#964;',  '&#965;',  '&#966;',
        '&#967;',  '&#968;',  '&#969;',  '&#977;',  '&#978;',
        '&#982;',  '&#8194;', '&#8195;', '&#8201;', '&#8204;',
        '&#8205;', '&#8206;', '&#8207;', '&#8211;', '&#8212;',
        '&#8216;', '&#8217;', '&#8218;', '&#8220;', '&#8221;',
        '&#8222;', '&#8224;', '&#8225;', '&#8226;', '&#8230;',
        '&#8240;', '&#8242;', '&#8243;', '&#8249;', '&#8250;',
        '&#8254;', '&#8260;', '&#8364;', '&#8465;', '&#8472;',
        '&#8476;', '&#8482;', '&#8501;', '&#8592;', '&#8593;',
        '&#8594;', '&#8595;', '&#8596;', '&#8629;', '&#8656;',
        '&#8657;', '&#8658;', '&#8659;', '&#8660;', '&#8704;',
        '&#8706;', '&#8707;', '&#8709;', '&#8711;', '&#8712;',
        '&#8713;', '&#8715;', '&#8719;', '&#8721;', '&#8722;',
        '&#8727;', '&#8730;', '&#8733;', '&#8734;', '&#8736;',
        '&#8743;', '&#8744;', '&#8745;', '&#8746;', '&#8747;',
        '&#8756;', '&#8764;', '&#8773;', '&#8776;', '&#8800;',
        '&#8801;', '&#8804;', '&#8805;', '&#8834;', '&#8835;',
        '&#8836;', '&#8838;', '&#8839;', '&#8853;', '&#8855;',
        '&#8869;', '&#8901;', '&#8968;', '&#8969;', '&#8970;',
        '&#8971;', '&#9001;', '&#9002;', '&#9674;', '&#9824;',
        '&#9827;', '&#9829;', '&#9830;',
    );

    /**
     * constructor.
     */
    public function __construct()
    {
        $this->setSingleton();
    }

    /**
     * convert plain text to url/email link tag embeded text.
     *
     * @param string $text src
     *
     * @return string dist
     */
    public function convert_link($text)
    {
        static $pattern = null;
        static $replace = null;
        if (is_null($pattern)) {
            // patterns
            $_numalpha = '[a-zA-Z0-9]';
            $_hex = '[a-fA-F0-9]';
            $_escape = '%'.$_hex.'{2}';
            $_safe = '[\\$\\-_\\.+]';
            $_extra = '[!\\*\'\\(\\),]';
            // $_unreserved = '('.$_numalpha.'|'.$_safe.'|'.$_extra.')';
            $_unreserved = '(?:'.$_numalpha.'|'.$_safe.')';
            $_uchar = '(?:'.$_unreserved.'|'.$_escape.')';
            $_hsegment = '(?:'.$_uchar.'|[;:@&=])*';
            $_search = '(?:'.$_uchar.'|[;:@&=])*';
            $_hpath = '~?'.$_hsegment.'(\\/'.$_hsegment.')*';
            $_domain = '(?:'.$_numalpha.'+(?:[\\.\\-]'.$_numalpha.'+)*)+\\.[a-zA-Z]{2,}';
            $_hostport = $_domain.'(?::[0-9]+)?';
            // email
            $pattern['email'] = '/[a-zA-Z0-9]+(?:[_\\.\\-][a-zA-Z0-9]+)*@'.$_domain.'/';
            $replace['email'] = '_convert_link_email';
            // url
            $pattern['url'] = '/(?:http|https|ftp):\\/\\/'.$_hostport.'(?:\\/'.$_hpath.'(?:\\?'.$_search.')?)?/';
            $replace['url'] = '<a href="\\0" target="_blank">\\0</a>';
        }
        $text = preg_replace($pattern['url'], $replace['url'], $text);
        $text = preg_replace_callback($pattern['email'], array($this, $replace['email']), $text);

        return $text;
    }

    /**
     * helper funtion for convert_link().
     *
     * @param array $m match condition
     *
     * @return string
     */
    public function _convert_link_email($m)
    {
        return $this->mail_to($m[0], $m[0]);
    }

    /**
     * truncate text.
     *
     * @param string $text   src
     * @param int    $length maximum char width
     * @param string $etc    appending text if $text truncated
     *
     * @return string dist
     */
    public function truncate($text, $length, $etc = '...')
    {
        // multi language extension support - strip ml tags
        if (defined('XOOPS_CUBE_LEGACY')) {
            // cubeutil module
            if (isset($GLOBALS['cubeUtilMlang'])) {
                $text = $GLOBALS['cubeUtilMlang']->obFilter($text);
            }
        } else {
            // sysutil module
            if (function_exists('sysutil_get_xoops_option')) {
                if (sysutil_get_xoops_option('sysutil', 'sysutil_use_ml')) {
                    if (function_exists('sysutil_ml_filter')) {
                        $text = sysutil_ml_filter($text);
                    }
                }
            }
        }

        $olen = strlen($text);
        // trim width
        if (XOOPS_USE_MULTIBYTES) {
            $text = mb_strimwidth($text, 0, $length, '', mb_detect_encoding($text));
        } else {
            $text = substr($text, 0, $length);
        }
        // remove broken html entity from trimed strig
        $text = preg_replace('/&#[^;]*$/s', '', $text);
        // append $etc char if text is trimed
        if ($olen != strlen($text)) {
            $text .= $etc;
        }

        return $text;
    }

    /**
     * create html with javascript encoded mailto link.
     *
     * @param string $text  text body
     * @param string $email mail address
     *
     * @return string created html
     */
    public function mail_to($text, $email)
    {
        $mailto = $this->_make_javascript('<a href="mailto:'.$email.'">');
        $mailto .= $text;
        $mailto .= $this->_make_javascript('</a>');

        return $mailto;
    }

    /**
     * convert text to numeric entities.
     *
     * @param string $text input string
     *
     * @return string converted string
     */
    public function html_numeric_entities($text)
    {
        return str_replace($this->_html_char_entity_ref, $this->_html_numeric_char_ref, $text);
    }

    /**
     * escape html special characters
     * this function will convert text to follow some rules:
     * - '&' => '&amp;'
     * - '"' => '&quot;'
     * - ''' => '&#039;'
     * - '<' => '&lt;'
     * - '>' => '&gt;'
     * - numeric entity reference => (pass)
     * - character entity reference => (pass)
     * - '&nbsp;' => '&amp;nbsp;'.
     *
     * @param string $text text string
     *
     * @return string escaped text string
     */
    public function html_special_chars($text)
    {
        static $s = array(
            '/&amp;#([xX][0-9a-fA-F]+|[0-9]+);/',
            '/&nbsp;/',
        );
        static $r = array(
            '&#\\1;',
            '&amp;nbsp;',
        );

        $text = preg_replace($s, $r, htmlspecialchars($text, ENT_QUOTES));

        return preg_replace_callback('/&amp;([a-zA-Z][0-9a-zA-Z]+);/', array($this, '_html_special_chars_char_entity'), $text);
    }

    /**
     * helper funtion for html_special_chars().
     *
     * @param array $m match condition
     *
     * @return string
     */
    public function _html_special_chars_char_entity($m)
    {
        return in_array('&'.$m[1].';', $this->_html_char_entity_ref) ? '&'.$m[1].';' : '&amp;'.$m[1].';';
    }

    /**
     * convert text to UTF-8 string with predefined five xml entitities
     * - predefined five xml entities are: &amp; &lt; &gt; &apos; &quot;.
     *
     * @param string $text input string
     * @param string $enc  text encoding
     *
     * @return string UTF-8 string with predefined five xml entities
     */
    public function xml_special_chars($text, $enc = '')
    {
        $unicode = &xoonips_getutility('unicode');
        $text = $unicode->encode_utf8($text, $enc);
        // html character entity reference to html numeric entity reference
        $text = str_replace($this->_html_char_entity_ref, $this->_html_numeric_char_ref, $text);
        // convert '&' to '&amp;' for mb_decode_numericentity()
        $text = preg_replace('/&/', '&amp;', $text);
        // convert numeric entity of hex type to dec type
        $text = preg_replace_callback(
            '/&amp;#[xX]([0-9a-fA-F]+);/', function ($m) {
                return '&#'.hexdec($m[1]).';';
            }, $text
        );
        $text = preg_replace('/&amp;#([0-9]+);/', '&#$1;', $text);
        // decode numeric entity
        $text = mb_decode_numericentity($text, array(0x0, 0x10000, 0, 0xfffff), 'UTF-8');
        // convert &amp; to '&' for htmlspecialchars()
        $text = preg_replace('/&amp;/', '&', $text);
        // eacape html special character
        $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
        // trim control character and convert &#039; to &apos;
        return preg_replace(array('/[\\x00-\\x09\\x0b\\x0c\\x0e-\\x1f\\x7f]/', '/&#039;/'), array('', '&apos;'), $text);
    }

    /**
     * convert text to javascript encoding characters.
     *
     * @param string $text text string
     *
     * @return string encoded text string
     */
    public function javascript_special_chars($text)
    {
        static $searches = array('\\', '"', '\'', '<', '>', "\r", "\n");
        static $replaces = array('\\\\', '\\x22', '\\x27', '\\x3c', '\\x3e', '\\r', '\\n');
        // trim control character
        $text = mb_ereg_replace('/[\\x00-\\x09\\x0b\\x0c\\x0e-\\x1f]/', '', $text);
        // convert \"'<>\r\n" char to escaped chars
        $text = str_replace($searches, $replaces, $text);
        // convert character entity reference to numeric entity reference
        $text = $this->html_numeric_entities($text);
        // convert numeric entity of dec type to hex type
        $text = preg_replace_callback(
            '/&#x([0-9a-f]+);/i', function ($m) {
                return '&#'.hexdec($m[1]).';';
            }, $text
        );
        // convert numeric entity to javascript '\uXX' char
        return preg_replace_callback(
            '/&#([0-9]+);/', function ($m) {
                return '\\u'.sprintf('%04x', $m[1]);
            }, $text
        );
    }

    /**
     * filters text form data
     * XooNIps specific $myts->displayTarea() function.
     *
     * @param string $text
     * @param bool   $html
     * @param bool   $smily
     * @param bool   $xcode
     * @param bool   $image
     * @param bool   $br
     *
     * @return string
     */
    public function display_text_area($text, $html, $smiley, $xcode, $image, $br)
    {
        (method_exists(MyTextSanitizer, sGetInstance) and $myts = &MyTextSanitizer::sGetInstance()) || $myts = &MyTextSanitizer::getInstance();
        if (!$html) {
            // html not allowed
            $text = $this->html_special_chars($text);
        }
        if ($br) {
            $text = $myts->nl2Br($text);
        }
        $text = $myts->codePreConv($text, $xcode);
        $text = $this->convert_link($text);
        if ($smiley) {
            // process smiley
            $text = $myts->smiley($text);
        }
        if ($xcode) {
            // decode xcode
            if ($image) {
                // image allowed
                  $text = $myts->xoopsCodeDecode($text, 1);
            } else {
                // image not allowed
                  $text = $myts->xoopsCodeDecode($text, 0);
            }
        }
        $text = $myts->codeConv($text, $xcode, $image);

        return $text;
    }

    /**
     * convert timestamp to iso8601 string.
     *
     * @param int $value input timestamp
     *
     * @return encoded string
     */
    public function timestamp_to_iso8601($value)
    {
        return gmstrftime('%Y%m%dT%H:%M:%S', $value);
    }

    /**
     * convert iso8601 string to timestamp
     * e.g. 2004, +002004
     *      2004-04, 200404
     *      2004-04-01, 20040401
     *      2004-04-01T00
     *      2004-04-01T00:00, 2004-03-31T24:00
     *      2004-04-01T00:00:00
     *      2004-04-01T00:00:00Z
     *      2004-04-01T09:00:00+09
     *      2004-04-01T09:00:00+09:00, 2004-04-01T09:00+0900
     *      2004-W14-4, 2004W144
     *      2004W144T09:00+09:00
     *      2004-092, 2004092
     *      2004-092T09:00:00+09:00.
     *
     * @param string $value iso8601 string
     *
     * @return int decoded timestamp
     */
    public function iso8601_to_timestamp($value)
    {
        $tz_offset = 0;
        $tm = false;
        if (preg_match('/^(-?\\d{4}|[+-]\\d{6})(?:-?(?:(\\d{2})(?:-?(\\d{2})?)?|W([0-5]\\d)-?([1-7])|([0-3]\\d\\d)))?(?:T(\\d{2})(?::(\\d{2})(?::(\\d{2}))?)?(?:Z|([-+])(\\d{2})(?::?(\\d{2}))?)?)?$/', $value, $matches)) {
            $year = intval($matches[1]);
            if ($year < 1970 || $year > 2038) {
                // unsupported year
                return false;
            }
            $month = intval(isset($matches[2]) ? $matches[2] : 1);
            $mday = intval(isset($matches[3]) ? $matches[3] : 1);
            $week = intval(isset($matches[4]) ? $matches[4] : 0);
            $wday = intval(isset($matches[5]) ? $matches[5] : 0);
            $oday = intval(isset($matches[6]) ? $matches[6] : 0);
            $hour = intval(isset($matches[7]) ? $matches[7] : 0);
            $min = intval(isset($matches[8]) ? $matches[8] : 0);
            $sec = intval(isset($matches[9]) ? $matches[9] : 0);
            $pm = intval(isset($matches[10]) ? $matches[10].'1' : 1);
            $tz_hour = intval(isset($matches[11]) ? $matches[11] : 0);
            $tz_min = intval(isset($matches[12]) ? $matches[12] : 0);
            $tz_offset = $pm * ($tz_hour * 3600 + $tz_min * 60);
            if ($week == 0 && $wday == 0 && $oday == 0) {
                // calendar dates
                $tm = intval(gmmktime($hour, $min, $sec, $month, $mday, $year));
            } else {
                $tsm = intval(gmmktime(0, 0, 0, 1, 1, $year));
                if ($week != 0 && $wday != 0) {
                    // week dates
                    $days = ($week - 1) * 7 - intval(gmdate('w', $tsm)) + $wday;
                } else {
                    // ordinal dates
                    $days = $oday - 1;
                }
                $tm = $tsm + $days * 86400 + $hour * 3600 + $min * 60 + $sec;
            }
        }
        $tm = intval($tm) - $tz_offset;

        return $tm;
    }

    /**
     * convert text to javascript tag.
     *
     * @param string $text src
     *
     * @return string dist
     */
    public function _make_javascript($text)
    {
        static $code_prefix = null;
        static $code_postfix = null;
        if (is_null($code_prefix)) {
            $code_prefix = '<script type="text/javascript"><!--'."\n";
            $code_prefix .= 'document.write(String.fromCharCode(';
            $code_postfix = '));'."\n";
            $code_postfix .= '//--></script>';
        }
        $chars = unpack('C*', $text);
        $first = true;
        $code = $code_prefix;
        foreach ($chars as $k) {
            if ($first) {
                $first = false;
            } else {
                $code .= ',';
            }
            $code .= $k;
        }
        $code .= $code_postfix;

        return $code;
    }
}
