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

if (isset($_POST['request'])) {
    $request = filter_input(INPUT_POST, 'request', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    try {
        switch ($request) {
            case 'AttackPlayer':
                // Copy functionality from Battle page; TO-DO: encapsulate common functionality
                $attack_id = $system->db->clean($_POST['target']);

                // get user id off the attack link
                $result = $system->db->query("SELECT `user_id` FROM `users` WHERE `attack_id`='{$attack_id}' LIMIT 1");
                if ($system->db->last_num_rows == 0) {
                    throw new RuntimeException("Invalid user!");
                }

                $attack_link = $system->db->fetch($result);
                $attack_id = $attack_link['user_id'];

                $user = User::loadFromId($system, $attack_id);
                $user->loadData(User::UPDATE_NOTHING, true);

                // check if the location forbids pvp
                if ($player->current_location->location_id && $player->current_location->pvp_allowed == 0) {
                    throw new RuntimeException("You cannot fight at this location!");
                }

                if ($user->village->name == $player->village->name) {
                    throw new RuntimeException("You cannot attack people from your own village!");
                }

                if ($user->rank_num < 3) {
                    throw new RuntimeException("You cannot attack people below Chuunin rank!");
                }
                if ($player->rank_num < 3) {
                    throw new RuntimeException("You cannot attack people Chuunin rank and higher!");
                }

                if ($user->rank_num !== $player->rank_num) {
                    throw new RuntimeException("You can only attack people of the same rank!");
                }

                if (!$user->location->equals($player->location)) {
                    throw new RuntimeException("Target is not at your location!");
                }
                if ($user->battle_id) {
                    throw new RuntimeException("Target is in battle!");
                }
                if ($user->last_active < time() - 120) {
                    throw new RuntimeException("Target is inactive/offline!");
                }
                if ($player->last_death_ms > System::currentTimeMs() - (60 * 1000)) {
                    throw new RuntimeException("You died within the last minute, please wait " .
                        ceil((($player->last_death_ms + (60 * 1000)) - System::currentTimeMs()) / 1000) . " more seconds.");
                }
                if ($user->last_death_ms > System::currentTimeMs() - (60 * 1000)) {
                    throw new RuntimeException("Target has died within the last minute, please wait " .
                        ceil((($user->last_death_ms + (60 * 1000)) - System::currentTimeMs()) / 1000) . " more seconds.");
                }

                if ($system->USE_NEW_BATTLES) {
                    BattleV2::start($system, $player, $user, Battle::TYPE_FIGHT);
                } else {
                    Battle::start($system, $player, $user, Battle::TYPE_FIGHT);
                }
                $response = new APIResponse();
                $response->data = ['success' => true, 'redirect' => $system->router->getUrl('battle')];
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
} else {
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
}



