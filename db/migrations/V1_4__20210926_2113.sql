START TRANSACTION;

ALTER TABLE `ai_opponents` modify `max_health` float(12,2);

UPDATE `ai_opponents` SET
    `max_health` = 0.6,
    `ninjutsu_skill` = 0.7,
    `genjutsu_skill` = 0.7,
    `taijutsu_skill` = 0.7,
    `cast_speed` = 0.25,
    `speed` = 0.25,
    `strength` = 0.01,
    `endurance` = 0.01,
    `intelligence` = 0.1,
    `willpower` = 0.05 where 0=0;

UPDATE `system_storage` SET `database_version`='20210926_2113' LIMIT 1;

COMMIT;