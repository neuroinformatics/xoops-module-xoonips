CREATE TABLE `xnptool_item_detail` (
    `tool_id` int(10) unsigned NOT NULL,
    `tool_type` varchar(30), 
    `readme` text,
    `rights` text,
     use_cc tinyint(3) NOT NULL,
     cc_commercial_use tinyint(3),
     cc_modification tinyint(3),
    `attachment_dl_limit` int(1) unsigned default 0,
    `attachment_dl_notify` int(1) unsigned default 0,
    PRIMARY KEY (tool_id)
) ENGINE=InnoDB;

#
# `xnptool_developer`
#

CREATE TABLE `xnptool_developer` (
  `tool_developer_id` int(10) unsigned NOT NULL auto_increment,
  `tool_id` int(10) unsigned NOT NULL,
  `developer` varchar(255) NOT NULL,
  `developer_order` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`tool_developer_id`)
) ENGINE=InnoDB;

