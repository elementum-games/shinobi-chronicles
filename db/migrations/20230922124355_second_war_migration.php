<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class SecondWarMigration extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        $this->execute("
            -- Alter region_locations table
            ALTER TABLE `region_locations` CHANGE `resource_production` `resource_count` INT(11) NOT NULL DEFAULT '0';
            UPDATE `region_locations` SET `resource_count` = 0;
            ALTER TABLE `region_locations` DROP COLUMN `resource_penalty`;
            ALTER TABLE `region_locations` DROP COLUMN `max_health`;

            -- Add loot table
            CREATE TABLE `loot` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `user_id` INT(11) NOT NULL,
            `resource_id` INT(11) NOT NULL,
            `quantity` INT(11) NOT NULL,
            `claimed_village_id` INT(11) NULL DEFAULT NULL,
            `battle_id` INT NULL DEFAULT NULL,
            PRIMARY KEY (`id`));

            -- Alter users table
            ALTER TABLE `users` ADD `pvp_immunity_ms` BIGINT(14) NOT NULL DEFAULT '0';

            -- Alter patrols table
            ALTER TABLE `patrols` ADD `tier` INT(11) NOT NULL;
            UPDATE `patrols` SET `tier` = 1;

            -- Alter battles table
            ALTER TABLE `battles` ADD `patrol_id` INT(11) NULL DEFAULT NULL;

            -- Alter operations table
            ALTER TABLE `operations` CHANGE `location_id` `target_id` INT(11) NOT NULL;
        ");
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        $this->execute("
            ALTER TABLE `region_locations` CHANGE `resource_count` `resource_production` INT(11) NOT NULL DEFAULT '0';
            UPDATE `region_locations` SET `resource_production` = 25;
            ALTER TABLE `region_locations` ADD `resource_penalty` INT(11) DEFAULT 0;
            ALTER TABLE `region_locations` ADD `max_health` INT(11) DEFAULT 100;
            DROP TABLE `loot`;
            ALTER TABLE `users` DROP COLUMN `pvp_immunity_ms`;
            ALTER TABLE `patrols` DROP COLUMN `tier`;
            ALTER TABLE `battles` DROP COLUMN `patrol_id`;
            ALTER TABLE `operations` CHANGE `target_id` `location_id`  INT(11) NOT NULL;
        ");
    }
}
