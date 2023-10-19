<?php

class VillageApiPresenter {
    public static function policyDataResponse(System $system, User $player): array
    {
        return [
            "policy_id" => VillageManager::getVillagePolicyID($system, $player->village->village_id),
            //"policy" => $player->village->policy,
        ];
    }
    public static function populationDataResponse(System $system, User $player): array
    {
        return VillageManager::getVillagePopulation($system, $player->village->name);
    }
    public static function seatDataResponse(System $system, User $player): array
    {
        return array_map(
            function (VillageSeatDto $seat) {
                return [
                    "seat_key" => $seat->seat_key,
                    "seat_id" => $seat->seat_id,
                    "user_id" => $seat->user_id,
                    "village_id" => $seat->village_id,
                    "seat_type" => $seat->seat_type,
                    "seat_title" => $seat->seat_title,
                    "seat_start" => $seat->seat_start,
                    "user_name" => $seat->user_name,
                    "avatar_link" => $seat->avatar_link,
                ];
            },
            VillageManager::getVillageSeats($system, $player->village->village_id)
        );
    }
    public static function pointsDataResponse(System $system, User $player): array
    {
        return [
            "points" => VillageManager::getVillagePoints($system, $player->village->village_id),
        ];
    }
    public static function diplomacyDataResponse(System $system, User $player): array
    {
        return array_map(
            function ($key, VillageRelation $relation) use ($system, $player) {
                $village = VillageManager::getVillageByID($system, $key);
                $villager_count = VillageManager::getVillagePopulationTotal($system, $village->name);
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
        $resources = VillageManager::getResources($system, $player->village->village_id);
        $resource_history = VillageManager::getResourceHistory($system, $player->village->village_id, $days);
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
        return VillageManager::getClans($system, $player->village->name);
    }
    public static function proposalDataResponse(System $system, User $player): array
    {
        return VillageManager::getProposalHistory($system, $player->village->village_id);
    }
    public static function strategicDataResponse($system): array
    {
        return VillageManager::getVillageStrategicInfo($system);
    }
    public static function challengeDataResponse(System $system, User $player): array
    {
        return VillageManager::getChallengeData($system, $player);
    }
}
