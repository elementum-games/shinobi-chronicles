<?php

# Begin standard auth
require_once __DIR__ . "/../classes.php";

$system = API::init();

try {
    $player = Auth::getUserFromSession($system);
    $player->loadData(User::UPDATE_NOTHING);
} catch(Exception $e) {
    API::exitWithException($e, system: $system);
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
            $direction = $system->db->clean($_POST['direction']);

            $success = $TravelManager->movePlayer($direction);
            $TravelAPIResponse->response = TravelApiPresenter::travelActionResponse($success, $player, $TravelManager);
            break;

        case 'EnterPortal':
            $portal_id = $system->db->clean($_POST['portal_id']);

            $success = $TravelManager->enterPortal($portal_id);
            $TravelAPIResponse->response = TravelApiPresenter::travelActionResponse($success, $player, $TravelManager);
            break;

        case 'UpdateFilter':
            $filter = $system->db->clean($_POST['filter']);
            $filter_value = $system->db->clean($_POST['filter_value']);

            $success = $TravelManager->updateFilter($filter, $filter_value);
            $TravelAPIResponse->response = TravelApiPresenter::travelActionResponse($success, $player, $TravelManager);

            break;

        default:
            API::exitWithError(message: "Invalid request!", system: $system);
    }

    API::exitWithData(
        data: $TravelAPIResponse->response,
        errors: $TravelAPIResponse->errors,
        debug_messages: $system->debug_messages,
        system: $system,
    );
} catch (Throwable $e) {
    API::exitWithException($e, system: $system);
}

