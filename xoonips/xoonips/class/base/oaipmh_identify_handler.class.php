<?php
// $Revision: 1.1.2.3 $
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

class IdentifyHandler extends HarvesterHandler {
    var $_dateFormat;
    var $_earliestDatestamp;
    var $_tagstack;
    var $_repositoryName;
    function IdentifyHandler( $_parser ) {
        parent::HarvesterHandler( $_parser );
        $this->_earliestDatestamp = null;
        $this->_dateFormat = null;
        $this->_tagstack = array( );
        $this->_repositoryName = '';
    }
    function __construct( $_parser ) {
        $this->IdentifyHandler( $_parser );
    }

    function startElementHandler( $parser, $name, $attribs ) {
        array_push( $this->_tagstack, $name );
    }

    function endElementHandler( $parser, $name ) {
        array_pop( $this->_tagstack );
    }

    function characterDataHandler( $parser, $data ) {
        if( end( $this->_tagstack ) == 'GRANULARITY' ) {
            if( $data == "YYYY-MM-DDThh:mm:ssZ" )
                $this->_dateFormat = "Y-m-d\TH:i:s\Z";
            else if( $data == "YYYY-MM-DD" )
                $this->_dateFormat = "Y-m-d";
            else
                $this->_dateFormat = false;
        } else if( end( $this->_tagstack ) == 'EARLIESTDATESTAMP' ) {
            $this->_earliestDatestamp = ISO8601toUTC( $data );
        } else if( end( $this->_tagstack ) == 'REPOSITORYNAME' ) {
            $this->_repositoryName.=$data;
        }
    }

    function getDateFormat( ) {
        return $this->_dateFormat;
    }
    function getEarliestDatestamp( ) {
        return $this->_earliestDatestamp;
    }
    function getRepositoryName(){
        return $this->_repositoryName;
    }
}

