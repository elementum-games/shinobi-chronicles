<?php

class BasicRamenDto {
    public function __construct(
        public string $key,
        public int $cost,
        public float $health_amount,
        public string $label,
        public string $image,
    ) {}
}