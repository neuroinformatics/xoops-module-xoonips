#
#
# Table structure for table `xnpmemo_item_detail`
#

CREATE TABLE xnpmemo_item_detail (
  memo_id int(10) unsigned NOT NULL,
  item_link varchar(255) default NULL,
  PRIMARY KEY  (memo_id)
) ENGINE=InnoDB;
