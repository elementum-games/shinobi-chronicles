<?php

class VillageUpgrade {
    public function __construct(
        public int $id,
        public string $key,
        public int $village_id,
        public string $status = VillageUpgradeConfig::UPGRADE_STATUS_LOCKED,
        public ?int $research_progress = null,
        public ?int $research_progress_required = null,
        public ?int $research_progress_last_updated = null,
        private VillageUpgradeConfigData $config_data,
    ) {}
    public function getName(): string {
        return $this->config_data->getName();
    }
    public function getDescription(): string {
        return $this->config_data->getDescription();
    }
    public function getResearchTime(): int {
        return $this->config_data->getResearchTime();
    }
    public function getResearchCostMaterials(): int {
        return $this->config_data->getResearchCostMaterials();
    }
    public function getResearchCostFood(): int {
        return $this->config_data->getResearchCostFood();
    }
    public function getResearchCostWealth(): int {
        return $this->config_data->getResearchCostWealth();
    }
    public function getUpkeepMaterials(): int {
        return $this->config_data->getResearchCostMaterials();
    }
    public function getUpkeepFood(): int {
        return $this->config_data->getUpkeepFood();
    }
    public function getUpkeepWealth(): int {
        return $this->config_data->getUpkeepFood();
    }
    public function getResearchRequirements(): array {
        return $this->config_data->getResearchRequirements();
    }
    public function getEffects(): array {
        return $this->config_data->getEffects();
    }
}