<?php

require __DIR__ . '/WarLogDto.php';

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

    const SCORE_WEIGHT_DAMAGE_DEALT = 0.1;
    const SCORE_WEIGHT_DAMAGE_HEALED = 0.1;
    const SCORE_WEIGHT_DEFENSE_REDUCED = 10;
    const SCORE_WEIGHT_DEFENSE_GAINED = 10;
    const SCORE_WEIGHT_REGIONS_CAPTURED = 1500;
    const SCORE_WEIGHT_VILLAGES_CAPTURED = 500;
    const SCORE_WEIGHT_RESOURCES_STOLEN = 7.5;
    const SCORE_WEIGHT_PVP_WINS = 100;
    const SCORE_WEIGHT_PATROL_WINS = 25;

    const WAR_LOG_TYPE_PLAYER = "player";
    const WAR_LOG_TYPE_VILLAGE = "village";

    const WAR_LOGS_PER_PAGE = 10;

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
                $result = $system->db->query("SELECT `{$type}` AS `total` FROM `player_war_logs` WHERE `user_id` = {$player->user_id} AND `relation_id` IS NULL");
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
            $war_log_result = $system->db->query("SELECT `player_war_logs`.*, `users`.`user_name`
            FROM `player_war_logs`
            INNER JOIN `users`
            ON `player_war_logs`.`user_id` = `users`.`user_id`
            WHERE `relation_id` IS NULL");
        }
        $war_log_result = $system->db->fetch_all($war_log_result);
        foreach ($war_log_result as $war_log) {
            $new_log = new WarLogDto($war_log, self::WAR_LOG_TYPE_PLAYER);
            self::calculateWarScore($new_log);
            $war_logs[] = $new_log;
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
            AND `player_war_logs`.`user_id` = {$player_id}
            WHERE `relation_id` = {$relation_id}
            LIMIT 1");
        } else {
            $war_log_result = $system->db->query("SELECT `player_war_logs`.*, `users`.`user_name`
            FROM `player_war_logs`
            RIGHT JOIN `users`
            ON `player_war_logs`.`user_id` = `users`.`user_id`
            AND `player_war_logs`.`user_id` = {$player_id}
            WHERE `relation_id` IS NULL
            LIMIT 1");
        }
        $war_log_result = $system->db->fetch($war_log_result);
        if (empty($war_log_result['log_id'])) {
            $war_log_result = [
                'log_id' => 0,
                'user_id' => $player_id,
                'user_name' => $war_log_result['user_name'],
                'village_id' => 0,
            ];
        }
        $new_log = new WarLogDto($war_log_result, self::WAR_LOG_TYPE_PLAYER);
        self::calculateWarScore($new_log);
        return $new_log;
    }

    /**
     * @return WarLogDto[]
     */
    public static function getVillageWarLogs(System $system, int $relation_id): array
    {
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