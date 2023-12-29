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
            INSERT INTO `ai_opponents` (`rank`, `money`, `name`, `max_health`, `level`, `ninjutsu_skill`, `genjutsu_skill`, `taijutsu_skill`, `cast_speed`, `speed`, `strength`, `intelligence`, `willpower`, `moves`, `shop_jutsu`, `shop_jutsu_priority`, `battle_iq`, `scaling`, `difficulty_level`, `arena_enabled`, `is_patrol`, `avatar_link`) VALUES
            (2, 150, 'Rival Genin', 1.00, 11, 0.00, 0.00, 0.60, 0.00, 0.30, 0.00, 0.00, 0.00, '[]', '4, 20, 19, 28, 23, 16', '16', 50, 1, 'normal', 1, 0, 'images/ai_avatars/TaijutsuRivalGenin.png'),
            (2, 150, 'Rival Genin', 1.00, 11, 0.60, 0.00, 0.00, 0.30, 0.00, 0.00, 0.00, 0.00, '[]', '4, 15, 18, 7, 17', '17', 50, 1, 'normal', 1, 0, 'images/ai_avatars/NinjutsuRivalGenin.png'),
            (2, 150, 'Rival Genin', 1.00, 11, 0.00, 0.60, 0.00, 0.30, 0.00, 0.00, 0.00, 0.00, '[]', '4, 21, 27, 25, 22', '22', 50, 1, 'normal', 1, 0, 'images/ai_avatars/GenjutsuRivalGenin.png'),
            (2, 0, 'Training Dummy', 1.00, 11, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, '[{\"name\":\"\",\"battle_text\":\"[opponent] stares blankly at [player], remaining upright in spite of the pummeling it takes all too happily.\",\"power\":\"1\",\"cooldown\":\"0\",\"jutsu_type\":\"taijutsu\",\"use_type\":\"indirect\",\"effect\":\"none\",\"effect_amount\":\"0\",\"effect_length\":\"0\",\"effect2\":\"none\",\"effect2_amount\":\"0\",\"effect2_length\":\"0\"}]', '', '', 0, 1, 'easy', 1, 0, 'images/ai_avatars/TrainingDummy.png'),
            (2, 300, 'Academy Instructor', 1.00, 25, 0.60, 0.60, 0.60, 0.40, 0.40, 0.00, 0.00, 0.00, '[{\"name\":\"Instruction\",\"battle_text\":\"[player] lands a direct hit, their triumph short-lived when [opponent] appears a short distance away having swapped places with a simple log. As [player] resumes the attack however, they&#039;re easily tripped from behind when the &quot;log&quot; transforms back into [Academy Instructor].\",\"power\":\"2\",\"cooldown\":\"6\",\"jutsu_type\":\"ninjutsu\",\"use_type\":\"projectile\",\"effect\":\"counter\",\"effect_amount\":\"42\",\"effect_length\":\"0\",\"effect2\":\"none\",\"effect2_amount\":\"0\",\"effect2_length\":\"0\"},{\"name\":\"Lecture\",\"battle_text\":\"[opponent] effortlessly dodges and parries [player]&#039;s attack, exposing each flaw in their technique with a sigh of disappointment.\",\"power\":\"2\",\"cooldown\":\"2\",\"jutsu_type\":\"genjutsu\",\"use_type\":\"physical\",\"effect\":\"offense_nerf\",\"effect_amount\":\"91\",\"effect_length\":\"1\",\"effect2\":\"none\",\"effect2_amount\":\"0\",\"effect2_length\":\"0\"},{\"name\":\"Pop Quiz\",\"battle_text\":\"[opponent]`s demeanor shifts suddenly, in an instant staggering [player] back with a lightning-fast kick that [player] is barely able to block, pushing their perception and reflexes to the limit.\",\"power\":\"2.8\",\"cooldown\":\"0\",\"jutsu_type\":\"taijutsu\",\"use_type\":\"physical\",\"effect\":\"none\",\"effect_amount\":\"0\",\"effect_length\":\"0\",\"effect2\":\"none\",\"effect2_amount\":\"0\",\"effect2_length\":\"0\"}]', '4', '', 0, 1, 'hard', 1, 0, 'images/ai_avatars/AcademyInstructor.png');

            -- Update table ai_opponents - Chuunin Arena
            INSERT INTO `ai_opponents` (`rank`, `money`, `name`, `max_health`, `level`, `ninjutsu_skill`, `genjutsu_skill`, `taijutsu_skill`, `cast_speed`, `speed`, `strength`, `intelligence`, `willpower`, `moves`, `shop_jutsu`, `shop_jutsu_priority`, `battle_iq`, `scaling`, `difficulty_level`, `arena_enabled`, `is_patrol`, `avatar_link`) VALUES
            (3, 300, 'Rival Chuunin', 1.00, 26, 0.00, 0.00, 0.65, 0.00, 0.35, 0.00, 0.00, 0.00, '[]', '4, 52, 43, 57, 50, 51, 44, 59, 201', '59, 201, 44', 50, 1, 'normal', 1, 0, 'images/ai_avatars/TaijutsuRivalChuunin.png'),
            (3, 300, 'Rival Chuunin', 1.00, 26, 0.65, 0.00, 0.00, 0.35, 0.00, 0.00, 0.00, 0.00, '[]', '4, 35, 54, 34, 36, 60, 33, 41', '60, 41', 50, 1, 'normal', 1, 0, 'images/ai_avatars/NinjutsuRivalChuunin.png'),
            (3, 300, 'Rival Chuunin', 1.00, 26, 0.00, 0.65, 0.00, 0.35, 0.00, 0.00, 0.00, 0.00, '[]', '4, 47, 56, 45, 48, 210, 53, 61', '61, 53', 50, 1, 'normal', 1, 0, 'images/ai_avatars/GenjutsuRivalChuunin.png'),
            (3, 0, 'Training Dummy', 1.00, 26, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, '[{\"name\":\"\",\"battle_text\":\"[opponent] stares blankly at [player], remaining upright in spite of the pummeling it takes all too happily.\",\"power\":\"1\",\"cooldown\":\"0\",\"jutsu_type\":\"taijutsu\",\"use_type\":\"indirect\",\"effect\":\"none\",\"effect_amount\":\"0\",\"effect_length\":\"0\",\"effect2\":\"none\",\"effect2_amount\":\"0\",\"effect2_length\":\"0\"}]', '', '', 0, 1, 'easy', 1, 0, 'images/ai_avatars/TrainingDummy.png'),
            (3, 600, 'Bloodline Mystic', 1.00, 50, 0.65, 0.65, 0.65, 0.40, 0.40, 0.00, 0.00, 0.00, '[{\"name\":\"Into the Mirror World\",\"battle_text\":\"Shimmering fragments trail [opponent]&#039;s hands as they open a reflective portal before them, swirling with iridescent colors. [player] is momentarily dazzled as they&#039;re drawn into the mirror realm, a twisted reflected of reality where [opponent]&#039;s strength is amplified as [player]&#039;s chakra is slowly drained by the countless mirrors that surround them.\",\"power\":\"4\",\"cooldown\":\"4\",\"jutsu_type\":\"ninjutsu\",\"use_type\":\"indirect\",\"effect\":\"offense_nerf\",\"effect_amount\":\"42\",\"effect_length\":\"2\",\"effect2\":\"ninjutsu_boost\",\"effect2_amount\":\"83\",\"effect2_length\":\"2\"},{\"name\":\"Self Reflection\",\"battle_text\":\"[opponent] conjures an ethereal mirror which flawlessly reflects [player]&#039;s attack, fracturing into a thousand shards that scatter violently. From the glittering light forms an identical copy of [player], mirroring their every move and blurring the lines between reality and illusion.\",\"power\":\"2.5\",\"cooldown\":\"4\",\"jutsu_type\":\"ninjutsu\",\"use_type\":\"indirect\",\"effect\":\"reflect\",\"effect_amount\":\"83\",\"effect_length\":\"1\",\"effect2\":\"none\",\"effect2_amount\":\"0\",\"effect2_length\":\"0\"},{\"name\":\"Ethereal Shards\",\"battle_text\":\"[opponent] snaps their fingers, causing the air to fracture and ripple. With a flourish, the distortion shatters into countless ethereal shards, hanging momentarily in the air before surging toward [player] with unerring precision.\",\"power\":\"2\",\"cooldown\":\"0\",\"jutsu_type\":\"ninjutsu\",\"use_type\":\"projectile\",\"effect\":\"residual_damage\",\"effect_amount\":\"83\",\"effect_length\":\"1\",\"effect2\":\"none\",\"effect2_amount\":\"0\",\"effect2_length\":\"0\"}]', '', '', 50, 1, 'hard', 1, 0, 'images/ai_avatars/BloodlineMystic.png');

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
