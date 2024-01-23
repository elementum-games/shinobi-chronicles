<?php

class VillageUpgradeDto {
    public function __construct(
        public int $id,
        public string $key,
        public int $village_id,
        public bool $is_active = false,
        public ?int $research_start_time = null,
        public ?int $research_end_time = null
    ) {}
}