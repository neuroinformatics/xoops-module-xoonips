<?php
// $Revision: 1.1.2.10 $
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

include_once dirname( dirname( __DIR__ ) )
    . '/xoonips/class/xoonips_import_item.class.php';

class XNPPresentationImportItem extends XooNIpsImportItem{
    var $_has_presentation_file = false;
    var $_has_preview = false;

    function XNPPresentationImportItem(){
        $handler =& xoonips_getormcompohandler( 'xnppresentation', 'item' );
        $this -> _item =& $handler -> create();
    }

    function setHasPresentationFile( ){
        $this -> _has_presentation_file = true;
    }
    
    function unsetHasPresentationFile( ){
        $this -> _has_presentation_file = false;
    }
    
    function hasPresentationFile( ){
        return $this -> _has_presentation_file;
    }
    
    function setHasPreview( ){
        $this -> _has_preview = true;
    }
    
    function unsetHasPreview( ){
        $this -> _has_preview = false;
    }
    
    function hasPreview( ){
        return $this -> _has_preview;
    }
    
    /**
     * get total file size(bytes) of this item
     * @return integer file size in bytes.
     */
    function getTotalFileSize(){
        $size = 0;
        $mainfile =& $this -> getVar( 'presentation_file' );
        if( !$mainfile ) return 0;
        $size = $mainfile -> get( 'file_size' );
        foreach( $this -> getVar( 'preview' ) as $preview ){
            $size += $preview -> get( 'file_size' );
        }
        return $size;
    }
    
    function &getClone( ){
        $clone =& parent::getClone();
        $clone -> _has_presentation_file = $this -> _has_presentation_file;
        $clone -> _has_preview = $this -> _has_preview;
        return $clone;
    }
}

class XNPPresentationImportItemHandler extends XooNIpsImportItemHandler{
    /**
     * array of supported version of import file
     */
    var $_import_file_version = array( "1.00", "1.01", "1.02", "1.03" );

    /**
     * version string of detail information
     */
    var $_detail_version = null;

    /**
     * attachment file object(XooNIpsFile)
     */
    var $_presentation_file = null;
    
    /**
     * flag of attachment file parsed
     */
    var $_presentation_file_flag = false;
    
    /**
     * attachment file object(XooNIpsFile)
     */
    var $_preview = null;
    
    /**
     * flag of attachment file parsed
     */
    var $_preview_flag = false;
    
    /**
     * attachment_dl_limit flag
     */
    var $_attachment_dl_limit_flag = false;
    
    /**
     * attachment_dl__notify_limit flag
     */
    var $_attachment_dl_notify_limit_flag = false;
    
    
    var $_file_type_attribute = null;
    
    function XNPPresentationImportItemHandler(){
        parent::XooNIpsImportItemHandler();
    }

    function create(){
        return new XNPPresentationImportItem();
    }

    /**
     * 
     * @param
     * @return void
     */
    function xmlStartElementHandler( $parser , $name , $attribs ){
        global $xoopsDB;
        parent::xmlStartElementHandler($parser, $name, $attribs);

        switch( implode( '/', $this -> _tag_stack ) ){
        case "ITEM/DETAIL":
            // 
            // validate version and set it to 'version' variable
            // 
            if( !empty( $attribs['VERSION'] ) ){
                if( in_array( $attribs['VERSION'],
                              $this -> _import_file_version ) ){
                    $this -> _detail_version = $attribs['VERSION'];
                    
                }else{
                    $this -> _import_item -> setErrors( 
                        E_XOONIPS_INVALID_VALUE,
                        "unsupported version(" . $attribs['VERSION'] . ") " 
                        . $this -> _get_parser_error_at() );
                }
            }else{
                $this -> _detail_version = '1.00';
            }
            break;
        case "ITEM/DETAIL/FILE":
            $this -> _file_type_attribute = $attribs['FILE_TYPE_NAME'];
            
            $file_type_handler =& xoonips_getormhandler( 'xoonips',
                                                         'file_type' );
            $file_handler =& xoonips_getormhandler( 'xoonips', 'file' );
            $criteria = new Criteria(
                'name', addslashes( $attribs['FILE_TYPE_NAME'] ) );
            $file_type =& $file_type_handler -> getObjects( $criteria );
            if( count( $file_type ) == 0 ){
                $this -> _import_item -> setErrors( 
                    E_XOONIPS_ATTR_NOT_FOUND,
                    "file_type_id is not found:" . $attribs['FILE_TYPE_NAME'] 
                    . $this -> _get_parser_error_at( ) );
                break;
            }
            
            $unicode =& xoonips_getutility( 'unicode' );
            if( $this -> _file_type_attribute == 'presentation_file' ){
                if( $this -> _presentation_file_flag ){
                    $this -> _import_item -> setErrors( 
                        E_XOONIPS_ATTACHMENT_HAS_REDUNDANT,
                        "multiple $name attachments is not allowed" 
                        . $this -> _get_parser_error_at( ) );
                    break;
                }
                $this -> _presentation_file =& $file_handler -> create();
                $this -> _presentation_file -> setFilepath(
                    $this -> _attachment_dir . '/' . $attribs['FILE_NAME'] );
                $this -> _presentation_file -> set(
                    'original_file_name',
                    $unicode->decode_utf8($attribs['ORIGINAL_FILE_NAME'],
                                          xoonips_get_server_charset(),'h') );
                $this -> _presentation_file -> set(
                    'mime_type', $attribs['MIME_TYPE'] );
                $this -> _presentation_file -> set(
                    'file_size', $attribs['FILE_SIZE'] );
                $this -> _presentation_file -> set( 'sess_id', session_id() );
                $this -> _presentation_file -> set(
                    'file_type_id', $file_type[0] -> get( 'file_type_id' ) );
            }else if( $this -> _file_type_attribute == 'preview' ){
                $this -> _preview =& $file_handler -> create();
                $this -> _preview -> setFilepath(
                    $this -> _attachment_dir . '/' . $attribs['FILE_NAME'] );
                $this -> _preview -> set(
                    'original_file_name',
                    $unicode->decode_utf8($attribs['ORIGINAL_FILE_NAME'],
                                          xoonips_get_server_charset(),'h') );
                $this -> _preview -> set( 'mime_type', $attribs['MIME_TYPE'] );
                $this -> _preview -> set( 'file_size', $attribs['FILE_SIZE'] );
                $this -> _preview -> set( 'sess_id', session_id() );
                $this -> _preview -> set(
                    'file_type_id', $file_type[0] -> get( 'file_type_id' ) );
            }else{
                $this -> _import_item -> setErrors( 
                    E_XOONIPS_ATTR_NOT_FOUND,
                    "file_type_id is not found:" . $attribs['FILE_TYPE_NAME'] 
                    . $this -> _get_parser_error_at( ) );
                break;
            }
            break;
        }
    }

    /**
     * 
     * @param
     * @return void
     */
    function xmlEndElementHandler( $parser ,$name ){
        global $xoopsDB;
        $detail =& $this -> _import_item -> getVar( 'detail' );
        $unicode =& xoonips_getutility( 'unicode' );
        
        switch( implode( '/', $this -> _tag_stack ) ){
        case "ITEM/DETAIL":
            if( $this -> _detail_version == '1.00' ){
            }else if( $this -> _detail_version == '1.01' ){
                $keys[] = 'attachment_dl_limit';
            }else{
                $keys[] = 'attachment_dl_notify';
            }
            
            foreach ( array(
                'presentation_type',
                'rights',
                'readme',
                'use_cc',
                'cc_commercial_use',
                'cc_modification') as $key )
                
                if ( is_null($detail -> get($key,'n')) )
                    $this -> _import_item -> setErrors( 
                        E_XOONIPS_TAG_NOT_FOUND,
                        " no $key" 
                        . $this -> _get_parser_error_at( ) );
            //error if no creators
            if(count($this -> _import_item -> getVar('creator'))==0){
                $this -> _import_item -> setErrors(E_XOONIPS_TAG_NOT_FOUND, " no creator" 
                                                   . $this -> _get_parser_error_at( ) );
            }
            break;
        case "ITEM/DETAIL/PRESENTATION_TYPE":
        case "ITEM/DETAIL/RIGHTS": 
        case "ITEM/DETAIL/README": 
        case "ITEM/DETAIL/USE_CC": 
        case "ITEM/DETAIL/CC_COMMERCIAL_USE": 
        case "ITEM/DETAIL/CC_MODIFICATION": 
            $detail -> set(
                strtolower( end( $this -> _tag_stack ) ) , 
                $unicode->decode_utf8($this->_cdata ,
                                      xoonips_get_server_charset(),'h'),
                true );
            break;
        case 'ITEM/DETAIL/CREATORS/CREATOR':
            if( $this -> _detail_version != '1.03' ){
                break;
            }
            $creators =& $this -> _import_item ->getVar('creator');
            
            $creator_handler =& xoonips_getormhandler( 'xnppresentation', 'creator' );
            $creator=&$creator_handler->create();
            
            $creator->set('creator', $unicode->decode_utf8($this -> _cdata ,
                                                         xoonips_get_server_charset(),'h'));
            $creator->set('creator_order', count($creators));
            
            $creators[] = $creator;
            break;
        case "ITEM/DETAIL/CREATOR":
            if( $this -> _detail_version != '1.00'
                && $this -> _detail_version != '1.01'
                && $this -> _detail_version != '1.02' ){
                //<creator> is only for 1.00, 1.01 and 1.02
                break;
            }
            $creator_handler =& xoonips_getormhandler( 'xnppresentation', 'creator' );
            $creators =& $this -> _import_item ->getVar('creator');
            $creator=&$creator_handler->create();
            $creator->set('creator', trim($unicode->decode_utf8($this -> _cdata ,
                                                              xoonips_get_server_charset(),'h')));
            $creator->set('creator_order', 0);
            $creators[0] = $creator;
            break;
        case "ITEM/DETAIL/ATTACHMENT_DL_LIMIT":
            if( $this -> _attachment_dl_limit_flag ){
                $this -> _import_item -> setErrors( 
                    E_XOONIPS_TAG_REDUNDANT,
                    "attachment_dl_limit is redundant" 
                    . $this -> _get_parser_error_at() );
            }else if( ctype_digit( $this -> _cdata ) ){
                $detail -> set(
                    'attachment_dl_limit', intval( $this -> _cdata ) );
                $this -> _attachment_dl_limit_flag = true;
            }else{
                $this -> _import_item -> setErrors( 
                    E_XOONIPS_INVALID_VALUE,
                    "invalid value(" . $this -> _cdata
                    . ") of attachment_dl_limit" 
                    . $this -> _get_parser_error_at() );
            }
            break;
        case "ITEM/DETAIL/ATTACHMENT_DL_NOTIFY":
            if( $this -> _attachment_dl_notify_limit_flag ){
                $this -> _import_item -> setErrors( 
                    E_XOONIPS_TAG_REDUNDANT,
                    "attachment_dl_notify is redundant" 
                    . $this -> _get_parser_error_at() );
            }else if( ctype_digit( $this -> _cdata ) ){
                $detail -> set(
                    'attachment_dl_notify', intval( $this -> _cdata ) );
                $this -> _attachment_dl_notify_limit_flag = true;
            }else{
                $this -> _import_item -> setErrors( 
                    E_XOONIPS_INVALID_VALUE,
                    "invalid value(" . $this -> _cdata
                    . ") of attachment_dl_notify" 
                    . $this -> _get_parser_error_at() );
            }
            break;
        case "ITEM/DETAIL/FILE":
            $file_handler =& xoonips_getormhandler( 'xoonips', 'file' );
            if( $this -> _file_type_attribute == 'presentation_file' ){
                $this -> _presentation_file_flag = true;
                if( !$file_handler -> insert( $this -> _presentation_file ) ){
                    $this -> _import_item -> setErrors( 
                        E_XOONIPS_DB_QUERY,
                        "can't insert attachment file:"
                        . $this -> _presentation_file -> get(
                            'original_file_name' ) 
                        . $this -> _get_parser_error_at() );
                }
                $this -> _presentation_file
                    = $file_handler -> get(
                        $this -> _presentation_file -> get( 'file_id' ) );
                $this -> _import_item -> setVar(
                    'presentation_file', $this -> _presentation_file );
                $this -> _import_item -> setHasPresentationFile();
                $this -> _file_type_attribute = null;
            }else if( $this -> _file_type_attribute == 'preview' ){
                $this -> _preview_flag = true;
                if( !$file_handler -> insert( $this -> _preview ) ){
                    $this -> _import_item -> setErrors( 
                        E_XOONIPS_DB_QUERY,
                        "can't insert attachment file:"
                        . $this -> _preview -> get( 'original_file_name' ) 
                        . $this -> _get_parser_error_at() );
                }
                $this -> _preview = $file_handler -> get(
                    $this -> _preview -> get( 'file_id' ) );
                $previews =& $this -> _import_item -> getVar( 'preview' );
                $previews[] = $this -> _preview;
                $this -> _import_item -> setHasPreview();
                $this -> _file_type_attribute = null;
            }else{
                die( 'unknown file type:'.$this -> _file_type_attribute );
            }
            break; 

        case 'ITEM/DETAIL/FILE/CAPTION':
            if( $this -> _file_type_attribute == 'presentation_file' ){
                $this -> _presentation_file -> set(
                    'caption', 
                    $unicode->decode_utf8($this -> _cdata ,
                                          xoonips_get_server_charset(),'h') );
            }else if( $this -> _file_type_attribute == 'preview' ){
                $this -> _preview -> set(
                    'caption', 
                    $unicode->decode_utf8($this -> _cdata ,
                                          xoonips_get_server_charset(),'h') );
            }
            break; 
        case 'ITEM/DETAIL/FILE/THUMBNAIL':
            if( $this -> _file_type_attribute == 'presentation_file' ){
                $this -> _presentation_file -> set(
                    'thumbnail_file', base64_decode( $this -> _cdata ) );
            }else if( $this -> _file_type_attribute == 'preview' ){
                $this -> _preview -> set( 
                    'thumbnail_file', base64_decode( $this -> _cdata ) );
            }
            break;
        }
    
        parent::xmlEndElementHandler($parser, $name);
    }


    /**
     * 
     * Update item_id and sess_id of xoonips_file.
     * 
     * @param $item XooNIpsImportItem that is imported.
     * @param $import_items array of all of XooNIpsImportItems
     */
    function onImportFinished( &$item, &$import_items ){
        if( 'xnppresentationimportitem' != strtolower( get_class($item) ) )
            return;
        
        $this -> _set_file_delete_flag($item);
        
        // nothing to do if no file
        if( $item -> hasPresentationFile() ){
            $presentation_file =& $item -> getVar( 'presentation_file' );
            $this -> _fix_item_id_of_file( $item, $presentation_file );
            $this -> _create_text_search_index( $presentation_file );
        }
        
        // nothing to do if no previews
        $previews =& $item -> getVar( 'preview' );
        foreach(array_keys( $previews ) as $key ){
            if( $previews[$key] -> get( 'file_id' ) > 0 ){
                $this -> _fix_item_id_of_file( $item, $previews[$key] );
                $this -> _create_text_search_index( $previews[$key] );
            }
        }
        
        parent::onImportFinished( $item, $import_items );
    }
    
    function insert( &$item ){
        $handler =& xoonips_getormcompohandler( 'xnppresentation', 'item' );
        return $handler -> insert($item);
    }
    
    function setNew( &$item ){
        $handler =& xoonips_getormcompohandler( 'xnppresentation', 'item' );
        return $handler -> setNew($item);
    }
    
    function unsetNew( &$item ){
        $handler =& xoonips_getormcompohandler( 'xnppresentation', 'item' );
        return $handler -> unsetNew($item);
    }
    
    function setDirty( &$item ){
        $handler =& xoonips_getormcompohandler( 'xnppresentation', 'item' );
        return $handler -> setDirty($item);
    }
    
    function unsetDirty( &$item ){
        $handler =& xoonips_getormcompohandler( 'xnppresentation', 'item' );
        return $handler -> unsetDirty($item);
    }
    

    /**
     * insert into xnppresentation_item_detail_child_* table
     * @return false if failed, true if insert successfully or already exists and unchanged.
     */
    function insert_child(){
        global $xoopsDB;
        
        
        $names = array( 
          'creator'             ,
        );
        
        foreach ( $names as $name ){
            $i = 0;
            foreach ( $this->cleanVars[ "detail_${name}" ] as $keyword ){
                $keys = implode( ",", array(
                    'presentation_id'                  ,
                    "${name}"                    ,
                    "${name}_order"              ,
                ) );
                $vals = implode( ",", array(
                    $this -> getVar( 'item_id' ),
                    $xoopsDB -> quoteString( $keyword ),
                    $i++,
                ) );
                $sql = "INSERT INTO " . $xoopsDB->prefix("xnppresentation_item_detail_child_${name}") . " ( $keys ) VALUES ( $vals ) ";
                $result = $xoopsDB->query($sql);
                if (!$result) {
                    $this -> setErrors( E_XOONIPS_DB_QUERY, $xoopsDB -> error() . $this -> getErrorAt( __LINE__, __FILE__, __FUNCTION__ ) );
                    return false;
                }
            }
        }
        
        return true;
    }
    

    /**
     * reeturn import log text of import item.
     * @param $import_item reference of XooNIpsImportItem object
     * @return string import log text
     */
    function getImportLog($import_item){
        $text = parent::getImportLog($import_item);
        $detail =& $import_item -> getVar( 'detail' );
        
        $text .= "\ndetail.presentation_type " 
            . $detail -> get( "presentation_type" );
        foreach( $import_item -> getVar( 'creator' ) as $creator ){
            $text .= "\ndetail.creator ". $creator->get('creator');
        }
        $text .= "\ndetail.use_cc " . $detail -> get( "use_cc" );
        $text .= "\ndetail.cc_commercial_use " 
            . $detail -> get( "cc_commercial_use" );
        $text .= "\ndetail.cc_modification " 
            . $detail -> get( "cc_modification" );
        $text .= "\ndetail.rights "
            . mb_ereg_replace( '\n', '\n',
                               mb_ereg_replace(
                                   '\\\\', '\\\\',
                                   $detail -> get( 'rights', 'n' )));
        $text .= "\ndetail.readme "
            . mb_ereg_replace( '\n', '\n', 
                               mb_ereg_replace(
                                   '\\\\', '\\\\',
                                   $detail -> get( 'readme', 'n' )));
        $text .= "\ndetail.attachment_dl_limit " 
            . $detail -> get( "attachment_dl_limit" );
        $text .= "\ndetail.attachment_dl_notify " 
            . $detail -> get( "attachment_dl_notify" );

        return $text;
    }

    function import(&$item){
        if ( $item -> getUpdateFlag() ) {
            $detail =& $item -> getVar( 'detail' );
            $detail -> unsetNew();
            $detail -> setDirty();
            
            //copy attachment file
            if( $item -> hasPresentationFile() ){
                $presentation_file =& $item -> getVar( 'presentation_file' );
                $file_handler =& xoonips_getormhandler( 'xoonips', 'file' );
                $clonefile =& $file_handler -> fileClone( $presentation_file );
                $clonefile -> setDirty();
                $item -> setVar( 'presentation_file', $clonefile );
            }
            if( $item -> hasPreview() ){
                $file_handler =& xoonips_getormhandler( 'xoonips', 'file' );
                $previews = array();
                foreach( $item -> getVar( 'preview' ) as $preview ){
                    $clonefile =& $file_handler -> fileClone( $preview );
                    $clonefile -> setDirty();
                    $previews[] =& $preview;
                }
                $item -> setVar( 'preview', $previews );
            }
        }
        parent::import($item);
    }
}

?>
