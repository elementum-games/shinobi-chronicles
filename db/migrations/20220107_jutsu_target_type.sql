START TRANSACTION;

alter table `jutsu` add column `target_type` varchar(64) after `use_type`;

UPDATE `system_storage` SET `database_version`='20220107_jutsu_target_type' LIMIT 1;

COMMIT;