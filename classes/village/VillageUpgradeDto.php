<?php

class VillageUpgradeDto {
    public function __construct(
        public ?int $id,
        public string $key,
        public int $village_id,
        public ?string $status = VillageUpgradeConfig::UPGRADE_STATUS_LOCKED,
        public ?int $research_progress = null,
        public ?int $research_progress_required = null,
        public ?int $research_progress_last_updated = null,
        public bool $research_boosted = false,
        public string $research_time_remaining = '',
        public string $name,
        public string $description,
        public int $materials_research_cost,
        public int $food_research_cost,
        public int $wealth_research_cost,
        public int $research_time,
        public int $materials_upkeep,
        public int $food_upkeep,
        public int $wealth_upkeep,
        public array $research_requirements = [],
        public array $effects = [],
        public bool $requirements_met,
    ) {}
}