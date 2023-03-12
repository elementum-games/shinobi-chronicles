<?php

# Begin standard auth
require "../classes/_autoload.php";

$system = new System();
$system->is_api_request = true;

try {
    $player = Auth::getUserFromSession($system);
    $player->loadData(User::UPDATE_NOTHING);
} catch(Exception $e) {
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

    $TravelAPIResponse = new TravelAPIResponse();
    $TravelAPIResponse->response['request'] = $request;
    $TravelManager = new TravelManager($system, $player);

    switch($request) {

        case 'LoadScoutData':
            $TravelAPIResponse->response['response'] = $TravelManager->fetchScoutData();
            break;

        case 'LoadMapData':
            $TravelAPIResponse->response['response'] = $TravelManager->fetchMapDataAPI();
            if (empty($TravelAPIResponse->response['response'])) {
                API::exitWithError("Failed to load map!");
            }
            break;

        case 'MovePlayer':
            $direction = $system->clean($_POST['direction']);
            $TravelAPIResponse->response['response'] = $TravelManager->movePlayer($direction);
            break;

        case 'EnterPortal':
            $portal_id = $system->clean($_POST['portal_id']);
            $TravelAPIResponse->response['response'] = $TravelManager->enterPortal($portal_id);
            break;

        case 'UpdateFilter':
            $filter = $system->clean($_POST['filter']);
            $filter_value = $system->clean($_POST['filter_value']);
            $TravelAPIResponse->response['response'] = $TravelManager->updateFilter($filter, $filter_value);
            break;

        default:
            API::exitWithError("Invalid request!");
    }

    API::exitWithData(
        data: $TravelAPIResponse->response,
        errors: $TravelAPIResponse->errors,
        debug_messages: $system->debug_messages,
    );
} catch (Throwable $e) {
    API::exitWithError($e->getMessage());
}

