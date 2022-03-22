<?php

# Begin standard auth
require "../classes/_autoload.php";

$system = new System();

try {
    $player = Auth::getUserFromSession($system);
} catch(Exception $e) {
    API::exitWithError($e->getMessage());
}
# End standard auth

$player->loadData(User::UPDATE_NOTHING);

$routes = require __DIR__ . '/../config/routes.php';

$battle_result = $system->query("SELECT battle_type FROM battles WHERE `battle_id`='{$player->battle_id}' LIMIT 1");
if($system->db_last_num_rows) {
    $battle_data = $system->db_fetch($battle_result);

    $battle_route = null;
    foreach($routes as $page_id => $page) {
        if(empty($page['battle_api_function_name'])) {
            continue;
        }
        if(empty($page['battle_type'])) {
            continue;
        }

        if($page['battle_type'] == $battle_data['battle_type']) {
            $battle_route = $page;
        }
    }

    if($battle_route == null) {
        API::exitWithError("No route found for battle type!");
    }

    require(__DIR__ . '/../pages/' . $battle_route['file_name']);
    $response = $battle_route['battle_api_function_name']($system, $player);
    if(!($response instanceof BattlePageAPIResponse)) {
        API::exitWithError("Invalid battle API response! - Expected BattlePageAPIResponse, got " . get_class($response));
    }

    API::exitWithData(
        [
            'battle' => $response->battle_data,
            'battle_result' => $response->battle_result,
        ],
        $response->errors
    );
}
else {
    API::exitWithError('Not in battle!');
}



