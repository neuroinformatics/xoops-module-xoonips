#
#
# Table structure for table `xnppaper_item_detail`
#

CREATE TABLE xnppaper_item_detail (
  paper_id int(10) unsigned NOT NULL,
  journal varchar(255) NOT NULL,
  volume int(10) unsigned,
  number int(10) unsigned,
  page varchar(30),
  abstract text,
  pubmed_id varchar(30),
  PRIMARY KEY  (paper_id)
) ENGINE=MyISAM;


CREATE TABLE `xnppaper_author` (
  `paper_author_id` int(10) unsigned NOT NULL auto_increment,
  `paper_id` int(10) unsigned NOT NULL,
  `author` varchar(255) NOT NULL,
  `author_order` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`paper_author_id`)
) ENGINE=InnoDB;

