<?php

class NearbyPlayerDto {
    public function __construct(
        public int $user_id,
        public string $user_name,
        public int $target_x,
        public int $target_y,
        public int $target_map_id,
        public string $rank_name,
        public int $rank_num,
        public string $village_icon,
        public string $alignment,
        public bool $attack,
        public string $attack_id,
        public int $level,
        public int $battle_id,
        public string $direction,
        public bool $invulnerable = false,
        public int $distance = 0,
        public int $village_id,
        public int $loot_count = 0,
    ) {}
}