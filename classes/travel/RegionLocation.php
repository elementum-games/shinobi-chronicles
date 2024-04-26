<?php

require_once __DIR__ . '/../war/WarManager.php';

class RegionLocation {
    public function __construct(
        public int $region_location_id,
        public int $region_id,
        public int $health,
        public int $max_health,
        public string $type,
        public int $map_id,
        public int $x,
        public int $y,
        public string $name,
        public int $resource_id,
        public int $resource_count,
        public int $defense,
        public int $occupying_village_id,
        public int $stability,
        public int $rebellion_active,
        public string $background_image,
    ) {}
    
    public static function fromDb(array $data, Village $village): RegionLocation {
        switch ($data['type']) {
            case 'castle':
                $max_health = floor(
                    WarManager::BASE_CASTLE_HEALTH *
                    (1 + ($village->active_upgrade_effects[VillageUpgradeConfig::UPGRADE_EFFECT_CASTLE_HP] / 100))
                );
                break;
            case 'village':
                $max_health = floor(
                    WarManager::BASE_TOWN_HEALTH *
                    (1 + ($village->active_upgrade_effects[VillageUpgradeConfig::UPGRADE_EFFECT_TOWN_HP] / 100))
                );
                break;
            default:
                $max_health = 0;
                break;
        }

        return new RegionLocation(
            region_location_id: $data['region_location_id'], 
            region_id: $data['region_id'], 
            health: $data['health'],
            max_health: $max_health,
            type: $data['type'],
            map_id: $data['map_id'], 
            x: $data['x'], 
            y: $data['y'], 
            name: $data['name'], 
            resource_id: $data['resource_id'], 
            resource_count: $data['resource_count'],
            defense: $data['defense'], 
            occupying_village_id: $data['occupying_village_id'], 
            stability: $data['stability'],
            rebellion_active: $data['rebellion_active'],
            background_image: $data['background_image']
        );
    }

    public function rollForRebellion(): void {
        // if village, and stability is negative, and not original village, roll for rebellion
        if ($this->type == 'village' && $this->stability < 0
            && $this->occupying_village_id != WarManager::REGION_ORIGINAL_VILLAGE[$this->region_id]
        ) {
            // rebellion chance is proportional to stability, spread evenly over each hour
            $rebellion_chance = abs($this->stability) / (60 / WarManager::REGEN_INTERVAL_MINUTES);
            if (mt_rand(0, 100) < $rebellion_chance) {
                $this->rebellion_active = 1;
            }
        }
    }

    public function processRegen(): int {
        switch ($this->type) {
            case 'castle':
                // increase health, cap at max
                $regen = $this->getRegenAmount();

                $this->health = min($this->health + $regen, $this->max_health);
                break;
            case 'village':
                if ($this->stability >= 0 && $this->rebellion_active) {
                    $this->rebellion_active = 0;
                }
                if ($this->rebellion_active) {
                    $damage = (WarManager::BASE_REBELLION_DAMAGE_PER_MINUTE * WarManager::REGEN_INTERVAL_MINUTES) * max((1 + (-1 * $this->stability / 100)), 0);
                    $this->health = max($this->health - $damage, 0);
                    // if health reaches 0, change control
                    if ($this->health == 0) {
                        $this->occupying_village_id = WarManager::REGION_ORIGINAL_VILLAGE[$this->region_id];
                        $this->rebellion_active = 0;
                        $this->health = (WarManager::INITIAL_LOCATION_CAPTURE_HEALTH_PERCENT / 100) * WarManager::BASE_TOWN_HEALTH;
                        $this->defense = WarManager::INITIAL_LOCATION_CAPTURE_DEFENSE;
                        $this->stability = WarManager::INITIAL_LOCATION_CAPTURE_STABILITY;
                    }
                } else {
                    // increase health, cap at max
                    $regen = $this->getRegenAmount();
                    $this->health = min($this->health + $regen, WarManager::BASE_TOWN_HEALTH);
                }
                break;
            default;
                break;
        }
    }

    public function getRegenAmount() {
        if($this->rebellion_active) {
            return 0;
        }

        $regen = $this->type === 'castle'
            ? WarManager::BASE_CASTLE_REGEN_PER_MINUTE
            : WarManager::BASE_TOWN_REGEN_PER_MINUTE;
        $regen *= WarManager::REGEN_INTERVAL_MINUTES;

        $regen *= max((1 + ($this->stability / 100)), 0);

        return $regen;
    }
}