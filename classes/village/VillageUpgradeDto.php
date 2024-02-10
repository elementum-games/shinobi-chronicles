<?php

class VillageUpgradeDto {
    public function __construct(
        public ?int $id = null,
        public string $key,
        public int $village_id,
        public string $status = VillageUpgradeConfig::UPGRADE_STATUS_LOCKED,
        public ?int $research_progress = null,
        public ?int $research_progress_required = null,
        // display-only properties
        public string $name = "",
        public string $description = "",
        public ?int $materials_research_cost = null,
        public ?int $food_research_cost = null,
        public ?int $wealth_research_cost = null,
        public ?int $research_time = null,
        public ?int $materials_upkeep = null,
        public ?int $food_upkeep = null,
        public ?int $wealth_upkeep = null,
        public ?int $requirements_met = null,
    ) {}
}