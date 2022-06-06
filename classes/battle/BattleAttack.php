<?php

/*
 * Represents an attack taking place in a fight
 */
class BattleAttack {
    public string $attacker_id;
    public Jutsu $jutsu;
    public float $starting_raw_damage;

    public AttackTarget $target;

    public BattleFieldTile $first_tile;

    // A linked list of attack path segments
    public ?AttackPathSegment $root_path_segment;

    /** @var BattleAttackHit[] */
    public array $hits = [];

    public function __construct(
        string $attacker_id, AttackTarget $target, Jutsu $jutsu, float $starting_raw_damage
    ) {
        $this->attacker_id = $attacker_id;
        $this->target = $target;
        $this->jutsu = $jutsu;
        $this->starting_raw_damage = $starting_raw_damage;
    }
}


class AttackPathSegment {
    public BattleFieldTile $tile;
    public float $raw_damage;

    public ?AttackPathSegment $next_segment = null;

    public function __construct(BattleFieldTile $tile, float $raw_damage) {
        $this->tile = $tile;
        $this->raw_damage = $raw_damage;
    }
}
