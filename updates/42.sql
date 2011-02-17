INSERT INTO settings (tenant_id,name,value) VALUES (1,'recording_host','');
UPDATE `settings` SET `value` = 42 WHERE `name` = 'schema-version';
UPDATE `settings` SET `value` = '0.85' WHERE `name` = 'version';
