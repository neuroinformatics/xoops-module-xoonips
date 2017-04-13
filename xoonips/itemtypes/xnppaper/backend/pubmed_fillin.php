<?php

// $Revision: 1.2.2.1.2.6 $
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
if (!defined('XOONIPS_PATH')) {
    exit();
}

// class file
require_once XOONIPS_PATH.'/class/base/JSON.php';
require_once dirname(__DIR__).'/class/pubmed.class.php';

// change internal encoding to UTF-8
if (extension_loaded('mbstring')) {
    mb_language('uni');
    mb_internal_encoding('UTF-8');
    mb_http_output('pass');
}

$is_error = false;
$error_message = '';
if (!isset($_SERVER['HTTP_REFERER']) || preg_match('/'.preg_quote(XOOPS_URL, '/').'/', $_SERVER['HTTP_REFERER']) == 0) {
    $is_error = true;
    $error_message = 'Turn REFERER on';
}

if (!$is_error && !isset($_GET['pmid'])) {
    $is_error = true;
    $error_message = 'pmid required';
}

if (!$is_error) {
    $pmid = trim($_GET['pmid']);
    if (!is_numeric($pmid)) {
        $is_error = true;
        $error_message = 'pmid has to be numeric character';
    }
    $pmid = intval($pmid);
}

function &get_pubmed_data($pmid)
{
    $ret = array();
    $pubmed = new XooNIps_PubMed_ArticleSet();
    if (!$pubmed->set_pmid($pmid)) {
        return $ret;
    }
    if (!$pubmed->fetch()) {
        return $ret;
    }
    if (!$pubmed->parse()) {
        return $ret;
    }
    if (!isset($pubmed->_data[$pmid])) {
        return $ret;
    }
    $article = &$pubmed->_data[$pmid];
    // pubmed id
    $ret['pmid'] = $article['PMID'];
    // title
    $ret['title'] = $article['ArticleTitle'];
    if (preg_match('/^\\[(.*)\\]$/', $ret['title'], $matches)) {
        $ret['title'] = $matches[1];
    }
    // volume
    $ret['volume'] = ($article['Journal_Volume'] != '') ? $article['Journal_Volume'] : '';
    // issue
    $ret['issue'] = ($article['Journal_Issue'] != '') ? $article['Journal_Issue'] : '';
    // publication_year
    if ($article['Journal_PubDate_Year'] != '') {
        $ret['year'] = $article['Journal_PubDate_Year'];
    } else {
        if (preg_match('/(\\d\\d\\d\\d)\\s.*/', $article['Journal_MedlineDate'], $matches)) {
            $ret['year'] = $matches[1];
        } else {
            $ret['year'] = '';
        }
    }
    // journal
    if ($article['Journal_Title'] != '') {
        $ret['journal'] = $article['Journal_Title'];
    } elseif ($article['MedlineTA'] != '') {
        $journal_esearch = new XooNIps_PubMed_JournalEsearch();
        if ($journal_esearch->set_journal_ta($article['MedlineTA'])) {
            if ($journal_esearch->fetch()) {
                if ($journal_esearch->parse()) {
                    if (isset($journal_esearch->_data['id'])) {
                        $jids = &$journal_esearch->_data['id'];
                        $journal_esummary = new XooNIps_PubMed_JournalEsummary();
                        foreach ($jids as $jid) {
                            $journal_esummary->set_journal_id($jid);
                        }
                        if ($journal_esummary->fetch()) {
                            if ($journal_esummary->parse()) {
                                foreach ($jids as $jid) {
                                    if (isset($journal_esummary->_data[$jid])) {
                                        $docsum = &$journal_esummary->_data[$jid];
                                        if ($docsum['MedAbbr'] == $article['MedlineTA']) {
                                            $ret['journal'] = $docsum['Title'];
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    } else {
        $ret['journal'] = '';
    }
    // page
    $ret['page'] = ($article['MedlinePgn'] != '') ? $article['MedlinePgn'] : '';
    // abstract
    $ret['abst'] = ($article['AbstractText'] != '') ? $article['AbstractText'] : '';
    if (empty($ret['abst'])) {
        if ($article['OtherAbstractText'] != '') {
            $ret['abst'] = $article['OtherAbstractText'];
        }
    }
    // author
    $ret['author'] = array();
    if (!empty($article['AuthorList'])) {
        foreach ($article['AuthorList'] as $author) {
            $str = $author['LastName'].' ';
            if ($author['Initials'] != '') {
                $str .= $author['Initials'];
            } elseif ($author['ForeName'] != '') {
                $str .= $author['ForeName'];
            } else {
                if ($author['MiddleName'] != '') {
                    $str .= $author['MiddleName'].' ';
                }
                $str .= $author['FirstName'];
            }
            $ret['author'][] = $str;
        }
    }
    // keywords
    $ret['keywords'] = array();
    if (!empty($article['MeshHeadingList'])) {
        foreach ($article['MeshHeadingList'] as $meshheading) {
            $str = $meshheading['DescriptorName'];
            if (count($meshheading['QualifierName']) > 0) {
                $tmpstr = implode(',', $meshheading['QualifierName']);
                $str .= ','.$tmpstr;
            }
            $ret['keywords'][] = $str;
        }
    }

    return $ret;
}

if (!$is_error) {
    $data = &get_pubmed_data($pmid);
    if (empty($data)) {
        $data['error'] = 'failed to get pubmed resources';
    }
} else {
    $data = array();
    $data['error'] = $error_message;
}

// json
$json = new Services_JSON();
$encode = $json->encode($data);

// output
header('Content-Type: text/javascript+json; charset=utf-8');
echo $encode;
