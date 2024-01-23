<?php

class VillageBuildingDto {
    public function __construct(
        public int $id,
        public int $building_id,
        public int $village_id,
        public int $tier = 0,
        public int $health = 0,
        public ?int $build_start_time = null,
        public ?int $build_end_time = null
    ) {}
}