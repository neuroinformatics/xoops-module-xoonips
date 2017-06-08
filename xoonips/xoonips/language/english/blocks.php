<?php

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
 */

// _MB_<MODULENAME>_<STRINGNAME>

// login block
define('_MB_XOONIPS_LOGIN_USERNAME', 'Username');
define('_MB_XOONIPS_LOGIN_PASSWORD', 'Password');
define('_MB_XOONIPS_LOGIN_LOGIN', 'Login');
define('_MB_XOONIPS_LOGIN_LOSTPASS', 'Lost Password?');
define('_MB_XOONIPS_LOGIN_USERREG', 'Register now!');
define('_MB_XOONIPS_LOGIN_SECURE', 'SSL');
define('_MB_XOONIPS_LOGIN_REMEMBERME', 'Remember Me');

// user menu block
define('_MB_XOONIPS_USER_VIEW_ACCOUNT', 'View Account');
define('_MB_XOONIPS_USER_EDIT_ACCOUNT', 'Edit Account');
define('_MB_XOONIPS_USER_REGISTER_ITEM', 'Add New Item');
define('_MB_XOONIPS_USER_SHOW_USERS', 'User List');
define('_MB_XOONIPS_USER_GROUP_LIST', 'Group List');
define('_MB_XOONIPS_USER_NOTIFICATION', 'Notifications');
define('_MB_XOONIPS_USER_INBOX', 'Inbox');
define('_MB_XOONIPS_USER_LISTING_ITEM', 'Listing Item');
define('_MB_XOONIPS_USER_EDIT_PRIVATE_INDEX', 'Edit Private Tree');
define('_MB_XOONIPS_USER_ADVANCED_SEARCH', 'Advanced Search');
define('_MB_XOONIPS_USER_IMPORT', 'Import');
define('_MB_XOONIPS_USER_LOGOUT', 'Logout');
define('_MB_XOONIPS_USER_ADMINMENU', 'Administration Menu');
define('_MB_XOONIPS_USER_SU_START', 'Switch User Account');
define('_MB_XOONIPS_USER_SU_END', 'End Switch User (<span style="font-weight: bold;">%s</span>)');
define('_MB_XOONIPS_USER_TRANSFER_USER_REQUEST', 'Request Item Transfer');
define('_MB_XOONIPS_USER_TRANSFER_USER_ACCEPT', 'Accept Item Transfer');
define('_MB_XOONIPS_USER_OAIPMH_SEARCH', 'OAI-PMH Metadata Search');

// group admin menu block
define('_MB_XOONIPS_GROUP_EDIT_GROUP_MEMBERS', 'Edit Group Members');
define('_MB_XOONIPS_GROUP_CERTIFY_GROUP_ITEMS', 'Certify Group Items');
define('_MB_XOONIPS_GROUP_EDIT_GROUP_INDEX', 'Edit Group Tree');

// moderator menu block
define('_MB_XOONIPS_MODERATOR_EDIT_GROUPS', 'Edit Groups');
define('_MB_XOONIPS_MODERATOR_CERTIFY_USERS', 'Certify Users');
define('_MB_XOONIPS_MODERATOR_GROUP_CERTIFY_PUBLIC_ITEMS', 'Certify Public Group Items');
define('_MB_XOONIPS_MODERATOR_CERTIFY_PUBLIC_ITEMS', 'Certify Public Items');
define('_MB_XOONIPS_MODERATOR_EDIT_PUBLIC_INDEX', 'Edit Public Tree');
define('_MB_XOONIPS_MODERATOR_EVENT_LOG', 'Event Log');

// quick search block
define('_MB_XOONIPS_SEARCH_QUICK', 'Search');
define('_MB_XOONIPS_SEARCH_ADVANCED', 'Advanced');
define('_MB_XOONIPS_SEARCH_ALL', 'ALL');
define('_MB_XOONIPS_SEARCH_TITLE_AND_KEYWORD', 'Title & Keyword');
define('_MB_XOONIPS_SEARCH_METADATA', 'Metadata');

define('_MB_XOONIPS_RANKING_VIEWED_ITEM', 'most accessed items');
define('_MB_XOONIPS_RANKING_DOWNLOADED_ITEM', 'most downloaded items');
define('_MB_XOONIPS_RANKING_CONTRIBUTING_USER', 'most active contributors');
define('_MB_XOONIPS_RANKING_SEARCHED_KEYWORD', 'most searched keywords');
define('_MB_XOONIPS_RANKING_CONTRIBUTED_GROUP', 'most active groups');

define('_MB_XOONIPS_RANKING_NEW_ITEM', 'newly arrived items');
define('_MB_XOONIPS_RANKING_NEW_GROUP', 'newly created groups');
define('_MB_XOONIPS_RANKING_EMPTY', 'ranking is empty.');
define('_MB_XOONIPS_RANKING_RANK_STR', 'st,nd,rd,th'); // 1st, 2nd, 3rd, 4th, ...
