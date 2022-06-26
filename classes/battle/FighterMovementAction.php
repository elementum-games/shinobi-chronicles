<?php

require_once __DIR__ . '/FighterAction.php';

class FighterMovementAction extends FighterAction {
    public string $type;

    public string $fighter_id;
    public int $target_tile;

    public function __construct(string $fighter_id, int $target_tile) {
        // This is for DB export
        $this->type = FighterAction::TYPE_MOVEMENT;

        $this->fighter_id = $fighter_id;
        $this->target_tile = $target_tile;
    }
}