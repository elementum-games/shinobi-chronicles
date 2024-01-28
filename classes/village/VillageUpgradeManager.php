<?php

require_once __DIR__ . "/VillageBuildingDto.php";
require_once __DIR__ . "/VillageUpgradeDto.php";

class VillageUpgradeManager {
    const BUILDING_STATUS_DEFAULT = 'default'; // used when no special status
    const BUILDING_STATUS_UPGRADING = 'upgrading'; // used when upgrading to next tier
    const BUILDING_STATUS_REPAIRING = 'disabled'; // used when disabled, currently unused
    const BUILDING_STATUS_REPAIRING = 'repairing'; // used when repairing damage, currently unused

    const BUILDING_VILLAGE_HQ = 1;
    const BUILDING_WORKSHOP = 2;
    const BUILDING_ACADEMY = 3;
    const BUILDING_HOSPITAL = 4;
    const BUILDING_ANBU_HQ = 5;
    const BUILDING_MARKET = 6;
    const BUILDING_RAMEN_STAND = 7;
    const BUILDING_SHRINE = 8;

    const BUILDING_NAMES = [
        self::BUILDING_VILLAGE_HQ => 'Village HQ',
        self::BUILDING_WORKSHOP => 'Workshop',
        self::BUILDING_ACADEMY => 'Academy',
        self::BUILDING_HOSPITAL => 'Hospital',
        self::BUILDING_ANBU_HQ => 'ANBU HQ',
        self::BUILDING_MARKET => 'Market',
        self::BUILDING_RAMEN_STAND => 'Ramen Stand',
        self::BUILDING_SHRINE => 'Shrine',
    ];

    const UPGRADE_STATUS_LOCKED = 'locked'; // default status
    const UPGRADE_STATUS_RESEARCHING = 'researching'; // used when unlocking
    const UPGRADE_STATUS_UNLOCKED = 'unlocked'; // state for unlocked, permanent upgrades
    const UPGRADE_STATUS_INACTIVE = 'inactive'; // state for unlocked, toggled OFF upgrades
    const UPGRADE_STATUS_ACTIVE = 'active'; // stat for unlocked, toggle ON upgrades

    // keys used for individual upgrade identifiers
    const UPGRADE_KEY_BONUS_FOOD_I = 'BONUS_FOOD_I';
    const UPGRADE_KEY_BONUS_FOOD_II = 'BONUS_FOOD_II';
    const UPGRADE_KEY_BONUS_FOOD_III = 'BONUS_FOOD_III';

    // constant used to identify individual effects that may be present in multiple upgrades
    const UPGRADE_EFFECT_MATERIALS_UPKEEP = 'MATERIALS_UPKEEP';
    const UPGRADE_EFFECT_FOOD_UPKEEP = 'FOOD_UPKEEP';
    const UPGRADE_EFFECT_WEALTH_UPKEEP = 'WEALTH_UPKEEP';
    const UPGRADE_EFFECT_MATERIALS_PRODUCTION = 'MATERIALS_PRODUCTION';
    const UPGRADE_EFFECT_FOOD_PRODUCTION = 'FOOD_PRODUCTION';
    const UPGRADE_EFFECT_WEALTH_PRODUCTION = 'WEALTH_PRODUCTION';

    // display names for each upgrade
    const UPGRADE_NAMES = [
        self::UPGRADE_KEY_BONUS_FOOD_I => 'Some Name',
    ];

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
            self::UPGRADE_EFFECT_FOOD_PRODUCTION => 0,
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
            case self::UPGRADE_KEY_BONUS_FOOD_I:
                $effects[self::UPGRADE_EFFECT_FOOD_PRODUCTION] = 10;
                break;
            case self::UPGRADE_KEY_BONUS_FOOD_II:
                $effects[self::UPGRADE_EFFECT_FOOD_PRODUCTION] = 20;
                break;
            case self::UPGRADE_KEY_BONUS_FOOD_III:
                $effects[self::UPGRADE_EFFECT_FOOD_PRODUCTION] = 30;
                break;
        }
        return $effects;
    }
}