START TRANSACTION;

alter table `system_storage` add column `time` varchar(64) after `global_message`;

UPDATE `system_storage` SET `database_version`='20210926_2300' LIMIT 1;

COMMIT;