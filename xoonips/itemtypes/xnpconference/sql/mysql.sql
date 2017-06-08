#
#
# Table structure for table `xnpconference_item_detail`
#

CREATE TABLE xnpconference_item_detail (
  conference_id int(10) unsigned NOT NULL,
  presentation_type varchar(30) NOT NULL,
  conference_title varchar(255) NOT NULL,
  place varchar(255) NOT NULL,
  abstract text,
  conference_from_year int(10) default NULL,
  conference_from_month int(10) default NULL,
  conference_from_mday int(10) default NULL,
  conference_to_year int(10) default NULL,
  conference_to_month int(10) default NULL,
  conference_to_mday int(10) default NULL,
  attachment_dl_limit int(1) unsigned default '1',
  `attachment_dl_notify` int(1) unsigned default 0,
  PRIMARY KEY  (conference_id)
) ENGINE=InnoDB;

CREATE TABLE `xnpconference_author` (
  `conference_author_id` int(10) unsigned NOT NULL auto_increment,
  `conference_id` int(10) unsigned NOT NULL,
  `author` varchar(255) NOT NULL,
  `author_order` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`conference_author_id`)
) ENGINE=InnoDB;

