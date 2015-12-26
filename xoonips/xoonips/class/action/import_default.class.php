<?php
// $Revision: 1.1.2.13 $
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

include_once dirname( dirname( __FILE__ ) ) . '/base/action.class.php';

class XooNIpsActionImportDefault extends XooNIpsAction{
    
    function XooNIpsActionImportDefault(){
        parent::XooNIpsAction();
    }
    
    function _get_logic_name(){
        return null;
    }
    
    function _get_view_name(){
        return "import_default";
    }
    
    function preAction(){
        xoonips_deny_guest_access();
        xoonips_allow_both_method();
    }
    
    function doAction(){
        global $xoopsUser;
        
        $zipfile = $this->_formdata->getFile( 'zipfile', false );
        
        $result = array(
            'max_file_size_bytes' => $this->_get_upload_max_filesize(),
            'max_file_size' => ini_get( 'upload_max_filesize' ) ,
            'xoonips_checked_xid' => $this->_formdata->getValue( 'post', 'xoonipsCheckedXID', 's', false ),
            'zipfile_is_given' => ( $zipfile !== NULL
                                    || is_array( $zipfile )
                                    && !array_key_exists( 'name', $zipfile )
                                    || $zipfile['tmp_name'] == '' 
                                    || $zipfile['size'] == 0 ),
            'admin' => isset( $_SESSION[ 'xoonips_old_uid' ] )
            || $xoopsUser -> isAdmin() );
        //$this -> _response -> setResult( true );
        //$this -> _response -> setSuccess( $result );
        $this -> _view_params['max_file_size_bytes'] = $this->_get_upload_max_filesize();
        $this -> _view_params['max_file_size']       
            = ini_get( 'upload_max_filesize' ) ;
        $this -> _view_params['xoonips_checked_xid'] 
            = $this->_formdata->getValue( 'post', 'xoonipsCheckedXID', 's', false );
        $this -> _view_params['zipfile_is_given'] 
            = ( $zipfile !== NULL
                || is_array( $zipfile ) && !array_key_exists( 'name', $zipfile )
                || $zipfile['tmp_name'] == '' 
                || $zipfile['size'] == 0 );
        $this -> _view_params['admin'] 
            = isset( $_SESSION[ 'xoonips_old_uid' ] )
            || $xoopsUser -> isAdmin();
        
        global $xoonipsTreeCheckBox, $xoonipsEditIndex, $xoonipsEditPublic;
        $xoonipsTreeCheckBox = true;
        $xoonipsEditIndex = true; //only import into editable index
        $xoonipsEditPublic = true; //
    }

  /**
   * get upload max file size from PHP settings
   *
   * @access private
   * @return int upload max file size
   */
  function _get_upload_max_filesize() {
    $val = ini_get( 'upload_max_filesize');
    if ( $val === '' || $val == -1 ) {
      // unlimit
      $val = '2G';
    }
    if ( preg_match( '/^(-?\d+)([KMG])$/i', strtoupper( $val ), $matches ) ) {
      $val = intval( $matches[1] );
      switch( $matches[2] ) {
      case 'G':
        $val *= 1024;
      case 'M':
        $val *= 1024;
      case 'K':
        $val *= 1024;
      }
    } else {
      $val = intval( $val );
    }
    return $val;
  }
}

?>
