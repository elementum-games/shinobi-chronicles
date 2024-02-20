<?php

class CharacterRamenData {
    public function __construct(
        public int $id,
        public int $user_id,
        public int $buff_duration,
        public int $purchase_time,
        public array $buff_effects,
        public bool $mystery_ramen_available,
        public array $mystery_ramen_effects,
        public int $purchase_count_since_last_mystery
    ) {}
}