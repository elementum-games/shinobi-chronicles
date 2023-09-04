<?php

class RegionObjective {
    public function __construct(
        public ?int $id = null,
        public string $name,
        public int $map_id,
        public int $x,
        public int $y,
        public string $image,
        public string $action_url = "",
        public string $action_message = "",
        public ?int $objective_health = null,
        public ?int $objective_max_health = null,
        public ?string $objective_type = null,
    ) {}
}