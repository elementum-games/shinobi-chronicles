<?php

class UserReputation {
    public static array $VillageRep = [
        1 => [
            'title' => 'New Villager',
            'outlaw_title' => 'Vagabond',
            'min_rep' => 0,
            'weekly_cap' => 500,
            'base_pvp_rep_reward' => 1,
            'base_decay' => 0,
        ],
        2 => [
            'title' => 'Villager',
            'outlaw_title' => 'Thief',
            'min_rep' => 500,
            'weekly_cap' => 500,
            'base_pvp_rep_reward' => 1,
            'base_decay' => 300,
        ],
        3 => [
            'title' => 'Well-Known Villager',
            'outlaw_title' => 'Infamous',
            'min_rep' => 1000,
            'weekly_cap' => 500,
            'base_pvp_rep_reward' => 1,
            'base_decay' => 400,
        ],
        4 => [
            'title' => 'Respected Villager',
            'outlaw_title' => 'Outlaw',
            'min_rep' => 1750,
            'weekly_cap' => 600,
            'base_pvp_rep_reward' => 2,
            'base_decay' => 450,
        ],
        5 => [
            'title' => 'Shinobi',
            'outlaw_title' => 'Rogue',
            'min_rep' => 2500,
            'weekly_cap' => 600,
            'base_pvp_rep_reward' => 2,
            'base_decay' => 500,
        ],
        6 => [
            'title' => 'Respected Shinobi',
            'outlaw_title' => 'Infamous Rogue',
            'min_rep' => 3500,
            'weekly_cap' => 600,
            'base_pvp_rep_reward' => 2,
            'base_decay' => 550,
        ],
        7 => [
            'title' => 'Elite Shinobi',
            'outlaw_title' => 'Assassin',
            'min_rep' => 5000,
            'weekly_cap' => 700,
            'base_pvp_rep_reward' => 3,
            'base_decay' => 600,
        ],
        8 => [
            'title' => 'Master Shinobi',
            'outlaw_title' => 'Master Assassin',
            'min_rep' => 7500,
            'weekly_cap' => 800,
            'base_pvp_rep_reward' => 3,
            'base_decay' => 650,
        ],
        9 => [
            'title' => 'Legendary Shinobi',
            'outlaw_title' => 'Legendary Assassin',
            'min_rep' => 10000,
            'weekly_cap' => 900,
            'base_pvp_rep_reward' => 3,
            'base_decay' => 750,
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
    const DAILY_TASK_REWARDS = [
        DailyTask::DIFFICULTY_EASY => [
            DailyTask::ACTIVITY_ARENA => 1,
            DailyTask::ACTIVITY_MISSIONS => 2,
            DailyTask::ACTIVITY_PVP => 5,
        ],
        DailyTask::DIFFICULTY_MEDIUM => [
            DailyTask::ACTIVITY_ARENA => 3,
            DailyTask::ACTIVITY_MISSIONS => 5,
            DailyTask::ACTIVITY_PVP => 10,
        ],
        DailyTask::DIFFICULTY_HARD => [
            DailyTask::ACTIVITY_ARENA => 5,
            DailyTask::ACTIVITY_MISSIONS => 10,
            DailyTask::ACTIVITY_PVP => 15,
        ],
    ];
    const DECAY_MODIFIER = 0.65;

    protected int $rep;
    protected int $weekly_rep;
    public int $mission_cd;

    public int $rank;
    public string $rank_name;
    public int $weekly_cap;
    public int $base_pvp_reward;

    public function __construct(&$player_rep, &$player_weekly_rep, $mission_cd) {
        $this->rep = &$player_rep;
        $this->weekly_rep = &$player_weekly_rep;
        $this->mission_cd = $mission_cd;
        $this->rank = self::tierByRepAmount($this->rep);

        //Rep rank info
        $REP_RANK = self::$VillageRep[$this->rank];

        $this->rank_name = self::nameByRepRank($this->rank); // Use method here for future proofing
        $this->weekly_cap = $REP_RANK['weekly_cap'];
        $this->base_pvp_reward = $REP_RANK['base_pvp_rep_reward'];
    }

    /**
     * @param int $amount
     * @param bool $bypass_weekly_cap
     * @param bool $increment_weekly
     * @return int
     *
     * Increments user village reputation
     * $bypass_weekly_cap enabled will always reward the full amount of rep and add to weekly amount
     * $increment_weekly disabled will disable weekly reputation incrementing (USE SPARINGLY!)
     *
     * Returns amount of reputation awarded for display/data confirmation purposes
     */
    public function addRep(int $amount, bool $bypass_weekly_cap = false, bool $increment_weekly = true):int {
        //Adjust reputation gain if gain goes above cap
        if(!$bypass_weekly_cap) {
            $new_rep = $this->rep + $amount;

            // Determine if rep rank changes and modify weekly cap if change occurs
            $rep_rank_after = self::tierByRepAmount($new_rep);
            $weekly_cap = ($this->rank != $rep_rank_after) ? self::$VillageRep[$rep_rank_after]['weekly_cap'] : $this->weekly_cap;

            // Adjust gain to conform with weekly caps
            if($this->weekly_rep + $amount > $weekly_cap) {
                $amount = $weekly_cap - $this->weekly_rep;
            }
        }
        //Increment weekly rep by amount if method requires incrementing
        if($increment_weekly && $amount > 0 && $this->weekly_rep < $this->weekly_cap) {
            $this->weekly_rep += $amount;
            if($this->weekly_rep > $this->weekly_cap) {
                $this->weekly_rep = $this->weekly_cap;
            }
        }
        //Increment rep amount
        if($amount > 0) {
            $this->rep += $amount;
        }

        return $amount; // Use this return for display/gain confirmation
    }
    /**
     * @param int $amount
     * @param bool $decrement_weekly
     *
     * Decreases user rep by amount provided
     * If $decrement_weekly is enabled, this will also decrease weekly rep allowing for restricted methods to allow gains again
     */
    public function subtractRep(int $amount, bool $decrement_weekly = false) {
        $this->rep -= $amount;
        //TODO: TEMPORARY! Remove with negative reputation (outlaw update)
        if($this->rep < 0) {
            $this->rep = 0;
        }
        if($decrement_weekly && $this->weekly_rep > 0) {
            $this->weekly_rep -= $amount;
            if($this->weekly_rep < 0) {
                $this->weekly_rep = 0;
            }
        }
    }

    // Returns numeric value of reputation
    public function getRepAmount():int {
        return $this->rep;
    }
    // Returns numeric value of weekly reputaiton
    public function getWeeklyRepAmount():int {
        return $this->weekly_rep;
    }
    // Return of user can gain more rep for restricted methods
    public function canGain($check_mission_cd = false):bool {
        if($this->weekly_rep < $this->weekly_cap) {
            if($check_mission_cd && $this->mission_cd - time() > 0) {
                return false;
            }
            return true;
        }
        return false;
    }

    // Calculate and return reputation amount gain from arena fights
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
    // Calculate and return reputation gains/losses from pvp wins/losses
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


    /** MISC FUNCTIONS */

    // Returns reputation rank name based on reputation tier
    public static function nameByRepRank($tier, $title_type = 'title') {
        if(isset(self::$VillageRep[$tier])) {
            return self::$VillageRep[$tier][$title_type];
        }
        return "Invalid Rank";
    }
    // Returns numeric value of reputation tier based on amount of rep
    public static function tierByRepAmount($amount) {
        foreach(array_reverse(self::$VillageRep, true) as $rank => $data) {
            if(abs($amount) >= $data['min_rep']) {
                return $rank;
            }
        }
    }
}

