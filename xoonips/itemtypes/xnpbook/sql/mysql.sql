# phpMyAdmin SQL Dump
# version 2.11.7
# http://www.phpmyadmin.net

# --------------------------------------------------------

#
# Table structure for table `xnpbook_item_detail`
#

CREATE TABLE `xnpbook_item_detail` (
  `book_id` int(10) unsigned NOT NULL auto_increment,
  `classification` varchar(30) default NULL,
  `editor` varchar(255) default NULL,
  `publisher` varchar(255) default NULL,
  `isbn` char(13) default NULL,
  `url` blob,
  `attachment_dl_limit` int(1) unsigned default '0',
  `attachment_dl_notify` int(1) unsigned default '0',
  PRIMARY KEY  (`book_id`)
) ENGINE=InnoDB;

#
# `xnpbook_author`
#

CREATE TABLE `xnpbook_author` (
  `book_author_id` int(10) unsigned NOT NULL auto_increment,
  `book_id` int(10) unsigned NOT NULL,
  `author` varchar(255) NOT NULL,
  `author_order` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`book_author_id`)
) ENGINE=InnoDB;

