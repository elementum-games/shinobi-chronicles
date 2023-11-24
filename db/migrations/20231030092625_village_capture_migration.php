<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class VillageCaptureMigration extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        $this->execute("
            -- Alter table player_war_logs
            ALTER TABLE `player_war_logs` ADD `villages_captured` INT(11) NOT NULL DEFAULT '0' AFTER `regions_captured`; 
            -- Alter table village_war_logs
            ALTER TABLE `village_war_logs` ADD `villages_captured` INT(11) NOT NULL DEFAULT '0' AFTER `regions_captured`; 
            -- Alter table region_locations
            ALTER TABLE `region_locations` ADD `occupying_village_id` INT(11) NULL DEFAULT NULL; 
        ");
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        $this->execute("
            -- Alter table player_war_logs
            ALTER TABLE `player_war_logs` DROP COLUMN `villages_captured`;
            -- Alter table village_war_logs
            ALTER TABLE `village_war_logs` DROP COLUMN `villages_captured`;
            -- Alter table region_locations
            ALTER TABLE `region_locations` DROP `occupying_village_id`;
        ");
    }
}
