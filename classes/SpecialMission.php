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

    // % of Extra damage taken if a jutsu is failed (no jutsu equipped, or out of pools)
    const FAILED_JUTSU_DAMAGE_PERCENT = 100;

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
        SpecialMission::DIFFICULTY_EASY => [ // tested time: 2:45-4:15 minutes
            'yen_per_battle' => 8,
            'yen_per_mission' => 70,
            'stats_per_mission' => 2,
            // 22.5% => 67.5% lost
            'hp_lost_percent' => 2.5,
            // 9 fights
            'intel_gain' => 12,
            'rep_gain' => UserReputation::SPECIAL_MISSION_REP_GAINS[SpecialMission::DIFFICULTY_EASY],
            'battles_per_region' => 3, // 3 regions
        ],
        SpecialMission::DIFFICULTY_NORMAL => [
            // 10 * 11 = 110
            'yen_per_battle' => 10,
            'yen_per_mission' => 110,
            'stats_per_mission' => 4,
            // 2.9: 34.8% => 104.4% lost
            'hp_lost_percent' => 2.9,
            'intel_gain' => 9, // 12 fights
            'rep_gain' => UserReputation::SPECIAL_MISSION_REP_GAINS[SpecialMission::DIFFICULTY_NORMAL],
            'battles_per_region' => 4, // 3 regions
        ],
        SpecialMission::DIFFICULTY_HARD => [
            // 12 * 12.5 = 150
            'yen_per_battle' => 12,
            'yen_per_mission' => 150,
            'stats_per_mission' => 6,
            // 3: 45% => 135%
            'hp_lost_percent' => 3,
            // 15 fights
            'intel_gain' => 7,
            'rep_gain' => UserReputation::SPECIAL_MISSION_REP_GAINS[SpecialMission::DIFFICULTY_HARD],
            'battles_per_region' => 5, // 3 regions
        ],
        SpecialMission::DIFFICULTY_NIGHTMARE => [ // tested time: 3:00-4:30 minutes
            'yen_per_battle' => 14, // 20 * 14.28 = 285
            'yen_per_mission' => 200,
            'stats_per_mission' => 8,
            // 3.9: 66.3% => 198.9% lost
            'hp_lost_percent' => 3.9,
            'intel_gain' => 6, // 17 fights
            'rep_gain' => UserReputation::SPECIAL_MISSION_REP_GAINS[SpecialMission::DIFFICULTY_NIGHTMARE],
            'battles_per_region' => 6, // 3 regions
        ]
    ];

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

    const STATUS_ACTIVE = 0;
    const STATUS_COMPLETED = 1;
    const STATUS_FAILED = 2;

    public static array $valid_targets = [
        SpecialMission::SPY_TARGET_LEAF => [
            SpecialMission::SPY_TARGET_LEAF => true,
            SpecialMission::SPY_TARGET_STONE => true,
            SpecialMission::SPY_TARGET_SAND => true,
            SpecialMission::SPY_TARGET_CLOUD => true,
            SpecialMission::SPY_TARGET_MIST => true,
        ],
        SpecialMission::SPY_TARGET_STONE => [
            SpecialMission::SPY_TARGET_LEAF => true,
            SpecialMission::SPY_TARGET_STONE => true,
            SpecialMission::SPY_TARGET_SAND => true,
            SpecialMission::SPY_TARGET_CLOUD => true,
            SpecialMission::SPY_TARGET_MIST => false,
        ],
        SpecialMission::SPY_TARGET_SAND => [
            SpecialMission::SPY_TARGET_LEAF => true,
            SpecialMission::SPY_TARGET_STONE => true,
            SpecialMission::SPY_TARGET_SAND => true,
            SpecialMission::SPY_TARGET_CLOUD => false,
            SpecialMission::SPY_TARGET_MIST => true,
        ],
        SpecialMission::SPY_TARGET_CLOUD => [
            SpecialMission::SPY_TARGET_LEAF => true,
            SpecialMission::SPY_TARGET_STONE => true,
            SpecialMission::SPY_TARGET_SAND => false,
            SpecialMission::SPY_TARGET_CLOUD => true,
            SpecialMission::SPY_TARGET_MIST => true,
        ],
        SpecialMission::SPY_TARGET_MIST => [
            SpecialMission::SPY_TARGET_LEAF => true,
            SpecialMission::SPY_TARGET_STONE => false,
            SpecialMission::SPY_TARGET_SAND => true,
            SpecialMission::SPY_TARGET_CLOUD => true,
            SpecialMission::SPY_TARGET_MIST => true,
        ],
    ];

    public static array $target_villages = [
        SpecialMission::SPY_TARGET_LEAF => [
            'negative_x' => 5,
            'positive_x' => 5,
            'negative_y' => 5,
            'positive_y' => 5
        ],
        SpecialMission::SPY_TARGET_STONE => [
            'negative_x' => 5,
            'positive_x' => 5,
            'negative_y' => 4,
            'positive_y' => 5
        ],
        SpecialMission::SPY_TARGET_SAND => [
            'negative_x' => 4,
            'positive_x' => 5,
            'negative_y' => 5,
            'positive_y' => 5
        ],
        SpecialMission::SPY_TARGET_CLOUD => [
            'negative_x' => 5,
            'positive_x' => 5,
            'negative_y' => 4,
            'positive_y' => 5
        ],
        SpecialMission::SPY_TARGET_MIST => [
            'negative_x' => 5,
            'positive_x' => 5,
            'negative_y' => 5,
            'positive_y' => 4
        ]
    ];

    private User $player;
    private System $system;

    public int $mission_id;
    private string $difficulty;

    public int $status;

    public int $start_time;
    public int $end_time;
    public int $progress;

    private ?SpecialMissionTarget $target;

    public $log;
    public int $player_health;
    public int $player_max_health;
    public int $reward;

    /**
     * @throws DatabaseDeadlockException
     */
    public function __construct(System $system, User $player, int $mission_id) {
        $this->system = $system;
        $this->player = $player;
        $this->mission_id = $mission_id;

        // GET MISSION DATA
        $result = $this->system->db->query("SELECT * FROM `special_missions` 
            WHERE `mission_id`={$this->mission_id}
            AND `user_id`={$this->player->user_id}
        ");
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
        $this->log = json_decode($mission_data['log'], true);
        $this->reward = $mission_data['reward'];

        $target_data = json_decode($mission_data['target'], true);
        $this->target = $target_data ? SpecialMissionTarget::fromArray($target_data) : null;

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
        $move_to_x = $this->player->location->x;
        $move_to_y = $this->player->location->y;

        // Check if the user is in battle
        if ($this->player->battle_id) {
            $new_event = self::EVENT_COMPLETE_FAIL;
            $event_text = self::$event_names[$new_event]['text'];
        }
        else {
            // Check if the user is in the target square
            if ($this->target->x == $this->player->location->x && $this->target->y == $this->player->location->y) {
                $new_event = self::EVENT_BATTLE;
            }

            // check if the user lost the battle, fail the mission
            if ($last_event['event'] == self::EVENT_BATTLE_LOSE) {
                $new_event = self::EVENT_COMPLETE_FAIL;
            }

            // check if the user has enough progress to complete mission and is back home
            if (
                $this->progress >= 100 && $this->player->location->x == self::$target_villages[$this->player->village->name]['x']
                && $this->player->location->y == self::$target_villages[$this->player->village->name]['y']
            ) {
                $new_event = self::EVENT_COMPLETE_SUCCESS;
            }

            // check what direction the user has to travel
            $villages = TravelManager::fetchVillageLocationsByCoordsStr($this->system);

            if ($this->player->location->x != $this->target->x) {
                if ($this->target->x > $this->player->location->x) {
                    $move_to_x = $this->player->location->x + 1;
                } else {
                    $move_to_x = $this->player->location->x - 1;
                }

                // Move Diagonal
                if ($this->player->location->y != $this->target->y) {
                    if ($this->target->y > $this->player->location->y) {
                        $move_to_y = $this->player->location->y + 1;
                    } else {
                        $move_to_y = $this->player->location->y - 1;
                    }
                }

                // Go around village not into it
                $target_location = new TravelCoords($move_to_x, $move_to_y, $this->player->location->map_id);
                if (isset($villages[$target_location->toString()]) && !$this->player->village_location->equals($target_location)) {
                    if ($this->player->location->y > $this->target->y) {
                        $move_to_y--;
                    } else {
                        $move_to_y++;
                    }
                }

                $new_event = self::EVENT_MOVE_X;

            } else if ($this->player->location->y != $this->target->y) {
                if ($this->target->y > $this->player->location->y) {
                    $move_to_y = $this->player->location->y + 1;
                } else {
                    $move_to_y = $this->player->location->y - 1;
                }

                // Move Diagonal
                if ($this->player->location->x != $this->target->x) {
                    if ($this->target->x > $this->player->location->x) {
                        $move_to_x = $this->player->location->x + 1;
                    } else {
                        $move_to_x = $this->player->location->x - 1;
                    }
                }

                // Skip past village if trying to move into it
                $target_location = new TravelCoords($move_to_x, $move_to_y, $this->player->location->map_id);
                if (isset($villages[$target_location->toString()]) && !$this->player->village_location->equals($target_location)) {
                    if ($this->player->location->x > $this->target->x) {
                        $move_to_x--;
                    } else {
                        $move_to_x++;
                    }
                }

                $new_event = self::EVENT_MOVE_Y;
            }

            // check if the mission is complete
            if ($this->progress >= 100 && $this->target->target_village != $this->player->village->name) {
                $new_event = self::EVENT_HOME;
            }
        }

        // Log the event
        if(SpecialMission::isMovingEvent($new_event)) {
            $event_text = "You moved to {$move_to_x}.{$move_to_y}";

            // Temporarily doing one-move-per-log on dev environment for network performance benchmarking
            if($this->system->isDevEnvironment()) {
                $this->logNewEvent($new_event, $event_text);
            }
            else {
                $latest_log_index = array_key_first($this->log);
                if(SpecialMission::isMovingEvent($this->log[$latest_log_index]['event'])) {
                    $this->log[$latest_log_index] = [
                        'event' => $new_event,
                        'timestamp_ms' => System::currentTimeMs(),
                        'description' => $event_text,
                    ];
                }
                else {
                    $this->logNewEvent($new_event, "You set out towards the next location at {$this->target->x}.{$this->target->y}");
                    $this->logNewEvent($new_event, $event_text);
                }
            }
        }
        else {
            $this->logNewEvent($new_event, self::$event_names[$new_event]['text']);
        }

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
                    expires: time() + (NotificationManager::NOTIFICATION_EXPIRATION_DAYS_SPECIAL_MISSION * 86400),
                    alert: true,
                );
                NotificationManager::createNotification($new_notification, $this->system, NotificationManager::UPDATE_REPLACE);
                break;
            case self::EVENT_COMPLETE_FAIL:
                $result = $this->completeMission($this->progress);
                if (strlen($result) > 0) {
                    $this->logNewEvent(self::EVENT_COMPLETE_FAIL, $result);
                }
                $result = $this->failMission();
                // Create notification
                require_once __DIR__ . '/../classes/notification/NotificationManager.php';
                $new_notification = new NotificationDto(
                    type: "specialmission_failed",
                    message: "Special Mission failed",
                    user_id: $this->player->user_id,
                    created: time(),
                    alert: true,
                    expires: time() + (NotificationManager::NOTIFICATION_EXPIRATION_DAYS_SPECIAL_MISSION * 86400),
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

            $this->status = self::STATUS_COMPLETED;
            $this->end_time = time();
            $this->player->addMoney($yen_gain, "Special mission");
            $this->reward += $yen_gain;
            $this->player->special_mission_id = 0;

            $reward_text = self::$event_names[self::EVENT_COMPLETE_REWARD]['text'] . $yen_gain . '!';

            //Reputation Reward
            if ($this->player->reputation->canGain(UserReputation::ACTIVITY_TYPE_PVE)) {
                $rep_gain = $this->player->reputation->addRep(self::$difficulties[$this->difficulty]['rep_gain'], UserReputation::ACTIVITY_TYPE_PVE);
                if ($rep_gain > 0) {
                    $this->player->mission_rep_cd = time() + UserReputation::ARENA_MISSION_CD;
                    $reward_text .= ' You have gained ' . $rep_gain . " village reputation!";
                }
            }
            // Daily Task
            if ($this->player->daily_tasks->hasTaskType(DailyTask::ACTIVITY_DAILY_PVE)) {
                $this->player->daily_tasks->progressTask(DailyTask::ACTIVITY_DAILY_PVE, self::$difficulties[$this->difficulty]['rep_gain']);
            }
        }

        $stat_to_gain = $this->player->getTrainingStatForArena();
        $extra_stats_for_rank = max(0, $this->player->rank_num - 2) * 2;
        $stat_gain = floor(
           (self::$difficulties[$this->difficulty]['stats_per_mission'] + $extra_stats_for_rank)
           * $progress_modifier
        );
        if($stat_to_gain != null && $stat_gain > 0) {
            $stat_gained = $this->player->addStatGain($stat_to_gain, $stat_gain);
            if (!empty($stat_gained)) {
                $reward_text .= ' ' . $stat_gained . '!';
            }
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

        $extra_health_lost = 0; // if you can't use any jutsu, consumes double the HP cost

        $health_per_jutsu = $health_lost / self::JUTSU_USES_PER_FIGHT;
        $failed_jutsu_extra_health_lost = $health_per_jutsu * (self::FAILED_JUTSU_DAMAGE_PERCENT / 100);

        for($i = 0; $i < self::JUTSU_USES_PER_FIGHT; $i++) {
            $jutsu = $this->pickJutsuToUse();
            if($jutsu == null) {
                $extra_health_lost += $failed_jutsu_extra_health_lost;
                continue;
            }

            $battle_text .= "[br] - Used {$jutsu->name} (level {$jutsu->level})";

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
        if($extra_health_lost > 0) {
            if($this->pickJutsuToUse() == null) {
                $battle_text .= "[br]You did not have any jutsu prepared, and were wounded as you fought with only basic taijutsu.";
            }
            else {
                $battle_text .= "[br]You ran out of chakra/stamina mid fight, and were wounded as you fought with only basic taijutsu.";
            }
        }

        $this->player->updateInventory();

        // Gains for mission progress, basic stuff at the moment.
        $intel_gained = self::$difficulties[$this->difficulty]['intel_gain'];

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
            $battle_text .= "[br]You lost {$health_lost} health";

            // Yen Gain
            $this->player->addMoney($yen_gain, "Special mission encounter");
            $this->reward += $yen_gain;

            // generate a new target
            $this->generateTarget();

            // Modify the event text
            $battle_text .= "[br][br]You collected &#165;{$yen_gain}!";
        }

        return ([$battle_result, $battle_text]);
    }

    protected function pickJutsuToUse(): ?Jutsu {
        $has_equipped_jutsu = count($this->player->equipped_jutsu) > 0;
        $has_bloodline_jutsu = $this->player->bloodline && count($this->player->bloodline->jutsu) > 0;

        $equipped_jutsu_ids = array_map(function($ej){ return $ej['id']; }, $this->player->equipped_jutsu);
        $uncapped_jutsu_ids = array_filter($equipped_jutsu_ids, function($jutsu_id) {
            return $this->player->hasJutsu($jutsu_id) && $this->player->jutsu[$jutsu_id]->level < 100;
        });

        $bloodline_jutsu_ids = array_map(function($bj){ return $bj->id; }, $this->player->bloodline?->jutsu ?? []);
        $uncapped_bl_jutsu_ids = array_filter($bloodline_jutsu_ids, function($jutsu_id) {
            return $this->player->bloodline->jutsu[$jutsu_id]->level < 100;
        });

        $use_bloodline_jutsu = $has_bloodline_jutsu && mt_rand(1, 100) <= self::BLOODLINE_JUTSU_CHANCE;

        // First, try uncapped BL jutsu (if BL roll has been triggered)
        if($use_bloodline_jutsu && count($uncapped_bl_jutsu_ids) > 0) {
            $jutsu_key = array_rand($uncapped_bl_jutsu_ids);
            $jutsu_id = $uncapped_bl_jutsu_ids[$jutsu_key];
            $jutsu = $this->player->bloodline->jutsu[$jutsu_id];
        }
        // Then uncapped equipped jutsu
        else if(count($uncapped_jutsu_ids) > 0) {
            $jutsu_key = array_rand($uncapped_jutsu_ids);
            $jutsu_id = $uncapped_jutsu_ids[$jutsu_key];
            $jutsu = $this->player->jutsu[$jutsu_id] ?? null;
        }
        // Then capped BL jutsu (if BL roll has been triggered)
        else if($use_bloodline_jutsu) {
            $jutsu_id = array_rand($this->player->bloodline->jutsu);
            $jutsu = $this->player->bloodline->jutsu[$jutsu_id];
        }
        // Then uncapped equipped jutsu
        else if($has_equipped_jutsu) {
            $jutsu_key = array_rand($this->player->equipped_jutsu);
            $jutsu_id = $this->player->equipped_jutsu[$jutsu_key]['id'];
            $jutsu = $this->player->jutsu[$jutsu_id] ?? null;
        }
        else {
            $jutsu = null;
        }

        return $jutsu;
    }

    // Fails the mission
    public function failMission(): bool {
        $this->end_time = time();
        $this->status = self::STATUS_FAILED;
        if (!$this->player->battle_id) {
            $this->player->location->x = self::$target_villages[$this->player->village->name]['x'];
            $this->player->location->y = self::$target_villages[$this->player->village->name]['y'];
        }
        $this->player->special_mission_id = 0;
        return true;
    }

    // Enter the new event into the log
    public function logNewEvent($new_event, $event_text): bool {
        $log_entry = [
            'event' => $new_event,
            'timestamp_ms' => System::currentTimeMs(),
            'description' => $event_text
        ];
        array_unshift($this->log, $log_entry);
        return true;
    }

    // Generates a new target location
    public function generateTarget($return_home = false): bool {
        /* general flow here - Pick a target village (e.g. cloud) and then visit a number of minor villages in that region */

        if ($this->target == null) {
            $this->target = $this->generateVillageTarget();
        }
        else if ($return_home) {
            $this->target = new SpecialMissionTarget(
                target_village: $this->player->village->name,
                x: self::$target_villages[$this->player->village->name]['x'],
                y: self::$target_villages[$this->player->village->name]['y'],
                count: 0,
            );
        }
        // create a set number of battles per region targeted
        else if ($this->target->count < self::$difficulties[$this->difficulty]['battles_per_region']) {
            // Set the Village
            $target_village = $this->target->target_village;
            $action_target_coords = $this->generateTargetCoords($target_village);

            $this->target = new SpecialMissionTarget(
                target_village: $target_village,
                x: $action_target_coords->x,
                y: $action_target_coords->y,
                count: $this->target->count + 1,
            );
        }
        else {
            $this->target = $this->generateVillageTarget();
        }

        return true;
    }

    protected function generateVillageTarget(): SpecialMissionTarget {
        // Set the Village
        $random_village_key = false;
        while ($random_village_key == false) {
            $key = array_rand(self::$target_villages, 1);
            if ($key != $this->player->village->name && SpecialMission::$valid_targets[$this->player->village->name][$key]) {
                $random_village_key = $key;
            }
        }

        $action_target_coords = $this->generateTargetCoords($random_village_key);

        return new SpecialMissionTarget(
            target_village: $random_village_key,
            x: $action_target_coords->x,
            y: $action_target_coords->y,
            count: 1,
        );
    }

    protected function generateTargetCoords(string $target_village): TravelCoords {
        $target_village_zone = self::$target_villages[$target_village];

        // Set the coords
        $is_x_negative = (bool) mt_rand(0, 1);
        $max_x = $is_x_negative
            ? $target_village_zone['negative_x']
            : $target_village_zone['positive_x'];
        $random_x = mt_rand(1, $max_x);
        $target_x = $is_x_negative
            ? ($target_village_zone['x'] - $random_x)
            : ($target_village_zone['x'] + $random_x);

        $is_y_negative = (bool) mt_rand(0, 1);
        $max_y = $is_y_negative
            ? $target_village_zone['negative_y']
            : $target_village_zone['positive_y'];
        $random_y = mt_rand(1, $max_y);
        $target_y = $is_y_negative
            ? ($target_village_zone['y'] - $random_y)
            : ($target_village_zone['y'] + $random_y);

        return new TravelCoords(
            x: $target_x,
            y: $target_y,
            map_id: Travel::DEFAULT_MAP_ID
        );
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
            ($stats_percent - self::BASE_STAT_CAP_PERCENT) /
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
        $nominal_health_lost = $this->player->max_health * ($hp_lost_percent / 100);

        return floor(
            ($nominal_health_lost * mt_rand(95, 105)) / 100
        );
    }

    protected static function isMovingEvent(string $event): bool {
        switch($event) {
            case self::EVENT_MOVE_Y:
            case self::EVENT_MOVE_X:
                return true;
            default:
                return false;
        }
    }

    // Cancel the mission
    public static function cancelMission(System $system, User $player, int $mission_id): bool {
        $timestamp = time();
        $system->db->query("UPDATE `special_missions`
            SET `status`=" . self::STATUS_FAILED . ", `end_time`={$timestamp} WHERE `mission_id`={$mission_id}");
        $player->special_mission_id = 0;
        $player->updateData();
        return true;
    }

    public static function startMission($system, User $player, $difficulty): SpecialMission {
        if ($player->special_mission_id != 0) {
            throw new RuntimeException('You cannot start multiple missions!');
        }

        if (!array_key_exists($difficulty, self::$difficulties)) {
            throw new RuntimeException('Error setting difficulty!');
        }

        if (!$player->location->equals($player->village_location)) {
            throw new RuntimeException('Must be in village to begin a Special Mission!');
        }

        // Clean up old special missions
        $system->db->query("DELETE FROM `special_missions` WHERE `user_id`={$player->user_id} AND `start_time` < " . (time() - 3600));

        $timestamp = time();

        $log = [
            0 => [
                'event' => self::$event_names['start']['event'],
                'timestamp_ms' => floor(microtime(true) * 1000),
                'description' => self::$event_names['start']['text']
            ]
        ];
        $log_encode = json_encode($log);


        $result = $system->db->query("
            INSERT INTO `special_missions` 
                (`user_id`, `start_time`, `log`, `difficulty`)
            VALUES 
                ('{$player->user_id}', '{$timestamp}', '$log_encode', '{$difficulty}')
        ");

        $mission_id = $system->db->last_insert_id;
        $player->special_mission_id = $mission_id;

        return new SpecialMission($system, $player, $mission_id);
    }

}

class SpecialMissionTarget {
    public function __construct(
        public string $target_village,
        public int $x,
        public int $y,
        public int $count,
    ){}

    public static function fromArray(array $data): SpecialMissionTarget {
        return new SpecialMissionTarget(
            // old special missions before this class will have it under `target`, new
            // special missions after this class will be `target_village`
            target_village: $data['target_village'] ?? $data['target'],
            x: $data['x'],
            y: $data['y'],
            count: $data['count'],
        );
    }
}