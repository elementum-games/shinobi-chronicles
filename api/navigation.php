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

    $NavigationResponse = new NavigationAPIResponse();
    $NavigationManager = new NavigationAPIManager($system, $player);

    switch ($request) {
        case "getNavigationLinks":
            $NavigationResponse->response_data = [
                'userMenu' => NavigationAPIPresenter::userMenuResponse(navigationManager: $NavigationManager),
                'activityMenu' => NavigationAPIPresenter::activityMenuResponse(navigationManager: $NavigationManager),
                'villageMenu' => NavigationAPIPresenter::villageMenuResponse(navigationManager: $NavigationManager),
                'staffMenu' => NavigationAPIPresenter::staffMenuResponse(navigationManager: $NavigationManager),
            ];
            break;
        case "getHeaderMenu":
            $NavigationResponse->response_data = [
                'headerMenu' => NavigationAPIPresenter::headerMenuResponse(navigationManager: $NavigationManager),
            ];
            break;
        default:
            API::exitWithError("Invalid request!");
    }

    API::exitWithData(
        data: $NavigationResponse->response_data,
        errors: $NavigationResponse->errors,
        debug_messages: $system->debug_messages,
    );
} catch (Throwable $e) {
    API::exitWithError($e->getMessage());
}