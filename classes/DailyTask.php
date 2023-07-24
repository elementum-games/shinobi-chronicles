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
    const ACTIVITY_TRAINING = 'Training';
    const ACTIVITY_EARN_MONEY = 'Money';

    const DIFFICULTY_EASY = 'Easy';
    const DIFFICULTY_MEDIUM = 'Medium';
    const DIFFICULTY_HARD = 'Hard';

    public static array $possible_task_names = [
        'Zodiac of Solaris',
        'Defend the Past',
        'Call of Grace',
        'The Shattered Corpse',
        'Trap the Fury',
        'The Obsidian Orb',
        'Zodia Clock',
        'Rats of the Frontline',
        'The Grey Quarry',
        'Something Immortal',
        'The Beast in the West',
        'Made for Error',
        'Spare Parts',
        'The Fall of the Orb',
        'Cry of Menace',
        'Breaking Rites',
        'Conjured Moon',
    ];

    public static array $activity_labels = [
        DailyTask::ACTIVITY_PVP => 'PvP Battles',
        DailyTask::ACTIVITY_ARENA => 'Arena Battles',
        DailyTask::ACTIVITY_MISSIONS => 'Missions',
        DailyTask::ACTIVITY_TRAINING => 'Train',
        DailyTask::ACTIVITY_EARN_MONEY => 'Earn',
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
        $prompt = ucwords($this->sub_task) . " " . $this->amount . ' ';

        if($this->activity === DailyTask::ACTIVITY_MISSIONS) {
            $prompt .= Mission::$rank_names[$this->mission_rank] . ' ' . self::$activity_labels[DailyTask::ACTIVITY_MISSIONS];
        }
        else {
            $prompt .= self::$activity_labels[$this->activity];
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

    public static function chooseTaskName(array $used_task_name_keys = []): string {
        $possible_task_names = DailyTask::$possible_task_names;
        foreach($used_task_name_keys as $utnk) {
            unset($possible_task_names[$utnk]);
        }
        if(count($possible_task_names) < 1) {
            $possible_task_names = DailyTask::$possible_task_names;
        }

        $task_name_key = array_rand($possible_task_names, 1);
        return $possible_task_names[$task_name_key];
    }

    public static function getPossibleTasks(int $user_rank): array {
        $possible_task_types = [
            DailyTask::ACTIVITY_PVP => [
                'type' => DailyTask::ACTIVITY_PVP,
                'sub_task' => [DailyTask::SUB_TASK_WIN_FIGHT, DailyTask::SUB_TASK_COMPLETE],
                'min_amount' => 1,
                'max_amount' => 10,
            ],
            DailyTask::ACTIVITY_ARENA => [
                'type' => DailyTask::ACTIVITY_ARENA,
                'sub_task' => [DailyTask::SUB_TASK_WIN_FIGHT],
                'min_amount' => 10,
                'max_amount' => 75,
            ],
            DailyTask::ACTIVITY_MISSIONS => [
                'type' => DailyTask::ACTIVITY_MISSIONS,
                'sub_task' => [DailyTask::SUB_TASK_COMPLETE],
                'min_amount' => 5,
                'max_amount' => 35,
                'mission_rank' => [],
            ],
            DailyTask::ACTIVITY_TRAINING => [
                'type' => DailyTask::ACTIVITY_TRAINING,
                'sub_task' => [self::SUB_TASK_SKILL, self::SUB_TASK_GEN, self::SUB_TASK_JUTSU],
                'min_amount' => 50,
                'max_amount' => 75,
            ],
            DailyTask::ACTIVITY_EARN_MONEY => [
                'type' => DailyTask::ACTIVITY_EARN_MONEY,
                'sub_task' => [self::SUB_TASK_EARN],
                'min_amount' => 100,
                'max_amount' => 250,
            ],
        ];

        $mission_ranks = [Mission::RANK_D, Mission::RANK_C, Mission::RANK_B, Mission::RANK_A, Mission::RANK_S];
        $max_mission_rank = Mission::maxMissionRank($user_rank);

        foreach($mission_ranks as $mission_rank) {
            if($max_mission_rank < $mission_rank) {
                break;
            }
            array_push($possible_task_types[DailyTask::ACTIVITY_MISSIONS]['mission_rank'], $mission_rank);
        }

        return $possible_task_types;
    }

    public static function generateTask(User $user, string $task_activity, array $used_task_name_keys = []): DailyTask {
        // Generate new Daily Tasks if there's never been a new task or if 24hrs have ellapsed since last reset

        $possible_tasks = DailyTask::getPossibleTasks($user->rank_num);
        $task_config = $possible_tasks[$task_activity];

        // Randomly choose a mission type and amount
        $sub_task_key = array_rand($task_config['sub_task'], 1);
        $sub_task = $task_config['sub_task'][$sub_task_key];
        $task_amount = mt_rand($task_config['min_amount'], $task_config['max_amount']);

        $task_name = DailyTask::chooseTaskName($used_task_name_keys);

        $mission_rank = 0;
        if($task_config['type'] == DailyTask::ACTIVITY_MISSIONS) {
            $mission_rank_key = array_rand($task_config['mission_rank'], 1);
            $mission_rank = $task_config['mission_rank'][$mission_rank_key];
        }

        if($task_config['type'] == DailyTask::ACTIVITY_TRAINING) {
            $task_config['min_amount'] *= 1.75;
            $task_config['max_amount'] *= 1.75;
        }

        // Decide the Task difficulty for rewards
        $task_reward = 200 + (pow($user->rank_num, 2) * 150);
        $task_reward = round($task_reward * (mt_rand(90, 110) / 100)); // 20% randomness

        $task_win_multiplier = 1;
        if($task_activity == DailyTask::ACTIVITY_PVP && $sub_task == DailyTask::SUB_TASK_WIN_FIGHT) {
            $task_win_multiplier = 2;
        }

        $mediumTarget = ceil($task_config['max_amount'] * 0.35);
        $hardTarget = ceil($task_config['max_amount'] * 0.70);

        $task_difficulty = DailyTask::DIFFICULTY_EASY;
        $difficulty_multiplier = 1;
        if($task_amount * $task_win_multiplier > $hardTarget) {
            $task_difficulty = DailyTask::DIFFICULTY_HARD;
            $difficulty_multiplier = 3;
        }
        else if($task_amount * $task_win_multiplier > $mediumTarget) {
            $task_difficulty = DailyTask::DIFFICULTY_MEDIUM;
            $difficulty_multiplier = 2;
        }

        // Reputation reward
        $rep_reward = UserReputation::DAILY_TASK_REWARDS[$task_difficulty][$task_config['type']];

        // Override harder missions to not give so many
        switch($mission_rank) {
            case Mission::RANK_S:
                $task_amount = ceil($task_amount * 0.45);
                break;
            case Mission::RANK_A:
                $task_amount = ceil($task_amount * 0.6);
                break;
            case Mission::RANK_B:
                $task_amount = ceil($task_amount * 0.75);
                break;
            case Mission::RANK_C:
                $task_amount = ceil($task_amount * 0.9);
                break;
            case Mission::RANK_D:
                break;
        }

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
     * @param User $user
     * @return DailyTask[]
     */
    public static function generateNewTasks(User $user): array {
        $daily_tasks = [];

        $daily_tasks[] = DailyTask::generateTask($user, DailyTask::ACTIVITY_ARENA);

        // Rank 1 only
        if($user->rank_num == 1) {
            $daily_tasks[] = DailyTask::generateTask($user, DailyTask::ACTIVITY_EARN_MONEY);
        }
        // Rank 1 and 2 only - training tasks
        if($user->rank_num <= 2) {
            $daily_tasks[] = DailyTask::generateTask($user, DailyTask::ACTIVITY_TRAINING);
        }
        // Rank 2+ mission activity
        if($user->rank_num >= 2) {
            $daily_tasks[] = DailyTask::generateTask($user, DailyTask::ACTIVITY_MISSIONS);
        }
        // Rank 3+ pvp
        if($user->rank_num >= 3) {
            $daily_tasks[] = DailyTask::generateTask($user, DailyTask::ACTIVITY_PVP);
        }

        return $daily_tasks;
    }
}
