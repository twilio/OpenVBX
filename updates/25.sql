ALTER TABLE `users` MODIFY COLUMN `voicemail` TEXT NOT NULL DEFAULT '';
ALTER TABLE `plugin_store` MODIFY COLUMN `plugin_id` varchar(34) NOT NULL;

UPDATE `settings` SET `value` = 25 WHERE `name` = 'schema-version';