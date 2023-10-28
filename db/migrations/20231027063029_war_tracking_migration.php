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
        ");
    }
}
