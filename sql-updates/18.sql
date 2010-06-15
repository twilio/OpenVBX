ALTER TABLE audio_files ADD COLUMN tag VARCHAR(100) DEFAULT NULL AFTER recording_call_sid;
CREATE INDEX `tag` ON `audio_files` (`tag`);

UPDATE `settings` SET `value` = 18 WHERE `name` = 'schema-version';