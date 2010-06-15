DROP INDEX `remote_url` ON `audio_files`;
DROP INDEX `local_file` ON `audio_files`;

ALTER TABLE `audio_files` CHANGE `remote_url` `url` varchar(255) DEFAULT NULL;
ALTER TABLE `audio_files` DROP COLUMN `local_file`;

CREATE INDEX `url` ON `audio_files` (`url`);