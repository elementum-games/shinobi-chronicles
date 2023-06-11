<?php

# Begin standard auth
require_once __DIR__ . "/../classes.php";

$system = new System();
$system->is_api_request = true;

try {
    $player = Auth::getUserFromSession($system);
    $player->loadData(User::UPDATE_REGEN);
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

    $UserAPIResponse = new UserAPIResponse();
    $UserAPIManager = new UserAPIManager($system, $player);

    switch ($request) {
        case "getPlayerData":
            $UserAPIResponse->response_data = [
                'playerData' => UserAPIPresenter::playerDataResponse(player: $player,
                    rank_names: RankManager::fetchNames($system)),
            ];
            break;
        case "getPlayerResources":
            $UserAPIResponse->response_data = [
                'playerResources' => UserAPIPresenter::playerResourcesResponse($player),
            ];
            break;
        case "getPlayerSettings":
            $UserAPIResponse->response_data = [
                'playerSettings' => UserAPIPresenter::playerSettingsResponse($player),
            ];
            break;
        case "getMissionData":
            $UserAPIResponse->response_data = [
                'missionData' => UserAPIPresenter::missionDataResponse(userManager: $UserAPIManager),
            ];
            break;
        case "getAIData":
            $UserAPIResponse->response_data = [
                'aiData' => UserAPIPresenter::aiDataResponse(userManager: $UserAPIManager),
            ];
            break;
        default:
            API::exitWithError("Invalid request!");
    }

    API::exitWithData(
        data: $UserAPIResponse->response_data,
        errors: $UserAPIResponse->errors,
        debug_messages: $system->debug_messages,
    );
} catch (Throwable $e) {
    API::exitWithError($e->getMessage());
}