<?php

require_once __DIR__ . "/VillageBuildingDto.php";
require_once __DIR__ . "/VillageUpgradeDto.php";
require_once __DIR__ . "/VillageUpgradeSetDto.php";
require_once __DIR__ . "/VillageBuildingConfig.php";
require_once __DIR__ . "/VillageUpgradeConfig.php";

class VillageUpgradeManager {
    const UPGRADE_TOGGLE_COOLDOWN_DAYS = 3;

    /**
     * @param System  $system
     * @param int $village_id
     * @return VillageBuildingDto[]
     */
    public static function getBuildingsForVillage(System $system, int $village_id): array {
        $buildings = [];
        $query = $system->db->query("
            SELECT * FROM `village_buildings` WHERE `village_id` = {$village_id}
        ");
        $building_data = $system->db->fetch_all($query);
        foreach ($building_data as $building) {
            $buildings[$building['key']] = new VillageBuildingDto(
                id: $building['id'],
                key: $building['key'],
                village_id: $building['village_id'],
                tier: $building['tier'],
                health: $building['health'],
                status: $building['status'],
                construction_progress: $building['construction_progress'],
                construction_progress_required: $building['construction_progress_required'],
                construction_progress_last_updated: $building['construction_progress_last_updated'],
            );
        }
        return $buildings;
    }

    /**
     * @param System  $system
     * @param int $village_id
     * @return VillageUpgradeDto[]
     */
    public static function getUpgradesForVillage(System $system, int $village_id): array {
        $upgrades = [];
        $query = $system->db->query("
            SELECT * FROM `village_upgrades` WHERE `village_id` = {$village_id}
        ");
        $upgrade_data = $system->db->fetch_all($query);
        foreach ($upgrade_data as $upgrade) {
            $upgrades[$upgrade['key']] = new VillageUpgradeDto(
                id: $upgrade['id'],
                key: $upgrade['key'],
                village_id: $upgrade['village_id'],
                status: $upgrade['status'],
                research_progress: $upgrade['research_progress'],
                research_progress_required: $upgrade['research_progress_required'],
                research_progress_last_updated: $upgrade['research_progress_last_updated'],
            );
        }
        return $upgrades;
    }

    /**
     * @param System  $system
     * @param VillageUpgradeDto[] $upgrades
     * @return array
     */
    public static function initializeEffectsForVillage(System $system, array $upgrades): array {
        // initialize array with defaults
        $effects = [
            VillageUpgradeConfig::UPGRADE_EFFECT_FOOD_PRODUCTION => 0,
            VillageUpgradeConfig::UPGRADE_EFFECT_CONSTRUCTION_SPEED => 0,
            VillageUpgradeConfig::UPGRADE_EFFECT_RESEARCH_SPEED => 0,
            VillageUpgradeConfig::UPGRADE_EFFECT_MATERIALS_PRODUCTION => 0,
            VillageUpgradeConfig::UPGRADE_EFFECT_FOOD_PRODUCTION => 0,
            VillageUpgradeConfig::UPGRADE_EFFECT_WEALTH_PRODUCTION => 0,
            VillageUpgradeConfig::UPGRADE_EFFECT_CONSTRUCTION_SPEED => 0,
            VillageUpgradeConfig::UPGRADE_EFFECT_RESEARCH_SPEED => 0,
            VillageUpgradeConfig::UPGRADE_EFFECT_RESEARCH_T1_ENABLED => 0,
            VillageUpgradeConfig::UPGRADE_EFFECT_RESEARCH_T2_ENABLED => 0,
            VillageUpgradeConfig::UPGRADE_EFFECT_RESEARCH_T3_ENABLED => 0,
            VillageUpgradeConfig::UPGRADE_EFFECT_CONSTRUCTION_T1_ENABLED => 0,
            VillageUpgradeConfig::UPGRADE_EFFECT_CONSTRUCTION_T2_ENABLED => 0,
            VillageUpgradeConfig::UPGRADE_EFFECT_CONSTRUCTION_T3_ENABLED => 0,
            VillageUpgradeConfig::UPGRADE_EFFECT_UPGRADE_UPKEEP => 0,
            VillageUpgradeConfig::UPGRADE_EFFECT_BASE_STABILITY => 0,
            VillageUpgradeConfig::UPGRADE_EFFECT_MAX_STABILITY => 0,
            VillageUpgradeConfig::UPGRADE_EFFECT_TRAINING_SPEED => 0,
            VillageUpgradeConfig::UPGRADE_EFFECT_DOUBLE_BATTLE_XP_CHANCE => 0,
            VillageUpgradeConfig::UPGRADE_EFFECT_VILLAGE_REGEN => 0,
            VillageUpgradeConfig::UPGRADE_EFFECT_HEAL_ITEM_COST => 0,
            VillageUpgradeConfig::UPGRADE_EFFECT_WAR_ACTION_COST => 0,
            VillageUpgradeConfig::UPGRADE_EFFECT_RAID_SPEED => 0,
            VillageUpgradeConfig::UPGRADE_EFFECT_RAID_DAMAGE => 0,
            VillageUpgradeConfig::UPGRADE_EFFECT_INFILTRATE_SPEED => 0,
            VillageUpgradeConfig::UPGRADE_EFFECT_OCCUPIED_BASE_STABILITY_REDUCTION => 0,
            VillageUpgradeConfig::UPGRADE_EFFECT_OCCUPIED_MAX_STABILITY_REDUCTION => 0,
            VillageUpgradeConfig::UPGRADE_EFFECT_PATROL_TIER_CHANCE => 0,
            VillageUpgradeConfig::UPGRADE_EFFECT_REINFORCE_SPEED => 0,
            VillageUpgradeConfig::UPGRADE_EFFECT_REINFORCE_HEAL => 0,
            VillageUpgradeConfig::UPGRADE_EFFECT_CASTLE_HP => 0,
            VillageUpgradeConfig::UPGRADE_EFFECT_TOWN_HP => 0,
            VillageUpgradeConfig::UPGRADE_EFFECT_RESOURCE_CAPACITY => 0,
            VillageUpgradeConfig::UPGRADE_EFFECT_BLOODLINE_CHANCE => 0,
            VillageUpgradeConfig::UPGRADE_EFFECT_DOUBLE_BATTLE_YEN_CHANCE => 0,
            VillageUpgradeConfig::UPGRADE_EFFECT_RAMEN_SET_ONE => 0,
            VillageUpgradeConfig::UPGRADE_EFFECT_RAMEN_DURATION => 0,
            VillageUpgradeConfig::UPGRADE_EFFECT_RAMEN_COST => 0,
            VillageUpgradeConfig::UPGRADE_EFFECT_MYSTERY_RAMEN_ENABLED => 0,
            VillageUpgradeConfig::UPGRADE_EFFECT_MYSTERY_RAMEN_CHANCE => 0,
        ];
        // go through all active upgrades and add effects to array
        foreach ($upgrades as $upgrade) {
            if ($upgrade->status == VillageUpgradeConfig::UPGRADE_STATUS_ACTIVE) {
                $upgrade_effects = VillageUpgradeConfig::UPGRADE_EFFECTS[$upgrade->key];
                foreach ($upgrade_effects as $key => $value) {
                    if (isset($effects[$key])) {
                        $effects[$key] += $value;
                    } else {
                        $effects[$key] = $value;
                    }
                }
            }
        }
        return $effects;
    }

    /**
     * @param System $system
     * @param Village $village
     * @return array<VillageBuildingDto>
     */
    public static function getBuildingUpgradesForDisplay(System $system, Village $village): array {
        foreach ($village->buildings as $building) {
            $building->name = VillageBuildingConfig::BUILDING_NAMES[$building->key];
            $building->materials_construction_cost = VillageBuildingConfig::BUILDING_CONSTRUCTION_COST[$building->key][$building->tier + 1][WarManager::RESOURCE_MATERIALS];
            $building->food_construction_cost = VillageBuildingConfig::BUILDING_CONSTRUCTION_COST[$building->key][$building->tier + 1][WarManager::RESOURCE_FOOD];
            $building->wealth_construction_cost = VillageBuildingConfig::BUILDING_CONSTRUCTION_COST[$building->key][$building->tier + 1][WarManager::RESOURCE_WEALTH];
            $building->construction_time = VillageBuildingConfig::BUILDING_CONSTRUCTION_TIME[$building->key][$building->tier + 1];
            $building->construction_time_remaining = System::TimeRemaining($building->construction_progress_required - $building->construction_progress, format: "long", include_seconds: false, include_minutes: false);
            $building->requirements_met = VillageUpgradeManager::checkConstructionRequirementsMet($village, $building->key, $building->tier + 1);
            foreach (VillageBuildingConfig::BUILDING_UPGRADE_SETS[$building->key] as $upgrade_set_key) {
                $upgrade_set = new VillageUpgradeSetDto(
                    key: $upgrade_set_key,
                    name: VillageBuildingConfig::UPGRADE_SET_NAMES[$upgrade_set_key],
                    description: VillageBuildingConfig::UPGRADE_SET_DESCRIPTIONS[$upgrade_set_key],
                );
                foreach (VillageBuildingConfig::UPGRADE_SET_UPGRADES[$upgrade_set_key] as $upgrade_key) {
                    $upgrade = new VillageUpgradeDto(
                        id: $village->upgrades[$upgrade_key]->id ?? null,
                        key: $upgrade_key,
                        village_id: $village->village_id,
                        status: $village->upgrades[$upgrade_key]->status ?? VillageUpgradeConfig::UPGRADE_STATUS_LOCKED,
                        research_progress: $village->upgrades[$upgrade_key]->research_progress ?? null,
                        research_progress_required: $village->upgrades[$upgrade_key]->research_progress_required ?? null,
                        name: VillageUpgradeConfig::UPGRADE_NAMES[$upgrade_key],
                        description: VillageUpgradeConfig::UPGRADE_DESCRIPTIONS[$upgrade_key],
                        materials_research_cost: VillageUpgradeConfig::UPGRADE_RESEARCH_COST[$upgrade_key][WarManager::RESOURCE_MATERIALS],
                        food_research_cost: VillageUpgradeConfig::UPGRADE_RESEARCH_COST[$upgrade_key][WarManager::RESOURCE_FOOD],
                        wealth_research_cost: VillageUpgradeConfig::UPGRADE_RESEARCH_COST[$upgrade_key][WarManager::RESOURCE_WEALTH],
                        research_time: VillageUpgradeConfig::UPGRADE_RESEARCH_TIME[$upgrade_key],
                        research_time_remaining: isset($village->upgrades[$upgrade_key]->research_progress_last_updated) ? System::TimeRemaining($village->upgrades[$upgrade_key]->research_progress_required - $village->upgrades[$upgrade_key]->research_progress, format: "long", include_seconds: false, include_minutes: false) : null,
                        food_upkeep: VillageUpgradeConfig::UPGRADE_UPKEEP[$upgrade_key][WarManager::RESOURCE_FOOD],
                        materials_upkeep: VillageUpgradeConfig::UPGRADE_UPKEEP[$upgrade_key][WarManager::RESOURCE_MATERIALS],
                        wealth_upkeep: VillageUpgradeConfig::UPGRADE_UPKEEP[$upgrade_key][WarManager::RESOURCE_WEALTH],
                        requirements_met: VillageUpgradeManager::checkResearchRequirementsMet($village, $upgrade_key),
                    );
                    $upgrade_set->upgrades[] = $upgrade;
                }
                $building->upgrade_sets[] = $upgrade_set;
            }
        }
        return $village->buildings;
    }

    /**
     * @param System $system
     * @param Village $village
     * @return array<VillageUpgradeDto>
     */
    public static function checkResearchRequirementsMet(Village $village, string $upgrade_key): bool {
        $effective_tier = 0;
        if (isset(VillageUpgradeConfig::UPGRADE_RESEARCH_REQUIREMENTS[$upgrade_key])) {
            $requirements = VillageUpgradeConfig::UPGRADE_RESEARCH_REQUIREMENTS[$upgrade_key];
            // check if the village has the required buildings
            if (isset($requirements[VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS])) {
                foreach ($requirements[VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS] as $building_key => $tier) {
                    if ($village->buildings[$building_key]->tier < $tier) {
                        return false;
                    }
                    // use highest building tier as the effective tier for the upgrade
                    if ($tier > $effective_tier) {
                        $effective_tier = $tier;
                    }
                }
            }
            // check if the village has the required upgrades
            if (isset($requirements[VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES])) {
                foreach ($requirements[VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES] as $required_upgrade_key) {
                    if ($village->upgrades[$required_upgrade_key]->status != VillageUpgradeConfig::UPGRADE_STATUS_ACTIVE) {
                        return false;
                    }
                }
            }
        }
        // check if village HQ is at least the same tier as the effective tier for the upgrade
        if ($village->buildings[VillageBuildingConfig::BUILDING_VILLAGE_HQ]->tier < $effective_tier) {
            return false;
        }
        return true;
    }

    /**
     * @param Village $village
     * @param string $upgrade_key
     * @return bool
     */
    public static function checkConstructionRequirementsMet(Village $village, string $building_key, int $tier): bool {
        // if not workshop, check that workshop is at least equal to the tier
        if ($building_key != VillageBuildingConfig::BUILDING_WORKSHOP && $village->buildings[VillageBuildingConfig::BUILDING_WORKSHOP]->tier < $tier) {
            return false;
        }
        // check if the previous tier is built
        if ($tier > 1 && $village->buildings[$building_key]->tier < $tier - 1) {
            return false;
        }
        return true;
    }

    /**
     * @param System $system
     * @param Village $village
     * @param string $building_key
     * @return string
     */
    public static function beginConstruction(System $system, Village $village, $building_key): string {
        // get construction costs and time
        $materials_cost = VillageBuildingConfig::BUILDING_CONSTRUCTION_COST[$building_key][$village->buildings[$building_key]->tier + 1][WarManager::RESOURCE_MATERIALS];
        $food_cost = VillageBuildingConfig::BUILDING_CONSTRUCTION_COST[$building_key][$village->buildings[$building_key]->tier + 1][WarManager::RESOURCE_FOOD];
        $wealth_cost = VillageBuildingConfig::BUILDING_CONSTRUCTION_COST[$building_key][$village->buildings[$building_key]->tier + 1][WarManager::RESOURCE_WEALTH];
        $progress_required = VillageBuildingConfig::BUILDING_CONSTRUCTION_TIME[$building_key][$village->buildings[$building_key]->tier + 1] * 86400;
        // check if requirements met
        if (!VillageUpgradeManager::checkConstructionRequirementsMet($village, $building_key, $village->buildings[$building_key]->tier + 1)) {
            return "Construction requirements not met!";
        }
        // check if another building is already under construction
        foreach ($village->buildings as $building) {
            if ($building->status == VillageBuildingConfig::BUILDING_STATUS_UPGRADING) {
                return "Another building is already under construction!";
            }
        }
        // check if has previous progress
        if (isset($village->buildings[$building_key]->construction_progress)) {
            // check if the village has enough resources
            if ($village->resources[WarManager::RESOURCE_MATERIALS] < $materials_cost || $village->resources[WarManager::RESOURCE_FOOD] < $food_cost || $village->resources[WarManager::RESOURCE_WEALTH] < $wealth_cost) {
                return "Not enough resources!";
            }
            // update village resources
            $village->subtractResource(WarManager::RESOURCE_MATERIALS, $materials_cost);
            $village->subtractResource(WarManager::RESOURCE_FOOD, $food_cost);
            $village->subtractResource(WarManager::RESOURCE_WEALTH, $wealth_cost);
            $village->updateResources();
            // log expenditure in resource_logs table
            $system->db->query("INSERT INTO `resource_logs`(`village_id`, `resource_id`, `type`, `quantity`, `time`) VALUES ({$village->village_id}, " . WarManager::RESOURCE_MATERIALS . ", " . VillageManager::RESOURCE_LOG_CONSTRUCTION_COST . ", {$materials_cost}, " . time() . ")");
            $system->db->query("INSERT INTO `resource_logs`(`village_id`, `resource_id`, `type`, `quantity`, `time`) VALUES ({$village->village_id}, " . WarManager::RESOURCE_FOOD . ", " . VillageManager::RESOURCE_LOG_CONSTRUCTION_COST . ", {$food_cost}, " . time() . ")");
            $system->db->query("INSERT INTO `resource_logs`(`village_id`, `resource_id`, `type`, `quantity`, `time`) VALUES ({$village->village_id}, " . WarManager::RESOURCE_WEALTH . ", " . VillageManager::RESOURCE_LOG_CONSTRUCTION_COST . ", {$wealth_cost}, " . time() . ")");
        }
        // update building data
        if (isset($village->buildings[$building_key]->construction_progress)) {
            $village->buildings[$building_key]->construction_progress_required = $progress_required;
            $village->buildings[$building_key]->construction_progress_last_updated = time();
            $village->buildings[$building_key]->status = VillageBuildingConfig::BUILDING_STATUS_UPGRADING;
            $system->db->query("UPDATE `village_buildings` SET `construction_progress_required` = {$progress_required}, `construction_progress_last_updated` = " . time() . ", `status` = '" . VillageBuildingConfig::BUILDING_STATUS_UPGRADING . "' WHERE `id` = {$village->buildings[$building_key]->id}");
            return "Construction resumed for " . VillageBuildingConfig::BUILDING_NAMES[$building_key] . "!";
        } else {
            $village->buildings[$building_key]->construction_progress = 0;
            $village->buildings[$building_key]->construction_progress_required = $progress_required;
            $village->buildings[$building_key]->construction_progress_last_updated = time();
            $village->buildings[$building_key]->status = VillageBuildingConfig::BUILDING_STATUS_UPGRADING;
            $system->db->query("INSERT INTO `village_buildings`
                (`village_id`, `key`, `tier`, `health`, `status`, `construction_progress`, `construction_progress_required`, `construction_progress_last_updated`)
                VALUES ({$village->village_id}, '{$building_key}', 0, 0, '" . VillageBuildingConfig::BUILDING_STATUS_DEFAULT . "', 0, {$progress_required}, " . time() . ")
            ");
            return "Construction started for " . VillageBuildingConfig::BUILDING_NAMES[$building_key] . "!";
        }
    }

    public static function cancelConstruction(System $system, Village $village, $building_id): string {
        // check if the building is under construction
        if ($village->buildings[$building_id]->status != VillageBuildingConfig::BUILDING_STATUS_UPGRADING) {
            return "Building is not under construction!";
        }
        // stop construction but maintain progress
        $village->buildings[$building_id]->status = VillageBuildingConfig::BUILDING_STATUS_DEFAULT;
        $village->buildings[$building_id]->progress_last_updated = time();
        $system->db->query("UPDATE `village_buildings` SET `status` = '" . VillageBuildingConfig::BUILDING_STATUS_DEFAULT . "', `construction_progress_last_updated` = " . time() . " WHERE `id` = {$village->buildings[$building_id]->id}");
        return "Construction cancelled for " . VillageBuildingConfig::BUILDING_NAMES[$building_id] . "!";
    }

    /**
     * @param Village $village
     * @param string $upgrade_key
     * @return bool
     */
    public static function beginResearch(System $system, Village $village, $upgrade_key): string {
        // get research costs and time
        $materials_cost = VillageUpgradeConfig::UPGRADE_RESEARCH_COST[$upgrade_key][WarManager::RESOURCE_MATERIALS];
        $food_cost = VillageUpgradeConfig::UPGRADE_RESEARCH_COST[$upgrade_key][WarManager::RESOURCE_FOOD];
        $wealth_cost = VillageUpgradeConfig::UPGRADE_RESEARCH_COST[$upgrade_key][WarManager::RESOURCE_WEALTH];
        $progress_required = VillageUpgradeConfig::UPGRADE_RESEARCH_TIME[$upgrade_key] * 86400;
        // check if requirements met
        if (!VillageUpgradeManager::checkResearchRequirementsMet($village, $upgrade_key)) {
            return "Research requirements not met!";
        }
        // check if another upgrade is already under research
        foreach ($village->upgrades as $upgrade) {
            if ($upgrade->status == VillageUpgradeConfig::UPGRADE_STATUS_RESEARCHING) {
                return "Another upgrade is already under research!";
            }
        }
        // check if has previous progress
        if (!isset($village->upgrades[$upgrade_key]->research_progress)) {
            // check if village has enough resources
            if ($village->resources[WarManager::RESOURCE_MATERIALS] < $materials_cost || $village->resources[WarManager::RESOURCE_FOOD] < $food_cost || $village->resources[WarManager::RESOURCE_WEALTH] < $wealth_cost) {
                return "Not enough resources!";
            }
            // update village resources
            $village->subtractResource(WarManager::RESOURCE_MATERIALS, $materials_cost);
            $village->subtractResource(WarManager::RESOURCE_FOOD, $food_cost);
            $village->subtractResource(WarManager::RESOURCE_WEALTH, $wealth_cost);
            $village->updateResources();
            // log expenditure in resource_logs table
            $system->db->query("INSERT INTO `resource_logs`(`village_id`, `resource_id`, `type`, `quantity`, `time`) VALUES ({$village->village_id}, " . WarManager::RESOURCE_MATERIALS . ", " . VillageManager::RESOURCE_LOG_RESEARCH_COST . ", {$materials_cost}, " . time() . ")");
            $system->db->query("INSERT INTO `resource_logs`(`village_id`, `resource_id`, `type`, `quantity`, `time`) VALUES ({$village->village_id}, " . WarManager::RESOURCE_FOOD . ", " . VillageManager::RESOURCE_LOG_RESEARCH_COST . ", {$food_cost}, " . time() . ")");
            $system->db->query("INSERT INTO `resource_logs`(`village_id`, `resource_id`, `type`, `quantity`, `time`) VALUES ({$village->village_id}, " . WarManager::RESOURCE_WEALTH . ", " . VillageManager::RESOURCE_LOG_RESEARCH_COST . ", {$wealth_cost}, " . time() . ")");
        }
        // update upgrade data
        if (isset($village->upgrades[$upgrade_key]->research_progress)) {
            $village->upgrades[$upgrade_key]->research_progress_required = $progress_required;
            $village->upgrades[$upgrade_key]->research_progress_last_updated = time();
            $village->upgrades[$upgrade_key]->status = VillageUpgradeConfig::UPGRADE_STATUS_RESEARCHING;
            $system->db->query("UPDATE `village_upgrades` SET `research_progress_required` = {$progress_required}, `research_progress_last_updated` = " . time() . ", `status` = '" . VillageUpgradeConfig::UPGRADE_STATUS_RESEARCHING . "' WHERE `id` = {$village->upgrades[$upgrade_key]->id}");
            return "Research resumed for " . VillageUpgradeConfig::UPGRADE_NAMES[$upgrade_key] . "!";
        } else {
            $village->upgrades[$upgrade_key]->research_progress = 0;
            $village->upgrades[$upgrade_key]->research_progress_required = $progress_required;
            $village->upgrades[$upgrade_key]->research_progress_last_updated = time();
            $village->upgrades[$upgrade_key]->status = VillageUpgradeConfig::UPGRADE_STATUS_RESEARCHING;
            $system->db->query("INSERT INTO `village_upgrades`
                (`village_id`, `key`, `status`, `research_progress`, `research_progress_required`, `research_progress_last_updated`)
                VALUES ({$village->village_id}, '{$upgrade_key}', '" . VillageUpgradeConfig::UPGRADE_STATUS_RESEARCHING . "', 0, {$progress_required}, " . time() . ")
            ");
            return "Research started for " . VillageUpgradeConfig::UPGRADE_NAMES[$upgrade_key] . "!";
        }
    }

    public static function cancelResearch(System $system, Village $village, $upgrade_key): string {
        // check if the upgrade is under research
        if ($village->upgrades[$upgrade_key]->status != VillageUpgradeConfig::UPGRADE_STATUS_RESEARCHING) {
            return "Upgrade is not under research!";
        }
        // stop research but maintain progress
        $village->upgrades[$upgrade_key]->status = VillageUpgradeConfig::UPGRADE_STATUS_LOCKED;
        $village->upgrades[$upgrade_key]->research_progress_last_updated = time();
        $system->db->query("UPDATE `village_upgrades` SET `status` = '" . VillageUpgradeConfig::UPGRADE_STATUS_LOCKED . "', `research_progress_last_updated` = " . time() . " WHERE `id` = {$village->upgrades[$upgrade_key]->id}");
        return "Research cancelled for " . VillageUpgradeConfig::UPGRADE_NAMES[$upgrade_key] . "!";
    }
}