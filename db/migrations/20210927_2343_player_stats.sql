START TRANSACTION;

CREATE TABLE `player_logs` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `user_name` VARCHAR(128),
    `log_type` VARCHAR(128),
    `log_time` DATETIME(2),
    `log_contents` VARCHAR(500)
);

UPDATE `system_storage` SET `database_version`='20210927_2343' LIMIT 1;

COMMIT;