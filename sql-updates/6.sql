ALTER TABLE users add column invite_code varchar(32) NULL after password;
ALTER TABLE messages add column called varchar(20) NULL after caller;

ALTER TABLE users modify column extension bigint NOT NULL;