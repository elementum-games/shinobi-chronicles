<?php

require_once __DIR__ . '/SpecialMission.php';

class Village {
    public System $system;
    public TravelCoords $coords;

    public static array $VillageRep = [
        1 => [
            'title' => 'New Villager',
            'outlaw_title' => 'Vagabond',
            'min_rep' => 0,
            'weekly_cap' => 500,
            'base_pvp_rep_reward' => 1,
        ],
        2 => [
            'title' => 'Villager',
            'outlaw_title' => 'Thief',
            'min_rep' => 500,
            'weekly_cap' => 500,
            'base_pvp_rep_reward' => 1,
        ],
        3 => [
            'title' => 'Well-Known Villager',
            'outlaw_title' => 'Infamous',
            'min_rep' => 1000,
            'weekly_cap' => 500,
            'base_pvp_rep_reward' => 1,
        ],
        4 => [
            'title' => 'Respected Villager',
            'outlaw_title' => 'Outlaw',
            'min_rep' => 1750,
            'weekly_cap' => 600,
            'base_pvp_rep_reward' => 2,
        ],
        5 => [
            'title' => 'Shinobi',
            'outlaw_title' => 'Rogue',
            'min_rep' => 2500,
            'weekly_cap' => 600,
            'base_pvp_rep_reward' => 2,
        ],
        6 => [
            'title' => 'Respected Shinobi',
            'outlaw_title' => 'Infamous Rogue',
            'min_rep' => 3500,
            'weekly_cap' => 600,
            'base_pvp_rep_reward' => 2,
        ],
        7 => [
            'title' => 'Elite Shinobi',
            'outlaw_title' => 'Master Rogue',
            'min_rep' => 5000,
            'weekly_cap' => 700,
            'base_pvp_rep_reward' => 3,
        ],
        8 => [
            'title' => 'Legendary Shinobi',
            'outlaw_title' => 'Assassin',
            'min_rep' => 7500,
            'weekly_cap' => 800,
            'base_pvp_rep_reward' => 3,
        ],
        9 => [
            'title' => 'Master Shinobi',
            'outlaw_title' => 'Master Assassin',
            'min_rep' => 10000,
            'weekly_cap' => 900,
            'base_pvp_rep_reward' => 3,
        ]
    ];

    const ARENA_MISSION_CD = 60;
    const MISSION_GAINS = [
        Mission::RANK_D => 1,
        Mission::RANK_C => 1,
        Mission::RANK_B => 2,
        Mission::RANK_A => 3,
        Mission::RANK_S => 4
    ];
    const SPECIAL_MISSION_REP_GAINS = [
        SpecialMission::DIFFICULTY_EASY => 1,
        SpecialMission::DIFFICULTY_NORMAL => 1,
        SpecialMission::DIFFICULTY_HARD => 2,
        SpecialMission::DIFFICULTY_NIGHTMARE => 3,
    ];
    const DAILY_TASK_GAINS = [
        DailyTask::DIFFICULTY_EASY . '_' . DailyTask::ACTIVITY_ARENA => 1,
        DailyTask::DIFFICULTY_MEDIUM . '_' . DailyTask::ACTIVITY_ARENA => 3,
        DailyTask::DIFFICULTY_HARD . '_' . DailyTask::ACTIVITY_ARENA => 5,

        DailyTask::DIFFICULTY_EASY . '_' . DailyTask::ACTIVITY_MISSIONS => 2,
        DailyTask::DIFFICULTY_MEDIUM . '_' . DailyTask::ACTIVITY_MISSIONS => 5,
        DailyTask::DIFFICULTY_HARD . '_' . DailyTask::ACTIVITY_MISSIONS => 8,

        DailyTask::DIFFICULTY_EASY . '_' . DailyTask::ACTIVITY_PVP => 5,
        DailyTask::DIFFICULTY_MEDIUM . '_' . DailyTask::ACTIVITY_PVP => 10,
        DailyTask::DIFFICULTY_HARD . '_' . DailyTask::ACTIVITY_PVP => 15,
    ];
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
        $rep_rank = $this->getRepRank($rep);
        $title = ($rep > 0) ? 'title' : 'outlaw_title';
        return self::$VillageRep[$rep_rank][$title];
    }
    public function getRepRank($rep) {
        foreach(array_reverse(self::$VillageRep, true) as $rank => $data) {
            if(abs($rep) >= $data['min_rep']) {
                return $rank;
            }
        }
    }
    public function calcArenaReputation($player_level, $opponent_level) {
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
    public function calcPvpRep($player_level, $player_rep_rank, $opponent_level, $opponent_rep_rank, $winner = true) {
        $level_difference = $player_level - $opponent_level;
        $rep_rank_difference = $player_rep_rank - $opponent_rep_rank;
        $rep_gain = 0;

        return 0;
        /*
        if($winner == true) {
            // Level based rewards
            // Opponent is no more than 3 to 5 levels below player
            if($level_difference >= -5 && $level_difference <= -3) {
                $rep_gain++;
            }
            // Opponent is within 2 levels of player
            if($level_difference >= -2 && $level_difference <= 2) {
                $rep_gain++;
            }
            // Opponent is 3 to 5 levels above player
            if($level_difference >= 3 && $level_difference <= 5) {
                $rep_gain++;
            }
            // Opponent is 6 or more levels above player
            if($level_difference >= 6) {
                $rep_gain++;
            }

            // Reputation difference rewards
            // Player is two tiers above opponent
            if($rep_rank_difference == 2) {
                $rep_gain++;
            }
            //Player is within 1 tier
            if($rep_rank_difference <= 1 && $player_rep_rank <= -1) {
                $rep_gain++;
            }
            //Opponent is 2 or more tiers above player
            if($rep_rank_difference <= -2) {
                $rep_gain++;
            }

        // Flat opponent rep rank reward
            $rep_gain += self::$VillageRep[$opponent_rep_rank]['base_pvp_rep_reward'];
        }
        if($winner == false) {
            $rep_gain = -2;
        // Opponent is 5 or more levels above player
            if($level_difference <= -5) {
                $rep_gain++; // Gain is negative by default, reduce rep loss
            }

        //Reputation based
            // Opponent is 2 or more tiers above player
            if($rep_rank_difference <= -2) {
                $rep_gain++; // Gain is negative by default, reduce rep loss
            }
            // Opponent is within 1 tier of player
            if($rep_rank_difference >= -1 && $rep_rank_difference <= 1) {
                $rep_gain--; // Increase rep loss
            }
            // Opponent is 2 or more tiers below player
            if($rep_rank_difference >= 2) {
                $rep_gain--;
            }

            // Redundancy to ensure no rep is rewarded
            if($rep_gain > 0) {
                $rep_gain = 0;
            }
        }

        return $rep_gain;
        */
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
