CREATE TABLE plugin_store (
  `key` varchar(255) NOT NULL,
  `value` TEXT NULL,
  `plugin_id` varchar(255) NOT NULL,
  UNIQUE KEY `key_plugin` (`key`, `plugin_id`),
  INDEX(`key`, `plugin_id`)
) ENGINE=InnoDB CHARSET=UTF8;