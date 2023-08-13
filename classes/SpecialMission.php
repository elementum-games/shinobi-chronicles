<?php

class SpecialMission {
    const DIFFICULTY_EASY = 'easy';
    const DIFFICULTY_NORMAL = 'normal';
    const DIFFICULTY_HARD = 'hard';
    const DIFFICULTY_NIGHTMARE = 'nightmare';

    // Scale damage multiplier from this point in the rank to cap
    const BASE_STAT_CAP_PERCENT = 20;
    // Damage multiplier starts here at the base number, then scales down to 1x at cap
    const MAX_DAMAGE_MULTIPLIER = 3;

    // Number of jutsu to use per fight, picked at random from equipped and bloodline jutsu
    const JUTSU_USES_PER_FIGHT = 3;
    const JUTSU_COST_DISCOUNT_PERCENT = 25;
    // % chance to use a bloodline jutsu instead of an equipped jutsu
    const BLOODLINE_JUTSU_CHANCE = 25;

    /*
     * DIFFICULTY
     * When setting difficulty, consider the max dmg multiplier above as well as that # of fights is
     * roughly equal to 100 / intel gain, then calculate the range of damage lost (shown in comments below)
     *
     * Pricing context:
     *  - Yen gains here will be multiplied by player's rank
     *  - healing shop cost is rank * 5 / rank * 20 / rank * 40
     *  - arena battles are approximately rank * 20-45 / 25-60 / 50-80 / 60-80
     *  - arena battles takes ~20 seconds
     *  - each fight in a mission takes ~13 seconds
    */
    public static array $difficulties = [
        SpecialMission::DIFFICULTY_EASY => [
            'yen_per_battle' => 8, // 8 * 10 = 80
            'yen_per_mission' => 70,
            'stats_per_mission' => 2,
            'hp_lost_percent' => 2, // 20% => 60% lost
            'intel_gain' => 10, // est. 10 fights (rank * 150 yen) [130 seconds]
            'rep_gain' => UserReputation::SPECIAL_MISSION_REP_GAINS[SpecialMission::DIFFICULTY_EASY],
        ],
        // Measured average 144 seconds (11.3 fights)
        SpecialMission::DIFFICULTY_NORMAL => [
            'yen_per_battle' => 10, // 10 * 11 = 110
            'yen_per_mission' => 110,
            'stats_per_mission' => 4,
            'hp_lost_percent' => 2.5, // 27.5% => 82.5% lost
            'intel_gain' => 9, // 11.1 fights (rank * 210 yen) (old: 63) [144 seconds]
            'rep_gain' => UserReputation::SPECIAL_MISSION_REP_GAINS[SpecialMission::DIFFICULTY_NORMAL],
        ],
        SpecialMission::DIFFICULTY_HARD => [
            'yen_per_battle' => 12, // 12 * 12.5 = 150
            'yen_per_mission' => 150,
            'stats_per_mission' => 6,
            'hp_lost_percent' => 3.5, // 44% => 132% lost
            'intel_gain' => 8, // 12.5 fights (rank * 270 yen) [162 seconds]
            'rep_gain' => UserReputation::SPECIAL_MISSION_REP_GAINS[SpecialMission::DIFFICULTY_HARD],
        ],
        SpecialMission::DIFFICULTY_NIGHTMARE => [
            'yen_per_battle' => 14, // 20 * 14.28 = 285
            'yen_per_mission' => 200,
            'stats_per_mission' => 8,
            'hp_lost_percent' => 4.5, // 71.4% => 214.2% lost
            'intel_gain' => 7, // 14.28 fights (rank * 340 yen) [185 seconds]
            'rep_gain' => UserReputation::SPECIAL_MISSION_REP_GAINS[SpecialMission::DIFFICULTY_NIGHTMARE],
        ]
    ];

    /* The longer this is set then the easier it is for users to get sniped and the longer the mission
    takes to complete.

    Baseline testing at 1200ms shows completion ranges of
    Easy (20% intel gain, ~5 encounters): 1:09 - 2:01

    Nightmare (14% intel gain, ~7 encounters): 1:27 -> 2:47
    */
    const EVENT_DURATION_MS = 650;

    const EVENT_START = 'start';
    const EVENT_MOVE_X = 'move_x';
    const EVENT_MOVE_Y = 'move_y';
    const EVENT_BATTLE = 'battle';
    const EVENT_BATTLE_WIN = 'battle_win';
    const EVENT_BATTLE_LOSE = 'battle_lose';
    const EVENT_HOME = 'mission_home';
    const EVENT_COMPLETE_SUCCESS = 'mission_complete';
    const EVENT_COMPLETE_FAIL = 'mission_failed';
    const EVENT_COMPLETE_REWARD = 'mission_reward';

    public static array $event_names = [
        SpecialMission::EVENT_START => [
            'event' => SpecialMission::EVENT_START,
            'text' => 'Mission started!'
        ],
        SpecialMission::EVENT_MOVE_X => [
            'event' => SpecialMission::EVENT_MOVE_X,
            'text' => 'You moved to '
        ],
        SpecialMission::EVENT_MOVE_Y => [
            'event' => SpecialMission::EVENT_MOVE_Y,
            'text' => 'You moved to '
        ],
        SpecialMission::EVENT_BATTLE => [
            'event' => SpecialMission::EVENT_BATTLE,
            'text' => 'You encountered an enemy patrol!'
        ],
        SpecialMission::EVENT_BATTLE_WIN => [
            'event' => SpecialMission::EVENT_BATTLE_WIN,
            'text' => 'You defeated an enemy patrol and found documents related to your assignment!'
        ],
        SpecialMission::EVENT_BATTLE_LOSE => [
            'event' => SpecialMission::EVENT_BATTLE_LOSE,
            'text' => 'You were defeated by an enemy patrol! Reinforcments rescued you and escorted you back to the village...'
        ],
        SpecialMission::EVENT_HOME => [
            'event' => SpecialMission::EVENT_HOME,
            'text' => 'You gathered enough intel to complete your assignment! Report back to the village!'
        ],
        SpecialMission::EVENT_COMPLETE_SUCCESS => [
            'event' => SpecialMission::EVENT_COMPLETE_SUCCESS,
            'text' => 'You completed the mission!'
        ],
        SpecialMission::EVENT_COMPLETE_FAIL => [
            'event' => SpecialMission::EVENT_COMPLETE_FAIL,
            'text' => 'You failed the mission!'
        ],
        SpecialMission::EVENT_COMPLETE_REWARD => [
            'event' => SpecialMission::EVENT_COMPLETE_REWARD,
            'text' => 'You completed the mission successfully and earned a bonus of &#165;'
        ]
    ];

    const SPY_TARGET_LEAF = 'Leaf';
    const SPY_TARGET_STONE = 'Stone';
    const SPY_TARGET_SAND = 'Sand';
    const SPY_TARGET_CLOUD = 'Cloud';
    const SPY_TARGET_MIST = 'Mist';

    public static array $target_villages = [
        SpecialMission::SPY_TARGET_LEAF => [
            'x' => 9,
            'y' => 6,
            'negative_x' => 2,
            'positive_x' => 2,
            'negative_y' => 2,
            'positive_y' => 2
        ],
        SpecialMission::SPY_TARGET_STONE => [
            'x' => 5,
            'y' => 3,
            'negative_x' => 2,
            'positive_x' => 2,
            'negative_y' => 2,
            'positive_y' => 2
        ],
        SpecialMission::SPY_TARGET_SAND => [
            'x' => 3,
            'y' => 8,
            'negative_x' => 2,
            'positive_x' => 2,
            'negative_y' => 2,
            'positive_y' => 2
        ],
        SpecialMission::SPY_TARGET_CLOUD => [
            'x' => 17,
            'y' => 2,
            'negative_x' => 2,
            'positive_x' => 1,
            'negative_y' => 1,
            'positive_y' => 2
        ],
        SpecialMission::SPY_TARGET_MIST => [
            'x' => 16,
            'y' => 10,
            'negative_x' => 2,
            'positive_x' => 2,
            'negative_y' => 2,
            'positive_y' => 2
        ]
    ];

    private User $player;
    private ?Team $team;
    private System $system;

    public int $mission_id;
    public int $status;
    public int $start_time;
    public int $end_time;
    public int $progress;
    public $log;
    public int $player_health;
    public int $player_max_health;
    public int $reward;

    public function __construct(System $system, User $player, $mission_id) {
        $this->system = $system;
        $this->player = $player;
        $this->team = ($this->player->team ? $this->player->team : null);
        $this->mission_id = $mission_id;

        // GET MISSION DATA
        $sql = "SELECT * FROM `special_missions` WHERE `mission_id`={$this->mission_id}";
        $result = $this->system->db->query($sql);
        // Return if the mission doesn't exist
        if ($this->system->db->last_num_rows == 0) {
            return false;
        }

        $mission_data = $this->system->db->fetch($result);

        $this->status = $mission_data['status'];
        $this->difficulty = $mission_data['difficulty'];
        $this->start_time = $mission_data['start_time'];
        $this->end_time = $mission_data['end_time'];
        $this->progress = $mission_data['progress'];
        $this->target = json_decode($mission_data['target'], true);
        $this->log = json_decode($mission_data['log'], true);
        $this->reward = $mission_data['reward'];

        $this->player_health = $this->player->health;
        $this->player_max_health = $this->player->max_health;

        // Village info
        $villages = TravelManager::fetchVillageLocationsByCoordsStr($this->system);
        foreach ($villages as $coords => $name) {
            $location = TravelCoords::fromDbString($coords);
            self::$target_villages[$name]['x'] = $location->x;
            self::$target_villages[$name]['y'] = $location->y;
        }

        // generate a new target if null
        if ($this->target == null) {
            $step_generated = $this->generateTarget();
            $this->updateMission();
        }
    }

    // update the mission with current set values
    public function updateMission() {
        $target = json_encode($this->target);
        $log = json_encode($this->log);
        $sql = "UPDATE `special_missions`
                SET `status`={$this->status},
                `end_time`={$this->end_time},
                `progress`={$this->progress},
                `target`='{$target}',
                `log`='{$log}',
                `reward`={$this->reward}
                WHERE `mission_id`={$this->mission_id}";
        $result = $this->system->db->query($sql);

        if ($this->system->db->last_affected_rows) {
            $this->player->updateData();
        }
    }


    // Plays the next event in the mission
    public function nextEvent() {
        $last_event = $this->returnLatestLog();

        $new_event = null;

        // Check if the user is in the target square
        if ($this->target['x'] == $this->player->location->x && $this->target['y'] == $this->player->location->y) {
            $new_event = self::EVENT_BATTLE;
            $event_text = self::$event_names[$new_event]['text'];
        }

        // check if the user lost the battle, fail the mission
        if ($last_event['event'] == self::EVENT_BATTLE_LOSE) {
            $new_event = self::EVENT_COMPLETE_FAIL;
            $event_text = self::$event_names[$new_event]['text'];
        }

        // check if the user has enough progress to complete mission and is back home
        if ($this->progress >= 100  && $this->player->location->x == self::$target_villages[$this->player->village->name]['x']
                                    && $this->player->location->y == self::$target_villages[$this->player->village->name]['y']) {
            $new_event = self::EVENT_COMPLETE_SUCCESS;
            $event_text = self::$event_names[$new_event]['text'];
        }

        // check what direction the user has to travel
        $villages = TravelManager::fetchVillageLocationsByCoordsStr($this->system);
        $move_to_x = $this->player->location->x;
        $move_to_y = $this->player->location->y;
        if ($this->player->location->x != $this->target['x']) {
            if ($this->target['x'] > $this->player->location->x) {
                $move_to_x = $this->player->location->x + 1;
            } else {
                $move_to_x = $this->player->location->x - 1;
            }

            // Go around village not into it
            $target_location = new TravelCoords($move_to_x, $move_to_y, $this->player->location->map_id);
            if(isset($villages[$target_location->fetchString()]) && !$this->player->village_location->equals($target_location)) {
                if($this->player->location->y > $this->target['y']) {
                    $move_to_y--;
                }
                else {
                    $move_to_y++;
                }
            }

            $new_event = self::EVENT_MOVE_X;
            $event_text = self::$event_names[$new_event]['text'] . $move_to_x . '.' . $move_to_y;

        } else if ($this->player->location->y != $this->target['y']) {
            if ($this->target['y'] > $this->player->location->y) {
                $move_to_y = $this->player->location->y + 1;
            } else {
                $move_to_y = $this->player->location->y - 1;
            }

            // Skip past village if trying to move into it
            $target_location = new TravelCoords($move_to_x, $move_to_y, $this->player->location->map_id);
            if(isset($villages[$target_location->fetchString()]) && !$this->player->village_location->equals($target_location)) {
                if($this->player->location->x > $this->target['x']) {
                    $move_to_x--;
                }
                else {
                    $move_to_x++;
                }
            }

            $new_event = self::EVENT_MOVE_Y;
            $event_text = self::$event_names[$new_event]['text'] . $move_to_x . '.' . $move_to_y;
        }

        // check if the mission is complete
        if ($this->progress >= 100 && $this->target['target'] != $this->player->village->name) {
            $new_event = self::EVENT_HOME;
            $event_text = self::$event_names[$new_event]['text'];
        }

        // Log the event
        $this->logNewEvent($new_event, $event_text);

        // Play Events
        switch($new_event) {
            case self::EVENT_BATTLE:
                $result = $this->simulateBattle();
                // log the results
                $this->logNewEvent($result[0], $result[1]);
                break;

            case self::EVENT_MOVE_Y:
            case self::EVENT_MOVE_X:
                $this->player->location->x = $move_to_x;
                $this->player->location->y = $move_to_y;
                break;
            case self::EVENT_HOME:
                $result = $this->generateTarget(true);
                break;
            case self::EVENT_COMPLETE_SUCCESS:
                $result = $this->completeMission($this->progress);
                $this->logNewEvent(self::EVENT_COMPLETE_REWARD, $result);
                // Create notification
                require_once __DIR__ . '/../classes/notification/NotificationManager.php';
                $new_notification = new NotificationDto(
                    type: "specialmission_complete",
                    message: "Special Mission completed",
                    user_id: $this->player->user_id,
                    created: time(),
                    alert: true,
                );
                NotificationManager::createNotification($new_notification, $this->system, NotificationManager::UPDATE_REPLACE);
                break;
            case self::EVENT_COMPLETE_FAIL:
                $result = $this->completeMission($this->progress);
                $this->logNewEvent(self::EVENT_COMPLETE_FAIL, $result);
                $result = $this->failMission();
                // Create notification
                require_once __DIR__ . '/../classes/notification/NotificationManager.php';
                $new_notification = new NotificationDto(
                    type: "specialmission_failed",
                    message: "Special Mission failed",
                    user_id: $this->player->user_id,
                    created: time(),
                    alert: true,
                );
                NotificationManager::createNotification($new_notification, $this->system, NotificationManager::UPDATE_REPLACE);
                break;
        }

        // Update stuff
        $this->updateMission();
    }

    // Complete the mission

    /**
     * @throws RuntimeException
     */
    public function completeMission($progress): string {
        if ($progress > 100) {
            $progress = 100;
        }
        $progress_modifier = $progress / 100;
        $reward_text = '';
        if ($progress == 100) {
            // Yen gain for completing the mission
            $yen_gain = self::$difficulties[$this->difficulty]['yen_per_mission'] * $this->player->rank_num;
            $yen_gain *= 0.8 + (mt_rand(1, 4) / 10);
            $yen_gain = floor($yen_gain);

            $this->status = 1;
            $this->end_time = time();
            $this->player->money->add($yen_gain, "Special mission");
            $this->reward += $yen_gain;
            $this->player->special_mission = 0;

            $reward_text = self::$event_names[self::EVENT_COMPLETE_REWARD]['text'] . $yen_gain . '!';

            //Reputation Reward
            if ($this->player->reputation->canGain(true)) {
                $rep_gain = $this->player->reputation->addRep(self::$difficulties[$this->difficulty]['rep_gain']);
                if ($rep_gain > 0) {
                    $this->player->mission_rep_cd = time() + UserReputation::ARENA_MISSION_CD;
                    $reward_text .= ' You have gained ' . $rep_gain . " village reputation!";
                }
            }
        }

        $stat_to_gain = $this->player->getTrainingStatForArena();
        $extra_stats_for_rank = max(0, $this->player->rank_num - 2) * 2;
        $stat_gain = floor(
           (self::$difficulties[$this->difficulty]['stats_per_mission'] + $extra_stats_for_rank)
           * $progress_modifier
        );
        if($stat_to_gain != null) {
            $reward_text .= ' ' . $this->player->addStatGain($stat_to_gain, $stat_gain) . '!';
        }

        return $reward_text;
    }

    // Simulates a battle with an ai

    /**
     * @throws RuntimeException
     */
    public function simulateBattle(): array {
        $battle_result = self::EVENT_BATTLE_WIN; // Winning by default, suffering from success
        $battle_text = self::$event_names[self::EVENT_BATTLE_WIN]['text'];

        // Percentage to decrease the HP loss, based on stat cap
        $health_lost = $this->calcHealthLost(
            self::$difficulties[$this->difficulty]['hp_lost_percent']
        );

        // Use Jutsu
        $this->player->getInventory();

        $has_equipped_jutsu = count($this->player->equipped_jutsu) > 0;
        $has_bloodline_jutsu = $this->player->bloodline && count($this->player->bloodline->jutsu) > 0;
        $equipped_jutsu_chance = 100 - self::BLOODLINE_JUTSU_CHANCE;
        $extra_health_lost = 0; // if you can't use any jutsu, consumes double the HP cost

        $failed_jutsu_extra_health_lost = ($health_lost / self::JUTSU_USES_PER_FIGHT) / 2;

        for($i = 0; $i < self::JUTSU_USES_PER_FIGHT; $i++) {
            if($has_equipped_jutsu && (
                mt_rand(1, 100) < $equipped_jutsu_chance || !$has_bloodline_jutsu
            )) {
                $jutsu_key = array_rand($this->player->equipped_jutsu);
                $jutsu_id = $this->player->equipped_jutsu[$jutsu_key]['id'];
                $jutsu = $this->player->jutsu[$jutsu_id] ?? null;
            }
            else if($has_bloodline_jutsu) {
                $jutsu_key = array_rand($this->player->bloodline->jutsu);
                $jutsu = $this->player->bloodline->jutsu[$jutsu_key];
            }
            else {
                $jutsu = null;
            }

            if($jutsu == null) {
                $extra_health_lost += $failed_jutsu_extra_health_lost;
                continue;
            }

            $original_level = $jutsu->level;

            $jutsu_cost_multiplier = (100 - self::JUTSU_COST_DISCOUNT_PERCENT) / 100;

            $result = $this->player->useJutsu($jutsu, $jutsu_cost_multiplier);
            if($result->failed) {
                $extra_health_lost += $failed_jutsu_extra_health_lost;
            }

            /** @noinspection PhpConditionAlreadyCheckedInspection - player->useJutsu can increase the level */
            if($jutsu->level > $original_level) {
                $battle_text .= "[br]" . stripslashes($jutsu->name) . " has increased to level {$jutsu->level}! ";
            }
        }

        $health_lost += $extra_health_lost;
        if($extra_health_lost > 0 && ($has_equipped_jutsu || $has_bloodline_jutsu)) {
            $battle_text .= "[br]You ran out of chakra/stamina mid fight, and were wounded as you fought with only basic taijutsu.";
        }
        else if($extra_health_lost > 0) {
            $battle_text .= "[br]You did not have any jutsu prepared, and were wounded as you fought with only basic taijutsu.";
        }

        $this->player->updateInventory();

        // Gains for mission progress, basic stuff at the moment.
        // 20% variance up/down from base on intel gains. Averages to 105% base

        $intel_gained = self::$difficulties[$this->difficulty]['intel_gain'];
        $intel_gained *= 0.8 + (mt_rand(1, 4) / 10);
        $intel_gained = floor($intel_gained);

        $yen_gain = self::$difficulties[$this->difficulty]['yen_per_battle'] * $this->player->rank_num;
        $yen_gain *= 0.8 + (mt_rand(1, 4) / 10);
        $yen_gain = floor($yen_gain);

        // ***********************************************************************************************

        // If the user loses all HP
        if ($this->player->health - $health_lost <= 0) {
            $battle_result = self::EVENT_BATTLE_LOSE;
            $battle_text = self::$event_names[self::EVENT_BATTLE_LOSE]['text'];
            $this->player->health = 0;
        }

        // REWARD for winning
        if ($battle_result == self::EVENT_BATTLE_WIN) {
            // intel gain
            $this->progress += $intel_gained;

            // Damage HP
            $this->player->health -= $health_lost;
            $this->player_health -= $health_lost;
            if($this->system->isDevEnvironment()) {
                $battle_text .= "[br]You lost {$health_lost} health";
            }

            // Yen Gain
            $this->player->money->add($yen_gain, "Special mission encounter");
            $this->reward += $yen_gain;

            // generate a new target
            $this->generateTarget();

            // Modify the event text
            $battle_text .= "[br]You collected &#165;{$yen_gain}!";
        }

        return ([$battle_result, $battle_text]);
    }

    // Fails the mission
    public function failMission(): bool {
        $this->end_time = time();
        $this->status = 2;
        $this->player->location->x = self::$target_villages[$this->player->village->name]['x'];
        $this->player->location->y = self::$target_villages[$this->player->village->name]['y'];
        $this->player->special_mission = 0;
        return true;
    }

    // Enter the new event into the log
    public function logNewEvent($new_event, $event_text): bool {
        $log_entry = [
            'event' => $new_event,
            'timestamp_ms' => floor(microtime(true) * 1000),
            'description' => $event_text
        ];
        array_unshift($this->log, $log_entry);
        return true;
    }

    // Generates a new target location
    public function generateTarget($home = false): int {
        // Set the Village
        $random_village_key = false;
        while($random_village_key == false) {
            $key = array_rand(self::$target_villages, 1);
            if ($key != $this->player->village->name) {
                $random_village_key = $key;
            }
        }

        // Set the coords
        $is_x_negative = (bool)mt_rand(0,1);
        $max_x = ($is_x_negative ? self::$target_villages[$random_village_key]['negative_x'] : self::$target_villages[$random_village_key]['positive_x']);
        $random_x = mt_rand(1, $max_x);
        $target_x = ($is_x_negative ?
            (self::$target_villages[$random_village_key]['x'] - $random_x) :
            (self::$target_villages[$random_village_key]['x'] + $random_x));

        $is_y_negative = (bool)mt_rand(0,1);
        $max_y = ($is_y_negative ? self::$target_villages[$random_village_key]['negative_y'] : self::$target_villages[$random_village_key]['positive_y']);
        $random_y = mt_rand(1, $max_y);
        $target_y = ($is_x_negative ? (self::$target_villages[$random_village_key]['y'] - $random_y) : (self::$target_villages[$random_village_key]['y'] + $random_y));

        $new_target = [
            'target' => $random_village_key,
            'x' => $target_x,
            'y' => $target_y
        ];

        // if the user is going home just override everything lmao #dontlookatthislol #arthesialookedatthis
        if ($home) {
            $new_target = [
                'target' => $this->player->village->name,
                'x' => self::$target_villages[$this->player->village->name]['x'],
                'y' => self::$target_villages[$this->player->village->name]['y']
            ];
        }

        $this->target = $new_target;

        return true;

    }

    // returns the latest entry to the log
    public function returnLatestLog(): array {
        $first_key = array_key_first($this->log);
        return $this->log[$first_key];
    }

    // Returns the last time an event was triggered
    public function returnLastUpdateMs(): int {
        $last_entry = $this->returnLatestLog();
        return $last_entry['timestamp_ms'];
    }

    private function calcHealthLost(int|float $base_hp_lost_percent): int {
        // We Decrease the lost hp per turn by % of user stats compared to cap
        $stats_percent = floor(($this->player->total_stats / $this->player->rank->stat_cap) * 100);
        if($stats_percent > 100) {
            $stats_percent = 100;
        }

        /* We expect people to enter most ranks at about 20-25% of the stat cap. So we adjust the stat
        percentage to reflect the realistic start => end points players will experience in a rank. With base
        set to 20%, we consider the 20% -> 100% range of stats to be 0% -> 100% for the purpose of calculating
        HP loss.
        */
        if($stats_percent < self::BASE_STAT_CAP_PERCENT) {
            $stats_percent = self::BASE_STAT_CAP_PERCENT;
        }
        $adjusted_stats_percent = (
            ($stats_percent - self::BASE_STAT_CAP_PERCENT - 1) /
            (100 - self::BASE_STAT_CAP_PERCENT)
        ) * 100;
        $inverse_stats_percent = 100 - $adjusted_stats_percent;

        /* What is this? Let's say our max damage multiplier is 3x, so we want damage to scale from 1x -> 3x.
            To accomplish that, we
            - set our base multiplier to be 1x
            then
            - add anywhere from 0x -> 2x based on lack of stats, aka inverse stats percent.
        */
        $damage_multiplier = 1;
        $damage_multiplier += (self::MAX_DAMAGE_MULTIPLIER - 1) * ($inverse_stats_percent / 100);

        $hp_lost_percent = $base_hp_lost_percent * $damage_multiplier;
        return floor(($hp_lost_percent / 100) * $this->player->max_health);
    }

    // Cancel the mission
    public static function cancelMission($system, $player, $mission_id) {
        $timestamp = time();
        $result = $system->db->query("UPDATE `special_missions`
SET `status`=2, `end_time`={$timestamp} WHERE `mission_id`={$mission_id}");
        $player->special_mission = 0;
        $player->updateData();
        return true;
    }

    public static function startMission($system, $player, $difficulty): SpecialMission {

        if ($player->special_mission != 0) {
            throw new RuntimeException('You cannot start multiple missions!');
        }

        if (!array_key_exists($difficulty, self::$difficulties)) {
            throw new RuntimeException('Error setting difficulty!');
        }

        $timestamp = time();

        $log = [
            0 => [
            'event' => self::$event_names['start']['event'],
            'timestamp_ms' => floor(microtime(true) * 1000),
            'description' => self::$event_names['start']['text']
            ]
        ];
        $log_encode = json_encode($log);

        $sql = "INSERT INTO `special_missions` (`user_id`, `start_time`, `log`, `difficulty`)
                VALUES ('{$player->user_id}', '{$timestamp}', '$log_encode', '{$difficulty}')";
        $result = $system->db->query($sql);

        $mission_id = $system->db->last_insert_id;
        $player->special_mission = $mission_id;

        return (new SpecialMission($system, $player, $mission_id));

    }

}
