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

    /**
     * Execute a function for each segment in the attack path.
     *
     * @param Closure $closure
     * @throws Exception
     */
    public function forEachSegment(Closure $closure) {
        $current_segment = $this->root_path_segment;
        $count = 0;

        while($current_segment != null) {
            if($count++ > self::MAX_PATH_SEGMENTS) {
                throw new Exception("forEachSegment: Max path segments reached!");
            }

            $closure($current_segment);
            $current_segment = $current_segment->next_segment;
        }
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
    public BattleFieldTile $tile;
    public float $raw_damage;
    public int $time_arrived;

    public ?AttackPathSegment $next_segment = null;

    public function __construct(BattleFieldTile $tile, float $raw_damage, int $time_arrived) {
        $this->tile = $tile;
        $this->raw_damage = $raw_damage;
        $this->time_arrived = $time_arrived;
    }
}
