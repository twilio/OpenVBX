UPDATE flows SET 
	   `sms_data` = replace(`sms_data`, 'standard---query', 'menu---query'),
	   `data` = replace(`data`, 'standard---menu', 'menu---menu');
UPDATE `settings` SET `value` = 31 WHERE `name` = 'schema-version';