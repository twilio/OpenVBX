CREATE TABLE `auth_types` (
  `id` tinyint NOT NULL AUTO_INCREMENT,
  `description` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB CHARSET=UTF8;

INSERT INTO auth_types (description)
	   VALUES
	   ('openvbx'),
	   ('google');

ALTER TABLE users ADD COLUMN `auth_type` TINYINT NOT NULL default 1;

ALTER TABLE users ADD FOREIGN KEY(auth_type) REFERENCES auth_types(id);

ALTER TABLE groups_users ADD FOREIGN KEY(user_id) REFERENCES users(id);
ALTER TABLE groups_users ADD FOREIGN KEY(group_id) REFERENCES groups(id);

ALTER TABLE groups add column is_active tinyint NOT NULL DEFAULT 1;