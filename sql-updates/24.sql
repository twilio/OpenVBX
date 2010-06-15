ALTER TABLE `users` ADD COLUMN `last_seen` datetime DEFAULT NULL;
ALTER TABLE `users` ADD COLUMN `last_login` datetime DEFAULT NULL;

UPDATE `settings` SET `value` = 24 WHERE `name` = 'schema-version';