<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AIUpdateMigration extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        $this->execute("
            -- Alter table ai_opponents
            ALTER TABLE `ai_opponents` ADD `shop_jutsu` VARCHAR(100) NOT NULL DEFAULT '0';
            ALTER TABLE `ai_opponents` ADD `shop_jutsu_priority` VARCHAR(100) NOT NULL DEFAULT '0';
            ALTER TABLE `ai_opponents` ADD `battle_iq` INT(11) NOT NULL DEFAULT '0';
            ALTER TABLE `ai_opponents` ADD `scaling` TINYINT(1) NOT NULL DEFAULT '0';
            ALTER TABLE `ai_opponents` ADD `difficulty_level` VARCHAR(20) NOT NULL DEFAULT 'none',
            ALTER TABLE `ai_opponents` ADD `arena_enabled` TINYINT(1) NOT NULL DEFAULT '0';
            ALTER TABLE `ai_opponents` ADD `is_patrol` TINYINT(1) NOT NULL DEFAULT '0';
            ALTER TABLE `ai_opponents` ADD `avatar_link` VARCHAR(100) NOT NULL;

            -- Alter table users
            ALTER TABLE `users` ADD `ai_cooldowns` VARCHAR(200) NOT NULL DEFAULT '[]';

            -- Alter table battles
            ALTER TABLE `battles` ADD `battle_background_link` VARCHAR(100) NOT NULL;

            -- Alter table regions
            ALTER TABLE `regions` ADD `battle_background_link` VARCHAR(100) NOT NULL;

            -- Alter table maps_locations
            ALTER TABLE `maps_locations` ADD `battle_background_link` VARCHAR(100) NOT NULL;

            -- Update table regions
            UPDATE `regions` SET `battle_background_link` = '/images/battle_backgrounds/LowMountainpath.jpg' WHERE `regions`.`region_id` = 1;
            UPDATE `regions` SET `battle_background_link` = '/images/battle_backgrounds/HighMountainpath.jpg' WHERE `regions`.`region_id` = 2;
            UPDATE `regions` SET `battle_background_link` = '/images/battle_backgrounds/Forest.jpg' WHERE `regions`.`region_id` = 3;
            UPDATE `regions` SET `battle_background_link` = '/images/battle_backgrounds/RockyPlains.jpg' WHERE `regions`.`region_id` = 4;
            UPDATE `regions` SET `battle_background_link` = '/images/battle_backgrounds/Shoreline.jpg' WHERE `regions`.`region_id` = 5;
            UPDATE `regions` SET `battle_background_link` = '/images/battle_backgrounds/LowMountainpath.jpg' WHERE `regions`.`region_id` = 6;
            UPDATE `regions` SET `battle_background_link` = '/images/battle_backgrounds/RockyPlains.jpg' WHERE `regions`.`region_id` = 7;
            UPDATE `regions` SET `battle_background_link` = '/images/battle_backgrounds/Canyon.jpg' WHERE `regions`.`region_id` = 8;
            UPDATE `regions` SET `battle_background_link` = '/images/battle_backgrounds/Shoreline.jpg' WHERE `regions`.`region_id` = 9;
            UPDATE `regions` SET `battle_background_link` = '/images/battle_backgrounds/EastPlains.jpg' WHERE `regions`.`region_id` = 10;
            UPDATE `regions` SET `battle_background_link` = '/images/battle_backgrounds/HighMountainpath.jpg' WHERE `regions`.`region_id` = 11;
            UPDATE `regions` SET `battle_background_link` = '/images/battle_backgrounds/Forest.jpg' WHERE `regions`.`region_id` = 12;
            UPDATE `regions` SET `battle_background_link` = '/images/battle_backgrounds/EastPlains.jpg' WHERE `regions`.`region_id` = 13;
            UPDATE `regions` SET `battle_background_link` = '/images/battle_backgrounds/Forest.jpg' WHERE `regions`.`region_id` = 14;
            UPDATE `regions` SET `battle_background_link` = '/images/battle_backgrounds/Canyon.jpg' WHERE `regions`.`region_id` = 15;
            UPDATE `regions` SET `battle_background_link` = '/images/battle_backgrounds/RockyPlains.jpg' WHERE `regions`.`region_id` = 16;
            UPDATE `regions` SET `battle_background_link` = '/images/battle_backgrounds/Shoreline.jpg' WHERE `regions`.`region_id` = 17;
            UPDATE `regions` SET `battle_background_link` = '/images/battle_backgrounds/Shoreline.jpg' WHERE `regions`.`region_id` = 18;
            UPDATE `regions` SET `battle_background_link` = '/images/battle_backgrounds/Shoreline.jpg' WHERE `regions`.`region_id` = 19;
            UPDATE `regions` SET `battle_background_link` = '/images/battle_backgrounds/Shoreline.jpg' WHERE `regions`.`region_id` = 20;

            -- Update table ai_opponents - Academy Arena
            INSERT INTO `ai_opponents` (`rank`, `money`, `name`, `max_health`, `level`, `ninjutsu_skill`, `genjutsu_skill`, `taijutsu_skill`, `cast_speed`, `speed`, `strength`, `intelligence`, `willpower`, `moves`, `shop_jutsu`, `shop_jutsu_priority`, `battle_iq`, `scaling`, `difficulty_level`, `arena_enabled`, `is_patrol`, `avatar_link`) VALUES
            (1, 100, 'Prodigy Student', 1.00, 10, 0.45, 0.45, 0.45, 0.25, 0.25, 0.01, 0.00, 0.00, '[{\"name\":\"Disorienting Strike\",\"battle_text\":\"[opponent] creates multiple clones that disorient [player] before they&#039;re struck from behind, knocking them to the ground as [opponent] leaps in the air!\",\"power\":\"2\",\"cooldown\":\"8\",\"jutsu_type\":\"taijutsu\",\"use_type\":\"physical\",\"effect\":\"vulnerability\",\"effect_amount\":\"42\",\"effect_length\":\"1\",\"effect2\":\"none\",\"effect2_amount\":\"0\",\"effect2_length\":\"0\"},{\"name\":\"Headsplitter\",\"battle_text\":\"[opponent] leaps down from above and slams their foot into [player]&#039;s head, the impact leaving a small crack in the floor.\",\"power\":\"1.9\",\"cooldown\":\"8\",\"jutsu_type\":\"taijutsu\",\"use_type\":\"physical\",\"effect\":\"none\",\"effect_amount\":\"0\",\"effect_length\":\"0\",\"effect2\":\"none\",\"effect2_amount\":\"0\",\"effect2_length\":\"0\"}]', '4, 14, 87', '0', 50, 1, 'hard', 1, 0, 'images/ai_avatars/ProdigyStudent.png'),
            (2, 150, 'Rival Genin', 1.00, 11, 0.00, 0.00, 0.55, 0.00, 0.25, 0.00, 0.00, 0.00, '[]', '4, 20, 19, 28, 23, 16', '16', 50, 1, 'normal', 1, 0, 'images/ai_avatars/TaijutsuRivalGenin.png'),
            (2, 150, 'Rival Genin', 1.00, 11, 0.55, 0.00, 0.00, 0.25, 0.00, 0.00, 0.00, 0.00, '[]', '4, 15, 18, 7, 17', '', 50, 1, 'normal', 1, 0, 'images/ai_avatars/NinjutsuRivalGenin.png'),
            (2, 150, 'Rival Genin', 1.00, 11, 0.00, 0.55, 0.00, 0.25, 0.00, 0.00, 0.00, 0.00, '[]', '4, 21, 27, 25, 22', '22', 50, 1, 'normal', 1, 0, 'images/ai_avatars/GenjutsuRivalGenin.png'),
            (1, 0, 'Training Dummy', 1.00, 1, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, '[{\"name\":\"\",\"battle_text\":\"[opponent] stares blankly at [player], remaining upright in spite of the pummeling it takes all too happily.\",\"power\":\"1\",\"cooldown\":\"0\",\"jutsu_type\":\"taijutsu\",\"use_type\":\"indirect\",\"effect\":\"none\",\"effect_amount\":\"0\",\"effect_length\":\"0\",\"effect2\":\"none\",\"effect2_amount\":\"0\",\"effect2_length\":\"0\"}]', '', '', 0, 1, 'easy', 1, 0, 'images/ai_avatars/TrainingDummy.png');

            -- Update table ai_opponents - Genin Arena

            -- Update table ai_opponents - Chuunin Arena

            -- Update table ai_opponents - Jonin Arena

            -- Update table ai_opponents - Patrols
        ");
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        $this->execute("
            -- Alter table ai_opponents
            ALTER TABLE `ai_opponents` DROP `shop_jutsu`;
            ALTER TABLE `ai_opponents` DROP `shop_jutsu_priority`;
            ALTER TABLE `ai_opponents` DROP `battle_iq`;
            ALTER TABLE `ai_opponents` DROP `scaling`;
            ALTER TABLE `ai_opponents` DROP `difficulty_level`,
            ALTER TABLE `ai_opponents` DROP `arena_enabled`;
            ALTER TABLE `ai_opponents` DROP `is_patrol`;
            ALTER TABLE `ai_opponents` DROP `avatar_link`;

            -- Alter table users
            ALTER TABLE `users` DROP `ai_cooldowns`;

            -- Alter table battles
            ALTER TABLE `battles` DROP `battle_background_link`;

            -- Alter table regions
            ALTER TABLE `regions` DROP `battle_background_link`;

            -- Alter table maps_locations
            ALTER TABLE `maps_locations` DROP `battle_background_link`;
        ");
    }
}
