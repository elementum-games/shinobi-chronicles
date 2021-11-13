<?php

require_once __DIR__ . '/../System.php';
require_once __DIR__ . '/Battle.php';

class BattleField {
    private System $system;
    private Battle $battle;

    // fighter locations


    public function __construct(System $system, Battle &$battle) {
        $this->system = $system;
        $this->battle = &$battle;
    }

}