<?php

require_once __DIR__ . "/Operation.php";

class WarManager {
    const BASE_TOWN_RESOURCE_PRODUCTION = 25;
    const BASE_CASTLE_RESOURCE_PRODUCTION = 50;
    const VILLAGE_BASE_RESOURCE_PRODUCTION = 75;
    const BASE_CARAVAN_TIME_MS = 300000; // 5 minute travel time
    const CARAVAN_TIMER_HOURS = 6; // 24m average caravan spawn timer, so 19 minute average downtime
    const BASE_VILLAGE_REGEN_PER_MINUTE = 40; // 2400/hour
    const BASE_CASTLE_REGEN_PER_MINUTE = 45; // 2700/hour
    const VILLAGE_REGEN_SHARE_PERCENT = 100; // 2 villages + base = 7500/hour max
    const BASE_VILLAGE_HEALTH = 5000;
    const BASE_CASTLE_HEALTH = 15000;
    const BASE_VILLAGE_DEFENSE = 50;
    const BASE_CASTLE_DEFENSE = 75;

    // region_regen_cron.php must run on matching cadence to this interval, if you change this value, change the cron job config to run region_regen_cron.php at whatever the new value is
    const REGEN_INTERVAL_MINUTES = 5;

    const RESOURCE_MATERIALS = 1;
    const RESOURCE_FOOD = 2;
    const RESOURCE_WEALTH = 3;

    const RESOURCE_NAMES = [
        self::RESOURCE_MATERIALS => 'materials',
        self::RESOURCE_FOOD => 'food',
        self::RESOURCE_WEALTH => 'wealth',
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
        1 => 8,
        2 => 17,
        3 => 130,
        4 => 17,
    ];
    const PATROL_CHANCE = [
        1 => 50,
        2 => 35,
        3 => 15,
        4 => 0,
    ];
    const PATROL_RESPAWN_TIME = 600;
    const BASE_LOOT_CAPACITY = 50;
    const MAX_PATROL_TIER = 3;
    const YEN_PER_RESOURCE = 10;

    private System $system;
    private User $user;

    /** @var VillageRelation[][] */
    public array $village_relations_by_village_ids;

    public function __construct(System $system, User $user)
    {
        $this->system = $system;
        $this->user = $user;

        $this->village_relations_by_village_ids = VillageManager::getAllRelationsByVillageIds($system);
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
        // check operation target valid
        switch ($operation->status) {
            case Operation::OPERATION_ACTIVE:
                if (!$this->checkOperationValid($operation)) {
                    $message = "Operation no longer valid!";
                    $this->cancelOperation();
                    break;
                }
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
        if ($this->user->rank_num <= 2) {
            throw new RuntimeException("Invalid rank!");
        }
        if ($this->user->battle_id > 0) {
            throw new RuntimeException("You are currently in battle!");
        }
        if ($operation_type != Operation::OPERATION_LOOT) {
            $target = $this->system->db->query("SELECT `region_locations`.*, COALESCE(`region_locations`.`occupying_village_id`, `regions`.`village`) as `village`, `regions`.`village` as `original_village` 
            FROM `region_locations`
            INNER JOIN `regions` on `regions`.`region_id` = `region_locations`.`region_id`
            WHERE `id` = {$target_id} LIMIT 1");
            if ($this->system->db->last_num_rows == 0) {
                throw new RuntimeException("Invalid operation target!");
            }
            $target = $this->system->db->fetch($target);
            // must be at target location
            $target_location = new TravelCoords($target['x'], $target['y'], $target['map_id']);
            if ($this->user->location->toString() != $target_location->toString()) {
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
            case Operation::OPERATION_LOOT_TOWN:
                // must be occupied by self
                if (empty($target['occupying_village_id']) || $target['occupying_village_id'] != $this->user->village->village_id) {
                    throw new RuntimeException("Invalid operation target!");
                }
                Operation::beginOperation($this->system, $this->user, $target_id, $operation_type, $target['original_village']);
                break;
            default:
                throw new RuntimeException("Invalid operation type!");
        }
    }

    /**
     * @throws RuntimeException
     */
    public function checkOperationValid(Operation $operation): bool {
        if ($this->user->battle_id > 0) {
            return false;
        }
        if ($operation->type != Operation::OPERATION_LOOT) {
            $target = $this->system->db->query("SELECT `region_locations`.*, COALESCE(`region_locations`.`occupying_village_id`, `regions`.`village`) as `village`, `regions`.`village` as `original_village`
            FROM `region_locations`
            INNER JOIN `regions` on `regions`.`region_id` = `region_locations`.`region_id`
            WHERE `id` = {$operation->target_id} LIMIT 1");
            if ($this->system->db->last_num_rows == 0) {
                return false;
            }
            $target = $this->system->db->fetch($target);
            // must be at target location
            $target_location = new TravelCoords($target['x'], $target['y'], $target['map_id']);
            if ($this->user->location->toString() != $target_location->toString()) {
                return false;
            }
        }
        switch ($operation->type) {
            case Operation::OPERATION_INFILTRATE:
                // must be neutral or at war
                if ($this->user->village->isAlly($target['village'])) {
                    return false;
                }
                break;
            case Operation::OPERATION_REINFORCE:
                // must be owned or ally
                if ($target['village'] != $this->user->village->village_id && !$this->user->village->isAlly($target['village'])) {
                    return false;
                }
                break;
            case Operation::OPERATION_RAID:
                // must be at war
                if (!$this->user->village->isEnemy($target['village'])) {
                    return false;
                }
                break;
            case Operation::OPERATION_LOOT:
                // must be neutral or at war
                if ($this->user->village->isAlly($operation->target_village)) {
                    return false;
                }
                break;
            case OPERATION::OPERATION_LOOT_TOWN:
                // must be occupied by self
                if (empty($target['occupying_village_id']) || $target['occupying_village_id'] != $this->user->village->village_id) {
                    return false;
                }
                break;
            default:
                return false;
        }
        return true;
    }

    public function cancelOperation() {
        Operation::cancelOperation($this->system, $this->user);
    }

    /**
     * @return array
     */
    public function getValidOperations(bool $for_display = false): array {
        $valid_operations = [];

        // exit if war disabled
        if (!$this->system->war_enabled) {
            return $valid_operations;
        }
        // exit if rank below Chuunin
        if ($this->user->rank_num <= 2) {
            return $valid_operations;
        }

        // get region location where location = player location
        $target_location = $this->system->db->query("SELECT `region_locations`.*, COALESCE(`region_locations`.`occupying_village_id`, `regions`.`village`) as `village`, `regions`.`village` as `original_village`
            FROM `region_locations`
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
                if ($for_display) {
                    $health_gain = floor($this->user->level / 2);
                    $valid_operations[Operation::OPERATION_REINFORCE] .= "<br><span class='reinforce_button_text'>{$health_gain} health</span>";
                }
                if (!empty($target_location['occupying_village_id'])) {
                    $valid_operations[Operation::OPERATION_LOOT_TOWN] = System::unSlug(Operation::OPERATION_TYPE[Operation::OPERATION_LOOT_TOWN]);
                }
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
                        if ($for_display) {
                            $health_gain = floor($this->user->level / 2);
                            $valid_operations[Operation::OPERATION_REINFORCE] .= "<br><span class='reinforce_button_text'>{$health_gain} health</span>";
                        }
                        break;
                    case VillageRelation::RELATION_WAR:
                        $valid_operations = [
                            Operation::OPERATION_INFILTRATE => System::unSlug(Operation::OPERATION_TYPE[Operation::OPERATION_INFILTRATE]),
                            Operation::OPERATION_RAID => System::unSlug(Operation::OPERATION_TYPE[Operation::OPERATION_RAID]),
                        ];
                        if ($for_display) {
                            $defense_reduction = min($target_location['defense'] / 100, 1);
                            $damage = intval($this->user->level * (1 - $defense_reduction));
                            $damage = max($damage, 0);
                            $valid_operations[Operation::OPERATION_RAID] .= "<br><span class='raid_button_text'>{$damage} damage</span>";
                        }
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
        $region_locations = $this->system->db->query("SELECT * FROM `region_locations`");
        $region_locations = $this->system->db->fetch_all($region_locations);
        foreach ($caravans as $caravan) {
            $patrol = new Patrol($caravan, "caravan");
            $patrol->setLocation($this->system, $region_locations);
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
        if ($this->user->location->toString() != $patrol_location->toString()) {
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
        $yen_gain = 0;
        foreach ($loot_gained as $resource_id => $count) {
            if ($first) {
                $message .= "Deposited";
                $first = false;
            } else {
                $message .= ",";
            }
            WarLogManager::logAction($this->system, $this->user, $count, WarLogManager::WAR_LOG_RESOURCES_CLAIMED, $this->user->village->village_id);
            $message .= " " . $count . " " . System::unSlug(WarManager::RESOURCE_NAMES[$resource_id]);
            $this->user->village->addResource($resource_id, $count);
            $yen_gain += $count * self::YEN_PER_RESOURCE;
        }
        $this->user->village->updateResources();
        $message .= "!";
        $message .= "\nGained �{$yen_gain}!";
        $this->user->addMoney($yen_gain, "Resource");
        // update loot table
        $time = time();
        $this->system->db->query("UPDATE `loot` SET `claimed_village_id` = {$this->user->village->village_id}, `claimed_time` = {$time} WHERE `user_id` = {$this->user->user_id} AND `claimed_village_id` IS NULL AND `battle_id` IS NULL");
        return $message;
    }

    /**
     * @return string
     */
    public function handleWinAgainstPatrol(int $patrol_id): string {
        $message = '';
        $time = time();
        $patrol_result = $this->system->db->query("SELECT * FROM `patrols` WHERE `id` = {$patrol_id} AND `start_time` < {$time}");
        $patrol_result = $this->system->db->fetch($patrol_result);
        if ($this->system->db->last_num_rows == 0) {
            return $message;
        }
        WarLogManager::logAction($this->system, $this->user, 1, WarLogManager::WAR_LOG_PATROLS_DEFEATED, $patrol_result['village_id']);
        $x = mt_rand(1, 100);
        if ($x <= self::PATROL_CHANCE[3]) {
            $name = self::PATROL_NAMES[min(3 + $this->user->village->policy->patrol_tier, self::MAX_PATROL_TIER)];
            $ai_id = self::PATROL_AI[min(3 + $this->user->village->policy->patrol_tier, self::MAX_PATROL_TIER)];
            $tier = min(3 + $this->user->village->policy->patrol_tier, self::MAX_PATROL_TIER);
        } else if ($x <= self::PATROL_CHANCE[3] + self::PATROL_CHANCE[2]) {
            $name = self::PATROL_NAMES[min(2 + $this->user->village->policy->patrol_tier, self::MAX_PATROL_TIER)];
            $ai_id = self::PATROL_AI[min(2 + $this->user->village->policy->patrol_tier, self::MAX_PATROL_TIER)];
            $tier = min(2 + $this->user->village->policy->patrol_tier, self::MAX_PATROL_TIER);
        } else {
            $name = self::PATROL_NAMES[min(1 + $this->user->village->policy->patrol_tier, self::MAX_PATROL_TIER)];
            $ai_id = self::PATROL_AI[min(1 + $this->user->village->policy->patrol_tier, self::MAX_PATROL_TIER)];
            $tier = min(1 + $this->user->village->policy->patrol_tier, self::MAX_PATROL_TIER);
        }
        if ($this->system->isDevEnvironment()) {
            $ai_id = 17;
        }
        $respawn_time = time() + round(self::PATROL_RESPAWN_TIME * (100 / (100 + $this->user->village->policy->patrol_respawn)), 1);
        $this->system->db->query("UPDATE `patrols` SET `start_time` = {$respawn_time}, `name` = '{$name}', `ai_id` = {$ai_id}, `tier` = {$tier} WHERE `id` = {$patrol_id}");
        // decrease region location defense in region that matches patrol's village
        $location_result = $this->system->db->query("SELECT `region_locations`.*, COALESCE(`region_locations`.`occupying_village_id`, `regions`.`village`) as `village`
            FROM `region_locations`
            INNER JOIN `regions` on `regions`.`region_id` = `region_locations`.`region_id`
            WHERE `region_locations`.`region_id` = {$patrol_result['region_id']}
        ");
        $location_result = $this->system->db->fetch_all($location_result);
        foreach ($location_result as $location) {
            if ($location['defense'] > 0 && $location['village'] == $patrol_result['village_id']) {
                $location['defense']--;
                $this->system->db->query("UPDATE `region_locations` SET `defense` = {$location['defense']} WHERE `id` = {$location['id']}");
            }
        }
        $message = 'Enemy region Defense decreased by 1.<br>';
        return $message;
    }

    public function handleLossAgainstPatrol(int $patrol_id): string
    {
        $message = '';
        // get patrol
        $result = $this->system->db->query("SELECT * FROM `patrols` WHERE `id` = {$patrol_id} LIMIT 1");
        $patrol = $this->system->db->fetch($result);
        $village = VillageManager::getVillageByID($this->system, $patrol['village_id']);
        // get loot
        $loot_result = $this->system->db->query("SELECT * FROM `loot` WHERE `user_id` = {$this->user->user_id} AND `claimed_village_id` IS NULL");
        $loot_result = $this->system->db->fetch_all($loot_result);
        if ($this->system->db->last_num_rows > 0) {
            $message .= "<br>Your loot was taken by the enemy patrol and returned to their village.";
        }
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
        foreach ($loot_gained as $resource_id => $count) {
            $village->addResource($resource_id, $count);
        }
        $village->updateResources();
        // update loot table
        $time = time();
        $this->system->db->query("UPDATE `loot` SET `claimed_village_id` = {$village->village_id}, `claimed_time` = {$time}, `battle_id` = NULL, `user_id` = 0 WHERE `user_id` = {$this->user->user_id} AND `claimed_village_id` IS NULL");
        return $message;
    }

    /**
     * Returns raid targets the player might want to attack or defend.
     * * Enemy towns/castles that allies are raiding
     * * Ally towns/castles that enemies are raiding
     *
     * @param System $system
     * @param User   $player
     * @return RaidTargetDto[]
     */
    public static function getPlayerAttackOrDefendRaidTargets(System $system, User $player): array {
        // only get recently updated raids, prevent something like starting a raid and logging out = perpetual notif
        $num_raid_cycles = 100 / Operation::BASE_OPERATION_INTERVAL_PROGRESS_PERCENT;
        $max_raid_duration_ms = $num_raid_cycles * Operation::BASE_OPERATION_INTERVAL_SECONDS * 1000;
        $oldest_active_raid_time = (microtime(true) * 1000) - $max_raid_duration_ms;

        $result = $system->db->query("SELECT
            `operations`.`user_village` as `attacking_user_village`, 
            `operations`.`target_id`,
            `operations`.`target_village`,
            `region_locations`.`x`, 
            `region_locations`.`y`, 
            `region_locations`.`map_id`, 
            `region_locations`.`name` 
            FROM `operations`
            INNER JOIN `region_locations` ON `region_locations`.`id` = `operations`.`target_id`
            AND `user_id` != {$player->user_id}
            AND `last_update_ms` > {$oldest_active_raid_time}
            AND `status` = " . Operation::OPERATION_ACTIVE . " 
            AND `operations`.`type` = " . Operation::OPERATION_RAID . "
            GROUP BY `operations`.`target_id`, `operations`.`target_village`, `attacking_user_village`");
        $raw_raid_targets = $system->db->fetch_all($result);

        $raid_targets = [];
        foreach($raw_raid_targets as $target) {
            if(!isset($raid_targets[$target['target_id']])) {
                // if ally being raided
                if ($player->village->isAlly($target['target_village'])) {
                    $raid_targets[$target['target_id']] = new RaidTargetDto(
                        name: $target['name'],
                        location: new TravelCoords(x: $target['x'], y: $target['y'], map_id: $target['map_id']),
                        is_ally_location: true,
                    );
                } 
                // if ally raiding
                else if ($player->village->isAlly($target['attacking_user_village'])) {
                    $raid_targets[$target['target_id']] = new RaidTargetDto(
                        name: $target['name'],
                        location: new TravelCoords(x: $target['x'], y: $target['y'], map_id: $target['map_id']),
                        is_ally_location: false,
                    );
                }
            }
        }

        return $raid_targets;
    }

    public function villagesAreAllies(int $village1_id, int $village2_id): bool {
        return $village1_id == $village2_id ||
            $this->village_relations_by_village_ids[$village1_id][$village2_id]->relation_type == VillageRelation::RELATION_ALLIANCE;
    }
}

class RaidTargetDto {
    public function __construct(
        public string $name,
        public TravelCoords $location,
        public bool $is_ally_location,
    ) {}
}