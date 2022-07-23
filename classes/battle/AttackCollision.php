<?php

class AttackCollision {
    public function __construct(
        public string $id,
        public BattleAttack $attack1,
        public BattleAttack $attack2,
        public int $attack1_collision_point,
        public int $attack2_collision_point,
        public AttackPathSegment $attack1_segment,
        public AttackPathSegment $attack2_segment,
        public int $time_occurred
    ) {}
}