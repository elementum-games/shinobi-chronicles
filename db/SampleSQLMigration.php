<?php

use Phinx\Migration\AbstractMigration;

class MyNewMigration extends AbstractMigration {
    /**
     * Migrate Up.
     */
    public function up() {
        $this->execute(
            "CREATE TABLE `villages` (
            `village_id` int(11) NOT NULL AUTO_INCREMENT,
              `name` varchar(32) DEFAULT NULL,
              `location` varchar(8) DEFAULT NULL,
              `points` int(11) DEFAULT '0',
              `leader` int(11) NOT NULL DEFAULT '0',
              PRIMARY KEY (`village_id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=`latin1`;"
        );

        $this->execute(
            "INSERT INTO `villages` (`village_id`, `name`, `location`, `points`, `leader`) VALUES
            (1, 'Stone', '5.3', 0, 0),
            (2, 'Cloud', '17.2', 0, 0),
            (3, 'Leaf', '9.6', 0, 0),
            (4, 'Sand', '3.8', 0, 0),
            (5, 'Mist', '16.10', 0, 0);"
        );
    }

    /**
     * Migrate Down.
     *
     * This should reverse all the changes you made in the `up` method, leaving the database in the same state as before.
     */
    public function down() {
        $this->execute("DROP TABLE `villages`;");
    }
}