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
define('_MB_XOONIPS_LOGIN_USERNAME', 'ユーザ名');
define('_MB_XOONIPS_LOGIN_PASSWORD', 'パスワード');
define('_MB_XOONIPS_LOGIN_LOGIN', 'ログイン');
define('_MB_XOONIPS_LOGIN_LOSTPASS', 'パスワード紛失');
define('_MB_XOONIPS_LOGIN_USERREG', '新規登録');
define('_MB_XOONIPS_LOGIN_SECURE', 'SSL');
define('_MB_XOONIPS_LOGIN_REMEMBERME', 'IDとパスワードを記憶');

// user menu block
define('_MB_XOONIPS_USER_VIEW_ACCOUNT', 'アカウント情報');
define('_MB_XOONIPS_USER_EDIT_ACCOUNT', 'アカウント編集');
define('_MB_XOONIPS_USER_REGISTER_ITEM', '新規アイテム登録');
define('_MB_XOONIPS_USER_SHOW_USERS', 'ユーザ一覧');
define('_MB_XOONIPS_USER_GROUP_LIST', 'グループ一覧');
define('_MB_XOONIPS_USER_NOTIFICATION', 'イベント通知機能');
define('_MB_XOONIPS_USER_INBOX', '受信箱');
define('_MB_XOONIPS_USER_LISTING_ITEM', 'アイテム一覧');
define('_MB_XOONIPS_USER_EDIT_PRIVATE_INDEX', 'プライベートツリー編集');
define('_MB_XOONIPS_USER_ADVANCED_SEARCH', '詳細検索');
define('_MB_XOONIPS_USER_IMPORT', 'インポート');
define('_MB_XOONIPS_USER_LOGOUT', 'ログアウト');
define('_MB_XOONIPS_USER_ADMINMENU', '管理者メニュー');
define('_MB_XOONIPS_USER_SU_START', 'アカウント切り替え');
define('_MB_XOONIPS_USER_SU_END', 'アカウント切り替え (<span style="font-weight: bold;">%s</span>) の終了');
define('_MB_XOONIPS_USER_TRANSFER_USER_REQUEST', 'アイテム移譲要求');
define('_MB_XOONIPS_USER_TRANSFER_USER_ACCEPT', 'アイテム移譲許可');
define('_MB_XOONIPS_USER_OAIPMH_SEARCH', 'OAI-PMHメタデータ検索');

// group admin menu block
define('_MB_XOONIPS_GROUP_EDIT_GROUP_MEMBERS', 'グループメンバー編集');
define('_MB_XOONIPS_GROUP_CERTIFY_GROUP_ITEMS', 'グループ共有アイテム承認');
define('_MB_XOONIPS_GROUP_EDIT_GROUP_INDEX', 'グループツリー編集');

// moderator menu block
define('_MB_XOONIPS_MODERATOR_EDIT_GROUPS', 'グループ編集');
define('_MB_XOONIPS_MODERATOR_CERTIFY_USERS', 'ユーザ承認');
define('_MB_XOONIPS_MODERATOR_GROUP_CERTIFY_PUBLIC_ITEMS', '公開グループアイテム承認');
define('_MB_XOONIPS_MODERATOR_CERTIFY_PUBLIC_ITEMS', '公開アイテム承認');
define('_MB_XOONIPS_MODERATOR_EDIT_PUBLIC_INDEX', '公開ツリー編集');
define('_MB_XOONIPS_MODERATOR_EVENT_LOG', 'イベントログ');

// quick search block
define('_MB_XOONIPS_SEARCH_QUICK', '検索');
define('_MB_XOONIPS_SEARCH_ADVANCED', '詳細検索');
define('_MB_XOONIPS_SEARCH_ALL', '全て');
define('_MB_XOONIPS_SEARCH_TITLE_AND_KEYWORD', 'タイトル & キーワード');
define('_MB_XOONIPS_SEARCH_METADATA', 'メタデータ');

define('_MB_XOONIPS_RANKING_VIEWED_ITEM', '最も多く閲覧されたアイテム ');
define('_MB_XOONIPS_RANKING_DOWNLOADED_ITEM', '最も多くダウンロードされたアイテム');
define('_MB_XOONIPS_RANKING_CONTRIBUTING_USER', '最も多く公開アイテムを作成したユーザ');
define('_MB_XOONIPS_RANKING_SEARCHED_KEYWORD', '最も多く検索されたキーワード ');
define('_MB_XOONIPS_RANKING_CONTRIBUTED_GROUP', '最も活気のあるグループ');

define('_MB_XOONIPS_RANKING_NEW_ITEM', '新着アイテム');
define('_MB_XOONIPS_RANKING_NEW_GROUP', '新着グループ');
define('_MB_XOONIPS_RANKING_EMPTY', 'ランキングが空です');
define('_MB_XOONIPS_RANKING_RANK_STR', '位');
