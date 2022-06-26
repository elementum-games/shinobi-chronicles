<?php

class BattleFieldTile {
    public int $index;
    public array $fighter_ids;

    public function __construct(int $index, array $fighter_ids) {
        $this->index = $index;
        $this->fighter_ids = $fighter_ids;
    }
}