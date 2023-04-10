<?php

# Begin standard auth
require "../classes/_autoload.php";

$system = new System();
$system->is_api_request = true;

try {
    $player = Auth::getUserFromSession($system);
} catch(Exception $e) {
    API::exitWithError($e->getMessage());
}
# End standard auth

$player->loadData(User::UPDATE_NOTHING);

$routes = Router::$routes;

$battle_result = $system->query("SELECT battle_type FROM battles WHERE `battle_id`='{$player->battle_id}' LIMIT 1");
if($system->db_last_num_rows) {
    $battle_data = $system->db_fetch($battle_result);

    $battle_route = null;
    foreach($routes as $page_id => $page) {
        if(empty($page->battle_api_function_name)) {
            continue;
        }
        if(empty($page->battle_type)) {
            continue;
        }

        if($page->battle_type == $battle_data['battle_type']) {
            $battle_route = $page;
        }
    }

    if($battle_route == null) {
        API::exitWithError("No route found for battle type!");
    }

    require(__DIR__ . '/../pages/' . $battle_route['file_name']);

    try {
        $response = $battle_route['battle_api_function_name']($system, $player);
    } catch (Throwable $e) {
        API::exitWithError(
            message: $e->getMessage(),
            debug_messages: $system->debug_messages
        );
    }

    if(!($response instanceof BattlePageAPIResponse)) {
        API::exitWithError("Invalid battle API response! - Expected BattlePageAPIResponse, got " . get_class($response));
    }

    API::exitWithData(
        data: [
            'battle' => $response->battle_data,
            'battleResult' =>$response->battle_result,
        ],
        errors: $response->errors,
        debug_messages: $system->debug_messages,
    );
}
else {
    API::exitWithError('Not in battle!');
}



