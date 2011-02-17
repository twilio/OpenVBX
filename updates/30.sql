ALTER TABLE `plugin_store` DROP KEY `key_plugin`;
ALTER TABLE `plugin_store` ADD KEY `key_plugin_tenant` (`key`, `plugin_id`, `tenant_id`);
UPDATE `settings` SET `value` = 30 WHERE `name` = 'schema-version';