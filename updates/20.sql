ALTER TABLE audio_files ADD COLUMN cancelled BIT DEFAULT 0 AFTER tag;

UPDATE `settings` SET `value` = 20 WHERE `name` = 'schema-version';