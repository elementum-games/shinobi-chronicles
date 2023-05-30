<?php

class BattleAttackHit {
    // Do we need this?
    // public float $final_damage;

    public function __construct(
        public Fighter $attacker,
        public Fighter $target,

        public float $raw_damage,
        public int $time_occurred,
    ) {}
}