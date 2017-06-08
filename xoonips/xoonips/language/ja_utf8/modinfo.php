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
define('_MI_XOONIPS_ITEM_SMNAME1', 'アイテム登録');
define('_MI_XOONIPS_ITEM_SMNAME2', 'アイテム承認');
define('_MI_XOONIPS_ITEM_SMNAME3', '詳細検索');

define('_MI_XOONIPS_ITEM_BNAME1', 'XooNIps検索');

define('_MI_XOONIPS_MODERATOR_BNAME2', 'XooNIpsモデレータ メインメニュー');

//submenu labels
define('_MI_XOONIPS_MODERATOR_SMNAME1', 'グループ管理');
define('_MI_XOONIPS_MODERATOR_SMNAME2', 'ユーザ承認');

define('_MI_XOONIPS_INDEX_BNAME1', 'インデックスツリー');

//submenu labels
define('_MI_XOONIPS_INDEX_SMNAME1', 'プライベートツリー編集');
define('_MI_XOONIPS_INDEX_SMNAME2', 'グループツリー編集');
define('_MI_XOONIPS_INDEX_SMNAME3', '公開ツリー編集');

// Names of admin menu items
define('_MI_XOONIPS_CONFIG_DSN', 'DSN');
define('_MI_XOONIPS_CONFIG_DSN_DESC', 'ODBC の DSN 名');

define('_MI_XOONIPS_ACCOUNT_BNAME1', 'XooNIps ログイン');
define('_MI_XOONIPS_ACCOUNT_BNAME2', 'XooNIps ユーザメニュー');
define('_MI_XOONIPS_ACCOUNT_BNAME3', 'XooNIps グループメニュー');
define('_MI_XOONIPS_ACCOUNT_BNAME4', 'XooNIps モデレータメニュー');

//submenu labels
define('_MI_XOONIPS_ACCOUNT_SMNAME1', 'アカウント情報');
define('_MI_XOONIPS_ACCOUNT_SMNAME2', 'アカウント編集');
define('_MI_XOONIPS_ACCOUNT_SMNAME4', 'グループ管理');

//administrator menu
define('_MI_XOONIPS_ADMENU1', 'システム設定');
define('_MI_XOONIPS_ADMENU2', 'サイトポリシー設定');
define('_MI_XOONIPS_ADMENU3', 'メンテナンス');

//notification
define('_MI_XOONIPS_USER_NOTIFY', 'XooNIpsユーザ');
define('_MI_XOONIPS_USER_NOTIFYDSC', 'XooNIpsユーザへの通知');

define('_MI_XOONIPS_ADMINISTRATOR_NOTIFY', '管理者');
define('_MI_XOONIPS_ADMINISTRATOR_NOTIFYDSC', 'モデレータ・グループ管理者への通知');

// 以下XooNIpsNotificationを使用する通知．subjectはmain.php で定義
define('_MI_XOONIPS_ITEM_TRANSFER_NOTIFY', 'アイテム移譲通知');
define('_MI_XOONIPS_ITEM_TRANSFER_NOTIFYCAP', 'アイテムの所有者が変更された場合に通知する');
define('_MI_XOONIPS_ITEM_TRANSFER_NOTIFYDSC', 'アイテムの所有者が変更された場合に通知を受け取る．');

define('_MI_XOONIPS_ACCOUNT_CERTIFY_NOTIFY', 'アカウント承認通知');
define('_MI_XOONIPS_ACCOUNT_CERTIFY_NOTIFYCAP', 'アカウントの承認を通知する');
define('_MI_XOONIPS_ACCOUNT_CERTIFY_NOTIFYDSC', 'アカウントの承認を要求された場合・アカウントが承認された場合に通知を受け取る．');

define('_MI_XOONIPS_ITEM_CERTIFY_NOTIFY', 'アイテム承認通知');
define('_MI_XOONIPS_ITEM_CERTIFY_NOTIFYCAP', 'アイテムの承認を通知する');
define('_MI_XOONIPS_ITEM_CERTIFY_NOTIFYDSC', 'アイテムの公開要求があった場合、アイテムの公開要求が承認・承認拒否された場合に通知を受け取る．');

define('_MI_XOONIPS_USER_ITEM_TRANSFER_NOTIFY', 'アイテム移譲通知');
define('_MI_XOONIPS_USER_ITEM_TRANSFER_NOTIFYCAP', 'アイテムの移譲を通知する');
define('_MI_XOONIPS_USER_ITEM_TRANSFER_NOTIFYDSC', 'アイテムの移譲を要求された場合、アイテムの移譲要求を承認・拒否された場合に通知を受け取る．');

define('_MI_XOONIPS_USER_ITEM_UPDATED_NOTIFY', 'アイテム更新通知');
define('_MI_XOONIPS_USER_ITEM_UPDATED_NOTIFYCAP', '自分のアイテムの内容が管理者により更新された場合に通知する');
define('_MI_XOONIPS_USER_ITEM_UPDATED_NOTIFYDSC', '自分のアイテムの内容が管理者により更新された場合に通知を受け取る．');

define('_MI_XOONIPS_USER_ITEM_CERTIFIED_NOTIFY', 'アイテム承認通知');
define('_MI_XOONIPS_USER_ITEM_CERTIFIED_NOTIFYCAP', 'アイテムが承認された場合に通知する');
define('_MI_XOONIPS_USER_ITEM_CERTIFIED_NOTIFYDSC', 'アイテムをインデックスに登録することを承認された場合に通知を受け取る．');

define('_MI_XOONIPS_USER_ITEM_REJECTED_NOTIFY', 'アイテム承認拒否通知');
define('_MI_XOONIPS_USER_ITEM_REJECTED_NOTIFYCAP', 'アイテムが承認されなかった場合に通知する');
define('_MI_XOONIPS_USER_ITEM_REJECTED_NOTIFYDSC', 'アイテムをインデックスに登録することを承認されなかった場合に通知を受け取る．');

define('_MI_XOONIPS_USER_FILE_DOWNLOADED_NOTIFY', 'ファイルのダウンロードを通知');
define('_MI_XOONIPS_USER_FILE_DOWNLOADED_NOTIFYCAP', '自分が作成したアイテムのファイルがダウンロードされた場合に通知する');
define('_MI_XOONIPS_USER_FILE_DOWNLOADED_NOTIFYDSC', '自分が作成したアイテムのファイルがダウンロードされた場合に通知を受け取る．');

define('_MI_XOONIPS_USER_GROUP_ITEM_CERTIFIED_NOTIFY', 'グループアイテム承認通知');
define('_MI_XOONIPS_USER_GROUP_ITEM_CERTIFIED_NOTIFYCAP', 'グループアイテムが承認された場合に通知する');
define('_MI_XOONIPS_USER_GROUP_ITEM_CERTIFIED_NOTIFYDSC', 'グループアイテムをインデックスに登録することを承認された場合に通知を受け取る．');
define('_MI_XOONIPS_USER_GROUP_ITEM_CERTIFIED_NOTIFYSBJ', 'あなたのグループアイテムは承認されました');

define('_MI_XOONIPS_USER_GROUP_ITEM_REJECTED_NOTIFY', 'グループアイテム承認拒否通知');
define('_MI_XOONIPS_USER_GROUP_ITEM_REJECTED_NOTIFYCAP', 'グループアイテムが承認されなかった場合に通知する');
define('_MI_XOONIPS_USER_GROUP_ITEM_REJECTED_NOTIFYDSC', 'グループアイテムをインデックスに登録することを承認されなかった場合に通知を受け取る．');
define('_MI_XOONIPS_USER_GROUP_ITEM_REJECTED_NOTIFYSBJ', 'あなたのグループアイテムは承認されませんでした');

define('_MI_XOONIPS_USER_GROUP_ITEM_CERTIFY_REQUEST_NOTIFY', 'グループアイテム承認要求通知');
define('_MI_XOONIPS_USER_GROUP_ITEM_CERTIFY_REQUEST_NOTIFYCAP', '承認が必要なグループアイテムが発生した場合に通知する');
define('_MI_XOONIPS_USER_GROUP_ITEM_CERTIFY_REQUEST_NOTIFYDSC', 'グループアイテムがPublicインデックスに登録されたときに通知を受け取る．');
define('_MI_XOONIPS_USER_GROUP_ITEM_CERTIFY_REQUEST_NOTIFYSBJ', '承認待ちのグループアイテムがあります');

//itemtype block labels
define('_MI_XOONIPS_ITEMTYPE_BNAME1', 'アイテムタイプ一覧');

define('_MI_XOONIPS_RANKING', 'ランキング');
define('_MI_XOONIPS_RANKING_NEW', '新着');

//userlist block label
define('_MI_XOONIPS_USERLIST', 'ユーザ一覧');
