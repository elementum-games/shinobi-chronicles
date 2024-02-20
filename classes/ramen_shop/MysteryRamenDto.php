<?php

class MysteryRamenDto {
    public function __construct(
        public string $cost,
        public string $label,
        public int $duration,
        public string $image,
        public array $effects,
        public bool $mystery_ramen_unlocked,
    ) {}
}