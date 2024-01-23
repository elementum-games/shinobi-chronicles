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
        ");
    }
}
