<?php

function travel(): void {
	global $system;
	global $player;

    require 'templates/travel.php';
}

function LoadScoutData(System $system, User $player): TravelAPIResponse {
    $response = new TravelAPIResponse();
    $travel = new TravelManager($system, $player);
    try {

        $players = $travel->getNearbyPlayers();

        $response->response_data = $players;

    } catch (Exception $e) {
        $response->errors[] = $e->getMessage();
    }
    return $response;
}

function LoadMapData(System $system, User $player): TravelAPIResponse {
    $response = new TravelAPIResponse();
    try {

        $map_data = Travel::getMapData($system, $player->z);
        if (empty($map_data)) {
            $response->errors[] = 'Could not load map';
            return $response;
        }

        // main background
        $map_data['background_image'] = $system->link . $map_data['background'];

        // player data
        $map_data['player_icon'] = $system->link . '/images/ninja_head.png';
        $map_data['player_location'] = $player->location;
        $map_data['player_x'] = $player->x;
        $map_data['player_y'] = $player->y;
        $map_data['player_z'] = $player->z;
        $map_data['in_village'] = $player->in_village;
        $map_data['self_user_id'] = $player->user_id;

        // container data
        $map_data['container_width_tiles'] = floor($map_data['container_width'] / $map_data['tile_width']);
        $map_data['container_height_tiles'] = floor($map_data['container_height'] / $map_data['tile_height']);

        // calculate the start and end of the view map
        $map_start_x = $player->x - floor($map_data['container_width_tiles'] / 2);
        $map_start_y = $player->y - floor($map_data['container_height_tiles'] / 2);
        $map_end_x = $player->x + floor($map_data['container_width_tiles'] / 2) - 1;
        $map_end_y = $player->y + floor($map_data['container_height_tiles'] / 2) - 1;
        $map_data['map_start_x'] = $map_start_x <= 0 ? 1 : $map_start_x;
        $map_data['map_start_y'] = $map_start_y <= 0 ? 1 : $map_start_y;
        $map_data['map_end_x'] = $map_end_x;
        $map_data['map_end_y'] = $map_end_y;
        // if the map ends before the display ends
        if (($map_data['map_end_x'] - $map_data['map_start_x']) < $map_data['container_width_tiles']) {
            $diff = $map_data['container_width_tiles'] - ($map_data['map_end_x'] - $map_data['map_start_x']);
            $map_data['map_end_x'] += $diff - 1;
        }
        // if the display end is greater than the actual map end
        if ($map_data['map_end_x'] > $map_data['map_width']) {
            $map_data['map_start_x'] = $map_data['map_width'] - ($map_data['container_width_tiles'] - 1);
            $map_data['map_end_x'] = $map_data['map_width'];
        }

        // if the map ends before the display ends
        if (($map_data['map_end_y'] - $map_data['map_start_y']) < $map_data['container_height_tiles']) {
            $diff = $map_data['container_height_tiles'] - ($map_data['map_end_y'] - $map_data['map_start_y']);
            $map_data['map_end_y'] += $diff - 1;
        }
        // if the display end is greater than the actual map end
        if ($map_data['map_end_y'] > $map_data['map_height']) {
            $map_data['map_start_y'] = $map_data['map_height'] - ($map_data['container_height_tiles'] - 1);
            $map_data['map_end_y'] = $map_data['map_height'];
        }

        // calculate the background position for render
        $map_data['bg_img_start_x'] = $map_data['map_start_x'] * $map_data['tile_width'] - $map_data['tile_width'];
        $map_data['bg_img_start_y'] = $map_data['map_start_y'] * $map_data['tile_height'] - $map_data['tile_height'];

        // calculate the character position for render
        $map_data['player_img_y'] = ($player->y - $map_data['map_start_y']) * $map_data['tile_height'];
        $map_data['player_img_x'] = ($player->x - $map_data['map_start_x']) * $map_data['tile_width'];

        // gutter numbers
        $gutters_x = [];
        for ($i = $map_data['map_start_x']; $i <= $map_data['map_end_x']; $i++) {
            $gutters_x[$i] = $i;
        }
        $map_data['gutters_x'] = $gutters_x;
        $gutters_y = [];
        for ($i = $map_data['map_start_y']; $i <= $map_data['map_end_y']; $i++) {
            $gutters_y[$i] = $i;
        }
        $map_data['gutters_y'] = $gutters_y;

        // locations
        $map_data['locations_data'] = [];
        foreach ($map_data['locations'] as $location) {
            $tmp_arr = [];
            // calculate the image start
            $tmp_arr['location_name'] = str_replace(' ', '', $location['name']);
            $tmp_arr['bm_img_start_y'] = ($location['y'] - $map_data['map_start_y']) * $map_data['tile_height'];
            $tmp_arr['bm_img_start_x'] = ($location['x'] - $map_data['map_start_x']) * $map_data['tile_height'];
            $tmp_arr['background_image'] = $system->link . $location['background_image'];
            // if the location is the player's home village highlight it yellow
            if ($location['name'] === $player->village) {
                $tmp_arr['background_color'] = Travel::HOME_VILLAGE_COLOR;
            } else {
                $tmp_arr['background_color'] = $location['background_color'];
            }
//
            $map_data['locations_data'][] = $tmp_arr;
        }

        // portal check
        foreach ($map_data['portals'] as $portal) {
            if ($portal['entrance_x'] === $player->x && $portal['entrance_y'] === $player->y) {
                $map_data['portal_display'] = true;
                $map_data['portal_text'] = $portal['portal_text'];
                $map_data['portal_id'] = $portal['portal_id'];
            }
        }

        // mission location check
        $map_data['mission_button'] = false;
        if ($player->mission_id &&
           ($player->mission_stage['action_type'] == 'travel' ||
            $player->mission_stage['action_type'] == 'search') &&
            $map_data['player_location'] == $player->mission_stage['action_data']) {
            $map_data['mission_button'] = true;
        }

        // player movement data
        $movement_actions = [];
        $movement_actions[] = [
            'move_direction' => 'move_north',
            'px_top' => ($map_data['player_img_y'] - $map_data['tile_height']),
            'px_left' => ($map_data['player_img_x'])
        ];
        $movement_actions[] = [
            'move_direction' => 'move_south',
            'px_top' => ($map_data['player_img_y'] + $map_data['tile_height']),
            'px_left' => ($map_data['player_img_x'])
        ];
        $movement_actions[] = [
            'move_direction' => 'move_west',
            'px_top' => ($map_data['player_img_y']),
            'px_left' => ($map_data['player_img_x'] - $map_data['tile_width'])
        ];
        $movement_actions[] = [
            'move_direction' => 'move_east',
            'px_top' => ($map_data['player_img_y']),
            'px_left' => ($map_data['player_img_x'] + $map_data['tile_width'])
        ];
        $movement_actions[] = [
            'move_direction' => 'move_northwest',
            'px_top' => ($map_data['player_img_y'] - $map_data['tile_height']),
            'px_left' => ($map_data['player_img_x'] - $map_data['tile_width'])
        ];
        $movement_actions[] = [
            'move_direction' => 'move_southwest',
            'px_top' => ($map_data['player_img_y'] + $map_data['tile_height']),
            'px_left' => ($map_data['player_img_x'] - $map_data['tile_width'])
        ];
        $movement_actions[] = [
            'move_direction' => 'move_northeast',
            'px_top' => ($map_data['player_img_y'] - $map_data['tile_height']),
            'px_left' => ($map_data['player_img_x'] + $map_data['tile_width'])
        ];
        $movement_actions[] = [
            'move_direction' => 'move_southeast',
            'px_top' => ($map_data['player_img_y'] + $map_data['tile_height']),
            'px_left' => ($map_data['player_img_x'] + $map_data['tile_width'])
        ];
        $map_data['movement_actions'] = $movement_actions;

        // return the response
        $response->response_data = $map_data;

    } catch (Exception $e) {
        $response->errors[] = $e->getMessage();
    }
    return $response;
}

function MovePlayer(System $system, User $player, string $direction): TravelAPIResponse {
    $response = new TravelAPIResponse();
    try {

        $map_data = Travel::getMapData($system, $player->z);
        $new_coords = Travel::getNewMovementValues($direction, $player->x, $player->y);

        $current_time_millisecond = floor(microtime(true) * 1000);

        $ignore_travel_restriction = $player->isHeadAdmin();

        // check if the user has moved too recently
        $move_time_left = Travel::checkMovementDelay($player->last_movement);
        if ($move_time_left > 0 && !$ignore_travel_restriction) {
            $response->response_data = [
                false,
                'You cannot move that fast!'
            ];
            return $response;
        }

        // check if the user has exited an ai too recently
        $ai_time_left = Travel::checkAIDelay($player->last_ai);
        if ($ai_time_left > 0 && !$ignore_travel_restriction) {
            $response->response_data = [
                false,
                'You have recently left a battle and cannot move for '.$ai_time_left.' seconds!'
            ];
            return $response;
        }

        // check if the user has exited battle too recently
        $pvp_time_left = Travel::checkPVPDelay($player->last_pvp);
        if ($pvp_time_left > 0 && !$ignore_travel_restriction) {
            $response->response_data = [
                false,
                'You have recently left a battle and cannot move for '.$pvp_time_left.' seconds!'
            ];
            return $response;
        }

        // check if the user has died to recently
        $death_time_left = Travel::checkDeathDelay($player->last_death);
        if ($death_time_left > 0 && !$ignore_travel_restriction) {
            $response->response_data = [
                false,
                'You are still recovering from a defeat and cannot move for ' . $death_time_left . ' seconds!'
            ];
            return $response;
        }

        // check if the user is in battle
        if ($player->battle_id && !$ignore_travel_restriction) {
            $response->response_data = [
                false,
                'You are in battle!'
            ];
            return $response;
        }

        // check if the user is in a special mission
        if ($player->special_mission && !$ignore_travel_restriction) {
            $response->response_data = [
                false,
                'You are currently in a Special Mission and cannot travel!'
            ];
            return $response;
        }

        // check if the user is in a combat mission fail it
        if ($player->mission_id && $player->mission_stage['action_type'] == 'combat') {
            $mission = new Mission($player->mission_id, $player);
            if ($mission->mission_type == 5) {
                $mission->nextStage($player->mission_stage['stage_id'] = 4);
                $player->mission_stage['mission_money'] /= 2;
                $response->errors[] = 'Mission failed! Return to the village.';
                return $response;
            }
        }

        // check if the coords exceed the map dimensions
        if ($new_coords['y'] > $map_data['map_height'] ||
            $new_coords['x'] > $map_data['map_width'] ||
            $new_coords['x'] < 1 ||
            $new_coords['y'] < 1) {
            $response->response_data = [
                false,
                'You cannot move past this point.'
            ];
            return $response;
        }

        // check if the user is trying to move to a village that is not theirs
        $location_string = $new_coords['x'].'.'.$new_coords['y'] . '.' . $player->z;
        $villages = $system->getVillageLocations();
        if (isset($villages[$location_string]) &&
            $location_string !== $player->village_location &&
            !$ignore_travel_restriction) {
            $response->response_data = [
                false,
                'You cannot enter another village!'
            ];
            return $response;
        }

        // check if the user is entering their own village or out of it
        if ($location_string == $player->village_location) {
            $player->in_village = true;
        } else {
            $player->in_village = false;
        }

        // update the player data
        $player->x = $new_coords['x'];
        $player->y = $new_coords['y'];
        $player->last_movement = $current_time_millisecond;
        $player->updateData();

        // return the response
        $response->response_data = [
            true,
            'You moved to ' . $new_coords['x'] . '.' . $new_coords['y']
        ];

    } catch (Exception $e) {
        $response->errors[] = $e->getMessage();
    }
    return $response;
}

function EnterPortal(System $system, User $player, int $portal_id): TravelAPIResponse {
    $response = new TravelAPIResponse();
    try {

        $map_data = Travel::getMapData($system, $player->z);

        // get the portal data and check if it exists
        $portal_data = Travel::getPortalData($system, $portal_id);
        if (empty($portal_data)) {
            $response->errors[] = 'This place doesn\'t exist!';
            return $response;
        }

        // check if the player is at the correct entrance
        if ($player->z !== $portal_data['from_id'] ||
            $player->x !== $portal_data['entrance_x'] ||
            $player->y !== $portal_data['entrance_y']) {
            $response->errors[] = 'You cannot enter '.$portal_data['entrance_name'].' here!';
            return $response;
        }

        // check if the user has moved too recently
        $move_time_left = Travel::checkMovementDelay($player->last_movement);
        if ($move_time_left > 0 && !$ignore_travel_restriction) {
            $response->errors[] = 'You cannot move that fast!';
            return $response;
        }

        // check if the user has exited an ai too recently
        $ai_time_left = Travel::checkAIDelay($player->last_ai);
        if ($ai_time_left > 0 && !$ignore_travel_restriction) {
            $response->errors[] = 'You have recently left a battle and cannot move for '.$ai_time_left.' seconds!';
            return $response;
        }

        // check if the user has exited battle too recently
        $pvp_time_left = Travel::checkPVPDelay($player->last_pvp);
        if ($pvp_time_left > 0 && !$ignore_travel_restriction) {
            $response->errors[] = 'You have recently left a battle and cannot move for '.$pvp_time_left.' seconds!';
            return $response;
        }

        // check if the user has died to recently
        $death_time_left = Travel::checkDeathDelay($player->last_death);
        if ($death_time_left > 0 && !$ignore_travel_restriction) {
            $response->errors[] = 'You are still recovering from a defeat and cannot move for ' . $death_time_left . ' seconds!';
            return $response;
        }

        // check if the user is in battle
        if ($player->battle_id && !$ignore_travel_restriction) {
            $response->errors[] = 'You are in battle!';
            return $response;
        }

        // check if the user is in a special mission
        if ($player->special_mission && !$ignore_travel_restriction) {
            $response->errors[] = 'You are currently in a Special Mission and cannot travel!';
            return $response;
        }

        // check if the user is in a combat mission fail it
        if ($player->mission_id && $player->mission_stage['action_type'] == 'combat') {
            $mission = new Mission($player->mission_id, $player);
            if ($mission->mission_type == 5) {
                $mission->nextStage($player->mission_stage['stage_id'] = 4);
                $player->mission_stage['mission_money'] /= 2;
                $response->errors[] = 'Mission failed! Return to the village.';
                return $response;
            }
        }

        // check if the player is in a faction that allows this portal
        $portal_whitelist = array_map('trim', explode(',', $portal_data['whitelist']));
        if (!in_array($player->village, $portal_whitelist) && !$ignore_travel_restriction) {
            $response->errors[] = 'You cannot enter '.$portal_data['entrance_name'].'!';
            return $response;
        }

        $current_time_millisecond = floor(microtime(true) * 1000);

        // update the player data
        $player->x = $portal_data['exit_x'];
        $player->y = $portal_data['exit_y'];
        $player->z = $portal_data['to_id'];
        $player->last_movement = $current_time_millisecond;
        $player->updateData();

        // return the response
        $response->response_data = [true, 'You traveled to '.$portal_data['entrance_name']];

    } catch (Exception $e) {
        $response->errors[] = $e->getMessage();
    }
    return $response;
}