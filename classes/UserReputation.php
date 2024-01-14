<?php

require_once __DIR__ . "/war/Operation.php";

class UserReputation {
    public static array $VillageRep = [
        1 => [
            'title' => 'Villager',
            'outlaw_title' => 'Vagabond',
            'min_rep' => 0,
            'weekly_pve_cap' => 750,
            'weekly_war_cap' => 1000,
            'weekly_pvp_cap' => 1500,
            'base_pvp_rep_reward' => 0,
            'base_decay' => 0,
        ],
        2 => [
            'title' => 'Aspiring Shinobi',
            'outlaw_title' => 'Thief',
            'min_rep' => 2500,
            'weekly_pve_cap' => 750,
            'weekly_war_cap' => 1000,
            'weekly_pvp_cap' => 1500,
            'base_pvp_rep_reward' => 1,
            'base_decay' => 0,
        ],
        3 => [
            'title' => 'Shinobi',
            'outlaw_title' => 'Bandit',
            'min_rep' => 5000,
            'weekly_pve_cap' => 750,
            'weekly_war_cap' => 1000,
            'weekly_pvp_cap' => 1500,
            'base_pvp_rep_reward' => 2,
            'base_decay' => 0,
        ],
        4 => [
            'title' => 'Experienced Shinobi',
            'outlaw_title' => 'Raider',
            'min_rep' => 7500,
            'weekly_pve_cap' => 750,
            'weekly_war_cap' => 1000,
            'weekly_pvp_cap' => 1500,
            'base_pvp_rep_reward' => 2,
            'base_decay' => 250,
        ],
        5 => [
            'title' => 'Veteran Shinobi',
            'outlaw_title' => 'Marauder',
            'min_rep' => 10000,
            'weekly_pve_cap' => 750,
            'weekly_war_cap' => 1000,
            'weekly_pvp_cap' => 1500,
            'base_pvp_rep_reward' => 3,
            'base_decay' => 500,
        ],
        6 => [
            'title' => 'Expert Shinobi',
            'outlaw_title' => 'Rogue',
            'min_rep' => 15000,
            'weekly_pve_cap' => 750,
            'weekly_war_cap' => 1000,
            'weekly_pvp_cap' => 1500,
            'base_pvp_rep_reward' => 3,
            'base_decay' => 750,
        ],
        7 => [
            'title' => 'Elite Shinobi',
            'outlaw_title' => 'Notorious Rogue',
            'min_rep' => 22500,
            'weekly_pve_cap' => 750,
            'weekly_war_cap' => 1000,
            'weekly_pvp_cap' => 1500,
            'base_pvp_rep_reward' => 4,
            'base_decay' => 1000,
        ],
        8 => [
            'title' => 'Master Shinobi',
            'outlaw_title' => 'Infamous Rogue',
            'min_rep' => 35000,
            'weekly_pve_cap' => 750,
            'weekly_war_cap' => 1000,
            'weekly_pvp_cap' => 1500,
            'base_pvp_rep_reward' => 4,
            'base_decay' => 1000,
        ],
        9 => [
            'title' => 'Legendary Shinobi',
            'outlaw_title' => 'Legendary Rogue',
            'min_rep' => 50000,
            'weekly_pve_cap' => 750,
            'weekly_war_cap' => 1000,
            'weekly_pvp_cap' => 1500,
            'base_pvp_rep_reward' => 5,
            'base_decay' => 1500,
        ]
    ];

    // Arena, special missions, etc
    const ACTIVITY_TYPE_PVE = 'pve';
    const ACTIVITY_TYPE_DAILY_TASK = 'daily_task';
    const ACTIVITY_TYPE_DAILY_TASK_PVE = 'daily_task_pve';
    const ACTIVITY_TYPE_DAILY_TASK_WAR = 'daily_task_war';
    const ACTIVITY_TYPE_DAILY_TASK_PVP = 'daily_task_pvp';
    // War operations
    const ACTIVITY_TYPE_WAR = 'war';
    // PvP
    const ACTIVITY_TYPE_PVP = 'pvp';
    // Uncapped
    const ACTIVITY_TYPE_UNCAPPED = 'uncapped';

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
        Operation::OPERATION_LOOT => 0,
        Operation::OPERATION_LOOT_TOWN => 0,
    ];

    const DAILY_TASK_REWARDS = [
        DailyTask::DIFFICULTY_EASY => [
            DailyTask::ACTIVITY_DAILY_PVE => 20,
            DailyTask::ACTIVITY_DAILY_WAR => 20,
            DailyTask::ACTIVITY_DAILY_PVP => 10,
            DailyTask::ACTIVITY_EARN_MONEY => 5,
            DailyTask::ACTIVITY_ARENA => 20,
            DailyTask::ACTIVITY_TRAINING => 10,
            DailyTask::ACTIVITY_MISSIONS => 20,
            DailyTask::ACTIVITY_PVP => 10,
        ],
        DailyTask::DIFFICULTY_MEDIUM => [
            DailyTask::ACTIVITY_DAILY_PVE => 30,
            DailyTask::ACTIVITY_DAILY_WAR => 30,
            DailyTask::ACTIVITY_DAILY_PVP => 15,
            DailyTask::ACTIVITY_EARN_MONEY => 10,
            DailyTask::ACTIVITY_ARENA => 30,
            DailyTask::ACTIVITY_TRAINING => 15,
            DailyTask::ACTIVITY_MISSIONS => 30,
            DailyTask::ACTIVITY_PVP => 15,
        ],
        DailyTask::DIFFICULTY_HARD => [
            DailyTask::ACTIVITY_DAILY_PVE => 40,
            DailyTask::ACTIVITY_DAILY_WAR => 40,
            DailyTask::ACTIVITY_DAILY_PVP => 20,
            DailyTask::ACTIVITY_EARN_MONEY => 15,
            DailyTask::ACTIVITY_ARENA => 40,
            DailyTask::ACTIVITY_TRAINING => 20,
            DailyTask::ACTIVITY_MISSIONS => 40,
            DailyTask::ACTIVITY_PVP => 20,
        ],
    ];

    const DAILY_TASK_PVP_WIN_MOD = 5; // Increase rep by this amount for tasks requiring pvp wins (harder than completes)f
    const DAILY_TASK_BYPASS_CAP = false;

    const MAX_PVP_LEVEL_DIFFERENCE = 20;
    const MAX_PVP_REP_TIER_DIFFERENCE = 4;

    const PVP_MEDIAN_LEVEL_BASED_GAIN = 5; // amount gained when fighting someone of the same level
    const PVP_MEDIAN_REP_TIER_BASED_GAIN = 2; // amount gained when fighting someone of the same rep tier (TODO: 7)

    const PVP_REP_ENABLED = true;

    // Only kills within last hour will mitigate pvp rep gains
    const RECENT_PLAYER_KILL_THRESHOLD = 3600;

    // Killing the same player more than this many times in an hour does not add/deduct rep for either person
    const PVP_CHAIN_KILL_LIMIT = 4;

    // Only being killed within last 30 minutes will mitigate pvp rep losses (further chainkill mitigation)
    const RECENTLY_KILLED_BY_THRESHOLD = 1800;

    const SPAR_REP_LOSS = 0;
    const SPAR_REP_DRAW = 1;
    const SPAR_REP_WIN = 2;

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

    protected ?Event $event;

    protected int $rep;
    protected int $weekly_pve_rep;
    protected int $weekly_war_rep;
    protected int $weekly_pvp_rep;
    public int $mission_cd;

    public int $rank;
    public string $rank_name;
    public int $weekly_pve_cap;
    public int $weekly_war_cap;
    public int $weekly_pvp_cap;

    protected int $bonus_pve_rep; // Use only for forbidden_seal bonuses!
    public bool $bonus_pve_loaded;

    // TODO: Make recent_killed private
    public ?string $recent_players_killed_ids;
    public array $recent_players_killed_ids_array;
    private ?string $recent_killer_ids;
    public array $recent_killer_ids_array;
    public int $base_pvp_reward;
    public array $benefits;
    public bool $debug;

    public function __construct(
        &$player_rep,
        &$player_weekly_pve_rep,
        &$player_weekly_war_rep,
        &$player_weekly_pvp_rep,
        &$last_pvp_kills,
        &$last_killer_ids,
        $mission_cd,
        $event
    ) {
        //System data
        $this->event = $event;
        $this->debug = false;

        //Player data
        $this->rep = &$player_rep;
        $this->weekly_pve_rep = &$player_weekly_pve_rep;
        $this->weekly_war_rep = &$player_weekly_war_rep;
        $this->weekly_pvp_rep = &$player_weekly_pvp_rep;

        $this->mission_cd = $mission_cd;
        $this->rank = self::tierByRepAmount($this->rep);
        $this->bonus_pve_rep = 0;
        $this->bonus_pve_loaded = false;

        //PvP data
        $this->recent_players_killed_ids = &$last_pvp_kills;
        $this->recent_killer_ids = &$last_killer_ids;

        //Load pvp kills/killer arrays
        $this->loadPvpKillsArray();
        // Load benefits - this may only be performed after determining reputation rank
        $this->benefits = $this->loadBenefits();

        //Rep rank info
        $REP_RANK = self::$VillageRep[$this->rank];

        $this->rank_name = self::nameByRepRank($this->rank); // Use method here for future proofing
        $this->weekly_pve_cap = $REP_RANK['weekly_pve_cap'];
        $this->weekly_war_cap = $REP_RANK['weekly_war_cap'];
        $this->weekly_pvp_cap = $REP_RANK['weekly_pvp_cap'];
        $this->base_pvp_reward = $REP_RANK['base_pvp_rep_reward'];

        // EVENT MODIFICATIONS
        if(!empty($this->event) && $this->event instanceof DoubleReputationEvent) {
            $this->weekly_pve_cap *= DoubleReputationEvent::pve_cap_multiplier;
            $this->weekly_war_cap *= DoubleReputationEvent::pve_cap_multiplier;
            $this->weekly_pvp_cap *= DoubleReputationEvent::pvp_cap_multiplier;
        }
    }

    /**
     * @param int    $amount
     * @param string $activity_type
     * @return int
     *
     * Increments user village reputation
     *
     * Returns amount of reputation awarded for display/data confirmation purposes
     */
    public function addRep(int $amount, string $activity_type): int {
        if($this->debug) {
            echo "Amount: $amount<br />";
        }

        // Double reputation
        if (!empty($this->event) && $this->event instanceof DoubleReputationEvent) {
            $amount = floor($amount * DoubleReputationEvent::rep_gain_multiplier);
            if($this->debug) {
                echo "Amount after double: $amount<br />";
            }
        }

        switch($activity_type) {
            case UserReputation::ACTIVITY_TYPE_DAILY_TASK_PVE:
                // add to weekly but go over cap
                $this->weekly_pve_rep += $amount;
                if ($this->debug) {
                    echo "Amount after PvE gain: $amount<br />";
                }
                break;
            case UserReputation::ACTIVITY_TYPE_DAILY_TASK_WAR:
                // add to weekly but go over cap
                $this->weekly_war_rep += $amount;
                if ($this->debug) {
                    echo "Amount after War gain: $amount<br />";
                }
                break;
            case UserReputation::ACTIVITY_TYPE_DAILY_TASK_PVP:
                // add to weekly but go over cap
                $this->weekly_pvp_rep += $amount;
                if ($this->debug) {
                    echo "Amount after PvP gain: $amount<br />";
                }
                break;
            case UserReputation::ACTIVITY_TYPE_PVE:
            case UserReputation::ACTIVITY_TYPE_DAILY_TASK:
                // Bonus seal reputation
                $amount += $this->bonus_pve_rep;
                if($this->debug) {
                    echo "Amount after bonus PVE: $amount<br />";
                }

                $new_rep = $this->rep + $amount;

                // Determine if rep rank changes and modify weekly cap if change occurs
                $rep_rank_after = self::tierByRepAmount($new_rep);
                $weekly_pve_cap = ($this->rank != $rep_rank_after) ? self::$VillageRep[$rep_rank_after]['weekly_pve_cap'] : $this->weekly_pve_cap;
                if(!empty($this->event) && $this->event instanceof DoubleReputationEvent && $this->rank != $rep_rank_after) {
                    $weekly_pve_cap = floor($weekly_pve_cap * DoubleReputationEvent::pve_cap_multiplier);
                    if($this->debug) {
                        echo "Weekly cap after rank change: $weekly_pve_cap<br />";
                    }
                }

                // Adjust gain to conform with weekly caps
                if($this->weekly_pve_rep + $amount > $weekly_pve_cap) {
                    $amount = $weekly_pve_cap - $this->weekly_pve_rep;
                }

                $this->weekly_pve_rep += $amount;
                if($this->debug) {
                    echo "Amount after weekly: $amount<br />";
                }
                break;
            case UserReputation::ACTIVITY_TYPE_WAR:
                if($this->weekly_war_rep + $amount > $this->weekly_war_cap) {
                    $amount = $this->weekly_war_cap - $this->weekly_war_rep;
                }
                $this->weekly_war_rep += $amount;
                if($this->debug) {
                    echo "Amount after War: $amount<br />";
                }
                break;
            case UserReputation::ACTIVITY_TYPE_PVP:
                if($this->weekly_pvp_rep + $amount > $this->weekly_pvp_cap) {
                    $amount = $this->weekly_pvp_cap - $this->weekly_pvp_rep;
                }
                $this->weekly_pvp_rep += $amount;
                if($this->debug) {
                    echo "Amount after PvP: $amount<br />";
                }
                break;
            case UserReputation::ACTIVITY_TYPE_UNCAPPED:
                if($this->debug) {
                    echo "Uncapped amount: $amount<br />";
                }
                break;
            default:
                throw new RuntimeException("Invalid activity type!");
        }

        //Increment rep amount
        if($amount > 0) {
            $this->rep += $amount;
        }

        if($this->debug) {
            echo "Final amount: $amount<br />";
        }
        return $amount; // Use this return for display/gain confirmation
    }

    /**
     * @param int    $amount
     * @param string $activity_type
     *
     * Decreases user rep by amount provided
     * If $decrement_weekly is enabled, this will also decrease weekly rep allowing for restricted methods to allow gains again
     */
    public function subtractRep(int $amount, string $activity_type): void {
        $this->rep -= $amount;

        if ($activity_type == UserReputation::ACTIVITY_TYPE_PVP) {
            $this->weekly_pvp_rep -= $amount;
            $this->weekly_pvp_rep = max(0, $this->weekly_pvp_rep);
        }

        //TODO: TEMPORARY! Remove with negative reputation (outlaw update)
        if($this->rep < 0) {
            $this->rep = 0;
        }
    }

    // Set bonus reputation from seal
    public function setBonusPveRep($amount): void {
        $this->bonus_pve_rep = $amount;
        $this->bonus_pve_loaded = true;
    }

    public function getBonusPveRep(): int {
        return $this->bonus_pve_rep;
    }

    public function resetPvpRep(): void {
        $this->weekly_pvp_rep = 0;
    }

    // Returns numeric value of reputation
    public function getRepAmount():int {
        return $this->rep;
    }
    // Returns numeric value of weekly reputaiton
    public function getWeeklyPveRep():int {
        return $this->weekly_pve_rep;
    }
    public function getWeeklyWarRep():int {
        return $this->weekly_war_rep;
    }
    // Returns numeric value of weekly pvp reputaiton
    public function getWeeklyPvpRep():int {
        return $this->weekly_pvp_rep;
    }

    // Return of user can gain more rep for restricted methods
    public function canGain(string $activity_type): bool {
        switch($activity_type) {
            case UserReputation::ACTIVITY_TYPE_PVE:
                if($this->mission_cd > time()) {
                    return false;
                }
                return $this->weekly_pve_rep < $this->weekly_pve_cap;
            case UserReputation::ACTIVITY_TYPE_DAILY_TASK:
                return $this->weekly_pve_rep < $this->weekly_pve_cap;
            case UserReputation::ACTIVITY_TYPE_PVP:
                return $this->weekly_pvp_rep < $this->weekly_pvp_cap;
            case UserReputation::ACTIVITY_TYPE_WAR:
                return $this->weekly_war_rep < $this->weekly_war_cap;
            case UserReputation::ACTIVITY_TYPE_UNCAPPED:
                return true;
            default:
                throw new RuntimeException("Invalid activity type!");
        }
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
    public function calcArenaReputation(string $difficulty_level, int $rank): int {
        switch ($difficulty_level) {
            case NPC::DIFFICULTY_NONE:
                return 1;
            case NPC::DIFFICULTY_EASY:
                return 1;
            case NPC::DIFFICULTY_NORMAL:
                return 2;
            case NPC::DIFFICULTY_HARD:
                return 4;
            default:
                return 1;
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
        if($this->weekly_pvp_rep >= $this->weekly_pvp_cap) {
            return 0;
        }

        // Hard limits - no gains if >20 levels over opponent or >2 rep tiers
        if($player_levels_above_opponent > self::MAX_PVP_LEVEL_DIFFERENCE) {
            return $player->reputation->addRep(self::$VillageRep[$opponent->reputation->rank]['base_pvp_rep_reward'], UserReputation::ACTIVITY_TYPE_PVP);
        }
        if($player_rep_tiers_above_opponent > self::MAX_PVP_REP_TIER_DIFFERENCE) {
            return $player->reputation->addRep(self::$VillageRep[$opponent->reputation->rank]['base_pvp_rep_reward'], UserReputation::ACTIVITY_TYPE_PVP);
        }

        /* This is so we can adjust from maximum level difference (say, 20 levels) to maximum gain difference (say, +/- 4 rep)
         The gain can stretch from 0x => 2x of the median level-based gain, e.g. if median is 5 we do 0 - 10.

         Level diff to gain divider represents how many level correspond to +/- 1 rep.

         Assuming max level diff of 20 and median gain of 5, level_diff_to_gain_divider is 20 / 5 = 4. For example:
         - +1 to +4 levels = +1 rep
         - +5 to +8 levels = +2 rep
         - +9 to +12 levels = +3 rep
         - +13 to +16 levels = +4 rep
         - +17 to +20 levels = +5 rep

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

        return $player->reputation->addRep($rep_gain, UserReputation::ACTIVITY_TYPE_PVP);
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

        $rep_loss *= 0.75;

        // If retreat, halve loss
        if ($retreat) {
            $rep_loss *= 0.5;
        }

        $rep_loss = ceil($rep_loss);

        // Redundancy to ensure rep is not lost when it shouldn't be
        if($rep_loss < 0) {
            $rep_loss = 0;
        }

        $player->reputation->subtractRep($rep_loss, UserReputation::ACTIVITY_TYPE_PVP);
        return $rep_loss;
    }

    public function handleSpar(User $player, Fighter $opponent, int $spar_gain): int {
        if (!($opponent instanceof User)) {
            return 0;
        }
        if (!self::PVP_REP_ENABLED) {
            return 0;
        }
        // Set current kill
        $this->recent_players_killed_ids_array[$opponent->user_id][] = time();
        // Get kill count
        $kill_count = isset($this->recent_players_killed_ids_array[$opponent->user_id]) ? sizeof($this->recent_players_killed_ids_array[$opponent->user_id]) : 0;
        // Encode and set last pvp kills
        $this->encodePvpKills();

        // Opponent killed too many times in mitigation frame, no gain
        if ($kill_count > self::PVP_CHAIN_KILL_LIMIT) {
            return 0;
        }

        // Weekly rep limit
        if ($this->weekly_pvp_rep >= $this->weekly_pvp_cap) {
            return 0;
        }
        return $player->reputation->addRep($spar_gain, UserReputation::ACTIVITY_TYPE_PVP);
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

