<?php

class DailyTask {
    const NUM_PER_DAY = 3;

    const SUB_TASK_EARN = 'earn';
    const SUB_TASK_WIN_FIGHT = 'win';
    const SUB_TASK_COMPLETE = 'complete';
    const SUB_TASK_SKILL = 'skills';
    const SUB_TASK_ATTRIBUTES = 'attributes';
    const SUB_TASK_JUTSU = 'jutsu';

    const ACTIVITY_PVP = 'PVP';
    const ACTIVITY_ARENA = 'Arena';
    const ACTIVITY_MISSIONS = 'Missions';
    const ACTIVITY_TRAINING = 'Train';
    const ACTIVITY_EARN_MONEY = 'Money';
    const ACTIVITY_BATTLES = 'Battles';

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
        DailyTask::ACTIVITY_BATTLES => [
            'Fill Thine Goblet',
            'Battle Master',
            'Scar Thy Foe',
        ],
    ];

    public static array $activity_labels = [
        DailyTask::ACTIVITY_PVP => 'PvP Battles',
        DailyTask::ACTIVITY_ARENA => 'Arena Battles',
        DailyTask::ACTIVITY_MISSIONS => 'Missions',
        DailyTask::ACTIVITY_TRAINING => [
            DailyTask::SUB_TASK_SKILL => 'points',
            DailyTask::SUB_TASK_ATTRIBUTES => 'points',
            DailyTask::SUB_TASK_JUTSU => 'levels',
        ],
        DailyTask::ACTIVITY_EARN_MONEY => Currency::MONEY_NAME,
        DailyTask::ACTIVITY_BATTLES => 'PvP Battles or Spars',
    ];

    public static array $task_reward_multipliers = [
        DailyTask::DIFFICULTY_EASY => [
            DailyTask::ACTIVITY_PVP => 25,
            DailyTask::ACTIVITY_ARENA => 40,
            DailyTask::ACTIVITY_MISSIONS => 30,
            DailyTask::ACTIVITY_TRAINING => 30,
            DailyTask::ACTIVITY_EARN_MONEY => 20,
            DailyTask::ACTIVITY_BATTLES => 25,
        ],
        DailyTask::DIFFICULTY_MEDIUM => [
            DailyTask::ACTIVITY_PVP => 50,
            DailyTask::ACTIVITY_ARENA => 60,
            DailyTask::ACTIVITY_MISSIONS => 60,
            DailyTask::ACTIVITY_TRAINING => 60,
            DailyTask::ACTIVITY_EARN_MONEY => 40,
            DailyTask::ACTIVITY_BATTLES => 50,
        ],
        DailyTask::DIFFICULTY_HARD => [
            DailyTask::ACTIVITY_PVP => 75,
            DailyTask::ACTIVITY_ARENA => 100,
            DailyTask::ACTIVITY_MISSIONS => 90,
            DailyTask::ACTIVITY_TRAINING => 90,
            DailyTask::ACTIVITY_EARN_MONEY => 60,
            DailyTask::ACTIVITY_BATTLES => 75,
        ]
    ];
    const TASK_REWARD_MULTIPLE_OF = 25;

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
                $prompt .= Mission::$rank_names[$this->mission_rank] . '+ ' . self::$activity_labels[$this->activity];
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
                    DailyTask::DIFFICULTY_EASY => 15,
                    DailyTask::DIFFICULTY_MEDIUM => 25,
                    DailyTask::DIFFICULTY_HARD => 35,
                ],
                'mission_rank' => [],
            ],
            DailyTask::ACTIVITY_TRAINING => [
                'type' => DailyTask::ACTIVITY_TRAINING,
                'sub_task' => [self::SUB_TASK_SKILL, self::SUB_TASK_ATTRIBUTES, self::SUB_TASK_JUTSU],
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
                    DailyTask::DIFFICULTY_EASY => Currency::getRoundedYen(rank_num: $user_rank, multiplier: 15, multiple_of: 10),
                    DailyTask::DIFFICULTY_MEDIUM => Currency::getRoundedYen(rank_num: $user_rank, multiplier: 25, multiple_of: 10),
                    DailyTask::DIFFICULTY_HARD => Currency::getRoundedYen(rank_num: $user_rank, multiplier: 40, multiple_of: 10),
                ],
            ],
            DailyTask::ACTIVITY_BATTLES => [
                'type' => DailyTask::ACTIVITY_BATTLES,
                'sub_task' => [self::SUB_TASK_COMPLETE],
                'max_amount' => [
                    DailyTask::DIFFICULTY_EASY => 10,
                    DailyTask::DIFFICULTY_MEDIUM => 20,
                    DailyTask::DIFFICULTY_HARD => 30,
                ]
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
        $task_amount = mt_rand($min_task_amount, $max_task_amount);

        // Decide the Task difficulty for rewards
        $task_reward = 200 + (pow($user_rank_num, 2) * 150);
        $task_reward = round($task_reward * (mt_rand(90, 110) / 100)); // 20% randomness

        $rep_reward_mod = 0;
        if($task_activity == DailyTask::ACTIVITY_PVP && $sub_task == DailyTask::SUB_TASK_WIN_FIGHT) {
            $rep_reward_mod += UserReputation::DAILY_TASK_PVP_WIN_MOD;
        }

        // Reputation & money reward
        $rep_reward = UserReputation::DAILY_TASK_REWARDS[$task_difficulty][$task_config['type']] + $rep_reward_mod;
        $money_reward = Currency::getRoundedYen(
            rank_num: $user_rank_num,
            multiplier: self::$task_reward_multipliers[$task_difficulty][$task_config['type']],
            multiple_of: self::TASK_REWARD_MULTIPLE_OF
        );

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
    public static function generateNewTasks(int $user_rank_num, int $total_skill, int $total_attributes, int $pvp_rep): array {
        $daily_tasks = [];

        // Rank 1 only
        if($user_rank_num == 1) {
            $daily_tasks[] = DailyTask::generateTask(
                $user_rank_num,
                DailyTask::ACTIVITY_EARN_MONEY,
                DailyTask::DIFFICULTY_MEDIUM
            );
        }
        // Standard Rank 1+
        $daily_tasks[] = DailyTask::generateTask(
            $user_rank_num,
            DailyTask::ACTIVITY_ARENA,
            DailyTask::DIFFICULTY_MEDIUM
        );
        // Rank 1 & 2
        if($user_rank_num < 3) {
            $daily_tasks[] = DailyTask::generateTask(
                $user_rank_num,
                DailyTask::ACTIVITY_TRAINING,
                DailyTask::DIFFICULTY_MEDIUM,
                $training_tasks
            );
        }
        // Standard Rank 2+
        if($user_rank_num > 1) {
            $daily_tasks[] = DailyTask::generateTask(
                $user_rank_num,
                DailyTask::ACTIVITY_MISSIONS,
                DailyTask::DIFFICULTY_MEDIUM
            );
        }
        // Rank 3+
        if($user_rank_num >= 3) {
            $task = DailyTask::ACTIVITY_BATTLES;
            if(mt_rand(1, 100) <= $pvp_rep + 20) {
                $task = DailyTask::ACTIVITY_PVP;
            }
            $daily_tasks[] = DailyTask::generateTask(
                $user_rank_num,
                $task,
                DailyTask::DIFFICULTY_MEDIUM
            );
        }

        return $daily_tasks;
    }
}
