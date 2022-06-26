<?php

use JetBrains\PhpStorm\NoReturn;

require __DIR__ . '/APIResponse.php';

class API {
    #[NoReturn]
    public static function exitWithError($message) {
        echo json_encode([
            'errors' => $message
        ]);
        exit;
    }

    #[NoReturn]
    public static function exitWithData(array $data, array $errors, array $debug_messages) {
        echo json_encode([
            'data' => $data,
            'debug' => $debug_messages,
            'errors' => $errors
        ]);
        exit;
    }
}