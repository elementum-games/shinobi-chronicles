<?php

require_once __DIR__ . "/VillageBuildingConfig.php";

class VillageUpgradeConfig {
    /* status options for an instance of an upgrade */
    const UPGRADE_STATUS_LOCKED = 'locked'; // default status
    const UPGRADE_STATUS_RESEARCHING = 'researching'; // used when unlocking
    const UPGRADE_STATUS_UNLOCKED = 'unlocked'; // state for unlocked, permanent upgrades
    const UPGRADE_STATUS_INACTIVE = 'inactive'; // state for unlocked, toggled OFF upgrades
    const UPGRADE_STATUS_ACTIVE = 'active'; // stat for unlocked, toggle ON upgrades

    /* keys used for individual upgrade identifiers */
    const UPGRADE_KEY_TEST = 'TEST';
    const UPGRADE_KEY_RESEARCH_I = 'RESEARCH_I';
    const UPGRADE_KEY_RESEARCH_II = 'RESEARCH_II';
    const UPGRADE_KEY_RESEARCH_III = 'RESEARCH_III';
    const UPGRADE_KEY_RESEARCH_SUBSIDIES_I = 'RESEARCH_SUBSIDIES_I';
    const UPGRADE_KEY_RESEARCH_SUBSIDIES_II = 'RESEARCH_SUBSIDIES_II';
    const UPGRADE_KEY_RESEARCH_SUBSIDIES_III = 'RESEARCH_SUBSIDIES_III';
    const UPGRADE_KEY_ADMINISTRATION_I = 'ADMINISTRATION_I';
    const UPGRADE_KEY_ADMINISTRATION_II = 'ADMINISTRATION_II';
    const UPGRADE_KEY_ADMINISTRATION_III = 'ADMINISTRATION_III';
    const UPGRADE_KEY_POWER_PROJECTION_I = 'POWER_PROJECTION_I';
    const UPGRADE_KEY_POWER_PROJECTION_II = 'POWER_PROJECTION_II';
    const UPGRADE_KEY_POWER_PROJECTION_III = 'POWER_PROJECTION_III';
    const UPGRADE_KEY_EDUCATION_SUBSIDIES_I = 'EDUCATION_SUBSIDIES_I';
    const UPGRADE_KEY_EDUCATION_SUBSIDIES_II = 'EDUCATION_SUBSIDIES_II';
    const UPGRADE_KEY_EDUCATION_SUBSIDIES_III = 'EDUCATION_SUBSIDIES_III';
    const UPGRADE_KEY_TRAINING_GROUNDS_I = 'TRAINING_GROUNDS_I';
    const UPGRADE_KEY_TRAINING_GROUNDS_II = 'TRAINING_GROUNDS_II';
    const UPGRADE_KEY_TRAINING_GROUNDS_III = 'TRAINING_GROUNDS_III';
    const UPGRADE_KEY_MEDICAL_SUBSIDIES_I = 'MEDICAL_SUBSIDIES_I';
    const UPGRADE_KEY_MEDICAL_SUBSIDIES_II = 'MEDICAL_SUBSIDIES_II';
    const UPGRADE_KEY_MEDICAL_SUBSIDIES_III = 'MEDICAL_SUBSIDIES_III';
    const UPGRADE_KEY_HERBICULTURE_I = 'HERBICULTURE_I';
    const UPGRADE_KEY_HERBICULTURE_II = 'HERBICULTURE_II';
    const UPGRADE_KEY_HERBICULTURE_III = 'HERBICULTURE_III';
    const UPGRADE_KEY_MILITARY_SUBSIDIES_I = 'MILITARY_SUBSIDIES_I';
    const UPGRADE_KEY_MILITARY_SUBSIDIES_II = 'MILITARY_SUBSIDIES_II';
    const UPGRADE_KEY_MILITARY_SUBSIDIES_III = 'MILITARY_SUBSIDIES_III';
    const UPGRADE_KEY_ASSAULT_TRAINING_I = 'ASSAULT_TRAINING_I';
    const UPGRADE_KEY_ASSAULT_TRAINING_II = 'ASSAULT_TRAINING_II';
    const UPGRADE_KEY_ASSAULT_TRAINING_III = 'ASSAULT_TRAINING_III';
    const UPGRADE_KEY_SALTED_EARTH_I = 'SALTED_EARTH_I';
    const UPGRADE_KEY_SALTED_EARTH_II = 'SALTED_EARTH_II';
    const UPGRADE_KEY_SALTED_EARTH_III = 'SALTED_EARTH_III';
    const UPGRADE_KEY_UNDERWORLD_CONNECTIONS_I = 'UNDERWORLD_CONNECTIONS_I';
    const UPGRADE_KEY_UNDERWORLD_CONNECTIONS_II = 'UNDERWORLD_CONNECTIONS_II';
    const UPGRADE_KEY_UNDERWORLD_CONNECTIONS_III = 'UNDERWORLD_CONNECTIONS_III';
    const UPGRADE_KEY_GUERRILLA_WARFARE_I = 'GUERRILLA_WARFARE_I';
    const UPGRADE_KEY_GUERRILLA_WARFARE_II = 'GUERRILLA_WARFARE_II';
    const UPGRADE_KEY_GUERRILLA_WARFARE_III = 'GUERRILLA_WARFARE_III';
    const UPGRADE_KEY_SELECTIVE_RECRUITMENT_I = 'SELECTIVE_RECRUITMENT_I';
    const UPGRADE_KEY_SELECTIVE_RECRUITMENT_II = 'SELECTIVE_RECRUITMENT_II';
    const UPGRADE_KEY_SELECTIVE_RECRUITMENT_III = 'SELECTIVE_RECRUITMENT_III';
    const UPGRADE_KEY_CONSTRUCTION_I = 'CONSTRUCTION_I';
    const UPGRADE_KEY_CONSTRUCTION_II = 'CONSTRUCTION_II';
    const UPGRADE_KEY_CONSTRUCTION_III = 'CONSTRUCTION_III';
    const UPGRADE_KEY_ENGINEERING_SUBSIDIES_I = 'ENGINEERING_SUBSIDIES_I';
    const UPGRADE_KEY_ENGINEERING_SUBSIDIES_II = 'ENGINEERING_SUBSIDIES_II';
    const UPGRADE_KEY_ENGINEERING_SUBSIDIES_III = 'ENGINEERING_SUBSIDIES_III';
    const UPGRADE_KEY_RAPID_DEPLOYMENT_I = 'RAPID_DEPLOYMENT_I';
    const UPGRADE_KEY_RAPID_DEPLOYMENT_II = 'RAPID_DEPLOYMENT_II';
    const UPGRADE_KEY_RAPID_DEPLOYMENT_III = 'RAPID_DEPLOYMENT_III';
    const UPGRADE_KEY_ENGINEERING_CORPS_I = 'ENGINEERING_CORPS_I';
    const UPGRADE_KEY_ENGINEERING_CORPS_II = 'ENGINEERING_CORPS_II';
    const UPGRADE_KEY_ENGINEERING_CORPS_III = 'ENGINEERING_CORPS_III';
    const UPGRADE_KEY_FORTIFICATIONS_I = 'FORTIFICATIONS_I';
    const UPGRADE_KEY_FORTIFICATIONS_II = 'FORTIFICATIONS_II';
    const UPGRADE_KEY_FORTIFICATIONS_III = 'FORTIFICATIONS_III';
    const UPGRADE_KEY_BULK_SUPPLIERS_I = 'BULK_SUPPLIERS_I';
    const UPGRADE_KEY_BULK_SUPPLIERS_II = 'BULK_SUPPLIERS_II';
    const UPGRADE_KEY_BULK_SUPPLIERS_III = 'BULK_SUPPLIERS_III';
    const UPGRADE_KEY_FARMERS_MARKET_I = 'FARMERS_MARKET_I';
    const UPGRADE_KEY_FARMERS_MARKET_II = 'FARMERS_MARKET_II';
    const UPGRADE_KEY_FARMERS_MARKET_III = 'FARMERS_MARKET_III';
    const UPGRADE_KEY_MERCHANTS_GUILD_I = 'MERCHANTS_GUILD_I';
    const UPGRADE_KEY_MERCHANTS_GUILD_II = 'MERCHANTS_GUILD_II';
    const UPGRADE_KEY_MERCHANTS_GUILD_III = 'MERCHANTS_GUILD_III';
    const UPGRADE_KEY_WAREHOUSES_I = 'WAREHOUSES_I';
    const UPGRADE_KEY_WAREHOUSES_II = 'WAREHOUSES_II';
    const UPGRADE_KEY_ANCESTRAL_LEGACY_I = 'ANCESTRAL_LEGACY_I';
    const UPGRADE_KEY_ANCESTRAL_LEGACY_II = 'ANCESTRAL_LEGACY_II';
    const UPGRADE_KEY_ANCESTRAL_LEGACY_III = 'ANCESTRAL_LEGACY_III';
    const UPGRADE_KEY_FORTUNES_BOUNTY_I = 'FORTUNES_BOUNTY_I';
    const UPGRADE_KEY_FORTUNES_BOUNTY_II = 'FORTUNES_BOUNTY_II';
    const UPGRADE_KEY_FORTUNES_BOUNTY_III = 'FORTUNES_BOUNTY_III';
    const UPGRADE_KEY_NEW_RECIPE_I = 'NEW_RECIPE_I';
    const UPGRADE_KEY_QUALITY_INGREDIENTS_I = 'QUALITY_INGREDIENTS_I';
    const UPGRADE_KEY_QUALITY_INGREDIENTS_II = 'QUALITY_INGREDIENTS_II';
    const UPGRADE_KEY_QUALITY_INGREDIENTS_III = 'QUALITY_INGREDIENTS_III';
    const UPGRADE_KEY_SHINOBI_DISCOUNT_I = 'SHINOBI_DISCOUNT_I';
    const UPGRADE_KEY_SHINOBI_DISCOUNT_II = 'SHINOBI_DISCOUNT_II';
    const UPGRADE_KEY_SHINOBI_DISCOUNT_III = 'SHINOBI_DISCOUNT_III';
    const UPGRADE_KEY_INSPIRED_ITAMAE_I = 'INSPIRED_ITAMAE_I';
    const UPGRADE_KEY_LUCK_IN_LEFTOVERS_I = 'LUCK_IN_LEFTOVERS_I';
    const UPGRADE_KEY_LUCK_IN_LEFTOVERS_II = 'LUCK_IN_LEFTOVERS_II';

    /* constant used to identify individual effects that may be present in multiple upgrades */
    const UPGRADE_EFFECT_MATERIALS_UPKEEP = 'MATERIALS_UPKEEP';
    const UPGRADE_EFFECT_FOOD_UPKEEP = 'FOOD_UPKEEP';
    const UPGRADE_EFFECT_WEALTH_UPKEEP = 'WEALTH_UPKEEP';
    const UPGRADE_EFFECT_MATERIALS_PRODUCTION = 'MATERIALS_PRODUCTION';
    const UPGRADE_EFFECT_FOOD_PRODUCTION = 'FOOD_PRODUCTION';
    const UPGRADE_EFFECT_WEALTH_PRODUCTION = 'WEALTH_PRODUCTION';
    const UPGRADE_EFFECT_CONSTRUCTION_SPEED = 'CONSTRUCTION_SPEED';
    const UPGRADE_EFFECT_RESEARCH_SPEED = 'RESEARCH_SPEED';
    const UPGRADE_EFFECT_RESEARCH_T1_ENABLED = 'RESEARCH_T1';
    const UPGRADE_EFFECT_RESEARCH_T2_ENABLED = 'RESEARCH_T2_ENABLED';
    const UPGRADE_EFFECT_RESEARCH_T3_ENABLED = 'RESEARCH_T3_ENABLED';
    const UPGRADE_EFFECT_CONSTRUCTION_T1_ENABLED = 'CONSTRUCTION_T1_ENABLED';
    const UPGRADE_EFFECT_CONSTRUCTION_T2_ENABLED = 'CONSTRUCTION_T2_ENABLED';
    const UPGRADE_EFFECT_CONSTRUCTION_T3_ENABLED = 'CONSTRUCTION_T3_ENABLED';
    const UPGRADE_EFFECT_UPGRADE_UPKEEP = 'UPGRADE_UPKEEP';
    const UPGRADE_EFFECT_BASE_STABILITY = 'BASE_STABILITY';
    const UPGRADE_EFFECT_MAX_STABILITY = 'MAX_STABILITY';
    const UPGRADE_EFFECT_TRAINING_SPEED = 'TRAINING_SPEED';
    const UPGRADE_EFFECT_DOUBLE_BATTLE_XP_CHANCE = 'DOUBLE_BATTLE_XP_CHANCE';
    const UPGRADE_EFFECT_VILLAGE_REGEN = 'VILLAGE_REGEN';
    const UPGRADE_EFFECT_HEAL_ITEM_COST = 'HEAL_ITEM_COST';
    const UPGRADE_EFFECT_WAR_ACTION_COST = 'WAR_ACTION_COST';
    const UPGRADE_EFFECT_RAID_SPEED = 'RAID_SPEED';
    const UPGRADE_EFFECT_RAID_DAMAGE = 'RAID_DAMAGE';
    const UPGRADE_EFFECT_INFILTRATE_SPEED = 'INFILTRATE_SPEED';
    const UPGRADE_EFFECT_OCCUPIED_BASE_STABILITY = 'OCCUPIED_BASE_STABILITY';
    const UPGRADE_EFFECT_OCCUPIED_MAX_STABILITY = 'OCCUPIED_MAX_STABILITY';
    const UPGRADE_EFFECT_PATROL_TIER_CHANCE = 'PATROL_TIER_CHANCE';
    const UPGRADE_EFFECT_REINFORCE_SPEED = 'REINFORCE_SPEED';
    const UPGRADE_EFFECT_REINFORCE_HEAL = 'REINFORCE_HEAL';
    const UPGRADE_EFFECT_CASTLE_HP = 'CASTLE_HP';
    const UPGRADE_EFFECT_TOWN_HP = 'TOWN_HP';
    const UPGRADE_EFFECT_RESOURCE_CAPACITY = 'RESOURCE_CAPACITY';
    const UPGRADE_EFFECT_BLOODLINE_CHANCE = 'BLOODLINE_CHANCE';
    const UPGRADE_EFFECT_DOUBLE_BATTLE_YEN_CHANCE = 'DOUBLE_BATTLE_YEN_CHANCE';
    const UPGRADE_EFFECT_RAMEN_SET_ONE = 'RAMEN_SET_ONE';
    const UPGRADE_EFFECT_RAMEN_DURATION = 'RAMEN_DURATION';
    const UPGRADE_EFFECT_RAMEN_COST = 'RAMEN_COST';
    const UPGRADE_EFFECT_MYSTERY_RAMEN_ENABLED = 'MYSTERY_RAMEN_ENABLED';
    const UPGRADE_EFFECT_MYSTERY_RAMEN_CHANCE = 'MYSTERY_RAMEN_CHANCE';

    /* research requirement types */
    const UPGRADE_REQUIREMENT_BUILDINGS = "BUILDINGS";
    const UPGRADE_REQUIREMENT_UPGRADES = "UPGRADES";

    /* display names for each upgrade indexed by upgrade key */
    const UPGRADE_NAMES = [
        VillageUpgradeConfig::UPGRADE_KEY_TEST => 'Test Upgrade',
        VillageUpgradeConfig::UPGRADE_KEY_RESEARCH_I => 'Research I',
        VillageUpgradeConfig::UPGRADE_KEY_RESEARCH_II => 'Research II',
        VillageUpgradeConfig::UPGRADE_KEY_RESEARCH_III => 'Research III',
        VillageUpgradeConfig::UPGRADE_KEY_RESEARCH_III => 'Research III',
        VillageUpgradeConfig::UPGRADE_KEY_RESEARCH_SUBSIDIES_I => 'Research Subsidies I',
        VillageUpgradeConfig::UPGRADE_KEY_RESEARCH_SUBSIDIES_II => 'Research Subsidies II',
        VillageUpgradeConfig::UPGRADE_KEY_RESEARCH_SUBSIDIES_III => 'Research Subsidies III',
        VillageUpgradeConfig::UPGRADE_KEY_ADMINISTRATION_I => 'Administration I',
        VillageUpgradeConfig::UPGRADE_KEY_ADMINISTRATION_II => 'Administration II',
        VillageUpgradeConfig::UPGRADE_KEY_ADMINISTRATION_III => 'Administration III',
        VillageUpgradeConfig::UPGRADE_KEY_POWER_PROJECTION_I => 'Power Projection I',
        VillageUpgradeConfig::UPGRADE_KEY_POWER_PROJECTION_II => 'Power Projection II',
        VillageUpgradeConfig::UPGRADE_KEY_POWER_PROJECTION_III => 'Power Projection III',
        VillageUpgradeConfig::UPGRADE_KEY_EDUCATION_SUBSIDIES_I => 'Education Subsidies I',
        VillageUpgradeConfig::UPGRADE_KEY_EDUCATION_SUBSIDIES_II => 'Education Subsidies II',
        VillageUpgradeConfig::UPGRADE_KEY_EDUCATION_SUBSIDIES_III => 'Education Subsidies III',
        VillageUpgradeConfig::UPGRADE_KEY_TRAINING_GROUNDS_I => 'Training Grounds I',
        VillageUpgradeConfig::UPGRADE_KEY_TRAINING_GROUNDS_II => 'Training Grounds II',
        VillageUpgradeConfig::UPGRADE_KEY_TRAINING_GROUNDS_III => 'Training Grounds III',
        VillageUpgradeConfig::UPGRADE_KEY_MEDICAL_SUBSIDIES_I => 'Medical Subsidies I',
        VillageUpgradeConfig::UPGRADE_KEY_MEDICAL_SUBSIDIES_II => 'Medical Subsidies II',
        VillageUpgradeConfig::UPGRADE_KEY_MEDICAL_SUBSIDIES_III => 'Medical Subsidies III',
        VillageUpgradeConfig::UPGRADE_KEY_HERBICULTURE_I => 'Herbiculture I',
        VillageUpgradeConfig::UPGRADE_KEY_HERBICULTURE_II => 'Herbiculture II',
        VillageUpgradeConfig::UPGRADE_KEY_HERBICULTURE_III => 'Herbiculture III',
        VillageUpgradeConfig::UPGRADE_KEY_MILITARY_SUBSIDIES_I => 'Military Subsidies I',
        VillageUpgradeConfig::UPGRADE_KEY_MILITARY_SUBSIDIES_II => 'Military Subsidies II',
        VillageUpgradeConfig::UPGRADE_KEY_MILITARY_SUBSIDIES_III => 'Military Subsidies III',
        VillageUpgradeConfig::UPGRADE_KEY_ASSAULT_TRAINING_I => 'Assault Training I',
        VillageUpgradeConfig::UPGRADE_KEY_ASSAULT_TRAINING_II => 'Assault Training II',
        VillageUpgradeConfig::UPGRADE_KEY_ASSAULT_TRAINING_III => 'Assault Training III',
        VillageUpgradeConfig::UPGRADE_KEY_SALTED_EARTH_I => 'Salted Earth I',
        VillageUpgradeConfig::UPGRADE_KEY_SALTED_EARTH_II => 'Salted Earth II',
        VillageUpgradeConfig::UPGRADE_KEY_SALTED_EARTH_III => 'Salted Earth III',
        VillageUpgradeConfig::UPGRADE_KEY_UNDERWORLD_CONNECTIONS_I => 'Underworld Connections I',
        VillageUpgradeConfig::UPGRADE_KEY_UNDERWORLD_CONNECTIONS_II => 'Underworld Connections II',
        VillageUpgradeConfig::UPGRADE_KEY_UNDERWORLD_CONNECTIONS_III => 'Underworld Connections III',
        VillageUpgradeConfig::UPGRADE_KEY_GUERRILLA_WARFARE_I => 'Guerrilla Warfare I',
        VillageUpgradeConfig::UPGRADE_KEY_GUERRILLA_WARFARE_II => 'Guerrilla Warfare II',
        VillageUpgradeConfig::UPGRADE_KEY_GUERRILLA_WARFARE_III => 'Guerrilla Warfare III',
        VillageUpgradeConfig::UPGRADE_KEY_SELECTIVE_RECRUITMENT_I => 'Selective Recruitment I',
        VillageUpgradeConfig::UPGRADE_KEY_SELECTIVE_RECRUITMENT_II => 'Selective Recruitment II',
        VillageUpgradeConfig::UPGRADE_KEY_SELECTIVE_RECRUITMENT_III => 'Selective Recruitment III',
        VillageUpgradeConfig::UPGRADE_KEY_CONSTRUCTION_I => 'Construction I',
        VillageUpgradeConfig::UPGRADE_KEY_CONSTRUCTION_II => 'Construction II',
        VillageUpgradeConfig::UPGRADE_KEY_CONSTRUCTION_III => 'Construction III',
        VillageUpgradeConfig::UPGRADE_KEY_ENGINEERING_SUBSIDIES_I => 'Engineering Subsidies I',
        VillageUpgradeConfig::UPGRADE_KEY_ENGINEERING_SUBSIDIES_II => 'Engineering Subsidies II',
        VillageUpgradeConfig::UPGRADE_KEY_ENGINEERING_SUBSIDIES_III => 'Engineering Subsidies III',
        VillageUpgradeConfig::UPGRADE_KEY_RAPID_DEPLOYMENT_I => 'Rapid Deployment I',
        VillageUpgradeConfig::UPGRADE_KEY_RAPID_DEPLOYMENT_II => 'Rapid Deployment II',
        VillageUpgradeConfig::UPGRADE_KEY_RAPID_DEPLOYMENT_III => 'Rapid Deployment III',
        VillageUpgradeConfig::UPGRADE_KEY_ENGINEERING_CORPS_I => 'Engineering Corps I',
        VillageUpgradeConfig::UPGRADE_KEY_ENGINEERING_CORPS_II => 'Engineering Corps II',
        VillageUpgradeConfig::UPGRADE_KEY_ENGINEERING_CORPS_III => 'Engineering Corps III',
        VillageUpgradeConfig::UPGRADE_KEY_FORTIFICATIONS_I => 'Fortifications I',
        VillageUpgradeConfig::UPGRADE_KEY_FORTIFICATIONS_II => 'Fortifications II',
        VillageUpgradeConfig::UPGRADE_KEY_FORTIFICATIONS_III => 'Fortifications III',
        VillageUpgradeConfig::UPGRADE_KEY_BULK_SUPPLIERS_I => 'Bulk Suppliers I',
        VillageUpgradeConfig::UPGRADE_KEY_BULK_SUPPLIERS_II => 'Bulk Suppliers II',
        VillageUpgradeConfig::UPGRADE_KEY_BULK_SUPPLIERS_III => 'Bulk Suppliers III',
        VillageUpgradeConfig::UPGRADE_KEY_FARMERS_MARKET_I => 'Farmers Market I',
        VillageUpgradeConfig::UPGRADE_KEY_FARMERS_MARKET_II => 'Farmers Market II',
        VillageUpgradeConfig::UPGRADE_KEY_FARMERS_MARKET_III => 'Farmers Market III',
        VillageUpgradeConfig::UPGRADE_KEY_MERCHANTS_GUILD_I => 'Merchants Guild I',
        VillageUpgradeConfig::UPGRADE_KEY_MERCHANTS_GUILD_II => 'Merchants Guild II',
        VillageUpgradeConfig::UPGRADE_KEY_MERCHANTS_GUILD_III => 'Merchants Guild III',
        VillageUpgradeConfig::UPGRADE_KEY_WAREHOUSES_I => 'Warehouses I',
        VillageUpgradeConfig::UPGRADE_KEY_WAREHOUSES_II => 'Warehouses II',
        VillageUpgradeConfig::UPGRADE_KEY_ANCESTRAL_LEGACY_I => 'Ancestral Legacy I',
        VillageUpgradeConfig::UPGRADE_KEY_ANCESTRAL_LEGACY_II => 'Ancestral Legacy II',
        VillageUpgradeConfig::UPGRADE_KEY_ANCESTRAL_LEGACY_III => 'Ancestral Legacy III',
        VillageUpgradeConfig::UPGRADE_KEY_FORTUNES_BOUNTY_I => 'Fortunes Bounty I',
        VillageUpgradeConfig::UPGRADE_KEY_FORTUNES_BOUNTY_II => 'Fortunes Bounty II',
        VillageUpgradeConfig::UPGRADE_KEY_FORTUNES_BOUNTY_III => 'Fortunes Bounty III',
        VillageUpgradeConfig::UPGRADE_KEY_NEW_RECIPE_I => 'New Recipe I',
        VillageUpgradeConfig::UPGRADE_KEY_QUALITY_INGREDIENTS_I => 'Quality Ingredients I',
        VillageUpgradeConfig::UPGRADE_KEY_QUALITY_INGREDIENTS_II => 'Quality Ingredients II',
        VillageUpgradeConfig::UPGRADE_KEY_QUALITY_INGREDIENTS_III => 'Quality Ingredients III',
        VillageUpgradeConfig::UPGRADE_KEY_SHINOBI_DISCOUNT_I => 'Shinobi Discount I',
        VillageUpgradeConfig::UPGRADE_KEY_SHINOBI_DISCOUNT_II => 'Shinobi Discount II',
        VillageUpgradeConfig::UPGRADE_KEY_SHINOBI_DISCOUNT_III => 'Shinobi Discount III',
        VillageUpgradeConfig::UPGRADE_KEY_INSPIRED_ITAMAE_I => 'Inspired Itamae I',
        VillageUpgradeConfig::UPGRADE_KEY_LUCK_IN_LEFTOVERS_I => 'Luck In Leftovers I',
        VillageUpgradeConfig::UPGRADE_KEY_LUCK_IN_LEFTOVERS_II => 'Luck In Leftovers II',
    ];

    /* research cost indexed by upgrade key, then resource type */
    const UPGRADE_RESEARCH_COST = [
        VillageUpgradeConfig::UPGRADE_KEY_TEST => [
            WarManager::RESOURCE_MATERIALS => 10000,
            WarManager::RESOURCE_FOOD => 20000,
            WarManager::RESOURCE_WEALTH => 30000,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_RESEARCH_I => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_RESEARCH_II => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_RESEARCH_III => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_RESEARCH_SUBSIDIES_I => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_RESEARCH_SUBSIDIES_II => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_RESEARCH_SUBSIDIES_III => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ADMINISTRATION_I => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ADMINISTRATION_II => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ADMINISTRATION_III => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_POWER_PROJECTION_I => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_POWER_PROJECTION_II => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_POWER_PROJECTION_III => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_EDUCATION_SUBSIDIES_I => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_EDUCATION_SUBSIDIES_II => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_EDUCATION_SUBSIDIES_III => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_TRAINING_GROUNDS_I => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_TRAINING_GROUNDS_II => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_TRAINING_GROUNDS_III => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_MEDICAL_SUBSIDIES_I => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_MEDICAL_SUBSIDIES_II => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_MEDICAL_SUBSIDIES_III => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_HERBICULTURE_I => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_HERBICULTURE_II => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_HERBICULTURE_III => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_MILITARY_SUBSIDIES_I => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_MILITARY_SUBSIDIES_II => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_MILITARY_SUBSIDIES_III => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ASSAULT_TRAINING_I => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ASSAULT_TRAINING_II => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ASSAULT_TRAINING_III => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_SALTED_EARTH_I => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_SALTED_EARTH_II => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_SALTED_EARTH_III => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_UNDERWORLD_CONNECTIONS_I => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_UNDERWORLD_CONNECTIONS_II => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_UNDERWORLD_CONNECTIONS_III => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_GUERRILLA_WARFARE_I => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_GUERRILLA_WARFARE_II => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_GUERRILLA_WARFARE_III => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_SELECTIVE_RECRUITMENT_I => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_SELECTIVE_RECRUITMENT_II => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_SELECTIVE_RECRUITMENT_III => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_CONSTRUCTION_I => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_CONSTRUCTION_II => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_CONSTRUCTION_III => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ENGINEERING_SUBSIDIES_I => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ENGINEERING_SUBSIDIES_II => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ENGINEERING_SUBSIDIES_III => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_RAPID_DEPLOYMENT_I => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_RAPID_DEPLOYMENT_II => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_RAPID_DEPLOYMENT_III => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ENGINEERING_CORPS_I => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ENGINEERING_CORPS_II => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ENGINEERING_CORPS_III => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_FORTIFICATIONS_I => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_FORTIFICATIONS_II => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_FORTIFICATIONS_III => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_BULK_SUPPLIERS_I => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_BULK_SUPPLIERS_II => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_BULK_SUPPLIERS_III => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_FARMERS_MARKET_I => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_FARMERS_MARKET_II => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_FARMERS_MARKET_III => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_MERCHANTS_GUILD_I => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_MERCHANTS_GUILD_II => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_MERCHANTS_GUILD_III => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_WAREHOUSES_I => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_WAREHOUSES_II => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ANCESTRAL_LEGACY_I => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ANCESTRAL_LEGACY_II => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ANCESTRAL_LEGACY_III => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_FORTUNES_BOUNTY_I => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_FORTUNES_BOUNTY_II => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_FORTUNES_BOUNTY_III => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_NEW_RECIPE_I => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_QUALITY_INGREDIENTS_I => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_QUALITY_INGREDIENTS_II => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_QUALITY_INGREDIENTS_III => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_SHINOBI_DISCOUNT_I => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_SHINOBI_DISCOUNT_II => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_SHINOBI_DISCOUNT_III => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_INSPIRED_ITAMAE_I => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_LUCK_IN_LEFTOVERS_I => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_LUCK_IN_LEFTOVERS_II => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
    ];

    /* research time indexed by upgrade key */
    const UPGRADE_RESEARCH_TIME = [
        VillageUpgradeConfig::UPGRADE_KEY_TEST => 25,
        VillageUpgradeConfig::UPGRADE_KEY_RESEARCH_I => 0,
        VillageUpgradeConfig::UPGRADE_KEY_RESEARCH_II => 5,
        VillageUpgradeConfig::UPGRADE_KEY_RESEARCH_III => 25,
        VillageUpgradeConfig::UPGRADE_KEY_RESEARCH_SUBSIDIES_I => 1,
        VillageUpgradeConfig::UPGRADE_KEY_RESEARCH_SUBSIDIES_II => 5,
        VillageUpgradeConfig::UPGRADE_KEY_RESEARCH_SUBSIDIES_III => 25,
        VillageUpgradeConfig::UPGRADE_KEY_ADMINISTRATION_I => 1,
        VillageUpgradeConfig::UPGRADE_KEY_ADMINISTRATION_II => 5,
        VillageUpgradeConfig::UPGRADE_KEY_ADMINISTRATION_III => 25,
        VillageUpgradeConfig::UPGRADE_KEY_POWER_PROJECTION_I => 1,
        VillageUpgradeConfig::UPGRADE_KEY_POWER_PROJECTION_II => 5,
        VillageUpgradeConfig::UPGRADE_KEY_POWER_PROJECTION_III => 25,
        VillageUpgradeConfig::UPGRADE_KEY_EDUCATION_SUBSIDIES_I => 1,
        VillageUpgradeConfig::UPGRADE_KEY_EDUCATION_SUBSIDIES_II => 5,
        VillageUpgradeConfig::UPGRADE_KEY_EDUCATION_SUBSIDIES_III => 25,
        VillageUpgradeConfig::UPGRADE_KEY_TRAINING_GROUNDS_I => 1,
        VillageUpgradeConfig::UPGRADE_KEY_TRAINING_GROUNDS_II => 5,
        VillageUpgradeConfig::UPGRADE_KEY_TRAINING_GROUNDS_III => 25,
        VillageUpgradeConfig::UPGRADE_KEY_MEDICAL_SUBSIDIES_I => 1,
        VillageUpgradeConfig::UPGRADE_KEY_MEDICAL_SUBSIDIES_II => 5,
        VillageUpgradeConfig::UPGRADE_KEY_MEDICAL_SUBSIDIES_III => 25,
        VillageUpgradeConfig::UPGRADE_KEY_HERBICULTURE_I => 1,
        VillageUpgradeConfig::UPGRADE_KEY_HERBICULTURE_II => 5,
        VillageUpgradeConfig::UPGRADE_KEY_HERBICULTURE_III => 25,
        VillageUpgradeConfig::UPGRADE_KEY_MILITARY_SUBSIDIES_I => 1,
        VillageUpgradeConfig::UPGRADE_KEY_MILITARY_SUBSIDIES_II => 5,
        VillageUpgradeConfig::UPGRADE_KEY_MILITARY_SUBSIDIES_III => 25,
        VillageUpgradeConfig::UPGRADE_KEY_ASSAULT_TRAINING_I => 1,
        VillageUpgradeConfig::UPGRADE_KEY_ASSAULT_TRAINING_II => 5,
        VillageUpgradeConfig::UPGRADE_KEY_ASSAULT_TRAINING_III => 25,
        VillageUpgradeConfig::UPGRADE_KEY_SALTED_EARTH_I => 1,
        VillageUpgradeConfig::UPGRADE_KEY_SALTED_EARTH_II => 5,
        VillageUpgradeConfig::UPGRADE_KEY_SALTED_EARTH_III => 25,
        VillageUpgradeConfig::UPGRADE_KEY_UNDERWORLD_CONNECTIONS_I => 1,
        VillageUpgradeConfig::UPGRADE_KEY_UNDERWORLD_CONNECTIONS_II => 5,
        VillageUpgradeConfig::UPGRADE_KEY_UNDERWORLD_CONNECTIONS_III => 25,
        VillageUpgradeConfig::UPGRADE_KEY_GUERRILLA_WARFARE_I => 1,
        VillageUpgradeConfig::UPGRADE_KEY_GUERRILLA_WARFARE_II => 5,
        VillageUpgradeConfig::UPGRADE_KEY_GUERRILLA_WARFARE_III => 25,
        VillageUpgradeConfig::UPGRADE_KEY_SELECTIVE_RECRUITMENT_I => 1,
        VillageUpgradeConfig::UPGRADE_KEY_SELECTIVE_RECRUITMENT_II => 5,
        VillageUpgradeConfig::UPGRADE_KEY_SELECTIVE_RECRUITMENT_III => 25,
        VillageUpgradeConfig::UPGRADE_KEY_CONSTRUCTION_I => 0,
        VillageUpgradeConfig::UPGRADE_KEY_CONSTRUCTION_II => 5,
        VillageUpgradeConfig::UPGRADE_KEY_CONSTRUCTION_III => 25,
        VillageUpgradeConfig::UPGRADE_KEY_ENGINEERING_SUBSIDIES_I => 1,
        VillageUpgradeConfig::UPGRADE_KEY_ENGINEERING_SUBSIDIES_II => 5,
        VillageUpgradeConfig::UPGRADE_KEY_ENGINEERING_SUBSIDIES_III => 25,
        VillageUpgradeConfig::UPGRADE_KEY_RAPID_DEPLOYMENT_I => 1,
        VillageUpgradeConfig::UPGRADE_KEY_RAPID_DEPLOYMENT_II => 5,
        VillageUpgradeConfig::UPGRADE_KEY_RAPID_DEPLOYMENT_III => 25,
        VillageUpgradeConfig::UPGRADE_KEY_ENGINEERING_CORPS_I => 1,
        VillageUpgradeConfig::UPGRADE_KEY_ENGINEERING_CORPS_II => 5,
        VillageUpgradeConfig::UPGRADE_KEY_ENGINEERING_CORPS_III => 25,
        VillageUpgradeConfig::UPGRADE_KEY_FORTIFICATIONS_I => 1,
        VillageUpgradeConfig::UPGRADE_KEY_FORTIFICATIONS_II => 5,
        VillageUpgradeConfig::UPGRADE_KEY_FORTIFICATIONS_III => 25,
        VillageUpgradeConfig::UPGRADE_KEY_BULK_SUPPLIERS_I => 1,
        VillageUpgradeConfig::UPGRADE_KEY_BULK_SUPPLIERS_II => 5,
        VillageUpgradeConfig::UPGRADE_KEY_BULK_SUPPLIERS_III => 25,
        VillageUpgradeConfig::UPGRADE_KEY_FARMERS_MARKET_I => 1,
        VillageUpgradeConfig::UPGRADE_KEY_FARMERS_MARKET_II => 5,
        VillageUpgradeConfig::UPGRADE_KEY_FARMERS_MARKET_III => 25,
        VillageUpgradeConfig::UPGRADE_KEY_MERCHANTS_GUILD_I => 1,
        VillageUpgradeConfig::UPGRADE_KEY_MERCHANTS_GUILD_II => 5,
        VillageUpgradeConfig::UPGRADE_KEY_MERCHANTS_GUILD_III => 25,
        VillageUpgradeConfig::UPGRADE_KEY_WAREHOUSES_I => 3,
        VillageUpgradeConfig::UPGRADE_KEY_WAREHOUSES_II => 15,
        VillageUpgradeConfig::UPGRADE_KEY_ANCESTRAL_LEGACY_I => 1,
        VillageUpgradeConfig::UPGRADE_KEY_ANCESTRAL_LEGACY_II => 5,
        VillageUpgradeConfig::UPGRADE_KEY_ANCESTRAL_LEGACY_III => 25,
        VillageUpgradeConfig::UPGRADE_KEY_FORTUNES_BOUNTY_I => 1,
        VillageUpgradeConfig::UPGRADE_KEY_FORTUNES_BOUNTY_II => 5,
        VillageUpgradeConfig::UPGRADE_KEY_FORTUNES_BOUNTY_III => 25,
        VillageUpgradeConfig::UPGRADE_KEY_NEW_RECIPE_I => 1,
        VillageUpgradeConfig::UPGRADE_KEY_QUALITY_INGREDIENTS_I => 1,
        VillageUpgradeConfig::UPGRADE_KEY_QUALITY_INGREDIENTS_II => 5,
        VillageUpgradeConfig::UPGRADE_KEY_QUALITY_INGREDIENTS_III => 25,
        VillageUpgradeConfig::UPGRADE_KEY_SHINOBI_DISCOUNT_I => 1,
        VillageUpgradeConfig::UPGRADE_KEY_SHINOBI_DISCOUNT_II => 5,
        VillageUpgradeConfig::UPGRADE_KEY_SHINOBI_DISCOUNT_III => 25,
        VillageUpgradeConfig::UPGRADE_KEY_INSPIRED_ITAMAE_I => 1,
        VillageUpgradeConfig::UPGRADE_KEY_LUCK_IN_LEFTOVERS_I => 5,
        VillageUpgradeConfig::UPGRADE_KEY_LUCK_IN_LEFTOVERS_II => 25,
    ];

    /* research requirements indexed by upgrade key, then requirement type */
    const UPGRADE_RESEARCH_REQUIREMENTS = [
        VillageUpgradeConfig::UPGRADE_KEY_TEST => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
                VillageBuildingConfig::BUILDING_ACADEMY => 1,
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
                VillageUpgradeConfig::UPGRADE_KEY_RESEARCH_I,
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_RESEARCH_I => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_RESEARCH_II => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_RESEARCH_III => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_RESEARCH_SUBSIDIES_I => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_RESEARCH_SUBSIDIES_II => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_RESEARCH_SUBSIDIES_III => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ADMINISTRATION_I => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ADMINISTRATION_II => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ADMINISTRATION_III => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_POWER_PROJECTION_I => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_POWER_PROJECTION_II => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_POWER_PROJECTION_III => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_EDUCATION_SUBSIDIES_I => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_EDUCATION_SUBSIDIES_II => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_EDUCATION_SUBSIDIES_III => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_TRAINING_GROUNDS_I => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_TRAINING_GROUNDS_II => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_TRAINING_GROUNDS_III => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_MEDICAL_SUBSIDIES_I => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_MEDICAL_SUBSIDIES_II => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_MEDICAL_SUBSIDIES_III => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_HERBICULTURE_I => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_HERBICULTURE_II => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_HERBICULTURE_III => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_MILITARY_SUBSIDIES_I => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_MILITARY_SUBSIDIES_II => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_MILITARY_SUBSIDIES_III => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ASSAULT_TRAINING_I => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ASSAULT_TRAINING_II => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ASSAULT_TRAINING_III => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_SALTED_EARTH_I => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_SALTED_EARTH_II => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_SALTED_EARTH_III => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_UNDERWORLD_CONNECTIONS_I => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_UNDERWORLD_CONNECTIONS_II => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_UNDERWORLD_CONNECTIONS_III => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_GUERRILLA_WARFARE_I => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_GUERRILLA_WARFARE_II => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_GUERRILLA_WARFARE_III => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_SELECTIVE_RECRUITMENT_I => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_SELECTIVE_RECRUITMENT_II => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_SELECTIVE_RECRUITMENT_III => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_CONSTRUCTION_I => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_CONSTRUCTION_II => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_CONSTRUCTION_III => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ENGINEERING_SUBSIDIES_I => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ENGINEERING_SUBSIDIES_II => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ENGINEERING_SUBSIDIES_III => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_RAPID_DEPLOYMENT_I => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_RAPID_DEPLOYMENT_II => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_RAPID_DEPLOYMENT_III => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ENGINEERING_CORPS_I => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ENGINEERING_CORPS_II => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ENGINEERING_CORPS_III => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_FORTIFICATIONS_I => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_FORTIFICATIONS_II => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_FORTIFICATIONS_III => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_BULK_SUPPLIERS_I => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_BULK_SUPPLIERS_II => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_BULK_SUPPLIERS_III => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_FARMERS_MARKET_I => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_FARMERS_MARKET_II => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_FARMERS_MARKET_III => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_MERCHANTS_GUILD_I => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_MERCHANTS_GUILD_II => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_MERCHANTS_GUILD_III => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_WAREHOUSES_I => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_WAREHOUSES_II => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ANCESTRAL_LEGACY_I => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ANCESTRAL_LEGACY_II => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ANCESTRAL_LEGACY_III => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_FORTUNES_BOUNTY_I => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_FORTUNES_BOUNTY_II => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_FORTUNES_BOUNTY_III => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_NEW_RECIPE_I => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_QUALITY_INGREDIENTS_I => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_QUALITY_INGREDIENTS_II => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_QUALITY_INGREDIENTS_III => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_SHINOBI_DISCOUNT_I => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_SHINOBI_DISCOUNT_II => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_SHINOBI_DISCOUNT_III => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_INSPIRED_ITAMAE_I => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_LUCK_IN_LEFTOVERS_I => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_LUCK_IN_LEFTOVERS_II => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
    ];

    /* upkeep cost indexed by upgrade key, then resource type */
    const UPGRADE_UPKEEP = [
        VillageUpgradeConfig::UPGRADE_KEY_TEST => [
            WarManager::RESOURCE_MATERIALS => 5,
            WarManager::RESOURCE_FOOD => 10,
            WarManager::RESOURCE_WEALTH => 2,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_RESEARCH_I => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_RESEARCH_II => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_RESEARCH_III => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_RESEARCH_SUBSIDIES_I => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_RESEARCH_SUBSIDIES_II => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_RESEARCH_SUBSIDIES_III => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ADMINISTRATION_I => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ADMINISTRATION_II => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ADMINISTRATION_III => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_POWER_PROJECTION_I => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_POWER_PROJECTION_II => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_POWER_PROJECTION_III => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_EDUCATION_SUBSIDIES_I => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_EDUCATION_SUBSIDIES_II => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_EDUCATION_SUBSIDIES_III => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_TRAINING_GROUNDS_I => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_TRAINING_GROUNDS_II => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_TRAINING_GROUNDS_III => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_MEDICAL_SUBSIDIES_I => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_MEDICAL_SUBSIDIES_II => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_MEDICAL_SUBSIDIES_III => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_HERBICULTURE_I => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_HERBICULTURE_II => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_HERBICULTURE_III => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_MILITARY_SUBSIDIES_I => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_MILITARY_SUBSIDIES_II => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_MILITARY_SUBSIDIES_III => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ASSAULT_TRAINING_I => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ASSAULT_TRAINING_II => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ASSAULT_TRAINING_III => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_SALTED_EARTH_I => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_SALTED_EARTH_II => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_SALTED_EARTH_III => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_UNDERWORLD_CONNECTIONS_I => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_UNDERWORLD_CONNECTIONS_II => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_UNDERWORLD_CONNECTIONS_III => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_GUERRILLA_WARFARE_I => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_GUERRILLA_WARFARE_II => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_GUERRILLA_WARFARE_III => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_SELECTIVE_RECRUITMENT_I => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_SELECTIVE_RECRUITMENT_II => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_SELECTIVE_RECRUITMENT_III => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_CONSTRUCTION_I => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_CONSTRUCTION_II => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_CONSTRUCTION_III => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ENGINEERING_SUBSIDIES_I => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ENGINEERING_SUBSIDIES_II => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ENGINEERING_SUBSIDIES_III => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_RAPID_DEPLOYMENT_I => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_RAPID_DEPLOYMENT_II => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_RAPID_DEPLOYMENT_III => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ENGINEERING_CORPS_I => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ENGINEERING_CORPS_II => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ENGINEERING_CORPS_III => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_FORTIFICATIONS_I => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_FORTIFICATIONS_II => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_FORTIFICATIONS_III => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_BULK_SUPPLIERS_I => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_BULK_SUPPLIERS_II => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_BULK_SUPPLIERS_III => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_FARMERS_MARKET_I => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_FARMERS_MARKET_II => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_FARMERS_MARKET_III => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_MERCHANTS_GUILD_I => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_MERCHANTS_GUILD_II => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_MERCHANTS_GUILD_III => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_WAREHOUSES_I => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_WAREHOUSES_II => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ANCESTRAL_LEGACY_I => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ANCESTRAL_LEGACY_II => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ANCESTRAL_LEGACY_III => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_FORTUNES_BOUNTY_I => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_FORTUNES_BOUNTY_II => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_FORTUNES_BOUNTY_III => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_NEW_RECIPE_I => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_QUALITY_INGREDIENTS_I => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_QUALITY_INGREDIENTS_II => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_QUALITY_INGREDIENTS_III => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_SHINOBI_DISCOUNT_I => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_SHINOBI_DISCOUNT_II => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_SHINOBI_DISCOUNT_III => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_INSPIRED_ITAMAE_I => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_LUCK_IN_LEFTOVERS_I => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_LUCK_IN_LEFTOVERS_II => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
    ];

    /* upkeep effects indexed by upgrade key */
    const UPGRADE_EFFECTS = [
        VillageUpgradeConfig::UPGRADE_KEY_TEST => [
            VillageUpgradeConfig::UPGRADE_EFFECT_BASE_STABILITY => 5,
            VillageUpgradeConfig::UPGRADE_EFFECT_MAX_STABILITY => 2,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_RESEARCH_I => [
        ],
        VillageUpgradeConfig::UPGRADE_KEY_RESEARCH_II => [
        ],
        VillageUpgradeConfig::UPGRADE_KEY_RESEARCH_III => [
        ],
        VillageUpgradeConfig::UPGRADE_KEY_RESEARCH_SUBSIDIES_I => [
        ],
        VillageUpgradeConfig::UPGRADE_KEY_RESEARCH_SUBSIDIES_II => [
        ],
        VillageUpgradeConfig::UPGRADE_KEY_RESEARCH_SUBSIDIES_III => [
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ADMINISTRATION_I => [
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ADMINISTRATION_II => [
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ADMINISTRATION_III => [
        ],
        VillageUpgradeConfig::UPGRADE_KEY_POWER_PROJECTION_I => [
        ],
        VillageUpgradeConfig::UPGRADE_KEY_POWER_PROJECTION_II => [
        ],
        VillageUpgradeConfig::UPGRADE_KEY_POWER_PROJECTION_III => [
        ],
        VillageUpgradeConfig::UPGRADE_KEY_EDUCATION_SUBSIDIES_I => [
        ],
        VillageUpgradeConfig::UPGRADE_KEY_EDUCATION_SUBSIDIES_II => [
        ],
        VillageUpgradeConfig::UPGRADE_KEY_EDUCATION_SUBSIDIES_III => [
        ],
        VillageUpgradeConfig::UPGRADE_KEY_TRAINING_GROUNDS_I => [
        ],
        VillageUpgradeConfig::UPGRADE_KEY_TRAINING_GROUNDS_II => [
        ],
        VillageUpgradeConfig::UPGRADE_KEY_TRAINING_GROUNDS_III => [
        ],
        VillageUpgradeConfig::UPGRADE_KEY_MEDICAL_SUBSIDIES_I => [
        ],
        VillageUpgradeConfig::UPGRADE_KEY_MEDICAL_SUBSIDIES_II => [
        ],
        VillageUpgradeConfig::UPGRADE_KEY_MEDICAL_SUBSIDIES_III => [
        ],
        VillageUpgradeConfig::UPGRADE_KEY_HERBICULTURE_I => [
        ],
        VillageUpgradeConfig::UPGRADE_KEY_HERBICULTURE_II => [
        ],
        VillageUpgradeConfig::UPGRADE_KEY_HERBICULTURE_III => [
        ],
        VillageUpgradeConfig::UPGRADE_KEY_MILITARY_SUBSIDIES_I => [
        ],
        VillageUpgradeConfig::UPGRADE_KEY_MILITARY_SUBSIDIES_II => [
        ],
        VillageUpgradeConfig::UPGRADE_KEY_MILITARY_SUBSIDIES_III => [
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ASSAULT_TRAINING_I => [
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ASSAULT_TRAINING_II => [
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ASSAULT_TRAINING_III => [
        ],
        VillageUpgradeConfig::UPGRADE_KEY_SALTED_EARTH_I => [
        ],
        VillageUpgradeConfig::UPGRADE_KEY_SALTED_EARTH_II => [
        ],
        VillageUpgradeConfig::UPGRADE_KEY_SALTED_EARTH_III => [
        ],
        VillageUpgradeConfig::UPGRADE_KEY_UNDERWORLD_CONNECTIONS_I => [
        ],
        VillageUpgradeConfig::UPGRADE_KEY_UNDERWORLD_CONNECTIONS_II => [
        ],
        VillageUpgradeConfig::UPGRADE_KEY_UNDERWORLD_CONNECTIONS_III => [
        ],
        VillageUpgradeConfig::UPGRADE_KEY_GUERRILLA_WARFARE_I => [
        ],
        VillageUpgradeConfig::UPGRADE_KEY_GUERRILLA_WARFARE_II => [
        ],
        VillageUpgradeConfig::UPGRADE_KEY_GUERRILLA_WARFARE_III => [
        ],
        VillageUpgradeConfig::UPGRADE_KEY_SELECTIVE_RECRUITMENT_I => [
        ],
        VillageUpgradeConfig::UPGRADE_KEY_SELECTIVE_RECRUITMENT_II => [
        ],
        VillageUpgradeConfig::UPGRADE_KEY_SELECTIVE_RECRUITMENT_III => [
        ],
        VillageUpgradeConfig::UPGRADE_KEY_CONSTRUCTION_I => [
        ],
        VillageUpgradeConfig::UPGRADE_KEY_CONSTRUCTION_II => [
        ],
        VillageUpgradeConfig::UPGRADE_KEY_CONSTRUCTION_III => [
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ENGINEERING_SUBSIDIES_I => [
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ENGINEERING_SUBSIDIES_II => [
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ENGINEERING_SUBSIDIES_III => [
        ],
        VillageUpgradeConfig::UPGRADE_KEY_RAPID_DEPLOYMENT_I => [
        ],
        VillageUpgradeConfig::UPGRADE_KEY_RAPID_DEPLOYMENT_II => [
        ],
        VillageUpgradeConfig::UPGRADE_KEY_RAPID_DEPLOYMENT_III => [
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ENGINEERING_CORPS_I => [
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ENGINEERING_CORPS_II => [
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ENGINEERING_CORPS_III => [
        ],
        VillageUpgradeConfig::UPGRADE_KEY_FORTIFICATIONS_I => [
        ],
        VillageUpgradeConfig::UPGRADE_KEY_FORTIFICATIONS_II => [
        ],
        VillageUpgradeConfig::UPGRADE_KEY_FORTIFICATIONS_III => [
        ],
        VillageUpgradeConfig::UPGRADE_KEY_BULK_SUPPLIERS_I => [
        ],
        VillageUpgradeConfig::UPGRADE_KEY_BULK_SUPPLIERS_II => [
        ],
        VillageUpgradeConfig::UPGRADE_KEY_BULK_SUPPLIERS_III => [
        ],
        VillageUpgradeConfig::UPGRADE_KEY_FARMERS_MARKET_I => [
        ],
        VillageUpgradeConfig::UPGRADE_KEY_FARMERS_MARKET_II => [
        ],
        VillageUpgradeConfig::UPGRADE_KEY_FARMERS_MARKET_III => [
        ],
        VillageUpgradeConfig::UPGRADE_KEY_MERCHANTS_GUILD_I => [
        ],
        VillageUpgradeConfig::UPGRADE_KEY_MERCHANTS_GUILD_II => [
        ],
        VillageUpgradeConfig::UPGRADE_KEY_MERCHANTS_GUILD_III => [
        ],
        VillageUpgradeConfig::UPGRADE_KEY_WAREHOUSES_I => [
        ],
        VillageUpgradeConfig::UPGRADE_KEY_WAREHOUSES_II => [
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ANCESTRAL_LEGACY_I => [
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ANCESTRAL_LEGACY_II => [
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ANCESTRAL_LEGACY_III => [
        ],
        VillageUpgradeConfig::UPGRADE_KEY_FORTUNES_BOUNTY_I => [
        ],
        VillageUpgradeConfig::UPGRADE_KEY_FORTUNES_BOUNTY_II => [
        ],
        VillageUpgradeConfig::UPGRADE_KEY_FORTUNES_BOUNTY_III => [
        ],
        VillageUpgradeConfig::UPGRADE_KEY_NEW_RECIPE_I => [
        ],
        VillageUpgradeConfig::UPGRADE_KEY_QUALITY_INGREDIENTS_I => [
        ],
        VillageUpgradeConfig::UPGRADE_KEY_QUALITY_INGREDIENTS_II => [
        ],
        VillageUpgradeConfig::UPGRADE_KEY_QUALITY_INGREDIENTS_III => [
        ],
        VillageUpgradeConfig::UPGRADE_KEY_SHINOBI_DISCOUNT_I => [
        ],
        VillageUpgradeConfig::UPGRADE_KEY_SHINOBI_DISCOUNT_II => [
        ],
        VillageUpgradeConfig::UPGRADE_KEY_SHINOBI_DISCOUNT_III => [
        ],
        VillageUpgradeConfig::UPGRADE_KEY_INSPIRED_ITAMAE_I => [
        ],
        VillageUpgradeConfig::UPGRADE_KEY_LUCK_IN_LEFTOVERS_I => [
        ],
        VillageUpgradeConfig::UPGRADE_KEY_LUCK_IN_LEFTOVERS_II => [
        ],
    ];
}