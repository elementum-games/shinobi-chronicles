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

    $response = new APIResponse();


    switch($request) {
        case "PurchaseBasicRamen":
            $ramen_key = $system->db->clean($_POST['ramen_key']);
            $result = RamenShopManager::purchaseBasicRamen($system, $player, $ramen_key);
            if ($result->succeeded) {
                $response->data = [
                    "player_data" => UserAPIPresenter::playerDataResponse($player, RankManager::fetchNames($system)),
                    "player_resources" => UserAPIPresenter::playerResourcesResponse($player),
                ];
            } else {
                $response->errors[] = $result->error_message;
            }
            break;
        case "PurchaseSpecialRamen":
            $ramen_key = $system->db->clean($_POST['ramen_key']);
            $result = RamenShopManager::purchaseSpecialRamen($system, $player, $ramen_key);
            if ($result->succeeded) {
                $response->data = [
                    "player_data" => UserAPIPresenter::playerDataResponse($player, RankManager::fetchNames($system)),
                ];
            } else {
                $response->errors[] = $result->error_message;
            }
            break;
        case "PurchaseMysteryRamen":
            $result = RamenShopManager::purchaseMysteryRamen($system, $player);
            if ($result->succeeded) {
                $response->data = [
                    "player_data" => UserAPIPresenter::playerDataResponse($player, RankManager::fetchNames($system)),
                ];
            } else {
                $response->errors[] = $result->error_message;
            }
            break;
        default:
            API::exitWithError(message: "Invalid request!", system: $system);
    }

    API::exitWithData(
        data: $response->data,
        errors: $response->errors,
        debug_messages: $system->debug_messages,
        system: $system,
    );
} catch (Throwable $e) {
    API::exitWithException($e, system: $system);
}

