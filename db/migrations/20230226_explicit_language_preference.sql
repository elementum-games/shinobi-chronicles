START TRANSACTION;

ALTER TABLE users ADD COLUMN censor_explicit_language tinyint(1) default 1;

UPDATE `system_storage` SET `database_version`='explicit_language_preference' LIMIT 1;

COMMIT;