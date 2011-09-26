UPDATE `settings` SET `value` = '2.0.0' WHERE `name` = 'version';
UPDATE `settings` SET `value` = 54 WHERE `name` = 'schema-version';
ALTER TABLE  `users` ADD  `data` TEXT NOT NULL COMMENT  'Stores VoiceVault claimant identifier.';

INSERT INTO `settings` 
	(tenant_id, name, value) 
	VALUES 
	(1, 'tropo_username', '');
INSERT INTO `settings`
	(tenant_id, name, value)
	VALUES
	(1, 'tropo_password', '');
INSERT INTO `settings`
	(tenant_id, name, value)
	VALUES
	(1, 'tropo_hostname	', '');
INSERT INTO `settings`
	(tenant_id, name, value)
	VALUES
	(1, 'phono_api_key', '');
INSERT INTO `settings`
	(tenant_id, name, value)
	VALUES
	(1, 'voicevault_username', '');
INSERT INTO `settings`
	(tenant_id, name, value)
	VALUES
	(1, 'voicevault_password', '');
INSERT INTO `settings`
	(tenant_id, name, value)
	VALUES
	(1, 'voicevault_config', '');
INSERT INTO `settings`
	(tenant_id, name, value)
	VALUES
	(1, 'voicevault_organisation', '');
INSERT INTO `settings`
  (tenant_id, name, value)
  VALUES
  (1, 'voicevault_number', '');