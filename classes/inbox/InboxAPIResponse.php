<?php

require_once __DIR__ . '/APIResponse.php';

class InboxAPIResponse extends APIResponse {
    public array $response_data;

    public function __construct(array $response_data = [], array $errors = []) {
        $this->response_data = $response_data;
        $this->errors = $errors;
    }
}