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

try {
    // api requires a request
    if (isset($_POST['request'])) {
        $request = filter_input(INPUT_POST, 'request', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    } else {
        throw new RuntimeException('No request was made!');
    }

    $VillageAPIResponse = new VillageAPIResponse();

    switch($request) {
        /*
        case 'LoadVillageData':
            $VillageAPIResponse->response = [
                'policyData' => VillageApiPresenter::boostDataResponse($system, $player),
                'populationData' => VillageApiPresenter::populationDataResponse($system, $player),
                'seatData' => VillageApiPresenter::seatDataResponse($system, $player),
                'pointsData' => VillageApiPresenter::pointsDataResponse($system, $player),
                'diplomacyData' => VillageApiPresenter::diplomacyDataResponse($system, $player),
                'resourceData' => VillageApiPresenter::resourceDataResponse($system, $player, 7),
                'clanData' => VillageApiPresenter::clanDataResponse($system, $player),
            ];
            break;*/
        case 'LoadResourceData':
            $days = $system->db->clean($_POST['days']);
            $VillageAPIResponse->response = VillageApiPresenter::resourceDataResponse($system, $player, $days);
            break;
        case 'ClaimSeat':
            $seat_type = $system->db->clean($_POST['seat_type']);
            $message = Village::claimSeat($system, $player, $seat_type);
            $VillageAPIResponse->response = [
                'seatData' => VillageApiPresenter::seatDataResponse($system, $player),
                'playerSeat' => Village::getPlayerSeat($system, $player->user_id),
                'response_message' => $message,
            ];
            break;
        case 'Resign':
            $message = Village::resign($system, $player);
            $VillageAPIResponse->response = [
                'seatData' => VillageApiPresenter::seatDataResponse($system, $player),
                'playerSeat' => Village::getPlayerSeat($system, $player->user_id),
                'response_message' => $message,
            ];
            break;
        default:
            API::exitWithError(message: "Invalid request!", system: $system);
    }

    API::exitWithData(
        data: $VillageAPIResponse->response,
        errors: $VillageAPIResponse->errors,
        debug_messages: $system->debug_messages,
        system: $system,
    );
} catch (Throwable $e) {
    API::exitWithException($e, system: $system);
}

