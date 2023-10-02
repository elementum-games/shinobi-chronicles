<?php

class WarManager {

    const BASE_RESOURCE_PRODUCTION = 25; // 25/hour, 600/village/day, 1800/region/day, 7200/faction/day (assuming 4 regions)
    const BASE_CARAVAN_TIME_MS = 300000; // 5 minute travel time
    const CARAVAN_TIMER_HOURS = 6; // 24m average caravan spawn timer, so 19 minute average downtime
    const BASE_VILLAGE_REGEN = 1250;
    const BASE_CASTLE_REGEN = 2500;
    const VILLAGE_REGEN_SHARE_PERCENT = 100; // 2 villages + base = 5000/hour max
    const BASE_VILLAGE_HEALTH = 5000;
    const BASE_CASTLE_HEALTH = 15000;
    const BASE_VILLAGE_DEFENSE = 50;
    const BASE_CASTLE_DEFENSE = 75;
    const RESOURCE_NAMES = [
        1 => 'materials',
        2 => 'food',
        3 => 'wealth',
        /*4 => 'adamantine',
        5 => 'quicksilver',
        6 => 'elderwood',
        7 => 'iron_sand',
        8 => 'obsidian',*/
    ];
    const PATROL_NAMES = [
        1 => "Patrol",
        2 => "Veteran Patrol",
        3 => "Elite Patrol",
        4 => "War Hero",
    ];
    const PATROL_AI = [
        1 => 17,
        2 => 17,
        3 => 17,
        4 => 17,
    ];
    const PATROL_CHANCE = [
        1 => 50,
        2 => 35,
        3 => 15,
        4 => 0,
    ];
    const PATROL_RESPAWN_TIME = 600;

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
                $operation->handleFailure();
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
    public function beginOperation(int $operation_type, int $target_id, Patrol $patrol = null) {
        if ($this->user->battle_id > 0) {
            throw new RuntimeException("You are currently in battle!");
        }
        if ($operation_type != Operation::OPERATION_LOOT) {
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
        }

        switch ($operation_type) {
            case Operation::OPERATION_INFILTRATE:
                // must be neutral or at war
                if ($this->user->village->relations[$target['village']]->relation_type != VillageRelation::RELATION_NEUTRAL && $this->user->village->relations[$target['village']]->relation_type != VillageRelation::RELATION_WAR) {
                    throw new RuntimeException("Invalid operation target!");
                }
                Operation::beginOperation($this->system, $this->user, $target_id, $operation_type, $target['village']);
                break;
            case Operation::OPERATION_REINFORCE:
                // must be owned or ally
                if ($target['village'] != $this->user->village->village_id && $this->user->village->relations[$target['village']]->relation_type != VillageRelation::RELATION_ALLIANCE) {
                    throw new RuntimeException("Invalid operation target!");
                }
                Operation::beginOperation($this->system, $this->user, $target_id, $operation_type, $target['village']);
                break;
            case Operation::OPERATION_RAID:
                // must be at war
                if ($this->user->village->relations[$target['village']]->relation_type != VillageRelation::RELATION_WAR) {
                    throw new RuntimeException("Invalid operation target!");
                }
                Operation::beginOperation($this->system, $this->user, $target_id, $operation_type, $target['village']);
                break;
            case Operation::OPERATION_LOOT:
                // must be neutral or at war
                if ($this->user->village->relations[$patrol->village_id]->relation_type != VillageRelation::RELATION_NEUTRAL && $this->user->village->relations[$patrol->village_id]->relation_type != VillageRelation::RELATION_WAR) {
                    throw new RuntimeException("Invalid operation target!");
                }
                Operation::beginOperation($this->system, $this->user, $patrol->id, $operation_type, $patrol->village_id);
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
        $target_location = $this->system->db->query("SELECT `region_locations`.*, `regions`.`village` FROM `region_locations`
            INNER JOIN `regions` on `regions`.`region_id` = `region_locations`.`region_id`
            WHERE `x` = {$this->user->location->x}
            AND `y` = {$this->user->location->y}
            AND `map_id` = {$this->user->location->map_id} LIMIT 1");
        if ($this->system->db->last_num_rows > 0) {
            $target_location = $this->system->db->fetch($target_location);
            // check each operation type, return array
            if ($target_location['village'] == $this->user->village->village_id) {
                $valid_operations = [
                    Operation::OPERATION_REINFORCE => System::unSlug(Operation::OPERATION_TYPE[Operation::OPERATION_REINFORCE]),
                ];
            } else {
                switch ($this->user->village->relations[$target_location['village']]->relation_type) {
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
                        break;
                }
            }
        }

        // get active caravans
        $time = time();
        $caravans = $this->system->db->query("SELECT * FROM `caravans` where `start_time` < {$time} && `village_id` != {$this->user->village->village_id}");
        $caravans = $this->system->db->fetch_all($caravans);
        foreach ($caravans as $caravan) {
            $patrol = new Patrol($caravan, "caravan");
            $patrol->setLocation($this->system);
            $patrol->setAlignment($this->user);
            if ($this->user->location->distanceDifference(new TravelCoords($patrol->current_x, $patrol->current_y, $patrol->map_id)) == 0 && $patrol->alignment != "Ally") {
                $valid_operations = [
                    Operation::OPERATION_LOOT => System::unSlug(Operation::OPERATION_TYPE[Operation::OPERATION_LOOT]),
                ];
            }
        }

        return $valid_operations;
    }

    function tryBeginPatrolBattle(Patrol $patrol) {
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
        if ($this->user->operation > 0) {
            $this->cancelOperation();
        }
        $ai = new NPC($this->system, $patrol->ai_id);
        $ai->loadData();
        $ai->health = $ai->max_health;
        if ($this->system->USE_NEW_BATTLES) {
            BattleV2::start($this->system, $this->user, $ai, BattleV2::TYPE_AI_WAR, $patrol->id);
        } else {
            Battle::start($this->system, $this->user, $ai, Battle::TYPE_AI_WAR, $patrol->id);
        }
    }

    /**
     * @throws RuntimeException
     */
    public function processLoot(): string
    {
        $message = '';
        // get loot
        $loot_result = $this->system->db->query("SELECT * FROM `loot` WHERE `user_id` = {$this->user->user_id} AND `claimed_village_id` IS NULL AND `battle_id` IS NULL");
        $loot_result = $this->system->db->fetch_all($loot_result);
        $loot_gained = [];
        foreach ($loot_result as $loot) {
            // count loot totals
            if (empty($loot_gained[$loot['resource_id']])) {
                $loot_gained[$loot['resource_id']] = 1;
            } else {
                $loot_gained[$loot['resource_id']] += 1;
            }
        }
        // update village resources
        $first = true;
        foreach ($loot_gained as $resource_id => $count) {
            if ($first) {
                $message .= "Claimed";
                $first = false;
            } else {
                $message .= ",";
            }
            $message .= " " . $count . " " . System::unSlug(WarManager::RESOURCE_NAMES[$resource_id]);
            $this->user->village->addResource($resource_id, $count);
        }
        $this->user->village->updateResources();
        $message .= "!";
        // update loot table
        $time = time();
        $this->system->db->query("UPDATE `loot` SET `claimed_village_id` = {$this->user->village->village_id}, `claimed_time` = {$time} WHERE `user_id` = {$this->user->user_id} AND `claimed_village_id` IS NULL AND `battle_id` IS NULL");
        return $message;
    }

    public function handlePatrolDefeat(int $patrol_id) {
        $x = mt_rand(1, 100);
        if ($x <= self::PATROL_CHANCE[3]) {
            $name = self::PATROL_NAMES[3];
            $ai_id = self::PATROL_AI[3];
            $tier = 3;
        } else if ($x <= self::PATROL_CHANCE[3] + self::PATROL_CHANCE[2]) {
            $name = self::PATROL_NAMES[2];
            $ai_id = self::PATROL_AI[2];
            $tier = 2;
        } else {
            $name = self::PATROL_NAMES[1];
            $ai_id = self::PATROL_AI[1];
            $tier = 1;
        }
        $respawn_time = time() + self::PATROL_RESPAWN_TIME;
        $this->system->db->query("UPDATE `patrols` SET `start_time` = {$respawn_time}, `name` = '{$name}', `ai_id` = {$ai_id}, `tier` = {$tier} WHERE `id` = {$patrol_id}");
    }
}