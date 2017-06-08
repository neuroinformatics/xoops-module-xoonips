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

require_once XOOPS_ROOT_PATH.'/modules/xoonips/class/base/xmlparser.class.php';

/**
 * The PubMed ArticleSet data handling class
 * this class will works under following DTDs
 *  http://www.ncbi.nlm.nih.gov/entrez/query/DTD/pubmed_080101.dtd
 *  http://www.ncbi.nlm.nih.gov/entrez/query/DTD/nlmmedline_080101.dtd
 *  http://www.ncbi.nlm.nih.gov/entrez/query/DTD/nlmmedlinecitation_080101.dtd
 *  http://www.ncbi.nlm.nih.gov/entrez/query/DTD/nlmsharedcatcit_080101.dtd
 *  http://www.ncbi.nlm.nih.gov/entrez/query/DTD/nlmcommon_080101.dtd.
 *
 * @author Yoshihiro OKUMURA <orrisroot@users.sourceforge.jp>
 */
class XooNIps_PubMed_ArticleSet extends XooNIpsXMLParser
{
    /**
     * parsed data.
     *
     * @var array
     */
    public $_data;

    /**
     * parsing condition.
     *
     * @var array
     */
    public $_condition = array();

    /**
     * pubmed ids.
     *
     * @var array
     */
    public $_pmids = array();

    public function __construct()
    {
        // call parent constructor
        parent::__construct();
        // set fetcher conditions
        $this->_fetch_url = 'http://eutils.ncbi.nlm.nih.gov/entrez/eutils/efetch.fcgi';
        $this->_fetch_arguments['db'] = 'pubmed';
        $this->_fetch_arguments['retmode'] = 'xml';
        // set parser conditions
        $this->_parser_doctype = 'PubmedArticleSet';
        // comment out next line for yearly update by PubMed site
        // $this->_parser_public_id = '-//NLM//DTD PubMedArticle, 1st January 2008//EN';
    }

    /**
     * set the pubmed id.
     *
     * @return bool TRUE if success
     */
    public function set_pmid($pmid)
    {
        if (in_array($pmid, $this->_pmids)) {
            $this->_error_message = 'pubmed id '.$pmid.' is already set';

            return false;
        }
        if (count($this->_pmids) >= 20) {
            $this->_error_message = 'too match register pubmed ids (>=20)';

            return false;
        }
        if (isset($this->_fetch_arguments['id'])) {
            $this->_fetch_arguments['id'] .= ','.$pmid;
        } else {
            $this->_fetch_arguments['id'] = $pmid;
        }

        return true;
    }

    /**
     * override function of start element handler.
     *
     */
    public function parser_start_element($attribs)
    {
        switch ($this->_parser_condition) {
        case '/PubmedArticleSet/PubmedArticle':
            $this->_condition['article'] = array(
            'PMID' => '',
            'Journal_Volume' => '',
            'Journal_Issue' => '',
            'Journal_MedlineDate' => '',
            'Journal_PubDate_Year' => '',
            'Journal_PubDate_Month' => '',
            'Journal_PubDate_Day' => '',
            'Journal_Title' => '',
            'MedlineTA' => '',
            'ArticleTitle' => '',
            'MedlinePgn' => '',
            'AbstractText' => '',
            'OtherAbstractText' => '',
            'AuthorList' => array(),
            'Language' => array(),
            'MeshHeadingList' => array(),
            );
            break;
        case '/PubmedArticleSet/PubmedArticle/MedlineCitation/Article/AuthorList/Author':
            $this->_condition['author'] = array(
            'LastName' => '',
            'ForeName' => '',
            'FirstName' => '',
            'MiddleName' => '',
            'Initials' => '',
            'Suffix' => '',
            );
            break;
        case '/PubmedArticleSet/PubmedArticle/MedlineCitation/Article/Language':
            $this->_condition['language'] = '';
            break;
        case '/PubmedArticleSet/PubmedArticle/MedlineCitation/MeshHeadingList/MeshHeading':
            $this->_condition['meshheading'] = array(
            'DescriptorName' => '',
            'QualifierName' => array(),
            );
            break;
        }
    }

    /**
     * override function of end element handler.
     *
     */
    public function parser_end_element()
    {
        switch ($this->_parser_condition) {
        case '/PubmedArticleSet/PubmedArticle':
            $pmid = $this->_condition['article']['PMID'];
            $this->_data[$pmid] = $this->_condition['article'];
            break;
        case '/PubmedArticleSet/PubmedArticle/MedlineCitation/Article/AuthorList/Author':
            $this->_condition['article']['AuthorList'][] = $this->_condition['author'];
            break;
        case '/PubmedArticleSet/PubmedArticle/MedlineCitation/Article/Language':
            $this->_condition['Language'][] = $this->_condition['language'];
            break;
        case '/PubmedArticleSet/PubmedArticle/MedlineCitation/MeshHeadingList/MeshHeading':
            $this->_condition['article']['MeshHeadingList'][] = $this->_condition['meshheading'];
            break;
        }
    }

    /**
     * override function of character data handler.
     *
     * @param string   $cdata  character data
     */
    public function parser_character_data($cdata)
    {
        switch ($this->_parser_condition) {
        case '/PubmedArticleSet/PubmedArticle/MedlineCitation/PMID':
            // PMID
            $this->_condition['article']['PMID'] .= $cdata;
            break;
        case '/PubmedArticleSet/PubmedArticle/MedlineCitation/Article/Journal/JournalIssue/Volume':
            // Volume?
            $this->_condition['article']['Journal_Volume'] .= $cdata;
            break;
        case '/PubmedArticleSet/PubmedArticle/MedlineCitation/Article/Journal/JournalIssue/Issue':
            // Issue?
            $this->_condition['article']['Journal_Issue'] .= $cdata;
            break;
        case '/PubmedArticleSet/PubmedArticle/MedlineCitation/Article/Journal/JournalIssue/PubDate/MedlineDate':
            // ( Year, ((Month,Day?)|Season)? ) | MedlineDate
            $this->_condition['article']['Journal_MedlineDate'] .= $cdata;
            break;
        case '/PubmedArticleSet/PubmedArticle/MedlineCitation/Article/Journal/JournalIssue/PubDate/Year':
            // ( Year, ((Month,Day?)|Season)? ) | MedlineDate
            $this->_condition['article']['Journal_PubDate_Year'] .= $cdata;
            break;
        case '/PubmedArticleSet/PubmedArticle/MedlineCitation/Article/Journal/JournalIssue/PubDate/Month':
            // ( Year, ((Month,Day?)|Season)? ) | MedlineDate
            $this->_condition['article']['Journal_PubDate_Month'] .= $cdata;
            break;
        case '/PubmedArticleSet/PubmedArticle/MedlineCitation/Article/Journal/JournalIssue/PubDate/Day':
            // ( Year, ((Month,Day?)|Season)? ) | MedlineDate
            $this->_condition['article']['Journal_PubDate_Day'] .= $cdata;
            break;
        case '/PubmedArticleSet/PubmedArticle/MedlineCitation/Article/Journal/Title':
            // Title?
            $this->_condition['article']['Journal_Title'] .= $cdata;
            break;
        case '/PubmedArticleSet/PubmedArticle/MedlineCitation/MedlineJournalInfo/MedlineTA':
            // MedlineTA
            $this->_condition['article']['MedlineTA'] .= $cdata;
            break;
        case '/PubmedArticleSet/PubmedArticle/MedlineCitation/Article/ArticleTitle':
            // ArticleTitle
            $this->_condition['article']['ArticleTitle'] .= $cdata;
            break;
        case '/PubmedArticleSet/PubmedArticle/MedlineCitation/Article/Pagination/MedlinePgn':
            // (StartPage, EndPage?, MedlinePgn?) | MedlinePgn)
            $this->_condition['article']['MedlinePgn'] .= $cdata;
            break;
        case '/PubmedArticleSet/PubmedArticle/MedlineCitation/Article/Abstract/AbstractText':
            // (AbstractText,CopyrightInformation?)
            $this->_condition['article']['AbstractText'] .= $cdata;
            break;
        case '/PubmedArticleSet/PubmedArticle/MedlineCitation/OtherAbstract/AbstractText':
            // (AbstractText,CopyrightInformation?)
            $this->_condition['article']['OtherAbstractText'] .= $cdata;
            break;
        case '/PubmedArticleSet/PubmedArticle/MedlineCitation/Article/AuthorList/Author/LastName':
            // LastName,(ForeName|(FirstName,MiddleName?),Initials?,Suffix?
            $this->_condition['author']['LastName'] .= $cdata;
            break;
        case '/PubmedArticleSet/PubmedArticle/MedlineCitation/Article/AuthorList/Author/ForeName':
            // LastName,(ForeName|(FirstName,MiddleName?),Initials?,Suffix?
            $this->_condition['author']['ForeName'] .= $cdata;
            break;
        case '/PubmedArticleSet/PubmedArticle/MedlineCitation/Article/AuthorList/Author/FirstName':
            // LastName,(ForeName|(FirstName,MiddleName?),Initials?,Suffix?
            $this->_condition['author']['FirstName'] .= $cdata;
            break;
        case '/PubmedArticleSet/PubmedArticle/MedlineCitation/Article/AuthorList/Author/MiddleName':
            // LastName,(ForeName|(FirstName,MiddleName?),Initials?,Suffix?
            $this->_condition['author']['MiddleName'] .= $cdata;
            break;
        case '/PubmedArticleSet/PubmedArticle/MedlineCitation/Article/AuthorList/Author/Initials':
            // LastName,(ForeName|(FirstName,MiddleName?),Initials?,Suffix?
            $this->_condition['author']['Initials'] .= $cdata;
            break;
        case '/PubmedArticleSet/PubmedArticle/MedlineCitation/Article/AuthorList/Author/Suffix':
            // LastName,(ForeName|(FirstName,MiddleName?),Initials?,Suffix?
            $this->_condition['author']['Suffix'] .= $cdata;
            break;
        case '/PubmedArticleSet/PubmedArticle/MedlineCitation/Article/Language':
            // Language+
            $this->_condition['language'] .= strtolower($cdata);
            break;
        case '/PubmedArticleSet/PubmedArticle/MedlineCitation/MeshHeadingList/MeshHeading/DescriptorName':
            // (DescriptorName, QualifierName*)
            $this->_condition['meshheading']['DescriptorName'] .= $cdata;
            break;
        case '/PubmedArticleSet/PubmedArticle/MedlineCitation/MeshHeadingList/MeshHeading/QualifierName':
            // (DescriptorName, QualifierName*)
            $this->_condition['meshheading']['QualifierName'][] .= $cdata;
            break;
        }
    }
}

/**
 * The class for the PubMed eSearch data of the Journal Title Abbreviation.
 *
 * this class will works under following DTDs
 *  http://www.ncbi.nlm.nih.gov/entrez/query/DTD/eSearch_020511.dtd
 *
 * @author Yoshihiro OKUMURA <orrisroot@users.sourceforge.jp>
 */
class XooNIps_PubMed_JournalEsearch extends XooNIpsXMLParser
{
    /**
     * parsed data.
     *
     * @var array
     */
    public $_data;

    /**
     * parsing condition.
     *
     * @var array
     */
    public $_condition = array();

    /**
     * jornal title abbreviation.
     *
     * @var string
     */
    public $_title_abbreviation = '';

    public function __construct()
    {
        // call parent constructor
        parent::__construct();
        // set fetcher conditions
        $this->_fetch_url = 'http://eutils.ncbi.nlm.nih.gov/entrez/eutils/esearch.fcgi';
        $this->_fetch_arguments['db'] = 'journals';
        $this->_fetch_arguments['retmode'] = 'xml';
        // set parser conditions
        $this->_parser_doctype = 'eSearchResult';
        $this->_parser_public_id = '-//NLM//DTD eSearchResult, 11 May 2002//EN';
    }

    /**
     * set the journal title abbreviation.
     *
     * @return bool TRUE if success
     */
    public function set_journal_ta($ta)
    {
        if (!empty($this->_title_abbreviation)) {
            $this->_error_message = 'journal title abbreviaion "'.$ta.'" is already set';

            return false;
        }
        $this->_title_abbreviation = $ta;
        $this->_fetch_arguments['term'] = '"'.$ta.'"[Title Abbreviation]';

        return true;
    }

    /**
     * override function of start element handler.
     *
     */
    public function parser_start_element($attribs)
    {
        switch ($this->_parser_condition) {
        case '/eSearchResult/IdList/Id':
            $this->_condition['Id'] = '';
            break;
        }
    }

    /**
     * override function of character data handler.
     *
     * @param string   $cdata  character data
     */
    public function parser_character_data($cdata)
    {
        switch ($this->_parser_condition) {
        case '/eSearchResult/IdList/Id':
            // Id*
            $this->_condition['Id'] .= $cdata;
            break;
        }
    }

    /**
     * override function of end element handler.
     *
     */
    public function parser_end_element()
    {
        switch ($this->_parser_condition) {
        case '/eSearchResult/IdList/Id':
            $this->_data['id'][] = $this->_condition['Id'];
            break;
        }
    }
}

/**
 * The class for the PubMed eSummary data of the Journal Title Abbreviation.
 *
 * this class will works under following DTDs
 *  http://www.ncbi.nlm.nih.gov/entrez/query/DTD/eSummary_041029.dtd
 *
 * @author Yoshihiro OKUMURA <orrisroot@users.sourceforge.jp>
 */
class XooNIps_PubMed_JournalEsummary extends XooNIpsXMLParser
{
    /**
     * parsed data.
     *
     * @var array
     */
    public $_data;

    /**
     * parsing condition.
     *
     * @var array
     */
    public $_condition = array();

    /**
     * jornal ids.
     *
     * @var array
     */
    public $_journal_ids = array();

    public function __construct()
    {
        // call parent constructor
        parent::__construct();
        // set fetcher conditions
        $this->_fetch_url = 'http://eutils.ncbi.nlm.nih.gov/entrez/eutils/esummary.fcgi';
        $this->_fetch_arguments['db'] = 'journals';
        $this->_fetch_arguments['retmode'] = 'xml';
        // set parser conditions
        $this->_parser_doctype = 'eSummaryResult';
        $this->_parser_public_id = '-//NLM//DTD eSummaryResult, 29 October 2004//EN';
    }

    /**
     * set the journal id.
     *
     * @return bool TRUE if success
     */
    public function set_journal_id($jid)
    {
        if (in_array($jid, $this->_journal_ids)) {
            $this->_error_message = 'journal id '.$jid.' is already set';

            return false;
        }
        if (count($this->_journal_ids) >= 20) {
            $this->_error_message = 'too match register journal ids (>=20)';

            return false;
        }
        if (isset($this->_fetch_arguments['id'])) {
            $this->_fetch_arguments['id'] .= ','.$jid;
        } else {
            $this->_fetch_arguments['id'] = $jid;
        }

        return true;
    }

    /**
     * override function of start element handler.
     *
     */
    public function parser_start_element($attribs)
    {
        switch ($this->_parser_condition) {
        case '/eSummaryResult/DocSum':
            $this->_condition['docsum'] = array(
            'Id' => '',
            'Title' => '',
            'MedAbbr' => '',
            );
            break;
        case '/eSummaryResult/DocSum/Item':
            $this->_condition['name'] = $attribs['Name'];
            break;
        }
    }

    /**
     * override function of end element handler.
     *
     */
    public function parser_end_element()
    {
        switch ($this->_parser_condition) {
        case '/eSummaryResult/DocSum':
            $jid = $this->_condition['docsum']['Id'];
            $this->_data[$jid] = $this->_condition['docsum'];
            break;
        }
    }

    /**
     * override function of character data handler.
     *
     * @param string   $cdata  character data
     */
    public function parser_character_data($cdata)
    {
        switch ($this->_parser_condition) {
        case '/eSummaryResult/DocSum/Id':
            // Id*
            $this->_condition['docsum']['Id'] .= $cdata;
            break;
        case '/eSummaryResult/DocSum/Item':
            switch ($this->_condition['name']) {
            case 'Title':
                $this->_condition['docsum']['Title'] .= $cdata;
                break;
            case 'MedAbbr':
                $this->_condition['docsum']['MedAbbr'] .= $cdata;
                break;
            }
            break;
        }
    }
}

// $pubmed = new XooNIps_PubMed_ArticleSet();
// if ( ! $pubmed->set_pmid( 15582374 ) ) {
//   die( $pubmed->get_error_message() );
// }
// if ( ! $pubmed->set_pmid( 16984943 ) ) {
//   die( $pubmed->get_error_message() );
// }
// if ( ! $pubmed->fetch() ) {
//   die( $pubmed->get_error_message() );
// }
// if ( ! $pubmed->parse() ) {
//   die( $pubmed->get_error_message() );
// }
// $data =& $pubmed->_data;
// var_dump( $data );
// $pubmed = new XooNIps_PubMed_JournalEsearch();
// if ( ! $pubmed->set_journal_ta( 'Ann Rheum Dis' ) ) {
//   die( $pubmed->get_error_message() );
// }
// if ( ! $pubmed->fetch() ) {
//   die( $pubmed->get_error_message() );
// }
// if ( ! $pubmed->parse() ) {
//   die( $pubmed->get_error_message() );
// }
// $data =& $pubmed->_data;
// var_dump( $data );
// $pubmed = new XooNIps_PubMed_JournalEsummary();
// if ( ! $pubmed->set_journal_id( '640' ) ) {
//   die( $pubmed->get_error_message() );
// }
// if ( ! $pubmed->set_journal_id( '650' ) ) {
//   die( $pubmed->get_error_message() );
// }
// if ( ! $pubmed->fetch() ) {
//   die( $pubmed->get_error_message() );
// }
// if ( ! $pubmed->parse() ) {
//   die( $pubmed->get_error_message() );
// }
// $data =& $pubmed->_data;
// var_dump( $data );
