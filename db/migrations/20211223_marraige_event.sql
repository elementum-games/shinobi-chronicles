START TRANSACTION;

ALTER TABLE `users`
    ADD COLUMN `spouse` INT DEFAULT 0,
    ADD COLUMN `marriage_time` INT DEFAULT 0,
    ADD COLUMN `missions_completed` VARCHAR(256);

COMMIT;
