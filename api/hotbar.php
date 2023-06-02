<?php

# Begin standard auth
require_once __DIR__ . "/../classes.php";

$system = new System();
$system->is_api_request = true;

try {
    $player = Auth::getUserFromSession($system);
    $player->loadData(User::UPDATE_REGEN);
}
catch(Exception $e) {
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

    $HotbarAPIResponse = new HotbarAPIResponse();
    $HotbarManager = new HotbarManager($system, $player);

    switch($request) {
        case "getPlayerData":
            $HotbarAPIResponse->response_data = [
                'playerData' => HotbarAPIPresenter::playerDataResponse(player: $player, rank_names: RankManager::fetchNames($system)),
            ];
            break;
        case "getMissionData":
            $HotbarAPIResponse->response_data = [
                'missionData' => HotbarAPIPresenter::missionDataResponse(hotbarManager: $HotbarManager),
            ];
            break;
        case "getAIData":
            $HotbarAPIResponse->response_data = [
                'aiData' => HotbarAPIPresenter::aiDataResponse(hotbarManager: $HotbarManager),
            ];
            break;
        default:
            API::exitWithError("Invalid request!");
    }

    API::exitWithData(
        data: $HotbarAPIResponse->response_data,
        errors: $HotbarAPIResponse->errors,
        debug_messages: $system->debug_messages,
    );
}
catch (Throwable $e) {
    API::exitWithError($e->getMessage());
}

