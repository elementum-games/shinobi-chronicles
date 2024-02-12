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
    const BUILDING_MARKET = 'MARKET';
    const BUILDING_ACADEMY = 'ACADEMY';
    const BUILDING_HOSPITAL = 'HOSPITAL';
    const BUILDING_ANBU_HQ = 'ANBU_HQ';
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

    /* upgrade set keys */
    const UPGRADE_SET_KEY_RESEARCH = 'RESEARCH';
    const UPGRADE_SET_KEY_RESEARCH_SUBSIDIES = 'RESEARCH_SUBSIDIES';
    const UPGRADE_SET_KEY_ADMINISTRATION = 'ADMINISTRATION';
    const UPGRADE_SET_KEY_POWER_PROJECTION = 'POWER_PROJECTION';
    const UPGRADE_SET_KEY_EDUCATION_SUBSIDIES = 'EDUCATION_SUBSIDIES';
    const UPGRADE_SET_KEY_TRAINING_GROUNDS = 'TRAINING_GROUNDS';
    const UPGRADE_SET_KEY_MEDICAL_SUBSIDIES = 'MEDICAL_SUBSIDIES';
    const UPGRADE_SET_KEY_HERBICULTURE = 'HERBICULTURE';
    const UPGRADE_SET_KEY_MILITARY_SUBSIDIES = 'MILITARY_SUBSIDIES';
    const UPGRADE_SET_KEY_ASSAULT_TRAINING = 'ASSAULT_TRAINING';
    const UPGRADE_SET_KEY_SALTED_EARTH = 'SALTED_EARTH';
    const UPGRADE_SET_KEY_UNDERWORLD_CONNECTIONS = 'UNDERWORLD_CONNECTIONS';
    const UPGRADE_SET_KEY_GUERRILLA_WARFARE = 'GUERRILLA_WARFARE';
    const UPGRADE_SET_KEY_SELECTIVE_RECRUITMENT = 'SELECTIVE_RECRUITMENT';
    const UPGRADE_SET_KEY_CONSTRUCTION = 'CONSTRUCTION';
    const UPGRADE_SET_KEY_ENGINEERING_SUBSIDIES = 'ENGINEERING_SUBSIDIES';
    const UPGRADE_SET_KEY_RAPID_DEPLOYMENT = 'RAPID_DEPLOYMENT';
    const UPGRADE_SET_KEY_ENGINEERING_CORPS = 'ENGINEERING_CORPS';
    const UPGRADE_SET_KEY_FORTIFICATIONS = 'FORTIFICATIONS';
    const UPGRADE_SET_KEY_BULK_SUPPLIERS = 'BULK_SUPPLIERS';
    const UPGRADE_SET_KEY_FARMERS_MARKET = 'FARMERS_MARKET';
    const UPGRADE_SET_KEY_MERCHANTS_GUILD = 'MERCHANTS_GUILD';
    const UPGRADE_SET_KEY_WAREHOUSES = 'WAREHOUSES';
    const UPGRADE_SET_KEY_ANCESTRAL_LEGACY = 'ANCESTRAL_LEGACY';
    const UPGRADE_SET_KEY_FORTUNES_BOUNTY = 'FORTUNES_BOUNTY';
    const UPGRADE_SET_KEY_NEW_RECIPE = 'NEW_RECIPE';
    const UPGRADE_SET_KEY_QUALITY_INGREDIENTS = 'QUALITY_INGREDIENTS';
    const UPGRADE_SET_KEY_NINJA_FRIENDLY_RATES = 'SHINOBI_DISCOUNT';
    const UPGRADE_SET_KEY_INSPIRED_ITAMAE = 'LUCK_IN_LEFTOVERS';

    /* upgrade sets for each building, indexed by building ID */
    const BUILDING_UPGRADE_SETS = [
        self::BUILDING_VILLAGE_HQ => [
            self::UPGRADE_SET_KEY_RESEARCH_SUBSIDIES,
            self::UPGRADE_SET_KEY_ADMINISTRATION,
            self::UPGRADE_SET_KEY_POWER_PROJECTION,
        ],
        self::BUILDING_ACADEMY => [
            self::UPGRADE_SET_KEY_EDUCATION_SUBSIDIES,
            self::UPGRADE_SET_KEY_TRAINING_GROUNDS,
        ],
        self::BUILDING_HOSPITAL => [
            self::UPGRADE_SET_KEY_MEDICAL_SUBSIDIES,
            self::UPGRADE_SET_KEY_HERBICULTURE,
        ],
        self::BUILDING_ANBU_HQ => [
            self::UPGRADE_SET_KEY_MILITARY_SUBSIDIES,
            self::UPGRADE_SET_KEY_ASSAULT_TRAINING,
            self::UPGRADE_SET_KEY_SALTED_EARTH,
            self::UPGRADE_SET_KEY_UNDERWORLD_CONNECTIONS,
            self::UPGRADE_SET_KEY_GUERRILLA_WARFARE,
            self::UPGRADE_SET_KEY_SELECTIVE_RECRUITMENT,
        ],
        self::BUILDING_WORKSHOP => [
            self::UPGRADE_SET_KEY_ENGINEERING_SUBSIDIES,
            self::UPGRADE_SET_KEY_RAPID_DEPLOYMENT,
            self::UPGRADE_SET_KEY_ENGINEERING_CORPS,
            self::UPGRADE_SET_KEY_FORTIFICATIONS,
        ],
        self::BUILDING_MARKET => [
            self::UPGRADE_SET_KEY_BULK_SUPPLIERS,
            self::UPGRADE_SET_KEY_FARMERS_MARKET,
            self::UPGRADE_SET_KEY_MERCHANTS_GUILD,
            self::UPGRADE_SET_KEY_WAREHOUSES,
        ],
        self::BUILDING_SHRINE => [
            self::UPGRADE_SET_KEY_ANCESTRAL_LEGACY,
            self::UPGRADE_SET_KEY_FORTUNES_BOUNTY,
        ],
        self::BUILDING_RAMEN_STAND => [
            self::UPGRADE_SET_KEY_NEW_RECIPE,
            self::UPGRADE_SET_KEY_QUALITY_INGREDIENTS,
            self::UPGRADE_SET_KEY_NINJA_FRIENDLY_RATES,
            self::UPGRADE_SET_KEY_INSPIRED_ITAMAE,
        ],
    ];

    /* upgrade set names, indexed by upgrade set key */
    const UPGRADE_SET_NAMES = [
        self::UPGRADE_SET_KEY_RESEARCH => 'Research',
        self::UPGRADE_SET_KEY_RESEARCH_SUBSIDIES => 'Research Subsidies',
        self::UPGRADE_SET_KEY_ADMINISTRATION => 'Administration',
        self::UPGRADE_SET_KEY_POWER_PROJECTION => 'Power Projection',
        self::UPGRADE_SET_KEY_EDUCATION_SUBSIDIES => 'Education Subsidies',
        self::UPGRADE_SET_KEY_TRAINING_GROUNDS => 'Training Grounds',
        self::UPGRADE_SET_KEY_MEDICAL_SUBSIDIES => 'Medical Subsidies',
        self::UPGRADE_SET_KEY_HERBICULTURE => 'Herbiculture',
        self::UPGRADE_SET_KEY_MILITARY_SUBSIDIES => 'Military Subsidies',
        self::UPGRADE_SET_KEY_ASSAULT_TRAINING => 'Assault Training',
        self::UPGRADE_SET_KEY_SALTED_EARTH => 'Salted Earth',
        self::UPGRADE_SET_KEY_UNDERWORLD_CONNECTIONS => 'Underworld Connections',
        self::UPGRADE_SET_KEY_GUERRILLA_WARFARE => 'Guerrilla Warfare',
        self::UPGRADE_SET_KEY_SELECTIVE_RECRUITMENT => 'Selective Recruitment',
        self::UPGRADE_SET_KEY_CONSTRUCTION => 'Construction',
        self::UPGRADE_SET_KEY_ENGINEERING_SUBSIDIES => 'Engineering Subsidies',
        self::UPGRADE_SET_KEY_RAPID_DEPLOYMENT => 'Rapid Deployment',
        self::UPGRADE_SET_KEY_ENGINEERING_CORPS => 'Engineering Corps',
        self::UPGRADE_SET_KEY_FORTIFICATIONS => 'Fortifications',
        self::UPGRADE_SET_KEY_BULK_SUPPLIERS => 'Bulk Suppliers',
        self::UPGRADE_SET_KEY_FARMERS_MARKET => 'Farmers Market',
        self::UPGRADE_SET_KEY_MERCHANTS_GUILD => 'Merchants Guild',
        self::UPGRADE_SET_KEY_WAREHOUSES => 'Warehouses',
        self::UPGRADE_SET_KEY_ANCESTRAL_LEGACY => 'Ancestral Legacy',
        self::UPGRADE_SET_KEY_FORTUNES_BOUNTY => 'Fortune\'s Bounty',
        self::UPGRADE_SET_KEY_NEW_RECIPE => 'New Recipe',
        self::UPGRADE_SET_KEY_QUALITY_INGREDIENTS => 'Quality Ingredients',
        self::UPGRADE_SET_KEY_NINJA_FRIENDLY_RATES => 'Ninja Friendly Rates',
        self::UPGRADE_SET_KEY_INSPIRED_ITAMAE => 'Inspired Itamae',
    ];

    /* upgrade set descriptions, indexed by upgrade set key */
    const UPGRADE_SET_DESCRIPTIONS = [
        self::UPGRADE_SET_KEY_RESEARCH => "Enables researching higher tier upgrades.",
        self::UPGRADE_SET_KEY_RESEARCH_SUBSIDIES => "Increases overall research speed.",
        self::UPGRADE_SET_KEY_ADMINISTRATION => "Decreases upkeep from active upgrades.",
        self::UPGRADE_SET_KEY_POWER_PROJECTION => "Increases the baseline and maximum Stability.",
        self::UPGRADE_SET_KEY_EDUCATION_SUBSIDIES => "Increases training speed.",
        self::UPGRADE_SET_KEY_TRAINING_GROUNDS => "Chance for double experience gains from battle.",
        self::UPGRADE_SET_KEY_MEDICAL_SUBSIDIES => "Increases village regen rate.",
        self::UPGRADE_SET_KEY_HERBICULTURE => "Decreases cost of healing items.",
        self::UPGRADE_SET_KEY_MILITARY_SUBSIDIES => "Decreases pool cost of war actions.",
        self::UPGRADE_SET_KEY_ASSAULT_TRAINING => "Increase speed of raiding.",
        self::UPGRADE_SET_KEY_SALTED_EARTH => "Increased damage from raids.",
        self::UPGRADE_SET_KEY_UNDERWORLD_CONNECTIONS => "Increases speed of infiltrating.",
        self::UPGRADE_SET_KEY_GUERRILLA_WARFARE => "Increases stability for villages occupied by an enemy.",
        self::UPGRADE_SET_KEY_SELECTIVE_RECRUITMENT => "Increases strength of patrols.",
        self::UPGRADE_SET_KEY_CONSTRUCTION => "Enables construction higher tier buildings",
        self::UPGRADE_SET_KEY_ENGINEERING_SUBSIDIES => "Increases construction speed.",
        self::UPGRADE_SET_KEY_RAPID_DEPLOYMENT => "Increases speed of reinforcing.",
        self::UPGRADE_SET_KEY_ENGINEERING_CORPS => "Increases heal from reinforcing.",
        self::UPGRADE_SET_KEY_FORTIFICATIONS => "Increases castle health.",
        self::UPGRADE_SET_KEY_BULK_SUPPLIERS => "Increases masterials production in exchange for wealth.",
        self::UPGRADE_SET_KEY_FARMERS_MARKET => "Increases food production in exchange for wealth.",
        self::UPGRADE_SET_KEY_MERCHANTS_GUILD => "Increases wealth production in exchange for materials and food.",
        self::UPGRADE_SET_KEY_WAREHOUSES => "Increases maximum resource capacity.",
        self::UPGRADE_SET_KEY_ANCESTRAL_LEGACY => "Increases chance of obtaining a bloodline.",
        self::UPGRADE_SET_KEY_FORTUNES_BOUNTY => "Chance for double yen gains from battle.",
        self::UPGRADE_SET_KEY_NEW_RECIPE => "Unlocks new ramen recipes.",
        self::UPGRADE_SET_KEY_QUALITY_INGREDIENTS => "Increases duration of ramen buffs.",
        self::UPGRADE_SET_KEY_NINJA_FRIENDLY_RATES => "Decreases cost of ramen.",
        self::UPGRADE_SET_KEY_INSPIRED_ITAMAE => "Increases chance of Mystery Ramen appearing.",
    ];

    /* upgrade set upgrades, indexed by upgrade set key */
    const UPGRADE_SET_UPGRADES = [
        self::UPGRADE_SET_KEY_RESEARCH => [
            VillageUpgradeConfig::UPGRADE_KEY_RESEARCH_I,
            VillageUpgradeConfig::UPGRADE_KEY_RESEARCH_II,
            VillageUpgradeConfig::UPGRADE_KEY_RESEARCH_III,
        ],
        self::UPGRADE_SET_KEY_RESEARCH_SUBSIDIES => [
            VillageUpgradeConfig::UPGRADE_KEY_RESEARCH_SUBSIDIES_I,
            VillageUpgradeConfig::UPGRADE_KEY_RESEARCH_SUBSIDIES_II,
            VillageUpgradeConfig::UPGRADE_KEY_RESEARCH_SUBSIDIES_III,
        ],
        self::UPGRADE_SET_KEY_ADMINISTRATION => [
            VillageUpgradeConfig::UPGRADE_KEY_ADMINISTRATION_I,
            VillageUpgradeConfig::UPGRADE_KEY_ADMINISTRATION_II,
            VillageUpgradeConfig::UPGRADE_KEY_ADMINISTRATION_III,
        ],
        self::UPGRADE_SET_KEY_POWER_PROJECTION => [
            VillageUpgradeConfig::UPGRADE_KEY_POWER_PROJECTION_I,
            VillageUpgradeConfig::UPGRADE_KEY_POWER_PROJECTION_II,
            VillageUpgradeConfig::UPGRADE_KEY_POWER_PROJECTION_III,
        ],
        self::UPGRADE_SET_KEY_EDUCATION_SUBSIDIES => [
            VillageUpgradeConfig::UPGRADE_KEY_EDUCATION_SUBSIDIES_I,
            VillageUpgradeConfig::UPGRADE_KEY_EDUCATION_SUBSIDIES_II,
            VillageUpgradeConfig::UPGRADE_KEY_EDUCATION_SUBSIDIES_III,
        ],
        self::UPGRADE_SET_KEY_TRAINING_GROUNDS => [
            VillageUpgradeConfig::UPGRADE_KEY_TRAINING_GROUNDS_I,
            VillageUpgradeConfig::UPGRADE_KEY_TRAINING_GROUNDS_II,
            VillageUpgradeConfig::UPGRADE_KEY_TRAINING_GROUNDS_III,
        ],
        self::UPGRADE_SET_KEY_MEDICAL_SUBSIDIES => [
            VillageUpgradeConfig::UPGRADE_KEY_MEDICAL_SUBSIDIES_I,
            VillageUpgradeConfig::UPGRADE_KEY_MEDICAL_SUBSIDIES_II,
            VillageUpgradeConfig::UPGRADE_KEY_MEDICAL_SUBSIDIES_III,
        ],
        self::UPGRADE_SET_KEY_HERBICULTURE => [
            VillageUpgradeConfig::UPGRADE_KEY_HERBICULTURE_I,
            VillageUpgradeConfig::UPGRADE_KEY_HERBICULTURE_II,
            VillageUpgradeConfig::UPGRADE_KEY_HERBICULTURE_III,
        ],
        self::UPGRADE_SET_KEY_MILITARY_SUBSIDIES => [
            VillageUpgradeConfig::UPGRADE_KEY_MILITARY_SUBSIDIES_I,
            VillageUpgradeConfig::UPGRADE_KEY_MILITARY_SUBSIDIES_II,
            VillageUpgradeConfig::UPGRADE_KEY_MILITARY_SUBSIDIES_III,
        ],
        self::UPGRADE_SET_KEY_ASSAULT_TRAINING => [
            VillageUpgradeConfig::UPGRADE_KEY_ASSAULT_TRAINING_I,
            VillageUpgradeConfig::UPGRADE_KEY_ASSAULT_TRAINING_II,
            VillageUpgradeConfig::UPGRADE_KEY_ASSAULT_TRAINING_III,
        ],
        self::UPGRADE_SET_KEY_SALTED_EARTH => [
            VillageUpgradeConfig::UPGRADE_KEY_SALTED_EARTH_I,
            VillageUpgradeConfig::UPGRADE_KEY_SALTED_EARTH_II,
            VillageUpgradeConfig::UPGRADE_KEY_SALTED_EARTH_III,
        ],
        self::UPGRADE_SET_KEY_UNDERWORLD_CONNECTIONS => [
            VillageUpgradeConfig::UPGRADE_KEY_UNDERWORLD_CONNECTIONS_I,
            VillageUpgradeConfig::UPGRADE_KEY_UNDERWORLD_CONNECTIONS_II,
            VillageUpgradeConfig::UPGRADE_KEY_UNDERWORLD_CONNECTIONS_III,
        ],
        self::UPGRADE_SET_KEY_GUERRILLA_WARFARE => [
            VillageUpgradeConfig::UPGRADE_KEY_GUERRILLA_WARFARE_I,
            VillageUpgradeConfig::UPGRADE_KEY_GUERRILLA_WARFARE_II,
            VillageUpgradeConfig::UPGRADE_KEY_GUERRILLA_WARFARE_III,
        ],
        self::UPGRADE_SET_KEY_SELECTIVE_RECRUITMENT => [
            VillageUpgradeConfig::UPGRADE_KEY_SELECTIVE_RECRUITMENT_I,
            VillageUpgradeConfig::UPGRADE_KEY_SELECTIVE_RECRUITMENT_II,
            VillageUpgradeConfig::UPGRADE_KEY_SELECTIVE_RECRUITMENT_III,
        ],
        self::UPGRADE_SET_KEY_CONSTRUCTION => [
            VillageUpgradeConfig::UPGRADE_KEY_CONSTRUCTION_I,
            VillageUpgradeConfig::UPGRADE_KEY_CONSTRUCTION_II,
            VillageUpgradeConfig::UPGRADE_KEY_CONSTRUCTION_III,
        ],
        self::UPGRADE_SET_KEY_ENGINEERING_SUBSIDIES => [
            VillageUpgradeConfig::UPGRADE_KEY_ENGINEERING_SUBSIDIES_I,
            VillageUpgradeConfig::UPGRADE_KEY_ENGINEERING_SUBSIDIES_II,
            VillageUpgradeConfig::UPGRADE_KEY_ENGINEERING_SUBSIDIES_III,
        ],
        self::UPGRADE_SET_KEY_RAPID_DEPLOYMENT => [
            VillageUpgradeConfig::UPGRADE_KEY_RAPID_DEPLOYMENT_I,
            VillageUpgradeConfig::UPGRADE_KEY_RAPID_DEPLOYMENT_II,
            VillageUpgradeConfig::UPGRADE_KEY_RAPID_DEPLOYMENT_III,
        ],
        self::UPGRADE_SET_KEY_ENGINEERING_CORPS => [
            VillageUpgradeConfig::UPGRADE_KEY_ENGINEERING_CORPS_I,
            VillageUpgradeConfig::UPGRADE_KEY_ENGINEERING_CORPS_II,
            VillageUpgradeConfig::UPGRADE_KEY_ENGINEERING_CORPS_III,
        ],
        self::UPGRADE_SET_KEY_FORTIFICATIONS => [
            VillageUpgradeConfig::UPGRADE_KEY_FORTIFICATIONS_I,
            VillageUpgradeConfig::UPGRADE_KEY_FORTIFICATIONS_II,
            VillageUpgradeConfig::UPGRADE_KEY_FORTIFICATIONS_III,
        ],
        self::UPGRADE_SET_KEY_BULK_SUPPLIERS => [
            VillageUpgradeConfig::UPGRADE_KEY_BULK_SUPPLIERS_I,
            VillageUpgradeConfig::UPGRADE_KEY_BULK_SUPPLIERS_II,
            VillageUpgradeConfig::UPGRADE_KEY_BULK_SUPPLIERS_III,
        ],
        self::UPGRADE_SET_KEY_FARMERS_MARKET => [
            VillageUpgradeConfig::UPGRADE_KEY_FARMERS_MARKET_I,
            VillageUpgradeConfig::UPGRADE_KEY_FARMERS_MARKET_II,
            VillageUpgradeConfig::UPGRADE_KEY_FARMERS_MARKET_III,
        ],
        self::UPGRADE_SET_KEY_MERCHANTS_GUILD => [
            VillageUpgradeConfig::UPGRADE_KEY_MERCHANTS_GUILD_I,
            VillageUpgradeConfig::UPGRADE_KEY_MERCHANTS_GUILD_II,
            VillageUpgradeConfig::UPGRADE_KEY_MERCHANTS_GUILD_III,
        ],
        self::UPGRADE_SET_KEY_WAREHOUSES => [
            VillageUpgradeConfig::UPGRADE_KEY_WAREHOUSES_I,
            VillageUpgradeConfig::UPGRADE_KEY_WAREHOUSES_II,
        ],
        self::UPGRADE_SET_KEY_ANCESTRAL_LEGACY => [
            VillageUpgradeConfig::UPGRADE_KEY_ANCESTRAL_LEGACY_I,
            VillageUpgradeConfig::UPGRADE_KEY_ANCESTRAL_LEGACY_II,
            VillageUpgradeConfig::UPGRADE_KEY_ANCESTRAL_LEGACY_III,
        ],
        self::UPGRADE_SET_KEY_FORTUNES_BOUNTY => [
            VillageUpgradeConfig::UPGRADE_KEY_FORTUNES_BOUNTY_I,
            VillageUpgradeConfig::UPGRADE_KEY_FORTUNES_BOUNTY_II,
            VillageUpgradeConfig::UPGRADE_KEY_FORTUNES_BOUNTY_III,
        ],
        self::UPGRADE_SET_KEY_NEW_RECIPE => [
            VillageUpgradeConfig::UPGRADE_KEY_NEW_RECIPE_I,
        ],
        self::UPGRADE_SET_KEY_QUALITY_INGREDIENTS => [
            VillageUpgradeConfig::UPGRADE_KEY_QUALITY_INGREDIENTS_I,
            VillageUpgradeConfig::UPGRADE_KEY_QUALITY_INGREDIENTS_II,
            VillageUpgradeConfig::UPGRADE_KEY_QUALITY_INGREDIENTS_III,
        ],
        self::UPGRADE_SET_KEY_NINJA_FRIENDLY_RATES => [
            VillageUpgradeConfig::UPGRADE_KEY_NINJA_FRIENDLY_RATES_I,
            VillageUpgradeConfig::UPGRADE_KEY_NINJA_FRIENDLY_RATES_II,
            VillageUpgradeConfig::UPGRADE_KEY_NINJA_FRIENDLY_RATES_III,
        ],
        self::UPGRADE_SET_KEY_INSPIRED_ITAMAE => [
            VillageUpgradeConfig::UPGRADE_KEY_INSPIRED_ITAMAE_I,
            VillageUpgradeConfig::UPGRADE_KEY_LUCK_IN_LEFTOVERS_I,
            VillageUpgradeConfig::UPGRADE_KEY_LUCK_IN_LEFTOVERS_II,
        ],
    ];
}