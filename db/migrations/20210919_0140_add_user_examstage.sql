START TRANSACTION;

alter table users add column `exam_stage` SMALLINT DEFAULT 0 after `mission_stage`;

UPDATE `system_storage` SET `database_version`='20210919_0140' LIMIT 1;

COMMIT;