<?php

# Begin standard auth
require_once __DIR__ . "/../classes.php";

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
    $TravelManager = new TravelManager($system, $player);

    switch($request) {
        case 'LoadTravelData':
            $TravelAPIResponse->response = [
                'mapData' => TravelApiPresenter::mapDataResponse(player: $player, travelManager: $TravelManager),
                'nearbyPlayers' => TravelApiPresenter::nearbyPlayersResponse(travelManager: $TravelManager),
            ];
            break;

        case 'MovePlayer':
            $direction = $system->clean($_POST['direction']);

            $success = $TravelManager->movePlayer($direction);
            $TravelAPIResponse->response = TravelApiPresenter::travelActionResponse($success, $player, $TravelManager);
            break;

        case 'EnterPortal':
            $portal_id = $system->clean($_POST['portal_id']);

            $success = $TravelManager->enterPortal($portal_id);
            $TravelAPIResponse->response = TravelApiPresenter::travelActionResponse($success, $player, $TravelManager);
            break;

        case 'UpdateFilter':
            $filter = $system->clean($_POST['filter']);
            $filter_value = $system->clean($_POST['filter_value']);

            $success = $TravelManager->updateFilter($filter, $filter_value);
            $TravelAPIResponse->response = TravelApiPresenter::travelActionResponse($success, $player, $TravelManager);

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

