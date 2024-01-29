<?php

require_once __DIR__ . "/BuildingConfig.php";

class UpgradeConfig {
    /* status options for an instance of an upgrade */
    const UPGRADE_STATUS_LOCKED = 'locked'; // default status
    const UPGRADE_STATUS_RESEARCHING = 'researching'; // used when unlocking
    const UPGRADE_STATUS_UNLOCKED = 'unlocked'; // state for unlocked, permanent upgrades
    const UPGRADE_STATUS_INACTIVE = 'inactive'; // state for unlocked, toggled OFF upgrades
    const UPGRADE_STATUS_ACTIVE = 'active'; // stat for unlocked, toggle ON upgrades

    /* keys used for individual upgrade identifiers */
    const UPGRADE_KEY_BONUS_FOOD_I = 'BONUS_FOOD_I';
    const UPGRADE_KEY_BONUS_FOOD_II = 'BONUS_FOOD_II';
    const UPGRADE_KEY_BONUS_FOOD_III = 'BONUS_FOOD_III';

    /* constant used to identify individual effects that may be present in multiple upgrades */
    const UPGRADE_EFFECT_MATERIALS_UPKEEP = 'MATERIALS_UPKEEP';
    const UPGRADE_EFFECT_FOOD_UPKEEP = 'FOOD_UPKEEP';
    const UPGRADE_EFFECT_WEALTH_UPKEEP = 'WEALTH_UPKEEP';
    const UPGRADE_EFFECT_MATERIALS_PRODUCTION = 'MATERIALS_PRODUCTION';
    const UPGRADE_EFFECT_FOOD_PRODUCTION = 'FOOD_PRODUCTION';
    const UPGRADE_EFFECT_WEALTH_PRODUCTION = 'WEALTH_PRODUCTION';
    const UPGRADE_EFFECT_CONSTRUCTION_SPEED = 'CONSTRUCTION_SPEED';
    const UPGRADE_EFFECT_RESEARCH_SPEED = 'RESEARCH_SPEED';

    /* research requirement types */
    const UPGRADE_REQUIREMENT_BUILDINGS = "BUILDINGS";
    const UPGRADE_REQUIREMENT_UPGRADES = "UPGRADES";

    /* display names for each upgrade indexed by upgrade key */
    const UPGRADE_NAMES = [
        self::UPGRADE_KEY_BONUS_FOOD_I => 'Some Name',
    ];

    /* research cost indexed by upgrade key, then resource type */
    const UPGRADE_RESEARCH_COST = [
        self::UPGRADE_KEY_BONUS_FOOD_I => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
    ];

    /* research time indexed by upgrade key */
    const UPGRADE_RESEARCH_TIME = [
        self::UPGRADE_KEY_BONUS_FOOD_I => 0,
    ];

    /* research requirements indexed by upgrade key, then requirement type */
    const UPGRADE_RESEARCH_REQUIREMENTS = [
        self::UPGRADE_KEY_BONUS_FOOD_I => [
            self::UPGRADE_REQUIREMENT_BUILDINGS => [
                BuildingConfig::BUILDING_MARKET => 1,
            ],
            self::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
    ];

    /* toggle requirements indexed by upgrade key, then requirement type */
    const UPGRADE_TOGGLE_REQUIREMENTS = [
        self::UPGRADE_KEY_BONUS_FOOD_I => [
            self::UPGRADE_REQUIREMENT_BUILDINGS => [
                BuildingConfig::BUILDING_MARKET => 1,
            ],
            self::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
    ];

    /* upkeep cost indexed by upgrade key, then resource type */
    const UPGRADE_UPKEEP = [
        self::UPGRADE_KEY_BONUS_FOOD_I => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
    ];
}