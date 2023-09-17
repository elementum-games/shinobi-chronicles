<?php

class WarManager {
    private System $system;
    private User $user;

    public function __construct(System $system, User $user)
    {
        $this->system = $system;
        $this->user = $user;
    }

    /**
     * @throws RuntimeException
     */
    public function getOperationById(int $operation_id): Operation {
        $operation_result = $this->system->db->query("SELECT * FROM `operations` WHERE `operation_id` = {$operation_id} LIMIT 1");
        if ($this->system->db->last_num_rows == 0) {
            throw new RuntimeException("Operation not found");
        }
        $operation_result = $this->system->db->fetch($operation_result);
        $operation = new Operation($this->system, $this->user, $operation_result);
        return $operation;
    }

    /**
     * @throws RuntimeException
     */
    public function processOperation(int $operation_id, ?int $status = null): string
    {
        $message = '';
        $operation = $this->getOperationById($operation_id);
        if (!empty($status)) {
            $operation->status = $status;
        }
        switch ($operation->status) {
            case Operation::OPERATION_ACTIVE:
                $message = $operation->progressActiveOperation();
                break;
            case Operation::OPERATION_COMPLETE:
                $message = $operation->handleCompletion();
                $operation->updateData();
                break;
            case Operation::OPERATION_FAILED:
                $message = $operation->handleFailure();
                $operation->updateData();
                break;
            default:
                throw new RuntimeException("Invalid operation status!");
        }
        return $message;
    }

    /**
     * @throws RuntimeException
     */
    public function beginOperation(int $operation_type, int $target_id) {
        $target = $this->system->db->query("SELECT `region_locations`.*, `regions`.`village` FROM `region_locations`
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
                if ($this->user->village->relations[$target['village']]->relation_type != VillageRelation::RELATION_NEUTRAL && $this->user->village->relations[$target['village']]->relation_type != VillageRelation::RELATION_WAR) {
                    throw new RuntimeException("Invalid operation target!");
                }
                $operation_id = Operation::beginOperation($this->system, $this->user, $target_id, $operation_type, $target['village']);
                break;
            case Operation::OPERATION_REINFORCE:
                // must be owned or ally
                if ($target['village'] != $this->user->village->village_id && $this->user->village->relations[$target['village']]->relation_type != VillageRelation::RELATION_ALLIANCE) {
                    throw new RuntimeException("Invalid operation target!");
                }
                $operation_id = Operation::beginOperation($this->system, $this->user, $target_id, $operation_type, $target['village']);
                break;
            case Operation::OPERATION_RAID:
                // must be at war
                if ($this->user->village->relations[$target['village']]->relation_type != VillageRelation::RELATION_WAR) {
                    throw new RuntimeException("Invalid operation target!");
                }
                $operation_id = Operation::beginOperation($this->system, $this->user, $target_id, $operation_type, $target['village']);
                break;
            default:
                throw new RuntimeException("Invalid operation type!");
        }
    }

    public function cancelOperation() {
        Operation::cancelOperation($this->system, $this->user);
    }

    /**
     * @return array
     */
    public function getValidOperations(): array {
        $valid_operations = [];

        // exit if war disabled
        if (!$this->system->war_enabled) {
            return $valid_operations;
        }

        // get region location where location = player location
        $target = $this->system->db->query("SELECT `region_locations`.*, `regions`.`village` FROM `region_locations`
            INNER JOIN `regions` on `regions`.`region_id` = `region_locations`.`region_id`
            WHERE `x` = {$this->user->location->x}
            AND `y` = {$this->user->location->y}
            AND `map_id` = {$this->user->location->map_id} LIMIT 1");
        // if no match, no valid operations
        if ($this->system->db->last_num_rows == 0) {
            return $valid_operations;
        }
        $target = $this->system->db->fetch($target);
        if ($target['village'] == $this->user->village->village_id) {
            $valid_operations = [
                Operation::OPERATION_REINFORCE => System::unSlug(Operation::OPERATION_TYPE[Operation::OPERATION_REINFORCE]),
            ];
            return $valid_operations;
        }
        // check each operation type, return array
        switch ($this->user->village->relations[$target['village']]->relation_type) {
            case VillageRelation::RELATION_NEUTRAL:
                $valid_operations = [
                    Operation::OPERATION_INFILTRATE => System::unSlug(Operation::OPERATION_TYPE[Operation::OPERATION_INFILTRATE]),
                ];
                break;
            case VillageRelation::RELATION_ALLIANCE:
                $valid_operations = [
                    Operation::OPERATION_REINFORCE => System::unSlug(Operation::OPERATION_TYPE[Operation::OPERATION_REINFORCE])
                ];
                break;
            case VillageRelation::RELATION_WAR:
                $valid_operations = [
                    Operation::OPERATION_INFILTRATE => System::unSlug(Operation::OPERATION_TYPE[Operation::OPERATION_INFILTRATE]),
                    Operation::OPERATION_RAID => System::unSlug(Operation::OPERATION_TYPE[Operation::OPERATION_RAID]),
                ];
                break;
            case 'default':
                return $valid_operations;
        }
        return $valid_operations;
    }

    function checkBeginPatrolBattle(Patrol $patrol) {
        $patrol_location = new TravelCoords($patrol->current_x, $patrol->current_y, $patrol->map_id);
        // if already in battle
        if ($this->user->battle_id) {
            return;
        }
        // if patrol non-hostile
        if ($this->user->village->village_id == $patrol->village_id || $this->user->village->relations[$patrol->village_id]->relation_type == VillageRelation::RELATION_ALLIANCE) {
            return;
        }
        // if not at same location
        if ($this->user->location->fetchString() != $patrol_location->fetchString()) {
            return;
        }
        // if no AI set
        if (empty($patrol->ai_id)) {
            return;
        }
        $ai = $this->system->db->query("SELECT `ai_id` FROM `ai_opponents` WHERE `ai_id` = {$patrol->ai_id} LIMIT 1");
        if ($this->system->db->last_num_rows == 0) {
            return;
        }
        $ai = new NPC($this->system, $patrol->ai_id);
        $ai->loadData();
        $ai->health = $ai->max_health;
        if ($this->system->USE_NEW_BATTLES) {
            BattleV2::start($this->system, $this->user, $ai, BattleV2::TYPE_AI_WAR);
        } else {
            Battle::start($this->system, $this->user, $ai, Battle::TYPE_AI_WAR);
        }
    }
}