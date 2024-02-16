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
                    "is_provisional" => $seat->is_provisional,
                    "provisional_days_label" => $seat->provisional_days_label,
                ];
            },
            VillageManager::getVillageSeats($system, $player->village->village_id)
        );
    }
    public static function pointsDataResponse(System $system, User $player): array
    {
        $points_arr = VillageManager::getVillagePoints($system, $player->village->village_id);
        return [
            "points" => $points_arr['points'],
            "monthly_points" => $points_arr['monthly_points'],
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
        return array_map(
            function (ChallengeRequestDto $challenge) {
                return [
                    "request_id" => $challenge->request_id,
                    "challenger_id" => $challenge->challenger_id,
                    "seat_holder_id" => $challenge->seat_holder_id,
                    "seat_id" => $challenge->seat_id,
                    "created_time" => $challenge->created_time,
                    "accepted_time" => $challenge->accepted_time,
                    "start_time" => $challenge->start_time,
                    "end_time" => $challenge->end_time,
                    "seat_holder_locked" => $challenge->seat_holder_locked,
                    "challenger_locked" => $challenge->challenger_locked,
                    "selected_times" => $challenge->selected_times,
                    "battle_id" => $challenge->battle_id,
                    "winner" => $challenge->winner,
                    "challenger_name" => $challenge->challenger_name,
                    "challenger_avatar" => $challenge->challenger_avatar,
                    "seat_holder_name" => $challenge->seat_holder_name,
                    "seat_holder_avatar" => $challenge->seat_holder_avatar
                ];
            },
            VillageManager::getChallengeData($system, $player)
        );
    }
    public static function playerWarLogDataResponse(System $system, User $player, int $page_number = 1): array {
        $global_leaderboard_war_logs = array_map(
            function (WarLogDto $war_log) {
                return [
                    "log_id" => $war_log->log_id,
                    "log_type" => $war_log->log_type,
                    "user_id" => $war_log->user_id,
                    "user_name" => $war_log->user_name,
                    "village_id" => $war_log->village_id,
                    "village_name" => $war_log->village_name,
                    "relation_id" => $war_log->relation_id,
                    "rank" => $war_log->rank,
                    "infiltrate_count" => $war_log->infiltrate_count,
                    "reinforce_count" => $war_log->reinforce_count,
                    "raid_count" => $war_log->raid_count,
                    "loot_count" => $war_log->loot_count,
                    "damage_dealt" => $war_log->damage_dealt,
                    "damage_healed" => $war_log->damage_healed,
                    "defense_gained" => $war_log->defense_gained,
                    "defense_reduced" => $war_log->defense_reduced,
                    "resources_stolen" => $war_log->resources_stolen,
                    "resources_claimed" => $war_log->resources_claimed,
                    "patrols_defeated" => $war_log->patrols_defeated,
                    "regions_captured" => $war_log->regions_captured,
                    "villages_captured" => $war_log->villages_captured,
                    "pvp_wins" => $war_log->pvp_wins,
                    "points_gained" => $war_log->points_gained,
                    "war_score" => $war_log->war_score,
                    "objective_score" => $war_log->objective_score,
                    "resource_score" => $war_log->resource_score,
                    "battle_score" => $war_log->battle_score,
                ];
            },
            WarLogManager::getPlayerWarLogs($system, page_number: $page_number)
        );
        $player_war_log = WarLogManager::getPlayerWarLogByID($system, $player->user_id);
        return [
            "global_leaderboard_war_logs" => $global_leaderboard_war_logs,
            "player_war_log" => $player_war_log
        ];
    }
    public static function warRecordDataResponse(System $system, User $player, int $page_number = 1): array
    {
        $war_records = array_map(
            function (WarRecordDto $warRecordDto) {
                return [
                    "village_relation" => [
                        "relation_id" => $warRecordDto->village_relation->relation_id,
                        "village1_id" => $warRecordDto->village_relation->village1_id,
                        "village2_id" => $warRecordDto->village_relation->village2_id,
                        "relation_type" => VillageRelation::RELATION_LABEL[$warRecordDto->village_relation->relation_type],
                        "relation_name" => $warRecordDto->village_relation->relation_name,
                        "relation_start" => date("n/j/Y", $warRecordDto->village_relation->relation_start),
                        "relation_end" => $warRecordDto->village_relation->relation_end ? date("n/j/Y", $warRecordDto->village_relation->relation_end) : null,
                    ],
                    "attacker_war_log" => [
                        "log_id" => $warRecordDto->attacker_war_log->log_id,
                        "log_type" => $warRecordDto->attacker_war_log->log_type,
                        "village_id" => $warRecordDto->attacker_war_log->village_id,
                        "village_name" => $warRecordDto->attacker_war_log->village_name,
                        "relation_id" => $warRecordDto->attacker_war_log->relation_id,
                        "infiltrate_count" => $warRecordDto->attacker_war_log->infiltrate_count,
                        "reinforce_count" => $warRecordDto->attacker_war_log->reinforce_count,
                        "raid_count" => $warRecordDto->attacker_war_log->raid_count,
                        "loot_count" => $warRecordDto->attacker_war_log->loot_count,
                        "damage_dealt" => $warRecordDto->attacker_war_log->damage_dealt,
                        "damage_healed" => $warRecordDto->attacker_war_log->damage_healed,
                        "defense_gained" => $warRecordDto->attacker_war_log->defense_gained,
                        "defense_reduced" => $warRecordDto->attacker_war_log->defense_reduced,
                        "resources_stolen" => $warRecordDto->attacker_war_log->resources_stolen,
                        "resources_claimed" => $warRecordDto->attacker_war_log->resources_claimed,
                        "patrols_defeated" => $warRecordDto->attacker_war_log->patrols_defeated,
                        "regions_captured" => $warRecordDto->attacker_war_log->regions_captured,
                        "villages_captured" => $warRecordDto->attacker_war_log->villages_captured,
                        "pvp_wins" => $warRecordDto->attacker_war_log->pvp_wins,
                        "points_gained" => $warRecordDto->attacker_war_log->points_gained,
                        "stability_gained" => $warRecordDto->attacker_war_log->stability_gained,
                        "stability_reduced" => $warRecordDto->attacker_war_log->stability_reduced,
                        "war_score" => $warRecordDto->attacker_war_log->war_score,
                        "objective_score" => $warRecordDto->attacker_war_log->objective_score,
                        "resource_score" => $warRecordDto->attacker_war_log->resource_score,
                        "battle_score" => $warRecordDto->attacker_war_log->battle_score,
                    ],
                    "defender_war_log" => [
                        "log_id" => $warRecordDto->defender_war_log->log_id,
                        "log_type" => $warRecordDto->defender_war_log->log_type,
                        "village_id" => $warRecordDto->defender_war_log->village_id,
                        "village_name" => $warRecordDto->defender_war_log->village_name,
                        "relation_id" => $warRecordDto->defender_war_log->relation_id,
                        "infiltrate_count" => $warRecordDto->defender_war_log->infiltrate_count,
                        "reinforce_count" => $warRecordDto->defender_war_log->reinforce_count,
                        "raid_count" => $warRecordDto->defender_war_log->raid_count,
                        "loot_count" => $warRecordDto->defender_war_log->loot_count,
                        "damage_dealt" => $warRecordDto->defender_war_log->damage_dealt,
                        "damage_healed" => $warRecordDto->defender_war_log->damage_healed,
                        "defense_gained" => $warRecordDto->defender_war_log->defense_gained,
                        "defense_reduced" => $warRecordDto->defender_war_log->defense_reduced,
                        "resources_stolen" => $warRecordDto->defender_war_log->resources_stolen,
                        "resources_claimed" => $warRecordDto->defender_war_log->resources_claimed,
                        "patrols_defeated" => $warRecordDto->defender_war_log->patrols_defeated,
                        "regions_captured" => $warRecordDto->defender_war_log->regions_captured,
                        "villages_captured" => $warRecordDto->defender_war_log->villages_captured,
                        "pvp_wins" => $warRecordDto->defender_war_log->pvp_wins,
                        "points_gained" => $warRecordDto->defender_war_log->points_gained,
                        "stability_gained" => $warRecordDto->defender_war_log->stability_gained,
                        "stability_reduced" => $warRecordDto->defender_war_log->stability_reduced,
                        "war_score" => $warRecordDto->defender_war_log->war_score,
                        "objective_score" => $warRecordDto->defender_war_log->objective_score,
                        "resource_score" => $warRecordDto->defender_war_log->resource_score,
                        "battle_score" => $warRecordDto->defender_war_log->battle_score,
                    ],
                ];
            },
            WarLogManager::getWarRecords($system, page_number: $page_number)
        );
        return [
            "war_records" => $war_records
        ];
    }
    public static function kageRecordResponse(System $system, User $player): array
    {
        return array_map(
            function ($row) {
                return [
                    "user_id" => $row['user_id'],
                    "user_name" => $row['user_name'],
                    "seat_title" => $row['seat_title'],
                    "seat_start" => $row['seat_start'],
                    "seat_end" => $row['seat_end'],
                    "time_held" => $row['time_held'],
                ];
            },
            array_values(VillageManager::getKageRecord($system, $player->village->village_id))
        );
    }
}
