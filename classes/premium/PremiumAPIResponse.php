<?php

require_once __DIR__ . '/../APIResponse.php';

class PremiumAPIResponse extends APIResponse {
    public array $response;

    public function __construct(array $response = [], array $errors = []) {
        $this->response = $response;
        $this->errors = $errors;
    }
}