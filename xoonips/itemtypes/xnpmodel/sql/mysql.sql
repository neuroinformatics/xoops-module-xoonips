CREATE TABLE xnpmodel_item_detail (
    model_id int(10) unsigned NOT NULL,
    model_type varchar(30), 
    readme text,
    rights text,
    use_cc tinyint(3) NOT NULL,
    cc_commercial_use tinyint(3),
    cc_modification tinyint(3),
    `attachment_dl_limit` int(1) unsigned default 0,
    `attachment_dl_notify` int(1) unsigned default 0,
    PRIMARY KEY (model_id)
) ENGINE=InnoDB;

#
# `xnpmodel_creator`
#

CREATE TABLE `xnpmodel_creator` (
  `model_creator_id` int(10) unsigned NOT NULL auto_increment,
  `model_id` int(10) unsigned NOT NULL,
  `creator` varchar(255) NOT NULL,
  `creator_order` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`model_creator_id`)
) ENGINE=InnoDB;
