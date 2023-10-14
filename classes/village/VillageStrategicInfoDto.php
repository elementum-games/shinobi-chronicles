<?php

class VillageStrategicInfoDto {
    public function __construct(
        public Village $village,
        public array $seats,
        public array $population,
        public array $regions,
        public array $supply_points,
        public array $allies,
        public array $enemies,
    ) {}
}