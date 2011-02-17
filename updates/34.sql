INSERT INTO `settings` SET `name` = 'rewrite_enabled',`value` = 0, `tenant_id`=1;
UPDATE `settings` SET `value` = '0.77' WHERE `name` = 'version';
UPDATE `settings` SET `value` = 34 WHERE `name` = 'schema-version';