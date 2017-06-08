#
#
# Table structure for table `xnpurl_item_detail`
#

CREATE TABLE `xnpurl_item_detail` (
  `url_id` int(10) unsigned NOT NULL,
  `url` varchar(255) NOT NULL,
  `url_count` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`url_id`)
) ENGINE=InnoDB;
