<?php

class VillageUpgradeManager {
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

    const UPGRADE_KEY_BONUS_FOOD_I = 'BONUS_FOOD_I';
    const UPGRADE_KEY_BONUS_FOOD_II = 'BONUS_FOOD_II';
    const UPGRADE_KEY_BONUS_FOOD_III = 'BONUS_FOOD_III';

    const UPGRADE_BONUS_FOOD_INCOME = 'FOOD_INCOME';

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
                build_start_time: $building['build_start_time'],
                build_end_time: $building['build_end_time'],
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
                is_active: $upgrade['is_active'],
                research_start_time: $upgrade['research_start_time'],
                research_end_time: $upgrade['research_end_time'],
            );
        }
        return $upgrades;
    }

    /**
     * @param System  $system
     * @param VillageUpgradeDto[] $upgrades
     * @return array
     */
    public static function initializeBonusesForVillage(System $system, array $upgrades): array {
        $bonuses = [];
        foreach ($upgrades as $upgrade) {
            $upgrade_bonuses = self::getBonusesByUpgrade($upgrade->key);
            foreach ($upgrade_bonuses as $key => $value) {
                if (isset($bonuses[$key])) {
                    $bonuses[$key] += $value;
                } else {
                    $bonuses[$key] = $value;
                }
            }
        }
    }

    /**
     * @param string $key
     * @return array
     */
    public static function getBonusesByUpgradeKey(string $key): array {
        $bonuses = [];
        switch ($key) {
            case self::UPGRADE_KEY_BONUS_FOOD_I:
                $bonuses[self::UPGRADE_BONUS_FOOD_INCOME] = 10;
                break;
            case self::UPGRADE_KEY_BONUS_FOOD_II:
                $bonuses[self::UPGRADE_BONUS_FOOD_INCOME] = 20;
                break;
            case self::UPGRADE_KEY_BONUS_FOOD_III:
                $bonuses[self::UPGRADE_BONUS_FOOD_INCOME] = 30;
                break;
        }
        return $bonuses;
    }
}