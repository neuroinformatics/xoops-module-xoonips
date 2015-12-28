<?php
// $Revision: 1.1.4.1.2.37 $
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
require_once XOOPS_ROOT_PATH.'/class/database/sqlutility.php';
require_once XOOPS_ROOT_PATH.'/modules/xoonips/condefs.php';
require_once XOOPS_ROOT_PATH.'/modules/xoonips/include/functions.php';

/**
 * insert default configs
 *
 * @param int $ver affected xoonips version
 */
function xoonips_insert_default_configs( $ver ) {
  $configs = array(
    330 => array(
      // --------------------------------------------
      // System Configurations
      // --------------------------------------------
      //  - basic
      'moderator_gid' => '',
      'upload_dir' => '',
      'magic_file_path' => '/usr/share/misc/magic',
      // - inline frame
      'tree_frame_width' => '100%',
      'tree_frame_height' => '400',
      // - printer
      'printer_friendly_header' => '',
      // - rss
      'rss_item_max' => '10',
      // - oai-pmh
      'repository_name' => '',
      'repository_nijc_code' => '',
      'repository_deletion_track' => '30',
      'repository_institution' => 'meta_author',
      'repository_publisher' => 'meta_author',
      // - proxy
      'proxy_host' => '',
      'proxy_port' => '80',
      'proxy_user' => '',
      'proxy_pass' => '',
      // --------------------------------------------
      // Site Policies
      // --------------------------------------------
      // - user informations
      'certify_user' => 'on',
      'account_realname_optional' => 'off',
      'account_company_name_optional' => 'off',
      'account_division_optional' => 'off',
      'account_country_optional' => 'off',
      'account_address_optional' => 'off',
      'account_zipcode_optional' => 'off',
      'account_tel_optional' => 'off',
      'account_fax_optional' => 'off',
      'private_item_number_limit' => '100',
      'private_index_number_limit' => '50',
      'private_item_storage_limit' => '50000000',
      // - group informations
      'group_item_number_limit' => '300',
      'group_index_number_limit' => '200',
      'group_item_storage_limit' => '100000000',
      // - publications
      'certify_item' => 'on',
      'public_item_target_user' => 'all',
      'download_file_compression' => 'on',
      'item_show_optional' => 'off',
      // - import / export
      'export_attachment' => 'off',
      'export_enabled' => 'off',
      'private_import_enabled' => 'off',
      // - moderator privileges
      'moderator_modify_any_items' => 'off',
      // - access rankings
      'ranking_num_rows' => '5',
      'ranking_order' => '0,1,2,3,4',
      'ranking_visible' => '1,1,1,1,1',
      'ranking_new_num_rows' => '5',
      'ranking_new_order' => '0,1',
      'ranking_new_visible' => '1,1',
      'ranking_days' => '14',
      'ranking_days_enabled' => '',
      'ranking_lock_timeout' => '0',
      'ranking_last_update' => '0',
      'ranking_sum_start' => '0',
      'ranking_sum_last_update' => '0',
    ),
    340 => array(
      'item_comment_dirname' => '',
      'item_comment_forum_id' => '0',
    ),
  );
  if ( ! isset( $configs[$ver] ) ) {
    return;
  }
  $xconfig_handler =& xoonips_getormhandler( 'xoonips', 'config' );
  foreach ( $configs[$ver] as $key => $val ) {
    $xconfig_obj =& $xconfig_handler->getConfig( $key );
    // insert default config if not found
    if ( ! is_object( $xconfig_obj ) ) {
      $xconfig_obj =& $xconfig_handler->create();
      $xconfig_obj->setVar( 'name', $key, true );
      $xconfig_obj->setVar( 'value', $val, true );
      $xconfig_handler->insert( $xconfig_obj );
    }
  }
}

/**
 * remove obsolete configs
 *
 * @param int $ver affected xoonips version
 */
function xoonips_delete_obsolete_configs( $ver ) {
  $configs = array(
    330 => array(
      'amazon_associates_id',
    ),
    340 => array(
      'rss_file_path',
    ),
  );
  if ( ! isset( $configs[$ver] ) ) {
    return;
  }
  $xconfig_handler =& xoonips_getormhandler( 'xoonips', 'config' );
  foreach ( $configs[$ver] as $key ) {
    $xconfig_obj =& $xconfig_handler->getConfig( $key );
    if ( is_object( $xconfig_obj ) ) {
      $xconfig_handler->delete( $xconfig_obj );
    }
  }
}

function xoonips_remove_zombie_related_to_ids() {
  // get broken related_to ids
  $rto_handler =& xoonips_getormhandler('xoonips', 'related_to');
  $criteria = new Criteria('ISNULL(`ib`.`item_id`)', 1);
  $join = new XooNIpsJoinCriteria('xoonips_item_basic', 'item_id', 'item_id', 'LEFT', 'ib');
  $res =& $rto_handler->open($criteria, 'related_to_id', false, $join);
  $rto_ids = array();
  while ($obj =& $rto_handler->getNext($res)) {
    $rto_ids[] = $obj->get('related_to_id');
  }
  $rto_handler->close($res);
  // remove broken related_to entries
  if (!empty($rto_ids)) {
    $criteria = new Criteria('related_to_id', '('.implode(',',$rto_ids).')', 'IN');
    $rto_handler->deleteAll($criteria);
  }
}

function xoonips_remove_duplicated_private_item_ids() {
  $ixil_handler =& xoonips_getormhandler('xoonips', 'index_item_link');
  $join = new XooNIpsJoinCriteria('xoonips_index', 'index_id', 'index_id', 'INNER', 'idx');
  $join->cascade(new XooNIpsJoinCriteria('xoonips_item_basic', 'item_id', 'item_id', 'INNER', 'ib'));
  $criteria = new CriteriaCompo();
  $criteria->add(new Criteria('open_level', OL_PRIVATE, '=', 'idx'));
  $criteria->add(new Criteria('ib.uid', '(idx.uid)', 'NOT IN'));
  $ixil_objs =& $ixil_handler->getObjects($criteria, false, '', false, $join);
  $ixil_ids = array();
  foreach ($ixil_objs as $ixil_obj)
    $ixil_ids[] = $ixil_obj->get('index_item_link_id');
  if (!empty($ixil_ids)) {
    $criteria = new Criteria('index_item_link_id', '('.implode(',',$ixil_ids).')', 'IN');
    $ixil_handler->deleteAll($criteria);
  }
}

function xoops_module_update_xoonips( $xoopsMod, $oldversion ) {
  $mydirname = basename( __DIR__ );

  $uid = $GLOBALS['xoopsUser']->getVar( 'uid', 'n' );
  $mid = $xoopsMod->getVar( 'mid', 'n' );

  global $xoopsDB;

  if ( $oldversion < 324 ) {
    echo '<code>The update does not supported before ver 3.24.</code><br />';
    return false;
  }

  echo '<code>Updating modules...</code><br />';
  switch ( $oldversion ) {
  case 324:
    xoonips_insert_default_configs( 330 );
    $sqls = <<<SQL
-- xoonips_changelog
ALTER TABLE `xoonips_changelog`
  ENGINE=INNODB;
-- xoonips_file
ALTER TABLE `xoonips_file`
  MODIFY `file_size` int(10) unsigned NOT NULL default '0',
  MODIFY `file_type_id` int(10) unsigned NOT NULL default '0',
  ADD `is_deleted` tinyint(1) unsigned NOT NULL default '0',
  ADD `download_count` int(10) unsigned NOT NULL default '0';
-- xoonips_file_type
ALTER TABLE `xoonips_file_type`
  MODIFY `name` varchar(30) NOT NULL default '';
-- xoonips_item_basic
ALTER TABLE `xoonips_item_basic`
  DROP `title`,
  DROP `keywords`;
-- xoonips_item_keyword
ALTER TABLE `xoonips_item_keyword`
  DROP PRIMARY KEY,
  ADD `seq_id` int(10) unsigned NOT NULL auto_increment PRIMARY KEY FIRST,
  MODIFY `keyword` varchar(255) NOT NULL default '',
  ADD UNIQUE (`item_id`,`keyword_id`);
-- xoonips_item_title
ALTER TABLE `xoonips_item_title`
  DROP PRIMARY KEY,
  ADD `seq_id` int(10) unsigned NOT NULL auto_increment PRIMARY KEY FIRST,
  MODIFY `title` varchar(255) NOT NULL default '',
  ADD UNIQUE (`item_id`,`title_id`);
-- xoonips_position
ALTER TABLE `xoonips_positions`
  ADD UNIQUE (`posi_title`);
-- xoonips_ranking_active_group
ALTER TABLE `xoonips_ranking_active_group`
  MODIFY `gid` int(10) unsigned NOT NULL default '0',
  MODIFY `count` int(10) unsigned NOT NULL default '0';
-- xoonips_ranking_contributing_user
ALTER TABLE `xoonips_ranking_contributing_user`
  MODIFY `item_id` int(10) unsigned NOT NULL default '0',
  MODIFY `uid` int(10) unsigned NOT NULL default '0';
-- xoonips_ranking_downloaded_item
ALTER TABLE `xoonips_ranking_downloaded_item`
  MODIFY `item_id` int(10) unsigned NOT NULL default '0',
  MODIFY `count` int(10) unsigned NOT NULL default '0';
-- xoonips_ranking_new_group
ALTER TABLE `xoonips_ranking_new_group`
  MODIFY `gid` int(10) unsigned NOT NULL default '0';
-- xoonips_ranking_new_item
ALTER TABLE `xoonips_ranking_new_item`
  MODIFY `item_id` int(10) unsigned NOT NULL default '0';
-- xoonips_ranking_searched_keyword
ALTER TABLE `xoonips_ranking_searched_keyword`
  MODIFY `keyword` varchar(255) binary NOT NULL default '',
  MODIFY `count` int(10) unsigned NOT NULL default '0';
-- xoonips_ranking_sum_active_group
ALTER TABLE `xoonips_ranking_sum_active_group`
  MODIFY `gid` int(10) unsigned NOT NULL default '0',
  MODIFY `count` int(10) unsigned NOT NULL default '0';
-- xoonips_ranking_sum_contributing_user
ALTER TABLE `xoonips_ranking_sum_contributing_user`
  MODIFY `item_id` int(10) unsigned NOT NULL default '0',
  MODIFY `uid` int(10) unsigned NOT NULL default '0';
-- xoonips_ranking_sum_downloaded_item
ALTER TABLE `xoonips_ranking_sum_downloaded_item`
  MODIFY `item_id` int(10) unsigned NOT NULL default '0',
  MODIFY `count` int(10) unsigned NOT NULL default '0';
-- xoonips_ranking_sum_new_group
ALTER TABLE `xoonips_ranking_sum_new_group`
  MODIFY `gid` int(10) unsigned NOT NULL default '0';
-- xoonips_ranking_sum_new_item
ALTER TABLE `xoonips_ranking_sum_new_item`
  MODIFY `item_id` int(10) unsigned NOT NULL default '0';
-- xoonips_ranking_sum_searched_keyword
ALTER TABLE `xoonips_ranking_sum_searched_keyword`
  MODIFY `keyword` varchar(255) binary NOT NULL default '',
  MODIFY `count` int(10) unsigned NOT NULL default '0';
-- xoonips_ranking_sum_viewed_item
ALTER TABLE `xoonips_ranking_sum_viewed_item`
  MODIFY `item_id` int(10) unsigned NOT NULL default '0',
  MODIFY `count` int(10) unsigned NOT NULL default '0';
-- xoonips_ranking_viewed_item
ALTER TABLE `xoonips_ranking_viewed_item`
  MODIFY `item_id` int(10) unsigned NOT NULL default '0',
  MODIFY `count` int(10) unsigned NOT NULL default '0';
-- xoonips_related_to
ALTER TABLE `xoonips_related_to`
  DROP PRIMARY KEY,
  ADD `related_to_id` int(10) unsigned NOT NULL auto_increment PRIMARY KEY FIRST,
  MODIFY `parent_id` int(10) unsigned NOT NULL default '0',
  MODIFY `item_id` int(10) unsigned NOT NULL default '0',
  ADD UNIQUE `related_to` (`parent_id`,`item_id`);
-- xoonips_search_cache
ALTER TABLE `xoonips_search_cache`
  MODIFY `sess_id` varchar(32) NOT NULL default '';
-- xoonips_search_cache_file
ALTER TABLE `xoonips_search_cache_file`
  MODIFY `search_cache_id` int(10) unsigned NOT NULL default '0';
-- xoonips_search_cache_item
ALTER TABLE `xoonips_search_cache_item`
  DROP PRIMARY KEY,
  ADD `search_cache_item_id` int(10) NOT NULL auto_increment PRIMARY KEY FIRST,
  MODIFY `search_cache_id` int(10) unsigned NOT NULL default '0',
  ADD `matchfor_index` tinyint(1) NOT NULL default '0',
  ADD `matchfor_item` tinyint(1) NOT NULL default '0',
  ADD `matchfor_file` tinyint(1) NOT NULL default '0',
  ADD UNIQUE `search_cache_id` (`search_cache_id`,`item_id`);
ALTER TABLE `xoonips_search_cache_metadata`
  MODIFY `search_cache_id` int(10) unsigned NOT NULL default '0',
  MODIFY `identifier` varchar(255) NOT NULL default '';
ALTER TABLE `xoonips_search_text`
  MODIFY `file_id` int(10) unsigned NOT NULL default '0';
-- xoonips_session
ALTER TABLE `xoonips_session`
  MODIFY uid int(10) unsigned NOT NULL default '0';
SQL;
    // queries
    if ( ! xoonips_sql_queries( $sqls ) ) {
      return false;
    }

    // fixed unlinked item bug.
    $sql = sprintf( 'DELETE FROM `%s` WHERE `item_id`=0', $xoopsDB->prefix( 'xoonips_related_to' ) );
    $xoopsDB->query( $sql );
    $sql = sprintf( 'DELETE FROM `%s` WHERE `item_id`=0', $xoopsDB->prefix( 'xoonips_index_item_link' ) );
    $xoopsDB->query( $sql );

    xoonips_delete_obsolete_configs( 330 );
  case 330:
  case 331:
    // fixed default xoonips group bug.
    // if xoonips was installed before 3.24, then the administrator have
    // joined to group id 0. this group id have to be GID_DEFAULT(1).
    $sql = sprintf( 'UPDATE `%s` SET `gid`=1 WHERE `gid`=0', $xoopsDB->prefix( 'xoonips_groups_users_link' ) );
    $xoopsDB->query( $sql );

    $sqls = <<<SQL
-- xoonips_config
ALTER TABLE `xoonips_config`
  ENGINE=INNODB;
-- xoonips_ranking_active_group
ALTER TABLE `xoonips_ranking_active_group`
  ENGINE=INNODB;
-- xoonips_ranking_contributing_user
ALTER TABLE `xoonips_ranking_contributing_user`
  ENGINE=INNODB;
-- xoonips_ranking_downloaded_item
ALTER TABLE `xoonips_ranking_downloaded_item`
  ENGINE=INNODB;
-- xoonips_ranking_new_group
ALTER TABLE `xoonips_ranking_new_group`
  ENGINE=INNODB;
-- xoonips_ranking_new_item
ALTER TABLE `xoonips_ranking_new_item`
  ENGINE=INNODB;
-- xoonips_ranking_searched_keyword
ALTER TABLE `xoonips_ranking_searched_keyword`
  ENGINE=INNODB;
-- xoonips_ranking_sum_active_group
ALTER TABLE `xoonips_ranking_sum_active_group`
  ENGINE=INNODB;
-- xoonips_ranking_sum_contributing_user
ALTER TABLE `xoonips_ranking_sum_contributing_user`
  ENGINE=INNODB;
-- xoonips_ranking_sum_downloaded_item
ALTER TABLE `xoonips_ranking_sum_downloaded_item`
  ENGINE=INNODB;
-- xoonips_ranking_sum_new_group
ALTER TABLE `xoonips_ranking_sum_new_group`
  ENGINE=INNODB;
-- xoonips_ranking_sum_new_item
ALTER TABLE `xoonips_ranking_sum_new_item`
  ENGINE=INNODB;
-- xoonips_ranking_sum_searched_keyword
ALTER TABLE `xoonips_ranking_sum_searched_keyword`
  ENGINE=INNODB;
-- xoonips_ranking_sum_viewed_item
ALTER TABLE `xoonips_ranking_sum_viewed_item`
  ENGINE=INNODB;
ALTER TABLE `xoonips_ranking_viewed_item`
  ENGINE=INNODB;
SQL;
    // queries
    if ( ! xoonips_sql_queries( $sqls ) ) {
      return false;
    }
  case 332:
    // Notice:
    //   version 333-339 are reserved number for future releases of
    //   RELENG_3_3 branch. don't change database structure after
    //   3.40 released.
  case 333:
  case 334:
  case 335:
  case 336:
  case 337:
  case 338:
  case 339:
    xoonips_insert_default_configs( 340 );
    // delete all harvested metadata, because table design has been changed.
    $sql = sprintf( 'DELETE FROM `%s`', $xoopsDB->prefix( 'xoonips_oaipmh_metadata' ) );
    $xoopsDB->query( $sql );
    $sqls = <<<SQL
-- xoonips_search_cache_metadata
ALTER TABLE xoonips_search_cache_metadata
    DROP PRIMARY KEY,
    ADD `search_cache_metadata_id` int(10) NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST,
    ADD UNIQUE ( `search_cache_id`, `identifier` );
-- xoonips_config
ALTER TABLE xoonips_config
    MODIFY `value` text NOT NULL;
-- xoonips_session
ALTER TABLE xoonips_session
    ADD `sess_data` longtext NOT NULL;
-- xoonips_groups_users_link
ALTER TABLE xoonips_groups_users_link
    DROP PRIMARY KEY,
    ADD groups_users_link_id int(10) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST,
    ADD UNIQUE ( gid, uid );
-- xoonips_item_show
ALTER TABLE xoonips_item_show
    DROP PRIMARY KEY,
    ADD item_show_id int(10) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST,
    ADD UNIQUE ( item_id, uid );
-- xoonips_item_lock
CREATE TABLE xoonips_item_lock (
    item_id int(10) unsigned NOT NULL,
    lock_count int(10) unsigned NOT NULL default '0',
    PRIMARY KEY  (`item_id`)
    ) TYPE=InnoDB;
-- xoonips_transfer_request
CREATE TABLE xoonips_transfer_request (
    item_id int(10) unsigned NOT NULL,
    to_uid int(10) unsigned NOT NULL,
    PRIMARY KEY  (`item_id`)
    ) TYPE=InnoDB;
-- xoonips_item_show
ALTER TABLE xoonips_item_show
    TYPE=innodb;
-- xoonips_groups_users_link
ALTER TABLE xoonips_groups_users_link
    TYPE=innodb;
-- xoonips_oaipmh_repositories
ALTER TABLE xoonips_oaipmh_repositories
    ADD repository_name text,
    ADD metadata_count int(10) unsigned NOT NULL default '0';
UPDATE xoonips_oaipmh_repositories
    SET last_access_date=NULL,
    last_success_date=NULL,
    last_access_result=NULL;
-- xoonips_oaipmh_metadata
ALTER TABLE xoonips_oaipmh_metadata
    DROP PRIMARY KEY,
    DROP metadata,
    ADD metadata_id int(10) NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST,
    ADD last_update_date varchar(255) NOT NULL default '',
    ADD creation_date varchar(255) NOT NULL default '',
    ADD `date` varchar(255) NOT NULL default '',
    ADD creator varchar(255) NOT NULL default '',
    ADD link text,
    ADD last_update_date_for_sort datetime NOT NULL default '1970-01-01 00:00:00',
    ADD creation_date_for_sort datetime NOT NULL default '1970-01-01 00:00:00',
    ADD date_for_sort datetime NOT NULL default '1970-01-01 00:00:00',
    ADD UNIQUE (identifier);
-- xoonips_oaipmh_metadata_field
CREATE TABLE `xoonips_oaipmh_metadata_field` (
  `metadata_field_id` int(10) unsigned NOT NULL auto_increment,
  `metadata_id` int(10) unsigned NOT NULL,
  `format` varchar(100) NOT NULL,
  `name` varchar(255) NOT NULL,
  `ordernum` int(10) unsigned NOT NULL,
  `category_name` varchar(255) NOT NULL default '',
  `value` text,
  `namespace` varchar(255) NOT NULL default '',
  `namespace_uri` text,
  PRIMARY KEY  (`metadata_field_id`),
  KEY `metadata_id` (`metadata_id`)
) TYPE=MyISAM;
-- xoonips_index
ALTER TABLE `xoonips_index`
  MODIFY `index_id` int(10) unsigned NOT NULL;
-- xoonips_index_group_index_link
CREATE TABLE `xoonips_index_group_index_link` (
  `index_group_index_link_id` int(10) unsigned NOT NULL auto_increment,
  `index_id` int(10) unsigned NOT NULL default '0',
  `group_index_id` int(10) unsigned NOT NULL default '0',
  `gid` int(10) NOT NULL default '0',
  `uid` int(10) NOT NULL default '0',
  PRIMARY KEY  (`index_group_index_link_id`),
  UNIQUE KEY `index_id_2` (`index_id`,`group_index_id`),
  KEY `index_id` (`index_id`),
  KEY `item_id` (`group_index_id`)
) TYPE=InnoDB;
SQL;
    // queries
    if ( ! xoonips_sql_queries( $sqls ) ) {
      return false;
    }

    // fixed old update script bugs.
    // from too old to 324
    if ( xoonips_sql_has_index( 'xoonips_file', 'sid' ) ) {
      $sql = sprintf( 'ALTER TABLE `%s` DROP INDEX `sid`, ADD INDEX (`sess_id`)', $xoopsDB->prefix( 'xoonips_file' ) );
      $xoopsDB->query( $sql );
    }
    if ( xoonips_sql_has_index( 'xoonips_search_cache', 'sess_id' ) ) {
      $sql = sprintf( 'ALTER TABLE `%s` DROP INDEX `sess_id`', $xoopsDB->prefix( 'xoonips_search_cache' ) );
      $xoopsDB->query( $sql );
    }
    if ( xoonips_sql_has_index( 'xoonips_search_text', 'search' ) ) {
      $sql = sprintf( 'ALTER TABLE `%s` DROP INDEX `search`, ADD FULLTEXT `search_text`', $xoopsDB->prefix( 'xoonips_search_text' ) );
      $xoopsDB->query( $sql );
    }
    // from 324 to 330
    $sqls = <<<SQL
-- xoonips_changelog
ALTER TABLE `xoonips_changelog`
  ENGINE=INNODB;
-- xoonips_ranking_searched_keyword
ALTER TABLE `xoonips_ranking_searched_keyword`
  MODIFY `keyword` varchar(255) binary NOT NULL default '';
-- xoonips_ranking_sum_searched_keyword
ALTER TABLE `xoonips_ranking_sum_searched_keyword`
  MODIFY `keyword` varchar(255) binary NOT NULL default '';
SQL;
    // queries
    if ( ! xoonips_sql_queries( $sqls ) ) {
      return false;
    }

    // remove obsolete configs
    xoonips_delete_obsolete_configs( 340 );
  case 340:
  case 341:
  case 342:
  case 343:
  case 344:
  case 345:
    // remove zombie related_to entries
    xoonips_remove_zombie_related_to_ids();
    // remove duplicated private item ids
    xoonips_remove_duplicated_private_item_ids();
  default:
    break;
  }

  // enable available notifications
  $admin_xoops_handler =& xoonips_gethandler( 'xoonips', 'admin_xoops' );
  $member_handler =& xoops_gethandler( 'member' );
  $uids = array_keys( $member_handler->getUsers( null, true ) );
  // php-indent: disable
  $notifications = array(
    'administrator' => array(
      'subscribe' => array(
        'item_transfer', 'account_certify', 'item_certify',
        'group_item_certify_request',
      ),
      'unsubscribe' => array(
        'binder_content_empty', 'item_certify_request',
      ),
    ),
    'user' => array(
      'subscribe' => array(
        'item_transfer', 'item_updated', 'item_certified', 'item_rejected',
        'file_downloaded', 'group_item_certified', 'group_item_rejected',
      ),
      'unsubscribe' => array(
        'index_renamed', 'index_moved', 'index_deleted',
      ),
    ),
  );
  // php-indent: enable
  foreach ( $notifications as $category => $events ) {
    // enable module event
    foreach ( $events['subscribe'] as $event ) {
      $admin_xoops_handler->enableNotification( $mid, $category, $event );
    }
  }
  // subscribe all notifications to all users
  foreach ( $uids as $uid ) {
    foreach ( $notifications as $category => $events ) {
      foreach ( $events['subscribe'] as $event ) {
        $admin_xoops_handler->subscribeNotification( $mid, $uid, $category, $event );
      }
    }
  }
  // unsubscribe obsolete notifications from all users
  foreach ( $notifications as $category => $events ) {
    foreach ( $events['unsubscribe'] as $event ) {
      $admin_xoops_handler->unsubscribeNotification( $mid, 0, $category, $event );
    }
  }
  return true;
}

/**
 * @split $sqls to individual queries, add prefix to table, and query sqls
 * output some informations to stdout.
 *
 * @param string $sqls string of sqls.
 * @return boolean true if succeed
 *
 */
function xoonips_sql_queries( $sqls ) {
  global $xoopsDB;
  $textutil =& xoonips_getutility( 'text' );
  $pieces = array();
  SqlUtility::splitMySqlFile( $pieces, $sqls );
  $created_tables = array();
  $errs = array();
  $msgs = array();
  $error = false;
  $ret = '';
  foreach ( $pieces as $piece ) {
    // [0] contains the prefixed query
    // [4] contains unprefixed table name
    $prefixed_query = SqlUtility::prefixQuery( $piece, $xoopsDB->prefix() );
    if ( ! $prefixed_query ) {
      $errs[] = '<b>'.$piece.'</b> is not a valid SQL!';
      $error = true;
      break;
    }
    if ( ! $xoopsDB->query( $prefixed_query[0] ) ) {
      $errs[] = $xoopsDB->error().' of SQL '.$textutil->html_special_chars( $prefixed_query[0] );
      $error = true;
      break;
    }
    if ( strncmp( 'CREATE', strtoupper( $prefixed_query[0] ), 6 ) == 0 && ! in_array( $prefixed_query[4], $created_tables ) ) {
      $msgs[] = '&nbsp;&nbsp;Table <b>'.$xoopsDB->prefix( $prefixed_query[4] ).'</b> created.';
      $created_tables[] = $prefixed_query[4];
    }
  }
  if ( $error ) {
    // if there was an error, delete the tables created so far,
    // so the next installation will not fail
    foreach ( $created_tables as $ct ) {
      $xoopsDB->query( 'DROP TABLE '.$xoopsDB->prefix( $ct ) );
    }
    // set error messages
    foreach ( $errs as $er ) {
      $ret .= '&nbsp;&nbsp;'.$er.'<br />';
    }
    unset( $msgs );
    unset( $errs );
  }
  echo $ret;
  return ! $error;
}

function xoonips_sql_has_index( $table, $name ) {
  global $xoopsDB;
  $sql = sprintf( 'SHOW INDEX FROM `%s` WHERE `Key_name`=%s', $xoopsDB->prefix( $table ), $xoopsDB->quoteString( $name ) );
  $result = $xoopsDB->query( $sql );
  if ( ! $result ) {
    return false;
  }
  $num = $xoopsDB->getRowsNum( $result );
  $xoopsDB->freeRecordSet( $result );
  return( $num != 0 );
}

function xoonips_sql_fetch_column( $table, $name ) {
  global $xoopsDB;
  $sql = sprintf( 'SHOW COLUMNS FROM `%s` WHERE `Field`=%s', $xoopsDB->prefix( $table ), $xoopsDB->quoteString( $name ) );
  $result = $xoopsDB->query( $sql );
  if ( ! $result ) {
    return false;
  }
  $ret = $xoopsDB->fetchArray( $result );
  $xoopsDB->freeRecordSet( $result );
  return $ret;
}

?>
