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

include_once dirname( dirname( __FILE__ ) ) . '/base/logic.class.php';

class XooNIpsLogicImportCheckConflict extends XooNIpsLogic
{
    function XooNIpsLogicImportCheckConflict(){
        parent::XooNIpsLogic();
    }
    
    function execute(&$vars, &$response){
        $this -> _import_items = $vars[0];
        $handler =& xoonips_gethandler( 'xoonips', 'import_item' );
        $handler -> findDuplicateItems( $this -> _import_items );
        
        $success = array( 'import_items' => $this -> _import_items,
                          'is_conflict' => $this -> _is_conflict(
                              $this -> _import_items ) );
        $response -> setResult( true );
        $response -> setSuccess( $success );
    }
    function _is_conflict( $import_items ){
        foreach( $import_items as $i ){
            if( count( $i -> getDuplicatePseudoId() ) > 0 ) return true;
            if( count( $i -> getDuplicateUpdatableItemId() ) > 0 ) return true;
            if( count( $i -> getDuplicateUnupdatableItemId() ) > 0 )
                return true;
            if( count( $i -> getDuplicateLockedItemId() ) > 0 ) return true;
        }
        return false;
    }
}
?>
