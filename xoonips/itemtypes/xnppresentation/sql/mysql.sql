#
#
# Table structure for table `xnppresentation_item_detail`
#

CREATE TABLE xnppresentation_item_detail (
  presentation_id int(10) unsigned NOT NULL,
  presentation_type varchar(30) NOT NULL,
  use_cc tinyint(3) NOT NULL,
  cc_commercial_use tinyint(3),
  cc_modification tinyint(3),
  rights text,
  readme text,
 `attachment_dl_limit` int(1) unsigned default 0,
  `attachment_dl_notify` int(1) unsigned default 0,
  PRIMARY KEY  (presentation_id)
) ENGINE=MyISAM;

#
# `xnppresentation_creator`
#

CREATE TABLE `xnppresentation_creator` (
  `presentation_creator_id` int(10) unsigned NOT NULL auto_increment,
  `presentation_id` int(10) unsigned NOT NULL,
  `creator` varchar(255) NOT NULL,
  `creator_order` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`presentation_creator_id`)
) ENGINE=InnoDB;

