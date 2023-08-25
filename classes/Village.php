<?php

require_once __DIR__ . '/SpecialMission.php';

class Village {
    public System $system;
    public TravelCoords $coords;

    public string $name;
    public string $kage_name;
    public int $region_id;
    public int $village_id;

    // to-do: we should restructure how village data is being saved
    // player village should reference the village ID and this constructor should get row by ID
    public function __construct($system, $village) {
        $this->system = $system;

        $this->name = $village;
        $this->kage_name = $this->getKageName();
        $this->coords = $this->setVillageCoords();
        $this->region_id = $this->getRegionId($this->system, $this->name);
        $this->village_id = $this->getVillageId($this->system, $this->name);
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

    public static function getRegionId(System $system, string $village_name): int {
        $result = $system->db->query(
            "SELECT `region_id` FROM `villages`
                WHERE `villages`.`name`='{$village_name}'"
        );
        $result = $system->db->fetch($result);
        return $result['region_id'];
    }

    public static function getVillageId(System $system, string $village_name): int
    {
        $result = $system->db->query(
            "SELECT `village_id` FROM `villages`
                WHERE `villages`.`name`='{$village_name}'"
        );
        $result = $system->db->fetch($result);
        return $result['village_id'];
    }
}
