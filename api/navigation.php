<?php

# Begin standard auth
require_once __DIR__ . "/../classes.php";

$system = API::init();

try {
    $player = Auth::getUserFromSession($system);
    $player->loadData(User::UPDATE_NOTHING);
} catch (Exception $e) {
    API::exitWithError($e->getMessage(), system: $system);
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
                'userMenu' => NavigationAPIPresenter::menuLinksResponse($NavigationManager->getUserMenu()),
                'activityMenu' => NavigationAPIPresenter::menuLinksResponse($NavigationManager->getActivityMenu()),
                'villageMenu' => NavigationAPIPresenter::menuLinksResponse($NavigationManager->getVillageMenu()),
                'staffMenu' => NavigationAPIPresenter::menuLinksResponse($NavigationManager->getStaffMenu()),
            ];
            break;
        case "getHeaderMenu":
            $NavigationResponse->response_data = [
                'headerMenu' => NavigationAPIPresenter::menuLinksResponse(
                    NavigationAPIManager::getHeaderMenu($system)
                ),
            ];
            break;
        default:
            API::exitWithError("Invalid request!", system: $system);
    }

    API::exitWithData(
        data: $NavigationResponse->response_data,
        errors: $NavigationResponse->errors,
        debug_messages: $system->debug_messages,
        system: $system,
    );
} catch (Throwable $e) {
    API::exitWithError($e->getMessage(), system: $system);
}