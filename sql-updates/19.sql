RENAME TABLE installs TO tenants;

ALTER TABLE settings DROP FOREIGN KEY `settings_ibfk_1`;
ALTER TABLE settings CHANGE install_id tenant_id bigint(20) NOT NULL;
ALTER TABLE settings ADD FOREIGN KEY(tenant_id) REFERENCES tenants(id);

ALTER TABLE flows DROP KEY `name`;
ALTER TABLE flows ADD tenant_id bigint(20) NOT NULL;
ALTER TABLE flows ADD UNIQUE (`name`, `tenant_id`);

ALTER TABLE groups ADD tenant_id BIGINT(20) NOT NULL;
ALTER TABLE groups ADD INDEX(tenant_id);

ALTER TABLE groups_users ADD tenant_id BIGINT(20) NOT NULL;
ALTER TABLE groups_users ADD INDEX(tenant_id);

ALTER TABLE audio_files ADD tenant_id BIGINT(20) NOT NULL;
ALTER TABLE audio_files ADD INDEX(tenant_id);

ALTER TABLE messages ADD tenant_id BIGINT(20) NOT NULL;
ALTER TABLE messages ADD INDEX(tenant_id);

ALTER TABLE numbers ADD tenant_id BIGINT(20) NOT NULL;
ALTER TABLE numbers ADD INDEX(tenant_id);

ALTER TABLE users ADD tenant_id BIGINT(20) NOT NULL;
ALTER TABLE users DROP KEY `email`;
ALTER TABLE users ADD UNIQUE (`email`, `tenant_id`);
ALTER TABLE users ADD INDEX(tenant_id);

ALTER TABLE user_openids ADD tenant_id BIGINT(20) NOT NULL;
ALTER TABLE user_openids ADD INDEX(tenant_id);

ALTER TABLE auth_types ADD tenant_id BIGINT(20) NOT NULL;
ALTER TABLE auth_types ADD INDEX(tenant_id);

ALTER TABLE rest_access ADD tenant_id BIGINT(20) NOT NULL;
ALTER TABLE rest_access ADD INDEX(tenant_id);

ALTER TABLE user_messages ADD tenant_id BIGINT(20) NOT NULL;
ALTER TABLE user_messages ADD INDEX(tenant_id);

ALTER TABLE group_messages ADD tenant_id BIGINT(20) NOT NULL;
ALTER TABLE group_messages ADD INDEX(tenant_id);

ALTER TABLE user_annotations ADD tenant_id BIGINT(20) NOT NULL;
ALTER TABLE user_annotations ADD INDEX(tenant_id);

ALTER TABLE group_annotations ADD tenant_id BIGINT(20) NOT NULL;
ALTER TABLE group_annotations ADD INDEX(tenant_id);

ALTER TABLE annotations ADD tenant_id BIGINT(20) NOT NULL;
ALTER TABLE annotations ADD INDEX(tenant_id);

ALTER TABLE annotation_types ADD tenant_id BIGINT(20) NOT NULL;
ALTER TABLE annotation_types ADD INDEX(tenant_id);

ALTER TABLE flow_store ADD tenant_id BIGINT(20) NOT NULL;
ALTER TABLE flow_store ADD INDEX(tenant_id);

ALTER TABLE plugin_store ADD tenant_id BIGINT(20) NOT NULL;
ALTER TABLE plugin_store ADD INDEX(tenant_id);

DROP TABLE user_labels;
DROP TABLE labels;

UPDATE flows SET tenant_id=1;
UPDATE groups SET tenant_id=1;
UPDATE groups_users SET tenant_id=1;
UPDATE audio_files SET tenant_id=1;
UPDATE messages SET tenant_id=1;
UPDATE numbers SET tenant_id=1;
UPDATE users SET tenant_id=1;
UPDATE auth_types SET tenant_id=1;
UPDATE rest_access SET tenant_id=1;
UPDATE user_messages SET tenant_id=1;
UPDATE group_messages SET tenant_id=1;
UPDATE user_annotations SET tenant_id=1;
UPDATE group_annotations SET tenant_id=1;
UPDATE annotations SET tenant_id=1;
UPDATE annotation_types SET tenant_id=1;
UPDATE user_openids SET tenant_id=1;
UPDATE flow_store SET tenant_id=1;
UPDATE plugin_store SET tenant_id=1;

ALTER TABLE flows ADD FOREIGN KEY(tenant_id) REFERENCES tenants(id);
ALTER TABLE groups ADD FOREIGN KEY(tenant_id) REFERENCES tenants(id);
ALTER TABLE groups_users ADD FOREIGN KEY(tenant_id) REFERENCES tenants(id);
ALTER TABLE audio_files ADD FOREIGN KEY(tenant_id) REFERENCES tenants(id);
ALTER TABLE messages ADD FOREIGN KEY(tenant_id) REFERENCES tenants(id);
ALTER TABLE numbers ADD FOREIGN KEY(tenant_id) REFERENCES tenants(id);
ALTER TABLE users ADD FOREIGN KEY(tenant_id) REFERENCES tenants(id);
ALTER TABLE auth_types ADD FOREIGN KEY(tenant_id) REFERENCES tenants(id);
ALTER TABLE rest_access ADD FOREIGN KEY(tenant_id) REFERENCES tenants(id);
ALTER TABLE user_messages ADD FOREIGN KEY(tenant_id) REFERENCES tenants(id);
ALTER TABLE group_messages ADD FOREIGN KEY(tenant_id) REFERENCES tenants(id);
ALTER TABLE user_annotations ADD FOREIGN KEY(tenant_id) REFERENCES tenants(id);
ALTER TABLE group_annotations ADD FOREIGN KEY(tenant_id) REFERENCES tenants(id);
ALTER TABLE annotations ADD FOREIGN KEY(tenant_id) REFERENCES tenants(id);
ALTER TABLE annotation_types ADD FOREIGN KEY(tenant_id) REFERENCES tenants(id);
ALTER TABLE user_openids ADD FOREIGN KEY(tenant_id) REFERENCES tenants(id);
ALTER TABLE flow_store ADD FOREIGN KEY(tenant_id) REFERENCES tenants(id);
ALTER TABLE plugin_store ADD FOREIGN KEY(tenant_id) REFERENCES tenants(id);
