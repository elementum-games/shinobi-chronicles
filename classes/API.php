<?php

require __DIR__ . '/APIResponse.php';

class API {

    public static function exitWithError(string $message, array $debug_messages = []): void {
        echo json_encode([
            'errors' => $message,
            'debug' => $debug_messages
        ]);
        exit;
    }

    public static function exitWithData(array $data, array $errors, array $debug_messages): void {
        echo json_encode([
            'data' => $data,
            'debug' => $debug_messages,
            'errors' => $errors
        ]);
        exit;
    }
}