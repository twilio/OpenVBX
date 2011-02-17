CREATE TABLE flow_store (
  `key` varchar(255) NOT NULL,
  `value` TEXT NULL,
  `flow_id` int(11) NOT NULL,
  UNIQUE KEY `key_flow` (`key`, `flow_id`),
  INDEX(`key`, `flow_id`)
) ENGINE=InnoDB CHARSET=UTF8;