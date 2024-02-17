<?php

class VillageBuilding {
    public function __construct(
        public int $id,
        public string $key,
        public int $village_id,
        public int $tier = 0,
        public int $health = 0,
        public int $max_health = 0,
        public int $defense = 0,
        public string $status = VillageBuildingConfig::BUILDING_STATUS_DEFAULT,
        public ?int $construction_progress = null,
        public ?int $construction_progress_required = null,
        public ?int $construction_progress_last_updated = null,
        public bool $construction_boosted = false,
        private VillageBuildingConfigData $config_data,
    ) {}
    public function getName(): string {
        return $this->config_data->getName();
    }
    public function getDescription(): string {
        return $this->config_data->getDescription();
    }
    public function getPhrase(): string {
        return $this->config_data->getPhrase();
    }
    public function getBackgroundImage(): string {
        return $this->config_data->getBackgroundImage();
    }
    public function getMaxHealth(?int $tier): int {
        return $tier ? $this->config_data->getMaxHealth($tier) : $this->config_data->getMaxHealth($this->tier);
    }
    public function getConstructionCostMaterials(?int $tier): int {
        return $tier ? $this->config_data->getConstructionCostMaterials($tier) : $this->config_data->getConstructionCostMaterials($this->tier + 1);
    }
    public function getConstructionCostFood(?int $tier): int {
        return $tier ? $this->config_data->getConstructionCostFood($tier) : $this->config_data->getConstructionCostFood($this->tier + 1);
    }
    public function getConstructionCostWealth(?int $tier): int {
        return $tier ? $this->config_data->getConstructionCostWealth($tier) : $this->config_data->getConstructionCostWealth($this->tier + 1);
    }
    public function getConstructionTime(?int $tier): int {
        return $tier ? $this->config_data->getConstructionTime($tier) : $this->config_data->getConstructionTime($this->tier + 1);
    }
    public function getUpgradeSets(): array {
        return $this->config_data->getUpgradeSets();
    }
}