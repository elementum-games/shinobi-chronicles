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
     *  - each fight in a mission takes ~25 seconds
    */
    public static array $difficulties = [
        SpecialMission::DIFFICULTY_EASY => [
            'yen_per_battle' => 8, // 8 * 5 = 40
            'yen_per_mission' => 35,
            'hp_lost_percent' => 4, // 20% => 60% lost
            'intel_gain' => 20, // est. 5 fights (rank * 75 yen) (old: 45)
        ],
        SpecialMission::DIFFICULTY_NORMAL => [
            'yen_per_battle' => 10, // 10 * 5.5 = 55
            'yen_per_mission' => 50,
            'hp_lost_percent' => 5, // 27.5% => 82.5% lost
            'intel_gain' => 18 // 5.5 fights (rank * 105 yen) (old: 63)
        ],
        SpecialMission::DIFFICULTY_HARD => [
            'yen_per_battle' => 12, // 12 * 6.25 = 75
            'yen_per_mission' => 60,
            'hp_lost_percent' => 7, // 44% => 132% lost
            'intel_gain' => 16 // 6.25 fights (rank * 135 yen) (old: 85)
        ],
        SpecialMission::DIFFICULTY_NIGHTMARE => [
            'yen_per_battle' => 14, // 20 * 7.14 = 100
            'yen_per_mission' => 70,
            'hp_lost_percent' => 10, // 71.4% => 214.2% lost
            'intel_gain' => 14 // 7.14 fights (rank * 170 yen) (old: 111)
        ]
    ];

    /* The longer this is set then the easier it is for users to get sniped and the longer the mission
    takes to complete.

    Baseline testing at 1200ms shows completion ranges of
    Easy (20% intel gain, ~5 encounters): 1:09 - 2:01

    Nightmare (14% intel gain, ~7 encounters): 1:27 -> 2:47
    */
    const EVENT_DURATION_MS = 1000;

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

    // 
    public function __construct(System $system, User $player, $mission_id) {
        $this->system = $system;
        $this->player = $player;
        $this->team = ($this->player->team ? $this->player->team : null);
        $this->mission_id = $mission_id;

        // GET MISSION DATA
        $sql = "SELECT * FROM `special_missions` WHERE `mission_id`={$this->mission_id}";
        $result = $this->system->query($sql);
        // Return if the mission doesn't exist
        if ($this->system->db_last_num_rows == 0) {
            return false;
        }
        
        $mission_data = $this->system->db_fetch($result);

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
        $villages = $this->system->getVillageLocations();
        foreach ($villages as $village) {
            $location = explode('.', $village['location']);
            self::$target_villages[$village['name']]['x'] = $location[0];
            self::$target_villages[$village['name']]['y'] = $location[1];
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
        $result = $this->system->query($sql);

        if ($this->system->db_last_affected_rows) {
            $this->player->updateData();
        }
    }


    // Plays the next event in the mission
    public function nextEvent() {
        $last_event = $this->returnLatestLog();

        $new_event = null;

        // Check if the user is in the target square
        if ($this->target['x'] == $this->player->x && $this->target['y'] == $this->player->y) {
            $new_event = self::EVENT_BATTLE;
            $event_text = self::$event_names[$new_event]['text'];
        }

        // check if the user lost the battle, fail the mission
        if ($last_event['event'] == self::EVENT_BATTLE_LOSE) {
            $new_event = self::EVENT_COMPLETE_FAIL;
            $event_text = self::$event_names[$new_event]['text'];
        }
        
        // check if the user has enough progress to complete mission and is back home
        if ($this->progress >= 100  && $this->player->x == self::$target_villages[$this->player->village]['x']
                                    && $this->player->y == self::$target_villages[$this->player->village]['y']) {
            $new_event = self::EVENT_COMPLETE_SUCCESS;
            $event_text = self::$event_names[$new_event]['text'];
        }

        // check what direction the user has to travel
        $villages = $this->system->getVillageLocations();
        $move_to_x = $this->player->x;
        $move_to_y = $this->player->y;
        if ($this->player->x != $this->target['x']) {
            if ($this->target['x'] > $this->player->x) {
                $move_to_x = $this->player->x + 1;
            } else {
                $move_to_x = $this->player->x - 1;
            }

            // Go around village not into it
            $target_location = $move_to_x . "." . $move_to_y;
            if(isset($villages[$target_location]) && $target_location !== $this->player->village_location) {
                if($this->player->y > $this->target['y']) {
                    $move_to_y--;
                }
                else {
                    $move_to_y++;
                }
            }

            $new_event = self::EVENT_MOVE_X;
            $event_text = self::$event_names[$new_event]['text'] . $move_to_x . '.' . $move_to_y;

        } else if ($this->player->y != $this->target['y']) {
            if ($this->target['y'] > $this->player->y) {
                $move_to_y = $this->player->y + 1;
            } else {
                $move_to_y = $this->player->y - 1;
            }

            // Skip past village if trying to move into it
            $target_location = $move_to_x . "." . $move_to_y;
            if(isset($villages[$target_location]) && $target_location !== $this->player->village_location) {
                if($this->player->x > $this->target['x']) {
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
        if ($this->progress >= 100 && $this->target['target'] != $this->player->village) {
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
                $this->player->x = $move_to_x;
                $this->player->y = $move_to_y;
                break;
            case self::EVENT_HOME:
                $result = $this->generateTarget(true);
                break;
            case self::EVENT_COMPLETE_SUCCESS:
                $result = $this->completeMission();
                $this->logNewEvent(self::EVENT_COMPLETE_REWARD, $result);
                break;
            case self::EVENT_COMPLETE_FAIL:
                $result = $this->failMission();
                break;
        }
        
        // Update stuff
        $this->updateMission();
    }

    // Complete the mission
    public function completeMission(): string {
        // Yen gain for completing the mission
        $yen_gain = self::$difficulties[$this->difficulty]['yen_per_mission'] * $this->player->rank;
        $yen_gain *= 0.8 + (mt_rand(1, 4) / 10);
        $yen_gain = floor($yen_gain);

        $this->status = 1;
        $this->end_time = time();
        $this->player->money += $yen_gain;
        $this->reward += $yen_gain;
        $this->player->special_mission = 0;

        $reward_text = self::$event_names[self::EVENT_COMPLETE_REWARD]['text'] . $yen_gain . '!';

        return $reward_text;
    }

    // Simulates a battle with an ai
    public function simulateBattle(): array {

        $battle_result = self::EVENT_BATTLE_WIN; // Winning by default, suffering from success
        $battle_text = self::$event_names[self::EVENT_BATTLE_WIN]['text'];

       // Gains for mission progress, basic stuff at the moment.
        // 20% variance up/down from base on intel gains. Averages to 105% base

        $intel_gained = self::$difficulties[$this->difficulty]['intel_gain'];
        $intel_gained *= 0.8 + (mt_rand(1, 4) / 10);
        $intel_gained = floor($intel_gained);

        $yen_gain = self::$difficulties[$this->difficulty]['yen_per_battle'] * $this->player->rank;
        $yen_gain *= 0.8 + (mt_rand(1, 4) / 10);
        $yen_gain = floor($yen_gain);

        // Percentage to decrease the HP loss, based on stat cap
        $health_lost = $this->calcHealthLost(
            self::$difficulties[$this->difficulty]['hp_lost_percent']
        );

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

            // Yen Gain
            $this->player->money += $yen_gain;
            $this->reward += $yen_gain;

            $this->player->getInventory();

            // Jutsu exp
            for($i = 0; $i < self::JUTSU_USES_PER_FIGHT; $i++) {
                // 33% chance of using bloodline jutsu, if there are any
                if($this->player->bloodline
                    && count($this->player->bloodline->jutsu) > 0
                    && mt_rand(1, 100) < 25
                ) {
                    $jutsu_key = array_rand($this->player->bloodline->jutsu);
                    $jutsu = $this->player->bloodline->jutsu[$jutsu_key];
                }
                else {
                    $jutsu_key = array_rand($this->player->equipped_jutsu);
                    $jutsu_id = $this->player->equipped_jutsu[$jutsu_key]['id'];
                    $jutsu = $this->player->jutsu[$jutsu_id] ?? null;
                }

                if($jutsu == null) {
                    continue;
                }

                $original_level = $jutsu->level;

                $this->player->useJutsu($jutsu);

                if($jutsu->level > $original_level) {
                    $battle_text .= "[br]" . stripslashes($jutsu->name) . " has increased to level {$jutsu->level}! ";
                }
            }

            $this->player->updateInventory();

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
        $this->player->x = self::$target_villages[$this->player->village]['x'];
        $this->player->y = self::$target_villages[$this->player->village]['y'];
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
            if ($key != $this->player->village) {
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

        // if the user is going home just override everything lmao #dontlookatthislol
        if ($home) {
            $new_target = [
                'target' => $this->player->village,
                'x' => self::$target_villages[$this->player->village]['x'],
                'y' => self::$target_villages[$this->player->village]['y']
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

    private function calcHealthLost(int $base_hp_lost_percent): int {
        // We Decrease the lost hp per turn by % of user stats compared to cap
        $stats_percent = floor(($this->player->total_stats / $this->player->stat_cap) * 100);
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
        $result = $system->query("UPDATE `special_missions`
SET `status`=2, `end_time`={$timestamp} WHERE `mission_id`={$mission_id}");
        $player->special_mission = 0;
        $player->updateData();
        return true;
    }

    public static function startMission($system, $player, $difficulty): SpecialMission {
        
        if ($player->special_mission != 0) {
            throw new Exception('You cannot start multiple missions!');
        }
        
        if (!array_key_exists($difficulty, self::$difficulties)) {
            throw new Exception('Error setting difficulty!');
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
        $result = $system->query($sql);

        $mission_id = $system->db_last_insert_id;
        $player->special_mission = $mission_id;

        return (new SpecialMission($system, $player, $mission_id));

    }

}