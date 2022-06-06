<?php

class BattleAttackHit {
    public Fighter $attacker;
    public Fighter $target;

    public float $raw_damage;
    public float $final_damage;

    public function __construct(
        Fighter $attacker,
        Fighter $target,
        float $raw_damage,
    ) {
        $this->attacker = $attacker;
        $this->target = $target;
        $this->raw_damage = $raw_damage;
    }
}