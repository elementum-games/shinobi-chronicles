<?php

class UserReputation {
    public static array $VillageRep = [
        1 => [
            'title' => 'New Villager',
            'outlaw_title' => 'Vagabond',
            'min_rep' => 0,
            'weekly_cap' => 500,
            'weekly_pvp_cap' => 200,
            'base_pvp_rep_reward' => 1,
            'base_decay' => 0,
        ],
        2 => [
            'title' => 'Villager',
            'outlaw_title' => 'Thief',
            'min_rep' => 500,
            'weekly_cap' => 500,
            'weekly_pvp_cap' => 200,
            'base_pvp_rep_reward' => 1,
            'base_decay' => 400,
        ],
        3 => [
            'title' => 'Well-Known Villager',
            'outlaw_title' => 'Infamous',
            'min_rep' => 1000,
            'weekly_cap' => 500,
            'weekly_pvp_cap' => 200,
            'base_pvp_rep_reward' => 1,
            'base_decay' => 450,
        ],
        4 => [
            'title' => 'Respected Villager',
            'outlaw_title' => 'Outlaw',
            'min_rep' => 1750,
            'weekly_cap' => 600,
            'weekly_pvp_cap' => 250,
            'base_pvp_rep_reward' => 2,
            'base_decay' => 500,
        ],
        5 => [
            'title' => 'Shinobi',
            'outlaw_title' => 'Rogue',
            'min_rep' => 2500,
            'weekly_cap' => 600,
            'weekly_pvp_cap' => 250,
            'base_pvp_rep_reward' => 2,
            'base_decay' => 600,
        ],
        6 => [
            'title' => 'Respected Shinobi',
            'outlaw_title' => 'Infamous Rogue',
            'min_rep' => 3500,
            'weekly_cap' => 600,
            'weekly_pvp_cap' => 250,
            'base_pvp_rep_reward' => 2,
            'base_decay' => 650,
        ],
        7 => [
            'title' => 'Elite Shinobi',
            'outlaw_title' => 'Assassin',
            'min_rep' => 5000,
            'weekly_cap' => 700,
            'weekly_pvp_cap' => 300,
            'base_pvp_rep_reward' => 3,
            'base_decay' => 775,
        ],
        8 => [
            'title' => 'Master Shinobi',
            'outlaw_title' => 'Master Assassin',
            'min_rep' => 7500,
            'weekly_cap' => 800,
            'weekly_pvp_cap' => 400,
            'base_pvp_rep_reward' => 3,
            'base_decay' => 850,
        ],
        9 => [
            'title' => 'Legendary Shinobi',
            'outlaw_title' => 'Legendary Assassin',
            'min_rep' => 10000,
            'weekly_cap' => 900,
            'weekly_pvp_cap' => 500,
            'base_pvp_rep_reward' => 3,
            'base_decay' => 975,
        ]
    ];

    // Shop benefits
    const ITEM_SHOP_DISCOUNT_RATE = 5;
    const BENEFIT_CONSUMABLE_DISCOUNT = 'consumable_discount';
    const BENEFIT_GEAR_DISCOUNT = 'gear_discount';
    const BENEFIT_JUTSU_SCROLL_DISCOUNT = 'scroll_discount';
    // Training benefits
    const EFFICIENT_LONG_INCREASE = 5;
    const EFFICIENT_EXTENDED_INCREASE = 6.25;
    const BENEFIT_EFFICIENT_LONG = 'efficient_long';
    const BENEFIT_EFFICIENT_EXTENDED = 'efficient_extended';
    const BENEFIT_PARTIAL_TRAINING_GAINS = 'partial_trains';
    // Benefits array, add all benefits as inactive and turn them on in loadBenefits() as appropriate
    public static array $Benefits = [
        self::BENEFIT_CONSUMABLE_DISCOUNT => false,
        self::BENEFIT_GEAR_DISCOUNT => false,
        self::BENEFIT_JUTSU_SCROLL_DISCOUNT => false,
        self::BENEFIT_EFFICIENT_LONG => false,
        self::BENEFIT_EFFICIENT_EXTENDED => false,
        self::BENEFIT_PARTIAL_TRAINING_GAINS => false,
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
    const DECAY_MODIFIER = 0.65; // Reduce reputation decay by 35% if weekly cap is met

    const PVP_REP_ENABLED = true;
    const RECENT_PLAYER_KILL_THRESHOLD = 3600; // Only kills within last hour will mitigate pvp rep gains
    const PVP_CHAIN_KILL_LIMIT = 4; // Pvp kills of same player above this amount reward 0 rep
    const MIN_DIMINISHED_REP = 2; // Minimum rep gain on diminished returns (will remain 0 on mitigated kills)
    const RECENTLY_KILLED_BY_THRESHOLD = 1800; // Only being killed within last 30 minutes will mitigate pvp rep losses (further chainkill mitigation)
    const PVP_REP_LOSS_LIMIT = 3; // Being killed by the same player above this amount will result in 0 rep loss
    const PVP_REP_RESET_DAILY = true; // If false, weekly pvp rep cap is used
    const PVP_WEEKLY_CONVERSION = 0.25; // Allow 25% of weekly reputation cap if daily reset is used

    protected int $rep;
    protected int $weekly_rep;
    protected int $pvp_rep;
    public int $last_pvp_rep_reset;
    public int $mission_cd;

    public int $rank;
    public string $rank_name;
    public int $weekly_cap;
    public int $pvp_cap;
    // TODO: Make recent_killed private
    public ?string $recent_players_killed_ids;
    public array $recent_players_killed_ids_array;
    private ?string $recent_killer_ids;
    public array $recent_killer_ids_array;
    public int $base_pvp_reward;
    public array $benefits;
    
    public function __construct(&$player_rep, &$player_weekly_rep, &$player_pvp_rep, &$player_last_pvp_rep_reset, &$last_pvp_kills, &$last_killer_ids, $mission_cd, $event) {
        //Player data
        $this->rep = &$player_rep;
        $this->weekly_rep = &$player_weekly_rep;
        $this->mission_cd = $mission_cd;
        $this->rank = self::tierByRepAmount($this->rep);

        //PvP data
        $this->pvp_rep = &$player_pvp_rep;
        $this->last_pvp_rep_reset = &$player_last_pvp_rep_reset;
        $this->recent_players_killed_ids = &$last_pvp_kills;
        $this->recent_killer_ids = &$last_killer_ids;

        //Reset daily/weekly rep
        if(self::PVP_REP_RESET_DAILY && time() - $this->last_pvp_rep_reset >= 86400) {
            $this->resetPvpRep();
        }
        else {
            if(time() - $this->last_pvp_rep_reset >= 86400*7) {
                $this->resetPvpRep();
            }
        }

        //Force weekly reputation for events
        if(!is_null($event)) {
            self::PVP_REP_RESET_DAILY = false;
        }

        //Load pvp kills/killer arrays
        $this->loadPvpKillsArray();
        // Load benefits - this may only be performed after determining reputation rank 
        $this->benefits = $this->loadBenefits();

        //Rep rank info
        $REP_RANK = self::$VillageRep[$this->rank];

        $this->rank_name = self::nameByRepRank($this->rank); // Use method here for future proofing
        $this->weekly_cap = $REP_RANK['weekly_cap'];
        $this->pvp_cap = (self::PVP_REP_RESET_DAILY) ? floor($REP_RANK['weekly_pvp_cap'] * self::PVP_WEEKLY_CONVERSION) : $REP_RANK['weekly_pvp_cap'];
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
     * $increment_pvp enabled will add towards pvp reputation threshold
     * $increment_weekly disabled will disable weekly reputation incrementing (USE SPARINGLY!)
     *
     * Returns amount of reputation awarded for display/data confirmation purposes
     */
    public function addRep(int $amount, bool $bypass_weekly_cap = false, bool $increment_pvp = false, bool $increment_weekly = true):int {
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
        //Increment pvp rep amount
        if($increment_pvp) {
            $this->pvp_rep += $maount;
            if($this->pvp_rep > $this->pvp_cap) {
                $this->pvp_rep = $this->pvp_cap;
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

    public function resetPvpRep() {
        $this->pvp_rep = 0;
        $this->last_pvp_rep_reset = time();
    }

    // Returns numeric value of reputation
    public function getRepAmount():int {
        return $this->rep;
    }
    // Returns numeric value of weekly reputaiton
    public function getWeeklyRepAmount():int {
        return $this->weekly_rep;
    }
    // Returns numeric value of weekly pvp reputaiton
    public function getWeeklyPvpRepAmount():int {
        return $this->pvp_rep;
    }
    // Return of user can gain more rep for restricted methods
    public function canGain($check_mission_cd = false, $check_pvp = false):bool {
        // Check mission cd
        if($check_mission_cd && $this->mission_cd > time()) {
            return false;
        }
        // Check pvp cap
        if($check_pvp && $this->pvp_rep >= $this->pvp_cap) {
            return false;
        }
        // Check weekly cap
        if($this->weekly_rep > $this->weekly_cap && !$check_pvp) {
            return false;
        }
        return true;
    }
    // Load reputation benefits
    private function loadBenefits() {
        $benefits = self::$Benefits;

        // Active benefits based on rank
        switch($this->rank) {
            case 9:
            case 8:
            case 7:
                $benefits[self::BENEFIT_PARTIAL_TRAINING_GAINS] = true;
            case 6:
                $benefits[self::BENEFIT_EFFICIENT_LONG] = true;
                $benefits[self::BENEFIT_EFFICIENT_EXTENDED] = true;
            case 5:
                $benefits[self::BENEFIT_JUTSU_SCROLL_DISCOUNT] = true;
            case 4:
                $benefits[self::BENEFIT_GEAR_DISCOUNT] = true;
            case 3:
                $benefits[self::BENEFIT_CONSUMABLE_DISCOUNT] = true;
            case 2:
            case 1:
        }

        return $benefits;
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
    public function calcPvpRep($player_level, $player_rep_rank, $opponent_level, $opponent_rep_rank, $opponent_user_id, $winner = true) {
        $player_levels_above_opponent = $player_level - $opponent_level;
        $rep_rank_difference = $player_rep_rank - $opponent_rep_rank;
        $rep_gain = 0;

        // Pvp reputation disabled
        if(!self::PVP_REP_ENABLED) {
            return 0;
        }

        if($winner == true) {
            // Set current kill
            $this->recent_players_killed_ids_array[$opponent_user_id][] = time();
            // Get kill count
            $kill_count = isset($this->recent_players_killed_ids_array[$opponent_user_id]) ? sizeof($this->recent_players_killed_ids_array[$opponent_user_id]) : 0;
            // Encode and set last pvp kills
            $this->encodePvpKills();
            
            // Opponent killed too many times in mitigation frame, no gain
            if($kill_count > self::PVP_CHAIN_KILL_LIMIT) {
                return 0;
            }

            // Weekly rep limit
            if($this->pvp_rep > $this->pvp_cap) {
                return 0;
            }

            // Level based rewards
            // Opponent is no more than 5 levels below player
            if($player_levels_above_opponent <= 5) {
                $rep_gain++;
            }
            // Opponent is no more than 2 levels below player
            if($player_levels_above_opponent <= 2) {
                $rep_gain++;
            }
            // Opponent is 3 or more levels above player
            if($player_levels_above_opponent <= -3) {
                $rep_gain++;
            }
            // Opponent is 6 or more levels above player
            if($player_levels_above_opponent <= -6) {
                $rep_gain++;
            }

            // Reputation difference rewards
            // Player is no more than two tiers above opponent
            if($rep_rank_difference <= 2) {
                $rep_gain++;
            }
            //Player is no more than 1 tier above opponent
            if($rep_rank_difference <= 1) {
                $rep_gain++;
            }
            //Opponent is 2 or more tiers above player
            if($rep_rank_difference <= -2) {
                $rep_gain++;
            }

            // Flat opponent rep rank reward
            $rep_gain += self::$VillageRep[$opponent_rep_rank]['base_pvp_rep_reward'];

            // Diminishing returns
            if($kill_count > 0) {
                if($kill_count / self::PVP_CHAIN_KILL_LIMIT > 0.5) {
                    $rep_gain = floor($rep_gain * 0.5);
                }
                if($kill_count / self::PVP_CHAIN_KILL_LIMIT >= 0.75) {
                    $rep_gain = ceil($rep_gain * 0.25);
                }
                $rep_gain = ($rep_gain < self::MIN_DIMINISHED_REP) ? self::MIN_DIMINISHED_REP : $rep_gain;
            }
        }
        if($winner == false) {
            // Set current loss
            $this->recent_killer_ids_array[$opponent_user_id][] = time();
            // Get loss count
            $loss_count = isset($this->recent_killer_ids_array[$opponent_user_id]) ? sizeof($this->recent_killer_ids_array[$opponent_user_id]) : 0;
            // Encode and set last pvp data
            $this->encodePvpKills();

            // Rep loss mitigation (chain kills only)
            if($loss_count >= self::PVP_REP_LOSS_LIMIT) {
                return 0;
            }
            
            $rep_loss = 2;
            // Opponent is 5 or more levels above player
            if($player_levels_above_opponent <= -5) {
                $rep_loss--; // Gain is negative by default, reduce rep loss
            }

            //Reputation based
            // Opponent is 2 or more tiers above player
            if($rep_rank_difference <= -2) {
                $rep_loss--; // Gain is negative by default, reduce rep loss
            }
            // Opponent is within 1 tier of player
            if($rep_rank_difference >= -1) {
                $rep_loss++; // Increase rep loss
            }
            // Opponent is 2 or more tiers below player
            if($rep_rank_difference >= 2) {
                $rep_loss += 2;
            }

            // Redundancy to ensure rep is not removed when it shouldn't be
            if($rep_gain < 0) {
                $rep_loss = 0;
            }
        }

        return $rep_loss;
    }
    // Encode and set player last pvp kills
    public function encodePvpKills() {
        $this->recent_players_killed_ids = json_encode($this->recent_players_killed_ids_array);
        $this->recent_killer_ids = jseon_encode($this->recent_killer_ids_array);
    }
    // Load pvp kills and remove outdated kills to prvent data bloat
    public function loadPvpKillsArray() {
        // Recently killed players
        if(is_array(json_decode($this->recent_players_killed_ids, true))) {
            $this->recent_players_killed_ids_array = json_decode($this->recent_players_killed_ids, true);

            // Remove outdated data to prevent bloat
            foreach($this->recent_players_killed_ids_array as $UID => $kills) {
                // Remove any invalid kill times from arrays
                foreach($kills as $key => $time) {
                    if($time + self::RECENT_PLAYER_KILL_THRESHOLD <= time()) {
                        unset($this->recent_players_killed_ids_array[$UID][$key]);
                    }
                }

                // Kill array empty, remove id from array
                if(empty($this->recent_players_killed_ids_array[$UID])) {
                    unset($this->recent_players_killed_ids_array[$UID]);
                }
            }
        }
        else {
            $this->recent_players_killed_ids_array = array();
        }

        // Recently kileld by
        if(is_array(json_decode($this->recent_killer_ids, true))) {
            $this->recent_killer_ids_array = json_decode($this->recent_killer_ids, true);

            // Remove outdated data to prevent bloat
            foreach($this->recent_killer_ids_array as $UID => $kills) {
                // Remove any invalid kill times from arrays
                foreach($kills as $key => $time) {
                    if($time + self::RECENTLY_KILLED_BY_THRESHOLD <= time()) {
                        unset($this->recent_killer_ids_array[$UID][$key]);
                    }
                }

                // Kill array empty, remove id from array
                if(empty($this->recent_killer_ids_array[$UID])) {
                    unset($this->recent_killer_ids_array[$UID]);
                }
            }
        }
        else {
            $this->recent_killer_ids_array = array();
        }
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

