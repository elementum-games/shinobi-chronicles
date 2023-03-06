SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT = @@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS = @@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION = @@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- --------------------------------------------------------


--
-- Table structure for table `maps`
--

CREATE TABLE `maps`
(
    `map_id`      int(11)      NOT NULL,
    `map_name`    varchar(255) NOT NULL,
    `start_x`     int(11)      NOT NULL,
    `start_y`     int(11)      NOT NULL,
    `end_x`       int(11)      NOT NULL,
    `end_y`       int(11)      NOT NULL,
    `tile_height` int(11)      NOT NULL,
    `tile_width`  int(11)      NOT NULL,
    `background`  varchar(255) NOT NULL
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_general_ci;

INSERT INTO `maps` (`map_id`, `map_name`, `start_x`, `start_y`, `end_x`, `end_y`, `tile_height`, `tile_width`,
                    `background`)
VALUES (1, 'Earth', 1, 1, 18, 12, 32, 32, '/images/map/default.png'),
       (2, 'The Purge', 1, 1, 18, 12, 32, 32, '/images/map/thepurge.png');

ALTER TABLE `maps`
    ADD PRIMARY KEY (`map_id`);

ALTER TABLE `maps`
    MODIFY `map_id` int(11) NOT NULL AUTO_INCREMENT,
    AUTO_INCREMENT = 3;
COMMIT;

-- --------------------------------------------------------

--
-- Table structure for table `maps_locations`
--

CREATE TABLE `maps_locations`
(
    `location_id`      int(11)      NOT NULL,
    `name`             varchar(255) NOT NULL,
    `map_id`           int(11)      NOT NULL,
    `x`                int(11)      NOT NULL,
    `y`                int(11)      NOT NULL,
    `background_image` varchar(255)          DEFAULT NULL,
    `background_color` varchar(11)           DEFAULT NULL,
    `pvp_allowed`      BOOLEAN      NOT NULL DEFAULT 1,
    `ai_allowed`       BOOLEAN      NOT NULL DEFAULT 1,
    `regen`            int(11)      NOT NULL DEFAULT 0
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_general_ci;

INSERT INTO `maps_locations` (`location_id`, `name`, `map_id`, `x`, `y`, `background_image`, `background_color`,
                              `pvp_allowed`, `ai_allowed`, `regen`)
VALUES (1, 'Underground Colosseum', 1, 11, 9, '/images/map/locations/undergroundcolosseum.png', 'FF00007E', 0, 0, 0),
       (2, 'Font of Vitality', 1, 10, 1, '/images/map/locations/fontofvitality.png', '38FF007E', 1, 1, 200),
       (3, 'Stone', 1, 5, 3, '/images/village_icons/stone.png', '0000009f', 0, 0, 50),
       (4, 'Cloud', 1, 17, 2, '/images/village_icons/cloud.png', '0000009f', 0, 0, 50),
       (5, 'Leaf', 1, 9, 6, '/images/village_icons/leaf.png', '0000009f', 0, 0, 50),
       (6, 'Sand', 1, 3, 8, '/images/village_icons/sand.png', '0000009f', 0, 0, 50),
       (7, 'Mist', 1, 16, 10, '/images/village_icons/mist.png', '0000009f', 0, 0, 50);

ALTER TABLE `maps_locations`
    ADD PRIMARY KEY (`location_id`);

ALTER TABLE `maps_locations`
    MODIFY `location_id` int(11) NOT NULL AUTO_INCREMENT,
    AUTO_INCREMENT = 8;
COMMIT;

-- --------------------------------------------------------

--
-- Table structure for table `maps_portals`
--

CREATE TABLE `maps_portals`
(
    `portal_id`     int(11)      NOT NULL,
    `from_id`       int(11)      NOT NULL,
    `to_id`         int(11)      NOT NULL,
    `entrance_x`    int(11)      NOT NULL,
    `entrance_y`    int(11)      NOT NULL,
    `entrance_name` varchar(255) NOT NULL,
    `exit_x`        int(11)      NOT NULL,
    `exit_y`        int(11)      NOT NULL,
    `active`        BOOLEAN      NOT NULL DEFAULT 1,
    `whitelist`     varchar(255) NOT NULL DEFAULT '[]'
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_general_ci;

INSERT INTO `maps_portals` (`portal_id`, `from_id`, `to_id`, `entrance_x`, `entrance_y`, `entrance_name`, `exit_x`,
                            `exit_y`, `active`, `whitelist`)
VALUES (1, 1, 2, 5, 4, 'Forbidden', 1, 1, 1, 'Mist,Leaf, Stone'),
       (2, 2, 1, 1, 1, 'Safety', 5, 4, 1, 'Mist,Leaf, Stone');

ALTER TABLE `maps_portals`
    ADD PRIMARY KEY (`portal_id`);

ALTER TABLE `maps_portals`
    MODIFY `portal_id` int(11) NOT NULL AUTO_INCREMENT,
    AUTO_INCREMENT = 3;
COMMIT;

-- --------------------------------------------------------

ALTER TABLE `users`
    ADD COLUMN `attack_id`        varchar(255) DEFAULT NULL,
    ADD COLUMN `attack_id_time`   int(11)      DEFAULT NULL,
    ADD COLUMN `filters`          TEXT         DEFAULT NULL,
    ADD COLUMN `last_movement_ms` bigint(14) NOT NULL DEFAULT 0;


UPDATE `users`
SET `location` = concat(`location`, '.1');

UPDATE `villages`
SET `location` = concat(`location`, '.1');

COMMIT;