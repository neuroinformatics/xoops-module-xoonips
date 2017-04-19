<?php

// $Revision:$
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
/* constant strings
 * $Revision:$
 */

// _MI_<MODULENAME>_<STRINGNAME>
/*
define("_MI_XOONIPS_ITEM_NAME","XooNIps Item");
define("_MI_XOONIPS_ITEM_DESC","XooNIps Item Module");
define("_MI_XOONIPS_ACCOUNT_NAME","XooNIps Account");
define("_MI_XOONIPS_ACCOUNT_DESC","XooNIps Account Module");
define("_MI_XOONIPS_BINDER_NAME","XooNIps Binder Item Type Module");
define("_MI_XOONIPS_BINDER_DESC","XooNIps Binder Item Type Module");
define("_MI_XOONIPS_CONFIG_NAME","XooNIps Configuration");
define("_MI_XOONIPS_CONFIG_DESC","XooNIps Configuration Module");
define("_MI_XOONIPS_MODERATOR_NAME","XooNIps Moderator");
define("_MI_XOONIPS_MODERATOR_DESC","XooNIps Moderator Module");
define("_MI_XOONIPS_INDEX_NAME","XooNIps Index");
define("_MI_XOONIPS_INDEX_DESC","XooNIps Index Module");
*/
// The name of this module
define('_MI_XOONIPS_NAME', 'XooNIps');
// A brief description of this module
define('_MI_XOONIPS_DESC', 'XooNIps Module');

//submenu labels
define('_MI_XOONIPS_ITEM_SMNAME1', 'Register Item');
define('_MI_XOONIPS_ITEM_SMNAME2', 'Certify Item');
define('_MI_XOONIPS_ITEM_SMNAME3', 'Advanced Search');

define('_MI_XOONIPS_ITEM_BNAME1', 'XooNIps Search');

define('_MI_XOONIPS_MODERATOR_BNAME2', 'XooNIps Moderator main menu');

//submenu labels
define('_MI_XOONIPS_MODERATOR_SMNAME1', 'Edit Groups');
define('_MI_XOONIPS_MODERATOR_SMNAME2', 'Certify users');

define('_MI_XOONIPS_INDEX_BNAME1', 'Index Tree');

//submenu labels
define('_MI_XOONIPS_INDEX_SMNAME1', 'Edit Private Tree');
define('_MI_XOONIPS_INDEX_SMNAME2', 'Edit Group Tree');
define('_MI_XOONIPS_INDEX_SMNAME3', 'Edit Public Tree');

// Names of admin menu items
define('_MI_XOONIPS_CONFIG_DSN', 'DSN');
define('_MI_XOONIPS_CONFIG_DSN_DESC', ' ODBC DSN');

define('_MI_XOONIPS_ACCOUNT_BNAME1', 'XooNIps Login');
define('_MI_XOONIPS_ACCOUNT_BNAME2', 'XooNIps User Menu');
define('_MI_XOONIPS_ACCOUNT_BNAME3', 'XooNIps Group Admin Menu');
define('_MI_XOONIPS_ACCOUNT_BNAME4', 'XooNIps Moderator Menu');

//submenu labels
define('_MI_XOONIPS_ACCOUNT_SMNAME1', 'View Account');
define('_MI_XOONIPS_ACCOUNT_SMNAME2', 'Edit Account');
define('_MI_XOONIPS_ACCOUNT_SMNAME4', 'Edit Group');

//administrator menu
define('_MI_XOONIPS_ADMENU1', 'System Configuration');
define('_MI_XOONIPS_ADMENU2', 'Site Policies');
define('_MI_XOONIPS_ADMENU3', 'Maintenance');

//notification
define('_MI_XOONIPS_USER_NOTIFY', 'XooNIps User');
define('_MI_XOONIPS_USER_NOTIFYDSC', 'Notifications for XooNIps users.');

define('_MI_XOONIPS_ADMINISTRATOR_NOTIFY', 'Administrator');
define('_MI_XOONIPS_ADMINISTRATOR_NOTIFYDSC', 'Notifications for Moderator and Group administrator.');

// use XooNIpsNotification. subject are defined in main.php
define('_MI_XOONIPS_ITEM_TRANSFER_NOTIFY', 'Item transferred');
define('_MI_XOONIPS_ITEM_TRANSFER_NOTIFYCAP', 'Notify me of tranferred item');
define('_MI_XOONIPS_ITEM_TRANSFER_NOTIFYDSC', 'Receive notification when a item is transferred.');

define('_MI_XOONIPS_ACCOUNT_CERTIFY_NOTIFY', 'Account certified');
define('_MI_XOONIPS_ACCOUNT_CERTIFY_NOTIFYCAP', 'Notify me of certified account');
define('_MI_XOONIPS_ACCOUNT_CERTIFY_NOTIFYDSC', 'Receive notification when you are requested to account or account is certified.');

define('_MI_XOONIPS_ITEM_CERTIFY_NOTIFY', 'Item certified');
define('_MI_XOONIPS_ITEM_CERTIFY_NOTIFYCAP', 'Notify me of new item is certified/rejected or needs to be certifyed/rejected.');
define('_MI_XOONIPS_ITEM_CERTIFY_NOTIFYDSC', 'Receive notification when a item is certified/rejected or needs to be certifyed/rejected.');

define('_MI_XOONIPS_USER_ITEM_TRANSFER_NOTIFY', 'Item transferred');
define('_MI_XOONIPS_USER_ITEM_TRANSFER_NOTIFYCAP', 'Notify me of transferring request is accepted/rejected');
define('_MI_XOONIPS_USER_ITEM_TRANSFER_NOTIFYDSC', 'Receive notification when you are requested to inherit items or your request to transfer items is accepted/rejected');

define('_MI_XOONIPS_USER_ITEM_UPDATED_NOTIFY', 'Item updated');
define('_MI_XOONIPS_USER_ITEM_UPDATED_NOTIFYCAP', 'Notify me of modified own item.');
define('_MI_XOONIPS_USER_ITEM_UPDATED_NOTIFYDSC', 'Receive notification when own items are modified');

define('_MI_XOONIPS_USER_ITEM_CERTIFIED_NOTIFY', 'Item certified');
define('_MI_XOONIPS_USER_ITEM_CERTIFIED_NOTIFYCAP', 'Notify me of certified own item');
define('_MI_XOONIPS_USER_ITEM_CERTIFIED_NOTIFYDSC', 'Receive notification when a item is certified.');

define('_MI_XOONIPS_USER_ITEM_REJECTED_NOTIFY', 'Item rejected');
define('_MI_XOONIPS_USER_ITEM_REJECTED_NOTIFYCAP', 'Notify me of rejected own item');
define('_MI_XOONIPS_USER_ITEM_REJECTED_NOTIFYDSC', 'Receive notification when a item is rejected.');

define('_MI_XOONIPS_USER_FILE_DOWNLOADED_NOTIFY', 'File downloaded');
define('_MI_XOONIPS_USER_FILE_DOWNLOADED_NOTIFYCAP', 'Notify me of downloaded own file');
define('_MI_XOONIPS_USER_FILE_DOWNLOADED_NOTIFYDSC', 'Receive notification when a file is downloaded.');

define('_MI_XOONIPS_USER_GROUP_ITEM_CERTIFIED_NOTIFY', 'Group item certified');
define('_MI_XOONIPS_USER_GROUP_ITEM_CERTIFIED_NOTIFYCAP', 'Notify me that group item is certified.');
define('_MI_XOONIPS_USER_GROUP_ITEM_CERTIFIED_NOTIFYDSC', 'Receive notification when group item is certified.');
define('_MI_XOONIPS_USER_GROUP_ITEM_CERTIFIED_NOTIFYSBJ', 'Your group item is certified.');

define('_MI_XOONIPS_USER_GROUP_ITEM_REJECTED_NOTIFY', 'Group item rejected');
define('_MI_XOONIPS_USER_GROUP_ITEM_REJECTED_NOTIFYCAP', 'Notify me that group item is rejected.');
define('_MI_XOONIPS_USER_GROUP_ITEM_REJECTED_NOTIFYDSC', 'Receive notification when group item is rejected.');
define('_MI_XOONIPS_USER_GROUP_ITEM_REJECTED_NOTIFYSBJ', 'Your group item is rejected.');

define('_MI_XOONIPS_USER_GROUP_ITEM_CERTIFY_REQUEST_NOTIFY', 'Requrest for group item certification.');
define('_MI_XOONIPS_USER_GROUP_ITEM_CERTIFY_REQUEST_NOTIFYCAP', 'Notify me of certification group index.');
define('_MI_XOONIPS_USER_GROUP_ITEM_CERTIFY_REQUEST_NOTIFYDSC', 'Receive notification when group item is registerd to public index.');
define('_MI_XOONIPS_USER_GROUP_ITEM_CERTIFY_REQUEST_NOTIFYSBJ', 'Certify group index');

//itemtype block labels
define('_MI_XOONIPS_ITEMTYPE_BNAME1', 'Registered Itemtypes');

define('_MI_XOONIPS_RANKING', 'XooNIps Ranking');
define('_MI_XOONIPS_RANKING_NEW', 'XooNIps Update');

//userlist block label
define('_MI_XOONIPS_USERLIST', 'User List');
