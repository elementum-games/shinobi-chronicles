<?php

require_once __DIR__ . "/VillageBuildingDto.php";
require_once __DIR__ . "/VillageUpgradeDto.php";
require_once __DIR__ . "/BuildingConfig.php";
require_once __DIR__ . "/UpgradeConfig.php";

class VillageUpgradeManager {
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
            $buildings[] = new VillageBuildingDto(
                id: $building['id'],
                building_id: $building['building_id'],
                village_id: $building['village_id'],
                tier: $building['tier'],
                health: $building['health'],
                status: $building['status'],
                construction_progress: $building['construction_progress'],
                construction_progress_required: $building['construction_progress_required'],
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
            $upgrades[] = new VillageUpgradeDto(
                id: $upgrade['id'],
                key: $upgrade['key'],
                village_id: $upgrade['village_id'],
                status: $upgrade['status'],
                research_progress: $upgrade['research_progress'],
                research_progress_required: $upgrade['research_progress_required'],
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
            UpgradeConfig::UPGRADE_EFFECT_FOOD_PRODUCTION => 0,
        ];
        // go through all active upgrades and add effects to array
        foreach ($upgrades as $upgrade) {
            $upgrade_effects = self::getEffectsByUpgradekey($upgrade->key);
            foreach ($upgrade_effects as $key => $value) {
                if (isset($effects[$key])) {
                    $effects[$key] += $value;
                } else {
                    $effects[$key] = $value;
                }
            }
        }
        return $effects;
    }

    /**
     * @param string $key
     * @return array
     */
    public static function getEffectsByUpgradeKey(string $key): array {
        $effects = [];
        switch ($key) {
            case UpgradeConfig::UPGRADE_KEY_BONUS_FOOD_I:
                $effects[UpgradeConfig::UPGRADE_EFFECT_FOOD_PRODUCTION] = 10;
                break;
            case UpgradeConfig::UPGRADE_KEY_BONUS_FOOD_II:
                $effects[UpgradeConfig::UPGRADE_EFFECT_FOOD_PRODUCTION] = 20;
                break;
            case UpgradeConfig::UPGRADE_KEY_BONUS_FOOD_III:
                $effects[UpgradeConfig::UPGRADE_EFFECT_FOOD_PRODUCTION] = 30;
                break;
        }
        return $effects;
    }

    public static function beginConstruction(System $system, Village $village, $building_id): void {

    }

    public static function cancelConstruction(System $system, Village $village, $building_id): void {

    }

    public static function beginResearch(System $system, Village $village, $upgrade_id): void {

    }

    public static function checkConstructionRequirements(System $system, Village $village): bool {

    }

    public static function checkResearchRequirements(System $system, Village $village): bool {

    }

    public static function checkToggleRequirements(System $system, Village $village): bool {

    }
}