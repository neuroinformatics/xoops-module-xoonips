# phpMyAdmin SQL Dump
# version 2.11.7
# http://www.phpmyadmin.net

# --------------------------------------------------------

#
# Table structure for table `xoonips_changelog`
#

CREATE TABLE `xoonips_changelog` (
  `log_id` int(10) unsigned NOT NULL auto_increment,
  `uid` int(10) unsigned NOT NULL default '0',
  `item_id` int(10) unsigned NOT NULL default '0',
  `log_date` int(10) unsigned NOT NULL default '0',
  `log` text,
  PRIMARY KEY  (`log_id`),
  KEY `item_id` (`item_id`),
  KEY `log_date` (`log_date`)
) ENGINE=InnoDB;

# --------------------------------------------------------

#
# Table structure for table `xoonips_config`
#

CREATE TABLE `xoonips_config` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) binary NOT NULL default '',
  `value` text NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `name` (`name`)
) ENGINE=InnoDB;

#
# Dumping data for table `xoonips_config`
#

INSERT INTO `xoonips_config` (`id`, `name`, `value`) VALUES(1, 'moderator_gid', '1');
INSERT INTO `xoonips_config` (`id`, `name`, `value`) VALUES(2, 'upload_dir', '/var/tmp');
INSERT INTO `xoonips_config` (`id`, `name`, `value`) VALUES(3, 'magic_file_path', '/usr/share/misc/magic');
INSERT INTO `xoonips_config` (`id`, `name`, `value`) VALUES(4, 'tree_frame_width', '100%');
INSERT INTO `xoonips_config` (`id`, `name`, `value`) VALUES(5, 'tree_frame_height', '400');
INSERT INTO `xoonips_config` (`id`, `name`, `value`) VALUES(6, 'printer_friendly_header', '');
INSERT INTO `xoonips_config` (`id`, `name`, `value`) VALUES(7, 'rss_item_max', '10');
INSERT INTO `xoonips_config` (`id`, `name`, `value`) VALUES(8, 'repository_name', '');
INSERT INTO `xoonips_config` (`id`, `name`, `value`) VALUES(9, 'repository_nijc_code', '');
INSERT INTO `xoonips_config` (`id`, `name`, `value`) VALUES(10, 'repository_deletion_track', '30');
INSERT INTO `xoonips_config` (`id`, `name`, `value`) VALUES(11, 'repository_institution', 'meta_author');
INSERT INTO `xoonips_config` (`id`, `name`, `value`) VALUES(12, 'repository_publisher', 'meta_author');
INSERT INTO `xoonips_config` (`id`, `name`, `value`) VALUES(13, 'proxy_host', '');
INSERT INTO `xoonips_config` (`id`, `name`, `value`) VALUES(14, 'proxy_port', '80');
INSERT INTO `xoonips_config` (`id`, `name`, `value`) VALUES(15, 'proxy_user', '');
INSERT INTO `xoonips_config` (`id`, `name`, `value`) VALUES(16, 'proxy_pass', '');
INSERT INTO `xoonips_config` (`id`, `name`, `value`) VALUES(17, 'certify_user', 'on');
INSERT INTO `xoonips_config` (`id`, `name`, `value`) VALUES(18, 'account_realname_optional', 'on');
INSERT INTO `xoonips_config` (`id`, `name`, `value`) VALUES(19, 'account_company_name_optional', 'on');
INSERT INTO `xoonips_config` (`id`, `name`, `value`) VALUES(20, 'account_division_optional', 'on');
INSERT INTO `xoonips_config` (`id`, `name`, `value`) VALUES(21, 'account_country_optional', 'on');
INSERT INTO `xoonips_config` (`id`, `name`, `value`) VALUES(22, 'account_address_optional', 'on');
INSERT INTO `xoonips_config` (`id`, `name`, `value`) VALUES(23, 'account_zipcode_optional', 'on');
INSERT INTO `xoonips_config` (`id`, `name`, `value`) VALUES(24, 'account_tel_optional', 'on');
INSERT INTO `xoonips_config` (`id`, `name`, `value`) VALUES(25, 'account_fax_optional', 'on');
INSERT INTO `xoonips_config` (`id`, `name`, `value`) VALUES(26, 'private_item_number_limit', '500');
INSERT INTO `xoonips_config` (`id`, `name`, `value`) VALUES(27, 'private_index_number_limit', '200');
INSERT INTO `xoonips_config` (`id`, `name`, `value`) VALUES(28, 'private_item_storage_limit', '500000000');
INSERT INTO `xoonips_config` (`id`, `name`, `value`) VALUES(29, 'group_item_number_limit', '1000');
INSERT INTO `xoonips_config` (`id`, `name`, `value`) VALUES(30, 'group_index_number_limit', '500');
INSERT INTO `xoonips_config` (`id`, `name`, `value`) VALUES(31, 'group_item_storage_limit', '1000000000');
INSERT INTO `xoonips_config` (`id`, `name`, `value`) VALUES(32, 'certify_item', 'on');
INSERT INTO `xoonips_config` (`id`, `name`, `value`) VALUES(33, 'public_item_target_user', 'all');
INSERT INTO `xoonips_config` (`id`, `name`, `value`) VALUES(34, 'download_file_compression', 'on');
INSERT INTO `xoonips_config` (`id`, `name`, `value`) VALUES(35, 'item_show_optional', 'off');
INSERT INTO `xoonips_config` (`id`, `name`, `value`) VALUES(36, 'export_attachment', 'off');
INSERT INTO `xoonips_config` (`id`, `name`, `value`) VALUES(37, 'export_enabled', 'off');
INSERT INTO `xoonips_config` (`id`, `name`, `value`) VALUES(38, 'private_import_enabled', 'off');
INSERT INTO `xoonips_config` (`id`, `name`, `value`) VALUES(39, 'moderator_modify_any_items', 'off');
INSERT INTO `xoonips_config` (`id`, `name`, `value`) VALUES(40, 'ranking_num_rows', '5');
INSERT INTO `xoonips_config` (`id`, `name`, `value`) VALUES(41, 'ranking_order', '0,1,2,3,4');
INSERT INTO `xoonips_config` (`id`, `name`, `value`) VALUES(42, 'ranking_visible', '1,1,1,1,1');
INSERT INTO `xoonips_config` (`id`, `name`, `value`) VALUES(43, 'ranking_new_num_rows', '5');
INSERT INTO `xoonips_config` (`id`, `name`, `value`) VALUES(44, 'ranking_new_order', '0,1');
INSERT INTO `xoonips_config` (`id`, `name`, `value`) VALUES(45, 'ranking_new_visible', '1,1');
INSERT INTO `xoonips_config` (`id`, `name`, `value`) VALUES(46, 'ranking_days', '14');
INSERT INTO `xoonips_config` (`id`, `name`, `value`) VALUES(47, 'ranking_days_enabled', '');
INSERT INTO `xoonips_config` (`id`, `name`, `value`) VALUES(48, 'ranking_lock_timeout', '0');
INSERT INTO `xoonips_config` (`id`, `name`, `value`) VALUES(49, 'ranking_last_update', '0');
INSERT INTO `xoonips_config` (`id`, `name`, `value`) VALUES(50, 'ranking_sum_start', '0');
INSERT INTO `xoonips_config` (`id`, `name`, `value`) VALUES(51, 'ranking_sum_last_update', '0');
INSERT INTO `xoonips_config` (`id`, `name`, `value`) VALUES(52, 'item_comment_dirname', '');
INSERT INTO `xoonips_config` (`id`, `name`, `value`) VALUES(53, 'item_comment_forum_id', '0');

# --------------------------------------------------------

#
# Table structure for table `xoonips_cvitaes`
#

CREATE TABLE `xoonips_cvitaes` (
  `cvitae_id` int(11) NOT NULL auto_increment,
  `uid` int(10) NOT NULL default '0',
  `from_month` int(11) default NULL,
  `from_year` int(11) default NULL,
  `to_month` int(11) default NULL,
  `to_year` int(11) default NULL,
  `cvitae_title` text NOT NULL,
  `cvitae_order` smallint(3) NOT NULL default '0',
  PRIMARY KEY  (`cvitae_id`)
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table `xoonips_event_log`
#

CREATE TABLE `xoonips_event_log` (
  `event_id` int(10) unsigned NOT NULL auto_increment,
  `event_type_id` int(10) unsigned NOT NULL default '0',
  `timestamp` int(10) unsigned NOT NULL default '0',
  `exec_uid` int(10) unsigned default NULL,
  `remote_host` varchar(255) default NULL,
  `index_id` int(10) unsigned default NULL,
  `item_id` int(10) unsigned default NULL,
  `file_id` int(10) unsigned default NULL,
  `uid` int(10) unsigned default NULL,
  `gid` int(10) unsigned default NULL,
  `search_keyword` blob,
  `additional_info` blob,
  PRIMARY KEY  (`event_id`),
  KEY `timestamp` (`timestamp`)
) ENGINE=InnoDB;

# --------------------------------------------------------

#
# Table structure for table `xoonips_file`
#

CREATE TABLE `xoonips_file` (
  `file_id` int(10) unsigned NOT NULL auto_increment,
  `item_id` int(10) unsigned default NULL,
  `original_file_name` varchar(255) default NULL,
  `mime_type` varchar(255) default NULL,
  `file_name` varchar(255) default NULL,
  `file_size` int(10) unsigned NOT NULL default '0',
  `thumbnail_file` blob,
  `caption` varchar(255) default NULL,
  `sess_id` varchar(32) default NULL,
  `file_type_id` int(10) unsigned NOT NULL default '0',
  `search_module_name` varchar(255) default NULL,
  `search_module_version` float default NULL,
  `header` varchar(32) binary default NULL,
  `timestamp` timestamp NOT NULL,
  `is_deleted` tinyint(1) unsigned NOT NULL default '0',
  `download_count` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`file_id`),
  KEY `item_id` (`item_id`),
  KEY `sess_id` (`sess_id`)
) ENGINE=InnoDB;

# --------------------------------------------------------

#
# Table structure for table `xoonips_file_type`
#

CREATE TABLE `xoonips_file_type` (
  `file_type_id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(30) NOT NULL default '',
  `mid` int(10) unsigned default NULL,
  `display_name` varchar(30) default NULL,
  PRIMARY KEY  (`file_type_id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM;

#
# Dumping data for table `xoonips_file_type`
#

INSERT INTO `xoonips_file_type` (`file_type_id`, `name`, `mid`, `display_name`) VALUES(1, 'preview', NULL, 'Preview');
INSERT INTO `xoonips_file_type` (`file_type_id`, `name`, `mid`, `display_name`) VALUES(2, 'readme', NULL, 'ReadMe');
INSERT INTO `xoonips_file_type` (`file_type_id`, `name`, `mid`, `display_name`) VALUES(3, 'license', NULL, 'License');
INSERT INTO `xoonips_file_type` (`file_type_id`, `name`, `mid`, `display_name`) VALUES(4, 'rights', NULL, 'Rights');

# --------------------------------------------------------

#
# Table structure for table `xoonips_groups`
#

CREATE TABLE `xoonips_groups` (
  `gid` int(10) unsigned NOT NULL auto_increment,
  `gname` varchar(255) binary NOT NULL default '',
  `gdesc` varchar(255) binary NOT NULL default '',
  `group_index_id` int(10) unsigned NOT NULL default '0',
  `group_item_number_limit` int(10) unsigned default NULL,
  `group_index_number_limit` int(10) unsigned default NULL,
  `group_item_storage_limit` double default NULL,
  PRIMARY KEY  (`gid`)
) ENGINE=MyISAM;

#
# Dumping data for table `xoonips_groups`
#

INSERT INTO `xoonips_groups` (`gid`, `gname`, `gdesc`, `group_index_id`, `group_item_number_limit`, `group_index_number_limit`, `group_item_storage_limit`) VALUES(1, 'default', 'default group', 0, NULL, NULL, NULL);

# --------------------------------------------------------

#
# Table structure for table `xoonips_groups_users_link`
#

CREATE TABLE `xoonips_groups_users_link` (
  `groups_users_link_id` int(10) unsigned NOT NULL auto_increment,
  `gid` int(10) unsigned NOT NULL default '0',
  `uid` int(10) unsigned NOT NULL default '0',
  `is_admin` tinyint(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (`groups_users_link_id`),
  UNIQUE KEY `gid` (`gid`,`uid`)
) ENGINE=InnoDB;

# --------------------------------------------------------

#
# `xoonips_index_group_index_link`
#

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
) ENGINE=InnoDB;

# --------------------------------------------------------

#
# Table structure for table `xoonips_index`
#

CREATE TABLE `xoonips_index` (
  `index_id` int(10) unsigned NOT NULL,
  `parent_index_id` int(10) unsigned default NULL,
  `uid` int(10) unsigned default NULL,
  `gid` int(10) unsigned default NULL,
  `open_level` tinyint(3) NOT NULL default '0',
  `sort_number` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`index_id`),
  KEY `parent_index_id` (`parent_index_id`)
) ENGINE=InnoDB;

#
# Dumping data for table `xoonips_index`
#

INSERT INTO `xoonips_index` (`index_id`, `parent_index_id`, `uid`, `gid`, `open_level`, `sort_number`) VALUES(1, 0, NULL, NULL, 1, 1);
INSERT INTO `xoonips_index` (`index_id`, `parent_index_id`, `uid`, `gid`, `open_level`, `sort_number`) VALUES(3, 1, NULL, NULL, 1, 1);

# --------------------------------------------------------

#
# Table structure for table `xoonips_index_item_link`
#

CREATE TABLE `xoonips_index_item_link` (
  `index_item_link_id` int(10) unsigned NOT NULL auto_increment,
  `index_id` int(10) unsigned NOT NULL default '0',
  `item_id` int(10) unsigned NOT NULL default '0',
  `certify_state` tinyint(3) unsigned NOT NULL default '0',
  PRIMARY KEY  (`index_item_link_id`),
  UNIQUE KEY `index_id_2` (`index_id`,`item_id`),
  KEY `index_id` (`index_id`),
  KEY `item_id` (`item_id`)
) ENGINE=InnoDB;

# --------------------------------------------------------

#
# Table structure for table `xoonips_item_basic`
#

CREATE TABLE `xoonips_item_basic` (
  `item_id` int(10) unsigned NOT NULL auto_increment,
  `item_type_id` int(10) unsigned NOT NULL default '0',
  `uid` int(10) unsigned NOT NULL default '0',
  `description` text,
  `doi` blob,
  `last_update_date` int(10) unsigned NOT NULL default '0',
  `creation_date` int(10) unsigned NOT NULL default '0',
  `publication_year` int(10) default NULL,
  `publication_month` int(10) default NULL,
  `publication_mday` int(10) default NULL,
  `lang` char(3) default 'eng',
  PRIMARY KEY  (`item_id`)
) ENGINE=InnoDB;

#
# Dumping data for table `xoonips_item_basic`
#

INSERT INTO `xoonips_item_basic` (`item_id`, `item_type_id`, `uid`, `description`, `doi`, `last_update_date`, `creation_date`, `publication_year`, `publication_month`, `publication_mday`, `lang`) VALUES(1, 1, 0, NULL, NULL, 0, 0, NULL, NULL, NULL, 'eng');
INSERT INTO `xoonips_item_basic` (`item_id`, `item_type_id`, `uid`, `description`, `doi`, `last_update_date`, `creation_date`, `publication_year`, `publication_month`, `publication_mday`, `lang`) VALUES(3, 1, 0, NULL, NULL, 0, 0, NULL, NULL, NULL, 'eng');

# --------------------------------------------------------

#
# Table structure for table `xoonips_item_keyword`
#

CREATE TABLE `xoonips_item_keyword` (
  `seq_id` int(10) unsigned NOT NULL auto_increment,
  `item_id` int(10) unsigned NOT NULL default '0',
  `keyword_id` int(10) unsigned NOT NULL default '0',
  `keyword` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`seq_id`),
  UNIQUE KEY `item_id` (`item_id`,`keyword_id`),
  KEY `keyword` (`keyword`)
) ENGINE=InnoDB;

# --------------------------------------------------------

#
# Table structure for table `xoonips_item_lock`
#

CREATE TABLE `xoonips_item_lock` (
  `item_id` int(10) unsigned NOT NULL,
  `lock_count` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`item_id`)
) ENGINE=InnoDB;

# --------------------------------------------------------

#
# Table structure for table `xoonips_item_show`
#

CREATE TABLE `xoonips_item_show` (
  `item_show_id` int(10) unsigned NOT NULL auto_increment,
  `item_id` int(10) NOT NULL default '0',
  `uid` int(10) NOT NULL default '0',
  PRIMARY KEY  (`item_show_id`),
  UNIQUE KEY `item_id` (`item_id`,`uid`)
) ENGINE=InnoDB;

# --------------------------------------------------------

#
# Table structure for table `xoonips_item_status`
#

CREATE TABLE `xoonips_item_status` (
  `item_id` int(10) unsigned NOT NULL default '0',
  `timestamp` timestamp NOT NULL,
  `created_timestamp` int(10) unsigned default NULL,
  `modified_timestamp` int(10) unsigned default NULL,
  `deleted_timestamp` int(10) unsigned default NULL,
  `is_deleted` tinyint(3) default NULL,
  PRIMARY KEY  (`item_id`),
  KEY `timestamp` (`timestamp`)
) ENGINE=InnoDB;

# --------------------------------------------------------

#
# Table structure for table `xoonips_item_title`
#

CREATE TABLE `xoonips_item_title` (
  `seq_id` int(10) unsigned NOT NULL auto_increment,
  `item_id` int(10) unsigned NOT NULL default '0',
  `title_id` int(10) unsigned NOT NULL default '0',
  `title` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`seq_id`),
  UNIQUE KEY `item_id` (`item_id`,`title_id`),
  KEY `title` (`title`)
) ENGINE=InnoDB;

#
# Dumping data for table `xoonips_item_title`
#

INSERT INTO `xoonips_item_title` (`seq_id`, `item_id`, `title_id`, `title`) VALUES(1, 1, 0, 'Root');
INSERT INTO `xoonips_item_title` (`seq_id`, `item_id`, `title_id`, `title`) VALUES(3, 3, 0, 'Public');

# --------------------------------------------------------

#
# Table structure for table `xoonips_item_type`
#

CREATE TABLE `xoonips_item_type` (
  `item_type_id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(30) default NULL,
  `mid` int(10) unsigned default NULL,
  `display_name` varchar(30) default NULL,
  `viewphp` varchar(255) default NULL,
  PRIMARY KEY  (`item_type_id`)
) ENGINE=MyISAM;

#
# Dumping data for table `xoonips_item_type`
#

INSERT INTO `xoonips_item_type` (`item_type_id`, `name`, `mid`, `display_name`, `viewphp`) VALUES(1, 'xoonips_index', NULL, 'Index', NULL);

# --------------------------------------------------------

#
# Table structure for table `xoonips_oaipmh_metadata`
#

CREATE TABLE `xoonips_oaipmh_metadata` (
  `metadata_id` int(10) NOT NULL auto_increment,
  `identifier` varchar(255) NOT NULL default '',
  `repository_id` int(11) unsigned NOT NULL default '0',
  `format` varchar(255) NOT NULL default '',
  `title` text,
  `search_text` text,
  `datestamp` datetime default NULL,
  `last_update_date` varchar(255) NOT NULL default '',
  `creation_date` varchar(255) NOT NULL default '',
  `date` varchar(255) NOT NULL default '',
  `creator` varchar(255) NOT NULL default '',
  `link` text,
  `last_update_date_for_sort` datetime NOT NULL default '1970-01-01 00:00:00',
  `creation_date_for_sort` datetime NOT NULL default '1970-01-01 00:00:00',
  `date_for_sort` datetime NOT NULL default '1970-01-01 00:00:00',
  PRIMARY KEY  (`metadata_id`),
  UNIQUE KEY `identifier` (`identifier`),
  FULLTEXT KEY `search` (`search_text`)
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table `xoonips_oaipmh_metadata_field`
#

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
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table `xoonips_oaipmh_repositories`
#

CREATE TABLE `xoonips_oaipmh_repositories` (
  `repository_id` int(11) unsigned NOT NULL auto_increment,
  `URL` varchar(255) default NULL,
  `last_access_date` int(11) unsigned default NULL,
  `last_success_date` int(11) unsigned default NULL,
  `last_access_result` text,
  `sort` int(10) unsigned NOT NULL default '0',
  `enabled` int(1) unsigned NOT NULL default '1',
  `deleted` int(1) unsigned NOT NULL default '0',
  `repository_name` text,
  `metadata_count` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`repository_id`),
  UNIQUE KEY `url` (`URL`)
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table `xoonips_oaipmh_resumption_token`
#

CREATE TABLE `xoonips_oaipmh_resumption_token` (
  `resumption_token` varchar(255) NOT NULL default '',
  `metadata_prefix` varchar(255) default NULL,
  `verb` varchar(32) default NULL,
  `args` text,
  `last_item_id` int(11) default NULL,
  `limit_row` int(11) default NULL,
  `publish_date` int(11) default NULL,
  `expire_date` int(11) default NULL,
  PRIMARY KEY  (`resumption_token`)
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table `xoonips_positions`
#

CREATE TABLE `xoonips_positions` (
  `posi_id` smallint(5) NOT NULL auto_increment,
  `posi_title` varchar(50) default NULL,
  `posi_order` smallint(3) NOT NULL default '0',
  PRIMARY KEY  (`posi_id`),
  UNIQUE KEY `posi_title` (`posi_title`)
) ENGINE=MyISAM;

#
# Dumping data for table `xoonips_positions`
#

INSERT INTO `xoonips_positions` (`posi_id`, `posi_title`, `posi_order`) VALUES(1, 'Professor', 10);
INSERT INTO `xoonips_positions` (`posi_id`, `posi_title`, `posi_order`) VALUES(2, 'Associate Professor', 20);
INSERT INTO `xoonips_positions` (`posi_id`, `posi_title`, `posi_order`) VALUES(3, 'Assistant Professor', 30);
INSERT INTO `xoonips_positions` (`posi_id`, `posi_title`, `posi_order`) VALUES(4, 'Lecturer', 40);
INSERT INTO `xoonips_positions` (`posi_id`, `posi_title`, `posi_order`) VALUES(5, 'Instructor', 50);
INSERT INTO `xoonips_positions` (`posi_id`, `posi_title`, `posi_order`) VALUES(6, 'Research Associate', 60);
INSERT INTO `xoonips_positions` (`posi_id`, `posi_title`, `posi_order`) VALUES(7, 'Research Assistant', 70);
INSERT INTO `xoonips_positions` (`posi_id`, `posi_title`, `posi_order`) VALUES(8, 'Assistant', 80);
INSERT INTO `xoonips_positions` (`posi_id`, `posi_title`, `posi_order`) VALUES(9, 'Secretary', 90);
INSERT INTO `xoonips_positions` (`posi_id`, `posi_title`, `posi_order`) VALUES(10, 'Others', 100);
INSERT INTO `xoonips_positions` (`posi_id`, `posi_title`, `posi_order`) VALUES(11, 'Moderator', 500);
INSERT INTO `xoonips_positions` (`posi_id`, `posi_title`, `posi_order`) VALUES(12, 'Registered Users', 510);

# --------------------------------------------------------

#
# Table structure for table `xoonips_ranking_active_group`
#

CREATE TABLE `xoonips_ranking_active_group` (
  `gid` int(10) unsigned NOT NULL default '0',
  `count` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`gid`)
) ENGINE=InnoDB;

# --------------------------------------------------------

#
# Table structure for table `xoonips_ranking_contributing_user`
#

CREATE TABLE `xoonips_ranking_contributing_user` (
  `item_id` int(10) unsigned NOT NULL default '0',
  `uid` int(10) unsigned NOT NULL default '0',
  `timestamp` timestamp NOT NULL,
  PRIMARY KEY  (`item_id`)
) ENGINE=InnoDB;

# --------------------------------------------------------

#
# Table structure for table `xoonips_ranking_downloaded_item`
#

CREATE TABLE `xoonips_ranking_downloaded_item` (
  `item_id` int(10) unsigned NOT NULL default '0',
  `count` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`item_id`)
) ENGINE=InnoDB;

# --------------------------------------------------------

#
# Table structure for table `xoonips_ranking_new_group`
#

CREATE TABLE `xoonips_ranking_new_group` (
  `gid` int(10) unsigned NOT NULL default '0',
  `timestamp` timestamp NOT NULL,
  PRIMARY KEY  (`gid`)
) ENGINE=InnoDB;

# --------------------------------------------------------

#
# Table structure for table `xoonips_ranking_new_item`
#

CREATE TABLE `xoonips_ranking_new_item` (
  `item_id` int(10) unsigned NOT NULL default '0',
  `timestamp` timestamp NOT NULL,
  PRIMARY KEY  (`item_id`)
) ENGINE=InnoDB;

# --------------------------------------------------------

#
# Table structure for table `xoonips_ranking_searched_keyword`
#

CREATE TABLE `xoonips_ranking_searched_keyword` (
  `keyword` varchar(255) binary NOT NULL default '',
  `count` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`keyword`)
) ENGINE=InnoDB;

# --------------------------------------------------------

#
# Table structure for table `xoonips_ranking_sum_active_group`
#

CREATE TABLE `xoonips_ranking_sum_active_group` (
  `gid` int(10) unsigned NOT NULL default '0',
  `count` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`gid`)
) ENGINE=InnoDB;

# --------------------------------------------------------

#
# Table structure for table `xoonips_ranking_sum_contributing_user`
#

CREATE TABLE `xoonips_ranking_sum_contributing_user` (
  `item_id` int(10) unsigned NOT NULL default '0',
  `uid` int(10) unsigned NOT NULL default '0',
  `timestamp` timestamp NOT NULL,
  PRIMARY KEY  (`item_id`)
) ENGINE=InnoDB;

# --------------------------------------------------------

#
# Table structure for table `xoonips_ranking_sum_downloaded_item`
#

CREATE TABLE `xoonips_ranking_sum_downloaded_item` (
  `item_id` int(10) unsigned NOT NULL default '0',
  `count` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`item_id`)
) ENGINE=InnoDB;

# --------------------------------------------------------

#
# Table structure for table `xoonips_ranking_sum_new_group`
#

CREATE TABLE `xoonips_ranking_sum_new_group` (
  `gid` int(10) unsigned NOT NULL default '0',
  `timestamp` timestamp NOT NULL,
  PRIMARY KEY  (`gid`)
) ENGINE=InnoDB;

# --------------------------------------------------------

#
# Table structure for table `xoonips_ranking_sum_new_item`
#

CREATE TABLE `xoonips_ranking_sum_new_item` (
  `item_id` int(10) unsigned NOT NULL default '0',
  `timestamp` timestamp NOT NULL,
  PRIMARY KEY  (`item_id`)
) ENGINE=InnoDB;

# --------------------------------------------------------

#
# Table structure for table `xoonips_ranking_sum_searched_keyword`
#

CREATE TABLE `xoonips_ranking_sum_searched_keyword` (
  `keyword` varchar(255) binary NOT NULL default '',
  `count` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`keyword`)
) ENGINE=InnoDB;

# --------------------------------------------------------

#
# Table structure for table `xoonips_ranking_sum_viewed_item`
#

CREATE TABLE `xoonips_ranking_sum_viewed_item` (
  `item_id` int(10) unsigned NOT NULL default '0',
  `count` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`item_id`)
) ENGINE=InnoDB;

# --------------------------------------------------------

#
# Table structure for table `xoonips_ranking_viewed_item`
#

CREATE TABLE `xoonips_ranking_viewed_item` (
  `item_id` int(10) unsigned NOT NULL default '0',
  `count` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`item_id`)
) ENGINE=InnoDB;

# --------------------------------------------------------

#
# Table structure for table `xoonips_related_to`
#

CREATE TABLE `xoonips_related_to` (
  `related_to_id` int(10) unsigned NOT NULL auto_increment,
  `parent_id` int(10) unsigned NOT NULL default '0',
  `item_id` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`related_to_id`),
  UNIQUE KEY `related_to` (`parent_id`,`item_id`)
) ENGINE=InnoDB;

# --------------------------------------------------------

#
# Table structure for table `xoonips_search_cache`
#

CREATE TABLE `xoonips_search_cache` (
  `search_cache_id` int(10) unsigned NOT NULL auto_increment,
  `sess_id` varchar(32) NOT NULL default '',
  `timestamp` timestamp NOT NULL,
  PRIMARY KEY  (`search_cache_id`)
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table `xoonips_search_cache_file`
#

CREATE TABLE `xoonips_search_cache_file` (
  `search_cache_id` int(10) unsigned NOT NULL default '0',
  `file_id` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`search_cache_id`,`file_id`)
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table `xoonips_search_cache_item`
#

CREATE TABLE `xoonips_search_cache_item` (
  `search_cache_item_id` int(10) NOT NULL auto_increment,
  `search_cache_id` int(10) unsigned NOT NULL default '0',
  `item_id` int(10) unsigned NOT NULL default '0',
  `matchfor_index` tinyint(1) NOT NULL default '0',
  `matchfor_item` tinyint(1) NOT NULL default '0',
  `matchfor_file` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`search_cache_item_id`),
  UNIQUE KEY `search_cache_id` (`search_cache_id`,`item_id`)
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table `xoonips_search_cache_metadata`
#

CREATE TABLE `xoonips_search_cache_metadata` (
  `search_cache_metadata_id` int(10) NOT NULL auto_increment,
  `search_cache_id` int(10) unsigned NOT NULL default '0',
  `identifier` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`search_cache_metadata_id`),
  UNIQUE KEY `search_cache_id` (`search_cache_id`,`identifier`)
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table `xoonips_search_text`
#

CREATE TABLE `xoonips_search_text` (
  `file_id` int(10) unsigned NOT NULL default '0',
  `search_text` longtext,
  PRIMARY KEY  (`file_id`),
  FULLTEXT KEY `search_text` (`search_text`)
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table `xoonips_session`
#

CREATE TABLE `xoonips_session` (
  `sess_id` varchar(32) NOT NULL default '',
  `updated` timestamp NOT NULL,
  `uid` int(10) unsigned NOT NULL default '0',
  `su_uid` int(10) unsigned default NULL,
  `sess_data` longtext NOT NULL,
  PRIMARY KEY  (`sess_id`)
) ENGINE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table `xoonips_transfer_request`
#

CREATE TABLE `xoonips_transfer_request` (
  `item_id` int(10) unsigned NOT NULL,
  `to_uid` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`item_id`)
) ENGINE=InnoDB;

# --------------------------------------------------------

#
# Table structure for table `xoonips_users`
#

CREATE TABLE `xoonips_users` (
  `uid` int(10) unsigned NOT NULL default '0',
  `activate` tinyint(1) unsigned NOT NULL default '0',
  `address` varchar(255) binary default NULL,
  `division` varchar(255) binary default NULL,
  `tel` varchar(32) binary NOT NULL default '',
  `company_name` varchar(255) binary default NULL,
  `country` varchar(255) binary NOT NULL default '',
  `zipcode` varchar(32) binary NOT NULL default '',
  `fax` varchar(32) binary NOT NULL default '',
  `base_url` varchar(255) binary default NULL,
  `notice_mail` int(10) unsigned default NULL,
  `notice_mail_since` int(10) unsigned default '0',
  `private_index_id` int(10) unsigned NOT NULL default '0',
  `private_item_number_limit` int(10) unsigned default NULL,
  `private_index_number_limit` int(10) unsigned default NULL,
  `private_item_storage_limit` double default NULL,
  `user_order` smallint(3) default '0',
  `posi` smallint(5) default '0',
  `appeal` text,
  PRIMARY KEY  (`uid`),
  KEY `activate` (`activate`)
) ENGINE=MyISAM;
