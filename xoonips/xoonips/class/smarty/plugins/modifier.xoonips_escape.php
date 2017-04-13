<?php
/**
 * Smarty xoonips_escape modifier plugin.
 *
 * Type:     modifier<br>
 * Name:     xoonips_escape<br>
 * Purpose:  Escape the string according to escapement type
 *
 * @param string $text input
 * @param string $type type of escape html, xml or javascript
 *
 * @return string
 */
function smarty_modifier_xoonips_escape($text, $type = 'html')
{
    if (!function_exists('xoonips_getutility')) {
        // return empty string if xoonips function not loaded.
        return '';
    }
    $textutil = &xoonips_getutility('text');
    switch ($type) {
    case 'html':
        $text = $textutil->html_special_chars($text);
        break;
    case 'xml':
        $text = $textutil->xml_special_chars($text, _CHARSET);
        break;
    case 'javascript':
        $text = $textutil->javascript_special_chars($text);
        break;
    }

    return $text;
}
