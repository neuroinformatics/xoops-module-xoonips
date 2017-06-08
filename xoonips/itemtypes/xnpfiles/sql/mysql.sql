#
#
# Table structure for table `xnpfiles_item_detail`
#

CREATE TABLE xnpfiles_item_detail (
  files_id int(10) unsigned NOT NULL,
  data_file_name varchar(255),
  data_file_mimetype varchar(255),
  data_file_filetype varchar(255),
  PRIMARY KEY  (files_id)
) ENGINE=InnoDB;
