<?php

use JetBrains\PhpStorm\NoReturn;

require __DIR__ . '/APIResponse.php';

class API {
    public static function init(): System {
        $system = new System();
        $system->startTransaction();
        $system->is_api_request = true;
        return $system;
    }

    #[NoReturn]
    public static function exitWithError(string $message, System $system, array $debug_messages = []): void {
        $system->rollbackTransaction();
        error_log($message);
        echo json_encode([
            'errors' => $message,
            'debug' => $debug_messages
        ]);
        exit;
    }

    #[NoReturn]
    public static function exitWithException(Exception $exception, System $system, array $debug_messages = []): void {
        if(
            $exception instanceof LoggedOutException
            || $exception instanceof InvalidMovementException
        ) {
            $system->rollbackTransaction();
            echo json_encode([
                'errors' => $exception->getMessage(),
                'debug' => $debug_messages
            ]);
            exit;
        }

        API::exitWithError(
            message: $exception->getMessage(),
            system: $system,
            debug_messages: $debug_messages
        );
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
