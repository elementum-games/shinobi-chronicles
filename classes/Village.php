<?php

require_once __DIR__ . '/SpecialMission.php';
require_once __DIR__ . '/../classes/village/VillageRelation.php';
require_once __DIR__ . '/../classes/village/VillagePolicy.php';
require_once __DIR__ . '/../classes/village/VillageUpgradeManager.php';
require_once __DIR__ . '/../classes/village/VillageBuildingConfig.php';
require_once __DIR__ . '/../classes/village/VillageUpgradeConfig.php';

class Village {
    public System $system;
    public TravelCoords $coords;

    public int $village_id;
    public string $name;
    public int $points;
    public int $monthly_points;
    public int $leader;
    public int $map_location_id;
    public int $region_id;
    public string $kage_name;
    public array $resources = [];
    /**
     * @var VillageRelation
     */
    public array $relations = [];
    public int $policy_id = 0;
    public VillagePolicy $policy;
    /**
     * @var VillageBuilding[]
     */
    public array $buildings = [];
    /**
     * @var VillageUpgrade[]
     */
    public array $upgrades = [];
    public array $active_upgrade_effects = [];
    public float $construction_speed = 1; // corresponds to 1 per second
    public float $research_speed = 1; // corresponds to 1 per second
    public int $resource_capacity = VillageManager::MAX_RESOURCE_CAPACITY;
    public int $research_score = 0;
    public int $construction_score = 0;

    // to-do: we should restructure how village data is being saved
    // player village should reference the village ID and this constructor should get row by ID
    public function __construct($system, $village = '', array $village_row = []) {
        $this->system = $system;
        // new constructor logic
        if (!empty($village_row)) {
            foreach ($village_row as $key => $value) {
                if ($key == 'resources') {
                    $this->$key = json_decode($value, true);
                } else {
                    $this->$key = $value;
                }
            }
            $this->kage_name = VillageManager::KAGE_NAMES[$this->village_id];
            $this->coords = VillageManager::getLocation($this->system, $this->village_id);
            $this->relations = VillageManager::getRelationsForVillage($this->system, $this->village_id);
            $this->policy = new VillagePolicy($this->policy_id);
            $this->buildings = VillageUpgradeManager::getBuildingsForVillage($this->system, $this->village_id);
            $this->upgrades = VillageUpgradeManager::getUpgradesForVillage($this->system, $this->village_id);
            $this->active_upgrade_effects = VillageUpgradeManager::initializeEffectsForVillage($this->system, $this->upgrades);
            $this->construction_speed += ($this->active_upgrade_effects[VillageUpgradeConfig::UPGRADE_EFFECT_CONSTRUCTION_SPEED] / 100);
            $this->research_speed += ($this->active_upgrade_effects[VillageUpgradeConfig::UPGRADE_EFFECT_RESEARCH_SPEED] / 100);
            $this->resource_capacity += $this->active_upgrade_effects[VillageUpgradeConfig::UPGRADE_EFFECT_RESOURCE_CAPACITY];
            if ($this->policy->construction_speed > 0) {
                $result = $system->db->query("SELECT `village_id`, `construction_score` FROM `villages`");
                $village_scores = $system->db->fetch_all($result);
                $num_villages = 0;
                foreach ($village_scores as $score) {
                    if ($score['village_id'] != $this->village_id && $score['construction_score'] > $this->construction_score) {
                        $num_villages++;
                    }
                }
                $this->construction_speed += ($num_villages * $this->policy->construction_speed) / 100;
            }
            if ($this->policy->research_speed > 0) {
                $result = $system->db->query("SELECT `village_id`, `research_score` FROM `villages`");
                $village_scores = $system->db->fetch_all($result);
                $num_villages = 0;
                foreach ($village_scores as $score) {
                    if ($score['village_id'] != $this->village_id && $score['research_score'] > $this->research_score) {
                        $num_villages++;
                    }
                }
                $this->research_speed += ($num_villages * $this->policy->research_speed) / 100;
            }
        }
        // updated legacy constructor logic
        else {
            $this->name = $village;
            $this->getVillageData();
            $this->kage_name = VillageManager::KAGE_NAMES[$this->village_id];
            $this->coords = VillageManager::getLocation($this->system, $this->village_id);
            $this->relations = VillageManager::getRelationsForVillage($this->system, $this->village_id);
            $this->policy = new VillagePolicy($this->policy_id);
            $this->buildings = VillageUpgradeManager::getBuildingsForVillage($this->system, $this->village_id);
            $this->upgrades = VillageUpgradeManager::getUpgradesForVillage($this->system, $this->village_id);
            $this->active_upgrade_effects = VillageUpgradeManager::initializeEffectsForVillage($this->system, $this->upgrades);
            $this->construction_speed += $this->policy->construction_speed + $this->active_upgrade_effects[VillageUpgradeConfig::UPGRADE_EFFECT_CONSTRUCTION_SPEED];
            $this->research_speed += $this->policy->research_speed + $this->active_upgrade_effects[VillageUpgradeConfig::UPGRADE_EFFECT_RESEARCH_SPEED];
            if ($this->policy->construction_speed > 0) {
                $result = $system->db->query("SELECT `village_id`, `construction_score` FROM `villages`");
                $village_scores = $system->db->fetch_all($result);
                $num_villages = 0;
                foreach ($village_scores as $score) {
                    if ($score['village_id'] != $this->village_id && $score['construction_score'] > $this->construction_score) {
                        $num_villages++;
                    }
                }
                $this->construction_speed += ($num_villages * $this->policy->construction_speed) / 100;
            }
            if ($this->policy->research_speed > 0) {
                $result = $system->db->query("SELECT `village_id`, `research_score` FROM `villages`");
                $village_scores = $system->db->fetch_all($result);
                $num_villages = 0;
                foreach ($village_scores as $score) {
                    if ($score['village_id'] != $this->village_id && $score['research_score'] > $this->research_score) {
                        $num_villages++;
                    }
                }
                $this->research_speed += ($num_villages * $this->policy->research_speed) / 100;
            }
        }
    }

    private function getVillageData()
    {
        $result = $this->system->db->query(
            "SELECT * FROM `villages`
                WHERE `villages`.`name`='{$this->name}'"
        );
        $result = $this->system->db->fetch($result);
        $this->region_id = $result['region_id'];
        $this->village_id = $result['village_id'];
        $this->points = $result['points'];
        $this->monthly_points = $result['points'];
        $this->resources = json_decode($result['resources'], true);
        $this->policy_id = $result['policy_id'];
        $this->leader = $result['leader'];
        $this->map_location_id = $result['map_location_id'];
    }

    public function addResource(int $resource_id, int $quantity) {
        if (!empty($this->resources[$resource_id])) {
            $this->resources[$resource_id] += $quantity;
        } else {
            $this->resources[$resource_id] = $quantity;
        }
    }

    public function subtractResource(int $resource_id, int $quantity): int {
        $change = 0;
        // if enough resources for full cost
        if (!empty($this->resources[$resource_id]) && $this->resources[$resource_id] >= $quantity) {
            $change = $quantity;
            $this->resources[$resource_id] -= $quantity;
        } else {
            // if less resources than cost
            if (!empty($this->resources[$resource_id])) {
                $change = $this->resources[$resource_id];
                $this->resources[$resource_id] = 0;
            } else {
                $change = 0;
                $this->resources[$resource_id] = 0;
            }
        }
        return $change;
    }

    public function updateResources(bool $run_query = true): string {
        $resources = json_encode($this->resources);
        $query = "UPDATE `villages` SET `resources` = '{$resources}' WHERE `village_id` = {$this->village_id}";
        if ($run_query) {
            $this->system->db->query("UPDATE `villages` SET `resources` = '{$resources}' WHERE `village_id` = {$this->village_id}");
        }
        return $query;
    }

    public function isAlly(int $target_village_id): bool {
        if ($target_village_id == $this->village_id) {
            return true;
        }
        return ($this->relations[$target_village_id]->relation_type == VillageRelation::RELATION_ALLIANCE);
    }
    public function isEnemy(int $target_village_id): bool {
        if ($target_village_id == $this->village_id) {
            return false;
        }
        return ($this->relations[$target_village_id]->relation_type == VillageRelation::RELATION_WAR);
    }
    public function isNeutral(int $target_village_id): bool {
        if ($target_village_id == $this->village_id) {
            return false;
        }
        return ($this->relations[$target_village_id]->relation_type == VillageRelation::RELATION_NEUTRAL);
    }
    public function subtractPoints(int $points): int {
        $change = 0;
        if ($this->points >= $points) {
            $change = $points;
            $this->points -= $points;
        } else {
            $change = $this->points;
            $this->points = 0;
        }
        return $change;
    }
    public function updatePoints(bool $run_query = true): string {
        $query = "UPDATE `villages` SET `points` = {$this->points}, `monthly_points` = {$this->monthly_points} WHERE `village_id` = {$this->village_id}";
        if ($run_query) {
            $this->system->db->query("UPDATE `villages` SET `points` = {$this->points} WHERE `village_id` = {$this->village_id}");
        }
        return $query;
    }
}
