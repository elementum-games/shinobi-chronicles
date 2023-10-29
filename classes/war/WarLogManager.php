<?php

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
    const WAR_LOG_PVP_WINS = 'pvp_wins';
    const WAR_LOG_POINTS_GAINED = 'points_gained';

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
        $system->db->query("INSERT INTO `region_logs` (`region_id`, `previous_village_id`, `new_village_id`, `user_id`, `capture_time`, `relation_id`) VALUES ({$region_id}, {$region['village_id']}, {$player->village->village_id}, {$player->user_id}, {$time}, {$player->village->relations[$region['village']]->relation_id})");
        // log for player
        self::updatePlayerLog($system, $player, 1, self::WAR_LOG_REGIONS_CAPTURED, $player->village->relations[$region['village_id']]->relation_id);
        // log for village
        self::updateVillageLog($system, $player, 1, self::WAR_LOG_REGIONS_CAPTURED, $player->village->relations[$region['village_id']]->relation_id);
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
                $result = $system->db->query("SELECT SUM(`{$type}`) AS `total` FROM `player_war_logs` WHERE `user_id` = {$player->user_id}");
                $result = $system->db->fetch($result);
                if (empty($result['total'])) {
                    return 0;
                }
                return $result['total'];
                break;
            case self::WAR_LOG_REINFORCE:
            case self::WAR_LOG_DAMAGE_HEALED:
            case self::WAR_LOG_DEFENSE_GAINED:
            case self::WAR_LOG_RESOURCES_CLAIMED:
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
}