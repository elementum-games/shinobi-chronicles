<?php

class SpecialMission {
    
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

    const DIFFICULTY_EASY = 'easy';
    const DIFFICULTY_NORMAL = 'normal';
    const DIFFICULTY_HARD = 'hard';
    const DIFFICULTY_NIGHTMARE = 'nightmare';

    public static array $difficulties = [
        SpecialMission::DIFFICULTY_EASY => [
            'yen_per_battle' => 10,
            'yen_per_mission' => 100,
            'hp_lost' => 10,
            'intel_gain' => 10
        ],
        SpecialMission::DIFFICULTY_NORMAL => [
            'yen_per_battle' => 20,
            'yen_per_mission' => 200,
            'hp_lost' => 15,
            'intel_gain' => 10
        ],
        SpecialMission::DIFFICULTY_HARD => [
            'yen_per_battle' => 30,
            'yen_per_mission' => 300,
            'hp_lost' => 20,
            'intel_gain' => 10
        ],
        SpecialMission::DIFFICULTY_NIGHTMARE => [
            'yen_per_battle' => 50,
            'yen_per_mission' => 500,
            'hp_lost' => 30,
            'intel_gain' => 10
        ]
    ];

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
            'text' => 'You defeated an enemy patrol and found documents related to your assignment! You collected &#165;'
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
            'text' => 'You completed the mission successfully and earned &#165;'
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
        $result = $this->system->query("SELECT `location`, `name` FROM villages");
        $villages = $this->system->db_fetch_all($result);
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
        if ($this->player->x != $this->target['x']) {
            if ($this->target['x'] > $this->player->x) {
                $new_coord = $this->player->x + 1;
            } else {
                $new_coord = $this->player->x - 1;
            }

            $new_event = self::EVENT_MOVE_X;
            $event_text = self::$event_names[$new_event]['text'] . $new_coord . '.' . $this->player->y;

        } else if ($this->player->y != $this->target['y']) {
            if ($this->target['y'] > $this->player->y) {
                $new_coord = $this->player->y + 1;
            } else {
                $new_coord = $this->player->y - 1;
            }

            $new_event = self::EVENT_MOVE_Y;
            $event_text = self::$event_names[$new_event]['text'] . $this->player->x . '.' . $new_coord;

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
                $result = $this->newBattle();
                // log the results
                $this->logNewEvent($result[0], $result[1]);
                break;

            case self::EVENT_MOVE_Y:
            case self::EVENT_MOVE_X:
                $result = $this->movePlayer($new_event, $new_coord);
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
    public function completeMission() {
        
        
        // *************** LSM ADJUSTMENTS ****************************************************************
        // Yen gain for completing the mission
        $yen_gain = self::$difficulties[$this->difficulty]['yen_per_mission'] + (random_int(0, 100));
        // ************************************************************************************************

        $this->status = 1;
        $this->end_time = time();
        $this->player->money += $yen_gain;
        $this->reward += $yen_gain;
        $this->player->special_mission = 0;

        $reward_text = self::$event_names[self::EVENT_COMPLETE_REWARD]['text'] . $yen_gain . '!';

        return $reward_text;
    }

    // Simulates a battle with an ai
    public function newBattle(): array {

        $battle_result = self::EVENT_BATTLE_WIN; // Winning by default, suffering from success
        $battle_text = self::$event_names[self::EVENT_BATTLE_WIN]['text'];

        // *************** LSM ADJUSTMENTS ****************************************************************
        // Gains for mission progress, basic stuff at the moment
        $intel_gained = self::$difficulties[$this->difficulty]['intel_gain'] + (random_int(0,5)); // Random increase on intel gain
        $yen_gain = self::$difficulties[$this->difficulty]['yen_per_battle'] + (random_int(0,5));

        // Percentage to decrease the HP loss, based on stat cap
        // We Decrease the lost hp per turn by % of user stats compared to cap
        // Maybe add ranks to this? IDK
        $hp_lost_base = self::$difficulties[$this->difficulty]['hp_lost']; // Based on difficulty
        $stat_cap_percent = floor($this->player->total_stats / $this->player->stat_cap * 100); // e.g. 90%
        $subtract_from_base_hp_lost = floor($stat_cap_percent / 100 * $hp_lost_base); // e.g. 18 hp per
        $hp_lost_percent = $hp_lost_base - $subtract_from_base_hp_lost; // e.g 2 hp per
        $hp_lost = floor($hp_lost_percent / 100 * $this->player->max_health); // e.g flat amount = 2% of hp -- 2 out of 100 hp

        // ***********************************************************************************************

        // If the user loses all HP
        if ($this->player->health - $hp_lost <= 0) {
            $battle_result = self::EVENT_BATTLE_LOSE;
            $battle_text = self::$event_names[self::EVENT_BATTLE_LOSE]['text'];
            $this->player->health = 0;
        }

        // REWARD for winning
        if ($battle_result == self::EVENT_BATTLE_WIN) {
             // intel gain
            $this->progress += $intel_gained;

            // Damage HP
            $this->player->health -= $hp_lost;
            $this->player_health -= $hp_lost;

            // Yen Gain
            $this->player->money += $yen_gain;
            $this->reward += $yen_gain;

            // generate a new target
            $this->generateTarget();

            // Modify the event text
            $battle_text .= $yen_gain."!";
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

    // Move the user to the new coordinate
    public function movePlayer($event, $new_coord) {
        if ($event == self::EVENT_MOVE_X) {
            $this->player->x = $new_coord;
        } else if ($event == self::EVENT_MOVE_Y) {
            $this->player->y = $new_coord;
        }
        return $event;
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
        $is_x_negative = (bool)random_int(0,1);
        $max_x = ($is_x_negative ? self::$target_villages[$random_village_key]['negative_x'] : self::$target_villages[$random_village_key]['positive_x']);
        $random_x = random_int(1, $max_x);
        $target_x = ($is_x_negative ? (self::$target_villages[$random_village_key]['x'] - $random_x) : (self::$target_villages[$random_village_key]['x'] + $random_x));

        $is_y_negative = (bool)random_int(0,1);
        $max_y = ($is_y_negative ? self::$target_villages[$random_village_key]['negative_y'] : self::$target_villages[$random_village_key]['positive_y']);
        $random_y = random_int(1, $max_y);
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
    
    // Cancel the mission
    public static function cancelMission($system, $player, $mission_id) {
        $timestamp = time();
        $result = $system->query("UPDATE `special_missions` SET `status`=2, `end_time`={$timestamp} WHERE `mission_id`={$mission_id}");
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