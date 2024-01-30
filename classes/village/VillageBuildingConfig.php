<?php

require_once __DIR__ . "/VillageUpgradeConfig.php";

class VillageBuildingConfig {
    /* status options for an instance of a building */
    const BUILDING_STATUS_DEFAULT = 'default'; // used when no special status
    const BUILDING_STATUS_UPGRADING = 'upgrading'; // used when upgrading to next tier
    const BUILDING_STATUS_DISABLED = 'disabled'; // used when disabled, currently unused
    const BUILDING_STATUS_REPAIRING = 'repairing'; // used when repairing damage, currently unused

    /* indentifiers for each building */
    const BUILDING_VILLAGE_HQ = 1;
    const BUILDING_WORKSHOP = 2;
    const BUILDING_ACADEMY = 3;
    const BUILDING_HOSPITAL = 4;
    const BUILDING_ANBU_HQ = 5;
    const BUILDING_MARKET = 6;
    const BUILDING_RAMEN_STAND = 7;
    const BUILDING_SHRINE = 8;

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
                WarManager::RESOURCE_MATERIALS => 0,
                WarManager::RESOURCE_FOOD => 0,
                WarManager:: RESOURCE_WEALTH => 0,
            ],
            2 => [
                WarManager::RESOURCE_MATERIALS => 0,
                WarManager::RESOURCE_FOOD => 0,
                WarManager:: RESOURCE_WEALTH => 0,
            ],
            3 => [
                WarManager::RESOURCE_MATERIALS => 0,
                WarManager::RESOURCE_FOOD => 0,
                WarManager:: RESOURCE_WEALTH => 0,
            ],
        ],
        self::BUILDING_WORKSHOP => [
            1 => [
                WarManager::RESOURCE_MATERIALS => 0,
                WarManager::RESOURCE_FOOD => 0,
                WarManager::RESOURCE_WEALTH => 0,
            ],
            2 => [
                WarManager::RESOURCE_MATERIALS => 0,
                WarManager::RESOURCE_FOOD => 0,
                WarManager::RESOURCE_WEALTH => 0,
            ],
            3 => [
                WarManager::RESOURCE_MATERIALS => 0,
                WarManager::RESOURCE_FOOD => 0,
                WarManager::RESOURCE_WEALTH => 0,
            ],
        ],
        self::BUILDING_ACADEMY => [
            1 => [
                WarManager::RESOURCE_MATERIALS => 0,
                WarManager::RESOURCE_FOOD => 0,
                WarManager::RESOURCE_WEALTH => 0,
            ],
            2 => [
                WarManager::RESOURCE_MATERIALS => 0,
                WarManager::RESOURCE_FOOD => 0,
                WarManager::RESOURCE_WEALTH => 0,
            ],
            3 => [
                WarManager::RESOURCE_MATERIALS => 0,
                WarManager::RESOURCE_FOOD => 0,
                WarManager::RESOURCE_WEALTH => 0,
            ],
        ],
        self::BUILDING_HOSPITAL => [
            1 => [
                WarManager::RESOURCE_MATERIALS => 0,
                WarManager::RESOURCE_FOOD => 0,
                WarManager::RESOURCE_WEALTH => 0,
            ],
            2 => [
                WarManager::RESOURCE_MATERIALS => 0,
                WarManager::RESOURCE_FOOD => 0,
                WarManager::RESOURCE_WEALTH => 0,
            ],
            3 => [
                WarManager::RESOURCE_MATERIALS => 0,
                WarManager::RESOURCE_FOOD => 0,
                WarManager::RESOURCE_WEALTH => 0,
            ],
        ],
        self::BUILDING_ANBU_HQ => [
            1 => [
                WarManager::RESOURCE_MATERIALS => 0,
                WarManager::RESOURCE_FOOD => 0,
                WarManager::RESOURCE_WEALTH => 0,
            ],
            2 => [
                WarManager::RESOURCE_MATERIALS => 0,
                WarManager::RESOURCE_FOOD => 0,
                WarManager::RESOURCE_WEALTH => 0,
            ],
            3 => [
                WarManager::RESOURCE_MATERIALS => 0,
                WarManager::RESOURCE_FOOD => 0,
                WarManager::RESOURCE_WEALTH => 0,
            ],
        ],
        self::BUILDING_MARKET => [
            1 => [
                WarManager::RESOURCE_MATERIALS => 0,
                WarManager::RESOURCE_FOOD => 0,
                WarManager::RESOURCE_WEALTH => 0,
            ],
            2 => [
                WarManager::RESOURCE_MATERIALS => 0,
                WarManager::RESOURCE_FOOD => 0,
                WarManager::RESOURCE_WEALTH => 0,
            ],
            3 => [
                WarManager::RESOURCE_MATERIALS => 0,
                WarManager::RESOURCE_FOOD => 0,
                WarManager::RESOURCE_WEALTH => 0,
            ],
        ],
        self::BUILDING_RAMEN_STAND => [
            1 => [
                WarManager::RESOURCE_MATERIALS => 0,
                WarManager::RESOURCE_FOOD => 0,
                WarManager::RESOURCE_WEALTH => 0,
            ],
            2 => [
                WarManager::RESOURCE_MATERIALS => 0,
                WarManager::RESOURCE_FOOD => 0,
                WarManager::RESOURCE_WEALTH => 0,
            ],
            3 => [
                WarManager::RESOURCE_MATERIALS => 0,
                WarManager::RESOURCE_FOOD => 0,
                WarManager::RESOURCE_WEALTH => 0,
            ],
        ],
        self::BUILDING_SHRINE => [
            1 => [
                WarManager::RESOURCE_MATERIALS => 0,
                WarManager::RESOURCE_FOOD => 0,
                WarManager::RESOURCE_WEALTH => 0,
            ],
            2 => [
                WarManager::RESOURCE_MATERIALS => 0,
                WarManager::RESOURCE_FOOD => 0,
                WarManager::RESOURCE_WEALTH => 0,
            ],
            3 => [
                WarManager::RESOURCE_MATERIALS => 0,
                WarManager::RESOURCE_FOOD => 0,
                WarManager::RESOURCE_WEALTH => 0,
            ],
        ],
    ];

    /* construction time indexed by building ID, then tier */
    const BUILDING_CONSTRUCTION_TIME = [
        self::BUILDING_VILLAGE_HQ => [
            1 => 0,
            2 => 0,
            3 => 0,
        ],
        self::BUILDING_WORKSHOP => [
            1 => 0,
            2 => 0,
            3 => 0,
        ],
        self::BUILDING_ACADEMY => [
            1 => 0,
            2 => 0,
            3 => 0,
        ],
        self::BUILDING_HOSPITAL => [
            1 => 0,
            2 => 0,
            3 => 0,
        ],
        self::BUILDING_ANBU_HQ => [
            1 => 0,
            2 => 0,
            3 => 0,
        ],
        self::BUILDING_MARKET => [
            1 => 0,
            2 => 0,
            3 => 0,
        ],
        self::BUILDING_RAMEN_STAND => [
            1 => 0,
            2 => 0,
            3 => 0,
        ],
        self::BUILDING_SHRINE => [
            1 => 0,
            2 => 0,
            3 => 0,
        ],
    ];

    /* construction requirements indexed by building ID, then tier, then type */
    const BUILDING_CONSTRUCTION_REQUIREMENTS = [
        self::BUILDING_VILLAGE_HQ => [
            1 => [
                self::BUILDING_REQUIREMENT_BUILDINGS => [
                ],
                self::BUILDING_REQUIREMENT_UPGRADES => [
                ],
            ],
            2 => [
                self::BUILDING_REQUIREMENT_BUILDINGS => [
                ],
                self::BUILDING_REQUIREMENT_UPGRADES => [
                ],
            ],
            3 => [
                self::BUILDING_REQUIREMENT_BUILDINGS => [
                ],
                self::BUILDING_REQUIREMENT_UPGRADES => [
                ],
            ],
        ],
        self::BUILDING_WORKSHOP => [
            1 => [
                self::BUILDING_REQUIREMENT_BUILDINGS => [
                ],
                self::BUILDING_REQUIREMENT_UPGRADES => [
                ],
            ],
            2 => [
                self::BUILDING_REQUIREMENT_BUILDINGS => [
                ],
                self::BUILDING_REQUIREMENT_UPGRADES => [
                ],
            ],
            3 => [
                self::BUILDING_REQUIREMENT_BUILDINGS => [
                ],
                self::BUILDING_REQUIREMENT_UPGRADES => [
                ],
            ],
        ],
        self::BUILDING_ACADEMY => [
            1 => [
                self::BUILDING_REQUIREMENT_BUILDINGS => [
                ],
                self::BUILDING_REQUIREMENT_UPGRADES => [
                ],
            ],
            2 => [
                self::BUILDING_REQUIREMENT_BUILDINGS => [
                ],
                self::BUILDING_REQUIREMENT_UPGRADES => [
                ],
            ],
            3 => [
                self::BUILDING_REQUIREMENT_BUILDINGS => [
                ],
                self::BUILDING_REQUIREMENT_UPGRADES => [
                ],
            ],
        ],
        self::BUILDING_HOSPITAL => [
            1 => [
                self::BUILDING_REQUIREMENT_BUILDINGS => [
                ],
                self::BUILDING_REQUIREMENT_UPGRADES => [
                ],
            ],
            2 => [
                self::BUILDING_REQUIREMENT_BUILDINGS => [
                ],
                self::BUILDING_REQUIREMENT_UPGRADES => [
                ],
            ],
            3 => [
                self::BUILDING_REQUIREMENT_BUILDINGS => [
                ],
                self::BUILDING_REQUIREMENT_UPGRADES => [
                ],
            ],
        ],
        self::BUILDING_ANBU_HQ => [
            1 => [
                self::BUILDING_REQUIREMENT_BUILDINGS => [
                ],
                self::BUILDING_REQUIREMENT_UPGRADES => [
                ],
            ],
            2 => [
                self::BUILDING_REQUIREMENT_BUILDINGS => [
                ],
                self::BUILDING_REQUIREMENT_UPGRADES => [
                ],
            ],
            3 => [
                self::BUILDING_REQUIREMENT_BUILDINGS => [
                ],
                self::BUILDING_REQUIREMENT_UPGRADES => [
                ],
            ],
        ],
        self::BUILDING_MARKET => [
            1 => [
                self::BUILDING_REQUIREMENT_BUILDINGS => [
                ],
                self::BUILDING_REQUIREMENT_UPGRADES => [
                ],
            ],
            2 => [
                self::BUILDING_REQUIREMENT_BUILDINGS => [
                ],
                self::BUILDING_REQUIREMENT_UPGRADES => [
                ],
            ],
            3 => [
                self::BUILDING_REQUIREMENT_BUILDINGS => [
                ],
                self::BUILDING_REQUIREMENT_UPGRADES => [
                ],
            ],
        ],
        self::BUILDING_RAMEN_STAND => [
            1 => [
                self::BUILDING_REQUIREMENT_BUILDINGS => [
                ],
                self::BUILDING_REQUIREMENT_UPGRADES => [
                ],
            ],
            2 => [
                self::BUILDING_REQUIREMENT_BUILDINGS => [
                ],
                self::BUILDING_REQUIREMENT_UPGRADES => [
                ],
            ],
            3 => [
                self::BUILDING_REQUIREMENT_BUILDINGS => [
                ],
                self::BUILDING_REQUIREMENT_UPGRADES => [
                ],
            ],
        ],
        self::BUILDING_SHRINE => [
            1 => [
                self::BUILDING_REQUIREMENT_BUILDINGS => [
                ],
                self::BUILDING_REQUIREMENT_UPGRADES => [
                ],
            ],
            2 => [
                self::BUILDING_REQUIREMENT_BUILDINGS => [
                ],
                self::BUILDING_REQUIREMENT_UPGRADES => [
                ],
            ],
            3 => [
                self::BUILDING_REQUIREMENT_BUILDINGS => [
                ],
                self::BUILDING_REQUIREMENT_UPGRADES => [
                ],
            ],
        ],
    ];
}