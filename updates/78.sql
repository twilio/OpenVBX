ALTER TABLE `cache` MODIFY `value` MEDIUMBLOB NOT NULL;
UPDATE `settings` SET `value` = 78 WHERE `name` = 'schema-version';