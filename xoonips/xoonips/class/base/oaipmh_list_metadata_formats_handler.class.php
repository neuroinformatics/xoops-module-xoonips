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

class ListMetadataFormatsHandler extends HarvesterHandler {
    var $metadataPrefix;
    var $tagstack;

    function ListMetadataFormatsHandler( $_parser ) {
        parent::HarvesterHandler( $_parser );

        $this->metadataPrefix = "oai_dc";
        $this->tagstack = array( );
    }
    function __construct( $_parser ) {
        $this->ListMetadataFormatsHandler( $_parser );
    }

    function startElementHandler( $parser, $name, $attribs ) {
        array_push( $this->tagstack, $name );
    }

    function endElementHandler( $parser, $name ) {
        array_pop( $this->tagstack );
    }

    function characterDataHandler( $parser, $data ) {
        if( end( $this->tagstack ) == 'METADATAPREFIX' ) {
            if( $data == "junii" && $this->metadataPrefix == "oai_dc") {
                $this->metadataPrefix = $data;
            }
            else if( $data == "junii2" ) {
                $this->metadataPrefix = $data;
            }
        }
    }

    function getMetadataPrefix( ) {
        return $this->metadataPrefix;
    }
}

