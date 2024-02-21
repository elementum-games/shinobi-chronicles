<?php

class SpecialRamenDto {
    public function __construct(
        public string $key,
        public int $cost,
        public string $label,
        public string $image,
        public string $description,
        public string $effect,
        public int $duration
    ) {}
}