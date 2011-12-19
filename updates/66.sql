# Version is now stored in `OpenVBX/config/version.php`
DELETE FROM `settings` WHERE `name` = 'version';
UPDATE `settings` SET `value` = 66 WHERE `name` = 'schema-version';