ALTER TABLE `settings` MODIFY COLUMN `value` TEXT NOT NULL DEFAULT '';
INSERT INTO `settings` (`install_id`, `name`, `value`) VALUES (1, 'iphone_theme', '');
ALTER TABLE `numbers` ADD COLUMN `sms` TINYINT NOT NULL DEFAULT 0;
INSERT INTO `annotation_types` (`description`) VALUES ('sms');
ALTER TABLE `users` ADD COLUMN `voicemail` VARCHAR(1024) NOT NULL DEFAULT '';