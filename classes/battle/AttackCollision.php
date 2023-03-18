<?php

use JetBrains\PhpStorm\ArrayShape;

class AttackCollision {
    public function __construct(
        public string $id,
        public BattleAttackV2 $attack1,
        public BattleAttackV2 $attack2,
        public int $attack1_collision_point,
        public int $attack2_collision_point,
        public AttackPathSegment $attack1_segment,
        public AttackPathSegment $attack2_segment,
        public int $time_occurred
    ) {}

    public function toArray(): array {
        return [
            'id' => $this->id,
            'attack1_id' => $this->attack1->id,
            'attack2_id' => $this->attack2->id,
            'attack1_collision_point' => $this->attack1_collision_point,
            'attack2_collision_point' => $this->attack2_collision_point,
            'attack1_segment' => $this->attack1_segment,
            'attack2_segment' => $this->attack2_segment,
            'time_occurred' => $this->time_occurred,
        ];
    }
}