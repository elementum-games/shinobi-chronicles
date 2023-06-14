<?php

class ObjectiveLocation {
    public function __construct(
        public string $name,
        public int $map_id,
        public int $x,
        public int $y,
        public string $image,
    ) {}
}