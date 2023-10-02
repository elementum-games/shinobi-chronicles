<?php

class RegionObjective {
    public function __construct(
        public ?int $id = null,
        public string $name,
        public int $map_id,
        public int $x,
        public int $y,
        public string $image,
        public int $objective_health,
        public int $objective_max_health,
        public int $defense,
        public ?string $objective_type = null,
        public int $village_id,
        public int $resource_id,
    ) {}
}