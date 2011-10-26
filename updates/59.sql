CREATE TABLE IF NOT EXISTS `user_settings` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `user_id` int(11) NOT NULL,
  `key` varchar(255) default NULL,
  `value` text,
  `tenant_id` int(11) NOT NULL default '1',
  PRIMARY KEY  (`id`),
  KEY `user_key` (`user_id`,`key`),
  KEY `key` (`key`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
UPDATE `settings` SET `value` = 59 WHERE `name` = 'schema-version';