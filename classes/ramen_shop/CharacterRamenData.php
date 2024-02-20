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

    public function checkBuffActive(string $effect_key): bool {
        if ($this->buff_duration + $this->purchase_time < time()) {
            return false;
        } else {
            return in_array($effect_key, $this->buff_effects);
        }
    }
}