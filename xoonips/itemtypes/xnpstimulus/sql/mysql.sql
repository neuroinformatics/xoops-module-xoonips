CREATE TABLE `xnpstimulus_item_detail` (
    `stimulus_id` int(10) unsigned NOT NULL,
    `stimulus_type` varchar(30), 
    `readme` text,
    `rights` text,
     use_cc tinyint(3) NOT NULL,
     cc_commercial_use tinyint(3),
     cc_modification tinyint(3),
    `attachment_dl_limit` int(1) unsigned default 0,
    `attachment_dl_notify` int(1) unsigned default 0,
    PRIMARY KEY (stimulus_id)
) ENGINE=InnoDB;

#
# `xnpstimulus_developer`
#

CREATE TABLE `xnpstimulus_developer` (
  `stimulus_developer_id` int(10) unsigned NOT NULL auto_increment,
  `stimulus_id` int(10) unsigned NOT NULL,
  `developer` varchar(255) NOT NULL,
  `developer_order` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`stimulus_developer_id`)
) ENGINE=InnoDB;

