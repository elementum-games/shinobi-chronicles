<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class IncreaseMapSize extends AbstractMigration {
    public function up(): void {
        $this->execute("UPDATE `maps_locations` SET `x` = 4, `y` = 12 WHERE `name` = 'Sand'");
        $this->execute("UPDATE `maps_locations` SET `x` = 7, `y` = 5 WHERE `name` = 'Stone'");
        $this->execute("UPDATE `maps_locations` SET `x` = 10, `y` = 1 WHERE `name` = 'Font of Vitality'");
        $this->execute("UPDATE `maps_locations` SET `x` = 15, `y` = 9 WHERE `name` = 'Leaf'");
        $this->execute("UPDATE `maps_locations` SET `x` = 16, `y` = 15 WHERE `name` = 'Underground Colosseum'");
        $this->execute("UPDATE `maps_locations` SET `x` = 24, `y` = 15 WHERE `name` = 'Mist'");
        $this->execute("UPDATE `maps_locations` SET `x` = 25, `y` = 3 WHERE `name` = 'Cloud'");
    }

    public function down(): void {
        $this->execute("UPDATE `maps_locations` SET `x` = 3, `y` = 8 WHERE `name` = 'Sand'");
        $this->execute("UPDATE `maps_locations` SET `x` = 5, `y` = 3 WHERE `name` = 'Stone'");
        $this->execute("UPDATE `maps_locations` SET `x` = 10, `y` = 1 WHERE `name` = 'Font of Vitality'");
        $this->execute("UPDATE `maps_locations` SET `x` = 9, `y` = 6 WHERE `name` = 'Leaf'");
        $this->execute("UPDATE `maps_locations` SET `x` = 11, `y` = 9 WHERE `name` = 'Underground Colosseum'");
        $this->execute("UPDATE `maps_locations` SET `x` = 16, `y` = 10 WHERE `name` = 'Mist'");
        $this->execute("UPDATE `maps_locations` SET `x` = 17, `y` = 2 WHERE `name` = 'Cloud'");
    }
}
