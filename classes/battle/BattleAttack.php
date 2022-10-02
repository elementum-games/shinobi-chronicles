<?php

/*
 * Represents an attack taking place in a fight
 */
class BattleAttack {
    const MAX_PATH_SEGMENTS = 1000;

    public string $id;
    public string $attacker_id;
    public Jutsu $jutsu;
    public float $starting_raw_damage;

    public AttackTarget $target;

    public BattleFieldTile $first_tile;

    // A linked list of attack path segments
    public ?AttackPathSegment $root_path_segment = null;

    /** @var AttackPathSegment[] */
    public array $path_segments = [];

    // Attack lifecycle status
    public bool $is_path_setup = false;
    public bool $are_collisions_applied = false;
    public bool $are_hits_calculated = false;

    /** @var BattleAttackHit[] */
    public array $hits = [];

    public function __construct(
        string $attacker_id, AttackTarget $target, Jutsu $jutsu, int $turn, float $starting_raw_damage
    ) {
        $this->id = implode(':', [$turn, $attacker_id, $jutsu->combat_id]);
        $this->attacker_id = $attacker_id;
        $this->target = $target;
        $this->jutsu = $jutsu;
        $this->starting_raw_damage = $starting_raw_damage;
    }

    public function addPathSegment(BattleFieldTile $tile, float $raw_damage, int $time_arrived): void {
        $index = count($this->path_segments);
        $this->path_segments[$index] = new AttackPathSegment(
            index: $index,
            tile: $tile,
            raw_damage: $raw_damage,
            time_arrived: $time_arrived
        );
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function isFacingRight(): bool {
        if($this->target instanceof AttackDirectionTarget) {
            return $this->target->isDirectionRight();
        }

        throw new Exception("Unsupported target type for direction check!");
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function isFacingLeft(): bool {
        if($this->target instanceof AttackDirectionTarget) {
            return $this->target->isDirectionLeft();
        }

        throw new Exception("Unsupported target type for direction check!");
    }
}

class AttackPathSegment {
    public int $index;
    public BattleFieldTile $tile;
    public float $raw_damage;
    public int $time_arrived;

    /**
     * Direct usage of this constructor is not recommended, use BattleAttack#addPathSegment
     *
     * @param int             $index
     * @param BattleFieldTile $tile
     * @param float           $raw_damage
     * @param int             $time_arrived
     */
    public function __construct(int $index, BattleFieldTile $tile, float $raw_damage, int $time_arrived) {
        $this->index = $index;
        $this->tile = $tile;
        $this->raw_damage = $raw_damage;
        $this->time_arrived = $time_arrived;
    }

    public static function fromArray(array $segment): AttackPathSegment {
        return new AttackPathSegment(
            index: $segment['index'],
            tile: new BattleFieldTile($segment['tile']['index'], $segment['tile']['fighter_ids']),
            raw_damage: $segment['raw_damage'],
            time_arrived: $segment['time_arrived'],
        );
    }
}
