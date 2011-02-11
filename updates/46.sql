DELETE FROM settings where name='from_email' and value='';
UPDATE `settings` SET `value` = '0.90' WHERE `name` = 'version';
UPDATE `settings` SET `value` = 46 WHERE `name` = 'schema-version';