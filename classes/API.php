<?php

use JetBrains\PhpStorm\NoReturn;

require __DIR__ . '/APIResponse.php';

class API {

    #[NoReturn]
    public static function exitWithError(string $message, array $debug_messages = [], System $system): void {
        $system->rollbackTransaction();
        error_log($message);
        echo json_encode([
            'errors' => $message,
            'debug' => $debug_messages
        ]);
        exit;
    }

    #[NoReturn]
    public static function exitWithData(array $data, array $errors, array $debug_messages, System $system): void {
        $system->commitTransaction();
        echo json_encode([
            'data' => $data,
            'debug' => $debug_messages,
            'errors' => $errors
        ]);
        exit;
    }
}
