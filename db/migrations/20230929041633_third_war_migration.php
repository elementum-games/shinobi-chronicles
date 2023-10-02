<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class ThirdWarMigration extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        $this->execute("
            -- Alter loot table
            ALTER TABLE `loot` ADD `claimed_time` INT(11) NULL DEFAULT NULL;

            -- Update region_locations table
            UPDATE region_locations SET `resource_id` = 1 WHERE `region_id` = 6 and `type` = 'castle';
            UPDATE region_locations SET `resource_id` = 2 WHERE `region_id` = 7 and `type` = 'castle';
            UPDATE region_locations SET `resource_id` = 3 WHERE `region_id` = 8 and `type` = 'castle';
            UPDATE region_locations SET `resource_id` = 3 WHERE `region_id` = 9 and `type` = 'castle';
            UPDATE region_locations SET `resource_id` = 2 WHERE `region_id` = 10 and `type` = 'castle';
            UPDATE region_locations SET `resource_id` = 1 WHERE `region_id` = 11 and `type` = 'castle';
            UPDATE region_locations SET `resource_id` = 1 WHERE `region_id` = 12 and `type` = 'castle';
            UPDATE region_locations SET `resource_id` = 2 WHERE `region_id` = 13 and `type` = 'castle';
            UPDATE region_locations SET `resource_id` = 3 WHERE `region_id` = 14 and `type` = 'castle';
            UPDATE region_locations SET `resource_id` = 3 WHERE `region_id` = 15 and `type` = 'castle';
            UPDATE region_locations SET `resource_id` = 2 WHERE `region_id` = 16 and `type` = 'castle';
            UPDATE region_locations SET `resource_id` = 1 WHERE `region_id` = 17 and `type` = 'castle';
            UPDATE region_locations SET `resource_id` = 3 WHERE `region_id` = 18 and `type` = 'castle';
            UPDATE region_locations SET `resource_id` = 2 WHERE `region_id` = 19 and `type` = 'castle';
            UPDATE region_locations SET `resource_id` = 1 WHERE `region_id` = 20 and `type` = 'castle';

            -- Add resource_logs table
            CREATE TABLE `resource_logs` (
              `log_id` int(11) NOT NULL AUTO_INCREMENT,
              `village_id` int(11) NOT NULL,
              `resource_id` int(11) NOT NULL,
              `type` int(11) NOT NULL,
              `quantity` int(11) NOT NULL,
              `time` int(11) NOT NULL,
              PRIMARY KEY (`id`));
            )
        ");
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        $this->execute("
            -- Alter loot table
            ALTER TABLE `loot` DROP COLUMN `claimed_time`;

            -- Update region_locations table
            UPDATE region_locations SET `resource_id` = 4 WHERE `region_id` = 6 and `type` = 'castle';
            UPDATE region_locations SET `resource_id` = 4 WHERE `region_id` = 7 and `type` = 'castle';
            UPDATE region_locations SET `resource_id` = 4 WHERE `region_id` = 8 and `type` = 'castle';
            UPDATE region_locations SET `resource_id` = 5 WHERE `region_id` = 9 and `type` = 'castle';
            UPDATE region_locations SET `resource_id` = 5 WHERE `region_id` = 10 and `type` = 'castle';
            UPDATE region_locations SET `resource_id` = 5 WHERE `region_id` = 11 and `type` = 'castle';
            UPDATE region_locations SET `resource_id` = 6 WHERE `region_id` = 12 and `type` = 'castle';
            UPDATE region_locations SET `resource_id` = 6 WHERE `region_id` = 13 and `type` = 'castle';
            UPDATE region_locations SET `resource_id` = 6 WHERE `region_id` = 14 and `type` = 'castle';
            UPDATE region_locations SET `resource_id` = 7 WHERE `region_id` = 15 and `type` = 'castle';
            UPDATE region_locations SET `resource_id` = 7 WHERE `region_id` = 16 and `type` = 'castle';
            UPDATE region_locations SET `resource_id` = 7 WHERE `region_id` = 17 and `type` = 'castle';
            UPDATE region_locations SET `resource_id` = 8 WHERE `region_id` = 18 and `type` = 'castle';
            UPDATE region_locations SET `resource_id` = 8 WHERE `region_id` = 19 and `type` = 'castle';
            UPDATE region_locations SET `resource_id` = 8 WHERE `region_id` = 20 and `type` = 'castle';

            -- Add resource_logs table
            DROP TABLE `resource_logs`;
        ");
    }
}
