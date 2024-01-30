<?php

class VillageBuildingDto {
    public function __construct(
        public int $id,
        public string $key,
        public int $village_id,
        public int $tier = 0,
        public int $health = 0,
        public string $status = VillageBuildingConfig::BUILDING_STATUS_DEFAULT,
        public ?int $construction_progress = null,
        public ?int $construction_progress_required = null
    ) {}
}