<?php

class RamenOwnerDto {
    public function __construct(
        public string $name,
        public string $image,
        public string $background,
        public string $shop_description,
        public string $dialogue,
        public string $shop_name,
    ) {}
}