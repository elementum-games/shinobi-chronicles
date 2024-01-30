<?php

require_once __DIR__ . "/VillageUpgradeConfig.php";

class VillageBuildingConfig {
    /* status options for an instance of a building */
    const BUILDING_STATUS_DEFAULT = 'default'; // used when no special status
    const BUILDING_STATUS_UPGRADING = 'upgrading'; // used when upgrading to next tier
    const BUILDING_STATUS_DISABLED = 'disabled'; // used when disabled, currently unused
    const BUILDING_STATUS_REPAIRING = 'repairing'; // used when repairing damage, currently unused

    /* indentifiers for each building */
    const BUILDING_VILLAGE_HQ = 'VILLAGE_HQ';
    const BUILDING_WORKSHOP = 'WORKSHOP';
    const BUILDING_ACADEMY = 'ACADEMY';
    const BUILDING_HOSPITAL = 'HOSPITAL';
    const BUILDING_ANBU_HQ = 'ANBU_HQ';
    const BUILDING_MARKET = 'MARKET';
    const BUILDING_SHRINE = 'SHRINE';
    const BUILDING_RAMEN_STAND = 'RAMEN_STAND';

    /* names for each building */
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

    /* construction requirement types */
    const BUILDING_REQUIREMENT_BUILDINGS = "BUILDINGS";
    const BUILDING_REQUIREMENT_UPGRADES = "UPGRADES";

    /* construction cost indexed by building ID, then tier, then resource type */
    const BUILDING_CONSTRUCTION_COST = [
        self::BUILDING_VILLAGE_HQ => [
            1 => [
                WarManager::RESOURCE_MATERIALS => 10800,
                WarManager::RESOURCE_FOOD => 4320,
                WarManager:: RESOURCE_WEALTH => 6480,
            ],
            2 => [
                WarManager::RESOURCE_MATERIALS => 54000,
                WarManager::RESOURCE_FOOD => 21600,
                WarManager:: RESOURCE_WEALTH => 32400,
            ],
            3 => [
                WarManager::RESOURCE_MATERIALS => 270000,
                WarManager::RESOURCE_FOOD => 108000,
                WarManager:: RESOURCE_WEALTH => 162000,
            ],
        ],
        self::BUILDING_WORKSHOP => [
            1 => [
                WarManager::RESOURCE_MATERIALS => 12960,
                WarManager::RESOURCE_FOOD => 4320,
                WarManager::RESOURCE_WEALTH => 4320,
            ],
            2 => [
                WarManager::RESOURCE_MATERIALS => 64800,
                WarManager::RESOURCE_FOOD => 21600,
                WarManager::RESOURCE_WEALTH => 21600,
            ],
            3 => [
                WarManager::RESOURCE_MATERIALS => 324000,
                WarManager::RESOURCE_FOOD => 108000,
                WarManager::RESOURCE_WEALTH => 108000,
            ],
        ],
        self::BUILDING_ACADEMY => [
            1 => [
                WarManager::RESOURCE_MATERIALS => 8640,
                WarManager::RESOURCE_FOOD => 6480,
                WarManager::RESOURCE_WEALTH => 6480,
            ],
            2 => [
                WarManager::RESOURCE_MATERIALS => 43200,
                WarManager::RESOURCE_FOOD => 32400,
                WarManager::RESOURCE_WEALTH => 32400,
            ],
            3 => [
                WarManager::RESOURCE_MATERIALS => 216000,
                WarManager::RESOURCE_FOOD => 162000,
                WarManager::RESOURCE_WEALTH => 162000,
            ],
        ],
        self::BUILDING_HOSPITAL => [
            1 => [
                WarManager::RESOURCE_MATERIALS => 8640,
                WarManager::RESOURCE_FOOD => 6480,
                WarManager::RESOURCE_WEALTH => 6480,
            ],
            2 => [
                WarManager::RESOURCE_MATERIALS => 43200,
                WarManager::RESOURCE_FOOD => 32400,
                WarManager::RESOURCE_WEALTH => 32400,
            ],
            3 => [
                WarManager::RESOURCE_MATERIALS => 216000,
                WarManager::RESOURCE_FOOD => 162000,
                WarManager::RESOURCE_WEALTH => 162000,
            ],
        ],
        self::BUILDING_ANBU_HQ => [
            1 => [
                WarManager::RESOURCE_MATERIALS => 10800,
                WarManager::RESOURCE_FOOD => 4320,
                WarManager::RESOURCE_WEALTH => 6480,
            ],
            2 => [
                WarManager::RESOURCE_MATERIALS => 54000,
                WarManager::RESOURCE_FOOD => 21600,
                WarManager::RESOURCE_WEALTH => 32400,
            ],
            3 => [
                WarManager::RESOURCE_MATERIALS => 270000,
                WarManager::RESOURCE_FOOD => 108000,
                WarManager::RESOURCE_WEALTH => 162000,
            ],
        ],
        self::BUILDING_MARKET => [
            1 => [
                WarManager::RESOURCE_MATERIALS => 4320,
                WarManager::RESOURCE_FOOD => 4320,
                WarManager::RESOURCE_WEALTH => 12960,
            ],
            2 => [
                WarManager::RESOURCE_MATERIALS => 21600,
                WarManager::RESOURCE_FOOD => 21600,
                WarManager::RESOURCE_WEALTH => 64800,
            ],
            3 => [
                WarManager::RESOURCE_MATERIALS => 108000,
                WarManager::RESOURCE_FOOD => 108000,
                WarManager::RESOURCE_WEALTH => 32400,
            ],
        ],
        self::BUILDING_SHRINE => [
            1 => [
                WarManager::RESOURCE_MATERIALS => 8640,
                WarManager::RESOURCE_FOOD => 6480,
                WarManager::RESOURCE_WEALTH => 6480,
            ],
            2 => [
                WarManager::RESOURCE_MATERIALS => 43200,
                WarManager::RESOURCE_FOOD => 32400,
                WarManager::RESOURCE_WEALTH => 32400,
            ],
            3 => [
                WarManager::RESOURCE_MATERIALS => 216000,
                WarManager::RESOURCE_FOOD => 162000,
                WarManager::RESOURCE_WEALTH => 162000,
            ],
        ],
        self::BUILDING_RAMEN_STAND => [
            1 => [
                WarManager::RESOURCE_MATERIALS => 4320,
                WarManager::RESOURCE_FOOD => 12960,
                WarManager::RESOURCE_WEALTH => 4320,
            ],
            2 => [
                WarManager::RESOURCE_MATERIALS => 21600,
                WarManager::RESOURCE_FOOD => 64800,
                WarManager::RESOURCE_WEALTH => 21600,
            ],
            3 => [
                WarManager::RESOURCE_MATERIALS => 108000,
                WarManager::RESOURCE_FOOD => 324000,
                WarManager::RESOURCE_WEALTH => 108000,
            ],
        ],
    ];

    /* construction time indexed by building ID, then tier */
    const BUILDING_CONSTRUCTION_TIME = [
        self::BUILDING_VILLAGE_HQ => [
            1 => 3,
            2 => 15,
            3 => 75,
        ],
        self::BUILDING_WORKSHOP => [
            1 => 3,
            2 => 15,
            3 => 75,
        ],
        self::BUILDING_ACADEMY => [
            1 => 3,
            2 => 15,
            3 => 75,
        ],
        self::BUILDING_HOSPITAL => [
            1 => 3,
            2 => 15,
            3 => 75,
        ],
        self::BUILDING_ANBU_HQ => [
            1 => 3,
            2 => 15,
            3 => 75,
        ],
        self::BUILDING_MARKET => [
            1 => 3,
            2 => 15,
            3 => 75,
        ],
        self::BUILDING_SHRINE => [
            1 => 3,
            2 => 15,
            3 => 75,
        ],
        self::BUILDING_RAMEN_STAND => [
            1 => 3,
            2 => 15,
            3 => 75,
        ],
    ];
}