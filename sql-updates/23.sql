ALTER TABLE audio_files CHANGE COLUMN cancelled cancelled TINYINT DEFAULT 0;
UPDATE `settings` SET `value` = 23 WHERE `name` = 'schema-version';