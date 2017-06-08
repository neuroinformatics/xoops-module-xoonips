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

// _AM_<MODULENAME>_<STRINGNAME>

// labels
define('_AM_XOONIPS_LABEL_ADD', '追加');
define('_AM_XOONIPS_LABEL_UPDATE', '更新');
define('_AM_XOONIPS_LABEL_MODIFY', '編集');
define('_AM_XOONIPS_LABEL_PREFERENCES', '設定');
define('_AM_XOONIPS_LABEL_DELETE', '削除');
define('_AM_XOONIPS_LABEL_REGISTER', '登録');
define('_AM_XOONIPS_LABEL_NEXT', '次へ');
define('_AM_XOONIPS_LABEL_BACK', '戻る');
define('_AM_XOONIPS_LABEL_DOWNLOAD', 'ダウンロード');
define('_AM_XOONIPS_LABEL_UPLOAD', 'アップロード');
define('_AM_XOONIPS_LABEL_CLEAR', 'クリア');
define('_AM_XOONIPS_LABEL_CANCEL', 'キャンセル');
define('_AM_XOONIPS_LABEL_EXECUTE', '実行');
define('_AM_XOONIPS_LABEL_REQUIRED', '必須');
define('_AM_XOONIPS_LABEL_OPTIONAL', '任意');
define('_AM_XOONIPS_LABEL_YES', 'はい');
define('_AM_XOONIPS_LABEL_NO', 'いいえ');
define('_AM_XOONIPS_LABEL_UP', '上へ');
define('_AM_XOONIPS_LABEL_DOWN', '下へ');
define('_AM_XOONIPS_LABEL_DATE', '日時');
define('_AM_XOONIPS_LABEL_URL', 'URL');
define('_AM_XOONIPS_LABEL_WEIGHT', '表示順');
define('_AM_XOONIPS_LABEL_ACTION', '操作');
define('_AM_XOONIPS_LABEL_VISIBLE', '表示する');
define('_AM_XOONIPS_LABEL_SORT', '並べ替え');
define('_AM_XOONIPS_LABEL_MODULENAME', 'モジュール名');
define('_AM_XOONIPS_LABEL_UID', 'UID');
define('_AM_XOONIPS_LABEL_UNAME', 'ユーザ名');
define('_AM_XOONIPS_LABEL_NAME', '本名');
define('_AM_XOONIPS_LABEL_EMAIL', 'メールアドレス');
define('_AM_XOONIPS_LABEL_POSITION', '職名');
define('_AM_XOONIPS_LABEL_DIVISION', '部門/研究室');
define('_AM_XOONIPS_LABEL_COMPANY_NAME', '機関名/大学名/会社名');
define('_AM_XOONIPS_LABEL_TEL', '電話番号');
define('_AM_XOONIPS_LABEL_FAX', 'FAX番号');
define('_AM_XOONIPS_LABEL_ADDRESS', '住所');
define('_AM_XOONIPS_LABEL_COUNTRY', '国名');
define('_AM_XOONIPS_LABEL_ZIPCODE', '郵便番号');
define('_AM_XOONIPS_LABEL_APPEAL', '自由記述');
define('_AM_XOONIPS_LABEL_NOTICE_MAIL', '送信メール間隔[日]');
define('_AM_XOONIPS_LABEL_PRIVATE', 'プライベート');
define('_AM_XOONIPS_LABEL_ITEM_NUMBER_LIMIT', '最大アイテム数');
define('_AM_XOONIPS_LABEL_INDEX_NUMBER_LIMIT', '最大インデックス数');
define('_AM_XOONIPS_LABEL_ITEM_STORAGE_LIMIT', '最大ディスク容量 [MB]');
define('_AM_XOONIPS_LABEL_PRIVATE_ITEM_NUMBER_LIMIT', _AM_XOONIPS_LABEL_PRIVATE.'の'._AM_XOONIPS_LABEL_ITEM_NUMBER_LIMIT);
define('_AM_XOONIPS_LABEL_PRIVATE_INDEX_NUMBER_LIMIT', _AM_XOONIPS_LABEL_PRIVATE.'の'._AM_XOONIPS_LABEL_INDEX_NUMBER_LIMIT);
define('_AM_XOONIPS_LABEL_PRIVATE_ITEM_STORAGE_LIMIT', _AM_XOONIPS_LABEL_PRIVATE.'の'._AM_XOONIPS_LABEL_ITEM_STORAGE_LIMIT);
define('_AM_XOONIPS_LABEL_REQUIRED_MARK', '<span style="font-weight: bold; color: red;">*</span>');
define('_AM_XOONIPS_LABEL_ITEM_ID', 'アイテムID');
define('_AM_XOONIPS_LABEL_ITEM_TYPE', 'アイテムタイプ');
define('_AM_XOONIPS_LABEL_ITEM_TITLE', 'タイトル');

// messages
define('_AM_XOONIPS_MSG_ACTIVATE_CONFIRM', 'このユーザは非アクティブです．今すぐアクティブにしますか？');
define('_AM_XOONIPS_MSG_EXECUTE_CONFIRM', '本当に実行しますか？');
define('_AM_XOONIPS_MSG_DELETE_CONFIRM', '本当に削除しますか？');
define('_AM_XOONIPS_MSG_REGISTER_CONFIRM', '本当に登録しますか？');
define('_AM_XOONIPS_MSG_EMPTY', '未登録です');
define('_AM_XOONIPS_MSG_DBUPDATED', 'データベースを更新しました');
define('_AM_XOONIPS_MSG_ILLACCESS', '不正なアクセスです');
define('_AM_XOONIPS_MSG_UNEXPECTED_ERROR', '予期せぬエラーが発生しました');
define('_AM_XOONIPS_MSG_PASSWORD_MISMATCH', 'パスワードが正しくありません．同じパスワードを二度入力して下さい．');
// unsupported
define('_AM_XOONIPS_UNSUPPORTED_DELUSER', 'ユーザの削除は現在の XooNIps のバージョンでは動作保証されていません．<br />今後のリリースでサポートする予定ですのでしばらくお待ちください．');

// main title
define('_AM_XOONIPS_TITLE', 'XooNIps 設定');

// sub titles
define('_AM_XOONIPS_SYSTEM_TITLE', 'システム設定');
define('_AM_XOONIPS_SYSTEM_DESC', 'XooNIps を動作させるための設定です．これらの項目はシステム管理者が変更します．');
define('_AM_XOONIPS_POLICY_TITLE', 'サイトポリシー設定');
define('_AM_XOONIPS_POLICY_DESC', 'XooNIps を運用する際のサイトポリシーを設定します．サイトを利用する前にこれらのポリシーを決めてください．');
define('_AM_XOONIPS_MAINTENANCE_TITLE', 'メンテナンス');
define('_AM_XOONIPS_MAINTENANCE_DESC', 'XooNIps を運用する上での様々な情報のメンテナンスを行います．');

// system configurations
define('_AM_XOONIPS_SYSTEM_BASIC_TITLE', '基本設定');
define('_AM_XOONIPS_SYSTEM_BASIC_DESC', 'XooNIps の最低限の動作に関わる設定です．');
define('_AM_XOONIPS_SYSTEM_TREE_TITLE', 'インラインフレーム表示設定');
define('_AM_XOONIPS_SYSTEM_TREE_DESC', 'インデックスツリーブロック内のインラインフレームの幅および高さを設定します．');
define('_AM_XOONIPS_SYSTEM_RSS_TITLE', 'RSS 設定');
define('_AM_XOONIPS_SYSTEM_RSS_DESC', 'アイテムの更新状況を RSS で配信するための設定です．');
define('_AM_XOONIPS_SYSTEM_PRINT_TITLE', '印刷設定');
define('_AM_XOONIPS_SYSTEM_PRINT_DESC', '印刷画面に関する設定です．');
define('_AM_XOONIPS_SYSTEM_OAIPMH_TITLE', 'OAI-PMH 設定');
define('_AM_XOONIPS_SYSTEM_OAIPMH_DESC', 'OAI-PMH のリポジトリおよびハーベスタに関する設定です．');
define('_AM_XOONIPS_SYSTEM_PROXY_TITLE', 'プロキシ設定');
define('_AM_XOONIPS_SYSTEM_PROXY_DESC', 'XooNIps から他のサーバのデータを取得する際のプロキシサーバについて設定します．');
define('_AM_XOONIPS_SYSTEM_MODULE_TITLE', 'イベント通知設定');
define('_AM_XOONIPS_SYSTEM_MODULE_DESC', '特定のイベントにおいて待ち構えているユーザにメッセージを送信する機能について設定します．');
//define( '_AM_XOONIPS_SYSTEM_MODULE_TITLE', 'モジュール設定' );
//define( '_AM_XOONIPS_SYSTEM_MODULE_DESC', 'イベント通知など XOOPS に機能統合されているオプションを設定します．' );
define('_AM_XOONIPS_SYSTEM_XOOPS_TITLE', 'XOOPS 拡張');
define('_AM_XOONIPS_SYSTEM_XOOPS_DESC', 'XooNIps は XOOPS 本体の情報を拡張して動作します．ここでは，XOOPS の情報を XooNIps で利用するための設定を行います．');
define('_AM_XOONIPS_SYSTEM_CHECK_TITLE', '動作確認');
define('_AM_XOONIPS_SYSTEM_CHECK_DESC', 'ミドルウェアや XooNIps の設定を確認して XooNIps が正しく動作するか検証します．');
// >> basic configurations
define('_AM_XOONIPS_SYSTEM_BASIC_MODERATOR_GROUP_TITLE', 'モデレータグループ');
define('_AM_XOONIPS_SYSTEM_BASIC_MODERATOR_GROUP_DESC', 'XooNIps のモデレータとして動作させる XOOPS グループを選びます．');
define('_AM_XOONIPS_SYSTEM_BASIC_UPLOAD_DIR_TITLE', 'ファイルアップロードディレクトリ');
define('_AM_XOONIPS_SYSTEM_BASIC_UPLOAD_DIR_DESC', '各アイテムの添付ファイルを格納するディレクトリをシステムの絶対パスで指定します．このディレクトリは Web サーバプロセスの権限で書き込みができる必要があります．');
define('_AM_XOONIPS_SYSTEM_BASIC_MAGIC_FILE_PATH_TITLE', 'マジックファイルのパス');
define('_AM_XOONIPS_SYSTEM_BASIC_MAGIC_FILE_PATH_DESC', 'mime-typeを自動判別するために必要なマジックファイルを絶対パスで指定します．拡張子\'.mime\'は不要です．');
// >> tree block configuration
define('_AM_XOONIPS_SYSTEM_TREE_TREE_FRAME_WIDTH_TITLE', 'インデックスツリーの幅');
define('_AM_XOONIPS_SYSTEM_TREE_TREE_FRAME_WIDTH_DESC', 'インデックスツリーブロック内に表示されるインラインフレームの幅を設定します．');
define('_AM_XOONIPS_SYSTEM_TREE_TREE_FRAME_HEIGHT_TITLE', 'インデックスツリーの高さ');
define('_AM_XOONIPS_SYSTEM_TREE_TREE_FRAME_HEIGHT_DESC', 'インデックスツリーブロック内に表示されるインラインフレームの高さを設定します．');
// >> rss feed configuration
define('_AM_XOONIPS_SYSTEM_RSS_FEED_URL_TITLE', 'RSS 配信 URL 一覧');
define('_AM_XOONIPS_SYSTEM_RSS_FEED_URL_DESC', 'XooNIps では RSS 1.0 (RDF), RSS 2.0, Atom 1.0 の 3種類のフィード形式をサポートしています．<br />以下のいずれかの URL を公開することにより公開アイテムの更新状況およびグループの新規作成状況をアナウンスできます．');
define('_AM_XOONIPS_SYSTEM_RSS_FEED_ITEM_MAX_TITLE', '最大掲載記事件数');
define('_AM_XOONIPS_SYSTEM_RSS_FEED_ITEM_MAX_DESC', 'RSS で配信する最大記事数を指定します．この値は 0 以上に設定してください．');
// >> printer priendly configuration
define('_AM_XOONIPS_SYSTEM_PRINT_PRINTER_FRIENDLY_HEADER_TITLE', 'ページヘッダ');
define('_AM_XOONIPS_SYSTEM_PRINT_PRINTER_FRIENDLY_HEADER_DESC', '各ページの印刷画面に表示されるヘッダ情報を HTML で記述します．');
// >> oai-pmh configuration
define('_AM_XOONIPS_SYSTEM_OAIPMH_REPOSITORY_TITLE', 'リポジトリ設定');
define('_AM_XOONIPS_SYSTEM_OAIPMH_HARVESTER_TITLE', 'ハーベスタ設定');
define('_AM_XOONIPS_SYSTEM_OAIPMH_REPOSITORY_INSTITUTION_TITLE', '&lt;institution&gt; の値');
define('_AM_XOONIPS_SYSTEM_OAIPMH_REPOSITORY_INSTITUTION_DESC', '');
define('_AM_XOONIPS_SYSTEM_OAIPMH_REPOSITORY_PUBLISHER_TITLE', '&lt;publisher&gt; の値');
define('_AM_XOONIPS_SYSTEM_OAIPMH_REPOSITORY_PUBLISHER_DESC', '');
define('_AM_XOONIPS_SYSTEM_OAIPMH_REPOSITORY_NAME_TITLE', 'リポジトリ名');
define('_AM_XOONIPS_SYSTEM_OAIPMH_REPOSITORY_NAME_DESC', '');
define('_AM_XOONIPS_SYSTEM_OAIPMH_REPOSITORY_NIJC_CODE_TITLE', 'データベース ID');
define('_AM_XOONIPS_SYSTEM_OAIPMH_REPOSITORY_NIJC_CODE_DESC', '');
define('_AM_XOONIPS_SYSTEM_OAIPMH_REPOSITORY_DELETION_TRACK_TITLE', 'アイテムの削除状態を保存する日数');
define('_AM_XOONIPS_SYSTEM_OAIPMH_REPOSITORY_DELETION_TRACK_DESC', '');
define('_AM_XOONIPS_SYSTEM_OAIPMH_HARVESTER_REPOSITORIES_TITLE', 'ハーベスト対象のリポジトリ URL');
define('_AM_XOONIPS_SYSTEM_OAIPMH_HARVESTER_REPOSITORIES_DESC', 'ハーベストするリポジトリの URL を指定します．入力はリポジトリごとに改行して下さい．<br />行の先頭に <strong>;</strong> を付けることでコメントアウトされます．');
// >> proxy configuration
define('_AM_XOONIPS_SYSTEM_PROXY_PROXY_HOST_TITLE', 'ホスト名');
define('_AM_XOONIPS_SYSTEM_PROXY_PROXY_HOST_DESC', 'プロキシを利用する場合，プロキシサーバのホスト名を指定します．');
define('_AM_XOONIPS_SYSTEM_PROXY_PROXY_PORT_TITLE', 'ポート番号');
define('_AM_XOONIPS_SYSTEM_PROXY_PROXY_PORT_DESC', 'プロキシサーバのポート番号を指定します．');
define('_AM_XOONIPS_SYSTEM_PROXY_PROXY_USER_TITLE', 'ユーザ名');
define('_AM_XOONIPS_SYSTEM_PROXY_PROXY_USER_DESC', 'プロキシサーバにユーザ認証が必要な場合，ユーザ名を入力します．');
define('_AM_XOONIPS_SYSTEM_PROXY_PROXY_PASS_TITLE', 'パスワード');
define('_AM_XOONIPS_SYSTEM_PROXY_PROXY_PASS_DESC', 'ユーザ認証のためのパスワードを入力します．');
// >> XOOPS extension
define('_AM_XOONIPS_SYSTEM_XOOPS_USERADD_TITLE', 'XOOPS ユーザを XooNIps へ登録');
define('_AM_XOONIPS_SYSTEM_XOOPS_USERADD_DESC', '既存の XOOPS ユーザを XooNIps を利用できるユーザとしてへシステムに登録します．');
define('_AM_XOONIPS_SYSTEM_XOOPS_USERADD_MSG_EMPTY', '未登録のユーザはいません');
define('_AM_XOONIPS_SYSTEM_XOOPS_ZOMBIELIST_TITLE', 'XOOPS の情報だけ削除されたユーザ');
define('_AM_XOONIPS_SYSTEM_XOOPS_ZOMBIELIST_DESC', 'XOOPS の機能によって削除され，XOOPS のユーザ情報を持たない XooNIps ユーザを表示します．');
define('_AM_XOONIPS_SYSTEM_XOOPS_ZOMBIELIST_LABEL_ITEMCOUNT', '登録アイテム (グループ/公開)');
define('_AM_XOONIPS_SYSTEM_XOOPS_ZOMBIELIST_MSG_EMPTY', '不整合が起きているユーザはいません');
define('_AM_XOONIPS_SYSTEM_XOOPS_ITEM_RESCUE_TITLE', '公開・共有アイテムの復旧');
define('_AM_XOONIPS_SYSTEM_XOOPS_ITEM_RESCUE_DESC', '指定されたユーザは下記の公開・グループ共有アイテムを持っています．<br />アイテムの移譲先を指定してください．');
define('_AM_XOONIPS_SYSTEM_XOOPS_ITEM_RESCUE_LABEL_FROM', 'From');
define('_AM_XOONIPS_SYSTEM_XOOPS_ITEM_RESCUE_LABEL_TO', 'To');
define('_AM_XOONIPS_SYSTEM_XOOPS_ZOMBIE_DELETE_MSG_REDIRECT', 'ユーザを削除する前にアイテム移譲を行ってください．');
// >> configuration check
define('_AM_XOONIPS_SYSTEM_CHECK_FORM_TITLE', 'XooNIps 動作のチェック');
define('_AM_XOONIPS_SYSTEM_CHECK_LABEL_CHECK', 'テスト');
define('_AM_XOONIPS_SYSTEM_CHECK_LABEL_RECHECK', '再テスト');
define('_AM_XOONIPS_SYSTEM_CHECK_LABEL_OK', 'OK');
define('_AM_XOONIPS_SYSTEM_CHECK_LABEL_NOTICE', 'Notice');
define('_AM_XOONIPS_SYSTEM_CHECK_LABEL_FAIL', 'Fail');
define('_AM_XOONIPS_SYSTEM_CHECK_LABEL_ENABLE', '有効');
define('_AM_XOONIPS_SYSTEM_CHECK_LABEL_DISABLE', '無効');
define('_AM_XOONIPS_SYSTEM_CHECK_LABEL_RESULTS', '総合判定');
define('_AM_XOONIPS_SYSTEM_CHECK_CATEGORY_PHPINI', 'php.ini');
define('_AM_XOONIPS_SYSTEM_CHECK_CATEGORY_PHPEXT', 'PHP 拡張モジュール');
define('_AM_XOONIPS_SYSTEM_CHECK_CATEGORY_MYSQL', 'MySQL 文字コード');
define('_AM_XOONIPS_SYSTEM_CHECK_CATEGORY_COMMAND', '外部補助プログラム');
define('_AM_XOONIPS_SYSTEM_CHECK_CATEGORY_XOONIPS', 'XooNIps の設定');
define('_AM_XOONIPS_SYSTEM_CHECK_MSG_PHP', 'PHPの設定を確認してください');
define('_AM_XOONIPS_SYSTEM_CHECK_MSG_MYSQL', 'MySQLの設定を確認してください');
define('_AM_XOONIPS_SYSTEM_CHECK_MSG_COMMAND', '外部補助プログラムの設定を確認して下さい');
define('_AM_XOONIPS_SYSTEM_CHECK_MSG_XOONIPS', 'XooNIpsの設定を確認してください');

// site policy settings
define('_AM_XOONIPS_POLICY_ACCOUNT_TITLE', 'ユーザ情報');
define('_AM_XOONIPS_POLICY_ACCOUNT_DESC', 'ユーザ情報に関するポリシーの設定を行います．');
define('_AM_XOONIPS_POLICY_GROUP_TITLE', 'グループ情報');
define('_AM_XOONIPS_POLICY_GROUP_DESC', 'グループ情報に関するポリシーの設定を行います．');
define('_AM_XOONIPS_POLICY_ITEM_TITLE', 'アイテム情報');
define('_AM_XOONIPS_POLICY_ITEM_DESC', 'アイテム情報に関するポリシーの設定を行います．');
define('_AM_XOONIPS_POLICY_MODERATOR_TITLE', 'モデレータ権限');
define('_AM_XOONIPS_POLICY_MODERATOR_DESC', 'モデレータの権限に関するポリシーの設定を行います．');
define('_AM_XOONIPS_POLICY_POSITION_TITLE', '職名一覧');
define('_AM_XOONIPS_POLICY_POSITION_DESC', 'ユーザ情報の編集の際，職名として選択可能な項目一覧を設定します．表示順に負の値を設定するとユーザ一覧画面においてその職名が非表示になります．');
define('_AM_XOONIPS_POLICY_RANKING_TITLE', 'ランキング');
define('_AM_XOONIPS_POLICY_RANKING_DESC', 'ランキングに関する設定を行います．');
// >> account policies
define('_AM_XOONIPS_POLICY_ACCOUNT_REGISTER_USER_TITLE', '新規ユーザ登録方法の設定');
define('_AM_XOONIPS_POLICY_ACCOUNT_ACTIVATE_USER_TITLE', 'アカウント有効化の方法');
define('_AM_XOONIPS_POLICY_ACCOUNT_ACTIVATE_USER_DESC', '新規登録されたユーザを有効にするための方法を設定します．');
define('_AM_XOONIPS_POLICY_ACCOUNT_ACTIVATE_USER_USER', 'ユーザ自身の確認が必要(推奨)');
define('_AM_XOONIPS_POLICY_ACCOUNT_ACTIVATE_USER_AUTO', '自動的にアカウントを有効にする');
define('_AM_XOONIPS_POLICY_ACCOUNT_ACTIVATE_USER_ADMIN', '管理者が確認してアカウントを有効にする');
define('_AM_XOONIPS_POLICY_ACCOUNT_CERTIFY_USER_TITLE', 'アカウント承認の方法');
define('_AM_XOONIPS_POLICY_ACCOUNT_CERTIFY_USER_DESC', 'アカウントを有効化されたユーザが XooNIps を利用するためにはそのユーザアカウントを承認する必要があります．ここではこのアカウント承認の方法を設定します．');
define('_AM_XOONIPS_POLICY_ACCOUNT_CERTIFY_USER_MANUAL', 'モデレータが確認してアカウントを承認する');
define('_AM_XOONIPS_POLICY_ACCOUNT_CERTIFY_USER_AUTO', '自動的にアカウントを承認する');
define('_AM_XOONIPS_POLICY_ACCOUNT_INFO_REQUIREMENT_TITLE', 'ユーザ情報入力時の必須項目の設定');
define('_AM_XOONIPS_POLICY_ACCOUNT_INITIAL_VALUES_TITLE', '新規ユーザ登録時の初期値の設定');
define('_AM_XOONIPS_POLICY_ACCOUNT_INITIAL_MAX_ITEM_DESC', '個人領域に登録可能なアイテム数の最大値を設定します．');
define('_AM_XOONIPS_POLICY_ACCOUNT_INITIAL_MAX_INDEX_DESC', '個人領域に登録可能なインデックス数の最大値を設定します．');
define('_AM_XOONIPS_POLICY_ACCOUNT_INITIAL_MAX_DISK_DESC', '個人領域の利用可能なディスク容量の最大値を[MB]単位で指定します．小数点を含む実数を指定できます．');
// >> group policies
define('_AM_XOONIPS_POLICY_GROUP_INITIAL_VALUES_TITLE', '新規グループ作成時の初期値の設定');
define('_AM_XOONIPS_POLICY_GROUP_INITIAL_MAX_ITEM_DESC', 'グループ領域に登録可能なアイテム数の最大値を設定します．');
define('_AM_XOONIPS_POLICY_GROUP_INITIAL_MAX_INDEX_DESC', 'グループ領域に登録可能なインデックス数の最大値を設定します．');
define('_AM_XOONIPS_POLICY_GROUP_INITIAL_MAX_DISK_DESC', 'グループ領域の利用可能なディスク容量の最大値を[MB]単位で指定します．小数点を含む実数を指定できます．');
// >> item policies
define('_AM_XOONIPS_POLICY_ITEM_PUBLIC_TITLE', 'アイテム公開');
define('_AM_XOONIPS_POLICY_ITEM_PUBLIC_DESC', 'アイテムの公開に関するポリシーの設定を行います．');
define('_AM_XOONIPS_POLICY_ITEM_PUBLIC_MAIN_TITLE', 'アイテムの公開ポリシー');
define('_AM_XOONIPS_POLICY_ITEM_PUBLIC_CERTIFY_ITEM_TITLE', '公開アイテムの承認方法');
define('_AM_XOONIPS_POLICY_ITEM_PUBLIC_CERTIFY_ITEM_DESC', 'アイテムを公開するためにはそのアイテムの公開を承認する必要があります．ここではこのアイテム公開の承認方法を設定します．');
define('_AM_XOONIPS_POLICY_ITEM_PUBLIC_CERTIFY_ITEM_MANUAL', 'モデレータが確認してアイテムの公開を承認する');
define('_AM_XOONIPS_POLICY_ITEM_PUBLIC_CERTIFY_ITEM_AUTO', '自動的にアイテムの公開を承認する');
define('_AM_XOONIPS_POLICY_ITEM_PUBLIC_TARGET_USER_TITLE', '公開領域を閲覧可能なユーザ');
define('_AM_XOONIPS_POLICY_ITEM_PUBLIC_TARGET_USER_DESC', '公開領域のインデックスやアイテムを閲覧することのできるユーザの範囲を設定します．');
define('_AM_XOONIPS_POLICY_ITEM_PUBLIC_TARGET_USER_PLATFORM', 'XooNIps に登録されたユーザのみ');
define('_AM_XOONIPS_POLICY_ITEM_PUBLIC_TARGET_USER_ALL', 'ゲストを含む全ユーザ');
define('_AM_XOONIPS_POLICY_ITEM_PUBLIC_OTHER_TITLE', 'その他の関連する設定');
define('_AM_XOONIPS_POLICY_ITEM_PUBLIC_DOWNLOAD_FILE_TITLE', '添付ファイルのダウンロード時のファイル形式');
define('_AM_XOONIPS_POLICY_ITEM_PUBLIC_DOWNLOAD_FILE_DESC', '添付ファイルをダウンロードする際のファイル形式を指定します．');
define('_AM_XOONIPS_POLICY_ITEM_PUBLIC_DOWNLOAD_FILE_ZIP', 'メタ情報と共に ZIP 圧縮する (推奨)');
define('_AM_XOONIPS_POLICY_ITEM_PUBLIC_DOWNLOAD_FILE_PLAIN', 'オリジナルのまま');
define('_AM_XOONIPS_POLICY_ITEM_PUBLIC_ITEM_SHOW_TITLE', '公開領域の全アイテムから業績アイテムを選択可能にする');
define('_AM_XOONIPS_POLICY_ITEM_PUBLIC_ITEM_SHOW_DESC', '「いいえ」を選ぶとユーザ本人が登録したアイテムからのみ選択可能となります．');
define('_AM_XOONIPS_POLICY_ITEM_TYPE_TITLE', 'アイテムタイプ');
define('_AM_XOONIPS_POLICY_ITEM_TYPE_DESC', 'アイテムタイプに関する設定を行います．');
define('_AM_XOONIPS_POLICY_ITEM_TYPE_VIEWCONFIG_TITLE', '表示設定');
define('_AM_XOONIPS_POLICY_ITEM_TYPE_VIEWCONFIG_DESC', '表示順は 1以上の整数を指定してください．');
define('_AM_XOONIPS_POLICY_ITEM_TYPE_EMPTY', 'アイテムタイプがインストールされていません');
define('_AM_XOONIPS_POLICY_ITEM_IMEXPORT_TITLE', 'インポート・エクスポート');
define('_AM_XOONIPS_POLICY_ITEM_IMEXPORT_DESC', 'インポート・エクスポートに関するポリシーの設定を行います．');
define('_AM_XOONIPS_POLICY_ITEM_IMEXPORT_EXPORT_TITLE', 'エクスポート');
define('_AM_XOONIPS_POLICY_ITEM_IMEXPORT_IMPORT_TITLE', 'インポート');
define('_AM_XOONIPS_POLICY_ITEM_IMEXPORT_EXPORT_ENABLED_TITLE', 'エクスポートを許可');
define('_AM_XOONIPS_POLICY_ITEM_IMEXPORT_EXPORT_ENABLED_DESC', '「はい」を選択すると登録ユーザが自身の作成したアイテムをエクスポートできるようになります．ただし，モデレータはこの設定に関わらずいつでもエクスポートできます．');
define('_AM_XOONIPS_POLICY_ITEM_IMEXPORT_EXPORT_ATTACHMENT_TITLE', '添付ファイルのエクスポートを許可');
define('_AM_XOONIPS_POLICY_ITEM_IMEXPORT_EXPORT_ATTACHMENT_DESC', '「はい」を選択するとアイテムの添付ファイルをエクスポートできるようになります．');
define('_AM_XOONIPS_POLICY_ITEM_IMEXPORT_IMPORT_ENABLED_TITLE', 'インポートを許可');
define('_AM_XOONIPS_POLICY_ITEM_IMEXPORT_IMPORT_ENABLED_DESC', '「はい」を選択すると登録ユーザが自身の個人領域に対してデータをインポートできるようになります．ただし，モデレータはこの設定に関わらずいつでもインポートできます．');
define('_AM_XOONIPS_POLICY_ITEM_COMMENT_TITLE', 'コメント機能');
define('_AM_XOONIPS_POLICY_ITEM_COMMENT_DESC', 'アイテムへのコメント機能の設定を行います.<br />この機能は d3forum モジュールに依存しており，利用するにはまず d3forum をインストールしコメントを格納するフォーラムを作成する必要があります．');
define('_AM_XOONIPS_POLICY_ITEM_COMMENT_DIRNAME_TITLE', 'ディレクトリ名の設定');
define('_AM_XOONIPS_POLICY_ITEM_COMMENT_DIRNAME_DESC', 'コメント機能で利用するd3forumのディレクトリ名の設定を行います.');
define('_AM_XOONIPS_POLICY_ITEM_COMMENT_DIRNAME_NOTFOUND', '保存された d3forum のディレクトリ名が存在しません');
define('_AM_XOONIPS_POLICY_ITEM_COMMENT_FORUMID_TITLE', 'フォーラムIDの設定');
define('_AM_XOONIPS_POLICY_ITEM_COMMENT_FORUMID_DESC', 'd3forumで作成したフォーラムの ID 番号を設定します.');
define('_AM_XOONIPS_POLICY_ITEM_COMMENT_FORUMID_NOTFOUND', '保存されたフォーラムの ID 番号が存在しません');

// >> moderator privileges policies
define('_AM_XOONIPS_POLICY_MODERATOR_MODIFY_TITLE', '全ユーザのアイテム編集を許可');
define('_AM_XOONIPS_POLICY_MODERATOR_MODIFY_DESC', '「はい」を選択するとモデレータは全ユーザのアイテムを編集できるようになります．');
// >> positions configuration
define('_AM_XOONIPS_POLICY_POSITION_MODIFY_TITLE', '編集');
define('_AM_XOONIPS_POLICY_POSITION_ADD_TITLE', '新規登録');
// >> ranking configuration
define('_AM_XOONIPS_POLICY_RANKING_BLOCK_TITLE', 'ブロック');
define('_AM_XOONIPS_POLICY_RANKING_NAME_TITLE', 'ランキング表示内容');
define('_AM_XOONIPS_POLICY_RANKING_RANGE_DESC1', '各ランキングの上位');
define('_AM_XOONIPS_POLICY_RANKING_RANGE_DESC2', '件を表示します．');
define('_AM_XOONIPS_POLICY_RANKING_DAYS_TITLE', 'ランキング対象期間');
define('_AM_XOONIPS_POLICY_RANKING_DAYS_DESC1', 'この設定を ON にした場合，集計値ファイルの値はランキングに反映されません．');
define('_AM_XOONIPS_POLICY_RANKING_DAYS_DESC2', '過去');
define('_AM_XOONIPS_POLICY_RANKING_DAYS_DESC3', '日間のデータのみをランキング対象をします．');

// site maintenance
define('_AM_XOONIPS_MAINTENANCE_ACCOUNT_TITLE', 'ユーザ管理');
define('_AM_XOONIPS_MAINTENANCE_ACCOUNT_DESC', 'ユーザ情報の管理を行います');
define('_AM_XOONIPS_MAINTENANCE_ITEM_TITLE', 'アイテム管理');
define('_AM_XOONIPS_MAINTENANCE_ITEM_DESC', 'アイテム管理のための一括操作を行います．影響範囲が大きいため各操作は慎重に行ってください．');
define('_AM_XOONIPS_MAINTENANCE_POSITION_TITLE', 'ユーザ表示順');
define('_AM_XOONIPS_MAINTENANCE_POSITION_DESC', 'ユーザ一覧画面に表示される職名別ユーザ一覧の表示順序をカスタマイズします．表示順が 0未満ならそのユーザは画面に表示されません');
define('_AM_XOONIPS_MAINTENANCE_RANKING_TITLE', 'ランキング');
define('_AM_XOONIPS_MAINTENANCE_RANKING_DESC', 'ランキングの集計値の管理を行います．');
define('_AM_XOONIPS_MAINTENANCE_FILESEARCH_TITLE', 'ファイル検索');
define('_AM_XOONIPS_MAINTENANCE_FILESEARCH_DESC', 'ファイル検索用のインデックスの管理を行います．');
define('_AM_XOONIPS_MAINTENANCE_OAIPMH_TITLE', 'OAI-PMH');
define('_AM_XOONIPS_MAINTENANCE_OAIPMH_DESC', 'OAI-PMH のハーベスト実行と実行結果の確認を行います．');
// >> account management
define('_AM_XOONIPS_MAINTENANCE_ACCOUNT_ADD_TITLE', '新規ユーザ追加');
define('_AM_XOONIPS_MAINTENANCE_ACCOUNT_MODIFY_TITLE', 'ユーザ情報編集');
define('_AM_XOONIPS_MAINTENANCE_ACCOUNT_EMPTY', 'ユーザが存在しません');
define('_AM_XOONIPS_MAINTENANCE_ACCOUNT_PAGENAVI_FORMAT', '%1$d - %2$d of %3$d Users');
define('_AM_XOONIPS_MAINTENANCE_ACCOUNT_ACONFIRM_TITLE', 'アクティベート確認');
define('_AM_XOONIPS_MAINTENANCE_ACCOUNT_DCONFIRM_TITLE', 'ユーザ削除確認');
define('_AM_XOONIPS_MAINTENANCE_ACCOUNT_DCONFIRM_MSG_IGNORE_USER', 'システム管理者，モデレータ，グループ管理者はアカウントを削除することはできません');
define('_AM_XOONIPS_MAINTENANCE_ACCOUNT_DCONFIRM_MSG_ITEM_HANDOVER', '公開・グループ共有アイテムを他のユーザに移譲してください');
define('_AM_XOONIPS_MAINTENANCE_ACCOUNT_DELETE_MSG_SUCCESS', 'ユーザを削除しました');
// >> item management
define('_AM_XOONIPS_MAINTENANCE_ITEM_LABEL_INDEX', 'インデックス');
define('_AM_XOONIPS_MAINTENANCE_ITEM_LABEL_SUCCEED', '成功');
define('_AM_XOONIPS_MAINTENANCE_ITEM_LABEL_FAILED', 'エラー');
define('_AM_XOONIPS_MAINTENANCE_ITEM_LABEL_UNCERTIFIED', '未承認状態');
define('_AM_XOONIPS_MAINTENANCE_ITEM_LABEL_ALLUSERS', '全てのユーザ');
define('_AM_XOONIPS_MAINTENANCE_ITEM_MSG_SELECT_INDEX', 'インデックスを選択してください．');
define('_AM_XOONIPS_MAINTENANCE_ITEM_MSG_SELECT_USER', '操作対象のユーザを選択して下さい．');
define('_AM_XOONIPS_MAINTENANCE_ITEM_INDEX_EMPTY', 'インデックスが選択されていません');
define('_AM_XOONIPS_MAINTENANCE_ITEM_WITHDRAW_TITLE', '公開アイテム一括取り下げ');
define('_AM_XOONIPS_MAINTENANCE_ITEM_WITHDRAW_DESC', 'インデックスを選択してください．選択したインデックスに存在する全てのアイテムの公開を取り下げます．');
define('_AM_XOONIPS_MAINTENANCE_ITEM_WITHDRAW_LABEL_WITHDRAW', '公開取り下げ');
define('_AM_XOONIPS_MAINTENANCE_ITEM_WITHDRAW_CONFIRM', '選択されたインデックスに存在する全てのアイテムの公開を取り下げます．');
define('_AM_XOONIPS_MAINTENANCE_ITEM_WUPDATE_TITLE', '実行結果');
define('_AM_XOONIPS_MAINTENANCE_ITEM_WUPDATE_DESC', '未承認状態のアイテムは変更されていません');
define('_AM_XOONIPS_MAINTENANCE_ITEM_DELETE_TITLE', 'アイテム一括削除');
define('_AM_XOONIPS_MAINTENANCE_ITEM_DELETE_DESC', 'インデックスを選択してください．選択したインデックスに存在する全てのアイテムを削除します．');
define('_AM_XOONIPS_MAINTENANCE_ITEM_DELETE_CONFIRM', '選択されたインデックスに存在する全てのアイテムを削除します．');
define('_AM_XOONIPS_MAINTENANCE_ITEM_DUPDATE_TITLE', '実行結果');
define('_AM_XOONIPS_MAINTENANCE_ITEM_TRANSFER_TITLE', 'アイテムの移譲');

// >> position (user list) maintenance
define('_AM_XOONIPS_MAINTENANCE_POSITION_USER_TITLE', '職名 [ ユーザ名 ]');
define('_AM_XOONIPS_MAINTENANCE_POSITION_EMPTY', '職名を設定しているユーザが存在しません');
// >> ranking file maintenance
define('_AM_XOONIPS_MAINTENANCE_RANKING_FILE_TITLE', '集計値ファイルの操作');
define('_AM_XOONIPS_MAINTENANCE_RANKING_DOWNLOAD_TITLE', 'ダウンロード');
define('_AM_XOONIPS_MAINTENANCE_RANKING_DOWNLOAD_DESC', '集計値をダウンロードする際のファイル名を指定します．');
define('_AM_XOONIPS_MAINTENANCE_RANKING_UPLOAD_TITLE', 'アップロード');
define('_AM_XOONIPS_MAINTENANCE_RANKING_UPLOAD_DESC', 'アップロードする集計値ファイルを指定します．');
define('_AM_XOONIPS_MAINTENANCE_RANKING_CLEAR_TITLE', '集計値のクリア');
define('_AM_XOONIPS_MAINTENANCE_RANKING_CLEAR_DESC', '集計値を消去します．');
define('_AM_XOONIPS_MAINTENANCE_RANKING_CLEAR_MESSAGE', '%s 〜 %s の集計値を持っています．');
define('_AM_XOONIPS_MAINTENANCE_RANKING_CLEAR_EMPTY', '集計値はありません．');
define('_AM_XOONIPS_MAINTENANCE_RANKING_NOTE', '集計値はイベントログを元に自動生成されます．イベントログを消去する際には集計値ファイルを事前にダウンロードし，消去後にアップロードすればランキングの集計値のみ継続利用できます．');
define('_AM_XOONIPS_MAINTENANCE_RANKING_LOCKED', '現在集計中です．数秒待ってからリトライしてください．');
// >> file search index maintenance
define('_AM_XOONIPS_MAINTENANCE_FILESEARCH_LABEL_PLUGIN', '検索プラグイン');
define('_AM_XOONIPS_MAINTENANCE_FILESEARCH_LABEL_MIMETYPE', 'MIME Type');
define('_AM_XOONIPS_MAINTENANCE_FILESEARCH_LABEL_SUFFIX', '拡張子');
define('_AM_XOONIPS_MAINTENANCE_FILESEARCH_LABEL_VERSION', 'バージョン');
define('_AM_XOONIPS_MAINTENANCE_FILESEARCH_LABEL_RESCAN', '再スキャン');
define('_AM_XOONIPS_MAINTENANCE_FILESEARCH_LABEL_RESCANNING', 'スキャン中');
define('_AM_XOONIPS_MAINTENANCE_FILESEARCH_PLUGINS_TITLE', '利用可能な検索プラグイン');
define('_AM_XOONIPS_MAINTENANCE_FILESEARCH_PLUGINS_EMPTY', '利用可能な検索プラグインが存在しません');
define('_AM_XOONIPS_MAINTENANCE_FILESEARCH_RESCAN_TITLE', '全ファイルの再スキャン');
define('_AM_XOONIPS_MAINTENANCE_FILESEARCH_RESCAN_LABEL_FILECOUNT', '登録済みファイル数');
define('_AM_XOONIPS_MAINTENANCE_FILESEARCH_RESCAN_INFO_TITLE', 'ファイル情報の更新');
define('_AM_XOONIPS_MAINTENANCE_FILESEARCH_RESCAN_INFO_DESC', '全てのファイルをスキャンしてファイルの詳細情報(MIME Type，サムネイル画像)を更新します');
define('_AM_XOONIPS_MAINTENANCE_FILESEARCH_RESCAN_INDEX_TITLE', '検索インデックスの更新');
define('_AM_XOONIPS_MAINTENANCE_FILESEARCH_RESCAN_INDEX_DESC', '全てのファイルをスキャンして検索用インデックスを再構築します');
// >> oaipmh maintenance
define('_AM_XOONIPS_MAINTENANCE_OAIPMH_LABEL_LASTRESULT', '実行結果');
define('_AM_XOONIPS_MAINTENANCE_OAIPMH_RESULTS_TITLE', '最新の実行結果');
define('_AM_XOONIPS_MAINTENANCE_OAIPMH_RESULTS_EMPTY', '実行結果が存在しません');
define('_AM_XOONIPS_MAINTENANCE_OAIPMH_CONFIGURE_TITLE', 'リポジトリ URL 変更');
define('_AM_XOONIPS_MAINTENANCE_OAIPMH_CONFIGURE_DESC', 'ハーベスト対象のリポジトリ URL を修正する');
define('_AM_XOONIPS_MAINTENANCE_OAIPMH_HARVEST_TITLE', 'ハーベスト実行');
define('_AM_XOONIPS_MAINTENANCE_OAIPMH_HARVEST_DESC', 'いますぐハーベストするにはこのボタンを押してください');
define('_AM_XOONIPS_MAINTENANCE_OAIPMH_LABEL_HARVEST', 'ハーベストする');

define('_AM_XOONIPS_MAINTENANCE_TRANSFER_ADMIN_ITEM_SELECT_CHECK', '選択');
define('_AM_XOONIPS_MAINTENANCE_TRANSFER_ADMIN_ITEM_SELECT_ITEM_ID', 'ID');
define('_AM_XOONIPS_MAINTENANCE_TRANSFER_ADMIN_ITEM_SELECT_ITEM_TYPE', 'アイテムタイプ');
define('_AM_XOONIPS_MAINTENANCE_TRANSFER_ADMIN_ITEM_SELECT_TITLE', 'タイトル');
define('_AM_XOONIPS_MAINTENANCE_TRANSFER_ADMIN_ITEM_SELECT_FROM', 'From');
define('_AM_XOONIPS_MAINTENANCE_TRANSFER_ADMIN_ITEM_SELECT_TO', 'To');
define('_AM_XOONIPS_MAINTENANCE_TRANSFER_ADMIN_ITEM_SELECT_SUBMIT', '移譲する');
define('_AM_XOONIPS_MAINTENANCE_TRANSFER_ADMIN_ITEM_SELECT_CAN_NOT_TRANSFER_BECAUSE_OF_ITEM_LOCK', 'アイテム(%d)はロックされているため移譲できません');
define('_AM_XOONIPS_MAINTENANCE_TRANSFER_ADMIN_ITEM_SELECT_CAN_NOT_TRANSFER_BECAUSE_OF_CHILD_OF_OTHER_ITEM', 'アイテム(%d)は他のアイテムからも参照されているため移譲できません');
define('_AM_XOONIPS_MAINTENANCE_TRANSFER_ADMIN_ITEM_SELECT_NO_ITEM', 'アイテムがありません');

define('_AM_XOONIPS_MAINTENANCE_TRANSFER_ADMIN_ITEM_LIST_PAGE_TITLE', 'アイテムの移譲の確認');
define('_AM_XOONIPS_MAINTENANCE_TRANSFER_ADMIN_ITEM_LIST_MESSAGE', '下記のアイテムを移譲します。本当に実行しますか？');
define('_AM_XOONIPS_MAINTENANCE_TRANSFER_ADMIN_ITEM_LIST_SUBSCRIBE_USER_TO_GROUP', '移譲先ユーザ%2$sをグループ%1$sにメンバー登録します');
define('_AM_XOONIPS_MAINTENANCE_TRANSFER_ADMIN_ITEM_LIST_LIMIT_CHECK_OUT_OF_BOUNDS', '移譲先ユーザのアイテム数またはファイル容量制限を超えるため移譲できません');
define('_AM_XOONIPS_MAINTENANCE_TRANSFER_ADMIN_ITEM_LIST_FROM', 'From');
define('_AM_XOONIPS_MAINTENANCE_TRANSFER_ADMIN_ITEM_LIST_USER_NAME', 'ユーザ名：');
define('_AM_XOONIPS_MAINTENANCE_TRANSFER_ADMIN_ITEM_LIST_INDEX_NAME', 'インデックス名：');
define('_AM_XOONIPS_MAINTENANCE_TRANSFER_ADMIN_ITEM_LIST_ITEM_ID', 'ID');
define('_AM_XOONIPS_MAINTENANCE_TRANSFER_ADMIN_ITEM_LIST_ITEM_TYPE', 'アイテムタイプ');
define('_AM_XOONIPS_MAINTENANCE_TRANSFER_ADMIN_ITEM_LIST_TITLE', 'タイトル ');
define('_AM_XOONIPS_MAINTENANCE_TRANSFER_ADMIN_ITEM_LIST_TO', 'To');
define('_AM_XOONIPS_MAINTENANCE_TRANSFER_ADMIN_ITEM_LIST_CANCEL', '中止');
define('_AM_XOONIPS_MAINTENANCE_TRANSFER_ADMIN_ITEM_LIST_SUBMIT', '実行');
define('_AM_XOONIPS_MAINTENANCE_TRANSFER_ADMIN_ITEM_LIST_CAN_NOT_TRANSFER', 'アイテム(%d)は移譲できません');
define('_AM_XOONIPS_MAINTENANCE_TRANSFER_ADMIN_ITEM_LIST_CAN_NOT_TRANSFER_REQUEST_CERTIFY_ITEM', 'アイテム(%d)は公開要求中のため移譲できません');
define('_AM_XOONIPS_MAINTENANCE_TRANSFER_ADMIN_ITEM_LIST_CAN_NOT_TRANSFER_REQUEST_TRANSFER_ITEM', 'アイテム(%d)は移譲要求中のため移譲できません');
define('_AM_XOONIPS_MAINTENANCE_TRANSFER_ADMIN_ITEM_LIST_CAN_NOT_TRANSFER_HAVE_ANOTHER_PARENT_ITEM', 'アイテム(%d)は他のアイテムからも参照されているため移譲できません');
define('_AM_XOONIPS_MAINTENANCE_TRANSFER_ADMIN_ITEM_LIST_CAN_NOT_TRANSFER_CHILD_REQUEST_CERTIFY_ITEM', '参照する子アイテムが公開要求中のため、アイテム(%d)を移譲できません');
define('_AM_XOONIPS_MAINTENANCE_TRANSFER_ADMIN_ITEM_LIST_CAN_NOT_TRANSFER_CHILD_REQUEST_TRANSFER_ITEM', '参照する子アイテムが移譲要求中のため、アイテム(%d)を移譲できません');
define('_AM_XOONIPS_MAINTENANCE_TRANSFER_ADMIN_ITEM_LIST_NO_ITEMS_SELECTED', '移譲するアイテムが選択されていません');

define('_AM_XOONIPS_MAINTENANCE_TRANSFER_ITEM_ERROR', 'アイテムの移譲に失敗しました');
define('_AM_XOONIPS_MAINTENANCE_TRANSFER_ITEM_ERROR_ITEM_NUMBER_EXCEEDS', '移譲先ユーザのアイテム数が設定された最大値を超えるため移譲できません');
define('_AM_XOONIPS_MAINTENANCE_TRANSFER_ITEM_ERROR_ITEM_STORAGE_EXCEEDS', '移譲先ユーザのファイル容量が設定された最大値を超えるため移譲できません');
define('_AM_XOONIPS_MAINTENANCE_TRANSFER_ITEM_ERROR_BAD_SUBSCRIBE_GROUP', '移譲先ユーザがグループに登録されていません。');
define('_AM_XOONIPS_MAINTENANCE_TRANSFER_ITEM_ERROR_ONLY_1_USER', 'ユーザが1人しかいないので移譲できません');
define('_AM_XOONIPS_MAINTENANCE_TRANSFER_ITEM_ERROR_NO_FROM_UID_SELECTED', '移譲元ユーザが選択されていません');
define('_AM_XOONIPS_MAINTENANCE_TRANSFER_ITEM_ERROR_NO_TO_UID_SELECTED', '移譲先ユーザが選択されていません');
define('_AM_XOONIPS_MAINTENANCE_TRANSFER_ITEM_ERROR_NO_FROM_INDEX_ID_SELECTED', '移譲元インデックスが選択されていません');
define('_AM_XOONIPS_MAINTENANCE_TRANSFER_ITEM_ERROR_NO_TO_INDEX_ID_SELECTED', '移譲先インデックスが選択されていません');
define('_AM_XOONIPS_MAINTENANCE_TRANSFER_ITEM_COMPLETE', 'アイテムの移譲を完了しました');
