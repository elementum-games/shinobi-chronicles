<?php

class Village {
    public System $system;
    public TravelCoords $coords;

    public string $name;
    public string $kage_name;

    const ARENA_REP_GAIN = 1;
    const PVP_REP_GAIN = 3;
    const DAILY_REP_GAIN = [
        DailyTask::DIFFICULTY_EASY => 3,
        DailyTask::DIFFICULTY_MEDIUM => 6,
        DailyTask::DIFFICULTY_HARD => 10,
    ];

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
    public static function getRepName($rep_amount) {
        return self::repName($rep_amount);
    }

    public function setVillageCoords() {
        return self::getLocation($this->system, $this->name);
    }

    public static function getLocation(System $system, string $village_name): ?TravelCoords {
        $result = $system->query(
            "SELECT `maps_locations`.`x`, `maps_locations`.`y`, `maps_locations`.`map_id` FROM `villages` 
                INNER JOIN `maps_locations` ON `villages`.`map_location_id`=`maps_locations`.`location_id`
                WHERE `villages`.`name`='{$village_name}' LIMIT 1"
        );
        if($system->db_last_num_rows != 0) {
            $result = $system->db_fetch($result);
            return new TravelCoords(
                x: (int)$result['x'],
                y: (int)$result['y'],
                map_id: (int)$result['map_id']
            );
        }

        return null;
    }
    public static function repName($rep_amount) {
        if($rep_amount >= 0) {
            if($rep_amount >= 2000) {
                return 'Legendary Shinobi';
            }
            elseif($rep_amount >= 1000) {
                return 'Elite Shinobi';
            }
            elseif($rep_amount >= 600) {
                return 'Respected Shinobi';
            }
            elseif($rep_amount >= 500) {
                return 'Well-known Shinobi';
            }
            elseif($rep_amount >= 400) {
                return 'Shinobi';
            }
            elseif($rep_amount >= 300) {
                return 'Respected Villager';
            }
            elseif($rep_amount >= 200) {
                return 'Well-known Villager';
            }
            elseif($rep_amount >= 100) {
                return 'Villager';
            }
            else {
                return 'New Villager';
            }
        }
        elseif($rep_amount < 0) {
            return 'Outlaw';
        }
        return '????';
    }
}