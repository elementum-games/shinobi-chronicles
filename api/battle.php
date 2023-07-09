<?php

# Begin standard auth
require "../classes/_autoload.php";

$system = API::init(row_lock: true);

try {
    $player = Auth::getUserFromSession($system);
    $player->loadData(User::UPDATE_NOTHING);
} catch(RuntimeException $e) {
    API::exitWithException($e, system: $system);
}
# End standard auth

$battle_result = $system->db->query("SELECT battle_type FROM battles WHERE `battle_id`='{$player->battle_id}' LIMIT 1");
if ($system->db->last_num_rows) {
    $battle_data = $system->db->fetch($battle_result);

    $battle_route = null;
    foreach (Router::$routes as $page_id => $page) {
        if (empty($page->battle_type)) {
            continue;
        }

        if ($page->battle_type == $battle_data['battle_type']) {
            $battle_route = $page;
        }
    }

    if ($battle_route == null) {
        API::exitWithError(
            message: "No route found for battle type!",
            system: $system
        );
    }

    require(__DIR__ . '/../pages/' . $battle_route->file_name);

    try {
        switch ($battle_route->battle_type) {
            case Battle::TYPE_AI_ARENA:
                $response = arenaFightAPI($system, $player);
                break;
            case Battle::TYPE_AI_MISSION:
                $response = missionFightAPI($system, $player);
                break;
            case Battle::TYPE_SPAR:
                $response = sparFightAPI($system, $player);
                break;
            case Battle::TYPE_FIGHT:
                $response = battleFightAPI($system, $player);
                break;
            case Battle::TYPE_AI_RANKUP:
                $response = rankupFightAPI($system, $player);
                break;
            default:
                throw new RuntimeException("Invalid battle route!");
        }

        if (!($response instanceof BattlePageAPIResponse)) {
            API::exitWithError(
                message: "Invalid battle API response! - Expected BattlePageAPIResponse, got " . get_class($response),
                system: $system,
            );
        }

        $player->updateData();
    } catch (Throwable $e) {
        API::exitWithException(
            exception: $e,
            system: $system,
            debug_messages: $system->debug_messages
        );
    }

    API::exitWithData(
        data: [
            'battle' => $response->battle_data,
            'battleResult' => $response->battle_result,
        ],
        errors: $response->errors,
        debug_messages: $system->debug_messages,
        system: $system,
    );
} else {
    API::exitWithError(message: 'Not in battle!', system: $system);
}