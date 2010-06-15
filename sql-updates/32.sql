UPDATE flows SET 
	   `sms_data` = replace(`sms_data`, 'standard---sms', 'sms---sms'),
	   `data` = replace(`data`, 'standard---sms', 'sms---sms');
UPDATE flows SET 
	   `sms_data` = replace(`sms_data`, 'standard---sms-forward', 'sms---sms-inbox');
UPDATE `settings` SET `value` = 32 WHERE `name` = 'schema-version';