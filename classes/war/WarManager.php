<?php

class WarManager {
    private System $system;
    private User $user;

    public function __construct(System $system, User $user)
    {
        $this->system = $system;
        $this->user = $user;
    }

    public function getOperationById(int $operation_id): Operation {
        $operation_result = $this->system->db->query("SELECT * FROM `operations` WHERE `operation_id` = {$operation_id} LIMIT 1");
        if ($this->system->db->last_num_rows == 0) {
            throw new RuntimeException("Operation not found");
        }
        $operation_result = $this->system->db->fetch($operation_result);
        $operation = new Operation($this->system, $this->user, $operation_result);
        return $operation;
    }

    public function processOperation(int $operation_id, ?int $status = null)
    {
        $operation = $this->getOperationById($operation_id);
        if (!empty($status)) {
            $operation->status = $status;
        }
        switch ($operation->status) {
            case Operation::OPERATION_ACTIVE:
                $operation->progressActiveOperation();
                break;
            case Operation::OPERATION_COMPLETE:
                $operation->handleCompletion();
                $operation->updateData();
                break;
            case Operation::OPERATION_FAILED:
                $operation->handleFailure();
                $operation->updateData();
                break;
            default:
                throw new RuntimeException("Invalid operation status!");
        }
    }

    public function startOperation(int $operation_type, int $target_id) {
        $target = $this->system->db->query("SELECT * FROM `region_locations`, `regions`.`village`
            INNER JOIN `regions` on `regions`.`region_id` = `region_locations`.`region_id`
            WHERE `id` = {$target_id} LIMIT 1");
        if ($this->system->db->last_num_rows == 0) {
            throw new RuntimeException("Invalid operation target!");
        }
        $target = $this->system->db->fetch($target);
        // must be at target location
        $target_location = new TravelCoords($target['x'], $target['y'], $target['map_id']);
        if ($this->user->location->fetchString() != $target_location->fetchString()) {
            throw new RuntimeException("Invalid operation target!");
        }
        switch ($operation_type) {
            case Operation::OPERATION_INFILTRATE:
                // must be neutral or at war
                if ($this->user->village->relations[$target['village']]->relation_type != "neutral" && $this->user->village->relations[$target['village']]->relation_type != "war") {
                    throw new RuntimeException("Invalid operation target!");
                }
                Operation::createOperation($this->system, $this->user, $target_id, $operation_type, $target['village']);
                break;
            case Operation::OPERATION_REINFORCE:
                // must be owned or ally
                if ($target['village'] != $this->user->village->village_id && $this->user->village->relations[$target['village']]->relation_type != "alliance") {
                    throw new RuntimeException("Invalid operation target!");
                }
                Operation::createOperation($this->system, $this->user, $target_id, $operation_type, $target['village']);
                break;
            case Operation::OPERATION_RAID:
                // must be at war
                if ($this->user->village->relations[$target['village']]->relation_type != "war") {
                    throw new RuntimeException("Invalid operation target!");
                }
                Operation::createOperation($this->system, $this->user, $target_id, $operation_type, $target['village']);
                break;
            default:
                throw new RuntimeException("Invalid operation type!");
        }
    }
}