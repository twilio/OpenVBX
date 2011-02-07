DROP TABLE IF EXISTS user_annotations;
DROP TABLE IF EXISTS group_annotations;
DROP TABLE IF EXISTS annotations;
DROP TABLE IF EXISTS annotation_types;
DROP TABLE IF EXISTS label_annotations;
DROP TABLE IF EXISTS user_messages;
DROP TABLE IF EXISTS group_messages;

UPDATE messages SET status='archive' WHERE status='archived';

CREATE TABLE IF NOT EXISTS user_messages (
	   user_id INT(11) NOT NULL,
	   message_id INT(11) NOT NULL,
	   PRIMARY KEY(user_id, message_id)
) ENGINE=InnoDB CHARSET=UTF8;

CREATE TABLE IF NOT EXISTS group_messages (
	   group_id INT(11) NOT NULL,
	   message_id INT(11) NOT NULL,
	   PRIMARY KEY(group_id, message_id)
) ENGINE=InnoDB CHARSET=UTF8;

CREATE TABLE IF NOT EXISTS annotations (
	   id BIGINT NOT NULL AUTO_INCREMENT,
	   annotation_type TINYINT NOT NULL,
	   message_id BIGINT NOT NULL,
	   user_id INT(11) NOT NULL,
	   description TEXT NOT NULL,
	   created DATETIME NOT NULL,
	   PRIMARY KEY(id),
     KEY annotation_type_message_id (annotation_type, message_id, created),
     KEY created (created)
) ENGINE=InnoDB CHARSET=UTF8;

CREATE TABLE IF NOT EXISTS annotation_types (
	   id TINYINT NOT NULL AUTO_INCREMENT,
	   description VARCHAR(32) NOT NULL,
	   PRIMARY KEY(id)
) ENGINE=InnoDB CHARSET=UTF8;

CREATE TABLE IF NOT EXISTS user_labels (
	   user_id INT(11) NOT NULL,
	   label_id INT(11) NOT NULL,
	   PRIMARY KEY(user_id, label_id)
) ENGINE=InnoDB CHARSET=UTF8;

CREATE TABLE IF NOT EXISTS labels (
	   id INT(11) NOT NULL AUTO_INCREMENT,
	   description VARCHAR(32) NOT NULL,
	   PRIMARY KEY(id)
) ENGINE=InnoDB CHARSET=UTF8;

INSERT INTO user_messages (user_id, message_id) 
	   SELECT user_id, id as message_id FROM messages where user_id != 0;

INSERT INTO group_messages (group_id, message_id) 
	   SELECT group_id, id as message_id FROM messages where group_id != 0;

INSERT INTO annotation_types (description) 
	   VALUES 
	   ('called'),
	   ('read'),
	   ('noted'),
	   ('changed'),
	   ('archived');

UPDATE messages SET status='archived' WHERE status='archive';

INSERT INTO annotations (annotation_type, message_id, user_id, description, created)
SELECT	
  annotation_types.id, messages.id, messages.user_id,
  '', messages.updated
FROM messages 
JOIN annotation_types ON messages.status = annotation_types.description;

-- SELECT * FROM annotations 
-- JOIN messages m ON m.id = annotations.message_id
-- JOIN annotation_types at ON at.id = annotations.annotation_type


-- RIGHT JOIN user_annotations ua ON ua.annotation_id = a.id RIGHT JOIN users u ON ua.user_id = u.id

-- SELECT count(*) FROM annotations 
-- JOIN messages m ON m.id = annotations.message_id
-- JOIN annotation_types at ON at.id = annotations.annotation_type where at.description not in ('read', 'archived');
