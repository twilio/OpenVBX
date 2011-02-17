ALTER TABLE messages DROP KEY `call_guid`;
ALTER TABLE messages CHANGE COLUMN `call_guid`  `call_sid` VARCHAR(34) DEFAULT NULL;
ALTER TABLE messages ADD KEY `call_sid` (`call_sid`);
UPDATE `settings` SET `value` = '0.90' WHERE `name` = 'version';
UPDATE `settings` SET `value` = 43 WHERE `name` = 'schema-version';

