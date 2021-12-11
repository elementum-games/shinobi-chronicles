START TRANSACTION;

ALTER TABLE `battles` ADD COLUMN `turn_type` VARCHAR(128) not null AFTER `turn_count`;

UPDATE `system_storage` SET `database_version`='20211210_2301' LIMIT 1;

COMMIT;