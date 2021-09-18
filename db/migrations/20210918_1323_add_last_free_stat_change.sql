START TRANSACTION;

    alter table `users` add column `last_free_stat_change` int after `last_ai`;

    alter table `system_storage` add column `database_version` varchar(128);

    UPDATE `system_storage` SET `database_version`='20210918_1323' LIMIT 1;

COMMIT;