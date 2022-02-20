<?php

require_once __DIR__ . '/../APIResponse.php';

class BattlePageAPIResponse extends APIResponse {
    public array $battle_data = [];
    public string $battle_result = "";

    public function __construct(array $battle_data = [], string $battle_result = "", array $errors = []) {
        $this->battle_data = $battle_data;
        $this->battle_result = $battle_result;
        $this->errors = $errors;
    }
}