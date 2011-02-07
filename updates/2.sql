CREATE TABLE IF NOT EXISTS `user_openids` (
 `openid_url` varchar(255) NOT NULL,
 `user_id` int(11) NOT NULL,
 PRIMARY KEY  (`openid_url`),
 KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=UTF8;