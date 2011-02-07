ALTER TABLE `audio`
ADD COLUMN `removed` TINYINT NOT NULL DEFAULT 0;

ALTER TABLE `audio`
ADD INDEX `idx_removed` (`removed`);