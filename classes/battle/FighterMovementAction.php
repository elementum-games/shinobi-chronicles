<?php

require_once __DIR__ . '/FighterAction.php';

class FighterMovementAction extends FighterAction {
    public string $type;

    public function __construct() {
        // This is for DB export
        $this->type = FighterAction::TYPE_MOVEMENT;
    }
}