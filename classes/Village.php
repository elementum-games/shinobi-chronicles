<?php

class Village {
    public System $system;
    public TravelCoords $coords;

    public static array $VillageRep = [
        1 => [
            'title' => 'New Villager',
            'outlaw_title' => 'Thief',
            'min_rep' => 0,
        ],
        2 => [
            'title' => 'Villager',
            'outlaw_title' => 'Outlaw',
            'min_rep' => 100,
        ],
        3 => [
            'title' => 'Well-Known Villager',
            'outlaw_title' => 'Infamous',
            'min_rep' => 150,
        ],
        4 => [
            'title' => 'Respected Villager',
            'outlaw_title' => 'Rogue',
            'min_rep' => 300,
        ],
        5 => [
            'title' => 'Shinobi',
            'outlaw_title' => 'Elite Rogue',
            'min_rep' => 500,
        ],
        6 => [
            'title' => 'Well-known Shinobi',
            'outlaw_title' => 'Legendary Rogue',
            'min_rep' => 700,
        ],
        7 => [
            'title' => 'Respected Shinobi',
            'outlaw_title' => 'Assassin',
            'min_rep' => 1000,
        ],
        8 => [
            'title' => 'Elite Shinobi',
            'outlaw_title' => 'Elite Assassin',
            'min_rep' => 1500,
        ],
        9 => [
            'title' => 'Legendary Shinobi',
            'outlaw_title' => 'Legendary Assassin',
            'min_rep' => 3000
        ],
        1337 => [
            'title' => 'Mythos',
            'outlaw_title' => 'Mythos',
            'min_rep' => 2147483647
        ]
    ];
    public static array $VillageRenown = [

    ];
    const ARENA_MISSION_CD = 300;
    const WEEKLY_REP_CAP = 150;
    const MISSION_GAINS = [
        Mission::RANK_D => 1,
        Mission::RANK_C => 1,
        Mission::RANK_B => 2,
        Mission::RANK_A => 3,
        Mission::RANK_S => 4
    ];
    const DAILY_TASK = [
        DailyTask::DIFFICULTY_EASY => 1,
        DailyTask::DIFFICULTY_MEDIUM => 2,
        DailyTask::DIFFICULTY_HARD => 3,
    ];
    const PVP_REP = 3;
    const DECAY = 0.95;

    public string $name;
    public string $kage_name;

    public function __construct($system, $village) {
        $this->system = $system;

        $this->name = $village;
        $this->kage_name = $this->getKageName();
        $this->coords = $this->setVillageCoords();
    }

    public function getRepName($rep) {
        foreach(array_reverse(self::$VillageRep, true) as $tier) {
            if (abs($rep) >= $tier['min_rep']) {
                $title = 'title';
                if($rep < 0) {
                    $title = 'outlaw_title';
                }
                return $tier[$title];
            }
        }
        return '???';
    }
    public function awardArenaReputation($player_level, $opponent_level) {
        if($player_level > $opponent_level) {
            if($player_level - $opponent_level >= 2) {
                return 0;
            }
            return 1;
        }
        else {
            $level_difference = $opponent_level - $player_level;
            if($level_difference <= 2) {
                return 1;
            }
            else {
                return 2;
            }
        }
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
}