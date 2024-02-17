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
            CREATE TABLE `village_buildings`
            (`id` INT(11) NOT NULL AUTO_INCREMENT,
            `key` VARCHAR(50) NOT NULL,
            `village_id` INT(11) NOT NULL ,
            `tier` INT(11) NOT NULL DEFAULT '0',
            `health` INT(11) NOT NULL DEFAULT '125000',
            `defense` INT(11) NOT NULL DEFAULT '100',
            `status` VARCHAR(50) NOT NULL DEFAULT 'default',
            `construction_progress` INT(11) NULL DEFAULT NULL,
            `construction_progress_required` INT(11) NULL DEFAULT NULL,
            `construction_progress_last_updated` INT(11) NULL DEFAULT NULL,
            `construction_boosted` TINYINT(1) NOT NULL DEFAULT 0,
            PRIMARY KEY (`id`),
            UNIQUE KEY `unique_key_village_id` (`key`, `village_id`));

            -- Create table village_upgrades
            CREATE TABLE `village_upgrades`
            (`id` INT(11) NOT NULL AUTO_INCREMENT,
            `key` VARCHAR(50) NOT NULL,
            `village_id` INT(11) NOT NULL,
            `status` VARCHAR(50) NOT NULL DEFAULT 'locked',
            `research_progress` INT(11) NULL DEFAULT NULL,
            `research_progress_required` INT(11) NULL DEFAULT NULL,
            `research_progress_last_updated` INT(11) NULL DEFAULT NULL,
            `research_boosted` TINYINT(1) NOT NULL DEFAULT 0,
            PRIMARY KEY (`id`),
            UNIQUE KEY `unique_key_village_id` (`key`, `village_id`));

            -- Alter table region_locations
            ALTER TABLE `region_locations` ADD `stability` INT(11) DEFAULT 0;
            ALTER TABLE `region_locations` CHANGE `id` `region_location_id` INT(11) NOT NULL AUTO_INCREMENT;
            ALTER TABLE `region_locations` ADD `rebellion_active` TINYINT(1) NOT NULL DEFAULT '0';
            ALTER TABLE `region_locations` ADD `background_image` VARCHAR(100) NOT NULL;

            -- Rename table operations
            RENAME TABLE `operations` TO `war_actions`;

            -- Rename column operation_id
            ALTER TABLE `war_actions` CHANGE `operation_id` `war_action_id` INT(11) NOT NULL AUTO_INCREMENT;
            ALTER TABLE `war_actions` DROP PRIMARY KEY, ADD PRIMARY KEY (`war_action_id`);

            -- Rename column operation
            ALTER TABLE `users` CHANGE `operation` `war_action_id` INT(11) NOT NULL DEFAULT '0';

            -- Alter table proposals
            ALTER TABLE `proposals` ADD `building_key` VARCHAR(50) NULL;
            ALTER TABLE `proposals` ADD `upgrade_key` VARCHAR(50) NULL;

            -- Alter table player_war_logs
            ALTER TABLE `player_war_logs` ADD `stability_gained` INT(11) NOT NULL DEFAULT '0';
            ALTER TABLE `player_war_logs` ADD `stability_reduced` INT(11) NOT NULL DEFAULT '0';

            -- Alter table village_war_logs
            ALTER TABLE `village_war_logs` ADD `stability_gained` INT(11) NOT NULL DEFAULT '0';
            ALTER TABLE `village_war_logs` ADD `stability_reduced` INT(11) NOT NULL DEFAULT '0';

            -- Use occupying_village_id as source of truth for region_locations
            UPDATE region_locations
            JOIN regions ON region_locations.region_id = regions.region_id
            SET region_locations.occupying_village_id = regions.village
            WHERE region_locations.occupying_village_id IS NULL;

            -- Fill village_buildings default
            INSERT INTO `village_buildings` (`key`, `village_id`, `tier`) VALUES ('VILLAGE_HQ', '1', '0');
            INSERT INTO `village_buildings` (`key`, `village_id`, `tier`) VALUES ('WORKSHOP', '1', '0');
            INSERT INTO `village_buildings` (`key`, `village_id`, `tier`) VALUES ('MARKET', '1', '0');
            INSERT INTO `village_buildings` (`key`, `village_id`, `tier`) VALUES ('ACADEMY', '1', '0');
            INSERT INTO `village_buildings` (`key`, `village_id`, `tier`) VALUES ('HOSPITAL', '1', '0');
            INSERT INTO `village_buildings` (`key`, `village_id`, `tier`) VALUES ('ANBU_HQ', '1', '0');
            INSERT INTO `village_buildings` (`key`, `village_id`, `tier`) VALUES ('SHRINE', '1', '0');
            INSERT INTO `village_buildings` (`key`, `village_id`, `tier`) VALUES ('RAMEN_STAND', '1', '0');
            INSERT INTO `village_buildings` (`key`, `village_id`, `tier`) VALUES ('VILLAGE_HQ', '2', '0');
            INSERT INTO `village_buildings` (`key`, `village_id`, `tier`) VALUES ('WORKSHOP', '2', '0');
            INSERT INTO `village_buildings` (`key`, `village_id`, `tier`) VALUES ('MARKET', '2', '0');
            INSERT INTO `village_buildings` (`key`, `village_id`, `tier`) VALUES ('ACADEMY', '2', '0');
            INSERT INTO `village_buildings` (`key`, `village_id`, `tier`) VALUES ('HOSPITAL', '2', '0');
            INSERT INTO `village_buildings` (`key`, `village_id`, `tier`) VALUES ('ANBU_HQ', '2', '0');
            INSERT INTO `village_buildings` (`key`, `village_id`, `tier`) VALUES ('SHRINE', '2', '0');
            INSERT INTO `village_buildings` (`key`, `village_id`, `tier`) VALUES ('RAMEN_STAND', '2', '0');
            INSERT INTO `village_buildings` (`key`, `village_id`, `tier`) VALUES ('VILLAGE_HQ', '3', '0');
            INSERT INTO `village_buildings` (`key`, `village_id`, `tier`) VALUES ('WORKSHOP', '3', '0');
            INSERT INTO `village_buildings` (`key`, `village_id`, `tier`) VALUES ('MARKET', '3', '0');
            INSERT INTO `village_buildings` (`key`, `village_id`, `tier`) VALUES ('ACADEMY', '3', '0');
            INSERT INTO `village_buildings` (`key`, `village_id`, `tier`) VALUES ('HOSPITAL', '3', '0');
            INSERT INTO `village_buildings` (`key`, `village_id`, `tier`) VALUES ('ANBU_HQ', '3', '0');
            INSERT INTO `village_buildings` (`key`, `village_id`, `tier`) VALUES ('SHRINE', '3', '0');
            INSERT INTO `village_buildings` (`key`, `village_id`, `tier`) VALUES ('RAMEN_STAND', '3', '0');
            INSERT INTO `village_buildings` (`key`, `village_id`, `tier`) VALUES ('VILLAGE_HQ', '4', '0');
            INSERT INTO `village_buildings` (`key`, `village_id`, `tier`) VALUES ('WORKSHOP', '4', '0');
            INSERT INTO `village_buildings` (`key`, `village_id`, `tier`) VALUES ('MARKET', '4', '0');
            INSERT INTO `village_buildings` (`key`, `village_id`, `tier`) VALUES ('ACADEMY', '4', '0');
            INSERT INTO `village_buildings` (`key`, `village_id`, `tier`) VALUES ('HOSPITAL', '4', '0');
            INSERT INTO `village_buildings` (`key`, `village_id`, `tier`) VALUES ('ANBU_HQ', '4', '0');
            INSERT INTO `village_buildings` (`key`, `village_id`, `tier`) VALUES ('SHRINE', '4', '0');
            INSERT INTO `village_buildings` (`key`, `village_id`, `tier`) VALUES ('RAMEN_STAND', '4', '0');
            INSERT INTO `village_buildings` (`key`, `village_id`, `tier`) VALUES ('VILLAGE_HQ', '5', '0');
            INSERT INTO `village_buildings` (`key`, `village_id`, `tier`) VALUES ('WORKSHOP', '5', '0');
            INSERT INTO `village_buildings` (`key`, `village_id`, `tier`) VALUES ('MARKET', '5', '0');
            INSERT INTO `village_buildings` (`key`, `village_id`, `tier`) VALUES ('ACADEMY', '5', '0');
            INSERT INTO `village_buildings` (`key`, `village_id`, `tier`) VALUES ('HOSPITAL', '5', '0');
            INSERT INTO `village_buildings` (`key`, `village_id`, `tier`) VALUES ('ANBU_HQ', '5', '0');
            INSERT INTO `village_buildings` (`key`, `village_id`, `tier`) VALUES ('SHRINE', '5', '0');
            INSERT INTO `village_buildings` (`key`, `village_id`, `tier`) VALUES ('RAMEN_STAND', '5', '0');

            -- Update region_location names
            UPDATE region_locations SET name = 'Ishiyama Castle' WHERE `region_location_id` = 16;
            UPDATE region_locations SET name = 'Daichikyū Castle' WHERE `region_location_id` = 1;
            UPDATE region_locations SET name = 'Doheki Castle' WHERE `region_location_id` = 10;
            UPDATE region_locations SET name = 'Aoiyama Castle' WHERE `region_location_id` = 49;
            UPDATE region_locations SET name = 'Midoriyama Castle' WHERE `region_location_id` = 25;
            UPDATE region_locations SET name = 'Akaiyama Castle' WHERE `region_location_id` = 28;
            UPDATE region_locations SET name = 'Ōtaiyō Castle' WHERE `region_location_id` = 32;
            UPDATE region_locations SET name = 'Ōhirahara Castle' WHERE `region_location_id` = 37;
            UPDATE region_locations SET name = 'Sakurama Castle' WHERE `region_location_id` = 58;
            UPDATE region_locations SET name = 'Sabaku-ishi Castle' WHERE `region_location_id` = 4;
            UPDATE region_locations SET name = 'Ōkaze Castle' WHERE `region_location_id` = 34;
            UPDATE region_locations SET name = 'Arashiyama Castle' WHERE `region_location_id` = 56;
            UPDATE region_locations SET name = 'Ōkaiyō Castle' WHERE `region_location_id` = 46;
            UPDATE region_locations SET name = 'Kiriyama Castle' WHERE `region_location_id` = 40;
            UPDATE region_locations SET name = 'Mikadzuki Castle' WHERE `region_location_id` = 53;
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
            ALTER TABLE `region_locations` DROP `background_image`;
            ALTER TABLE `region_locations` CHANGE `region_location_id` `id` INT(11) NOT NULL AUTO_INCREMENT;

            -- Rename column operation_id
            ALTER TABLE `war_actions` CHANGE `war_action_id` `operation_id` INT(11) NOT NULL AUTO_INCREMENT;
            ALTER TABLE `war_actions` DROP PRIMARY KEY, ADD PRIMARY KEY (`operation_id`);

            -- Rename table war_actions
            RENAME TABLE `war_actions` TO `operations`;

            -- Rename column war_action_id
            ALTER TABLE `users` CHANGE `war_action_id` `operation` INT(11) NOT NULL DEFAULT '0';

            -- Clear occupying village_id
            UPDATE region_locations SET region_locations.occupying_village_id = NULL;

            -- Alter table proposals
            ALTER TABLE `proposals` DROP `building_key`;
            ALTER TABLE `proposals` DROP `upgrade_key`;
            ALTER TABLE `proposals` DROP `upgrade_data`;

            -- Alter table player_war_logs
            ALTER TABLE `player_war_logs` DROP `stability_gained`;
            ALTER TABLE `player_war_logs` DROP `stability_reduced`;

            -- Alter table village_war_logs
            ALTER TABLE `village_war_logs` DROP `stability_gained`;
            ALTER TABLE `village_war_logs` DROP `stability_reduced`;

            -- Alter table region_locations
            ALTER TABLE `region_locations` DROP `rebellion_active`;
        ");
    }
}
