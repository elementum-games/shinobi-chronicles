START TRANSACTION;

alter table `battle_logs` add `turn_phase` varchar(128) default 'movement' not null;

UPDATE `system_storage` SET `database_version`='20220924' LIMIT 1;

COMMIT;