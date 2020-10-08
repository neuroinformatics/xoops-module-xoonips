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
defined('XOOPS_ROOT_PATH') || exit('XOOPS root path not defined');

/**
 * search utilities.
 *
 * @copyright copyright &copy; 2005-2009 RIKEN Japan
 */
class XooNIpsUtilitySearch extends XooNIpsUtility
{
    /**
     * constant value for search query operator 'AND'.
     *
     * @var string
     */
    public $OP_AND = 'AND';

    /**
     * constant value for search query operator 'AND'.
     *
     * @var string
     */
    public $OP_OR = 'OR';

    /**
     * constant value for search text encoding.
     *
     * @var string
     */
    public $ENCODING = XOONIPS_SEARCH_TEXT_ENCODING;

    /**
     * constant value for fulltext search data.
     *
     * @var string
     */
    public $WINDOW_SIZE = XOONIPS_WINDOW_SIZE;

    /**
     * stop words.
     *
     * @var array
     */
    public $_stop_words;

    /**
     * regex patterns.
     *
     * @var array
     */
    public $_regex_pattens;

    /**
     * constractor.
     */
    public function __construct()
    {
        $this->setSingleton();
        $this->_load_stopwords();
        $this->_initialize_regex_patterns();
    }

    /**
     * get keyword search criteria by query string.
     *
     * @param string $field    field part in SQL
     * @param string $query    search query
     * @param string $encoding text encoding of search query
     * @param string $prefix   table prefix in SQL
     *
     * @return object object instance of keyword search criteria
     */
    public function &getKeywordSearchCriteria($field, $query, $encoding, $prefix = '')
    {
        // convert query encoding to 'UTF-8'
        if ('UTF-8' != $encoding) {
            $query = mb_convert_encoding($query, 'UTF-8', $encoding);
        }

        // backup multi byte regex encoding
        $regex_encoding = mb_regex_encoding();
        // set multi byte regex encoding
        mb_regex_encoding('UTF-8');

        // normalize query string for keyword search
        $query = $this->_normalize_keyword_search_string($query);

        // split query to search tokens
        $tokens = $this->_split_to_tokens($query);

        // normalize search tokens
        $tokens = $this->_normalize_search_tokens($tokens);

        // get reverse polish notation tokens
        $tokens = $this->_reverse_polish_notation($tokens);

        // create keyword search criteria
        $criteria = &$this->_make_keyword_search_criteria($field, $tokens, $encoding, $prefix);

        // restore original multi byte regex encoding
        mb_regex_encoding($regex_encoding);

        return $criteria;
    }

    /**
     * get fulltext search criteria by query string.
     *
     * @param string $field    field part in SQL
     * @param string $query    search query
     * @param string $encoding text encoding of search query
     * @param string $prefix   table prefix in SQL
     *
     * @return object object instance of fulltext search criteria
     */
    public function &getFulltextSearchCriteria($field, $query, $encoding, $prefix = '')
    {
        // convert query encoding to 'UTF-8'
        if ('UTF-8' != $encoding) {
            $query = mb_convert_encoding($query, 'UTF-8', $encoding);
        }

        // backup multi byte regex encoding
        $regex_encoding = mb_regex_encoding();
        // set multi byte regex encoding
        mb_regex_encoding('UTF-8');

        // normalize query string for fulltext search
        $query = $this->_normalize_fulltext_search_string($query);

        // split query to search tokens
        $tokens = $this->_split_to_tokens($query);

        // normalize search tokens
        $tokens = $this->_normalize_search_tokens($tokens);

        // get reverse polish notation tokens
        $tokens = $this->_reverse_polish_notation($tokens);

        // create fulltext search expression part of SQL
        $criteria = &$this->_make_fulltext_search_criteria($field, $tokens, $prefix);

        // restore original multi byte regex encoding
        mb_regex_encoding($regex_encoding);

        return $criteria;
    }

    /**
     * get fulltext data for storing into database.
     *
     * @param string $text 'UTF-8' encoded text
     *
     * @return string $this->ENCODING encoded fulltext data
     */
    public function getFulltextData($text)
    {
        // backup multi byte regex encoding
        $regex_encoding = mb_regex_encoding();
        // set multi byte regex encoding
        mb_regex_encoding('UTF-8');

        // normalize string for fulltext search
        $text = $this->_normalize_fulltext_search_string($text);

        // split text to search tokens
        $tokens = $this->_split_to_tokens($text);

        // get fulltext search data
        $data = $this->_make_fulltext_search_data($tokens);

        // restore original multi byte regex encoding
        mb_regex_encoding($regex_encoding);

        return $data;
    }

    /**
     * make keyword search criteria.
     *
     * @param string $field    field part in SQL
     * @param array  $tokens   'UTF-8' encoded RPN applied tokens
     * @param string $encoding text encoding for creating criteria
     * @param string $prefix   table prefix in SQL
     *
     * @return object created criteria
     */
    public function &_make_keyword_search_criteria($field, $tokens, $encoding, $prefix)
    {
        if (false === $tokens) {
            // fatal error : normalizer returned false
            return $this->_make_force_unmatch_criteria($field, $prefix);
        }
        $is_utf8 = ('UTF-8' == $encoding);
        $stack = array();
        foreach ($tokens as $token) {
            if ($this->_is_operator($token)) {
                if (count($stack) < 2) {
                    // fatal error : invalid RPN data found
                    return $this->_make_force_unmatch_criteria($field, $prefix);
                }
                if ($this->_is_and_op($token)) {
                    // 'AND' operator
                    $op = $this->OP_AND;
                } else {
                    // 'OR' operator
                    $op = $this->OP_OR;
                }
                list($op1, $val1) = array_pop($stack);
                list($op2, $val2) = array_pop($stack);
                if (false === $op1 && false === $op2) {
                    $val = new CriteriaCompo($val2);
                    $val->add($val1);
                } elseif (false == $op1) {
                    $val = &$val2;
                    $val->add($val1, $op);
                } else {
                    $val = &$val1;
                    $val->add($val2, $op);
                }
                unset($val1, $val2, $op1, $op2);
            } else {
                $is_phrase = $this->_strip_double_quote($token);
                $token = ($is_utf8 ? $token : mb_convert_encoding($token, $encoding, 'UTF-8'));
                $token = addslashes($token);
                if ($is_phrase) {
                    $cr_op = '=';
                } else {
                    $cr_op = 'LIKE';
                    $token = '%'.preg_replace('/([%_])/', '\\\\\\1', $token).'%';
                }
                $op = false;
                $val = new Criteria($field, $token, $cr_op, $prefix);
            }
            $stack[] = array($op, $val);
            unset($op, $val);
        }
        $cnt = count($stack);
        if (0 == $cnt) {
            // no search query found
            return $this->_make_force_unmatch_criteria($field, $prefix);
        } elseif ($cnt > 1) {
            // fatal error : invalid RPN data found
            return $this->_make_force_unmatch_criteria($field, $prefix);
        }
        list($op, $val) = array_pop($stack);

        return $val;
    }

    /**
     * make fulltext search criteria.
     *
     * @param string $field  field part in SQL
     * @param array  $tokens 'UTF-8' encoded RPN applied tokens
     * @param string $prefix table prefix in SQL
     *
     * @return object object instance of fulltext search criteria
     */
    public function &_make_fulltext_search_criteria($field, $tokens, $prefix)
    {
        if (false === $tokens) {
            // fatal error : normalizer returned false
            return $this->_make_force_unmatch_criteria($field, $prefix);
        }
        $is_utf8 = ('UTF-8' == $this->ENCODING);
        $stack = array();
        foreach ($tokens as $token) {
            if ($this->_is_operator($token)) {
                if (count($stack) < 2) {
                    // fatal error : invalid RPN data found
                    return $this->_make_force_unmatch_criteria($field, $prefix);
                }
                list($op1, $val1) = array_pop($stack);
                list($op2, $val2) = array_pop($stack);
                if ($this->_is_and_op($token)) {
                    // 'AND' operator
                    $op = $this->OP_AND;
                    $fmt1 = (false === $op1 ? '+%s' : ($this->_is_or_op($op1) ? '+( %s )' : '%s'));
                    $fmt2 = (false === $op2 ? '+%s' : ($this->_is_or_op($op2) ? '+( %s )' : '%s'));
                } else {
                    // 'OR' operator
                    $fmt1 = ($this->_is_and_op($op1) ? '( %s )' : '%s');
                    $fmt2 = ($this->_is_and_op($op2) ? '( %s )' : '%s');
                    $op = $this->OP_OR;
                }
                $val = sprintf($fmt2.' '.$fmt1, $val2, $val1);
            } else {
                $is_phrase = $this->_strip_double_quote($token);
                if (!$this->_is_multibyte_word($token)) {
                    // single byte token
                    $token = ($is_utf8 ? $token : mb_convert_encoding($token, $this->ENCODING, 'UTF-8'));
                } else {
                    // multi byte token
                    $token = ($is_utf8 ? $token : mb_convert_encoding($token, $this->ENCODING, 'UTF-8'));
                    $mbtokens = $this->_ngram($token, XOONIPS_WINDOW_SIZE, $this->ENCODING, false, false);
                    if (count($mbtokens) > 1) {
                        $is_phrase = true;
                    }
                    $token = implode(' ', array_map('bin2hex', $mbtokens));
                }
                $fmt = $is_phrase ? '"%s"' : '%s*';
                $op = false;
                $val = sprintf($fmt, addslashes($token));
            }
            $stack[] = array($op, $val);
        }
        $cnt = count($stack);
        if (0 == $cnt) {
            // no search query found
            return $this->_make_force_unmatch_criteria($field, $prefix);
        } elseif ($cnt > 1) {
            // fatal error : invalid RPN data found
            return $this->_make_force_unmatch_criteria($field, $prefix);
        }
        list($op, $expr) = array_pop($stack);
        $criteria = new XooNIpsFulltextCriteria($field, $expr, true, $prefix);

        return $criteria;
    }

    /**
     * make force unmatch criteria for fatal error.
     *
     * @param string $field
     * @param string $prefix
     */
    public function &_make_force_unmatch_criteria($field, $prefix)
    {
        $criteria = new CriteriaCompo();
        $criteria->add(new Criteria($field, '', '=', $prefix));
        $criteria->add(new Criteria($field, '', '!=', $prefix));

        return $criteria;
    }

    /**
     * make fulltext search data.
     *
     * @param array $tokens 'UTF-8' encoded fulltext search tokens
     *
     * @return string $this->ENCODING encoded fulltext search data
     */
    public function _make_fulltext_search_data($tokens)
    {
        $is_utf8 = ('UTF-8' == $this->ENCODING);
        $trailing = ($this->WINDOW_SIZE > 2);
        $windowed = array();
        foreach ($tokens as $token) {
            $this->_strip_double_quote($token);
            if (!$this->_is_multibyte_word($token)) {
                // single byte token
                $windowed[] = ($is_utf8 ? $token : mb_convert_encoding($token, $this->ENCODING, 'UTF-8'));
            } else {
                // multi byte token
                $token = ($is_utf8 ? $token : mb_convert_encoding($token, $this->ENCODING, 'UTF-8'));
                $mbtokens = $this->_ngram($token, $this->WINDOW_SIZE, $this->ENCODING, false, $trailing);
                foreach ($mbtokens as $mbtoken) {
                    $windowed[] = bin2hex($mbtoken);
                }
            }
        }

        return implode(' ', $windowed);
    }

    /**
     * split text to search tokens.
     *
     * @param string $text 'UTF-8' encoded search text
     *
     * @return array array of search text token
     */
    public function _split_to_tokens($text)
    {
        $tokens = array();

        // set search token patterns
        // 1. double quoted phrase
        // 2. single byte word contains html entities and latin1 letters
        // 3. multi byte word
        // 4. symbol - !#$%&'()*+,-./:;<=>?@[\]~_`{|}~ and latin1 supplement symbol
        $pattern = sprintf('%s|%s|%s|%s', $this->_regex_patterns['phrase'], $this->_regex_patterns['sbword'], $this->_regex_patterns['mbword'], $this->_regex_patterns['symbol']);
        mb_ereg_search_init($text, $pattern);

        $len = strlen($text);
        for ($i = 0; $i < $len; $i = mb_ereg_search_getpos()) {
            mb_ereg_search_setpos($i);
            $regs = mb_ereg_search_regs();
            if (false === $regs) {
                break;
            }
            // put back token encoding if changed to 'UTF-8'
            $tokens[] = $regs[0];
        }

        return $tokens;
    }

    /**
     * normalize keyword search string.
     *
     * @param string $text 'UTF-8' encoded input text
     *
     * @return string false if empty query
     */
    public function _normalize_keyword_search_string($text)
    {
        // sanitize non printable characters
        $pattern = sprintf('%s+', $this->_regex_patterns['noprint']);
        $text = mb_ereg_replace($pattern, ' ', $text);

        // trim string
        $text = trim($text);

        return $text;
    }

    /**
     * normalize fulltext search string.
     *
     * @param string $text 'UTF-8' encoded input text
     *
     * @return string normalized string
     */
    public function _normalize_fulltext_search_string($text)
    {
        // convert html character entities to numeric entities
        $textutil = &xoonips_getutility('text');
        $text = $textutil->html_numeric_entities($text);

        // convert all html numeric entities to UTF-8 character
        $text = mb_decode_numericentity($text, array(0x0, 0xffff, 0, 0xffff), 'UTF-8');

        // sanitize non printable characters
        $pattern = sprintf('%s+', $this->_regex_patterns['noprint']);
        $text = mb_ereg_replace($pattern, ' ', $text);

        // normalize Japanese characters
        // 1. 'a'  - zenkaku alpha and number chars to hankaku chars
        // 2. 's'  - zenkaku space to hankaku space
        // 3. 'KV' - hankaku katakana and dakuten/handakuten to zenkaku katakana
        $text = mb_convert_kana($text, 'asKV', 'UTF-8');

        // convert latin1 suppliment characters to html numeric entities
        $text = mb_encode_numericentity($text, array(0x0080, 0x00ff, 0, 0xffff), 'UTF-8');

        // trim string
        $text = trim($text);

        return $text;
    }

    /**
     * normalize search query tokens
     *  - remove single character
     *  - remove stopwords
     *  - remove invalid operators and parentheses
     *  - insert AND operator between operands or right parenthesis ')'.
     *
     * @param array $tokens 'UTF-8' encoded search query tokens
     *
     * @return array normalized search query tokens
     */
    public function _normalize_search_tokens($tokens)
    {
        $tmp = array();
        $ptoken = false;
        $pdepth = 0;
        foreach ($tokens as $token) {
            // remove single character
            $patterns = sprintf('^(?:[^()]|%s)$', $this->_regex_patterns['entity']);
            if (mb_ereg_match($patterns, $token)) {
                continue;
            }
            // remove stop words
            if (in_array(mb_strtolower($token, 'UTF-8'), $this->_stop_words)) {
                continue;
            }
            if ($this->_is_operator($token)) {
                // operator token
                $token = mb_strtoupper($token, 'UTF-8');
                if (false === $ptoken) {
                    // remove operator at begin of $tokens
                    continue;
                } elseif ('(' == $ptoken) {
                    // remove operator at next left parenthesis '('
                    continue;
                } elseif ($this->_is_operator($ptoken)) {
                    // remove redundant operator
                    array_pop($tmp);
                }
            } elseif (')' == $token) {
                // right parenthesis token
                if (0 == $pdepth) {
                    // remove right parenthesis ')' at begin of $tokens
                    continue;
                } elseif ($this->_is_operator($ptoken)) {
                    // remove previous operator
                    array_pop($tmp);
                } elseif ('(' == $ptoken) {
                    // remove empty parenthesis
                    array_pop($tmp);
                    $ptoken = empty($tmp) ? false : $tmp[count($tmp) - 1];
                    --$pdepth;
                    continue;
                }
                --$pdepth;
            } elseif ('(' == $token) {
                // left parenthesis token
                if (false !== $ptoken && !$this->_is_operator($ptoken) && '(' != $ptoken) {
                    // insert AND operator between operands and left parenthesis '('
                    $tmp[] = $this->OP_AND;
                }
                ++$pdepth;
            } else {
                // normal token
                if (false !== $ptoken && !$this->_is_operator($ptoken) && '(' != $ptoken) {
                    // insert AND operator between operands or right parenthesis ')'
                    $tmp[] = $this->OP_AND;
                }
            }
            $tmp[] = $token;
            $ptoken = $token;
        }
        if (false !== $ptoken && $this->_is_operator($ptoken)) {
            // remove last operator
            array_pop($tmp);
        }
        if (0 != $pdepth) {
            // fatal error : mismatched parenthesis found
            return false;
        }
        $tokens = $tmp;

        return $tokens;
    }

    /**
     * get reverse polish notation tokens.
     *
     * @param array $tokens 'UTF-8' encoded search query tokens
     *
     * @return array reverse polish notation applied tokens
     */
    public function _reverse_polish_notation($tokens)
    {
        if (false === $tokens) {
            // fatal error : normalizer returned false
            return false;
        }
        // get reverse polish notation from expression tokens
        $operands = array();
        $operators = array();
        foreach ($tokens as $token) {
            if ('(' == $token) {
                $operators[] = $token;
            } elseif (')' == $token) {
                if (empty($operators)) {
                    // fatal error : mismatched parenthesis found
                    return false;
                }
                for ($tmp = array_pop($operators); '(' != $tmp; $tmp = array_pop($operators)) {
                    $operands[] = $tmp;
                    if (empty($operators)) {
                        // fatal error : mismatched parenthesis found
                        return false;
                    }
                }
            } elseif ($this->_is_operator($token)) {
                while (!empty($operators)) {
                    $tmp = array_pop($operators);
                    if ($this->_is_and_op($tmp) && $this->_is_or_op($token)) {
                        $operands[] = $tmp;
                    } else {
                        $operators[] = $tmp;
                        break;
                    }
                }
                $operators[] = $token;
            } else {
                // words
                $operands[] = $token;
            }
        }
        while (!empty($operators)) {
            $operands[] = array_pop($operators);
        }

        return $operands;
    }

    /**
     * get array of N-gram applied window string.
     *
     * @param string $word     input string
     * @param int    $n        window size
     * @param string $encoding input string encoding
     * @param bool   $leading  flag for output leading
     * @param bool   $trailing flag for output trailing
     *
     * @return array array of window string
     */
    public function _ngram($word, $n, $encoding, $leading, $trailing)
    {
        $words = array();
        $word = trim($word);
        if (empty($word) || $n < 1) {
            return $words;
        }
        $len = mb_strlen($word, $encoding);
        $wsize = min($len, $n);
        $lsize = $wsize - 1;
        $bsize = $len - $lsize;
        // leading
        if ($leading) {
            for ($i = 1; $i <= $lsize; ++$i) {
                $words[] = mb_substr($word, 0, $i, $encoding);
            }
        }
        // body
        for ($i = 0; $i < $bsize; ++$i) {
            $words[] = mb_substr($word, $i, $wsize, $encoding);
        }
        // trailing
        if ($trailing) {
            for ($i = $lsize; $i > 0; --$i) {
                $words[] = mb_substr($word, $bsize + $lsize - $i, $i, $encoding);
            }
        }

        return $words;
    }

    /**
     * return true if operator is AND or OR.
     *
     * @param string $token 'UTF-8' encoded input text
     *
     * @return bool true if operator token
     */
    public function _is_operator($token)
    {
        return $this->_is_and_op($token) || $this->_is_or_op($token);
    }

    /**
     * return true if operator is AND.
     *
     * @param string $token 'UTF-8' encoded input text
     *
     * @return bool true if 'AND' token
     */
    public function _is_and_op($token)
    {
        return $this->OP_AND == mb_strtoupper($token, 'UTF-8');
    }

    /**
     * return true if operator is OR.
     *
     * @param string $token 'UTF-8' encoded input text
     *
     * @return bool true if 'OR' token
     */
    public function _is_or_op($token)
    {
        return $this->OP_OR == mb_strtoupper($token, 'UTF-8');
    }

    /**
     * return true if multibyte word.
     *
     * @param string $token 'UTF-8' encoded word
     *
     * @return bool true if multibyte word
     */
    public function _is_multibyte_word($token)
    {
        $result = mb_ereg($this->_regex_patterns['mbword'], $token);

        return false !== $result;
    }

    /**
     * strip double quote from token.
     *
     * @param string &$token 'UTF-8' encoded input text
     *
     * @return bool true if unquoted
     */
    public function _strip_double_quote(&$token)
    {
        // strip double quote
        if (preg_match('/^"(.*)"$/', $token, $matches)) {
            // remove escape symbol in quoted string '\'
            $token = mb_ereg_replace('\\x5c([\\x22\\x27\\x5c])', '\\1', $matches[1]);

            return true;
        }

        return false;
    }

    /**
     * initialize multi byte regex patterns.
     */
    public function _initialize_regex_patterns()
    {
        // latin1 character codes - http://en.wikipedia.org/wiki/Latin-1
        $latin1 = array();
        $ascii = array(
            'letter' => '0-9a-zA-Z',
            'symbol' => '\\x21\\x23-\\x2f\\x3a-\\x40\\x5b-\\x60\\x7b-\\x7e',
            'noprint' => '\\x00-\\x1f\\x7f',
        );
        $ranges = array(
            'letter' => array(array(0xc0, 0xd6), array(0xd8, 0xf6), array(0xf8, 0xff)),
            'symbol' => array(array(0xa0, 0xbf), 0xd7, 0xf7),
            'noprint' => array(array(0x80, 0x9f)),
        );
        foreach ($ranges as $name => $range) {
            $chars = array();
            foreach ($range as $code) {
                if (is_array($code)) {
                    list($from, $to) = $code;
                    for ($i = $from; $i <= $to; ++$i) {
                        $chars[] = chr($i);
                    }
                } else {
                    $chars[] = chr($code);
                }
            }
            $latin1[$name] = $ascii[$name].mb_convert_encoding(implode('', $chars), 'UTF-8', 'ISO-8859-1');
        }

        // non printable characters
        $patterns['noprint'] = sprintf('[%s]', $latin1['noprint']);
        // symbols
        $patterns['symbol'] = sprintf('[%s]', $latin1['symbol']);
        // html entities
        $patterns['entity'] = '&(?:#[0-9]+|[xX][0-9a-fA-F]+|[0-9a-zA-Z]+);';
        // single byte word
        $patterns['sbword'] = sprintf('(?:[%s]|%s)+', $latin1['letter'], $patterns['entity']);
        // multi byte character with out latin1
        $patterns['mbchar'] = sprintf('[^\\x20\\x22%s%s%s]', $latin1['letter'], $latin1['symbol'], $latin1['noprint']);
        // multi byte word
        $patterns['mbword'] = sprintf('%s+', $patterns['mbchar']);
        // phrase
        $patterns['phrase'] = '"(?:\\x5c\\x22|[^\\x22])*"';

        $this->_regex_patterns = $patterns;
    }

    /**
     * load stop words.
     *
     * @return bool|null false if faiure
     */
    public function _load_stopwords()
    {
        $stop_words = array();
        if (defined('XOONIPS_STOPWORD_FILE_PATH')) {
            $words = array_map('trim', file(XOONIPS_STOPWORD_FILE_PATH));
            foreach ($words as $word) {
                if (preg_match('/^\\s*#.*$/', $word) || '' == $word) {
                    // skip comment out line or empty line
                    continue;
                }
                $stop_words[] = $word;
            }
        }
        $this->_stop_words = $stop_words;
    }
}
