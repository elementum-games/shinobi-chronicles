<?php

class BattleFieldTile {
    public array $fighter_ids;

    public function __construct(array $fighter_ids) {
        $this->fighter_ids = $fighter_ids;
    }
}