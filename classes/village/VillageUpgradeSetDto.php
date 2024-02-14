<?php
    
class VillageUpgradeSetDto {
    public function __construct(
        public string $key,
        public string $name,
        public string $description,
        public array $upgrades = [],
    ) {}
}