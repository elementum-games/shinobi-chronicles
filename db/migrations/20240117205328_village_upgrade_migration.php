<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class VillageUpgradeMigration extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        $this->execute("
            -- Create table village_buildings
            CREATE TABLE ``village_buildings`
            (`id` INT(11) NOT NULL AUTO_INCREMENT,
            `building_id` INT(11) NOT NULL,
            `village_id` INT(11) NOT NULL ,
            `tier` INT(11) NOT NULL DEFAULT '0',
            `health` INT(11) NOT NULL DEFAULT '0',
            `build_start_time` INT(11) NULL DEFAULT NULL,
            `build_end_time` INT(11) NULL DEFAULT NULL,
            PRIMARY KEY (`id`));

            -- Create table village_upgrades
            CREATE TABLE `village_upgrades`
            (`id` INT(11) NOT NULL AUTO_INCREMENT,
            `key` VARCHAR(100) NOT NULL,
            `village_id` INT(11) NOT NULL,
            `is_active` TINYINT(1) NOT NULL DEFAULT '0',
            `research_start_time` INT(11) NULL DEFAULT NULL,
            `research_end_time` INT(11) NULL DEFAULT NULL,
            PRIMARY KEY (`id`));

            -- Alter table region_locations
            ALTER TABLE `region_locations` ADD `stability` INT(11) DEFAULT 0;
            ALTER TABLE `region_locations` CHANGE `id` `region_location_id` INT(11) NOT NULL AUTO_INCREMENT;
            
            -- Rename table operations
            RENAME TABLE `operations` TO `war_actions`;

            -- Rename column operation_id
            ALTER TABLE `war_actions` CHANGE `operation_id` `war_action_id` INT(11) NOT NULL AUTO_INCREMENT;
            ALTER TABLE `war_actions` DROP PRIMARY KEY, ADD PRIMARY KEY (`war_action_id`); 

            -- Rename column operation
            ALTER TABLE `users` CHANGE `operation` `war_action_id` INT(11) NOT NULL DEFAULT '0'; 

            -- Use occupying_village_id as source of truth for region_locations
            UPDATE region_locations
            JOIN regions ON region_locations.region_id = regions.region_id
            SET region_locations.occupying_village_id = regions.village
            WHERE region_locations.occupying_village_id IS NULL;

            -- Fill village_buildings default
            INSERT INTO `village_buildings` (`building_id`, `village_id`, `tier`, `health`, `build_start_time`, `build_end_time`) VALUES ('1', '1', '1', '0', NULL, NULL);
            INSERT INTO `village_buildings` (`building_id`, `village_id`, `tier`, `health`, `build_start_time`, `build_end_time`) VALUES ('2', '1', '1', '0', NULL, NULL);
            INSERT INTO `village_buildings` (`building_id`, `village_id`, `tier`, `health`, `build_start_time`, `build_end_time`) VALUES ('3', '1', '0', '0', NULL, NULL);
            INSERT INTO `village_buildings` (`building_id`, `village_id`, `tier`, `health`, `build_start_time`, `build_end_time`) VALUES ('4', '1', '0', '0', NULL, NULL);
            INSERT INTO `village_buildings` (`building_id`, `village_id`, `tier`, `health`, `build_start_time`, `build_end_time`) VALUES ('5', '1', '0', '0', NULL, NULL);
            INSERT INTO `village_buildings` (`building_id`, `village_id`, `tier`, `health`, `build_start_time`, `build_end_time`) VALUES ('6', '1', '0', '0', NULL, NULL);
            INSERT INTO `village_buildings` (`building_id`, `village_id`, `tier`, `health`, `build_start_time`, `build_end_time`) VALUES ('7', '1', '0', '0', NULL, NULL);
            INSERT INTO `village_buildings` (`building_id`, `village_id`, `tier`, `health`, `build_start_time`, `build_end_time`) VALUES ('8', '1', '0', '0', NULL, NULL);
            INSERT INTO `village_buildings` (`building_id`, `village_id`, `tier`, `health`, `build_start_time`, `build_end_time`) VALUES ('1', '2', '1', '0', NULL, NULL);
            INSERT INTO `village_buildings` (`building_id`, `village_id`, `tier`, `health`, `build_start_time`, `build_end_time`) VALUES ('2', '2', '1', '0', NULL, NULL);
            INSERT INTO `village_buildings` (`building_id`, `village_id`, `tier`, `health`, `build_start_time`, `build_end_time`) VALUES ('3', '2', '0', '0', NULL, NULL);
            INSERT INTO `village_buildings` (`building_id`, `village_id`, `tier`, `health`, `build_start_time`, `build_end_time`) VALUES ('4', '2', '0', '0', NULL, NULL);
            INSERT INTO `village_buildings` (`building_id`, `village_id`, `tier`, `health`, `build_start_time`, `build_end_time`) VALUES ('5', '2', '0', '0', NULL, NULL);
            INSERT INTO `village_buildings` (`building_id`, `village_id`, `tier`, `health`, `build_start_time`, `build_end_time`) VALUES ('6', '2', '0', '0', NULL, NULL);
            INSERT INTO `village_buildings` (`building_id`, `village_id`, `tier`, `health`, `build_start_time`, `build_end_time`) VALUES ('7', '2', '0', '0', NULL, NULL);
            INSERT INTO `village_buildings` (`building_id`, `village_id`, `tier`, `health`, `build_start_time`, `build_end_time`) VALUES ('8', '2', '0', '0', NULL, NULL);
            INSERT INTO `village_buildings` (`building_id`, `village_id`, `tier`, `health`, `build_start_time`, `build_end_time`) VALUES ('1', '3', '1', '0', NULL, NULL);
            INSERT INTO `village_buildings` (`building_id`, `village_id`, `tier`, `health`, `build_start_time`, `build_end_time`) VALUES ('2', '3', '1', '0', NULL, NULL);
            INSERT INTO `village_buildings` (`building_id`, `village_id`, `tier`, `health`, `build_start_time`, `build_end_time`) VALUES ('3', '3', '0', '0', NULL, NULL);
            INSERT INTO `village_buildings` (`building_id`, `village_id`, `tier`, `health`, `build_start_time`, `build_end_time`) VALUES ('4', '3', '0', '0', NULL, NULL);
            INSERT INTO `village_buildings` (`building_id`, `village_id`, `tier`, `health`, `build_start_time`, `build_end_time`) VALUES ('5', '3', '0', '0', NULL, NULL);
            INSERT INTO `village_buildings` (`building_id`, `village_id`, `tier`, `health`, `build_start_time`, `build_end_time`) VALUES ('6', '3', '0', '0', NULL, NULL);
            INSERT INTO `village_buildings` (`building_id`, `village_id`, `tier`, `health`, `build_start_time`, `build_end_time`) VALUES ('7', '3', '0', '0', NULL, NULL);
            INSERT INTO `village_buildings` (`building_id`, `village_id`, `tier`, `health`, `build_start_time`, `build_end_time`) VALUES ('8', '3', '0', '0', NULL, NULL);
            INSERT INTO `village_buildings` (`building_id`, `village_id`, `tier`, `health`, `build_start_time`, `build_end_time`) VALUES ('1', '4', '1', '0', NULL, NULL);
            INSERT INTO `village_buildings` (`building_id`, `village_id`, `tier`, `health`, `build_start_time`, `build_end_time`) VALUES ('2', '4', '1', '0', NULL, NULL);
            INSERT INTO `village_buildings` (`building_id`, `village_id`, `tier`, `health`, `build_start_time`, `build_end_time`) VALUES ('3', '4', '0', '0', NULL, NULL);
            INSERT INTO `village_buildings` (`building_id`, `village_id`, `tier`, `health`, `build_start_time`, `build_end_time`) VALUES ('4', '4', '0', '0', NULL, NULL);
            INSERT INTO `village_buildings` (`building_id`, `village_id`, `tier`, `health`, `build_start_time`, `build_end_time`) VALUES ('5', '4', '0', '0', NULL, NULL);
            INSERT INTO `village_buildings` (`building_id`, `village_id`, `tier`, `health`, `build_start_time`, `build_end_time`) VALUES ('6', '4', '0', '0', NULL, NULL);
            INSERT INTO `village_buildings` (`building_id`, `village_id`, `tier`, `health`, `build_start_time`, `build_end_time`) VALUES ('7', '4', '0', '0', NULL, NULL);
            INSERT INTO `village_buildings` (`building_id`, `village_id`, `tier`, `health`, `build_start_time`, `build_end_time`) VALUES ('8', '4', '0', '0', NULL, NULL);
            INSERT INTO `village_buildings` (`building_id`, `village_id`, `tier`, `health`, `build_start_time`, `build_end_time`) VALUES ('1', '5', '1', '0', NULL, NULL);
            INSERT INTO `village_buildings` (`building_id`, `village_id`, `tier`, `health`, `build_start_time`, `build_end_time`) VALUES ('2', '5', '1', '0', NULL, NULL);
            INSERT INTO `village_buildings` (`building_id`, `village_id`, `tier`, `health`, `build_start_time`, `build_end_time`) VALUES ('3', '5', '0', '0', NULL, NULL);
            INSERT INTO `village_buildings` (`building_id`, `village_id`, `tier`, `health`, `build_start_time`, `build_end_time`) VALUES ('4', '5', '0', '0', NULL, NULL);
            INSERT INTO `village_buildings` (`building_id`, `village_id`, `tier`, `health`, `build_start_time`, `build_end_time`) VALUES ('5', '5', '0', '0', NULL, NULL);
            INSERT INTO `village_buildings` (`building_id`, `village_id`, `tier`, `health`, `build_start_time`, `build_end_time`) VALUES ('6', '5', '0', '0', NULL, NULL);
            INSERT INTO `village_buildings` (`building_id`, `village_id`, `tier`, `health`, `build_start_time`, `build_end_time`) VALUES ('7', '5', '0', '0', NULL, NULL);
            INSERT INTO `village_buildings` (`building_id`, `village_id`, `tier`, `health`, `build_start_time`, `build_end_time`) VALUES ('8', '5', '0', '0', NULL, NULL);
        ");
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        $this->execute("
            -- Drop table village_buildings
            DROP TABLE `village_buildings`;

            -- Drop table village_upgrades
            DROP TABLE `village_upgrades`;

            -- Update columns on region_locations
            ALTER TABLE `region_locations` DROP `stability`;
            ALTER TABLE `region_locations` CHANGE `region_location_id` `id` INT(11) NOT NULL AUTO_INCREMENT;

            -- Rename table war_actions
            RENAME TABLE `war_actions` TO `operations`;

            -- Rename column operation_id
            ALTER TABLE `war_actions` CHANGE `war_action_id` `operation_id` INT(11) NOT NULL AUTO_INCREMENT;
            ALTER TABLE `war_actions` DROP PRIMARY KEY, ADD PRIMARY KEY (`operation_id`); 

            -- Rename column war_action_id
            ALTER TABLE `users` CHANGE `war_action_id` `operation` INT(11) NOT NULL DEFAULT '0'; 

            -- Clear occupying village_id
            UPDATE region_locations SET region_locations.occupying_village_id = NULL;
        ");
    }
}
