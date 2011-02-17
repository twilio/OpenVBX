UPDATE settings SET value='0.74' WHERE name='version';
UPDATE settings SET value=1 WHERE name='enable_sandbox_number' AND tenant_id=1;
UPDATE `settings` SET `value` = 29 WHERE `name` = 'schema-version';