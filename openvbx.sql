SET foreign_key_checks=0;

DROP TABLE IF EXISTS `flows`;
CREATE TABLE IF NOT EXISTS `flows` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(100) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created` datetime default NULL,
  `updated` datetime default NULL,
  `data` text NULL,
  `sms_data` text NULL,
  `tenant_id` BIGINT(20) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`, `tenant_id`)
) ENGINE=InnoDB CHARSET=UTF8;

DROP TABLE IF EXISTS `groups`;
CREATE TABLE IF NOT EXISTS `groups` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(100) default NULL,
  `is_active` tinyint NOT NULL DEFAULT 1,
  `tenant_id` BIGINT(20) NOT NULL,
  PRIMARY KEY  (`id`),
  INDEX(`tenant_id`)
) ENGINE=InnoDB CHARSET=UTF8;

DROP TABLE IF EXISTS `groups_users`;
CREATE TABLE IF NOT EXISTS `groups_users` (
  `id` int(11) NOT NULL auto_increment,
  `group_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `tenant_id` BIGINT(20) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `group_id` (`group_id`),
  INDEX(`tenant_id`)
) ENGINE=InnoDB CHARSET=UTF8;

DROP TABLE IF EXISTS `audio_files`;
CREATE TABLE IF NOT EXISTS `audio_files` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `label` varchar(255) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `url` varchar(255) DEFAULT NULL,
  `recording_call_sid` varchar(100) DEFAULT NULL,
  `tag` varchar(100) DEFAULT NULL,
  `cancelled` TINYINT DEFAULT 0,
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  `tenant_id` BIGINT(20) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX(`user_id`),
  INDEX(`url`),
  INDEX(`recording_call_sid`),
  INDEX(`tag`),
  INDEX(`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=UTF8;

DROP TABLE IF EXISTS `messages`;
CREATE TABLE IF NOT EXISTS `messages` (
  `id` int(11) NOT NULL auto_increment,
  `created` datetime default NULL,
  `updated` datetime default NULL,
  `read` datetime default NULL,
  `call_sid` varchar(40) default NULL,
  `caller` varchar(20) default NULL,
  `called` varchar(20) default NULL,
  `type` varchar(10) default NULL,
  `status` varchar(10) default NULL,
  `content_url` varchar(255) default NULL,
  `content_text` varchar(5000) default NULL,
  `notes` varchar(5000) default NULL,
  `size` smallint(6) default NULL,
  `assigned_to` BIGINT NULL,
  `archived` TINYINT NOT NULL DEFAULT 0,
  `ticket_status` ENUM('open', 'closed', 'pending') NOT NULL DEFAULT 'open',
  `tenant_id` BIGINT(20) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `call_sid` (`call_sid`),
  INDEX(`tenant_id`)
) ENGINE=InnoDB CHARSET=UTF8;

DROP TABLE IF EXISTS `numbers`;
CREATE TABLE IF NOT EXISTS `numbers` (
  `id` int(11) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL,
  `name` varchar(100) default NULL,
  `value` TEXT NOT NULL,
  `is_active` tinyint(1) default 1,
  `sms` tinyint(1) default 0,
  `sequence` smallint(6) default NULL,
  `tenant_id` BIGINT(20) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `user_id` (`user_id`),
  INDEX(`tenant_id`)
) ENGINE=InnoDB CHARSET=UTF8;

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL auto_increment,
  `is_admin` tinyint(1) default NULL,
  `is_active` tinyint(1) default 1,
  `first_name` varchar(100) default NULL,
  `last_name` varchar(100) default NULL,
  `password` varchar(40) default NULL,
  `invite_code` varchar(32) NULL,
  `email` varchar(200) default NULL,
  `pin` varchar(40) default NULL,
  `notification` varchar(20) default NULL,
  `auth_type` TINYINT NOT NULL default 1,
  `voicemail` TEXT NOT NULL,
  `tenant_id` BIGINT(20) NOT NULL,
  `last_seen` datetime DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `online` TINYINT(1) NOT NULL DEFAULT 9,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `email` (`email`, `tenant_id`),
  INDEX(`tenant_id`)
) ENGINE=InnoDB CHARSET=UTF8;

DROP TABLE IF EXISTS `auth_types`;
CREATE TABLE IF NOT EXISTS `auth_types` (
  `id` tinyint NOT NULL AUTO_INCREMENT,
  `description` VARCHAR(255) NOT NULL,
  `tenant_id` BIGINT(20) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX(`tenant_id`)
) ENGINE=InnoDB CHARSET=UTF8;

DROP TABLE IF EXISTS `rest_access`;
CREATE TABLE IF NOT EXISTS `rest_access` (
  `key` VARCHAR(32) NOT NULL,
  `locked` TINYINT NOT NULL DEFAULT 0,
  `created` DATETIME NOT NULL,
  `user_id` INT NOT NULL,
  `tenant_id` BIGINT(20) NOT NULL,
  PRIMARY KEY (`key`),
  INDEX(`tenant_id`)
) ENGINE=InnoDB CHARSET=UTF8;

DROP TABLE IF EXISTS `user_messages`;
CREATE TABLE IF NOT EXISTS `user_messages` (
  user_id INT(11) NOT NULL,
  message_id INT(11) NOT NULL,
  `tenant_id` BIGINT(20) NOT NULL,
  PRIMARY KEY(user_id, message_id),
  INDEX(`tenant_id`)
) ENGINE=InnoDB CHARSET=UTF8;

CREATE TABLE IF NOT EXISTS `group_messages` (
  group_id INT(11) NOT NULL,
  message_id INT(11) NOT NULL,
  `tenant_id` BIGINT(20) NOT NULL,
  PRIMARY KEY(group_id, message_id),
  INDEX(`tenant_id`)
) ENGINE=InnoDB CHARSET=UTF8;

DROP TABLE IF EXISTS `user_annotations`;
CREATE TABLE IF NOT EXISTS `user_annotations` (
  user_id INT(11) NOT NULL,
  annotation_id BIGINT NOT NULL,
  `tenant_id` BIGINT(20) NOT NULL,
  PRIMARY KEY(user_id, annotation_id),
  INDEX(`tenant_id`)
) ENGINE=InnoDB CHARSET=UTF8;

DROP TABLE IF EXISTS `group_annotations`;
CREATE TABLE IF NOT EXISTS `group_annotations` (
  group_id INT(11) NOT NULL,
  annotation_id BIGINT NOT NULL,
  `tenant_id` BIGINT(20) NOT NULL,
  PRIMARY KEY(group_id, annotation_id),
  INDEX(`tenant_id`)
) ENGINE=InnoDB CHARSET=UTF8;

DROP TABLE IF EXISTS `annotations`;
CREATE TABLE IF NOT EXISTS `annotations` (
  `id` bigint(20) NOT NULL auto_increment,
  `annotation_type` tinyint(4) NOT NULL,
  `message_id` bigint(20) NOT NULL,
  `user_id` int(11) NOT NULL,
  `description` text character set latin1 NOT NULL,
  `created` datetime NOT NULL,
  `tenant_id` BIGINT(20) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `annotation_type_message_id` (`annotation_type`,`message_id`,`created`),
  KEY `created` (`created`),
  INDEX(`tenant_id`)
) ENGINE=InnoDB CHARSET=UTF8;

DROP TABLE IF EXISTS `annotation_types`;
CREATE TABLE IF NOT EXISTS `annotation_types` (
  id TINYINT NOT NULL AUTO_INCREMENT,
  description VARCHAR(32) NOT NULL,
  `tenant_id` BIGINT(20) NOT NULL,
  PRIMARY KEY(id),
  INDEX(`tenant_id`)
) ENGINE=InnoDB CHARSET=UTF8;

DROP TABLE IF EXISTS `tenants`;
CREATE TABLE IF NOT EXISTS `tenants` (
  id BIGINT AUTO_INCREMENT,
  name VARCHAR(255) NOT NULL,
  url_prefix VARCHAR(255) NOT NULL,
  local_prefix VARCHAR(1000) NOT NULL,
  active TINYINT NOT NULL DEFAULT 1,
  PRIMARY KEY(id),
  INDEX(name),
  INDEX url_prefix (url_prefix)
) ENGINE=InnoDB CHARSET=UTF8;

DROP TABLE IF EXISTS `settings`;
CREATE TABLE IF NOT EXISTS `settings` (
  id BIGINT AUTO_INCREMENT,
  tenant_id BIGINT NOT NULL,
  name VARCHAR(32) NOT NULL,
  value VARCHAR(255) NOT NULL,
  PRIMARY KEY(id),
  INDEX(name),
  INDEX(tenant_id, name)
) ENGINE=InnoDB CHARSET=UTF8;

DROP TABLE IF EXISTS `flow_store`;
CREATE TABLE IF NOT EXISTS `flow_store` (
  `key` varchar(255) NOT NULL,
  `value` TEXT NULL,
  `flow_id` int(11) NOT NULL,
  `tenant_id` BIGINT(20) NOT NULL,
  UNIQUE KEY `key_flow` (`key`, `flow_id`),
  INDEX(`key`, `flow_id`),
  INDEX(`tenant_id`)
) ENGINE=InnoDB CHARSET=UTF8;

DROP TABLE IF EXISTS `plugin_store`;
CREATE TABLE IF NOT EXISTS `plugin_store` (
  `key` varchar(255) NOT NULL,
  `value` TEXT NULL,
  `plugin_id` varchar(34) NOT NULL,
  `tenant_id` BIGINT(20) NOT NULL,
  UNIQUE KEY `key_plugin` (`key`, `plugin_id`, `tenant_id`),
  INDEX(`key`, `plugin_id`),
  INDEX(`tenant_id`)
) ENGINE=InnoDB CHARSET=UTF8;

ALTER TABLE settings ADD FOREIGN KEY(tenant_id) REFERENCES tenants(id);

ALTER TABLE user_messages ADD FOREIGN KEY(user_id) REFERENCES users(id);
ALTER TABLE user_messages ADD FOREIGN KEY(message_id) REFERENCES messages(id);

ALTER TABLE group_messages ADD FOREIGN KEY(group_id) REFERENCES groups(id);
ALTER TABLE group_messages ADD FOREIGN KEY(message_id) REFERENCES messages(id);
ALTER TABLE numbers ADD FOREIGN KEY(user_id) REFERENCES users(id);
ALTER TABLE flows ADD FOREIGN KEY(user_id) REFERENCES users(id);

ALTER TABLE users ADD FOREIGN KEY(auth_type) REFERENCES auth_types(id);

ALTER TABLE groups_users ADD FOREIGN KEY(user_id) REFERENCES users(id);
ALTER TABLE groups_users ADD FOREIGN KEY(group_id) REFERENCES groups(id);


ALTER TABLE flows ADD FOREIGN KEY(tenant_id) REFERENCES tenants(id);
ALTER TABLE groups ADD FOREIGN KEY(tenant_id) REFERENCES tenants(id);
ALTER TABLE groups_users ADD FOREIGN KEY(tenant_id) REFERENCES tenants(id);
ALTER TABLE audio_files ADD FOREIGN KEY(tenant_id) REFERENCES tenants(id);
ALTER TABLE messages ADD FOREIGN KEY(tenant_id) REFERENCES tenants(id);
ALTER TABLE numbers ADD FOREIGN KEY(tenant_id) REFERENCES tenants(id);
ALTER TABLE users ADD FOREIGN KEY(tenant_id) REFERENCES tenants(id);
ALTER TABLE auth_types ADD FOREIGN KEY(tenant_id) REFERENCES tenants(id);
ALTER TABLE rest_access ADD FOREIGN KEY(tenant_id) REFERENCES tenants(id);
ALTER TABLE user_messages ADD FOREIGN KEY(tenant_id) REFERENCES tenants(id);
ALTER TABLE group_messages ADD FOREIGN KEY(tenant_id) REFERENCES tenants(id);
ALTER TABLE user_annotations ADD FOREIGN KEY(tenant_id) REFERENCES tenants(id);
ALTER TABLE group_annotations ADD FOREIGN KEY(tenant_id) REFERENCES tenants(id);
ALTER TABLE annotations ADD FOREIGN KEY(tenant_id) REFERENCES tenants(id);
ALTER TABLE annotation_types ADD FOREIGN KEY(tenant_id) REFERENCES tenants(id);
ALTER TABLE flow_store ADD FOREIGN KEY(tenant_id) REFERENCES tenants(id);
ALTER TABLE plugin_store ADD FOREIGN KEY(tenant_id) REFERENCES tenants(id);


INSERT INTO tenants
	   (name, url_prefix, local_prefix)
	   VALUES
	   ('default', '', '');

INSERT INTO annotation_types (description, tenant_id)
	   VALUES
	   ('called', 1),
	   ('read', 1),
	   ('noted', 1),
	   ('changed', 1),
	   ('labeled', 1),
	   ('sms', 1);

INSERT INTO auth_types (description, tenant_id)
	   VALUES
	   ('openvbx', 1),
	   ('google', 1);


INSERT INTO settings
	   (name, value, tenant_id)
	   VALUES
	   ('dash_rss', '', 1),
	   ('theme', '', 1),
	   ('version', '2.0.0', 1),
	   ('iphone_theme', '', 1),
	   ('enable_sandbox_number', 1, 1),
	   ('twilio_endpoint', 'https://api.twilio.com/2010-04-01', 1),
	   ('recording_host','',1);


INSERT INTO groups
       (name, is_active, tenant_id)
       VALUES
       ('Sales', 1, 1),
       ('Support', 1, 1);

ALTER TABLE  `users` ADD  `data` TEXT NOT NULL COMMENT  'Stores VoiceVault claimant identifier.';
