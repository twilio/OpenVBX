drop table if exists rest_access;

create table rest_access ( 
	   `key` VARCHAR(32) NOT NULL,
	   `locked` TINYINT NOT NULL DEFAULT 0, 
	   `created` DATETIME NOT NULL, 
	   `user_id` INT NOT NULL,
	   PRIMARY KEY (`key`)
);