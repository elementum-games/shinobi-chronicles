<?php

class Operation
{
    const OPERATION_INFILTRATE = 1;
    const OPERATION_REINFORCE = 2;
    const OPERATION_RAID = 3;

    const OPERATION_TYPE = [
        self::OPERATION_INFILTRATE => "infiltrate",
        self::OPERATION_REINFORCE => "reinforce",
        self::OPERATION_RAID => "raid",
    ];

    const OPERATION_TYPE_DESCRIPTOR = [
        self::OPERATION_INFILTRATE => "infiltrating",
        self::OPERATION_REINFORCE => "reinforcing",
        self::OPERATION_RAID => "raiding",
    ];

    const OPERATION_ACTIVE = 1;
    const OPERATION_FAILED = 2;
    const OPERATION_COMPLETE = 3;

    const OPERATION_STATUS = [
        self::OPERATION_ACTIVE => 'active',
        self::OPERATION_FAILED => 'failed',
        self::OPERATION_COMPLETE => 'complete',
    ];

    const BASE_OPERATION_SPEED = 5; // progress per interval
    const BASE_OPERATION_INTERVAL = 10; // time per interval
    /* 100 / 5 * 10s = 3:20 */

    private System $system;
    private User $user;

    public int $operation_id;
    public int $user_id;
    public int $location_id;
    public int $type;
    public int $progress;
    public int $status;
    public int $target_village;
    public int $user_village;
    public int $last_update;
    public int $interval_progress = 0;

    public function __construct(System $system, User $user, array $operation_data)
    {
        foreach ($operation_data as $key => $value) {
            $this->$key = $value;
        }
        $this->system = $system;
        $this->user = $user;
        $this->interval_progress = ((time() - $this->last_update) / self::BASE_OPERATION_INTERVAL) * 100;
    }

    public function updateData() {
        $this->last_update = time();
        $query = "UPDATE `operations`
            SET `user_id` = '{$this->user_id}',
                `location_id` = '{$this->location_id}',
                `type` = '{$this->type}',
                `progress` = '{$this->progress}',
                `status` = '{$this->status}',
                `target_village` = '{$this->target_village}',
                `user_village` = '{$this->user_village}',
                `last_update` = '{$this->last_update}'
            WHERE `operation_id` = '{$this->operation_id}'";
        $this->system->db->query($query);
    }

    public function progressActiveOperation()
    {
        // only progress if active and interval time has passed
        if ($this->status == self::OPERATION_ACTIVE && time() > $this->last_update + Operation::BASE_OPERATION_INTERVAL) {
            $this->progress += self::BASE_OPERATION_SPEED;
            // if progress reaches 100, operation is complete
            if ($this->progress >= 100) {
                $this->progress = 100;
                $this->status = self::OPERATION_COMPLETE;
                // handle completion
                $this->handleCompletion();
            }
            // update operation data
            $this->updateData();
        }
    }

    /**
     * @throws RuntimeException
     */
    public function handleCompletion() {
        if ($this->status != self::OPERATION_COMPLETE) {
            throw new RuntimeException("Invalid operation status!");
        }
        switch ($this->type) {
            case self::OPERATION_INFILTRATE:
                break;
            case self::OPERATION_REINFORCE:
                break;
            case self::OPERATION_RAID:
                break;
        }
        $this->user->operation = 0;
    }

    /**
     * @throws RuntimeException
     */
    public function handleFailure() {
        if ($this->status != self::OPERATION_FAILED) {
            throw new RuntimeException("Invalid operation status!");
        }
        $this->user->operation = 0;
    }

    /**
     * @throws RuntimeException
     */
    public static function beginOperation(System $system, User $user, int $location_id, int $type, int $target_village): int {
        $time = time();
        $system->db->query("INSERT INTO `operations` (`user_id`, `location_id`, `type`, `progress`, `status`, `target_village`, `user_village`, `last_update`)
            VALUES ({$user->user_id}, {$location_id}, {$type}, 0, " . self::OPERATION_ACTIVE . ", {$target_village}, {$user->village->village_id}, {$time})
        ");
        $operation_id = $system->db->last_insert_id;
        $user->operation = $operation_id;
        return $operation_id;
    }

    /**
     * @throws RuntimeException
     */
    public static function cancelOperation(System $system, User $user) {
        $user->operation = 0;
        $system->db->query("UPDATE `operations` set `status` = " . self::OPERATION_FAILED . " WHERE `operation_id` = {$user->operation}");
        if ($system->db->last_num_rows == 0) {
            throw new RuntimeException("Operation not found!");
        }
    }
}