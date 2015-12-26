<?php
// $Revision: 1.1.2.11 $
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

class XooNIpsLogicImportReadFile extends XooNIpsLogic
{
    /**
     * result of XooNIpsResponse
     * @access private
     */
    var $_response = null;
    
    /**
     * import file path
     * @access private
     */
    var $_import_file_path = null;
    
    /**
     * zip extract dir path
     * @access private
     */
    var $_extract_dir = null;
    
    /**
     * array of items to import
     * @access private
     */
    var $_items = array();
    
    function XooNIpsLogicImportReadFile(){
        parent::XooNIpsLogic();
    }
    
    function execute(&$vars, &$response){
        $this -> _response =& $response;
        $this -> _import_file_path = $vars[0];
        $import_index_ids = $vars[1];
        
        $unzip =& xoonips_getutility( 'unzip' );
        if ( ! $unzip->open( $this -> _import_file_path ) ) {
            $response -> addError( E_XOONIPS_OPEN_FILE,
                                   "can't open file(" 
                                   . $this -> _import_file_path . ")" );
            $response -> setResult( false );
            $unzip->close();
            return false;
        }
        $fnames = $unzip->get_file_list();
        $unzip->close();
        
        if( !$this -> _extract_zip() ){
            $response -> addError( E_XOONIPS_OPEN_FILE, 
                                   "can't open file(" 
                                   . $this -> _import_file_path . ")" );
            $response -> setResult( false );
            return false;
        }
        
        $items = array();
        foreach( $fnames as $fname ){
            // read from extract_dir(not sub directories)
            if( dirname( $fname ) == "." && ctype_digit( basename( $fname, '.xml' ) ) ) {
                $handler =& xoonips_gethandler( 'xoonips', 'import_item' );
                $item =& $handler -> parseXml( file_get_contents( $this -> _extract_dir.'/'.$fname ) );
                $basic =& $item -> getVar('basic');
                $handler_item
                    =& $this -> _get_import_item_handler_by_item_type_id( 
                        $basic -> get( 'item_type_id' ) );
                $handler_item -> setAttachmentDirectoryPath(
                    $this -> _extract_dir );
                foreach( $import_index_ids as $index_id ){
                    $handler_item -> addImportIndexId($index_id);
                }
                $item =& $handler_item -> parseXml( file_get_contents( $this -> _extract_dir.'/'.$fname ) );
                $item -> setFilename( $fname );
                $this -> _items[] =& $item;
            }
        }

        $itemtype_handler =& xoonips_getormhandler( 'xoonips', 'item_type' );
        foreach( array_keys( $this -> _items ) as $key ){
            $basic =& $this -> _items[$key] -> getVar( 'basic' );
            $itemtype
                =& $itemtype_handler -> get( $basic -> get( 'item_type_id' ) );
            $handler
                =& xoonips_gethandler( $itemtype -> get( 'name' ),
                                       'import_item' );
            $handler -> onReadFileFinished(
                $this -> _items[$key], $this -> _items );
        }
        
        $this -> _check_import_items_and_set_errors();
        
        $success = array('import_items' => $this -> _items);
        $response -> setResult( true );
        $response -> setSuccess( $success );
        
        if( !$this -> _clean_files() ) return false;
    }
    
    /**
     * 
     * check all import items and set error
     * 
     */
    function _check_import_items_and_set_errors(){
        // every item has unique pseudo_id ?
        $pseudo_id2i = array();
        foreach ( $this -> _items as $i => $item ){
            $pseudo_id = $item -> getPseudoId();
            if( isset($pseudo_id2i[ $pseudo_id ]) ){
                foreach ( $pseudo_id2i[$pseudo_id] as $j ){
                    $this -> _items[ $i ] -> setErrors( 
                        E_XOONIPS_PSEUDO_ID_CONFLICT, 
                        " pseudo_id($pseudo_id) conflicts with " 
                        . $this -> _items[ $j ] -> getFilename());
                    $this -> _items[ $j ] -> setErrors(
                        E_XOONIPS_PSEUDO_ID_CONFLICT, 
                        " pseudo_id($pseudo_id) conflicts with " 
                        . $this -> _items[ $i ] -> getFilename() );
                }
                $pseudo_id2i[ $pseudo_id ][] = $i;
            }
            else
                $pseudo_id2i[ $pseudo_id ] = array( $i );
        }
        
        // every related_to has valid pseudo_id ?
        $valid_pseudo_ids = array();
        foreach ( $this -> _items as $item )
            $valid_pseudo_ids[ $item->getPseudoId() ] = true;
        foreach ( $this -> _items as $key => $item ){
            if( count( $item -> getVar( 'related_tos' ) ) == 0 ) continue;
            foreach ( $item -> getVar( 'related_tos' ) as $related_to ){
                if ( !isset( $valid_pseudo_ids[$related_to->get('item_id')] ) ){
                    $this -> _items[$key] -> setErrors(
                        E_XOONIPS_RELATED_ITEM_IS_NOT_FOUND, 
                        "unresolvable related_to(id="
                        .$related_to->get('item_id').")" );
                }
            }
        }
        
        //doi conflict in import file
        $doi2i = array();
        foreach ( $this -> _items as $i => $item ){
            $basic =& $item -> getVar( 'basic' );
            $doi = $basic -> get( 'doi' );
            if( empty($doi) ) continue;
            if( isset($doi2i[ $doi ]) ){
                foreach ( $doi2i[$doi] as $j ){
                    $this -> _items[ $i ] -> setErrors(
                        E_XOONIPS_DOI_CONFLICT, 
                        " doi($doi) conflicts with " 
                        . $this -> _items[ $j ] -> getFilename());
                    $this -> _items[ $j ] -> setErrors(
                        E_XOONIPS_DOI_CONFLICT, 
                        " doi($doi) conflicts with " 
                        . $this -> _items[ $i ] -> getFilename() );
                }
                $doi2i[ $doi ][] = $i;
            }
            else
                $doi2i[ $doi ] = array( $i );
        }
    }    

    /**
     * 
     * extract zip file to temp dir
     * 
     * @return bool
     * @private
     */
    function _extract_zip(){
        $unzip =& xoonips_getutility( 'unzip' );
        if ( ! $unzip->open( $this -> _import_file_path ) ) {
            $this -> _response -> addError( E_XOONIPS_OPEN_FILE, "can't open file(".$this -> _import_file_path.")" );
            return false;
        }
        $this -> _extract_dir = tempnam( '/tmp', 'XNP' );
        @unlink( $this -> _extract_dir );
        if( ! @mkdir( $this->_extract_dir, 0755 ) ) {
            $this -> _response -> addError( E_XOONIPS_FILE_SYSTEM, "can't mkdir(".$this -> _extract_dir.")" );
            $unzip->close();
            return false;
        }
        
        $fnames = $unzip->get_file_list();
        foreach( $fnames as $fname ){
            if( ! $unzip->extract_file( $fname, $this -> _extract_dir ) ) {
                $this -> _response -> addError( E_XOONIPS_FILE_SYSTEM, "can't extract file to ".$this->_extract_dir.'/'.$fname );
                $unzip->close();
                return false;
            }
        }
        $unzip->close();
        return true;
    }
    
    /** 
     * 
     * Clean all extrcted files and directories. 
     * @param path would be deleted. if path omitted,
     *  zip extracted dir is removed
     * @return true if succeed. false if failed removing files or directories.
     * 
     */
    function _clean_files($path=null){
        if( is_null( $path ) ){
            $path = $this -> _extract_dir;
        }
        foreach( glob( $path . "/*" ) as $file ){
            if( is_dir( $file ) ){
                if( !$this -> _clean_files( $file ) ) return false;
            }else{
                if( !unlink( $file ) ){
                    $this -> _response -> addError(
                        E_XOONIPS_FILE_SYSTEM, "can't remove file(${file})" );
                    return false;
                }
            }
        }
        if( !rmdir( $path ) ){
            $this -> _response -> addError(
                E_XOONIPS_FILE_SYSTEM, "can't remove directory(${path})" );
            return false;
        }
        return true;
    }
    
    function &_get_import_item_handler_by_item_type_id( $item_type_id ){
        $falseValue = false;
        $handler =& xoonips_getormhandler( 'xoonips', 'item_type' );
        $itemtype =& $handler -> get( $item_type_id );
        if( !$itemtype ) return $falseValue;
        return xoonips_gethandler( $itemtype -> get( 'name' ), 'import_item' );
    }
}
?>
