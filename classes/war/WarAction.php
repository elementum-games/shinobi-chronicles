<?php

class WarAction {
    const WAR_ACTION_INFILTRATE = 1;
    const WAR_ACTION_REINFORCE = 2;
    const WAR_ACTION_RAID = 3;
    const WAR_ACTION_LOOT = 4;
    const WAR_ACTION_LOOT_TOWN = 5;

    const WAR_ACTION_TYPE = [
        self::WAR_ACTION_INFILTRATE => "infiltrate",
        self::WAR_ACTION_REINFORCE => "reinforce",
        self::WAR_ACTION_RAID => "raid",
        self::WAR_ACTION_LOOT => "loot",
        self::WAR_ACTION_LOOT_TOWN => "loot",
    ];

    const WAR_ACTION_TYPE_DESCRIPTOR = [
        self::WAR_ACTION_INFILTRATE => "infiltrating",
        self::WAR_ACTION_REINFORCE => "reinforcing",
        self::WAR_ACTION_RAID => "raiding",
        self::WAR_ACTION_LOOT => "looting",
        self::WAR_ACTION_LOOT_TOWN => "looting",
    ];

    const WAR_ACTION_ACTIVE = 1;
    const WAR_ACTION_FAILED = 2;
    const WAR_ACTION_COMPLETE = 3;

    const WAR_ACTION_STATUS = [
        self::WAR_ACTION_ACTIVE => 'active',
        self::WAR_ACTION_FAILED => 'failed',
        self::WAR_ACTION_COMPLETE => 'complete',
    ];

    const WAR_ACTION_STAT_GAIN = [
        self::WAR_ACTION_INFILTRATE => 3,
        self::WAR_ACTION_REINFORCE => 2,
        self::WAR_ACTION_RAID => 4,
        self::WAR_ACTION_LOOT => 0,
        self::WAR_ACTION_LOOT_TOWN => 0,
    ];

    const BASE_WAR_ACTION_INTERVAL_PROGRESS_PERCENT = 20; // progress per interval
    const BASE_WAR_ACTION_INTERVAL_SECONDS = 12; // time per interval
    const BASE_WAR_ACTION_POOL_COST = [ // chakra/stam cost per interval per rank, 750 total
        3 => 100,
        4 => 150
    ];
    /* 100 / 20 * 12s = 60s */

    const LOOT_GAIN = 10;
    const LOOT_WAR_ACTION_INTERVAL_PROGRESS_PERCENT = 100; // each loot action is only 1 interval
    const LOOT_WAR_ACTION_INTERVAL_SECONDS = 6; // takes half time as normal
    const LOOT_WAR_ACTION_POOL_COST = [
        3 => 50,
        4 => 75
    ];
    /* 100 / 100 * 6s = 6s */


    private System $system;
    private User $user;

    public int $war_action_id;
    public int $user_id;
    public int $target_id;
    public int $type;
    public int $progress;
    public int $status;
    public int $target_village;
    public int $user_village;
    public int $last_update_ms;
    public int $interval_progress = 0;

    public function __construct(System $system, User $user, array $war_action_data)
    {
        foreach ($war_action_data as $key => $value) {
            $this->$key = $value;
        }
        $this->system = $system;
        $this->user = $user;
        switch ($this->type) {
            case self::WAR_ACTION_LOOT:
            case self::WAR_ACTION_LOOT_TOWN:
                $interval = self::LOOT_WAR_ACTION_INTERVAL_SECONDS;
                break;
            case self::WAR_ACTION_INFILTRATE:
                $interval = self::BASE_WAR_ACTION_INTERVAL_SECONDS;
                $interval = $interval * (100 / (100 + $this->user->village->policy->infiltrate_speed));
                $interval = round($interval * (100 / (100 + $this->user->village->active_upgrade_effects[VillageUpgradeConfig::UPGRADE_EFFECT_INFILTRATE_SPEED])), 1);
                break;
            case self::WAR_ACTION_REINFORCE:
                $interval = self::BASE_WAR_ACTION_INTERVAL_SECONDS;
                $interval = $interval * (100 / (100 + $this->user->village->policy->reinforce_speed));
                $interval = round($interval * (100 / (100 + $this->user->village->active_upgrade_effects[VillageUpgradeConfig::UPGRADE_EFFECT_REINFORCE_SPEED])), 1);
                break;
            case self::WAR_ACTION_RAID:
                $interval = self::BASE_WAR_ACTION_INTERVAL_SECONDS;
                $interval = $interval * (100 / (100 + $this->user->village->policy->raid_speed));
                $interval = round($interval * (100 / (100 + $this->user->village->active_upgrade_effects[VillageUpgradeConfig::UPGRADE_EFFECT_RAID_SPEED])), 1);
                break;
            default:
                break;
        }
        $this->interval_progress = (((microtime(true) * 1000) - $this->last_update_ms) / ($interval * 1000)) * 100;
    }

    public function updateData() {
        $this->last_update_ms = microtime(true) * 1000;
        $query = "UPDATE `war_actions`
            SET `user_id` = '{$this->user_id}',
                `target_id` = '{$this->target_id}',
                `type` = '{$this->type}',
                `progress` = '{$this->progress}',
                `status` = '{$this->status}',
                `target_village` = '{$this->target_village}',
                `user_village` = '{$this->user_village}',
                `last_update_ms` = '{$this->last_update_ms}'
            WHERE `war_action_id` = '{$this->war_action_id}'";
        $this->system->db->query($query);
    }

    public function progressActiveWarAction(): string {
        $message = '';
        switch ($this->type) {
            case self::WAR_ACTION_LOOT:
                $interval = self::LOOT_WAR_ACTION_INTERVAL_SECONDS;
                $cost = self::LOOT_WAR_ACTION_POOL_COST[$this->user->rank_num];;
                $speed = self::LOOT_WAR_ACTION_INTERVAL_PROGRESS_PERCENT;
                break;
            case self::WAR_ACTION_INFILTRATE:
                $interval = self::BASE_WAR_ACTION_INTERVAL_SECONDS;
                $interval = $interval * (100 / (100 + $this->user->village->policy->infiltrate_speed));
                $interval = round($interval * (100 / (100 + $this->user->village->active_upgrade_effects[VillageUpgradeConfig::UPGRADE_EFFECT_INFILTRATE_SPEED])), 1);
                $cost = self::BASE_WAR_ACTION_POOL_COST[$this->user->rank_num];
                $speed = self::BASE_WAR_ACTION_INTERVAL_PROGRESS_PERCENT;
                break;
            case self::WAR_ACTION_REINFORCE:
                $interval = self::BASE_WAR_ACTION_INTERVAL_SECONDS;
                $interval = $interval * (100 / (100 + $this->user->village->policy->reinforce_speed));
                $interval = round($interval * (100 / (100 + $this->user->village->active_upgrade_effects[VillageUpgradeConfig::UPGRADE_EFFECT_REINFORCE_SPEED])), 1);
                $cost = self::BASE_WAR_ACTION_POOL_COST[$this->user->rank_num];
                $speed = self::BASE_WAR_ACTION_INTERVAL_PROGRESS_PERCENT;
                break;
            case self::WAR_ACTION_RAID:
                $interval = self::BASE_WAR_ACTION_INTERVAL_SECONDS;
                $interval = $interval * (100 / (100 + $this->user->village->policy->raid_speed));
                $interval = round($interval * (100 / (100 + $this->user->village->active_upgrade_effects[VillageUpgradeConfig::UPGRADE_EFFECT_RAID_SPEED])), 1);
                $cost = self::BASE_WAR_ACTION_POOL_COST[$this->user->rank_num];
                $speed = self::BASE_WAR_ACTION_INTERVAL_PROGRESS_PERCENT;
                break;
            default:
                $interval = self::BASE_WAR_ACTION_INTERVAL_SECONDS;
                $cost = self::BASE_WAR_ACTION_POOL_COST[$this->user->rank_num];
                $speed = self::BASE_WAR_ACTION_INTERVAL_PROGRESS_PERCENT;
                break;
        }

        $cost *= 1 - ($this->user->village->active_upgrade_effects[VillageUpgradeConfig::UPGRADE_EFFECT_WAR_ACTION_COST] / 100);

        // only progress if active and interval time has passed
        if ($this->status == self::WAR_ACTION_ACTIVE && microtime(true) * 1000 > $this->last_update_ms + $interval * 1000) {
            // check pools
            if ($this->user->chakra < $cost || $this->user->stamina < $cost) {
                $this->user->chakra = max($this->user->chakra - $cost, 0);
                $this->user->stamina = max($this->user->stamina - $cost, 0);
                $message .= "You exhausted yourself while " . self::WAR_ACTION_TYPE_DESCRIPTOR[$this->type] . " and were forced to retreat!";
                $this->status = self::WAR_ACTION_FAILED;
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


            if ($this->type == self::WAR_ACTION_LOOT) {
                $caravan_target = $this->system->db->query("SELECT * FROM `caravans` WHERE `id` = {$this->target_id} LIMIT 1");
                $caravan_target = $this->system->db->fetch($caravan_target);
            } else {
                $location_target = $this->system->db->query("SELECT * FROM `region_locations` WHERE `region_location_id` = {$this->target_id} LIMIT 1");
                $location_target = $this->system->db->fetch($location_target);
                $location_target = RegionLocation::fromDb(
                    data: $location_target,
                    village: VillageManager::getVillageByID($this->system, $this->target_village)
                );
            }

            $early_completion = false;
            $cancel_war_action = false;
            switch ($this->type) {
                case self::WAR_ACTION_INFILTRATE:
                    if ($loot_count >= $max_loot) {
                        $message .= "You cannot carry any more resources!";
                        break;
                    }
                    if ($location_target->resource_count > 0) {
                        $this->system->db->query("INSERT INTO `loot` (`user_id`, `resource_id`, `target_village_id`, `target_location_id`) VALUES ({$this->user_id}, {$location_target->resource_id}, {$this->target_village}, {$this->target_id})");
                        $message .= "Stole 1 " . System::unSlug(WarManager::RESOURCE_NAMES[$location_target->resource_id]) . "!";
                        $location_target->resource_count--;
                        WarLogManager::logAction($this->system, $this->user, 1, WarLogManager::WAR_LOG_RESOURCES_STOLEN, $this->target_village);
                        $this->system->db->query("UPDATE `region_locations` SET `resource_count` = {$location_target->resource_count} WHERE `region_location_id` = {$this->target_id}");
                    }
                    break;
                case self::WAR_ACTION_REINFORCE:
                    if ($location_target->health < $location_target->max_health) {
                        $player_heal = floor($this->user->level / 2);
                        $player_heal *= 1 + ($this->user->village->active_upgrade_effects[VillageUpgradeConfig::UPGRADE_EFFECT_REINFORCE_HEAL] / 100);
                        $new_health = $location_target->health + $player_heal;
                        $actual_heal = min($new_health, $location_target->max_health) - $location_target->health;
                        $location_target->health = $location_target->health + $actual_heal;
                        $message .= "Restored " . $actual_heal . " health to " . $location_target->name . "!";
                        WarLogManager::logAction($this->system, $this->user, $actual_heal, WarLogManager::WAR_LOG_DAMAGE_HEALED, $this->target_village);
                        $this->system->db->query("UPDATE `region_locations` SET `health` = {$location_target->health} WHERE `region_location_id` = {$this->target_id}");
                    }
                    break;
                case self::WAR_ACTION_RAID:
                    if ($location_target->health > 0) {
                        //$player_damage = max($this->user->level - $location_target->defense, 0);
                        $defense_reduction = min($location_target->defense / 100, 1);
                        $player_damage = $this->user->level;
                        $player_damage *= 1 + ($this->user->village->active_upgrade_effects[VillageUpgradeConfig::UPGRADE_EFFECT_RAID_DAMAGE] / 100);
                        $player_damage = intval($player_damage * (1 - $defense_reduction));
                        $player_damage = max($player_damage, 0);
                        $new_health = $location_target->health - $player_damage;
                        $actual_damage = $location_target->health - max($new_health, 0);
                        $location_target->health = $new_health;
                        $message .= "Dealt " . $actual_damage . " damage to " . $location_target->name . "!";
                        WarLogManager::logAction($this->system, $this->user, $actual_damage, WarLogManager::WAR_LOG_DAMAGE_DEALT, $this->target_village);
                        $this->system->db->query("UPDATE `region_locations` SET `health` = {$location_target->health} WHERE `region_location_id` = {$this->target_id}");
                        if ($location_target->health <= 0) {
                            $early_completion = true;
                        }
                    } else {
                        $early_completion = true;
                    }
                    break;
                case self::WAR_ACTION_LOOT:
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
                            $cancel_war_action = true;
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
            }

            if ($cancel_war_action) {
                $this->status = self::WAR_ACTION_FAILED;
                $this->handleFailure();
            }
            // if progress reaches 100, war action is complete
            else if ($this->progress >= 100 || $early_completion) {
                $this->progress = 100;
                $this->status = self::WAR_ACTION_COMPLETE;
                // handle completion
                $message .= ' ' . $this->handleCompletion();
            }
            // update war action data
            $this->updateData();
        }
        return $message;
    }

    /**
     * @throws RuntimeException
     */
    public function handleCompletion(): string {
        $message = "\n" . System::unSlug(self::WAR_ACTION_TYPE_DESCRIPTOR[$this->type]) . " complete!";
        if ($this->status != self::WAR_ACTION_COMPLETE) {
            throw new RuntimeException("Invalid war action status!");
        }

        $target_village = VillageManager::getVillageByID($this->system, $this->target_village);

        $location_target_result = $this->system->db->query("SELECT * from `region_locations` WHERE `region_location_id` = {$this->target_id} LIMIT 1");
        $location_target_row = $this->system->db->fetch($location_target_result);
        $location_target = RegionLocation::fromDb($location_target_row, $target_village);

        switch ($this->type) {
            case self::WAR_ACTION_INFILTRATE:
                WarLogManager::logAction($this->system, $this->user, 1, WarLogManager::WAR_LOG_INFILTRATE, $this->target_village);
                $defense_reduction = 1 + $this->user->village->policy->infiltrate_defense;
                $stability_reduction = 1 + $this->user->village->policy->infiltrate_stability;
                if ($location_target->defense > 0) {
                    $result = max($location_target->defense - $defense_reduction, 0);
                    $defense_reduction = $location_target->defense - $result;
                    $location_target->defense = $result;
                    $message .= "\nDecreased target Defense by {$defense_reduction}!";
                    WarLogManager::logAction($this->system, $this->user, $defense_reduction, WarLogManager::WAR_LOG_DEFENSE_REDUCED, $this->target_village);
                    $this->system->db->query("UPDATE `region_locations` SET `defense` = {$location_target->defense} WHERE `region_location_id` = {$this->target_id}");
                } else {
                    $message .= "\nTarget Defense already at 0!";
                }
                if ($location_target->stability > WarManager::MIN_STABILITY) {
                    $result = max($location_target->stability - $stability_reduction, WarManager::MIN_STABILITY);
                    $stability_reduction = $location_target->stability - $result;
                    $location_target->stability = $result;
                    $message .= "\nDecreased target Stability by {$stability_reduction}!";
                    WarLogManager::logAction($this->system, $this->user, $stability_reduction, WarLogManager::WAR_LOG_STABILITY_REDUCED, $this->target_village);
                    $this->system->db->query("UPDATE `region_locations` SET `stability` = {$location_target->stability} WHERE `region_location_id` = {$this->target_id}");
                } else {
                    $message .= "\nTarget Stability already at " . WarManager::MIN_STABILITY ."!";
                }
                break;
            case self::WAR_ACTION_REINFORCE:
                WarLogManager::logAction($this->system, $this->user, 1, WarLogManager::WAR_LOG_REINFORCE, $this->target_village);
                $defense_gain = 1 + $this->user->village->policy->reinforce_defense;
                $stability_gain = 1 + $this->user->village->policy->reinforce_stability;
                $max_stability = WarManager::MAX_STABILITY + $target_village->active_upgrade_effects[VillageUpgradeConfig::UPGRADE_EFFECT_MAX_STABILITY] + $target_village->policy->max_stability;
                if ($location_target->defense < 100) {
                    $result = min($location_target->defense + $defense_gain, 100);
                    $defense_gain = $result - $location_target->defense;
                    $location_target->defense = $result;
                    $message .= "\nIncreased target Defense by {$defense_gain}!";
                    WarLogManager::logAction($this->system, $this->user, $defense_gain, WarLogManager::WAR_LOG_DEFENSE_GAINED, $this->target_village);
                    $this->system->db->query("UPDATE `region_locations` SET `defense` = {$location_target->defense} WHERE `region_location_id` = {$this->target_id}");
                } else {
                    $message .= "\nTarget Defense already at 100!";
                }
                if ($location_target->stability < $max_stability) {
                    $result = min($location_target->stability + $stability_gain, $max_stability);
                    $stability_gain = $result - $location_target->stability;
                    $location_target->stability = $result;
                    $message .= "\nIncreased target Stability by {$stability_gain}!";
                    WarLogManager::logAction($this->system, $this->user, $stability_gain, WarLogManager::WAR_LOG_STABILITY_GAINED, $this->target_village);
                    $this->system->db->query("UPDATE `region_locations` SET `stability` = {$location_target->stability} WHERE `region_location_id` = {$this->target_id}");
                } else {
                    $message .= "\nTarget Stability already at " . $max_stability . "!";
                }
                break;
            case self::WAR_ACTION_RAID:
                WarLogManager::logAction($this->system, $this->user, 1, WarLogManager::WAR_LOG_RAID, $this->target_village);
                $defense_reduction = 1 + $this->user->village->policy->raid_defense;
                $stability_reduction = 1 + $this->user->village->policy->raid_stability;
                if ($location_target->health <= 0 && $location_target->type == 'castle') {
                    WarLogManager::logRegionCapture($this->system, $this->user, $location_target->region_id);
                    // change region ownership
                    $this->system->db->query("UPDATE `regions` SET `village` = {$this->user->village->village_id} WHERE `region_id` = {$location_target->region_id}");

                    // update castle health to 50%, defense to 25, stability to 25, occupying village
                    $castle_hp = floor($location_target->max_health * (WarManager::INITIAL_LOCATION_CAPTURE_HEALTH_PERCENT / 100));
                    $castle_defense = WarManager::INITIAL_LOCATION_CAPTURE_DEFENSE;
                    $castle_stability = WarManager::INITIAL_LOCATION_CAPTURE_STABILITY;

                    $this->system->db->query("UPDATE `region_locations` SET `health` = {$castle_hp}, `defense` = {$castle_defense}, `stability` = {$castle_stability}, `occupying_village_id` = {$this->user->village->village_id} WHERE `region_id` = {$location_target->region_id} AND `type` = 'castle'");
                    // update patrols, move back 5 minutes
                    $patrol_spawn = time() + (60 * 5);
                    $this->system->db->query("UPDATE `patrols` SET `start_time` = {$patrol_spawn}, `village_id` = {$this->user->village->village_id} WHERE `region_id` = {$location_target->region_id}");
                    // update caravans, change only caravans that haven't spawned
                    $name = VillageManager::VILLAGE_NAMES[$this->user->village->village_id] . " Caravan";
                    $time = time();
                    $this->system->db->query("UPDATE `caravans` SET `village_id` = {$this->user->village->village_id}, `name` = '{$name}' WHERE `region_id` = {$location_target->region_id} AND `start_time` > {$time}");
                } else if ($location_target->health <= 0 && $location_target->type == 'village') {
                    WarLogManager::logAction($this->system, $this->user, 1, WarLogManager::WAR_LOG_VILLAGES_CAPTURED, $this->target_village);

                    // occupy town and set HP/defense/stability
                    $town_hp = floor($location_target->max_health * (WarManager::INITIAL_LOCATION_CAPTURE_HEALTH_PERCENT / 100));
                    $town_defense = WarManager::INITIAL_LOCATION_CAPTURE_DEFENSE;

                    // if retaken by original village, clear rebellion and capture normally
                    $town_stability = $location_target->stability;
                    $town_rebellion = $location_target->rebellion_active;
                    if ($location_target->rebellion_active) {
                        if ($this->user_village == WarManager::REGION_ORIGINAL_VILLAGE[$location_target->region_id]) {
                            $town_stability = WarManager::INITIAL_LOCATION_CAPTURE_STABILITY;
                            $town_rebellion = 0;
                        }
                    } else {
                        $town_stability = WarManager::INITIAL_LOCATION_CAPTURE_STABILITY;
                    }
                    $this->system->db->query("UPDATE `region_locations` SET `occupying_village_id` = {$this->user->village->village_id}, `health` = {$town_hp}, `defense` = {$town_defense}, `stability` = {$town_stability}, `rebellion_active` = {$town_rebellion} WHERE `region_location_id` = {$location_target->region_location_id}");
                } else {
                    if ($location_target->defense > 0) {
                        $result = max($location_target->defense - $defense_reduction, 0);
                        $defense_reduction = $location_target->defense - $result;
                        $location_target->defense = $result;
                        $message .= "\nDecreased target Defense by {$defense_reduction}!";
                        WarLogManager::logAction($this->system, $this->user, $defense_reduction, WarLogManager::WAR_LOG_DEFENSE_REDUCED, $this->target_village);
                        $this->system->db->query("UPDATE `region_locations` SET `defense` = {$location_target->defense} WHERE `region_location_id` = {$this->target_id}");
                    } else {
                        $message .= "\nTarget Defense already at 0!";
                    }
                    if ($location_target->stability > WarManager::MIN_STABILITY) {
                        $result = max($location_target->stability - $stability_reduction, WarManager::MIN_STABILITY);
                        $stability_reduction = $location_target->stability - $result;
                        $location_target->stability = $result;
                        $message .= "\nDecreased target Stability by {$stability_reduction}!";
                        WarLogManager::logAction($this->system, $this->user, $stability_reduction, WarLogManager::WAR_LOG_STABILITY_REDUCED, $this->target_village);
                        $this->system->db->query("UPDATE `region_locations` SET `stability` = {$location_target->stability} WHERE `region_location_id` = {$this->target_id}");
                    } else {
                        $message .= "\nTarget Stability already at " . WarManager::MIN_STABILITY . "!";
                    }
                }
                break;
            case self::WAR_ACTION_LOOT:
                WarLogManager::logAction($this->system, $this->user, 1, WarLogManager::WAR_LOG_LOOT, $this->target_village);
                break;
        }

        // Add reputation
        if ($this->user->reputation->canGain(UserReputation::ACTIVITY_TYPE_WAR)) {
            $rep_gain = $this->user->reputation->addRep(
                amount: UserReputation::WAR_ACTION_GAINS[$this->type],
                activity_type: UserReputation::ACTIVITY_TYPE_WAR
            );
            if ($rep_gain > 0) {
                $message .= "\nGained " . $rep_gain . " village reputation!";
            }
        }

        // Daily Task
        if ($this->user->daily_tasks->hasTaskType(DailyTask::ACTIVITY_DAILY_WAR)) {
            $this->user->daily_tasks->progressTask(DailyTask::ACTIVITY_DAILY_WAR, UserReputation::WAR_ACTION_GAINS[$this->type]);
        }

        // Add stats
        $stat_to_gain = $this->user->getTrainingStatForArena();
        $stat_gain = self::WAR_ACTION_STAT_GAIN[$this->type];
        if ($this->user->rank_num > 3 && $stat_gain > 0) {
            $stat_gain += $this->user->rank_num - 3;
        }
        if ($stat_to_gain != null && $stat_gain > 0) {
            $stat_gained = $this->user->addStatGain($stat_to_gain, $stat_gain);
            if (!empty($stat_gained)) {
                $message .= "\n" . $stat_gained . "!";
            }
        }

        $this->user->war_action_id = 0;

        return $message;
    }

    /**
     * @throws RuntimeException
     */
    public function handleFailure() {
        if ($this->status != self::WAR_ACTION_FAILED) {
            throw new RuntimeException("Invalid status!");
        }
        $this->user->war_action_id = 0;
    }

    /**
     * @throws RuntimeException
     */
    public static function beginWarAction(System $system, User $user, int $target_id, int $type, int $target_village): int {
        $time = microtime(true) * 1000;
        $system->db->query("INSERT INTO `war_actions` (`user_id`, `target_id`, `type`, `progress`, `status`, `target_village`, `user_village`, `last_update_ms`)
            VALUES ({$user->user_id}, {$target_id}, {$type}, 0, " . self::WAR_ACTION_ACTIVE . ", {$target_village}, {$user->village->village_id}, {$time})
        ");
        $war_action_id = $system->db->last_insert_id;
        $user->war_action_id = $war_action_id;
        // engaging in war mechanics disables pvp immunity
        $user->pvp_immunity_ms = 0;
        return $war_action_id;
    }

    /**
     * @throws RuntimeException
     */
    public static function cancelWarAction(System $system, User $user) {
        $system->db->query("UPDATE `war_actions` set `status` = " . self::WAR_ACTION_FAILED . " WHERE `war_action_id` = '{$user->war_action_id}'");
        if ($system->db->last_affected_rows == 0) {
            throw new RuntimeException("War action not found!");
        }
        $user->war_action_id = 0;
        $user->updateData();
    }
}