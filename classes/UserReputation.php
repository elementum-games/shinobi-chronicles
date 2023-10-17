<?php

require_once __DIR__ . "/war/Operation.php";

class UserReputation {
    public static array $VillageRep = [
        1 => [
            'title' => 'Villager',
            'outlaw_title' => 'Vagabond',
            'min_rep' => 0,
            'weekly_cap' => 1000,
            'weekly_pvp_cap' => 1500,
            'base_pvp_rep_reward' => 0,
            'base_decay' => 0,
        ],
        2 => [
            'title' => 'Aspiring Shinobi',
            'outlaw_title' => 'Thief',
            'min_rep' => 2500,
            'weekly_cap' => 1000,
            'weekly_pvp_cap' => 1500,
            'base_pvp_rep_reward' => 2,
            'base_decay' => 0,
        ],
        3 => [
            'title' => 'Shinobi',
            'outlaw_title' => 'Bandit',
            'min_rep' => 5000,
            'weekly_cap' => 1000,
            'weekly_pvp_cap' => 1500,
            'base_pvp_rep_reward' => 2,
            'base_decay' => 0,
        ],
        4 => [
            'title' => 'Experienced Shinobi',
            'outlaw_title' => 'Raider',
            'min_rep' => 7500,
            'weekly_cap' => 1000,
            'weekly_pvp_cap' => 1500,
            'base_pvp_rep_reward' => 3,
            'base_decay' => 250,
        ],
        5 => [
            'title' => 'Veteran Shinobi',
            'outlaw_title' => 'Marauder',
            'min_rep' => 10000,
            'weekly_cap' => 1000,
            'weekly_pvp_cap' => 1500,
            'base_pvp_rep_reward' => 3,
            'base_decay' => 500,
        ],
        6 => [
            'title' => 'Expert Shinobi',
            'outlaw_title' => 'Rogue',
            'min_rep' => 15000,
            'weekly_cap' => 1000,
            'weekly_pvp_cap' => 1500,
            'base_pvp_rep_reward' => 3,
            'base_decay' => 750,
        ],
        7 => [
            'title' => 'Elite Shinobi',
            'outlaw_title' => 'Notorious Rogue',
            'min_rep' => 22500,
            'weekly_cap' => 1000,
            'weekly_pvp_cap' => 1500,
            'base_pvp_rep_reward' => 4,
            'base_decay' => 1000,
        ],
        8 => [
            'title' => 'Master Shinobi',
            'outlaw_title' => 'Infamous Rogue',
            'min_rep' => 35000,
            'weekly_cap' => 1000,
            'weekly_pvp_cap' => 1500,
            'base_pvp_rep_reward' => 4,
            'base_decay' => 1000,
        ],
        9 => [
            'title' => 'Legendary Shinobi',
            'outlaw_title' => 'Legendary Rogue',
            'min_rep' => 50000,
            'weekly_cap' => 1000,
            'weekly_pvp_cap' => 1500,
            'base_pvp_rep_reward' => 4,
            'base_decay' => 1500,
        ]
    ];

    // Shop benefits
    const ITEM_SHOP_DISCOUNT_RATE = 10;
    const BENEFIT_CONSUMABLE_DISCOUNT = 'consumable_discount';
    const BENEFIT_GEAR_DISCOUNT = 'gear_discount';
    const BENEFIT_JUTSU_SCROLL_DISCOUNT = 'scroll_discount';

    // Training benefits
    const EFFICIENT_LONG_INCREASE = 5;
    const EFFICIENT_EXTENDED_INCREASE = 6.25;
    const JUTSU_TRAINING_BONUS = 25;
    const BENEFIT_EFFICIENT_LONG = 'efficient_long';
    const BENEFIT_EFFICIENT_EXTENDED = 'efficient_extended';
    const BENEFIT_PARTIAL_TRAINING_GAINS = 'partial_trains';
    const BENEFIT_JUTSU_TRAINING_BONUS = 'jutsu_bonus';

    // Other benefits
    const FREE_TRANSFER_BONUS = 50;
    const BENEFIT_FREE_TRANSFER_BONUS = 'free_transfer_bonus';

    // Benefits array, add all benefits as inactive and turn them on in loadBenefits() as appropriate
    public static array $Benefits = [
        self::BENEFIT_CONSUMABLE_DISCOUNT => false,
        self::BENEFIT_GEAR_DISCOUNT => false,
        self::BENEFIT_JUTSU_SCROLL_DISCOUNT => false,
        self::BENEFIT_EFFICIENT_LONG => false,
        self::BENEFIT_EFFICIENT_EXTENDED => false,
        self::BENEFIT_PARTIAL_TRAINING_GAINS => false,
        self::BENEFIT_JUTSU_TRAINING_BONUS => false,
        self::BENEFIT_FREE_TRANSFER_BONUS => false,
    ];

    // Limits
    const ARENA_MISSION_CD = 0;
    const MISSION_GAINS = [
        Mission::RANK_D => 1,
        Mission::RANK_C => 2,
        Mission::RANK_B => 4,
        Mission::RANK_A => 6,
        Mission::RANK_S => 8
    ];
    const SPECIAL_MISSION_REP_GAINS = [
        SpecialMission::DIFFICULTY_EASY => 2,
        SpecialMission::DIFFICULTY_NORMAL => 4,
        SpecialMission::DIFFICULTY_HARD => 6,
        SpecialMission::DIFFICULTY_NIGHTMARE => 8,
    ];
    const OPERATION_GAINS = [
        Operation::OPERATION_REINFORCE => 3,
        Operation::OPERATION_INFILTRATE => 4,
        Operation::OPERATION_RAID => 5,
        Operation::OPERATION_LOOT => 1,
    ];

    const DAILY_TASK_REWARDS = [
        DailyTask::DIFFICULTY_EASY => [
            DailyTask::ACTIVITY_EARN_MONEY => 5,
            DailyTask::ACTIVITY_ARENA => 20,
            DailyTask::ACTIVITY_TRAINING => 10,
            DailyTask::ACTIVITY_MISSIONS => 20,
            DailyTask::ACTIVITY_PVP => 10,
        ],
        DailyTask::DIFFICULTY_MEDIUM => [
            DailyTask::ACTIVITY_EARN_MONEY => 10,
            DailyTask::ACTIVITY_ARENA => 30,
            DailyTask::ACTIVITY_TRAINING => 15,
            DailyTask::ACTIVITY_MISSIONS => 30,
            DailyTask::ACTIVITY_PVP => 15,
        ],
        DailyTask::DIFFICULTY_HARD => [
            DailyTask::ACTIVITY_EARN_MONEY => 15,
            DailyTask::ACTIVITY_ARENA => 40,
            DailyTask::ACTIVITY_TRAINING => 20,
            DailyTask::ACTIVITY_MISSIONS => 40,
            DailyTask::ACTIVITY_PVP => 20,
        ],
    ];

    const DAILY_TASK_PVP_WIN_MOD = 5; // Increase rep by this amount for tasks requiring pvp wins (harder than completes)f
    const DAILY_TASK_BYPASS_CAP = false;

    const WEEKLY_CAP_MET_DECAY_MULTIPLIER = 1; // Reduce reputation decay by 30% if weekly cap is met

    const MAX_PVP_LEVEL_DIFFERENCE = 20;
    const MAX_PVP_REP_TIER_DIFFERENCE = 4;

    const PVP_MEDIAN_LEVEL_BASED_GAIN = 4; // amount gained when fighting someone of the same level (TODO: 5)
    const PVP_MEDIAN_REP_TIER_BASED_GAIN = 2; // amount gained when fighting someone of the same rep tier (TODO: 7)

    const PVP_REP_ENABLED = true;

    // Only kills within last hour will mitigate pvp rep gains
    const RECENT_PLAYER_KILL_THRESHOLD = 3600;

    // Killing the same player more than this many times in an hour does not add/deduct rep for either person
    const PVP_CHAIN_KILL_LIMIT = 4;

    // Only being killed within last 30 minutes will mitigate pvp rep losses (further chainkill mitigation)
    const RECENTLY_KILLED_BY_THRESHOLD = 1800;

    const SPAR_REP_LOSS = 3;
    const SPAR_REP_DRAW = 5;
    const SPAR_REP_WIN = 7;

    protected ?Event $event;

    protected int $rep;
    protected int $weekly_rep;
    protected int $weekly_pvp_rep;
    public int $mission_cd;

    public int $rank;
    public string $rank_name;
    public int $weekly_cap;
    public int $weekly_pvp_cap;

    // TODO: Make recent_killed private
    public ?string $recent_players_killed_ids;
    public array $recent_players_killed_ids_array;
    private ?string $recent_killer_ids;
    public array $recent_killer_ids_array;
    public int $base_pvp_reward;
    public array $benefits;

    public function __construct(&$player_rep, &$player_weekly_rep, &$player_pvp_rep, &$last_pvp_kills, &$last_killer_ids, $mission_cd, $event) {
        //System data
        $this->event = $event;

        //Player data
        $this->rep = &$player_rep;
        $this->weekly_rep = &$player_weekly_rep;
        $this->mission_cd = $mission_cd;
        $this->rank = self::tierByRepAmount($this->rep);

        //PvP data
        $this->weekly_pvp_rep = &$player_pvp_rep;
        $this->recent_players_killed_ids = &$last_pvp_kills;
        $this->recent_killer_ids = &$last_killer_ids;

        //Load pvp kills/killer arrays
        $this->loadPvpKillsArray();
        // Load benefits - this may only be performed after determining reputation rank
        $this->benefits = $this->loadBenefits();

        //Rep rank info
        $REP_RANK = self::$VillageRep[$this->rank];

        $this->rank_name = self::nameByRepRank($this->rank); // Use method here for future proofing
        $this->weekly_cap = $REP_RANK['weekly_cap'];
        $this->weekly_pvp_cap = $REP_RANK['weekly_pvp_cap'];
        $this->base_pvp_reward = $REP_RANK['base_pvp_rep_reward'];
    }

    /**
     * @param int  $amount
     * @param bool $bypass_weekly_cap
     * @param bool $increment_pvp
     * @return int
     *
     * Increments user village reputation
     * $bypass_weekly_cap enabled will always reward the full amount of rep and add to weekly amount
     * $increment_pvp enabled will add towards pvp reputation threshold
     *
     * Returns amount of reputation awarded for display/data confirmation purposes
     */
    public function addRep(int $amount, bool $bypass_weekly_cap = false, bool $increment_pvp = false): int {
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
            $this->weekly_rep += $amount;
        }

        //Double reputation
        if (!empty($this->event) && $this->event instanceof DoubleReputation) {
            $amount *= DoubleReputation::rep_modifier;
        }

        // Increment Pvp rep
        if($increment_pvp) {
            if($this->weekly_pvp_rep + $amount > $this->weekly_pvp_cap) {
                $amount = $this->weekly_pvp_cap - $this->weekly_pvp_rep;
            }
            $this->weekly_pvp_rep += $amount;
        }
        //Increment rep amount
        if($amount > 0) {
            $this->rep += $amount;
        }

        return $amount; // Use this return for display/gain confirmation
    }

    /**
     * @param int $amount
     *
     * Decreases user rep by amount provided
     * If $decrement_weekly is enabled, this will also decrease weekly rep allowing for restricted methods to allow gains again
     */
    public function subtractRep(int $amount): void {
        $this->rep -= $amount;
        $this->weekly_pvp_rep -= $amount;
        $this->weekly_pvp_rep = max(0, $this->weekly_pvp_rep);
        //TODO: TEMPORARY! Remove with negative reputation (outlaw update)
        if($this->rep < 0) {
            $this->rep = 0;
        }
    }

    public function resetPvpRep(): void {
        $this->weekly_pvp_rep = 0;
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
    public function getWeeklyPvpRep():int {
        return $this->weekly_pvp_rep;
    }

    // Return of user can gain more rep for restricted methods
    public function canGain($check_mission_cd = false, $check_pvp = false):bool {
        // Check mission cd
        if($check_mission_cd && $this->mission_cd > time()) {
            return false;
        }
        // Check pvp cap
        if($check_pvp && $this->weekly_pvp_rep >= $this->weekly_pvp_cap) {
            return false;
        }
        // Check weekly cap
        if($this->weekly_rep > $this->weekly_cap && !$check_pvp) {
            return false;
        }
        return true;
    }

    // Load reputation benefits
    private function loadBenefits(): array {
        $benefits = self::$Benefits;

        // Active benefits based on rank
        switch($this->rank) {
            case 9: // 50000 Reputation
            case 8: // 35000 Reputation
            case 7: // 22500 Reputation
            case 6: // 15000 Reputation
            case 5: // 10000 Reputation
                $benefits[self::BENEFIT_FREE_TRANSFER_BONUS] = true;
            case 4: // 7500 Reputation
                $benefits[self::BENEFIT_JUTSU_TRAINING_BONUS] = true;
            case 3: // 5000 Reputation
                $benefits[self::BENEFIT_EFFICIENT_LONG] = true;
                $benefits[self::BENEFIT_EFFICIENT_EXTENDED] = true;
            case 2: // 2500 Reputation
                $benefits[self::BENEFIT_GEAR_DISCOUNT] = true;
                $benefits[self::BENEFIT_CONSUMABLE_DISCOUNT] = true;
                $benefits[self::BENEFIT_JUTSU_SCROLL_DISCOUNT] = true;
            case 1: // baseline
        }

        return $benefits;
    }

    // Calculate and return reputation amount gain from arena fights
    public function calcArenaReputation($player_level, $opponent_level): int {
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
    public function handlePvPWin(User $player, Fighter $opponent, bool $retreat = false): int {
        if(!($opponent instanceof User)) {
            return 0;
        }

        // Pvp reputation disabled
        if(!self::PVP_REP_ENABLED) {
            return 0;
        }

        $player_levels_above_opponent = $player->level - $opponent->level;
        $player_rep_tiers_above_opponent = $player->reputation->rank - $opponent->reputation->rank;

        // Set current kill
        $this->recent_players_killed_ids_array[$opponent->user_id][] = time();
        // Get kill count
        $kill_count = isset($this->recent_players_killed_ids_array[$opponent->user_id]) ? sizeof($this->recent_players_killed_ids_array[$opponent->user_id]) : 0;
        // Encode and set last pvp kills
        $this->encodePvpKills();

        // Opponent killed too many times in mitigation frame, no gain
        if($kill_count > self::PVP_CHAIN_KILL_LIMIT) {
            return 0;
        }

        // Weekly rep limit
        if($this->weekly_pvp_rep > $this->weekly_pvp_cap) {
            return 0;
        }

        // Hard limits - no gains if >20 levels over opponent or >2 rep tiers
        if($player_levels_above_opponent > self::MAX_PVP_LEVEL_DIFFERENCE) {
            return 0;
        }
        if($player_rep_tiers_above_opponent > self::MAX_PVP_REP_TIER_DIFFERENCE) {
            return 0;
        }

        /* This is so we can adjust from maximum level difference (say, 20 levels) to maximum gain difference (say, +/- 4 rep)
         Max point difference is 5
         Assuming max level diff of 20, level_diff_to_gain_divider is 20 / 5 = 5

         Scenario 1: Beat someone 10 levels higher. Levels above opponent = -10

         - Normalize level diff to gain: -10 / 5 = -2
         - level_based_gain = 5 - (-2)
         - level_based_gain = 7

         Scenario 2: Beat someone 15 levels lower. Levels above opponent = 10
            - Normalize level diff to gain: 15 / 5 = 3
            - level_based_gain = 5 - 3
            - level_based_gain = 2
        */

        $level_diff_to_gain_divider = self::MAX_PVP_LEVEL_DIFFERENCE / self::PVP_MEDIAN_LEVEL_BASED_GAIN;
        $rep_tier_diff_to_gain_divider = self::MAX_PVP_REP_TIER_DIFFERENCE / self::PVP_MEDIAN_REP_TIER_BASED_GAIN;

        $level_based_gain = self::PVP_MEDIAN_LEVEL_BASED_GAIN - ($player_levels_above_opponent / $level_diff_to_gain_divider);
        $tier_based_gain = self::PVP_MEDIAN_REP_TIER_BASED_GAIN - ($player_rep_tiers_above_opponent / $rep_tier_diff_to_gain_divider);

        $level_based_gain = min($level_based_gain, self::PVP_MEDIAN_LEVEL_BASED_GAIN * 2);
        $tier_based_gain = min($tier_based_gain, self::PVP_MEDIAN_LEVEL_BASED_GAIN * 2);

        // Flat opponent rep rank reward
        $rep_gain = round(
            $level_based_gain
            + $tier_based_gain
            + self::$VillageRep[$opponent->reputation->rank]['base_pvp_rep_reward']
        );

        // Redundancy to ensure rep is not lost when it shouldn't be
        if($rep_gain < 0) {
            $rep_gain = 0;
        }

        // If retreat, halve gain
        if ($retreat) {
            $rep_gain = ceil($rep_gain / 2);
        }

        $player->reputation->addRep($rep_gain, true, true);

        return $rep_gain;
    }

    public function handlePvPLoss(User $player, Fighter $opponent, bool $retreat = false): int {
        if(!($opponent instanceof User)) {
            return 0;
        }

        // Pvp reputation disabled
        if(!self::PVP_REP_ENABLED) {
            return 0;
        }

        $player_levels_below_opponent = $opponent->level - $player->level;
        $player_rep_tiers_below_opponent = $opponent->reputation->rank - $player->reputation->rank;
        $rep_loss = 0;

        // Set current loss
        $this->recent_killer_ids_array[$opponent->user_id][] = time();
        // Get loss count
        $loss_count = isset($this->recent_killer_ids_array[$opponent->user_id])
            ? sizeof($this->recent_killer_ids_array[$opponent->user_id])
            : 0;
        // Encode and set last pvp data
        $this->encodePvpKills();

        // Rep loss mitigation (chain kills only)
        if($loss_count > self::PVP_CHAIN_KILL_LIMIT) {
            return 0;
        }

        // Hard limits - no gains if >20 levels over opponent or >2 rep tiers
        if($player_levels_below_opponent > self::MAX_PVP_LEVEL_DIFFERENCE) {
            return 0;
        }
        if($player_rep_tiers_below_opponent > self::MAX_PVP_REP_TIER_DIFFERENCE) {
            return 0;
        }

        // See handlePvPWin for why we have this
        $level_diff_to_loss_divider = self::MAX_PVP_LEVEL_DIFFERENCE / self::PVP_MEDIAN_LEVEL_BASED_GAIN;
        $rep_tier_diff_to_loss_divider = self::MAX_PVP_REP_TIER_DIFFERENCE / self::PVP_MEDIAN_REP_TIER_BASED_GAIN;

        // Loss goes from 0-10 based on level diff and 0-14 based on rep tier
        $level_based_loss = self::PVP_MEDIAN_LEVEL_BASED_GAIN - ($player_levels_below_opponent / $level_diff_to_loss_divider);
        $tier_based_loss = self::PVP_MEDIAN_REP_TIER_BASED_GAIN - ($player_rep_tiers_below_opponent / $rep_tier_diff_to_loss_divider);

        $level_based_loss = min($level_based_loss, self::PVP_MEDIAN_LEVEL_BASED_GAIN * 2);
        $tier_based_loss = min($tier_based_loss, self::PVP_MEDIAN_REP_TIER_BASED_GAIN * 2);

        $rep_loss = round($level_based_loss + $tier_based_loss);

        // Redundancy to ensure rep is not lost when it shouldn't be
        if($rep_loss < 0) {
            $rep_loss = 0;
        }

        // If retreat, halve loss
        if ($retreat) {
            $rep_loss = ceil($rep_loss / 2);
        }

        $player->reputation->subtractRep($rep_loss);
        return $rep_loss;
    }

    // Encode and set player last pvp kills
    public function encodePvpKills(): void {
        $this->recent_players_killed_ids = json_encode($this->recent_players_killed_ids_array);
        $this->recent_killer_ids = json_encode($this->recent_killer_ids_array);
    }
    // Load pvp kills and remove outdated kills to prvent data bloat
    public function loadPvpKillsArray(): void {
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
    public static function nameByRepRank($tier, $title_type = 'title'): string {
        if(isset(self::$VillageRep[$tier])) {
            return self::$VillageRep[$tier][$title_type];
        }
        return "Invalid Rank";
    }
    // Returns numeric value of reputation tier based on amount of rep
    public static function tierByRepAmount($amount): int|string {
        foreach(array_reverse(self::$VillageRep, true) as $rank => $data) {
            if(abs($amount) >= $data['min_rep']) {
                return $rank;
            }
        }
        return 1;
    }
}

