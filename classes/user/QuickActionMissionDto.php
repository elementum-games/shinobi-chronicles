<?php

class QuickActionMissionDto {
    public function __construct(
        public int $mission_id,
        public string $name,
    ) {}
}