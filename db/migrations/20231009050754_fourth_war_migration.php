<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class FourthWarMigration extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        $this->execute("
            -- Alter loot table
            ALTER TABLE `loot` ADD `target_village_id` INT(11) NOT NULL;
            ALTER TABLE `loot` ADD `target_location_id` INT(11) NULL DEFAULT NULL;

            -- Cleanup of unused column
            ALTER TABLE `loot` DROP IF EXISTS `quantity`;

            -- Refresh tables
            DELETE FROM caravans;
            DELETE FROM loot;
            DELETE FROM resource_logs;
            UPDATE `region_locations` set `resource_count` = 0;
            UPDATE `villages` set `resources` = '[]';
            UPDATE `region_locations` set `health` = 5000 WHERE `type` = 'village';
            UPDATE `region_locations` set `health` = 15000 WHERE `type` = 'castle';
        ");
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        $this->execute("
            -- Alter loot table
            ALTER TABLE `loot` DROP COLUMN `target_village_id`;
            ALTER TABLE `loot` DROP COLUMN `target_location_id`;
    }
}
