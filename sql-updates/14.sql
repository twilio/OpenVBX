DROP TABLE audio;

CREATE TABLE audio_files (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `label` varchar(255) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `local_file` varchar(255) DEFAULT NULL,
  `remote_url` varchar(255) DEFAULT NULL,
  `recording_call_sid` varchar(100) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX(`user_id`),
  INDEX(`local_file`),
  INDEX(`remote_url`),
  INDEX(`recording_call_sid`)
) ENGINE=InnoDB DEFAULT CHARSET=UTF8;
