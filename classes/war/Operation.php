<?php

class Operation {
    const OPERATION_INFILTRATE = 1;
    const OPERATION_REINFORCE = 2;
    const OPERATION_RAID = 3;
    const OPERATION_LOOT = 4;
    const OPERATION_LOOT_TOWN = 5;

    const OPERATION_TYPE = [
        self::OPERATION_INFILTRATE => "infiltrate",
        self::OPERATION_REINFORCE => "reinforce",
        self::OPERATION_RAID => "raid",
        self::OPERATION_LOOT => "loot",
        self::OPERATION_LOOT_TOWN => "loot_town",
    ];

    const OPERATION_TYPE_DESCRIPTOR = [
        self::OPERATION_INFILTRATE => "infiltrating",
        self::OPERATION_REINFORCE => "reinforcing",
        self::OPERATION_RAID => "raiding",
        self::OPERATION_LOOT => "looting",
        self::OPERATION_LOOT_TOWN => "looting",
    ];

    const OPERATION_ACTIVE = 1;
    const OPERATION_FAILED = 2;
    const OPERATION_COMPLETE = 3;

    const OPERATION_STATUS = [
        self::OPERATION_ACTIVE => 'active',
        self::OPERATION_FAILED => 'failed',
        self::OPERATION_COMPLETE => 'complete',
    ];

    const OPERATION_STAT_GAIN = [
        self::OPERATION_INFILTRATE => 2,
        self::OPERATION_REINFORCE => 1,
        self::OPERATION_RAID => 2,
        self::OPERATION_LOOT => 0,
        self::OPERATION_LOOT_TOWN => 0,
    ];

    /*const OPERATION_YEN_GAIN = [
        self::OPERATION_INFILTRATE => 200,
        self::OPERATION_REINFORCE => 150,
        self::OPERATION_RAID => 250,
        self::OPERATION_LOOT => 50,
    ];*/

    const BASE_OPERATION_INTERVAL_PROGRESS_PERCENT = 20; // progress per interval
    const BASE_OPERATION_INTERVAL_SECONDS = 12; // time per interval
    const BASE_OPERATION_POOL_COST = [ // chakra/stam cost per interval per rank, 750 total
        3 => 100,
        4 => 150
    ];
    /* 100 / 20 * 12s = 60s */

    const LOOT_GAIN = 10;
    const LOOT_OPERATION_INTERVAL_PROGRESS_PERCENT = 100; // each loot action is only 1 interval
    const LOOT_OPERATION_INTERVAL_SECONDS = 6; // takes half time as normal
    const LOOT_OPERATION_POOL_COST = [
        3 => 50,
        4 => 75
    ];
    /* 100 / 100 * 6s = 6s */


    private System $system;
    private User $user;

    public int $operation_id;
    public int $user_id;
    public int $target_id;
    public int $type;
    public int $progress;
    public int $status;
    public int $target_village;
    public int $user_village;
    public int $last_update_ms;
    public int $interval_progress = 0;

    public function __construct(System $system, User $user, array $operation_data)
    {
        foreach ($operation_data as $key => $value) {
            $this->$key = $value;
        }
        $this->system = $system;
        $this->user = $user;
        if ($this->type == self::OPERATION_LOOT || $this->type == self::OPERATION_LOOT_TOWN) {
            $interval = self::LOOT_OPERATION_INTERVAL_SECONDS;
        } else {
            $interval = self::BASE_OPERATION_INTERVAL_SECONDS;
        }
        $this->interval_progress = (((microtime(true) * 1000) - $this->last_update_ms) / ($interval * 1000)) * 100;
    }

    public function updateData() {
        $this->last_update_ms = microtime(true) * 1000;
        $query = "UPDATE `operations`
            SET `user_id` = '{$this->user_id}',
                `target_id` = '{$this->target_id}',
                `type` = '{$this->type}',
                `progress` = '{$this->progress}',
                `status` = '{$this->status}',
                `target_village` = '{$this->target_village}',
                `user_village` = '{$this->user_village}',
                `last_update_ms` = '{$this->last_update_ms}'
            WHERE `operation_id` = '{$this->operation_id}'";
        $this->system->db->query($query);
    }

    public function progressActiveOperation(): string
    {
        $message = '';
        switch ($this->type) {
            case self::OPERATION_LOOT:
            case self::OPERATION_LOOT_TOWN:
                $interval = self::LOOT_OPERATION_INTERVAL_SECONDS;
                $cost = self::LOOT_OPERATION_POOL_COST[$this->user->rank_num];;
                $speed = self::LOOT_OPERATION_INTERVAL_PROGRESS_PERCENT;
                break;
            case self::OPERATION_INFILTRATE:
                $interval = self::BASE_OPERATION_INTERVAL_SECONDS;
                $interval = round($interval * (100 / (100 + $this->user->village->policy->infiltrate_speed)), 1);
                $cost = self::BASE_OPERATION_POOL_COST[$this->user->rank_num];
                $speed = self::BASE_OPERATION_INTERVAL_PROGRESS_PERCENT;
                break;
            case self::OPERATION_REINFORCE:
                $interval = self::BASE_OPERATION_INTERVAL_SECONDS;
                $interval = round($interval * (100 / (100 + $this->user->village->policy->reinforce_speed)), 1);
                $cost = self::BASE_OPERATION_POOL_COST[$this->user->rank_num];
                $speed = self::BASE_OPERATION_INTERVAL_PROGRESS_PERCENT;
                break;
            case self::OPERATION_RAID:
                $interval = self::BASE_OPERATION_INTERVAL_SECONDS;
                $interval = round($interval * (100 / (100 + $this->user->village->policy->raid_speed)), 1);
                $cost = self::BASE_OPERATION_POOL_COST[$this->user->rank_num];
                $speed = self::BASE_OPERATION_INTERVAL_PROGRESS_PERCENT;
                break;
            default:
                $interval = self::BASE_OPERATION_INTERVAL_SECONDS;
                $cost = self::BASE_OPERATION_POOL_COST[$this->user->rank_num];
                $speed = self::BASE_OPERATION_INTERVAL_PROGRESS_PERCENT;
                break;
        }

        // only progress if active and interval time has passed
        if ($this->status == self::OPERATION_ACTIVE && microtime(true) * 1000 > $this->last_update_ms + $interval * 1000) {
            if ($this->type == self::OPERATION_LOOT) {
                $caravan_target = $this->system->db->query("SELECT * FROM `caravans` WHERE `id` = {$this->target_id} LIMIT 1");
                $caravan_target = $this->system->db->fetch($caravan_target);
            } else {
                $location_target = $this->system->db->query("SELECT * FROM `region_locations` WHERE `id` = {$this->target_id} LIMIT 1");
                $location_target = $this->system->db->fetch($location_target);
            }

            //check pools
            if ($this->user->chakra < $cost || $this->user->stamina < $cost) {
                $this->user->chakra = max($this->user->chakra - $cost, 0);
                $this->user->stamina = max($this->user->stamina - $cost, 0);
                $message .= "You exhausted yourself while " . self::OPERATION_TYPE_DESCRIPTOR[$this->type] . " and were forced to retreat!";
                $this->status = self::OPERATION_FAILED;
                $this->handleFailure();
                return $message;
            }

            $this->user->chakra = max($this->user->chakra - $cost, 0);
            $this->user->stamina = max($this->user->stamina - $cost, 0);

            // add progress
            $this->progress += $speed;

            // get current loot count
            $loot_count = 0;
            $max_loot = WarManager::BASE_LOOT_CAPACITY + $this->user->village->policy->loot_capacity;
            $loot_result = $this->system->db->query("SELECT COUNT(*) as `count` FROM `loot` WHERE `user_id` = {$this->user->user_id} AND `claimed_village_id` IS NULL AND `battle_id` IS NULL LIMIT 1");
            if ($this->system->db->last_num_rows > 0) {
                $loot_result = $this->system->db->fetch($loot_result);
                $loot_count = $loot_result['count'];
            }

            $early_completion = false;
            $cancel_operation = false;
            switch ($this->type) {
                case self::OPERATION_INFILTRATE:
                    if ($loot_count >= $max_loot) {
                        $message .= "You cannot carry any more resources!";
                        break;
                    }
                    if ($location_target['resource_count'] > 0) {
                        $this->system->db->query("INSERT INTO `loot` (`user_id`, `resource_id`, `target_village_id`, `target_location_id`) VALUES ({$this->user_id}, {$location_target['resource_id']}, {$this->target_village}, {$this->target_id})");
                        $message .= "Stole 1 " . System::unSlug(WarManager::RESOURCE_NAMES[$location_target['resource_id']]) . "!";
                        $location_target['resource_count']--;
                        WarLogManager::logAction($this->system, $this->user, 1, WarLogManager::WAR_LOG_RESOURCES_STOLEN, $this->target_village);
                        $this->system->db->query("UPDATE `region_locations` SET `resource_count` = {$location_target['resource_count']} WHERE `id` = {$this->target_id}");
                    } else {
                        $message .= "Target has run out of resources!";
                    }
                    break;
                case self::OPERATION_REINFORCE:
                    switch ($location_target['type']) {
                        case 'castle':
                            $location_target['max_health'] = WarManager::BASE_CASTLE_HEALTH;
                            break;
                        case 'village':
                            $location_target['max_health'] = WarManager::BASE_VILLAGE_HEALTH;
                            break;
                        default:
                            break;
                    }
                    if ($location_target['health'] < $location_target['max_health']) {
                        $player_heal = floor($this->user->level / 2);
                        $new_health = $location_target['health'] + $player_heal;
                        $actual_heal = min($new_health, $location_target['max_health']) - $location_target['health'];
                        $location_target['health'] = $location_target['health'] + $actual_heal;
                        $message .= "Restored " . $actual_heal . " health to " . $location_target['name'] . "!";
                        WarLogManager::logAction($this->system, $this->user, $actual_heal, WarLogManager::WAR_LOG_DAMAGE_HEALED, $this->target_village);
                        $this->system->db->query("UPDATE `region_locations` SET `health` = {$location_target['health']} WHERE `id` = {$this->target_id}");
                    } else {
                        $message .= "Target is already at max health!";
                    }
                    break;
                case self::OPERATION_RAID:
                    if ($location_target['health'] > 0) {
                        //$player_damage = max($this->user->level - $location_target['defense'], 0);
                        $defense_reduction = min($location_target['defense'] / 100, 1);
                        $player_damage = intval($this->user->level * (1 - $defense_reduction));
                        $player_damage = max($player_damage, 0);
                        $new_health = $location_target['health'] - $player_damage;
                        $actual_damage = $location_target['health'] - max($new_health, 0);
                        $location_target['health'] = $new_health;
                        $message .= "Dealt " . $actual_damage . " damage to " . $location_target['name'] . "!";
                        WarLogManager::logAction($this->system, $this->user, $actual_damage, WarLogManager::WAR_LOG_DAMAGE_DEALT, $this->target_village);
                        $this->system->db->query("UPDATE `region_locations` SET `health` = {$location_target['health']} WHERE `id` = {$this->target_id}");
                        if ($location_target['health'] <= 0) {
                            $early_completion = true;
                        }
                    } else {
                        $message .= "Target is already at 0 health!";
                        $early_completion = true;
                    }
                    break;
                case self::OPERATION_LOOT:
                    if ($loot_count >= $max_loot) {
                        $message .= "You cannot carry any more resources!";
                        break;
                    }
                    $caravan_resources = json_decode($caravan_target['resources'], true);
                    $stolen_resources = [];
                    for ($i = 0; $i < self::LOOT_GAIN; $i++) {
                        if ($loot_count >= $max_loot) {
                            $message .= "You cannot carry any more resources!";
                            break;
                        }
                        if (!empty($caravan_resources)) {
                            $random_resource = array_rand($caravan_resources);
                            $caravan_resources[$random_resource]--;
                            if ($caravan_resources[$random_resource] <= 0) {
                                unset($caravan_resources[$random_resource]);
                            }
                            $this->system->db->query("INSERT INTO `loot` (`user_id`, `resource_id`, `target_village_id`) VALUES ({$this->user_id}, {$random_resource}, {$this->target_village})");
                            if (!isset($stolen_resources[$random_resource])) {
                                $stolen_resources[$random_resource] = 0;
                            }
                            $stolen_resources[$random_resource]++;
                        } else {
                            $message .= "Target has run out of resources!";
                            $cancel_operation = true;
                            break;
                        }
                    }
                    $stolen_messages = [];
                    foreach ($stolen_resources as $resource_id => $count) {
                        if (count($stolen_messages) > 0) {
                            $stolen_messages[] = "$count " . System::unSlug(WarManager::RESOURCE_NAMES[$resource_id]);
                        } else {
                            $stolen_messages[] = "Stole $count " . System::unSlug(WarManager::RESOURCE_NAMES[$resource_id]);
                        }
                        WarLogManager::logAction($this->system, $this->user, $count, WarLogManager::WAR_LOG_RESOURCES_STOLEN, $this->target_village);
                    }
                    $message .= implode(", ", $stolen_messages) . ".";
                    $caravan_resources = json_encode($caravan_resources);
                    $this->system->db->query("UPDATE `caravans` SET `resources` = '{$caravan_resources}' WHERE `id` = {$this->target_id}");
                    break;
                case self::OPERATION_LOOT_TOWN:
                    if ($loot_count >= $max_loot) {
                        $message .= "You cannot carry any more resources!";
                        break;
                    }
                    if ($location_target['resource_count'] > 0) {
                        $stolen_resource_count = self::LOOT_GAIN;
                        if ($location_target['resource_count'] < $stolen_resource_count) {
                            $stolen_resource_count = $location_target['resource_count'];
                        }
                        $location_target['resource_count'] -= $stolen_resource_count;
                        for ($i = 0; $i < $stolen_resource_count; $i++) {
                            $this->system->db->query("INSERT INTO `loot` (`user_id`, `resource_id`, `target_village_id`, `target_location_id`) VALUES ({$this->user_id}, {$location_target['resource_id']}, {$this->target_village}, {$this->target_id})");
                        }
                        $message .= "Stole {$stolen_resource_count} " . System::unSlug(WarManager::RESOURCE_NAMES[$location_target['resource_id']]) . "!";
                        WarLogManager::logAction($this->system, $this->user, $stolen_resource_count, WarLogManager::WAR_LOG_RESOURCES_STOLEN, $this->target_village);
                        $this->system->db->query("UPDATE `region_locations` SET `resource_count` = {$location_target['resource_count']} WHERE `id` = {$this->target_id}");
                    } else {
                        $message .= "Target has run out of resources!";
                        $cancel_operation = true;
                        break;
                    }
                    break;
            }
            if ($cancel_operation) {
                $this->status = self::OPERATION_FAILED;
                $this->handleFailure();
            }
            // if progress reaches 100, operation is complete
            else if ($this->progress >= 100 || $early_completion) {
                $this->progress = 100;
                $this->status = self::OPERATION_COMPLETE;
                // handle completion
                $message .= ' ' . $this->handleCompletion();
            }
            // update operation data
            $this->updateData();
        }
        return $message;
    }

    /**
     * @throws RuntimeException
     */
    public function handleCompletion(): string {
        $message = "\n" . System::unSlug(self::OPERATION_TYPE_DESCRIPTOR[$this->type]) . " complete!";
        if ($this->status != self::OPERATION_COMPLETE) {
            throw new RuntimeException("Invalid operation status!");
        }
        $location_target = $this->system->db->query("SELECT `region_locations`.*, `regions`.`village` as `original_village`
            FROM `region_locations`
            INNER JOIN `regions` on `regions`.`region_id` = `region_locations`.`region_id`
            WHERE `id` = {$this->target_id} LIMIT 1");
        $location_target = $this->system->db->fetch($location_target);
        switch ($this->type) {
            case self::OPERATION_INFILTRATE:
                WarLogManager::logAction($this->system, $this->user, 1, WarLogManager::WAR_LOG_INFILTRATE, $this->target_village);
                $defense_reduction = 1 + $this->user->village->policy->infiltrate_defense;
                if ($location_target['defense'] > 0) {
                    $result = max($location_target['defense'] - $defense_reduction, 0);
                    $defense_reduction = $location_target['defense'] - $result;
                    $location_target['defense'] = $result;
                    $message .= "\nDecreased target Defense by {$defense_reduction}!";
                    WarLogManager::logAction($this->system, $this->user, $defense_reduction, WarLogManager::WAR_LOG_DEFENSE_REDUCED, $this->target_village);
                    $this->system->db->query("UPDATE `region_locations` SET `defense` = {$location_target['defense']} WHERE `id` = {$this->target_id}");
                } else {
                    $message .= "\nTarget Defense already at 0!";
                }
                break;
            case self::OPERATION_REINFORCE:
                WarLogManager::logAction($this->system, $this->user, 1, WarLogManager::WAR_LOG_REINFORCE, $this->target_village);
                $defense_gain = 1 + $this->user->village->policy->reinforce_defense;
                if ($location_target['defense'] < 100) {
                    $result = min($location_target['defense'] + $defense_gain, 100);
                    $defense_gain = $result - $location_target['defense'];
                    $location_target['defense'] = $result;
                    $message .= "\nIncreased target Defense by {$defense_gain}!";
                    WarLogManager::logAction($this->system, $this->user, $defense_gain, WarLogManager::WAR_LOG_DEFENSE_GAINED, $this->target_village);
                    $this->system->db->query("UPDATE `region_locations` SET `defense` = {$location_target['defense']} WHERE `id` = {$this->target_id}");
                } else {
                    $message .= "\nTarget Defense already at 100!";
                }
                break;
            case self::OPERATION_RAID:
                WarLogManager::logAction($this->system, $this->user, 1, WarLogManager::WAR_LOG_RAID, $this->target_village);
                $defense_reduction = 1 + $this->user->village->policy->raid_defense;
                if ($location_target['health'] <= 0 && $location_target['type'] == 'castle') {
                    WarLogManager::logRegionCapture($this->system, $this->user, $location_target['region_id']);
                    // change region ownership
                    $this->system->db->query("UPDATE `regions` SET `village` = {$this->user->village->village_id} WHERE `region_id` = {$location_target['region_id']}");
                    // update castle health to 50%, defense to 25
                    $castle_hp = floor(WarManager::BASE_CASTLE_HEALTH * 0.5);
                    $this->system->db->query("UPDATE `region_locations` SET `health` = {$castle_hp}, `defense` = 25 WHERE `region_id` = {$location_target['region_id']} AND `type` = 'castle'");
                    // update patrols, move back 5 minutes
                    $patrol_spawn = time() + (60 * 5);
                    $this->system->db->query("UPDATE `patrols` SET `start_time` = {$patrol_spawn}, `village_id` = {$this->user->village->village_id} WHERE `region_id` = {$location_target['region_id']}");
                    // update caravans, change only caravans that haven't spawned
                    $name = VillageManager::VILLAGE_NAMES[$this->user->village->village_id] . " Caravan";
                    $time = time();
                    $this->system->db->query("UPDATE `caravans` SET `village_id` = {$this->user->village->village_id}, `name` = '{$name}' WHERE `region_id` = {$location_target['region_id']} AND `start_time` > {$time}");
                    // for each occupied village in newly controlled region, if not at war then clear occupation
                    $occupied_villages = $this->system->db->query("SELECT * FROM `region_locations` WHERE `region_id` = {$location_target['region_id']} AND `occupying_village_id` IS NOT NULL");
                    $occupied_villages = $this->system->db->fetch_all($occupied_villages);
                    if ($this->system->db->last_num_rows > 0) {
                        foreach ($occupied_villages as $village) {
                            if (!$this->user->village->isEnemy($village['occupying_village_id'])) {
                                $this->system->db->query("UPDATE `region_locations` SET `occupying_village_id` = NULL WHERE `id` = {$village['id']}");
                            }
                        }
                    }
                } 
                else if ($location_target['health'] <= 0 && $location_target['type'] == 'village') {
                    WarLogManager::logAction($this->system, $this->user, 1, WarLogManager::WAR_LOG_VILLAGES_CAPTURED, $this->target_village);
                    // occupy village and set HP/defense
                    $village_hp = floor(WarManager::BASE_VILLAGE_HEALTH * 0.5);
                    // if self or ally owns the region, set occupying village to null
                    if ($this->user->village->isAlly($location_target['original_village'])) {
                        $this->system->db->query("UPDATE `region_locations` SET `occupying_village_id` = NULL, `health` = {$village_hp}, `defense` = 25 WHERE `id` = {$location_target['id']}");
                    } else {
                        $this->system->db->query("UPDATE `region_locations` SET `occupying_village_id` = {$this->user->village->village_id}, `health` = {$village_hp}, `defense` = 25 WHERE `id` = {$location_target['id']}");
                    }
                }
                else if ($location_target['defense'] > 0) {
                    $result = max($location_target['defense'] - $defense_reduction, 0);
                    $defense_reduction = $location_target['defense'] - $result;
                    $location_target['defense'] = $result;
                    $message .= "\nDecreased target Defense by {$defense_reduction}!";
                    WarLogManager::logAction($this->system, $this->user, $defense_reduction, WarLogManager::WAR_LOG_DEFENSE_REDUCED, $this->target_village);
                    $this->system->db->query("UPDATE `region_locations` SET `defense` = {$location_target['defense']} WHERE `id` = {$this->target_id}");
                } else {
                    $message .= "\nTarget Defense already at 0!";
                }
                break;
            case self::OPERATION_LOOT:
            case self::OPERATION_LOOT_TOWN:
                WarLogManager::logAction($this->system, $this->user, 1, WarLogManager::WAR_LOG_LOOT, $this->target_village);
                break;
        }
        /* Add yen
        $this->user->addMoney(self::OPERATION_YEN_GAIN[$this->type], "Operation");
        $message .= "\nGained " . self::OPERATION_YEN_GAIN[$this->type] . "yen!";*/
        // Add reputation
        if ($this->user->reputation->canGain(UserReputation::ACTIVITY_TYPE_WAR)) {
            $rep_gain = $this->user->reputation->addRep(
                amount: UserReputation::OPERATION_GAINS[$this->type],
                activity_type: UserReputation::ACTIVITY_TYPE_WAR
            );
            if ($rep_gain > 0) {
                $message .= "\nGained " . $rep_gain . " village reputation!";
            }
        }
        // Add stats
        $stat_to_gain = $this->user->getTrainingStatForArena();
        $stat_gain = self::OPERATION_STAT_GAIN[$this->type];
        if ($this->user->rank_num > 3 && $stat_gain > 0) {
            $stat_gain += $this->user->rank_num - 3;
        }
        if ($stat_to_gain != null && $stat_gain > 0) {
            $stat_gained = $this->user->addStatGain($stat_to_gain, $stat_gain);
            if (!empty($stat_gained)) {
                $message .= "\n" . $stat_gained;
            }
        }
        $message .= '!';
        $this->user->operation = 0;
        return $message;
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
    public static function beginOperation(System $system, User $user, int $target_id, int $type, int $target_village): int {
        $time = microtime(true) * 1000;
        $system->db->query("INSERT INTO `operations` (`user_id`, `target_id`, `type`, `progress`, `status`, `target_village`, `user_village`, `last_update_ms`)
            VALUES ({$user->user_id}, {$target_id}, {$type}, 0, " . self::OPERATION_ACTIVE . ", {$target_village}, {$user->village->village_id}, {$time})
        ");
        $operation_id = $system->db->last_insert_id;
        $user->operation = $operation_id;
        // engaging in war mechanics disables pvp immunity
        // $user->pvp_immunity_ms = 0;
        return $operation_id;
    }

    /**
     * @throws RuntimeException
     */
    public static function cancelOperation(System $system, User $user) {
        $system->db->query("UPDATE `operations` set `status` = " . self::OPERATION_FAILED . " WHERE `operation_id` = '{$user->operation}'");
        if ($system->db->last_affected_rows == 0) {
            throw new RuntimeException("Operation not found!");
        }
        $user->operation = 0;
        $user->updateData();
    }
}