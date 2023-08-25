<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class MapUpdateMigration extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        // Start Transaction
        $this->execute("START TRANSACTION");

        // Modify villages table structure
        $this->execute("
            ALTER TABLE `villages`
                ADD `map_location_id` int(11) DEFAULT NULL,
                ADD `region_id` int(11) NOT NULL
        ");

        // Modify villages data
        $this->execute("UPDATE `villages` SET `region_id` = 1 WHERE `village_id` = 1");
        $this->execute("UPDATE `villages` SET `region_id` = 2 WHERE `village_id` = 2");
        $this->execute("UPDATE `villages` SET `region_id` = 3 WHERE `village_id` = 3");
        $this->execute("UPDATE `villages` SET `region_id` = 4 WHERE `village_id` = 4");
        $this->execute("UPDATE `villages` SET `region_id` = 5 WHERE `village_id` = 5");

        // Create Regions table
        $this->execute("
            CREATE TABLE `regions` (
              `region_id` int(11) NOT NULL,
              `name` varchar(50) NOT NULL,
              `village` int(11) NOT NULL,
              `vertices` varchar(100) NOT NULL DEFAULT '[]'
            )
        ");
        $this->execute("
            ALTER TABLE `regions`
                ADD PRIMARY KEY (`region_id`)
        ");
        $this->execute("
            ALTER TABLE `regions`
                MODIFY `region_id` int(11) NOT NULL AUTO_INCREMENT
        ");

        // Insert into Regions
        $this->execute("
            INSERT INTO `regions` (`region_id`, `name`, `village`, `vertices`) VALUES
                (1, 'Stone', 1, '[[0,0],[23,0],[23,9],[21,12],[0,12]]'),
                (2, 'Cloud', 2, '[[59,0],[84,0],[84,13],[71,13],[59,2]]'),
                (3, 'Leaf', 3, '[[47,17],[52,22],[52,23],[49,27],[35,27],[31,23],[31,17]]'),
                (4, 'Sand', 4, '[[0,27],[21,27],[27,33],[13,48],[0,48]]'),
                (5, 'Mist', 5, '[[62,48],[62,43],[71,34],[84,34],[84,48]]'),
                (6, 'Stone East', 1, '[[23,0],[39,0],[39,8],[31,14],[23,9]]'),
                (7, 'Stone Southeast', 1, '[[21,12],[23,9],[31,14],[31,23],[21,23],[21,12]]'),
                (8, 'Stone South', 1, '[[0,12],[21,12],[21,23],[16,23],[13,19],[0,18]]'),
                (9, 'Cloud South', 2, '[[70,12],[84,13],[84,22],[64,22],[65,22],[64,18]]'),
                (10, 'Cloud Southwest', 2, '[[52,7],[59,2],[69,12],[69,13],[64,19]]'),
                (11, 'Cloud West', 2, '[[39,0],[59,0],[59,3],[57,3],[56,4],[52,7],[52,6],[39,8]]'),
                (12, 'Leaf North', 3, '[[39,8],[52,6],[47,12],[47,17],[31,17],[31,14]]'),
                (13, 'Leaf East', 3, '[[47,12],[52,6],[64,19],[64,22],[62,25],[55,23],[53,23],[47,17]]'),
                (14, 'Leaf South', 3, '[[38,27],[49,27],[53,22],[61,25],[52,34],[38,34]]'),
                (15, 'Sand North', 4, '[[0,18],[13,19],[16,23],[21,23],[21,27],[0,27]]'),
                (16, 'Sand East', 4, '[[21,23],[31,23],[35,27],[38,27],[38,33],[27,33],[21,26]]'),
                (17, 'Sand Southeast', 4, '[[27,33],[38,32],[38,40],[47,48],[13,48]]'),
                (18, 'Mist North', 5, '[[64,22],[84,22],[84,34],[71,34],[62,24]]'),
                (19, 'Mist West', 5, '[[38,34],[54,34],[62,42],[62,48],[47,48],[38,40]]'),
                (20, 'Mist Northwest', 5, '[[62,24],[71,34],[63,43],[53,33]]')
        ");

        // Create Region_locations table
        $this->execute("
            CREATE TABLE `region_locations` (
              `id` int(11) NOT NULL,
              `region_id` int(11) NOT NULL,
              `health` int(11) NOT NULL DEFAULT 100,
              `max_health` int(11) NOT NULL DEFAULT 100,
              `type` varchar(50) NOT NULL DEFAULT 'none',
              `map_id` int(11) NOT NULL DEFAULT 1,
              `x` int(11) NOT NULL,
              `y` int(11) NOT NULL,
              `name` varchar(100) NOT NULL DEFAULT 'Nameless',
              `resource` varchar(50) DEFAULT NULL
            )
        ");
        $this->execute("
            ALTER TABLE `region_locations`
                ADD PRIMARY KEY (`id`)
        ");
        $this->execute("
            ALTER TABLE `region_locations`
                MODIFY `id` int(11) NOT NULL AUTO_INCREMENT
        ");

        // Insert into region_locations
        $this->execute("
            INSERT INTO `region_locations` (`id`, `region_id`, `health`, `max_health`, `type`, `map_id`, `x`, `y`, `name`, `resource`) VALUES
                (1, 8, 100, 100, 'castle', 1, 15, 16, 'Nameless Castle', NULL),
                (2, 8, 100, 100, 'village', 1, 7, 15, 'Nameless Village', 'wealth'),
                (3, 8, 100, 100, 'village', 1, 18, 21, 'Nameless Village', 'wealth'),
                (4, 15, 100, 100, 'castle', 1, 15, 26, 'Nameless Castle', NULL),
                (5, 15, 100, 100, 'village', 1, 11, 21, 'Nameless Village', 'materials'),
                (6, 15, 100, 100, 'village', 1, 3, 25, 'Nameless Village', 'wealth'),
                (7, 1, 100, 100, 'village', 1, 16, 11, 'Nameless Village', 'food'),
                (8, 1, 100, 100, 'village', 1, 4, 9, 'Nameless Village', 'materials'),
                (9, 1, 100, 100, 'village', 1, 17, 5, 'Nameless Village', 'wealth'),
                (10, 7, 100, 100, 'castle', 1, 27, 18, 'Nameless Castle', NULL),
                (11, 7, 100, 100, 'village', 1, 24, 20, 'Nameless Village', 'food'),
                (12, 4, 100, 100, 'village', 1, 13, 34, 'Nameless Village', 'food'),
                (13, 4, 100, 100, 'village', 1, 8, 29, 'Nameless Village', 'wealth'),
                (14, 4, 100, 100, 'village', 1, 12, 41, 'Nameless Village', 'materials'),
                (15, 7, 100, 100, 'village', 1, 22, 13, 'Nameless Village', 'materials'),
                (16, 6, 100, 100, 'castle', 1, 36, 6, 'Nameless Castle', NULL),
                (17, 6, 100, 100, 'village', 1, 27, 7, 'Nameless Village', 'food'),
                (18, 6, 100, 100, 'village', 1, 29, 11, 'Nameless Village', 'materials'),
                (19, 3, 100, 100, 'village', 1, 38, 25, 'Nameless Village', 'materials'),
                (20, 3, 100, 100, 'village', 1, 33, 21, 'Nameless Village', 'food'),
                (21, 3, 100, 100, 'village', 1, 47, 22, 'Nameless Village', 'wealth'),
                (22, 2, 100, 100, 'village', 1, 77, 8, 'Nameless Village', 'wealth'),
                (23, 2, 100, 100, 'village', 1, 71, 12, 'Nameless Village', 'food'),
                (24, 2, 100, 100, 'village', 1, 65, 7, 'Nameless Village', 'materials'),
                (25, 10, 100, 100, 'castle', 1, 59, 9, 'Nameless Castle', NULL),
                (26, 10, 100, 100, 'village', 1, 63, 14, 'Nameless Village', 'food'),
                (27, 10, 100, 100, 'village', 1, 58, 4, 'Nameless Village', 'wealth'),
                (28, 11, 100, 100, 'castle', 1, 48, 3, 'Nameless Castle', NULL),
                (29, 11, 100, 100, 'village', 1, 48, 7, 'Nameless Village', 'food'),
                (30, 11, 100, 100, 'village', 1, 42, 4, 'Nameless Village', 'materials'),
                (31, 12, 100, 100, 'village', 1, 46, 13, 'Nameless Village', 'wealth'),
                (32, 12, 100, 100, 'castle', 1, 39, 13, 'Nameless Castle', NULL),
                (33, 12, 100, 100, 'village', 1, 36, 15, 'Nameless Village', 'materials'),
                (34, 16, 100, 100, 'castle', 1, 30, 27, 'Nameless Castle', NULL),
                (35, 16, 100, 100, 'village', 1, 36, 33, 'Nameless Village', 'food'),
                (36, 16, 100, 100, 'village', 1, 23, 27, 'Nameless Village', 'materials'),
                (37, 13, 100, 100, 'castle', 1, 54, 14, 'Nameless Castle', NULL),
                (38, 13, 100, 100, 'village', 1, 55, 17, 'Nameless Village', 'food'),
                (39, 13, 100, 100, 'village', 1, 59, 22, 'Nameless Village', 'materials'),
                (40, 20, 100, 100, 'castle', 1, 63, 33, 'Nameless Castle', NULL),
                (41, 20, 100, 100, 'village', 1, 58, 33, 'Nameless Village', 'food'),
                (42, 20, 100, 100, 'village', 1, 62, 26, 'Nameless Village', 'wealth'),
                (43, 5, 100, 100, 'village', 1, 71, 41, 'Nameless Village', 'food'),
                (44, 5, 100, 100, 'village', 1, 83, 40, 'Nameless Village', 'materials'),
                (45, 5, 100, 100, 'village', 1, 73, 47, 'Nameless Village', 'wealth'),
                (46, 18, 100, 100, 'castle', 1, 80, 28, 'Nameless Castle', NULL),
                (47, 18, 100, 100, 'village', 1, 76, 32, 'Nameless Village', 'wealth'),
                (48, 18, 100, 100, 'village', 1, 70, 25, 'Nameless Village', 'food'),
                (49, 9, 100, 100, 'castle', 1, 72, 17, 'Nameless Castle', NULL),
                (50, 9, 100, 100, 'village', 1, 68, 19, 'Nameless Village', 'materials'),
                (51, 9, 100, 100, 'village', 1, 74, 20, 'Nameless Village', 'wealth'),
                (52, 14, 100, 100, 'village', 1, 54, 25, 'Nameless Village', 'wealth'),
                (53, 19, 100, 100, 'castle', 1, 48, 39, 'Nameless Castle', NULL),
                (54, 19, 100, 100, 'village', 1, 46, 36, 'Nameless Village', 'materials'),
                (55, 19, 100, 100, 'village', 1, 53, 41, 'Nameless Village', 'materials'),
                (56, 17, 100, 100, 'castle', 1, 27, 38, 'Nameless Castle', NULL),
                (57, 17, 100, 100, 'village', 1, 29, 34, 'Nameless Village', 'food'),
                (58, 14, 100, 100, 'castle', 1, 51, 29, 'Nameless Castle', NULL),
                (59, 14, 100, 100, 'village', 1, 46, 32, 'Nameless Village', 'food'),
                (60, 17, 100, 100, 'village', 1, 19, 43, 'Nameless Village', 'wealth')
        ");

        // Insert into map_locations
        $this->execute("
            INSERT INTO `maps_locations` (`name`, `map_id`, `x`, `y`, `background_image`, `background_color`, `pvp_allowed`, `ai_allowed`, `regen`) VALUES
                ('Unknown', 1, 19, 17, '/images/map/locations/unknown.png', '00000000', 1, 1, 0),
                ('Unknown', 1, 51, 9, '/images/map/locations/unknown.png', '00000000', 1, 1, 0)
        ");

        // Modify map_locations
        $this->execute("UPDATE `maps_locations` SET `x` = 34, `y` = 45 WHERE `name` = 'Underground Colosseum'");
        $this->execute("UPDATE `maps_locations` SET `x` = 32, `y` = 9 WHERE `name` = 'Font of Vitality'");
        $this->execute("UPDATE `maps_locations` SET `x` = 12, `y` = 8 WHERE `name` = 'Stone'");
        $this->execute("UPDATE `maps_locations` SET `x` = 71, `y` = 5 WHERE `name` = 'Cloud'");
        $this->execute("UPDATE `maps_locations` SET `x` = 41, `y` = 21 WHERE `name` = 'Leaf'");
        $this->execute("UPDATE `maps_locations` SET `x` = 5, `y` = 35 WHERE `name` = 'Sand'");
        $this->execute("UPDATE `maps_locations` SET `x` = 76, `y` = 43 WHERE `name` = 'Mist'");
        $this->execute("UPDATE `maps_locations` SET `x` = 75, `y` = 28 WHERE `name` = 'Ayakashi\'s Abyss'");

        // Modify map
        $this->execute("UPDATE `maps` SET `end_x` = 84, `end_y` = 48, `background` = '/images/map/Map_v2_default.jpg' WHERE `map_id` = 1");

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

        // Drop columns from villages
        $this->execute("
            ALTER TABLE `villages`
                DROP COLUMN `region_id`
        ");

        // Drop regions table
        $this->execute("DROP TABLE `regions`");

        // Drop region_locations table
        $this->execute("DROP TABLE `region_locations`");

        // Delete from map_locations
        $this->execute("DELETE FROM `map_locations` WHERE `name` = 'Unknown'");

        // Modify map_locations
        $this->execute("UPDATE `maps_locations` SET `x` = 16, `y` = 15 WHERE `name` = 'Underground Colosseum'");
        $this->execute("UPDATE `maps_locations` SET `x` = 10, `y` = 1 WHERE `name` = 'Font of Vitality'");
        $this->execute("UPDATE `maps_locations` SET `x` = 7, `y` = 7 WHERE `name` = 'Stone'");
        $this->execute("UPDATE `maps_locations` SET `x` = 25, `y` = 3 WHERE `name` = 'Cloud'");
        $this->execute("UPDATE `maps_locations` SET `x` = 15, `y` = 9 WHERE `name` = 'Leaf'");
        $this->execute("UPDATE `maps_locations` SET `x` = 4, `y` = 12 WHERE `name` = 'Sand'");
        $this->execute("UPDATE `maps_locations` SET `x` = 24, `y` = 15 WHERE `name` = 'Mist'");
        $this->execute("UPDATE `maps_locations` SET `x` = 26, `y` = 10 WHERE `name` = 'Ayakashi\'s Abyss'");

        // Modify map
        $this->execute("UPDATE `maps` SET `end_x` = 84, `end_y` = 48, `background` = '/images/map/default_900x600.png' WHERE `map_id` = 1");

        // Commit Transaction
        $this->execute("COMMIT");
    }
}
