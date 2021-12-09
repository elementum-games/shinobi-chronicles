<?php

class BattleFieldTile {
    /** @var Fighter[] */
    public array $fighters;

    public function __construct(array $fighters) {
        $this->fighters = $fighters;
    }
}