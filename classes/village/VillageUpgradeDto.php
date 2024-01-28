<?php

class VillageUpgradeDto {
    public function __construct(
        public int $id,
        public string $key,
        public int $village_id,
        public string $status = VillageUpgradeManager::UPGRADE_STATUS_LOCKED,
        public ?int $research_progress = null,
        public ?int $research_progress_required = null
    ) {}
}