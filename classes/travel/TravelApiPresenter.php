<?php

class TravelApiPresenter {
    public static function travelActionResponse(bool $success, User $player, TravelManager $travelManager): array {
        return [
            'success' => $success,
            'mapData' => TravelApiPresenter::mapDataResponse(player: $player, travelManager: $travelManager),
            'nearbyPlayers' => TravelApiPresenter::nearbyPlayersResponse(travelManager: $travelManager),
        ];
    }

    public static function mapDataResponse(User $player, TravelManager $travelManager): array {
        $locations = $travelManager->fetchCurrentMapLocations();
        $current_location_portal = $travelManager->fetchCurrentLocationPortal();
        $location_action = $travelManager->getMapLocationAction($locations, $player);

        return [
            'player_x'          => $player->location->x,
            'player_y'          => $player->location->y,
            'player_map_id'     => $player->location->map_id,
            'player_id'         => $player->user_id,
            'player_filters'    => $player->filters,
            'player_icon'       => Travel::PLAYER_ICON,
            'map_name'          => $travelManager->map_data['map_name'],
            'background_image'  => $travelManager->map_data['background'],
            'start_x'           => $travelManager->map_data['start_x'],
            'start_y'           => $travelManager->map_data['start_y'],
            'end_x'             => $travelManager->map_data['end_x'],
            'end_y'             => $travelManager->map_data['end_y'],
            'in_village'        => $player->in_village,
            'current_portal'    => $current_location_portal,
            'current_mission'   => $travelManager->shouldShowMissionLocationPrompt(),
            'all_locations'     => $locations,
            'tile_width'        => $travelManager->map_data['tile_width'],
            'tile_height'       => $travelManager->map_data['tile_height'],
            'action_url'        => $location_action->action_url,
            'action_message'    => $location_action->action_message,
        ];
    }

    public static function nearbyPlayersResponse(TravelManager $travelManager): array {
        return array_map(
            function(NearbyPlayerDto $nearbyPlayer) {
                return [
                    'user_id'       => $nearbyPlayer->user_id,
                    'user_name'     => $nearbyPlayer->user_name,
                    'target_x'      => $nearbyPlayer->target_x,
                    'target_y'      => $nearbyPlayer->target_y,
                    'target_map_id' => $nearbyPlayer->target_map_id,
                    'rank_name'     => $nearbyPlayer->rank_name,
                    'rank_num'      => $nearbyPlayer->rank_num,
                    'village_icon'  => $nearbyPlayer->village_icon,
                    'alignment'     => $nearbyPlayer->alignment,
                    'attack'        => $nearbyPlayer->attack,
                    'attack_id'     => $nearbyPlayer->attack_id,
                    'level'         => $nearbyPlayer->level,
                    'battle_id'     => $nearbyPlayer->battle_id,
                    'direction'     => $nearbyPlayer->direction,
                ];
            },
            $travelManager->fetchNearbyPlayers()
        );
    }
}
