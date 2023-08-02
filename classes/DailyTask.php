<?php

class DailyTask {
    const NUM_PER_DAY = 3;

    const SUB_TASK_EARN = 'earn';
    const SUB_TASK_WIN_FIGHT = 'win';
    const SUB_TASK_COMPLETE = 'complete';
    const SUB_TASK_SKILL = 'skills';
    const SUB_TASK_GEN = 'attributes';
    const SUB_TASK_JUTSU = 'jutsu';

    const ACTIVITY_PVP = 'PVP';
    const ACTIVITY_ARENA = 'Arena';
    const ACTIVITY_MISSIONS = 'Missions';
    const ACTIVITY_TRAINING = 'Train';
    const ACTIVITY_EARN_MONEY = 'Money';

    const DIFFICULTY_EASY = 'Easy';
    const DIFFICULTY_MEDIUM = 'Medium';
    const DIFFICULTY_HARD = 'Hard';

    public static array $possible_task_names = [
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
        if($this->activity === DailyTask::ACTIVITY_TRAINING) {
            $prompt = ucwords($this->activity) . " {$this->amount} {$this->sub_task} " . self::$activity_labels[$this->activity][$this->sub_task];
        }
        else {
            $prompt = ucwords($this->sub_task) . " " . $this->amount . ' ';
            if($this->activity === DailyTask::ACTIVITY_MISSIONS) {
                $prompt .= Mission::$rank_names[$this->mission_rank] . ' ' . self::$activity_labels[$this->activity];
            }
            else {
                $prompt .= self::$activity_labels[$this->activity];
            }
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
            DailyTask::ACTIVITY_PVP => [
                'type' => DailyTask::ACTIVITY_PVP,
                'sub_task' => [DailyTask::SUB_TASK_WIN_FIGHT, DailyTask::SUB_TASK_COMPLETE],
                'min_amount' => [
                    DailyTask::DIFFICULTY_EASY => 1,
                    DailyTask::DIFFICULTY_MEDIUM => 3,
                    DailyTask::DIFFICULTY_HARD => 7,
                ],
                'max_amount' => [
                    DailyTask::DIFFICULTY_EASY => 3,
                    DailyTask::DIFFICULTY_MEDIUM => 5,
                    DailyTask::DIFFICULTY_HARD => 10,
                ],
            ],
            DailyTask::ACTIVITY_ARENA => [
                'type' => DailyTask::ACTIVITY_ARENA,
                'sub_task' => [DailyTask::SUB_TASK_WIN_FIGHT],
                'min_amount' => [
                    DailyTask::DIFFICULTY_EASY => 5,
                    DailyTask::DIFFICULTY_MEDIUM => 16,
                    DailyTask::DIFFICULTY_HARD => 21,
                ],
                'max_amount' => [
                    DailyTask::DIFFICULTY_EASY => 15,
                    DailyTask::DIFFICULTY_MEDIUM => 20,
                    DailyTask::DIFFICULTY_HARD => 30,
                ],
            ],
            DailyTask::ACTIVITY_MISSIONS => [
                'type' => DailyTask::ACTIVITY_MISSIONS,
                'sub_task' => [DailyTask::SUB_TASK_COMPLETE],
                'min_amount' => [
                    DailyTask::DIFFICULTY_EASY => 5,
                    DailyTask::DIFFICULTY_MEDIUM => 11,
                    DailyTask::DIFFICULTY_HARD => 16,
                ],
                'max_amount' => [
                    DailyTask::DIFFICULTY_EASY => 10,
                    DailyTask::DIFFICULTY_MEDIUM => 15,
                    DailyTask::DIFFICULTY_HARD => 25,
                ],
                'mission_rank' => [],
            ],
            DailyTask::ACTIVITY_TRAINING => [
                'type' => DailyTask::ACTIVITY_TRAINING,
                'sub_task' => [self::SUB_TASK_SKILL, self::SUB_TASK_GEN, self::SUB_TASK_JUTSU],
                'min_amount' => [
                    DailyTask::DIFFICULTY_EASY => 25,
                    DailyTask::DIFFICULTY_MEDIUM => 51,
                    DailyTask::DIFFICULTY_HARD => 61,
                ],
                'max_amount' => [
                    DailyTask::DIFFICULTY_EASY => 50,
                    DailyTask::DIFFICULTY_MEDIUM => 60,
                    DailyTask::DIFFICULTY_HARD => 75,
                ],
            ],
            DailyTask::ACTIVITY_EARN_MONEY => [
                'type' => DailyTask::ACTIVITY_EARN_MONEY,
                'sub_task' => [self::SUB_TASK_EARN],
                'min_amount' => [
                    DailyTask::DIFFICULTY_EASY => 200,
                    DailyTask::DIFFICULTY_MEDIUM => 750,
                    DailyTask::DIFFICULTY_HARD => 1000,
                ],
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

    public static function generateTask(int $user_rank_num, string $task_activity, &$possible_difficulties): DailyTask {
        // Generate new Daily Tasks if there's never been a new task or if 24hrs have ellapsed since last reset

        $possible_tasks = DailyTask::getPossibleTasks($user_rank_num);
        $task_config = $possible_tasks[$task_activity];

        // Randomly choose a mission type and amount
        $sub_task_key = array_rand($task_config['sub_task'], 1);
        $sub_task = $task_config['sub_task'][$sub_task_key];
        $task_name = DailyTask::$possible_task_names[$task_activity][array_rand(DailyTask::$possible_task_names[$task_activity], 1)];

        // Configure mission data
        $mission_rank = 0;
        if($task_config['type'] == DailyTask::ACTIVITY_MISSIONS) {
            $mission_rank_key = array_rand($task_config['mission_rank'], 1);
            $mission_rank = $task_config['mission_rank'][$mission_rank_key];
        }

        // Determine task difficulty & remove for next tasks
        $task_difficulty_key = array_rand($possible_difficulties);
        $task_difficulty = $possible_difficulties[$task_difficulty_key];
        unset($possible_difficulties[$task_difficulty_key]);

        // Configure min/max amounts based on difficulty
        $task_config['min_amount'] = $task_config['min_amount'][$task_difficulty];
        $task_config['max_amount'] = $task_config['max_amount'][$task_difficulty];

        // Increase training difficulty for rank 2 [skills & attributes]
        if($task_config['type'] == DailyTask::ACTIVITY_TRAINING && $user_rank_num > 1) {
            $task_config['min_amount'] = ceil($task_config['min_amount'] * 1.75);
            $task_config['max_amount'] = ceil($task_config['max_amount'] * 1.75);
        }
        // Override training amounts of jutsu training
        if($task_config['type'] == DailyTask::ACTIVITY_TRAINING && $sub_task == DailyTask::SUB_TASK_JUTSU) {
            if($user_rank_num == 1) {
                $task_config['min_amount'] = 5;
                $task_config['max_amount'] = 10;
            }
            else {
                $task_config['min_amount'] = 10;
                $task_config['max_amount'] = 15;
            }
        }

        // Assign task amount
        $task_amount = mt_rand($task_config['min_amount'], $task_config['max_amount']);

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
        $possible_difficulties = [self::DIFFICULTY_EASY, self::DIFFICULTY_MEDIUM, self::DIFFICULTY_HARD];
        $daily_tasks = [];

        $daily_tasks[] = DailyTask::generateTask($user_rank_num, DailyTask::ACTIVITY_ARENA, $possible_difficulties);

        // Rank 1 only
        if($user_rank_num == 1) {
            $daily_tasks[] = DailyTask::generateTask($user_rank_num, DailyTask::ACTIVITY_EARN_MONEY, $possible_difficulties);
        }
        // Rank 1 and 2 only - training tasks
        if($user_rank_num <= 2) {
            $daily_tasks[] = DailyTask::generateTask($user_rank_num, DailyTask::ACTIVITY_TRAINING, $possible_difficulties);
        }
        // Rank 2+ mission activity
        if($user_rank_num >= 2) {
            $daily_tasks[] = DailyTask::generateTask($user_rank_num, DailyTask::ACTIVITY_MISSIONS, $possible_difficulties);
        }
        // Rank 3+ pvp
        if($user_rank_num >= 3) {
            $daily_tasks[] = DailyTask::generateTask($user_rank_num, DailyTask::ACTIVITY_PVP, $possible_difficulties);
        }

        return $daily_tasks;
    }
}
