<?php

class VillageBuildingDto {
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
        public string $construction_time_remaining = '',
        public string $name,
        public string $description,
        public string $phrase,
        public string $background_image,
        public int $materials_construction_cost,
        public int $food_construction_cost,
        public int $wealth_construction_cost,
        public int $construction_time,
        public array $upgrade_sets = [],
        public bool $requirements_met,
    ) {}
}