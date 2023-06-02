<?php

# Begin standard auth
require_once __DIR__ . "/../classes.php";

$system = new System();
$system->is_api_request = true;

try {
    $player = Auth::getUserFromSession($system);
    $player->loadData(User::UPDATE_NOTHING);
} catch (Exception $e) {
    API::exitWithError($e->getMessage());
}
# End standard auth

try {
    // api requires a request
    if (isset($_POST['request'])) {
        $request = filter_input(INPUT_POST, 'request', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    } else {
        throw new Exception('No request was made!');
    }

    $TopbarResponse = new TopbarAPIResponse();
    $TopbarManager = new TopbarManager($system, $player);

    switch ($request) {
        case "getSidebarLinks":
            $TopbarResponse->response_data = [
                'notificationData' => TopbarAPIPresenter::notificationDataResponse(topbarManager: $TopbarManager),
            ];
            break;
        default:
            API::exitWithError("Invalid request!");
    }

    API::exitWithData(
        data: $TopbarResponse->response_data,
        errors: $TopbarResponse->errors,
        debug_messages: $system->debug_messages,
    );
} catch (Throwable $e) {
    API::exitWithError($e->getMessage());
}