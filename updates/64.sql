CREATE TABLE IF NOT EXISTS `cache` (
  `key` varchar(255) NOT NULL default '',
  `group` varchar(255) NOT NULL default '',
  `value` text NOT NULL,
  `tenant_id` int(11) NOT NULL,
  PRIMARY KEY  (`key`,`group`,`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
UPDATE `settings` SET `value` = '1.2b-object-cache' WHERE `name` = 'version';
UPDATE `settings` SET `value` = 64 WHERE `name` = 'schema-version';