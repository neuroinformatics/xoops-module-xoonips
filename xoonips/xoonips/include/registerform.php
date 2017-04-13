<?php

// $Revision: 1.12.2.1.2.8 $
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

if (!defined('XOOPS_ROOT_PATH')) {
    exit();
}
require_once XOOPS_ROOT_PATH.'/class/xoopslists.php';
require_once XOOPS_ROOT_PATH.'/class/xoopsformloader.php';

$myxoopsConfig = &xoonips_get_xoops_configs(XOOPS_CONF);
$myxoopsConfigUser = &xoonips_get_xoops_configs(XOOPS_CONF_USER);

$textutil = &xoonips_getutility('text');

$email_tray = new XoopsFormElementTray(_US_EMAIL._MD_XOONIPS_ACCOUNT_REQUIRED_MARK, '<br />');
$email_text = new XoopsFormText('', 'email', 25, 60, $textutil->html_special_chars($email));
$email_option = new XoopsFormCheckBox('', 'user_viewemail', $user_viewemail);
$email_option->addOption(1, _US_ALLOWVIEWEMAIL);
$email_tray->addElement($email_text);
$email_tray->addElement($email_option);

//$avatar_select = new XoopsFormSelect( '', 'user_avatar', $user_avatar );
//$avatar_array =& XoopsLists::getImgListAsArray( XOOPS_ROOT_PATH.'/images/avatar/' );
//$avatar_select->addOptionArray( $avatar_array );
//$a_dirlist =& XoopsLists::getDirListAsArray( XOOPS_ROOT_PATH.'/images/avatar/' );
//$a_dir_labels = array();
//$a_count = 0;
//$a_dir_link = '<a href="javascript:openWithSelfMain(\''.XOOPS_URL.'/misc.php?action=showpopups&amp;type=avatars&amp;start='.$a_count.'\',\'avatars\',600,400);">XOOPS</a>';
//$a_count = $a_count + count( $avatar_array );
//$a_dir_labels[] = new XoopsFormLabel( '', $a_dir_link );
//foreach ( $a_dirlist as $a_dir ) {
//  if ( $a_dir == 'users' ) {
//    continue;
//  }
//  $avatars_array =& XoopsLists::getImgListAsArray( XOOPS_ROOT_PATH.'/images/avatar/'.$a_dir.'/', $a_dir.'/' );
//  $avatar_select->addOptionArray( $avatars_array );
//  $a_dir_link = '<a href="javascript:openWithSelfMain(\''.XOOPS_URL.'/misc.php?action=showpopups&amp;type=avatars&amp;subdir='.$a_dir.'&amp;start='.$a_count.'\',\'avatars\',600,400);">'.$a_dir.'</a>';
//  $a_dir_labels[] = new XoopsFormLabel( '', $a_dir_link );
//  $a_count = $a_count + count( $avatars_array );
//}
//$avatar_select->setExtra( 'onchange="showImgSelected( \'avatar\', \'user_avatar\', \'images/avatar\', \'\', \''.XOOPS_URL.'\')"' );
//$avatar_label = new XoopsFormLabel( '', '<img src="images/avatar/blank.gif" name="avatar" id="avatar" alt=""/>' );
//$avatar_tray = new XoopsFormElementTray( _US_AVATAR, '&nbsp;' );
//$avatar_tray->addElement( $avatar_select );
//$avatar_tray->addElement( $avatar_label );
//foreach ( $a_dir_labels as $a_dir_label ) {
//  $avatar_tray->addElement( $a_dir_label );
//}

$reg_form = new XoopsThemeForm(_US_USERREG, 'userinfo', 'registeruser.php');
$uname_size = $myxoopsConfigUser['maxuname'] < 25 ? $myxoopsConfigUser['maxuname'] : 25;
$reg_form->addElement(new XoopsFormText(_US_NICKNAME._MD_XOONIPS_ACCOUNT_REQUIRED_MARK, 'uname', $uname_size, $uname_size, $textutil->html_special_chars($uname)), true);
$reg_form->addElement(new XoopsFormText(_US_REALNAME.$required['realname']['mark'], 'realname', 30, 60, $textutil->html_special_chars($realname)), $required['realname']['flag']);
$reg_form->addElement($email_tray);
$reg_form->addElement(new XoopsFormText(_US_WEBSITE, 'url', 25, 255, $textutil->html_special_chars($url)));
$reg_form->addElement(new XoopsFormPassword(_US_PASSWORD._MD_XOONIPS_ACCOUNT_REQUIRED_MARK, 'pass', 10, 32, $textutil->html_special_chars($pass)), true);
$reg_form->addElement(new XoopsFormPassword(_US_VERIFYPASS._MD_XOONIPS_ACCOUNT_REQUIRED_MARK, 'vpass', 10, 32, $textutil->html_special_chars($vpass)), true);
$reg_form->addElement(new XoopsFormText(_MD_XOONIPS_ACCOUNT_COMPANY_NAME.$required['company_name']['mark'], 'company_name', 60, 255, $textutil->html_special_chars($company_name)), $required['company_name']['flag']);
$reg_form->addElement(new XoopsFormText(_MD_XOONIPS_ACCOUNT_DIVISION.$required['division']['mark'], 'division', 60, 255, $textutil->html_special_chars($division)), $required['division']['flag']);
$reg_form->addElement(new XoopsFormText(_MD_XOONIPS_ACCOUNT_TEL.$required['tel']['mark'], 'tel', 25, 32, $textutil->html_special_chars($tel)), $required['tel']['flag']);
$reg_form->addElement(new XoopsFormText(_MD_XOONIPS_ACCOUNT_FAX.$required['fax']['mark'], 'fax', 25, 32, $textutil->html_special_chars($fax)), $required['fax']['flag']);
$reg_form->addElement(new XoopsFormText(_MD_XOONIPS_ACCOUNT_ADDRESS.$required['address']['mark'], 'address', 60, 255, $textutil->html_special_chars($address)), $required['address']['flag']);
$reg_form->addElement(new XoopsFormText(_MD_XOONIPS_ACCOUNT_COUNTRY.$required['country']['mark'], 'country', 25, 255, $textutil->html_special_chars($country)), $required['country']['flag']);
$reg_form->addElement(new XoopsFormText(_MD_XOONIPS_ACCOUNT_ZIPCODE.$required['zipcode']['mark'], 'zipcode', 20, 32, $textutil->html_special_chars($zipcode)), $required['zipcode']['flag']);

$tzselected = ($timezone_offset != '') ? $timezone_offset : $myxoopsConfig['default_TZ'];
$reg_form->addElement(new XoopsFormSelectTimezone(_US_TIMEZONE, 'timezone_offset', $tzselected));
$reg_form->addElement(new XoopsFormText(_MD_XOONIPS_ACCOUNT_NOTICE_MAIL, 'notice_mail', 5, 10, $notice_mail));
//$reg_form->addElement( $avatar_tray );
$reg_form->addElement(new XoopsFormRadioYN(_US_MAILOK, 'user_mailok', $user_mailok));
if ($myxoopsConfigUser['reg_dispdsclmr'] != 0 && $myxoopsConfigUser['reg_disclaimer'] != '') {
    $disc_tray = new XoopsFormElementTray(_US_DISCLAIMER, '<br />');
    $disc_text = new XoopsFormTextarea('', 'disclaimer', $myxoopsConfigUser['reg_disclaimer'], 8);
    $disc_text->setExtra('readonly="readonly"');
    $disc_tray->addElement($disc_text);
    $agree_chk = new XoopsFormCheckBox('', 'agree_disc', $agree_disc);
    $agree_chk->addOption(1, _US_IAGREE);
    $disc_tray->addElement($agree_chk);
    $reg_form->addElement($disc_tray);
}

$submit_tray = new XoopsFormElementTray('', '');
$submit_tray->addElement(new XoopsFormHidden('op', 'newuser'));
$submit_tray->addElement($xoopsGTicket->getTicketXoopsForm(__LINE__, 1800, 'register_newuser'));
$submit_tray->addElement(new XoopsFormButton('', 'submit', _US_SUBMIT, 'submit'));
$reg_form->addElement($submit_tray);
$reg_form->setRequired($email_text);
