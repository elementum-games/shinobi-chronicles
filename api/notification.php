<?php

# Begin standard auth
require_once __DIR__ . "/../classes.php";

$system = API::init(row_lock: false);

try {
    $player = Auth::getUserFromSession($system);
    $player->loadData(User::UPDATE_PREVIEW);
    //$player->updateData();
} catch (Exception $e) {
    API::exitWithException($e, system: $system);
}
# End standard auth

try {
    // api requires a request
    if (isset($_POST['request'])) {
        $request = filter_input(INPUT_POST, 'request', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    } else {
        throw new RuntimeException('No request was made!');
    }

    $NotificationResponse = new NotificationAPIResponse();
    $NotificationManager = new NotificationAPIManager($system, $player);

    switch ($request) {
        case "getUserNotifications":
            $NotificationResponse->response_data = [
                'userNotifications' => NotificationAPIPresenter::userNotificationResponse($NotificationManager),
            ];
            break;
        case "closeNotification":
            $NotificationResponse->response_data = [
                'success' => $NotificationManager->closeNotification($system->db->clean($_POST['notification_id'])),
            ];
            break;
        case "clearNotificationAlert":
            $NotificationResponse->response_data = [
                'success' => $NotificationManager->clearNotificationAlert($system->db->clean($_POST['notification_id'])),
            ];
            break;
        default:
            API::exitWithError("Invalid request!", system: $system);
    }

    API::exitWithData(
        data: $NotificationResponse->response_data,
        errors: $NotificationResponse->errors,
        debug_messages: $system->debug_messages,
        system: $system,
    );
} catch (Throwable $e) {
    API::exitWithException($e, system: $system);
}