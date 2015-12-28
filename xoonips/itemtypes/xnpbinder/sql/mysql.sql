#
# `xnpbinder_binder_item_link`
#

CREATE TABLE xnpbinder_item_detail (
  binder_id INT( 10 ) NOT NULL ,
  extra varchar(255) NOT NULL ,
  PRIMARY KEY ( binder_id ) 
) ENGINE=InnoDB;

CREATE TABLE xnpbinder_binder_item_link (
  binder_item_link_id int(10) unsigned NOT NULL auto_increment,
  binder_id int(10) unsigned NOT NULL default '0',
  item_id int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (binder_item_link_id),
  UNIQUE KEY binder_id (binder_id,item_id),
  KEY index_id (binder_id),
  KEY item_id (item_id)
) ENGINE=InnoDB;
