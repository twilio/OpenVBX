UPDATE `settings` SET `value` = '1.1.1b-object-cache' WHERE `name` = 'version';
UPDATE `settings` SET `value` = 63 WHERE `name` = 'schema-version';
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL default '',
  `group` varchar(255) NOT NULL default '',
  `value` text NOT NULL,
  `tenant_id` int(11) NOT NULL,
  PRIMARY KEY  (`key`,`group`,`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;