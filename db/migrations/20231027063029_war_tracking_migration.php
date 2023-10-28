<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class WarTrackingMigration extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        $this->execute("
            -- Add table player_war_logs
            CREATE TABLE `player_war_logs` (
            `log_id` INT(11) NOT NULL AUTO_INCREMENT,
            `user_id` INT(11) NOT NULL,
            `village_id` INT(11) NOT NULL,
            `relation_id` INT(11) NULL,
            `infiltrate_count` INT(11) NOT NULL DEFAULT 0,
            `reinforce_count` INT(11) NOT NULL DEFAULT 0,
            `raid_count` INT(11) NOT NULL DEFAULT 0,
            `loot_count` INT(11) NOT NULL DEFAULT 0,
            `damage_dealt` INT(11) NOT NULL DEFAULT 0,
            `damage_healed` INT(11) NOT NULL DEFAULT 0,
            `defense_gained` INT(11) NOT NULL DEFAULT 0,
            `defense_reduced` INT(11) NOT NULL DEFAULT 0,
            `resources_stolen` INT(11) NOT NULL DEFAULT 0,
            `resources_claimed` INT(11) NOT NULL DEFAULT 0,
            `patrols_defeated` INT(11) NOT NULL DEFAULT 0,
            `regions_captured` INT(11) NOT NULL DEFAULT 0,
            `pvp_wins` INT(11) NOT NULL DEFAULT 0,
            `points_gained` INT(11) NOT NULL DEFAULT 0,
            PRIMARY KEY (`log_id`));

            -- Add table village_war_logs
            CREATE TABLE `village_war_logs` (
            `log_id` INT(11) NOT NULL AUTO_INCREMENT,
            `village_id` INT(11) NOT NULL,
            `relation_id` INT(11) NULL,
            `infiltrate_count` INT(11) NOT NULL DEFAULT 0,
            `reinforce_count` INT(11) NOT NULL DEFAULT 0,
            `raid_count` INT(11) NOT NULL DEFAULT 0,
            `loot_count` INT(11) NOT NULL DEFAULT 0,
            `damage_dealt` INT(11) NOT NULL DEFAULT 0,
            `damage_healed` INT(11) NOT NULL DEFAULT 0,
            `defense_gained` INT(11) NOT NULL DEFAULT 0,
            `defense_reduced` INT(11) NOT NULL DEFAULT 0,
            `resources_stolen` INT(11) NOT NULL DEFAULT 0,
            `resources_claimed` INT(11) NOT NULL DEFAULT 0,
            `patrols_defeated` INT(11) NOT NULL DEFAULT 0,
            `regions_captured` INT(11) NOT NULL DEFAULT 0,
            `pvp_wins` INT(11) NOT NULL DEFAULT 0,
            `points_gained` INT(11) NOT NULL DEFAULT 0,
            PRIMARY KEY (`log_id`));

            -- Add table region_logs
            CREATE TABLE `region_logs` (
            `log_id` INT(11) NOT NULL AUTO_INCREMENT,
            `region_id` INT(11) NOT NULL,
            `previous_village_id` INT(11) NOT NULL,
            `new_village_id` INT(11) NOT NULL,
            `user_id` INT(11) NOT NULL,
            `capture_time` INT(11) NOT NULL,
            `relation_id` INT(11) NOT NULL,
            PRIMARY KEY (`log_id`));

            -- Clear patrols
            DELETE FROM `patrols`;

            -- Insert patrols
            INSERT INTO `patrols` (`id`, `start_time`, `travel_time`, `travel_interval`, `region_id`, `village_id`, `name`, `ai_id`, `tier`) VALUES (NULL, '1698454683', NULL, '2000 ', '6', '1', 'Patrol', '8', '1');
            INSERT INTO `patrols` (`id`, `start_time`, `travel_time`, `travel_interval`, `region_id`, `village_id`, `name`, `ai_id`, `tier`) VALUES (NULL, '1698454683', NULL, '2000 ', '7', '1', 'Patrol', '8', '1');
            INSERT INTO `patrols` (`id`, `start_time`, `travel_time`, `travel_interval`, `region_id`, `village_id`, `name`, `ai_id`, `tier`) VALUES (NULL, '1698454683', NULL, '2000 ', '8', '1', 'Patrol', '8', '1');
            INSERT INTO `patrols` (`id`, `start_time`, `travel_time`, `travel_interval`, `region_id`, `village_id`, `name`, `ai_id`, `tier`) VALUES (NULL, '1698454683', NULL, '2000 ', '9', '2', 'Patrol', '8', '1');
            INSERT INTO `patrols` (`id`, `start_time`, `travel_time`, `travel_interval`, `region_id`, `village_id`, `name`, `ai_id`, `tier`) VALUES (NULL, '1698454683', NULL, '2000 ', '10', '2', 'Patrol', '8', '1');
            INSERT INTO `patrols` (`id`, `start_time`, `travel_time`, `travel_interval`, `region_id`, `village_id`, `name`, `ai_id`, `tier`) VALUES (NULL, '1698454683', NULL, '2000 ', '11', '2', 'Patrol', '8', '1');
            INSERT INTO `patrols` (`id`, `start_time`, `travel_time`, `travel_interval`, `region_id`, `village_id`, `name`, `ai_id`, `tier`) VALUES (NULL, '1698454683', NULL, '2000 ', '12', '3', 'Patrol', '8', '1');
            INSERT INTO `patrols` (`id`, `start_time`, `travel_time`, `travel_interval`, `region_id`, `village_id`, `name`, `ai_id`, `tier`) VALUES (NULL, '1698454683', NULL, '2000 ', '13', '3', 'Patrol', '8', '1');
            INSERT INTO `patrols` (`id`, `start_time`, `travel_time`, `travel_interval`, `region_id`, `village_id`, `name`, `ai_id`, `tier`) VALUES (NULL, '1698454683', NULL, '2000 ', '14', '3', 'Patrol', '8', '1');
            INSERT INTO `patrols` (`id`, `start_time`, `travel_time`, `travel_interval`, `region_id`, `village_id`, `name`, `ai_id`, `tier`) VALUES (NULL, '1698454683', NULL, '2000 ', '15', '4', 'Patrol', '8', '1');
            INSERT INTO `patrols` (`id`, `start_time`, `travel_time`, `travel_interval`, `region_id`, `village_id`, `name`, `ai_id`, `tier`) VALUES (NULL, '1698454683', NULL, '2000 ', '16', '4', 'Patrol', '8', '1');
            INSERT INTO `patrols` (`id`, `start_time`, `travel_time`, `travel_interval`, `region_id`, `village_id`, `name`, `ai_id`, `tier`) VALUES (NULL, '1698454683', NULL, '2000 ', '17', '4', 'Patrol', '8', '1');
            INSERT INTO `patrols` (`id`, `start_time`, `travel_time`, `travel_interval`, `region_id`, `village_id`, `name`, `ai_id`, `tier`) VALUES (NULL, '1698454683', NULL, '2000 ', '18', '5', 'Patrol', '8', '1');
            INSERT INTO `patrols` (`id`, `start_time`, `travel_time`, `travel_interval`, `region_id`, `village_id`, `name`, `ai_id`, `tier`) VALUES (NULL, '1698454683', NULL, '2000 ', '19', '5', 'Patrol', '8', '1');
            INSERT INTO `patrols` (`id`, `start_time`, `travel_time`, `travel_interval`, `region_id`, `village_id`, `name`, `ai_id`, `tier`) VALUES (NULL, '1698454683', NULL, '2000 ', '20', '5', 'Patrol', '8', '1');

            UPDATE `regions` SET `name` = 'Verdant Plateau' WHERE `region_id` = 1;
            UPDATE `regions` SET `name` = 'Stormveil Peaks' WHERE `region_id` = 2;
            UPDATE `regions` SET `name` = 'Ashen Forest' WHERE `region_id` = 3;
            UPDATE `regions` SET `name` = 'Windswept Barrens' WHERE `region_id` = 4;
            UPDATE `regions` SET `name` = 'Shrouded Isles' WHERE `region_id` = 5;
            UPDATE `regions` SET `name` = 'Stillwind Shore' WHERE `region_id` = 6;
            UPDATE `regions` SET `name` = 'Gradient Plains' WHERE `region_id` = 7;
            UPDATE `regions` SET `name` = 'Fortune''s Pass' WhERE `region_id` = 8;
            UPDATE `regions` SET `name` = 'Cerulean Cliffs' WHERE `region_id` = 9;
            UPDATE `regions` SET `name` = 'Royal Valley' WHERE `region_id` = 10;
            UPDATE `regions` SET `name` = 'World''s End' WHERE `region_id` = 11;
            UPDATE `regions` SET `name` = 'Whispering Woods' WHERE `region_id` = 12;
            UPDATE `regions` SET `name` = 'Eternal Plains' WHERE `region_id` = 13;
            UPDATE `regions` SET `name` = 'Ancient''s Woodland' WHERE `region_id` = 14;
            UPDATE `regions` SET `name` = 'Shimmering Sands' WHERE `region_id` = 15;
            UPDATE `regions` SET `name` = 'Hero''s Steppe' WHERE `region_id` = 16;
            UPDATE `regions` SET `name` = 'Fighter''s Bay' WHERE `region_id` = 17;
            UPDATE `regions` SET `name` = 'Silent Depths' WHERE `region_id` = 18;
            UPDATE `regions` SET `name` = 'Crescent Bay' WHERE `region_id` = 19;
            UPDATE `regions` SET `name` = 'Heaven''s Reach' WHERE `region_id` = 20;
        ");
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        $this->execute("
            -- Drop table player_war_logs
            DROP TABLE `player_war_logs`;

            -- Drop table village_war_logs
            DROP TABLE `village_war_logs`;

            -- Drop table region_logs
            DROP TABLE `region_logs`;

            -- Alter table regions
            UPDATE `regions` SET `name` = 'Stone' WHERE `region_id` = 1;
            UPDATE `regions` SET `name` = 'Cloud' WHERE `region_id` = 2;
            UPDATE `regions` SET `name` = 'Leaf' WHERE `region_id` = 3;
            UPDATE `regions` SET `name` = 'Sand' WHERE `region_id` = 4;
            UPDATE `regions` SET `name` = 'Mist' WHERE `region_id` = 5;
            UPDATE `regions` SET `name` = 'Stone East' WHERE `region_id` = 6;
            UPDATE `regions` SET `name` = 'Stone Southeast' WHERE `region_id` = 7;
            UPDATE `regions` SET `name` = 'Stone South' WHERE `region_id` = 8;
            UPDATE `regions` SET `name` = 'Cloud South' WHERE `region_id` = 9;
            UPDATE `regions` SET `name` = 'Could Southwest' WHERE `region_id` = 10;
            UPDATE `regions` SET `name` = 'Cloud West' WHERE `region_id` = 11;
            UPDATE `regions` SET `name` = 'Leaf North' WHERE `region_id` = 12;
            UPDATE `regions` SET `name` = 'Leaf East' WHERE `region_id` = 13;
            UPDATE `regions` SET `name` = 'Leaf South' WHERE `region_id` = 14;
            UPDATE `regions` SET `name` = 'Sand North' WHERE `region_id` = 15;
            UPDATE `regions` SET `name` = 'Sand East' WHERE `region_id` = 16;
            UPDATE `regions` SET `name` = 'Sand Southeast' WHERE `region_id` = 17;
            UPDATE `regions` SET `name` = 'Mist North' WHERE `region_id` = 18;
            UPDATE `regions` SET `name` = 'Mist West' WHERE `region_id` = 19;
            UPDATE `regions` SET `name` = 'Mist Northwest' WHERE `region_id` = 20;
        ");
    }
}
