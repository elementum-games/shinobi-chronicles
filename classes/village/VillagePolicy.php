<?php

class VillagePolicy {
    const POLICY_BONUS_INFILTRATE_SPEED = "INFILTRATE_SPEED";
    const POLICY_BONUS_INFILTRATE_DEFENSE = "INFILTRATE_DEFENSE";
    const POLICY_BONUS_INFILTRATE_STABILITY = "INFILTRATE_STABILITY";
    const POLICY_BONUS_REINFORCE_SPEED = "REINFORCE_SPEED";
    const POLICY_BONUS_REINFORCE_DEFENSE = "REINFORCE_DEFENSE";
    const POLICY_BONUS_REINFORCE_STABILITY = "REINFORCE_STABILITY";
    const POLICY_BONUS_RAID_SPEED = "RAID_SPEED";
    const POLICY_BONUS_RAID_DEFENSE = "RAID_DEFENSE";
    const POLICY_BONUS_RAID_STABILITY = "RAID_STABILITY";
    const POLICY_BONUS_CARAVAN_SPEED = "CARAVAN_SPEED";
    const POLICY_BONUS_PATROL_RESPAWN = "PATROL_RESPAWN";
    const POLICY_BONUS_PATROL_TIER = "PATROL_TIER";
    const POLICY_BONUS_TRAINING_SPEED = "TRAINING_SPEED";
    const POLICY_BONUS_TRANSFER_COST_REDUCTION = "TRANSFER_COST_REDUCTION";
    const POLICY_BONUS_HOME_PRODUCTION_BOOST = "HOME_PRODUCTION_BOOST";
    const POLICY_BONUS_SCOUTING = "SCOUTING";
    const POLICY_BONUS_STEALTH = "STEALTH";
    const POLICY_BONUS_LOOT_CAPACITY = "LOOT_CAPACITY";
    const POLICY_BONUS_PVP_VILLAGE_POINT = "PVP_VILLAGE_POINT";
    const POLICY_BONUS_CONSTRUCTION_SPEED = "CONSTRUCTION_SPEED";
    const POLICY_BONUS_RESEARCH_SPEED = "RESEARCH_SPEED";
    const POLICY_BONUS_BASE_STABILITY = "BASE_STABILITY";
    const POLICY_BONUS_MAX_STABILITY = "MAX_STABILITY";
    const POLICY_BONUS_PVE_INCOME = "PVE_INCOME";
    const POLICY_BONUS_UPKEEP_REDUCTION = "UPKEEP_REDUCTION";
    const POLICY_RESTRICTION_WAR_ENABLED = "WAR_ENABLED";
    const POLICY_RESTRICTION_ALLIANCE_ENABLED = "ALLIANCE_ENABLED";
    const POLICY_BASE_MATERIALS_PRODUCTION = "MATERIALS_PRODUCTION";
    const POLICY_BASE_FOOD_PRODUCTION = "FOOD_PRODUCTION";
    const POLICY_BASE_WEALTH_PRODUCTION = "WEALTH_PRODUCTION";

    const POLICY_NONE = 0;
    const POLICY_GROWTH = 1;
    const POLICY_ESPIONAGE = 2;
    const POLICY_DEFENSE = 3;
    const POLICY_WAR = 4;
    const POLICY_PROSPERITY = 5;

    const POLICY_NAMES = [
        self::POLICY_NONE => "Inactive Policy",
        self::POLICY_GROWTH => "From the Ashes",
        self::POLICY_ESPIONAGE => "Eye of the Storm",
        self::POLICY_DEFENSE => "Fortress of Solitude",
        self::POLICY_WAR => "Forged in Flames",
        self::POLICY_PROSPERITY => "The Gilded Hand",
    ];

    public System $system;
    public int $infiltrate_speed;
    public int $infiltrate_defense;
    public int $infiltrate_stability;
    public int $reinforce_speed;
    public int $reinforce_defense;
    public int $reinforce_stability;
    public int $raid_speed;
    public int $raid_defense;
    public int $raid_stability;
    public int $caravan_speed;
    public int $patrol_respawn;
    public int $patrol_tier;
    public int $training_speed;
    public int $transfer_cost_reduction;
    public int $home_production_boost;
    public int $scouting;
    public int $stealth;
    public int $loot_capacity;
    public int $pvp_village_point;
    public int $construction_speed;
    public int $research_speed;
    public int $base_stability;
    public int $max_stability;
    public int $pve_income;
    public int $upkeep_reduction;
    public bool $war_enabled;
    public bool $alliance_enabled;
    public int $materials_production;
    public int $food_production;
    public int $wealth_production;

    public static $POLICY_EFFECTS = [];

    public static function initializePolicyEffects() {
        self::$POLICY_EFFECTS = [
            self::POLICY_NONE => [
                self::POLICY_BONUS_INFILTRATE_SPEED => 0,
                self::POLICY_BONUS_INFILTRATE_DEFENSE => 0,
                self::POLICY_BONUS_INFILTRATE_STABILITY => 0,
                self::POLICY_BONUS_REINFORCE_SPEED => 0,
                self::POLICY_BONUS_REINFORCE_DEFENSE => 0,
                self::POLICY_BONUS_REINFORCE_STABILITY => 0,
                self::POLICY_BONUS_RAID_SPEED => 0,
                self::POLICY_BONUS_RAID_DEFENSE => 0,
                self::POLICY_BONUS_RAID_STABILITY => 0,
                self::POLICY_BONUS_CARAVAN_SPEED => 0,
                self::POLICY_BONUS_PATROL_RESPAWN => 0,
                self::POLICY_BONUS_PATROL_TIER => 0,
                self::POLICY_BONUS_TRAINING_SPEED => 0,
                self::POLICY_BONUS_TRANSFER_COST_REDUCTION => 0,
                self::POLICY_BONUS_HOME_PRODUCTION_BOOST => 0,
                self::POLICY_BONUS_SCOUTING => 0,
                self::POLICY_BONUS_STEALTH => 0,
                self::POLICY_BONUS_LOOT_CAPACITY => 0,
                self::POLICY_BONUS_PVP_VILLAGE_POINT => 0,
                self::POLICY_BONUS_CONSTRUCTION_SPEED => 0,
                self::POLICY_BONUS_RESEARCH_SPEED => 0,
                self::POLICY_BONUS_BASE_STABILITY => 0,
                self::POLICY_BONUS_MAX_STABILITY => 0,
                self::POLICY_BONUS_PVE_INCOME => 0,
                self::POLICY_BONUS_UPKEEP_REDUCTION => 0,
                self::POLICY_RESTRICTION_WAR_ENABLED => true,
                self::POLICY_RESTRICTION_ALLIANCE_ENABLED => true,
                self::POLICY_BASE_MATERIALS_PRODUCTION => 0,
                self::POLICY_BASE_FOOD_PRODUCTION => 0,
                self::POLICY_BASE_WEALTH_PRODUCTION => 0,
            ],
            self::POLICY_GROWTH => [
                self::POLICY_BONUS_INFILTRATE_SPEED => 0,
                self::POLICY_BONUS_INFILTRATE_DEFENSE => 0,
                self::POLICY_BONUS_INFILTRATE_STABILITY => 0,
                self::POLICY_BONUS_REINFORCE_SPEED => 0,
                self::POLICY_BONUS_REINFORCE_DEFENSE => 0,
                self::POLICY_BONUS_REINFORCE_STABILITY => 0,
                self::POLICY_BONUS_RAID_SPEED => 0,
                self::POLICY_BONUS_RAID_DEFENSE => 0,
                self::POLICY_BONUS_RAID_STABILITY => 0,
                self::POLICY_BONUS_CARAVAN_SPEED => 25,
                self::POLICY_BONUS_PATROL_RESPAWN => 0,
                self::POLICY_BONUS_PATROL_TIER => 0,
                self::POLICY_BONUS_TRAINING_SPEED => 0,
                self::POLICY_BONUS_TRANSFER_COST_REDUCTION => 50,
                self::POLICY_BONUS_HOME_PRODUCTION_BOOST => 0,
                self::POLICY_BONUS_SCOUTING => 0,
                self::POLICY_BONUS_STEALTH => 0,
                self::POLICY_BONUS_LOOT_CAPACITY => 0,
                self::POLICY_BONUS_PVP_VILLAGE_POINT => 0,
                self::POLICY_BONUS_CONSTRUCTION_SPEED => 15,
                self::POLICY_BONUS_RESEARCH_SPEED => 15,
                self::POLICY_BONUS_BASE_STABILITY => 0,
                self::POLICY_BONUS_MAX_STABILITY => 0,
                self::POLICY_BONUS_PVE_INCOME => 0,
                self::POLICY_BONUS_UPKEEP_REDUCTION => 0,
                self::POLICY_RESTRICTION_WAR_ENABLED => false,
                self::POLICY_RESTRICTION_ALLIANCE_ENABLED => true,
                self::POLICY_BASE_MATERIALS_PRODUCTION => 70,
                self::POLICY_BASE_FOOD_PRODUCTION => 100,
                self::POLICY_BASE_WEALTH_PRODUCTION => 40,
            ],
            self::POLICY_ESPIONAGE => [
                self::POLICY_BONUS_INFILTRATE_SPEED => 25,
                self::POLICY_BONUS_INFILTRATE_DEFENSE => 1,
                self::POLICY_BONUS_INFILTRATE_STABILITY => 1,
                self::POLICY_BONUS_REINFORCE_SPEED => 0,
                self::POLICY_BONUS_REINFORCE_DEFENSE => 0,
                self::POLICY_BONUS_REINFORCE_STABILITY => 0,
                self::POLICY_BONUS_RAID_SPEED => 0,
                self::POLICY_BONUS_RAID_DEFENSE => 0,
                self::POLICY_BONUS_RAID_STABILITY => 0,
                self::POLICY_BONUS_CARAVAN_SPEED => 0,
                self::POLICY_BONUS_PATROL_RESPAWN => 0,
                self::POLICY_BONUS_PATROL_TIER => 0,
                self::POLICY_BONUS_TRAINING_SPEED => 0,
                self::POLICY_BONUS_TRANSFER_COST_REDUCTION => 0,
                self::POLICY_BONUS_HOME_PRODUCTION_BOOST => 0,
                self::POLICY_BONUS_SCOUTING => 0,
                self::POLICY_BONUS_STEALTH => 1,
                self::POLICY_BONUS_LOOT_CAPACITY => 0,
                self::POLICY_BONUS_PVP_VILLAGE_POINT => 0,
                self::POLICY_BONUS_CONSTRUCTION_SPEED => 0,
                self::POLICY_BONUS_RESEARCH_SPEED => 0,
                self::POLICY_BONUS_BASE_STABILITY => 0,
                self::POLICY_BONUS_MAX_STABILITY => 0,
                self::POLICY_BONUS_PVE_INCOME => 0,
                self::POLICY_BONUS_UPKEEP_REDUCTION => 0,
                self::POLICY_RESTRICTION_WAR_ENABLED => true,
                self::POLICY_RESTRICTION_ALLIANCE_ENABLED => true,
                self::POLICY_BASE_MATERIALS_PRODUCTION => 70,
                self::POLICY_BASE_FOOD_PRODUCTION => 40,
                self::POLICY_BASE_WEALTH_PRODUCTION => 100,
            ],
            self::POLICY_DEFENSE => [
                self::POLICY_BONUS_INFILTRATE_SPEED => 0,
                self::POLICY_BONUS_INFILTRATE_DEFENSE => 0,
                self::POLICY_BONUS_INFILTRATE_STABILITY => 0,
                self::POLICY_BONUS_REINFORCE_SPEED => 25,
                self::POLICY_BONUS_REINFORCE_DEFENSE => 1,
                self::POLICY_BONUS_REINFORCE_STABILITY => 1,
                self::POLICY_BONUS_RAID_SPEED => 0,
                self::POLICY_BONUS_RAID_DEFENSE => 0,
                self::POLICY_BONUS_RAID_STABILITY => 0,
                self::POLICY_BONUS_CARAVAN_SPEED => 0,
                self::POLICY_BONUS_PATROL_RESPAWN => 0,
                self::POLICY_BONUS_PATROL_TIER => 0,
                self::POLICY_BONUS_TRAINING_SPEED => 0,
                self::POLICY_BONUS_TRANSFER_COST_REDUCTION => 0,
                self::POLICY_BONUS_HOME_PRODUCTION_BOOST => 0,
                self::POLICY_BONUS_SCOUTING => 1,
                self::POLICY_BONUS_STEALTH => 0,
                self::POLICY_BONUS_LOOT_CAPACITY => 0,
                self::POLICY_BONUS_PVP_VILLAGE_POINT => 0,
                self::POLICY_BONUS_CONSTRUCTION_SPEED => 0,
                self::POLICY_BONUS_RESEARCH_SPEED => 0,
                self::POLICY_BONUS_BASE_STABILITY => 0,
                self::POLICY_BONUS_MAX_STABILITY => 0,
                self::POLICY_BONUS_PVE_INCOME => 0,
                self::POLICY_BONUS_UPKEEP_REDUCTION => 0,
                self::POLICY_RESTRICTION_WAR_ENABLED => true,
                self::POLICY_RESTRICTION_ALLIANCE_ENABLED => true,
                self::POLICY_BASE_MATERIALS_PRODUCTION => 100,
                self::POLICY_BASE_FOOD_PRODUCTION => 70,
                self::POLICY_BASE_WEALTH_PRODUCTION => 40,
            ],
            self::POLICY_WAR => [
                self::POLICY_BONUS_INFILTRATE_SPEED => 0,
                self::POLICY_BONUS_INFILTRATE_DEFENSE => 0,
                self::POLICY_BONUS_INFILTRATE_STABILITY => 0,
                self::POLICY_BONUS_REINFORCE_SPEED => 0,
                self::POLICY_BONUS_REINFORCE_DEFENSE => 0,
                self::POLICY_BONUS_REINFORCE_STABILITY => 0,
                self::POLICY_BONUS_RAID_SPEED => 25,
                self::POLICY_BONUS_RAID_DEFENSE => 1,
                self::POLICY_BONUS_RAID_STABILITY => 1,
                self::POLICY_BONUS_CARAVAN_SPEED => 0,
                self::POLICY_BONUS_PATROL_RESPAWN => 25,
                self::POLICY_BONUS_PATROL_TIER => 0,
                self::POLICY_BONUS_TRAINING_SPEED => 0,
                self::POLICY_BONUS_TRANSFER_COST_REDUCTION => 0,
                self::POLICY_BONUS_HOME_PRODUCTION_BOOST => 0,
                self::POLICY_BONUS_SCOUTING => 0,
                self::POLICY_BONUS_STEALTH => 0,
                self::POLICY_BONUS_LOOT_CAPACITY => 0,
                self::POLICY_BONUS_PVP_VILLAGE_POINT => 1,
                self::POLICY_BONUS_CONSTRUCTION_SPEED => 0,
                self::POLICY_BONUS_RESEARCH_SPEED => 0,
                self::POLICY_BONUS_BASE_STABILITY => 0,
                self::POLICY_BONUS_MAX_STABILITY => 0,
                self::POLICY_BONUS_PVE_INCOME => 0,
                self::POLICY_BONUS_UPKEEP_REDUCTION => 0,
                self::POLICY_RESTRICTION_WAR_ENABLED => true,
                self::POLICY_RESTRICTION_ALLIANCE_ENABLED => false,
                self::POLICY_BASE_MATERIALS_PRODUCTION => 70,
                self::POLICY_BASE_FOOD_PRODUCTION => 70,
                self::POLICY_BASE_WEALTH_PRODUCTION => 70,
            ],
            self::POLICY_PROSPERITY => [
                self::POLICY_BONUS_INFILTRATE_SPEED => 0,
                self::POLICY_BONUS_INFILTRATE_DEFENSE => 0,
                self::POLICY_BONUS_INFILTRATE_STABILITY => 0,
                self::POLICY_BONUS_REINFORCE_SPEED => 0,
                self::POLICY_BONUS_REINFORCE_DEFENSE => 0,
                self::POLICY_BONUS_REINFORCE_STABILITY => 0,
                self::POLICY_BONUS_RAID_SPEED => 0,
                self::POLICY_BONUS_RAID_DEFENSE => 0,
                self::POLICY_BONUS_RAID_STABILITY => 0,
                self::POLICY_BONUS_CARAVAN_SPEED => 0,
                self::POLICY_BONUS_PATROL_RESPAWN => 0,
                self::POLICY_BONUS_PATROL_TIER => 0,
                self::POLICY_BONUS_TRAINING_SPEED => 0,
                self::POLICY_BONUS_TRANSFER_COST_REDUCTION => 0,
                self::POLICY_BONUS_HOME_PRODUCTION_BOOST => 0,
                self::POLICY_BONUS_SCOUTING => 0,
                self::POLICY_BONUS_STEALTH => 0,
                self::POLICY_BONUS_LOOT_CAPACITY => 0,
                self::POLICY_BONUS_PVP_VILLAGE_POINT => 0,
                self::POLICY_BONUS_CONSTRUCTION_SPEED => 0,
                self::POLICY_BONUS_RESEARCH_SPEED => 0,
                self::POLICY_BONUS_BASE_STABILITY => 25,
                self::POLICY_BONUS_MAX_STABILITY => 25,
                self::POLICY_BONUS_PVE_INCOME => 25,
                self::POLICY_BONUS_UPKEEP_REDUCTION => 25,
                self::POLICY_RESTRICTION_WAR_ENABLED => true,
                self::POLICY_RESTRICTION_ALLIANCE_ENABLED => true,
                self::POLICY_BASE_MATERIALS_PRODUCTION => 40,
                self::POLICY_BASE_FOOD_PRODUCTION => 70,
                self::POLICY_BASE_WEALTH_PRODUCTION => 100,
            ],
        ];
    }

    public function __construct(int $policy_id) {
        self::initializePolicyEffects();
        $this->setPolicyEffects($policy_id);
    }

    public function setPolicyEffects($policy_id)
    {
        $this->infiltrate_speed = self::$POLICY_EFFECTS[$policy_id][self::POLICY_BONUS_INFILTRATE_SPEED];
        $this->infiltrate_defense = self::$POLICY_EFFECTS[$policy_id][self::POLICY_BONUS_INFILTRATE_DEFENSE];
        $this->infiltrate_stability = self::$POLICY_EFFECTS[$policy_id][self::POLICY_BONUS_INFILTRATE_STABILITY];
        $this->reinforce_speed = self::$POLICY_EFFECTS[$policy_id][self::POLICY_BONUS_REINFORCE_SPEED];
        $this->reinforce_defense = self::$POLICY_EFFECTS[$policy_id][self::POLICY_BONUS_REINFORCE_DEFENSE];
        $this->reinforce_stability = self::$POLICY_EFFECTS[$policy_id][self::POLICY_BONUS_REINFORCE_STABILITY];
        $this->raid_speed = self::$POLICY_EFFECTS[$policy_id][self::POLICY_BONUS_RAID_SPEED];
        $this->raid_defense = self::$POLICY_EFFECTS[$policy_id][self::POLICY_BONUS_RAID_DEFENSE];
        $this->raid_stability = self::$POLICY_EFFECTS[$policy_id][self::POLICY_BONUS_RAID_STABILITY];
        $this->caravan_speed = self::$POLICY_EFFECTS[$policy_id][self::POLICY_BONUS_CARAVAN_SPEED];
        $this->patrol_respawn = self::$POLICY_EFFECTS[$policy_id][self::POLICY_BONUS_PATROL_RESPAWN];
        $this->patrol_tier = self::$POLICY_EFFECTS[$policy_id][self::POLICY_BONUS_PATROL_TIER];
        $this->training_speed = self::$POLICY_EFFECTS[$policy_id][self::POLICY_BONUS_TRAINING_SPEED];
        $this->transfer_cost_reduction = self::$POLICY_EFFECTS[$policy_id][self::POLICY_BONUS_TRANSFER_COST_REDUCTION];
        $this->home_production_boost = self::$POLICY_EFFECTS[$policy_id][self::POLICY_BONUS_HOME_PRODUCTION_BOOST];
        $this->scouting = self::$POLICY_EFFECTS[$policy_id][self::POLICY_BONUS_SCOUTING];
        $this->stealth = self::$POLICY_EFFECTS[$policy_id][self::POLICY_BONUS_STEALTH];
        $this->loot_capacity = self::$POLICY_EFFECTS[$policy_id][self::POLICY_BONUS_LOOT_CAPACITY];
        $this->pvp_village_point = self::$POLICY_EFFECTS[$policy_id][self::POLICY_BONUS_PVP_VILLAGE_POINT];
        $this->construction_speed = self::$POLICY_EFFECTS[$policy_id][self::POLICY_BONUS_CONSTRUCTION_SPEED];
        $this->research_speed = self::$POLICY_EFFECTS[$policy_id][self::POLICY_BONUS_RESEARCH_SPEED];
        $this->base_stability = self::$POLICY_EFFECTS[$policy_id][self::POLICY_BONUS_BASE_STABILITY];
        $this->max_stability = self::$POLICY_EFFECTS[$policy_id][self::POLICY_BONUS_MAX_STABILITY];
        $this->pve_income = self::$POLICY_EFFECTS[$policy_id][self::POLICY_BONUS_PVE_INCOME];
        $this->war_enabled = self::$POLICY_EFFECTS[$policy_id][self::POLICY_RESTRICTION_WAR_ENABLED];
        $this->alliance_enabled = self::$POLICY_EFFECTS[$policy_id][self::POLICY_RESTRICTION_ALLIANCE_ENABLED];
        $this->materials_production = self::$POLICY_EFFECTS[$policy_id][self::POLICY_BASE_MATERIALS_PRODUCTION];
        $this->food_production = self::$POLICY_EFFECTS[$policy_id][self::POLICY_BASE_FOOD_PRODUCTION];
        $this->wealth_production = self::$POLICY_EFFECTS[$policy_id][self::POLICY_BASE_WEALTH_PRODUCTION];
    }
}