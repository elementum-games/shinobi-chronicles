<?php

class VillageApiPresenter {
    public static function policyDataResponse(System $system, User $player): array
    {
        return [
        ];
    }
    public static function populationDataResponse(System $system, User $player): array
    {
        return Village::getVillagePopulation($system, $player->village->name);
    }
    public static function seatDataResponse(System $system, User $player): array
    {
        return array_values(Village::getVillageSeats($system, $player->village->village_id));
    }
    public static function pointsDataResponse(System $system, User $player): array
    {
        return [
            "points" => $player->village->points,
        ];
    }
    public static function diplomacyDataResponse(System $system, User $player): array
    {
        return array_map(
            function ($key, VillageRelation $relation) use ($system, $player) {
                $village = Village::getVillageByID($system, $key);
                $villager_count = Village::getVillagePopulationTotal($system, $village->name);
                return [
                    "village_id" => $key,
                    "village_name" => $village->name,
                    "village_points" => $village->points,
                    "villager_count" => $villager_count,
                    "relation_type" => VillageRelation::RELATION_LABEL[$relation->relation_type],
                    "relation_name" => $relation->relation_name,
                ];
            },
            array_keys($player->village->relations),
            array_values($player->village->relations),
        );
    }
    public static function resourceDataResponse(System $system, User $player, int $days): array
    {
        $resources = Village::getResources($system, $player->village->village_id);
        $resource_history = Village::getResourceHistory($system, $player->village->village_id, $days);
        return array_map(
            function ($key, $value) use ($resources, $resource_history) {
                return [
                    "resource_id" => $key,
                    "resource_name" => $value,
                    "count" => !empty($resources[$key]) ? $resources[$key] : 0,
                    "produced" => !empty($resource_history[$key]) ? $resource_history[$key]['produced'] : 0,
                    "collected" => !empty($resource_history[$key]) ? $resource_history[$key]['collected'] : 0,
                    "claimed" => !empty($resource_history[$key]) ? $resource_history[$key]['claimed'] : 0,
                    "lost" => !empty($resource_history[$key]) ? $resource_history[$key]['lost'] : 0,
                    "spent" => !empty($resource_history[$key]) ? $resource_history[$key]['spent'] : 0,
                ];
            },
            array_keys(WarManager::RESOURCE_NAMES),
            array_values(WarManager::RESOURCE_NAMES)
        );
    }
    public static function clanDataResponse(System $system, User $player): array
    {
        return Village::getClans($system, $player->village->name);
    }
}
