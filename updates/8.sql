ALTER TABLE messages ADD COLUMN assigned_to BIGINT NULL;
ALTER TABLE messages ADD INDEX(assigned_to);

ALTER TABLE messages ADD COLUMN  `ticket_status` ENUM('open', 'closed', 'pending') NOT NULL DEFAULT 'open';
ALTER TABLE messages ADD COLUMN archived TINYINT NOT NULL DEFAULT 0;
ALTER TABLE messages ADD KEY `archived` (`archived`);
ALTER TABLE messages ADD KEY `status` (`status`);

ALTER TABLE numbers ADD UNIQUE (`user_id`, `value`);

ALTER TABLE installs ADD COLUMN active TINYINT NOT NULL DEFAULT 1;

INSERT INTO installs 
	   (name, url_prefix, local_prefix) 
	   VALUES 
	   ('default', '', '');

INSERT INTO settings 
	   (name, value, install_id)
	   VALUES
	   ('twilio_sid', '', 1),
	   ('twilio_token', '', 1),
	   ('from_email' , '', 1),
	   ('dash_rss', '', 1),
	   ('theme', '', 1);
