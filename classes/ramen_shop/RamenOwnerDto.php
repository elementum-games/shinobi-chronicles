<?php

class RamenOwnerDto {
    public function __construct(
        public string $name,
        public string $image,
        public string $background,
        public string $description,
        public string $dialogue,
    ) {}
}