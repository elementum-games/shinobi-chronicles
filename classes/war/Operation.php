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

    const OPERATION_ACTIVE = 1;
    const OPERATION_FAILED = 2;
    const OPERATION_COMPLETE = 3;

    const OPERATION_STATUS = [
        self::OPERATION_ACTIVE => 'active',
        self::OPERATION_FAILED => 'failed',
        self::OPERATION_COMPLETE => 'complete',
    ];

    const BASE_OPERATION_SPEED = 5; // progress per interval
    const BASE_OPERATION_INTERVAL = 10000; // time per interval
    /* 100 / 5 * 10000ms = 3:20 */

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

    public function __construct(System $system, User $user, array $operation_data)
    {
        foreach ($operation_data as $key => $value) {
            $this->$key = $value;
        }
        $this->system = $system;
        $this->user = $user;
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
            WHERE operation_id = '{$this->operation_id}')";
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

    public function handleFailure() {
        if ($this->status != self::OPERATION_FAILED) {
            throw new RuntimeException("Invalid operation status!");
        }
        $this->user->operation = 0;
    }
}