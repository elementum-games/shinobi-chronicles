<?php

class TravelApiPresenter {
    public static function travelActionResponse(bool $success, User $player, TravelManager $travelManager, System $system): array {
        return [
            'success' => $success,
            'mapData' => TravelApiPresenter::mapDataResponse(player: $player, travelManager: $travelManager, system: $system),
            'nearbyPlayers' => TravelApiPresenter::nearbyPlayersResponse(travelManager: $travelManager),
            'nearbyNPCs' => TravelApiPresenter::nearbyNPCsResponse(travelManager: $travelManager),
            'travel_message' => $travelManager->travel_message,
        ];
    }

    public static function mapDataResponse(User $player, TravelManager $travelManager, System $system): array {
        $locations = $travelManager->fetchCurrentMapLocations();
        $current_location_portal = $travelManager->fetchCurrentLocationPortal();
        $location_action = $travelManager->getMapLocationAction($locations, $player);
        $regions = $travelManager->getRegions($player);
        $travelManager->checkWarAction();
        $war_action = $player->war_action_id > 0 ? $travelManager->warManager->getWarActionById($player->war_action_id) : null;
        return [
            'player_x'              => $player->location->x,
            'player_y'              => $player->location->y,
            'player_map_id'         => $player->location->map_id,
            'player_id'             => $player->user_id,
            'player_filters'        => $player->filters,
            'player_icon'           => Travel::PLAYER_ICON,
            'map_name'              => $travelManager->map_data['map_name'],
            'background_image'      => $travelManager->map_data['background'],
            'start_x'               => $travelManager->map_data['start_x'],
            'start_y'               => $travelManager->map_data['start_y'],
            'end_x'                 => $travelManager->map_data['end_x'],
            'end_y'                 => $travelManager->map_data['end_y'],
            'in_village'            => $player->in_village,
            'current_portal'        => $current_location_portal,
            'current_mission'       => $travelManager->shouldShowMissionLocationPrompt(),
            'all_locations'         => $locations,
            'tile_width'            => $travelManager->map_data['tile_width'],
            'tile_height'           => $travelManager->map_data['tile_height'],
            'action_url'            => $location_action->action_url,
            'action_message'        => $location_action->action_message,
            'invulnerable'          => ($player->pvp_immunity_ms > System::currentTimeMs()),
            'region'                => $player->region,
            'region_coords'         => $travelManager->getCoordsByRegion($regions),
            'spar_link'             => $system->router->getUrl('spar'),
            'colosseum_coords'      => $travelManager->getColosseumCoords(),
            'region_objectives'     => TravelApiPresenter::regionObjectiveResponse($travelManager),
            'map_objectives'        => $travelManager->fetchMapObjectives(),
            'battle_url'            => $travelManager->getPlayerBattleUrl(),
            'war_actions'           => $travelManager->warManager->getValidWarActions(for_display: true),
            'war_action_type'       => $war_action ? System::unSlug(WarAction::WAR_ACTION_TYPE_DESCRIPTOR[$war_action->type]) : null,
            'war_action_progress'   => $war_action ? $war_action->progress : null,
            'war_action_interval'   => $war_action ? $war_action->interval_progress : null,
            'loot_count'            => $travelManager->getPlayerLootCount(),
            'is_protected'          => $travelManager->dbFetchIsProtectedByAlly($player),
        ];
    }

    public static function nearbyPlayersResponse(TravelManager $travelManager): array {
        return array_map(
            function(NearbyPlayerDto $nearbyPlayer) {
                return [
                    'user_id'       => $nearbyPlayer->user_id,
                    'user_name'     => $nearbyPlayer->user_name,
                    'target_x'      => $nearbyPlayer->location->x,
                    'target_y'      => $nearbyPlayer->location->y,
                    'target_map_id' => $nearbyPlayer->location->map_id,
                    'rank_name'     => $nearbyPlayer->rank_name,
                    'rank_num'      => $nearbyPlayer->rank_num,
                    'village_icon'  => $nearbyPlayer->village_icon,
                    'alignment'     => $nearbyPlayer->alignment,
                    'attack'        => $nearbyPlayer->attack,
                    'attack_id'     => $nearbyPlayer->attack_id,
                    'level'         => $nearbyPlayer->level,
                    'battle_id'     => $nearbyPlayer->battle_id,
                    'direction'     => $nearbyPlayer->direction,
                    'village_id'    => $nearbyPlayer->village_id,
                    'invulnerable'  => $nearbyPlayer->invulnerable,
                    'distance'      => $nearbyPlayer->distance,
                    'loot_count'    => $nearbyPlayer->loot_count,
                    'is_protected'  => $nearbyPlayer->is_protected,
                ];
            },
            $travelManager->fetchNearbyPlayers()
        );
    }

    public static function nearbyNPCsResponse(TravelManager $travelManager): array
    {
        return array_map(
            function (MapNPC $nearbyNPC) {
                return [
                    'npc_id' => $nearbyNPC->id,
                    'npc_name' => $nearbyNPC->name,
                    'target_x' => $nearbyNPC->current_x,
                    'target_y' => $nearbyNPC->current_y,
                    'target_map_id' => $nearbyNPC->map_id,
                    'npc_type' => $nearbyNPC->type,
                    'alignment' => $nearbyNPC->alignment,
                    'village_id' => $nearbyNPC->village_id,
                    'tier' => $nearbyNPC->tier,
                    'resources' => $nearbyNPC->resources,
                ];
            },
            $travelManager->fetchNearbyNPCs()
        );
    }

    public static function regionObjectiveResponse(TravelManager $travelManager): array
    {
        return array_map(
            function (RegionObjective $regionObjective) {
                return [
                    'id' => $regionObjective->id,
                    'name' => $regionObjective->name,
                    'map_id' => $regionObjective->map_id,
                    'x' => $regionObjective->x,
                    'y' => $regionObjective->y,
                    'image' => $regionObjective->image,
                    'objective_health' => $regionObjective->objective_health,
                    'objective_max_health' => $regionObjective->objective_max_health,
                    'defense' => $regionObjective->defense,
                    'objective_type' => $regionObjective->objective_type,
                    'village_id' => $regionObjective->village_id,
                    'resource_id' => $regionObjective->resource_id,
                    'resource_count' => $regionObjective->resource_count,
                    'resource_name' => WarManager::RESOURCE_NAMES[$regionObjective->resource_id],
                    'stability' => $regionObjective->stability,
                    'rebellion_active' => $regionObjective->rebellion_active,
                ];
            },
            $travelManager->fetchRegionObjectives()
        );
    }

    public static function attackPlayerResponse(bool $success, TravelManager $travelManager, System $system): array
    {
        return [
            'success' => $success,
            'travel_message' => $travelManager->travel_message,
            'redirect' => $system->router->getUrl('battle'),
        ];
    }
}
