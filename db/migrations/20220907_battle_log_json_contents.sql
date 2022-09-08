START TRANSACTION;

alter table `battle_logs` add `fighter_action_logs` json null after content;

UPDATE `system_storage` SET `database_version`='20220907_battle_log_json_contents' LIMIT 1;

COMMIT;