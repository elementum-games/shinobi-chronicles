START TRANSACTION;

ALTER TABLE `users`
    ADD COLUMN `spouse` INT DEFAULT 0,
    ADD COLUMN `marriage_time` INT DEFAULT 0,
    ADD COLUMN `missions_completed` VARCHAR(256),
    ADD COLUMN `presents_claimed` VARCHAR(100);

ALTER TABLE `items`
    ADD COLUMN `description` VARCHAR(150) AFTER `name`;

COMMIT;
