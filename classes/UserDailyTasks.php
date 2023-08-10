<?php
class UserDailyTasks {
    //const TASK_RESET = 86400;
    const TASK_RESET = 10;

    public System $system;
    public int $user_id;
    public int $user_rank_num;
    public int $last_reset;
    public string $tasks_string;
    public array $tasks;
    public ?array $current_task_types;
    public function __construct($system, $user_id, $user_rank_num) {
        $this->system = $system;
        $this->user_id = $user_id;
        $this->user_rank_num = $user_rank_num;
        $this->current_task_types = null;

        $this->loadTasksFromDb();
    }

    public function progressTask($activity, $amount, $sub_task = false): void {
        /** @var DailyTask $task **/
        foreach($this->tasks as $task) {
            if($task->activity == $activity) {
                $progress_task = true;

                // Check subtask and disable if mismatch
                if($sub_task !== false && $activity != DailyTask::ACTIVITY_MISSIONS && $task->sub_task != $sub_task) {
                    $progress_task = false;
                }
                // Validate mission data
                if($sub_task !== false && $activity == DailyTask::ACTIVITY_MISSIONS && $task->mission_rank > $sub_task) {
                    $progress_task = false;
                }

                if($progress_task) {
                    $task->progress += $amount;
                }
            }
        }
    }
    public function checkTaskCompletion(): ?array
    {
        $data = null;

        /** @var DailyTask $task */
        foreach($this->tasks as $task) {
            if(!$task->complete && $task->progress >= $task->amount) {
                $task->progress = $task->amount;
                $task->complete = true;
                $data['money_gain'] = $task->reward;
                $data['rep_gain'] = $task->rep_reward;
                $data['tasks_completed'][] = $task->name;
            }
        }

        return $data;
    }
    public function hasTaskType($type):bool {
        if(is_null($this->current_task_types)) {
            return false;
        }
        if(is_array($this->current_task_types) && in_array($type, $this->current_task_types)) {
            return true;
        }
        return false;
    }
    public function dbEncodeTasks(): void {
        $this->tasks_string = json_encode($this->tasks);
    }
    public function generateNewTasks($update_db = true): void {
        $this->tasks = DailyTask::generateNewTasks($this->user_rank_num);
        if($update_db) {
            $this->update(true);
        }
    }
    public function loadTasksFromDb(): void {
        $task_result = $this->system->db->query("SELECT `tasks`, `last_reset` FROM `daily_tasks` WHERE `user_id`='{$this->user_id}'");
        if($this->system->db->last_num_rows !== 0) {
            $task_data = $this->system->db->fetch($task_result);

            $this->last_reset = $task_data['last_reset'];

            // Start new tasks
            if(time() - $this->last_reset >= self::TASK_RESET) {
                $this->generateNewTasks();
            }
            // Continue current tasks
            else {
                $this->tasks_string = $task_data['tasks'];
                $dt_arr = json_decode($this->tasks_string, true);
                $this->tasks = array_map(function($dt_data) {
                    return new DailyTask($dt_data);
                }, $dt_arr);
            }

            // Set current tasks
            foreach($this->tasks as $task) {
                if(!$task->complete) {
                    $this->current_task_types[] = $task->activity;
                }
            }
        }
        else {
            $this->last_reset = time();
            $this->generatenewTasks(false);
            $this->dbEncodeTasks();

            $this->system->db->query(
                "INSERT INTO `daily_tasks` (`user_id`, `tasks`, `last_reset`)
                    VALUES ('{$this->user_id}', '{$this->tasks_string}', '{$this->last_reset}')"
            );
        }
    }
    public function update($update_reset_time = false): void {
        $this->dbEncodeTasks();
        $query = "UPDATE `daily_tasks` SET `tasks`='{$this->tasks_string}'";
        if($update_reset_time) {
            $this->last_reset = time();
            $query .= ", `last_reset`='{$this->last_reset}'";
        }
        $query .= " WHERE `user_id`='{$this->user_id}'";
        $this->system->db->query($query);
    }
}