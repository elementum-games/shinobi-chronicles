<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class InitialWarMigration extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        // Start Transaction
        $this->execute("START TRANSACTION");

        // Update region_locations - setting resource to ID value
        $this->execute("UPDATE region_locations SET `resource` = 1 where `resource` = 'materials'");
        $this->execute("UPDATE region_locations SET `resource` = 2 where `resource` = 'food'");
        $this->execute("UPDATE region_locations SET `resource` = 3 where `resource` = 'wealth'");
        $this->execute("ALTER TABLE `region_locations` CHANGE `resource` `resource_id` INT(11) NULL DEFAULT NULL");
        $this->execute("UPDATE `region_locations` SET `resource_id` = 4 WHERE `type` = 'castle' AND `region_id` IN (6, 7, 8)");
        $this->execute("UPDATE `region_locations` SET `resource_id` = 5 WHERE `type` = 'castle' AND `region_id` IN (9, 10, 11)");
        $this->execute("UPDATE `region_locations` SET `resource_id` = 6 WHERE `type` = 'castle' AND `region_id` IN (12, 13, 14)");
        $this->execute("UPDATE `region_locations` SET `resource_id` = 7 WHERE `type` = 'castle' AND `region_id` IN (15, 16, 17)");
        $this->execute("UPDATE `region_locations` SET `resource_id` = 8 WHERE `type` = 'castle' AND `region_id` IN (18, 19, 20)");

        // Update region_locations - add columns for production
        $this->execute("ALTER TABLE `region_locations` ADD `resource_production` INT(11) DEFAULT 0");
        $this->execute("ALTER TABLE `region_locations` ADD `resource_penalty` INT(11) DEFAULT 0");
        $this->execute("ALTER TABLE `region_locations` ADD `defense` INT(11) NOT NULL DEFAULT '0' AFTER `resource_penalty`");
        $this->execute("UPDATE `region_locations` SET `resource_production` = 25");

        // Update villages table - add column for resources
        $this->execute("ALTER TABLE `villages` ADD `resources` VARCHAR(200) DEFAULT '[]'");

        // Create patrols table
        $this->execute("CREATE TABLE `patrols` (
            `id` int(11) NOT NULL,
            `start_time` int(11) NOT NULL,
            `travel_time` int(11) DEFAULT NULL,
            `travel_interval` int(11) DEFAULT NULL,
            `region_id` int(11) NOT NULL,
            `village_id` int(11) DEFAULT NULL,
            `name` varchar(50) NOT NULL
        )");
        $this->execute("ALTER TABLE `patrols` ADD PRIMARY KEY (`id`)");
        $this->execute("ALTER TABLE `patrols` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT");

        // Create caravan table
        $this->execute("CREATE TABLE `caravans` (
          `id` int(11) NOT NULL,
          `start_time` int(11) NOT NULL,
          `travel_time` int(11) DEFAULT NULL,
          `travel_interval` int(11) DEFAULT NULL,
          `region_id` int(11) NOT NULL,
          `village_id` int(11) NOT NULL,
          `caravan_type` varchar(50) NOT NULL,
          `resources` varchar(100) NOT NULL DEFAULT '[]',
          `name` varchar(50) NOT NULL
        )");
        $this->execute("ALTER TABLE `caravans` ADD PRIMARY KEY (`id`)");
        $this->execute("ALTER TABLE `caravans` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT");

        // Create resources table
        $this->execute("CREATE TABLE `resources` (
          `resource_id` int(11) NOT NULL,
          `resource_name` varchar(50) NOT NULL
        )");
        $this->execute("ALTER TABLE `resources` ADD PRIMARY KEY (`resource_id`)");
        $this->execute("ALTER TABLE `resources` MODIFY `resource_id` int(11) NOT NULL AUTO_INCREMENT");

        // Add placeholders to resources table
        $this->execute("INSERT INTO `resources` (`resource_id`, `resource_name`) VALUES
            (1, 'materials'),
            (2, 'food'),
            (3, 'wealth'),
            (4, 'adamantine'),
            (5, 'orichalcum'),
            (6, 'elderwood'),
            (7, 'breezepowder'),
            (8, 'relics')
        ");

        // Create village_relations table
        $this->execute("CREATE TABLE `village_relations` (
            `relation_id` INT(11) NOT NULL AUTO_INCREMENT,
            `village1_id` INT(11) NOT NULL,
            `village2_id` INT(11) NOT NULL,
            `relation_type` VARCHAR(50) NOT NULL,
            `relation_name` VARCHAR(200) NOT NULL,
            `relation_start` INT(11) NULL DEFAULT NULL,
            `relation_end` INT(11) NULL DEFAULT NULL,
            PRIMARY KEY (`relation_id`))");
        $this->execute("INSERT INTO `shinobi_chronicles`.`village_relations` (`village1_id`, `village2_id`, `relation_type`, `relation_name`, `relation_start`, `relation_end`) VALUES (1, 2, 'Neutral', 'Ancient Calm', NULL, NULL)");
        $this->execute("INSERT INTO `shinobi_chronicles`.`village_relations` (`village1_id`, `village2_id`, `relation_type`, `relation_name`, `relation_start`, `relation_end`) VALUES (1, 3, 'Neutral', 'Ancient Calm', NULL, NULL)");
        $this->execute("INSERT INTO `shinobi_chronicles`.`village_relations` (`village1_id`, `village2_id`, `relation_type`, `relation_name`, `relation_start`, `relation_end`) VALUES (1, 4, 'Neutral', 'Ancient Calm', NULL, NULL)");
        $this->execute("INSERT INTO `shinobi_chronicles`.`village_relations` (`village1_id`, `village2_id`, `relation_type`, `relation_name`, `relation_start`, `relation_end`) VALUES (1, 5, 'Neutral', 'Ancient Calm', NULL, NULL)");
        $this->execute("INSERT INTO `shinobi_chronicles`.`village_relations` (`village1_id`, `village2_id`, `relation_type`, `relation_name`, `relation_start`, `relation_end`) VALUES (2, 3, 'Neutral', 'Ancient Calm', NULL, NULL)");
        $this->execute("INSERT INTO `shinobi_chronicles`.`village_relations` (`village1_id`, `village2_id`, `relation_type`, `relation_name`, `relation_start`, `relation_end`) VALUES (2, 4, 'Neutral', 'Ancient Calm', NULL, NULL)");
        $this->execute("INSERT INTO `shinobi_chronicles`.`village_relations` (`village1_id`, `village2_id`, `relation_type`, `relation_name`, `relation_start`, `relation_end`) VALUES (2, 5, 'Neutral', 'Ancient Calm', NULL, NULL)");
        $this->execute("INSERT INTO `shinobi_chronicles`.`village_relations` (`village1_id`, `village2_id`, `relation_type`, `relation_name`, `relation_start`, `relation_end`) VALUES (3, 4, 'Neutral', 'Ancient Calm', NULL, NULL)");
        $this->execute("INSERT INTO `shinobi_chronicles`.`village_relations` (`village1_id`, `village2_id`, `relation_type`, `relation_name`, `relation_start`, `relation_end`) VALUES (3, 5, 'Neutral', 'Ancient Calm', NULL, NULL)");
        $this->execute("INSERT INTO `shinobi_chronicles`.`village_relations` (`village1_id`, `village2_id`, `relation_type`, `relation_name`, `relation_start`, `relation_end`) VALUES (4, 5, 'Neutral', 'Ancient Calm', NULL, NULL)");

        // Create village_seats table
        $this->execute("CREATE TABLE `shinobi_chronicles`.`village_seats` (
            `seat_id` INT(11) NOT NULL AUTO_INCREMENT,
            `user_id` INT(11) NOT NULL,
            `village_id` INT(11) NOT NULL,
            `seat_type` VARCHAR(50) NOT NULL,
            `seat_title` VARCHAR(100) NOT NULL,
            `seat_start` INT(11) NOT NULL,
            `seat_end` INT(11) NULL DEFAULT NULL,
            PRIMARY KEY (`seat_id`))
        ");

        // Commit Transaction
        $this->execute("COMMIT");
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        // Start Transaction
        $this->execute("START TRANSACTION");

        // Update region_locations - setting resource to ID value
        $this->execute("ALTER TABLE `region_locations` CHANGE `resource_id` `resource` VARCHAR(50) NULL DEFAULT NULL");

        // Update region_locations - add columns for production
        $this->execute("ALTER TABLE `region_locations` DROP COLUMN `resource_production`");
        $this->execute("ALTER TABLE `region_locations` DROP COLUMN `resource_penalty`");

        // Update villages table - add column for resources
        $this->execute("ALTER TABLE `villages` DROP COLUMN `resources`");

        // Create patrols table
        $this->execute("DROP TABLE `patrols`");

        // Create caravan table
        $this->execute("DROP TABLE `caravans`");

        // Create resources table
        $this->execute("DROP TABLE `resources`");

        // Create village_relations table
        $this->execute("DROP TABLE `village_relations`");

        // Create village_seats table
        $this->execute("DROP TABLE `village_seats`");

        // Commit Transaction
        $this->execute("COMMIT");
    }
}
