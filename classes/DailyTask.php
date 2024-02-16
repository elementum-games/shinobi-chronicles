<?php

class DailyTask {
    const NUM_PER_DAY = 3;

    const SUB_TASK_EARN = 'earn';
    const SUB_TASK_WIN_FIGHT = 'win';
    const SUB_TASK_COMPLETE = 'complete';
    const SUB_TASK_SKILL = 'skills';
    const SUB_TASK_GEN = 'attributes';
    const SUB_TASK_JUTSU = 'jutsu';

    const ACTIVITY_DAILY_PVE = 'PvE';
    const ACTIVITY_DAILY_WAR = 'War';
    const ACTIVITY_DAILY_PVP = 'PvP';
    const ACTIVITY_PVP = 'PVP';
    const ACTIVITY_ARENA = 'Arena';
    const ACTIVITY_MISSIONS = 'Missions';
    const ACTIVITY_TRAINING = 'Train';
    const ACTIVITY_EARN_MONEY = 'Money';

    const DIFFICULTY_EASY = 'Easy';
    const DIFFICULTY_MEDIUM = 'Medium';
    const DIFFICULTY_HARD = 'Hard';

    public static array $possible_task_names = [
        DailyTask::ACTIVITY_DAILY_PVE => [
            'Daily PvE Progress',
        ],
        DailyTask::ACTIVITY_DAILY_WAR => [
            'Daily War Progress',
        ],
        DailyTask::ACTIVITY_DAILY_PVP => [
            'Daily PvP Progress',
        ],
        DailyTask::ACTIVITY_PVP => [
            'Defend the Past',
            'The Beast in the West',
            'Something Immortal',
        ],
        DailyTask::ACTIVITY_ARENA => [
            'Zodiac of Solaris',
            'The Shattered Corpse',
            'Rats of the Frontline',
        ],
        DailyTask::ACTIVITY_MISSIONS => [
            'Call of Grace',
            'Made for Error',
            'Codename RED',
        ],
        DailyTask::ACTIVITY_TRAINING => [
            'Breaking Rites',
            'Cry of Menace',
            'Conjured Moon',
        ],
        DailyTask::ACTIVITY_EARN_MONEY => [
            'The Grey Quarry',
            'Hand of Midas',
            'Fill the Coffers',
        ],
    ];

    public static array $activity_labels = [
        DailyTask::ACTIVITY_PVP => 'PvP Battles',
        DailyTask::ACTIVITY_ARENA => 'Arena Battles',
        DailyTask::ACTIVITY_MISSIONS => 'Missions',
        DailyTask::ACTIVITY_TRAINING => [
            DailyTask::SUB_TASK_SKILL => 'points',
            DailyTask::SUB_TASK_GEN => 'points',
            DailyTask::SUB_TASK_JUTSU => 'levels',
        ],
        DailyTask::ACTIVITY_EARN_MONEY => 'yen',
    ];

    public string $name;
    public $activity;
    public $mission_rank;
    public string $sub_task;
    public int $amount;
    public $difficulty;
    public $reward;
    public int $rep_reward;
    public $progress;
    public bool $complete;

    public function __construct(array $db_data) {
        $this->name = $db_data['name'];
        $this->activity = $db_data['activity'];
        $this->mission_rank = $db_data['mission_rank'];
        $this->sub_task = $db_data['sub_task'];
        $this->amount = $db_data['amount'];
        $this->difficulty = $db_data['difficulty'];
        $this->reward = $db_data['reward'];
        $this->rep_reward = $db_data['rep_reward'] ?? UserReputation::DAILY_TASK_REWARDS[$this->difficulty][$this->activity];
        $this->progress = $db_data['progress'];
        $this->complete = (bool)$db_data['complete'];
    }

    public function getPrompt(): string {
        $prompt = '';
        switch ($this->activity) {
            case DailyTask::ACTIVITY_TRAINING:
                $prompt = ucwords($this->activity) . " {$this->amount} {$this->sub_task} " . self::$activity_labels[$this->activity][$this->sub_task];
                break;
            case DailyTask::ACTIVITY_DAILY_PVE:
                $prompt = "Complete Arena battles and Missions";
                break;
            case DailyTask::ACTIVITY_DAILY_WAR:
                $prompt = "Participate in War and Espionage";
                break;
            case DailyTask::ACTIVITY_DAILY_PVP:
                $prompt = "Complete PvP battles and Spars";
                break;
            default:
                $prompt = ucwords($this->sub_task) . " " . $this->amount . ' ';
                if($this->activity === DailyTask::ACTIVITY_MISSIONS) {
                    $prompt .= Mission::$rank_names[$this->mission_rank] . '+ ' . self::$activity_labels[$this->activity];
                }
                else {
                    $prompt .= self::$activity_labels[$this->activity];
                }
                break;
        }
        return $prompt;
    }

    public function getProgressPercent(): float {
        $dt_progress = 0;
        if($this->progress != 0) {
            $dt_progress = $this->progress / $this->amount * 100;
        }

        return $dt_progress;
    }

    public static function getPossibleTasks(int $user_rank): array {
        // 50|75, 1000|5000
        $possible_task_types = [
            DailyTask::ACTIVITY_DAILY_PVE => [
                'type' => DailyTask::ACTIVITY_DAILY_PVE,
                'sub_task' => [DailyTask::ACTIVITY_DAILY_PVE],
                'max_amount' => [
                    DailyTask::DIFFICULTY_EASY => 20,
                    DailyTask::DIFFICULTY_MEDIUM => 30,
                    DailyTask::DIFFICULTY_HARD => 40,
                ],
            ],
            DailyTask::ACTIVITY_DAILY_WAR => [
                'type' => DailyTask::ACTIVITY_DAILY_WAR,
                'sub_task' => [DailyTask::ACTIVITY_DAILY_WAR],
                'max_amount' => [
                    DailyTask::DIFFICULTY_EASY => 20,
                    DailyTask::DIFFICULTY_MEDIUM => 30,
                    DailyTask::DIFFICULTY_HARD => 40,
                ],
            ],
            DailyTask::ACTIVITY_DAILY_PVP => [
                'type' => DailyTask::ACTIVITY_DAILY_PVP,
                'sub_task' => [DailyTask::ACTIVITY_DAILY_PVP],
                'max_amount' => [
                    DailyTask::DIFFICULTY_EASY => 10,
                    DailyTask::DIFFICULTY_MEDIUM => 15,
                    DailyTask::DIFFICULTY_HARD => 20,
                ],
            ],
            DailyTask::ACTIVITY_PVP => [
                'type' => DailyTask::ACTIVITY_PVP,
                'sub_task' => [/*DailyTask::SUB_TASK_WIN_FIGHT, */DailyTask::SUB_TASK_COMPLETE],
                'max_amount' => [
                    DailyTask::DIFFICULTY_EASY => 4,
                    DailyTask::DIFFICULTY_MEDIUM => 6,
                    DailyTask::DIFFICULTY_HARD => 8,
                ],
            ],
            DailyTask::ACTIVITY_ARENA => [
                'type' => DailyTask::ACTIVITY_ARENA,
                'sub_task' => [DailyTask::SUB_TASK_WIN_FIGHT],
                'max_amount' => [
                    DailyTask::DIFFICULTY_EASY => 15,
                    DailyTask::DIFFICULTY_MEDIUM => 25,
                    DailyTask::DIFFICULTY_HARD => 35,
                ],
            ],
            DailyTask::ACTIVITY_MISSIONS => [
                'type' => DailyTask::ACTIVITY_MISSIONS,
                'sub_task' => [DailyTask::SUB_TASK_COMPLETE],
                'max_amount' => [
                    DailyTask::DIFFICULTY_EASY => 10,
                    DailyTask::DIFFICULTY_MEDIUM => 20,
                    DailyTask::DIFFICULTY_HARD => 30,
                ],
                'mission_rank' => [],
            ],
            DailyTask::ACTIVITY_TRAINING => [
                'type' => DailyTask::ACTIVITY_TRAINING,
                'sub_task' => [self::SUB_TASK_SKILL, self::SUB_TASK_GEN, self::SUB_TASK_JUTSU],
                'max_amount' => [
                    DailyTask::DIFFICULTY_EASY => 50,
                    DailyTask::DIFFICULTY_MEDIUM => 65,
                    DailyTask::DIFFICULTY_HARD => 80,
                ],
            ],
            DailyTask::ACTIVITY_EARN_MONEY => [
                'type' => DailyTask::ACTIVITY_EARN_MONEY,
                'sub_task' => [self::SUB_TASK_EARN],
                'max_amount' => [
                    DailyTask::DIFFICULTY_EASY => 500,
                    DailyTask::DIFFICULTY_MEDIUM => 1000,
                    DailyTask::DIFFICULTY_HARD => 1250,
                ],
            ],
        ];

        $mission_ranks = [Mission::RANK_D, Mission::RANK_C, Mission::RANK_B, Mission::RANK_A, Mission::RANK_S];
        $max_mission_rank = Mission::maxMissionRank($user_rank);

        foreach($mission_ranks as $mission_rank) {
            if($max_mission_rank < $mission_rank) {
                break;
            }
            $possible_task_types[DailyTask::ACTIVITY_MISSIONS]['mission_rank'][] = $mission_rank;
        }

        return $possible_task_types;
    }

    public static function generateTask(int $user_rank_num, string $task_activity, string $task_difficulty): DailyTask {
        // Generate new Daily Tasks if there's never been a new task or if 24hrs have ellapsed since last reset
        $possible_tasks = DailyTask::getPossibleTasks($user_rank_num);
        $task_config = $possible_tasks[$task_activity];

        // Randomly choose a mission type and amount
        $sub_task_key = array_rand($task_config['sub_task']);
        $sub_task = $task_config['sub_task'][$sub_task_key];
        $task_name = DailyTask::$possible_task_names[$task_activity][array_rand(DailyTask::$possible_task_names[$task_activity])];

        // Configure min/max amounts based on difficulty
        $max_task_amount = $task_config['max_amount'][$task_difficulty];
        $min_task_amount = floor($task_config['max_amount'][$task_difficulty] * 0.7);

        // Increase training difficulty for rank 2 [skills & attributes]
        if($task_config['type'] == DailyTask::ACTIVITY_TRAINING && $user_rank_num > 1) {
            $min_task_amount *= $user_rank_num;
            $max_task_amount *= $user_rank_num;
        }
        // Override training amounts of jutsu training
        if($task_config['type'] == DailyTask::ACTIVITY_TRAINING && $sub_task == DailyTask::SUB_TASK_JUTSU) {
            if($user_rank_num == 1) {
                $min_task_amount = 5;
                $max_task_amount = 10;
            }
            else {
                $min_task_amount = 10;
                $max_task_amount = 15;
            }
        }

        // Configure mission data
        $mission_rank = 0;
        if($task_config['type'] == DailyTask::ACTIVITY_MISSIONS) {
            $mission_rank_key = array_rand($task_config['mission_rank']);
            $mission_rank = $task_config['mission_rank'][$mission_rank_key];

            switch($mission_rank) {
                case Mission::RANK_S:
                    $min_task_amount = ceil($min_task_amount * 0.3);
                    $max_task_amount = ceil($max_task_amount * 0.3);
                    break;
                case Mission::RANK_A:
                case Mission::RANK_B:
                    $min_task_amount = ceil($min_task_amount * 0.5);
                    $max_task_amount = ceil($max_task_amount * 0.5);
                    break;
                case Mission::RANK_C:
                    $min_task_amount = ceil($min_task_amount * 0.75);
                    $max_task_amount = ceil($max_task_amount * 0.75);
                    break;
                case Mission::RANK_D:
                    break;
            }
        }

        // Assign task amount
        $type_overrides = [DailyTask::ACTIVITY_DAILY_PVE, DailyTask::ACTIVITY_DAILY_WAR, DailyTask::ACTIVITY_DAILY_PVP];
        if (in_array($task_config['type'], $type_overrides)) {
            $task_amount = $max_task_amount;
        } else {
            $task_amount = mt_rand($min_task_amount, $max_task_amount);
        }

        // Decide the Task difficulty for rewards
        $task_reward = 200 + (pow($user_rank_num, 2) * 150);
        $task_reward = round($task_reward * (mt_rand(90, 110) / 100)); // 20% randomness

        $task_win_multiplier = 1;
        $rep_reward_mod = 0;
        if($task_activity == DailyTask::ACTIVITY_PVP && $sub_task == DailyTask::SUB_TASK_WIN_FIGHT) {
            $task_win_multiplier = 2;
            $rep_reward_mod += UserReputation::DAILY_TASK_PVP_WIN_MOD;
        }

        $difficulty_multiplier = 1;
        if($task_difficulty == DailyTask::DIFFICULTY_MEDIUM) {
            $difficulty_multiplier++;
        }
        if($task_difficulty == DailyTask::DIFFICULTY_HARD) {
            $difficulty_multiplier++;
        }

        // Reputation & money reward
        $rep_reward = UserReputation::DAILY_TASK_REWARDS[$task_difficulty][$task_config['type']] + $rep_reward_mod;
        $money_reward = $task_reward * $difficulty_multiplier * $task_win_multiplier;

        return new DailyTask([
            'name' => $task_name,
            'activity' => $task_activity,
            'mission_rank' => $mission_rank,
            'sub_task' => $sub_task,
            'amount' => $task_amount,
            'difficulty' => $task_difficulty,
            'reward' => $money_reward,
            'rep_reward' => $rep_reward,
            'progress' => 0,
            'complete' => 0,
        ]);
    }

    /**
     * @param int $user_rank_num
     * @return DailyTask[]
     */
    public static function generateNewTasks(int $user_rank_num): array {
        $daily_tasks = [];
        switch ($user_rank_num) {
            case 1:
            case 2:
                $daily_tasks[] = DailyTask::generateTask(
                    $user_rank_num,
                    DailyTask::ACTIVITY_DAILY_PVE,
                    DailyTask::DIFFICULTY_MEDIUM
                );
                $daily_tasks[] = DailyTask::generateTask(
                    $user_rank_num,
                    DailyTask::ACTIVITY_EARN_MONEY,
                    DailyTask::DIFFICULTY_MEDIUM
                );
                break;
            case 3:
            case 4:
                $daily_tasks[] = DailyTask::generateTask(
                    $user_rank_num,
                    DailyTask::ACTIVITY_DAILY_PVE,
                    DailyTask::DIFFICULTY_MEDIUM
                );
                $daily_tasks[] = DailyTask::generateTask(
                    $user_rank_num,
                    DailyTask::ACTIVITY_DAILY_WAR,
                    DailyTask::DIFFICULTY_MEDIUM
                );
                $daily_tasks[] = DailyTask::generateTask(
                    $user_rank_num,
                    DailyTask::ACTIVITY_DAILY_PVP,
                    DailyTask::DIFFICULTY_MEDIUM
                );
                break;
        }
        return $daily_tasks;
    }
}
