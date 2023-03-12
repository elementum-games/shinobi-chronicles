<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class TravelOverhaul extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */

    public function up() {

        // maps table
        $this->execute("
            CREATE TABLE `maps`
            (
                `map_id`      int(11)      NOT NULL AUTO_INCREMENT,
                `map_name`    varchar(255) NOT NULL,
                `start_x`     int(11)      NOT NULL,
                `start_y`     int(11)      NOT NULL,
                `end_x`       int(11)      NOT NULL,
                `end_y`       int(11)      NOT NULL,
                `tile_height` int(11)      NOT NULL,
                `tile_width`  int(11)      NOT NULL,
                `background`  varchar(255) NOT NULL,
                PRIMARY KEY (`map_id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=`latin1`;
        ");
        $this->execute("
            INSERT INTO `maps`
                (`map_id`, `map_name`, `start_x`, `start_y`, `end_x`, `end_y`, `tile_height`, `tile_width`,
                 `background`)
            VALUES (1, 'Earth', 1, 1, 18, 12, 32, 32, '/images/map/default.png'),
               (2, 'The Purge', 1, 1, 18, 12, 32, 32, '/images/map/thepurge.png');
            ");

        // maps_location table
        $this->execute("
            CREATE TABLE `maps_locations`
            (
                `location_id`      int(11)      NOT NULL AUTO_INCREMENT,
                `name`             varchar(255) NOT NULL,
                `map_id`           int(11)      NOT NULL,
                `x`                int(11)      NOT NULL,
                `y`                int(11)      NOT NULL,
                `background_image` varchar(255)          DEFAULT NULL,
                `background_color` varchar(11)           DEFAULT NULL,
                `pvp_allowed`      BOOLEAN      NOT NULL DEFAULT 1,
                `ai_allowed`       BOOLEAN      NOT NULL DEFAULT 1,
                `regen`            int(11)      NOT NULL DEFAULT 0,
                PRIMARY KEY (`location_id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=`latin1`;
        ");
        $this->execute("
            INSERT INTO `maps_locations`
                    (`location_id`, `name`, `map_id`, `x`, `y`, `background_image`, `background_color`,
                     `pvp_allowed`, `ai_allowed`, `regen`)
            VALUES (1, 'Underground Colosseum', 1, 11, 9, '/images/map/locations/undergroundcolosseum.png', 'FF00007E', 0, 0, 0),
               (2, 'Font of Vitality', 1, 10, 1, '/images/map/locations/fontofvitality.png', '38FF007E', 1, 1, 200),
               (3, 'Stone', 1, 5, 3, '/images/village_icons/stone.png', '0000009f', 0, 0, 50),
               (4, 'Cloud', 1, 17, 2, '/images/village_icons/cloud.png', '0000009f', 0, 0, 50),
               (5, 'Leaf', 1, 9, 6, '/images/village_icons/leaf.png', '0000009f', 0, 0, 50),
               (6, 'Sand', 1, 3, 8, '/images/village_icons/sand.png', '0000009f', 0, 0, 50),
               (7, 'Mist', 1, 16, 10, '/images/village_icons/mist.png', '0000009f', 0, 0, 50);
        ");
        // maps_portal table
        $this->execute("
            CREATE TABLE `maps_portals`
            (
                `portal_id`     int(11)      NOT NULL AUTO_INCREMENT,
                `from_id`       int(11)      NOT NULL,
                `to_id`         int(11)      NOT NULL,
                `entrance_x`    int(11)      NOT NULL,
                `entrance_y`    int(11)      NOT NULL,
                `entrance_name` varchar(255) NOT NULL,
                `exit_x`        int(11)      NOT NULL,
                `exit_y`        int(11)      NOT NULL,
                `active`        BOOLEAN      NOT NULL DEFAULT 1,
                `whitelist`     varchar(255) NOT NULL DEFAULT '[]',
                PRIMARY KEY (`portal_id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=`latin1`;
        ");
        $this->execute("
            INSERT INTO `maps_portals` 
                (`portal_id`, `from_id`, `to_id`, `entrance_x`, `entrance_y`, `entrance_name`, `exit_x`,
                            `exit_y`, `active`, `whitelist`)
            VALUES (1, 1, 2, 5, 4, 'Forbidden', 1, 1, 1, 'Mist,Leaf,Stone,Sand,Cloud'),
                   (2, 2, 1, 1, 1, 'Safety', 5, 4, 1, 'Mist,Leaf,Stone,Sand,Cloud');
        ");
        // users modifications

        // add new columns
        $this->execute("
            ALTER TABLE `users`
                ADD COLUMN `attack_id`          varchar(255) DEFAULT NULL,
                ADD COLUMN `attack_id_time_ms`  bigint(14) DEFAULT NULL,
                ADD COLUMN `filters`            TEXT DEFAULT NULL,
                ADD COLUMN `last_movement_ms`   bigint(14) NOT NULL DEFAULT 0;
        ");

        // change from x.y to x:y:map_id
        $this->execute("
            UPDATE `users` 
                SET `location`=CONCAT(
                    REPLACE(`location`, '.', ':'),
                    ':1'
                )
        ");

        // use milliseconds instead of seconds
        $this->execute("
            ALTER TABLE `users`
                CHANGE `last_pvp` `last_pvp_ms` BIGINT(14),
                CHANGE `last_ai` `last_ai_ms` BIGINT(14),
                CHANGE `last_death` `last_death_ms` BIGINT(14)
        ");

        // change villages from x.y to x:y:map_id
        $this->execute("
            UPDATE `villages`
                SET `location`=CONCAT(
                    REPLACE(`location`, '.', ':'),
                    ':1'
                )
        ");

    }

    public function down() {

        // maps table
        $this->execute("DROP TABLE `maps`");

        // maps_location table
        $this->execute("DROP TABLE `maps_locations`");

        // maps_portal table
        $this->execute("DROP TABLE `maps_portals`");

        //users modifications

        // add new columns
        $this->execute("
            ALTER TABLE `users`
                DROP COLUMN `attack_id`,
                DROP COLUMN `attack_id_time_ms`,
                DROP COLUMN `filters`,
                DROP COLUMN `last_movement_ms`
        ");

        // change back to seconds
        $this->execute("
            ALTER TABLE `users`
                CHANGE `last_pvp_ms` `last_pvp` INT(11),
                CHANGE `last_ai_ms` `last_ai` INT(11),
                CHANGE `last_death_ms` `last_death` INT(11)
        ");

        // undo from x:y:map_id to x.y
        $this->execute("
            UPDATE `users`
                SET `location`=REPLACE(
                    SUBSTRING_INDEX(`location`, ':', 2),
                    ':',
                    '.'
                )
        ");

        // undo from x:y:map_id to x:y
        $this->execute("
            UPDATE `villages`
                SET `location`=REPLACE(
                    SUBSTRING_INDEX(`location`, ':', 2),
                    ':',
                    '.'
                )
        ");
    }
}
