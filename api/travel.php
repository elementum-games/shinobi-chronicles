<?php

# Begin standard auth
require_once __DIR__ . "/../classes.php";

$system = API::init(row_lock: true);

try {
    $player = Auth::getUserFromSession($system);
    $player->loadData(User::UPDATE_NOTHING);
} catch(RuntimeException $e) {
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

    $TravelAPIResponse = new TravelAPIResponse();
    $TravelManager = new TravelManager($system, $player);

    // for optimization testing
    //$start_time = microtime(true) * 1000;

    switch($request) {
        case 'LoadTravelData':
            $TravelAPIResponse->response = [
                'mapData' => TravelApiPresenter::mapDataResponse(player: $player, travelManager: $TravelManager, system: $system),
                'nearbyPlayers' => TravelApiPresenter::nearbyPlayersResponse(travelManager: $TravelManager),
                'nearbyPatrols' => TravelApiPresenter::nearbyPatrolsResponse(travelManager: $TravelManager),
                'travel_message' => $TravelManager->travel_message,
            ];
            break;

        case 'MovePlayer':
            $direction = $system->db->clean($_POST['direction']);

            $success = $TravelManager->movePlayer($direction);
            $TravelAPIResponse->response = TravelApiPresenter::travelActionResponse($success, $player, $TravelManager, $system);
            break;

        case 'EnterPortal':
            $portal_id = $system->db->clean($_POST['portal_id']);

            $success = $TravelManager->enterPortal($portal_id);
            $TravelAPIResponse->response = TravelApiPresenter::travelActionResponse($success, $player, $TravelManager, $system);
            break;

        case 'UpdateFilter':
            $filter = $system->db->clean($_POST['filter']);
            $filter_value = $system->db->clean($_POST['filter_value']);

            $success = $TravelManager->updateFilter($filter, $filter_value);
            $TravelAPIResponse->response = TravelApiPresenter::travelActionResponse($success, $player, $TravelManager, $system);
            break;

        case 'AttackPlayer':
            $target_attack_id = $system->db->clean($_POST['target']);

            $success = $TravelManager->attackPlayer($target_attack_id);
            $TravelAPIResponse->response = TravelApiPresenter::attackPlayerResponse($success, $system);
            break;

        case 'BeginOperation':
            $operation_type = $system->db->clean($_POST['operation_type']);

            $success = $TravelManager->beginOperation($operation_type);
            $TravelAPIResponse->response = TravelApiPresenter::travelActionResponse($success, $player, $TravelManager, $system);
            break;

        case 'CancelOperation':
            $success = $TravelManager->cancelOperation();
            $TravelAPIResponse->response = TravelApiPresenter::travelActionResponse($success, $player, $TravelManager, $system);
            break;

        default:
            API::exitWithError(message: "Invalid request!", system: $system);
    }

    //$duration = microtime(true) * 1000 - $start_time;
    //$TravelAPIResponse->response['time'] = $duration;

    API::exitWithData(
        data: $TravelAPIResponse->response,
        errors: $TravelAPIResponse->errors,
        debug_messages: $system->debug_messages,
        system: $system,
    );
} catch (Throwable $e) {
    API::exitWithException($e, system: $system);
}

