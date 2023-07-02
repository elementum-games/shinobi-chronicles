<?php

class AchievementReward {
    const TYPE_MONEY = 'MONEY';
    const TYPE_VILLAGE_REP = 'VILLAGE_REP';
    const FREEMIUM_CREDITS = 'FREEMIUM_CREDITS';

    public function __construct(
        public string $type,
        public int $amount,
    ) {}
}