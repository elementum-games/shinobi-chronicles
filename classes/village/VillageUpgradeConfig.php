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
    const UPGRADE_KEY_NINJA_FRIENDLY_RATES_I = 'NINJA_FRIENDLY_RATES_I';
    const UPGRADE_KEY_NINJA_FRIENDLY_RATES_II = 'NINJA_FRIENDLY_RATES_II';
    const UPGRADE_KEY_NINJA_FRIENDLY_RATES_III = 'NINJA_FRIENDLY_RATES_III';
    const UPGRADE_KEY_INSPIRED_ITAMAE_I = 'INSPIRED_ITAMAE_I';
    const UPGRADE_KEY_LUCK_IN_LEFTOVERS_I = 'LUCK_IN_LEFTOVERS_I';
    const UPGRADE_KEY_LUCK_IN_LEFTOVERS_II = 'LUCK_IN_LEFTOVERS_II';

    /* Used for auto population */
    const UPGRADE_KEYS = [
        VillageUpgradeConfig::UPGRADE_KEY_TEST,
        VillageUpgradeConfig::UPGRADE_KEY_RESEARCH_I,
        VillageUpgradeConfig::UPGRADE_KEY_RESEARCH_II,
        VillageUpgradeConfig::UPGRADE_KEY_RESEARCH_III,
        VillageUpgradeConfig::UPGRADE_KEY_RESEARCH_SUBSIDIES_I,
        VillageUpgradeConfig::UPGRADE_KEY_RESEARCH_SUBSIDIES_II,
        VillageUpgradeConfig::UPGRADE_KEY_RESEARCH_SUBSIDIES_III,
        VillageUpgradeConfig::UPGRADE_KEY_ADMINISTRATION_I,
        VillageUpgradeConfig::UPGRADE_KEY_ADMINISTRATION_II,
        VillageUpgradeConfig::UPGRADE_KEY_ADMINISTRATION_III,
        VillageUpgradeConfig::UPGRADE_KEY_POWER_PROJECTION_I,
        VillageUpgradeConfig::UPGRADE_KEY_POWER_PROJECTION_II,
        VillageUpgradeConfig::UPGRADE_KEY_POWER_PROJECTION_III,
        VillageUpgradeConfig::UPGRADE_KEY_EDUCATION_SUBSIDIES_I,
        VillageUpgradeConfig::UPGRADE_KEY_EDUCATION_SUBSIDIES_II,
        VillageUpgradeConfig::UPGRADE_KEY_EDUCATION_SUBSIDIES_III,
        VillageUpgradeConfig::UPGRADE_KEY_TRAINING_GROUNDS_I,
        VillageUpgradeConfig::UPGRADE_KEY_TRAINING_GROUNDS_II,
        VillageUpgradeConfig::UPGRADE_KEY_TRAINING_GROUNDS_III,
        VillageUpgradeConfig::UPGRADE_KEY_MEDICAL_SUBSIDIES_I,
        VillageUpgradeConfig::UPGRADE_KEY_MEDICAL_SUBSIDIES_II,
        VillageUpgradeConfig::UPGRADE_KEY_MEDICAL_SUBSIDIES_III,
        VillageUpgradeConfig::UPGRADE_KEY_HERBICULTURE_I,
        VillageUpgradeConfig::UPGRADE_KEY_HERBICULTURE_II,
        VillageUpgradeConfig::UPGRADE_KEY_HERBICULTURE_III,
        VillageUpgradeConfig::UPGRADE_KEY_MILITARY_SUBSIDIES_I,
        VillageUpgradeConfig::UPGRADE_KEY_MILITARY_SUBSIDIES_II,
        VillageUpgradeConfig::UPGRADE_KEY_MILITARY_SUBSIDIES_III,
        VillageUpgradeConfig::UPGRADE_KEY_ASSAULT_TRAINING_I,
        VillageUpgradeConfig::UPGRADE_KEY_ASSAULT_TRAINING_II,
        VillageUpgradeConfig::UPGRADE_KEY_ASSAULT_TRAINING_III,
        VillageUpgradeConfig::UPGRADE_KEY_SALTED_EARTH_I,
        VillageUpgradeConfig::UPGRADE_KEY_SALTED_EARTH_II,
        VillageUpgradeConfig::UPGRADE_KEY_SALTED_EARTH_III,
        VillageUpgradeConfig::UPGRADE_KEY_UNDERWORLD_CONNECTIONS_I,
        VillageUpgradeConfig::UPGRADE_KEY_UNDERWORLD_CONNECTIONS_II,
        VillageUpgradeConfig::UPGRADE_KEY_UNDERWORLD_CONNECTIONS_III,
        VillageUpgradeConfig::UPGRADE_KEY_GUERRILLA_WARFARE_I,
        VillageUpgradeConfig::UPGRADE_KEY_GUERRILLA_WARFARE_II,
        VillageUpgradeConfig::UPGRADE_KEY_GUERRILLA_WARFARE_III,
        VillageUpgradeConfig::UPGRADE_KEY_SELECTIVE_RECRUITMENT_I,
        VillageUpgradeConfig::UPGRADE_KEY_SELECTIVE_RECRUITMENT_II,
        VillageUpgradeConfig::UPGRADE_KEY_SELECTIVE_RECRUITMENT_III,
        VillageUpgradeConfig::UPGRADE_KEY_CONSTRUCTION_I,
        VillageUpgradeConfig::UPGRADE_KEY_CONSTRUCTION_II,
        VillageUpgradeConfig::UPGRADE_KEY_CONSTRUCTION_III,
        VillageUpgradeConfig::UPGRADE_KEY_ENGINEERING_SUBSIDIES_I,
        VillageUpgradeConfig::UPGRADE_KEY_ENGINEERING_SUBSIDIES_II,
        VillageUpgradeConfig::UPGRADE_KEY_ENGINEERING_SUBSIDIES_III,
        VillageUpgradeConfig::UPGRADE_KEY_RAPID_DEPLOYMENT_I,
        VillageUpgradeConfig::UPGRADE_KEY_RAPID_DEPLOYMENT_II,
        VillageUpgradeConfig::UPGRADE_KEY_RAPID_DEPLOYMENT_III,
        VillageUpgradeConfig::UPGRADE_KEY_ENGINEERING_CORPS_I,
        VillageUpgradeConfig::UPGRADE_KEY_ENGINEERING_CORPS_II,
        VillageUpgradeConfig::UPGRADE_KEY_ENGINEERING_CORPS_III,
        VillageUpgradeConfig::UPGRADE_KEY_FORTIFICATIONS_I,
        VillageUpgradeConfig::UPGRADE_KEY_FORTIFICATIONS_II,
        VillageUpgradeConfig::UPGRADE_KEY_FORTIFICATIONS_III,
        VillageUpgradeConfig::UPGRADE_KEY_BULK_SUPPLIERS_I,
        VillageUpgradeConfig::UPGRADE_KEY_BULK_SUPPLIERS_II,
        VillageUpgradeConfig::UPGRADE_KEY_BULK_SUPPLIERS_III,
        VillageUpgradeConfig::UPGRADE_KEY_FARMERS_MARKET_I,
        VillageUpgradeConfig::UPGRADE_KEY_FARMERS_MARKET_II,
        VillageUpgradeConfig::UPGRADE_KEY_FARMERS_MARKET_III,
        VillageUpgradeConfig::UPGRADE_KEY_MERCHANTS_GUILD_I,
        VillageUpgradeConfig::UPGRADE_KEY_MERCHANTS_GUILD_II,
        VillageUpgradeConfig::UPGRADE_KEY_MERCHANTS_GUILD_III,
        VillageUpgradeConfig::UPGRADE_KEY_WAREHOUSES_I,
        VillageUpgradeConfig::UPGRADE_KEY_WAREHOUSES_II,
        VillageUpgradeConfig::UPGRADE_KEY_ANCESTRAL_LEGACY_I,
        VillageUpgradeConfig::UPGRADE_KEY_ANCESTRAL_LEGACY_II,
        VillageUpgradeConfig::UPGRADE_KEY_ANCESTRAL_LEGACY_III,
        VillageUpgradeConfig::UPGRADE_KEY_FORTUNES_BOUNTY_I,
        VillageUpgradeConfig::UPGRADE_KEY_FORTUNES_BOUNTY_II,
        VillageUpgradeConfig::UPGRADE_KEY_FORTUNES_BOUNTY_III,
        VillageUpgradeConfig::UPGRADE_KEY_NEW_RECIPE_I,
        VillageUpgradeConfig::UPGRADE_KEY_QUALITY_INGREDIENTS_I,
        VillageUpgradeConfig::UPGRADE_KEY_QUALITY_INGREDIENTS_II,
        VillageUpgradeConfig::UPGRADE_KEY_QUALITY_INGREDIENTS_III,
        VillageUpgradeConfig::UPGRADE_KEY_NINJA_FRIENDLY_RATES_I,
        VillageUpgradeConfig::UPGRADE_KEY_NINJA_FRIENDLY_RATES_II,
        VillageUpgradeConfig::UPGRADE_KEY_NINJA_FRIENDLY_RATES_III,
        VillageUpgradeConfig::UPGRADE_KEY_INSPIRED_ITAMAE_I,
        VillageUpgradeConfig::UPGRADE_KEY_LUCK_IN_LEFTOVERS_I,
        VillageUpgradeConfig::UPGRADE_KEY_LUCK_IN_LEFTOVERS_II,
    ];

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
    const UPGRADE_EFFECT_OCCUPIED_BASE_STABILITY_REDUCTION = 'OCCUPIED_BASE_STABILITY';
    const UPGRADE_EFFECT_OCCUPIED_MAX_STABILITY_REDUCTION = 'OCCUPIED_MAX_STABILITY';
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

    /* Used for auto population */
    const UPGRADE_EFFECTS_LIST = [
        VillageUpgradeConfig::UPGRADE_EFFECT_MATERIALS_PRODUCTION,
        VillageUpgradeConfig::UPGRADE_EFFECT_FOOD_PRODUCTION,
        VillageUpgradeConfig::UPGRADE_EFFECT_WEALTH_PRODUCTION,
        VillageUpgradeConfig::UPGRADE_EFFECT_CONSTRUCTION_SPEED,
        VillageUpgradeConfig::UPGRADE_EFFECT_RESEARCH_SPEED,
        VillageUpgradeConfig::UPGRADE_EFFECT_RESEARCH_T1_ENABLED,
        VillageUpgradeConfig::UPGRADE_EFFECT_RESEARCH_T2_ENABLED,
        VillageUpgradeConfig::UPGRADE_EFFECT_RESEARCH_T3_ENABLED,
        VillageUpgradeConfig::UPGRADE_EFFECT_CONSTRUCTION_T1_ENABLED,
        VillageUpgradeConfig::UPGRADE_EFFECT_CONSTRUCTION_T2_ENABLED,
        VillageUpgradeConfig::UPGRADE_EFFECT_CONSTRUCTION_T3_ENABLED,
        VillageUpgradeConfig::UPGRADE_EFFECT_UPGRADE_UPKEEP,
        VillageUpgradeConfig::UPGRADE_EFFECT_BASE_STABILITY,
        VillageUpgradeConfig::UPGRADE_EFFECT_MAX_STABILITY,
        VillageUpgradeConfig::UPGRADE_EFFECT_TRAINING_SPEED,
        VillageUpgradeConfig::UPGRADE_EFFECT_DOUBLE_BATTLE_XP_CHANCE,
        VillageUpgradeConfig::UPGRADE_EFFECT_VILLAGE_REGEN,
        VillageUpgradeConfig::UPGRADE_EFFECT_HEAL_ITEM_COST,
        VillageUpgradeConfig::UPGRADE_EFFECT_WAR_ACTION_COST,
        VillageUpgradeConfig::UPGRADE_EFFECT_RAID_SPEED,
        VillageUpgradeConfig::UPGRADE_EFFECT_RAID_DAMAGE,
        VillageUpgradeConfig::UPGRADE_EFFECT_INFILTRATE_SPEED,
        VillageUpgradeConfig::UPGRADE_EFFECT_OCCUPIED_BASE_STABILITY_REDUCTION,
        VillageUpgradeConfig::UPGRADE_EFFECT_OCCUPIED_MAX_STABILITY_REDUCTION,
        VillageUpgradeConfig::UPGRADE_EFFECT_PATROL_TIER_CHANCE,
        VillageUpgradeConfig::UPGRADE_EFFECT_REINFORCE_SPEED,
        VillageUpgradeConfig::UPGRADE_EFFECT_REINFORCE_HEAL,
        VillageUpgradeConfig::UPGRADE_EFFECT_CASTLE_HP,
        VillageUpgradeConfig::UPGRADE_EFFECT_TOWN_HP,
        VillageUpgradeConfig::UPGRADE_EFFECT_RESOURCE_CAPACITY,
        VillageUpgradeConfig::UPGRADE_EFFECT_BLOODLINE_CHANCE,
        VillageUpgradeConfig::UPGRADE_EFFECT_DOUBLE_BATTLE_YEN_CHANCE,
        VillageUpgradeConfig::UPGRADE_EFFECT_RAMEN_SET_ONE,
        VillageUpgradeConfig::UPGRADE_EFFECT_RAMEN_DURATION,
        VillageUpgradeConfig::UPGRADE_EFFECT_RAMEN_COST,
        VillageUpgradeConfig::UPGRADE_EFFECT_MYSTERY_RAMEN_ENABLED,
        VillageUpgradeConfig::UPGRADE_EFFECT_MYSTERY_RAMEN_CHANCE
    ];

    /* research requirement types */
    const UPGRADE_REQUIREMENT_BUILDINGS = "BUILDINGS";
    const UPGRADE_REQUIREMENT_UPGRADES = "UPGRADES";

    /* display names for each upgrade indexed by upgrade key */
    const UPGRADE_NAMES = [
        VillageUpgradeConfig::UPGRADE_KEY_TEST => 'Test Upgrade',
        VillageUpgradeConfig::UPGRADE_KEY_RESEARCH_I => 'Research I',
        VillageUpgradeConfig::UPGRADE_KEY_RESEARCH_II => 'Research II',
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
        VillageUpgradeConfig::UPGRADE_KEY_NINJA_FRIENDLY_RATES_I => 'Ninja Friendly Rates I',
        VillageUpgradeConfig::UPGRADE_KEY_NINJA_FRIENDLY_RATES_II => 'Ninja Friendly Rates II',
        VillageUpgradeConfig::UPGRADE_KEY_NINJA_FRIENDLY_RATES_III => 'Ninja Friendly Rates III',
        VillageUpgradeConfig::UPGRADE_KEY_INSPIRED_ITAMAE_I => 'Inspired Itamae I',
        VillageUpgradeConfig::UPGRADE_KEY_LUCK_IN_LEFTOVERS_I => 'Luck In Leftovers I',
        VillageUpgradeConfig::UPGRADE_KEY_LUCK_IN_LEFTOVERS_II => 'Luck In Leftovers II',
    ];

    /* display descriptions for each upgrade indexed by upgrade key */
    const UPGRADE_DESCRIPTIONS = [
        VillageUpgradeConfig::UPGRADE_KEY_TEST => 'Test description for an upgrade.',
        VillageUpgradeConfig::UPGRADE_KEY_RESEARCH_I => 'Allows researching Tier I Upgrades',
        VillageUpgradeConfig::UPGRADE_KEY_RESEARCH_II => 'Allows researching Tier II Upgrades',
        VillageUpgradeConfig::UPGRADE_KEY_RESEARCH_III => 'Allows researching Tier III Upgrades',
        VillageUpgradeConfig::UPGRADE_KEY_RESEARCH_SUBSIDIES_I => '5% increased Research speed',
        VillageUpgradeConfig::UPGRADE_KEY_RESEARCH_SUBSIDIES_II => '5% increased Research speed',
        VillageUpgradeConfig::UPGRADE_KEY_RESEARCH_SUBSIDIES_III => '5% increased Research speed',
        VillageUpgradeConfig::UPGRADE_KEY_ADMINISTRATION_I => '5% reduced Upkeep from Upgrades',
        VillageUpgradeConfig::UPGRADE_KEY_ADMINISTRATION_II => '5% reduced Upkeep from Upgrades',
        VillageUpgradeConfig::UPGRADE_KEY_ADMINISTRATION_III => '5% reduced Upkeep from Upgrades',
        VillageUpgradeConfig::UPGRADE_KEY_POWER_PROJECTION_I => '+5 baseline and maximum Stability',
        VillageUpgradeConfig::UPGRADE_KEY_POWER_PROJECTION_II => '+5 baseline and maximum Stability',
        VillageUpgradeConfig::UPGRADE_KEY_POWER_PROJECTION_III => '+5 baseline and maximum Stability',
        VillageUpgradeConfig::UPGRADE_KEY_EDUCATION_SUBSIDIES_I => '3% increased Training speed',
        VillageUpgradeConfig::UPGRADE_KEY_EDUCATION_SUBSIDIES_II => '3% increased Training speed',
        VillageUpgradeConfig::UPGRADE_KEY_EDUCATION_SUBSIDIES_III => '3% increased Training speed',
        VillageUpgradeConfig::UPGRADE_KEY_TRAINING_GROUNDS_I => '+3% chance for double XP gains from battle',
        VillageUpgradeConfig::UPGRADE_KEY_TRAINING_GROUNDS_II => '+3% chance for double XP gains from battle',
        VillageUpgradeConfig::UPGRADE_KEY_TRAINING_GROUNDS_III => '+3% chance for double XP gains from battle',
        VillageUpgradeConfig::UPGRADE_KEY_MEDICAL_SUBSIDIES_I => '5% increased village regen',
        VillageUpgradeConfig::UPGRADE_KEY_MEDICAL_SUBSIDIES_II => '5% increased village regen',
        VillageUpgradeConfig::UPGRADE_KEY_MEDICAL_SUBSIDIES_III => '5% increased village regen',
        VillageUpgradeConfig::UPGRADE_KEY_HERBICULTURE_I => '10% decreased cost of healing items',
        VillageUpgradeConfig::UPGRADE_KEY_HERBICULTURE_II => '10% decreased cost of healing items',
        VillageUpgradeConfig::UPGRADE_KEY_HERBICULTURE_III => '10% decreased cost of healing items',
        VillageUpgradeConfig::UPGRADE_KEY_MILITARY_SUBSIDIES_I => '5% decreased pool cost of war actions',
        VillageUpgradeConfig::UPGRADE_KEY_MILITARY_SUBSIDIES_II => '5% decreased pool cost of war actions',
        VillageUpgradeConfig::UPGRADE_KEY_MILITARY_SUBSIDIES_III => '5% decreased pool cost of war actions',
        VillageUpgradeConfig::UPGRADE_KEY_ASSAULT_TRAINING_I => '5% increased Raid speed',
        VillageUpgradeConfig::UPGRADE_KEY_ASSAULT_TRAINING_II => '5% increased Raid speed',
        VillageUpgradeConfig::UPGRADE_KEY_ASSAULT_TRAINING_III => '5% increased Raid speed',
        VillageUpgradeConfig::UPGRADE_KEY_SALTED_EARTH_I => '5% increased Raid damage',
        VillageUpgradeConfig::UPGRADE_KEY_SALTED_EARTH_II => '5% increased Raid damage',
        VillageUpgradeConfig::UPGRADE_KEY_SALTED_EARTH_III => '5% increased Raid damage',
        VillageUpgradeConfig::UPGRADE_KEY_UNDERWORLD_CONNECTIONS_I => '5% increased Infiltrate speed',
        VillageUpgradeConfig::UPGRADE_KEY_UNDERWORLD_CONNECTIONS_II => '5% increased Infiltrate speed',
        VillageUpgradeConfig::UPGRADE_KEY_UNDERWORLD_CONNECTIONS_III => '5% increased Infiltrate speed',
        VillageUpgradeConfig::UPGRADE_KEY_GUERRILLA_WARFARE_I => '-5 base and max Stability for occupied villages',
        VillageUpgradeConfig::UPGRADE_KEY_GUERRILLA_WARFARE_II => '-5 base and max Stability for occupied villages',
        VillageUpgradeConfig::UPGRADE_KEY_GUERRILLA_WARFARE_III => '-5 base and max Stability for occupied villages',
        VillageUpgradeConfig::UPGRADE_KEY_SELECTIVE_RECRUITMENT_I => '+5% chance to spawn higher tier patrols',
        VillageUpgradeConfig::UPGRADE_KEY_SELECTIVE_RECRUITMENT_II => '+5% chance to spawn higher tier patrols',
        VillageUpgradeConfig::UPGRADE_KEY_SELECTIVE_RECRUITMENT_III => '+5% chance to spawn higher tier patrols',
        VillageUpgradeConfig::UPGRADE_KEY_CONSTRUCTION_I => 'Allows constructing Tier I buildings',
        VillageUpgradeConfig::UPGRADE_KEY_CONSTRUCTION_II => 'Allows constructing Tier II buildings',
        VillageUpgradeConfig::UPGRADE_KEY_CONSTRUCTION_III => 'Allows constructing Tier III buildings',
        VillageUpgradeConfig::UPGRADE_KEY_ENGINEERING_SUBSIDIES_I => '5% increased construction speed',
        VillageUpgradeConfig::UPGRADE_KEY_ENGINEERING_SUBSIDIES_II => '5% increased construction speed',
        VillageUpgradeConfig::UPGRADE_KEY_ENGINEERING_SUBSIDIES_III => '5% increased construction speed',
        VillageUpgradeConfig::UPGRADE_KEY_RAPID_DEPLOYMENT_I => '5% increased Reinforce speed',
        VillageUpgradeConfig::UPGRADE_KEY_RAPID_DEPLOYMENT_II => '5% increased Reinforce speed',
        VillageUpgradeConfig::UPGRADE_KEY_RAPID_DEPLOYMENT_III => '5% increased Reinforce speed',
        VillageUpgradeConfig::UPGRADE_KEY_ENGINEERING_CORPS_I => '5% increased Reinforce heal',
        VillageUpgradeConfig::UPGRADE_KEY_ENGINEERING_CORPS_II => '5% increased Reinforce heal',
        VillageUpgradeConfig::UPGRADE_KEY_ENGINEERING_CORPS_III => '5% increased Reinforce heal',
        VillageUpgradeConfig::UPGRADE_KEY_FORTIFICATIONS_I => '5% increased Castle health',
        VillageUpgradeConfig::UPGRADE_KEY_FORTIFICATIONS_II => '5% increased Castle health',
        VillageUpgradeConfig::UPGRADE_KEY_FORTIFICATIONS_III => '5% increased Castle health',
        VillageUpgradeConfig::UPGRADE_KEY_BULK_SUPPLIERS_I => '+25 Materials production/hour',
        VillageUpgradeConfig::UPGRADE_KEY_BULK_SUPPLIERS_II => '+50 Materials production/hour',
        VillageUpgradeConfig::UPGRADE_KEY_BULK_SUPPLIERS_III => '+75 Materials production/hour',
        VillageUpgradeConfig::UPGRADE_KEY_FARMERS_MARKET_I => '+25 Food production/hour',
        VillageUpgradeConfig::UPGRADE_KEY_FARMERS_MARKET_II => '+50 Food production/hour',
        VillageUpgradeConfig::UPGRADE_KEY_FARMERS_MARKET_III => '+75 Food production/hour',
        VillageUpgradeConfig::UPGRADE_KEY_MERCHANTS_GUILD_I => '+25 Wealth production/hour',
        VillageUpgradeConfig::UPGRADE_KEY_MERCHANTS_GUILD_II => '+50 Wealth production/hour',
        VillageUpgradeConfig::UPGRADE_KEY_MERCHANTS_GUILD_III => '+75 Wealth production/hour',
        VillageUpgradeConfig::UPGRADE_KEY_WAREHOUSES_I => '+100000 resource capacity',
        VillageUpgradeConfig::UPGRADE_KEY_WAREHOUSES_II => '+500000 resource capacity',
        VillageUpgradeConfig::UPGRADE_KEY_ANCESTRAL_LEGACY_I => '10% increased chance of obtaining a Bloodline',
        VillageUpgradeConfig::UPGRADE_KEY_ANCESTRAL_LEGACY_II => '10% increased chance of obtaining a Bloodline',
        VillageUpgradeConfig::UPGRADE_KEY_ANCESTRAL_LEGACY_III => '10% increased chance of obtaining a Bloodline',
        VillageUpgradeConfig::UPGRADE_KEY_FORTUNES_BOUNTY_I => '+5% chance for double yen gains from battle',
        VillageUpgradeConfig::UPGRADE_KEY_FORTUNES_BOUNTY_II => '+5% chance for double yen gains from battle',
        VillageUpgradeConfig::UPGRADE_KEY_FORTUNES_BOUNTY_III => '+5% chance for double yen gains from battle',
        VillageUpgradeConfig::UPGRADE_KEY_NEW_RECIPE_I => 'Unlocks new ramen recipes',
        VillageUpgradeConfig::UPGRADE_KEY_QUALITY_INGREDIENTS_I => '+5 minutes to ramen buff duration',
        VillageUpgradeConfig::UPGRADE_KEY_QUALITY_INGREDIENTS_II => '+5 minutes to ramen duration',
        VillageUpgradeConfig::UPGRADE_KEY_QUALITY_INGREDIENTS_III => '+5 minutes to ramen duration',
        VillageUpgradeConfig::UPGRADE_KEY_NINJA_FRIENDLY_RATES_I => '5% decreased ramen cost',
        VillageUpgradeConfig::UPGRADE_KEY_NINJA_FRIENDLY_RATES_II => '5% decreased ramen cost',
        VillageUpgradeConfig::UPGRADE_KEY_NINJA_FRIENDLY_RATES_III => '5% decreased ramen cost',
        VillageUpgradeConfig::UPGRADE_KEY_INSPIRED_ITAMAE_I => 'Allows Mystery Ramen to appear (5%)',
        VillageUpgradeConfig::UPGRADE_KEY_LUCK_IN_LEFTOVERS_I => '+5% chance for Mystery Ramen to appear',
        VillageUpgradeConfig::UPGRADE_KEY_LUCK_IN_LEFTOVERS_II => '+5% chance for Mystery Ramen to appear',
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
            WarManager::RESOURCE_MATERIALS => 3000,
            WarManager::RESOURCE_FOOD => 6000,
            WarManager::RESOURCE_WEALTH => 9000,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_RESEARCH_III => [
            WarManager::RESOURCE_MATERIALS => 15000,
            WarManager::RESOURCE_FOOD => 30000,
            WarManager::RESOURCE_WEALTH => 45000,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_RESEARCH_SUBSIDIES_I => [
            WarManager::RESOURCE_MATERIALS => 600,
            WarManager::RESOURCE_FOOD => 1200,
            WarManager::RESOURCE_WEALTH => 1800,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_RESEARCH_SUBSIDIES_II => [
            WarManager::RESOURCE_MATERIALS => 3000,
            WarManager::RESOURCE_FOOD => 6000,
            WarManager::RESOURCE_WEALTH => 9000,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_RESEARCH_SUBSIDIES_III => [
            WarManager::RESOURCE_MATERIALS => 15000,
            WarManager::RESOURCE_FOOD => 30000,
            WarManager::RESOURCE_WEALTH => 45000,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ADMINISTRATION_I => [
            WarManager::RESOURCE_MATERIALS => 600,
            WarManager::RESOURCE_FOOD => 1200,
            WarManager::RESOURCE_WEALTH => 1800,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ADMINISTRATION_II => [
            WarManager::RESOURCE_MATERIALS => 3000,
            WarManager::RESOURCE_FOOD => 6000,
            WarManager::RESOURCE_WEALTH => 9000,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ADMINISTRATION_III => [
            WarManager::RESOURCE_MATERIALS => 15000,
            WarManager::RESOURCE_FOOD => 30000,
            WarManager::RESOURCE_WEALTH => 45000,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_POWER_PROJECTION_I => [
            WarManager::RESOURCE_MATERIALS => 1200,
            WarManager::RESOURCE_FOOD => 1200,
            WarManager::RESOURCE_WEALTH => 1200,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_POWER_PROJECTION_II => [
            WarManager::RESOURCE_MATERIALS => 6000,
            WarManager::RESOURCE_FOOD => 6000,
            WarManager::RESOURCE_WEALTH => 6000,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_POWER_PROJECTION_III => [
            WarManager::RESOURCE_MATERIALS => 30000,
            WarManager::RESOURCE_FOOD => 30000,
            WarManager::RESOURCE_WEALTH => 30000,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_EDUCATION_SUBSIDIES_I => [
            WarManager::RESOURCE_MATERIALS => 600,
            WarManager::RESOURCE_FOOD => 1200,
            WarManager::RESOURCE_WEALTH => 1800,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_EDUCATION_SUBSIDIES_II => [
            WarManager::RESOURCE_MATERIALS => 3000,
            WarManager::RESOURCE_FOOD => 6000,
            WarManager::RESOURCE_WEALTH => 9000,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_EDUCATION_SUBSIDIES_III => [
            WarManager::RESOURCE_MATERIALS => 15000,
            WarManager::RESOURCE_FOOD => 30000,
            WarManager::RESOURCE_WEALTH => 45000,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_TRAINING_GROUNDS_I => [
            WarManager::RESOURCE_MATERIALS => 1200,
            WarManager::RESOURCE_FOOD => 1200,
            WarManager::RESOURCE_WEALTH => 1200,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_TRAINING_GROUNDS_II => [
            WarManager::RESOURCE_MATERIALS => 6000,
            WarManager::RESOURCE_FOOD => 6000,
            WarManager::RESOURCE_WEALTH => 6000,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_TRAINING_GROUNDS_III => [
            WarManager::RESOURCE_MATERIALS => 30000,
            WarManager::RESOURCE_FOOD => 30000,
            WarManager::RESOURCE_WEALTH => 30000,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_MEDICAL_SUBSIDIES_I => [
            WarManager::RESOURCE_MATERIALS => 1200,
            WarManager::RESOURCE_FOOD => 1200,
            WarManager::RESOURCE_WEALTH => 1200,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_MEDICAL_SUBSIDIES_II => [
            WarManager::RESOURCE_MATERIALS => 6000,
            WarManager::RESOURCE_FOOD => 6000,
            WarManager::RESOURCE_WEALTH => 6000,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_MEDICAL_SUBSIDIES_III => [
            WarManager::RESOURCE_MATERIALS => 30000,
            WarManager::RESOURCE_FOOD => 30000,
            WarManager::RESOURCE_WEALTH => 30000,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_HERBICULTURE_I => [
            WarManager::RESOURCE_MATERIALS => 600,
            WarManager::RESOURCE_FOOD => 1800,
            WarManager::RESOURCE_WEALTH => 1200,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_HERBICULTURE_II => [
            WarManager::RESOURCE_MATERIALS => 3000,
            WarManager::RESOURCE_FOOD => 9000,
            WarManager::RESOURCE_WEALTH => 6000,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_HERBICULTURE_III => [
            WarManager::RESOURCE_MATERIALS => 15000,
            WarManager::RESOURCE_FOOD => 45000,
            WarManager::RESOURCE_WEALTH => 30000,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_MILITARY_SUBSIDIES_I => [
            WarManager::RESOURCE_MATERIALS => 1200,
            WarManager::RESOURCE_FOOD => 1200,
            WarManager::RESOURCE_WEALTH => 1200,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_MILITARY_SUBSIDIES_II => [
            WarManager::RESOURCE_MATERIALS => 6000,
            WarManager::RESOURCE_FOOD => 6000,
            WarManager::RESOURCE_WEALTH => 6000,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_MILITARY_SUBSIDIES_III => [
            WarManager::RESOURCE_MATERIALS => 30000,
            WarManager::RESOURCE_FOOD => 30000,
            WarManager::RESOURCE_WEALTH => 30000,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ASSAULT_TRAINING_I => [
            WarManager::RESOURCE_MATERIALS => 1200,
            WarManager::RESOURCE_FOOD => 1200,
            WarManager::RESOURCE_WEALTH => 1200,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ASSAULT_TRAINING_II => [
            WarManager::RESOURCE_MATERIALS => 6000,
            WarManager::RESOURCE_FOOD => 6000,
            WarManager::RESOURCE_WEALTH => 6000,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ASSAULT_TRAINING_III => [
            WarManager::RESOURCE_MATERIALS => 30000,
            WarManager::RESOURCE_FOOD => 30000,
            WarManager::RESOURCE_WEALTH => 30000,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_SALTED_EARTH_I => [
            WarManager::RESOURCE_MATERIALS => 1800,
            WarManager::RESOURCE_FOOD => 1200,
            WarManager::RESOURCE_WEALTH => 600,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_SALTED_EARTH_II => [
            WarManager::RESOURCE_MATERIALS => 9000,
            WarManager::RESOURCE_FOOD => 6000,
            WarManager::RESOURCE_WEALTH => 3000,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_SALTED_EARTH_III => [
            WarManager::RESOURCE_MATERIALS => 45000,
            WarManager::RESOURCE_FOOD => 30000,
            WarManager::RESOURCE_WEALTH => 15000,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_UNDERWORLD_CONNECTIONS_I => [
            WarManager::RESOURCE_MATERIALS => 600,
            WarManager::RESOURCE_FOOD => 600,
            WarManager::RESOURCE_WEALTH => 2400,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_UNDERWORLD_CONNECTIONS_II => [
            WarManager::RESOURCE_MATERIALS => 3000,
            WarManager::RESOURCE_FOOD => 3000,
            WarManager::RESOURCE_WEALTH => 12000,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_UNDERWORLD_CONNECTIONS_III => [
            WarManager::RESOURCE_MATERIALS => 15000,
            WarManager::RESOURCE_FOOD => 15000,
            WarManager::RESOURCE_WEALTH => 60000,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_GUERRILLA_WARFARE_I => [
            WarManager::RESOURCE_MATERIALS => 1200,
            WarManager::RESOURCE_FOOD => 1200,
            WarManager::RESOURCE_WEALTH => 1200,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_GUERRILLA_WARFARE_II => [
            WarManager::RESOURCE_MATERIALS => 6000,
            WarManager::RESOURCE_FOOD => 6000,
            WarManager::RESOURCE_WEALTH => 6000,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_GUERRILLA_WARFARE_III => [
            WarManager::RESOURCE_MATERIALS => 30000,
            WarManager::RESOURCE_FOOD => 30000,
            WarManager::RESOURCE_WEALTH => 30000,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_SELECTIVE_RECRUITMENT_I => [
            WarManager::RESOURCE_MATERIALS => 1200,
            WarManager::RESOURCE_FOOD => 1200,
            WarManager::RESOURCE_WEALTH => 1200,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_SELECTIVE_RECRUITMENT_II => [
            WarManager::RESOURCE_MATERIALS => 6000,
            WarManager::RESOURCE_FOOD => 6000,
            WarManager::RESOURCE_WEALTH => 6000,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_SELECTIVE_RECRUITMENT_III => [
            WarManager::RESOURCE_MATERIALS => 30000,
            WarManager::RESOURCE_FOOD => 30000,
            WarManager::RESOURCE_WEALTH => 30000,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_CONSTRUCTION_I => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_CONSTRUCTION_II => [
            WarManager::RESOURCE_MATERIALS => 12000,
            WarManager::RESOURCE_FOOD => 3000,
            WarManager::RESOURCE_WEALTH => 3000,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_CONSTRUCTION_III => [
            WarManager::RESOURCE_MATERIALS => 60000,
            WarManager::RESOURCE_FOOD => 15000,
            WarManager::RESOURCE_WEALTH => 15000,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ENGINEERING_SUBSIDIES_I => [
            WarManager::RESOURCE_MATERIALS => 2400,
            WarManager::RESOURCE_FOOD => 600,
            WarManager::RESOURCE_WEALTH => 600,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ENGINEERING_SUBSIDIES_II => [
            WarManager::RESOURCE_MATERIALS => 12000,
            WarManager::RESOURCE_FOOD => 3000,
            WarManager::RESOURCE_WEALTH => 3000,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ENGINEERING_SUBSIDIES_III => [
            WarManager::RESOURCE_MATERIALS => 60000,
            WarManager::RESOURCE_FOOD => 15000,
            WarManager::RESOURCE_WEALTH => 15000,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_RAPID_DEPLOYMENT_I => [
            WarManager::RESOURCE_MATERIALS => 1200,
            WarManager::RESOURCE_FOOD => 1200,
            WarManager::RESOURCE_WEALTH => 1200,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_RAPID_DEPLOYMENT_II => [
            WarManager::RESOURCE_MATERIALS => 6000,
            WarManager::RESOURCE_FOOD => 6000,
            WarManager::RESOURCE_WEALTH => 6000,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_RAPID_DEPLOYMENT_III => [
            WarManager::RESOURCE_MATERIALS => 30000,
            WarManager::RESOURCE_FOOD => 30000,
            WarManager::RESOURCE_WEALTH => 30000,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ENGINEERING_CORPS_I => [
            WarManager::RESOURCE_MATERIALS => 1800,
            WarManager::RESOURCE_FOOD => 1200,
            WarManager::RESOURCE_WEALTH => 600,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ENGINEERING_CORPS_II => [
            WarManager::RESOURCE_MATERIALS => 9000,
            WarManager::RESOURCE_FOOD => 6000,
            WarManager::RESOURCE_WEALTH => 3000,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ENGINEERING_CORPS_III => [
            WarManager::RESOURCE_MATERIALS => 45000,
            WarManager::RESOURCE_FOOD => 30000,
            WarManager::RESOURCE_WEALTH => 15000,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_FORTIFICATIONS_I => [
            WarManager::RESOURCE_MATERIALS => 2400,
            WarManager::RESOURCE_FOOD => 600,
            WarManager::RESOURCE_WEALTH => 600,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_FORTIFICATIONS_II => [
            WarManager::RESOURCE_MATERIALS => 12000,
            WarManager::RESOURCE_FOOD => 3000,
            WarManager::RESOURCE_WEALTH => 3000,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_FORTIFICATIONS_III => [
            WarManager::RESOURCE_MATERIALS => 60000,
            WarManager::RESOURCE_FOOD => 15000,
            WarManager::RESOURCE_WEALTH => 15000,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_BULK_SUPPLIERS_I => [
            WarManager::RESOURCE_MATERIALS => 600,
            WarManager::RESOURCE_FOOD => 600,
            WarManager::RESOURCE_WEALTH => 2400,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_BULK_SUPPLIERS_II => [
            WarManager::RESOURCE_MATERIALS => 3000,
            WarManager::RESOURCE_FOOD => 3000,
            WarManager::RESOURCE_WEALTH => 12000,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_BULK_SUPPLIERS_III => [
            WarManager::RESOURCE_MATERIALS => 15000,
            WarManager::RESOURCE_FOOD => 15000,
            WarManager::RESOURCE_WEALTH => 60000,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_FARMERS_MARKET_I => [
            WarManager::RESOURCE_MATERIALS => 600,
            WarManager::RESOURCE_FOOD => 600,
            WarManager::RESOURCE_WEALTH => 2400,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_FARMERS_MARKET_II => [
            WarManager::RESOURCE_MATERIALS => 3000,
            WarManager::RESOURCE_FOOD => 3000,
            WarManager::RESOURCE_WEALTH => 12000,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_FARMERS_MARKET_III => [
            WarManager::RESOURCE_MATERIALS => 15000,
            WarManager::RESOURCE_FOOD => 15000,
            WarManager::RESOURCE_WEALTH => 60000,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_MERCHANTS_GUILD_I => [
            WarManager::RESOURCE_MATERIALS => 600,
            WarManager::RESOURCE_FOOD => 600,
            WarManager::RESOURCE_WEALTH => 2400,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_MERCHANTS_GUILD_II => [
            WarManager::RESOURCE_MATERIALS => 3000,
            WarManager::RESOURCE_FOOD => 3000,
            WarManager::RESOURCE_WEALTH => 12000,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_MERCHANTS_GUILD_III => [
            WarManager::RESOURCE_MATERIALS => 15000,
            WarManager::RESOURCE_FOOD => 15000,
            WarManager::RESOURCE_WEALTH => 60000,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_WAREHOUSES_I => [
            WarManager::RESOURCE_MATERIALS => 10800,
            WarManager::RESOURCE_FOOD => 4320,
            WarManager::RESOURCE_WEALTH => 6480,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_WAREHOUSES_II => [
            WarManager::RESOURCE_MATERIALS => 54000,
            WarManager::RESOURCE_FOOD => 21600,
            WarManager::RESOURCE_WEALTH => 32400,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ANCESTRAL_LEGACY_I => [
            WarManager::RESOURCE_MATERIALS => 1200,
            WarManager::RESOURCE_FOOD => 1200,
            WarManager::RESOURCE_WEALTH => 1200,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ANCESTRAL_LEGACY_II => [
            WarManager::RESOURCE_MATERIALS => 6000,
            WarManager::RESOURCE_FOOD => 6000,
            WarManager::RESOURCE_WEALTH => 6000,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ANCESTRAL_LEGACY_III => [
            WarManager::RESOURCE_MATERIALS => 30000,
            WarManager::RESOURCE_FOOD => 30000,
            WarManager::RESOURCE_WEALTH => 30000,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_FORTUNES_BOUNTY_I => [
            WarManager::RESOURCE_MATERIALS => 1200,
            WarManager::RESOURCE_FOOD => 1200,
            WarManager::RESOURCE_WEALTH => 1200,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_FORTUNES_BOUNTY_II => [
            WarManager::RESOURCE_MATERIALS => 6000,
            WarManager::RESOURCE_FOOD => 6000,
            WarManager::RESOURCE_WEALTH => 6000,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_FORTUNES_BOUNTY_III => [
            WarManager::RESOURCE_MATERIALS => 30000,
            WarManager::RESOURCE_FOOD => 30000,
            WarManager::RESOURCE_WEALTH => 30000,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_NEW_RECIPE_I => [
            WarManager::RESOURCE_MATERIALS => 600,
            WarManager::RESOURCE_FOOD => 2400,
            WarManager::RESOURCE_WEALTH => 600,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_QUALITY_INGREDIENTS_I => [
            WarManager::RESOURCE_MATERIALS => 600,
            WarManager::RESOURCE_FOOD => 2400,
            WarManager::RESOURCE_WEALTH => 600,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_QUALITY_INGREDIENTS_II => [
            WarManager::RESOURCE_MATERIALS => 3000,
            WarManager::RESOURCE_FOOD => 12000,
            WarManager::RESOURCE_WEALTH => 3000,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_QUALITY_INGREDIENTS_III => [
            WarManager::RESOURCE_MATERIALS => 15000,
            WarManager::RESOURCE_FOOD => 60000,
            WarManager::RESOURCE_WEALTH => 15000,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_NINJA_FRIENDLY_RATES_I => [
            WarManager::RESOURCE_MATERIALS => 600,
            WarManager::RESOURCE_FOOD => 1200,
            WarManager::RESOURCE_WEALTH => 1800,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_NINJA_FRIENDLY_RATES_II => [
            WarManager::RESOURCE_MATERIALS => 3000,
            WarManager::RESOURCE_FOOD => 6000,
            WarManager::RESOURCE_WEALTH => 9000,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_NINJA_FRIENDLY_RATES_III => [
            WarManager::RESOURCE_MATERIALS => 15000,
            WarManager::RESOURCE_FOOD => 30000,
            WarManager::RESOURCE_WEALTH => 45000,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_INSPIRED_ITAMAE_I => [
            WarManager::RESOURCE_MATERIALS => 600,
            WarManager::RESOURCE_FOOD => 2400,
            WarManager::RESOURCE_WEALTH => 600,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_LUCK_IN_LEFTOVERS_I => [
            WarManager::RESOURCE_MATERIALS => 3000,
            WarManager::RESOURCE_FOOD => 12000,
            WarManager::RESOURCE_WEALTH => 3000,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_LUCK_IN_LEFTOVERS_II => [
            WarManager::RESOURCE_MATERIALS => 15000,
            WarManager::RESOURCE_FOOD => 60000,
            WarManager::RESOURCE_WEALTH => 15000,
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
        VillageUpgradeConfig::UPGRADE_KEY_NINJA_FRIENDLY_RATES_I => 1,
        VillageUpgradeConfig::UPGRADE_KEY_NINJA_FRIENDLY_RATES_II => 5,
        VillageUpgradeConfig::UPGRADE_KEY_NINJA_FRIENDLY_RATES_III => 25,
        VillageUpgradeConfig::UPGRADE_KEY_INSPIRED_ITAMAE_I => 1,
        VillageUpgradeConfig::UPGRADE_KEY_LUCK_IN_LEFTOVERS_I => 5,
        VillageUpgradeConfig::UPGRADE_KEY_LUCK_IN_LEFTOVERS_II => 25,
    ];

    /* research requirements indexed by upgrade key, then requirement type */
    const UPGRADE_RESEARCH_REQUIREMENTS = [
        VillageUpgradeConfig::UPGRADE_KEY_TEST => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
                VillageBuildingConfig::BUILDING_ACADEMY => 1
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
                VillageUpgradeConfig::UPGRADE_KEY_RESEARCH_I
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_RESEARCH_I => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
                VillageBuildingConfig::BUILDING_VILLAGE_HQ => 1
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_RESEARCH_II => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
                VillageBuildingConfig::BUILDING_VILLAGE_HQ => 2
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
                VillageUpgradeConfig::UPGRADE_KEY_RESEARCH_I
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_RESEARCH_III => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
                VillageBuildingConfig::BUILDING_VILLAGE_HQ => 3
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
                VillageUpgradeConfig::UPGRADE_KEY_RESEARCH_II
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_RESEARCH_SUBSIDIES_I => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
                VillageBuildingConfig::BUILDING_VILLAGE_HQ => 1
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_RESEARCH_SUBSIDIES_II => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
                VillageBuildingConfig::BUILDING_VILLAGE_HQ => 2
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
                VillageUpgradeConfig::UPGRADE_KEY_RESEARCH_SUBSIDIES_I
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_RESEARCH_SUBSIDIES_III => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
                VillageBuildingConfig::BUILDING_VILLAGE_HQ => 3
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
                VillageUpgradeConfig::UPGRADE_KEY_RESEARCH_SUBSIDIES_II
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ADMINISTRATION_I => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
                VillageBuildingConfig::BUILDING_VILLAGE_HQ => 1
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ADMINISTRATION_II => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
                VillageBuildingConfig::BUILDING_VILLAGE_HQ => 2
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
                VillageUpgradeConfig::UPGRADE_KEY_ADMINISTRATION_I
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ADMINISTRATION_III => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
                VillageBuildingConfig::BUILDING_VILLAGE_HQ => 3
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
                VillageUpgradeConfig::UPGRADE_KEY_ADMINISTRATION_II
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_POWER_PROJECTION_I => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
                VillageBuildingConfig::BUILDING_VILLAGE_HQ => 1
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_POWER_PROJECTION_II => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
                VillageBuildingConfig::BUILDING_VILLAGE_HQ => 2
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
                VillageUpgradeConfig::UPGRADE_KEY_POWER_PROJECTION_I
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_POWER_PROJECTION_III => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
                VillageBuildingConfig::BUILDING_VILLAGE_HQ => 3
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
                VillageUpgradeConfig::UPGRADE_KEY_POWER_PROJECTION_II
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_EDUCATION_SUBSIDIES_I => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
                VillageBuildingConfig::BUILDING_ACADEMY => 1
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_EDUCATION_SUBSIDIES_II => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
                VillageBuildingConfig::BUILDING_ACADEMY => 2
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
                VillageUpgradeConfig::UPGRADE_KEY_EDUCATION_SUBSIDIES_I
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_EDUCATION_SUBSIDIES_III => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
                VillageBuildingConfig::BUILDING_ACADEMY => 3
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
                VillageUpgradeConfig::UPGRADE_KEY_EDUCATION_SUBSIDIES_II
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_TRAINING_GROUNDS_I => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
                VillageBuildingConfig::BUILDING_ACADEMY => 1
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_TRAINING_GROUNDS_II => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
                VillageBuildingConfig::BUILDING_ACADEMY => 2
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
                VillageUpgradeConfig::UPGRADE_KEY_TRAINING_GROUNDS_I
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_TRAINING_GROUNDS_III => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
                VillageBuildingConfig::BUILDING_ACADEMY => 3
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
                VillageUpgradeConfig::UPGRADE_KEY_TRAINING_GROUNDS_II
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_MEDICAL_SUBSIDIES_I => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
                VillageBuildingConfig::BUILDING_HOSPITAL => 1
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_MEDICAL_SUBSIDIES_II => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
                VillageBuildingConfig::BUILDING_HOSPITAL => 2
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
                VillageUpgradeConfig::UPGRADE_KEY_MEDICAL_SUBSIDIES_I
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_MEDICAL_SUBSIDIES_III => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
                VillageBuildingConfig::BUILDING_HOSPITAL => 3
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
                VillageUpgradeConfig::UPGRADE_KEY_MEDICAL_SUBSIDIES_II
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_HERBICULTURE_I => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
                VillageBuildingConfig::BUILDING_HOSPITAL => 1
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_HERBICULTURE_II => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
                VillageBuildingConfig::BUILDING_HOSPITAL => 2
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
                VillageUpgradeConfig::UPGRADE_KEY_HERBICULTURE_I
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_HERBICULTURE_III => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
                VillageBuildingConfig::BUILDING_HOSPITAL => 3
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
                VillageUpgradeConfig::UPGRADE_KEY_HERBICULTURE_II
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_MILITARY_SUBSIDIES_I => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
                VillageBuildingConfig::BUILDING_ANBU_HQ => 1
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_MILITARY_SUBSIDIES_II => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
                VillageBuildingConfig::BUILDING_ANBU_HQ => 2
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
                VillageUpgradeConfig::UPGRADE_KEY_MILITARY_SUBSIDIES_I
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_MILITARY_SUBSIDIES_III => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
                VillageBuildingConfig::BUILDING_ANBU_HQ => 3
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
                VillageUpgradeConfig::UPGRADE_KEY_MILITARY_SUBSIDIES_II
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ASSAULT_TRAINING_I => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
                VillageBuildingConfig::BUILDING_ANBU_HQ => 1
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ASSAULT_TRAINING_II => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
                VillageBuildingConfig::BUILDING_ANBU_HQ => 2
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
                VillageUpgradeConfig::UPGRADE_KEY_ASSAULT_TRAINING_I
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ASSAULT_TRAINING_III => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
                VillageBuildingConfig::BUILDING_ANBU_HQ => 3
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
                VillageUpgradeConfig::UPGRADE_KEY_ASSAULT_TRAINING_II
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_SALTED_EARTH_I => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
                VillageBuildingConfig::BUILDING_ANBU_HQ => 1
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_SALTED_EARTH_II => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
                VillageBuildingConfig::BUILDING_ANBU_HQ => 2
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
                VillageUpgradeConfig::UPGRADE_KEY_SALTED_EARTH_I
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_SALTED_EARTH_III => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
                VillageBuildingConfig::BUILDING_ANBU_HQ => 3
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
                VillageUpgradeConfig::UPGRADE_KEY_SALTED_EARTH_II
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_UNDERWORLD_CONNECTIONS_I => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
                VillageBuildingConfig::BUILDING_ANBU_HQ => 1
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_UNDERWORLD_CONNECTIONS_II => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
                VillageBuildingConfig::BUILDING_ANBU_HQ => 2
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
                VillageUpgradeConfig::UPGRADE_KEY_UNDERWORLD_CONNECTIONS_I
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_UNDERWORLD_CONNECTIONS_III => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
                VillageBuildingConfig::BUILDING_ANBU_HQ => 3
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
                VillageUpgradeConfig::UPGRADE_KEY_UNDERWORLD_CONNECTIONS_II
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_GUERRILLA_WARFARE_I => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
                VillageBuildingConfig::BUILDING_ANBU_HQ => 1
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_GUERRILLA_WARFARE_II => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
                VillageBuildingConfig::BUILDING_ANBU_HQ => 2
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
                VillageUpgradeConfig::UPGRADE_KEY_GUERRILLA_WARFARE_I
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_GUERRILLA_WARFARE_III => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
                VillageBuildingConfig::BUILDING_ANBU_HQ => 3
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
                VillageUpgradeConfig::UPGRADE_KEY_GUERRILLA_WARFARE_II
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_SELECTIVE_RECRUITMENT_I => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
                VillageBuildingConfig::BUILDING_ANBU_HQ => 1
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_SELECTIVE_RECRUITMENT_II => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
                VillageBuildingConfig::BUILDING_ANBU_HQ => 2
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
                VillageUpgradeConfig::UPGRADE_KEY_SELECTIVE_RECRUITMENT_I
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_SELECTIVE_RECRUITMENT_III => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
                VillageBuildingConfig::BUILDING_ANBU_HQ => 3
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
                VillageUpgradeConfig::UPGRADE_KEY_SELECTIVE_RECRUITMENT_II
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_CONSTRUCTION_I => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
                VillageBuildingConfig::BUILDING_WORKSHOP => 1
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_CONSTRUCTION_II => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
                VillageBuildingConfig::BUILDING_WORKSHOP => 2
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
                VillageUpgradeConfig::UPGRADE_KEY_CONSTRUCTION_I
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_CONSTRUCTION_III => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
                VillageBuildingConfig::BUILDING_WORKSHOP => 3
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
                VillageUpgradeConfig::UPGRADE_KEY_CONSTRUCTION_II
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ENGINEERING_SUBSIDIES_I => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
                VillageBuildingConfig::BUILDING_WORKSHOP => 1
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ENGINEERING_SUBSIDIES_II => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
                VillageBuildingConfig::BUILDING_WORKSHOP => 2
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
                VillageUpgradeConfig::UPGRADE_KEY_ENGINEERING_SUBSIDIES_I
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ENGINEERING_SUBSIDIES_III => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
                VillageBuildingConfig::BUILDING_WORKSHOP => 3
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
                VillageUpgradeConfig::UPGRADE_KEY_ENGINEERING_SUBSIDIES_II
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_RAPID_DEPLOYMENT_I => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
                VillageBuildingConfig::BUILDING_WORKSHOP => 1
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_RAPID_DEPLOYMENT_II => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
                VillageBuildingConfig::BUILDING_WORKSHOP => 2
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
                VillageUpgradeConfig::UPGRADE_KEY_RAPID_DEPLOYMENT_I
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_RAPID_DEPLOYMENT_III => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
                VillageBuildingConfig::BUILDING_WORKSHOP => 3
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
                VillageUpgradeConfig::UPGRADE_KEY_RAPID_DEPLOYMENT_II
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ENGINEERING_CORPS_I => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
                VillageBuildingConfig::BUILDING_WORKSHOP => 1
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ENGINEERING_CORPS_II => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
                VillageBuildingConfig::BUILDING_WORKSHOP => 2
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
                VillageUpgradeConfig::UPGRADE_KEY_ENGINEERING_CORPS_I
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ENGINEERING_CORPS_III => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
                VillageBuildingConfig::BUILDING_WORKSHOP => 3
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
                VillageUpgradeConfig::UPGRADE_KEY_ENGINEERING_CORPS_II
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_FORTIFICATIONS_I => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
                VillageBuildingConfig::BUILDING_WORKSHOP => 1
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_FORTIFICATIONS_II => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
                VillageBuildingConfig::BUILDING_WORKSHOP => 2
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
                VillageUpgradeConfig::UPGRADE_KEY_FORTIFICATIONS_I
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_FORTIFICATIONS_III => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
                VillageBuildingConfig::BUILDING_WORKSHOP => 3
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
                VillageUpgradeConfig::UPGRADE_KEY_FORTIFICATIONS_II
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_BULK_SUPPLIERS_I => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
                VillageBuildingConfig::BUILDING_MARKET => 1
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_BULK_SUPPLIERS_II => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
                VillageBuildingConfig::BUILDING_MARKET => 2
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
                VillageUpgradeConfig::UPGRADE_KEY_BULK_SUPPLIERS_I
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_BULK_SUPPLIERS_III => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
                VillageBuildingConfig::BUILDING_MARKET => 3
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
                VillageUpgradeConfig::UPGRADE_KEY_BULK_SUPPLIERS_II
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_FARMERS_MARKET_I => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
                VillageBuildingConfig::BUILDING_MARKET => 1
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_FARMERS_MARKET_II => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
                VillageBuildingConfig::BUILDING_MARKET => 2
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
                VillageUpgradeConfig::UPGRADE_KEY_FARMERS_MARKET_I
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_FARMERS_MARKET_III => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
                VillageBuildingConfig::BUILDING_MARKET => 3
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
                VillageUpgradeConfig::UPGRADE_KEY_FARMERS_MARKET_II
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_MERCHANTS_GUILD_I => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
                VillageBuildingConfig::BUILDING_MARKET => 1
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_MERCHANTS_GUILD_II => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
                VillageBuildingConfig::BUILDING_MARKET => 2
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
                VillageUpgradeConfig::UPGRADE_KEY_MERCHANTS_GUILD_I
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_MERCHANTS_GUILD_III => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
                VillageBuildingConfig::BUILDING_MARKET => 3
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
                VillageUpgradeConfig::UPGRADE_KEY_MERCHANTS_GUILD_II
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_WAREHOUSES_I => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
                VillageBuildingConfig::BUILDING_MARKET => 1
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_WAREHOUSES_II => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
                VillageBuildingConfig::BUILDING_MARKET => 2
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
                VillageUpgradeConfig::UPGRADE_KEY_WAREHOUSES_I
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ANCESTRAL_LEGACY_I => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
                VillageBuildingConfig::BUILDING_SHRINE => 1
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ANCESTRAL_LEGACY_II => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
                VillageBuildingConfig::BUILDING_SHRINE => 2
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
                VillageUpgradeConfig::UPGRADE_KEY_ANCESTRAL_LEGACY_I
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ANCESTRAL_LEGACY_III => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
                VillageBuildingConfig::BUILDING_SHRINE => 3
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
                VillageUpgradeConfig::UPGRADE_KEY_ANCESTRAL_LEGACY_II
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_FORTUNES_BOUNTY_I => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
                VillageBuildingConfig::BUILDING_SHRINE => 1
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_FORTUNES_BOUNTY_II => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
                VillageBuildingConfig::BUILDING_SHRINE => 2
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
                VillageUpgradeConfig::UPGRADE_KEY_FORTUNES_BOUNTY_I
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_FORTUNES_BOUNTY_III => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
                VillageBuildingConfig::BUILDING_SHRINE => 3
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
                VillageUpgradeConfig::UPGRADE_KEY_FORTUNES_BOUNTY_II
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_NEW_RECIPE_I => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
                VillageBuildingConfig::BUILDING_RAMEN_STAND => 1
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_QUALITY_INGREDIENTS_I => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
                VillageBuildingConfig::BUILDING_RAMEN_STAND => 1
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_QUALITY_INGREDIENTS_II => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
                VillageBuildingConfig::BUILDING_RAMEN_STAND => 2
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
                VillageUpgradeConfig::UPGRADE_KEY_QUALITY_INGREDIENTS_I
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_QUALITY_INGREDIENTS_III => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
                VillageBuildingConfig::BUILDING_RAMEN_STAND => 3
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
                VillageUpgradeConfig::UPGRADE_KEY_QUALITY_INGREDIENTS_II
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_NINJA_FRIENDLY_RATES_I => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
                VillageBuildingConfig::BUILDING_RAMEN_STAND => 1
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_NINJA_FRIENDLY_RATES_II => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
                VillageBuildingConfig::BUILDING_RAMEN_STAND => 2
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
                VillageUpgradeConfig::UPGRADE_KEY_NINJA_FRIENDLY_RATES_I
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_NINJA_FRIENDLY_RATES_III => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
                VillageBuildingConfig::BUILDING_RAMEN_STAND => 3
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
                VillageUpgradeConfig::UPGRADE_KEY_NINJA_FRIENDLY_RATES_II
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_INSPIRED_ITAMAE_I => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
                VillageBuildingConfig::BUILDING_RAMEN_STAND => 1
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_LUCK_IN_LEFTOVERS_I => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
                VillageBuildingConfig::BUILDING_RAMEN_STAND => 2
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
                VillageUpgradeConfig::UPGRADE_KEY_INSPIRED_ITAMAE_I
            ],
        ],
        VillageUpgradeConfig::UPGRADE_KEY_LUCK_IN_LEFTOVERS_II => [
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_BUILDINGS => [
                VillageBuildingConfig::BUILDING_RAMEN_STAND => 3
            ],
            VillageUpgradeConfig::UPGRADE_REQUIREMENT_UPGRADES => [
                VillageUpgradeConfig::UPGRADE_KEY_LUCK_IN_LEFTOVERS_I
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
            WarManager::RESOURCE_MATERIALS => 2,
            WarManager::RESOURCE_FOOD => 4,
            WarManager::RESOURCE_WEALTH => 6,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_RESEARCH_SUBSIDIES_II => [
            WarManager::RESOURCE_MATERIALS => 3,
            WarManager::RESOURCE_FOOD => 6,
            WarManager::RESOURCE_WEALTH => 9,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_RESEARCH_SUBSIDIES_III => [
            WarManager::RESOURCE_MATERIALS => 5,
            WarManager::RESOURCE_FOOD => 10,
            WarManager::RESOURCE_WEALTH => 15,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ADMINISTRATION_I => [
            WarManager::RESOURCE_MATERIALS => 2,
            WarManager::RESOURCE_FOOD => 4,
            WarManager::RESOURCE_WEALTH => 6,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ADMINISTRATION_II => [
            WarManager::RESOURCE_MATERIALS => 3,
            WarManager::RESOURCE_FOOD => 6,
            WarManager::RESOURCE_WEALTH => 9,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ADMINISTRATION_III => [
            WarManager::RESOURCE_MATERIALS => 5,
            WarManager::RESOURCE_FOOD => 10,
            WarManager::RESOURCE_WEALTH => 15,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_POWER_PROJECTION_I => [
            WarManager::RESOURCE_MATERIALS => 4,
            WarManager::RESOURCE_FOOD => 4,
            WarManager::RESOURCE_WEALTH => 4,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_POWER_PROJECTION_II => [
            WarManager::RESOURCE_MATERIALS => 6,
            WarManager::RESOURCE_FOOD => 6,
            WarManager::RESOURCE_WEALTH => 6,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_POWER_PROJECTION_III => [
            WarManager::RESOURCE_MATERIALS => 10,
            WarManager::RESOURCE_FOOD => 10,
            WarManager::RESOURCE_WEALTH => 10,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_EDUCATION_SUBSIDIES_I => [
            WarManager::RESOURCE_MATERIALS => 2,
            WarManager::RESOURCE_FOOD => 4,
            WarManager::RESOURCE_WEALTH => 6,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_EDUCATION_SUBSIDIES_II => [
            WarManager::RESOURCE_MATERIALS => 3,
            WarManager::RESOURCE_FOOD => 6,
            WarManager::RESOURCE_WEALTH => 9,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_EDUCATION_SUBSIDIES_III => [
            WarManager::RESOURCE_MATERIALS => 5,
            WarManager::RESOURCE_FOOD => 10,
            WarManager::RESOURCE_WEALTH => 15,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_TRAINING_GROUNDS_I => [
            WarManager::RESOURCE_MATERIALS => 4,
            WarManager::RESOURCE_FOOD => 4,
            WarManager::RESOURCE_WEALTH => 4,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_TRAINING_GROUNDS_II => [
            WarManager::RESOURCE_MATERIALS => 6,
            WarManager::RESOURCE_FOOD => 6,
            WarManager::RESOURCE_WEALTH => 6,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_TRAINING_GROUNDS_III => [
            WarManager::RESOURCE_MATERIALS => 10,
            WarManager::RESOURCE_FOOD => 10,
            WarManager::RESOURCE_WEALTH => 10,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_MEDICAL_SUBSIDIES_I => [
            WarManager::RESOURCE_MATERIALS => 4,
            WarManager::RESOURCE_FOOD => 4,
            WarManager::RESOURCE_WEALTH => 4,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_MEDICAL_SUBSIDIES_II => [
            WarManager::RESOURCE_MATERIALS => 6,
            WarManager::RESOURCE_FOOD => 6,
            WarManager::RESOURCE_WEALTH => 6,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_MEDICAL_SUBSIDIES_III => [
            WarManager::RESOURCE_MATERIALS => 10,
            WarManager::RESOURCE_FOOD => 10,
            WarManager::RESOURCE_WEALTH => 10,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_HERBICULTURE_I => [
            WarManager::RESOURCE_MATERIALS => 2,
            WarManager::RESOURCE_FOOD => 6,
            WarManager::RESOURCE_WEALTH => 4,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_HERBICULTURE_II => [
            WarManager::RESOURCE_MATERIALS => 3,
            WarManager::RESOURCE_FOOD => 9,
            WarManager::RESOURCE_WEALTH => 6,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_HERBICULTURE_III => [
            WarManager::RESOURCE_MATERIALS => 5,
            WarManager::RESOURCE_FOOD => 15,
            WarManager::RESOURCE_WEALTH => 10,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_MILITARY_SUBSIDIES_I => [
            WarManager::RESOURCE_MATERIALS => 4,
            WarManager::RESOURCE_FOOD => 4,
            WarManager::RESOURCE_WEALTH => 4,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_MILITARY_SUBSIDIES_II => [
            WarManager::RESOURCE_MATERIALS => 6,
            WarManager::RESOURCE_FOOD => 6,
            WarManager::RESOURCE_WEALTH => 6,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_MILITARY_SUBSIDIES_III => [
            WarManager::RESOURCE_MATERIALS => 10,
            WarManager::RESOURCE_FOOD => 10,
            WarManager::RESOURCE_WEALTH => 10,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ASSAULT_TRAINING_I => [
            WarManager::RESOURCE_MATERIALS => 4,
            WarManager::RESOURCE_FOOD => 4,
            WarManager::RESOURCE_WEALTH => 4,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ASSAULT_TRAINING_II => [
            WarManager::RESOURCE_MATERIALS => 6,
            WarManager::RESOURCE_FOOD => 6,
            WarManager::RESOURCE_WEALTH => 6,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ASSAULT_TRAINING_III => [
            WarManager::RESOURCE_MATERIALS => 10,
            WarManager::RESOURCE_FOOD => 10,
            WarManager::RESOURCE_WEALTH => 10,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_SALTED_EARTH_I => [
            WarManager::RESOURCE_MATERIALS => 6,
            WarManager::RESOURCE_FOOD => 4,
            WarManager::RESOURCE_WEALTH => 2,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_SALTED_EARTH_II => [
            WarManager::RESOURCE_MATERIALS => 9,
            WarManager::RESOURCE_FOOD => 6,
            WarManager::RESOURCE_WEALTH => 3,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_SALTED_EARTH_III => [
            WarManager::RESOURCE_MATERIALS => 15,
            WarManager::RESOURCE_FOOD => 10,
            WarManager::RESOURCE_WEALTH => 5,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_UNDERWORLD_CONNECTIONS_I => [
            WarManager::RESOURCE_MATERIALS => 2,
            WarManager::RESOURCE_FOOD => 2,
            WarManager::RESOURCE_WEALTH => 8,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_UNDERWORLD_CONNECTIONS_II => [
            WarManager::RESOURCE_MATERIALS => 3,
            WarManager::RESOURCE_FOOD => 3,
            WarManager::RESOURCE_WEALTH => 12,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_UNDERWORLD_CONNECTIONS_III => [
            WarManager::RESOURCE_MATERIALS => 5,
            WarManager::RESOURCE_FOOD => 5,
            WarManager::RESOURCE_WEALTH => 20,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_GUERRILLA_WARFARE_I => [
            WarManager::RESOURCE_MATERIALS => 4,
            WarManager::RESOURCE_FOOD => 4,
            WarManager::RESOURCE_WEALTH => 4,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_GUERRILLA_WARFARE_II => [
            WarManager::RESOURCE_MATERIALS => 6,
            WarManager::RESOURCE_FOOD => 6,
            WarManager::RESOURCE_WEALTH => 6,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_GUERRILLA_WARFARE_III => [
            WarManager::RESOURCE_MATERIALS => 10,
            WarManager::RESOURCE_FOOD => 10,
            WarManager::RESOURCE_WEALTH => 10,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_SELECTIVE_RECRUITMENT_I => [
            WarManager::RESOURCE_MATERIALS => 4,
            WarManager::RESOURCE_FOOD => 4,
            WarManager::RESOURCE_WEALTH => 4,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_SELECTIVE_RECRUITMENT_II => [
            WarManager::RESOURCE_MATERIALS => 6,
            WarManager::RESOURCE_FOOD => 6,
            WarManager::RESOURCE_WEALTH => 6,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_SELECTIVE_RECRUITMENT_III => [
            WarManager::RESOURCE_MATERIALS => 10,
            WarManager::RESOURCE_FOOD => 10,
            WarManager::RESOURCE_WEALTH => 10,
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
            WarManager::RESOURCE_MATERIALS => 8,
            WarManager::RESOURCE_FOOD => 2,
            WarManager::RESOURCE_WEALTH => 2,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ENGINEERING_SUBSIDIES_II => [
            WarManager::RESOURCE_MATERIALS => 12,
            WarManager::RESOURCE_FOOD => 3,
            WarManager::RESOURCE_WEALTH => 3,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ENGINEERING_SUBSIDIES_III => [
            WarManager::RESOURCE_MATERIALS => 20,
            WarManager::RESOURCE_FOOD => 5,
            WarManager::RESOURCE_WEALTH => 5,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_RAPID_DEPLOYMENT_I => [
            WarManager::RESOURCE_MATERIALS => 4,
            WarManager::RESOURCE_FOOD => 4,
            WarManager::RESOURCE_WEALTH => 4,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_RAPID_DEPLOYMENT_II => [
            WarManager::RESOURCE_MATERIALS => 6,
            WarManager::RESOURCE_FOOD => 6,
            WarManager::RESOURCE_WEALTH => 6,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_RAPID_DEPLOYMENT_III => [
            WarManager::RESOURCE_MATERIALS => 10,
            WarManager::RESOURCE_FOOD => 10,
            WarManager::RESOURCE_WEALTH => 10,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ENGINEERING_CORPS_I => [
            WarManager::RESOURCE_MATERIALS => 6,
            WarManager::RESOURCE_FOOD => 4,
            WarManager::RESOURCE_WEALTH => 2,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ENGINEERING_CORPS_II => [
            WarManager::RESOURCE_MATERIALS => 9,
            WarManager::RESOURCE_FOOD => 6,
            WarManager::RESOURCE_WEALTH => 3,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ENGINEERING_CORPS_III => [
            WarManager::RESOURCE_MATERIALS => 15,
            WarManager::RESOURCE_FOOD => 10,
            WarManager::RESOURCE_WEALTH => 5,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_FORTIFICATIONS_I => [
            WarManager::RESOURCE_MATERIALS => 8,
            WarManager::RESOURCE_FOOD => 2,
            WarManager::RESOURCE_WEALTH => 2,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_FORTIFICATIONS_II => [
            WarManager::RESOURCE_MATERIALS => 12,
            WarManager::RESOURCE_FOOD => 3,
            WarManager::RESOURCE_WEALTH => 3,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_FORTIFICATIONS_III => [
            WarManager::RESOURCE_MATERIALS => 20,
            WarManager::RESOURCE_FOOD => 5,
            WarManager::RESOURCE_WEALTH => 5,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_BULK_SUPPLIERS_I => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 25,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_BULK_SUPPLIERS_II => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 25,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_BULK_SUPPLIERS_III => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 25,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_FARMERS_MARKET_I => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 25,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_FARMERS_MARKET_II => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 25,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_FARMERS_MARKET_III => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 25,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_MERCHANTS_GUILD_I => [
            WarManager::RESOURCE_MATERIALS => 25,
            WarManager::RESOURCE_FOOD => 25,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_MERCHANTS_GUILD_II => [
            WarManager::RESOURCE_MATERIALS => 25,
            WarManager::RESOURCE_FOOD => 25,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_MERCHANTS_GUILD_III => [
            WarManager::RESOURCE_MATERIALS => 25,
            WarManager::RESOURCE_FOOD => 25,
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
            WarManager::RESOURCE_MATERIALS => 4,
            WarManager::RESOURCE_FOOD => 4,
            WarManager::RESOURCE_WEALTH => 4,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_FORTUNES_BOUNTY_II => [
            WarManager::RESOURCE_MATERIALS => 6,
            WarManager::RESOURCE_FOOD => 6,
            WarManager::RESOURCE_WEALTH => 6,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_FORTUNES_BOUNTY_III => [
            WarManager::RESOURCE_MATERIALS => 10,
            WarManager::RESOURCE_FOOD => 10,
            WarManager::RESOURCE_WEALTH => 10,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_NEW_RECIPE_I => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_QUALITY_INGREDIENTS_I => [
            WarManager::RESOURCE_MATERIALS => 2,
            WarManager::RESOURCE_FOOD => 8,
            WarManager::RESOURCE_WEALTH => 2,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_QUALITY_INGREDIENTS_II => [
            WarManager::RESOURCE_MATERIALS => 3,
            WarManager::RESOURCE_FOOD => 12,
            WarManager::RESOURCE_WEALTH => 3,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_QUALITY_INGREDIENTS_III => [
            WarManager::RESOURCE_MATERIALS => 5,
            WarManager::RESOURCE_FOOD => 20,
            WarManager::RESOURCE_WEALTH => 5,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_NINJA_FRIENDLY_RATES_I => [
            WarManager::RESOURCE_MATERIALS => 2,
            WarManager::RESOURCE_FOOD => 4,
            WarManager::RESOURCE_WEALTH => 6,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_NINJA_FRIENDLY_RATES_II => [
            WarManager::RESOURCE_MATERIALS => 3,
            WarManager::RESOURCE_FOOD => 6,
            WarManager::RESOURCE_WEALTH => 9,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_NINJA_FRIENDLY_RATES_III => [
            WarManager::RESOURCE_MATERIALS => 5,
            WarManager::RESOURCE_FOOD => 10,
            WarManager::RESOURCE_WEALTH => 15,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_INSPIRED_ITAMAE_I => [
            WarManager::RESOURCE_MATERIALS => 0,
            WarManager::RESOURCE_FOOD => 0,
            WarManager::RESOURCE_WEALTH => 0,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_LUCK_IN_LEFTOVERS_I => [
            WarManager::RESOURCE_MATERIALS => 3,
            WarManager::RESOURCE_FOOD => 12,
            WarManager::RESOURCE_WEALTH => 3,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_LUCK_IN_LEFTOVERS_II => [
            WarManager::RESOURCE_MATERIALS => 5,
            WarManager::RESOURCE_FOOD => 20,
            WarManager::RESOURCE_WEALTH => 5,
        ],
    ];

    /* upkeep effects indexed by upgrade key */
    const UPGRADE_EFFECTS = [
        VillageUpgradeConfig::UPGRADE_KEY_TEST => [
            VillageUpgradeConfig::UPGRADE_EFFECT_BASE_STABILITY => 5,
            VillageUpgradeConfig::UPGRADE_EFFECT_MAX_STABILITY => 2,
        ],
        VillageUpgradeConfig::UPGRADE_KEY_RESEARCH_I => [
            VillageUpgradeConfig::UPGRADE_EFFECT_RESEARCH_T1_ENABLED => 1
        ],
        VillageUpgradeConfig::UPGRADE_KEY_RESEARCH_II => [
            VillageUpgradeConfig::UPGRADE_EFFECT_RESEARCH_T2_ENABLED => 1
        ],
        VillageUpgradeConfig::UPGRADE_KEY_RESEARCH_III => [
            VillageUpgradeConfig::UPGRADE_EFFECT_RESEARCH_T3_ENABLED => 1
        ],
        VillageUpgradeConfig::UPGRADE_KEY_RESEARCH_SUBSIDIES_I => [
            VillageUpgradeConfig::UPGRADE_EFFECT_RESEARCH_SPEED => 5
        ],
        VillageUpgradeConfig::UPGRADE_KEY_RESEARCH_SUBSIDIES_II => [
            VillageUpgradeConfig::UPGRADE_EFFECT_RESEARCH_SPEED => 5
        ],
        VillageUpgradeConfig::UPGRADE_KEY_RESEARCH_SUBSIDIES_III => [
            VillageUpgradeConfig::UPGRADE_EFFECT_RESEARCH_SPEED => 5
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ADMINISTRATION_I => [
            VillageUpgradeConfig::UPGRADE_EFFECT_UPGRADE_UPKEEP => 5
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ADMINISTRATION_II => [
            VillageUpgradeConfig::UPGRADE_EFFECT_UPGRADE_UPKEEP => 5
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ADMINISTRATION_III => [
            VillageUpgradeConfig::UPGRADE_EFFECT_UPGRADE_UPKEEP => 5
        ],
        VillageUpgradeConfig::UPGRADE_KEY_POWER_PROJECTION_I => [
            VillageUpgradeConfig::UPGRADE_EFFECT_BASE_STABILITY => 5,
            VillageUpgradeConfig::UPGRADE_EFFECT_MAX_STABILITY => 5
        ],
        VillageUpgradeConfig::UPGRADE_KEY_POWER_PROJECTION_II => [
            VillageUpgradeConfig::UPGRADE_EFFECT_BASE_STABILITY => 5,
            VillageUpgradeConfig::UPGRADE_EFFECT_MAX_STABILITY => 5
        ],
        VillageUpgradeConfig::UPGRADE_KEY_POWER_PROJECTION_III => [
            VillageUpgradeConfig::UPGRADE_EFFECT_BASE_STABILITY => 5,
            VillageUpgradeConfig::UPGRADE_EFFECT_MAX_STABILITY => 5
        ],
        VillageUpgradeConfig::UPGRADE_KEY_EDUCATION_SUBSIDIES_I => [
            VillageUpgradeConfig::UPGRADE_EFFECT_TRAINING_SPEED => 3
        ],
        VillageUpgradeConfig::UPGRADE_KEY_EDUCATION_SUBSIDIES_II => [
            VillageUpgradeConfig::UPGRADE_EFFECT_TRAINING_SPEED => 3
        ],
        VillageUpgradeConfig::UPGRADE_KEY_EDUCATION_SUBSIDIES_III => [
            VillageUpgradeConfig::UPGRADE_EFFECT_TRAINING_SPEED => 3
        ],
        VillageUpgradeConfig::UPGRADE_KEY_TRAINING_GROUNDS_I => [
            VillageUpgradeConfig::UPGRADE_EFFECT_DOUBLE_BATTLE_XP_CHANCE => 5
        ],
        VillageUpgradeConfig::UPGRADE_KEY_TRAINING_GROUNDS_II => [
            VillageUpgradeConfig::UPGRADE_EFFECT_DOUBLE_BATTLE_XP_CHANCE => 5
        ],
        VillageUpgradeConfig::UPGRADE_KEY_TRAINING_GROUNDS_III => [
            VillageUpgradeConfig::UPGRADE_EFFECT_DOUBLE_BATTLE_XP_CHANCE => 5
        ],
        VillageUpgradeConfig::UPGRADE_KEY_MEDICAL_SUBSIDIES_I => [
            VillageUpgradeConfig::UPGRADE_EFFECT_VILLAGE_REGEN => 5
        ],
        VillageUpgradeConfig::UPGRADE_KEY_MEDICAL_SUBSIDIES_II => [
            VillageUpgradeConfig::UPGRADE_EFFECT_VILLAGE_REGEN => 5
        ],
        VillageUpgradeConfig::UPGRADE_KEY_MEDICAL_SUBSIDIES_III => [
            VillageUpgradeConfig::UPGRADE_EFFECT_VILLAGE_REGEN => 5
        ],
        VillageUpgradeConfig::UPGRADE_KEY_HERBICULTURE_I => [
            VillageUpgradeConfig::UPGRADE_EFFECT_HEAL_ITEM_COST => 10
        ],
        VillageUpgradeConfig::UPGRADE_KEY_HERBICULTURE_II => [
            VillageUpgradeConfig::UPGRADE_EFFECT_HEAL_ITEM_COST => 10
        ],
        VillageUpgradeConfig::UPGRADE_KEY_HERBICULTURE_III => [
            VillageUpgradeConfig::UPGRADE_EFFECT_HEAL_ITEM_COST => 10
        ],
        VillageUpgradeConfig::UPGRADE_KEY_MILITARY_SUBSIDIES_I => [
            VillageUpgradeConfig::UPGRADE_EFFECT_WAR_ACTION_COST => 5
        ],
        VillageUpgradeConfig::UPGRADE_KEY_MILITARY_SUBSIDIES_II => [
            VillageUpgradeConfig::UPGRADE_EFFECT_WAR_ACTION_COST => 5
        ],
        VillageUpgradeConfig::UPGRADE_KEY_MILITARY_SUBSIDIES_III => [
            VillageUpgradeConfig::UPGRADE_EFFECT_WAR_ACTION_COST => 5
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ASSAULT_TRAINING_I => [
            VillageUpgradeConfig::UPGRADE_EFFECT_RAID_SPEED => 5
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ASSAULT_TRAINING_II => [
            VillageUpgradeConfig::UPGRADE_EFFECT_RAID_SPEED => 5
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ASSAULT_TRAINING_III => [
            VillageUpgradeConfig::UPGRADE_EFFECT_RAID_SPEED => 5
        ],
        VillageUpgradeConfig::UPGRADE_KEY_SALTED_EARTH_I => [
            VillageUpgradeConfig::UPGRADE_EFFECT_RAID_DAMAGE => 5
        ],
        VillageUpgradeConfig::UPGRADE_KEY_SALTED_EARTH_II => [
            VillageUpgradeConfig::UPGRADE_EFFECT_RAID_DAMAGE => 5
        ],
        VillageUpgradeConfig::UPGRADE_KEY_SALTED_EARTH_III => [
            VillageUpgradeConfig::UPGRADE_EFFECT_RAID_DAMAGE => 5
        ],
        VillageUpgradeConfig::UPGRADE_KEY_UNDERWORLD_CONNECTIONS_I => [
            VillageUpgradeConfig::UPGRADE_EFFECT_INFILTRATE_SPEED => 5
        ],
        VillageUpgradeConfig::UPGRADE_KEY_UNDERWORLD_CONNECTIONS_II => [
            VillageUpgradeConfig::UPGRADE_EFFECT_INFILTRATE_SPEED => 5
        ],
        VillageUpgradeConfig::UPGRADE_KEY_UNDERWORLD_CONNECTIONS_III => [
            VillageUpgradeConfig::UPGRADE_EFFECT_INFILTRATE_SPEED => 5
        ],
        VillageUpgradeConfig::UPGRADE_KEY_GUERRILLA_WARFARE_I => [
            VillageUpgradeConfig::UPGRADE_EFFECT_OCCUPIED_BASE_STABILITY_REDUCTION => 5,
            VillageUpgradeConfig::UPGRADE_EFFECT_OCCUPIED_MAX_STABILITY_REDUCTION => 5
        ],
        VillageUpgradeConfig::UPGRADE_KEY_GUERRILLA_WARFARE_II => [
            VillageUpgradeConfig::UPGRADE_EFFECT_OCCUPIED_BASE_STABILITY_REDUCTION => 5,
            VillageUpgradeConfig::UPGRADE_EFFECT_OCCUPIED_MAX_STABILITY_REDUCTION => 5
        ],
        VillageUpgradeConfig::UPGRADE_KEY_GUERRILLA_WARFARE_III => [
            VillageUpgradeConfig::UPGRADE_EFFECT_OCCUPIED_BASE_STABILITY_REDUCTION => 5,
            VillageUpgradeConfig::UPGRADE_EFFECT_OCCUPIED_MAX_STABILITY_REDUCTION => 5
        ],
        VillageUpgradeConfig::UPGRADE_KEY_SELECTIVE_RECRUITMENT_I => [
            VillageUpgradeConfig::UPGRADE_EFFECT_PATROL_TIER_CHANCE => 5
        ],
        VillageUpgradeConfig::UPGRADE_KEY_SELECTIVE_RECRUITMENT_II => [
            VillageUpgradeConfig::UPGRADE_EFFECT_PATROL_TIER_CHANCE => 5
        ],
        VillageUpgradeConfig::UPGRADE_KEY_SELECTIVE_RECRUITMENT_III => [
            VillageUpgradeConfig::UPGRADE_EFFECT_PATROL_TIER_CHANCE => 5
        ],
        VillageUpgradeConfig::UPGRADE_KEY_CONSTRUCTION_I => [
            VillageUpgradeConfig::UPGRADE_EFFECT_CONSTRUCTION_T1_ENABLED => 1
        ],
        VillageUpgradeConfig::UPGRADE_KEY_CONSTRUCTION_II => [
            VillageUpgradeConfig::UPGRADE_EFFECT_CONSTRUCTION_T2_ENABLED => 1
        ],
        VillageUpgradeConfig::UPGRADE_KEY_CONSTRUCTION_III => [
            VillageUpgradeConfig::UPGRADE_EFFECT_CONSTRUCTION_T3_ENABLED => 1
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ENGINEERING_SUBSIDIES_I => [
            VillageUpgradeConfig::UPGRADE_EFFECT_CONSTRUCTION_SPEED => 5
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ENGINEERING_SUBSIDIES_II => [
            VillageUpgradeConfig::UPGRADE_EFFECT_CONSTRUCTION_SPEED => 5
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ENGINEERING_SUBSIDIES_III => [
            VillageUpgradeConfig::UPGRADE_EFFECT_CONSTRUCTION_SPEED => 5
        ],
        VillageUpgradeConfig::UPGRADE_KEY_RAPID_DEPLOYMENT_I => [
            VillageUpgradeConfig::UPGRADE_EFFECT_REINFORCE_SPEED => 5
        ],
        VillageUpgradeConfig::UPGRADE_KEY_RAPID_DEPLOYMENT_II => [
            VillageUpgradeConfig::UPGRADE_EFFECT_REINFORCE_SPEED => 5
        ],
        VillageUpgradeConfig::UPGRADE_KEY_RAPID_DEPLOYMENT_III => [
            VillageUpgradeConfig::UPGRADE_EFFECT_REINFORCE_SPEED => 5
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ENGINEERING_CORPS_I => [
            VillageUpgradeConfig::UPGRADE_EFFECT_REINFORCE_HEAL => 5
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ENGINEERING_CORPS_II => [
            VillageUpgradeConfig::UPGRADE_EFFECT_REINFORCE_HEAL => 5
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ENGINEERING_CORPS_III => [
            VillageUpgradeConfig::UPGRADE_EFFECT_REINFORCE_HEAL => 5
        ],
        VillageUpgradeConfig::UPGRADE_KEY_FORTIFICATIONS_I => [
            VillageUpgradeConfig::UPGRADE_EFFECT_CASTLE_HP => 5
        ],
        VillageUpgradeConfig::UPGRADE_KEY_FORTIFICATIONS_II => [
            VillageUpgradeConfig::UPGRADE_EFFECT_CASTLE_HP => 5
        ],
        VillageUpgradeConfig::UPGRADE_KEY_FORTIFICATIONS_III => [
            VillageUpgradeConfig::UPGRADE_EFFECT_CASTLE_HP => 5
        ],
        VillageUpgradeConfig::UPGRADE_KEY_BULK_SUPPLIERS_I => [
            VillageUpgradeConfig::UPGRADE_EFFECT_MATERIALS_PRODUCTION => 25
        ],
        VillageUpgradeConfig::UPGRADE_KEY_BULK_SUPPLIERS_II => [
            VillageUpgradeConfig::UPGRADE_EFFECT_MATERIALS_PRODUCTION => 50
        ],
        VillageUpgradeConfig::UPGRADE_KEY_BULK_SUPPLIERS_III => [
            VillageUpgradeConfig::UPGRADE_EFFECT_MATERIALS_PRODUCTION => 75
        ],
        VillageUpgradeConfig::UPGRADE_KEY_FARMERS_MARKET_I => [
            VillageUpgradeConfig::UPGRADE_EFFECT_FOOD_PRODUCTION => 25
        ],
        VillageUpgradeConfig::UPGRADE_KEY_FARMERS_MARKET_II => [
            VillageUpgradeConfig::UPGRADE_EFFECT_FOOD_PRODUCTION => 50
        ],
        VillageUpgradeConfig::UPGRADE_KEY_FARMERS_MARKET_III => [
            VillageUpgradeConfig::UPGRADE_EFFECT_FOOD_PRODUCTION => 75
        ],
        VillageUpgradeConfig::UPGRADE_KEY_MERCHANTS_GUILD_I => [
            VillageUpgradeConfig::UPGRADE_EFFECT_WEALTH_PRODUCTION => 50
        ],
        VillageUpgradeConfig::UPGRADE_KEY_MERCHANTS_GUILD_II => [
            VillageUpgradeConfig::UPGRADE_EFFECT_WEALTH_PRODUCTION => 75
        ],
        VillageUpgradeConfig::UPGRADE_KEY_MERCHANTS_GUILD_III => [
            VillageUpgradeConfig::UPGRADE_EFFECT_WEALTH_PRODUCTION => 100
        ],
        VillageUpgradeConfig::UPGRADE_KEY_WAREHOUSES_I => [
            VillageUpgradeConfig::UPGRADE_EFFECT_RESOURCE_CAPACITY => 200000
        ],
        VillageUpgradeConfig::UPGRADE_KEY_WAREHOUSES_II => [
            VillageUpgradeConfig::UPGRADE_EFFECT_RESOURCE_CAPACITY => 500000
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ANCESTRAL_LEGACY_I => [
            VillageUpgradeConfig::UPGRADE_EFFECT_BLOODLINE_CHANCE => 5
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ANCESTRAL_LEGACY_II => [
            VillageUpgradeConfig::UPGRADE_EFFECT_BLOODLINE_CHANCE => 5
        ],
        VillageUpgradeConfig::UPGRADE_KEY_ANCESTRAL_LEGACY_III => [
            VillageUpgradeConfig::UPGRADE_EFFECT_BLOODLINE_CHANCE => 5
        ],
        VillageUpgradeConfig::UPGRADE_KEY_FORTUNES_BOUNTY_I => [
            VillageUpgradeConfig::UPGRADE_EFFECT_DOUBLE_BATTLE_YEN_CHANCE => 5
        ],
        VillageUpgradeConfig::UPGRADE_KEY_FORTUNES_BOUNTY_II => [
            VillageUpgradeConfig::UPGRADE_EFFECT_DOUBLE_BATTLE_YEN_CHANCE => 5
        ],
        VillageUpgradeConfig::UPGRADE_KEY_FORTUNES_BOUNTY_III => [
            VillageUpgradeConfig::UPGRADE_EFFECT_DOUBLE_BATTLE_YEN_CHANCE => 5
        ],
        VillageUpgradeConfig::UPGRADE_KEY_NEW_RECIPE_I => [
            VillageUpgradeConfig::UPGRADE_EFFECT_RAMEN_SET_ONE => 1
        ],
        VillageUpgradeConfig::UPGRADE_KEY_QUALITY_INGREDIENTS_I => [
            VillageUpgradeConfig::UPGRADE_EFFECT_RAMEN_DURATION => 5
        ],
        VillageUpgradeConfig::UPGRADE_KEY_QUALITY_INGREDIENTS_II => [
            VillageUpgradeConfig::UPGRADE_EFFECT_RAMEN_DURATION => 5
        ],
        VillageUpgradeConfig::UPGRADE_KEY_QUALITY_INGREDIENTS_III => [
            VillageUpgradeConfig::UPGRADE_EFFECT_RAMEN_DURATION => 5
        ],
        VillageUpgradeConfig::UPGRADE_KEY_NINJA_FRIENDLY_RATES_I => [
            VillageUpgradeConfig::UPGRADE_EFFECT_RAMEN_COST => 5
        ],
        VillageUpgradeConfig::UPGRADE_KEY_NINJA_FRIENDLY_RATES_II => [
            VillageUpgradeConfig::UPGRADE_EFFECT_RAMEN_COST => 5
        ],
        VillageUpgradeConfig::UPGRADE_KEY_NINJA_FRIENDLY_RATES_III => [
            VillageUpgradeConfig::UPGRADE_EFFECT_RAMEN_COST => 5
        ],
        VillageUpgradeConfig::UPGRADE_KEY_INSPIRED_ITAMAE_I => [
            VillageUpgradeConfig::UPGRADE_EFFECT_MYSTERY_RAMEN_ENABLED => 1
        ],
        VillageUpgradeConfig::UPGRADE_KEY_LUCK_IN_LEFTOVERS_I => [
            VillageUpgradeConfig::UPGRADE_EFFECT_MYSTERY_RAMEN_CHANCE => 5
        ],
        VillageUpgradeConfig::UPGRADE_KEY_LUCK_IN_LEFTOVERS_II => [
            VillageUpgradeConfig::UPGRADE_EFFECT_MYSTERY_RAMEN_CHANCE => 5
        ],
    ];
}