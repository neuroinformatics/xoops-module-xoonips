<?php
// $Revision: 1.23.2.1.2.15 $
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

$xoopsOption['pagetype'] = 'user';
include 'include/common.inc.php';
require_once 'include/notification.inc.php';
require_once 'class/base/gtickets.php';

if ( is_object( $xoopsUser ) ) {
  // if user already logged in then redirect to top page
  header( 'Location: '.XOOPS_URL.'/' );
  exit();
}

$textutil =& xoonips_getutility( 'text' );

$myxoopsConfig =& xoonips_get_xoops_configs( XOOPS_CONF );
$myxoopsConfigUser =& xoonips_get_xoops_configs( XOOPS_CONF_USER );

$xconfig_handler =& xoonips_getormhandler( 'xoonips', 'config' );

if ( empty( $myxoopsConfigUser['allow_register'] ) ) {
  redirect_header( XOOPS_URL.'/', 6, _US_NOREGISTER );
  exit();
}

function userCheck( $uname, $email, $pass, $vpass ) {
  global $myxoopsConfigUser;
  $xoopsDB =& Database::getInstance();
  $stop = '';
  if ( ! checkEmail( $email ) ) {
    $stop .= _US_INVALIDMAIL.'<br />';
  }
  foreach ( $myxoopsConfigUser['bad_emails'] as $be ) {
    if ( ! empty( $be ) && preg_match( '/'.$be.'/i', $email ) ) {
      $stop .= _US_INVALIDMAIL.'<br />';
      break;
    }
  }
  if ( strrpos( $email,' ' ) > 0 ) {
    $stop .= _US_EMAILNOSPACES.'<br />';
  }
  $uname = xoops_trim( $uname );
  $restrictions = array(
    0 => '/[^a-zA-Z0-9\_\-]/', // strict
    1 => '/[^a-zA-Z0-9\_\-\<\>\,\.\$\%\#\@\!\\\'\"]/', // medium
    2 => '/[\000-\040]/' // loose
  );
  $restriction = $restrictions[$myxoopsConfigUser['uname_test_level']];
  if ( empty( $uname ) || preg_match( $restriction, $uname ) ) {
    $stop .= _US_INVALIDNICKNAME.'<br />';
  }
  if ( strlen($uname) > $myxoopsConfigUser['maxuname'] ) {
    $stop .= sprintf( _US_NICKNAMETOOLONG, $myxoopsConfigUser['maxuname'] ).'<br />';
  }
  if ( strlen( $uname ) < $myxoopsConfigUser['minuname'] ) {
    $stop .= sprintf( _US_NICKNAMETOOSHORT, $myxoopsConfigUser['minuname'] ).'<br />';
  }
  foreach ( $myxoopsConfigUser['bad_unames'] as $bu ) {
    if ( ! empty( $bu ) && preg_match( '/'.$bu.'/i', $uname ) ) {
      $stop .= _US_NAMERESERVED.'<br />';
      break;
    }
  }
  if ( strrpos( $uname, ' ' ) > 0 ) {
    $stop .= _US_NICKNAMENOSPACES.'<br />';
  }
  $u_handler =& xoonips_getormhandler( 'xoonips', 'xoops_users' );
  $criteria = new Criteria( 'uname', addslashes( $uname ) );
  if ( $u_handler->getCount( $criteria ) > 0 ) {
    $stop .= _US_NICKNAMETAKEN."<br />";
  }
  if ( $email ) {
    $criteria = new Criteria( 'email', addslashes( $email ) );
    if ( $u_handler->getCount( $criteria ) > 0 ) {
      $stop .= _US_EMAILTAKEN."<br />";
    }
  }
  if ( ! isset( $pass ) || $pass == '' || ! isset( $vpass ) || $vpass == '' ) {
    $stop .= _US_ENTERPWD.'<br />';
  }
  if ( ( isset( $pass ) ) && ( $pass != $vpass ) ) {
    $stop .= _US_PASSNOTSAME.'<br />';
  } elseif ( ( $pass != '' ) && ( strlen( $pass ) < $myxoopsConfigUser['minpass'] ) ) {
    $stop .= sprintf( _US_PWDTOOSHORT, $myxoopsConfigUser['minpass'] ).'<br />';
  }
  return $stop;
}

function userCheckXooNIps( $realname, $address, $company_name, $division, $tel,  $country, $zipcode, $fax, $notice_mail ) {
  global $required;
  $errors = array();
  // acquire required flags of XooNIps user information
  $check_fields = array(
    // 'post key' => array( 'label', maxlength, 'error message' ),
    'realname' => array( _US_REALNAME, null, null ),
    'address' => array( _MD_XOONIPS_ACCOUNT_ADDRESS, 255, _MD_XOONIPS_ACCOUNT_REG_TOO_LONG_ADDRESS ),
    'company_name' => array( _MD_XOONIPS_ACCOUNT_COMPANY_NAME, 255, _MD_XOONIPS_ACCOUNT_REG_TOO_LONG_COMPANY_NAME ),
    'division' => array( _MD_XOONIPS_ACCOUNT_DIVISION, 255, _MD_XOONIPS_ACCOUNT_REG_TOO_LONG_DIVISION ),
    'tel' => array( _MD_XOONIPS_ACCOUNT_TEL, 32, _MD_XOONIPS_ACCOUNT_REG_TOO_LONG_TEL ),
    'country' => array( _MD_XOONIPS_ACCOUNT_COUNTRY, 255, _MD_XOONIPS_ACCOUNT_REG_TOO_LONG_COUNTRY ),
    'zipcode' => array( _MD_XOONIPS_ACCOUNT_ZIPCODE, 32, _MD_XOONIPS_ACCOUNT_REG_TOO_LONG_ZIPCODE ),
    'fax' => array( _MD_XOONIPS_ACCOUNT_FAX, 32, _MD_XOONIPS_ACCOUNT_REG_TOO_LONG_FAX ),
  );
  // -- notice mail
  if ( $notice_mail < 0 ) {
    $errors[] = _MD_XOONIPS_ACCOUNT_NOTICE_MAIL_TOO_LITTLE;
  }
  // -- requirements
  foreach ( $check_fields as $key => $info ) {
    list( $label, $maxlength, $errmes ) = $info;
    if ( $required[$key]['flag'] && ${$key} == '' ) {
        $errors[] = sprintf( _MD_XOONIPS_ACCOUNT_MUST_BE_FILLED_IN, $label );
    }
  }
  // -- field length
  foreach ( $check_fields as $key => $info ) {
    list( $label, $maxlength, $errmes ) = $info;
    if ( ! is_null( $maxlength ) && strlen( ${$key} ) > $maxlength ) {
      $errors[] = $errmes;
    }
  }
  $stop = '';
  if ( count( $errors ) > 0 ) {
    $stop = implode( '<br />'."\n", $errors ).'<br />'."\n";
  }
  return $stop;
}

$formdata =& xoonips_getutility( 'formdata' );
$op = $formdata->getValue( 'post', 'op', 'n', false, 'register' );
xoonips_validate_request( in_array( $op, array( 'register', 'newuser', 'finish' ) ) );

$post_keys = array(
  'uname' => array( 'type' => 's', 'default' => '' ),
  'email' => array( 'type' => 's', 'default' => '' ),
  'url' => array( 'type' => 's', 'default' => '' ),
  'pass' => array( 'type' => 'n', 'default' => '' ),
  'vpass' => array( 'type' => 'n', 'default' => '' ),
  'timezone_offset' => array( 'type' => 'f', 'default' => $myxoopsConfig['default_TZ'] ),
  'user_viewemail' => array( 'type' => 'b', 'default' => 0 ),
  'user_mailok' => array( 'type' => 'b', 'default' => 0 ),
  'agree_disc' => array( 'type' => 'b', 'default' => 0 ),
  // for xoonips user information
  'realname' => array( 'type' => 's', 'default' => '' ),
  'address' => array( 'type' => 's', 'default' => '' ),
  'company_name' => array( 'type' => 's', 'default' => '' ),
  'division' => array( 'type' => 's', 'default' => '' ),
  'tel' => array( 'type' => 's', 'default' => '' ),
  'country' => array( 'type' => 's', 'default' => '' ),
  'zipcode' => array( 'type' => 's', 'default' => '' ),
  'fax' => array( 'type' => 's', 'default' => '' ),
  'notice_mail' => array( 'type' => 'i', 'default' => 0 ),
);
foreach ( $post_keys as $key => $meta ) {
  $val = $formdata->getValue( 'post', $key, $meta['type'], false, $meta['default'] );
  $$key = $val;
}

// get and check xoonips configuration
$certify_user = $xconfig_handler->getValue( 'certify_user' );
$is_certify_auto = ( $certify_user == 'auto' );
$required = array();
foreach ( array( 'realname', 'address', 'division', 'tel', 'company_name', 'country', 'zipcode', 'fax' ) as $key ) {
  $optional = $xconfig_handler->getValue( 'account_'.$key.'_optional' );
  if ( $optional == 'on' ) {
    $required[$key] = array( 'flag' => false, 'mark' => '' );
  } else {
    $required[$key] = array( 'flag' => true, 'mark' => _MD_XOONIPS_ACCOUNT_REQUIRED_MARK );
  }
}

switch ( $op ) {
case 'newuser':
  if ( ! $xoopsGTicket->check( true, 'register_newuser', false ) ) {
    redirect_header( XOOPS_URL.'/', 3, $xoopsGTicket->getErrors() );
  }
  include XOOPS_ROOT_PATH.'/header.php';
  $stop = '';
  if ( $myxoopsConfigUser['reg_dispdsclmr'] != 0 && $myxoopsConfigUser['reg_disclaimer'] != '' ) {
    if ( empty( $agree_disc ) ) {
      $stop .= _US_UNEEDAGREE.'<br />';
    }
  }
  $stop .= userCheck( $uname, $email, $pass, $vpass );
  $stop .= userCheckXooNIps( $realname, $address, $company_name, $division, $tel, $country, $zipcode, $fax, $notice_mail );
  if ( empty( $stop ) ) {
    echo _US_USERNAME.': '.$textutil->html_special_chars( $uname ).'<br />';
    echo _US_EMAIL.': '.$textutil->html_special_chars( $email ).'<br />';
    if ( $url != '' ) {
      $url = formatURL( $url );
      echo _US_WEBSITE.': '.$textutil->html_special_chars( $url ).'<br />';
    }
    $f_timezone = 'GMT '.( $timezone_offset < 0.0 ) ? $timezone_offset : '+'.$timezone_offset;
    echo _US_TIMEZONE.': '.$f_timezone.'<br />';
    // display user information for XooNIps
    echo _MD_XOONIPS_ACCOUNT_COMPANY_NAME.': '.$textutil->html_special_chars( $company_name ).'<br />';
    echo _MD_XOONIPS_ACCOUNT_DIVISION.': '.$textutil->html_special_chars( $division ).'<br />';
    echo _MD_XOONIPS_ACCOUNT_TEL.': '.$textutil->html_special_chars( $tel ).'<br />';
    echo _MD_XOONIPS_ACCOUNT_FAX.': '.$textutil->html_special_chars( $fax ).'<br />';
    echo _MD_XOONIPS_ACCOUNT_ADDRESS.': '.$textutil->html_special_chars( $address ).'<br />';
    echo _MD_XOONIPS_ACCOUNT_COUNTRY.': '.$textutil->html_special_chars( $country ).'<br />';
    echo _MD_XOONIPS_ACCOUNT_ZIPCODE.': '.$textutil->html_special_chars( $zipcode ).'<br />';
    echo _MD_XOONIPS_ACCOUNT_NOTICE_MAIL.': '.$textutil->html_special_chars( $notice_mail ).'<br />';
    echo '<form action="registeruser.php" method="post">';
    echo $xoopsGTicket->getTicketHtml( __LINE__, 1800, 'register_finish' );
    echo '<input type="hidden" name="uname" value="'.$textutil->html_special_chars( $uname ).'"/>';
    echo '<input type="hidden" name="email" value="'.$textutil->html_special_chars( $email ).'"/>';
    echo '<input type="hidden" name="user_viewemail" value="'.$user_viewemail.'"/>';
    echo '<input type="hidden" name="timezone_offset" value="'.(float)$timezone_offset.'"/>';
    echo '<input type="hidden" name="url" value="'.$textutil->html_special_chars( $url ).'"/>';
    echo '<input type="hidden" name="pass" value="'.$textutil->html_special_chars( $pass ).'"/>';
    echo '<input type="hidden" name="vpass" value="'.$textutil->html_special_chars( $vpass ).'"/>';
    echo '<input type="hidden" name="user_mailok" value="'.$user_mailok.'"/>';
    // for xoonips user information
    echo '<input type="hidden" name="realname" value="'.$textutil->html_special_chars( $realname ).'"/>';
    echo '<input type="hidden" name="company_name" value="'.$textutil->html_special_chars( $company_name ).'"/>';
    echo '<input type="hidden" name="division" value="'.$textutil->html_special_chars( $division ).'"/>';
    echo '<input type="hidden" name="tel" value="'.$textutil->html_special_chars( $tel ).'"/>';
    echo '<input type="hidden" name="fax" value="'.$textutil->html_special_chars( $fax ).'"/>';
    echo '<input type="hidden" name="address" value="'.$textutil->html_special_chars( $address ).'"/>';
    echo '<input type="hidden" name="country" value="'.$textutil->html_special_chars( $country ).'"/>';
    echo '<input type="hidden" name="zipcode" value="'.$textutil->html_special_chars( $zipcode ).'"/>';
    echo '<input type="hidden" name="notice_mail" value="'.$notice_mail.'"/>';
    echo '<br /><br />';
    echo '<input type="hidden" name="op" value="finish" />';
    echo '<input class="formButton" type="submit" value="'. _US_FINISH .'"/>';
    echo '</form>';
  } else {
    echo '<span style="color:#ff0000;">'.$stop.'</span>';
    echo '<br />'._MD_XOONIPS_ACCOUNT_EXPLAIN_REQUIRED_MARK.'<br />'."\n";
    include 'include/registerform.php';
    $reg_form->display();
  }
  include XOOPS_ROOT_PATH.'/footer.php';
  break;
case 'finish':
  if ( ! $xoopsGTicket->check( true, 'register_finish', false ) ) {
    redirect_header( XOOPS_URL.'/', 3, $xoopsGTicket->getErrors() );
    exit();
  }
  include XOOPS_ROOT_PATH.'/header.php';
  $stop = userCheck( $uname, $email, $pass, $vpass );
  $stop .= userCheckXooNIps( $realname, $address, $company_name, $division, $tel, $country, $zipcode, $fax, $notice_mail );
  if ( empty( $stop ) ) {
    $member_handler =& xoops_gethandler( 'member' );
    $newuser =& $member_handler->createUser();
    $newuser->setVar( 'user_viewemail',$user_viewemail, true ); // not gpc
    $newuser->setVar( 'uname', $uname, true ); // not gpc
    $newuser->setVar( 'email', $email, true ); // not gpc
    if ( $url != '' ) {
      $newuser->setVar( 'url', formatURL($url), true ); // not gpc
    }
    $newuser->setVar( 'user_avatar', 'blank.gif', true ); // not gpc
    $actkey = substr( md5( uniqid( mt_rand(), 1 ) ), 0, 8 );
    $newuser->setVar( 'actkey', $actkey, true ); // not gpc
    $newuser->setVar( 'pass', md5( $pass ), true ); // not gpc
    $newuser->setVar( 'timezone_offset', $timezone_offset, true ); // not gpc
    $newuser->setVar( 'user_regdate', time(), true ); // not gpc
    $newuser->setVar( 'uorder',$myxoopsConfig['com_order'], true ); // not gpc
    $newuser->setVar( 'umode',$myxoopsConfig['com_mode'], true ); // not gpc
    $newuser->setVar( 'user_mailok',$user_mailok, true ); // not gpc
    $newuser->setVar( 'name', $realname, true ); // not gpc
    if ( $myxoopsConfigUser['activation_type'] == 1 ) {
      $newuser->setVar( 'level', 1, true ); // not gpc
    }
    if ( ! $member_handler->insertUser( $newuser ) ) {
      echo _US_REGISTERNG;
      include  XOOPS_ROOT_PATH.'/footer.php';
      exit();
    }
    $newid = $newuser->getVar( 'uid' );
    if ( ! $member_handler->addUserToGroup( XOOPS_GROUP_USERS, $newid ) ) {
      echo _US_REGISTERNG;
      include XOOPS_ROOT_PATH.'/footer.php';
      exit();
    }
    // create XooNIps user information
    // - pickup XOOPS user
    $xm_handler =& xoonips_gethandler( 'xoonips', 'member' );
    if ( ! $xm_handler->pickupXoopsUser( $newid, $is_certify_auto ) ) {
      echo _US_REGISTERNG;
      include XOOPS_ROOT_PATH.'/footer.php';
      exit();
    }
    // - update XooNIps user informations
    $xu_handler =& xoonips_getormhandler( 'xoonips', 'users' );
    $xu_obj =& $xu_handler->get( $newid );
    $xu_obj->set( 'company_name', $company_name );
    $xu_obj->set( 'division', $division );
    $xu_obj->set( 'tel', $tel );
    $xu_obj->set( 'fax', $fax );
    $xu_obj->set( 'address', $address );
    $xu_obj->set( 'country', $country );
    $xu_obj->set( 'zipcode', $zipcode );
    $xu_obj->set( 'notice_mail', $notice_mail );
    $xu_handler->insert( $xu_obj );

    // send mail
    if ( $myxoopsConfigUser['activation_type'] == 0 ) {
      // activate xoops account by user
      $langman =& xoonips_getutility( 'languagemanager' );
      $xoopsMailer =& getMailer();
      $xoopsMailer->useMail();
      $xoopsMailer->setTemplateDir( $langman->mail_template_dir() );
      if ( $is_certify_auto ) {
        // XOOPS : by user, XooNIps : auto
        $xoopsMailer->setTemplate('xoonips_activate_by_user_certify_auto.tpl');
      } else {
        // XOOPS : by user, XooNIps : moderator
        $xoopsMailer->setTemplate('xoonips_activate_by_user_certify_manual.tpl');
      }
      $xoopsMailer->assign( 'X_UACTLINK', XOOPS_URL.'/modules/xoonips/user.php?op=actv&id='.$newid.'&actkey='.$actkey );
      $xoopsMailer->assign( 'SITENAME', $myxoopsConfig['sitename'] );
      $xoopsMailer->assign( 'ADMINMAIL', $myxoopsConfig['adminmail'] );
      $xoopsMailer->assign( 'SITEURL', XOOPS_URL.'/' );
      $xoopsMailer->setToUsers( new XoopsUser( $newid ) );
      $xoopsMailer->setFromEmail( $myxoopsConfig['adminmail'] );
      $xoopsMailer->setFromName( $myxoopsConfig['sitename'] );
      $xoopsMailer->setSubject( sprintf( _MD_XOONIPS_ACTIVATE_KEY_SUBJECT, $uname ) );
      if ( ! $xoopsMailer->send() ) {
        echo _US_YOURREGMAILNG;
      } else {
        if ( $is_certify_auto ) {
          echo _MD_XOONIPS_ACTIVATE_BY_USER_CERTIFY_AUTO;
        } else {
          echo _MD_XOONIPS_ACTIVATE_BY_USER_CERTIFY_MANUAL;
        }
      }
    } else if ( $myxoopsConfigUser['activation_type'] == 1 ) {
      // activate xoops account automatically
      // - To send a e-mail to users who are belong to the group
      //   specified by moderator_gid if certify_user is 'manual'
      if ( ! $is_certify_auto ) {
        // XOOPS : auto, XooNIps : moderator
        xoonips_notification_account_certify_request( $newid );
        echo _MD_XOONIPS_ACTIVATE_AUTO_CERTIFY_MANUAL;
      } else {
        // XOOPS : auto, XooNIps : auto
        xoonips_notification_account_certified( $newid );
        redirect_header( 'user.php', 5, _MD_XOONIPS_ACTIVATE_AUTO_CERTIFY_AUTO, false );
      }
    } else if ( $myxoopsConfigUser['activation_type'] == 2 ) {
      // activate xoops accunt by xoops administrator
      $xoopsMailer =& getMailer();
      $xoopsMailer->useMail();
      $xoopsMailer->setTemplate( 'adminactivate.tpl' );
      $xoopsMailer->assign( 'USERNAME', $uname );
      $xoopsMailer->assign( 'USEREMAIL', $email );
      $xoopsMailer->assign( 'USERACTLINK', XOOPS_URL.'/modules/xoonips/user.php?op=actv&id='.$newid.'&actkey='.$actkey );
      $xoopsMailer->assign( 'SITENAME', $myxoopsConfig['sitename'] );
      $xoopsMailer->assign( 'ADMINMAIL', $myxoopsConfig['adminmail'] );
      $xoopsMailer->assign( 'SITEURL', XOOPS_URL.'/' );
      $member_handler =& xoops_gethandler( 'member' );
      $xoopsMailer->setToGroups( $member_handler->getGroup( $myxoopsConfigUser['activation_group'] ) );
      $xoopsMailer->setFromEmail( $myxoopsConfig['adminmail'] );
      $xoopsMailer->setFromName( $myxoopsConfig['sitename'] );
      $xoopsMailer->setSubject( sprintf( _MD_XOONIPS_ACTIVATE_KEY_SUBJECT, $uname ) );
      if ( ! $xoopsMailer->send() ) {
        echo _MD_XOONIPS_ACTIVATE_BY_ADMIN_MAILNG;
      } else {
        if ( $is_certify_auto ) {
          // XOOPS : by admin, XooNIps : auto
          echo _MD_XOONIPS_ACTIVATE_BY_ADMIN_CERTIFY_AUTO;
        } else {
         // XOOPS : by admin, XooNIps : moderator
         echo _MD_XOONIPS_ACTIVATE_BY_ADMIN_CERTIFY_MANUAL;
        }
      }
    }
    // send e-mail to XOOPS Admin
    if ( $myxoopsConfigUser['new_user_notify'] == 1 && ! empty( $myxoopsConfigUser['new_user_notify_group'] ) ) {
      $xoopsMailer =& getMailer();
      $xoopsMailer->useMail();
      $member_handler =& xoops_gethandler('member');
      $xoopsMailer->setToGroups( $member_handler->getGroup( $myxoopsConfigUser['new_user_notify_group'] ) );
      $xoopsMailer->setFromEmail( $myxoopsConfig['adminmail'] );
      $xoopsMailer->setFromName( $myxoopsConfig['sitename'] );
      $xoopsMailer->setSubject( sprintf( _US_NEWUSERREGAT, $myxoopsConfig['sitename'] ) );
      $xoopsMailer->setBody( sprintf( _US_HASJUSTREG, $uname ) );
      $xoopsMailer->send();
    }
  } else {
    echo '<span style="color:#ff0000; font-weight:bold;">'.$stop.'</span>';
    include 'include/registerform.php';
    $reg_form->display();
  }
  include XOOPS_ROOT_PATH.'/footer.php';
  break;
case 'register':
  include XOOPS_ROOT_PATH.'/header.php';
  echo '<br />'._MD_XOONIPS_ACCOUNT_EXPLAIN_REQUIRED_MARK.'<br />'."\n";
  include 'include/registerform.php';
  $reg_form->display();
  include XOOPS_ROOT_PATH.'/footer.php';
  break;
}

?>
