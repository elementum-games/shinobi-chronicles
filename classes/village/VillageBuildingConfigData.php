<?php

require_once __DIR__ . "/../war/WarManager.php";

class VillageBuildingConfigData {
        public function __construct(
        private string $key,
        private string $name,
        private string $description,
        private string $phrase,
        private string $background_image,
        private array $max_healths,
        private array $construction_costs = [],
        private array $construction_times = [],
        private array $upgrade_sets = [],
    ) {}
    public function getKey(): string {
        return $this->key;
    }
    public function getName(): string {
        return $this->name;
    }
    public function getDescription(): string {
        return $this->description;
    }
    public function getPhrase(): string {
        return $this->phrase;
    }
    public function getBackgroundImage(): string {
        return $this->background_image;
    }
    public function getMaxHealth($tier): int {
        return $this->max_healths[$tier] ?? 0;
    }
    public function getConstructionCostMaterials($tier): int {
        return $this->construction_costs[$tier][WarManager::RESOURCE_MATERIALS] ?? 0;
    }
    public function getConstructionCostFood($tier): int {
        return $this->construction_costs[$tier][WarManager::RESOURCE_FOOD] ?? 0;
    }
    public function getConstructionCostWealth($tier): int {
        return $this->construction_costs[$tier][WarManager::RESOURCE_WEALTH] ?? 0;
    }
    public function getConstructionTime($tier): int {
        return $this->construction_times[$tier] ?? 0;
    }
    public function getUpgradeSets(): array {
        return $this->upgrade_sets;
    }
}