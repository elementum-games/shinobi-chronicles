<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class PointVillagesToMapLocations extends AbstractMigration {
    public function up(): void {
        $this->execute("ALTER TABLE `villages` add column `map_location_id` int");

        $this->execute("
            UPDATE `villages` SET `map_location_id`=3 WHERE `name`='Stone';
            UPDATE `villages` SET `map_location_id`=4 WHERE `name`='Cloud';
            UPDATE `villages` SET `map_location_id`=5 WHERE `name`='Leaf';
            UPDATE `villages` SET `map_location_id`=6 WHERE `name`='Sand';
            UPDATE `villages` SET `map_location_id`=7 WHERE `name`='Mist';
        ");
    }

    public function down(): void {
        $this->execute("ALTER TABLE `villages` drop column `map_location_id`;");
    }
}
