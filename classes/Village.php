<?php

require_once __DIR__ . '/SpecialMission.php';

class Village {
    public System $system;
    public TravelCoords $coords;

    public string $name;
    public string $kage_name;

    public function __construct($system, $village) {
        $this->system = $system;

        $this->name = $village;
        $this->kage_name = $this->getKageName();
        $this->coords = $this->setVillageCoords();
    }

    public function getKageName() {
        switch($this->name) {
            case 'Leaf':
                return 'Hokage';
            case 'Mist':
                return 'Mizukage';
            case 'Cloud':
                return 'Raikage';
            case 'Sand':
                return 'Kazekage';
            case 'Stone':
                return 'Tsuchikage';
            default:
                return "Kage";
        }
    }

    public function setVillageCoords() {
        return self::getLocation($this->system, $this->name);
    }

    public static function getLocation(System $system, string $village_name): ?TravelCoords {
        $result = $system->db->query(
            "SELECT `maps_locations`.`x`, `maps_locations`.`y`, `maps_locations`.`map_id` FROM `villages` 
                INNER JOIN `maps_locations` ON `villages`.`map_location_id`=`maps_locations`.`location_id`
                WHERE `villages`.`name`='{$village_name}' LIMIT 1"
        );
        if($system->db->last_num_rows != 0) {
            $result = $system->db->fetch($result);
            return new TravelCoords(
                x: (int)$result['x'],
                y: (int)$result['y'],
                map_id: (int)$result['map_id']
            );
        }

        return null;
    }
}
