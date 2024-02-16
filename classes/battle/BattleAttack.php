<?php

/*
 * Represents an attack taking place in a fight
 */

class BattleAttack {
    public Jutsu $jutsu;
    public float $raw_damage = 0;
    public float $piercing_percent = 0;
    public float $substitution_percent = 0;
    public float $counter_percent = 0;
    public float $reflect_percent = 0;
    public float $reflect_duration = 0;
    public float $immolate_percent = 0;
    public float $immolate_raw_damage = 0;
    public float $recoil_percent = 0;
    public float $recoil_raw_damage = 0;
    public float $countered_percent = 0; // opponent's counter
    public float $countered_raw_damage = 0; // damage dealt from opponent
    public string $countered_jutsu_type; // jutsu type of opponent's counter
    public float $reflected_percent = 0; // opponent's reflect
    public float $reflected_raw_damage = 0; // damage dealt from opponent
    public string $reflected_jutsu_type; // jutsu type of opponent's reflect
    /** @var Effect[] */
    public array $effects = [];
}