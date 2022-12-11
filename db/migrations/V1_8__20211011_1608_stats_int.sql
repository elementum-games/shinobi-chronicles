START TRANSACTION;

ALTER TABLE `users`modify `cast_speed` int not null;
ALTER TABLE `users`modify `speed` int not null;
ALTER TABLE `users`modify `intelligence` int not null;
ALTER TABLE `users`modify `willpower` int not null;

UPDATE `system_storage` SET `database_version`='20211011_1608' LIMIT 1;

COMMIT;