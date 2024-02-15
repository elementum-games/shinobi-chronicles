<?php

class VillageUpgradeConfigData {
    public function __construct(
        private string $key,
        private string $name,
        private string $description,
        private int $materials_research_cost,
        private int $food_research_cost,
        private int $wealth_research_cost,
        private int $research_time,
        private int $materials_upkeep,
        private int $food_upkeep,
        private int $wealth_upkeep,
        private array $research_requirements = [],
        private array $effects = [],
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
    public function getResearchTime(): int {
        return $this->research_time;
    }
    public function getResearchCostMaterials(): int {
        return $this->materials_research_cost;
    }
    public function getResearchCostFood(): int {
        return $this->food_research_cost;
    }
    public function getResearchCostWealth(): int {
        return $this->wealth_research_cost;
    }
    public function getUpkeepMaterials(): int {
        return $this->materials_upkeep;
    }
    public function getUpkeepFood(): int {
        return $this->food_upkeep;
    }
    public function getUpkeepWealth(): int {
        return $this->wealth_upkeep;
    }
    public function getResearchRequirements(): array {
        return $this->research_requirements;
    }
    public function getEffects(): array {
        return $this->effects;
    }
}