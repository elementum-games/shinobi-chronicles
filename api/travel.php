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
    include __DIR__ . '/../pages/travel.php';

    $request = $system->clean($_POST['request']);
    switch($request) {

        case 'LoadScoutData':
            $response = LoadScoutData($system, $player);
            break;

        case 'LoadMapData':
            $response = LoadMapData($system, $player);
            break;

        case 'MovePlayer':
            $direction = $system->clean($_POST['direction']);
            $response = MovePlayer($system, $player, $direction);
            break;

        case 'EnterPortal':
            $portal_id = $system->clean($_POST['portal_id']);
            $response = EnterPortal($system, $player, $portal_id);
            break;

        default:
            API::exitWithError("Invalid request!");
    }

    API::exitWithData(
        data: [
            'request' => $request,
            'response_data' => $response->response_data
        ],
        errors: $response->errors,
        debug_messages: $system->debug_messages,
    );
} catch (Throwable $e) {
    API::exitWithError($e->getMessage());
}

