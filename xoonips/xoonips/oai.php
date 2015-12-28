<?php
// $Revision: 1.8.2.1.2.6 $
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

error_reporting(0);
include 'include/common.inc.php';

$session = session_id();
$sess_handler =& xoops_gethandler('session');
if( $sess_handler -> write( $session, session_encode( ) ) ){
    $_SESSION['XNPSID'] = $session;
}

include_once 'include/lib.php';
include_once 'include/AL.php';
include_once 'class/base/oaipmh.class.php';

// retrieve admin's e-mail
$emails = array();
$member_handler =& xoops_gethandler('member');
$members = $member_handler->getUsersByGroup(XOOPS_GROUP_ADMIN, false);
foreach( $members as $userid ){
	$user =& $member_handler->getUser($userid);
	$emails[] = $user -> getVar( 'email' );
}

$xconfig_handler=&xoonips_getormhandler('xoonips', 'config');
$repositoryName = $xconfig_handler->getValue('repository_name');

$pmh = new OAIPMH( XOOPS_URL."/modules/xoonips/oai.php", $repositoryName, $emails );
$pmh -> addHandler( new JUNIIHandler() );
$pmh -> addHandler( new JUNII2Handler() );
$pmh -> addHandler( new OAI_DCHandler() );

$args = array();
foreach( array( 'verb', 'metadataPrefix', 'set', 'from', 'until', 'identifier', 'resumptionToken' ) as $k ){
	if( isset( $_GET[ $k ] ) ) $args[$k] = $_GET[ $k ];
	else if( isset( $_POST[ $k ] ) ) $args[$k] = $_POST[ $k ];
}

header( "Content-Type: application/xml" );

if( !isset( $args['verb'] ) ){
	echo $pmh -> header()
		.$pmh -> request( $args )
		.$pmh -> error( 'badVerb', 'no verb' )
		.$pmh -> footer();
	exit();
}
if( $args['verb'] == 'GetRecord' ){
	echo $pmh -> GetRecord( $args );
}else if( $args['verb'] == 'Identify' ){
	echo $pmh -> Identify( );
}else if( $args['verb'] == 'ListIdentifiers' ){
	echo $pmh -> ListIdentifiers( $args );
}else if( $args['verb'] == 'ListMetadataFormats' ){
	echo $pmh -> ListMetadataFormats( $args );
}else if( $args['verb'] == 'ListRecords' ){
	echo $pmh -> ListRecords( $args );
}else if( $args['verb'] == 'ListSets' ){
	echo $pmh -> ListSets( $args );
}else{
	echo $pmh -> header()
		.$pmh -> request( $args )
		.$pmh -> error( 'badVerb', 'illegal verb' )
		.$pmh -> footer();
}
?>
