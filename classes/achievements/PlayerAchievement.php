<?php

require_once __DIR__ . '/Achievement.php';

class PlayerAchievement {
    public function __construct(
        public Achievement $achievement,
        public int $achieved_at,
    ) {}
}