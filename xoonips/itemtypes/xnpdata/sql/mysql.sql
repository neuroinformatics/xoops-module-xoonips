#
#
# Table structure for table `xnpdata_item_detail`
#

CREATE TABLE xnpdata_item_detail (
  data_id int(10) unsigned NOT NULL,
  data_type varchar(30) NOT NULL,
  rights text,
  readme text,
  use_cc tinyint(3) NOT NULL,
  cc_commercial_use tinyint(3),
  cc_modification tinyint(3),
  attachment_dl_limit int(1) unsigned default '0',
  `attachment_dl_notify` int(1) unsigned default 0,
  PRIMARY KEY  (data_id)
) ENGINE=InnoDB;

#
# `xnpdata_experimenter`
#

CREATE TABLE `xnpdata_experimenter` (
  `data_experimenter_id` int(10) unsigned NOT NULL auto_increment,
  `data_id` int(10) unsigned NOT NULL,
  `experimenter` varchar(255) NOT NULL,
  `experimenter_order` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`data_experimenter_id`)
) ENGINE=InnoDB;
