<?php

require_once __DIR__ . "/VillageBuildingDto.php";
require_once __DIR__ . "/VillageUpgradeDto.php";
require_once __DIR__ . "/VillageUpgradeSetDto.php";
require_once __DIR__ . "/VillageBuildingConfig.php";
require_once __DIR__ . "/VillageUpgradeConfig.php";
require_once __DIR__ . "/VillageBuilding.php";
require_once __DIR__ . "/VillageUpgrade.php";
require_once __DIR__ . "/VillageBuildingConfigData.php";
require_once __DIR__ . "/VillageUpgradeConfigData.php";

class VillageUpgradeManager {
    const UPGRADE_ACTIVATION_TIME_DAYS = 3; // time in days to activate an upgrade
    const BOOST_CONSTRUCTION_POINT_COST_PER_HOUR = 5; // cost in points her hour saved
    const BOOST_RESEARCH_POINT_COST_PER_HOUR = 5; // cost in points her hour saved

    public static $UPGRADE_CONFIGS = array();
    public static $BUILDING_CONFIGS = array();

    public static function initialize() {
        if (self::$UPGRADE_CONFIGS) {
            return;
        }
        $upgrade_array = [];
        foreach (VillageUpgradeConfig::UPGRADE_KEYS as $key) {
            $upgrade_array[$key] = new VillageUpgradeConfigData(
                key: $key,
                name: VillageUpgradeConfig::UPGRADE_NAMES[$key],
                tier: VillageUpgradeConfig::UPGRADE_TIERS[$key],
                description: VillageUpgradeConfig::UPGRADE_DESCRIPTIONS[$key],
                materials_research_cost: VillageUpgradeConfig::UPGRADE_RESEARCH_COST[$key][WarManager::RESOURCE_MATERIALS],
                food_research_cost: VillageUpgradeConfig::UPGRADE_RESEARCH_COST[$key][WarManager::RESOURCE_FOOD],
                wealth_research_cost: VillageUpgradeConfig::UPGRADE_RESEARCH_COST[$key][WarManager::RESOURCE_WEALTH],
                research_time: VillageUpgradeConfig::UPGRADE_RESEARCH_TIME[$key],
                materials_upkeep: VillageUpgradeConfig::UPGRADE_UPKEEP[$key][WarManager::RESOURCE_MATERIALS],
                food_upkeep: VillageUpgradeConfig::UPGRADE_UPKEEP[$key][WarManager::RESOURCE_FOOD],
                wealth_upkeep: VillageUpgradeConfig::UPGRADE_UPKEEP[$key][WarManager::RESOURCE_WEALTH],
                research_requirements: VillageUpgradeConfig::UPGRADE_RESEARCH_REQUIREMENTS[$key],
                effects: VillageUpgradeConfig::UPGRADE_EFFECTS[$key],
            );
        }
        self::$UPGRADE_CONFIGS = $upgrade_array;
        $building_array = [];
        foreach (VillageBuildingConfig::BUILDING_KEYS as $key) {
            $building_array[$key] = new VillageBuildingConfigData(
                key: $key,
                name: VillageBuildingConfig::BUILDING_NAMES[$key],
                description: VillageBuildingConfig::BUILDING_DESCRIPTION[$key],
                phrase: VillageBuildingConfig::BUILDING_PHRASE[$key],
                background_image: VillageBuildingConfig::BUILDING_BACKGROUND_IMAGE[$key],
                max_healths: VillageBuildingConfig::BUILDING_MAX_HEALTH[$key],
                construction_costs: VillageBuildingConfig::BUILDING_CONSTRUCTION_COST[$key],
                construction_times: VillageBuildingConfig::BUILDING_CONSTRUCTION_TIME[$key],
                upgrade_sets: VillageBuildingConfig::BUILDING_UPGRADE_SETS[$key],
            );
        }
        self::$BUILDING_CONFIGS = $building_array;
    }

    /**
     * @param System  $system
     * @param int $village_id
     * @return VillageBuilding[]
     */
    public static function getBuildingsForVillage(System $system, int $village_id): array {
        self::initialize();
        $buildings = [];
        $query = $system->db->query("
            SELECT * FROM `village_buildings` WHERE `village_id` = {$village_id}
        ");
        $building_data = $system->db->fetch_all($query);
        foreach ($building_data as $building) {
            $buildings[$building['key']] = new VillageBuilding(
                id: $building['id'],
                key: $building['key'],
                village_id: $building['village_id'],
                tier: $building['tier'],
                health: $building['health'],
                max_health: VillageBuildingConfig::BUILDING_MAX_HEALTH[$building['key']][$building['tier']],
                defense: $building['defense'],
                status: $building['status'],
                construction_progress: $building['construction_progress'],
                construction_progress_required: $building['construction_progress_required'],
                construction_progress_last_updated: $building['construction_progress_last_updated'],
                construction_boosted: $building['construction_boosted'],
                config_data: self::$BUILDING_CONFIGS[$building['key']],
            );
        }
        return $buildings;
    }

    /**
     * @param System  $system
     * @param int $village_id
     * @return VillageUpgrade[]
     */
    public static function getUpgradesForVillage(System $system, int $village_id): array {
        self::initialize();
        $upgrades = [];
        $query = $system->db->query("
            SELECT * FROM `village_upgrades` WHERE `village_id` = {$village_id}
        ");
        $upgrade_data = $system->db->fetch_all($query);
        foreach ($upgrade_data as $upgrade) {
            $upgrades[$upgrade['key']] = new VillageUpgrade(
                id: $upgrade['id'],
                key: $upgrade['key'],
                village_id: $upgrade['village_id'],
                status: $upgrade['status'],
                research_progress: $upgrade['research_progress'],
                research_progress_required: $upgrade['research_progress_required'],
                research_progress_last_updated: $upgrade['research_progress_last_updated'],
                research_boosted: $upgrade['research_boosted'],
                config_data: self::$UPGRADE_CONFIGS[$upgrade['key']],
            );
        }
        return $upgrades;
    }

    /**
     * @param System  $system
     * @param VillageUpgrade[] $upgrades
     * @return array
     */
    public static function initializeEffectsForVillage(System $system, array $upgrades): array {
        self::initialize();
        // initialize array with defaults
        $effects = [];
        foreach (VillageUpgradeConfig::UPGRADE_EFFECTS_LIST as $key) {
            $effects[$key] = 0;
        }
        // go through all active upgrades and add effects to array
        foreach ($upgrades as $upgrade) {
            if ($upgrade->status == VillageUpgradeConfig::UPGRADE_STATUS_ACTIVE) {
                foreach ($upgrade->getEffects() as $key => $value) {
                    $effects[$key] += $value;
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
        self::initialize();
        $buildingDtos = [];
        foreach ($village->buildings as $building) {
            $upgrade_sets = [];
            foreach ($building->getUpgradeSets() as $upgrade_set_key) {
                $upgrades = [];
                foreach (VillageBuildingConfig::UPGRADE_SET_UPGRADES[$upgrade_set_key] as $upgrade_key) {
                    $upgrade_config_data = VillageUpgradeManager::$UPGRADE_CONFIGS[$upgrade_key];
                    $upgrade = $village->upgrades[$upgrade_key] ?? null;
                    $upgrades[] = new VillageUpgradeDto(
                        id: $upgrade ? $upgrade->id : null,
                        key: $upgrade_key,
                        village_id: $village->village_id,
                        status: $upgrade ? $upgrade->status : VillageUpgradeConfig::UPGRADE_STATUS_LOCKED,
                        research_progress: $upgrade ? $upgrade->research_progress : null,
                        research_progress_required: $upgrade ? $upgrade->research_progress_required : null,
                        research_progress_last_updated: $upgrade ? $upgrade->research_progress_last_updated : null,
                        research_boosted: $upgrade ? $upgrade->research_boosted : false,
                        research_time_remaining: $upgrade ? System::TimeRemaining($upgrade->research_progress_required - $upgrade->research_progress, format: "long", include_seconds: false, include_minutes: false) : '',
                        name: $upgrade_config_data->getName(),
                        tier: $upgrade_config_data->getTier(),
                        description: $upgrade_config_data->getDescription(),
                        materials_research_cost: ($upgrade && $upgrade->research_progress !== null) ? 0 : $upgrade_config_data->getResearchCostMaterials(),
                        food_research_cost: ($upgrade && $upgrade->research_progress !== null) ? 0 : $upgrade_config_data->getResearchCostFood(),
                        wealth_research_cost: ($upgrade && $upgrade->research_progress !== null) ? 0 : $upgrade_config_data->getResearchCostWealth(),
                        research_time: ($upgrade && $upgrade->research_progress !== null) ? round($upgrade_config_data->getResearchTime() * (($upgrade->research_progress_required - $upgrade->research_progress) / $upgrade->research_progress_required)) : $upgrade_config_data->getResearchTime(),
                        materials_upkeep: $upgrade_config_data->getUpkeepMaterials(),
                        food_upkeep: $upgrade_config_data->getUpkeepFood(),
                        wealth_upkeep: $upgrade_config_data->getUpkeepWealth(),
                        research_requirements: $upgrade_config_data->getResearchRequirements(),
                        effects: $upgrade_config_data->getEffects(),
                        requirements_met: VillageUpgradeManager::checkResearchRequirementsMet($village, $upgrade_key),
                    );
                }
                $upgrade_sets[] = new VillageUpgradeSetDto(
                    key: $upgrade_set_key,
                    name: VillageBuildingConfig::UPGRADE_SET_NAMES[$upgrade_set_key],
                    description: VillageBuildingConfig::UPGRADE_SET_DESCRIPTIONS[$upgrade_set_key],
                    upgrades: $upgrades,
                );
            }
            $buildingDtos[] = new VillageBuildingDto(
                id: $building->id,
                key: $building->key,
                village_id: $building->village_id,
                tier: $building->tier,
                health: $building->health,
                max_health: $building->max_health,
                defense: $building->defense,
                status: $building->status,
                construction_progress: $building->construction_progress,
                construction_progress_required: $building->construction_progress_required,
                construction_progress_last_updated: $building->construction_progress_last_updated,
                construction_boosted: $building->construction_boosted,
                name: $building->getName(),
                description: $building->getDescription(),
                phrase: $building->getPhrase(),
                background_image: $building->getBackgroundImage(),
                materials_construction_cost: $building->construction_progress != null ? 0 : $building->getConstructionCostMaterials($building->tier + 1),
                food_construction_cost: $building->construction_progress != null ? 0 : $building->getConstructionCostFood($building->tier + 1),
                wealth_construction_cost: $building->construction_progress != null ? 0 : $building->getConstructionCostWealth($building->tier + 1),
                construction_time: $building->construction_progress != null ? round($building->getConstructionTime($building->tier + 1) * (($building->construction_progress_required - $building->construction_progress) / $building->construction_progress_required)) : $building->getConstructionTime($building->tier + 1),
                construction_time_remaining: System::TimeRemaining($building->construction_progress_required - $building->construction_progress, format: "long", include_seconds: false, include_minutes: false),
                upgrade_sets: $upgrade_sets,
                requirements_met: VillageUpgradeManager::checkConstructionRequirementsMet($village, $building->key, $building->tier + 1),
            );
        }
        return $buildingDtos;
    }

    /**
     * @param Village $village
     * @return array<VillageUpgrade>
     */
    public static function checkResearchRequirementsMet(Village $village, string $upgrade_key): bool {
        self::initialize();
        if (isset(VillageUpgradeConfig::UPGRADE_RESEARCH_REQUIREMENTS[$upgrade_key])) {
            $requirements = VillageUpgradeConfig::UPGRADE_RESEARCH_REQUIREMENTS[$upgrade_key];
            // check if the village has the required buildings
            if (isset($requirements[VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS])) {
                foreach ($requirements[VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS] as $building_key => $tier) {
                    if ($village->buildings[$building_key]->tier < $tier) {
                        return false;
                    }
                }
            }
            // check if the village has the required upgrades
            if (isset($requirements[VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES])) {
                foreach ($requirements[VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES] as $required_upgrade_key) {
                    $valid_status = [
                        VillageUpgradeConfig::UPGRADE_STATUS_ACTIVE,
                        VillageUpgradeConfig::UPGRADE_STATUS_ACTIVATING,
                        VillageUpgradeConfig::UPGRADE_STATUS_UNLOCKED,
                        VillageUpgradeConfig::UPGRADE_STATUS_INACTIVE
                    ];
                    if (!isset($village->upgrades[$required_upgrade_key]) || !in_array($village->upgrades[$required_upgrade_key]->status, $valid_status)) {
                        return false;
                    }
                }
            }
        }
        // check if village HQ is at least the same tier as the effective tier for the upgrade
        if ($village->buildings[VillageBuildingConfig::BUILDING_VILLAGE_HQ]->tier < VillageUpgradeManager::$UPGRADE_CONFIGS[$upgrade_key]->getTier()) {
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
        self::initialize();
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
     * @throws RuntimeException
     * @return string
     */
    public static function beginConstruction(System $system, Village $village, $building_key): string {
        self::initialize();
        // get construction costs and time
        $materials_cost = VillageBuildingConfig::BUILDING_CONSTRUCTION_COST[$building_key][$village->buildings[$building_key]->tier + 1][WarManager::RESOURCE_MATERIALS];
        $food_cost = VillageBuildingConfig::BUILDING_CONSTRUCTION_COST[$building_key][$village->buildings[$building_key]->tier + 1][WarManager::RESOURCE_FOOD];
        $wealth_cost = VillageBuildingConfig::BUILDING_CONSTRUCTION_COST[$building_key][$village->buildings[$building_key]->tier + 1][WarManager::RESOURCE_WEALTH];
        $progress_required = VillageBuildingConfig::BUILDING_CONSTRUCTION_TIME[$building_key][$village->buildings[$building_key]->tier + 1] * 86400;
        // check if requirements met
        if (!VillageUpgradeManager::checkConstructionRequirementsMet($village, $building_key, $village->buildings[$building_key]->tier + 1)) {
            throw new RuntimeException("Construction requirements not met!");
        }
        // check if another building is already under construction
        foreach ($village->buildings as $building) {
            if ($building->status == VillageBuildingConfig::BUILDING_STATUS_UPGRADING) {
                throw new RuntimeException("Another building is already under construction!");
            }
        }
        // check if has previous progress
        if (!isset($village->buildings[$building_key]->construction_progress)) {
            // check if the village has enough resources
            if ($village->resources[WarManager::RESOURCE_MATERIALS] < $materials_cost || $village->resources[WarManager::RESOURCE_FOOD] < $food_cost || $village->resources[WarManager::RESOURCE_WEALTH] < $wealth_cost) {
                throw new RuntimeException("Not enough resources!");
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
            $system->db->query("UPDATE `village_buildings` SET `construction_progress` = 0, `construction_progress_required` = {$progress_required}, `construction_progress_last_updated` = " . time() . ", `status` = '" . VillageBuildingConfig::BUILDING_STATUS_UPGRADING . "' WHERE `id` = {$village->buildings[$building_key]->id}");
            return "Construction started for " . VillageBuildingConfig::BUILDING_NAMES[$building_key] . "!";
        }
    }

    /**
     * @param System $system
     * @param Village $village
     * @param string $building_key
     * @throws RuntimeException
     * @return string
     */
    public static function cancelConstruction(System $system, Village $village, $building_id): string {
        self::initialize();
        // check if the building is under construction
        if ($village->buildings[$building_id]->status != VillageBuildingConfig::BUILDING_STATUS_UPGRADING) {
            throw new RuntimeException("Building is not under construction!");
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
     * @throws RuntimeException
     * @return bool
     */
    public static function beginResearch(System $system, Village $village, $upgrade_key): string {
        self::initialize();
        // get research costs and time
        $materials_cost = VillageUpgradeConfig::UPGRADE_RESEARCH_COST[$upgrade_key][WarManager::RESOURCE_MATERIALS];
        $food_cost = VillageUpgradeConfig::UPGRADE_RESEARCH_COST[$upgrade_key][WarManager::RESOURCE_FOOD];
        $wealth_cost = VillageUpgradeConfig::UPGRADE_RESEARCH_COST[$upgrade_key][WarManager::RESOURCE_WEALTH];
        $progress_required = VillageUpgradeConfig::UPGRADE_RESEARCH_TIME[$upgrade_key] * 86400;
        // check if requirements met
        if (!VillageUpgradeManager::checkResearchRequirementsMet($village, $upgrade_key)) {
            throw new RuntimeException("Research requirements not met!");
        }
        // check if another upgrade is already under research
        foreach ($village->upgrades as $upgrade) {
            if ($upgrade->status == VillageUpgradeConfig::UPGRADE_STATUS_RESEARCHING) {
                throw new RuntimeException("Another upgrade is already under research!");
            }
        }
        // check if has previous progress
        if (!isset($village->upgrades[$upgrade_key]->research_progress)) {
            // check if village has enough resources
            if ($village->resources[WarManager::RESOURCE_MATERIALS] < $materials_cost || $village->resources[WarManager::RESOURCE_FOOD] < $food_cost || $village->resources[WarManager::RESOURCE_WEALTH] < $wealth_cost) {
                throw new RuntimeException("Not enough resources!");
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
            $system->db->query("INSERT INTO `village_upgrades`
                (`village_id`, `key`, `status`, `research_progress`, `research_progress_required`, `research_progress_last_updated`)
                VALUES ({$village->village_id}, '{$upgrade_key}', '" . VillageUpgradeConfig::UPGRADE_STATUS_RESEARCHING . "', 0, {$progress_required}, " . time() . ")
            ");
            $upgrade_id = $system->db->last_insert_id;
            $new_upgrade = new VillageUpgrade(
                id: $upgrade_id,
                key: $upgrade_key,
                village_id: $village->village_id,
                status: VillageUpgradeConfig::UPGRADE_STATUS_RESEARCHING,
                research_progress: 0,
                research_progress_required: $progress_required,
                research_progress_last_updated: time(),
                research_boosted: false,
                config_data: self::$UPGRADE_CONFIGS[$upgrade_key],
            );
            $village->upgrades[$upgrade_key] = $new_upgrade;
            return "Research started for " . VillageUpgradeConfig::UPGRADE_NAMES[$upgrade_key] . "!";
        }
    }

/**
     * @param System $system
     * @param Village $village
     * @param string $upgrade_key
     * @throws RuntimeException
     * @return string
     */
    public static function cancelResearch(System $system, Village $village, $upgrade_key): string {
        self::initialize();
        // check if the upgrade is under research
        if ($village->upgrades[$upgrade_key]->status != VillageUpgradeConfig::UPGRADE_STATUS_RESEARCHING) {
            throw new RuntimeException("Upgrade is not under research!");
        }
        // stop research but maintain progress
        $village->upgrades[$upgrade_key]->status = VillageUpgradeConfig::UPGRADE_STATUS_LOCKED;
        $village->upgrades[$upgrade_key]->research_progress_last_updated = time();
        $system->db->query("UPDATE `village_upgrades` SET `status` = '" . VillageUpgradeConfig::UPGRADE_STATUS_LOCKED . "', `research_progress_last_updated` = " . time() . " WHERE `id` = {$village->upgrades[$upgrade_key]->id}");
        return "Research cancelled for " . VillageUpgradeConfig::UPGRADE_NAMES[$upgrade_key] . "!";
    }

    /**
     * @param System $system
     * @param Village $village
     * @param string $upgrade_key
     * @return string
     */
    public static function activateUpgrade(System $system, Village $village, $upgrade_key): string {
        self::initialize();
        // check if upgrade is inactive
        if (!isset($village->upgrades[$upgrade_key]) || $village->upgrades[$upgrade_key]->status != VillageUpgradeConfig::UPGRADE_STATUS_INACTIVE) {
            return "Invalid upgrade selection.";
        }
        // check if activation requirements are met
        if (!VillageUpgradeManager::checkActivationRequirements($system, $village, $upgrade_key)) {
            return "Activation requirements not met!";
        }
        $village->upgrades[$upgrade_key]->status = VillageUpgradeConfig::UPGRADE_STATUS_ACTIVATING;
        $village->upgrades[$upgrade_key]->research_progress = 0;
        $village->upgrades[$upgrade_key]->research_progress_required = VillageUpgradeManager::UPGRADE_ACTIVATION_TIME_DAYS * 86400;
        $village->upgrades[$upgrade_key]->research_progress_last_updated = time();
        $system->db->query("UPDATE `village_upgrades` SET `status` = '" . VillageUpgradeConfig::UPGRADE_STATUS_ACTIVATING . "', `research_progress` = 0, `research_progress_required` = " . VillageUpgradeManager::UPGRADE_ACTIVATION_TIME_DAYS * 86400 . ", `research_progress_last_updated` = " . time() . " WHERE `id` = {$village->upgrades[$upgrade_key]->id}");
        return "Activation started for " . VillageUpgradeConfig::UPGRADE_NAMES[$upgrade_key] . "!";
    }

    /**
     * @param System $system
     * @param Village $village
     * @param string $upgrade_key
     * @return string
     */
    public static function cancelActivation(System $system, Village $village, $upgrade_key): string {
        self::initialize();
        // check if the upgrade is under activation
        if ($village->upgrades[$upgrade_key]->status != VillageUpgradeConfig::UPGRADE_STATUS_ACTIVATING) {
            return "Upgrade is not under activation!";
        }
        // stop activation but maintain progress
        $village->upgrades[$upgrade_key]->status = VillageUpgradeConfig::UPGRADE_STATUS_INACTIVE;
        $village->upgrades[$upgrade_key]->research_progress = null;
        $village->upgrades[$upgrade_key]->research_progress_required = null;
        $village->upgrades[$upgrade_key]->research_progress_last_updated = time();
        $system->db->query("UPDATE `village_upgrades` SET `status` = '" . VillageUpgradeConfig::UPGRADE_STATUS_INACTIVE . "', `research_progress` = NULL, `research_progress_required` = NULL, `research_progress_last_updated` = " . time() . " WHERE `id` = {$village->upgrades[$upgrade_key]->id}");
        return "Activation cancelled for " . VillageUpgradeConfig::UPGRADE_NAMES[$upgrade_key] . "!";
    }

    /**
     * @param System $system
     * @param Village $village
     * @param string $upgrade_key
     * @return string
     */
    public static function deactivateUpgrade(System $system, Village $village, $upgrade_key): string {
        self::initialize();
        // check if upgrade is active
        if (!isset($village->upgrades[$upgrade_key]) || $village->upgrades[$upgrade_key]->status != VillageUpgradeConfig::UPGRADE_STATUS_ACTIVE) {
            return "Invalid upgrade selection.";
        }
        // check if deactivation requirements are met
        if (!VillageUpgradeManager::checkDeactivationRequirements($system, $village, $upgrade_key)) {
            return "Deactivation requirements not met!";
        }
        $village->upgrades[$upgrade_key]->status = VillageUpgradeConfig::UPGRADE_STATUS_INACTIVE;
        $system->db->query("UPDATE `village_upgrades` SET `status` = '" . VillageUpgradeConfig::UPGRADE_STATUS_INACTIVE . "' WHERE `id` = {$village->upgrades[$upgrade_key]->id}");
        return "Upgrade deactivated for " . VillageUpgradeConfig::UPGRADE_NAMES[$upgrade_key] . "!";
    }

    /**
     * @param System $system
     * @param Village $village
     * @param string $upgrade_key
     * @return bool
     */
    public static function checkActivationRequirements(System $system, Village $village, $upgrade_key): bool {
        self::initialize();
        // check upgrade exists and is inactive
        if (!isset($village->upgrades[$upgrade_key]) || $village->upgrades[$upgrade_key]->status != VillageUpgradeConfig::UPGRADE_STATUS_INACTIVE) {
            return false;
        }
        // check if the village has the required buildings
        if (isset(VillageUpgradeConfig::UPGRADE_RESEARCH_REQUIREMENTS[$upgrade_key][VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS])) {
            foreach (VillageUpgradeConfig::UPGRADE_RESEARCH_REQUIREMENTS[$upgrade_key][VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS] as $building_key => $tier) {
                if ($village->buildings[$building_key]->tier < $tier) {
                    return false;
                }
            }
        }
        // check if the village has the required upgrades and that those upgrades are also active
        if (isset(VillageUpgradeConfig::UPGRADE_RESEARCH_REQUIREMENTS[$upgrade_key][VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES])) {
            foreach (VillageUpgradeConfig::UPGRADE_RESEARCH_REQUIREMENTS[$upgrade_key][VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES] as $required_upgrade_key) {
                if (!isset($village->upgrades[$required_upgrade_key]) || $village->upgrades[$required_upgrade_key]->status != VillageUpgradeConfig::UPGRADE_STATUS_ACTIVE) {
                    return false;
                }
                if ($village->upgrades[$required_upgrade_key]->status != VillageUpgradeConfig::UPGRADE_STATUS_ACTIVE) {
                    return false;
                }
            }
        }
        // check if village HQ is at least the same tier as the effective tier for the upgrade
        if ($village->buildings[VillageBuildingConfig::BUILDING_VILLAGE_HQ]->tier < $tier) {
            return false;
        }
        return true;
    }

    /**
     * @param System $system
     * @param Village $village
     * @param string $upgrade_key
     * @return bool
     */
    public static function checkDeactivationRequirements(System $system, Village $village, $upgrade_key): bool {
        self::initialize();
        // check upgrade exists and is active
        if (!isset($village->upgrades[$upgrade_key]) || $village->upgrades[$upgrade_key]->status != VillageUpgradeConfig::UPGRADE_STATUS_ACTIVE) {
            return false;
        }
        // loop through all upgrades and check if the upgrade is a requirement for any other active upgrade
        foreach ($village->upgrades as $upgrade) {
            if ($upgrade->status == VillageUpgradeConfig::UPGRADE_STATUS_ACTIVE) {
                if (isset($upgrade->getResearchRequirements()[VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES])) {
                    if (in_array($upgrade_key, $upgrade->getResearchRequirements()[VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES])) {
                        return false;
                    }
                }
            }
        }
        return true;
    }

    public static function getActiveUpkeepForBuilding(System $system, Village $village, $building_key): array {
        self::initialize();
        $upkeep = [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ];
        // get list of upgrade keys for the building based on upgrade sets
        $upgrade_keys = [];
        foreach (VillageBuildingConfig::BUILDING_UPGRADE_SETS[$building_key] as $upgrade_set_key) {
            $upgrade_keys = array_merge($upgrade_keys, VillageBuildingConfig::UPGRADE_SET_UPGRADES[$upgrade_set_key]);
        }
        // for each upgrade, if upgrade exists and is active, add upkeep to array
        foreach ($upgrade_keys as $upgrade_key) {
            if (isset($village->upgrades[$upgrade_key]) && ($village->upgrades[$upgrade_key]->status == VillageUpgradeConfig::UPGRADE_STATUS_ACTIVE || $village->upgrades[$upgrade_key]->status == VillageUpgradeConfig::UPGRADE_STATUS_ACTIVATING)) {
                $upkeep[WarManager::RESOURCE_MATERIALS] += VillageUpgradeConfig::UPGRADE_UPKEEP[$upgrade_key][WarManager::RESOURCE_MATERIALS];
                $upkeep[WarManager::RESOURCE_FOOD] += VillageUpgradeConfig::UPGRADE_UPKEEP[$upgrade_key][WarManager::RESOURCE_FOOD];
                $upkeep[WarManager::RESOURCE_WEALTH] += VillageUpgradeConfig::UPGRADE_UPKEEP[$upgrade_key][WarManager::RESOURCE_WEALTH];
            }
        }
        return $upkeep;
    }

    public static function getTotalUpkeepForVillage(System $system, Village $village): array {
        self::initialize();
        $upkeep = [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ];
        foreach ($village->buildings as $building) {
            $building_upkeep = VillageUpgradeManager::getActiveUpkeepForBuilding($system, $village, $building->key);
            $upkeep[WarManager::RESOURCE_MATERIALS] += $building_upkeep[WarManager::RESOURCE_MATERIALS];
            $upkeep[WarManager::RESOURCE_FOOD] += $building_upkeep[WarManager::RESOURCE_FOOD];
            $upkeep[WarManager::RESOURCE_WEALTH] += $building_upkeep[WarManager::RESOURCE_WEALTH];
        }
        return $upkeep;
    }

    /**
     * @param System $system
     * @param Village $village
     * @param string $building_key
     * @return int
     */
    public static function calcBoostConstructionCost(System $system, Village $village, $building_key): int {
        self::initialize();
        $remaining_construction_time = $village->buildings[$building_key]->construction_progress_required - $village->buildings[$building_key]->construction_progress;
        return ceil(VillageUpgradeManager::BOOST_CONSTRUCTION_POINT_COST_PER_HOUR * ($remaining_construction_time / 3600 / 2));
    }

    public static function calcBoostResearchCost(System $system, Village $village, $upgrade_key): int {
        self::initialize();
        $remaining_research_time = $village->upgrades[$upgrade_key]->research_progress_required - $village->upgrades[$upgrade_key]->research_progress;
        return ceil(VillageUpgradeManager::BOOST_RESEARCH_POINT_COST_PER_HOUR * ($remaining_research_time / 3600 / 2));
    }

    /**
     * @param System $system
     * @param Village $village
     * @param string $building_key
     * #throws RuntimeException
     * @return string
     */
    public static function boostConstruction(System $system, Village $village, string $building_key): string {
        self::initialize();
        // check if the building is under construction
        if ($village->buildings[$building_key]->status != VillageBuildingConfig::BUILDING_STATUS_UPGRADING) {
            throw new RuntimeException("Building is not under construction!");
        }
        // check if the village has enough points
        $points_cost = VillageUpgradeManager::calcBoostConstructionCost($system, $village, $building_key);
        if ($village->points < $points_cost) {
            throw new RuntimeException("Not enough points!");
        }
        // update village points
        $village->subtractPoints($points_cost);
        $village->updatePoints();
        // update building data
        $village->buildings[$building_key]->construction_boosted = true;
        $village->buildings[$building_key]->construction_progress_last_updated = time();
        // reduce remaining construction time required by 50%
        $remaining_construction_time = $village->buildings[$building_key]->construction_progress_required - $village->buildings[$building_key]->construction_progress;
        $village->buildings[$building_key]->construction_progress_required = $village->buildings[$building_key]->construction_progress + ($remaining_construction_time / 2);
        // update village buildings table
        $system->db->query("UPDATE `village_buildings` SET `construction_boosted` = 1, `construction_progress_last_updated` = " . time() . ", `construction_progress_required` = " . $village->buildings[$building_key]->construction_progress_required . " WHERE `id` = {$village->buildings[$building_key]->id}");
        return "Construction boosted for " . VillageBuildingConfig::BUILDING_NAMES[$building_key] . "!";
    }

    public static function boostResearch(System $system, Village $village, string $upgrade_key): string {
        self::initialize();
        // check if the upgrade exists and is under research
        if (isset($village->upgrades[$upgrade_key]) && $village->upgrades[$upgrade_key]->status != VillageUpgradeConfig::UPGRADE_STATUS_RESEARCHING) {
            throw new RuntimeException("Upgrade is not under research!");
        }
        // check if the village has enough points
        $points_cost = VillageUpgradeManager::calcBoostResearchCost($system, $village, $upgrade_key);
        if ($village->points < $points_cost) {
            throw new RuntimeException("Not enough points!");
        }
        // update village points
        $village->subtractPoints($points_cost);
        $village->updatePoints();
        // log expenditure in resource_logs table
        // update upgrade data
        $village->upgrades[$upgrade_key]->research_boosted = true;
        $village->upgrades[$upgrade_key]->research_progress_last_updated = time();
        // reduce remaining research time required by 50%
        $remaining_research_time = $village->upgrades[$upgrade_key]->research_progress_required - $village->upgrades[$upgrade_key]->research_progress;
        $village->upgrades[$upgrade_key]->research_progress_required = $village->upgrades[$upgrade_key]->research_progress + ($remaining_research_time / 2);
        // update village upgrades table
        $system->db->query("UPDATE `village_upgrades` SET `research_boosted` = 1, `research_progress_last_updated` = " . time() . ", `research_progress_required` = " . $village->upgrades[$upgrade_key]->research_progress_required . " WHERE `id` = {$village->upgrades[$upgrade_key]->id}");
        return "Research boosted for " . VillageUpgradeConfig::UPGRADE_NAMES[$upgrade_key] . "!";
    }
}