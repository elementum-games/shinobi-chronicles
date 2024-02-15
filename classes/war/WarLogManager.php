<?php

require_once __DIR__ . '/WarLogDto.php';
require_once __DIR__ . '/../village/VillageRelation.php';
require_once __DIR__ . '/WarRecordDto.php';

class WarLogManager {
    const WAR_LOG_INFILTRATE = 'infiltrate_count';
    const WAR_LOG_REINFORCE = 'reinforce_count';
    const WAR_LOG_RAID = 'raid_count';
    const WAR_LOG_LOOT = 'loot_count';
    const WAR_LOG_DAMAGE_DEALT = 'damage_dealt';
    const WAR_LOG_DAMAGE_HEALED = 'damage_healed';
    const WAR_LOG_DEFENSE_GAINED = 'defense_gained';
    const WAR_LOG_DEFENSE_REDUCED = 'defense_reduced';
    const WAR_LOG_RESOURCES_STOLEN = 'resources_stolen';
    const WAR_LOG_RESOURCES_CLAIMED = 'resources_claimed';
    const WAR_LOG_PATROLS_DEFEATED = 'patrols_defeated';
    const WAR_LOG_REGIONS_CAPTURED = 'regions_captured';
    const WAR_LOG_VILLAGES_CAPTURED = 'villages_captured';
    const WAR_LOG_PVP_WINS = 'pvp_wins';
    const WAR_LOG_POINTS_GAINED = 'points_gained';
    const WAR_LOG_STABILITY_REDUCED = 'stability_reduced';
    const WAR_LOG_STABILITY_GAINED = 'stability_gained';

    const SCORE_WEIGHT_DAMAGE_DEALT = 0.1;
    const SCORE_WEIGHT_DAMAGE_HEALED = 0.1;
    const SCORE_WEIGHT_DEFENSE_REDUCED = 10;
    const SCORE_WEIGHT_DEFENSE_GAINED = 10;
    const SCORE_WEIGHT_REGIONS_CAPTURED = 1500;
    const SCORE_WEIGHT_VILLAGES_CAPTURED = 500;
    const SCORE_WEIGHT_RESOURCES_STOLEN = 7.5;
    const SCORE_WEIGHT_PVP_WINS = 100;
    const SCORE_WEIGHT_PATROL_WINS = 25;
    const SCORE_WEIGHT_POINTS_GAINED = 0;
    const SCORE_WEIGHT_STABILITY_REDUCED = 0;
    const SCORE_WEIGHT_STABILITY_GAINED = 0;

    const WAR_LOG_TYPE_PLAYER = "player";
    const WAR_LOG_TYPE_VILLAGE = "village";

    const WAR_LOGS_PER_PAGE = 10;
    const WAR_RECORDS_PER_PAGE = 10;

    public static function logAction(System $system, User $player, int $value, string $type, int $target_village_id) {
        // use null relation_id to track overall
        self::updatePlayerLog($system, $player, $value, $type, null);
        self::updateVillageLog($system, $player, $value, $type, null);
        if (!$player->village->isAlly($target_village_id)) {
            self::updatePlayerLog($system, $player, $value, $type, $player->village->relations[$target_village_id]->relation_id);
            self::updateVillageLog($system, $player, $value, $type, $player->village->relations[$target_village_id]->relation_id);
        }
        else {
            // if target village is self or ally then we log those action types for all ongoing neutral/war relations
            foreach ($player->village->relations as $relation) {
                if ($relation->relation_type != VillageRelation::RELATION_ALLIANCE) {
                    self::updatePlayerLog($system, $player, $value, $type, $relation->relation_id);
                    self::updateVillageLog($system, $player, $value, $type, $relation->relation_id);
                }
            }
        }

    }

    public static function logRegionCapture(System $system, User $player, int $region_id) {
        $region = $system->db->query("SELECT * FROM `regions` WHERE `region_id` = {$region_id} LIMIT 1");
        $region = $system->db->fetch($region);
        $time = time();
        $system->db->query("INSERT INTO `region_logs` (`region_id`, `previous_village_id`, `new_village_id`, `user_id`, `capture_time`, `relation_id`) VALUES ({$region_id}, {$region['village']}, {$player->village->village_id}, {$player->user_id}, {$time}, {$player->village->relations[$region['village']]->relation_id})");
        // log for player
        self::updatePlayerLog($system, $player, 1, self::WAR_LOG_REGIONS_CAPTURED, $player->village->relations[$region['village']]->relation_id);
        self::updatePlayerLog($system, $player, 1, self::WAR_LOG_REGIONS_CAPTURED, null);
        // log for village
        self::updateVillageLog($system, $player, 1, self::WAR_LOG_REGIONS_CAPTURED, $player->village->relations[$region['village']]->relation_id);
        self::updateVillageLog($system, $player, 1, self::WAR_LOG_REGIONS_CAPTURED, null);
    }

    private static function updatePlayerLog(System $system, User $player, int $value, string $type, ?int $relation_id) {
        $player_log = $system->db->query("SELECT * FROM `player_war_logs` WHERE `user_id` = {$player->user_id} AND `village_id` = {$player->village->village_id} AND `relation_id` " . (!empty($relation_id) ? "= " . $relation_id : "IS NULL") . " LIMIT 1");
        $player_log = $system->db->fetch($player_log);
        if ($system->db->last_num_rows == 0) {
            $system->db->query("INSERT INTO `player_war_logs` (`user_id`, `village_id`, `relation_id`, `{$type}`) VALUES ({$player->user_id}, {$player->village->village_id}, " . (!empty($relation_id) ? $relation_id : "NULL") . ", {$value})");
        } else {
            $system->db->query("UPDATE `player_war_logs` SET `{$type}` = `{$type}` + {$value} WHERE `log_id` = {$player_log['log_id']}");
        }
    }

    private static function updateVillageLog(System $system, User $player, int $value, string $type, ?int $relation_id) {
        $village_log = $system->db->query("SELECT * FROM `village_war_logs` WHERE `village_id` = {$player->village->village_id} AND `relation_id` " . (!empty($relation_id) ? "= " . $relation_id : "IS NULL") . " LIMIT 1");
        $village_log = $system->db->fetch($village_log);
        if ($system->db->last_num_rows == 0) {
            $system->db->query("INSERT INTO `village_war_logs` (`village_id`, `relation_id`, `{$type}`) VALUES ({$player->village->village_id}, " . (!empty($relation_id) ? $relation_id : "NULL") . ", {$value})");
        } else {
            $system->db->query("UPDATE `village_war_logs` SET `{$type}` = `{$type}` + {$value} WHERE `log_id` = {$village_log['log_id']}");
        }
    }

    /**
     * @return int
     */
    public static function getPlayerTotal(System $system, User $player, string $type): int {
        switch ($type) {
            case self::WAR_LOG_INFILTRATE:
            case self::WAR_LOG_RAID:
            case self::WAR_LOG_LOOT:
            case self::WAR_LOG_DAMAGE_DEALT:
            case self::WAR_LOG_DEFENSE_REDUCED:
            case self::WAR_LOG_RESOURCES_STOLEN:
            case self::WAR_LOG_PATROLS_DEFEATED:
            case self::WAR_LOG_REGIONS_CAPTURED:
            case self::WAR_LOG_PVP_WINS:
            case self::WAR_LOG_POINTS_GAINED:
            case self::WAR_LOG_REINFORCE:
            case self::WAR_LOG_DAMAGE_HEALED:
            case self::WAR_LOG_DEFENSE_GAINED:
            case self::WAR_LOG_RESOURCES_CLAIMED:
            case self::WAR_LOG_STABILITY_REDUCED:
            case self::WAR_LOG_STABILITY_GAINED:
                $result = $system->db->query("SELECT SUM(`{$type}`) AS `total` FROM `player_war_logs` WHERE `user_id` = {$player->user_id} AND `relation_id` IS NULL");
                $result = $system->db->fetch($result);
                if (empty($result['total'])) {
                    return 0;
                }
                return $result['total'];
                break;
            default:
                return 0;
                break;
        }
    }

    /**
     * @return WarLogDto[]
     */
    public static function getPlayerWarLogs(System $system, int $page_number = 1, int $relation_id = null): array {
        $war_logs = [];
        if (!empty($relation_id)) {
            $war_log_result = $system->db->query("SELECT `player_war_logs`.*, `users`.`user_name`
            FROM `player_war_logs`
            INNER JOIN `users`
            ON `player_war_logs`.`user_id` = `users`.`user_id`
            WHERE `relation_id` = {$relation_id}");
        }
        else {
            $war_log_result = $system->db->query("SELECT `player_war_logs`.*, `users`.`user_name`, `villages`.`village_id`
            FROM `player_war_logs`
            INNER JOIN `users`
            ON `player_war_logs`.`user_id` = `users`.`user_id`
            LEFT JOIN `villages`
            ON `users`.`village` = `villages`.`name`
            WHERE `player_war_logs`.`relation_id` IS NULL");
        }
        $war_log_result = $system->db->fetch_all($war_log_result);
        foreach ($war_log_result as $war_log) {
            if (empty($relation_id)) {
                if (isset($war_logs[$war_log['user_id']])) {
                    // Sum new and existing values
                    $new_log = new WarLogDto($war_log, self::WAR_LOG_TYPE_PLAYER);
                    self::calculateWarScore($new_log);
                    $war_logs[$war_log['user_id']]->addValues($new_log);
                } else {
                    // Create new log
                    $new_log = new WarLogDto($war_log, self::WAR_LOG_TYPE_PLAYER);
                    self::calculateWarScore($new_log);
                    $war_logs[$war_log['user_id']] = $new_log;
                }
            } else {
                $new_log = new WarLogDto($war_log, self::WAR_LOG_TYPE_PLAYER);
                self::calculateWarScore($new_log);
                $war_logs[$war_log['log_id']] = $new_log;
            }
        }
        // sort by war score, to-do filters (?)
        usort($war_logs, function ($a, $b) {
            return $b->war_score <=> $a->war_score;
        });
        // assign ranks
        foreach ($war_logs as $index => $war_log) {
            $war_log->rank = $index + 1;
        }
        // pagination
        $war_logs = array_slice($war_logs, ($page_number - 1) * self::WAR_LOGS_PER_PAGE, self::WAR_LOGS_PER_PAGE);
        return $war_logs;
    }

    /**
     * @return WarLogDto
     */
    public static function getPlayerWarLogByID(System $system, int $player_id, int $relation_id = null): WarLogDto {
        // right join to get user info even if no log found
        if (!empty($relation_id)) {
            $war_log_result = $system->db->query("SELECT `player_war_logs`.*, `users`.`user_name`
            FROM `player_war_logs`
            RIGHT JOIN `users`
            ON `player_war_logs`.`user_id` = `users`.`user_id`
            AND `users`.`user_id` = {$player_id}
            WHERE `relation_id` = {$relation_id}
            LIMIT 1");
            $war_log_result = $system->db->fetch($war_log_result);
        } else {
            $war_log_result = $system->db->query("SELECT `player_war_logs`.*, `users`.`user_name`, `villages`.`village_id`
            FROM `player_war_logs`
            RIGHT JOIN `users`
            ON `player_war_logs`.`user_id` = `users`.`user_id`
            LEFT JOIN `villages`
            ON `users`.`village` = `villages`.`name`
            WHERE `player_war_logs`.`relation_id` IS NULL
            AND `users`.`user_id` = {$player_id}");
            $war_log_result = $system->db->fetch_all($war_log_result);
        }
        // If no relation set we get all totals (1 for each village player has been in)
        if (empty($relation_id)) {
            $new_log = null;
            foreach ($war_log_result as $war_log) {
                // If no existing log in DB
                if (empty($war_log['log_id'])) {
                    $war_log = [
                        'log_id' => 0,
                        'user_id' => $player_id,
                        'user_name' => $war_log['user_name'],
                        'village_id' => $war_log['village_id'],
                    ];
                    $new_log = new WarLogDto($war_log, self::WAR_LOG_TYPE_PLAYER);
                }
                // If more than 1 results found, sum new and existing values
                else if (!empty($new_log)) {
                    $next_log = new WarLogDto($war_log, self::WAR_LOG_TYPE_PLAYER);
                    self::calculateWarScore($next_log);
                    $new_log->addValues($next_log);
                }
                // Create new log
                else {
                    $new_log = new WarLogDto($war_log, self::WAR_LOG_TYPE_PLAYER);
                    self::calculateWarScore($new_log);
                }
            }
        } else {
            // If no existing log in DB
            if (empty($war_log_result['log_id'])) {
                $war_log_result = [
                    'log_id' => 0,
                    'user_id' => $player_id,
                    'user_name' => $war_log_result['user_name'],
                    'village_id' => $war_log_result['village_id'],
                ];
            }
            $new_log = new WarLogDto($war_log_result, self::WAR_LOG_TYPE_PLAYER);
            self::calculateWarScore($new_log);
        }
        return $new_log;
    }

    public static function getWarRecords(System $system, int $page_number): array {
        $war_records = [];
        // get all wars from village_relations ordered by relation_end
        $query = $system->db->query("SELECT * FROM `village_relations` WHERE `relation_type` = 3 ORDER BY `relation_start` DESC");
        $relations = $system->db->fetch_all($query);
        foreach ($relations as $relation_data) {
            $relation = new VillageRelation($relation_data);
            $query = $system->db->query("SELECT * FROM `village_war_logs` WHERE `relation_id` = {$relation_data['relation_id']} AND `village_id` = {$relation_data['village1_id']} LIMIT 1");
            $war_log_result = $system->db->fetch($query);
            if (!$system->db->last_num_rows) {
                $war_log_result = [
                    'log_id' => 0,
                    'log_type' => self::WAR_LOG_TYPE_VILLAGE,
                    'village_id' => $relation_data['village1_id'],
                    'relation_id' => $relation_data['relation_id'],
                ];
            }
            $war_log_result['village_name'] = VillageManager::VILLAGE_NAMES[$war_log_result['village_id']];
            $attacker_war_log = new WarLogDto($war_log_result, self::WAR_LOG_TYPE_VILLAGE);
            self::calculateWarScore($attacker_war_log);
            $query = $system->db->query("SELECT * FROM `village_war_logs` WHERE `relation_id` = {$relation_data['relation_id']} AND `village_id` = {$relation_data['village2_id']} LIMIT 1");
            $war_log_result = $system->db->fetch($query);
            if (!$system->db->last_num_rows) {
                $war_log_result = [
                    'log_id' => 0,
                    'log_type' => self::WAR_LOG_TYPE_VILLAGE,
                    'village_id' => $relation_data['village2_id'],
                    'relation_id' => $relation_data['relation_id'],
                ];
            }
            $war_log_result['village_name'] = VillageManager::VILLAGE_NAMES[$war_log_result['village_id']];
            $defender_war_log = new WarLogDto($war_log_result, self::WAR_LOG_TYPE_VILLAGE);
            self::calculateWarScore($defender_war_log);
            $war_records[] = new WarRecordDto($relation, $attacker_war_log, $defender_war_log);
        }
        // pagination
        $war_records = array_slice($war_records, ($page_number - 1) * self::WAR_RECORDS_PER_PAGE, self::WAR_RECORDS_PER_PAGE);
        return $war_records;
    }

    /**
     * @return WarLogDto[]
     */
    public static function getVillageWarLogsByRelationID(System $system, int $relation_id): array {
        $war_logs = [];
        $war_log_result = $system->db->query("SELECT `village_war_logs`.*, `villages`.`name` as `village_name`
        FROM `village_war_logs`
        INNER JOIN `villages`
        ON `village_war_logs`.`village_id` = `villages`.`village_id`
        WHERE `relation_id` = {$relation_id}");
        $war_log_result = $system->db->fetch_all($war_log_result);
        foreach ($war_log_result as $war_log) {
            $new_log = new WarLogDto($war_log, self::WAR_LOG_TYPE_VILLAGE);
            self::calculateWarScore($new_log);
            $war_logs[] = $new_log;
        }
        return $war_logs;
    }

    public static function calculateWarScore(WarLogDto &$warLog) {
        $war_score = 0;
        $objective_score = 0;
        $resource_score = 0;
        $battle_score = 0;

        // calculate objective score
        $objective_score += $warLog->damage_dealt * self::SCORE_WEIGHT_DAMAGE_DEALT;
        $objective_score += $warLog->damage_healed * self::SCORE_WEIGHT_DAMAGE_HEALED;
        $objective_score += $warLog->defense_reduced * self::SCORE_WEIGHT_DEFENSE_REDUCED;
        $objective_score += $warLog->defense_gained * self::SCORE_WEIGHT_DEFENSE_GAINED;
        $objective_score += $warLog->regions_captured * self::SCORE_WEIGHT_REGIONS_CAPTURED;
        $objective_score += $warLog->villages_captured * self::SCORE_WEIGHT_VILLAGES_CAPTURED;
        $objective_score += $warLog->stability_reduced * self::SCORE_WEIGHT_STABILITY_REDUCED;
        $objective_score += $warLog->stability_gained * self::SCORE_WEIGHT_STABILITY_GAINED;

        // calculate resource score
        $resource_score += $warLog->resources_stolen * self::SCORE_WEIGHT_RESOURCES_STOLEN;

        // calculate battle score
        $battle_score += $warLog->pvp_wins * self::SCORE_WEIGHT_PVP_WINS;
        $battle_score += $warLog->patrols_defeated * self::SCORE_WEIGHT_PATROL_WINS;

        // calculatge war score
        $war_score += $objective_score + $battle_score + $resource_score;

        $warLog->war_score = $war_score;
        $warLog->objective_score = $objective_score;
        $warLog->resource_score = $resource_score;
        $warLog->battle_score = $battle_score;
    }
}