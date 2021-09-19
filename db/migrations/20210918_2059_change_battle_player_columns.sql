START TRANSACTION;

alter table `battles` modify column `player1` varchar(64) not null;
alter table `battles` modify column `player2` varchar(64) not null;
alter table `battles` modify column `battle_text` text;
alter table `battles` modify column `winner` varchar(16);
alter table `battles` add column `player1_health` float default 0.0 after player2;
alter table `battles` add column `player2_health` float default 0.0 after player1_health;

UPDATE `system_storage` SET `database_version`='20210918_2059' LIMIT 1;

COMMIT;