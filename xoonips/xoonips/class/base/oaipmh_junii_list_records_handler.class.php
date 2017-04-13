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

class JuniiListRecordsHandler extends ListRecordsHandler
{
    public $_identifierTypeAttr;
    public $_support_tags = array(
        'CODE',
        'USERID',
        'FANO',
        'ADATE',
        'UDATE',
        'INSTITUTION',
        'TITLE.TRANSCRIPTION',
        'TITLE.ALTERNATIVE',
        'CREATOR.TRANSCRIPTION',
        'CREATOR.ALTERNATIVE',
        'SUBJECT',
        'DESCRIPTION',
        'PUBLISHER',
        'PUBLISHER.TRANSCRIPTION',
        'PUBLISHER.ALTERNATIVE',
        'CONTRIBUTOR',
        'CONTRIBUTOR.TRANSCRIPTION',
        'CONTRIBUTOR.ALTERNATIVE',
        'DATE',
        'DATE.CREATED',
        'DATE.MODIFIED',
        'TYPE',
        'FORMAT',
        'IDENTIFIER',
        'SOURCE',
        'LANGUAGE',
        'RELATION.ISVERSIONOF',
        'RELATION.HASVERSION',
        'RELATION.ISREPLACEDBY',
        'RELATION.REPLACES',
        'RELATION.ISREQUIREDBY',
        'RELATION.REQUIRES',
        'RELATION.ISPARTOF',
        'RELATION.HASPART',
        'RELATION.ISREFERENCEDBY',
        'RELATION.REFERENCES',
        'RELATION.ISFORMATOF',
        'RELATION.HASFORMAT',
        'RELATION.HASIMAGEOF',
        'COVERAGE',
        'COVERAGE.SPATIAL',
        'COVERAGE.TEMPORAL',
        'RIGHTS',
        'COMMENT',
        'AHDNG',
        'AID', );

    public function __construct($_parser, $_baseURL)
    {
        parent::__construct($_parser, $_baseURL, 'junii');
        $this->_identifierTypeAttr = '';
    }

    public function startElementHandler($parser, $name, $attribs)
    {
        if ($this->getElementName($name) == 'IDENTIFIER') {
            if ($this->isTypeIsURL($attribs)) {
                $this->_identifierTypeAttr = 'URL';
            } else {
                $this->_identifierTypeAttr = '';
            }
            $this->_cdata_buf = '';
            array_push($this->tagstack, $name);
        } elseif ($this->getElementName($name) == 'META') {
            $this->_namespaces = $this->getNamespaceArray($attribs);
            array_push($this->tagstack, $name);
        } else {
            parent::startElementHandler($parser, $name, $attribs);
        }
    }

    public function endElementHandler($parser, $name)
    {
        if (isset($this->tagstack[3]) && $this->getElementName($this->tagstack[3]) == 'HEADER' || !in_array($this->getElementName(end($this->tagstack)), $this->_support_tags)
        ) {
            parent::endElementHandler($parser, $name);
        } elseif ($this->getElementName(end($this->tagstack)) == 'DATE.MODIFIED'
        ) {
            $this->_last_update_date = $this->_cdata_buf;
            $this->search_text[] = $this->_cdata_buf;
            $this->addMetadataField(end($this->tagstack), $this->_cdata_buf, XOONIPS_METADATA_CATEGORY_LAST_UPDATE_DATE);
            array_pop($this->tagstack);
        } elseif ($this->getElementName(end($this->tagstack)) == 'DATE.CREATED') {
            $this->_creation_date = $this->_cdata_buf;
            $this->search_text[] = $this->_cdata_buf;
            $this->addMetadataField(end($this->tagstack), $this->_cdata_buf, XOONIPS_METADATA_CATEGORY_CREATION_DATE);
            array_pop($this->tagstack);
        } elseif ($this->getElementName(end($this->tagstack)) == 'DATE') {
            $this->_date = $this->_cdata_buf;
            $this->search_text[] = $this->_cdata_buf;
            $this->addMetadataField(end($this->tagstack), $this->_cdata_buf, XOONIPS_METADATA_CATEGORY_DATE);
            array_pop($this->tagstack);
        } elseif ($this->getElementName(end($this->tagstack)) == 'IDENTIFIER'
            && $this->_identifierTypeAttr == 'URL'
        ) {
            $this->_resource_url[] = $this->_cdata_buf;
            $this->search_text[] = $this->_cdata_buf;
            $this->addMetadataField(end($this->tagstack), $this->_cdata_buf, XOONIPS_METADATA_CATEGORY_RESOURCE_LINK);
            array_pop($this->tagstack);
        } else {
            $this->addMetadataField(end($this->tagstack), $this->_cdata_buf);
            array_pop($this->tagstack);
        }
    }

    public function isTypeIsURL($attribs)
    {
        foreach ($attribs as $key => $val) {
            $tmp = preg_split('/:/', $key);
            if (end($tmp) == 'TYPE') {
                return $val == 'URL';
            }
        }

        return false;
    }
}
