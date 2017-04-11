<?php

// $Revision: 1.1.2.4 $
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

class Junii2ListRecordsHandler extends ListRecordsHandler
{
    public $_support_tags = array(
        'ALTERNATIVE',
        'SUBJECT',
        'NIISUBJECT',
        'NDC',
        'NDLC',
        'BSH',
        'NDLSH',
        'MESH',
        'DDC',
        'LCC',
        'UDC',
        'LCSH',
        'DESCRIPTION',
        'PUBLISHER',
        'CONTRIBUTOR',
        'DATE',
        'TYPE',
        'NIITYPE',
        'FORMAT',
        'IDENTIFIER',
        'URI',
        'FULLTEXTURL',
        'ISSN',
        'NCID',
        'JTITLE',
        'VOLUME',
        'ISSUE',
        'SPAGE',
        'EPAGE',
        'DATEOFISSUED',
        'SOURCE',
        'LANGUAGE',
        'RELATION',
        'PMID',
        'DOI',
        'ISVERSIONOF',
        'HASVERSION',
        'ISREPLACEDBY',
        'REPLACES',
        'ISREQUIREDBY',
        'REQUIRES',
        'ISPARTOF',
        'HASPART',
        'ISREFERENCEDBY',
        'REFERENCES',
        'ISFORMATOF',
        'HASFORMAT',
        'COVERAGE',
        'SPATIAL',
        'NIISPATIAL',
        'TEMPORAL',
        'NIITEMPORAL',
        'RIGHTS',
        'TEXTVERSION',
        );

    public function Junii2ListRecordsHandler($_parser, $_baseURL)
    {
        parent::ListRecordsHandler($_parser, $_baseURL, 'junii2');
    }

    public function __construct($_parser, $_baseURL)
    {
        $this->Junii2ListRecordsHandler($_parser, $_baseURL);
    }

    public function startElementHandler($parser, $name, $attrs)
    {
        if ($this->getElementName($name) == 'JUNII2') {
            $this->_namespaces = $this->getNamespaceArray($attrs);
            array_push($this->tagstack, $name);
        } else {
            parent::startElementHandler($parser, $name, $attrs);
        }
    }

    public function endElementHandler($parser, $name)
    {
        if (isset($this->tagstack[3])
            && $this->getElementName($this->tagstack[3]) == 'HEADER'
            || !in_array($this->getElementName(end($this->tagstack)),
                          $this->_support_tags)) {
            parent::endElementHandler($parser, $name);
        } elseif ($this->getElementName(end($this->tagstack)) == 'DATE') {
            $this->_creation_date = $this->_cdata_buf;
            $this->search_text[] = $this->_cdata_buf;
            $this->addMetadataField(end($this->tagstack), $this->_cdata_buf,
                                    XOONIPS_METADATA_CATEGORY_DATE);
            array_pop($this->tagstack);
        } elseif ($this->getElementName(end($this->tagstack))
                  == 'DATEOFISSUED') {
            $this->_date = $this->_cdata_buf;
            $this->search_text[] = $this->_cdata_buf;
            $this->addMetadataField(
                end($this->tagstack), $this->_cdata_buf,
                XOONIPS_METADATA_CATEGORY_CREATION_DATE);
            array_pop($this->tagstack);
        } elseif ($this->getElementName(end($this->tagstack)) == 'URI') {
            $this->_resource_url[] = $this->_cdata_buf;
            $this->search_text[] = $this->_cdata_buf;
            $this->addMetadataField(
                end($this->tagstack), $this->_cdata_buf,
                XOONIPS_METADATA_CATEGORY_RESOURCE_LINK);
            array_pop($this->tagstack);
        } else {
            $this->addMetadataField(end($this->tagstack), $this->_cdata_buf);
            array_pop($this->tagstack);
        }
    }
}
