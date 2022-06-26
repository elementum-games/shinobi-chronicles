<?php

class AttackCollision {
    public string $id;
    public BattleAttack $attack1;
    public BattleAttack $attack2;
    public int $attack1_collision_point;
    public int $attack2_collision_point;
    
    public function __construct(
        string $id,
        BattleAttack $attack1,
        BattleAttack $attack2,
        int $attack1_collision_point,
        int $attack2_collision_point,
    ) {
        $this->id = $id;
        $this->attack1 = $attack1;
        $this->attack2 = $attack2;
        $this->attack1_collision_point = $attack1_collision_point;
        $this->attack2_collision_point = $attack2_collision_point;
    }
}