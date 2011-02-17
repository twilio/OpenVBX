ALTER TABLE users MODIFY COLUMN `voicemail` TEXT NOT NULL;

UPDATE `settings` SET `value` = 27 WHERE `name` = 'schema-version';