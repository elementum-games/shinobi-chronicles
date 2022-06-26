<?php

require_once __DIR__ . '/FighterAction.php';

class FighterAttackAction extends FighterAction {
    public string $type;

    public string $fighter_id;
    public int $jutsu_id;
    public int $jutsu_purchase_type;
    public ?int $weapon_id;

    public AttackTarget $target;

    public function __construct(
        string $fighter_id,
        int $jutsu_id,
        int $jutsu_purchase_type,
        ?int $weapon_id,
        AttackTarget $target,
    ) {
        // This is for DB export
        $this->type = FighterAction::TYPE_ATTACK;

        $this->fighter_id = $fighter_id;
        $this->jutsu_id = $jutsu_id;
        $this->jutsu_purchase_type = $jutsu_purchase_type;
        $this->weapon_id = $weapon_id;

        $this->target = $target;
    }
}