<?php

class Operation
{
    const OPERATION_INFILTRATE = 1;
    const OPERATION_REINFORCE = 2;
    const OPERATION_RAID = 3;
    const OPERATION_LOOT = 4;

    const OPERATION_TYPE = [
        self::OPERATION_INFILTRATE => "infiltrate",
        self::OPERATION_REINFORCE => "reinforce",
        self::OPERATION_RAID => "raid",
        self::OPERATION_LOOT => "loot",
    ];

    const OPERATION_TYPE_DESCRIPTOR = [
        self::OPERATION_INFILTRATE => "infiltrating",
        self::OPERATION_REINFORCE => "reinforcing",
        self::OPERATION_RAID => "raiding",
        self::OPERATION_LOOT => "looting",
    ];

    const OPERATION_ACTIVE = 1;
    const OPERATION_FAILED = 2;
    const OPERATION_COMPLETE = 3;

    const OPERATION_STATUS = [
        self::OPERATION_ACTIVE => 'active',
        self::OPERATION_FAILED => 'failed',
        self::OPERATION_COMPLETE => 'complete',
    ];

    /*const OPERATION_STAT_GAIN = [
        self::OPERATION_INFILTRATE => 4,
        self::OPERATION_REINFORCE => 3,
        self::OPERATION_RAID => 5,
        self::OPERATION_LOOT => 1,
    ];

    const OPERATION_YEN_GAIN = [
        self::OPERATION_INFILTRATE => 200,
        self::OPERATION_REINFORCE => 150,
        self::OPERATION_RAID => 250,
        self::OPERATION_LOOT => 50,
    ];*/

    const BASE_OPERATION_SPEED = 20; // progress per interval
    const BASE_OPERATION_INTERVAL = 12; // time per interval
    const BASE_OPERATION_COST = 150; // chakra/stam cost per interval, 750 total
    /* 100 / 20 * 12s = 60s */

    const LOOT_GAIN = 5;
    const LOOT_OPERATION_SPEED = 100; // each loot action is only 1 interval
    const LOOT_OPERATION_INTERVAL = 6; // takes half time as normal
    const LOOT_OPERATION_COST = 75; // takes half pool cost
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
        if ($this->type == self::OPERATION_LOOT) {
            $interval = self::LOOT_OPERATION_INTERVAL;
        } else {
            $interval = self::BASE_OPERATION_INTERVAL;
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
                $interval = self::LOOT_OPERATION_INTERVAL;
                $cost = self::LOOT_OPERATION_COST;
                $speed = self::LOOT_OPERATION_SPEED;
                break;
            case self::OPERATION_INFILTRATE:
                $interval = self::BASE_OPERATION_INTERVAL * 1000;
                $interval = round($interval * (100 / (100 + $this->user->village->policy->infiltrate_speed)), 1);
                $cost = self::BASE_OPERATION_COST;
                $speed = self::BASE_OPERATION_SPEED;
                break;
            case self::OPERATION_REINFORCE:
                $interval = self::BASE_OPERATION_INTERVAL;
                $interval = round($interval * (100 / (100 + $this->user->village->policy->reinforce_speed)), 1);
                $cost = self::BASE_OPERATION_COST;
                $speed = self::BASE_OPERATION_SPEED;
                break;
            case self::OPERATION_RAID:
                $interval = self::BASE_OPERATION_INTERVAL;
                $interval = round($interval * (100 / (100 + $this->user->village->policy->raid_speed)), 1);
                $cost = self::BASE_OPERATION_COST;
                $speed = self::BASE_OPERATION_SPEED;
                break;
            default:
                $interval = self::BASE_OPERATION_INTERVAL;
                $cost = self::BASE_OPERATION_COST;
                $speed = self::BASE_OPERATION_SPEED;
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
            switch ($this->type) {
                case self::OPERATION_INFILTRATE:
                    if ($loot_count >= $max_loot) {
                        $message .= "You cannot carry any more loot!";
                        break;
                    }
                    if ($location_target['resource_count'] > 0) {
                        $this->system->db->query("INSERT INTO `loot` (`user_id`, `resource_id`, `target_village_id`, `target_location_id`) VALUES ({$this->user_id}, {$location_target['resource_id']}, {$this->target_village}, {$this->target_id})");
                        $message .= "Stole 1 " . System::unSlug(WarManager::RESOURCE_NAMES[$location_target['resource_id']]) . "!";
                        $location_target['resource_count']--;

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
                        $player_attack = $this->user->level;
                        $player_heal = floor($this->user->level / 2);
                        $location_target['health'] = min($location_target['health'] + $player_heal, $location_target['max_health']);
                        $message .= "Restored " . $player_heal . " health to " . $location_target['name'] . "!";
                        $this->system->db->query("UPDATE `region_locations` SET `health` = {$location_target['health']} WHERE `id` = {$this->target_id}");
                    } else {
                        $message .= "Target is already at max health!";
                    }
                    break;
                case self::OPERATION_RAID:
                    if ($location_target['health'] > 0) {
                        $player_attack = $this->user->level;
                        $player_damage = max($this->user->level - $location_target['defense'], 0);
                        $location_target['health'] = max($location_target['health'] - $player_damage, 0);
                        $message .= "Dealt " . $player_damage . " damage to " . $location_target['name'] . "!";
                        $this->system->db->query("UPDATE `region_locations` SET `health` = {$location_target['health']} WHERE `id` = {$this->target_id}");
                    } else {
                        $message .= "Target is already at 0 health!";
                        $early_completion = true;
                    }
                    break;
                case self::OPERATION_LOOT:
                    if ($loot_count >= $max_loot) {
                        $message .= "You cannot carry any more loot!";
                        break;
                    }
                    $caravan_resources = json_decode($caravan_target['resources'], true);
                    for ($i = 0; $i < self::LOOT_GAIN; $i++) {
                        if (!empty($caravan_resources)) {
                            $random_resource = array_rand($caravan_resources);
                            $caravan_resources[$random_resource]--;
                            if ($caravan_resources[$random_resource] == 0) {
                                unset($caravan_resources[$random_resource]);
                            }
                            $this->system->db->query("INSERT INTO `loot` (`user_id`, `resource_id`, `target_village_id`) VALUES ({$this->user_id}, {$random_resource}, {$this->target_village})");
                            $message .= "Stole 1 " . System::unSlug(WarManager::RESOURCE_NAMES[$random_resource]) . "! ";
                        } else {
                            $message .= "Target has run out of resources!";
                        }
                    }
                    $caravan_resources = json_encode($caravan_resources);
                    $this->system->db->query("UPDATE `caravans` SET `resources` = '{$caravan_resources}' WHERE `id` = {$this->target_id}");
                    break;
            }

            // if progress reaches 100, operation is complete
            if ($this->progress >= 100 || $early_completion) {
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
        $location_target = $this->system->db->query("SELECT * FROM `region_locations` WHERE `id` = {$this->target_id} LIMIT 1");
        $location_target = $this->system->db->fetch($location_target);
        switch ($this->type) {
            case self::OPERATION_INFILTRATE:
                $defense_reduction = 1 + $this->user->village->policy->infiltrate_defense;
                if ($location_target['defense'] > 0) {
                    $result = max($location_target['defense'] - $defense_reduction, 0);
                    $defense_reduction = $location_target['defense'] - $result;
                    $location_target['defense'] = $result;
                    $message .= "\nDecreased target Defense by {$defense_reduction}!";
                    $this->system->db->query("UPDATE `region_locations` SET `defense` = {$location_target['defense']} WHERE `id` = {$this->target_id}");
                } else {
                    $message .= "\nTarget Defense already at 0!";
                }
                break;
            case self::OPERATION_REINFORCE:
                $defense_gain = 1 + $this->user->village->policy->reinforce_defense;
                if ($location_target['defense'] < 100) {
                    $result = min($location_target['defense'] + $defense_gain, 100);
                    $defense_gain = $result - $location_target['defense'];
                    $location_target['defense'] = $result;
                    $message .= "\nIncreased target Defense by {$defense_gain}!";
                    $this->system->db->query("UPDATE `region_locations` SET `defense` = {$location_target['defense']} WHERE `id` = {$this->target_id}");
                } else {
                    $message .= "\nTarget Defense already at 100!";
                }
                break;
            case self::OPERATION_RAID:
                $defense_reduction = 1 + $this->user->village->policy->raid_defense;
                if ($location_target['health'] == 0) {
                    // change region ownership
                    $this->system->db->query("UPDATE `regions` SET `village` = {$this->user->village->village_id} WHERE `region_id` = {$location_target['region_id']}");
                    // update castle health to 25%
                    $castle_hp = floor(WarManager::BASE_CASTLE_HEALTH * 0.25);
                    $this->system->db->query("UPDATE `region_locations` SET `health` = {$castle_hp} WHERE `region_id` = {$location_target['region_id']} AND `type` = 'Castle'");
                    // update patrols, move back 5 minutes
                    $patrol_spawn = time() + (60 * 5);
                    $this->system->db->query("UPDATE `patrols` SET `start_time` = {$patrol_spawn}, `village_id` = {$this->user->village->village_id} WHERE `region_id` = {$location_target['region_id']}");
                    // update caravans, change only caravans that haven't spawned
                    $time = time();
                    $this->system->db->query("UPDATE `caravans` SET `village_id` = {$this->user->village->village_id} WHERE `region_id` = {$location_target['region_id']} AND `start_time` > {$time}");
                }
                else if ($location_target['defense'] > 0) {
                    $result = max($location_target['defense'] - $defense_reduction, 0);
                    $defense_reduction = $location_target['defense'] - $result;
                    $location_target['defense'] = $result;
                    $message .= "\nDecreased target Defense by {$defense_reduction}!";
                    $this->system->db->query("UPDATE `region_locations` SET `defense` = {$location_target['defense']} WHERE `id` = {$this->target_id}");
                } else {
                    $message .= "\nTarget Defense already at 0!";
                }
                break;
            case self::OPERATION_LOOT:
                break;
        }
        /* Add yen
        $this->user->addMoney(self::OPERATION_YEN_GAIN[$this->type], "Operation");
        $message .= "\nGained " . self::OPERATION_YEN_GAIN[$this->type] . "yen!";*/
        // Add reputation
        if ($this->user->reputation->canGain()) {
            $rep_gain = $this->user->reputation->addRep(UserReputation::OPERATION_GAINS[$this->type]);
            if ($rep_gain > 0) {
                $message .= "\nGained " . $rep_gain . " village reputation!";
            }
        }
        /* Add stats
        $stat_to_gain = $this->user->getTrainingStatForArena();
        $stat_gain = self::OPERATION_STAT_GAIN[$this->type];
        if ($stat_to_gain != null && $stat_gain > 0) {
            $stat_gained = $this->user->addStatGain($stat_to_gain, $stat_gain);
            if (!empty($stat_gained)) {
                $message .= "\n" . $stat_gained;
            }
        }
        $message .= '!';*/
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
        $user->pvp_immunity_ms = 0;
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